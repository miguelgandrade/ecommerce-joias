<?php
/** Apresentação do carrinho de blocos; cálculos e ações pertencem ao WooCommerce. */
defined( 'ABSPATH' ) || exit;

/** Acrescenta contexto e ajuda ao redor do bloco, sem interferir na atualização do carrinho. */
function ecommerce_joias_cart_layout( $content ) {
	if ( ! function_exists( 'is_cart' ) || ! is_cart() ) {
		return $content;
	}

	ob_start();
	?>
	<header class="nc-cart-heading">
		<div>
			<p class="nc-cart-eyebrow"><?php esc_html_e( 'Suas escolhas, em um só lugar', 'ecommerce-joias' ); ?></p>
			<h1><?php esc_html_e( 'Meu carrinho', 'ecommerce-joias' ); ?></h1>
			<p><?php esc_html_e( 'Confira suas peças antes de continuar.', 'ecommerce-joias' ); ?></p>
		</div>
		<a class="nc-cart-back" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>"><span aria-hidden="true">←</span> <?php esc_html_e( 'Continuar comprando', 'ecommerce-joias' ); ?></a>
	</header>
	<ol class="nc-cart-steps" aria-label="<?php esc_attr_e( 'Etapas da compra', 'ecommerce-joias' ); ?>">
		<li aria-current="step"><span aria-hidden="true">1</span> <?php esc_html_e( 'Carrinho', 'ecommerce-joias' ); ?></li>
		<li><span aria-hidden="true">2</span> <?php esc_html_e( 'Dados e entrega', 'ecommerce-joias' ); ?></li>
		<li><span aria-hidden="true">3</span> <?php esc_html_e( 'Pagamento', 'ecommerce-joias' ); ?></li>
	</ol>
	<?php
	$heading = ob_get_clean();

	ob_start();
	?>
	<section class="nc-cart-help" aria-labelledby="nc-cart-help-title">
		<div class="nc-cart-help-intro">
			<p class="nc-cart-eyebrow"><?php esc_html_e( 'Antes de continuar', 'ecommerce-joias' ); ?></p>
			<h2 id="nc-cart-help-title"><?php esc_html_e( 'Sua compra, sem dúvidas', 'ecommerce-joias' ); ?></h2>
			<p><?php esc_html_e( 'Confira os próximos passos para finalizar seu pedido.', 'ecommerce-joias' ); ?></p>
		</div>
		<div class="nc-cart-faq">
			<?php if ( wc_shipping_enabled() ) : ?>
				<details><summary><?php esc_html_e( 'Como consultar a entrega?', 'ecommerce-joias' ); ?></summary><p><?php esc_html_e( 'Informe seu endereço para consultar as opções de envio disponíveis. Confira o frete e o prazo apresentados antes de concluir o pedido.', 'ecommerce-joias' ); ?></p></details>
			<?php endif; ?>
			<?php if ( wc_coupons_enabled() ) : ?>
				<details><summary><?php esc_html_e( 'Tenho um cupom. Onde uso?', 'ecommerce-joias' ); ?></summary><p><?php esc_html_e( 'Abra o campo de cupom no resumo da compra, digite o código e aplique. Se ele for válido para suas peças, o desconto aparecerá no total.', 'ecommerce-joias' ); ?></p></details>
			<?php endif; ?>
			<details><summary><?php esc_html_e( 'Posso alterar as peças?', 'ecommerce-joias' ); ?></summary><p><?php esc_html_e( 'Sim. Ajuste a quantidade ou remova um item no carrinho. Para escolher outro tamanho ou acabamento, volte à página da peça e selecione a opção desejada.', 'ecommerce-joias' ); ?></p></details>
			<details><summary><?php esc_html_e( 'Quando escolho como pagar?', 'ecommerce-joias' ); ?></summary><p><?php esc_html_e( 'As formas de pagamento disponíveis aparecem na finalização da compra, após o preenchimento dos seus dados. Revise o total antes de confirmar.', 'ecommerce-joias' ); ?></p></details>
			<?php if ( wc_get_page_id( 'myaccount' ) > 0 ) : ?>
				<p class="nc-cart-account-link"><a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>"><?php esc_html_e( 'Já tem uma conta? Acesse seus pedidos', 'ecommerce-joias' ); ?> <span aria-hidden="true">→</span></a></p>
			<?php endif; ?>
		</div>
	</section>
	<?php
	return $heading . $content . ob_get_clean();
}
add_filter( 'render_block_woocommerce/cart', 'ecommerce_joias_cart_layout' );

/** Enriquece a mensagem vazia mantendo os produtos sugeridos do editor. */
function ecommerce_joias_cart_empty_heading( $content, $block ) {
	if ( ! function_exists( 'is_cart' ) || ! is_cart() || ! str_contains( $block['attrs']['className'] ?? '', 'wc-block-cart__empty-cart__title' ) ) {
		return $content;
	}

	ob_start();
	?>
	<div class="nc-cart-empty">
		<span class="nc-cart-empty-icon" aria-hidden="true"><i class="bx bx-shopping-bag"></i></span>
		<h2><?php esc_html_e( 'Seu carrinho espera suas escolhas', 'ecommerce-joias' ); ?></h2>
		<p><?php esc_html_e( 'Ainda não há peças por aqui. Explore a coleção e encontre os detalhes que combinam com você.', 'ecommerce-joias' ); ?></p>
		<a class="nc-cart-shop-button" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>"><?php esc_html_e( 'Explorar a coleção', 'ecommerce-joias' ); ?> <span aria-hidden="true">→</span></a>
	</div>
	<?php
	return ob_get_clean();
}
add_filter( 'render_block_core/heading', 'ecommerce_joias_cart_empty_heading', 10, 2 );
