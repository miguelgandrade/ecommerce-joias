<?php
/** Busca geral; a busca de produtos mantém o catálogo do WooCommerce. */
defined( 'ABSPATH' ) || exit;
get_header();
?>
<main class="nc-support" id="primary">
<h1>Resultados da busca</h1>
<?php if ( have_posts() ) : ?>
	<?php ecommerce_joias_support_search(); ?>
	<?php while ( have_posts() ) : the_post(); ?><article class="nc-support-order"><h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2><?php the_excerpt(); ?></article><?php endwhile; ?>
	<?php the_posts_pagination(); ?>
<?php else : ecommerce_joias_support_empty(); endif; ?>
</main>
<?php get_footer(); ?>
