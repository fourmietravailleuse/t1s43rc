<?php

/**
Insertion du choix du nombre de colonnes sur le dashboard
*/
function creasit_number_columns() {
    add_screen_option(
        'layout_columns',
        array(
            'max'     => 4,
            'default' => 1
        )
    );
}
add_action( 'admin_head-index.php', 'creasit_number_columns' );

/**
Ajouter un widget creasit sur le Dashboard
*/

function creasit_dasboard_widget_construction() {


echo "
  <div id='credits-creasit'>
    <div class='banniere-creasit'><img src='http://pub.creasit.com/credits/bandeau.php' alt='agence de création et de refonte de sites Internet'></div>
    <div class='info-creasit'><img src='http://pub.creasit.com/credits/images/creasit.png' alt='agence de création de sites Internet'></div>
  </div>
  <p>Vous rencontrez un problème avec votre Crealocal ?<br> Vous pouvez nous contactez soit par email :</p>
  <p><a href='mailto:info@creasit.fr'>info@creasit.fr</a></p>
  <p>Soit en utilisant notre formulaire de contact :</p>
  <p><a href='http://www.creasit.fr/module-Contenus-viewpub-tid-2-pid-8.html' target='_blank'>Formulaire de contact</a></p>
  <p>Ou alors par téléphone :</p>
  <p>02 40 37 01 77</p>

";
}

function creasit_dasboard_widget_implemente() {
  
  wp_add_dashboard_widget('wp_dashboard_widget', 'Creasit', 'creasit_dasboard_widget_construction');
}
add_action('wp_dashboard_setup', 'creasit_dasboard_widget_implemente' );


function creasit_modifier_style_widget_dashboard($post) {

  
  echo '<style type="text/css">

    #credits-creasit {text-align:center;}
    .banniere-creasit img {max-width:100%;}
    .info-creasit img {max-width:100%;}


  </style>';

    }

add_action('admin_head', 'creasit_modifier_style_widget_dashboard');

/**
Ajouter la date de modification sur les pages, pages systèmes et les actualités
*/

add_action ( 'manage_posts_custom_column',  'creasit_post_columns_data',  99, 2 );
add_action ( 'manage_pages_custom_column',  'creasit_post_columns_data',  99, 2 );
add_action ( 'manage_page-systeme_custom_column',  'creasit_post_columns_data',  99, 2 );
 
function creasit_post_columns_data( $column, $post_id ) {

  switch ( $column ) {
 
  case 'modifie':
    $m_orig   = get_post_field( 'post_modified', $post_id, 'raw' );
    $m_stamp  = strtotime( $m_orig );
    $modified = date('j/m/y à H\hi', $m_stamp );

    echo '<p>';
    echo '<strong>'.$modified.'</strong>';
    echo '</p>';
 
    break;

  }
 
}

/**
Ajouter la petite option de filtre sur la date de modification
*/

add_filter ( 'manage_edit-post_columns',  'creasit_post_columns_display',99);
add_filter ( 'manage_edit-page_columns',  'creasit_post_columns_display',99);
add_filter ( 'manage_edit-page-systeme_columns',  'creasit_post_columns_display',99);

function creasit_post_columns_display( $columns ) {

    // Si l'url contient le paramètre POST_TYPE     
    if (isset($_GET['post_type'])){

        // Si le paramètre POST_TYPE est égal à page
        if ($_GET['post_type'] == "page"){

            // Si les paramètres ORDER et ORDERBY valent DESC et MODIFIED
            if (isset($_GET['order']) == "desc" && $_GET['orderby'] == "modified"){

                $columns['modifie']  = '<a href="'.admin_url( 'edit.php?post_type=page&orderby=modified&order=asc').'" class="modifie-lien"><span>Dernière modification</span><span class="sorting-indicator"></span></a>';
                return $columns;

            // Ou alors si les paramètres ORDER et ORDERBY valent ASC et MODIFIED        
            } elseif (isset($_GET['order']) == "asc" && $_GET['orderby'] == "modified"){

                $columns['modifie']  = '<a href="'.admin_url( 'edit.php?post_type=page&orderby=modified&order=desc').'" class="modifie-lien"><span>Dernière modification</span><span class="sorting-indicator"></span></a>';
                return $columns;
            
            // Ou alors si les paramètres ORDER et ORDERBY ne valent rien ou n'éxistent pas  
            } else {

                $columns['modifie']  = '<a href="'.admin_url( 'edit.php?post_type=page&orderby=modified&order=desc').'" class="modifie-lien"><span>Dernière modification</span><span class="sorting-indicator"></span></a>';
                return $columns;
            }

        // Si le paramètre POST_TYPE est égal à page-systeme
        } else if ($_GET['post_type'] == "page-systeme"){

            // Si les paramètres ORDER et ORDERBY valent DESC et MODIFIED
            if (isset($_GET['order']) == "desc" && $_GET['orderby'] == "modified"){

                $columns['modifie']  = '<a href="'.admin_url( 'edit.php?post_type=page-systeme&orderby=modified&order=asc').'" class="modifie-lien"><span>Dernière modification</span><span class="sorting-indicator"></span></a>';
                return $columns;

            // Si les paramètres ORDER et ORDERBY valent ASC et MODIFIED
            } elseif (isset($_GET['order']) == "asc" && $_GET['orderby'] == "modified"){

                $columns['modifie']  = '<a href="'.admin_url( 'edit.php?post_type=page-systeme&orderby=modified&order=desc').'" class="modifie-lien"><span>Dernière modification</span><span class="sorting-indicator"></span></a>';
                return $columns;
            
            // Si les paramètres ORDER et ORDERBY ne valent rien ou n'éxistent pas
            } else {

                $columns['modifie']  = '<a href="'.admin_url( 'edit.php?post_type=page-systeme&orderby=modified&order=desc').'" class="modifie-lien"><span>Dernière modification</span><span class="sorting-indicator"></span></a>';
                return $columns;
            }

        }

    // Si les paramètres ORDER et ORDERBY existent
    } else if (isset($_GET['order']) && isset($_GET['orderby'])){

        // Si les paramètres ORDER et ORDERBY valent DESC et MODIFIED
        if ($_GET['order'] == "desc" && $_GET['orderby'] == "modified"){

            $columns['modifie']  = '<a href="'.admin_url( 'edit.php?orderby=modified&order=asc').'" class="modifie-lien"><span>Dernière modification</span><span class="sorting-indicator"></span></a>';
            return $columns;

        // Si les paramètres ORDER et ORDERBY valent ASC et MODIFIED
        } elseif ($_GET['order'] == "asc" && $_GET['orderby'] == "modified"){

            $columns['modifie']  = '<a href="'.admin_url( 'edit.php?orderby=modified&order=desc').'" class="modifie-lien"><span>Dernière modification</span><span class="sorting-indicator"></span></a>';
            return $columns;
        
        // Si les paramètres ORDER et ORDERBY ne valent rien ou n'éxiste pas
        } else {

            $columns['modifie']  = '<a href="'.admin_url( 'edit.php?orderby=modified&order=desc').'" class="modifie-lien"><span>Dernière modification</span><span class="sorting-indicator"></span></a>';
            return $columns;
        }

    }

    $columns['modifie']  = '<a href="'.admin_url( 'edit.php?orderby=modified&order=desc').'" class="modifie-lien"><span>Dernière modification</span><span class="sorting-indicator"></span></a>';
    return $columns;
 
}


function creasit_javascript($post_id){

    // Si l'url contient le paramètre POST_TYPE  
    if (isset($_GET['post_type'])){

        // Si les paramètres ORDER et ORDERBY éxistent
        if (isset($_GET['order']) && isset($_GET['orderby'])){

            // Si les paramètres ORDER et ORDERBY valent DESC et MODIFIED
            if ($_GET['order'] == "desc" && $_GET['orderby'] == "modified"){

               echo '<style>a.modifie-lien span {float:left;}
                            a.modifie-lien span.sorting-indicator {display:block;background-position:-7px 0;}
                            a.modifie-lien span.sorting-indicator:before {content:"\f140";}
                            a:hover.modifie-lien span.sorting-indicator {display:block;background-position:-7px 0;}
                            a:hover.modifie-lien span.sorting-indicator:before {content:"\f142";} 
               </style>';

            // Si les paramètres ORDER et ORDERBY valent ASC et MODIFIED
            } else if ($_GET['order'] == "asc" && $_GET['orderby'] == "modified"){

                echo '<style>a.modifie-lien span {float:left;}
                             a.modifie-lien span.sorting-indicator {display:block;background-position:-7px 0;}
                             a.modifie-lien span.sorting-indicator:before {content:"\f142";}
                             a:hover.modifie-lien span.sorting-indicator {display:block;background-position:-7px 0;}
                             a:hover.modifie-lien span.sorting-indicator:before {content:"\f140";}   
                </style>';

            // Si les paramètres ORDER et ORDERBY ne valent rien
            } else {

                 echo '<style>a:hover.modifie-lien span {float:left;}
                             a:hover.modifie-lien span.sorting-indicator {display:block;background-position:-7px 0;}
                             a:hover.modifie-lien span.sorting-indicator:before {content:"\f142";}     
                </style>';

            }

        // Si les paramètres ORDER et ORDERBY n'éxistent pas
        } else {

            echo '<style>a:hover.modifie-lien span {float:left;}
                             a:hover.modifie-lien span.sorting-indicator {display:block;background-position:-7px 0;}
                             a:hover.modifie-lien span.sorting-indicator:before {content:"\f142";}     
                </style>';

        }

    // Si les paramètres ORDER et ORDERBY existent
    } else if (isset($_GET['order']) && isset($_GET['orderby'])){

        // Si les paramètres ORDER et ORDERBY valent DESC et MODIFIED
        if ($_GET['order'] == "desc" && $_GET['orderby'] == "modified"){

           echo '<style>a.modifie-lien span {float:left;}
                        a.modifie-lien span.sorting-indicator {display:block;background-position:-7px 0;}
                        a.modifie-lien span.sorting-indicator:before {content:"\f140";}
                        a:hover.modifie-lien span.sorting-indicator {display:block;background-position:-7px 0;}
                        a:hover.modifie-lien span.sorting-indicator:before {content:"\f142";} 
           </style>';

        // Si les paramètres ORDER et ORDERBY valent ASC et MODIFIED
        } else if ($_GET['order'] == "asc" && $_GET['orderby'] == "modified"){

            echo '<style>a.modifie-lien span {float:left;}
                         a.modifie-lien span.sorting-indicator {display:block;background-position:-7px 0;}
                         a.modifie-lien span.sorting-indicator:before {content:"\f142";}
                         a:hover.modifie-lien span.sorting-indicator {display:block;background-position:-7px 0;}
                         a:hover.modifie-lien span.sorting-indicator:before {content:"\f140";}   
            </style>';

        // Si les paramètres ORDER et ORDERBY ne valent rien
        } else {

             echo '<style>a:hover.modifie-lien span {float:left;}
                         a:hover.modifie-lien span.sorting-indicator {display:block;background-position:-7px 0;}
                         a:hover.modifie-lien span.sorting-indicator:before {content:"\f142";}     
            </style>';

        }

    // Si les paramètres ORDER et ORDERBY n'éxistent pas
    } else {

        echo '<style>a:hover.modifie-lien span {float:left;}
                             a:hover.modifie-lien span.sorting-indicator {display:block;background-position:-7px 0;}
                             a:hover.modifie-lien span.sorting-indicator:before {content:"\f142";}     
                </style>';

    }

    
}
add_action('admin_head','creasit_javascript');


/**
Ajouter le nombre de ctp sur le dashboard news
*/


add_filter( 'dashboard_glance_items', 'creasit_dashboard_dun_coup_doeil', 10, 1 );
function creasit_dashboard_dun_coup_doeil( $items = array() ) {
    $post_types = array( 'albums' );
    foreach( $post_types as $type ) {
        if( ! post_type_exists( $type ) ) continue;
        $num_posts = wp_count_posts( $type );
        if( $num_posts ) {
            $published = intval( $num_posts->publish );
            $post_type = get_post_type_object( $type );
            $text = _n( '%s ' . $post_type->labels->singular_name, '%s ' . $post_type->labels->name, $published, 'your_textdomain' );
            $text = sprintf( $text, number_format_i18n( $published ) );
            if ( current_user_can( $post_type->cap->edit_posts ) ) {
            $output = '<a href="edit.php?post_type=' . $post_type->name . '">' . $text . '</a>';
                echo '<li class="post-count ' . $post_type->name . '-count">' . $output . '</li>';
            } else {
            $output = '<span>' . $text . '</span>';
                echo '<li class="post-count ' . $post_type->name . '-count">' . $output . '</li>';
            }
        }
    }
    return $items;
}

// Ajouter une îcone au nombre albums + Cacher version de Wp + "Moteur de recherche refusés" bien indiqué
function creasit_icon_dashboard_dun_coup_doeil() {
    echo '<style type="text/css">
        .albums-count a:before {content:"\f232"!important}
        #dashboard_right_now p#wp-version-message {display:none;}
        </style>';

    global $current_user;
    $user_role = $current_user->roles[0];
    if($user_role != 'administrator') {
        
        echo '<style type="text/css">
            .albums-count a:before {content:"\f232"!important}
            #dashboard_right_now p#wp-version-message {display:none;}
            #dashboard_right_now .main p a {display:none;}
            </style>';

    } else {
         echo '<style type="text/css">
            .albums-count a:before {content:"\f232"!important}
            #dashboard_right_now p#wp-version-message {display:none;}
            #dashboard_right_now .main p a {color:red;font-size:16px;background:url('.get_template_directory_uri().'/images/ico-error.png) no-repeat left center;padding-left:25px;}
            #dashboard_right_now .main p a:hover {text-decoration:underline;}
            </style>';
        
    }
}
add_action('admin_head', 'creasit_icon_dashboard_dun_coup_doeil');

/**
Cacher les widgets natifs de WP
*/
add_action('wp_dashboard_setup', 'creasit_suppression_widget_dashboard' );    
function creasit_suppression_widget_dashboard() {    
    global $wp_meta_boxes;
 
    // Tableau de bord général
    unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_incoming_links']);
    unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_activity']); // Activité
    unset($wp_meta_boxes['dashboard']['normal']['core']['welcome-panel']); // Bienvenue
    unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_plugins']); // Extensions
    unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_quick_press']); // Liens entrant
    unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_recent_drafts']); // Billets en brouillon
    unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_primary']); // Blogs WordPress
    unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_secondary']); // Autres actualités WordPress
}

remove_action('welcome_panel', 'wp_welcome_panel');


/**
Suppression des elements modification rapide (quick box)
*/

function creasit_suppression_element_modification_rapide(){
  echo '<style>
          table.wp-list-table fieldset.inline-edit-col-right label.inline-edit-tags {display:none;}
          table.wp-list-table fieldset.inline-edit-col-right .inline-edit-group label.alignleft {display:none;}
          table.wp-list-table fieldset.inline-edit-col-right .inline-edit-group label.inline-edit-status {display:block;}
          #wpbody-content .inline-edit-row-post .inline-edit-col-center {width:60%;}
          table.wp-list-table label.inline-edit-author {display:none;}
        </style>';
}

add_action( 'quick_edit_custom_box', 'creasit_suppression_element_modification_rapide', 10, 2 );