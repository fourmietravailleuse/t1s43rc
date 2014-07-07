<?php
/*
Plugin Name: Creasit Albums
Description: Gestion des albums
Author: Creasit
Version: 1.0
*/


// Création du cunstom post type
add_action( 'init', 'create_post_type_albums' );

function create_post_type_albums() {
  $labels = array(
    'name' => __( 'Albums', 'solution' ),
    'singular_name' => __( 'Albums', 'solution' ),
    'search_items'      => __('Rechercher des albums', 'solution'),
    'all_items'         => __('Tous les albums', 'solution'),
    'edit_item'         => __('Editer un album', 'solution'),
    'update_item'       => __('Mise à jour d\'un album', 'solution'),
    'add_new_item'      => __('Ajouter un nouveau album', 'solution'),
    'new_item_name'     => __('Nouveau titre d\'un album', 'solution'),
    'menu_name'         => __('Albums', 'solution'),
  );

  $args = array(
    'labels' => $labels,
    'public' => true,
    'show_ui' => true,
    'capability_type' => 'post',
    'menu_position' => 10,
    'hierarchical' => false,
    'has_archive' => true,
    'supports' => array( 'title', 'page-attributes'),
    'menu_icon' => 'dashicons-images-alt',
  );

  register_post_type( 'albums', $args );


}



function albums_register() {
    if(function_exists("register_field_group")) {
        register_field_group(array (
            'id' => 'acf_images',
            'title' => 'Médias',
            'fields' => array (
                array (
                    'key' => 'key_images_albums',
                    'label' => 'Choisissez les médias de votre albums',
                    'name' => 'gallery',
                    'type' => 'gallery',
                    'required' => 1,
                    'preview_size' => 'medium',
                ),
            ),
            'location' => array (
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
                'position' => 'normal',
                'layout' => 'default',
                'hide_on_screen' => array (
                ),
            ),
            'menu_order' => 0,
        ));
    }


    if(function_exists("register_field_group")) {
        register_field_group(array (
            'id' => 'acf_parametrage',
            'title' => 'Paramètrage',
            'fields' => array (
                array (
                    'key' => 'key_choix_diapo',
                    'label' => 'Choix du diaporama',
                    'name' => 'choix_du_diaporama',
                    'type' => 'radio',
                    'instructions' => 'Seuls les fichiers images (jpeg, png...) sont inclus dans les diaporamas.',
                    'choices' => array (
                        'lien_diapo' => '<img src="'.get_template_directory_uri().'/images/AlbumIcon.png" title="Lien vers l\'album" alt="Lien vers l\'album">',
                        'simple' => '<img src="'.get_template_directory_uri().'/images/AlbumIconSimple.png" title="Simple" alt="Simple">',
                        'perspective' => '<img src="'.get_template_directory_uri().'/images/AlbumIconPerspective.png" title="Perspective" alt="Perspective">',
                        'polaroid' => '<img src="'.get_template_directory_uri().'/images/AlbumIconPolaroid.png" title="Polaroid" alt="Polaroid">',
                        'accordeon' => '<img src="'.get_template_directory_uri().'/images/AlbumIconAccordeon.png" title="Accordeon" alt="Accordeon">',
                    ),
                    'other_choice' => 0,
                    'save_other_choice' => 0,
                    'default_value' => '',
                    'layout' => 'horizontal',
                ),
                array (
                    'key' => 'key_mode_affichage',
                    'label' => 'Mode d\'affichage',
                    'name' => 'mode_affichage',
                    'type' => 'radio',
                    'conditional_logic' => array (
                        'status' => 1,
                        'rules' => array (
                            array (
                                'field' => 'key_choix_diapo',
                                'operator' => '==',
                                'value' => 'simple',
                            ),
                        ),
                        'allorany' => 'all',
                    ),
                    'choices' => array (
                        'normal' => 'Normal',
                        'vignettes' => 'Normal avec des vignettes',
                        'carousel' => 'Carousel',
                    ),
                    'other_choice' => 0,
                    'save_other_choice' => 0,
                    'default_value' => '',
                    'layout' => 'horizontal',
                ),
                array (
                    'key' => 'key_largeur',
                    'label' => 'Largeur de l\'album',
                    'name' => 'largeur',
                    'type' => 'radio',
                    'conditional_logic' => array (
                        'status' => 1,
                        'rules' => array (
                            array (
                                'field' => 'key_choix_diapo',
                                'operator' => '==',
                                'value' => 'simple',
                            ),
                        ),
                        'allorany' => 'all',
                    ),
                    'choices' => array (
                        '640' => 'Grand',
                        '480' => 'Moyen',
                        '320' => 'Petit',
                    ),
                    'other_choice' => 0,
                    'save_other_choice' => 0,
                    'default_value' => '480',
                    'layout' => 'horizontal',
                ),
                array (
                    'key' => 'key_effets',
                    'label' => 'Effet sur l\'album',
                    'name' => 'effets',
                    'type' => 'select',
                    'conditional_logic' => array (
                        'status' => 1,
                        'rules' => array (
                            array (
                                'field' => 'key_choix_diapo',
                                'operator' => '==',
                                'value' => 'simple',
                            ),
                        ),
                        'allorany' => 'all',
                    ),
                    'choices' => array (
                        'none' => 'Aucune',
                        'scroll' => 'Défilement horizontale',
                        'cover' => 'Superposition',
                        'face' => 'Fondu',
                    ),
                    'default_value' => 'scroll',
                    'allow_null' => 0,
                    'multiple' => 0,
                ),
                array (
                    'key' => 'key_fleches_controle',
                    'label' => 'Flêches de controle',
                    'name' => 'fleche_de_controle',
                    'type' => 'radio',
                    'conditional_logic' => array (
                        'status' => 1,
                        'rules' => array (
                            array (
                                'field' => 'key_choix_diapo',
                                'operator' => '==',
                                'value' => 'simple',
                            ),
                        ),
                        'allorany' => 'all',
                    ),
                    'choices' => array (
                        true => 'Oui',
                        false => 'Non',
                    ),
                    'other_choice' => 0,
                    'save_other_choice' => 0,
                    'default_value' => true,
                    'layout' => 'horizontal',
                ),
                array (
                    'key' => 'key_puces_pagination',
                    'label' => 'Puces de pagination',
                    'name' => 'puces_de_pagination',
                    'type' => 'radio',
                    'conditional_logic' => array (
                        'status' => 1,
                        'rules' => array (
                            array (
                                'field' => 'key_choix_diapo',
                                'operator' => '==',
                                'value' => 'simple',
                            ),
                        ),
                        'allorany' => 'all',
                    ),
                    'choices' => array (
                        true => 'Oui',
                        false => 'Non',
                    ),
                    'other_choice' => 0,
                    'save_other_choice' => 0,
                    'default_value' => false,
                    'layout' => 'horizontal',
                ),
                array (
                    'key' => 'key_affiche_legende',
                    'label' => 'Afficher la légende des images',
                    'name' => 'afficher_la_legende',
                    'type' => 'radio',
                    'conditional_logic' => array (
                        'status' => 1,
                        'rules' => array (
                            array (
                                'field' => 'key_choix_diapo',
                                'operator' => '==',
                                'value' => 'simple',
                            ),
                        ),
                        'allorany' => 'all',
                    ),
                    'choices' => array (
                        true => 'Oui',
                        false => 'Non',
                    ),
                    'other_choice' => 0,
                    'save_other_choice' => 0,
                    'default_value' => false,
                    'layout' => 'horizontal',
                ),
                array (
                    'key' => 'key_vitesse',
                    'label' => 'Vitesse',
                    'name' => 'vitesse',
                    'type' => 'number',
                    'conditional_logic' => array (
                        'status' => 1,
                        'rules' => array (
                            array (
                                'field' => 'key_choix_diapo',
                                'operator' => '==',
                                'value' => 'simple',
                            ),
                        ),
                        'allorany' => 'all',
                    ),
                    'default_value' => 3,
                    'placeholder' => '',
                    'prepend' => '',
                    'append' => 'seconde(s)',
                    'min' => 1,
                    'max' => 10,
                    'step' => 1,
                ),
            ),
            'location' => array (
                array (
                    array (
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'albums',
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

}
add_action( 'admin_init', 'albums_register' );



/**
Déplacer le fichier content-albums et archive-albums dans le thème du site
*/

// TODO : A tester sur un server !

define("SOURCE_CONTENT_ALBUMS", $_SERVER['DOCUMENT_ROOT'].'/wp-content/plugins/creasit-albums/templates/content-albums.php', 0755);
define("DESTINATION_CONTENT_ALBUMS", get_template_directory().'/content-albums.php', 0755);

define("SOURCE_ARCHIVE_ALBUMS", $_SERVER['DOCUMENT_ROOT'].'/wp-content/plugins/creasit-albums/templates/archive-albums.php', 0755);
define("DESTINATION_ARCHIVE_ALBUMS", get_template_directory().'/archive-albums.php', 0755);

define("SOURCE_SINGLE_ALBUMS", $_SERVER['DOCUMENT_ROOT'].'/wp-content/plugins/creasit-albums/templates/single-albums.php', 0755);
define("DESTINATION_SINGLE_ALBUMS", get_template_directory().'/single-albums.php', 0755);

function albums_activated() {
    // $fp = fopen(DESTINATION_SINGLE_ALBUMS, 'w');
    // chmod(DESTINATION_SINGLE_ALBUMS,0777);
    // file_put_contents(DESTINATION_SINGLE_ALBUMS, $contentAlbum);
    // $content = fgets(SOURCE_SINGLE_ALBUMS);
    // fwrite($fp, $content);
    // fclose($fp);

    // $contentAlbum = file_get_contents(SOURCE_CONTENT_ALBUMS);
    // file_put_contents(DESTINATION_CONTENT_ALBUMS, $contentAlbum);

    // $archiveAlbum = file_get_contents(SOURCE_ARCHIVE_ALBUMS);
    // file_put_contents(DESTINATION_ARCHIVE_ALBUMS, $archiveAlbum);

    // $singleAlbum = file_get_contents(SOURCE_SINGLE_ALBUMS);
    // file_put_contents(DESTINATION_SINGLE_ALBUMS, $singleAlbum);

    copy(SOURCE_CONTENT_ALBUMS, DESTINATION_CONTENT_ALBUMS);
    copy(SOURCE_ARCHIVE_ALBUMS, DESTINATION_ARCHIVE_ALBUMS);
    copy(SOURCE_SINGLE_ALBUMS, DESTINATION_SINGLE_ALBUMS);
}

function albums_deactivated() {
     unlink(DESTINATION_CONTENT_ALBUMS);
     unlink(DESTINATION_ARCHIVE_ALBUMS);
     unlink(DESTINATION_SINGLE_ALBUMS);
}

register_activation_hook(__FILE__, 'albums_activated');
register_deactivation_hook( __FILE__, 'albums_deactivated' );



/**
Modification de la requête d'archive des albums
*/

function archive_albums($query) {
    if(is_admin() || !$query->is_main_query())
        return;

    if(is_post_type_archive('albums')) {
        $query->set('orderby', 'menu_order');
        $query->set('order', 'ASC');
        $query->set('post_status', 'publish');
        return;
    }
}
add_action('pre_get_posts', 'archive_albums', 1);




/**
Ajouter des vignettes d'aperçu dans la liste des albums
*/

// add new column
function creasit_albums_columns_head_only_albums($defaults) {
    $defaults['image_preview'] = 'Aperçu';
    $defaults['choix_diaporama'] = 'Choix du diaporama';
    return $defaults;
}
add_filter('manage_albums_posts_columns', 'creasit_albums_columns_head_only_albums', 10);
 

// show the preview
function creasit_albums_columns_content_only_albums($column_name, $post_ID) {
    if($column_name == 'image_preview') {
        $post_thumbnail_id = get_field('gallery', $post_ID ); 

        if ($post_thumbnail_id) {
            foreach ($post_thumbnail_id as $key => $thumbnail) {
                if($key == 5) break; // J'affiche que 5 images max
                echo '<div style="float:left; margin-left: 5px;">'.wp_get_attachment_image($thumbnail, array('40','40')).'</div>';
            }
        }
    }



    if($column_name == 'choix_diaporama') {
        $choix_du_diaporama = get_field('choix_du_diaporama', $post_ID);

        if (!empty($choix_du_diaporama)) {
            // Si le iapo est simple, je vérifie le mode d'affichage
            if($choix_du_diaporama == 'simple') {
                $choix_du_diaporama = get_field('mode_affichage', $post_ID);
            }   

            if($choix_du_diaporama == 'lien_diapo') {
               $srcImg = '<img src="'.get_template_directory_uri().'/images/AlbumIcon.png" title="Simple" alt="Simple">';
            }

            else if($choix_du_diaporama == 'normal') {
               $srcImg = '<img src="'.get_template_directory_uri().'/images/AlbumIconSimple.png" title="Simple" alt="Simple">';
            }
            
            else if($choix_du_diaporama == 'vignettes') {
               $srcImg = '<img src="'.get_template_directory_uri().'/images/AlbumIconSimpleVignette.png" title="Simple avec vignette" alt="Simple avec vignette">';  
            }

            else if($choix_du_diaporama == 'carousel') {
               $srcImg = '<img src="'.get_template_directory_uri().'/images/AlbumIconSimpleCarousel.png" title="Carousel" alt="Carousel">';
            }

            else if($choix_du_diaporama == 'polaroid') {
               $srcImg = '<img src="'.get_template_directory_uri().'/images/AlbumIconPolaroid.png" title="Polaroid" alt="Polaroid">';
            }  

            else if($choix_du_diaporama == 'perspective') {
               $srcImg = '<img src="'.get_template_directory_uri().'/images/AlbumIconPerspective.png" title="Perspective" alt="Perspective">';
            }

            else if($choix_du_diaporama == 'accordeon') {
               $srcImg = '<img src="'.get_template_directory_uri().'/images/AlbumIconAccordeon.png" title="Accordeon" alt="Accordeon">';
            }
            

            echo '<div style="margin-top: 5px;">'.$srcImg.'</div>';
        }
    }
}
add_action('manage_albums_posts_custom_column', 'creasit_albums_columns_content_only_albums', 10, 2);
 








/**
Shortcode pour albums
*/
function shortcode_carousel($atts, $content=null) {
    wp_enqueue_style('styles-albums', plugins_url().'/creasit-albums/css/styles-albums.css', array(), '1.0', 'screen, projection');

    $output = '';
    $id_carousel = $atts['id'];
    $class_carousel = 'carousel'; 
    $post_carousel = get_post($atts['id']); 

    $name_carousel = $class_carousel.$id_carousel;

    // Choix du diapo
    $choix_du_diaporama = get_field('choix_du_diaporama', $id_carousel);

    // LEs images de l'album
    $images_carousel = get_field('gallery', $id_carousel);



    if(empty($id_carousel) || $post_carousel->post_type != 'albums' || $post_carousel->post_status != 'publish') { 
        $output .= '<p>Ce carousel n\'existe pas.</p>';
        return $output;

    } 

    elseif($choix_du_diaporama == 'lien_diapo') {
          include('types-albums/lien.php');
          return $output;
    }

    elseif($choix_du_diaporama == 'simple') {
          include('types-albums/simple.php');
          return $output;
    }

    elseif($choix_du_diaporama == 'polaroid') {
          include('types-albums/polaroid.php');
          return $output;
    }  

    elseif($choix_du_diaporama == 'perspective') {
          include('types-albums/perspective.php');
          return $output;
    }

    elseif($choix_du_diaporama == 'accordeon') {
          include('types-albums/accordeon.php');
          return $output;
    }
}

add_shortcode( 'album', 'shortcode_carousel' );





/**
Nouveau bouton dans l'éditeur afin d'ajouter la liste des albums
 */
// Génère la liste des albums dans le select
function add_sc_select(){

    $slides = query_posts(array('post_type' => 'albums','posts_per_page' => -1,'post_parent' => 0,'orderby' => 'menu_order'));

     if(!empty($slides)) { 

        $liste_albums = array();
        
        foreach ($slides as $slide) {
            $choix_du_diaporama = get_field('choix_du_diaporama', $slide->ID);
            if($choix_du_diaporama == 'simple') {
                $choix_du_diaporama = get_field('mode_affichage', $slide->ID);
            }
            $liste_albums[$slide->post_title] = "[album choix='".$choix_du_diaporama."' id='".$slide->ID."']";
        } 
    } 


    if(!empty($liste_albums)) {
        $shortcodes_list = '';
        echo '&nbsp;Liste des albums : <select id="sc_select"><option></option>';
        foreach ($liste_albums as $key => $val){
            $shortcodes_list .= '<option value="'.$val.'">'.$key.'</option>';
        }
        echo $shortcodes_list;
        echo '</select>';
    }
}
add_action('media_buttons','add_sc_select',11);

// Ajout d'une image à la place du shortcode
function image_replace_shortcode() {
    
    echo '<script type="text/javascript">
    jQuery(document).ready(function() {
        jQuery("#sc_select").change(function() {
            
            var choix_du_diaporama = jQuery("#sc_select :selected").val();
            var idTmp = choix_du_diaporama.substring(choix_du_diaporama.indexOf("id=\'") + 4);
            var id = idTmp.substring(0, idTmp.lastIndexOf("\'"));
            
            var classAlbumIcon = "";
          
            
            if(choix_du_diaporama.indexOf("lien_diapo") != -1) {
               var srcImg = "src=\''.get_template_directory_uri().'/images/PictosAlbumsDiapo.png\' title=\'Lien\' alt=\'Lien\'";
               classAlbumIcon = "albumIcon";
            }

            else if(choix_du_diaporama.indexOf("normal") != -1) {
               var srcImg = "src=\''.get_template_directory_uri().'/images/AlbumIconSimple.png\' title=\'Simple\' alt=\'Simple\'";
            }
            
            else if(choix_du_diaporama.indexOf("vignettes") != -1) {
               var srcImg = "src=\''.get_template_directory_uri().'/images/AlbumIconSimpleVignette.png\' title=\'Simple avec vignette\' alt=\'Simple avec vignette\'";  
            }

            else if(choix_du_diaporama.indexOf("carousel") != -1) {
               var srcImg = "src=\''.get_template_directory_uri().'/images/AlbumIconSimpleCarousel.png\' title=\'Carousel\' alt=\'Carousel\'";
            }

            else if(choix_du_diaporama.indexOf("polaroid") != -1) {
               var srcImg = "src=\''.get_template_directory_uri().'/images/AlbumIconPolaroid.png\' title=\'Polaroid\' alt=\'Polaroid\'";
            }  

            else if(choix_du_diaporama.indexOf("perspective") != -1) {
               var srcImg = "src=\''.get_template_directory_uri().'/images/AlbumIconPerspective.png\' title=\'Perspective\' alt=\'Perspective\'";
            }

            else if(choix_du_diaporama.indexOf("accordeon") != -1) {
               var srcImg = "src=\''.get_template_directory_uri().'/images/AlbumIconAccordeon.png\' title=\'Accordeon\' alt=\'Accordeon\'";
            }
            
           
            send_to_editor("<div class=\'hide-shortcode-album\'>"+jQuery("#sc_select :selected").val()+"</div><div class=\'show-img-shortcode-album "+classAlbumIcon+" \'><a href=\''.get_site_url().'/wp-admin/post.php?post="+id+"&action=edit\' class=\'album-action album-edit\'>&nbsp;</a><a href=\'#\' class=\'album-action album-remove\'>&nbsp;</a><img "+srcImg+"></div><div>&nbsp;</div><div class=\'apprendScript\'></div>");
            
            var script = "<script>jQuery(document).ready(function() { jQuery(\'#content_ifr\').contents().find(\'.album-edit\').click(function() { window.open(\''.get_site_url().'/wp-admin/post.php?post="+id+"&action=edit\'); }); jQuery(\'#content_ifr\').contents().find(\'.album-remove\').click(function() { jQuery(\'#content_ifr\').contents().find(\'.show-img-shortcode-album\').remove(); jQuery(\'#content_ifr\').contents().find(\'.hide-img-shortcode-album\').remove(); jQuery(\'#content_ifr\').contents().find(\'.apprendScript\').remove(); }); });<";
            script +=  "/script>";

            jQuery("#content_ifr").contents().find(".apprendScript").append(script);
    

            return false;
        });
    });
    </script>';
}
add_action('admin_head', 'image_replace_shortcode');

// Gestion du css pour le shortcode et l'image
function custom_editor_shortcode_albums($wp) {
    $wp .= ','.plugins_url( 'css/custom-editor-shortcode-albums.css', __FILE__ );
    return $wp;
}
add_filter('mce_css', 'custom_editor_shortcode_albums');


/**
Vérifier si le fichier est une image (jpg, jpeg, png)
*/
function creasit_albums_check_not_image($attachment_ID) {
    $mime_types = array('image/jpeg', 'image/png', 'image/jpg');
    $file = get_post_mime_type($attachment_ID);
    
    if(!in_array($file, $mime_types)) return false;

    return true;
}




/**
Ajouter un picto selon les types MIME dans le champ de média de l'album
*/
function change_mime_icon($icon, $mime, $post_id) {
    if($mime == 'application/rar' || $mime == 'application/zip') {
        $icon = WP_CONTENT_URL.'/themes/crealocal/images/icons/back_archive.gif';
    }
    else if($mime == 'application/vnd.openxmlformats-officedocument.wordprocessingml.document') {
        $icon = WP_CONTENT_URL.'/themes/crealocal/images/icons/back_document.gif';
    }
    else if($mime == 'application/pdf') {
        $icon = WP_CONTENT_URL.'/themes/crealocal/images/icons/back_pdf.gif';
    }
    else if(strpos($mime, 'audio/') !== false) {
        $icon = WP_CONTENT_URL.'/themes/crealocal/images/icons/back_audio.gif';
    }
    else if(strpos($mime, 'video/') !== false) {
        $icon = WP_CONTENT_URL.'/themes/crealocal/images/icons/back_video.gif';
    }
    return $icon;
}
add_filter('wp_mime_type_icon', 'change_mime_icon', 20, 3);



/**
Modifier les messages
*/
add_filter( 'post_updated_messages', 'creasit_modifier_messages_album' );
function creasit_modifier_messages_album($messages) {
  global $post;
  if ( $post->post_type != 'albums' )
    return $messages;

  $post_url = get_permalink($post->ID);
  $post_url_esc = esc_url( $post_url );
  $post_url_pvw = esc_url( add_query_arg( 'preview', 'true', $post_url ) );

  $messages['post'] = array(
     0 => '', // Unused. Messages start at index 1.
     1 => sprintf( __('Album mis à jour. <a href="%s">Voir l\'album</a>', 'solution'), $post_url_esc ),
     2 => __('Custom field updated.'),
     3 => __('Custom field deleted.'),
     4 => __('L\'album a été mis à jour.', 'solution'),
     5 => isset($_GET['revision']) ? sprintf( __('L\'album a été restauré depuis la version de %s', 'solution'), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
     6 => sprintf( __('L\'album a été publié. <a href="%s">Voir l\'album</a>', 'solution'), $post_url_esc ),
     7 => __('Album sauvegardé.', 'solution'),
     8 => sprintf( __('Album envoyé. <a target="_blank" href="%s">Prévisualiser l\'album</a>', 'solution'), $post_url_pvw ),
     9 => sprintf( __('Album programmé pour le : <strong>%s</strong>.', 'solution'), date_i18n( __( 'M j, Y @ G(idea)' ), strtotime( $post->post_date ) ) ),
    10 => sprintf( __('Le brouillon de l\'album à été modifié. <a target="_blank" href="%s">Prévisualiser l\'album</a>', 'solution'), $post_url_pvw ),
  );
  return $messages;
}