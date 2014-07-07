<?php get_header(); ?>

<div id="main-content" class="main-content">

    <div id="content" class="site-content" role="main">

        <?php if ( have_posts() ) : the_post(); ?>

            <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

                <?php
                if (has_post_thumbnail()) { ?>
                    <div class="img-actu">
                        <a href="<?php the_permalink(); ?>"><?php the_post_thumbnail('news_preview'); ?></a>
                    </div>
                <?php
                } ?>
                
                <div class="articles-content">
                    <header class="entry-header">
                        <span class="title">
                            <?php
                            $trimmed_title = wp_trim_words( get_the_title(), 20, '...' );
                            echo '<p><a href="'.get_permalink().'">'.$trimmed_title.'</a></p>';
                            ?>
                        </span>
                        <hr>
                        <p class="date">
                            <span><?php echo __('PubliÃ© le', 'scot');?></span>
                            <span><?php the_time(get_option('date_format')); ?></span>
                        </p>
                    </header>

                    <div class="entry-content">

                        <div class="the-content">
                            <?php the_content(); ?>
                        </div>

                    </div>
                </div>
            </article>
        
        <?php endif; ?>

    </div>
</div>


<?php get_sidebar(); ?>

<?php get_footer(); ?>
