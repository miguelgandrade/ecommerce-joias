<?php
/** Páginas de ajuda e estados de navegação. */
defined( 'ABSPATH' ) || exit;

function ecommerce_joias_support_url( $slug = 'ajuda' ) {
	$page = get_page_by_path( $slug );
	return $page && 'publish' === $page->post_status && ! $page->post_password ? get_permalink( $page ) : home_url( '/' );
}
function ecommerce_joias_support_shop_url() {
	$page = get_page_by_path( 'produtos' );
	return $page && 'publish' === $page->post_status ? get_permalink( $page ) : ( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' ) );
}
function ecommerce_joias_support_search() {
	?>
	<form class="nc-support-search" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
		<label class="screen-reader-text" for="nc-support-query">Buscar peças</label>
		<input id="nc-support-query" type="search" name="s" placeholder="Busque por anel, colar, brinco…" value="<?php echo esc_attr( get_search_query() ); ?>" required>
		<input type="hidden" name="post_type" value="product">
		<button type="submit">Buscar</button>
	</form>
	<?php
}
function ecommerce_joias_support_empty() {
	// A seleção de mais vendidos tem sua própria mensagem.
	if ( function_exists( 'is_shop' ) && is_shop() && function_exists( 'ecommerce_joias_catalog_view' ) && 'mais-vendidos' === ecommerce_joias_catalog_view() ) {
		return;
	}
	remove_action( 'woocommerce_no_products_found', 'wc_no_products_found', 10 );
	?>
	<section class="nc-support-state" aria-labelledby="nc-empty-title">
		<span class="nc-support-symbol" aria-hidden="true">✦</span>
		<h2 id="nc-empty-title">Nenhuma peça encontrada</h2>
		<p>Tente buscar por outro nome ou remova os filtros para descobrir mais peças.</p>
		<?php ecommerce_joias_support_search(); ?>
		<a class="nc-support-button" href="<?php echo esc_url( ecommerce_joias_support_shop_url() ); ?>">Limpar filtros e ver coleção</a>
	</section>
	<?php
}
add_action( 'woocommerce_no_products_found', 'ecommerce_joias_support_empty', 6 );

/** O pedido continua no fluxo protegido do WooCommerce. */
function ecommerce_joias_support_order_state( $order_id ) {
	$order = wc_get_order( $order_id );
	if ( ! $order || ! $order->has_status( array( 'failed', 'cancelled' ) ) ) {
		return;
	}
	// Sem dados do pedido em acessos sem autorização (incluindo visitantes).
	$key = isset( $_GET['key'] ) ? wc_clean( wp_unslash( $_GET['key'] ) ) : '';
	$owner = is_user_logged_in() && (int) $order->get_customer_id() === get_current_user_id();
	if ( ! $owner && ! current_user_can( 'manage_woocommerce' ) && ( ! $key || ! hash_equals( $order->get_order_key(), $key ) ) ) {
		return;
	}
	$cancelled = $order->has_status( 'cancelled' );
	?>
	<aside class="nc-support-order">
		<h2><?php echo esc_html( $cancelled ? 'Pedido cancelado' : 'Pagamento não concluído' ); ?></h2>
		<p><?php echo esc_html( $cancelled ? 'O cancelamento não confirma um reembolso. Se houve pagamento, fale com a equipe para acompanhar a devolução do valor.' : 'Confira o status e as instruções do pedido antes de tentar pagar novamente. Se tiver dúvidas sobre uma cobrança, fale com a equipe.' ); ?></p>
		<a href="<?php echo esc_url( ecommerce_joias_support_url( $cancelled ? 'pedido-cancelado' : 'pagamento-nao-concluido' ) ); ?>">Ver orientações</a>
	</aside>
	<?php
}
add_action( 'woocommerce_thankyou', 'ecommerce_joias_support_order_state', 5 );
add_action( 'woocommerce_view_order', 'ecommerce_joias_support_order_state', 5 );

/** Evita indexar páginas de orientação sobre estados transitórios. */
function ecommerce_joias_support_robots( $robots ) {
	if ( is_page( array( 'manutencao', 'pedido-cancelado', 'pagamento-nao-concluido' ) ) ) {
		$robots['noindex'] = true;
	}
	return $robots;
}
add_filter( 'wp_robots', 'ecommerce_joias_support_robots' );

/** Somente a rota de manutenção responde 503; a loja permanece disponível. */
function ecommerce_joias_support_maintenance_status() {
	if ( is_page( 'manutencao' ) ) {
		status_header( 503 );
		nocache_headers();
		header( 'Retry-After: 3600' );
	}
}
add_action( 'template_redirect', 'ecommerce_joias_support_maintenance_status' );
