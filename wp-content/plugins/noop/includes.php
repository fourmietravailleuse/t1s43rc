<?php
if( !defined( 'ABSPATH' ) )
	die( 'Cheatin\' uh?' );

if ( !class_exists('Noop') )
	include( NOOP_DIR . 'libs/class-noop.php' );


if ( !function_exists('noop_includes') ) :
function noop_includes( $args ) {

	if ( !class_exists('Noop_Utils') ) {
		include( NOOP_DIR . 'libs/class-noop-utils.php' );
	}

	if ( !empty( $args['option_group'] ) ) {
		if ( !class_exists('Noop_i18n') ) {
			include( NOOP_DIR . 'libs/class-noop-i18n.php' );
		}
		if ( !class_exists('Noop_Options') ) {
			include( NOOP_DIR . 'libs/class-noop-options.php' );
		}
	}

	if ( is_admin() && defined('DOING_AJAX') && DOING_AJAX ) {
		if ( !function_exists('wp_ajax_noop_find_posts') ) {
			include( NOOP_DIR . 'inc/admin-ajax.inc.php' );
		}
	}
	elseif ( is_admin() ) {
		if ( !class_exists('Noop_Admin') ) {
			include( NOOP_DIR . 'libs/class-noop-admin.php' );
		}
	}

	// !Intanciate Noop

	$noop = Noop::get_instance( $args );

	// !Include the class Noop_Settings if we display the settings page

	if ( !class_exists('Noop_Settings') && !is_null($noop) && $noop->is_instance_settings_page() )
		include( NOOP_DIR . 'libs/class-noop-settings.php' );

	return $noop;
}
endif;


if ( !function_exists('noop_uninstall') ) :
function noop_uninstall( $args ) {

	if ( !class_exists('Noop_Options') )
		include( NOOP_DIR . 'libs/class-noop-options.php' );

	Noop_Options::get_instance( $args )->uninstall();
}
endif;
/**/