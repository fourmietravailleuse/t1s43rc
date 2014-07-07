<?php
/**
Setup du thème
*/
function creasit_after_setup_theme() {

	// Charge les locale
 	load_theme_textdomain('solution', get_template_directory() .'/languages');


	// Support des miniatures d'articles
	add_theme_support('post-thumbnails');

	// Custom header
	$args = array(
	'default-image'          => get_template_directory_uri(). '/images/logo.png',
	'header-text'            => false,
	'width'                  => 344,
	'height'                 => 106,
	'flex-height'            => false,
	);

	add_theme_support( 'custom-header', $args );


	// Déclaration des menus utilisés sur le site
	register_nav_menus(
	      array(
	      'main'    => __('Menu principal'),
	      'foot'    => __('Menu de pied de page'),
	      )
	);

	// Styles d'images
	add_image_size('little_preview', 55, 55, true);
	add_image_size('slider_home', 962, 372, true);
	add_image_size('carousel', 960, 960, true);
	
  

}
add_action( 'after_setup_theme', 'creasit_after_setup_theme' );



/**
Init widget
*/
if ( function_exists('register_sidebar') )
    register_sidebar();



/**
Inclusion des scripts et des styles
*/
add_action('wp_enqueue_scripts', 'creasit_add_my_stylesheet');
function creasit_add_my_stylesheet() {
	// Styles CSS
	global $wp_styles, $wp_scripts;

	wp_enqueue_style('solution', get_template_directory_uri().'/css/styles.css', array(), '1.0', 'screen, projection');


	wp_enqueue_script('scripts', get_template_directory_uri().'/js/scripts.js', array('jquery'), '1.0', true);
  wp_enqueue_script('addClassExterne', get_template_directory_uri().'/js/jquery.addClassExterne.js', array('jquery'), '2.2.0', true);
}



/**
Ajouter un script dans l'admin
*/
add_action('admin_enqueue_scripts', 'creasit_add_scripts_admin');
function creasit_add_scripts_admin() {
    // Scripts JS
    wp_enqueue_script('scripts_admin', get_template_directory_uri().'/js/scripts_admin.js', array('jquery'), '1.0', true);
}



/**
Supprimer les taxonomies des articles
*/

// Suppression de la métabox dans les articles
function creasit_suppression_tag_metabox() {
    remove_meta_box('tagsdiv-post_tag', 'post', 'side');
}
add_action('admin_menu', 'creasit_suppression_tag_metabox');

// Suppression de la colonne "Mots-clefs" sur la liste des articles
function creasit_suppression_tag_colonne($defaults) {
    unset($defaults['tags']);
    return $defaults;
}
add_filter( 'manage_posts_columns', 'creasit_suppression_tag_colonne');

// Suppression du menu "Mots-clefs"
function creasit_suppression_tag_menu() {
    global $submenu;
    unset($submenu['edit.php'][16]);
}
add_action('admin_head', 'creasit_suppression_tag_menu');

