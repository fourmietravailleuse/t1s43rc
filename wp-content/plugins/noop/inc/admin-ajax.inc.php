<?php
if( !defined( 'ABSPATH' ) )
	die( 'Cheatin\' uh?' );

/*-----------------------------------------------------------------------------------*/
/* !Find Posts/Users =============================================================== */
/*-----------------------------------------------------------------------------------*/

add_action( 'wp_ajax_noop_find_posts', 'wp_ajax_noop_find_posts' );

if ( !function_exists('wp_ajax_noop_find_posts') ) :

function wp_ajax_noop_find_posts() {
	global $wpdb;

	check_ajax_referer( 'find-posts' );

	$s			= !empty($_POST['ps']) ? stripslashes( $_POST['ps'] ) : '';
	$what		= !empty($_POST['what']) ? esc_attr($_POST['what']) : 'posts|||';
	$type		= !empty($_POST['type']) ? esc_attr($_POST['type']) : 'any';
	$multiple	= !empty($_POST['multiple']);

	$what		= explode( '|', $what );
	$page_name	= $what[1];
	$option_name= $what[2];
	$_name		= str_replace('][', '|', $what[3]);
	$what		= $what[0];

	do_action( 'before_wp_ajax_noop_find_posts', $page_name, $option_name, $_name, $s, $what, $type, $multiple );

	if ( $what == 'posts' || $what == 'posts-thumb' ) {		// Find posts

		$type		= explode(',', $type);
		$post_types	= array();

		if ( ($any_i = array_search('any', $type)) !== false ) {
			$post_types = get_post_types( array( 'public' => true ), 'objects' );
			unset( $type[$any_i], $post_types['attachment'] );
		}
		if ( count($type) ) {
			$type		= array_combine($type, $type);
			$type		= array_filter($type, 'post_type_exists');
			$type		= array_map('get_post_type_object', $type);
			$post_types	= array_merge($post_types, $type);
		}

		$args = array(
			'post_type'			=> (count($post_types) ? array_keys( $post_types ) : 'any'),
			'post_status'		=> 'any',
			'posts_per_page'	=> 50,
			'suppress_filters'	=> false,
		);
		if ( '' !== $s ) {
			$args['s'] = $s;
		}
		if ( $what == 'posts-thumb' ) {
			$args['meta_query']	= array(
				array(
					'key'		=> '_thumbnail_id',
					'value'		=> 0,
					'type'		=> 'NUMERIC',
					'compare'	=> '>',
				)
			);
			if ( isset( $post_types['attachment'] ) ) {
				$args['meta_query'][] = array(
					'key'		=> '_wp_attachment_metadata',
					'value'		=> ':"image/',
					'compare'	=> 'LIKE',
				);
				$args['meta_query']['relation'] = 'OR';
			}
		}

		$items = get_posts( $args );

	} else {						// Find users

		$args = array(
			'who' => $type,
		);
		if ( '' !== $s )
			$args['search'] = $s;

		$items = get_users($args);

	}

	$items = apply_filters( 'wp_ajax_noop_find_posts_items', $items, $args, $page_name, $option_name, $_name, $s, $what, $type, $multiple );

	if ( ! $items ) {
		do_action( 'before_die_wp_ajax_noop_find_posts', $page_name, $option_name, $_name, $s, $what, $type, $multiple );
		wp_die( __('No items found.') );
	}

	if ( $multiple ) {
		$input_type = 'checkbox';
		$name ='[]';
	} else {
		$input_type = 'radio';
		$name ='';
	}

	if ( $what == 'posts' || $what == 'posts-thumb' ) {

		$statuses = wp_list_pluck($GLOBALS['wp_post_statuses'], 'label');
		if ( isset($statuses['auto-draft']) ) {
			$statuses['auto-draft'] = __('Auto Draft');
		}
		if ( isset($statuses['inherit']) ) {
			$statuses['inherit'] = __('Inherited', 'noop');
		}

		$html = '<table class="widefat" cellspacing="0"><thead><tr><th class="found-radio"><br /></th><th>'.__('Title').'</th><th class="no-break">'.__('Type').'</th><th class="no-break">'.__('Date').'</th><th class="no-break">'.__('Status')."</th></tr></thead><tbody>\n";
		foreach ( $items as $item ) {
			$title = trim( $item->post_title ) ? $item->post_title : __( '(no title)' );
			$stat  = !empty($statuses[$item->post_status]) ? $statuses[$item->post_status] : __('None');

			if ( '0000-00-00 00:00:00' == $item->post_date ) {
				$time = '';
			} else {
				/* translators: date format in table columns, see http://php.net/date */
				$time = mysql2date(__('Y/m/d'), $item->post_date);
			}

			$data_image = '';
			if ( $what == 'posts-thumb' ) {
				if ( $item->post_type == 'attachment' && substr($item->post_mime_type, 0, 5) == 'image' ) {
					$thumb_ID = $item->ID;
				}
				elseif ( $thumb_ID = (int) get_post_thumbnail_id( $item->ID ) ) {
				}
				else {
					continue;
				}

				list($src, $width, $height) = wp_get_attachment_image_src($thumb_ID, 'medium');
				$orientation	= $width > $height ? 'landscape' : 'portrait';
				$subtype		= substr( get_post_mime_type($thumb_ID), 6 );
				$data_image		= ' data-image="' . $src . '|' . $orientation . '|' . $subtype . '"';
			}

			$html .= '<tr class="found-posts"><td class="found-'.$input_type.'"><input type="'.$input_type.'" id="found-'.$item->ID.'" name="found_post_id'.$name.'" value="' . esc_attr($item->ID) . '"' . $data_image . '></td>';
			$html .= '<td><label for="found-'.$item->ID.'">' . esc_html( $title ) . '</label></td><td class="no-break">' . esc_html( $post_types[$item->post_type]->labels->singular_name ) . '</td><td class="no-break">'.esc_html( $time ) . '</td><td class="no-break">' . esc_html( $stat ). ' </td></tr>' . "\n\n";
		}

	} else {

		global $wp_roles;
		$html = '<table class="widefat" cellspacing="0"><thead><tr><th class="found-radio"><br /></th><th>'.__('Name').'</th><th class="no-break">'.__('Role').'</th><th class="no-break">'.__('ID').'</th></tr></thead><tbody>';
		foreach ( $items as $item ) {
			$html .= '<tr class="found-posts"><td class="found-'.$input_type.'"><input type="'.$input_type.'" id="found-'.$item->ID.'" name="found_post_id'.$name.'" value="' . esc_attr($item->ID) . '"></td>';
			$html .= '<td><label for="found-'.$item->ID.'">' . esc_html( $item->display_name ) . '</label></td><td class="no-break">' . translate_user_role( $wp_roles->role_names[$item->roles[0]] ) . '</td><td class="no-break">'.esc_attr($item->ID) . '</td></tr>' . "\n\n";
		}
	}

	$html .= '</tbody></table>';

	$html = apply_filters( 'wp_ajax_noop_find_posts_output', $html, $items, $args, $page_name, $option_name, $_name, $s, $what, $type, $multiple );

	if ( version_compare( $GLOBALS['wp_version'], '3.9-alpha' ) >= 0 ) {
		wp_send_json_success( $html );
	}

	$x = new WP_Ajax_Response();
	$x->add( array(
		'data' => $html
	));
	$x->send();
}

endif;
/**/