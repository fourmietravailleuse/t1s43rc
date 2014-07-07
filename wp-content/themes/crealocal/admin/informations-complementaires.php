<?php 
/**
Référencement simple (le reste du code est dans le header.php)
*/
// Champs de référencement
if(function_exists("register_field_group")) {

  register_field_group(array (
    'id' => 'acf_referencement',
    'title' => 'Référencement',
    'fields' => array (
      array (
        'key' => 'field_meta_titre',
        'label' => 'Meta Titre',
        'name' => 'meta_titre',
        'type' => 'text',
        'default_value' => '',
        'placeholder' => '',
        'maxlength' => '',
        'rows' => '',
        'formatting' => 'br',
      ),
      array (
        'key' => 'field_meta_description',
        'label' => 'Meta Description',
        'name' => 'meta_description',
        'type' => 'textarea',
        'default_value' => '',
        'placeholder' => '',
        'maxlength' => '',
        'rows' => '',
        'formatting' => 'br',
      ),
    ),
    'location' => array (
      array (
        array (
          'param' => 'post_type',
          'operator' => '==',
          'value' => 'post',
          'order_no' => 0,
          'group_no' => 0,
        ),
      ),
      array (
        array (
          'param' => 'post_type',
          'operator' => '==',
          'value' => 'page',
          'order_no' => 0,
          'group_no' => 1,
        ),
      ),
      array (
        array (
          'param' => 'post_type',
          'operator' => '==',
          'value' => 'page-systeme',
          'order_no' => 0,
          'group_no' => 1,
        ),
      ),
    ),
    'options' => array (
      'position' => 'normal',
      'layout' => 'default',
      'hide_on_screen' => array (
      ),
    ),
    'menu_order' => 0,
  ));
}

// Insertion automatique des champs de référencement
function save_meta_seo($post_id) {
    $slug = array('post', 'page', 'page-systeme');
    if (!in_array($_REQUEST['post_type'], $slug)) {
        return;
    }

    if(empty($_REQUEST['fields']['field_meta_titre'])) {
      if(!empty($_REQUEST['post_title'])) {
        update_post_meta($post_id,'meta_titre', esc_html($_REQUEST['post_title']));
      }
    }

    if(empty($_REQUEST['fields']['field_meta_description'])) {
      if(!empty($_REQUEST['fields']['field_introduction'])) {
        update_post_meta($post_id,'meta_description', esc_html($_REQUEST['fields']['field_introduction']));
      }
    }

}
add_action( 'save_post', 'save_meta_seo');






/**
//Introduction après le titre 
*/
if(function_exists("register_field_group"))
{
  register_field_group(array (
    'id' => 'acf_introduction',
    'title' => 'Introduction',
    'fields' => array (
      array (
        'key' => 'field_introduction',
        'label' => 'Introduction',
        'name' => 'introduction',
        'type' => 'textarea',
        'required' => 1,
        'default_value' => '',
        'placeholder' => '',
        'maxlength' => '',
        'rows' => '',
        'formatting' => 'br',
      ),
    ),
    'location' => array (
      array (
        array (
          'param' => 'post_type',
          'operator' => '==',
          'value' => 'page',
          'order_no' => 0,
          'group_no' => 0,
        ),

      ),
      array (
        array (
          'param' => 'post_type',
          'operator' => '==',
          'value' => 'post',
          'order_no' => 0,
          'group_no' => 0,
        ),
        
      ),
      array (
        array (
          'param' => 'post_type',
          'operator' => '==',
          'value' => 'page-systeme',
          'order_no' => 0,
          'group_no' => 0,
        ),
        
      ),
      array (
        array (
          'param' => 'post_type',
          'operator' => '==',
          'value' => 'albums',
          'order_no' => 0,
          'group_no' => 0,
        ),
        
      ),
    ),
    'options' => array (
      'position' => 'acf_after_title',
      'layout' => 'no_box',
      'hide_on_screen' => array (
      ),
    ),
    'menu_order' => 0,
  ));
}


