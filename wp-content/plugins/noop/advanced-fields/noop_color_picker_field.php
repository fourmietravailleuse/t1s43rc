<?php
if( !defined( 'ABSPATH' ) )
	die( 'Cheatin\' uh?' );


// !Color picker - WP 3.5 needed
if ( !function_exists('noop_color_picker_field') ):

function noop_color_picker_field( $o ) {
	if ( ( empty($o['label_for']) && empty($o['name']) ) || empty($o['option_name']) )
		return;

	$o = array_merge( array(
		'label_for'		=> '',		// (1)
		'name'			=> '',		// (1)
		'label'			=> '',
		'value'			=> null,

		'class'			=> '',		// small-text regular-text large-text code
		'attributes'	=> array(),

		'defaultColor'	=> '',
		'palettes'		=> '',		// "#acacac,#224466,..." (up to 8) for the 8 colors at the bottom of the picker. true for default palettes
		'width'			=> 0,		// Picker width (default: 255)
		'overlap'		=> true,	// If true, the picker will be positioned with absolute
	), $o);
	extract($o, EXTR_SKIP);

	$id				= $label_for ? $label_for : $name;
	$name			= $name ? $name : $id;

	if ( isset($defaults) && $defaultColor === '' ) {
		if ( strpos($name, '|') !== false )
			$defaultColor	= Noop_Fields::get_deep_array_val( $defaults, explode('|', $name) );
		else
			$defaultColor	= $defaults[$name];
	}

	if ( is_null($value) ) {
		if ( strpos($name, '|') !== false )
			$value	= Noop_Fields::get_deep_array_val( $options, explode('|', $name) );
		else
			$value	= $options[$name];
	}
	$name	= str_replace('|', '][', $name);

	$class	= trim($class.' color-picker-hex');

	$attrs	= '';
	$attributes['type']			= 'text';
	$attributes['maxlength']	= 7;
	$attributes['placeholder']	= esc_attr__( 'Hex Value' );
	$attributes['id']			= $id;
	$attributes['value']		= Noop_Fields::esc_quote($value);
	$attributes['name']			= $option_name.(!empty($locales['locale']) ? '['.$locales['locale'].']' : '').'['.$name.']';
	if ( $class != '' )
		$attributes['class']	= $class;
	$attributes['data-defaultColor']	= $defaultColor;
	$attributes['data-palettes']		= $palettes;
	if ( $width )
		$attributes['data-width']		= $width;
	foreach ( $attributes as $attr => $val ) {
		$attrs .= ' '.$attr.'="'.$val.'"';
	}

	echo "\t\t\t\t";
	echo '<div id="'.$id.'-picker" class="color-picker-field'.($overlap ? ' overlap' : '').'">';

		echo $label ? '<label for="'.$id.'">'.$label.'</label> ' : '';
		echo '<input'.$attrs.'/> ';
		$o['id'] = $id.'-picker';				// In case we use a help pointer
		echo Noop_Fields::get_instance( $option_name )->default_and_description( $o );

	echo '</div>';

	wp_enqueue_style('wp-color-picker');
	wp_enqueue_script('wp-color-picker');
	wp_enqueue_script('noop-settings');
}

endif;
/**/