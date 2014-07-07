<?php get_header(); ?>

<?php 
wp_enqueue_style('styles-albums', plugins_url().'/creasit-albums/css/styles-albums.css', array(), '1.0', 'screen, projection'); 
wp_enqueue_script('masonry', plugins_url().'/creasit-albums/js/masonry.pkgd.min.js', array('jquery'), '1.0', true); 
?>

<div id="main-content" class="main-content">

    <div id="content" class="site-content" role="main">

        <?php if ( have_posts() ) : the_post(); ?>


            <article id="post-<?php the_ID(); ?>" <?php post_class('single-albums'); ?>>

                <header class="entry-header">
                    <?php the_title( '<h1 class="entry-title">', '</h1>' ); 
                
                    // Boutons de partage 
                    get_template_part('parts/partage'); 
                    ?>
                </header>


                <?php if (get_field('introduction')){ ?>
                    <div class="introduction">
                        <p><?php the_field('introduction'); ?></p>
                    </div>
                <?php } ?>


                <div class="entry-content">

                    <?php
                    // Afficher les medias de l'album
                    $medias =  get_field('gallery', get_the_ID()); 
                    if(!empty($medias)) { ?>

                        <ul class="liste-album-file">
                            <?php
                            $arrayOtherFile = array();
                            foreach ($medias as $media) {
                                $link_img = wp_get_attachment_image($media, array('300', '300'));
                                $link_img_full = wp_get_attachment_image_src($media, 'full'); 

                                // Si le fichier n'est pas image, il n'apparait pas dans l'album
                                $check_file = creasit_check_not_image($media);
                                if(!empty($check_file)) { ?>

                                    <li class="item">
                                        <a href="<?php echo $link_img_full[0]; ?>" rel="album-name">
                                            <?php echo $link_img; ?>
                                        </a>
                                    </li>

                            <?php
                                } else { // Sinon j'insère les ID des médias qui ne sont pas des images pour les afficher après
                                    $arrayOtherFile[] = $media;
                                }
                            } 

                            if(!empty($arrayOtherFile)) {

                                foreach ($arrayOtherFile as $value) {
                                    $otherFileMimeType = get_post_mime_type($value);
                                    $otherFileSrc = wp_get_attachment_url($value);
                                    
                                    if($otherFileMimeType == 'application/rar' || $otherFileMimeType == 'application/zip') {
                                        $otherFile = '<a href="'.$otherFileSrc.'"><img src="'.WP_CONTENT_URL.'/themes/crealocal/images/icons/archive.gif"></a>';
                                    }
                                    else if($otherFileMimeType == 'application/vnd.openxmlformats-officedocument.wordprocessingml.document') {
                                        $otherFile = '<a href="'.$otherFileSrc.'"><img src="'.WP_CONTENT_URL.'/themes/crealocal/images/icons/document.gif"></a>';
                                    }
                                    else if($otherFileMimeType == 'application/pdf') {
                                        $otherFile = '<a href="'.$otherFileSrc.'"><img src="'.WP_CONTENT_URL.'/themes/crealocal/images/icons/pdf.gif"></a>';
                                    }
                                    else if(strpos($otherFileMimeType, 'audio/') !== false) {
                                        $mimeAudio = array('audio/mpeg', 'audio/ogg', 'audio/wav');
                                        if(in_array($otherFileMimeType, $mimeAudio)) {
                                            $otherFile = '
                                            <audio src="'.$otherFileSrc.'" controls>Veuillez mettre à jour votre navigateur !</audio>';
                                        }
                                        else {
                                            $otherFile = 'Le format du fichier audio n\'est pas compatible';
                                        }
                                    }

                                    echo '<li class="item">'.$otherFile.'</li>'; 

                                }
                            } ?>
                        </ul>

                    <?php 
                    } ?>

                </div>

            </article>

            <script type="text/javascript">
            jQuery(function($) {
                var $container = $('.liste-album-file');
                $container.masonry({
                  columnWidth: 300,
                  itemSelector: '.item',
                  gutter: 20
                });
            });
            </script>

        <?php endif; ?>

    </div>

</div>

<?php get_footer(); ?>