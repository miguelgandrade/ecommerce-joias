<?php
/** Apresentação da área de cliente; autenticação e dados continuam no WooCommerce. */
defined( 'ABSPATH' ) || exit;

function ecommerce_joias_account_heading() {
	?>
	<header class="nc-account-heading">
		<p class="nc-account-eyebrow"><?php esc_html_e( 'Seu espaço na NC', 'ecommerce-joias' ); ?></p>
		<h1><?php esc_html_e( 'Minha conta', 'ecommerce-joias' ); ?></h1>
		<p><?php esc_html_e( 'Acompanhe suas compras e mantenha seus dados em dia.', 'ecommerce-joias' ); ?></p>
	</header>
	<?php
}
add_action( 'woocommerce_before_account_navigation', 'ecommerce_joias_account_heading' );
