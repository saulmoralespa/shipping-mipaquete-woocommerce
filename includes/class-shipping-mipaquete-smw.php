<?php

use Mipaquete\Client;

class Shipping_Shipping_Mipaquete_SMW extends WC_Shipping_Method_Shipping_Mipaquete_SMW
{
    public function __construct($instance_id = 0)
    {
        parent::__construct($instance_id);

        $this->mipaquete = new Client($this->email, $this->_password);
        $this->mipaquete->sandboxMode($this->isTest);
    }

    public static function test_connection()
    {
        $instance = new self();

        try{
            $instance->mipaquete->getToken();
        }catch (\Exception $exception){
            shipping_mipaquete_smw_smp_notices($exception->getMessage());
        }
    }

    public static function calculate_shipping_mipaquete(array $params){

        $instance = new self();

        $data = array();

        try{
            $data = $instance->mipaquete->calculateSending($params);
        }catch (\Exception $exception){
            shipping_mipaquete_smw_smp()->log($exception->getMessage());
        }

        return $data;
    }

    public static function sendings_type($order_id, $old_status, $new_status, $order)
    {
        $instance = new self();

        if( !$order->has_shipping_method($instance->id))
            return;

        $sending_type_id = get_post_meta($order_id, 'sending_type_id', true);

        if (empty($sending_type_id) && $new_status === 'processing'){
            $sending = $instance->sending($order);
            if ($sending == new stdClass())
                return;

            $sending_id = $sending->result->sending->_id;

            update_post_meta($order_id, 'sending_type_id', $sending_id);
            $order->add_order_note(sprintf('Envío Mipaquete.com %s generado con éxito', $sending_id));
        }
    }

    public function sending(WC_Order $order)
    {
        $instance = new self();

        $packing_weight_max = 150;
        $packing_length_max = 100;
        $packing_width_max = 100;
        $packing_height_max = 100;

        $calculate_dimensions_weight = Shipping_Shipping_Mipaquete_SMW::calculate_dimensions_weight($order->get_items());

        $length = $calculate_dimensions_weight['length'];
        $width = $calculate_dimensions_weight['width'];
        $height = $calculate_dimensions_weight['height'];
        $weight = $calculate_dimensions_weight['weight'];
        $total_valorization = $calculate_dimensions_weight['total_valorization'];


        $weight = ceil($weight);

        $type_shipping = 1;

        if($weight <= 5 && $length <= 30 && $width <= 15 && $height <= 40)
            $type_shipping = 2;

        $state = $order->get_shipping_state() ? $order->get_shipping_state() : $order->get_billing_state();
        $city = $order->get_shipping_city() ? $order->get_shipping_city() : $order->get_billing_city();
        $receiver_name = $order->get_shipping_first_name() ? $order->get_shipping_first_name() .
            " " . $order->get_shipping_last_name() : $order->get_billing_first_name() .
            " " . $order->get_billing_last_name();
        $receiver_phone = $order->get_billing_phone();
        $receiver_email = $order->get_billing_email();
        $receiver_address = $order->get_shipping_address_1() ? $order->get_shipping_address_1() .
            " " . $order->get_shipping_address_2() : $order->get_billing_address_1() .
            " " . $order->get_billing_address_2();
        $sender_address = get_option( 'woocommerce_store_address' ) .
            " " .  get_option( 'woocommerce_store_address_2' ) .
            " " . get_option( 'woocommerce_store_city' );
        $country = 'CO';

        $name_state_destination = Shipping_Shipping_Mipaquete_SMW::name_destination($country, $state);
        $address_destine = "$city - $name_state_destination";

        $cities = include dirname(__FILE__) . '/cities.php';
        $destine = array_search($address_destine, $cities);

        if(!$destine)
            $destine = array_search($address_destine, Shipping_Shipping_Mipaquete_SMW::clean_cities($cities));

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
                'value_collection' => (int)$order->get_total(),
                'payment_type' => $this->collection == 2 ? 5 : 1
            );


        $sender = array(
            'sender' => array(
                'name' => get_bloginfo('name'),
                'surname' => get_bloginfo('name'),
                'phone' => '',
                'cell_phone' => '',
                'email' => '',
                'collection_address' => $sender_address,
                'nit' => $this->nit
            )
        );

        $receiver = array(
            'receiver' => array(
                'name' => $receiver_name,
                'surname' => $receiver_name,
                'phone' => 3004938245,
                'cell_phone' => $receiver_phone,
                'email' => $receiver_email,
                'destination_address' => $receiver_address,
            )
        );

        $collection_information = array(
            'collection_information' => array(
                'bank' => get_post_meta($order->get_id(),'_billing_bank_name', true),
                'type_account' => get_post_meta($order->get_id(),'_billing_bank_account_type', true),
                'number_account' => (int)get_post_meta($order->get_id(),'_billing_bank_account_number', true),
                'name_beneficiary' => get_post_meta($order->get_id(),'_billing_bank_beneficiary_name', true),
                'number_beneficiary' => (int)get_post_meta($order->get_id(),'_billing_bank_account_number', true)
            )
        );

        $others_params = array(
            'type' => (int)$type_shipping,
            'origin' => $this->city_sender,
            'destiny' => $destine,
            'declared_value' => (int)$total_valorization,
            'quantity' => 1,
            'alternative' => (int)$this->value_select,
            'comments' => ''
        );

        $params = array_merge($shipping_attributes, $collection, $sender, $receiver, $others_params);

        if ($type_shipping == 2)
            $params = array_merge($params, $collection_information);

        $data = new stdClass;

        try{
            $data = $instance->mipaquete->sendingType($params);
        }catch (\Exception $exception){
            shipping_mipaquete_smw_smp()->log($exception->getMessage());
            shipping_mipaquete_smw_smp()->log($params);
        }

        return $data;
    }

    public static function calculate_dimensions_weight($items)
    {
        $height = 0;
        $length = 0;
        $weight = 0;
        $width = 0;
        $packing_weight_max = 150;
        $packing_length_max = 100;
        $packing_width_max = 100;
        $packing_height_max = 100;
        $total_valorization = 0;

        foreach ( $items as $item => $values ) {
            $_product_id = $item['product_id'] ?? $values->get_product_id();
            $_product = wc_get_product( $_product_id );

            if ( !$_product->get_weight() || !$_product->get_length()
                || !$_product->get_width() || !$_product->get_height() )
                break;

            if ( ceil($_product->get_weight()) > $packing_weight_max || $_product->get_length() > $packing_length_max
                || $_product->get_width() > $packing_width_max || $_product->get_height() > $packing_height_max ){
                shipping_mipaquete_smw_smp()->log($_product->get_name() . " dimensions or weight for exceeded, maxims: 100 x 100 x 100 and 150kg");
                break;
            }

            $custom_price_product = get_post_meta($_product_id, '_shipping_custom_price_product_smp', true);
            $total_valorization += $custom_price_product ? $custom_price_product : $_product->get_price();

            $quantity = $values['quantity'];

            $total_valorization = $total_valorization * $quantity;

            $height += $_product->get_height() * $quantity;
            $length = $_product->get_length() > $length ? $_product->get_length() : $length;
            $weight =+ $weight + ($_product->get_weight() * $quantity);
            $width =  $_product->get_width() > $width ? $_product->get_width() : $width;
        }

        return array(
            'height' => $height,
            'length' => $length,
            'weight' => $weight,
            'width' =>  $width,
            'total_valorization' => $total_valorization
        );
    }

    public static  function name_destination($country, $state_destination)
    {
        $countries_obj = new WC_Countries();
        $country_states_array = $countries_obj->get_states();

        $name_state_destination = '';

        if(!isset($country_states_array[$country][$state_destination]))
            return $name_state_destination;

        $name_state_destination = $country_states_array[$country][$state_destination];
        $name_state_destination = self::clean_string($name_state_destination);
        return self::short_name_location($name_state_destination);
    }

    public static function short_name_location($name_location)
    {
        if ( 'Valle del Cauca' === $name_location )
            $name_location =  'Valle';
        return $name_location;
    }

    public static function clean_string($string)
    {
        $not_permitted = array ("á","é","í","ó","ú","Á","É","Í",
            "Ó","Ú","ñ","À","Ã","Ì","Ò","Ù","Ã™","Ã ","Ã¨","Ã¬",
            "Ã²","Ã¹","ç","Ç","Ã¢","ê","Ã®","Ã´","Ã»","Ã‚","ÃŠ",
            "ÃŽ","Ã”","Ã›","ü","Ã¶","Ã–","Ã¯","Ã¤","«","Ò","Ã",
            "Ã„","Ã‹");
        $permitted = array ("a","e","i","o","u","A","E","I","O",
            "U","n","N","A","E","I","O","U","a","e","i","o","u",
            "c","C","a","e","i","o","u","A","E","I","O","U","u",
            "o","O","i","a","e","U","I","A","E");
        $text = str_replace($not_permitted, $permitted, $string);
        return $text;
    }

    public static function clean_cities($cities)
    {
        foreach ($cities as $key => $value){
            $cities[$key] = self::clean_string($value);
        }

        return $cities;
    }
}