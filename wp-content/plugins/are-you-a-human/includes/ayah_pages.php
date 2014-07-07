<?php

/**
 * Displays the settings page
 */
function ayah_get_options_page($action) {
    wp_enqueue_style('AYAHStylesheet');
    
	// This should never happen. In case it does, print a stacktrace so we can
	// find it and fix it.
    if (!in_array($action, array('install', 'upgrade', 'settings'))) {
		error_log("FATAL ERROR: action not found. Dumping stacktrace:"
					. print_r(debug_backtrace(), true));
		echo "Something went wrong. Please contact the Are You A Human support team.";
		return;
	}
    
    switch ($action) {
        case 'install':
            $page_opts = ayah_get_install_page_options($action);
            break;
        case 'upgrade':
            $page_opts = ayah_get_upgrade_page_options($action);
            break;
		case 'settings':
			$page_opts = ayah_get_settings_page_options();
			break;
    }
    
    echo "<div class='wrap'>";
    echo "<h2>Are You a Human " . $page_opts['title'] . "</h2>";
    
    if ($page_opts['flash_message']) {
        echo $page_opts['flash_message'];
    }
        
    echo ayah_get_form($action, $page_opts['button']);
    echo "</div>";
}

/**
 * Sets the page option for the settings page
 */
function ayah_get_settings_page_options() {

	$page_opts = array();
    $page_opts['title'] = 'PlayThru Settings';
    $page_opts['button'] = 'Update Settings';
    $page_opts['flash_message'] = null;
    
	// If the form was posted
    if (isset($_POST['ayah'])) {
        $page_opts['flash_message'] = "Your settings have been saved!";
    }
    
    return $page_opts;
}

/**
 * Sets the page options for the upgrade page
 */
function ayah_get_upgrade_page_options(&$action) {
    
    $page_opts = array();
    $page_opts['title'] = 'Upgrade';
    $page_opts['button'] = 'Complete Upgrade';
    $page_opts['flash_message'] = "Please take a moment to make sure the following is correct and complete your upgrade.";
    
	// If the form was posted
    if (isset($_POST['ayah'])) {
		$action = 'settings';
		$page_opts = ayah_get_settings_page_options();
        $page_opts['flash_message'] = "Your plugin has been successfully upgraded.";
    }
    
    return $page_opts;
}

/**
 * Sets the page options for the install page
 */
function ayah_get_install_page_options(&$action) {

    $page_opts = array();
    $page_opts['title'] =  "Installation";
    $page_opts['button'] = "Complete Installation";
    $page_opts['flash_message'] = "<p>To complete your installation:</p><ol><li><a href='http://portal.areyouahuman.com/signup/wordpress'>Register for an account</a> on Are You a Human.</li><li>Copy and paste the Publisher and Scoring keys into the fields below.</li></ol>";
    
	// If the form was posted
    if (isset($_POST['ayah'])) {
		$action = 'settings';
		$page_opts = ayah_get_settings_page_options();
        $page_opts['flash_message'] = "<div class='alert valid'>The plugin has been successfully installed.</div>";
    }
    
    return $page_opts;
}

/**
 * Returns the settings page HTML with the settings inputs filled in
 */
function ayah_get_form($action, $button) {
    
	$ayah_options = ayah_get_options();
	
	// Set up our checkbox checked
	$chk_enable_register_form = ( $ayah_options['enable_register_form'] ) ? 'checked' : '';
	$chk_enable_lost_password_form = ( $ayah_options['enable_lost_password_form'] ) ? 'checked' : '';
	$chk_enable_comment_form = ( $ayah_options['enable_comment_form'] ) ? 'checked' : '';
	$chk_hide_registered_users = ( $ayah_options['hide_registered_users'] ) ? 'checked' : '';

    include('settings_page.php');
}

/**
 * Adds a button that allows the settings in the database to be deleted. Used
 * for testing/debugging.
 */
function ayah_clear_options_button() {
    return "
        <form action=".$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING']." method='POST'>
            <input type='hidden' name='page' value='".$_GET['page']."' />
            <input type='hidden' name='ayah_clear_options' value='true' />
            <button type='submit' onclick='return confirm(\"Are you sure you want to clear the options?\");'>Clear Options</button>
        </form>
    ";
}