<?php
/** Submenu de categorias da navegação do cabeçalho. */

defined( 'ABSPATH' ) || exit;

/** Consulta as categorias da coleção sem incluir a categoria padrão do sistema. */
function ecommerce_joias_get_header_categories() {
	if ( ! taxonomy_exists( 'product_cat' ) ) {
		return array();
	}
	$categories = get_terms( array(
		'taxonomy' => 'product_cat',
		'hide_empty' => false,
		'exclude' => array( absint( get_option( 'default_product_cat' ) ) ),
		'orderby' => 'name',
		'order' => 'ASC',
	) );
	return is_wp_error( $categories ) ? array() : $categories;
}

/** Habilita abertura por ponteiro e fechamento por Escape no menu nativo. */
function ecommerce_joias_enqueue_header_categories() {
	$path = get_stylesheet_directory() . '/assets/js/header-categories.js';
	wp_enqueue_script( 'ecommerce-joias-header-categories', get_stylesheet_directory_uri() . '/assets/js/header-categories.js', array(), (string) filemtime( $path ), true );
}
add_action( 'wp_enqueue_scripts', 'ecommerce_joias_enqueue_header_categories', 30 );

/** Substitui apenas categorias do cabeçalho, preservando menus em outras áreas. */
function ecommerce_joias_filter_header_category_items( $items, $args ) {
	if ( 'nc-header__menu' !== $args->menu_class || ! taxonomy_exists( 'product_cat' ) ) {
		return $items;
	}
	$removed = array();
	foreach ( $items as $item ) {
		if ( 'taxonomy' === $item->type && 'product_cat' === $item->object ) {
			$removed[] = (int) $item->ID;
		}
	}
	// Remove também descendentes dos itens removidos para não deixar submenus órfãos.
	do {
		$count = count( $removed );
		foreach ( $items as $item ) {
			if ( in_array( (int) $item->menu_item_parent, $removed, true ) && ! in_array( (int) $item->ID, $removed, true ) ) {
				$removed[] = (int) $item->ID;
			}
		}
	} while ( count( $removed ) > $count );
	return array_values( array_filter( $items, function ( $item ) use ( $removed ) {
		return ! in_array( (int) $item->ID, $removed, true );
	} ) );
}
add_filter( 'wp_nav_menu_objects', 'ecommerce_joias_filter_header_category_items', 10, 2 );
