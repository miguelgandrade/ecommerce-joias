<?php
/** Cria páginas locais editáveis sem sobrescrever conteúdo existente. */
if ( PHP_SAPI !== 'cli' || '1' !== getenv( 'NC_SETUP_SUPPORT' ) ) { exit( 1 ); }
$_SERVER['HTTP_HOST'] = 'localhost:8080';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/';
require '/var/www/html/wp-load.php';
if ( ! in_array( wp_parse_url( home_url(), PHP_URL_HOST ), array( 'localhost', '127.0.0.1' ), true ) ) { exit( 'Somente ambiente local.' ); }
$pages = array(
'ajuda' => array( 'Como podemos ajudar?', '' ),
'entrega-e-frete' => array( 'Entrega e frete', '<h2>Consulte as opções para seu endereço</h2><p>Informe seu CEP e endereço no carrinho ou na finalização da compra para consultar as opções disponíveis. Confira o valor e o prazo apresentados antes de confirmar o pedido.</p><h2>Acompanhe sua compra</h2><p>Consulte os detalhes em Meus pedidos ou no e-mail de confirmação. Quando houver rastreamento disponível, use o código e o endereço enviados pela loja.</p><h2>Precisa corrigir o endereço?</h2><p>Entre em contato com o número do pedido e os dados a corrigir. A equipe verificará se ainda é possível alterar o envio. Não há confirmação de alteração até o retorno do atendimento.</p>' ),
'trocas-e-devolucoes' => array( 'Trocas e devoluções', '<h2>Vamos orientar você</h2><p>Para solicitar uma troca ou devolução, fale com a equipe e informe o número do pedido, a peça e o motivo da solicitação. Se houver avaria ou diferença em relação ao pedido, fotos podem ajudar na análise.</p><h2>Antes de enviar uma peça</h2><p>Aguarde as instruções de atendimento sobre endereço, embalagem e forma de envio. Preserve a peça, seus acessórios e os documentos da compra.</p><h2>Como acompanhar</h2><p>Consulte o andamento pelo canal em que abriu a solicitação. Condições, custos e prazos aplicáveis serão informados pela equipe, respeitando os direitos do consumidor.</p>' ),
'cancelamentos-e-reembolsos' => array( 'Cancelamentos e reembolsos', '<h2>Quero cancelar uma compra</h2><p>Confira o pedido em Minha conta e entre em contato com a equipe. Informe o número do pedido e peça a confirmação do cancelamento. A orientação depende da etapa em que a compra se encontra.</p><h2>Cancelamento e reembolso</h2><p>Um pedido cancelado não significa que o valor já foi devolvido. Se houve pagamento, solicite a confirmação do reembolso e acompanhe também o meio de pagamento utilizado.</p><h2>Minha compra já foi enviada</h2><p>Fale com o atendimento para receber as instruções adequadas ao seu caso antes de recusar ou devolver uma entrega.</p>' ),
'cuidados-com-as-pecas' => array( 'Cuidados com as peças', '<h2>Pequenos cuidados, todos os dias</h2><ul><li>Evite contato com perfumes, cosméticos e produtos de limpeza.</li><li>Guarde as peças secas, separadas e protegidas de atrito.</li><li>Evite tração em correntes, fechos e partes delicadas.</li><li>Use um pano macio e seco, seguindo as instruções específicas da peça.</li></ul><h2>Cada material pede atenção</h2><p>Confira a composição e as orientações na página do produto. Não use produtos químicos, abrasivos ou métodos caseiros sem confirmar a compatibilidade com o material.</p>' ),
'guia-de-tamanhos' => array( 'Guia de tamanhos', '<h2>Encontre uma medida confortável</h2><p>Confira as dimensões e as opções disponíveis na página da peça. Compare com uma peça sua de tamanho confortável e observe a unidade de medida informada.</p><details><summary>Anéis</summary><p>Compare o diâmetro interno de um anel que já fique confortável. Não converta diâmetro em numeração sem a tabela específica do produto; numerações podem variar.</p></details><details><summary>Pulseiras</summary><p>Meça a circunferência do pulso e confira o comprimento, o fecho e a possibilidade de ajuste da peça. A folga ideal depende do modelo e da sua preferência.</p></details><details><summary>Colares</summary><p>Use uma fita para visualizar o comprimento informado na descrição e considere a altura do pingente e os extensores, quando houver.</p></details><h2>Ainda ficou em dúvida?</h2><p>Consulte a equipe antes de comprar. Não há uma tabela universal aplicável a todas as peças da loja.</p>' ),
'pagamento-nao-concluido' => array( 'Pagamento não concluído', '<p>Esta página traz orientações gerais e não confirma o status de uma compra.</p><h2>Confira antes de tentar novamente</h2><p>Acesse Meus pedidos ou o link recebido no e-mail da compra e confira o status e as instruções de pagamento.</p><ul><li>Se o pedido permitir pagamento, use a opção disponível nos detalhes dele.</li><li>Se houver cobrança no seu banco e o pedido não estiver confirmado, consulte o atendimento antes de pagar novamente.</li><li>Se o código ou prazo de pagamento expirou, confira as opções do pedido com a equipe.</li></ul>' ),
'pedido-cancelado' => array( 'Pedido cancelado', '<p>Esta página traz orientações gerais. Para confirmar se sua compra foi cancelada, consulte os detalhes do pedido.</p><h2>E se eu já paguei?</h2><p>Cancelamento não confirma reembolso automático. Fale com o atendimento para verificar se houve pagamento e acompanhar a devolução do valor, quando aplicável.</p><h2>Quero comprar novamente</h2><p>Confira o status do pedido anterior antes de iniciar outra compra. Os preços, o estoque e as condições disponíveis serão apresentados na nova compra.</p>' ),
'manutencao' => array( 'Voltamos em breve', '<p>Quando a loja estiver temporariamente indisponível, tente acessar novamente mais tarde. Se já fez uma compra e precisa de informações, use os canais de atendimento abaixo.</p>' ),
);
$ids = array();
foreach ( $pages as $slug => $data ) {
	$page = get_page_by_path( $slug );
	if ( $page ) { $ids[ $slug ] = $page->ID; echo "Preservada: $slug\n"; continue; }
	$id = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_name' => $slug, 'post_title' => $data[0], 'post_content' => $data[1], 'meta_input' => array( '_wp_page_template' => 'page-ajuda.php' ) ), true );
	if ( is_wp_error( $id ) ) { throw new RuntimeException( $id->get_error_message() ); }
	$ids[ $slug ] = $id;
	echo "Criada: $slug ($id)\n";
}
$help = get_post( $ids['ajuda'] );
{
	$content = '<p>Encontre orientações para escolher sua peça, acompanhar sua compra e resolver imprevistos.</p><div class="nc-support-grid">';
	foreach ( $pages as $slug => $data ) {
		if ( in_array( $slug, array( 'ajuda', 'manutencao' ), true ) ) { continue; }
		$content .= '<a class="nc-support-card" href="' . esc_url( get_permalink( $ids[ $slug ] ) ) . '"><h2>' . esc_html( $data[0] ) . '</h2><p>Ver orientações →</p></a>';
	}
	$content .= '</div>';
	if ( '' === trim( $help->post_content ) || $help->post_content === $content ) {
		// Bloco HTML impede wpautop de quebrar links com títulos e parágrafos.
		wp_update_post( array( 'ID' => $help->ID, 'post_content' => '<!-- wp:html -->' . $content . '<!-- /wp:html -->' ) );
	}
}
foreach ( array( 'delivery' => 'entrega-e-frete', 'returns' => 'trocas-e-devolucoes', 'care' => 'cuidados-com-as-pecas', 'sizes' => 'guia-de-tamanhos' ) as $key => $slug ) {
	if ( ! get_theme_mod( 'ecommerce_joias_footer_page_' . $key ) ) { set_theme_mod( 'ecommerce_joias_footer_page_' . $key, $ids[ $slug ] ); }
}
foreach ( $ids as $slug => $id ) { echo $slug . ': ' . get_permalink( $id ) . "\n"; }
