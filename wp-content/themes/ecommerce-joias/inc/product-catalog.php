<?php
/** Integração da apresentação do catálogo com os hooks do tema pai. */

defined( 'ABSPATH' ) || exit;

/** Identifica fotos ausentes e produtos esgotados nos cartões do catálogo. */
function ecommerce_joias_catalog_product_notes( $product ) {
	if ( ! $product instanceof WC_Product ) {
		return;
	}
	if ( ! wp_attachment_is_image( $product->get_image_id() ) ) {
		echo '<span class="nc-featured-product__photo-note">' . esc_html__( 'Foto indisponível', 'ecommerce-joias' ) . '</span>';
	}
	if ( ! $product->is_in_stock() ) {
		echo '<span class="nc-featured-product__badge">' . esc_html__( 'Esgotado', 'ecommerce-joias' ) . '</span>';
	}
}
add_action( 'orchid_store_product_thumbnail', 'ecommerce_joias_catalog_product_notes', 20 );

/** Aponta os menus para cada apresentação e identifica a seleção atual. */
function ecommerce_joias_catalog_menu_link( $attributes ) {
	if ( empty( $attributes['href'] ) ) {
		return $attributes;
	}
	$view = ecommerce_joias_catalog_menu_view( $attributes['href'] );
	if ( null !== $view ) {
		$attributes['href'] = ecommerce_joias_catalog_view_url( $view );
		if ( is_shop() && $view === ecommerce_joias_catalog_view() ) {
			$attributes['aria-current'] = 'page';
		} else {
			unset( $attributes['aria-current'] );
		}
	}
	return $attributes;
}
add_filter( 'nav_menu_link_attributes', 'ecommerce_joias_catalog_menu_link' );
