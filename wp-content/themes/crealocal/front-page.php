<?php get_header(); ?>

<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

<div class="front-page center">

    <div class="slider">
        <?php the_content(); ?>
        <?php if( current_user_can ('edit_pages')) { ?>
            <a href="<?php echo get_edit_post_link(); ?>" target="_blank" class="edition"><?php _e("Editer la page","crealocal"); ?></a>
        <?php } ?>
    </div>

    <div class="front-page-content">
    	
    	<div class="block-front-page ref">
    		<img src="<?php echo get_template_directory_uri(); ?>/images/refs.png">
    		<div class="block-content">
    			<img src="<?php echo get_template_directory_uri(); ?>/images/screens.png">
    			<a href="http://www.creasit.fr/index.php?module=Contenus&tid=100"><?php _e('Découvrez toutes les références Creasit','solution'); ?> ></a>
    		</div>
    	</div>

    	<div class="block-front-page news">
    		<img src="<?php echo get_template_directory_uri(); ?>/images/news.png">
    		<div class="block-content">
                <?php
                $args = array(
                  'posts_per_page' => 1,
                  'post__in'  => get_option( 'sticky_posts' ),
                  'ignore_sticky_posts' => 1
                );

                $actu_sticky = new WP_Query( $args );
                if ($actu_sticky->have_posts()) {
                  while ($actu_sticky->have_posts()) {
                    $actu_sticky->the_post(); ?>
                    <?php if( current_user_can ('edit_pages')) { ?>
                            <a href="<?php echo get_edit_post_link(); ?>" target="_blank" class="edition"><?php _e("Editer l'article","crealocal"); ?></a>
                        <?php } ?>
                    <h2><?php the_title(); ?></h2>
                    <div class="content">
                        <?php
                        $trimmed_content = wp_trim_words( get_the_content(), 35, ' [...]' );
                        echo '<p>'.$trimmed_content.'</p>';
                        ?>
                        <a href="<?php the_permalink(); ?>"><?php echo __('Lire la suite', 'solution'); ?> ></a>
                    </div>
                <?php
                  }
                } ?>

    		</div>
    	</div>

    	<iframe src="//www.facebook.com/plugins/likebox.php?href=https%3A%2F%2Fwww.facebook.com%2Fagence.creasit&amp;width&amp;height=284&amp;colorscheme=light&amp;show_faces=true&amp;header=true&amp;stream=false&amp;show_border=true" scrolling="no" frameborder="0" style="border:none; overflow:hidden; height:284px;" allowTransparency="true"></iframe>

    </div>

</div>

<?php endwhile; endif; ?>

<?php get_footer(); ?>