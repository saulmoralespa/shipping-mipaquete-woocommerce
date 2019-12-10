<?php


class WC_Shipping_Method_Shipping_Mipaquete_SMW extends WC_Shipping_Method
{
    public $email;

    protected $_password;

    public function __construct($instance_id = 0)
    {
        parent::__construct($instance_id);

        $this->id = 'shipping_mipaquete_wc';
        $this->instance_id = absint( $instance_id );
        $this->method_title = __( 'Mipaquete' );
        $this->method_description = __( 'Facilitador de entregas de productos' );
        $this->title = __( 'Mipaquete' );

        $this->supports = array(
            'settings',
            'shipping-zones'
        );

        $this->init();

        $this->debug = $this->get_option( 'debug' );
        $this->isTest = (bool)$this->get_option( 'environment' );

        if ($this->isTest){
            $this->email = $this->get_option( 'sandbox_email' );
            $this->_password = $this->get_option( 'sandbox_password' );
        }else{
            $this->email = $this->get_option( 'email' );
            $this->_password = $this->get_option( 'password' );
        }

        $this->nit = $this->get_option( 'nit' );
        $this->city_sender = $this->get_option( 'city_sender' );
        $this->value_select = $this->get_option( 'value_select' );
        $this->collection = $this->get_option( 'collection' );
    }

    public function is_available($package)
    {
        return parent::is_available($package) &&
            !empty($this->email) &&
            !empty($this->_password) &&
            !empty($this->nit);
    }

    public function init()
    {
        // Load the settings API.
        $this->init_form_fields(); // This is part of the settings API. Override the method to add your own settings.
        $this->init_settings(); // This is part of the settings API. Loads settings you previously init.
        // Save settings in admin if you have any defined.
        add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
    }

    public function init_form_fields()
    {
        $this->form_fields = include( dirname( __FILE__ ) . '/admin/settings.php' );
    }

    public function admin_options()
    {
        ?>
        <h3><?php echo $this->title; ?></h3>
        <p><?php echo $this->method_description; ?></p>
        <table class="form-table">
            <?php if (!empty($this->email) && !empty($this->_password)) ?>
                <?php Shipping_Shipping_Mipaquete_SMW::test_connection(); ?>
            <?php $this->generate_settings_html(); ?>
        </table>
        <?php
    }

    public function calculate_shipping($package = array())
    {
        global $woocommerce;
        $country = $package['destination']['country'];
        $state_destination = $package['destination']['state'];
        $city_destination  = $package['destination']['city'];
        $items = $woocommerce->cart->get_cart();

        if($country !== 'CO' || empty($state_destination))
            return apply_filters( 'woocommerce_shipping_' . $this->id . '_is_available', false, $package, $this );

        $name_state_destination = Shipping_Shipping_Mipaquete_SMW::name_destination($country, $state_destination);

        if (empty($name_state_destination))
            return apply_filters( 'woocommerce_shipping_' . $this->id . '_is_available', false, $package, $this );

        $address_destine = "$city_destination - $name_state_destination";

        if ($this->debug === 'yes')
            shipping_mipaquete_smw_smp()->log("origin: $this->city_sender address_destine: $address_destine");

        $cities = include dirname(__FILE__) . '/cities.php';

        $destine = array_search($address_destine, $cities);

        if(!$destine)
            $destine = array_search($address_destine, Shipping_Shipping_Mipaquete_SMW::clean_cities($cities));

        if ($this->debug === 'yes' && !$destine)
            shipping_mipaquete_smw_smp()->log("$address_destine  not found in cities Mipaquete");

        if(!$destine)
            return apply_filters( 'woocommerce_shipping_' . $this->id . '_is_available', false, $package, $this );

        //Mensajería de 1 a 5kg cuyas dimensiones del producto sea maximo de 40 x 30 x 15 . Es decir que cabe en una bolsa de mensajeria
        //Paquetería. Mas de 5 kg indiferente de las dimensiones

        $quantityItems = count($items);
        $initial_weight = 5;
        $count = 0;
        $quantity_packages = 0;
        $packing_weight_max = 150;
        $packing_length_max = 100;
        $packing_width_max = 100;
        $packing_height_max = 100;

        $calculate_dimensions_weight = Shipping_Shipping_Mipaquete_SMW::calculate_dimensions_weight($items);
        $length = $calculate_dimensions_weight['length'];
        $width = $calculate_dimensions_weight['width'];
        $height = $calculate_dimensions_weight['height'];
        $weight = $calculate_dimensions_weight['weight'];
        $total_valorization = $calculate_dimensions_weight['total_valorization'];

        if ($length > $packing_length_max || $width >  $packing_width_max || $height > $packing_height_max || $weight > $packing_weight_max){
            shipping_mipaquete_smw_smp()->log("Dimensions $length $width $height $weight exceeded, maxims: 100 x 100 x 100 and 150kg");
            return apply_filters( 'woocommerce_shipping_' . $this->id . '_is_available', false, $package, $this );
        }

        $weight = ceil($weight);

        $type_shipping = 1;

        if($weight <= 5 && $length <= 30 && $width <= 15 && $height <= 40)
            $type_shipping = 2;

        $shipping_attributes = array(
            'weight' => (int)$weight,
            'width' => (int)$width,
            'height' => (int)$height,
            'large' => (int)$length
        );

        if ($type_shipping === 2)
            $shipping_attributes = array(
                'weight' => (int)$weight,
            );

        $collection = array(
                'payment_type' => 1
        );

        if ($type_shipping == 2)
            $collection = array(
                    'special_service'=> (int)$this->collection,
                    'value_collection' => WC()->cart->get_subtotal(),
                    'payment_type' => 1
            );

        $others_params = array(
            'type' => (int)$type_shipping,
            'origin' => $this->city_sender,
            'destiny' => $destine,
            'declared_value' => $total_valorization,
            'quantity' => 1,
            'value_select' => (int)$this->value_select
        );

        $params_calculate_shipping = array_merge($shipping_attributes, $collection, $others_params);

        $response_calculate_shipping = Shipping_Shipping_Mipaquete_SMW::calculate_shipping_mipaquete($params_calculate_shipping);

        if ( empty( $response_calculate_shipping ) )
            return apply_filters( 'woocommerce_shipping_' . $this->id . '_is_available', false, $package, $this );

        $rate = array(
            'id'      => $this->id,
            'label'   => $this->title,
            'cost'    => $response_calculate_shipping->company->price,
            'package' => $package,
        );

        add_filter( 'woocommerce_cart_shipping_method_full_label', function($label) use($response_calculate_shipping) {
            $label .= "<br /><small>";
            $label .= "Estimación de entrega: ";
            $label .= $response_calculate_shipping->company->schedule;
            $label .= '</small>';
            return $label;
        }, 1);

        return $this->add_rate( $rate );

    }
}