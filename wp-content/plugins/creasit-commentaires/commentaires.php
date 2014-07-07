<?php
/*
Plugin Name: Creasit Commentaire
Description: Suppression des commentaires
Author: Creasit
Version: 1.0
*/


/**

Liens utile :


*/



/**
I18N
*/
add_action( 'plugins_loaded', 'creasit_commentaire_lang_init' );
function creasit_commentaire_lang_init() {
	load_plugin_textdomain( 'default', false, basename( dirname( __FILE__ ) ) . '/languages' );
}



/**
Cacher le widget "commentaire"
*/
add_action('wp_dashboard_setup', 'creasit_suppression_widget_commentaire_dashboard' );    
function creasit_suppression_widget_commentaire_dashboard() {    
    global $wp_meta_boxes;
 
    // Tableau de bord général
    unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_recent_comments']);
}



/**
Supprimer le menu "Commentaires"
*/
function creasit_menu_commentaire() {

	global $menu;

	unset($menu[25]);

}
add_action('admin_menu', 'creasit_menu_commentaire', 9999);

function creasit_webmestre_redirection_commentaire(){
	global $current_user;
	$user_role = $current_user->roles[0];

	$cur_url = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

	// if ($user_role == 'webmestre'){

		if($cur_url == admin_url('edit-comments.php')){
			wp_redirect(admin_url(), 301);
			exit;
		}
	// }
}
add_action('admin_init','creasit_webmestre_redirection_commentaire');



/**
Cacher le nombre de commentaire dans le widget du dashboard
*/
function creasit_commentaire_count() {
	$blog_url = get_bloginfo('url');
	$templ_url = get_bloginfo('template_url');
	echo '<style type="text/css">li.comment-count {display:none;}</style>';
}
add_action('admin_head', 'creasit_commentaire_count');


/**
Desactiver les commentaires sur toutes les pages par défaut
*/

add_filter('comments_open', 'creasit_commentaires_fermes', 10, 2);

function creasit_commentaires_fermes( $open, $post_id ) {
$post = get_post( $post_id );
$open = false;
return $open;
}