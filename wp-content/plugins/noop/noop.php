<?php
/*
 * Plugin Name: Noop
 * Plugin URI: http://www.screenfeed.fr
 * Description: Noop is a framework to use within other plugins or themes, it does nothing by itself. It's aim is to provide a secure and robust foundation, and also powerful tools, to build great settings pages and manage options.
 * Version: 1.0.8
 * Author: Grégory Viguier
 * Author URI: http://www.screenfeed.fr/greg/
 * License: GPLv3
 * License URI: http://www.screenfeed.fr/gpl-v3.txt
 * Text Domain: noop
 * Domain Path: /languages
 * Provides: Noop
 *
 * If you use Noop as a Must-Use plugin, place the "noop" folder inside your "mu-plugins" folder, then, place this file directly inside your "mu-plugins" folder too. The updater won't work anymore though.
 */

if( !defined( 'ABSPATH' ) )
	die( 'Cheatin\' uh?' );

// 3.3+: All is OK. 3.1+: OK but tinyMCE won't work (too lazy to fallback to the old editor).
if ( version_compare( $GLOBALS['wp_version'], '3.1', '<' ) ) {
	return;
}

define( 'NOOP_VERSION',	'1.0.8' );
define( 'NOOP_FILE',	__FILE__ );

if ( realpath( WPMU_PLUGIN_DIR ) && strpos( strtolower( realpath( NOOP_FILE ) ), strtolower( realpath( WPMU_PLUGIN_DIR ) ) ) === 0 ) {

	define( 'NOOP_DIR',	WPMU_PLUGIN_DIR.'/noop/' );					// Slashed
	define( 'NOOP_URL',	plugin_dir_url( NOOP_DIR.'index.php' ) );	// Slashed

} else {

	define( 'NOOP_DIR',	plugin_dir_path( NOOP_FILE ) );	// Slashed
	define( 'NOOP_URL',	plugin_dir_url( NOOP_FILE ) );	// Slashed

	if ( is_admin() ) {

		// Updater
		if ( !function_exists('sf_check_update') ) {
			include( NOOP_DIR . 'inc/updater.inc.php' );
		}

		add_filter( 'sf_plugins_updates', 'noop_update' );
		function noop_update( $plugins = array() ) {
			$plugins[NOOP_FILE] = NOOP_VERSION;
			return $plugins;
		}

	}

}
/**/