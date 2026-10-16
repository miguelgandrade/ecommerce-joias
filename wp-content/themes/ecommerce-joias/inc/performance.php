<?php
/** Otimizações de apresentação; preserva os recursos funcionais de compra. */
defined( 'ABSPATH' ) || exit;

/** Identifica templates completos que não usam o layout do tema pai. */
function ecommerce_joias_uses_own_layout() {
	return is_front_page() || is_404() || is_page_template( 'page-ajuda.php' )
		|| is_page_template( 'page-sobre-a-loja.php' ) || is_page( array( 'sobre-a-loja', 'produtos' ) )
		|| ( is_search() && 'product' !== get_query_var( 'post_type' ) );
}

/** Fonte local com os mesmos pesos e caracteres, sem conexão ao Google Fonts. */
function ecommerce_joias_local_fonts( $src, $handle ) {
	if ( 'orchid-store-fonts' === $handle ) {
		return get_stylesheet_directory_uri() . '/assets/fonts/inter.css?ver=' . filemtime( get_stylesheet_directory() . '/assets/fonts/inter.css' );
	}
	return $src;
}
add_filter( 'style_loader_src', 'ecommerce_joias_local_fonts', 10, 2 );

/** Remove pacotes legados somente dos templates próprios sem blocos de compra. */
function ecommerce_joias_trim_custom_layout_assets() {
	if ( ! ecommerce_joias_uses_own_layout() ) {
		return;
	}
	$content = (string) get_post_field( 'post_content', get_queried_object_id() );
	if ( str_contains( $content, 'wp:woocommerce/' ) || preg_match( '/\\[(?:woocommerce[_a-z]*|product[_a-z]*|add_to_cart[_a-z]*|sale_products|featured_products|recent_products|best_selling_products|top_rated_products)\\b/', $content ) ) {
		return;
	}
	foreach ( array( 'orchid-store-main-style', 'orchid-store-main-style-rtl', 'orchid-store-style', 'orchid-store-fonts', 'orchid-store-fontawesome', 'orchid-store-boxicons', 'woocommerce-general', 'woocommerce-layout', 'woocommerce-smallscreen' ) as $handle ) {
		wp_dequeue_style( $handle );
	}
	// O CSS próprio não pode recolocar o pai na fila por uma dependência.
	$styles = wp_styles();
	$styles->registered['ecommerce-joias-style']->deps = array();
	wp_dequeue_script( 'orchid-store-bundle' );
	$path = get_stylesheet_directory() . '/assets/js/back-to-top.js';
	wp_enqueue_script( 'ecommerce-joias-back-to-top', get_stylesheet_directory_uri() . '/assets/js/back-to-top.js', array(), (string) filemtime( $path ), true );
	if ( ! is_front_page() ) {
		foreach ( array( 'wc-add-to-cart', 'woocommerce', 'wc-js-cookie' ) as $handle ) {
			wp_dequeue_script( $handle );
		}
	}
}
add_action( 'wp_enqueue_scripts', 'ecommerce_joias_trim_custom_layout_assets', 110 );

/** Estratégia nativa mantém a ordem e respeita dependências e scripts inline. */
function ecommerce_joias_defer_catalog_scripts() {
	if ( ! function_exists( 'is_woocommerce' ) || ! is_woocommerce() || is_cart() || is_checkout() || is_account_page() ) {
		return;
	}
	foreach ( array( 'jquery-core', 'jquery-migrate', 'orchid-store-bundle', 'wc-add-to-cart' ) as $handle ) {
		wp_script_add_data( $handle, 'strategy', 'defer' );
	}
}
add_action( 'wp_enqueue_scripts', 'ecommerce_joias_defer_catalog_scripts', 120 );

/** O logo mede até 72 px de altura, e não a largura inteira da janela. */
function ecommerce_joias_responsive_logo( $attr, $attachment ) {
	if ( ! str_contains( $attr['class'] ?? '', 'custom-logo' ) ) {
		return $attr;
	}
	$meta = wp_get_attachment_metadata( $attachment->ID );
	if ( ! empty( $meta['height'] ) && ! empty( $meta['width'] ) ) {
		$attr['sizes'] = (string) (int) ceil( 72 * $meta['width'] / $meta['height'] ) . 'px';
	}
	return $attr;
}
add_filter( 'wp_get_attachment_image_attributes', 'ecommerce_joias_responsive_logo', 10, 2 );

/** Ícones essenciais vetoriais dispensam carregar uma fonte no cabeçalho. */
function ecommerce_joias_icon( $name ) {
	$paths = array(
		'search' => '<circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 5 5"/>',
		'user' => '<circle cx="12" cy="8" r="4"/><path d="M4 21v-2a8 8 0 0 1 16 0v2"/>',
		'cart' => '<path d="M2 3h3l3 13h11l3-9H6"/><circle cx="9" cy="21" r="1"/><circle cx="19" cy="21" r="1"/>',
		'up' => '<path d="m6 15 6-6 6 6"/>',
	);
	if ( isset( $paths[ $name ] ) ) {
		return '<svg class="nc-icon" viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . $paths[ $name ] . '</svg>';
	}
	return '';
}
function ecommerce_joias_own_layout_class( $classes ) {
	if ( ecommerce_joias_uses_own_layout() ) { $classes[] = 'nc-own-layout'; }
	return $classes;
}
add_filter( 'body_class', 'ecommerce_joias_own_layout_class' );

/** Prioriza somente a primeira miniatura da vitrine, não todas as fotos. */
function ecommerce_joias_catalog_lcp_image( $attr ) {
	if ( function_exists( 'is_product' ) && is_product() && str_contains( $attr['class'] ?? '', 'wp-post-image' ) ) {
		$attr['loading'] = 'eager';
		$attr['fetchpriority'] = 'high';
		return $attr;
	}
	if ( ! function_exists( 'is_shop' ) || ( ! is_shop() && ! is_product_taxonomy() ) || ! str_contains( $attr['class'] ?? '', 'attachment-woocommerce_thumbnail' ) || wc_get_loop_prop( 'loop', 0 ) > 0 ) {
		return $attr;
	}
	$attr['loading'] = 'eager';
	$attr['fetchpriority'] = 'high';
	return $attr;
}
add_filter( 'wp_get_attachment_image_attributes', 'ecommerce_joias_catalog_lcp_image', 15 );

/** Pedir o link por e-mail não exige avaliar a força de uma senha nova. */
function ecommerce_joias_password_meter_when_needed() {
	if ( function_exists( 'is_lost_password_page' ) && is_lost_password_page() && empty( $_GET['show-reset-form'] ) ) {
		// Mantém o medidor no formulário real de redefinição e em editar conta.
		wp_dequeue_script( 'wc-password-strength-meter' );
	}
}
add_action( 'wp_enqueue_scripts', 'ecommerce_joias_password_meter_when_needed', 130 );
