<?php
/*
Template Name Posts: Actualités
*/
?>

<?php

get_header(); ?>
  

<?php query_posts('showposts=-1'); ?>


    <div class="page page-articles">
      <header class="page-header">
        <h1 class="entry-title"><?php the_title(); ?></h1>
      </header>

      <?php if (get_field('introduction')){ ?>
          <div class="introduction">
            <p><?php the_field('introduction'); ?></p>
          </div>
      <?php } ?>

      <?php if ( have_posts() ) : ?>
        <div class="liste-actus">
          <?php
          while ( have_posts() ) : the_post();
              get_template_part( 'content', 'post');
          endwhile; ?>
        </div>  

        <div class="pagination">
          <?php echo previous_posts_link(__('ActualitÃ©s prÃ©cÃ©dents', 'scot')); ?>
          <?php echo next_posts_link(__('ActualitÃ©s suivants', 'scot')); ?>
        </div>

    <?php endif; ?>
    </div>

<?php wp_reset_query(); ?>

<?php
get_footer();
?>