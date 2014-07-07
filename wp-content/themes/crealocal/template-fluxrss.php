<?php
/*
Template Name Posts: Flux RSS
*/
?>


<?php get_header(); ?>


<div id="main-content" class="main-content">

        <div id="content" class="site-content" role="main">
            
            <h1><?php the_title(); ?></h1>

            <?php if (get_field('introduction')){ ?>
                <div class="introduction">
                    <p><?php the_field('introduction'); ?></p>
                </div>
            <?php } ?>

            <div class="entry-content"><?php the_content(); ?></div>
            
            <div class="liste-flux-rss entry-content">
                <h2><?php _e('Abonnez-vous !', 'solution'); ?></h2>
                <h3><?php _e('Nous vous proposons le(s) flux RSS suivant(s)', 'solution'); ?> :</h3>
                <ul>
                    <li>
                        <a href="<?php echo get_bloginfo('url').'/feed/?post_type=post'; ?>" target="_blank"><?php _e('flux rss Pages', 'solution'); ?></a>
                    </li> 
                    <li>
                        <a href="<?php echo get_bloginfo('url').'/feed/?post_type=page'; ?>" target="_blank"><?php _e('flux rss Actualités', 'solution'); ?></a>
                    </li>
                </ul>
            </div>


        </div>
    
</div>

<?php get_footer(); ?>



 