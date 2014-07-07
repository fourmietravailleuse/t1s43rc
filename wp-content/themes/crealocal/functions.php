<?php

/**
Custom login admin
*/
require_once('custom-login/custom-login.php');



/**
Options de développement
*/
require_once('admin/dev-options.php');



/**
Options de développement
*/
require_once('admin/informations-complementaires.php');



/**
Setup du thème
*/
require_once('admin/starter.php');



/**
Paramétrage de l'éditeur de texte (tinyMCE 4)
*/
require_once('admin/config-editeur.php');



/**
Page système
*/
require_once('admin/page-systeme.php');



/**
Ajout dans l'administration Wordpress
*/
require_once('admin/ajout-admin.php');


/**
Affichage Liste ou contextualités
*/
require_once('admin/affichage-liste-contextualites.php');


/**
Force l'installation des plugins requis pour le thème, cf. http://tgmpluginactivation.com/
*/
require_once('helpers/tgm-plugin-activation.class.php');

// Register the required plugins for this theme.
function creasit_register_required_plugins() {
  // TGM_Plugin_Activation class constructor.

  // Array of plugin arrays. Required keys are name and slug. If the source is NOT from the .org repo, then source is also required.
  $plugins = array(
      // This is an example of how to include a plugin from the WordPress Plugin Repository
      array(
          'name' => 'Gravity Forms', // The plugin name
          'slug' => 'gravityforms', // The plugin slug (typically the folder name)
          'source' => get_template_directory_uri() . '/plugins/gravityforms.zip', // The plugin source
          'required' => true, // If false, the plugin is only 'recommended' instead of required
          'version' => '', // E.g. 1.0.0. If set, the active plugin must be this version or higher, otherwise a notice is presented
          'force_activation' => true, // If true, plugin is activated upon theme activation and cannot be deactivated until theme switch
          'force_deactivation' => false, // If true, plugin is deactivated upon theme switch, useful for theme-specific plugins
          'external_url' => '', // If set, overrides default API URL and points to an external URL
      ),

      array(
          'name' => 'Advanced Custom Fields',
          'slug' => 'advanced-custom-fields',
          'required' => true,
          'force_activation' => false,
      ),

      array(
          'name' => 'Simple page ordering',
          'slug' => 'simple-page-ordering',
          'required' => true,
          'force_activation' => false,
      ),
  );

  tgmpa($plugins);
}

add_action('tgmpa_register', 'creasit_register_required_plugins');


/**
Check si on est sur un CPT de type ...
*/
function is_post_type($type){
  global $wp_query;
  if($type == get_post_type($wp_query->post->ID)) return true;
  return false;
}


/**
Check si il y a http:// dans une URL
*/
function check_http($url) {
  if (false === strpos($url, '://')) {
    $url = 'http://' . $url;
  }
  return $url;
}


/**
Formulaire de recherche
*/
function my_search_form( $form ) {
    $form = '
    <form role="search" method="get" id="searchform" action="'.home_url('/').'">
      <div>
        <input type="text" class="rechercher" placeholder="Entrez votre recherche" name="s" id="s" />
        <button type="submit" id="searchsubmit"><span class="loupe"></span></button>
      </div>
    </form>';

    return $form;
}

add_filter( 'get_search_form', 'my_search_form' );



/**
Limiter les résultats de recherche à certains Custom Post Types
*/
add_filter('pre_get_posts','creasit_search_filter');
function creasit_search_filter($query) {
    if ($query->is_search && !is_admin())
        $query->set('post_type',array('post','page'));
}



/**
Pagination 
*/

// Ajout class sur pagination
function posts_link_next_class($format) {
     $format = str_replace('href=', 'class="next" href=', $format);
     return $format;
}
add_filter('next_post_link', 'posts_link_next_class');

function posts_link_prev_class($format) {
     $format = str_replace('href=', 'class="prev" href=', $format);
     return $format;
}
add_filter('previous_post_link', 'posts_link_prev_class');



// Ajout class sur pagination
add_filter('next_posts_link_attributes', 'posts_link_attributes_next');
add_filter('previous_posts_link_attributes', 'posts_link_attributes_prev');

function posts_link_attributes_prev() {
    return 'class="prev"';
}
function posts_link_attributes_next() {
    return 'class="next"';
}



/**
Retourne la première page utilisant le template donné
*/
function creasit_get_page_by_template($template_name) {
  $posts_args = array(
    'numberposts'     => 1,
    'meta_key'        => '_wp_page_template',
    'meta_value'      => $template_name,
    'post_type'       => 'page',
  );
  $posts = get_posts($posts_args);
  if (is_array($posts) && isset($posts[0])) {
    return $posts[0];
  }
}

function creasit_get_page_url_by_template($template_name) {
  $page = creasit_get_page_by_template($template_name);
  return get_permalink($page->ID);
}




/**
Flux RSS - Exlure les pages privées 
*/
add_filter( 'request', 'creasit_add_cpts_to_rss_feed' );
function creasit_add_cpts_to_rss_feed( $args ) {
 
    if ( isset( $args['feed'] ) && !isset( $args['post_type'] ) ) {
        
        empty($_GET['post_type']) ? $args['post_type'] = array('post') : $args['post_type'] = $_GET['post_type'];
    }
     
    return $args;
}

function creasit_exclude_filter($query) {
  if (!$query->is_admin && $query->is_feed) {
    $arg_query = array(
      'post_type' => 'page',
          'has_password' => true,
          'posts_per_page' => -1,
      );
      $query_posts = new WP_Query($arg_query);
      $ids_posts_has_password = array();
      foreach ($query_posts->posts as $post_pwd) {
        $ids_posts_has_password[] = $post_pwd->ID;
      }
    $query->set('post__not_in', $ids_posts_has_password ); // id of page or post
  }
  return $query;
}
add_filter( 'pre_get_posts', 'creasit_exclude_filter' );




/**
Suppr la possibilitée de créer une galerie d'image via la pop-up de média
*/
function creasit_custom_media_uploader( $strings ) {
  // Enlever certains onglets
  $disabled = array( 'selectFiles', 'createNewGallery', 'insertFromUrlTitle', 'createGalleryTitle' );

  foreach( $disabled as $string )
  $strings[$string] = '';
  $strings['selectFiles'] = __( 'Séléctionner', 'crealocal' );
  $strings['insertIntoPost']     = __( 'Insérer dans l\'article', 'crealocal' );
  $strings['insertFromUrlTitle']     = __( 'Insérer à partir d\'une adresse web', 'crealocal' );
  return $strings;
}
add_filter( 'media_view_strings', 'creasit_custom_media_uploader' );


/**
Affichage de la phrase pour les permaliens
*/

add_action( 'edit_form_after_title', 'creasit_edit_form_after_title' );
function creasit_edit_form_after_title() {
    global $post;
    if ( $post->post_type != 'acf' ){
    echo '<p style="background:url(../wp-content/themes/crealocal/images/ico-error.png) no-repeat left center;padding-left:30px;margin:0px 0 15px 10px;float:left;">Le permalien est un lien permanent. <strong>Merci de ne pas le changer.</strong></p><div style="clear:both;"></div>';
    }
}

add_filter( 'no_texturize_shortcodes', 'shortcodes_to_exempt_from_wptexturize' );
function shortcodes_to_exempt_from_wptexturize($shortcodes){
    $shortcodes[] = 'slider';
    return $shortcodes;
}

add_action('admin_head', 'creasit_style_permalink');
function creasit_style_permalink() {
  
    echo '<style type="text/css">
            #post-body-content #titlediv .inside {float:left;margin-top:3px;}
         </style>';

}


/**
Personnaliser le footer du backoffice
*/

function remove_footer_admin () {
echo 'Crealocal, une solution développée par <a href="http://www.creasit.fr/" target=_blank" title="Creasit, création de site internet">Creasit</a>';
 }
 add_filter('admin_footer_text', 'remove_footer_admin');


/**
Sécurité
*/
// Suppression de la balise meta generator
remove_action("wp_head", "wp_generator");



/**
Vérifier si le fichier est une image (jpg, jpeg, png)
*/
function creasit_check_not_image($attachment_ID) {
    $mime_types = array('image/jpeg', 'image/png', 'image/jpg');
    $file = get_post_mime_type($attachment_ID);
    
    if(!in_array($file, $mime_types)) return false;

    return true;
}



/**
Modifier le style du lien "Supprimer définitivement" + Suppression du titre introduction
*/

function creasit_style_supprimer_definitivement() {
   echo '<style type="text/css">
      .submitbox a.submitdelete {text-align:center;height:30px;line-height:28px;padding:0 12px 2px !important;background:#cc4c2e;border-color:#a22a00 !important;-webkit-box-shadow: inset 0 1px 0 rgba(162, 42, 0, 1.0),0 1px 0 rgba(162, 42, 0, 1.0) !important;box-shadow:inset 0 1px 0 rgba(162, 42, 0, 1.0),0 1px 0 rgba(162, 42, 0, 1.0) !important;color:#fff !important;text-decoration:none !important;border-width:1px !important;border-style:solid !important;box-sizing: border-box !important;-webkit-border-radius:3px;border-radius:3px;float:right;width:100%;}
      .submitbox  a:hover.submitdelete {background:#be351e;border-color:#a22200;-webkit-box-shadow:inset 0 1px 0 rgba(162, 34, 0, 1.0);box-shadow:inset 0 1px 0 rgba(162, 34, 0, 1.0);color:#fff;}
      .submitbox  #delete-action {float:right !important;width:100%}

      .submitbox #publishing-action {width:100%;margin-bottom:15px;}
      .submitbox #major-publishing-actions input {width:100%;}
      .submitbox #publishing-action .spinner {float:left;position:absolute;top:10px;left:10px;}
      .submitbox #wp-link-update {width:100%;margin-bottom:15px;padding-top:10px;}
      .submitbox #wp-link-update #wp-link-submit {width:100%;}
      
      .misc-pub-section.mla-links {display:none;}

     </style>';
}

add_action('admin_head', 'creasit_style_supprimer_definitivement');



/**
Modifier le style des actions (voir, editer, supprimer...) dans médias
*/

function creasit_modifier_style_medias_actions($post) {

  if (isset($_GET['page'])){

    $namePage = $_GET['page'];

    if ($namePage == "mla-menu"){
      
      echo '<style type="text/css">

        table.wp-list-table td.column-icon {padding-bottom:45px;}
        table.wp-list-table .row-actions {position:absolute;bottom:7px;left:-87px;width:420px;}
        table.wp-list-table td {position: relative;overflow:visible;}

      </style>';

    }

  }

}

add_action('admin_head', 'creasit_modifier_style_medias_actions');

/**
Modifier le style de l'action "Voir" de la catégorie
*/

function creasit_modifier_style_medias_categorie($post) {

  if (isset($_GET['taxonomy']) && isset($_GET['post_type'])){

    $namePostType = $_GET['post_type'];
    $nameTaxonomy = $_GET['taxonomy'];

    if ($namePostType == "attachment" && $nameTaxonomy == "attachment_category"){
      
      echo '<style type="text/css">

        #col-right table .row-actions span.view {display:none;}

      </style>';

    }

  }

}

add_action('admin_head', 'creasit_modifier_style_medias_categorie');

/**
Modifier l'adresse email pour les mot de passe perdu
*/

// add_filter('wp_mail_from', 'new_mail_from');
// add_filter('wp_mail_from_name', 'new_mail_from_name');
 
// function new_mail_from() { return 'no-reply@creasit.com'; }
// function new_mail_from_name() { $namesite = get_bloginfo('name'); return '['.$namesite.']'; }


/**
Modifier l'adresse email pour les mot de passe perdu
*/

// function wp_password_change_notification( $user )
// {
//     $message = sprintf('Password Lost and Changed for user: %s', $user->user_login) . "\r\n";
//     // The blogname option is escaped with esc_html on the way into the database in sanitize_option
//     // we want to reverse this for the plain text arena of emails.
//     $blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
//     wp_mail(get_option('admin_email'), sprintf('[%s] Password Lost/Changed', $blogname), $message);
// }













