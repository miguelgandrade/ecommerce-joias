<?php
/**
 * Painel do cliente NC Semijoias.
 * @package Ecommerce_Joias
 * @version 4.4.0
 */
defined( 'ABSPATH' ) || exit;

$customer = wp_get_current_user();
$name     = $customer->first_name ?: $customer->display_name;
$orders   = wc_get_orders( array( 'customer_id' => $customer->ID, 'limit' => 1, 'orderby' => 'date', 'order' => 'DESC' ) );
$shortcuts = array(
	array( 'orders', 'bx-shopping-bag', 'Meus pedidos', 'Consulte suas compras e o status de cada pedido.', 'Ver pedidos' ),
	array( 'edit-address', 'bx-map', 'Meus endereços', 'Organize seus endereços de entrega e cobrança.', 'Gerenciar endereços' ),
	array( 'edit-account', 'bx-user', 'Meus dados', 'Atualize seu nome, e-mail e senha de acesso.', 'Editar meus dados' ),
);
?>
<div class="nc-account-welcome">
	<h2><?php echo esc_html( sprintf( __( 'Olá, %s!', 'ecommerce-joias' ), $name ) ); ?></h2>
	<p><?php esc_html_e( 'Que bom ter você por aqui. O que deseja fazer hoje?', 'ecommerce-joias' ); ?></p>
</div>
<div class="nc-account-shortcuts">
	<?php foreach ( $shortcuts as $shortcut ) : ?>
		<a class="nc-account-card" href="<?php echo esc_url( wc_get_account_endpoint_url( $shortcut[0] ) ); ?>">
			<i class="bx <?php echo esc_attr( $shortcut[1] ); ?>" aria-hidden="true"></i>
			<h3><?php echo esc_html( $shortcut[2] ); ?></h3>
			<p><?php echo esc_html( $shortcut[3] ); ?></p>
			<span><?php echo esc_html( $shortcut[4] ); ?> <span aria-hidden="true">→</span></span>
		</a>
	<?php endforeach; ?>
</div>
<section class="nc-account-recent" aria-labelledby="nc-recent-order">
	<div class="nc-account-section-heading">
		<h3 id="nc-recent-order"><?php esc_html_e( 'Sua última compra', 'ecommerce-joias' ); ?></h3>
		<a href="<?php echo esc_url( wc_get_account_endpoint_url( 'orders' ) ); ?>"><?php esc_html_e( 'Ver todos os pedidos', 'ecommerce-joias' ); ?></a>
	</div>
	<?php if ( $orders ) : ?>
		<?php $recent_order = $orders[0]; ?>
		<div class="nc-account-order">
			<div><span class="nc-account-label"><?php esc_html_e( 'Pedido', 'ecommerce-joias' ); ?></span><strong>#<?php echo esc_html( $recent_order->get_order_number() ); ?></strong></div>
			<div><span class="nc-account-label"><?php esc_html_e( 'Data', 'ecommerce-joias' ); ?></span><?php echo esc_html( $recent_order->get_date_created() ? wc_format_datetime( $recent_order->get_date_created() ) : '—' ); ?></div>
			<div><span class="nc-account-label"><?php esc_html_e( 'Status', 'ecommerce-joias' ); ?></span><span class="nc-account-status"><?php echo esc_html( wc_get_order_status_name( $recent_order->get_status() ) ); ?></span></div>
			<div><span class="nc-account-label"><?php esc_html_e( 'Total', 'ecommerce-joias' ); ?></span><?php echo wp_kses_post( $recent_order->get_formatted_order_total() ); ?></div>
			<a class="nc-account-text-link" href="<?php echo esc_url( $recent_order->get_view_order_url() ); ?>"><?php esc_html_e( 'Ver detalhes', 'ecommerce-joias' ); ?> <span aria-hidden="true">→</span></a>
		</div>
	<?php else : ?>
		<div class="nc-account-empty">
			<i class="bx bx-shopping-bag" aria-hidden="true"></i>
			<div><h4><?php esc_html_e( 'Sua primeira compra começa aqui', 'ecommerce-joias' ); ?></h4><p><?php esc_html_e( 'Quando fizer um pedido, você poderá acompanhar os detalhes neste espaço.', 'ecommerce-joias' ); ?></p></div>
			<a class="button" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>"><?php esc_html_e( 'Explorar peças', 'ecommerce-joias' ); ?></a>
		</div>
	<?php endif; ?>
</section>
<?php
do_action( 'woocommerce_account_dashboard' );
do_action( 'woocommerce_before_my_account' );
do_action( 'woocommerce_after_my_account' );
