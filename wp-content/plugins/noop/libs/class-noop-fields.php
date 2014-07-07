<?php
if( !defined( 'ABSPATH' ) )
	die( 'Cheatin\' uh?' );

/*-----------------------------------------------------------------------------------*/
/* !Noop FIELDS CLASS ============================================================== */
/* Provides basic fields to build your settings page. Can be used alone.			 */
/* Also provides some useful utilities.												 */
/* Used in: Noop_Settings															 */
/*-----------------------------------------------------------------------------------*/

if ( !class_exists('Noop_Fields') ) :
class Noop_Fields {

	const VERSION = '0.5.4';
	protected static $instances = array();
	protected static $opts_static = array(
		'page_name'		=> '',
	);
	protected $opts = array(
		'option_name'	=> '',
		'noop_url'		=> false,
	);


	/**
	 * $name is also the key used to retrieve the class instance later.
	 */
	protected function __construct( $args = array() ) {

		$args = array_merge( (array) self::$opts_static, (array) $this->opts, (array) $args );

		self::$opts_static	= (object) array_intersect_key($args, (array) self::$opts_static);
		$this->opts 		= (object) array_intersect_key($args, (array) $this->opts);

		// The url will be used for the images
		if ( !$this->opts->noop_url ) {

			$noop_path = str_replace( DIRECTORY_SEPARATOR, '/', trailingslashit(dirname(dirname(__FILE__))) );

			if ( strpos( $noop_path, ( $t_dir = str_replace( DIRECTORY_SEPARATOR, '/', get_template_directory() ) ) ) === 0 ) {
				$this->opts->noop_url = str_replace($t_dir, get_template_directory_uri(), $noop_path);			// It's a theme
			}
			elseif ( strpos( $noop_path, ( $s_dir = str_replace( DIRECTORY_SEPARATOR, '/', get_stylesheet_directory() ) ) ) === 0 ) {
				$this->opts->noop_url = str_replace($t_dir, get_stylesheet_directory_uri(), $noop_path);		// It's a child theme
			}
			else {
				$this->opts->noop_url = plugin_dir_url( $noop_path.'foop.php' );								// It's a plugin (or must-use plugin)
			}

		}

		self::$instances[$this->opts->option_name] = $this;
	}


	/**
	 * !Static Not-Singleton Factory Method
	 * @return one of the instances
	 */
	static public function get_instance( $args = false ) {
		if ( !$args ) {		// PEBCAK
			_doing_it_wrong( __CLASS__.'::'.__METHOD__, '"U Can\'t Touch This".');
			return null;
		}

		if ( is_string($args) )
			$option_name = $args;
		elseif ( is_array($args) && !empty($args['option_name']) )
			$option_name = $args['option_name'];
		elseif ( is_object($args) && !empty($args->option_name) )
			$option_name = $args->option_name;
		else
			return null;

		if ( !empty(self::$instances[$option_name]) )
			return self::$instances[$option_name];

		$args = Noop::get_instance( $args );
		if ( !is_null( $args ) ) {
			$className	= __CLASS__;
			$args		= Noop::get_props( $option_name );
			return new $className( $args );
		}
	}


	// !Return the class properties

	public function get_properties() {
		return (object) array_merge( (array) self::$opts_static, (array) $this->opts );
	}


	/*-----------------------------------------------------------------------*/
	/* !Sections tweaks ==================================================== */
	/*-----------------------------------------------------------------------*/

	/* !List all pre-WP-3.8 icons */

	static public function pre_38_icons() {
		return array(
		//	'dashboard'		=> 'dashboard',
			'post'			=> 'post',
			'media'			=> 'media',
			'links'			=> 'links',
			'page'			=> 'page',
			'comments'		=> 'comments',
			'appearance'	=> 'appearance',
			'plugins'		=> 'plugins',
			'users'			=> 'users',
			'tools'			=> 'tools',
			'settings'		=> 'settings',
		//	'site'			=> 'site',
			'generic'		=> 'generic',
		);
	}


	/* !Section icon */

	static public function section_icon( $class = 'generic', $icon16 = false ) {
		// In WP 3.8, "site" and "admin-site" both exist: the old "site" is the new "admin-site".
		if ( $icon16 || version_compare($GLOBALS['wp_version'], '3.8-alpha', '<') ) {
			return '<span class="icon16 icon-' . ($class == 'admin-site' ? 'site' : $class) . '">&#160;</span>';
		}
		elseif ( ($icons = self::pre_38_icons()) && isset($icons[$class]) ) {
			$class = 'admin-' . $class;
		}
		return '<span class="dashicons dashicons-' . $class . '"></span>';
	}


	/* !Shorthand to add an icon to a section after the section is created */

	static public function add_section_icon( $section, $class = 'generic', $icon16 = false ) {
		global $wp_settings_sections;
		if ( !empty(self::$opts_static->page_name) && isset($section, $wp_settings_sections[self::$opts_static->page_name][$section]) )
			$wp_settings_sections[self::$opts_static->page_name][$section]['title'] = section_icon( $class, $icon16 ) . $wp_settings_sections[self::$opts_static->page_name][$section]['title'];
	}


	/* !Shorthand to add a description to a section */
	/* The section callback must use 'description */

	static public function add_section_description( $section, $description = '' ) {
		global $wp_settings_sections;
		if ( !empty($description) && !empty(self::$opts_static->page_name) && isset($section, $wp_settings_sections[self::$opts_static->page_name][$section]) )
			$wp_settings_sections[self::$opts_static->page_name][$section]['description'] = $description;
	}


	/* !A white space */

	static public function white_space() {
		return '<div class="white-space-field"></div>';		// height 100px
	}


	/*-----------------------------------------------------------------------*/
	/* !Fields ============================================================= */
	/*-----------------------------------------------------------------------*/

	/* !Multifields: use multiple fields in one row */

	static public function multifields( $fields = array() ) {
		if ( !is_array($fields) || !count($fields) )
			return;

		if ( !empty($fields['width']) )
			$field_width = $fields['width'];
		$description = !empty($fields['description']) ? $fields['description'] : ( !empty($fields['description_under']) ? $fields['description_under'] : '' );
		unset($fields['label_for'], $fields['width'], $fields['description'], $fields['description_under'], $fields['depends_on']);

		$width = array_fill(0, count($fields), false);
		if ( isset($field_width) )
			$width = is_array($field_width) ? ($field_width + $width) : array_fill(0, count($fields), $field_width);

		$i = 0;
		echo '<div class="multifields">';
		foreach ( $fields as $k => $o ) {
			if ( !isset($o['callback'], $o['params']) || !is_callable($o['callback']) || !is_numeric($k) )
				continue;
			echo '<div class="multifield '.(is_array($o['callback']) ? end($o['callback']) : $o['callback']).'"'.($width[$i] ? ' style="width:'.$width[$i].'"' : '').'>';
				call_user_func($o['callback'], $o['params']);
			echo '</div>';
			++$i;
		}
		echo '<div class="clear"></div>';
		echo $description ? '<p class="description">' . $description . '</p>' : '';
		echo '</div>';
	}


	/* !Description field */

	static public function description_field( $o ) {
		$o = array_merge( array(
			'description'	=> '',
			'wpautop'		=> true,
		), $o);
		echo $o['wpautop'] ? wpautop($o['description']) : $o['description'];
	}


	/* !Text field */

	public function text_field( $o ) {
		if ( empty($o['label_for']) && empty($o['name']) )
			return;

		$o = array_merge( array(
			'label_for'		=> '',		// (1)
			'name'			=> '',		// (1)
			'label'			=> '',
			'value'			=> null,

			'type'			=> 'text',
			'class'			=> '',		// small-text regular-text large-text code auto-select
			'attributes'	=> array(),
		), $o);
		extract($o);

		$id		= $label_for ? $label_for : $name;
		$name	= $name ? $name : $id;

		if ( is_null($value) ) {
			if ( strpos($name, '|') !== false )
				$value	= self::get_deep_array_val( $options, explode('|', $name) );
			else
				$value	= $options[$name];
		}
		$name	= str_replace('|', '][', $name);

		$attrs	= '';
		$attributes['type']			= $type;
		$attributes['id']			= $id;
		$attributes['value']		= self::esc_quote($value);
		$attributes['name']			= $this->opts->option_name.(!empty($locales['locale']) ? '['.$locales['locale'].']' : '').'['.$name.']';
		$attributes['class']		= 'depfield-' . str_replace( array('.', ']['), array('-', '|'), $name );
		if ( $class != '' )
			$attributes['class']	.= ' '.trim($class);
		foreach ( $attributes as $attr => $val ) {
			$attrs .= ' '.$attr.'="'.$val.'"';
		}

		echo "\t\t\t\t";
		echo $label ? '<label for="'.$id.'">'.$label.'</label> ' : '';
		echo '<input'.$attrs.'/> ';
		echo $this->default_and_description( $o );
		if ( strpos($class, 'auto-select') !== false )
			wp_enqueue_script( 'noop-settings' );
	}


	/* !Checkbox and Radio field */

	public function checkbox_field( $o ) {
		$this->choices_field( array_merge($o, array( 'type' => 'checkbox' )) );
	}


	public function radio_field( $o ) {
		$this->choices_field( array_merge($o, array( 'type' => 'radio' )) );
	}


	public function choices_field( $o ) {
		if ( empty($o['label_for']) && empty($o['name']) )
			return;
		if ( empty($o['values']) || !is_array($o['values']) )
			return;

		$o = array_merge( array(
			'label_for'		=> '',		// (1)
			'name'			=> '',		// (1)
			'label'			=> '',
			'value'			=> null,

			'type'			=> 'checkbox',
			'values'		=> array(),	// (2)
			'multiple'		=> false,	// For checkbox only
			'class'			=> '',
			'attributes'	=> array(),

			'next_under'	=> false,	// When true, the inputs are printed one under the other
			'label_wrap'	=> true,	// When false, the inputs are printed outside their label
		), $o);
		extract($o);

		$id		= $label_for ? $label_for : $name;
		$name	= $name ? $name : $id;

		if ( is_null($value) ) {
			if ( strpos($name, '|') !== false ) {
				$value	= self::get_deep_array_val( $options, explode('|', $name) );
			}
			else {
				$value	= $options[ $name ];
			}
		}
		if ( isset( $default ) && !isset( $values[$value] ) )
			$value = $default;
		$name	= str_replace('|', '][', $name);

		$count	= 0;
		$mult	= is_array($value);

		$attrs	= '';
		$attributes['type']			= $type;
		$attributes['name']			= $this->opts->option_name.(!empty($locales['locale']) ? '['.$locales['locale'].']' : '').'['.$name.']';
		$attributes['class']		= 'depfield-' . str_replace( array('.', ']['), array('-', '|'), $name );
		if ( $class != '' )
			$attributes['class']	.= ' '.trim($class);
		if ( $type == 'checkbox' && (count($values) > 1 || $multiple) )
			$attributes['name']		.= '[]';
		foreach ( $attributes as $attr => $val ) {
			$attrs .= ' '.$attr.'="'.$val.'"';
		}

		echo "\t\t\t\t";
		echo $label ? '<label for="'.$id.'">'.$label.'</label> ' . ($next_under ? '<br/>' : '') : '';
		foreach ( $values as $val => $lab ) {
			if ( $mult )
				$checked = is_array($value) && in_array($val, $value) ? ' checked="checked"' : '';
			else
				$checked = checked($value, $val, false);
			$label_tag = '<label for="'.$id.($count ? '-'.$count : '').'"'.($label_wrap ? ' class="checkbox-label"' : '').'>';
			echo $label_wrap ? $label_tag : '';
			echo '<input id="'.$id.($count ? '-'.$count : '').'" value="'.self::esc_quote($val).'" onclick="onclick"'.$attrs.$checked.'/> ';
			echo $label_wrap ? '' : $label_tag;
			echo $lab.'</label> ';
			echo $next_under ? '<br/>' : '';
			$count++;
		}
		echo $this->default_and_description( $o );
	}


	/* !Select field */

	public function select_field( $o ) {
		if ( empty($o['label_for']) && empty($o['name']) )
			return;
		if ( empty($o['values']) || !is_array($o['values']) )
			return;

		$o = array_merge( array(
			'label_for'			=> '',		// (1)
			'name'				=> '',		// (1)
			'label'				=> '',
			'value'				=> null,

			'values'			=> array(),	// (2)
			'class'				=> '',
			'attributes'		=> array(),

			'multiple'			=> false,
			'show_option_none'	=> '',		// 'choose' or 'select' will print '&mdash; Select &mdash;'
		), $o);
		extract($o);

		$id		= $label_for ? $label_for : $name;
		$name	= $name ? $name : $id;

		if ( is_null($value) ) {
			if ( strpos($name, '|') !== false )
				$value	= self::get_deep_array_val( $options, explode('|', $name) );
			else
				$value	= $options[$name];
		}
		if ( isset( $default ) && !isset( $values[$value] ) )
			$value = $default;
		$name	= str_replace('|', '][', $name);

		$attrs	= '';
		$attributes['id']			= $id;
		$attributes['name']			= $this->opts->option_name.(!empty($locales['locale']) ? '['.$locales['locale'].']' : '').'['.$name.']';
		$attributes['class']		= 'depfield-' . str_replace( array('.', ']['), array('-', '|'), $name );
		if ( $class != '' )
			$attributes['class']	.= ' '.trim($class);
		if ( $multiple ) {
			$attributes['name']		.= '[]';
			$attributes['multiple']	= 'multiple';
		}
		foreach ( $attributes as $attr => $val ) {
			$attrs .= ' '.$attr.'="'.$val.'"';
		}

		$show_option_none = ($show_option_none == 'choose' || $show_option_none == 'select') ? __('&mdash; Select &mdash;') : $show_option_none;
		$show_option_none = $show_option_none ? '<option class="level-0" value="">'.$show_option_none.'</option>' : '';

		echo "\t\t\t\t";
		echo $label ? '<label for="'.$id.'">'.$label.'</label> ' : '';
		echo '<select'.$attrs.'>';
			echo $show_option_none;
			foreach ( $values as $val => $lab ) {
				if ( $multiple )
					$selected = is_array($value) && in_array($val, $value) ? ' selected="selected"' : '';
				else
					$selected = selected($value, $val, false);
				echo '<option class="level-0" value="'.self::esc_quote($val).'"'.$selected.'>'.$lab.'</option>';
			}
		echo '</select> ';
		echo $this->default_and_description( $o );
	}


	/* !Textarea and TinyMCE */

	public function textarea_field( $o ) {
		if ( empty($o['label_for']) && empty($o['name']) )
			return;

		$o = array_merge( array(
			'label_for'			=> '',		// (1)
			'name'				=> '',		// (1)
			'label'				=> '',
			'value'				=> null,

			'type'				=> 'textarea',
			'class'				=> '',		// regular-text large-text code auto-select
			'textarea_rows'		=> get_option('default_post_edit_rows', 10),

			// For textarea only
			'attributes'		=> array(),
			'textarea_cols'		=> 50,

			// For mce only
			'teeny'				=> true,
			'media_buttons'		=> false,
			'dfw'				=> false,
			'tinymce'			=> true,	// Can be an array
			'quicktags'			=> true,	// Can be an array
			'wpautop'			=> true,
			'editor_css'		=> '',
		), $o);
		extract($o);

		$id		= $label_for ? $label_for : $name;
		$name	= $name ? $name : $id;

		if ( is_null($value) ) {
			if ( strpos($name, '|') !== false )
				$value	= self::get_deep_array_val( $options, explode('|', $name) );
			else
				$value	= $options[$name];
		}
		$name	= str_replace('|', '][', $name);

		if ( $type == 'textarea' || !function_exists('wp_editor') ) {	// wp_editor() since 3.3
			$attrs = '';
			$attributes['id']			= $id;
			$attributes['name']			= $this->opts->option_name.(!empty($locales['locale']) ? '['.$locales['locale'].']' : '').'['.$name.']';
			$attributes['rows']			= $textarea_rows;
			$attributes['cols']			= $textarea_cols;
			if ( $class !== '' )
				$attributes['class']	= $class;
			foreach ( $attributes as $attr => $val ) {
				$attrs .= ' '.$attr.'="'.$val.'"';
			}
			echo "\t\t\t\t";
			if ( $label ) {
				echo '<label for="'.$id.'">';
				if ( substr($label, -5) === '<br/>' ) {
					echo substr($label, 0, -5) . '</label><br/>';
				}
				else {
					echo $label . '</label> ';
				}
			}
			echo '<textarea'.$attrs.'>'.$value."</textarea>\n";
		}
		else {
			$textarea_name	= $this->opts->option_name.(!empty($locales['locale']) ? '['.$locales['locale'].']' : '').'['.$name.']';
			$editor_class	= $class;
			$p = compact('textarea_name', 'editor_class', 'textarea_rows', 'teeny', 'media_buttons', 'dfw', 'tinymce', 'quicktags', 'wpautop', 'editor_css');
			wp_editor( $value, $id, $p );
		}
		echo $this->default_and_description( $o );
	}


	/* !Hidden field */

	public function hidden_field( $o ) {
		if ( empty($o['label_for']) && empty($o['name']) )
			return;

		$o = array_merge( array(
			'label_for'		=> '',		// Shouldn't be used in most cases
			'name'			=> '',		// (1)
			'value'			=> null,
			'attributes'	=> array(),
		), $o);
		extract($o);

		$name	= $name ? $name : $label_for;

		if ( is_null($value) ) {
			if ( strpos($name, '|') !== false )
				$value	= self::get_deep_array_val( $options, explode('|', $name) );
			else
				$value	= $options[$name];
		}
		$name	= str_replace('|', '][', $name);

		$attrs	= '';
		$attributes['type']		= 'hidden';
		$attributes['value']	= self::esc_quote($value);
		$attributes['name']		= $this->opts->option_name.(!empty($locales['locale']) ? '['.$locales['locale'].']' : '').'['.$name.']';
		foreach ( $attributes as $attr => $val ) {
			$attrs .= ' '.$attr.'="'.$val.'"';
		}

		echo "\t\t\t\t";
		echo '<input'.$attrs.'/> ';
		echo $this->default_and_description( $o );
	}


	/* !Debug metaboxes content */

	static public function debug_field( $object, $box = array('args' => '') ) {
		echo '<pre style="overflow:auto">'.print_r($box['args'], 1)."</pre>\n";
	}


	/*-----------------------------------------------------------------------*/
	/* !Utilities ========================================================== */
	/*-----------------------------------------------------------------------*/

	/* !Used to replace "&" with "&amp;" in urls */

	static public function ampersand( $url ) {
		return str_replace(array('&amp;', '&'), array('&', '&amp;'), $url);
	}


	/* !Escape the quotes (they'll blow up the value attribute of inputs otherwise) */

	static public function esc_quote( $val ) {
		return str_replace('"', '&#34;', $val);
	}


	/*
	 * !Get a value in an array $out. Position is based on $keys
	 * self::get_deep_array_val($out, array('a', 'b', 'c'))
	 * will return the value of $out['a']['b']['c']
	 */
	static public function get_deep_array_val( $out = array(), $keys = array(), $return_null_on_fail = true ) {
		$key = array_shift($keys);
		if ( !is_array($out) || !isset($out[$key]) )
			return $return_null_on_fail ? null : $out;

		if (!count($keys))
			return $out[$key];

		return self::get_deep_array_val($out[$key], $keys);
	}


	/*
	 * !Set a value $val in an array $out. Position is based on $keys
	 * $out = self::set_deep_array_val($val, array('a', 'b', 'c'), $out)
	 * will return the array $out with $out['a']['b']['c'] set to $val
	 */
	static public function set_deep_array_val( $val = '', $keys = array(), $out = array() ) {
		$out = is_array($out) ? $out : array();
		$key = array_shift($keys);

		if ( !count($keys) )
			$out[$key] = $val;
		else {
			$out[$key] = isset($out[$key]) ? $out[$key] : array();
			$out[$key] = self::set_deep_array_val($val, $keys, $out[$key]);
		}
		return $out;
	}


	/*
	 * !Used in the date fields (date, month-year, hour-minute) to print (or not) the css for the icon.
	 */
	static public function date_icon_css() {
		static $css_printed = 0;
		if ( !$css_printed ) {
			$css_printed++;
			if ( version_compare($GLOBALS['wp_version'], '3.8', '<') )
				echo '<style type="text/css">.settings-timestamp{background-image:url("'.admin_url('images/date-button.gif').'")}@media print,(-o-min-device-pixel-ratio:5/4),(-webkit-min-device-pixel-ratio:.25),(min-resolution:120dpi){.settings-timestamp{background-image:url("'.admin_url('images/date-button-2x.gif').'")}}</style>';
		}
	}


	/* !Add unit, default value, and description at the end of a field */

	public function default_and_description( $args ) {
		// First, get the default value
		$name = !empty($args['name']) ? $args['name'] : $args['label_for'];

		if ( !isset($args['default']) ) {
			if ( !empty($args['defaults']) ) {
				if ( empty($args['default']) ) {
					if ( strpos($name, '|') !== false ) {
						$args['default'] = self::get_deep_array_val( $args['defaults'], explode('|', $name) );
					}
					else {
						$args['default'] = $args['defaults'][$name];
					}
				}
			}
			else
				$args['default'] = '';
		}

		$default = '';
		if ( is_array($args['default']) ) {
			foreach ( $args['default'] as $def ) {
				$default .= (isset($args['values'][$def]) ? $args['values'][$def] : $def) . ', ';
			}
			$default = rtrim($default, ', ');
		} else
			$default = isset($args['values'][$args['default']]) ? $args['values'][$args['default']] : $args['default'];

		$args = array_merge(array(
			'id'		=> false,
			'label_for'	=> false,
			'name'		=> false,
			'help'		=> false,
			'unit'		=> false,
			'fill_button'	=> false,
			'description'	=> false,
			'description_under'	=> false,
			'translatables'	=> array(),
			'locales'	=> array( 'locale' => 'en_US', 'default' => 'en_US', 'languages' => array() )
		), $args);

		$i18n = (object) $args['locales'];

		$out = '';
		// Unit
		if ( $args['unit'] ) {
			$value = !is_null($args['value']) ? $args['value'] : (isset($args['options'][$name]) ? $args['options'][$name] : 2);
			$units = array(
				'ms'	=> _n('millisecond', 'milliseconds', $value, 'noop'),
				's'		=> _n('second', 'seconds', $value, 'noop'),
				'mn'	=> trim( sprintf( _n('%s min', '%s mins', $value), '' ) ),
				'h'		=> trim( sprintf( _n('%s hour', '%s hours', $value), '' ) ),
				'd'		=> trim( sprintf( _n('%s day', '%s days', $value), '' ) ),
				'w'		=> trim( sprintf( _n('%s week', '%s weeks', $value), '' ) ),
				'm'		=> trim( sprintf( _n('%s month', '%s months', $value), '' ) ),
				'y'		=> trim( sprintf( _n('%s year', '%s years', $value), '' ) ),
			);
			$out .= isset($units[$args['unit']]) ? $units[$args['unit']] : $args['unit'];
		}

		// "Fill" button
		if ( !empty($args['attributes']['placeholder']) && $args['fill_button'] ) {
			$out .= ' <span class="fill-placeholder-button button hidden" tabindex="0">' . __('Fill', 'noop') . '</span>';
			wp_enqueue_script('noop-settings');
		}

		// Default
		if ( $default )
			$out .= ' <span class="description default-value">' . sprintf( __("(default: %s)", 'noop'), $default ) . '</span>';

		// Help pointer
		if ( $args['help'] ) {
			$id = $args['id'] ? $args['id'] : ($args['label_for'] ? $args['label_for'] : $args['name']);
			$out .= ' <span class="help-pointer" data-target="' . $id . '" data-title="' . esc_attr($args['help']['title']) . '" title="' . esc_attr($args['help']['content']) . '">?</span>';
			wp_enqueue_style( 'wp-pointer' );
			wp_enqueue_script( 'wp-pointer' );
			wp_enqueue_script( 'noop-settings' );
		}

		// Description
		if ( $args['description'] )
			$out .= ' <span class="description">' . $args['description'] . '</span>';

		// Translatable
		if ( in_array(reset((explode('|', $name))), $args['translatables']) && $i18n->locale == $i18n->default && count($i18n->languages) > 1 && Noop_i18n::use_multilang() ) {		// We tell the user this field is translatable.
			if ( version_compare( $GLOBALS['wp_version'], '3.8-alpha', '>' ) )
				$out .= ' <span class="dashicons dashicons-translation translatable-field" title="' . esc_attr__('This option is translatable', 'noop') . '"></span>';
			else
				$out .= ' <img class="translatable-field" src="'.$this->opts->noop_url.'res/images/translatable.png" width="16" height="16" title="' . esc_attr__('This option is translatable', 'noop') . '" alt=""/>';
		}

		// Description under the field
		if ( $args['description_under'] )
			$out .= '<p class="description">' . $args['description_under'] . '</p>';

		return $out;
	}

}
endif;
/**/