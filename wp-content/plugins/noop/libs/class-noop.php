<?php
if( !defined( 'ABSPATH' ) )
	die( 'Cheatin\' uh?' );

/*-----------------------------------------------------------------------------------*/
/* !Noop CLASS ===================================================================== */
/* This is the starting point, used (almost) everywhere.							 */
/* Used in: Noop_Options, Noop_Admin, Noop_Settings									 */
/*-----------------------------------------------------------------------------------*/

if ( !class_exists('Noop') ) :
class Noop {

	const VERSION = '0.9';
	// Noop params
	protected static $debug = array();
	protected static $instances = array();
	protected static $init_done = array();
	protected static $noop_opts = array(
		'noop_path'			=> null,		// Slashed
		'noop_url'			=> null,		// Slashed
		'is_plugin'			=> 0,
	);
	// Params to pass to Noop
	protected static $opts_def = array(
		'option_name'		=> '',					// The name of your option. The name attribute for the fields will be built like this: {option_name}[en_US][whatever].
		'option_group'		=> '',					// Option group, see register_setting(). If omitted, no option will be handled, the settings page will become an "action" tab.

		'page_name'			=> '',					// Will appear in the url of your settings page: whatever.php?page={page_name}.
		'page_parent_name'	=> '',					// Something like "appearance". See "common values" below.
		'page_parent'		=> '',					// Something like "themes.php". See "common values" below.
		'capability'		=> '',					// User access to the settings page.
		'network_menu'		=> false,				// Tell if the menu item must be added in the network admin menu, rather than the classic admin menu.

		'plugin_page_title'	=> '',					// Title of your settings page. Default to __('Settings') or __('Theme Options') if not provided.
		'plugin_menu_name'	=> '',					// Will appear in the menu.
		'plugin_file'		=> '',					// Path to the main file of your plugin/theme (__FILE__ or get_template_directory().'/style.css').
		'plugin_logo_url'	=> false,				// Url of a 112x68px image displayed on your settings page (see res/images/icon.png) (112x68 because it will contain both normal and "retina" version). Also, can be a word, like 'media' or 'users' (look "Common values for page_parent_name and page_parent" below).

		'support_url'		=> false,				// Where can we find help about your plugin/theme? (will appear in the contextual help sidebar). Default to the plugin/theme url (provided in the plugin/theme infos).
		'support_image'		=> false,				// A logo url? Your face? (will appear in the contextual help sidebar).
		'donation_url'		=> false,				// A paypal url? (will appear in the contextual help sidebar).
		'donation_like'		=> false,				// Will be inserted in the sentense "by the way, I like {donation_like}" to display your donation link (default to "coffee").

		'plugin_is_plugin'	=> 0,					// Will be overridden.
		'plugin_is_theme'	=> 0,					// Will be overridden.
	);

	protected $opts = array();

	/**
	 * Common values for page_parent_name and page_parent:
	 * dashboard,	post,		my-CPT,						media,			links,				comments,			appearance,		plugins,		users,		tools,		settings,				some-plugin-id
	 * index.php,	edit.php,	edit.php?post_type=my-CPT,	upload.php,		link-manager.php,	edit-comments.php,	themes.php,		plugins.php,	users.php,	tools.php,	options-general.php,	some-plugin-id
	 *
	 * Tip:
	 * If you use __('Whatever') in your params ('plugin_page_title', 'plugin_menu_name', 'donation_like') and they're not tranlated, use _n_noop('Settings', '', 'default'). _nx_noop won't work though.
	**/


	/*-------------------------------------------------------------------------------*/
	/* !Instance and Properties ==================================================== */
	/*-------------------------------------------------------------------------------*/

	public function __construct( $args = array() ) {

		$args = (object) $args;
		if ( empty($args->option_name) )
			return null;
		if ( !empty(self::$instances[$args->option_name]) )
			return self::$instances[$args->option_name];

		$args = array_merge( self::$opts_def, (array) $args );

		self::$noop_opts	= (object) self::$noop_opts;
		$this->opts			= (object) array_intersect_key($args, (array) self::$opts_def);

		$page_title = _n_noop('Settings', 'Settings', 'default');

		// Determine if the "client" is a theme or a plugin
		if ( $this->opts->plugin_file && !is_null($this->opts->plugin_file) ) {
			$plugin_path	= str_replace( DIRECTORY_SEPARATOR, '/', trailingslashit(dirname($this->opts->plugin_file)) );

			if ( strpos( $plugin_path, ( $t_dir = str_replace( DIRECTORY_SEPARATOR, '/', get_template_directory() ) ) ) === 0 ) {
				$this->opts->plugin_is_plugin = 0;
				$this->opts->plugin_is_theme  = 1;		// It's a theme
				$page_title	= __( 'Theme Options' );
			}
			elseif ( strpos( $plugin_path, ( $s_dir = str_replace( DIRECTORY_SEPARATOR, '/', get_stylesheet_directory() ) ) ) === 0 ) {
				$this->opts->plugin_is_plugin = 0;
				$this->opts->plugin_is_theme  = 2;		// It's a child theme
				$page_title	= __( 'Theme Options' );
			}
			else {
				$this->opts->plugin_is_plugin = strpos($plugin_path, WPMU_PLUGIN_DIR) === 0 ? 2 : 1;	// It's a plugin (or must-use plugin)
				$this->opts->plugin_is_theme  = 0;
			}
		}

		// Force the page title not to be empty
		$this->opts->plugin_page_title = !empty($this->opts->plugin_page_title) ? $this->opts->plugin_page_title : $page_title;

		// Determine Noop path and url (and if the Noop is installed as a plugin or a mu-plugin)
		if ( empty(self::$noop_opts->noop_path) )
			self::$noop_opts->noop_path = str_replace( DIRECTORY_SEPARATOR, '/', trailingslashit(dirname(dirname(__FILE__))) );

		if ( empty(self::$noop_opts->noop_url) ) {
			self::$noop_opts->noop_url  = plugin_dir_url( self::$noop_opts->noop_path.'index.php' );
			self::$noop_opts->is_plugin = strpos(self::$noop_opts->noop_path, WPMU_PLUGIN_DIR) === 0 ? 2 : 1;
		}

		// Init
		if ( empty(self::$init_done) ) {
			// Init Lang
			self::$init_done[] = 'noop_lang_init';
			add_action( 'init', array( __CLASS__, 'lang_init' ) );
		}

		if ( !in_array($this->opts->option_name, self::$init_done) ) {

			self::$init_done[] = $this->opts->option_name;
			$is_admin		= is_admin() && !( defined('DOING_AJAX') && DOING_AJAX );
			$use_options	= $this->opts->option_name && $this->opts->option_group;
			$use_admin		= $is_admin && $this->opts->option_name && $this->opts->capability;		// && $this->opts->page_name && $this->opts->page_parent_name && $this->opts->page_parent

			// Init Options
			self::$instances[$this->opts->option_name] = $this;

			if ( class_exists('Noop_Options') && $use_options ) {
				Noop_Options::get_instance( $this->opts->option_name );
			}
			// Init Admin
			if ( class_exists('Noop_Admin') && $use_admin ) {
				Noop_Admin::get_instance( $this->opts->option_name );
			}
		}
		else {
			self::$instances[$this->opts->option_name] = $this;
		}

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

		if ( is_string( $args ) ) {
			if ( isset(self::$instances[$args]) )
				return self::$instances[$args];
			else
				return null;
		}
		elseif ( is_array( $args ) || is_object( $args ) ) {
			$args = (array) $args;
			if ( empty($args['option_name']) )
				return null;
			if ( isset( self::$instances[$args['option_name']] ) )
				return self::$instances[$args['option_name']];
			$className = __CLASS__;
			new $className( $args );
			return !empty(self::$instances[$args['option_name']]) ? self::$instances[$args['option_name']] : null;
		}
		return null;
	}


	// !Return the class properties

	public function get_properties() {
		return (object) array_merge( (array) self::$noop_opts, (array) $this->opts );
	}


	// !Return the class properties (static shorthand)

	static public function get_props( $inst = false ) {
		if ( !$inst || !is_string($inst) || empty(self::$instances[$inst]) )
			return (object) array_merge( (array) self::$noop_opts, (array) self::$opts_def );
		return self::get_instance( $inst )->get_properties();
	}


	/*-------------------------------------------------------------------------------*/
	/* !Language support =========================================================== */
	/*-------------------------------------------------------------------------------*/

	static public function lang_init() {
		global $l10n;
		if ( isset( $l10n['noop'] ) )
			return;
		if ( self::$noop_opts->is_plugin == 1 )
			load_plugin_textdomain( 'noop', false, substr(self::$noop_opts->noop_path, strlen(WP_PLUGIN_DIR)) . 'languages/' );
		else
			load_muplugin_textdomain( 'noop', substr(self::$noop_opts->noop_path, strlen(WPMU_PLUGIN_DIR)) . 'languages/' );
	}


	/*-------------------------------------------------------------------------------*/
	/* !Utilities ================================================================== */
	/*-------------------------------------------------------------------------------*/

	// !Check if the settings page is currently displaying

	public function is_instance_settings_page() {
		return self::is_settings_page( $this->opts->page_parent, $this->opts->page_parent_name, $this->opts->page_name );
	}


	static public function is_settings_page( $page_parent = false, $page_parent_name = false, $page_name = false ) {
		if ( !is_admin() || (defined('DOING_AJAX') && DOING_AJAX) || !$page_name || !$page_parent || !$page_parent_name  )
			return false;

		global $pagenow, $typenow;

		$type = !is_null($typenow) ? $typenow : (!empty($_GET['post_type']) ? esc_attr($_GET['post_type']) : null);

		$pl = (    ($pagenow == $page_parent)										// Whatever
				|| ($pagenow == 'edit.php' && $type == $page_parent_name) 			// Post type page
				|| ($pagenow == 'admin.php' && $page_parent == $page_parent_name)	// Plugin page
			  )
			&& isset($_GET['page'])
			&& $_GET['page'] == $page_name;
		return $pl;
	}


	/*-------------------------------------------------------------------------------*/
	/* !Debug utility ============================================================== */
	/*-------------------------------------------------------------------------------*/

	static public function log( $text, $hard = false ) {
		if ( !WP_DEBUG )
			return;
		static $prio = 10;
		if ( $hard )
			self::$debug['##pre##'.$prio] = $text;
		else
			self::$debug[] = $text;
		add_action( 'in_admin_footer', array( __CLASS__, 'print_log' ), $prio++ );
		add_action( 'wp_print_footer_scripts', array( __CLASS__, 'print_log' ), $prio++ );
	}


	static public function print_log() {
		if ( !is_user_logged_in() )
			return;
		reset( self::$debug );
		$key  = key( self::$debug );
		$text = array_shift( self::$debug );
		if ( is_null($text) )
			return;
		if ( substr($key, 0, 7) == '##pre##' ) {
			if ( function_exists('pre_print_r') )
				pre_print_r($text, 1);
			else
				echo '<pre style="hoverflow:auto">'.print_r($text, 1).'</pre>';
			$text = 'printed';
		}
		if ( is_array($text) )
			$text = serialize($text);
		elseif ( is_object($text) )
			$text = str_replace(array("\r", "\n"), '', print_r($text, 1));
		else
			$text = str_replace(array("\r", "\n"), '', esc_attr($text));
		echo '<script type="text/javascript">if(window.console){console.log(\''.$text.'\');}</script>';
	}

}
endif;
/**/