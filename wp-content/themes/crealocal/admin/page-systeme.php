<?php 

/**
Création du cpt "Page Système"
*/

// Création du cunstom post type
add_action( 'init', 'create_post_type_page_systeme' );

function create_post_type_page_systeme() {

 
    $labels = array(
    'name' => __( 'Pages Systèmes', 'crealocal' ),
    'singular_name' => __( 'Page Système', 'crealocal' ),
    'search_items'      => __('Rechercher des pages systèmes', 'crealocal'),
    'all_items'         => __('Toutes les pages systèmes', 'crealocal'),
    'edit_item'         => __('Editer une page système', 'crealocal'),
    'update_item'       => __('Mise à jour d\'une page système', 'crealocal'),
    'add_new_item'      => __('Ajouter une nouvelle page système', 'crealocal'),
    'new_item_name'     => __('Nouveau titre d\'une page système', 'crealocal'),
    'menu_name'         => __('Pages Systèmes', 'crealocal'),
    );

    if(current_user_can('activate_plugins')) {
        $args = array(
            'labels' => $labels,
            'public' => true,
            'show_ui' => true,
            'capability_type' => 'page',
            'menu_position' => 20,
            'hierarchical' => true,
            'rewrite'  => array( 'slug' => 'page-systeme' ),
            'supports' => array( 'title', 'page-attributes', 'editor', 'post-formats'),
            'menu_icon' => 'dashicons-hammer',
        );
    } else {
         $args = array(
            'labels' => $labels,
            'public' => true,
            'show_ui' => true,
            'capability_type' => 'page',
            'capabilities' => array(
              'create_posts' => false, // Removes support for the "Add New" function
            ),
            'map_meta_cap' => true, // Set to false, if users are not allowed to edit/delete existing posts
            'menu_position' => 20,
            'hierarchical' => true,
            'rewrite'  => array( 'slug' => 'page-systeme' ),
            'supports' => array( 'title', 'page-attributes', 'editor', 'post-formats'),
            'menu_icon' => 'dashicons-hammer',
        );

    }

    register_post_type( 'page-systeme', $args );

}

/**
Suppression du slug et modification du permalien
*/

function custom_remove_cpt_slug( $post_link, $post, $leavename ) {
 
    if ( 'page-systeme' != $post->post_type || 'publish' != $post->post_status ) {
        return $post_link;
    }
 
    $post_link = str_replace( '/' . $post->post_type . '/', '/', $post_link );
 
    return $post_link;
}
add_filter( 'post_type_link', 'custom_remove_cpt_slug', 10, 3 );


function custom_parse_request_tricksy( $query ) {
 
    // Only noop the main query
    if ( ! $query->is_main_query() )
        return;
 
    // Only noop our very specific rewrite rule match
    if ( 2 != count( $query->query ) || ! isset( $query->query['page'] ) ) {
        return;
    }
 
    // 'name' will be set if post permalinks are just post_name, otherwise the page rule will match
    if ( ! empty( $query->query['name'] ) ) {
        $query->set( 'post_type', array( 'post', 'page-systeme', 'page' ) );
    }
}
add_action( 'pre_get_posts', 'custom_parse_request_tricksy' );


/**
Ajout du custom post type dans le plugin "Custom post template"
*/

function creasit_add_template( $post_types ) {
    $post_types[] = 'page-systeme';
    return $post_types;
}
add_filter( 'cpt_post_types', 'creasit_add_template' );


/**
Ajout du champ google map dans la page contact
*/

add_action('admin_init','creasit_coordonnees_meta_box_init');

function creasit_coordonnees_meta_box_init() {
  
  $post_id = isset($_GET['post']) ? $_GET['post'] : '';
  $template_file = get_post_meta($post_id,'custom_post_template',TRUE);

  if ($template_file == 'template-contact.php') {
        
    if(function_exists("register_field_group"))
    {
        register_field_group(array (
            'id' => 'acf_contact-google-map',
            'title' => 'Contact - Google map',
            'fields' => array (
                array (
                    'key' => 'field_53a9592f3e762',
                    'label' => 'Plan interactif',
                    'name' => 'plan_interactif_contact',
                    'type' => 'radio',
                    'instructions' => 'Si aucune adresse n\'est indiquée, l\'adresse postale sera utilisée pour le plan',
                    'choices' => array (
                        'gps' => 'Rentrer ses coordonnées GPS ou son adresse',
                        'iframe' => 'Rentrer l\'iframe récupéré sur Google Map',
                    ),
                    'other_choice' => 0,
                    'save_other_choice' => 0,
                    'default_value' => '',
                    'layout' => 'vertical',
                ),
                array (
                    'key' => 'field_53a959903e763',
                    'label' => 'Coordonnées GPS / Adresse',
                    'name' => 'coordonnees_gps_adresse',
                    'type' => 'google_map',
                    'conditional_logic' => array (
                        'status' => 1,
                        'rules' => array (
                            array (
                                'field' => 'field_53a9592f3e762',
                                'operator' => '==',
                                'value' => 'gps',
                            ),
                        ),
                        'allorany' => 'all',
                    ),
                    'center_lat' => '47.225201',
                    'center_lng' => '-1.529172',
                    'zoom' => 14,
                    'height' => '300',
                ),
                array (
                    'key' => 'field_53a959e33e764',
                    'label' => 'Iframe',
                    'name' => 'iframe_google_map',
                    'type' => 'textarea',
                    'instructions' => 'Merci de rentrer entièrement le code donné par google map',
                    'conditional_logic' => array (
                        'status' => 1,
                        'rules' => array (
                            array (
                                'field' => 'field_53a9592f3e762',
                                'operator' => '==',
                                'value' => 'iframe',
                            ),
                        ),
                        'allorany' => 'all',
                    ),
                    'default_value' => '',
                    'placeholder' => '',
                    'maxlength' => '',
                    'rows' => '',
                    'formatting' => 'html',
                ),
            ),
            'location' => array (
                array (
                    array (
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'page-systeme',
                        'order_no' => 0,
                        'group_no' => 0,
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

  }
}

        

?>