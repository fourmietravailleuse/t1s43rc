<?php
/*
Plugin Name: AYAH Gravity Forms
Plugin URI:  http://wordpress.org/extend/plugins/are-you-a-human/
Description: AYAH-GF
Author: Are You A Human
Author URI: http://www.areyouahuman.com/
Version: 1.0
*/

$GF_ERROR_MESSAGE = 'We could not verify that you are a human. Please try again.';

/**
 * Shows the PlayThru when not on an admin page and shows a PlayThru image
 * when on an admin page
 */
function ayahgf_field($input, $field, $value, $lead_id, $form_id) {

	if ($field['type'] == 'ayah'){
		if (!is_admin()) {	
			$ayah = ayah_load_library();
			$input .= $ayah->getPublisherHTML();
		} else {
			$input = $input . '<img src=' . plugins_url() . '/are-you-a-human/includes/plugin-integration/gravity-forms/images/Cooler.png />';
		}
	}
	
	return $input;
}

/**
 * Adds the Are You A Human button to the Advanced Fields section of GF
 */
function ayahgf_add_button($field_groups) {
	
	require_once("ayah_gf_js.php");
	
	// Add the AYAH button in the Advanced Settings tab
	$field_groups[1]["fields"][] = array("class"=>"button", "value" => GFCommon::get_field_type_title("Are You A Human"), "onclick" => "ayahgf_add_field('ayah');");
	
	return $field_groups;
}

/**
 * Changes the field title to Are You A Human PlayThru
 */
function ayahgf_add_field_title($type) {
	
	if ($type == 'ayah') {
		return 'Are You A Human PlayThru';
	} else {
		return $type;
	}
}

/**
 * Validates the PlayThru
 */
function ayahgf_validate($result, $value, $form, $field) {

	if ($field['type'] == 'ayah') {
		$ayah = ayah_load_library();
		if($result["is_valid"] && $ayah->scoreResult()){
			
		} else {
			global $GF_ERROR_MESSAGE;
			$result["is_valid"] = false;
			$result["message"] = $GF_ERROR_MESSAGE;
		}
	}
    return $result;
}

?>