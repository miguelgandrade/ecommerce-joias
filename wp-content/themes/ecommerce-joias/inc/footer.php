<?php
/** Configuração de conteúdo complementar do rodapé. */
defined( 'ABSPATH' ) || exit;

function ecommerce_joias_footer_setup() {
	register_nav_menu( 'nc-footer', __( 'Navegação do rodapé', 'ecommerce-joias' ) );
}
add_action( 'after_setup_theme', 'ecommerce_joias_footer_setup', 20 );

function ecommerce_joias_footer_customize( $wp_customize ) {
	foreach ( array(
		'whatsapp' => __( 'WhatsApp (país + DDD + número)', 'ecommerce-joias' ),
		'hours'    => __( 'Horário de atendimento', 'ecommerce-joias' ),
	) as $key => $label ) {
		$id = 'ecommerce_joias_footer_' . $key;
		$wp_customize->add_setting( $id, array( 'default' => '', 'sanitize_callback' => 'sanitize_text_field' ) );
		$wp_customize->add_control( $id, array( 'label' => $label, 'section' => 'ecommerce_joias_footer', 'type' => 'text' ) );
	}
	foreach ( array(
		'delivery' => __( 'Página de entrega e frete', 'ecommerce-joias' ),
		'returns'  => __( 'Página de trocas e devoluções', 'ecommerce-joias' ),
		'care'     => __( 'Página de cuidados com as peças', 'ecommerce-joias' ),
		'sizes'    => __( 'Página do guia de tamanhos', 'ecommerce-joias' ),
		'privacy'  => __( 'Página de privacidade', 'ecommerce-joias' ),
		'terms'    => __( 'Página de termos de uso', 'ecommerce-joias' ),
	) as $key => $label ) {
		$id = 'ecommerce_joias_footer_page_' . $key;
		$wp_customize->add_setting( $id, array( 'default' => 0, 'sanitize_callback' => 'absint' ) );
		$wp_customize->add_control( $id, array( 'label' => $label, 'description' => __( 'O link aparece somente se a página estiver publicada e sem senha.', 'ecommerce-joias' ), 'section' => 'ecommerce_joias_footer', 'type' => 'dropdown-pages' ) );
	}
}
add_action( 'customize_register', 'ecommerce_joias_footer_customize', 20 );

/** Somente destinos públicos podem aparecer no rodapé. */
function ecommerce_joias_footer_page_url( $key ) {
	$id = absint( get_theme_mod( 'ecommerce_joias_footer_page_' . $key, 0 ) );
	if ( ! $id && 'privacy' === $key ) {
		$id = absint( get_option( 'wp_page_for_privacy_policy' ) );
	}
	if ( ! $id && 'terms' === $key && function_exists( 'wc_get_page_id' ) ) {
		$id = wc_get_page_id( 'terms' );
	}
	$page = $id > 0 ? get_post( $id ) : null;
	return $page && 'page' === $page->post_type && 'publish' === $page->post_status && ! $page->post_password ? get_permalink( $page ) : '';
}
