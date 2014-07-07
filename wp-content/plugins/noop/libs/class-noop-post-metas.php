<?php
if( !defined( 'ABSPATH' ) )
	die( 'Cheatin\' uh?' );

/*-----------------------------------------------------------------------------------*/
/* !Noop POST METAS CLASS ========================================================== */
/* Provides the post metas system (get, update, sanitize, metabox).					 */
/*-----------------------------------------------------------------------------------*/

if ( !class_exists('Noop_Post_Metas') ) :
class Noop_Post_Metas {

	const VERSION = '0.4.2';
	protected static $instances	= array();
	protected static $init_done = array();
	protected static $noop_opts = array(
		'noop_path'			=> null,		// Slashed
		'noop_url'			=> null,		// Slashed
		'is_plugin'			=> 0,
	);

	protected $args	= array(
		'meta_name'					=> '',
		'capability'				=> '',			// empty means $post_type_object->cap->edit_post
		'hide_on_capability'		=> true,		// If true, the metaboxes are hidden if the current user doesn't have the capability to use it
		'multiple_metas'			=> false,		// If true, the values won't be stored under 1 meta "meta_name", but each field will be stored as a single meta. In this case, "meta_name" will be used as a simple identifier for this group of metas.
	);

	protected $opts	= array(
		'metas'						=> array(),
		'metas_default'				=> array(),
		'escape_functions'			=> array(),
		'sanitization_functions'	=> array(),
		'fields_args'				=> array(),
	);

	protected $metaboxes	= array();
	protected $sections		= array();
	protected $fields		= array();


	/*-------------------------------------------------------------------------------*/
	/* !Instance and Properties ==================================================== */
	/*-------------------------------------------------------------------------------*/

	protected function __construct( $args ) {

		if ( is_string($args) )
			$args = array( 'meta_name' => $args );
		$args = (object) $args;

		// PEBCAK
		if ( empty($args->meta_name) )
			return null;
		// Instance already exists
		if ( !empty(self::$instances[$args->meta_name]) )
			return self::$instances[$args->meta_name];

		$args		= array_merge( $this->args, (array) $args );
		$this->args	= (object) array_intersect_key($args, (array) $this->args);

		$this->opts	= (object) $this->opts;
		self::$noop_opts	= (object) self::$noop_opts;

		// Init
		global $pagenow;
		if ( is_admin() && !(defined('DOING_AJAX') && DOING_AJAX) && ($pagenow == 'post.php' || $pagenow == 'post-new.php') ) {

			if ( empty(self::$init_done) ) {
				self::$init_done[] = 'init';

				// Init Lang
				add_action( 'init',				array( __CLASS__, 'lang_init' ) );
			}

			if ( !in_array($this->args->meta_name, self::$init_done) ) {
				self::$init_done[] = $this->args->meta_name;

				// Don't use the action "load-{page}", so the user can use it ;)
				// $typenow is not set yet, need to hook later
				add_action( 'add_meta_boxes',	array( $this	, 'load_init' ) );

				// Need to be loaded after $this->load_init().
				add_action( 'add_meta_boxes',	array( $this	, 'fields_autoload' ) );

				// Save metas values on post save
				add_action( 'edit_attachment',	array( $this	, 'save_attachment_metas' ) );
				add_action( 'save_post',		array( $this	, 'save_metas' ), 10, 2 );
			}

			// Determine Noop path and url (and if the Noop is installed as a plugin or a mu-plugin)
			if ( empty(self::$noop_opts->noop_path) )
				self::$noop_opts->noop_path = str_replace( DIRECTORY_SEPARATOR, '/', trailingslashit(dirname(dirname(__FILE__))) );

			if ( empty(self::$noop_opts->noop_url) ) {
				self::$noop_opts->noop_url  = plugin_dir_url( self::$noop_opts->noop_path.'index.php' );
				self::$noop_opts->is_plugin = strpos(self::$noop_opts->noop_path, WPMU_PLUGIN_DIR) === 0 ? 2 : 1;
			}

		}

		self::$instances[$this->args->meta_name] = $this;

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
			if ( empty($args['meta_name']) )
				return null;
			if ( isset( self::$instances[$args['meta_name']] ) )
				return self::$instances[$args['meta_name']];
			$className = __CLASS__;
			new $className( $args );
			return !empty(self::$instances[$args['meta_name']]) ? self::$instances[$args['meta_name']] : null;
		}
		return null;
	}


	// !Return the class properties

	public function get_properties() {
		return (object) $this->args;
	}


	// !Return the post types in use

	public function get_metas_post_types() {
		$def = apply_filters( $this->args->meta_name.'_default_metas', array() );
		return !empty( $def ) ? array_keys((array) $def) : array();
	}


	// !Return the metaboxes

	protected function get_metaboxes( $post_type ) {
		/*
		array(
			'a_post_type' => array(
				array( 'id' => @string, 'title' => @string, 'context' => @string, 'priority' => @string ),
				array( 'id' => @string, 'title' => @string, 'context' => @string, 'priority' => @string ),
			),
			'another_post_type' => array(
				array( 'id' => @string, 'title' => @string, 'context' => @string, 'priority' => @string ),
				array( 'id' => @string, 'title' => @string, 'context' => @string, 'priority' => @string ),
			),
		)
		For the "attachment" post type, a parameter "mime" (string) can be passed for each metabox: image, video, etc. See Noop_Post_Metas::add_meta_boxes().
		*/
		if ( empty($this->metaboxes[$post_type]) )
			$this->metaboxes = apply_filters( $this->args->meta_name.'_add_meta_boxes', array() );
		return !empty($this->metaboxes[$post_type]) ? $this->metaboxes[$post_type] : array();
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
	/* !Init ======================================================================= */
	/*-------------------------------------------------------------------------------*/

	public function load_init() {
		global $typenow;
		if ( !in_array($typenow, $this->get_metas_post_types()) )
			return;

		$suffix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';
		$ver	= defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? time() : self::VERSION;
		wp_enqueue_style( 'noop-settings', self::$noop_opts->noop_url.'res/css/settings'.$suffix.'.css', false, $ver, 'all' );

		wp_register_script( 'noop-settings', self::$noop_opts->noop_url.'res/js/settings'.$suffix.'.js', array('jquery'), $ver, true );
		wp_localize_script( 'noop-settings', 'NoopSettingsL10n', array( 'del' => __("Delete"), 'help' => esc_attr__('Help') ) );

		wp_register_script( 'noop-findposts', self::$noop_opts->noop_url.'res/js/findposts'.$suffix.'.js', array('jquery'), $ver, true );
		wp_localize_script( 'noop-findposts', 'noopAttachMediaBoxL10n', array( 'error' => __( 'An error has occurred. Please reload the page and try again.' ), 'is39' => (version_compare( $GLOBALS['wp_version'], '3.9' ) > 0 ? 1 : 0) ) );

		// Add metaboxes
		add_action( 'add_meta_boxes_' . $typenow, array( $this	, 'add_meta_boxes' ) );

		add_action( 'admin_footer', array( __CLASS__, 'add_action_after_form' ) );
	}


	public function fields_autoload() {
		global $typenow;
		if ( !in_array($typenow, $this->get_metas_post_types()) )
			return;

		if ( !class_exists('Noop_Fields') )
			include( NOOP_DIR . 'libs/class-noop-fields.php' );

		Noop_Fields::get_instance( array(
			'option_name'	=> $this->args->meta_name,
			'page_name'		=> 'noop_metas',
			'noop_url'		=> self::$noop_opts->noop_url
		) );

		if ( isset( $_GET['post'] ) )
		 	$post_id = (int) $_GET['post'];
		elseif ( isset( $_POST['post_ID'] ) )
		 	$post_id = (int) $_POST['post_ID'];
		else
		 	$post_id = 0;

		$metas			= $this->get_meta($post_id, $typenow);
		$defaults		= $this->get_default($typenow);
		$this->opts->fields_args = array(
			'options'		=> $metas,
			'defaults'		=> $defaults,
		);
		// These values will be passed to the fields via the Noop_Settings::add_field method
		$this->opts->fields_args = apply_filters( $this->args->meta_name.'_metas_fields_args', $this->opts->fields_args, $this->args );

		do_action( 'load_'.$this->args->meta_name.'_meta_boxes', $post_id, $typenow );	// The best way to add sections and fields
	}


	/*-------------------------------------------------------------------------------*/
	/* !Deal with metas ============================================================ */
	/*-------------------------------------------------------------------------------*/

	/*
	 * !Utility: tell if a value should fallback to the default value.
	 * 0 (or "0") is a valid empty value for a field/meta.
	 * In other words: empty field => default, not empty (or 0) => sanitize the value.
	 * Moreover, no need to sanitize if the value === the default value.
	 * @return boolean
	 */

	static public function fall_to_default( $val, $def ) {
		return $val === $def || ( empty($val) && $val !== 0 && $val !== '0' );
	}

	// !Return default metas

	public function get_default( $post_type = false, $name = false ) {

		if ( !$post_type )
			return false;

		$this->maybe_clear_metas_cache();

		if ( empty($this->opts->metas_default) )
			$this->opts->metas_default = apply_filters( $this->args->meta_name.'_default_metas', array() );

		if ( empty($this->opts->metas_default[$post_type]) )
			return array();

		if ( $name ) {		// We request only one default meta (or a group)
			$group = self::get_sub_metas( $name, $this->opts->metas_default[$post_type] );
			return !is_null($group) ? $group : null;
		}

		return $this->opts->metas_default[$post_type];
	}


	// !Return escaped meta

	public function get_meta( $post_id = 0, $post_type = false, $name = false, $single = true ) {
		if ( !$post_id )
			return false;

		$metas	= $this->get_metas( $post_id, $post_type );
		if ( !$metas )
			return false;

		if ( $name && count($metas) ) {
			$post_type = post_type_exists($post_type) ? $post_type : get_post_type( $post_id );
			if ( !$post_type )
				return false;

			foreach ( $metas as $i => $meta ) {
				$group		= self::get_sub_metas( $name, $meta );
				$metas[$i]	= !is_null($group) ? $group : $this->get_default( $post_type, $name );
			}
		}

		return $single ? reset($metas) : $metas;
	}


	// !Return escaped metas

	public function get_metas( $post_id = 0, $post_type = false ) {
		$post_type = post_type_exists($post_type) ? $post_type : ($post_id ? get_post_type( $post_id ) : false);
		if ( !$post_type )
			return array( array() );

		if ( !$post_id )
			return array( $this->get_default( $post_type ) );

		$this->maybe_clear_metas_cache();

		if ( empty($this->opts->metas[$post_id]) ) {

			$new_metas			= array();	// Output

			if ( ! $this->args->multiple_metas ) {
				$metas			= get_post_meta( $post_id, $this->args->meta_name );

				if ( count($metas) ) {
					foreach ( $metas as $i => $meta ) {
						$new_metas[]= $this->escape_metas( $meta, $post_type );
					}
				}
				else {
					$new_metas[]	= $this->get_default( $post_type );
				}
			}
			// The values are stored as separated metas.
			else {
				$defaults		= $this->get_default( $post_type );
				if ( !empty($defaults) ) {
					foreach ( $defaults as $key => $def ) {
						$meta	= get_post_meta( $post_id, $key );
						if ( !empty($meta) ) {
							foreach ( $meta as $i => $v ) {
								if ( !isset($new_metas[$i]) )
									$new_metas[$i] = array();
								$new_metas[$i][$key] = $v;
							}
						}
						else {
							$new_metas[0][$key] = $def;
						}
					}
					foreach ( $new_metas as $i => $meta ) {
						$new_metas[$i]= $this->escape_metas( $meta, $post_type );
					}
				}
			}

			$this->opts->metas[$post_id] = $new_metas;
		}

		return $this->opts->metas[$post_id];
	}


	// !Return an array of "sub-metas". Only one level to keep it simple.

	static public function get_sub_metas( $name = false, $metas = array() ) {
		if ( empty($metas) || !$name )
			return array();

		$metas = (array) $metas;

		if ( isset($metas[$name]) )
			return $metas[$name];

		$group	= array();
		$name	= rtrim($name, '.').'.';
		foreach ( $metas as $k => $v ) {
			if ( strpos($k, $name) === 0 )
			$group[substr($k, strlen($name))] = $v;
		}
		return !empty($group) ? $group : null;
	}


	// !If you have trouble with the static cache for the options (triggered too soon, need an update), you can clear it here, before rebuilding all of this.

	public function maybe_clear_metas_cache( $force = false ) {
		if ( $force || apply_filters($this->args->meta_name.'_clear_metas_cache', false) ) {
			$this->opts->metas = array();
			$this->opts->metas_default = array();
			$this->opts->escape_functions = array();
			$this->opts->sanitization_functions = array();
			remove_all_filters( $this->args->meta_name.'_clear_metas_cache' );
		}
	}


	/*-------------------------------------------------------------------------------*/
	/* !Update metas, sanitization, escape ========================================= */
	/*-------------------------------------------------------------------------------*/

	// !Sanitize and update some metas.

	public function update_metas( $post_id = 0, $post_type = false, $new_values = array() ) {
		if ( !$post_id )
			return false;

		$post_type = post_type_exists($post_type) ? $post_type : get_post_type( $post_id );
		if ( !$post_type )
			return false;

		$new_values = array_merge( reset($this->get_metas( $post_id, $post_type )), $new_values );
		$new_values = apply_filters( $this->args->meta_name.'_validate_raw_metas', $new_values, $post_id, $post_type, false );
		$new_values = $this->sanitize_metas( $new_values, $post_type );
		$new_values = apply_filters( $this->args->meta_name.'_validate_metas', $new_values, $post_id, $post_type, false );
		if ( $check = update_post_meta( $post_id, $this->args->meta_name, $new_values ) ) {
			$this->opts->metas = $new_values;
			return $check;
		}
		// Meta has not changed or update failure
		$this->opts->metas = null;	// Empty the cache
		return false;
	}


	// !Return an array of functions for escape purpose

	public function escape_functions( $post_type = false, $name = false ) {

		$this->maybe_clear_metas_cache();

		if ( empty($this->opts->escape_functions) )
			$this->opts->escape_functions = apply_filters( $this->args->meta_name.'_metas_escape_functions', array() );

		if ( !$post_type )
			return $this->opts->escape_functions;

		if ( !post_type_exists($post_type) || !isset($this->opts->escape_functions[$post_type]) )
			return false;

		$functions = $this->opts->escape_functions[$post_type];

		if ( $name )
			return isset($functions[$name]) ? $functions[$name] : array( 'function' => 'esc_attr', 'array_map' => 'esc_attr' );

		return $functions;
	}


	// !Escape metas (used when you output/display/get the options)
	// Used in $this->get_metas()

	public function escape_metas( $metas = array(), $post_type = false ) {

		$default_metas	= $this->get_default( $post_type );
		$functions		= $this->escape_functions( $post_type );

		$metas			= is_array($metas) ? $metas : array();
		$metas			= apply_filters( $this->args->meta_name.'_before_escape_'.$post_type.'_metas', $metas, $default_metas, $functions );	// raw values
		$new_metas		= array();									// Output

		foreach( $default_metas as $name => $def ) {
			$new_metas[$name]	= isset($metas[$name]) && !self::fall_to_default( $metas[$name], $def ) ? self::sanitize_meta( $name, $metas[$name], $functions, $def ) : $def;
		}

		$new_metas		= apply_filters( $this->args->meta_name.'_after_escape_'.$post_type.'_metas', $new_metas, $default_metas, $functions );
		return $new_metas;
	}


	// !Return an array of functions for sanitization purpose

	public function sanitization_functions( $post_type = false, $name = false ) {

		$this->maybe_clear_metas_cache();

		if ( empty($this->opts->sanitization_functions) )
			$this->opts->sanitization_functions = apply_filters( $this->args->meta_name.'_metas_sanitization_functions', $this->escape_functions() );

		if ( !$post_type )
			return $this->opts->sanitization_functions;

		if ( !post_type_exists($post_type) || !isset($this->opts->sanitization_functions[$post_type]) )
			return false;

		$functions = $this->opts->sanitization_functions[$post_type];

		if ( $name )
			return isset($functions[$name]) ? $functions[$name] : array( 'function' => 'esc_attr', 'array_map' => 'esc_attr' );

		return $functions;
	}


	// !Sanitize metas (used when you save the options into the database)
	// Used in $this->update_metas() and $this->save_metas()

	public function sanitize_metas( $metas = array(), $post_type = false ) {

		$default_metas	= $this->get_default( $post_type );
		$functions		= $this->sanitization_functions( $post_type );

		$metas			= is_array($metas) ? $metas : array();
		$metas			= apply_filters( $this->args->meta_name.'_before_sanitize_'.$post_type.'_metas', $metas, $default_metas, $functions );	// raw values
		$new_metas		= array();									// Output

		foreach( $default_metas as $name => $def ) {
			$new_metas[$name]	= isset($metas[$name]) && !self::fall_to_default( $metas[$name], $def ) ? self::sanitize_meta( $name, $metas[$name], $functions, $def ) : $def;
		}

		$new_metas		= apply_filters( $this->args->meta_name.'_after_sanitize_'.$post_type.'_metas', $new_metas, $default_metas, $functions );
		return $new_metas;
	}


	/**
	 * !Return the sanitized option
	 * @var $name string (required): the option name
	 * @var $value mixed (required): the option value
	 * @var $functions array: the sanitization functions
	 * @return $value mixed: the sanitized option value
	 */
	static public function sanitize_meta( $name, $value, $functions, $default_value = '' ) {

		$fa = !empty( $functions[$name] ) ? $functions[$name] : array();

		if ( !is_array( $value ) && isset( $fa['function'] ) )
			return self::sanitize_meta_function( $value, $fa, $default_value );

		if ( is_array( $value ) && isset( $fa['array_map'] ) )
			return self::sanitize_meta_map( $value, $fa, $default_value );

		if ( isset( $fa['function'] ) )
			return self::sanitize_meta_function( $value, $fa, $default_value );

		if ( isset( $fa['array_map'] ) )
			return self::sanitize_meta_map( $value, $fa, $default_value );

		return esc_attr( $value );
	}


	// !Return the sanitized value when "function" is used as sanitization method

	static protected function sanitize_meta_function( $value, $fa, $default_value = '' ) {
		if ( isset( $fa['params'] ) )
			$values = array_merge( array($value), (array) $fa['params'] );
		elseif ( isset( $fa['param'] ) )
			$values = array_merge( array($value), array( $fa['param'] ) );
		else
			return call_user_func( $fa['function'], $value );

		if ( isset($values['%def%']) )
			$values['%def%'] = $default_value;
		return call_user_func_array( $fa['function'], $values );
	}


	// !Return the sanitized value when "array_map" is used as sanitization method

	static protected function sanitize_meta_map( $value, $fa, $default_value = '' ) {
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


	/*-------------------------------------------------------------------------------*/
	/* !Metas form and save on form submission ===================================== */
	/*-------------------------------------------------------------------------------*/

	// !
	public function add_meta_boxes() {
		global $typenow, $post_id;

		$metaboxes = $this->get_metaboxes( $typenow );
		if ( empty($metaboxes) )
			return;

		$_mime = get_post_mime_type($post_id);

		foreach ( $metaboxes as $metabox ) {
			if ( empty($metabox['id']) )
				continue;
			if ( !empty($metabox['mime']) && $_mime && strpos($_mime, $metabox['mime']) !== 0 )
				continue;
			$title		= !empty($metabox['title'])		? $metabox['title']		: sprintf( __('About this %s', 'noop'), get_post_type_object( $typenow )->labels->singular_name );
			$context	= !empty($metabox['context'])	? $metabox['context']	: 'normal';		// 'normal', 'advanced', 'side'
			$priority	= !empty($metabox['priority'])	? $metabox['priority']	: 'default';	// 'high', 'core', 'default', 'low'
			add_meta_box( $metabox['id'], $title, array( $this, 'metabox_form' ), $typenow, $context, $priority );
		}
	}


	// !
	public function add_section( $id, $title, $metabox_id ) {
		global $pagenow, $typenow;
		if ( !is_admin() || ($pagenow != 'post.php' && $pagenow != 'post-new.php') )
			return;
		$metaboxes = $this->get_metaboxes( $typenow );
		if ( empty($metaboxes) )
			return;

		if ( !isset($this->sections[$typenow]) )
			$this->sections[$typenow] = array();
		if ( !isset($this->sections[$typenow][$metabox_id]) )
			$this->sections[$typenow][$metabox_id] = array();

		$this->sections[$typenow][$metabox_id][$id] = array( 'title' => $title );
	}


	// !
	public function add_field( $id, $title, $callback, $metabox_id, $section, $args = array() ) {
		global $pagenow, $typenow;
		if ( !is_admin() || ($pagenow != 'post.php' && $pagenow != 'post-new.php') )
			return;
		$metaboxes = $this->get_metaboxes( $typenow );
		if ( empty($metaboxes) )
			return;

		if ( !isset($this->sections[$typenow]) )
			$this->sections[$typenow] = array();
		if ( !isset($this->sections[$typenow][$metabox_id]) )
			$this->sections[$typenow][$metabox_id] = array();
		if ( !isset($this->sections[$typenow][$metabox_id][$section]) )
			$this->sections[$typenow][$metabox_id][$section] = array();

		if ( is_array($callback) && end($callback) == 'multifields' ) {
			foreach ( $args as $i => $arg ) {
				if ( is_array($arg) && !empty($arg['params']) ) {
					$args[$i]['params'] = array_merge($this->opts->fields_args, $args[$i]['params']);
				}
			}
		}
		else {
			$args = array_merge($this->opts->fields_args, $args);
		}

		$this->fields[$typenow][$metabox_id][$section][$id] = array( 'title' => $title, 'callback' => $callback, 'args' => $args );
	}


	// !Metabox form

	public function metabox_form( $post, $metabox ) {
		static $nonce = array();
		$output = false;

		if ( in_array($post->post_type, $this->get_metas_post_types()) )
			$output = true;

		// User capability
		$cap = $this->args->capability ? $this->args->capability : get_post_type_object( $post->post_type )->cap->edit_post;
		if ( $this->args->hide_on_capability && !current_user_can( $cap, $post->ID ) )
			$output = false;

		// Check empty metabox
		if ( empty($this->sections[$post->post_type][$metabox['id']]) || empty($this->fields[$post->post_type][$metabox['id']]) )
			$output = false;

		// Output if allowed
		if ( $output ) {
			if ( empty($nonce[$this->args->meta_name]) ) {
				wp_nonce_field( 'post_'.$post->ID.'_save_metas', $this->args->meta_name.'_meta_nonce', false, true );	// No referer needed, already printed in the page
				$nonce[$this->args->meta_name] = true;
			}

			$fields = Noop_Fields::get_instance( $this->args->meta_name );

			// Include fields files
			$folders = array(
				path_join( get_stylesheet_directory(), 'noop-advanced-fields/' ),
				self::$noop_opts->noop_path . 'advanced-fields/',
			);
			$folders = apply_filters( 'noop_metas_advanced_fields', $folders );

			foreach ( $folders as $folder ) {
				if ( !file_exists($folder) )
					continue;
				foreach ( $this->fields[$post->post_type][$metabox['id']] as $section_id => $section ) {
					foreach ( $section as $field_id => $field ) {
						if ( !empty($field['callback']) ) {
							if ( is_array($field['callback']) && end($field['callback']) == 'multifields' && is_a(reset($field['callback']), 'Noop_Fields') && !empty($field['args']) && is_array($field['args']) ) {

								foreach ( $field['args'] as $arg_id => $arg ) {
									if ( !empty($arg['callback']) && is_string($arg['callback']) && file_exists($folder.$arg['callback'].'.php') ) {
										$this->fields[$post->post_type][$metabox['id']][$section_id][$field_id]['args'][$arg_id]['params']['page_name'] = 'noop_metas';
										$this->fields[$post->post_type][$metabox['id']][$section_id][$field_id]['args'][$arg_id]['params']['option_name'] = $this->args->meta_name;
										if ( !function_exists($arg['callback']) )
											include($folder.$arg['callback'].'.php');
									}
								}

							}
							elseif ( is_string($field['callback']) && file_exists($folder.$field['callback'].'.php') ) {
								$this->fields[$post->post_type][$metabox['id']][$section_id][$field_id]['args']['page_name'] = 'noop_metas';
								$this->fields[$post->post_type][$metabox['id']][$section_id][$field_id]['args']['option_name'] = $this->args->meta_name;
								if ( !function_exists($field['callback']) )
									include($folder.$field['callback'].'.php');
							}
						}
					}
				}
			}

			$wrap_class = 'noop-form';
			if ( version_compare( $GLOBALS['wp_version'], '3.8-alpha', '>' ) )
				$wrap_class .= ' mp6';

			echo '<div class="' . $wrap_class . '">' . "\n";

				// Print the fields
				do_action( $metabox['id'].'_meta_box_before_fields', $post );

				foreach ( $this->sections[$post->post_type][$metabox['id']] as $section_id => $section ) {
					if ( empty($this->fields[$post->post_type][$metabox['id']][$section_id]) )
						continue;
					if ( $section['title'] )
						echo '<h4>'.$section['title']."</h4>\n";
					echo '<table class="form-table">';
					foreach ( $this->fields[$post->post_type][$metabox['id']][$section_id] as $field_id => $field ) {
						echo '<tr>';
						if ( !empty($field['args']['label_for']) )
							echo '<th scope="row"><label for="' . esc_attr( $field['args']['label_for'] ) . '">' . $field['title'] . '</label></th>';
						else
							echo '<th scope="row">' . $field['title'] . '</th>';
						echo '<td>';
						call_user_func($field['callback'], $field['args']);
						echo "</td></tr>\n";
					}
					echo "</table>\n";
				}

				do_action( $metabox['id'].'_meta_box_after_fields', $post );

			echo "</div>\n";
		}
	}


	// ! Used by noop_find_posts_div()

	static public function add_action_after_form() {
		do_action( 'noop_metas_after_form' );
	}


	// !Sanitize, validate and save metas after submission with the form (for attachment)

	public function save_attachment_metas( $post_id ) {
		$post = get_post( $post_id );
		$this->save_metas( $post_id, $post );
	}


	// !Sanitize, validate and save metas after submission with the form

	public function save_metas( $post_id, $post ) {
		// Autosave
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			return;

		// Nonce
		if ( !isset($_POST[$this->args->meta_name.'_meta_nonce']) || !wp_verify_nonce( $_POST[$this->args->meta_name.'_meta_nonce'], 'post_'.$post->ID.'_save_metas' ) )
			return;

		// Valid post type
		$post_type = $post && !empty($post->post_type) ? $post->post_type : false;
		if ( !$post_type || !post_type_exists($post_type) || !in_array($post_type, $this->get_metas_post_types()) )
			return;

		// Maybe valid mime
		if ( $post_type == 'attachment' && !empty($post->post_mime_type) ) {
			$metabox = $this->get_metaboxes( $post_type );
			if ( !empty($metabox['mime']) && strpos($post->post_mime_type, $metabox['mime']) !== 0 )
				return;
		}

		// User capability
		$cap = $this->args->capability ? $this->args->capability : get_post_type_object( $post_type )->cap->edit_post;
		if ( !current_user_can( $cap, $post->ID ) )
			return;

		do_action( 'noop_before_save_metas', $this->args, $post );

		// Update or delete
		if ( !empty( $_POST[ $this->args->meta_name ] ) && is_array($_POST[ $this->args->meta_name ]) ) {

			$metas = apply_filters( $this->args->meta_name.'_validate_raw_metas', $_POST[ $this->args->meta_name ], $post->ID, $post_type, true );	// true means "on form submit"
			$metas = $this->sanitize_metas( $metas, $post_type );
			$metas = apply_filters( $this->args->meta_name.'_validate_metas', $metas, $post->ID, $post_type, true );

			if ( ! $this->args->multiple_metas ) {
				if ( update_post_meta( $post->ID, $this->args->meta_name, $metas ) )
					$this->opts->metas = $metas;
				else
					$this->opts->metas = null;
			}
			else {
				$update_fail = false;
				foreach ( $metas as $key => $meta ) {
					if ( ! update_post_meta( $post->ID, $key, $meta ) )
						$update_fail = true;
				}
				$this->opts->metas = $update_fail ? null : $metas;
			}
		} else {
			if ( ! $this->args->multiple_metas ) {
				delete_post_meta( $post->ID, $this->args->meta_name );
			}
			else {
				$default_metas	= $this->get_default( $post_type );
				foreach ( $default_metas as $key => $def ) {
					delete_post_meta( $post->ID, $key );
				}
			}
		}
	}

}
endif;
/**/