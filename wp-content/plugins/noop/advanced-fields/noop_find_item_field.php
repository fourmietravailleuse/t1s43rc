<?php
if( !defined( 'ABSPATH' ) )
	die( 'Cheatin\' uh?' );


// !Select post
if ( !function_exists('noop_find_item_field') ):

function noop_find_item_field( $o ) {
	if ( ( empty($o['label_for']) && empty($o['name']) ) || empty($o['page_name']) || empty($o['option_name']) )
		return;

	$o = array_merge( array(
		'label_for'		=> '',		// (1) Both should be used because label_for is passed through sanitize_html_class() for the id attribute (to not blow up the jQuery script because of the dots).
		'name'			=> '',		// (1)
		'label'			=> '',
		'value'			=> null,

		'label_after'	=> '',		// For "what" = "posts-thumb" only

		'what'			=> 'posts',	// 'posts', 'users', or 'posts-thumb'
		'type'			=> 'page',	// "post_type" for posts, use comma to separate post types ("" or "any" for any public post types but not attachement, "any,attachment" for any public post types), "who" for users ('authors' for users able to create posts)
		'multiple'		=> 0,
	), $o);
	extract($o, EXTR_SKIP);

	do_action( 'before_noop_find_item_field', $o );

	$id			= $label_for ? $label_for : $name;
	$name		= $name ? $name : $id;
	$id			= sanitize_html_class($id);		// Remove the dots, jQuery will like it this way.

	if ( is_null($value) ) {
		if ( strpos($name, '|') !== false ) {
			$value	= Noop_Fields::get_deep_array_val( $options, explode('|', $name) );
		}
		else {
			$value	= $options[ $name ];
		}
	}
	$name	= str_replace('|', '][', $name);

	$multiple	= (int) $multiple;				// In case we passed a boolean
	$what		= in_array( $what, array( 'posts', 'posts-thumb', 'users' ) ) ? $what : 'posts';

	if ( $what == 'posts' || $what == 'posts-thumb' ) {
		$type	= explode(',', $type);
		$any	= in_array('any', $type);
		$type	= array_filter($type, 'post_type_exists');
		if ( $any || empty($type) )
			$type[] = 'any';
		$button_text	= count($type) > 1 || in_array('any', $type) ? __('Find Posts or Pages') : get_post_type_object((reset($type)))->labels->search_items;
		$type	= implode(',', $type);
	}
	else {
		$type	= $type != 'authors' ? 'any' : $type;
		$button_text	= __('Search Users');
	}

	$has_upload = function_exists('wp_enqueue_media');		// For WP <3.5 we'll hide previews (no CSS) if $what == 'posts-thumb'

	$items		= is_array( $value ) ? $value : explode( ',', $value );
	$items		= array_filter( $items );
	$value		= implode( ',', $items );

	$button  = '<input'
				.' id="'.$id.'"'
				.' class="'.($what == 'posts-thumb' && !$has_upload ? '' : 'hide-if-js ').'response-input'.($multiple ? '' : ' small-text').'"'
				.' type="text"'
				.' name="'.$option_name.(!empty($locales['locale']) ? '['.$locales['locale'].']' : '').'['.$name.']"'
				.' value="'.$value.'"'
				.' autocomplete="off"/> ';
	$button .= '<button type="button" id="'.$id.'-button" class="find-post-or-user button hide-if-no-js" data-what="'.$what.'|'.$o['page_name'].'|'.$option_name.'|'.$name.'" data-type="'.$type.'" data-multiple="'.$multiple.'">'.$button_text.'</button> ';

	echo "\t\t\t\t";

	if ( $what == 'posts-thumb' ) {
		echo '<div id="wp-'.$id.'-wrap" class="wp-editor-wrap find-item-container">';
			echo $label ? '<p><label for="'.$id.'">'.$label.'</label></p>' : '';
			if ( $has_upload ) {
				echo '<ul id="'.$id.'-response" class="find-item-response upload-media-display dashed-box'.($multiple ? ' ui-sortable' : '').'">';
			}
	}
	elseif ( $multiple ) {
		echo $label ? '<label for="'.$id.'">'.$label.'</label> ' : '';
		echo '<table class="wp-list-table widefat fixed find-item-container" cellspacing="0">';
		echo '<thead><tr>'
				.'<th class="manage-column remove-column hide-if-no-js"></th>'
				.'<th class="manage-column column-title desc">'.($what == 'posts' ? __('Title') : __('User')).'</th>'
				.'<th class="manage-column column-type desc">'.($what == 'posts' ? __('Type') : __('Role')).'</th>'
				.'<th class="manage-column column-id">'.__('ID').'</th>'
			.'</tr></thead>';
		echo '<tbody id="'.$id.'-response" class="find-item-response">';
	}
	else {
		echo $label ? '<label for="'.$id.'">'.$label.'</label> ' : '';
		echo $button;
		echo '<span id="'.$id.'-response" class="find-item-response find-item-container tagchecklist">';
	}

	if ( $items ) {
		// Posts or posts thumb
		if ( $what == 'posts' || $what == 'posts-thumb' ) {
			$type = explode(',', $type);
			$post_types	= array();

			if ( ($any_i = array_search('any', $type)) !== false ) {
				$post_types = get_post_types( array( 'public' => true ) );
				unset( $type[$any_i], $post_types['attachment'] );
			}
			if ( count($type) ) {
				$post_types	= array_merge($post_types, array_combine($type, $type));
			}

			$args = array(
				'post_type'			=> (count($post_types) ? $post_types : 'any'),
				'post__in'			=> $items,
				'post_status'		=> 'any',
				'orderby'			=> 'post__in',
				'posts_per_page'	=> -1,
				'suppress_filters'	=> false,
			);

			// Posts thumb
			if ( $what == 'posts-thumb' ) {
				if ( $has_upload ) {
					$args['meta_query']	= array(
						array(
							'key'		=> '_thumbnail_id',
							'value'		=> 0,
							'type'		=> 'NUMERIC',
							'compare'	=> '>',
						)
					);
					if ( isset( $post_types['attachment'] ) ) {
						$args['meta_query']['relation'] = 'OR';
						$args['meta_query'][] = array(
							'key'		=> '_wp_attachment_metadata',
							'value'		=> ':"image/',
							'compare'	=> 'LIKE',
						);
					}

					$post_items = get_posts( $args );

					if ( $post_items ) {
						foreach ( $post_items as $item ) {
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

							echo '<li data-id="'.$item->ID.'" class="found-item attachment media-attachment"><div class="attachment-preview type-image subtype-'.$subtype.' '.$orientation.'"><div class="thumbnail"><div class="centered"><img src="'.$src.'" alt=""/></div></div><div class="filename"><div>'.get_the_title($item->ID).'</div></div><button title="'.__("Delete").'" class="close media-modal-icon">&#160;</button></div></li>';
						}
					}
				}
			}
			// Posts
			else {
				$post_items = get_posts( $args );
				$pts = get_post_types(array(), 'objects');

				if ( $post_items ) {
					foreach ( $post_items as $item ) {
						$item_title = get_the_title($item->ID);
						$item_title = trim($item_title) ? esc_html( $item_title ) : __( '(no title)' );
						if ( $multiple ) {
							echo '<tr class="found-item" data-id="'.$item->ID.'">'
									.'<td class="tagchecklist hide-if-no-js"><span><a class="ntdelbutton">X</a></span></td>'
									.'<td>'.$item_title.'</td>'
									.'<td>'.$pts[$item->post_type]->labels->singular_name.'</td>'
									.'<td>'.$item->ID.'</td>'
								.'</tr>';
						}
						else {
							echo '<span class="found-item" data-id="'.$item->ID.'"><a class="ntdelbutton hide-if-no-js">X</a> '.$item_title.' <em class="hide-if-js">('.$item->ID.')</em></span> ';
						}
					}
				}
			}
		}
		// Users
		else {
			$users = get_users(array( 'include' => $items, 'who' => $type ));
			if ( $users ) {
				global $wp_roles;
				foreach ( $users as $user ) {
					if ( $multiple ) {
						echo '<tr class="found-item" data-id="'.$user->ID.'">'
								.'<td><a class="ntdelbutton hide-if-no-js">X</a></td>'
								.'<td>'.$user->display_name.'</td>'
								.'<td>'.translate_user_role( $wp_roles->role_names[$item->roles[0]] ).'</td>'
								.'<td>'.$user->ID.'</td>'
							.'</tr> ';
					}
					else {
						echo '<span class="found-item" data-id="'.$user->ID.'"><a class="ntdelbutton hide-if-no-js">X</a> '.$user->display_name.' <em class="hide-if-js">('.$user->ID.')</em></span> ';
					}
				}
			}
		}
	}

	if ( $what == 'posts-thumb' ) {
		if ( !$items ) {
			echo '<li class="attachment no-attachment"><div class="attachment-preview"><span class="icon no-media-icon">&#160;</span></div></li>';
		}
		if ( $has_upload ) {
			echo '</ul>';
			echo '<div class="clear"></div>';
			echo $label_after ? '<p><label for="'.$id.'">'.$label_after.'</label></p>' : '';
		}
		echo $button;
	}
	elseif ( $multiple ) {
		echo "</tbody></table>\n";
		echo $button;
	}
	else {
		echo '</span>';
	}

	// Prepare for the default values
	if ( !isset($default) && isset($defaults) ) {
		$is_posts = $what == 'posts' || $what == 'posts-thumb';
		if ( strpos($name, '|') !== false ) {
			$default	= Noop_Fields::get_deep_array_val( $defaults, explode('|', $name) );
			if ( $is_posts && $default && $post_title = get_the_title($default) ) {
				$o['values'] = Noop_Fields::set_deep_array_val($post_title, explode('|', $name), $o['values']);
			}
			elseif ( !$is_posts && $default && $user = get_userdata($default) ) {
				$o['values'] = Noop_Fields::set_deep_array_val($user->display_name, explode('|', $name), $o['values']);
			}
		}
		elseif ( isset($defaults[$name]) ) {
			if ( $is_posts && $defaults[$name] && $post_title = get_the_title($defaults[$name]) ) {
				$o['values'][$defaults[$name]] = $post_title;
			}
			elseif ( !$is_posts && $defaults[$name] && $user = get_userdata($default) ) {
				$o['values'][$defaults[$name]] = $user->display_name;
			}
		}
	}

	echo Noop_Fields::get_instance( $option_name )->default_and_description( $o );
	if ( $what == 'posts-thumb' ) {
		echo "</div>\n";
	}

	do_action( 'after_noop_find_item_field', $o );

	static $noop_find_posts_div;
	if ( !$noop_find_posts_div ) {
		$noop_find_posts_div = true;
		add_action( $page_name.'_after_form', 'noop_find_posts_div' );
		wp_enqueue_script('wp-ajax-response');
		wp_enqueue_script('noop-findposts');
		wp_enqueue_script('noop-settings');
	}
}

endif;


if ( !function_exists('noop_find_posts_div') ):

function noop_find_posts_div( $found_action = '' ) {
	static $done = false;
	if ( $done ) {
		return;
	}
	$done = true;
	?>
	<div id="noop-find-posts" class="find-box" style="display: none;">
		<div id="noop-find-posts-head" class="find-box-head">
			<?php _e( 'Find Posts or Pages' ); ?>
			<div id="find-posts-close"></div>
		</div>
		<div class="find-box-inside">
			<div class="find-box-search">
				<?php if ( $found_action ) { ?>
					<input type="hidden" name="found_action" value="<?php echo esc_attr($found_action); ?>" />
				<?php } ?>
				<input type="hidden" name="affected" id="noop-affected" value="" />
				<input type="hidden" id="noop-find-posts-nonce" name="_ajax_nonce" value="<?php echo wp_create_nonce( 'find-posts' ); ?>" />
				<label class="screen-reader-text" for="find-posts-input"><?php _e( 'Search' ); ?></label>
				<input type="text" id="find-posts-input" name="ps" value="" />
				<span class="spinner"></span>
				<input type="button" id="find-posts-search" value="<?php esc_attr_e( 'Search' ); ?>" class="button" />
				<div class="clear"></div>
			</div>
			<div id="find-posts-response"></div>
		</div>
		<div class="find-box-buttons">
			<?php submit_button( __( 'Select' ), 'button-primary alignright', 'find-posts-submit', false, array( 'id' => 'noop-find-posts-submit' ) ); ?>
			<div class="clear"></div>
		</div>
	</div>
	<?php
}

endif;

/**/