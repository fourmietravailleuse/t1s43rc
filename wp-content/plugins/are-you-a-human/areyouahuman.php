<?php
/**
 * @package Are You a Human
 * @version 1.4.10
 */
/*
Plugin Name: Are You a Human
Plugin URI:  http://wordpress.org/extend/plugins/are-you-a-human/
Description: The Are You a Human PlayThru plugin replaces obnoxious CAPTCHAs with fun, simple games. Fight spam with fun!
Author: Are You a Human
Author URI: http://www.areyouahuman.com/
Version: 1.4.10
*/

/* TO DO:
 * Split the error notification and options page styles into two sheets
 * Allow users to customize error message
  Add a text domain
 * Validate the user's keys
 * Switch to Settings API for settings page
 */

define('AYAH_VERSION', '1.4.1');
define('AYAH_WEB_SERVICE_HOST', 'ws.areyouahuman.com');
define('PLUGIN_BASENAME', plugin_basename(__FILE__));
define('AYAH_PLUGIN_SLUG', 'are-you-a-human');
define('PLUGIN_DIR_PATH', plugin_dir_path(__FILE__));
require_once(PLUGIN_DIR_PATH . "includes/ayah.php");
require_once(PLUGIN_DIR_PATH . "includes/ayah_form_actions.php");
require_once(PLUGIN_DIR_PATH . "includes/ayah_functions.php");
require_once(PLUGIN_DIR_PATH . "includes/ayah_pages.php");

// Register a style sheet that can be loaded later with wp_enqueue_style
add_action('init', 'ayah_register_style');
add_action('login_enqueue_scripts', 'ayah_login_styles');

// Adds a AYAH Options page link to the Settings admin menu
add_action( 'admin_menu', 'ayah_add_admin_menu' );

// Registers the custom plugin action links
add_filter( 'plugin_action_links_' . PLUGIN_BASENAME, 'ayah_register_plugin_action_links', 10, 1);

// Registers custom plugin meta links
add_filter('plugin_row_meta', 'ayah_register_plugin_meta_links', 10, 2);

// Initialize our plugin on every init call
add_action('init', 'ayah_add_playthru');

// Reload PlayThru on contact form 7 send
add_filter( 'wpcf7_ajax_json_echo', 'ajax_json_echo_filter');

/**
 * Adds the playthru to the forms chosen in the options menu
 * This is achieved by attaching to the appropriate hooks
 * 
 * @link http://codex.wordpress.org/Plugin_API#Hooks.2C_Actions_and_Filters
 * @link http://codex.wordpress.org/Function_Reference/add_action
 */
function ayah_add_playthru() {
	ayah_check_for_other_plugins();
	ayah_add_admin_notice_action();

    $ayah_options = ayah_get_options();

    // If enable_comment_form is set in the options, attach to the comment hooks
    if( $ayah_options['enable_comment_form'] ) {
		add_action('comment_form_after_fields', 'ayah_comment_form_after');
		add_action('comment_form_logged_in_after', 'ayah_comment_form_after');
		add_action('comment_form', 'ayah_comment_form');
		add_filter('preprocess_comment', 'ayah_comment_post', 10, 1);
    }
    
	// If enable_register_form is set in the options, attach to the register hooks
    if( $ayah_options['enable_register_form'] ) {
        add_action('register_form', 'ayah_register_form');
        add_action('register_post', 'ayah_register_post', 10, 3);
    }

    // If enable_lost_password_form is set in the options, attach to the lost password hooks	
    if( $ayah_options['enable_lost_password_form'] ) {
        add_action('lostpassword_form', 'ayah_lost_password_form');
        add_action('lostpassword_post', 'ayah_lost_password_post');
    }
	
	// Registers the AYAH CF7 Actions if plugin is activated
	if (CF7_DETECTED) {
		require_once(PLUGIN_DIR_PATH . "includes/plugin-integration/contact-form-7/ayah_cf7.php");
		ayah_register_cf7_actions();
	}
	
	// Registers the AYAH GF Actions if plugin is activated
	if (GF_DETECTED) {
		require_once(PLUGIN_DIR_PATH . "includes/plugin-integration/gravity-forms/ayah_gf.php");
		ayah_register_gf_actions();
	}

    if (defined('BP_VERSION')) {
		require_once(PLUGIN_DIR_PATH . "includes/plugin-integration/buddypress/ayah_buddypress.php");
    	ayah_register_bp_actions();
	}
	
	// Deactivates the old AYAH CF7 extension if activated
	if (AYAHCF7_DETECTED) {
		require_once(ABSPATH . 'wp-admin/includes/plugin.php');
		deactivate_plugins('are-you-a-human-cf7-extension/are-you-a-human-cf7-extension.php');
	}
}

// TODO: Switching to the settings API will fix this bad function
/**
 *	Registers a hook that displays an admin notice if any keys are missing
 */
function ayah_add_admin_notice_action() {
	// Adds an admin notice to set the keys if not set
	if (ayah_is_key_missing()) {
		add_action('admin_notices', 'ayah_display_keys_notice');
	}
}

/**
 * Checks if certain plugins are active so we can add settings for them on the
 * options page
 */
function ayah_check_for_other_plugins() {
	require_once(ABSPATH . 'wp-admin/includes/plugin.php');
	define('CF7_DETECTED', is_plugin_active('contact-form-7/wp-contact-form-7.php'));
	define('AYAHCF7_DETECTED', is_plugin_active('are-you-a-human-cf7-extension/are-you-a-human-cf7-extension.php'));
	define('GF_DETECTED', is_plugin_active('gravityforms/gravityforms.php'));
}

/**
 * Registers all the actions and filters necessary to integrate PlayThru with CF7
 */
function ayah_register_cf7_actions() {
	// Register the AYAH CF7 shortcode
	ayahcf7_register_shortcode();
	
	// Register the AYAH CF7 validation function
	add_filter('wpcf7_validate_ayah', 'ayahcf7_validate', 10, 1);
	
	// Register the AYAH CF7 tag pane generator
	add_action('admin_init', 'ayahcf7_tag_generator');
}

/**
 * Registers all the actions and filters necessary to integrate PlayThru with GF
 */
function ayah_register_gf_actions() {
	add_filter('gform_add_field_buttons', 'ayahgf_add_button');
	add_filter('gform_field_type_title', 'ayahgf_add_field_title');
	add_filter('gform_field_validation', 'ayahgf_validate', 10, 4);
	add_filter("gform_field_input", "ayahgf_field", 10, 5);
}

/**
 * Registers the actions for BuddyPress
 */
function ayah_register_bp_actions() {
	$ayah_options = ayah_get_options();

	if ($ayah_options['enable_register_form']) {
		add_action('bp_before_registration_submit_buttons', 'ayah_register_form');
		add_action('bp_signup_validate', 'ayah_buddypress_register');
	}
}

/**
 * Adds a AYAH Options page link to the Settings admin menu
 * 
 * @link http://codex.wordpress.org/Function_Reference/add_options_page
 */
function ayah_add_admin_menu() {
	add_options_page( "Are You a Human Options", "Are You a Human", 'manage_options', AYAH_PLUGIN_SLUG, 'ayah_choose_options_page' );
}

/**
 * Registers the settings action link
 * 
 * @link http://thematosoup.com/development/add-action-meta-links-wordpress-plugins/
 */
function ayah_register_plugin_action_links($links) {

	$settings_link = '<a href="options-general.php?page='.AYAH_PLUGIN_SLUG.'">' . __('Settings', 'captcha') . '</a>';
	array_unshift( $links, $settings_link );

	return $links;
}

/**
 * Registers the settings, support, and feedback meta links
 * 
 * @link http://thematosoup.com/development/add-action-meta-links-wordpress-plugins/
 */
function ayah_register_plugin_meta_links($links, $file) {

	if ($file == PLUGIN_BASENAME) {

		$links[] = '<a href="options-general.php?page='.AYAH_PLUGIN_SLUG.'">' . __('Settings','captcha') . '</a>';
		$links[] = '<a href="http://support.areyouahuman.com" target="_blank">' . __('Support','captcha') . '</a>';
		$links[] = '<a href="http://www.areyouahuman.com/feedback">' . __('Feedback','captcha') . '</a>';
	}
	return $links;
}

/**
 * Reloads the iframe for situations where the page isn't submitted, like in cf7
 */
function ajax_json_echo_filter( $items ) {
	if ( ! is_array( $items['onSubmit'] ) )
	$items['onSubmit'] = array();

	$div_id = "AYAH" . $_REQUEST["session_secret"];
	
	$items['onSubmit'][] = '
		var div_id = document.getElementById("' . $div_id . '");
		var iframe_id = div_id.getElementsByTagName("iframe")[0].id;
		document.getElementById(iframe_id).src = document.getElementById(iframe_id).src;
	';
	
	return $items;
}

/**
 * Registers the stylesheet
 */
function ayah_register_style() {
    wp_register_style('AYAHStylesheet', plugins_url('css/ayah_styles.css', __FILE__));
}

function ayah_login_styles() {
	$action = ( isset( $_GET['action'] ) ) ? $_GET['action'] : '';
	$options = ayah_get_options();

	if ( ( $action == 'lostpassword' && $options['enable_lost_password_form'] ) || ( $action == 'register' && $options['enable_register_form'] ) ) {
		?><style type="text/css">#login{width: 418px !important;}</style><?php
	}
}