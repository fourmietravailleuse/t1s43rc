<?php
if( !defined( 'ABSPATH' ) )
	die( 'Cheatin\' uh?' );


// !Date field
if ( !function_exists('noop_month_year_field') ):

function noop_month_year_field( $o ) {
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
			if ( $default && $default != '0000-00' ) {
				$default = explode('-', $default);
				/* translators: F: month, Y: year */
				$default = date_i18n(__('F, Y', 'noop'), mktime(0, 0, 0, (int)$default[1], 1, (int)$default[0]));
				$o['defaults'] = Noop_Fields::set_deep_array_val($default, explode('][', $name), $defaults);
			}
			elseif( $default == '0000-00' )
				$o['defaults'] = Noop_Fields::set_deep_array_val('', explode('][', $name), $defaults);
		}
		else {
			if ( $defaults[$name] && $defaults[$name] != '0000-00' ) {
				$default = explode('-', $defaults[$name]);
				$default = date_i18n(__('F, Y', 'noop'), mktime(0, 0, 0, (int)$default[1], 1, (int)$default[0]));
				$o['defaults'][$name] = $default;
			}
			elseif( $defaults[$name] == '0000-00' )
				$o['defaults'][$name] = '';
		}
	}

	$edit	= $value && $value != '0000-00';

	if ( $fallback_today ) {
		$time_adj = current_time('timestamp');
		$cur_mm = gmdate( 'm', $time_adj );
		$cur_aa = gmdate( 'Y', $time_adj );
	} else
		$cur_mm = $cur_aa = '';

	$mm = ($edit) ? mysql2date( 'm', $value, false ) : $cur_mm;
	$aa = ($edit) ? mysql2date( 'Y', $value, false ) : $cur_aa;

	$name  = $option_name.(!empty($locales['locale']) ? '['.$locales['locale'].']' : '').'['.$name.']';
	$class = ' '.trim($class);

	$month  = '<select id="'.$id.'" name="'.$name.'[mm]" class="mm'.$class.'">'."\n";
	$month .= "\t\t\t" . '<option value="">' . __('Select Month') . "</option>\n";
	for ( $i = 1; $i < 13; $i = $i +1 ) {
		$monthnum = zeroise($i, 2);
		$month .= "\t\t\t" . '<option value="' . $monthnum . '"';
		if ( $i == $mm )
			$month .= ' selected="selected"';
		/* translators: 1: month number (01, 02, etc.), 2: month abbreviation */
		$month .= '>' . sprintf( __( '%1$s-%2$s' ), $monthnum, $wp_locale->get_month_abbrev( $wp_locale->get_month( $i ) ) ) . "</option>\n";
	}
	$month .= '</select>';

	$year = '<input type="text" name="'.$name.'[aa]" value="' . $aa . '" size="4" class="aa'.$class.'" maxlength="4" autocomplete="off" />';

	echo "\t\t\t\t";
	echo '<div class="timestamp-wrap settings-timestamp">';

		echo $label ? '<label for="'.$id.'">'.$label.'</label> ' : '';
		/* translators: 1: month input, 2: year input */
		printf(__('%1$s, %2$s', 'noop'), $month, $year);

		echo Noop_Fields::get_instance( $option_name )->default_and_description( $o );

	echo '</div>';

	// CSS
	Noop_Fields::date_icon_css();
}

endif;
/**/