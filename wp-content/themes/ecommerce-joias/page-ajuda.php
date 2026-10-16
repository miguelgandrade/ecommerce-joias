<?php
/** Template Name: Ajuda e orientações NC */
defined( 'ABSPATH' ) || exit;
get_header();
?>
<main class="nc-support" id="primary">
<?php while ( have_posts() ) : the_post(); ?>
	<nav class="nc-support-breadcrumb" aria-label="Você está aqui"><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Início</a> / <a href="<?php echo esc_url( ecommerce_joias_support_url() ); ?>">Ajuda</a></nav>
	<header class="nc-support-heading"><p class="nc-support-eyebrow">Cuidado em cada detalhe</p><h1><?php the_title(); ?></h1></header>
	<div class="nc-support-content"><?php the_content(); ?></div>
	<?php if ( is_page( 'manutencao' ) ) : ?><div class="nc-support-actions"><a class="nc-support-button" href="<?php echo esc_url( home_url( '/' ) ); ?>">Tentar acessar a loja</a></div><?php endif; ?>
	<aside class="nc-support-contact"><h2>Conte com a nossa equipe</h2><p>Tenha o número do pedido em mãos, se já comprou. Use os canais abaixo para consultar informações sobre sua compra.</p>
	<div class="nc-support-actions">
	<?php $phone = preg_replace( '/\D/', '', get_theme_mod( 'ecommerce_joias_footer_whatsapp', '' ) ); $email = sanitize_email( get_theme_mod( 'ecommerce_joias_footer_email', '' ) ); ?>
	<?php if ( strlen( $phone ) >= 10 && strlen( $phone ) <= 15 ) : ?><a class="nc-support-button" href="<?php echo esc_url( 'https://wa.me/' . $phone ); ?>">Falar pelo WhatsApp</a><?php endif; ?>
	<?php if ( $email ) : ?><a class="nc-support-button nc-support-button--outline" href="<?php echo esc_url( 'mailto:' . $email ); ?>">Enviar e-mail</a><?php endif; ?>
	<?php if ( function_exists( 'wc_get_page_id' ) && wc_get_page_id( 'myaccount' ) > 0 ) : ?><a class="nc-support-button nc-support-button--outline" href="<?php echo esc_url( wc_get_account_endpoint_url( 'orders' ) ); ?>">Meus pedidos</a><?php endif; ?>
	</div>
	<?php if ( ! $phone && ! $email ) : ?><p>Os canais diretos de atendimento ainda não foram informados pela loja.</p><?php endif; ?>
	</aside>
<?php endwhile; ?>
</main>
<?php get_footer(); ?>
