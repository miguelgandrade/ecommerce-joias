<?php
/** Correções semânticas dos controles de quantidade herdados. */
defined( 'ABSPATH' ) || exit;

function ecommerce_joias_quantity_button_setup() {
	if ( has_action( 'woocommerce_before_add_to_cart_quantity', 'orchid_store_quantity_minus' ) ) {
		remove_action( 'woocommerce_before_add_to_cart_quantity', 'orchid_store_quantity_minus' );
		add_action( 'woocommerce_before_add_to_cart_quantity', 'ecommerce_joias_quantity_minus' );
	}
	if ( has_action( 'woocommerce_after_add_to_cart_quantity', 'orchid_store_quantity_plus' ) ) {
		remove_action( 'woocommerce_after_add_to_cart_quantity', 'orchid_store_quantity_plus' );
		add_action( 'woocommerce_after_add_to_cart_quantity', 'ecommerce_joias_quantity_plus' );
	}
}
add_action( 'after_setup_theme', 'ecommerce_joias_quantity_button_setup', 30 );

function ecommerce_joias_quantity_minus() {
	global $product;
	if ( ! $product || $product->is_sold_individually() || 1 === $product->get_stock_quantity() ) {
		return;
	}
	echo '<div class="os-quantity-wrapper"><button type="button" class="woo-quantity-btn woo-quantity-minus" aria-label="Diminuir quantidade"><i class="bx bx-minus" aria-hidden="true"></i></button>';
}
function ecommerce_joias_quantity_plus() {
	global $product;
	if ( ! $product || $product->is_sold_individually() || 1 === $product->get_stock_quantity() ) {
		return;
	}
	echo '<button type="button" class="woo-quantity-btn woo-quantity-plus" aria-label="Aumentar quantidade"><i class="bx bx-plus" aria-hidden="true"></i></button></div>';
}
