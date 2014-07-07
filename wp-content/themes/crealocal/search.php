
<?php get_header(); ?>
<div class="page">

  <header class="page-header">
    <h1 class="entry-title">Résultats de votre recherche sur les termes : <span class="vert-clair">" <?php the_search_query(); ?> "</span></h1>
  </header>

  <div class="search entry-content">

    <div class="container ">
      
    </div>


    <?php
    if(have_posts()) :
      while(have_posts()) : the_post(); ?>

        <div <?php post_class(); ?>>
          <div class="content-post-search">
            <a href="<?php the_permalink(); ?>">
              <?php
              if ( has_post_thumbnail() ) {
                the_post_thumbnail('little_preview');
              } ?>
            </a>

            <div class="content-post-excerpt">
              <?php 
              $keys = implode('|', explode(' ', get_search_query()));
              $title = preg_replace('/('.$keys .')/iu', '<span class="search-term">\0</span>', get_the_title());
              $excerpt = preg_replace('/('.$keys .')/iu', '<span class="search-term">\0</span>', get_the_excerpt());
              ?>
              <h3><a href="<?php the_permalink(); ?>"><?php echo $title; ?></a></h3>
              <?php echo $excerpt; ?>
            </div>
          </div>
        </div>


    <?php
      endwhile;
    else: ?>

      <div class="post">
        <p>Aucun résultat...</p>
      </div>

    <?php
    endif; ?>

      <div class="navigation">
        <div class="alignleft"><?php previous_posts_link('Page précedente') ?></div>
        <div class="alignright"><?php next_posts_link('Page suivante') ?></div>
      </div>
    </div>



</div>


<?php get_footer(); ?>