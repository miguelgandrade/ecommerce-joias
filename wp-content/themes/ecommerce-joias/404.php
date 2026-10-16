<?php
/** Página não encontrada: mantém o status HTTP 404 do WordPress. */
defined( 'ABSPATH' ) || exit;
get_header();
?>
<main class="nc-support nc-support-state" id="primary">
	<div class="nc-support-number" aria-hidden="true">404</div>
	<p class="nc-support-eyebrow">Página não encontrada</p>
	<h1>Vamos encontrar seu próximo brilho?</h1>
	<p>Não encontramos esta página. O endereço pode ter mudado ou o link pode estar incompleto.</p>
	<?php ecommerce_joias_support_search(); ?>
	<div class="nc-support-actions">
		<a class="nc-support-button" href="<?php echo esc_url( ecommerce_joias_support_shop_url() ); ?>">Explorar a coleção</a>
		<a class="nc-support-button nc-support-button--outline" href="<?php echo esc_url( home_url( '/' ) ); ?>">Voltar ao início</a>
	</div>
	<p>Precisa de ajuda? <a href="<?php echo esc_url( ecommerce_joias_support_url() ); ?>">Fale com a nossa equipe.</a></p>
</main>
<?php get_footer(); ?>
