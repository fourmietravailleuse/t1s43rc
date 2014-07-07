<?php

// Appel du js et css
wp_enqueue_script('caroufredsel', plugins_url().'/creasit-albums/js/jquery.carouFredSel-6.2.1-packed.js', array('jquery'), '6.2.1', true);
wp_enqueue_script('caroufredsel-trans', plugins_url().'/creasit-albums/js/jquery.transit.min.js', array('jquery'), '6.2.1', true);
wp_enqueue_style('styles-caroufredsel', plugins_url().'/creasit-albums/css/styles-caroufredsel.css', array(), '1.0', 'screen, projection');

    
if(!is_front_page()) {
    $width_px = get_field('largeur', $id_carousel);
} else {
    $width_px = '960';
}

$effet = get_field('effets', $id_carousel);

$mode_affichage = get_field('mode_affichage', $id_carousel);

$vitesse = get_field('vitesse', $id_carousel);
$vitesse = $vitesse*1000;

$fleche_controle = get_field('fleche_de_controle', $id_carousel);

$puce_pagination = get_field('puces_de_pagination', $id_carousel);

$minuteur = get_field('minuteur', $id_carousel);


// Je récupère le nombre de vignette
if($mode_affichage == 'vignettes') {
    $nbVignette = 1;
    foreach ( $images_carousel as $attachment ) {
        $check_file = creasit_albums_check_not_image($attachment);
        if(empty($check_file)) {
            $nbVignette++;
        }
    }
}

$output .= '<div class="carousel-main" style="width: '.$width_px.'px;">';
$output .= '<ul id="carousel" class="'.$class_carousel.' '.$name_carousel.'" >';
foreach ( $images_carousel as $attachment ) {
    
    // Si le fichier n'est pas image, il n'apparait pas dans l'album
    $check_file = creasit_albums_check_not_image($attachment);
    if(empty($check_file)) break;

    

    // Légende
    $caption = wp_prepare_attachment_for_js($attachment);
    $caption = $caption['caption'];


    // Si le mode d'affichage est en carousel, je calcule la taille des images afin qu'elles apparaissent correctement 
    if($mode_affichage == 'carousel') {
        // Les images du carousel sont affichés par 3
        $width_px_carousel = $width_px/3;
        // add_image_size('carousel_img', $width_px_carousel);
        $image = wp_get_attachment_image($attachment, array($width_px_carousel, $width_px_carousel));
    } 
    // Sinon j'adapte l'image à la taille de l'album

    else {
         // src
        // add_image_size('carousel', $width_px, $width_px, true);
        $image = wp_get_attachment_image($attachment, array($width_px, $width_px));
    }

   
    $image_full = wp_get_attachment_image_src($attachment, 'full');


    $output .= '<li class="slide '.$attachment.'">';
    // $output .= "<div class='center'>";

    $output .= '<a href="'.$image_full[0].'" rel="gallery-'.$id_carousel.'">';
    $output .= $image;
    $output .= '</a>';

    if(!empty($caption)) $output .= '<p class="carousel-caption">'.$caption.'</p>';

    $output .= '</li>';
}



$output .= '</ul>';

if(!empty($fleche_controle)) {
    $output .= '<div class="carousel-direction-nav">
                    <a class="carousel-prev" href="#"></a>
                    <a class="carousel-next" href="#"></a>
                </div>';
}

// if(!empty($minuteur)) {
//         $output .= '<div id="timer-carousel" class="timer"></div>';
// }

if(!empty($puce_pagination)) {
    $noBg = '';
    if($mode_affichage != 'vignettes' && !empty($puce_pagination)) $noBg = 'style="background:none;"';
    $output .= '<div class="carousel-pag" '.$noBg.'>';

    if($mode_affichage == 'vignettes') {
        // Je récupère le nombre de vignette pour ajuste la taille des vignettes
        $width_px_vignette = $width_px/$nbVignette;
        
        foreach ( $images_carousel as $attachment ) { 
            $image_vignette = wp_get_attachment_image($attachment, array($width_px_vignette, $width_px_vignette));
            $output .= $image_vignette;
        }
    }

    $output .= '</div>';
}

$output .= '</div>';



// Script de paramètrage de carouFredsel : http://docs.dev7studios.com/jquery-plugins/caroufredsel-advanced
$output .= '
<script type="text/javascript" charset="utf-8">
jQuery(function($) {';
        


// Liste fx : "none", "scroll", "directscroll", "fade", "crossfade", "cover", "cover-fade", "uncover" or "uncover-fade"
// Liste easing : "linear" and "swing", built in: "quadratic", "cubic" and "elastic".
$output .= '
$(".'.$name_carousel.'").carouFredSel({';

    if($mode_affichage == 'carousel') {
        $output .= 'items: 3,';
    }

    $output .= '
    width: "'.$width_px.'",
    responsive: true,
    scroll : {
        fx: "'.$effet.'",
        easing: "linear",
        duration: 1000,
        pauseOnHover: true
    },';

    // if(!empty($minuteur)) {
    //     $output .= 'progress: { 
    //         bar: "#timer-carousel"
    //     },';
    // }
     
    $output .= '
    auto: '.$vitesse;
    
    if(!empty($fleche_controle)) {
        $output .= '
        ,next: {
            button: ".carousel-next",
        },
        prev: {
            button: ".carousel-prev",
        }';
    }

    if(!empty($puce_pagination)) {
        $output .= '
        ,pagination: {';
            if($mode_affichage == 'vignettes') $output .= 'anchorBuilder: false,';
            $output .= 'container: ".carousel-pag"    
        }';
    }      


$output .= '    
    });
});
</script>
';
