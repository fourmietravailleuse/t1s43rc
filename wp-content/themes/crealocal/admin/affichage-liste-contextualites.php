<?php

/**
Relations entre les pages, posts, etc.
*/

function creasit_relation_post_types() {
    p2p_register_connection_type( array(
        'name' => 'posts_to_pages',
        'from' => 'page',
        'to' => 'post'
    ) );

    p2p_register_connection_type( array(
        'name' => 'pages_to_pages',
        'from' => 'page',
        'to' => 'page'
    ) );

    p2p_register_connection_type( array(
        'name' => 'medias_to_pages',
        'from' => 'page',
        'to' => 'attachment',
        'connected_query' => array(
            'post_mime_type' => 'application/pdf',
          )
    ) );

}
add_action( 'p2p_init', 'creasit_relation_post_types' );

// Variable utilisé dans la sidebar afin d'afficher les différentes relations entre les post types
$relations_names = array('posts_to_pages', 'pages_to_pages', 'medias_to_pages');

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
                'key' => 'field_53b1722cd252a',
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
                'key' => 'field_53b172bad252b',
                'label' => 'Explication Contextualités',
                'name' => '',
                'type' => 'message',
                'conditional_logic' => array (
                    'status' => 1,
                    'rules' => array (
                        array (
                            'field' => 'field_53b1722cd252a',
                            'operator' => '==',
                            'value' => 'contextualites',
                        ),
                    ),
                    'allorany' => 'any',
                ),
                'message' => 'Si vous cochez cette case, les différentes pages, articles et téléchargement connectés seront affichés à droite du contenu de la page, sous forme de colonne.',
            ),
            array (
                'key' => 'field_53b17343d252c',
                'label' => 'Explications Affichage liste',
                'name' => '',
                'type' => 'message',
                'conditional_logic' => array (
                    'status' => 1,
                    'rules' => array (
                        array (
                            'field' => 'field_53b1722cd252a',
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
