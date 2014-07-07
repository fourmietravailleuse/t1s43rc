<?php
/*
 * Plugin Name: Screenfeed Updater
 * Plugin URI: http://www.screenfeed.fr
 * Description: Updater for plugins provided directly from the screenfeed's server.
 * Version: 1.1.2
 * Author: Grégory Viguier
 * Author URI: http://www.screenfeed.fr/greg/
 * License: GPLv3
 * License URI: http://www.screenfeed.fr/gpl-v3.txt
 *
 * The filter 'sf_plugins_updates' waits for an array like this: array( '/absolute/path/to/plugin.php' => 'plugin.version', '/absolute/path/to/other_plugin.php' => 'other.plugin.version' )
 */

if( !defined( 'ABSPATH' ) )
	die( 'Cheatin\' uh?' );


/* !---------------------------------------------------------------------------- */
/* !	EXCLUDE SF PLUGINS FROM WP UPDATES										 */
/* ----------------------------------------------------------------------------- */

// Requests sent to WP servers

if ( !function_exists('sf_updates_exclude') ) :
add_filter( 'http_request_args', 'sf_updates_exclude', 5, 2 );

function sf_updates_exclude( $r, $url ) {
	if ( 0 !== strpos( $url, 'http://api.wordpress.org/plugins/update-check/' ) && 0 !== strpos( $url, 'https://api.wordpress.org/plugins/update-check/' ) )
		return $r;		// Not a plugin update request.

	$sf_plugins = apply_filters( 'sf_plugins_updates', array() );
	if ( !count($sf_plugins) )
		return $r;		// No SF plugins to remove.

	if ( empty($r['body']['plugins']) )
		return $r;		// Error?

	if ( is_serialized( $r['body']['plugins'] ) )	// WP < 3.7 (http)
		$plugins = unserialize( $r['body']['plugins'] );
	else											// WP 3.7+ (https)
		$plugins = json_decode( $r['body']['plugins'] );

	foreach ( $sf_plugins as $sf_plugin => $version ) {
		$sf_plugin = plugin_basename( $sf_plugin );

		if ( is_array($plugins->plugins) )
			unset( $plugins->plugins[ $sf_plugin ] );
		else
			unset( $plugins->plugins->$sf_plugin );													//pre_print_r($plugins->plugins,1);

		if ( is_object($plugins->active) ) {
			$plugins->active = array_values( (array) $plugins->active );							//pre_print_r('is_object',1);	pre_print_r($plugins->active,1);
			if ( false !== ($sf_plugin_pos = array_search( $sf_plugin, $plugins->active )) )
				unset( $plugins->active[ $sf_plugin_pos ] );
			$plugins->active = (object) $plugins->active;											//pre_print_r($sf_plugin_pos,1);	pre_print_r($plugins->active,1);
		}
		elseif ( is_array($plugins->active) && false !== ($sf_plugin_pos = array_search( $sf_plugin, $plugins->active )) ) {							//pre_print_r('is_array',1);	pre_print_r($plugins->active,1);
			unset( $plugins->active[ $sf_plugin_pos ] );
			$plugins->active = array_values( $plugins->active );									//pre_print_r($sf_plugin_pos,1);	pre_print_r($plugins->active,1);
		}
	}

	if ( is_serialized( $r['body']['plugins'] ) )
		$r['body']['plugins'] = serialize( $plugins );
	else
		$r['body']['plugins'] = json_encode( $plugins );

	return $r;
}
endif;


/* !---------------------------------------------------------------------------- */
/* !	CHECK SF UPDATES TWICE A DAY, OR EVERY TIME WE VISIT THE UPDATES PAGES	 */
/* ----------------------------------------------------------------------------- */

if ( !function_exists('sf_check_update') ) :
add_action( 'plugins_loaded', 'sf_check_update' );

function sf_check_update() {
	global $pagenow;
	if ( $pagenow == 'plugins.php' || $pagenow == 'update.php' || $pagenow == 'update-core.php' ) {
		add_filter( 'site_transient_update_plugins', 'noop_site_transient_update_plugins' );
	}
	else {
		$current = get_site_transient( 'update_sf' );
		$timeout = apply_filters( 'sf_check_update', 12 * HOUR_IN_SECONDS );
		if ( !empty( $current ) && $timeout > ( time() - $current ) )
			return;
		add_filter( 'site_transient_update_plugins', 'noop_site_transient_update_plugins' );
	}
}
endif;


if ( !function_exists('noop_site_transient_update_plugins') ) :
function noop_site_transient_update_plugins( $current ) {

	static $updates = null;

	if ( !is_object($current) )
		$current = new stdClass;
	if ( empty($current->response) || !is_array($current->response) )
		$current->response = array();

	if ( is_null($updates) ) {

		$updates    = array();
		$sf_plugins = apply_filters( 'sf_plugins_updates', array() );

		if ( !empty($sf_plugins) && is_array($sf_plugins) ) {

			$last_checked = !empty($current->last_checked) ? $current->last_checked : time();
			set_site_transient( 'update_sf', $last_checked );

			foreach ( $sf_plugins as $sf_plugin => $sf_version ) {
				$plugin_folder		= plugin_basename( dirname( $sf_plugin ) );
				$plugin_file		= basename( $sf_plugin );
				$request			= array( 'slug' => $plugin_folder, 'fields' => array('sections' => false) );
				$response			= sf_remote_plugin_infos( $request );

				if ( !is_wp_error( $response ) ) {
					$plugin = sf_remote_retrieve_body( $response );
					if ( empty($plugin->download_link) || empty($plugin->version) || version_compare( $plugin->version, $sf_version, '<=' ) ) {
						unset($current->response[$plugin_folder.'/'.$plugin_file]);
						continue;
					}

					$updates[$plugin_folder.'/'.$plugin_file] = (object) array(
						'slug'			=> $plugin_folder,
						'new_version'	=> $plugin->version,
						'url'			=> (!empty($plugin->homepage) ? esc_url($plugin->homepage) : ''),
						'package'		=> esc_url($plugin->download_link)
					);
				}
			}

		}

	}

	if ( count($updates) ) {
		$current->response = array_merge($current->response, $updates);
		set_site_transient( 'update_plugins', $current );
	}

	return $current;
}
endif;


/* !---------------------------------------------------------------------------- */
/* !	REPLACE THE REQUEST TO THE WP SERVERS WITH ONE TO OUR					 */
/* ----------------------------------------------------------------------------- */

if ( !function_exists('sf_pull_info') ) :
add_filter( 'plugins_api', 'sf_pull_info', 10, 3 );

function sf_pull_info( $res, $action, $args ) {
	if( $action == 'plugin_information' ) {
		$sf_plugins = apply_filters( 'sf_plugins_updates', array() );
		if ( !count($sf_plugins) )
			return $res;

		$sf_plugins = array_map('basename', array_map('dirname', array_keys($sf_plugins)));
		if ( in_array( $args->slug, $sf_plugins) )
			return sf_remote_retrieve_body( sf_remote_plugin_infos( $args ) );
	}
	return $res;
}
endif;


/* !---------------------------------------------------------------------------- */
/* !	UTILITIES																 */
/* ----------------------------------------------------------------------------- */

// !Call home

if ( !function_exists('sf_remote_plugin_infos') ) :
function sf_remote_plugin_infos( $request ) {
	return wp_remote_post(
		'http://www.screenfeed.fr/downloads/',
		array( 'timeout' => 30, 'body' => array( 'action' => 'plugin_information', 'request' => serialize( (array) $request ) ) )
	);
}
endif;


// !Deal with sf_remote_plugin_infos() result

if ( !function_exists('sf_remote_retrieve_body') ) :
function sf_remote_retrieve_body( $response ) {
	$error_msg = sprintf(
		__( 'An unexpected error occurred. Something may be wrong with screenfeed.fr or this server&#8217;s configuration. If you continue to have problems, please leave a message on <a href="%s">my blog</a>.', 'noop' ),
		'http://www.screenfeed.fr/blog/'
	);

	if ( is_wp_error( $response ) ) {
		return new WP_Error(
			'plugins_api_failed',
			$error_msg,
			$response->get_error_message()
		);
	}

	$response = wp_unslash( wp_remote_retrieve_body( $response ) );
	if ( is_serialized( $response ) )
		return (object) @unserialize( $response );

	return new WP_Error( 'plugins_api_failed', $error_msg, $response );
}
endif;


// For WP < 3.6
if ( !function_exists('wp_unslash') ) :
function wp_unslash( $value ) {
	return stripslashes_deep( $value );
}
endif;
/**/