<?php

/**
* Attached to the BuddyPress Registration
*/
function ayah_buddypress_register() {
    $ayah = ayah_load_library();
    
    if ( $ayah->scoreResult() ) {
        return;
    } else {
    	global $bp;
    	$bp->signup->errors['signup_username'] = __( 'Please complete the PlayThru again', 'ayah' );
    }
    return;
}