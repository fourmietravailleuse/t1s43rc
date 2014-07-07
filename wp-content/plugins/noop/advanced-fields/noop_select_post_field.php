<?php
if( !defined( 'ABSPATH' ) )
	die( 'Cheatin\' uh?' );


// !Select post (be aware that displaying a long list of posts can be a bad idea)
if ( !function_exists('noop_select_post_field') ):

function noop_select_post_field( $o ) {
	if ( ( empty($o['label_for']) && empty($o['name']) ) || empty($o['option_name']) )
		return;

	$o = array_merge( array(
		'label_for'					=> '',		// (1)
		'name'						=> '',		// (1)
		'label'						=> '',
		'value'						=> null,

		'depth'						=> 0,		// 0: sub-pages indented, -1: flat display, 1: only top levels pages, 2+: depth
		'child_of'					=> 0,
		'sort_order'				=> 'ASC',
		'sort_column'				=> 'post_title',
		'include'					=> '',
		'exclude'					=> '',		// Comma separated list of category IDs to exclude
		'exclude_tree'				=> '',		// Define a parent Page ID to be excluded
		'meta_key'					=> '',
		'meta_value'				=> '',
		'authors'					=> '',
		'post_type'					=> 'page',
		'show_option_none'			=> '',		// 'choose' or 'select' for default text
		'show_option_none_value'	=> '',		// Value for the "show_option_none" item
	), $o);

	if ( !post_type_exists($o['post_type']) ) {
		_e("There's no such thing here!", 'noop');
		return;
	}

	$o['id']				= $o['label_for'] ? $o['label_for'] : $o['name'];
	$o['name']				= $o['name'] ? $o['name'] : $o['id'];
	$name					= $o['name'];			// Store this for the description/default value

	if ( is_null($o['value']) ) {
		if ( strpos($name, '|') !== false )
			$o['selected']	= Noop_Fields::get_deep_array_val( $o['options'], explode('|', $o['name']) );
		else
			$o['selected']	= $o['options'][$o['name']];
	} else
		$o['selected']		= $o['value'];
	$o['name']				= $o['option_name'] . (!empty($o['locales']['locale']) ? '['.$o['locales']['locale'].']' : '') . '[' . str_replace('|', '][', $o['name']) . ']';

	$o['show_option_none']	= ($o['show_option_none'] == 'choose' || $o['show_option_none'] == 'select') ? __('&mdash; Select &mdash;') : $o['show_option_none'];

	echo "\t\t\t\t";
	echo $o['label'] ? '<label for="'.$o['id'].'">'.$o['label'].'</label> ' : '';
		if ( is_post_type_hierarchical( $o['post_type'] ) )
			wp_dropdown_pages( $o );
		else
			wp_dropdown_posts( $o );

	// Prepare for the default values
	$o['name'] = $name;
	if ( isset($defaults) ) {
		if ( strpos($name, '|') !== false ) {
			$default	= Noop_Fields::get_deep_array_val( $o['defaults'], explode('|', $name) );
			if ( $default && $post_title = get_the_title($default) )
				$o['values'] = Noop_Fields::set_deep_array_val($post_title, explode('|', $name), $o['values']);
		}
		else {
			if ( $o['defaults'][$name] && $post_title = get_the_title($o['defaults'][$name]) )
				$o['values'][$o['defaults'][$name]] = $post_title;
		}
	}
	echo Noop_Fields::get_instance( $o['option_name'] )->default_and_description( $o );
}

endif;


/**
 * !Retrieve or display list of posts as a dropdown (select list).
 *
 * @param array|string $args Optional. Override default arguments.
 * @return string HTML content, if not displaying.
 */
 if ( !function_exists('wp_dropdown_posts') ) :

function wp_dropdown_posts($args = '') {
	$defaults = array(
		'depth' => 0, 'numberposts' => 20,		// -1 for all, but it's a terrible idea
		'selected' => 0, 'echo' => 1,
		'name' => 'post_id', 'id' => '',
		'show_option_none' => '', 'show_option_no_change' => '',
		'option_none_value' => '',
	);

	$r = wp_parse_args( $args, $defaults );
	extract( $r, EXTR_SKIP );

	$name = $r['name'];		// Name is messing with get_posts
	unset($r['name']);
	$pages = get_posts($r);
	$r['name'] = $name;
	$output = '';
	// Back-compat with old system where both id and name were based on $name argument
	if ( empty($id) )
		$id = $name;

	if ( ! empty($pages) ) {
		$output = "<select name='" . esc_attr( $name ) . "' id='" . esc_attr( $id ) . "'>\n";
		if ( $show_option_no_change )
			$output .= "\t<option value=\"-1\">$show_option_no_change</option>";
		if ( $show_option_none )
			$output .= "\t<option value=\"" . esc_attr($option_none_value) . "\">$show_option_none</option>\n";
		$output .= walk_page_dropdown_tree($pages, $depth, $r);
		$output .= "</select>\n";
	}

	$output = apply_filters('wp_dropdown_pages', $output);

	if ( $echo )
		echo $output;

	return $output;
}

endif;
/**/