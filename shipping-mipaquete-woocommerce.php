<?php
/**
 * Plugin Name: Shipping Mipaquete Woocommerce
 * Description: Shipping Mipaquete Woocommerce is available for Colombia
 * Version: 1.0.0
 * Author: Saul Morales Pacheco
 * Author URI: https://saulmoralespa.com
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * WC tested up to: 3.6
 * WC requires at least: 2.6
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if(!defined('SHIPPING_MIPAQUETE_SMW_SMP_VERSION')){
    define('SHIPPING_MIPAQUETE_SMW_SMP_VERSION', '1.0.0');
}

add_action( 'plugins_loaded', 'shipping_mipaquete_smw_smp_init', 0 );

function shipping_mipaquete_smw_smp_init(){
    if ( !shipping_mipaquete_smw_smp_requirements() )
        return;

    shipping_mipaquete_smw_smp()->run_mipaquete();
}

function shipping_mipaquete_smw_smp_notices( $notice ) {
    ?>
    <div class="error notice is-dismissible">
        <p><?php echo $notice; ?></p>
    </div>
    <?php
}

function shipping_mipaquete_smw_smp_requirements(){

    if ( version_compare( '7.1.0', PHP_VERSION, '>' ) ) {
        if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
            add_action(
                'admin_notices',
                function() {
                    shipping_mipaquete_smw_smp_notices( 'Shipping Mipaquete Woocommerce: Requiere la versión de php 7.1 o superior' );
                }
            );
        }
        return false;
    }

    if ( ! extension_loaded( 'curl' ) ) {
        if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
            add_action(
                'admin_notices',
                function() {
                    shipping_mipaquete_smw_smp_notices( 'Shipping Mipaquete Woocommerce: Requiere la extensión cURL se encuentre instalada' );
                }
            );
        }
        return false;
    }

    if ( !in_array(
        'woocommerce/woocommerce.php',
        apply_filters( 'active_plugins', get_option( 'active_plugins' ) ),
        true
    ) ) {
        if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
            add_action(
                'admin_notices',
                function() {
                    shipping_mipaquete_smw_smp_notices( 'Shipping Mipaquete Woocommerce: Requiere que se encuentre instalado y activo el plugin Woocommerce' );
                }
            );
        }
        return false;
    }

    if ( ! in_array(
        'departamentos-y-ciudades-de-colombia-para-woocommerce/departamentos-y-ciudades-de-colombia-para-woocommerce.php',
        apply_filters( 'active_plugins', get_option( 'active_plugins' ) ),
        true
    ) ) {
        if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
            add_action(
                'admin_notices',
                function() {
                    shipping_mipaquete_smw_smp_notices( 'Shipping Mipaquete Woocommerce: Requiere que se encuentre instalado y activo el plugin Departamentos y ciudades de Colombia para Woocommerce' );
                }
            );
        }
        return false;
    }

    return true;
}


function shipping_mipaquete_smw_smp(){
    static $plugin;
    if (!isset($plugin)){
        require_once('includes/class-shipping-mipaquete-smw-plugin.php');
        $plugin = new Shipping_Mipaquete_SMW_Plugin(__FILE__, SHIPPING_MIPAQUETE_SMW_SMP_VERSION);
    }
    return $plugin;

}

add_action( 'woocommerce_product_options_shipping', array('Shipping_Mipaquete_SMW_Plugin', 'add_custom_shipping_option_to_products'));