<?php
if( !defined( 'ABSPATH' ) )
	die( 'Cheatin\' uh?' );

/*-----------------------------------------------------------------------------------*/
/* !Noop OPTIONS CLASS ============================================================= */
/* Provides the options system (escape, get, sanitize, update).						 */
/* Provides Activation and Uninstall methods.										 */
/* Requires: Noop, Noop_i18n, Noop_Utils											 */
/*-----------------------------------------------------------------------------------*/

if ( !class_exists('Noop_Options') ) :
class Noop_Options {

	const VERSION = '0.12';
	protected static $instances	= array();
	protected static $init_done = array();
	protected $opts	= array(
		'options'					=> array(),
		'options_default'			=> array(),
		'options_initial'			=> array(),
		'options_translatable'		=> array(),
		'sanitization_functions'	=> array(),
		'escape_functions'			=> array(),
	);
	protected $args;


	/*-------------------------------------------------------------------------------*/
	/* !Instance and Properties ==================================================== */
	/*-------------------------------------------------------------------------------*/

	protected function __construct( $args ) {

		if ( isset(self::$instances[$args->option_name]) )
			return self::$instances[$args->option_name];

		$this->args = $args;
		$this->opts = (object) $this->opts;

		// Init
		if ( !in_array($this->args->option_name, self::$init_done) ) {

			self::$init_done[]	= $this->args->option_name;
			$is_admin			= is_admin() && !( defined('DOING_AJAX') && DOING_AJAX );
			$use_import_export	= $this->args->option_group && (bool) apply_filters( $this->args->option_group.'-'.$this->args->option_name.'_use_import_export', true );

			// Activation
			if ( $is_admin && $this->args->plugin_file && $this->args->plugin_is_plugin == 1 && !did_action('activate_'.plugin_basename($this->args->plugin_file)) ) {
				register_activation_hook( $this->args->plugin_file,	array( $this, 'activation' ) );
			}

			// Import / Export
			if ( $is_admin && $use_import_export && !in_array('noop-import-export', self::$init_done) ) {
				self::$init_done[]	= 'noop-import-export';
				add_action( 'admin_post_noop-import-settings', array( __CLASS__, 'import_settings' ) );
				add_action( 'admin_post_noop-export-settings', array( __CLASS__, 'export_settings' ) );
			}

		}

		self::$instances[$this->args->option_name] = $this;

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
		return (object) $this->opts;
	}


	// !Return all instances names for a given option group

	static public function get_page_instances_names( $option_group ) {
		$out = array();
		if ( count( self::$instances ) ) {
			foreach ( self::$instances as $option_name => $inst ) {
				if ( !empty($inst->args->option_group) && $inst->args->option_group == $option_group )
					$out[$option_name] = $option_name;
			}
		}
		return $out;
	}


	/*-------------------------------------------------------------------------------*/
	/* !Options Utilities ========================================================== */
	/*-------------------------------------------------------------------------------*/

	// !get_option(), depending on the argument "network_menu". If "network_menu" is true, the options are stored in the main blog options.

	protected function get_blog_option( $option = false, $default = false ) {
		$option = $option ? $option : $this->args->option_name;

		if ( $this->args->network_menu && is_multisite() ) {
			global $blog_id;
			$main_blog_id = defined( 'BLOG_ID_CURRENT_SITE' ) && BLOG_ID_CURRENT_SITE ? absint( BLOG_ID_CURRENT_SITE ) : 1;

			if ( $blog_id != $main_blog_id ) {
				switch_to_blog( $main_blog_id );
				$blog_options = get_option( $option, $default );
				restore_current_blog();
				return $blog_options;
			}
		}
		return get_option( $option, $default );
	}


	// !update_option(), depending on the argument "network_menu". If "network_menu" is true, the options are stored in the main blog options.

	protected function update_blog_option( $option, $new_value ) {
		if ( $this->args->network_menu && is_multisite() ) {
			global $blog_id;
			$main_blog_id = defined( 'BLOG_ID_CURRENT_SITE' ) && BLOG_ID_CURRENT_SITE ? absint( BLOG_ID_CURRENT_SITE ) : 1;

			if ( $blog_id != $main_blog_id ) {
				switch_to_blog( $main_blog_id );
				$updated = update_option( $option, $new_value );
				restore_current_blog();
				return $updated;
			}
		}
		return update_option( $option, $new_value );
	}


	// !delete_option(), depending on the argument "network_menu". If "network_menu" is true, the options are stored in the main blog options.

	protected function delete_blog_option( $option = false ) {
		$option = $option ? $option : $this->args->option_name;

		if ( $this->args->network_menu && is_multisite() ) {
			global $blog_id;
			$main_blog_id = defined( 'BLOG_ID_CURRENT_SITE' ) && BLOG_ID_CURRENT_SITE ? absint( BLOG_ID_CURRENT_SITE ) : 1;

			if ( $blog_id != $main_blog_id ) {
				switch_to_blog( $main_blog_id );
				$deleted = delete_option( $option );
				restore_current_blog();
				return $deleted;
			}
		}
		return delete_option( $option );
	}


	// !tell if some settings are recorded
	public function has_blog_option( $option = false ) {
		$options = $this->get_blog_option( $option );
		$def_loc = Noop_i18n::get_default_locale( $this->args->network_menu );
		return $options && is_array( $options ) && !empty( $options[ $def_loc ] );
	}


	/*-------------------------------------------------------------------------------*/
	/* !Deal with options ========================================================== */
	/*-------------------------------------------------------------------------------*/

	/*
	 * !Utility: tell if a value should fallback to the default value.
	 * 0 (or "0") is a valid empty value for a field/option.
	 * In other words: empty field => default, not empty (or 0) => sanitize the value.
	 * Moreover, no need to sanitize if the value === the default value.
	 * @return boolean
	 */

	static public function fall_to_default( $val, $def ) {
		return $val === $def || ( empty($val) && $val !== 0 && $val !== '0' );
	}

	// !Return default options

	public function get_default( $option = false ) {

		$this->maybe_clear_options_cache();

		if ( empty($this->opts->options_default) )
			$this->opts->options_default = apply_filters( $this->args->option_name.'_default_options', array() );

		if ( $option ) {		// We request only one default option (or a group)
			$group = self::get_sub_options( $option, $this->opts->options_default );
			return !is_null($group) ? $group : null;
		}

		return $this->opts->options_default;
	}


	// !Return initial options values: they're used on the plugin activation and when the user reset them in the options form

	public function get_initial_options( $option = false ) {

		$this->maybe_clear_options_cache();

		if ( empty($this->opts->options_initial) )
			$this->opts->options_initial = apply_filters( $this->args->option_name.'_initial_options', $this->get_default() );

		if ( $option ) {		// We request only one initial option (or a group)
			$group = self::get_sub_options( $option, $this->opts->options_initial );
			return !is_null($group) ? $group : null;
		}

		return $this->opts->options_initial;
	}


	// !Return options that should be translated (basically, textareas/tinyMCE/text inputs/pages id) if the user has a multilingual site

	public function get_translatable_options( $option = false ) {

		$this->maybe_clear_options_cache();

		if ( empty($this->opts->options_translatable) ) {
			$this->opts->options_translatable = apply_filters( $this->args->option_name.'_translatable_options', array() );
			$this->opts->options_translatable = count($this->opts->options_translatable) ? array_combine( $this->opts->options_translatable, $this->opts->options_translatable ) : array();
		}

		if ( $option !== false )		// If not false, the method becomes a "is_option_translatable"-like method
			return in_array($option, $this->opts->options_translatable);

		return $this->opts->options_translatable;
	}


	// !Return an escaped option

	public function get_option( $name = false ) {
		$options = $this->get_options();

		$group	= self::get_sub_options( $name, $options );

		return !is_null($group) ? $group : $this->get_default( $name );
	}


	// !Return the escaped options
	// @var $for_locale (bool) if false, we return the entire array, including all languages

	public function get_options( $for_locale = true ) {

		$this->maybe_clear_options_cache();

		$locale = Noop_i18n::get_locale( $this->args->option_group.'-'.$this->args->option_name );	// Current locale

		if ( empty($this->opts->options[$locale]) ) {
			$default_locale		= Noop_i18n::get_default_locale( $this->args->network_menu );		// Default locale
			$default_options	= $this->get_default();												// Default options

			if ( empty($default_options) )
				return $for_locale ? array() : array( $locale => array() );							// PEBCAK

			$functions			= $this->escape_functions();										// Escape functions
			$translatables		= $this->get_translatable_options();								// Translatables fields
			$options			= $this->get_blog_option();											// Stored options
			$options			= is_array($options) ? $options : array();
			$def_loc_options	= !empty($options[$default_locale]) && is_array($options[$default_locale]) ? $options[$default_locale] : array();	// Value of the options for the default locale
			$locs_to_return		= Noop_i18n::get_languages();										// All Locales
			$new_options		= array( $default_locale => array() );								// Output

			$def_loc_options	= apply_filters( $this->args->option_name.'_before_escape_settings', $def_loc_options, $default_options, $functions );

			// First, escape the values for the default locale
			if ( count($default_options) ) {
				foreach( $default_options as $name => $def ) {
					$new_options[$default_locale][$name] = isset( $def_loc_options[$name] ) && !self::fall_to_default( $def_loc_options[$name], $def ) ? $this->sanitize_option( $name, $def_loc_options[$name], $functions, $def ) : $def;
				}
			}

			$new_options[$default_locale]	= apply_filters( $this->args->option_name.'_after_escape_settings', $new_options[$default_locale], $default_options, $functions );

			// Now we have to deal with the "non default locale" values and translatables fields
			foreach ( $locs_to_return as $loc ) {
				if ( $loc == $default_locale )
					continue;
				$new_options[$loc]	= $new_options[$default_locale];								// Assign the "default locale" values
				$options[$loc]		= isset($options[$loc]) ? $options[$loc] : $new_options[$loc];
				$options[$loc]		= apply_filters( $this->args->option_name.'_before_escape_settings', $options[$loc], $default_options, $functions );
				foreach( $translatables as $name ) {												// Loop through the translatables fields only
					$new_options[$loc][$name] = isset( $options[$loc][$name] ) && !self::fall_to_default( $options[$loc][$name], $default_options[$name] ) ? $this->sanitize_option( $name, $options[$loc][$name], $functions, $new_options[$default_locale][$name] ) : $default_options[$name];
				}
				$new_options[$loc]	= apply_filters( $this->args->option_name.'_after_escape_settings', $new_options[$loc], $default_options, $functions );
			}

			$this->opts->options = $new_options;
		}

		return $for_locale ? ( !empty($this->opts->options[$locale]) ? $this->opts->options[$locale] : array() ) : $this->opts->options;
	}


	// !Return an array of "sub-options". Only one level to keep it simple.

	static public function get_sub_options( $name = false, $options = array() ) {
		if ( empty($options) || !$name )
			return array();

		$options = (array) $options;

		if ( isset($options[$name]) )
			return $options[$name];

		$group	= array();
		$name	= rtrim($name, '.').'.';
		foreach ( $options as $k => $v ) {
			if ( strpos($k, $name) === 0 )
			$group[substr($k, strlen($name))] = $v;
		}
		return !empty($group) ? $group : null;
	}


	// !If you have trouble with the static cache for the options (triggered too soon, need an update), you can clear it here, before rebuilding all of this.

	public function maybe_clear_options_cache( $force = false ) {
		if ( $force || apply_filters($this->args->option_name.'_clear_options_cache', false) ) {
			$this->opts->options = null;
			$this->opts->options_default = null;
			$this->opts->options_initial = null;
			$this->opts->options_translatable = null;
			$this->opts->sanitization_functions = null;
			$this->opts->escape_functions = null;
			remove_all_filters( $this->args->option_name.'_clear_options_cache' );
		}
	}


	/*-------------------------------------------------------------------------------*/
	/* !Update options, sanitization, escape ======================================= */
	/*-------------------------------------------------------------------------------*/

	// !Return an array of functions for escape purpose (used when you output/display/get the options)
	// See http://codex.wordpress.org/Data_Validation#Output_Sanitation

	public function escape_functions( $option = false ) {

		$this->maybe_clear_options_cache();

		if ( empty($this->opts->escape_functions) )
			$this->opts->escape_functions = apply_filters( $this->args->option_name.'_escape_functions', array() );

		if ( $option )
			return isset($this->opts->escape_functions[$option]) ? $this->opts->escape_functions[$option] : array( 'function' => 'esc_attr', 'array_map' => 'esc_attr' );

		return $this->opts->escape_functions;
	}


	// !Return an array of functions for sanitization purpose (used when you save the options into the database)
	// See http://codex.wordpress.org/Data_Validation#Input_Validation

	public function sanitization_functions( $option = false ) {

		$this->maybe_clear_options_cache();

		if ( empty($this->opts->sanitization_functions) )
			// We don't start with an empty array this time, but with the escape functions. Then we'll merge them with the sanitization functions (escape functions are the most important imho).
			$this->opts->sanitization_functions = apply_filters( $this->args->option_name.'_sanitization_functions', $this->escape_functions() );

		if ( $option )
			return isset($this->opts->sanitization_functions[$option]) ? $this->opts->sanitization_functions[$option] : array( 'function' => 'esc_attr', 'array_map' => 'esc_attr' );

		return $this->opts->sanitization_functions;
	}


	/**
	 * !Return the sanitized/escaped option (used to get or save an option)
	 * @var $name string (required): the option name
	 * @var $value mixed (required): the option value
	 * @var $functions array: the sanitization/escape functions
	 * @return $value mixed: the sanitized/escaped option value
	 */
	public function sanitize_option( $name, $value, $functions = array(), $default_value = '' ) {

		if ( !is_array( $functions ) || empty( $functions ) )
			$functions = $this->escape_functions();

		$fa = !empty( $functions[$name] ) ? $functions[$name] : array();

		if ( !is_array( $value ) && isset( $fa['function'] ) )
			return self::sanitize_option_function( $value, $fa, $default_value );

		if ( is_array( $value ) && isset( $fa['array_map'] ) )
			return self::sanitize_option_map( $value, $fa, $default_value );

		if ( isset( $fa['function'] ) )
			return self::sanitize_option_function( $value, $fa, $default_value );

		if ( isset( $fa['array_map'] ) )
			return self::sanitize_option_map( $value, $fa, $default_value );

		return esc_attr( $value );
	}


	// !Return the sanitized/escaped value when "function" is used as sanitization/escape method

	static protected function sanitize_option_function( $value, $fa, $default_value = '' ) {
		if ( isset( $fa['params'] ) ) {
			$values = array_merge( array($value), (array) $fa['params'] );
		}
		elseif ( isset( $fa['param'] ) ) {
			$values = array_merge( array($value), array( $fa['param'] ) );
		}
		else {
			return call_user_func( $fa['function'], $value );
		}

		if ( isset($values['%def%']) )
			$values['%def%'] = $default_value;
		return call_user_func_array( $fa['function'], $values );
	}


	// !Return the sanitized/escaped value when "array_map" is used as sanitization/escape method

	static protected function sanitize_option_map( $value, $fa, $default_value = '' ) {
		$value = (array) $value;
		if ( isset( $fa['params'] ) )
			$params = (array) $fa['params'];
		elseif ( isset( $fa['param'] ) )
			$params = array( $fa['param'] );
		else
			return array_map( $fa['array_map'], $value );

		foreach ( $value as $k => $v ) {
			if ( isset($params['%def%']) )
				$params['%def%'] = isset($default_value[$k]) ? $default_value[$k] : '';
			$value[$k] = call_user_func_array( $fa['array_map'], array_merge( array( $v ), $params) );
		}
		return $value;
	}


	// !Update some options for the current locale.

	public function update_options( $new_values = array() ) {

		$locale				= Noop_i18n::get_locale( $this->args->option_group.'-'.$this->args->option_name );

		$old				= $this->get_options( false );	// Get all options
		$old[$locale]		= array_merge($old[$locale], $new_values);

		// Clone the non translatable fields to all languages
		$translatables		= $this->get_translatable_options();
		$non_translatables	= array_diff_key( $old[$locale], $translatables );
		if ( count($non_translatables) ) {
			$languages		= Noop_i18n::get_languages();
			foreach ( $languages as $lang ) {
				if ( $lang == $locale )
					continue;
				$old[$lang]	= array_merge($old[$lang], $old[$locale]);
			}
		}

		$this->opts->options = null;	// Empty the cache
		return $this->update_blog_option( $this->args->option_name, $old );
	}


	// !Sanitize and validate settings. Triggered on update_option().
	// Used in {Noop_Admin}->register_settings()

	public function sanitize_settings( $new = array() ) {

		$old			= $this->get_options( false );	// Get all options

		if ( is_null($new) )
			return $old;

		// Is it a form submission?
		$screen			= get_current_screen();
		$is_form_submit	= is_admin() && !is_null( $screen ) && $screen->id == 'options' && $screen->base == 'options' && isset($_POST['noop'], $_POST[$this->args->option_name], $_POST['action']) && $_POST['action'] == 'update';

		// All the i18n-related stuff.
		$default_locale	= Noop_i18n::get_default_locale( $this->args->network_menu );
		$languages		= Noop_i18n::get_languages();

		if ( $is_form_submit ) {
			// Check if the current user can update options
			if ( !current_user_can( $this->args->capability ) ) {
				add_settings_error( $this->args->option_group, 'capability', __('You do not have permission to do that.') );
				return $old;
			}

			// Check nonce or referer
			if ( empty($_POST['_wpnonce']) || !check_admin_referer( $this->args->option_group.'-options', '_wpnonce' ) )
				wp_die(__('Cheatin&#8217; uh?'));

			// Check locale (and tab)
			$locale			= !empty($_POST['noop']['locale']) ? esc_attr($_POST['noop']['locale']) : false;

			if ( !$locale || !in_array($locale, $languages) || empty($_POST['noop']['tab']) || $_POST['noop']['tab'] != $this->args->option_name )
				return $old;

			// Reset settings
			if ( !empty($_POST['noop']['reset']) ) {
				add_settings_error( $this->args->option_group, 'reset', __("Your settings have been reset.", 'noop'), 'updated' );

				$this->opts->options	= array_fill_keys( $languages, $this->get_initial_options() );
				return $this->opts->options;
			}
		}
		else {
			$locale		= Noop_i18n::get_locale( $this->args->option_group.'-'.$this->args->option_name );
		}

		//-- Start sanitization process --//
		$defaults		= $this->get_default();
		$functions		= $this->sanitization_functions();
		$translatables	= $this->get_translatable_options();

		if ( !count($defaults) )
			return $opts;

		// raw new values
		$new[$locale]	= apply_filters( $this->args->option_name.'_before_sanitize_settings', $new[$locale], $defaults, $functions );

		// Sanitize options and provide default values for the empty ones
		if ( $default_locale === $locale ) {
			foreach( $defaults as $name => $def ) {
				$old[$locale][$name] = isset($new[$locale][$name]) && !self::fall_to_default( $new[$locale][$name], $def ) ? $this->sanitize_option( $name, $new[$locale][$name], $functions, $def ) : $def;
			}
		}
		// If it's not the default language, loop through the translatable fields only
		else {
			$old[$locale]	= $old[$default_locale];	// Make sure all fields are filled (they should be already).
			$defaults_temp	= array_intersect_key($defaults, $translatables);
			if ( count( $defaults_temp ) ) {
				foreach( $defaults_temp as $name => $def ) {
					$old[$locale][$name] = isset($new[$locale][$name]) && !self::fall_to_default( $new[$locale][$name], $def ) ? $this->sanitize_option( $name, $new[$locale][$name], $functions, $def ) : $def;
				}
			}
		}

		// Sanitized new values
		$old[$locale]	= apply_filters( $this->args->option_name.'_after_sanitize_settings', $old[$locale], $defaults, $functions );

		// Validate options and (maybe) trigger error messages
		$context		= $is_form_submit ? 'save-form' : 'save-manual';
		$old[$locale]	= $this->validate_settings( $old[$locale], $defaults, $context );

		// If we have some non translatable fields, clone them to all other languages.
		if ( $default_locale === $locale ) {
			$non_translatables	= array_diff_key( $old[$locale], $translatables );
			if ( count($non_translatables) ) {
				foreach ( $languages as $lang ) {
					if ( $lang == $locale )
						continue;
					$old[$lang]	= array_merge($old[$lang], $non_translatables);
				}
			}
		}

		// The final filter
		$old			= apply_filters( $this->args->option_name.'_sanitize_settings', $old, $defaults, $functions );

		if ( $is_form_submit ) {
			// History
			$restore_date	= !empty($_POST['noop']['restore_date']) ? esc_attr($_POST['noop']['restore_date']) : false;
			$this->add_option_to_history( $old, false, $restore_date, true );
		}

		$this->opts->options = $old;
		return $this->opts->options;
	}


	// !Validate settings and (maybe) trigger error messages
	// $context can have 3 possible values: "save-form" on form submission update, "save-manual" on manual update with $this->update_options(), "permanent-errors" when we visit the settings page.
	// Settings errors should be triggered only on "save-form" or "permanent-errors".

	public function validate_settings( $opts = array(), $default_options = array(), $context = 'permanent-errors' ) {

		if ( $context == 'permanent-errors' ) {
			// Display History message
			if ( Noop_Settings::use_history() && !empty($_GET['hist']) ) {
				if ( $date = Noop_Settings::get_restore_date() ) {
					$date_timestamp = Noop_Utils::mysql2timestamp( $date );
					add_settings_error(
						$this->args->option_group, 'time-machine',
						sprintf(
							__("These are your settings saved at the date of %s.", 'noop'),
							sprintf( __('%1$s at %2$s'), date_i18n( get_option('date_format'), $date_timestamp), date_i18n( get_option('time_format'), $date_timestamp) )
						),
						'updated'
					);
				}
				else {
					add_settings_error(
						$this->args->option_group, 'time-machine',
						__("It seems that date does not exist in your timeline.", 'noop')
					);
				}
			}
		}

		return apply_filters( $this->args->option_name.'_validate_settings', $opts, $default_options, $context );
	}


	// !History++

	public function add_option_to_history( $opts = array(), $date = false, $remove = false, $trigger_errors = false ) {

		if ( ! apply_filters( $this->args->option_group.'-'.$this->args->option_name.'_use_history', true ) )
			return;

		$history = $this->get_blog_option( $this->args->option_name . '_history' );
		if ( empty($history) || !is_array($history) )
			$history = array();

		// The settings will be stored at a custom date
		if ( $date ) {
			$date = esc_attr( str_replace('@', ' ', $date) );
			$date_arr = Noop_Utils::explode_date( $date );
			if ( Noop_Utils::checkdate( $date_arr['mm'], $date_arr['jj'], $date_arr['aa'], $date ) ) {
				$date_arr['hh'] = Noop_Utils::min_max( $date_arr['hh'], 0, 23 );
				$date_arr['mn'] = Noop_Utils::min_max( $date_arr['mn'], 0, 59 );
				$date_arr['ss'] = Noop_Utils::min_max( $date_arr['ss'], 0, 59 );
				$date = $date = sprintf( "%04d-%02d-%02d %02d:%02d:%02d", $date_arr['aa'], $date_arr['mm'], $date_arr['jj'], $date_arr['hh'], $date_arr['mn'], $date_arr['ss'] );
			}
			else {
				$date = gmdate('Y-m-d H:i:s', Noop_Utils::timestamp_gmt_to_local( time() ));
			}
		}
		else {
			$date = gmdate('Y-m-d H:i:s', Noop_Utils::timestamp_gmt_to_local( time() ));;
		}

		// Remove old settings
		if ( $remove) {
			$remove = esc_attr( str_replace('@', ' ', $remove) );
			if ( !empty($history[$remove]) ) {
				unset($history[$remove]);
				if ( $trigger_errors )
					$remove_timestamp = Noop_Utils::mysql2timestamp( $remove );
					add_settings_error(
						$this->args->option_group, 'time-machine',
						sprintf(
							__("Your settings have been restored from %s.", 'noop'),
							sprintf( __('%1$s at %2$s'), date_i18n( get_option('date_format'), $remove_timestamp), date_i18n( get_option('time_format'), $remove_timestamp) )
						),
						'updated'
					);
			}
			elseif ( $trigger_errors ) {
				add_settings_error(
					$this->args->option_group, 'time-machine',
					__("Something went wrong, that date does not exist in your timeline.", 'noop')
				);
			}
		}

		// Add the new settings
		$history[$date] = apply_filters($this->args->option_name . '_add_option_to_history', array( 'opts' => $opts ), $date, $remove);
		// Limit to 10
		while ( count($history) > 10 ) {
			array_shift( $history );
		}

		$this->update_blog_option( $this->args->option_name . '_history', $history );
	}


	// !Import settings from a json file.

	static public function import_settings() {

		if ( empty($_POST['_noop_imp_nonce']) || !check_admin_referer( 'noop-import-settings', '_noop_imp_nonce' ) )
			wp_die(__('Cheatin&#8217; uh?'));

		if ( empty( $_POST['option-name'] ) )
			wp_die(__('Cheatin&#8217; uh?'));

		$option_name	= esc_attr( $_POST['option-name'] );
		$instance		= self::get_instance( $option_name );

		if ( $instance ) {

			$props		= Noop::get_props( $option_name );
			if ( !$props->capability || !current_user_can( $props->capability ) ) {
				wp_die(__('Cheatin&#8217; uh?'));
			}

			if ( empty( $_FILES['noop-import-file'] ) ) {
				add_settings_error('general', 'no_file', __('No files sent.', 'noop'), 'error');
			}
			else {
				$file = $_FILES['noop-import-file'];
				if ( $file['error'] ) {
					switch ( $file['error'] ) {
						case 1:
							add_settings_error( 'general', 'server_upload_size', __('Server maximum upload size exceeded.', 'noop'), 'error' );
							break;
						case 2:
							add_settings_error( 'general', 'form_upload_size', __('Form maximum upload size exceeded.', 'noop'), 'error' );
							break;
						case 3:
							add_settings_error( 'general', 'interrupted', __('Upload interrupted.', 'noop'), 'error' );
							break;
						case 4:
							add_settings_error( 'general', 'empty_file', __('Empty file.', 'noop'), 'error' );
							break;
					}
				}
				elseif ( empty( $file['tmp_name'] ) || ! realpath( $file['tmp_name'] ) ) {
					add_settings_error( 'general', 'no_file', __('No files sent.', 'noop'), 'error' );
				}
				elseif ( $file['type'] != 'application/json' ) {
					add_settings_error('general', 'wrong_file_type', __( 'That is not a <samp>.json</samp> file.', 'noop'), 'error' );
				}
				else {
					$file_content = file_get_contents( $file['tmp_name'] );
					@unlink( $file['tmp_name'] );

					$file_content = json_decode( trim( $file_content ), true );
					$file_content = apply_filters( 'noop-import-file-content', $file_content, $props );

					if ( empty( $file_content ) || !is_array( $file_content ) || empty( $file_content[ $option_name ] ) || !is_array( $file_content[ $option_name ] ) ) {
						$message = $props->plugin_is_plugin ? __( 'Sorry, but this file is not meant for this plugin.', 'noop' ) : __( 'Sorry, but this file is not meant for this theme.', 'noop' );
						add_settings_error( 'general', 'wrong_plugin', $message, 'error' );
					}
					else {
						$file_content	= $file_content[ $option_name ];
						// Merge old values with the new ones
						$old			= $instance->get_options( false );
						if ( !empty( $old ) ) {
							foreach ( $old as $loc => $o ) {
								$file_content[ $loc ] = !empty( $file_content[ $loc ] ) && is_array( $file_content[ $loc ] ) ? array_merge( $o, $file_content[ $loc ] ) : $o;
							}
						}
						// Sanitize and save
						$instance->update_blog_option( $option_name, $file_content );
						// Get saved options and add them to history
						$file_content	= $instance->get_blog_option();
						$file_content	= $instance->add_option_to_history( $file_content, false, false, true );

						do_action( 'noop-settings-imported', $props );

						if ( ! get_settings_errors() ) {
							add_settings_error( 'general', 'settings_updated', __('Settings imported.', 'noop'), 'updated' );
						}
					}
				}
			}

		}
		else {
			add_settings_error( 'general', 'export_no_instance', __( 'Something went wrong, it seems those settings do not exist.', 'noop' ) );
		}

		set_transient('settings_errors', get_settings_errors(), 30);

		$goback = add_query_arg( 'settings-updated', 'true',  wp_get_referer() );
		wp_redirect( $goback );
		exit;
	}


	// !Export settings in a json file.

	static public function export_settings() {

		if ( empty($_POST['_noop_exp_nonce']) || !check_admin_referer( 'noop-export-settings', '_noop_exp_nonce' ) )
			wp_die(__('Cheatin&#8217; uh?'));

		if ( empty( $_POST['option-name'] ) )
			wp_die(__('Cheatin&#8217; uh?'));

		$option_name	= esc_attr( $_POST['option-name'] );
		$instance		= self::get_instance( $option_name );

		if ( $instance ) {

			$props		= Noop::get_props( $option_name );
			if ( !$props->capability || !current_user_can( $props->capability ) ) {
				wp_die(__('Cheatin&#8217; uh?'));
			}

			if ( ini_get( 'zlib.output_compression' ) ) {
				ini_set( 'zlib.output_compression', 'Off' );
			}

			set_time_limit(0);

			$filename	= !empty( $props->plugin_page_title ) ? $props->plugin_page_title : $option_name;
			$filename	= apply_filters( 'noop-export-filename', $filename . '-export', $props );
			$filename	= mb_strtolower( sanitize_file_name( $filename . '.json' ), 'UTF-8' );

			ob_start();
			// New headers
			nocache_headers();
			header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );
			header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
			header( 'Content-Transfer-Encoding: binary' );
			header( 'Connection: close' );
			ob_end_clean();
			flush();

			$file_content = array( $option_name => $instance->get_options( false ) );
			$file_content = apply_filters( 'noop-export-file-content', $file_content, $props );

			echo json_encode( $file_content );
			die;

		}

		add_settings_error( 'general', 'export_no_instance', __( 'Something went wrong, it seems those settings do not exist.', 'noop' ) );

		set_transient('settings_errors', get_settings_errors(), 30);

		$goback = add_query_arg( 'settings-updated', 'true', wp_get_referer() );
		wp_redirect( $goback );
		exit;
	}


	/*-------------------------------------------------------------------------------*/
	/* ! Activation / Uninstall ==================================================== */
	/*-------------------------------------------------------------------------------*/

	public function activation() {
		$opts = $this->get_blog_option();

		if ( !$opts || !is_array($opts) || empty($opts) ) {
			if ( !class_exists('Noop_i18n') )
				return;

			// Create initial options
			$init	= self::get_instance( $this->args->option_name )->get_initial_options();
			$langs	= Noop_i18n::get_available_languages();
			if ( count($langs) ) {
				$opts	= array_fill_keys( $langs, $init );
			}
			else {
				$default_locale	= Noop_i18n::get_default_locale( $this->args->network_menu );
				$opts = array( $default_locale => $init );
			}
			$this->update_blog_option( $this->args->option_name, $opts );
		}

		do_action( $this->args->option_name.'_activation' );
	}


	// Must be launched from outside, by the plugin itself (with a static class or a function).
	// Can be used as a static method by using the 3 parameters. In this case, you have to run the register_plugin() method by yourself with add_filter().
	// To get this to work, this file must be included directly, not within a hook (it's too late).

	public function uninstall( $option_name = false, $page_parent_name = false, $page_name = false ) {
		$option_name		= $option_name		? $option_name		: $this->args->option_name;
		$page_parent_name	= $page_parent_name	? $page_parent_name	: $this->args->page_parent_name;
		$page_name			= $page_name		? $page_name		: $this->args->page_name;

		// Remove the main option and the history option
		delete_option( $option_name );
		delete_option( $option_name.'_history' );
		if ( is_multisite() ) {
			$this->delete_blog_option();
			$this->delete_blog_option( $option_name.'_history' );
		}

		// Remove the users metadatas, reguarding the metaboxes placement
		delete_metadata('user', 0, 'screen_layout_'.$page_parent_name.'_page_'.$page_name, null, true);
		delete_metadata('user', 0, 'metaboxhidden_'.$page_parent_name.'_page_'.$page_name, null, true);
		delete_metadata('user', 0, 'meta-box-order_'.$page_parent_name.'_page_'.$page_name, null, true);
		delete_metadata('user', 0, 'closedpostboxes_'.$page_parent_name.'_page_'.$page_name, null, true);
		if ( is_multisite() ) {
			delete_metadata('user', 0, 'screen_layout_'.$page_parent_name.'_page_'.$page_name.'-network', null, true);
			delete_metadata('user', 0, 'metaboxhidden_'.$page_parent_name.'_page_'.$page_name.'-network', null, true);
			delete_metadata('user', 0, 'meta-box-order_'.$page_parent_name.'_page_'.$page_name.'-network', null, true);
			delete_metadata('user', 0, 'closedpostboxes_'.$page_parent_name.'_page_'.$page_name.'-network', null, true);
		}

		do_action($option_name.'_uninstall');
	}

}
endif;
/**/