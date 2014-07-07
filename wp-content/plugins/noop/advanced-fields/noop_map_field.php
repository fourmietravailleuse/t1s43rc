<?php
if( !defined( 'ABSPATH' ) )
	die( 'Cheatin\' uh?' );


// !Google map
if ( !function_exists('noop_map_field') ):

function noop_map_field( $o ) {
	if ( ( empty($o['label_for']) && empty($o['name']) ) || empty($o['page_name']) || empty($o['option_name']) )
		return;

	/*
	 * The name (or label_for) passed should correspond to an array of the following fields in the options.
	 * Fields name:
	 * They must be built like this: "{name}.lato", "{name}.lngo", etc
	 * lato: latitude original (retrieved with Gmap API)
	 * lngo: longitude original (retrieved with Gmap API)
	 * lat:  latitude (set manually by user)
	 * lng:  longitude (set manually by user)
	 * latc: latitude (center of the map)
	 * lngc: longitude (center of the map)
	 * zoom: zoom of the map (value between 2 and 18/20, default should be around 15)
	 * For example:
	 * If your options are "myoption.gmap.lato", "myoption.gmap.lngo", "myoption.gmap.lat", etc, name will be "myoption.gmap"
	 */
	$t_name = !empty($o['name']) ? $o['name'] : $o['label_for'];
	$o = array_merge( array(
		// name should be provided.
		// If label_for is provided, it should be {name}.'-lat'.
		'name'				=> '',		// (1)
		'label_for'			=> '',		// (1)

		// Source fields IDs.
		'address'			=> $t_name.'.address',
		'address_2'			=> $t_name.'.address_2',
		'state'				=> $t_name.'.state',
		'zip'				=> $t_name.'.zip',
		'city'				=> $t_name.'.city',
		'country'			=> $t_name.'.country',

		'source'			=> 'gmaps',			// or OSM for Open Street Map
	), $o);
	extract($o, EXTR_SKIP);

	$id			= $name ? $name : $label_for;
	$name		= $id;

	if ( strpos($name, '|') !== false )
		$value	= Noop_Fields::get_deep_array_val( $options, explode('|', $name), false );		// TODO: test, not sure it works.
	else
		$value	= $options;

	$input_name	= $option_name.(!empty($locales['locale']) ? '['.$locales['locale'].']' : '').'['.$name;	// Last item is still open

	$def_zoom	= isset($def_zoom) ? $def_zoom : (!empty($defaults[$name.'.zoom']) ? $defaults[$name.'.zoom'] : 15);
	$def_zoom	= $def_zoom ? ' <span class="description default-value">' . sprintf( __("(default: %s)", 'noop'), $def_zoom ) . '</span>' : '';

	$source		= strtolower($source);
	$source		= $source == 'osm' ? 'osm' : 'gmaps';
	?>

	<div id="<?php echo $id; ?>-map-wrap" class="map-wrap">

		<div id="<?php echo $id; ?>-map-infos" class="map-infos hidden"><?php // Sidebar ?>
			<p><?php $side_attrs = ' class="large-text" type="text" value="" readonly="readonly" id="'.$id; ?>
				<?php _e('Marker:', 'noop'); ?><br/>
				<input<?php echo $side_attrs.'-marker-lat"'; ?>/><br/>
				<input<?php echo $side_attrs.'-marker-lng"'; ?>/><br/>
				<button type="button" id="<?php echo $id; ?>-marker-send" class="secondary button marker-send"><?php _e('Submit'); ?></button>
			</p>
			<p>
				<?php _e('Center:', 'noop'); ?><br/>
				<input<?php echo $side_attrs.'-center-lat"'; ?>/><br/>
				<input<?php echo $side_attrs.'-center-lng"'; ?>/><br/>
				<button type="button" id="<?php echo $id; ?>-center-send" class="secondary button center-send"><?php _e('Submit'); ?></button>
			</p>
			<p>
				<?php _e('Zoom:', 'noop'); ?><br/>
				<input<?php echo $side_attrs.'-map-zoom"'; ?>/><br/>
				<button type="button" id="<?php echo $id; ?>-zoom-send" class="secondary button zoom-send"><?php _e('Submit'); ?></button>
			</p>
		</div>

		<div id="<?php echo $name; ?>-map" class="map"></div><?php // Map ?>

		<div id="<?php echo $name; ?>-map-coords" class="map-coords"><?php // All the stuff ?>

			<p class="center">
				<button type="button" id="<?php echo $id; ?>-get-coords" class="button-primary button get-coords hide-if-no-js"><?php _e('Get coordinates', 'noop'); ?></button>
				<button type="button" id="<?php echo $id; ?>-show-map" class="secondary button hidden show-map"><?php _e('Refresh the map', 'noop'); ?></button>
			</p>

			<p class="hide-if-no-js"><?php _e('Once you have filled the address fields, the map will NEED the latitude and longitude.<br/>Provide them by clicking the &#171;&#160;Get coordinates&#160;&#187; button.<br/>Each time you change the address, you must click it again.', 'noop'); ?></p>
			<p class="hide-if-no-js">
				<label for="<?php echo $id; ?>-lato"><?php _e('Latitude:', 'noop'); ?></label>
				<input id="<?php echo $id; ?>-lato" class="lato" type="text" name="<?php echo $input_name; ?>.lato]" value="<?php echo $value[$name.'.lato']; ?>" readonly="readonly"/><br/>

				<label for="<?php echo $id; ?>-lngo"><?php _e('Longitude:', 'noop'); ?></label>
				<input id="<?php echo $id; ?>-lngo" class="lngo" type="text" name="<?php echo $input_name; ?>.lngo]" value="<?php echo $value[$name.'.lngo']; ?>" readonly="readonly"/>
				<span id="<?php echo $id; ?>-geo-msg" class="geo-msg">&#160;</span>
			</p>

			<p>
				<b class="hide-if-js"><?php _e('To retrieve coordinates from the address, javascript must be activated in your browser preferences. However, you can set the latitude and longitude manually.', 'noop'); ?></b>
				<b class="hide-if-no-js"><?php _e('If the map fails to retrieve coordinates or if they are not accurate enough, you can set the latitude and longitude manually.', 'noop'); ?></b><br/>
				<label for="<?php echo $id; ?>-lat" ><?php _e('Latitude:', 'noop'); ?></label>
				<input id="<?php echo $id; ?>-lat" class="lat" type="text" name="<?php echo $input_name; ?>.lat]" value="<?php echo $value[$name.'.lat']; ?>"/>
				<span class="description"><?php printf(__('(e.g. %s)', 'noop'), '43.5956092'); ?></span><br/>

				<label for="<?php echo $id; ?>-lng" ><?php _e('Longitude:', 'noop'); ?></label>
				<input id="<?php echo $id; ?>-lng" class="lng" type="text" name="<?php echo $input_name; ?>.lng]" value="<?php echo $value[$name.'.lng']; ?>"/>
				<span class="description"><?php printf(__('(e.g. %s)', 'noop'), '1.4501434'); ?></span>
			</p>

			<p>
				<b><?php _e('You can also specify a different point to center the map (facultative).', 'noop'); ?></b><br/>
				<label for="<?php echo $id; ?>-latc" ><?php _e('Latitude:', 'noop'); ?></label>
				<input id="<?php echo $id; ?>-latc" class="latc" type="text" name="<?php echo $input_name; ?>.latc]" value="<?php echo $value[$name.'.latc']; ?>"/>
				<span class="description"><?php printf(__('(e.g. %s)', 'noop'), '43.5937764'); ?></span><br/>

				<label for="<?php echo $id; ?>-lngc" ><?php _e('Longitude:', 'noop'); ?></label>
				<input id="<?php echo $id; ?>-lngc" class="lngc" type="text" name="<?php echo $input_name; ?>.lngc]" value="<?php echo $value[$name.'.lngc']; ?>"/>
				<span class="description"><?php printf(__('(e.g. %s)', 'noop'), '1.4393971'); ?></span>
			</p>

			<p>
				<label for="<?php echo $id; ?>-zoom" ><?php _e('Initial zoom:', 'noop'); ?></label>
				<input id="<?php echo $id; ?>-zoom" class="zoom small-text" type="number" step="1" min="2" max="<?php echo $source == 'gmaps' ? 20 : 18; ?>" name="<?php echo $input_name; ?>.zoom]" value="<?php echo $value[$name.'.zoom']; ?>"/>
				<?php printf( __('from %1$d (faaaaar away) to %2$d (very close).', 'noop'), 2, ($source == 'gmaps' ? 20 : 18)); ?>
				<?php echo $def_zoom; ?>
			</p>
			<br/>

			<script>/* <![CDATA[ */
if(!window.maps)var maps={};<?php
$params = array(
	'prefix'	=> $name,
	'address'	=> $address,
	'address_2'	=> $address_2,
	'state'		=> $state,
	'zip'		=> $zip,
	'city'		=> $city,
	'country'	=> $country,
	'source'	=> $source
);
if ( $source == 'osm' )
	$params['tileLayer'] = apply_filters( $page_name.'-map-tile_layer', 'http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', $name, $option_name );

echo 'maps["'.$name.'"]='.json_encode($params); ?>;
/* ]]> */</script>

		</div>

		<div class="clear"></div>
	</div>

	<?php
	if ( !wp_script_is($source) ) {
		if ( $source == 'gmaps' )
			wp_enqueue_script( $source, 'https://maps.google.com/maps/api/js?sensor=false', false, false, true );
		else {	// OSM
			$suffix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';
			$js_url = Noop_Fields::get_instance( $option_name )->get_properties()->noop_url.'res/js/map/';
			$leaflet_version = '0.7.3';
			$leaflet_zoom_version = '0.6.1';

			// Styles (it's better if you can add them into the <head> tag)
			wp_enqueue_style( 'leaflet', $js_url.'leaflet/leaflet'.$suffix.'.css', false, $leaflet_version, 'all' );
			wp_enqueue_style( 'leaflet.zoomslider', $js_url.'leaflet.zoomslider/L.Control.Zoomslider'.$suffix.'.css', array('leaflet'), $leaflet_zoom_version, 'all' );
			wp_register_style( 'leaflet.zoomslider-ie', $js_url.'leaflet.zoomslider/L.Control.Zoomslider.ie'.$suffix.'.css', array('leaflet.zoomslider'), $leaflet_zoom_version, 'all' );
			$GLOBALS['wp_styles']->add_data( 'leaflet.zoomslider-ie', 'conditional', 'lte IE 8' );
			wp_enqueue_style( 'leaflet.zoomslider-ie' );

			// Scripts
			wp_enqueue_script( $source, $js_url.'leaflet/leaflet'.$suffix.'.js', false, $leaflet_version, true );
			wp_enqueue_script( $source.'.zoomslider', $js_url.'leaflet.zoomslider/L.Control.Zoomslider'.$suffix.'.js', array($source), $leaflet_zoom_version, true );
		}
	}

	if ( !wp_script_is($page_name.'_map') ) {
		$suffix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';

		$map_script_url = Noop_Fields::get_instance( $option_name )->get_properties()->noop_url.'res/js/map/map'.$suffix.'.js';
		$map_script_url = apply_filters( $page_name.'-map-script-url', $map_script_url, $name, $option_name );

		wp_enqueue_script( $page_name.'_map', $map_script_url, array('jquery', $source), Noop_Fields::VERSION, true );
		$js_vars = array(
			'not_found'	=> __('Sorry, can&#8217;t retrieve coordinates. Try another address or fill the following fields.', 'noop'),
			'error'		=> __('Error: geocoding can&#8217;t be launched.', 'noop'),
		);

		// The custom icon won't work with OSM
		$icon = apply_filters( $page_name.'-map-icon', false, $name, $option_name );	// Noop_Fields::getInstance( $option_name )->get_properties()->noop_url.'res/images/marker.png'
		if ( $icon )
			$js_vars['icon'] = esc_url($icon);

		wp_localize_script( $page_name.'_map', 'mapl10n', $js_vars );
	}
}

endif;
/**/