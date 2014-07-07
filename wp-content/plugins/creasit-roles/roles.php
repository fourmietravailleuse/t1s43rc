<?php
/*
Plugin Name: Creasit Rôles
Description: Gestion des rôles
Author: Creasit
Version: 1.0
*/


/**

Liens utile :
- http://codex.wordpress.org/Roles_and_Capabilities

*/



/**
I18N
*/
add_action( 'plugins_loaded', 'creasit_lang_init' );
function creasit_lang_init() {
	load_plugin_textdomain( 'default', false, basename( dirname( __FILE__ ) ) . '/languages' );
}


/**
Création du nouveau rôle
*/
register_activation_hook( __FILE__, 'creasit_ajout_role' );

function creasit_ajout_role() {
	// Creates a Owner User Role (editor level, with super-powers)
	if ( $editor = get_role('editor') ) {
		/* remove the unnecessary roles */
		remove_role('subscriber');
		remove_role('editor');
		remove_role('author');
		remove_role('contributor');

		remove_role( 'webmestre' );
		add_role( 'webmestre', 'Webmestre', $editor->capabilities );

		$webmestre = get_role('webmestre');
		// Can manipulate users
		$webmestre->add_cap( 'list_users' );
		

		$webmestre->add_cap( 'edit_theme_options' );
		$webmestre->add_cap( 'manage_options' );

		// Gestion des plugins dans la sidebar
		$webmestre->add_cap( 'wysija_newsletters' );


	}
}


/**
Supprimer des sous-menus inutiles 
*/

function creasit_menus() {
	global $menu;
	global $submenu;
	global $current_user;

    $user_role = $current_user->roles[0];
    if($user_role != 'administrator') {

		remove_action('admin_bar_menu', 'wpseo_admin_bar_menu',95); // Supprimer le menu "SEP by Yoast"
    	remove_menu_page('wpseo_dashboard'); // Supprimer le menu "SEO by Yoast"
    	remove_menu_page('edit.php?post_type=acf'); // Supprimer le menu "ACF"
    	remove_menu_page('gf_edit_forms'); // Supprimer le menu "Formulaire" de Gravity Forms
    	remove_submenu_page('post-new.php?post_type=page-systeme', 'edit.php?post_type=page-systeme'); // Supprimer le menu "Formulaire" de Gravity Forms

		unset($menu[25]); // Supprimer le menu "Commentaires"

		unset($menu[80]); // Supprimer le menu "Réglages"
		unset($submenu['options-general.php']); // Supprime les sous-menus de "Réglages"

		// Change le menu "Apparence" en "Menus"
		$menu[60] = array( __('Menus'), 'edit_theme_options', 'nav-menus.php', '', 'menu-top menu-icon-appearance', 'menu-appearance', 'dashicons-menu' ); 

		// Change le menu "Utilisateurs" en "Votre profil"
		$menu[70] = array( __('Profil'), 'edit_posts', 'profile.php', '', 'menu-top menu-icon-users', 'menu-users', 'dashicons-admin-users' ); 

		// Change le menu "Outils" en "Options du site"
		$menu[75] = array( __('Options du site'), 'edit_posts', 'themes.php?page=creasit-theme-option', '', 'menu-top menu-icon-tools', 'menu-tools', 'dashicons-admin-tools' );

	}

	// Supprimer le menu "Media"
	unset($menu[10]); 
	add_menu_page( 'Médias', 'Médias', 'manage_options', 'upload.php?page=mla-menu', '', 'dashicons-format-image', 10 );
	add_submenu_page( 'upload.php?page=mla-menu', 'Ajouter', 'Ajouter', 'manage_options', 'media-new.php', '' );
	add_submenu_page( 'upload.php?page=mla-menu', 'Catégories', 'Catégories', 'manage_options', 'edit-tags.php?taxonomy=attachment_category&post_type=attachment', '' );	


	// Change le menu "MailPoet" en "Newsletters"
	$menu[50] = array( __('Newsletters'), 'wysija_newsletters', 'wysija_campaigns', '', 'menu-top toplevel_page_wysija_campaigns', 'toplevel_page_wysija_campaigns', 'div' ); 


}
add_action('admin_menu', 'creasit_menus', 9999);






/**
Un webmestre ne peut pas donner les droits admin
*/
function creasit_filter_get_editable_roles( $editable_roles ) {
    global $pagenow;
    global $current_user;

    $user_role = $current_user->roles[0];
    if($user_role != 'administrator') {

	    if ( 'user-new.php' == $pagenow || 'user-edit.php' == $pagenow ) {
	    	// Suppression du rôle administrateur dans la liste des rôles
	        unset( $editable_roles['administrator'] );
	    }
	}
    return $editable_roles;

}
add_filter( 'editable_roles', 'creasit_filter_get_editable_roles' );






/**
Un webmeste ne peut plus supprimer de compte administrateur via la liste des utilisateurs
*/
function creasit_unset_button_remove_user( $actions, $user_object ) {
	global $current_user;

    $user_role = $current_user->roles[0];
    // Si je suis pas admin
    if($user_role != 'administrator') {
    	// Si cet utilisateur est un admin
	    if($user_object->roles[0] == 'administrator') {
	    	// Suppression du bouton "supprimer"
	    	unset($actions['delete']); 
	    }
	}
    return $actions;
}
add_filter( 'user_row_actions', 'creasit_unset_button_remove_user', 10, 2 );





// Partie TONY

/**
Suppresion des différents onglets/liens dans la barre admin
*/

// Suppression du sous menu dans le logo wordpress

function edit_admin_bar() {
	global $current_user;
    global $wp_admin_bar;		

	$user_role = $current_user->roles[0];
	// Si je suis pas admin
	if($user_role != 'administrator') {
	    $wp_admin_bar->remove_menu('about');
	    $wp_admin_bar->remove_menu('wporg');
	    $wp_admin_bar->remove_menu('documentation');
	    $wp_admin_bar->remove_menu('support-forums');
	    $wp_admin_bar->remove_menu('feedback');
	    $wp_admin_bar->remove_menu('view-site');
	    $wp_admin_bar->remove_menu('site-name');
	    $wp_admin_bar->remove_menu('updates');
	    $wp_admin_bar->remove_menu('comments');
	    $wp_admin_bar->remove_menu('wpseo-menu');
	    $wp_admin_bar->remove_menu('my-account');
	    $wp_admin_bar->remove_menu('wp-logo');
	    $wp_admin_bar->remove_menu('new-content');
	} else {
		$wp_admin_bar->remove_menu('comments');
		$wp_admin_bar->remove_menu('about');
	    $wp_admin_bar->remove_menu('wporg');
	    $wp_admin_bar->remove_menu('documentation');
	    $wp_admin_bar->remove_menu('support-forums');
	    $wp_admin_bar->remove_menu('feedback');
	    if (is_admin()){
	    	$wp_admin_bar->remove_menu('site-name');
	    }
	    $wp_admin_bar->remove_menu('my-account');
	    $wp_admin_bar->remove_menu('wp-logo');
	}
}
add_action('wp_before_admin_bar_render', 'edit_admin_bar'); 


/**
Ajout du sous-menu "Contacter le support", du lien "Creasit" et du logo Creasit
*/

function add_menu_admin_bar($wp_admin_bar) {
	// Ajout menu creasit
   $args = array(
   	'title' => 'Creasit',
    'id' => 'creasit',
    'href' => '',
    'meta' => array(
            'target' => '',
            'title' => ''
        )
   );
   $wp_admin_bar->add_node( $args );
   // Ajout lien contact support
   $args = array(
    'title' => __('Contacter le support'),
    'id' => 'contact-support',
    'href' => 'http://creasit.fr/formulaire-de-support',
    'parent' => 'creasit',
    'meta' => array(
            'class' => 'logo-admin-bar',
            'target' => '_blank'
    )
   );
   $wp_admin_bar->add_node( $args );
   // Ajout lien creasit
   $args = array(
    'title' => __('Creasit'),
    'id' => 'site-web',
    'href' => 'http://creasit.fr',
    'parent' => 'creasit',
    'meta' => array(
            'target' => '_blank',
    )
   );
   $wp_admin_bar->add_node( $args );
   // Ajout lien se déconnecter
   $args = array(
    'title' => __('Se déconnecter'),
    'id' => 'logout',
    'parent' => 'top-secondary',
   );
   $wp_admin_bar->add_node( $args );
 
}
add_action('admin_bar_menu', 'add_menu_admin_bar', 1);


/**
Ajout de l'onglet "Journal de bord" et "Voir mon site"
*/

function make_parent_node( $wp_admin_bar ) {

	global $current_user;

	$user_role = $current_user->roles[0];

	if(is_admin()) {

		$args = array(
			'id'     => 'voir-site',     
			'title'  => 'Voir mon site', 
			'href' => network_site_url( '/' ),
			'parent' => false,
			'meta' => array(
	            'target' => '_blank'
	        )

		);
		$wp_admin_bar->add_node( $args );

	}


	// Si je suis pas admin
	if($user_role != 'administrator') {

		// Si ce n'est pas le backoffice

		if (!is_admin()){
		  $args = array(
		    'id'     => 'dashboard',     
		    'title'  => 'Tableau de bord', 
		    'parent' => false,
		  );
		  $wp_admin_bar->add_node( $args );

		} else {
			// Ajout menu Ajouter
			$args = array(
				'title' => '<span style="font-size: 18px; padding: 4px 0; margin-right: 6px; color: #999;">✚</span><span style="float: right;" class="ab-label">Ajouter</span>',
				'id' => 'new-content-creasit',
				'href' => admin_url().'post-new.php',
				'meta' => array(
					'title' => __('Ajouter', 'solution')
				)
			);
			$wp_admin_bar->add_node( $args );

			// Ajout menu actu
			$args = array(
				'title' => __('Actualité', 'solution'),
				'id' => 'new-pos-creasit',
				'parent' => 'new-content-creasit',
				'href' => admin_url().'post-new.php',
			);
			$wp_admin_bar->add_node( $args );

			// Ajout menu media
			$args = array(
				'title' => __('Fichier média', 'solution'),
				'id' => 'new-media-creasit',
				'parent' => 'new-content-creasit',
				'href' => admin_url().'media-new.php',
			);
			$wp_admin_bar->add_node( $args );

			// Ajout menu album
			$args = array(
				'title' => __('Album', 'solution'),
				'id' => 'new-album-creasit',
				'parent' => 'new-content-creasit',
				'href' => admin_url().'post-new.php?post_type=albums',
			);
			$wp_admin_bar->add_node( $args );

			// Ajout menu page
			$args = array(
				'title' => __('Page', 'solution'),
				'id' => 'new-page-creasit',
				'parent' => 'new-content-creasit',
				'href' => admin_url().'post-new.php?post_type=page',
			);
			$wp_admin_bar->add_node( $args );


		}

	}
}

add_action( 'admin_bar_menu', 'make_parent_node', 40 );

/**
Modification du logo
*/

function custom_admin_logo() {
    echo '
        <style type="text/css">
        #wp-admin-bar-creasit > .ab-item  {background: url(' . get_bloginfo('stylesheet_directory') . '/images/logo-admin-creasit.png) no-repeat left 8px top 6px !important;background-position: 0 0;color:rgba(0, 0, 0, 0)!important;height:26px;width:20px;}
        #wp-admin-bar-creasit:hover > .ab-item {background: url(' . get_bloginfo('stylesheet_directory') . '/images/logo-admin-creasit.png) #333 no-repeat left 7px bottom 6px !important;background-position: 0 0;color:rgba(0, 0, 0, 0)!important;}
        </style>
        ';
}

add_action('wp_before_admin_bar_render', 'custom_admin_logo');



/**
Redirection si le webmestre accède à une page dont il n'a pas les droits
*/

function creasit_webmestre_redirection_page(){
	global $current_user;

	$user_role = $current_user->roles[0];

	$cur_url = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

	if ($user_role == 'webmestre'){

		if($cur_url == admin_url('themes.php')){
			wp_redirect(admin_url(), 301);
			exit;
		} else if ($cur_url == admin_url('widgets.php')){
			wp_redirect(admin_url(), 301);
			exit;
		} else if ($cur_url == admin_url('customize.php')){
			wp_redirect(admin_url(), 301);
			exit;
		} else if ($cur_url == admin_url('tools.php')){
			wp_redirect(admin_url(), 301);
			exit;
		} else if ($cur_url == admin_url('widgets.php')){
			wp_redirect(admin_url(), 301);
			exit;
		} else if ($cur_url == admin_url('options-general.php')){
			wp_redirect(admin_url(), 301);
			exit;
		} else if ($cur_url == admin_url('options-writing.php')){
			wp_redirect(admin_url(), 301);
			exit;
		} else if ($cur_url == admin_url('options-reading.php')){
			wp_redirect(admin_url(), 301);
			exit;
		} else if ($cur_url == admin_url('options-discussion.php')){
			wp_redirect(admin_url(), 301);
			exit;
		} else if ($cur_url == admin_url('options-media.php')){
			wp_redirect(admin_url(), 301);
			exit;
		} else if ($cur_url == admin_url('options-permalink.php')){
			wp_redirect(admin_url(), 301);
			exit;
		} else if ($cur_url == admin_url('edit.php?post_type=acf')){
			wp_redirect(admin_url(), 301);
			exit;
		} 
	}
}
add_action('admin_init','creasit_webmestre_redirection_page');


