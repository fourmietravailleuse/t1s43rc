<?php

$CF7_ERROR_MESSAGE = 'We could not verify that you are a human. Please try again.';

/**
 * Register the AYAH CF7 shortcode
 */
function ayahcf7_register_shortcode() {
	wpcf7_add_shortcode('ayah', 'ayahcf7_tag_handler');
}

/**
 * Handles the [ayah] tag by displaying a PlayThru
 */
function ayahcf7_tag_handler($atts) {
	$ayah = ayah_load_library();
    return $ayah->getPublisherHTML();
}

/**
 * Attached to wpcf7_validate. Validates PlayThru result.
 */
function ayahcf7_validate($errors) {

    $ayah = ayah_load_library();
    
    if ($ayah->scoreResult()) {
		return $errors;
    } else {
		global $CF7_ERROR_MESSAGE;
		$errors['valid'] = false;
		$errors['reason']['your-message'] = __($CF7_ERROR_MESSAGE, 'ayah');
		return $errors;
    }
}

/**
 * Registers the AYAH tag with CF7.
 */
function ayahcf7_tag_generator() {
	// The third argument tells CF7 the id of the div in which we will display
	// HTML, and the fourth argument tells CF7 which function will display the
	// HTML.
	wpcf7_add_tag_generator('ayah', 'Are You A Human',
		    'ayahcf7-tag-pane', 'ayahcf7_tag_pane');
}

/**
 * Displays the HTML of the tag pane in the CF7 Plugin
 */
function ayahcf7_tag_pane(&$contact_form) {
	?>
	<div id="ayahcf7-tag-pane" class="hidden">
		<form action="">
			<table>
			<tr>
				<td><?php _e('Name', 'ayah'); ?><br /><input type="text" name="name" class="tg-name oneline" /></td>
				<td></td>
			</tr>
			</table>

			<div class="tg-tag">
				<?php _e('Copy this code and paste it into the form left.', 'ayah' ); ?>
				<br />
				<input type="text" name="ayah" class="tag" readonly="readonly" onfocus="this.select()" />
			</div>
		</form>
	</div>
	<?php
}
?>
