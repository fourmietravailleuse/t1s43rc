<?php
if( !defined( 'ABSPATH' ) )
	die( 'Cheatin\' uh?' );

/*-----------------------------------------------------------------------------------*/
/* !Noop ADMIN CLASS =============================================================== */
/* Used in the administration area.													 */
/* Does lots of things like register settings, create the menu item, sanitize,		 */
/* store in history and validate options on form submission, etc.					 */
/* Requires: Noop, Noop_i18n, Noop_Options, (Noop_Settings: on settings page)		 */
/*-----------------------------------------------------------------------------------*/

if ( !class_exists('Noop_Admin') ) :
class Noop_Admin {

	const VERSION = '0.9';
	protected static $instances = array();
	protected static $init_done = array();
	protected $option_name;


	/*-------------------------------------------------------------------------------*/
	/* !Instance and Properties ==================================================== */
	/*-------------------------------------------------------------------------------*/

	protected function __construct( $args ) {

		if ( isset(self::$instances[$args->option_name]) )
			return self::$instances[$args->option_name];
		$this->option_name = $args->option_name;

		// Init

		// Register settings
		if ( $args->option_group && !in_array('register_settings_'.$args->option_group.'|'.$this->option_name, self::$init_done) ) {
			self::$init_done[] = 'register_settings_'.$args->option_group.'|'.$this->option_name;
			add_action( 'admin_init', array( $this, 'register_settings' ) );
		}

		if ( $args->page_name && $args->page_parent && $args->page_parent_name ) {
			// On the settings page, use $_GET['loc'] for language
			if ( $args->option_group && !in_array('noop_use_locale_param_'.$args->page_parent_name.'|'.$args->page_name, self::$init_done) ) {
				self::$init_done[] = 'noop_use_locale_param_'.$args->option_group.'|'.$this->option_name;
				if ( Noop::get_instance( $this->option_name )->is_instance_settings_page() )
					add_filter('noop_use_locale_param', '__return_true');
			}
			// Admin menu and contextual help
			if ( !in_array('register_menu_and_help_'.$args->page_parent_name.'|'.$args->page_name, self::$init_done) ) {
				self::$init_done[] = 'register_menu_and_help_'.$args->page_parent_name.'|'.$args->page_name;
				$prefix = $args->network_menu ? 'network_' : '';
				add_action( $prefix.'admin_menu', array( $this, 'register_menu_and_help' ) );
			}
		}

		// Adjust the capability for the settings
		if ( $args->option_group && !in_array('option_page_capability_'.$args->option_group, self::$init_done) ) {
			self::$init_done[] = 'option_page_capability_'.$args->option_group;
			add_filter( 'option_page_capability_'.$args->option_group, array( $this, 'option_page_capability' ) );
		}

		// Add a Settings link to "client" plugin in the plugins list
		if ( $args->page_name && $args->page_parent && $args->plugin_file && $args->plugin_is_plugin == 1 && !in_array('plugin_file_'.$args->plugin_file, self::$init_done) ) {
			self::$init_done[] = 'plugin_file_'.$args->plugin_file;
			$prefix = $args->network_menu ? 'network_admin_' : '';
			add_filter( $prefix.'plugin_action_links_'.plugin_basename($args->plugin_file), array( $this, 'settings_action_links' ), 10, 2 );
		}

		self::$instances[$this->option_name] = $this;
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


	/*-------------------------------------------------------------------------------*/
	/* !For the Settings page ====================================================== */
	/*-------------------------------------------------------------------------------*/

	// Return the capability for the settings

	public function option_page_capability() {
		return Noop::get_props( $this->option_name )->capability;
	}


	// Register the settings

	public function register_settings() {
		$args = Noop::get_props( $this->option_name );
		register_setting( $args->option_group, $args->option_name, array( Noop_Options::get_instance( $args ), 'sanitize_settings' ) );
	}


	// Add the menu item and load the contextual help

	public function register_menu_and_help() {
		$args = Noop::get_props( $this->option_name );

		if ( empty($args->plugin_page_title) && empty($args->plugin_menu_name) ) {
			$args->plugin_page_title = 'Noop';
			$args->plugin_menu_name  = 'Noop';
		}
		elseif ( empty($args->plugin_page_title) ) {
			$args->plugin_page_title = $args->plugin_menu_name;
		}
		elseif ( empty($args->plugin_menu_name) ) {
			$args->plugin_menu_name  = $args->plugin_page_title;
		}

		$page_title		= is_array( $args->plugin_page_title ) ? translate_nooped_plural($args->plugin_page_title, 1) : $args->plugin_page_title;
		$menu_name		= is_array( $args->plugin_menu_name )  ? translate_nooped_plural($args->plugin_menu_name, 1)  : $args->plugin_menu_name;
		$callback		= class_exists('Noop_Settings') && Noop::get_instance( $args )->is_instance_settings_page() ? Noop_Settings::get_instance( $args ) : false;

		$settings_page = add_submenu_page( $args->page_parent, $page_title, $menu_name, $args->capability, $args->page_name, ($callback ? array( $callback, 'settings_page' ) : '__return_true') );

		if ( $settings_page ) {
			add_action( 'load-'.$settings_page, ($callback ? array( $callback, 'contextual_help' ) : '__return_true') );
		}
	}


	// Add a Settings link to "client" plugin in the plugins list

	public function settings_action_links( $links, $file ) {
		$args = Noop::get_props( $this->option_name );
		if ( !current_user_can($args->capability) )
			return $tabs;
		$sub_tabs			= apply_filters( $args->page_name.'_settings_tabs', array() );
		$tab				= isset($sub_tabs[$args->option_name]) ? '&amp;tab='.$args->option_name : '';
		$sep				= strpos( $args->page_parent, '?' ) === false ? '?' : '&amp;';
		$links['settings']	= '<a href="' . self_admin_url( $args->page_parent . $sep . 'page='.$args->page_name.$tab ) . '">' . __("Settings") . '</a>';
		return $links;
	}

}
endif;
/**/