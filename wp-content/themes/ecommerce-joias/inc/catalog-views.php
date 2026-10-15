<?php
/** Apresentações próprias de Novidades e Mais vendidos sobre o catálogo nativo. */

defined( 'ABSPATH' ) || exit;

function ecommerce_joias_catalog_query_vars( $vars ) {
	$vars[] = 'nc_vitrine';
	return $vars;
}
add_filter( 'query_vars', 'ecommerce_joias_catalog_query_vars' );

/** Aceita somente as seleções conhecidas; a ordenação pode mudar sem trocar a página. */
function ecommerce_joias_catalog_view() {
	$view = get_query_var( 'nc_vitrine', '' );
	return in_array( $view, array( 'novidades', 'mais-vendidos' ), true ) ? $view : 'colecao';
}

function ecommerce_joias_catalog_view_url( $view = 'colecao' ) {
	$url = get_post_type_archive_link( 'product' );
	return 'colecao' === $view ? $url : add_query_arg( 'nc_vitrine', $view, $url );
}

/** Identifica links antigos da loja e links novos sem depender do texto do menu. */
function ecommerce_joias_catalog_menu_view( $href ) {
	if ( ! function_exists( 'wc_get_page_id' ) ) {
		return null;
	}
	$url = wp_parse_url( $href );
	$archive = wp_parse_url( get_post_type_archive_link( 'product' ) );
	if ( ! $url || ! $archive || ( isset( $url['host'] ) && $url['host'] !== ( $archive['host'] ?? '' ) ) ) {
		return null;
	}
	parse_str( $url['query'] ?? '', $query );
	$is_shop_page = isset( $query['page_id'] ) && absint( $query['page_id'] ) === wc_get_page_id( 'shop' );
	$is_archive = ( $url['path'] ?? '/' ) === ( $archive['path'] ?? '/' ) && ( isset( $query['post_type'] ) ? 'product' === $query['post_type'] : ! isset( $archive['query'] ) );
	if ( ! $is_shop_page && ! $is_archive ) {
		return null;
	}
	if ( isset( $query['nc_vitrine'] ) && in_array( $query['nc_vitrine'], array( 'novidades', 'mais-vendidos' ), true ) ) {
		return $query['nc_vitrine'];
	}
	return array( 'date' => 'novidades', 'popularity' => 'mais-vendidos' )[ $query['orderby'] ?? '' ] ?? 'colecao';
}

function ecommerce_joias_catalog_view_order( $order ) {
	if ( ! is_shop() ) {
		return $order;
	}
	return array( 'novidades' => 'date', 'mais-vendidos' => 'popularity' )[ ecommerce_joias_catalog_view() ] ?? $order;
}
add_filter( 'woocommerce_default_catalog_orderby', 'ecommerce_joias_catalog_view_order' );

/** O ranking considera apenas produtos com vendas; não modifica dados da loja. */
function ecommerce_joias_catalog_best_sellers_query( $query ) {
	if ( ! is_shop() || 'mais-vendidos' !== ecommerce_joias_catalog_view() ) {
		return;
	}
	$meta_query = $query->get( 'meta_query' );
	$query->set( 'meta_query', array(
		'relation' => 'AND',
		is_array( $meta_query ) ? $meta_query : array(),
		array( 'key' => 'total_sales', 'value' => 0, 'compare' => '>', 'type' => 'NUMERIC' ),
	) );
}
add_action( 'woocommerce_product_query', 'ecommerce_joias_catalog_best_sellers_query' );

/** Dados de apresentação compartilhados pelo título da página e pela abertura. */
function ecommerce_joias_catalog_view_content() {
	$views = array(
		'colecao' => array( 'eyebrow' => 'NC Semijoias', 'title' => 'Nossa coleção', 'description' => 'Encontre os detalhes que combinam com você e escolha sua próxima joia.' ),
		'novidades' => array( 'eyebrow' => 'Acabou de chegar', 'title' => 'Novidades', 'description' => 'Conheça as peças mais recentes da nossa seleção.' ),
		'mais-vendidos' => array( 'eyebrow' => 'Escolhidos por vocês', 'title' => 'Mais vendidos', 'description' => 'Descubra os favoritos que conquistaram espaço nas histórias de nossas clientes.' ),
	);
	return $views[ ecommerce_joias_catalog_view() ];
}

function ecommerce_joias_catalog_body_class( $classes ) {
	if ( function_exists( 'is_shop' ) && is_shop() ) {
		$classes[] = 'nc-catalog-view--' . ecommerce_joias_catalog_view();
	}
	return $classes;
}
add_filter( 'body_class', 'ecommerce_joias_catalog_body_class' );

function ecommerce_joias_catalog_view_title( $title ) {
	return is_shop() ? ecommerce_joias_catalog_view_content()['title'] : $title;
}
add_filter( 'woocommerce_page_title', 'ecommerce_joias_catalog_view_title' );

function ecommerce_joias_catalog_document_title( $parts ) {
	if ( function_exists( 'is_shop' ) && is_shop() ) {
		$parts['title'] = ecommerce_joias_catalog_view_content()['title'];
	}
	return $parts;
}
add_filter( 'document_title_parts', 'ecommerce_joias_catalog_document_title' );

function ecommerce_joias_catalog_view_intro() {
	if ( ! is_shop() ) {
		return;
	}
	$view = ecommerce_joias_catalog_view();
	$content = ecommerce_joias_catalog_view_content();
	?>
	<section class="nc-catalog-intro nc-catalog-intro--<?php echo esc_attr( $view ); ?>" aria-labelledby="nc-catalog-title">
		<div class="nc-catalog-intro__copy">
			<p class="nc-section-heading__eyebrow"><?php echo esc_html( $content['eyebrow'] ); ?></p>
			<h1 id="nc-catalog-title"><?php echo esc_html( $content['title'] ); ?></h1>
			<p class="nc-catalog-intro__description"><?php echo esc_html( $content['description'] ); ?></p>
		</div>
	</section>
	<?php
}
add_action( 'woocommerce_archive_description', 'ecommerce_joias_catalog_view_intro', 5 );

/** Cria sinais visuais próprios nos cartões de cada vitrine. */
function ecommerce_joias_catalog_card_marker() {
	if ( ! is_shop() ) {
		return;
	}
	$view = ecommerce_joias_catalog_view();
	if ( 'novidades' === $view ) {
		echo '<span class="nc-catalog-card-marker">' . esc_html__( 'Novo', 'ecommerce-joias' ) . '</span>';
	} elseif ( 'mais-vendidos' === $view ) {
		global $woocommerce_loop;
		$position = isset( $woocommerce_loop['loop'] ) ? absint( $woocommerce_loop['loop'] ) : 0;
		printf( '<span class="nc-catalog-card-marker nc-catalog-card-marker--rank">#%s</span>', esc_html( str_pad( (string) ( $position + 1 ), 2, '0', STR_PAD_LEFT ) ) );
	}
}
add_action( 'orchid_store_product_thumbnail', 'ecommerce_joias_catalog_card_marker', 5 );

function ecommerce_joias_catalog_empty_selection() {
	if ( ! is_shop() || 'mais-vendidos' !== ecommerce_joias_catalog_view() ) {
		return;
	}
	remove_action( 'woocommerce_no_products_found', 'wc_no_products_found', 10 );
	?>
	<section class="nc-catalog-empty">
		<span aria-hidden="true">✦</span>
		<h2><?php esc_html_e( 'Sua próxima descoberta está na coleção', 'ecommerce-joias' ); ?></h2>
		<p><?php esc_html_e( 'Esta seleção ainda não tem produtos. Enquanto os favoritos ganham seu lugar, conheça as novidades da loja.', 'ecommerce-joias' ); ?></p>
		<a class="wp-block-button__link" href="<?php echo esc_url( ecommerce_joias_catalog_view_url( 'novidades' ) ); ?>"><?php esc_html_e( 'Explorar novidades', 'ecommerce-joias' ); ?></a>
	</section>
	<?php
}
add_action( 'woocommerce_no_products_found', 'ecommerce_joias_catalog_empty_selection', 5 );

function ecommerce_joias_catalog_active_menu( $classes, $item ) {
	$view = ecommerce_joias_catalog_menu_view( $item->url );
	if ( null !== $view && is_shop() ) {
		$classes = array_diff( $classes, array( 'current-menu-item', 'current_page_item', 'current_page_parent' ) );
		if ( $view === ecommerce_joias_catalog_view() ) {
			$classes[] = 'current-menu-item';
		}
	}
	return $classes;
}
add_filter( 'nav_menu_css_class', 'ecommerce_joias_catalog_active_menu', 10, 2 );
