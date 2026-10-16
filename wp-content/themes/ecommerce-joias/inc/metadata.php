<?php
/** Descrições das páginas públicas; plugins de SEO continuam responsáveis quando ativos. */
defined( 'ABSPATH' ) || exit;
function ecommerce_joias_public_meta_description() {
	if ( defined( 'WPSEO_VERSION' ) || defined( 'RANK_MATH_VERSION' ) || defined( 'SEOPRESS_VERSION' ) || defined( 'AIOSEO_VERSION' )
		|| is_404() || is_search() || ( function_exists( 'is_shop' ) && ( is_shop() || is_cart() || is_checkout() || is_account_page() ) )
		|| is_page( array( 'manutencao', 'pedido-cancelado', 'pagamento-nao-concluido' ) ) ) {
		return;
	}
	$description = '';
	if ( is_front_page() ) {
		$description = get_theme_mod( 'ecommerce_joias_hero_description', 'Descubra peças escolhidas para iluminar seus momentos e expressar seu estilo. Conheça a coleção NC Semijoias.' );
	} elseif ( is_singular() ) {
		$post = get_queried_object();
		$description = $post->post_excerpt;
		if ( ! $description && preg_match( '/<p[^>]*>(.*?)<\\/p>/s', $post->post_content, $match ) ) {
			$description = $match[1];
		}
		if ( ! $description ) {
			$description = get_the_title() . ' — conheça os detalhes na ' . get_bloginfo( 'name' ) . '.';
		}
	} elseif ( is_tax() ) {
		$description = term_description() ?: single_term_title( '', false ) . ' — explore as peças da coleção ' . get_bloginfo( 'name' ) . '.';
	}
	$description = trim( preg_replace( '/\\s+/', ' ', wp_strip_all_tags( strip_shortcodes( $description ) ) ) );
	if ( $description ) {
		printf( '<meta name="description" content="%s">' . "\n", esc_attr( wp_html_excerpt( $description, 160, '…' ) ) );
	}
}
add_action( 'wp_head', 'ecommerce_joias_public_meta_description', 5 );
