<?php

// L'affichage vignette ne peut pas être fait sur la page d'accueil
if(is_front_page()) {
    $output .= '<div>Cet affichage ne peut pas être sur la page d\'accueil.</div>'; 
} else {
    wp_enqueue_style('styles-lien-album', plugins_url().'/creasit-albums/css/styles-lien-album.css', array(), '1.0', 'screen, projection');

    $titre = get_the_title($id_carousel);
    $lien = get_permalink($id_carousel);


    $output .= '<a href="'.$lien.'" class="vignette-album">';

    $output .= '<img src="'.get_template_directory_uri().'/images/PictosAlbumsDiapo.png" title="Lien vers l\'album" alt="Lien vers l\'album">';
    $output .= '<p>'.$titre.'</p>';
        
    $output .= '</a>'; 
}




