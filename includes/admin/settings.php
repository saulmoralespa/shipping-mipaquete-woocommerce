<?php

wc_enqueue_js( "
    jQuery( function( $ ) {
	
	let shipping_mipaquete_live_fields = '#woocommerce_shipping_mipaquete_wc_email, #woocommerce_shipping_mipaquete_wc_password';
	
	let shipping_mipaquete_sandbox_fields = '#woocommerce_shipping_mipaquete_wc_sandbox_email, #woocommerce_shipping_mipaquete_wc_sandbox_password';

	$( '#woocommerce_shipping_mipaquete_wc_environment' ).change(function(){

		$( shipping_mipaquete_sandbox_fields + ',' + shipping_mipaquete_live_fields ).closest( 'tr' ).hide();

		if ( '0' === $( this ).val() ) {
		    $( '#woocommerce_shipping_mipaquete_wc_credentials, #woocommerce_shipping_mipaquete_wc_credentials + p' ).show();    
		    
			$( '#woocommerce_shipping_mipaquete_wc_sandbox_credentials, #woocommerce_shipping_mipaquete_wc_sandbox_credentials + p' ).hide();
			$( shipping_mipaquete_live_fields ).closest( 'tr' ).show();
			
		}else{
		  $( '#woocommerce_shipping_mipaquete_wc_sandbox_credentials, #woocommerce_shipping_mipaquete_wc_sandbox_credentials + p' ).show();
		  
		  $( '#woocommerce_shipping_mipaquete_wc_credentials, #woocommerce_shipping_mipaquete_wc_credentials + p' ).hide(); 
		  $( shipping_mipaquete_sandbox_fields ).closest( 'tr' ).show();
		}
	}).change();
});	
");

return array(
    'enabled' => array(
        'title' => __('Activar/Desactivar'),
        'type' => 'checkbox',
        'label' => __('Activar Mipaquete'),
        'default' => 'no'
    ),
    'title'        => array(
        'title'       => __( 'Título método de envío' ),
        'type'        => 'text',
        'description' => __( 'Esto controla el título que el usuario ve durante el pago' ),
        'default'     => __( 'Mipaquete' ),
        'desc_tip'    => true
    ),
    'debug'        => array(
        'title'       => __( 'Depurador' ),
        'label'       => __( 'Habilitar el modo de desarrollador' ),
        'type'        => 'checkbox',
        'default'     => 'no',
        'description' => __( 'Enable debug mode to show debugging information on your cart/checkout.' ),
        'desc_tip' => true
    ),
    'environment' => array(
        'title' => __('Entorno'),
        'type'        => 'select',
        'class'       => 'wc-enhanced-select',
        'description' => __('Entorno de pruebas o producción'),
        'desc_tip' => true,
        'default' => '1',
        'options'     => array(
            '0'    => __( 'Producción'),
            '1' => __( 'Pruebas')
        ),
    ),
    'sandbox_credentials'          => array(
        'title'       => __( 'Credenciales de pruebas' ),
        'type'        => 'title',
        'description' => __( 'email y contraseña para el entorno de pruebas' )
    ),
    'sandbox_email' => array(
        'title' => __( 'Email' ),
        'type'  => 'email',
        'description' => __( 'Usuario asignado' ),
        'desc_tip' => true
    ),
    'sandbox_password' => array(
        'title' => __( 'Contraseña' ),
        'type'  => 'password',
        'description' => __( 'No confunda con la de seguimiento de despachos' ),
        'desc_tip' => true
    ),
    'credentials'          => array(
        'title'       => __( 'Credenciales de producción' ),
        'type'        => 'title',
        'description' => __( 'email y contraseña para el entorno de producción' )
    ),
    'email' => array(
        'title' => __( 'Email' ),
        'type'  => 'email',
        'description' => __( 'Usuario asignado' ),
        'desc_tip' => true
    ),
    'password' => array(
        'title' => __( 'Contraseña' ),
        'type'  => 'password',
        'description' => __( 'No confunda con la de seguimiento de despachos' ),
        'desc_tip' => true
    ),
    'nit' => array(
        'title' => __( 'NIT' ),
        'type'  => 'number',
        'description' => __( 'NIT vinculado a la cuenta de Mipaquete.com' ),
        'desc_tip' => true
    ),
    'city_sender' => array(
        'title' => __('Ciudad del remitente (donde se encuentra ubicada la tienda)'),
        'type'        => 'select',
        'class'       => 'wc-enhanced-select',
        'description' => __('Se recomienda selecionar ciudadades centrales'),
        'desc_tip' => true,
        'default' => true,
        'options'     => include dirname(__FILE__) . '/../cities.php'
    ),
    'value_select' => array(
        'title' => __('Criterio de selección de la transportadora)'),
        'type'        => 'select',
        'class'       => 'wc-enhanced-select',
        'description' => __('Criterio de selección de la transportadora por (precio, tiempo, servicio)'),
        'desc_tip' => true,
        'default' => '1',
        'options'     => array(
            '1' => __('Precio'),
            '2' => __('Tiempo'),
            '3' => __('Servicio')
        )
    ),
    'collection' => array(
        'title' => __('¿ Envíos con recaudo o pago contraentrega ? (Solo aplica para envíos considerados de tipo mensajeria)'),
        'type'        => 'select',
        'class'       => 'wc-enhanced-select',
        'description' => __('El Servicio espacial de cobro de racaudo solo esta habilitado para envíos que según Mipaquete considere de tipo mensajria'),
        'desc_tip' => true,
        'default' => '0',
        'options'     => array(
            '0' => __('No'),
            '2' => __('Sí'),
        )
    )
);