<?php
/**
Ajout de styles et de couleurs dans l'éditeur de texte (tinyMCE 4)
*/

function mce_mod( $init ) {
    $init['block_formats'] = 'Paragraph=p;Heading 1=h1;Heading 2=h2;Heading 3=h3;Heading 4=h4;Heading 5=h5;Heading 6=h6';

     // Afficher les styles dans la liste déroulante
	$init['preview_styles'] = 'font-family font-weight font-size text-decoration text-transform color border-left background-color padding';



  $style_formats = array(
        array(
            'title' => 'Niveaux de titre',
                'items' => array(
                array( 'title' => 'h2', 'block' => 'h2'),
                array( 'title' => 'h3', 'block' => 'h3'),
                array( 'title' => 'h4', 'block' => 'h4'),
                array( 'title' => 'h5', 'block' => 'h5'),
                array( 'title' => 'h6', 'block' => 'h6'),
            )
        ),
        array(
            'title' => 'Blocs simples',
                'items' => array(
                array( 'title' => 'Bordure noire', 'block' => 'div', 'classes' => 'border border-noir', 'wrapper' => true),
                array( 'title' => 'Bordure verte', 'block' => 'div', 'classes' => 'border border-vert', 'wrapper' => true),
                array( 'title' => 'Bordure grise', 'block' => 'div', 'classes' => 'border border-gris', 'wrapper' => true),
            )
        ),

        array(
            'title' => 'Blocs avec fond',
                'items' => array(
                array( 'title' => 'Fond noir', 'block' => 'div', 'classes' => 'fond fond-noir', 'wrapper' => true),
                array( 'title' => 'Fond vert', 'block' => 'div', 'classes' => 'fond fond-vert', 'wrapper' => true),
                array( 'title' => 'Font gris', 'block' => 'div', 'classes' => 'fond fond-gris', 'wrapper' => true),
            )
        ),

        array(
            'title' => 'Autres formats',
                'items' => array(
                array( 'title' => 'Alerte', 'block' => 'div', 'classes' => 'alerte'),
                array( 'title' => 'Texte surligné', 'inline' => 'span', 'classes' => 'highlight'),
            )
        ),       

    );

   
    $init['style_formats'] = json_encode( $style_formats );
    $init['style_formats_merge'] = false;
    return $init;
}
add_filter('tiny_mce_before_init', 'mce_mod');

// mce_add_buttons remplace le contenu du select "styleselect"
function mce_add_buttons_2( $buttons ) 
{    array_splice( $buttons, 1, 0, 'styleselect' );
    return $buttons;
}
// mce_buttons_2 est la deuxième ligne de bouton
add_filter( 'mce_buttons_2', 'mce_add_buttons_2' );



function mce_colors_options($init) {
    $custom_colours = '"3b3b3b", "Vert fonçé", "5c7d80", "Vert clair", "e7e7e7", "Gris clair"';
    $init['textcolor_map'] = '['.$custom_colours.']';

    return $init;
}
add_filter('tiny_mce_before_init', 'mce_colors_options');


// Ajout des styles dans la liste déroulante
function my_theme_add_editor_styles() {
    add_editor_style( 'css/custom-editor-style.css' );
}
add_action( 'init', 'my_theme_add_editor_styles' );



/**
Ajout de la détection automatique des liens dans l'éditeur de texte (TinyMCE 4) + Enlever l'homothétie
*/

// Détection des liens
function autoLinkDetect () {
     $plugins = array('autolink'); //Add any more plugins you want to load here
     $plugins_array = array();

     
     foreach ($plugins as $plugin ) {
          $plugins_array[ $plugin ] =  get_template_directory_uri() . '/js/' . $plugin . '/plugin.min.js';
     }
     return $plugins_array;
}

add_filter('mce_external_plugins', 'autoLinkDetect');

// Style CSS pour l'éditeur (Homothétie + margin, padding des images)
function custom_editor_image_homothetie($wp) {
  $wp .= ',' . get_template_directory_uri().'/css/custom-editor-image.css';
  return $wp;
}
add_filter('mce_css', 'custom_editor_image_homothetie');