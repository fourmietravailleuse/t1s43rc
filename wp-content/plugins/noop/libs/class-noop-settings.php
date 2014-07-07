<?php
if( !defined( 'ABSPATH' ) )
	die( 'Cheatin\' uh?' );

/*-----------------------------------------------------------------------------------*/
/* !Noop SETTINGS CLASS ============================================================ */
/* Used to create the settings page.												 */
/* Requires: Noop, Noop_i18n, Noop_Options, Noop_Utils								 */
/* 				   Noop_Fields, Noop_Values, Noop_Admin								 */
/*-----------------------------------------------------------------------------------*/

if ( !class_exists('Noop_Settings') ) :
class Noop_Settings {

	const VERSION = '0.9.6';
	protected static $instance  = array();
	protected static $init_done = array();
	protected static $opts = array(
		'fields_args'		=> array(),
		'tabs'				=> array(),
		'current_tab'		=> null,
		'main_tab'			=> null,
		'is_main_tab'		=> null,
		'current_url'		=> null,
		'use_history'		=> null,
		'use_import_export'	=> null,
		'restore_date'		=> null,
		'non_tr_flieds'		=> 0,
	);
	protected static $args;


	/*-------------------------------------------------------------------------------*/
	/* !INSTANCE AND PROPERTIES ==================================================== */
	/*-------------------------------------------------------------------------------*/

	protected function __construct( $args ) {

		if ( !empty(self::$args->option_name) && self::$args->option_name == $args->option_name ) {
			return self::$instance;
		}

		self::$args = $args;
		self::$opts = (object) self::$opts;

		// Init
		if ( empty(self::$init_done) ) {
			self::$opts->main_tab	= self::$args->option_name;
			self::$init_done[]		= self::$args->page_parent_name.'_page_'.self::$args->page_name.(is_network_admin() ? '-network' : '');

			// Tweak to use Noop settings errors
			add_action( 'all_admin_notices', array( __CLASS__, 'shunt_options_settings_errors' ), PHP_INT_MAX );

			// Styles and scripts
			add_action( 'load-'.self::$args->page_parent_name.'_page_'.self::$args->page_name, array( __CLASS__, 'settings_style' ), 9 );

			// Settings
			add_action( 'load-'.self::$args->page_parent_name.'_page_'.self::$args->page_name, array( __CLASS__, 'add_settings' ), 9 );

			// Language tabs
			add_action( self::$args->page_name.'_before_form', array( __CLASS__, 'languages_tabs' ), 2 );
		}

		self::$instance = $this;
	}


	/**
	 * !Static Singleton Factory Method
	 * @return one of the instances
	 */
	static public function get_instance( $args = false ) {
		if ( !$args ) {		// PEBCAK
			_doing_it_wrong( __CLASS__.'::'.__METHOD__, '"U Can\'t Touch This".');
			return null;
		}

		if ( is_string($args) ) {
			$option_name = $args;
		}
		elseif ( is_array($args) && !empty($args['option_name']) ) {
			$option_name = $args['option_name'];
		}
		elseif ( is_object($args) && !empty($args->option_name) ) {
			$option_name = $args->option_name;
		}
		else {
			return null;
		}

		if ( is_a(self::$instance, __CLASS__) && !empty(self::$args->option_name) && self::$args->option_name == $option_name ) {
			return self::$instance;
		}

		if ( empty(self::$instance) ) {	// Singleton
			$args = Noop::get_instance( $args );
			if ( !is_null( $args ) ) {
				$className	= __CLASS__;
				$args		= Noop::get_props( $option_name );
				return new $className( $args );
			}
		}
	}


	// !Return the class properties

	public function get_properties() {
		return (object) self::$opts;
	}


	/*-------------------------------------------------------------------------------*/
	/* !TWEAKS ===================================================================== */
	/*-------------------------------------------------------------------------------*/

	// If the parent page is 'options-general.php', WP will automaticaly use settings_errors().
	// But the alerts will be displayed before the page title, and then some JS will move it after the title.
	// Under some circumstances (large page, slow browser), the "swap" won't happen fast enough, and the user will see it.
	// I prefer use my own settings_errors() (already displayed AFTER the title). See the end of self::screen_title_or_tabs().

	public static function shunt_options_settings_errors() {
		global $parent_file;
		$parent_file .= '#noop';	// Prevent wp-admin/options-head.php to be included.
	}


	/*-------------------------------------------------------------------------------*/
	/* !STYLES AND SCRIPTS ========================================================= */
	/*-------------------------------------------------------------------------------*/

	static public function settings_style() {
		$suffix	= defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';
		$ver	= defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? time() : self::VERSION;
		wp_enqueue_style( 'noop-settings', self::$args->noop_url.'res/css/settings'.$suffix.'.css', false, $ver, 'all' );

		wp_register_script( 'noop-settings', self::$args->noop_url.'res/js/settings'.$suffix.'.js', array('jquery'), $ver, true );
		wp_localize_script( 'noop-settings', 'NoopSettingsL10n', array( 'del' => __("Delete"), 'help' => esc_attr__('Help') ) );

		wp_register_script( 'noop-findposts', self::$args->noop_url.'res/js/findposts'.$suffix.'.js', array('jquery'), $ver, true );
		wp_localize_script( 'noop-findposts', 'noopAttachMediaBoxL10n', array( 'error' => __( 'An error has occurred. Please reload the page and try again.' ), 'is39' => (version_compare( $GLOBALS['wp_version'], '3.9' ) > 0 ? 1 : 0) ) );

		// Icon
		add_action( 'admin_print_styles-'.self::$args->page_parent_name.'_page_'.self::$args->page_name, array( __CLASS__, 'settings_icon_style' ) );
	}


	// Page icon

	static public function settings_icon_style() {
		if ( self::$args->plugin_logo_url && strpos(self::$args->plugin_logo_url, 'http') === 0 ) {
			$icon_id = esc_attr( self::$args->page_name );
			echo '<style type="text/css">';
				echo '.wrap #icon-' . $icon_id . '{background:transparent url("' . esc_url( self::$args->plugin_logo_url ) . '") 0 0 no-repeat}';
				echo '@media print,(-o-min-device-pixel-ratio:5/4),(-webkit-min-device-pixel-ratio:1.25),(min-resolution:120dpi){.wrap #icon-' . $icon_id . '{background-position:-20px 0;background-size:56px auto}}';
			echo "</style>\n";
		}
	}


	/*-------------------------------------------------------------------------------*/
	/* !SETTINGS =================================================================== */
	/*-------------------------------------------------------------------------------*/

	// !Add settings sections, fields, metaboxes, etc to the settings page

	static public function add_settings() {
		global $wp_settings_sections, $wp_meta_boxes;

		if ( !class_exists('Noop_Fields') ) {
			include( self::$args->noop_path . 'libs/class-noop-fields.php' );
		}
		if ( !class_exists('Noop_Values') ) {
			include( self::$args->noop_path . 'libs/class-noop-values.php' );
		}

		if ( !self::is_an_option_screen_tab() ) {
			return;
		}

		// Load tab options
		if ( !self::is_main_screen_tab() ) {
			$new_args	= Noop::get_instance( self::get_current_screen_tab() );
			if ( is_null($new_args) ) {
				return;
			}
			$intersect = array(
				'option_name'		=> '',
				'capability'		=> '',
			);
			$new_args	= (array) $new_args->get_properties();
			$new_args	= array_intersect_key($new_args, $intersect);
			self::$args	= (object) array_merge((array) self::$args, $new_args);
		}

		// Noop_Options lib: get the options
		$noop_options	= Noop_Options::get_instance( self::$args );
		if ( is_null($noop_options) ) {
			return;
		}

		// Noop_Fields lib: contains common fields
		$noop_fields = Noop_Fields::get_instance( self::$args );
		if ( is_null($noop_fields) ) {
			return;
		}

		$options		= $noop_options->get_options();
		$locale			= Noop_i18n::get_locale( self::$args->option_group.'-'.self::$args->option_name );
		$defaults		= $noop_options->get_default();
		// Display old settings
		if ( $hist = self::get_past_options() ) {
			$options	= !empty($hist['opts'][$locale]) ? array_merge($defaults, $hist['opts'][$locale]) : $defaults;
		}
		$translatables	= $noop_options->get_translatable_options();
		self::$opts->fields_args = array(
			'options'		=> $options,
			'defaults'		=> $defaults,
			'translatables'	=> $translatables,
			'locales'		=> array(
				'locale'		=> $locale,
				'default'		=> Noop_i18n::get_default_locale( is_network_admin() ),
				'languages'		=> Noop_i18n::get_languages()
			)
		);
		// These values will be passed to the fields via the Noop_Settings::add_field method
		self::$opts->fields_args = apply_filters( self::$args->option_group.'_fields_args', self::$opts->fields_args, self::$args );

		// Trigger permanent errors
		Noop_Options::get_instance( self::$args )->validate_settings( $options, $defaults );

		// Debug metaboxes
		self::debug_metaboxes( $noop_options );

		// Add sections and fields here
		do_action( self::$args->option_group.'-'.self::$args->option_name.'_add_fields', self::$opts->fields_args, self::$args );

		// Deal with translatable fields
		self::remove_non_translatable_fields( $noop_fields );

		// Remove empty sections
		self::remove_empty_sections();

		// Advanced fields in separate files: include the files
		self::include_advanced_fields( $noop_fields );

		// Display a short message if there's nothing to display
		if( empty($wp_settings_sections[self::$args->page_name]) ) {
			self::add_section( 'noop', Noop_Fields::section_icon( 'comments' ) . __('It seems there\'s nothing to see here...', 'noop') );
		}

		// Print the hidden fields (locale, restore date, tab)
		add_action( self::$args->page_name.'_hidden_fields', array( __CLASS__, 'hidden_fields' ) );

		// Screen option: 'layout_columns' for the metaboxes
		if ( !empty($wp_meta_boxes[self::$args->page_parent_name.'_page_'.self::$args->page_name.(is_network_admin() ? '-network' : '')]) ) {
			$nbr_cols	= apply_filters( self::$args->page_name.'_settings_max_metabox_cols', 4 );
			$nbr_cols	= Noop_Utils::min_max( $nbr_cols, 1, 4 );
			add_screen_option('layout_columns', array('max' => $nbr_cols, 'default' => 3) );
			wp_enqueue_script( 'postbox' );
			wp_enqueue_script( 'noop-settings' );
		}
	}


	// !do_settings_sections()-like

	static public function do_sections( $page ) {
		global $wp_settings_sections, $wp_settings_fields;

		if ( ! isset( $wp_settings_sections[$page] ) ) {
			return;
		}

		foreach ( (array) $wp_settings_sections[$page] as $section ) {
			if ( $section['title'] ) {
				echo "<h3>{$section['title']}</h3>\n";
			}

			if ( $section['callback'] ) {
				call_user_func( $section['callback'], $section );
			}

			if ( ! isset( $wp_settings_fields ) || !isset( $wp_settings_fields[$page] ) || !isset( $wp_settings_fields[$page][$section['id']] ) ) {
				continue;
			}
			echo '<table class="form-table">';
			self::do_fields( $page, $section['id'] );
			echo '</table>';
		}
	}


	// !do_settings_fields()-like

	static public function do_fields($page, $section) {
		global $wp_settings_fields;

		if ( ! isset( $wp_settings_fields[$page][$section] ) ) {
			return;
		}

		foreach ( (array) $wp_settings_fields[$page][$section] as $field ) {
			$class = '';

			if ( !empty($field['args']['depends_on']) ) {				// Show/hide fields depending on other fields value
				$opts  = isset($field['args'][0]['params']['options']) ? $field['args'][0]['params']['options'] : $field['args']['options'];
				$show  = array();
				$and   = isset($field['args']['depends_on']['operator']) && $field['args']['depends_on']['operator'] == 'and';
				$class = '';
				unset($field['args']['depends_on']['operator']);
				foreach ( $field['args']['depends_on'] as $dep ) {		// array( array( 'option1-name', 'option1-value' ), array( 'option2-name', 'option2-value1,option2-value2' ), ... )
					$dep[1] = explode(',', $dep[1]);
					foreach ( $dep[1] as $dep_val ) {
						$class .= ' depends-' . str_replace('.', '-', $dep[0] . '___' . esc_attr($dep_val));

						if ( isset($opts[$dep[0]]) ) {
							if ( is_array($opts[$dep[0]]) ) {
								if ( in_array($dep_val, $opts[$dep[0]]) ) {
									$show[$dep[0]] = 1;
								}
							}
							elseif ( $opts[$dep[0]] == $dep_val ) {
								$show[$dep[0]] = 1;
							}
						}
					}
				}
				$class .= ($and && count($show) == count($field['args']['depends_on'])) || (!$and && !empty($show)) ? '' : ' hide-if-js';
				$class  = ' class="' . trim($class) . '"';
			}

			echo '<tr' . $class . '>';
			if ( !empty($field['args']['label_for']) ) {
				echo '<th scope="row"><label for="' . esc_attr( $field['args']['label_for'] ) . '">' . $field['title'] . '</label></th>';
			}
			else {
				echo '<th scope="row">' . $field['title'] . '</th>';
			}
			echo '<td>';
			call_user_func($field['callback'], $field['args']);
			echo '</td>';
			echo '</tr>';
		}
	}


	// !Add a section

	static public function add_section( $id, $title = '', $callback = false, $override = false ) {
		if ( !$override && self::section_exists( $id ) ) {
			return;
		}
		$callback = $callback ? $callback : array( 'Noop_fields', 'description_field' );
		add_settings_section( $id, $title, $callback, self::$args->page_name );
	}


	// !Remove a section

	static public function remove_section( $section, $remove_fields = false ) {
		unset( $GLOBALS['wp_settings_sections'][self::$args->page_name][$section] );
		if ( $remove_fields ) {
			unset( $GLOBALS['wp_settings_fields'][self::$args->page_name][$section] );
		}
	}


	// !Tell if a section exists

	static public function section_exists( $section ) {
		return isset( $GLOBALS['wp_settings_sections'][self::$args->page_name][$section] );
	}


	// !Add a field

	static public function add_field( $id, $title, $callback, $section, $args = array(), $override = false ) {
		if ( !$override && self::field_exists( $id, $section ) ) {
			return;
		}
		if ( is_array($callback) && end($callback) == 'multifields' ) {
			foreach ( $args as $i => $arg ) {
				if ( is_array($arg) && !empty($arg['params']) ) {
					$args[$i]['params'] = array_merge(self::$opts->fields_args, $args[$i]['params']);
				}
			}
		}
		else {
			$args = array_merge(self::$opts->fields_args, $args);
		}
		add_settings_field(
			$id,
			$title,
			$callback,
			self::$args->page_name,
			$section,
			$args
		);
	}


	// !Remove a field

	static public function remove_field( $id, $section, $remove_section = false ) {
		unset($GLOBALS['wp_settings_fields'][self::$args->page_name][$section][$id]);
		if ( $remove_section && empty($GLOBALS['wp_settings_fields'][self::$args->page_name][$section]) ) {
			unset($GLOBALS['wp_settings_fields'][self::$args->page_name][$section]);
		}
	}


	// !Tell if a field exists

	static public function field_exists( $id, $section ) {
		return isset( $GLOBALS['wp_settings_fields'][self::$args->page_name][$section][$id] );
	}


	// !Add a metabox

	static public function add_meta_box( $id, $title, $callback, $context, $args = array() ) {
		add_meta_box(
			$id,
			$title,
			$callback,
			self::$args->page_parent_name.'_page_'.self::$args->page_name.(is_network_admin() ? '-network' : ''),
			$context,
			'core',
			$args
		);
	}


	// !Remove a metabox

	static public function remove_meta_box( $id, $context ) {
		remove_meta_box(
			$id,
			self::$args->page_parent_name.'_page_'.self::$args->page_name.(is_network_admin() ? '-network' : ''),
			$context
		);
	}


	// !Tell if a metabox exists
	// @return (bool|string) false or priority.

	static public function meta_box_exists( $id, $context ) {
		$page = self::$args->page_parent_name.'_page_'.self::$args->page_name.(is_network_admin() ? '-network' : '');

		foreach ( array('high', 'core', 'default', 'low') as $priority ) {
			if ( !empty($GLOBALS['wp_meta_boxes'][$page][$context][$priority][$id]) ) {
				return $priority;
			}
		}
		return false;
	}


	// !Hidden fields (locale, restore date and tab)

	static public function hidden_fields() {
		echo '<input type="hidden" id="locale_value" name="noop[locale]" value="'.Noop_i18n::get_locale( self::$args->option_group.'-'.self::$args->option_name ).'"/>'."\n";
		echo '<input type="hidden" id="current_tab" name="noop[tab]" value="'.self::get_current_screen_tab().'"/>'."\n";
		if ( self::use_history() && ($date = self::get_restore_date()) ) {
			echo '<input type="hidden" id="restore_date" name="noop[restore_date]" value="'.$date.'"/>'."\n";
		}
	}


	/*-------------------------------------------------------------------------------*/
	/* !DEAL WITH SETTINGS FIELDS, SECTIONS AND METABOXES ========================== */
	/*-------------------------------------------------------------------------------*/

	// !Add Debug metaboxes

	static public function debug_metaboxes( $noop_options = false ) {
		$show = WP_DEBUG && self::is_an_option_screen_tab();
		if ( ! apply_filters( self::$args->page_name.'_show_debug_metaboxes', $show ) ) {
			return;
		}

		$nbr_cols		= apply_filters( self::$args->page_name.'_settings_max_metabox_cols', 4 );
		$nbr_cols		= Noop_Utils::min_max( $nbr_cols, 1, 4 );

		$noop_options	= $noop_options ? $noop_options : Noop_Options::getInstance( self::$args->option_name );
		$us_pagename	= str_replace('-', '_', self::$args->page_name);	// No "-" in context
		$box_no			= 1;
		$coef			= $nbr_cols / 7;

		self::add_meta_box(
			'debug-settings',
			sprintf(__('Debug: %s', 'noop'), __("Settings")),
			array('Noop_Fields', 'debug_field'),
			$us_pagename.'1',
			$noop_options->get_options()
		);
		$col = floor( ++$box_no * $coef );
		self::add_meta_box(
			'debug-all-settings',
			sprintf(__('Debug: %s', 'noop'), __("Settings for all languages", 'noop')),
			array('Noop_Fields', 'debug_field'),
			$us_pagename.$col,
			$noop_options->get_options(false)
		);
		$col = floor( ++$box_no * $coef );
		self::add_meta_box(
			'debug-defaults',
			sprintf(__('Debug: %s', 'noop'), __("Default values", 'noop')),
			array('Noop_Fields', 'debug_field'),
			$us_pagename.$col,
			$noop_options->get_default()
		);
		$col = floor( ++$box_no * $coef );
		self::add_meta_box(
			'debug-initial',
			sprintf(__('Debug: %s', 'noop'), __("Initial values", 'noop')),
			array('Noop_Fields', 'debug_field'),
			$us_pagename.$col,
			$noop_options->get_initial_options()
		);
		$col = floor( ++$box_no * $coef );
		self::add_meta_box(
			'debug-locale',
			sprintf(__('Debug: %s', 'noop'), __("Locale", 'noop')),
			array('Noop_Fields', 'debug_field'),
			$us_pagename.$col,
			Noop_i18n::get_locale( self::$args->option_group.'-'.self::$args->option_name )
		);
		$col = floor( ++$box_no * $coef );
		self::add_meta_box(
			'debug-default-locale',
			sprintf(__('Debug: %s', 'noop'), __("Default locale", 'noop')),
			array('Noop_Fields', 'debug_field'),
			$us_pagename.$col,
			Noop_i18n::get_default_locale( is_network_admin() )
		);
		self::add_meta_box(
			'debug-languages',
			sprintf(__('Debug: %s', 'noop'), __("Languages", 'noop')),
			array('Noop_Fields', 'debug_field'),
			$us_pagename.$nbr_cols,
			Noop_i18n::get_languages()
		);
	}


	// !Remove non translatable fields

	static public function remove_non_translatable_fields( $noop_fields = false ) {
		global $wp_settings_fields;

		if ( empty($wp_settings_fields[self::$args->page_name]) || Noop_i18n::get_default_locale( is_network_admin() ) === Noop_i18n::get_locale( self::$args->option_group.'-'.self::$args->option_name ) ) {
			return;
		}

		$noop_fields = $noop_fields ? $noop_fields : Noop_Fields::getInstance( self::$args->option_name );

		foreach ( $wp_settings_fields[self::$args->page_name] as $section_name => $section ) {
			foreach ( $section as $field_id => $field ) {
				if ( !empty($field['callback']) && $field['callback'] == array($noop_fields, 'multifields') && !empty($field['args']) && is_array($field['args']) ) {

					$keep_field = false;
					foreach ( $field['args'] as $arg_id => $arg ) {
						if ( empty($arg['params']) ) {
							continue;
						}
						$name = !empty($arg['params']['name']) ? reset((explode('|', $arg['params']['name']))) : (!empty($arg['params']['label_for']) ? reset((explode('|', $arg['params']['label_for']))) : false);
						if ( !$name || !in_array($name, self::$opts->fields_args['translatables']) ) {
							unset($wp_settings_fields[self::$args->page_name][$section_name][$field_id][$arg_id]);
						}
						else {
							$keep_field = true;
						}
					}
					if ( !$keep_field ) {
						self::$opts->non_tr_flieds++;
						unset($wp_settings_fields[self::$args->page_name][$section_name][$field_id]);
					}
					continue;

				}

				$name = !empty($field['args']['name']) ? reset((explode('|', $field['args']['name']))) : (!empty($field['args']['label_for']) ? reset((explode('|', $field['args']['label_for']))) : false);
				if ( !$name || !in_array($name, self::$opts->fields_args['translatables']) ) {
					unset($wp_settings_fields[self::$args->page_name][$section_name][$field_id]);
				}
			}
		}
	}


	// !Remove empty sections

	static public function remove_empty_sections() {
		global $wp_settings_sections, $wp_settings_fields;

		if ( !empty($wp_settings_sections[self::$args->page_name]) ) {
			foreach ( $wp_settings_sections[self::$args->page_name] as $section_name => $section ) {
				if ( empty($wp_settings_fields[self::$args->page_name][$section_name]) ) {
					unset($wp_settings_sections[self::$args->page_name][$section_name]);
				}
			}
		}
	}


	// !Include files for the advanced fields

	static public function include_advanced_fields( $noop_fields = false ) {
		global $wp_settings_fields;
		if ( empty($wp_settings_fields[self::$args->page_name]) ) {
			return;
		}

		$noop_fields = $noop_fields ? $noop_fields : Noop_Fields::getInstance( self::$args );

		$folders = array(
			path_join( get_stylesheet_directory(), 'noop-advanced-fields/' ),
			self::$args->noop_path . 'advanced-fields/',
		);
		$folders = apply_filters( self::$args->page_name . '_advanced_fields', $folders );

		foreach ( $folders as $folder ) {
			if ( !file_exists($folder) ) {
				continue;
			}
			foreach ( $wp_settings_fields[self::$args->page_name] as $section_id => $section ) {
				foreach ( $section as $field_id => $field ) {
					if ( !empty($field['callback']) ) {
						if ( is_array($field['callback']) && end($field['callback']) == 'multifields' && is_a(reset($field['callback']), 'Noop_Fields') && !empty($field['args']) && is_array($field['args']) ) {

							foreach ( $field['args'] as $arg_id => $arg ) {
								if ( !empty($arg['callback']) && is_string($arg['callback']) && file_exists($folder.$arg['callback'].'.php') ) {
									$wp_settings_fields[self::$args->page_name][$section_id][$field_id]['args'][$arg_id]['params']['page_name'] = self::$args->page_name;
									$wp_settings_fields[self::$args->page_name][$section_id][$field_id]['args'][$arg_id]['params']['option_name'] = self::$args->option_name;
									if ( !function_exists($arg['callback']) ) {
										include($folder.$arg['callback'].'.php');
									}
								}
							}

						}
						elseif ( is_string($field['callback']) && file_exists($folder.$field['callback'].'.php') ) {
							$wp_settings_fields[self::$args->page_name][$section_id][$field_id]['args']['page_name'] = self::$args->page_name;
							$wp_settings_fields[self::$args->page_name][$section_id][$field_id]['args']['option_name'] = self::$args->option_name;
							if ( !function_exists($field['callback']) ) {
								include($folder.$field['callback'].'.php');
							}
						}
					}
				}
			}
		}
	}


	/*-------------------------------------------------------------------------------*/
	/* !Settings form ============================================================== */
	/*-------------------------------------------------------------------------------*/

	static public function settings_page() {
		$wrap_class = 'wrap noop-form';
		if ( WP_DEBUG )
			$wrap_class .= ' wp-debug';
		if ( version_compare( $GLOBALS['wp_version'], '3.8-alpha', '>' ) )
			$wrap_class .= ' mp6';
		?>
		<div class="<?php echo $wrap_class; ?>">

			<?php
			self::screen_icon();
			$current_tab = self::screen_title_or_tabs();

			do_action(self::$args->page_name.'_before_form', $current_tab);

			// Display the form only on the first tab
			if ( self::is_an_option_screen_tab() ) :
			?>

			<form name="<?php echo self::$args->page_name; ?>" method="post" action="<?php echo admin_url('options.php'); ?>" id="<?php echo self::$args->page_name; ?>">
				<?php
				if ( self::show_top_submit() ) {
					submit_button( __("Save Changes"), 'primary', 'submit-top' );
				}

				// Sections and Fields
				self::do_sections( self::$args->page_name );

				// Hidden fields
				do_action( self::$args->page_name.'_hidden_fields' );
				self::settings_fields();

				// Submit
				global $wp_settings_fields;
				if ( !empty($wp_settings_fields[self::$args->page_name]) ) {
					if ( self::get_restore_date() && self::use_history() ) {
						echo '<p class="submit">';
							submit_button( __('Restore'), 'primary', 'submit', false );
							echo '<a class="button" href="'.esc_url(self::current_url()).'">'.__('Cancel').'</a>';
						echo "</p>\n";
					}
					else {
						submit_button();
						$reset_msg  = __( "Are you sure you want to reset your settings to default?", 'noop' );
						if ( self::use_multilang() ) {
							$reset_msg = __( "Are you sure you want to reset your settings to default? (this will affect all languages)", 'noop' );
						}
						$reset_attr = array('id' => 'reset', 'onclick' => "if ( !confirm(\"" . $reset_msg . "\") ) return false;");
						submit_button( __('Reset', 'noop'), 'delete', 'noop[reset]', true, $reset_attr );
					}
				}
				?>
			</form>

			<?php
			else:

				do_action( self::$args->page_name.'_tab_form', $current_tab );

			endif;

			do_action( self::$args->page_name.'_after_form', $current_tab );

			// Metaboxes
			global $wp_meta_boxes;
			$screen = get_current_screen();

			if ( !empty($wp_meta_boxes[$screen->id]) ) {
				$nbr_cols		= apply_filters( self::$args->page_name.'_settings_max_metabox_cols', 4 );
				$nbr_cols		= Noop_Utils::min_max( $nbr_cols, 1, 4 );
				$us_pagename	= str_replace('-', '_', self::$args->page_name);	// No "-" in context
				?>
				<div id="dashboard-widgets" class="metabox-holder columns-<?php echo $screen->get_columns(); ?>">
					<?php for ( $i = 1; $i <= $nbr_cols; $i++ ) : ?>
					<div id='postbox-container-<?php echo $i; ?>' class='postbox-container'><?php do_meta_boxes( $screen->id, $us_pagename.$i, $i ); ?></div>
					<?php endfor; ?>
				</div>
				<form method="get" action="">
					<?php
					wp_nonce_field('closedpostboxes', 'closedpostboxesnonce', false );
					wp_nonce_field('meta-box-order',  'meta-box-order-nonce', false );
					?>
				</form>
			<?php } ?>

		</div>
	<?php
	}


	/*-------------------------------------------------------------------------------*/
	/* !Utilities ================================================================== */
	/*-------------------------------------------------------------------------------*/

	// !Return the current url with only the page, locale and tab parameters

	static public function current_url() {

		if ( self::$opts->current_url ) {
			return self::$opts->current_url;
		}

		$url		= add_query_arg( 'page', self::$args->page_name, self_admin_url( self::$args->page_parent ) );
		if ( !self::is_main_screen_tab() ) {
			$url	= add_query_arg( 'tab', self::get_current_screen_tab(), $url );
		}

		if ( !empty( self::$args->option_group ) ) {
			$is_network	= is_network_admin();
			$locale		= Noop_i18n::get_locale( self::$args->option_group.'-'.self::$args->option_name );
			$def_locale	= Noop_i18n::get_default_locale( $is_network );
			if ( $locale !== $def_locale ) {
				$url	= add_query_arg( 'loc', $locale, $url );
			}
		}

		$url		= apply_filters( 'noop_current_url', $url, self::$args->option_name );
		self::$opts->current_url = Noop_Fields::ampersand( $url );
		return self::$opts->current_url;
	}


	// !Tell if we display a submit button on top of the page

	static public function show_top_submit() {
		global $wp_settings_fields;

		$show = false;
		$nbr_fields = 0;
		if ( !empty($wp_settings_fields[self::$args->page_name]) ) {
			foreach ( $wp_settings_fields[self::$args->page_name] as $section_id => $section ) {
				$nbr_fields += count( $section );
			}
			if ( ($nbr_fields + self::$opts->non_tr_flieds) >= 16 ) {
				$show = true;
			}
		}
		return apply_filters( self::$args->page_name.'_show_top_submit', $show, $nbr_fields );
	}


	// !settings_fields()
	// $_GET['hist'] must not be in the referer for the redirect

	static public function settings_fields() {
		echo '<input type="hidden" name="option_page" value="' . esc_attr(self::$args->option_group) . "\" />";
		echo '<input type="hidden" name="action" value="update" />';
		echo '<input type="hidden" name="_wpnonce" id="noop_wpnonce" value="' . wp_create_nonce( self::$args->option_group.'-options' ) . '" />';
		echo '<input type="hidden" name="_wp_http_referer" value="'. esc_attr( remove_query_arg( 'hist', self::unslash( $_SERVER['REQUEST_URI'] ) ) ) . '" />';
	}


	static public function unslash( $value ) {
		return function_exists('wp_unslash') ? wp_unslash( $value ) : stripslashes_deep( $value );
	}


	// !wp_nonce_field()
	// $_GET['hist'] must not be in the referer for the redirect
	// Can be used in an "action form" in another tab

	static public function nonce_field( $action = -1, $name = '_wpnonce' ) {
		$action = esc_attr($action);
		$name   = esc_attr($name);
		if ( !self::use_history() ) {
			echo '<input type="hidden" name="action" value="'.$action.'" />';
			wp_nonce_field( $action, $name );
		}
		else {
			echo '<input type="hidden" name="action" value="'.$action.'" />';
			echo '<input type="hidden" id="'.$name.'" name="'.$name.'" value="' . wp_create_nonce( $action ) . '" />';
			echo '<input type="hidden" name="_wp_http_referer" value="'. esc_attr( remove_query_arg( 'hist', self::unslash( $_SERVER['REQUEST_URI'] ) ) ) . '" />';
		}
	}


	// ! Like Noop_i18n::use_multilang() but consider if the option has translatable fields too.

	static public function use_multilang() {
		if ( empty( self::$args->option_group ) ) {
			return false;
		}
		$noop_options		= Noop_Options::get_instance( self::$args );
		$has_translatables	= is_null($noop_options) ? 0 : count($noop_options->get_translatable_options());

		return Noop_i18n::use_multilang() && $has_translatables;
	}


	// ! Check if a pointer has been dismissed

	static public function user_dismissed_pointer( $pointer = '' ) {
		if ( !$pointer ) {
			return true;
		}
		$pointer   = preg_replace('/[^a-z0-9_]/i', '', self::$args->option_name) . '_' . $pointer;
		$dismissed = (string) get_user_meta( get_current_user_id(), 'dismissed_wp_pointers', true );
		return strpos( ','.$dismissed.',', ','.$pointer.',' ) !== false;
	}


	// ! Shorthand to add an ajax pointer

	static public function add_pointer( $pointer = '', $atts = array() ) {
		global $wp_version;
		if ( version_compare($wp_version, '3.3', '<') || self::user_dismissed_pointer( $pointer ) ) {
			return;
		}

		$pointer = preg_replace('/[^a-z0-9_]/i', '', self::$args->option_name) . '_' . $pointer;
		$atts    = array_merge( array(
			'content'	=> '<h3>' . __('Custom pointer', 'noop') . '</h3><p>' . __('This is a custom pointer', 'noop') . '</p>',
			'target'	=> '#adminmenu',
			'edge'		=> 'top',			// which edge (left, right, top, bottom) should be adjacent to the target.
			'align'		=> 'left',			// how the pointer should be aligned on this edge, relative to the target (top, bottom, left, right, middle).
		), $atts);
		$atts['pointer_id'] = $pointer;
		$atts['l10n_print_after'] = 'if(!window.NoopAjaxPointers)var NoopAjaxPointers=[];NoopAjaxPointers.push("'.$pointer.'");';

		wp_enqueue_style( 'wp-pointer' );
		wp_enqueue_script( 'wp-pointer' );
		wp_enqueue_script( 'noop-settings' );
		wp_localize_script( 'noop-settings', $pointer, $atts );
	}


	/*-------------------------------------------------------------------------------*/
	/* !Multipages, tabs =========================================================== */
	/*-------------------------------------------------------------------------------*/

	// !Icon

	static public function screen_icon() {
		if ( version_compare($GLOBALS['wp_version'], '3.8-alpha', '>=' ) ) {
			return;
		}
		if ( self::$args->plugin_logo_url ) {
			if ( strpos(self::$args->plugin_logo_url, 'http') === 0 ) {	// It's an url
				screen_icon( self::$args->page_name );
			}
			else {														// It's a word, like 'index' or 'themes'
				screen_icon( self::$args->plugin_logo_url );
			}
		}
		elseif ( is_network_admin() ) {
			switch ( self::$args->page_parent_name ) {
				case 'index':
				case 'users':
				case 'themes':
				case 'plugins':
					$icon = self::$args->page_parent_name;
					break;
				case 'sites':
					$icon = 'ms-admin';
					break;
				case 'settings':
				case 'update-core':
					$icon = 'tools';
					break;
				default:
					$icon = '';
			}
			screen_icon( $icon );
		}
		else {
			screen_icon();
		}
	}


	// !Tabs

	static public function get_screen_tabs() {
		global $title;
		if ( empty(self::$opts->tabs) ) {
			$page_title = is_array( self::$args->plugin_page_title ) ? translate_nooped_plural(self::$args->plugin_page_title, 1) : self::$args->plugin_page_title;
			$main_tab   = array( self::$opts->main_tab => $page_title );
			self::$opts->tabs = apply_filters( self::$args->page_name.'_settings_tabs', array() );
			unset(self::$opts->tabs[self::$opts->main_tab]);
			self::$opts->tabs = array_merge( $main_tab, self::$opts->tabs );
		}
		return self::$opts->tabs;
	}


	// !Get current tab

	static public function get_current_screen_tab( $tabs = null ) {
		if ( !empty(self::$opts->current_tab) ) {
			return self::$opts->current_tab;
		}

		if ( is_null($tabs) ) {
			$tabs = self::get_screen_tabs();
		}

		if ( !is_array($tabs) || !count($tabs) ) {
			return false;
		}

		$first_tab = self::$opts->main_tab;

		$tab = !empty($_GET['tab']) ? esc_attr($_GET['tab']) : false;

		if ( !$tab || count($tabs) == 1 || !isset($tabs[$tab]) ) {
			$tab = $first_tab;
		}

		self::$opts->current_tab = $tab;
		return $tab;
	}


	// !Returns true if the current tab is the first tab

	static public function is_main_screen_tab() {
		if ( !empty(self::$opts->is_main_tab) ) {
			return self::$opts->is_main_tab;
		}
		$current = self::get_current_screen_tab();

		self::$opts->is_main_tab = !$current || $current == self::$opts->main_tab;
		return self::$opts->is_main_tab;
	}


	// !Returns true if the current tab is an "option tab"

	static public function is_an_option_screen_tab() {
		if ( empty( self::$args->option_group ) ) {
			return false;
		}
		$current_tab	= self::get_current_screen_tab();
		$options_insts	= Noop_Options::get_page_instances_names( self::$args->option_group );

		return isset($options_insts[$current_tab]);
	}


	// !Display tabs or title + error messages

	static public function screen_title_or_tabs( $tabs = null ) {
		if ( is_null($tabs) ) {
			$tabs = self::get_screen_tabs();
		}

		$current_tab = false;
		if ( !is_array($tabs) || !count($tabs) ) {				// No tabs, somebody messed it up. Fallback
			global $title;
			echo '<h2>' . $title . '</h2>';
		}
		elseif ( count($tabs) == 1 ) {							// Only 1 tab, no need to go further
			$current_tab = self::$opts->main_tab;
			echo '<h2>' . $tabs[$current_tab] . '</h2>';
		}
		else {
			if ( !empty( self::$args->option_group ) ) {
				$is_network		= is_network_admin();
				$locale			= Noop_i18n::get_locale( self::$args->option_group.'-'.self::$args->option_name );
				$def_locale		= Noop_i18n::get_default_locale( $is_network );
				$locale_query	= $locale == $def_locale ? '' : '&amp;loc='.$locale;
			}
			else {
				$locale_query	= '';
			}
			$current_tab	= self::get_current_screen_tab( $tabs );	// Won't be false
			$page_url		= Noop_Fields::ampersand( add_query_arg( 'page', self::$args->page_name, self_admin_url( self::$args->page_parent ) ) );

			$i = 0;
			echo '<h2 class="nav-tab-wrapper">';
			foreach ( $tabs as $tab => $label ) {
				$current_url = $i ? Noop_Fields::ampersand( add_query_arg('tab', $tab, $page_url) ) : $page_url;
				echo '<a class="nav-tab'.($tab === $current_tab ? ' nav-tab-active' : '').'" href="'.$current_url.$locale_query.'">'.$label.'</a>';
				$i++;
			}
			echo "</h2>\n";
		}

		// Settings error messages
		settings_errors();

		return $current_tab;
	}


	/*-------------------------------------------------------------------------------*/
	/* !Multilang ================================================================== */
	/*-------------------------------------------------------------------------------*/

	// !Language Tabs

	static public function languages_tabs() {

		if ( !self::use_multilang() ) {
			return;
		}

		$is_network	= is_network_admin();
		$locale		= Noop_i18n::get_locale( self::$args->option_group.'-'.self::$args->option_name );
		$default	= Noop_i18n::get_default_locale( $is_network );
		$locales	= Noop_i18n::get_languages();
		$langs_attr	= self::get_languages_attributes();
		$url		= add_query_arg( 'page', self::$args->page_name, self_admin_url( self::$args->page_parent ) );
		if ( !self::is_main_screen_tab() ) {
			$url	= add_query_arg( 'tab', self::get_current_screen_tab(), $url );
		}
		$url		= Noop_Fields::ampersand( $url );
		$current	= '';

		// Reorder by label
		$locales	= array_combine( $locales, $locales );
		$locales	= array_intersect_key( wp_list_pluck($langs_attr, 'name'), $locales );
		asort( $locales );
		$locales	= array_keys( $locales );

		echo "\t\t\t\t".'<div class="icl_tabs alignright">' . "\n";
		echo "\t\t\t\t\t".'<span class="icl_tabs_label">' . __("Available languages:", 'noop') . "</span> \n";
		foreach( $locales as $loc ) {
			if ( isset($langs_attr[$loc]) ) {
				$lang = $langs_attr[$loc];
			}
			else {
				$lang = $langs_attr['unknown'];
				$lang['name'] = $loc;
			}

			if ( $loc == $locale ) {
				$current  = "\t\t\t\t\t".'<div class="icl_current_tab alignright">';
					$current .= '<span class="icl_tabs_label">' . _x("Current:", 'current language', 'noop') . '</span> ';
					$current .= '<span class="icl_tab button button-disabled"><img src="' . esc_url( $lang['flag'] ) . '" alt="' . esc_attr($loc) . '"/> ' . esc_html($lang['name']) . '</span>';
				$current .= "</div>\n";
			}
			else {
				$link = $url.($loc != $default ? '&amp;loc='.$loc : '');
				$link = apply_filters( 'noop_languages_tabs_url', $link, $url, $loc, $default, $langs_attr );
				echo "\t\t\t\t\t".'<a class="icl_tab button" href="'.esc_url($link).'">';
					echo '<img src="' . esc_url($lang['flag']) . '" alt="' . esc_attr($loc) . '"/> ' . esc_html($lang['name']);
				echo "</a> \n";
			}
		}
		echo "\t\t\t\t</div>\n";
		echo $current;
	}


	// !Return the flag and language name (well, only the ones we got)

	static public function get_languages_attributes( $lang = false ) {
		$langs = array(
			'de_DE'	=> __('German', 'noop'),
			'en_CA'	=> __('English (CA)', 'noop'),
			'en_GB'	=> __('English (GB)', 'noop'),
			'en_US'	=> __('English (US)', 'noop'),
			'es_ES'	=> __('Spanish', 'noop'),
			'fr_BE'	=> __('Belgian', 'noop'),
			'fr_FR'	=> __('French', 'noop'),
			'it_IT'	=> __('Italian', 'noop'),
			'unknown' => esc_attr($lang),
		);
		foreach ( $langs as $code => $label ) {
			$langs[$code] = array( 'name' => $label, 'flag' => (file_exists(self::$args->noop_path.'res/images/flags/'.$code.'.png') ? self::$args->noop_url.'res/images/flags/'.$code.'.png' : self::$args->noop_url.'res/images/flags/unknown.png') );
		}

		$langs = apply_filters( 'noop_languages_attibutes', $langs, $lang );

		if ( $lang ) {
			return isset($langs[$lang]) ? $langs[$lang] : $langs['unknown'];
		}

		return $langs;
	}


	/*-------------------------------------------------------------------------------*/
	/* !Settings history =========================================================== */
	/*-------------------------------------------------------------------------------*/

	// !History Panel

	static public function history_panel() {

		if ( !self::use_history() ) {
			return '<p>'.__('This feature is disabled.', 'noop').'</p>';
		}

		$out = '<h5>'.__('This panel lists your settings history, up to 10 backups.', 'noop').'</h5>';
		$history = self::get_blog_option( self::$args->option_name . '_history' );
		if ( empty($history) || !is_array($history) ) {
			$out .= '<p>'.__('There\'s nothing to show yet.', 'noop').'</p>';
			return $out;
		}

		$url	= self::current_url();

		$content= apply_filters( self::$args->page_name.'_history_panel_content', '', $history, $url );

		if ( $content ) {
			$out .= $content;
		}
		else {

			$current		= self::get_restore_date();
			if ( !$current ) {
				end($history);
				$current	= key($history);
			}

			$out .= '<p class="history-list">';
			foreach ( $history as $date => &$props ) {
				$title = $date === $current ? esc_attr__('The settings currently displayed (unless you have reset them).', 'noop') : false;
				$class = $date === $current ? ' button-primary' : '';
				$date  = Noop_Utils::mysql2timestamp( $date );
				$link  = Noop_Fields::ampersand( add_query_arg( 'hist', date('Y-m-d@H:i:s', $date), $url ) );
				$date  = date('Y-m-d @ H:i:s', $date);
				$title = $title ? $title : sprintf(esc_attr__('See what %s was like.', 'noop'), $date);
				$props = '<a class="button'.$class.'" href="'.esc_url($link).'" title="'.$title.'">'.$date.'</a>';
			}
			$out .= implode(' ', $history);
			$out .= '</p>';

		}

		$uml  = self::use_multilang() ? __('This will affect only the settings for the current language.', 'noop') : '';
		$out .= '<p>'.sprintf( __('Click one of the buttons above to see the settings at that date. Then, you will be able to restore these settings by clicking the &#171;Restore&#187; button. The settings at that date will be removed from the timeline and a new point will be created at the current date. %s', 'noop'), $uml ).'</p>';

		return $out;
	}


	// !get restore date

	static public function get_restore_date() {

		if ( !is_null(self::$opts->restore_date) ) {
			return self::$opts->restore_date;
		}
		if ( empty($_GET['hist']) ) {
			self::$opts->restore_date = false;
			return false;
		}

		$date		= esc_attr( str_replace('@', ' ', $_GET['hist']) );
		$history	= self::get_blog_option( self::$args->option_name . '_history' );

		if ( empty($history) || !is_array($history) || empty($history[$date]) ) {
			self::$opts->restore_date = false;
			return false;
		}

		self::$opts->restore_date = $date;
		return $date;
	}


	// !Use History?

	static public function use_history() {

		if ( !is_null(self::$opts->use_history) ) {
			return self::$opts->use_history;
		}
		self::$opts->use_history = !empty( self::$args->option_group ) && (bool) apply_filters( self::$args->option_group.'-'.self::$args->option_name.'_use_history', true );
		return self::$opts->use_history;
	}


	// !Get History settings

	static public function get_past_options() {

		if ( !($date = self::get_restore_date()) || !self::use_history() ) {
			return;
		}
		$history = self::get_blog_option( self::$args->option_name . '_history' );

		return $history[$date];
	}

	// !get_option(), depending on the argument is_network_admin(). If is_network_admin() is true, the options are stored in the main blog options.

	static protected function get_blog_option( $option, $default = false ) {
		if ( is_network_admin() ) {
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


	/*-------------------------------------------------------------------------------*/
	/* !Settings Import/Export ===================================================== */
	/*-------------------------------------------------------------------------------*/

	// !Import/Export Panel

	static public function import_export_panel() {

		if ( !self::use_import_export() ) {
			return '<p>'.__('This feature is disabled.', 'noop').'</p>';
		}

		$out  = '<div class="noop-import-panel">'."\n";
			$out .= '<h5>'.__('This panel lets you import settings previously saved.', 'noop').'</h5>'."\n";

			$out .= '<form method="post" enctype="multipart/form-data" action="' . admin_url( 'admin-post.php' ) . '" autocomplete="off">'."\n";
				$out .= wp_nonce_field( 'noop-import-settings', '_noop_imp_nonce', true, false );
				$out .= '<input type="hidden" name="action" value="noop-import-settings"/>'."\n";
				$out .= '<input type="hidden" name="option-name" value="' . self::$args->option_name . '"/>'."\n";
				$out .= '<input type="file" name="noop-import-file" id="noop-import-file" required="required" accept="application/json"/>'."\n";
				$out .= '<p class="submit"><input type="submit" name="noop-import-submit" id="noop-import-submit" class="button button-primary" value="' . esc_attr__( 'Send file', 'noop' ) . '"/></p>'."\n";
			$out .= '</form>'."\n";

		$out .= '</div>'."\n";

		$out .= '<div class="noop-export-panel">'."\n";
			$out .= '<h5>'.__('This panel lets you export your current settings to a file.', 'noop').'</h5>'."\n";

			$settings = Noop_Options::get_instance( self::$args->option_name );
			$settings = $settings ? $settings->has_blog_option() : false;
			if ( $settings ) {
				$out .= '<form method="post" action="' . admin_url( 'admin-post.php' ) . '">'."\n";
					$out .= wp_nonce_field( 'noop-export-settings', '_noop_exp_nonce', true, false );
					$out .= '<input type="hidden" name="action" value="noop-export-settings"/>'."\n";
					$out .= '<input type="hidden" name="option-name" value="' . self::$args->option_name . '"/>'."\n";
					$out .= '<p class="submit"><input type="submit" name="noop-export-submit" id="noop-export-submit" class="button button-primary" value="' . esc_attr__( 'Download file', 'noop' ) . '"/></p>'."\n";
				$out .= '</form>'."\n";
			}
			else {
				$out .= '<p>'.__('... but it seems there\'s nothing to export here.', 'noop').'</p>';
			}

		$out .= '</div>'."\n";

		$out .= '<div class="clear"></div>'."\n";
		return $out;
	}


	// !Use Import/Export?

	static public function use_import_export() {

		if ( !is_null(self::$opts->use_import_export) ) {
			return self::$opts->use_import_export;
		}
		self::$opts->use_import_export = !empty( self::$args->option_group ) && (bool) apply_filters( self::$args->option_group.'-'.self::$args->option_name.'_use_import_export', true );
		return self::$opts->use_import_export;
	}


	/*-------------------------------------------------------------------------------*/
	/* !Contextual help and multilang settings ===================================== */
	/*-------------------------------------------------------------------------------*/

	static public function contextual_help() {

		// Sidebar
		$sidebar	= '<p><strong>' . __( 'For more information:' ) . '</strong></p>';

		// Support, plugin name, version
		if ( self::$args->plugin_is_plugin ) {
			$infos	= (object) get_plugin_data( self::$args->plugin_file, false, false );
			$name	= esc_attr( $infos->Name );
			$url	= !empty( self::$args->support_url ) ? self::$args->support_url : ( $infos->PluginURI ? $infos->PluginURI : false );
			$version= esc_attr( $infos->Version );
		}
		else {
			if ( function_exists( 'wp_get_theme' ) ) {	// WP 3.4+
				$infos	= wp_get_theme();
				$name	= esc_attr( $infos->Name );
				$url	= !empty( self::$args->support_url ) ? self::$args->support_url : ( $infos->PluginURI ? $infos->PluginURI : false );
				$version= esc_attr( $infos->Version );
			}
			else {	// WP < 3.4
				$infos	= (object) get_theme_data( self::$args->plugin_file );
				$name	= esc_attr( $infos->Name );
				$url	= !empty( self::$args->support_url ) ? self::$args->support_url : ( $infos->PluginURI ? $infos->PluginURI : false );
				$version= esc_attr( $infos->Version );
			}
		}

		$sidebar	.= '<p>'.($url ? '<a href="'.esc_url( $url ).'" target="_blank" title="'.__('Get some help', 'noop').'">' : '');
			$sidebar	.= $name.' <small class="'.self::$args->page_name.'-version"><em>v. '.$version.'</em></small>';
			$sidebar	.= self::$args->support_image ? '<br/><img class="help-avatar" src="'.esc_url( self::$args->support_image ).'" alt=""/>' : '';		// Max 140px for the image/avatar
		$sidebar	.= ($url ? '</a>' : '')."</p>\n";

		// Donation
		if ( self::$args->donation_url ) {
			$donation_like	= self::$args->donation_like ? self::$args->donation_like : __('coffee', 'noop');
			$donation_like	= is_array( self::$args->donation_like ) ? translate_nooped_plural(self::$args->donation_like, 1) : self::$args->donation_like;
			$donation_link	= '<a href="'.esc_url(self::$args->donation_url).'" target="_blank">'.$donation_like.'</a>';
			$sidebar	   .= '<p>' . sprintf( __('(by the way, I like %s)', 'noop'), $donation_link ) . '</p>';
		}

		$sidebar	.= apply_filters( self::$args->page_name.'_contextual_sidebar_content', '' );		// Add more links


		// Help tabs
		$helps		= apply_filters( self::$args->page_name.'_contextual_help_tabs', array() );


		// History
		if ( self::is_an_option_screen_tab() && self::use_history() ) {
			$helps['history']	= array(
						'id'		=> 'history',
						'title'		=> __('Settings History', 'noop'),
						'content'	=> self::history_panel(),
			);
		}


		// Import/Export
		if ( self::is_an_option_screen_tab() && self::use_import_export() ) {
			$helps['import_export']	= array(
						'id'		=> 'import_export',
						'title'		=> __('Import / Export', 'noop'),
						'content'	=> self::import_export_panel(),
						'nowpautop'	=> true,
			);
		}

		// Credits
		$credits	= array();
		$credits[]	= array( 'author' => 'Grégory Viguier',				'author_uri' => 'http://www.screenfeed.fr/greg/',	'what' => 'Noop',		'what_uri' => 'http://www.screenfeed.fr' );
		$credits[]	= array( 'author' => 'Julio Potier',				'author_uri' => 'http://www.boiteaweb.fr/',			'what' => __('the precious help and advises', 'noop') );
		$credits[]	= array( 'author' => 'The WordPress Foundation',	'author_uri' => 'http://wordpressfoundation.org/',	'what' => 'WordPress',	'what_uri' => 'http://wordpress.org/' );
		$credits	= apply_filters( self::$args->page_name.'_contextual_credits_tab_content', $credits );

		// Add the credits tab
		if ( $nbr_credits = count($credits) ) {
			$credits_title = _n("Thanks a lot to this awsome person/team:", "Thanks a lot to these awsome persons/teams:", $nbr_credits, 'noop');
			$credits_title = apply_filters( self::$args->page_name.'_contextual_credits_tab_title', $credits_title );
			$helps['credits'] = array(
						'id'		=> 'credits',
						'title'		=> __("Credits"),
						'content'	=> ( $credits_title ? '<h5>' . $credits_title . '</h5>' : '' ),
			);
			foreach ( $credits as $credit ) {
				$author = empty($credit['author']) ? '' : ( empty($credit['author_uri']) ? $credit['author'] : '<a href="'.esc_url($credit['author_uri']).'" target="_blank">'.$credit['author'].'</a>' );
				$what   = empty($credit['what']) ? '' : ( empty($credit['what_uri']) ? $credit['what'] : '<a href="'.esc_url($credit['what_uri']).'" target="_blank">'.$credit['what'].'</a>' );
				if ( $author && $what ) {
					$helps['credits']['content'] .= sprintf( __( '%1$s for %2$s', 'noop' ), $author, $what ) . '<br/>';
				}
				elseif ( $author || $what ) {
					$helps['credits']['content'] .= $author . $what . '<br/>';
				}
			}
		}


		// Finally...
		if ( !empty($helps) ) {
			$screen = get_current_screen();

			if ( method_exists( $screen, 'add_help_tab' ) ) {
				// WordPress 3.3+
				foreach ( $helps as $h ) {
					$h['content'] = empty( $h['nowpautop'] ) ? wpautop( $h['content'] ) : $h['content'];
					$screen->add_help_tab( $h );
				}

				if ( !empty($sidebar) ) {
					$screen->set_help_sidebar( $sidebar );
				}

			}
			else {
				// WordPress 3.2
				$help = '';
				foreach ( $helps as $h ) {
					$help .= '<h5>'.$h['title'].'</h5>' . ( empty( $h['nowpautop'] ) ? wpautop( $h['content'] ) : $h['content'] );
				}
				add_contextual_help( $screen, $help . '<div style="border-top:solid 1px #dfdfdf">' . $sidebar . '</div>' );
			}
		}
	}

}
endif;


/*-----------------------------------------------------------------------------------*/
/* !Link WPML/POLYLANG and Noop in the administration ============================== */
/*-----------------------------------------------------------------------------------*/

// !Filter Noop_Settings::get_languages_attributes()

if ( !function_exists('noop_third_parties_languages_attributes') ):
add_filter( 'noop_languages_attibutes', 'noop_third_parties_languages_attributes', 0, 2 );

function noop_third_parties_languages_attributes( $langs, $lang ) {
	global $sitepress, $polylang;
	if ( empty( $sitepress ) && empty( $polylang ) ) {
		return $langs;
	}

	static $thirdp_langs = null;
	if ( is_null($thirdp_langs) ) {
		// WPML
		if ( ! empty($sitepress) ) {
			$all_languages = $sitepress->get_languages();
			if ( empty( $all_languages ) ) {
				$thirdp_langs = $langs;
			}
			else {
				$thirdp_langs = array();
				foreach ( $all_languages as $thirdp_lang ) {
					$thirdp_langs[ $thirdp_lang['default_locale'] ] = array(
						'name'	=> $thirdp_lang['display_name'],
						'code'	=> $thirdp_lang['code'],
					);
				}
				// Add the flags (only to languages we use)
				$locales = Noop_i18n::get_languages();
				foreach ( $locales as $code => $locale ) {
					$thirdp_langs[$locale]['flag'] = $sitepress->get_flag_url($code);
				}
				if ( empty( $thirdp_langs ) ) {
					$thirdp_langs = $langs;
				}
				else {
					$thirdp_langs['unknown'] = $langs['unknown'];
					$thirdp_langs['unknown']['code'] = 'unknown';
				}
			}
		}
		// Polylang
		else {
			$all_languages = $polylang->model->get_languages_list();
			if ( empty( $all_languages ) ) {
				$thirdp_langs = $langs;
			}
			else {
				$thirdp_langs = array();
				foreach ( $all_languages as $thirdp_lang ) {
					$thirdp_langs[ $thirdp_lang->locale ] = array(
						'name'	=> $thirdp_lang->name,
						'code'	=> $thirdp_lang->slug,
						'flag'	=> $thirdp_lang->flag_url,
					);
				}
				$thirdp_langs['unknown'] = $langs['unknown'];
				$thirdp_langs['unknown']['code'] = 'unknown';
			}
		}
	}
	return $thirdp_langs;
}
endif;


// !Filter Noop_Settings::languages_tabs() (urls)

if ( !function_exists('noop_third_parties_languages_tabs_url') ):
add_filter( 'noop_languages_tabs_url', 'noop_third_parties_languages_tabs_url', 0, 5 );

function noop_third_parties_languages_tabs_url( $link, $url, $loc, $default, $langs_attr ) {
	global $sitepress, $polylang;
	if ( empty( $langs_attr[$loc]['code'] ) ) {
		return $link;
	}
	if ( ! empty( $polylang ) ) {
		return $url . '&amp;lang='.$langs_attr[$loc]['code'];
	}
	if ( ! empty( $sitepress ) ) {
		return $url . ( $loc != $default ? '&amp;lang='.$langs_attr[$loc]['code'] : '' );
	}

	return $url;
}
endif;


// !Filter Noop_Settings::current_url()

if ( !function_exists('noop_third_parties_current_url') ):
add_filter( 'noop_current_url', 'noop_third_parties_current_url', 0, 2 );

function noop_third_parties_current_url( $url, $option_name ) {
	global $sitepress, $polylang;
	if ( empty( $sitepress ) && empty( $polylang ) ) {
		return $url;
	}

	$is_network	= is_network_admin();
	$args		= Noop::get_props( $option_name );

	if ( empty( $args->option_group ) ) {
		return $url;
	}

	$locale		= Noop_i18n::get_locale( $args->option_group.'-'.$option_name );
	$def_locale	= Noop_i18n::get_default_locale( $is_network );

	if ( $locale === $def_locale ) {
		return $url;
	}

	$languages	= Noop_Settings::get_languages_attributes();

	if ( empty( $languages[$locale]['code'] ) ) {
		return $url;
	}

	return str_replace('loc=' . $locale, 'lang=' . $languages[$locale]['code'], $url);
}
endif;

/**/