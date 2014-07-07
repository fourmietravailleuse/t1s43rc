<?php
if( !defined( 'ABSPATH' ) )
	die( 'Cheatin\' uh?' );


// !Upload field - WP 3.5 needed - values are stored as a comma separated list, not an array
if ( !function_exists('noop_upload_field') ):

function noop_upload_field( $o ) {
	if ( ( empty($o['label_for']) && empty($o['name']) ) || empty($o['option_name']) )
		return;

	$o = array_merge( array(
		'label_for'				=> '',		// (1)
		'name'					=> '',		// (1)
		'label'					=> '',
		'value'					=> null,

		'class'					=> '',			// small-text regular-text large-text code

		'label_after'			=> '',
		'mime'					=> '',			// 'all', 'uploaded' (if we have a post ID), 'image', 'audio', 'video'
		'multiple'				=> 0,			// 0 or 1
		'uploader_title'		=> '',
		'uploader_button_text'	=> '',

		'return_url'			=> false,		// Used only when we want an url, not an ID. Use `true` or `1` for anything but images, use `medium` or any image size for images.
	), $o);
	extract($o, EXTR_SKIP);

	$id				= $label_for ? $label_for : $name;
	$name			= $name ? $name : $id;

	if ( is_null($value) ) {
		if ( strpos($name, '|') !== false )
			$value	= Noop_Fields::get_deep_array_val( $options, explode('|', $name) );
		else
			$value	= $options[$name];
	}
	$name			= str_replace('|', '][', $name);

	$has_upload		= function_exists('wp_enqueue_media');		// For WP <3.5 we'll hide previews (no CSS) and the button (no JS)
	$mime			= $mime == 'all' ? '' : $mime;	// For "all", we need an emty string
	$multiple		= (int) $multiple;				// In case we passed a boolean
	$return_url		= !empty($return_url) ? $return_url : false;
	if ( $return_url && $return_url !== true && $return_url !== 1 ) {
		$image_sizes = array_merge( array( 'thumbnail', 'medium', 'large', 'full' ), array_keys($GLOBALS['_wp_additional_image_sizes']) );
		$return_url  = in_array($return_url, $image_sizes) ? $return_url : 'full';
	}

	if ( !$class )
		if ( $multiple )
			$class	= $return_url ? 'large-text' : 'regular-text';
		else
			$class	= $return_url ? 'regular-text' : 'small-text';

	$uploader_titles		= array( ''	=> __('Add Media'),		'image'	=> __('Add an Image', 'noop'),	'audio'	=> __('Add Audio'),	'video'	=> __('Add Video') );
	$uploader_button_texts	= array( ''	=> __('Insert Media'),	'image'	=> __('Insert Image'),			'audio'	=> __('Insert'),	'video'	=> __('Insert Video') );
	if ( empty($uploader_title) )
		$uploader_title = isset($uploader_titles[$mime]) ? $uploader_titles[$mime] : $uploader_titles[''];
	if ( empty($uploader_button_text) )
		$uploader_button_text = isset($uploader_button_texts[$mime]) ? $uploader_button_texts[$mime] : $uploader_button_texts[''];

	$option_val		= is_array($value) ? implode(',', $value) : $value;
	$option_arr		= is_array($value) ? $value : explode(',', $value);

	$attrs			= '';
	$attributes		= array(
		'id'			=> $id,
		'class'			=> $class.($has_upload && !$return_url ? ' hide-if-js' : ''),
		'type'			=> (!$return_url && !$multiple ? 'number' : 'text'),
		'name'			=> $option_name.(!empty($locales['locale']) ? '['.$locales['locale'].']' : '').'['.$name.']',
		'value'			=> ($option_val ? $option_val : ''),
		'autocomplete'	=> 'off',
	);
	if ( $return_url ) {
		$return_url = 'data-image_size="' . $return_url . '" ';
		if ( $multiple ) {
			unset( $attributes['type'], $attributes['value'], $attributes['autocomplete'] );
			$attributes['rows'] = min(10, max(4, count($option_arr)));
		}
		else
			$attributes['type'] = 'url';
	}
	foreach ( $attributes as $attr => $val ) {
		$attrs .= ' '.$attr.'="'.$val.'"';
	}

	$medias			= new WP_Query( array(
		'post_type'			=> 'attachment',
		'posts_per_page'	=> -1,
		'orderby'			=> 'post__in',
		'post_status'		=> 'inherit',
		'post__in'			=> $option_arr,
	) );

	echo "\t\t\t\t";
	echo '<div id="wp-'.$id.'-wrap" class="wp-editor-wrap noop-upload">';
		echo $label ? '<p><label for="'.$id.'">'.$label.'</label></p>' : '';
		if ( $has_upload && !$return_url ) {
			echo '<ul class="upload-media-display dashed-box'.($multiple ? ' ui-sortable' : '').'">';
				if ( $medias->have_posts() ):
					global $post;
					$_post = $post;
					while ( $medias->have_posts() ): $medias->the_post();
						global $post;
						if ( substr($post->post_mime_type, 0, 5) == 'image' ) {
							list($src, $width, $height) = wp_get_attachment_image_src($post->ID, 'medium');
							$orientation	= $width > $height ? 'landscape' : 'portrait';
							$att_type		= substr($post->post_mime_type, 0, 5);
							$subtype		= substr($post->post_mime_type, 6);

							echo '<li data-id="'.$post->ID.'" class="attachment media-attachment"><div class="attachment-preview type-'.$att_type.' subtype-'.$subtype.' '.$orientation.'"><div class="thumbnail"><div class="centered"><img src="'.$src.'" alt=""/></div></div><button title="'.__("Delete").'" class="close media-modal-icon">&#160;</button></div></li>';
						} else {
							$type_arr		= explode('/', $post->post_mime_type);
							$att_type		= reset($type_arr);
							$subtype		= end($type_arr);
							$icon			= wp_get_attachment_image( $post->ID, 'medium', true, array( 'class' => 'icon', 'alt' => '' ) );
							$filename		= end(explode('/', wp_get_attachment_url( $post->ID )));

							echo '<li data-id="'.$post->ID.'" class="attachment media-attachment"><div class="attachment-preview type-'.$att_type.' subtype-'.$subtype.' landscape">'.$icon.'<div class="filename"><div>'.$filename.'</div></div><button title="'.__("Delete").'" class="close media-modal-icon">&#160;</button></div></li>';
						}
					endwhile;
					$post = $_post;
					wp_reset_postdata();
				else:
					echo '<li class="attachment no-attachment"><div class="attachment-preview"><span class="icon no-media-icon">&#160;</span></div></li>';
				endif;
			echo '</ul>';
			echo '<div class="clear"></div>';
		}

		echo $label_after ? '<p><label for="'.$id.'">'.$label_after.'</label></p>' : '';
		if ( $return_url && $multiple ) {
			echo '<textarea'.$attrs.'>'.($option_val ? $option_val : '').'</textarea> ';
			echo '<p class="description">' . __('One value per line', 'noop') . "</p>\n";
		}
		else
			echo '<input'.$attrs.'/> ';

		if ( $has_upload ) {
			echo '<a  data-uploader_button_text="'.esc_attr($uploader_button_text).'" '
					.'data-multiple="'.($multiple ? 'add' : 0).'" '
					.'data-media_mime="'.esc_attr($mime).'" '
					.$return_url
					.'data-editor="'.$id.'" '
					.'class="button-secondary upload-media-button upload" '
					.'id="'.$id.'-upload" '
					.'target="_blank" '
					.'href="'.admin_url('media-upload.php?post_id=0').'">'			// post_id => 0 / disabled for now
				.esc_attr($uploader_title).'</a>';
		}

		$o['id'] = $id.'-upload';				// In case we use a help pointer
		if ( isset($defaults) ) {
			if ( strpos($name, '|') !== false )
				$o['defaults'] = Noop_Fields::set_deep_array_val('', explode('|', $name), $o['defaults']);
			else
				$o['defaults'][$name] = '';
		}

		echo Noop_Fields::get_instance( $option_name )->default_and_description( $o );
	echo "</div>\n";

	// JS
	if ( $has_upload ) {
		global $post;
		$enq_media = !empty($post->ID) ? array( 'post' => $post ) : array();
		wp_enqueue_media( $enq_media );
		wp_enqueue_script('noop-settings');
	}
}

endif;


if ( !function_exists('noop_upload_field_style') ):
add_action( (function_exists('did_action') && did_action('admin_print_styles') ? 'admin_print_footer_scripts' : 'admin_print_styles'), 'noop_upload_field_style' );
function noop_upload_field_style() {
	echo '<style type="text/css">.upload-media-display .attachment-preview .no-media-icon{background-image:url("'.admin_url('images/icons32.png').'")}@media print,(-o-min-device-pixel-ratio:5/4),(-webkit-min-device-pixel-ratio:.25),(min-resolution:120dpi){.upload-media-display .attachment-preview .no-media-icon{background-image:url("'.admin_url('images/icons32-2x.png').'")}}</style>';
}
endif;
/**/