<?php
if( !defined( 'ABSPATH' ) )
	die( 'Cheatin\' uh?' );


// !Date field
if ( !function_exists('noop_hour_minute_field') ):

function noop_hour_minute_field( $o ) {
	if ( ( empty($o['label_for']) && empty($o['name']) ) || empty($o['option_name']) )
		return;

	global $wp_locale;

	$o = array_merge( array(
		'label_for'		=> '',		// (1)
		'name'			=> '',		// (1)
		'label'			=> '',
		'value'			=> null,

		'class'			=> '',

		'fallback_today'=> false	// If no date, fallback to today date
	), $o);
	extract($o, EXTR_SKIP);

	$id				= $label_for ? $label_for : $name;
	$name			= $name ? $name : $id;
	$time_format	= get_option('time_format');

	if ( is_null($value) ) {
		if ( strpos($name, '|') !== false )
			$value	= Noop_Fields::get_deep_array_val( $options, explode('|', $name) );
		else
			$value	= $options[$name];
	}
	$name	= str_replace('|', '][', $name);

	// Default value
	if ( isset($defaults) ) {
		if ( strpos($name, '][') !== false ) {
			$default = Noop_Fields::get_deep_array_val( $defaults, explode('][', $name) );
			if ( $default && $default != '00:00' ) {
				$default = explode(':', $default);
				$default = date( $time_format, mktime((int)$default[0], (int)$default[1], 0) );
				$o['defaults'] = Noop_Fields::set_deep_array_val($default, explode('][', $name), $defaults);
			}
			elseif( $default == '00:00' )
				$o['defaults'] = Noop_Fields::set_deep_array_val('', explode('][', $name), $defaults);
		}
		else {
			if ( $defaults[$name] && $defaults[$name] != '00:00' ) {
				$default = explode(':', $defaults[$name]);
				$default = date( $time_format, mktime((int)$default[0], (int)$default[1], 0) );
				$o['defaults'][$name] = $default;
			}
			elseif( $defaults[$name] == '00:00' )
				$o['defaults'][$name] = '';
		}
	}

	if ( $fallback_today ) {
		$time_adj = current_time('timestamp');
		$cur_hh = date( 'H', $time_adj );
		$cur_mn = date( 'i', $time_adj );
	} else
		$cur_hh = $cur_mn = '';

	$value = $value && $value != '00:00' ? explode(':', $value) : false;

	$hh = $value ? $value[0] : $cur_hh;
	$mn = $value ? $value[1] : $cur_mn;

	$name  = $option_name.(!empty($locales['locale']) ? '['.$locales['locale'].']' : '').'['.$name.']';
	$class = ' '.trim($class);
	$hour = '<input type="text" name="'.$name.'[hh]" value="' . $hh . '" size="2" class="hh'.$class.'" maxlength="2" autocomplete="off" id="'.$id.'" />';
	$minute = '<input type="text" name="'.$name.'[mn]" value="' . $mn . '" size="2" class="mn'.$class.'" maxlength="2" autocomplete="off" />';

	echo "\t\t\t\t";
	echo '<div class="timestamp-wrap settings-timestamp">';

		echo $label ? '<label for="'.$id.'">'.$label.'</label> ' : '';
		/* translators: 1: hour input, 2: minute input */
		printf(__('%1$s h %2$s min', 'noop'), $hour, $minute);

		echo Noop_Fields::get_instance( $option_name )->default_and_description( $o );

	echo '</div>';

	// CSS
	Noop_Fields::date_icon_css();
}

endif;
/**/