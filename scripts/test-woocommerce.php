<?php
/**
 * Integração local com dados descartáveis. Não instala gateway nem envia e-mails.
 * Execute conforme README; requer NC_RUN_LOCAL_TESTS=1 e WordPress em localhost.
 */
if ( '1' !== getenv( 'NC_RUN_LOCAL_TESTS' ) || PHP_SAPI !== 'cli' ) {
	fwrite( STDERR, "Execução permitida apenas no CLI local com NC_RUN_LOCAL_TESTS=1.\n" );
	exit( 1 );
}
$_SERVER['HTTP_HOST'] = 'localhost:8080';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/';
require '/var/www/html/wp-load.php';
if ( ! in_array( wp_parse_url( home_url(), PHP_URL_HOST ), array( 'localhost', '127.0.0.1' ), true ) ) {
	throw new RuntimeException( 'O teste só pode rodar na loja local.' );
}
add_filter( 'pre_wp_mail', '__return_true' );
// Impede webhooks e integrações HTTP de saírem deste processo de teste.
add_filter( 'pre_http_request', function () { return new WP_Error( 'nc_test_offline', 'HTTP externo bloqueado nos testes.' ); } );

$fixture_ids = array( 'products' => array(), 'users' => array(), 'orders' => array(), 'coupons' => array() );
$results = array();
$run = 'nc-v1-' . wp_generate_uuid4();
$cleaned = false;
function nc_test_check( $name, $passed ) {
	global $results;
	$results[ $name ] = (bool) $passed;
	if ( ! $passed ) {
		throw new RuntimeException( $name );
	}
}
function nc_test_request( $method, $path, $data = array(), $nonce = true ) {
	$request = new WP_REST_Request( $method, '/wc/store/v1/' . $path );
	if ( $nonce ) {
		$request->set_header( 'Nonce', wp_create_nonce( 'wc_store_api' ) );
	}
	$request->set_body_params( $data );
	$response = rest_do_request( $request );
	// A resposta interna contém stdClass; normaliza como o JSON recebido por HTTP.
	$response->set_data( json_decode( wp_json_encode( $response->get_data() ), true ) );
	return $response;
}
function nc_test_cleanup() {
	global $fixture_ids, $cleaned;
	if ( $cleaned ) { return; }
	$cleaned = true;
	if ( WC()->cart ) { WC()->cart->empty_cart(); }
	if ( WC()->session ) { WC()->session->destroy_session(); }
	foreach ( array_unique( $fixture_ids['orders'] ) as $id ) {
		$order = wc_get_order( $id );
		if ( $order ) { wc_release_stock_for_order( $order ); $order->delete( true ); }
	}
	foreach ( array_reverse( $fixture_ids['products'] ) as $id ) {
		$product = wc_get_product( $id );
		if ( $product ) { $product->delete( true ); }
	}
	foreach ( $fixture_ids['coupons'] as $id ) {
		$coupon = new WC_Coupon( $id );
		$coupon->delete( true );
	}
	require_once ABSPATH . 'wp-admin/includes/user.php';
	wp_set_current_user( 0 );
	foreach ( $fixture_ids['users'] as $id ) { wp_delete_user( $id ); }
}
register_shutdown_function( 'nc_test_cleanup' );
// Inclui pedidos provisórios que o checkout possa criar antes de uma validação falhar.
add_action( 'woocommerce_new_order', function ( $id ) use ( &$fixture_ids ) { $fixture_ids['orders'][] = $id; } );

class NC_V1_Test_Gateway extends WC_Payment_Gateway {
	public function __construct() {
		$this->id = 'nc_v1_test';
		$this->title = 'Teste local, sem cobrança';
		$this->enabled = 'yes';
		$this->supports = array( 'products' );
	}
	public function process_payment( $order_id ) {
		$order = wc_get_order( $order_id );
		$order->update_status( 'on-hold', 'Pedido descartável da verificação local.' );
		return array( 'result' => 'success', 'redirect' => $this->get_return_url( $order ) );
	}
}
add_filter( 'woocommerce_payment_gateways', function ( $methods ) { $methods[] = 'NC_V1_Test_Gateway'; return $methods; } );

try {
	foreach ( array( 'a', 'b' ) as $suffix ) {
		$id = wp_insert_user( array( 'user_login' => $run . $suffix, 'user_pass' => wp_generate_password( 32 ), 'user_email' => $run . $suffix . '@example.invalid', 'role' => 'customer', 'first_name' => 'Cliente de teste' ) );
		if ( is_wp_error( $id ) ) { throw new RuntimeException( 'Falha ao criar cliente descartável.' ); }
		$fixture_ids['users'][] = $id;
	}
	$user_id = $fixture_ids['users'][0];
	wp_set_current_user( $user_id );
	wc_load_cart();
	WC()->cart->empty_cart();

	$product = new WC_Product_Simple();
	$product->set_name( $run );
	$product->set_status( 'publish' );
	$product->set_catalog_visibility( 'hidden' );
	$product->set_regular_price( '10.05' );
	$product->set_virtual( true );
	$product->set_tax_status( 'none' );
	$product->set_manage_stock( true );
	$product->set_stock_quantity( 5 );
	$fixture_ids['products'][] = $product->save();

	$response = nc_test_request( 'POST', 'cart/add-item', array( 'id' => $product->get_id(), 'quantity' => 1 ), false );
	nc_test_check( 'mutation_without_nonce_rejected', 401 === $response->get_status() );
	$response = nc_test_request( 'POST', 'cart/add-item', array( 'id' => $product->get_id(), 'quantity' => 6 ) );
	nc_test_check( 'quantity_above_stock_rejected', 400 === $response->get_status() );
	$product->set_stock_quantity( 0 );
	$product->save();
	$response = nc_test_request( 'POST', 'cart/add-item', array( 'id' => $product->get_id(), 'quantity' => 1 ) );
	nc_test_check( 'out_of_stock_rejected', 400 === $response->get_status() );
	$product->set_stock_quantity( 5 );
	$product->save();

	$variable = new WC_Product_Variable();
	$variable->set_name( $run . '-variable' );
	$variable->set_status( 'publish' );
	$variable->set_catalog_visibility( 'hidden' );
	$attribute = new WC_Product_Attribute();
	$attribute->set_name( 'Tamanho' );
	$attribute->set_options( array( '16', '18' ) );
	$attribute->set_variation( true );
	$variable->set_attributes( array( $attribute ) );
	$fixture_ids['products'][] = $variable->save();
	$variation = new WC_Product_Variation();
	$variation->set_parent_id( $variable->get_id() );
	$variation->set_attributes( array( 'tamanho' => '16' ) );
	$variation->set_regular_price( '15.00' );
	$variation->set_virtual( true );
	$fixture_ids['products'][] = $variation->save();
	WC_Product_Variable::sync( $variable->get_id() );
	$response = nc_test_request( 'POST', 'cart/add-item', array( 'id' => $variable->get_id(), 'quantity' => 1 ) );
	nc_test_check( 'variation_selection_required', 400 === $response->get_status() );
	$response = nc_test_request( 'POST', 'cart/add-item', array( 'id' => $variation->get_id(), 'quantity' => 1 ) );
	$data = $response->get_data();
	nc_test_check( 'selected_variation_added', 201 === $response->get_status() && $variation->get_id() === $data['items'][0]['id'] );
	$response = nc_test_request( 'POST', 'cart/remove-item', array( 'key' => $data['items'][0]['key'] ) );
	nc_test_check( 'remove_item_and_zero_total', 200 === $response->get_status() && 0 === $response->get_data()['items_count'] && '0' === $response->get_data()['totals']['total_items'] );
	$response = nc_test_request( 'POST', 'cart/add-item', array( 'id' => $product->get_id(), 'quantity' => 2, 'price' => '0.01' ) );
	$data = $response->get_data();
	nc_test_check( 'add_two_items', 201 === $response->get_status() && 2 === $data['items_count'] );
	nc_test_check( 'server_price_and_money_rounding', '2010' === $data['totals']['total_items'] );
	$key = $data['items'][0]['key'];
	$response = nc_test_request( 'POST', 'cart/update-item', array( 'key' => $key, 'quantity' => 3 ) );
	nc_test_check( 'update_quantity_and_total', 200 === $response->get_status() && '3015' === $response->get_data()['totals']['total_items'] );
	nc_test_request( 'POST', 'cart/update-item', array( 'key' => $key, 'quantity' => 2 ) );

	$coupon = new WC_Coupon();
	$coupon->set_code( $run );
	$coupon->set_discount_type( 'percent' );
	$coupon->set_amount( 10 );
	$coupon->set_product_ids( array( $product->get_id() ) );
	$fixture_ids['coupons'][] = $coupon->save();
	$response = nc_test_request( 'POST', 'cart/apply-coupon', array( 'code' => $run . '-invalid' ) );
	nc_test_check( 'invalid_coupon_rejected', 400 === $response->get_status() );
	$response = nc_test_request( 'POST', 'cart/apply-coupon', array( 'code' => $run ) );
	$data = $response->get_data();
	nc_test_check( 'coupon_discount_and_rounding', 200 === $response->get_status() && '201' === $data['totals']['total_discount'] && '1809' === $data['totals']['total_price'] );
	$response = nc_test_request( 'POST', 'cart/remove-coupon', array( 'code' => $run ) );
	nc_test_check( 'remove_coupon_restores_total', 200 === $response->get_status() && '2010' === $response->get_data()['totals']['total_price'] );
	nc_test_request( 'POST', 'cart/apply-coupon', array( 'code' => $run ) );

	ob_start();
	wc_get_template( 'myaccount/my-account.php' );
	$dashboard = ob_get_clean();
	nc_test_check( 'account_empty_dashboard', str_contains( $dashboard, 'nc-account-empty' ) && 3 === substr_count( $dashboard, 'class="nc-account-card"' ) );
	$response = nc_test_request( 'POST', 'checkout', array( 'billing_address' => array( 'email' => 'invalid' ) ) );
	nc_test_check( 'invalid_checkout_rejected', $response->get_status() >= 400 && $response->get_status() < 500 );

	$billing = array( 'first_name' => 'Cliente', 'last_name' => 'Teste', 'company' => '', 'address_1' => 'Rua de Teste, 100', 'address_2' => '', 'city' => 'São Paulo', 'state' => 'SP', 'postcode' => '01001000', 'country' => 'BR', 'email' => $run . 'a@example.invalid', 'phone' => '11999999999' );
	$response = nc_test_request( 'POST', 'checkout', array( 'billing_address' => $billing, 'payment_method' => 'nc_v1_test', 'payment_data' => array(), 'customer_note' => 'TESTE LOCAL DESCARTÁVEL', 'expected_total' => '1809' ) );
	$data = $response->get_data();
	if ( 200 !== $response->get_status() ) {
		throw new RuntimeException( 'checkout_status_' . $response->get_status() . ': ' . ( $data['code'] ?? 'unknown' ) . ' ' . wp_strip_all_tags( $data['message'] ?? '' ) );
	}
	$order = wc_get_order( $data['order_id'] );
	nc_test_check( 'checkout_success_without_real_payment', 'success' === $data['payment_result']['payment_status'] && $order && $order->has_status( 'on-hold' ) );
	nc_test_check( 'order_total_matches_cart', '18.09' === $order->get_total() );
	nc_test_check( 'order_customer_and_items', $user_id === $order->get_customer_id() && 2 === $order->get_item_count() );
	$product = wc_get_product( $product->get_id() );
	nc_test_check( 'stock_reduced_after_order', 3 === $product->get_stock_quantity() );
	nc_test_check( 'own_order_access', current_user_can( 'view_order', $order->get_id() ) );

	ob_start(); wc_get_template( 'myaccount/dashboard.php' ); $dashboard = ob_get_clean();
	nc_test_check( 'dashboard_shows_latest_order', str_contains( $dashboard, 'nc-account-order' ) && str_contains( $dashboard, esc_url( $order->get_view_order_url() ) ) );
	ob_start(); woocommerce_account_view_order( $order->get_id() ); $details = ob_get_clean();
	nc_test_check( 'own_order_details_render', str_contains( $details, $run ) );
	wp_set_current_user( $fixture_ids['users'][1] );
	nc_test_check( 'other_customer_order_forbidden', ! current_user_can( 'view_order', $order->get_id() ) );
	ob_start(); woocommerce_account_view_order( $order->get_id() ); $details = ob_get_clean();
	nc_test_check( 'other_customer_cannot_read_order', ! str_contains( $details, $run ) && str_contains( $details, 'woocommerce-error' ) );
	$other_user = get_user_by( 'id', $fixture_ids['users'][1] );
	$reset_key = get_password_reset_key( $other_user );
	nc_test_check( 'password_reset_key_valid', ! is_wp_error( $reset_key ) && ! is_wp_error( check_password_reset_key( $reset_key, $other_user->user_login ) ) );
	nc_test_check( 'invalid_password_reset_key_rejected', is_wp_error( check_password_reset_key( 'invalid-key', $other_user->user_login ) ) );
	$new_password = wp_generate_password( 32 );
	reset_password( $other_user, $new_password );
	nc_test_check( 'login_with_reset_password', ! is_wp_error( wp_authenticate( $other_user->user_login, $new_password ) ) );
	nc_test_check( 'wrong_password_rejected', is_wp_error( wp_authenticate( $other_user->user_login, $new_password . '-wrong' ) ) );
	nc_test_check( 'reset_key_cannot_be_reused', is_wp_error( check_password_reset_key( $reset_key, $other_user->user_login ) ) );
	wp_set_current_user( 0 );
	nc_test_check( 'guest_order_forbidden', ! current_user_can( 'view_order', $order->get_id() ) );
	ob_start(); echo do_shortcode( '[woocommerce_my_account]' ); $guest = ob_get_clean();
	nc_test_check( 'guest_sees_login', str_contains( $guest, 'woocommerce-form-login' ) && ! str_contains( $guest, 'nc-account-card' ) );
} catch ( Throwable $error ) {
	$results['failure'] = $error->getMessage();
} finally {
	nc_test_cleanup();
}
$remaining = array_filter( $fixture_ids['products'], fn( $id ) => (bool) get_post( $id ) );
$remaining = array_merge( $remaining, array_filter( $fixture_ids['users'], fn( $id ) => (bool) get_user_by( 'id', $id ) ) );
$remaining = array_merge( $remaining, array_filter( array_unique( $fixture_ids['orders'] ), fn( $id ) => (bool) wc_get_order( $id ) ) );
$remaining = array_merge( $remaining, array_filter( $fixture_ids['coupons'], fn( $id ) => (bool) get_post( $id ) ) );
$results['fixtures_removed'] = ! $remaining;
echo wp_json_encode( $results, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE ) . PHP_EOL;
exit( isset( $results['failure'] ) || in_array( false, $results, true ) ? 1 : 0 );
