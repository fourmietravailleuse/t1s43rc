<?php
if( !defined( 'ABSPATH' ) )
	die( 'Cheatin\' uh?' );


// !Select user
if ( !function_exists('noop_select_user_field') ):

function noop_select_user_field( $o ) {
	if ( ( empty($o['label_for']) && empty($o['name']) ) || empty($o['option_name']) )
		return;

	$o = array_merge( array(
		'label_for'			=> '',		// (1)
		'name'				=> '',		// (1)
		'label'				=> '',
		'value'				=> null,

		'class'				=> '',

		'order'				=> 'ASC',				// 'DESC', 'ASC'
		'orderby'			=> 'display_name',		// 'ID', 'user_nicename', 'display_name'
		'include'			=> '',					// Comma separated list of users IDs to include
		'exclude'			=> '',					// Comma separated list of users IDs to exclude
		'show'				=> 'display_name',		// 'ID', 'user_login', 'display_name'
		'include_selected'	=> false,
		'show_option_all'	=> '',					// 'All Users'
		'show_option_none'	=> '',					// '&mdash; Select &mdash;'
		'blog_id'			=> $GLOBALS['blog_id'],
		'who'				=> '',					// 'authors'
	), $o);

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

	$o['show_option_all']	= ($o['show_option_all'] == 'all' || $o['show_option_all'] == 'all users') ? __('All Users') : $o['show_option_all'];
	$o['show_option_none']	= ($o['show_option_none'] == 'choose' || $o['show_option_none'] == 'select') ? __('&mdash; Select &mdash;') : $o['show_option_none'];

	echo "\t\t\t\t";
	echo $o['label'] ? '<label for="'.$o['id'].'">'.$o['label'].'</label> ' : '';
		wp_dropdown_users( $o );

	// Prepare for the default values
	$o['name'] = $name;
	if ( isset($defaults) ) {
		if ( strpos($name, '|') !== false ) {
			$default	= Noop_Fields::get_deep_array_val( $o['defaults'], explode('|', $name) );
			if ( $default == -1 || !$default )
				$o['defaults'] = Noop_Fields::set_deep_array_val(0, explode('|', $name), $o['defaults']);
			elseif ( $default && $user = get_userdata($default) ) {
				$val = esc_html((!empty($user->{$o['show']}) ? $user->{$o['show']} : '('. $user->user_login . ')'));
				$o['values'] = Noop_Fields::set_deep_array_val($val, explode('|', $name.'|'.$user->ID), $o['values']);
			}
		}
		else {
			if ( $o['defaults'][$name] == -1 || !$o['defaults'][$name] )
				$o['defaults'][$name] = 0;
			elseif ( $default && $user = get_userdata($default) )
				$o['values'][$user->ID] = esc_html((!empty($user->{$o['show']}) ? $user->{$o['show']} : '('. $user->user_login . ')'));
		}
	}

	echo Noop_Fields::get_instance( $o['option_name'] )->default_and_description( $o );
}

endif;
/**/