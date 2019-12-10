<?php


class Shipping_Mipaquete_SMW_Plugin
{
    /**
     * Filepath of main plugin file.
     *
     * @var string
     */
    public $file;
    /**
     * Plugin version.
     *
     * @var string
     */
    public $version;
    /**
     * Absolute plugin path.
     *
     * @var string
     */
    public $plugin_path;
    /**
     * Absolute plugin URL.
     *
     * @var string
     */
    public $plugin_url;
    /**
     * Absolute path to plugin includes dir.
     *
     * @var string
     */
    public $includes_path;
    /**
     * Absolute path to plugin lib dir
     *
     * @var string
     */
    public $lib_path;
    /**
     * @var bool
     */
    private $_bootstrapped = false;


    public function __construct($file, $version)
    {
        $this->file = $file;
        $this->version = $version;

        $this->plugin_path   = trailingslashit( plugin_dir_path( $this->file ) );
        $this->plugin_url    = trailingslashit( plugin_dir_url( $this->file ) );
        $this->includes_path = $this->plugin_path . trailingslashit( 'includes' );
        $this->lib_path = $this->plugin_path . trailingslashit( 'lib' );
    }

    public function run_mipaquete()
    {
        try{
            if ($this->_bootstrapped){
                throw new Exception( 'Shipping Mipaquete Woocommerce can only be called once');
            }
            $this->_run();
            $this->_bootstrapped = true;
        }catch (Exception $e){
            if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
                add_action('admin_notices', function() use($e) {
                    shipping_mipaquete_smw_smp_notices($e->getMessage());
                });
            }
        }
    }

    protected function _run()
    {
        if (!class_exists('\Mipaquete\Client'))
            require_once ($this->lib_path . 'vendor/autoload.php');

        require_once ($this->includes_path . 'class-method-shipping-mipaquete-smw.php');
        require_once ($this->includes_path . 'class-shipping-mipaquete-smw.php');

        add_filter( 'plugin_action_links_' . plugin_basename( $this->file), array( $this, 'plugin_action_links' ) );
        add_filter( 'woocommerce_shipping_methods', array( $this, 'shipping_mipaquete_wc_add_method') );
        add_filter( 'woocommerce_billing_fields', array($this, 'custom_woocommerce_billing_fields'), 20);
        add_action( 'woocommerce_process_product_meta', array($this, 'save_custom_shipping_option_to_products') );
        add_action( 'woocommerce_order_status_changed', array('Shipping_Shipping_Mipaquete_SMW', 'sendings_type'), 20, 4 );
    }

    public function plugin_action_links($links)
    {
        $plugin_links = array();
        $plugin_links[] = '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=shipping&section=shipping_mipaquete_wc') . '">' . 'Configuraciones' . '</a>';
        $plugin_links[] = '<a target="_blank" href="https://shop.saulmoralespa.com/shipping-mipaquete-woocommerce/">' . 'Documentación' . '</a>';
        return array_merge( $plugin_links, $links );
    }

    public function shipping_mipaquete_wc_add_method($methods)
    {
        $methods['shipping_mipaquete_wc'] = 'WC_Shipping_Method_Shipping_Mipaquete_SMW';
        return $methods;
    }

    public function log($message)
    {
        if (is_array($message) || is_object($message))
            $message = print_r($message, true);
        $logger = new WC_Logger();
        $logger->add('shipping-mipaquete', $message);
    }

    public static function add_custom_shipping_option_to_products()
    {
        global $post;

        if( ! get_post_meta( $post->ID, '_shipping_custom_price_product_smp', true )) {
            woocommerce_wp_text_input( [
                'id'          => '_shipping_custom_price_product_smp',
                'label'       => __( 'Valor declarado del producto'),
                'placeholder' => 'Valor declarado del envío',
                'desc_tip'    => true,
                'description' => __( 'El valor que desea declarar para el envío'),
                'value'       => get_post_meta( $post->ID, '_shipping_custom_price_product_smp', true )
            ] );
        }
    }

    public function save_custom_shipping_option_to_products($post_id)
    {
        $custom_price_product = sanitize_text_field($_POST['_shipping_custom_price_product_smp']);
        if( isset( $custom_price_product ) )
            update_post_meta( $post_id, '_shipping_custom_price_product_smp', esc_attr( $custom_price_product ) );
    }

    public function custom_woocommerce_billing_fields($fields)
    {
        global $woocommerce;
        $settings = get_option('woocommerce_shipping_mipaquete_wc_settings' );
        $items = $woocommerce->cart->get_cart();
        $calculate_dimensions_weight = Shipping_Shipping_Mipaquete_SMW::calculate_dimensions_weight($items);
        $length = $calculate_dimensions_weight['length'];
        $width = $calculate_dimensions_weight['width'];
        $height = $calculate_dimensions_weight['height'];
        $weight = $calculate_dimensions_weight['weight'];

        if ($settings['collection'] === '2' && $weight <= 5 && $length <= 30 && $width <= 15 && $height <= 40){
            $fields['billing_bank_name'] = array(
                'label' => __('Nombre del banco'),
                'placeholder' => _x('Bancolombia', 'placeholder'),
                'required' => true,
                'clear' => false,
                'type' => 'text',
                'class' => array('my-css')
            );
            $fields['billing_bank_account_type'] = array(
                'label' => __('Tipo de cuenta bancaria'),
                'required' => true,
                'clear' => false,
                'type' => 'select',
                'options'     => array(
                    'A' => __('Cuenta de ahorros'),
                    'C' => __('Cuenta corriente')
                )
            );
            $fields['billing_bank_account_number'] = array(
                'label' => __('Número de cuenta bancaria'),
                'placeholder' => _x('3004938484', 'placeholder'),
                'required' => true,
                'clear' => false,
                'type' => 'number'
            );
            $fields['billing_bank_beneficiary_name'] = array(
                'label' => __('Nombre beneficiario de la cuenta bancaria'),
                'placeholder' => _x('Escriba nombre del beneficiario', 'placeholder'),
                'required' => true,
                'clear' => false,
                'type' => 'text'
            );
        }

        return $fields;
    }
}