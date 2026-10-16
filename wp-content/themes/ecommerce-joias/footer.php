<?php
/**
 * Rodapé do tema Ecommerce Joias.
 *
 * @package Ecommerce_Joias
 */
defined( 'ABSPATH' ) || exit;

/**
 * Funções e variáveis do rodapé do tema Ecommerce Joias.
 */
$footer_description = get_theme_mod(
	'ecommerce_joias_footer_description',
	'Joias escolhidas para acompanhar momentos que merecem ser lembrados.'
);

$footer_email = get_theme_mod( 'ecommerce_joias_footer_email', '' );
$footer_instagram = get_theme_mod( 'ecommerce_joias_footer_instagram', '' );
$footer_whatsapp = preg_replace( '/\D/', '', get_theme_mod( 'ecommerce_joias_footer_whatsapp', '' ) );
$footer_whatsapp = strlen( $footer_whatsapp ) >= 10 && strlen( $footer_whatsapp ) <= 15 ? $footer_whatsapp : '';
$footer_hours = get_theme_mod( 'ecommerce_joias_footer_hours', '' );
$footer_help = array(
	'delivery' => __( 'Entrega e frete', 'ecommerce-joias' ),
	'returns'  => __( 'Trocas e devoluções', 'ecommerce-joias' ),
	'care'     => __( 'Cuidados com as peças', 'ecommerce-joias' ),
	'sizes'    => __( 'Guia de tamanhos', 'ecommerce-joias' ),
);
$footer_payments = array();
if ( function_exists( 'WC' ) && WC()->payment_gateways() ) {
	foreach ( WC()->payment_gateways()->payment_gateways() as $gateway ) {
		if ( 'yes' === $gateway->enabled && $gateway->get_title() ) {
			$footer_payments[] = wp_strip_all_tags( $gateway->get_title() );
		}
	}
}
$footer_copyright = get_theme_mod(
	'ecommerce_joias_footer_copyright',
	'Todos os direitos reservados.'
);
?>
	</div><!-- #content.site-content -->

	<!-- Nome ou logo da marca + Navegação -->
	<footer class="nc-site-footer">
		<div class="nc-site-footer__main">
			<div class="nc-site-footer__brand">
				<?php if ( has_custom_logo() ) : ?>
					<?php the_custom_logo(); ?>
				<?php else : ?>
					<a class="nc-site-footer__brand-name" href="<?php echo esc_url( home_url( '/' ) ); ?>">
						<?php bloginfo( 'name' ); ?>
					</a>
				<?php endif; ?>

				<p><?php echo nl2br( esc_html( $footer_description ) ); ?></p>
			</div>

			<!-- Menu de navegação do rodapé -->
			<nav class="nc-site-footer__navigation" aria-label="<?php esc_attr_e( 'Navegação do rodapé', 'ecommerce-joias' ); ?>">
				<h2><?php esc_html_e( 'Navegue', 'ecommerce-joias' ); ?></h2>

				<?php
					if ( has_nav_menu( 'nc-footer' ) ) {
					wp_nav_menu(
					array(
							'theme_location' => 'nc-footer',
						'container'      => false,
						'menu_class'     => 'nc-site-footer__menu',
						'fallback_cb'    => false,
					)
				);
					} else {
						$footer_links = array();
						if ( function_exists( 'wc_get_page_id' ) && wc_get_page_id( 'shop' ) > 0 ) {
							$footer_links[ wc_get_page_permalink( 'shop' ) ] = __( 'Todas as peças', 'ecommerce-joias' );
						}
						$about_page = get_page_by_path( 'sobre-a-loja' );
						if ( $about_page && 'publish' === $about_page->post_status && ! $about_page->post_password ) {
							$footer_links[ get_permalink( $about_page ) ] = __( 'Sobre a NC', 'ecommerce-joias' );
						}
						?>
						<ul class="nc-site-footer__menu">
							<?php foreach ( $footer_links as $url => $label ) : ?>
								<li><a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $label ); ?></a></li>
							<?php endforeach; ?>
						</ul>
						<?php
					}
				?>
				<?php if ( function_exists( 'wc_get_page_id' ) && wc_get_page_id( 'myaccount' ) > 0 ) : ?>
					<ul class="nc-site-footer__menu nc-site-footer__account">
						<li><a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>"><?php esc_html_e( 'Minha conta', 'ecommerce-joias' ); ?></a></li>
						<li><a href="<?php echo esc_url( wc_get_account_endpoint_url( 'orders' ) ); ?>"><?php esc_html_e( 'Meus pedidos', 'ecommerce-joias' ); ?></a></li>
					</ul>
				<?php endif; ?>
			</nav>
			<nav class="nc-site-footer__help" aria-label="<?php esc_attr_e( 'Ajuda para comprar', 'ecommerce-joias' ); ?>">
				<h2><?php esc_html_e( 'Ajuda', 'ecommerce-joias' ); ?></h2>
				<ul class="nc-site-footer__menu">
					<?php foreach ( $footer_help as $key => $label ) : ?>
						<?php $url = ecommerce_joias_footer_page_url( $key ); ?>
						<?php if ( $url ) : ?>
							<li><a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $label ); ?></a></li>
						<?php elseif ( 'care' === $key ) : ?>
							<li><details><summary><?php echo esc_html( $label ); ?></summary><p><?php esc_html_e( 'Evite contato com perfumes e produtos de limpeza. Guarde suas peças secas e separadas. Siga também as orientações específicas de cada produto.', 'ecommerce-joias' ); ?></p></details></li>
						<?php elseif ( 'sizes' === $key ) : ?>
							<li><details><summary><?php echo esc_html( $label ); ?></summary><p><?php esc_html_e( 'Confira as medidas e variações na página da peça antes de adicionar ao carrinho. Para anéis e pulseiras, compare com uma peça que já fique confortável em você.', 'ecommerce-joias' ); ?></p></details></li>
						<?php elseif ( 'delivery' === $key && function_exists( 'wc_shipping_enabled' ) && wc_shipping_enabled() ) : ?>
							<li><details><summary><?php echo esc_html( $label ); ?></summary><p><?php esc_html_e( 'Informe seu endereço no carrinho ou na finalização para consultar as opções de envio disponíveis antes de confirmar o pedido.', 'ecommerce-joias' ); ?></p></details></li>
						<?php endif; ?>
					<?php endforeach; ?>
				</ul>
			</nav>
			<!-- Menu de contato do rodapé -->
			<?php if ( $footer_email || $footer_instagram || $footer_whatsapp || $footer_hours ) : ?>
				<div class="nc-site-footer__contact">
					<h2><?php esc_html_e( 'Atendimento', 'ecommerce-joias' ); ?></h2>
					<?php if ( $footer_whatsapp ) : ?>
						<a href="<?php echo esc_url( 'https://wa.me/' . $footer_whatsapp ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Fale pelo WhatsApp', 'ecommerce-joias' ); ?></a>
					<?php endif; ?>
					<?php if ( $footer_hours ) : ?>
						<p class="nc-site-footer__hours"><?php echo esc_html( $footer_hours ); ?></p>
					<?php endif; ?>

					<?php if ( $footer_email ) : ?>
						<a href="mailto:<?php echo esc_attr( $footer_email ); ?>">
							<?php echo esc_html( $footer_email ); ?>
						</a>
					<?php endif; ?>

					<?php if ( $footer_instagram ) : ?>
						<a href="<?php echo esc_url( $footer_instagram ); ?>" target="_blank" rel="noopener noreferrer">
							Instagram
						</a>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>
		<?php if ( $footer_payments ) : ?>
			<div class="nc-site-footer__payments">
				<h2><?php esc_html_e( 'Formas de pagamento', 'ecommerce-joias' ); ?></h2>
				<ul><?php foreach ( array_unique( $footer_payments ) as $payment ) : ?><li><?php echo esc_html( $payment ); ?></li><?php endforeach; ?></ul>
				<p><?php esc_html_e( 'Disponibilidade e condições confirmadas na finalização da compra.', 'ecommerce-joias' ); ?></p>
			</div>
		<?php endif; ?>

		<!-- Area de copyright do rodapé -->
		<div class="nc-site-footer__bottom">
			<?php
			$footer_legal_links = array_filter( array(
				__( 'Privacidade', 'ecommerce-joias' ) => ecommerce_joias_footer_page_url( 'privacy' ),
				__( 'Termos de uso', 'ecommerce-joias' ) => ecommerce_joias_footer_page_url( 'terms' ),
			) );
			?>
			<?php if ( $footer_legal_links ) : ?>
				<nav class="nc-site-footer__legal" aria-label="<?php esc_attr_e( 'Políticas do site', 'ecommerce-joias' ); ?>">
					<?php foreach ( $footer_legal_links as $label => $url ) : ?><a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $label ); ?></a><?php endforeach; ?>
				</nav>
			<?php endif; ?>
			<p>
				© <?php echo esc_html( wp_date( 'Y' ) ); ?>
				<?php bloginfo( 'name' ); ?>.
				<?php echo esc_html( $footer_copyright ); ?>
			</p>
		</div>
	</footer>

	<!-- Botão de voltar ao topo da página -->
	<?php if ( function_exists( 'orchid_store_get_option' ) && orchid_store_get_option( 'display_scroll_top_button' ) ) : ?>
		<button type="button" class="orchid-backtotop" aria-label="<?php esc_attr_e( 'Voltar ao topo', 'ecommerce-joias' ); ?>"<?php echo is_front_page() ? ' hidden' : ''; ?>>
			<span><i class="bx bx-chevron-up"></i></span>
		</button>
	<?php endif; ?>

</div><!-- #page.site -->

<?php wp_footer(); ?>

</body>
</html>
