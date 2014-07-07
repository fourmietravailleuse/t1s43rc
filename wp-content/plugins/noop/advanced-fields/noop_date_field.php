<?php
if( !defined( 'ABSPATH' ) )
	die( 'Cheatin\' uh?' );


// !Date field
if ( !function_exists('noop_date_field') ):

function noop_date_field( $o ) {
	if ( ( empty($o['label_for']) && empty($o['name']) ) || empty($o['option_name']) )
		return;

	global $wp_locale, $wp_version;

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
			if ( $default && $default != '0000-00-00 00:00:00' ) {
				/* translators: F: month, d: day input, Y: year, H: hour, i: minute */
				$default = date_i18n( __('F d, Y @ H : i', 'noop'), mysql2timestamp( $default ) );
				$o['defaults'] = Noop_Fields::set_deep_array_val($default, explode('][', $name), $defaults);
			}
			elseif( $default == '0000-00-00 00:00:00' )
				$o['defaults'] = Noop_Fields::set_deep_array_val('', explode('][', $name), $defaults);
		}
		else {
			if ( $defaults[$name] && $defaults[$name] != '0000-00-00 00:00:00' ) {
				/* translators: F: month, d: day input, Y: year, H: hour, i: minute */
				$default = date_i18n( __('F d, Y @ H : i', 'noop'), mysql2timestamp( $default ) );
				$o['defaults'][$name] = $default;
			}
			elseif( $defaults[$name] == '0000-00-00 00:00:00' )
				$o['defaults'][$name] = '';
		}
	}

	$edit = $value && $value != '0000-00-00 00:00:00';

	if ( $fallback_today ) {
		$time_adj = current_time('timestamp');
		$cur_jj = gmdate( 'd', $time_adj );
		$cur_mm = gmdate( 'm', $time_adj );
		$cur_aa = gmdate( 'Y', $time_adj );
		$cur_hh = gmdate( 'H', $time_adj );
		$cur_mn = gmdate( 'i', $time_adj );
	} else
		$cur_jj = $cur_mm = $cur_aa = $cur_hh = $cur_mn = '';

	$jj = ($edit) ? mysql2date( 'd', $value, false ) : $cur_jj;
	$mm = ($edit) ? mysql2date( 'm', $value, false ) : $cur_mm;
	$aa = ($edit) ? mysql2date( 'Y', $value, false ) : $cur_aa;
	$hh = ($edit) ? mysql2date( 'H', $value, false ) : $cur_hh;
	$mn = ($edit) ? mysql2date( 'i', $value, false ) : $cur_mn;

	$name  = $option_name.(!empty($locales['locale']) ? '['.$locales['locale'].']' : '').'['.$name.']';
	$class = ' '.trim($class);

	$month  = '<select name="'.$name.'[mm]" class="mm'.$class.'">'."\n";
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

	$day = '<input type="text" name="'.$name.'[jj]" value="' . $jj . '" size="2" class="jj'.$class.'" maxlength="2" autocomplete="off" id="'.$id.'" />';
	$year = '<input type="text" name="'.$name.'[aa]" value="' . $aa . '" size="4" class="aa'.$class.'" maxlength="4" autocomplete="off" />';
	$hour = '<input type="text" name="'.$name.'[hh]" value="' . $hh . '" size="2" class="hh'.$class.'" maxlength="2" autocomplete="off" />';
	$minute = '<input type="text" name="'.$name.'[mn]" value="' . $mn . '" size="2" class="mn'.$class.'" maxlength="2" autocomplete="off" />';

	echo "\t\t\t\t";
	echo '<div class="timestamp-wrap settings-timestamp">';

		echo $label ? '<label for="'.$id.'">'.$label.'</label> ' : '';
		/* translators: 1: month input, 2: day input, 3: year input, 4: hour input, 5: minute input */
		$fields = version_compare( $wp_version, '3.6', '<' ) ? __('%1$s%2$s, %3$s @ %4$s : %5$s') : __('%1$s %2$s, %3$s @ %4$s : %5$s');
		printf($fields, $month, $day, $year, $hour, $minute);
		echo ' &#160; <input class="date-now hide-if-no-js button" type="button" value="'.esc_attr__('Right Now').'"/>';

		echo Noop_Fields::get_instance( $option_name )->default_and_description( $o );

	echo '</div><input type="hidden" name="'.$name.'[ss]" value="00" />';

	wp_enqueue_script('noop-settings');

	// CSS
	Noop_Fields::date_icon_css();
}

endif;
/**/