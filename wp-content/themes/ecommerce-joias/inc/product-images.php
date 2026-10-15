<?php
/** Apresentação de produtos cuja imagem substituta foi removida da biblioteca. */

defined( 'ABSPATH' ) || exit;

/** Usa o recurso nativo do WooCommerce quando o anexo configurado não existe. */
function ecommerce_joias_product_placeholder_src( $src ) {
	$attachment_id = absint( get_option( 'woocommerce_placeholder_image', 0 ) );
	if ( $attachment_id && function_exists( 'WC' ) ) {
		$file = get_attached_file( $attachment_id );
		if ( ! $file || ! is_file( $file ) ) {
			return WC()->plugin_url() . '/assets/images/placeholder.webp';
		}
	}
	return $src;
}
add_filter( 'woocommerce_placeholder_img_src', 'ecommerce_joias_product_placeholder_src' );
