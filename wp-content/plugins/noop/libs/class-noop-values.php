<?php
if( !defined( 'ABSPATH' ) )
	die( 'Cheatin\' uh?' );

/*-----------------------------------------------------------------------------------*/
/* !Noop UTILITIES CLASS =========================================================== */
/* Some handy methods to get values for your fields. Can be used alone.				 */
/*-----------------------------------------------------------------------------------*/

if ( !class_exists('Noop_Values') ) :
class Noop_Values {

	const VERSION = '0.2.1';


	/*-------------------------------------------------------------------------------*/
	/* !These methods return an array of 'ID' => 'label' by default ================ */
	/*-------------------------------------------------------------------------------*/

	/* !Post types */

	static public function get_post_types( $args = array(), $return = 'label', $operator = 'and' ) {
		$args = array_merge( array(
			'public'	=> true,
			'show_ui'	=> true
		), $args);

		$post_types = array();
		$get_post_types = get_post_types( $args, 'objects', $operator );
		if ( count($get_post_types) ) {
			foreach ( $get_post_types as $get_post_type ) {
				$post_types[$get_post_type->name] = $return == 'objects' ? $get_post_type : $get_post_type->$return;
			}
		}
		return $post_types;
	}


	/* !Posts */

	static public function get_posts( $args = array(), $return = 'post_title' ) {
		$args = array_merge( array(
			'post_status'		=> 'publish',
			'posts_per_page'	=> -1,
			'orderby'			=> 'title',
			'order'				=> 'ASC',
		), $args);

		$posts = array();
		$get_posts = get_posts( $args );
		if ( count($get_posts) ) {
			foreach ( $get_posts as $get_post ) {
				$posts[$get_post->ID] = $return == 'objects' ? $get_post : ( !empty($get_post->$return) ? $get_post->$return : '('.$get_post->post_name.')' );
			}
		}
		return $posts;
	}


	/* !Taxonomies */
	/* $post_types is not handled the same way $args['object_type'] is. */

	static public function get_taxonomies( $args = array(), $post_types = array(), $operator = 'or', $return = 'label' ) {
		$args = array_merge( array(
			'public'	=> true,
			'show_ui'	=> true,
		), $args);
		$post_types = $post_types ? (array) $post_types : array();

		$taxonomies = array();
		$get_taxonomies = get_taxonomies( $args, 'objects' );
		if ( count($get_taxonomies) ) {
			foreach ( $get_taxonomies as $get_taxonomy ) {
				$pt = array_intersect($post_types, $get_taxonomy->object_type);
				if ( empty($post_types) || ( $operator == 'or' && !empty($pt) ) || ( $operator == 'and' && $pt == $post_types ) )
					$taxonomies[$get_taxonomy->name] = $return == 'objects' ? $get_taxonomy : $get_taxonomy->$return;
			}
		}
		return $taxonomies;
	}


	/* !Terms */

	static public function get_terms( $taxonomies = array( 'category' ), $args = array(), $return = 'name' ) {

		$terms = array();
		$get_terms = get_terms( $taxonomies, $args );
		if ( count($get_terms) && !is_wp_error($get_terms) ) {
			foreach ( $get_terms as $get_term ) {
				$terms[$get_term->term_id] = $return == 'objects' ? $get_term : $get_term->$return;
			}
		}
		return $terms;
	}


	/* !Users */

	static public function get_users( $args = array(), $return = 'display_name' ) {

		$users = array();
		if ( $return !== 'objects' ) {
			$user_return = in_array($return, array('ID', 'display_name')) ? $return : 'user_'.$return;
			$args['fields'] = array_unique(array('ID', $user_return, 'user_login'));
			if ( $return !== 'login' && empty($args['orderby']) )
				$args['orderby'] = $return;
		}
		$get_users = get_users( $args );
		if ( count($get_users) ) {
			foreach ( $get_users as $get_user ) {
				$users[$get_user->ID] = $return === 'objects' ? $get_user : (!empty($get_user->$user_return) ? esc_html($get_user->$user_return) : '('.esc_html($get_user->user_login).')');
			}
		}
		return $users;

	}


	/* !Roles, based on their capabilities */
	/* Returns an array of 'slug' => 'role nicename', or 'slug' => WP_Role object */

	static public function get_roles( $can = null, $cannot = null, $return = 'nicename' ) {

		static $roles = array();
		if ( !isset($roles[$can.'/'.$cannot]) ) {

			global $wp_roles;
			if ( !isset( $wp_roles ) )
				$wp_roles = new WP_Roles();

			foreach ( $wp_roles->role_objects as $role ) {
				if ( !is_null($can) && !$role->has_cap($can) )
					continue;
				if ( !is_null($cannot) && $role->has_cap($cannot) )
					continue;
				if ( $return == 'objects' )
					$roles[$can.'/'.$cannot][$role->name] = $role;
				else
					$roles[$can.'/'.$cannot][$role->name] = translate_user_role($wp_roles->role_names[$role->name]);
			}

		}
		return $roles[$can.'/'.$cannot];

	}

}
endif;