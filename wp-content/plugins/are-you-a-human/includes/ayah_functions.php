<?php

function ayah_load_library() {
	$options = ayah_get_options();
	
    $ayah = new AYAH(array( 'publisher_key' => $options['publisher_key'],
                            'scoring_key' => $options['scoring_key'],
                            'web_service_host' => AYAH_WEB_SERVICE_HOST
                ));
    return $ayah;
}

function ayah_choose_options_page() {
    // Used for testing/debugging. Clears the AYAH settings from the database
    if ( isset( $_POST['ayah_clear_options'] ) && $_POST['ayah_clear_options'] == 'true') {
        ayah_delete_options();
    }

	// TODO: The following code isn't very intuitive/clear. It works for now,
	// but this section should be considered for a rewrite
	
    // Get which page to display based on the form post. If a form was not posted
	// then the default case will select a page to display
	$action = ( isset( $_POST['ayah']['action'] ) ) ? $_POST['ayah']['action'] : '';
    switch ( $action ) {
        case 'install':
            ayah_install_plugin();
            break;
        case 'upgrade':
            ayah_upgrade_plugin();
            break;
        case 'settings':
            ayah_update_settings();
            break;
        default:
            $action = ayah_check_for_upgrade_or_install();
            break;
    }
	
	// Display the settings page
    ayah_get_options_page($action);
}

function ayah_display_keys_notice() {
	wp_enqueue_style('AYAHStylesheet');
	$url = admin_url("options-general.php?page=".AYAH_PLUGIN_SLUG);
	$error_message = "<div class='ayah-error-text'>You've enabled the <strong>Are You a Human</strong> plugin, but some of your keys appear to be missing.</div> <a href='" . $url .  "' class='ayah-error-button'>Enter Keys &raquo;</a>";
	echo "<div class='ayah-error'>" . $error_message . "</div>";
}

// TODO: Change when settings api is implemented
function ayah_is_key_missing() {
	if (array_key_exists('ayah', $_POST)) {
		$options = ayah_get_settings_post();
	} else {
		$options = ayah_get_options();
	}

	if ( $options['publisher_key'] == '' ||  $options['scoring_key'] == '' ) {
		return true;
	}
}

function ayah_update_settings() {
    // This does the same thing as install right now
    ayah_install_plugin();
}

/**
 * Get the settings from the install form and store them
 */
function ayah_install_plugin() {
    $options = ayah_get_settings_post();
    ayah_set_options($options);	
}

/**
 * Increase the version number in the stored options
 */
function ayah_upgrade_plugin() {
    $options = ayah_get_options();
    $options['version'] = AYAH_VERSION;
    
    ayah_set_options($options);
}

/**
 * Deletes the legacy plugin options and stores the new options
 */
function ayah_upgrade_legacy_plugin() {
    
    // Get the previous settings
    $ayah_options = ayah_get_options();
    $pub_key = $ayah_options['ayah_publisher_key'];
    $scoring_key = $ayah_options['ayah_scoring_key'];
    $enable_register_form = $ayah_options['ayah_register_form'];
    $enable_lost_password_form = $ayah_options['ayah_lost_password_form'];
    $enable_comment_form = $ayah_options['ayah_comments_form'];
    $hide_registered_users = $ayah_options['ayah_hide_register'];
    
    // Clear the options out
    ayah_delete_options();
    
    // Set new options
    $options = array(   'version' => AYAH_VERSION,
                        'publisher_key' => $pub_key,
                        'scoring_key' => $scoring_key,
                        'enable_register_form' => $enable_register_form,
                        'enable_lost_password_form' => $enable_lost_password_form,
                        'enable_comment_form' => $enable_comment_form,
                        'hide_registered_users' => $hide_registered_users,
						'submit_id' => 'submit'
                    );
    
    ayah_set_options($options);   
}

/**
 * Determines whether to upgrade the plugin if running an old verion or the
 * legacy plugin, install if no plugin is detected, or just display settings if
 * up to date.
 */
function ayah_check_for_upgrade_or_install() {

	$ayah_options = ayah_get_options();

    // Check for previous installation
    if (isset($ayah_options['version'])) {
	
        // If there's a previous installation, check for an upgrade
        if (ayah_upto_date($ayah_options['version'])) {
  
            // Version is up to date, move on to settings page
            return 'settings';
        
        } else {
            // Version is not up to date, time to upgrade
            ayah_upgrade_plugin();
            return 'upgrade';
        }
    // Either legacy plugin is installed or this is a fresh install
    } else {
    
        // Check for legacy version of plugin
        if ( isset( $ayah_options['ayah_webservice_host'] ) && $ayah_options['ayah_webservice_host'] ) {
        
            // Legacy installed, upgrade
            ayah_upgrade_legacy_plugin();
            return 'upgrade';
        
        } else {
        
            // No plugin detected, fresh install
            return 'install';
        }
    }
}

// This function can be removed once the Settings API is implemented
/**
 * Updates the options in the database
 */
function ayah_set_options($options) {    
	$options_allowed = array(   'publisher_key',
	                            'scoring_key',
	                            'version',
	                            'enable_register_form',
                                'enable_lost_password_form',
                                'enable_comment_form',
								'hide_registered_users',
								'submit_id');

	$new_options = array();
	foreach($options_allowed as $optal) {
	    if(isset($options[$optal])) {
	        $new_options[$optal] = $options[$optal];
	    }
	}
	
    update_option( 'ayah_options', $new_options );
	
	return $new_options;
}

/** 
 * Gets the settings from the database
 * 
 * TODO: According to documentation a check isn't needed
 * @link http://codex.wordpress.org/Function_Reference/get_site_option
 */
function ayah_get_options() {
    global $wpmu;
    
    $defaults = array(
        'version' => AYAH_VERSION,
        'publisher_key' => '',
        'scoring_key'   => '',
        'enable_register_form' => false,
        'enable_lost_password_form' => false,
        'enable_comment_form' => false,
        'hide_registered_users' => false,
        'submit_id' => ''
    );

    if ( 1 == $wpmu && get_site_option( 'ayah_options' ) ){
    	$ayah_options = get_site_option( 'ayah_options' ); // blog network settings (depricated)
        update_option( 'ayah_options', $ayah_options ); // Assign it to a wp_<blog_id>_options setting
        $ayah_options = get_option( 'ayah_options', array() ); // Verify we're getting the right options
        delete_site_option( 'ayah_options' ); // Remove the old one from site_options
    } else {
    	$ayah_options = get_option( 'ayah_options', array() ); // We've already deleted the site option, just use this blog's setting
    }

    return wp_parse_args( $ayah_options, $defaults );
}

/**
 * Deletes the options from the database
 */
function ayah_delete_options() {
    global $wpmu;
    
    if ( 1 == $wpmu) {
        delete_site_option('ayah_options');
    } else {
        delete_option('ayah_options');
    }
}

/**
 * Checks if the installed version of the plugin is less than the current version
 */
function ayah_upto_date($install_version) {
    return version_compare( $install_version, AYAH_VERSION, '>=' );
}

/**
 * Get the options from the submitted form data
 */
function ayah_get_settings_post() {
    $ayah_post = $_POST['ayah'];

    $defaults = array(
        'version' => AYAH_VERSION,
        'publisher_key' => '',
        'scoring_key'   => '',
        'enable_register_form' => '',
        'enable_lost_password_form' => '',
        'enable_comment_form' => '',
        'hide_registered_users' => '',
        'submit_id' => ''
        );

    return wp_parse_args( $ayah_post, $defaults );
}

?>