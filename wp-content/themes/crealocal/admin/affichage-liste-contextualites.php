<?php

/**
Relations entre les pages, posts, etc.
*/
function creasit_relation_post_types() {
    p2p_register_connection_type( array(
        'name' => 'posts_to_pages',
        'from' => 'page',
        'to' => 'post',
        'title' => array(
            'from' => __( 'Actualités liées', 'solution' ),
        ),
    ) );

    p2p_register_connection_type( array(
        'name' => 'pages_to_pages',
        'from' => 'page',
        'to' => 'page',
        'title' => array(
            'from' => __( 'Pages liées', 'solution' ),
        ),
        'admin_box' => array(
            'show' => 'from',
        )
    ) );
}
add_action( 'p2p_init', 'creasit_relation_post_types' );

// Variable utilisé dans la sidebar afin d'afficher les différentes relations entre les post types
$relations_names = array('posts_to_pages', 'pages_to_pages', 'medias_to_pages');



/**
Relations médias 
*/
function creasit_media_to_metabox() {
    add_meta_box( 'box-media-to', 'Médias liés', 'medias_to', 'page', 'side');
}
add_action( 'add_meta_boxes', 'creasit_media_to_metabox' );


function medias_to($post) {
    $arg_query = array(
        'post_type' => 'attachment',
        'post_status'=>'any',
    );
    $query = new WP_Query($arg_query);

    echo '<div style="max-height:110px; overflow-y: scroll;">';

    $checked_tmp = get_post_meta($post->ID,'medias_to',true); 
    $checked_medias_to = (empty($checked_tmp)) ? '' : $checked_tmp;
    
    foreach ($query->posts as $attachment) {
        if(strpos($attachment->post_mime_type, 'image/') === false) {
            $checked = '';
        	if(in_array($attachment->ID, $checked_medias_to)) $checked = 'checked="checked"';
            echo '<p><input type="checkbox" name="medias_to[]" value="'.$attachment->ID.'" '.$checked.'>'.$attachment->post_title.'</p>';

        }
    }

    echo '</div>';
}


function save_medias_to($post_ID) {
    if(isset($_POST['medias_to'])) {
        update_post_meta($post_ID, 'medias_to', $_POST['medias_to']);
    }
}
add_action('save_post','save_medias_to');




/**
Champs ACF (choix entre contextualités (défault) ou affichage liste)
*/

if(function_exists("register_field_group"))
{
    register_field_group(array (
        'id' => 'acf_affichage',
        'title' => 'Affichage',
        'fields' => array (
            array (
                'key' => 'field_context_affiche',
                'label' => 'Contextualités ou Affichage liste ',
                'name' => 'contextualites_ou_affichage_liste_page',
                'type' => 'radio',
                'choices' => array (
                    'contextualites' => 'Contextualités',
                    'affichage-liste' => 'Affichage Liste',
                ),
                'other_choice' => 0,
                'save_other_choice' => 0,
                'default_value' => 'contextualites',
                'layout' => 'horizontal',
            ),
            array (
                'key' => 'field_expli_context',
                'label' => 'Explication Contextualités',
                'name' => '',
                'type' => 'message',
                'conditional_logic' => array (
                    'status' => 1,
                    'rules' => array (
                        array (
                            'field' => 'field_context_affiche',
                            'operator' => '==',
                            'value' => 'contextualites',
                        ),
                    ),
                    'allorany' => 'any',
                ),
                'message' => 'Si vous cochez cette case, les différentes pages, articles et téléchargement connectés seront affichés à droite du contenu de la page, sous forme de colonne.',
            ),
            array (
                'key' => 'field_expli_affichage',
                'label' => 'Explications Affichage liste',
                'name' => '',
                'type' => 'message',
                'conditional_logic' => array (
                    'status' => 1,
                    'rules' => array (
                        array (
                            'field' => 'field_context_affiche',
                            'operator' => '==',
                            'value' => 'affichage-liste',
                        ),
                    ),
                    'allorany' => 'all',
                ),
                'message' => 'Si vous cochez cette case, les différentes pages, articles et téléchargement connectés seront affichés sous le contenu de la page en bloc.',
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
        ),
        'options' => array (
            'position' => 'side',
            'layout' => 'default',
            'hide_on_screen' => array (
            ),
        ),
        'menu_order' => 0,
    ));
}

/**
Contextualités "En savoir +"
*/

if(function_exists("register_field_group"))
{
    register_field_group(array (
        'id' => 'acf_en_savoir_plus_champ',
        'title' => 'En savoir +',
        'fields' => array (
            array (
                'key' => 'field_53b26ce3478f8',
                'label' => '',
                'name' => 'en_savoir_plus_page',
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
                    'value' => 'page',
                    'order_no' => 0,
                    'group_no' => 0,
                ),
            ),
        ),
        'options' => array (
            'position' => 'side',
            'layout' => 'default',
            'hide_on_screen' => array (
            ),
        ),
        'menu_order' => 0,
    ));
}

