<!doctype html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7" <?php language_attributes(); ?>> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8" <?php language_attributes(); ?>> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9" <?php language_attributes(); ?>> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" <?php language_attributes(); ?>> <!--<![endif]-->
<head>

<meta name="viewport" content="width=device-width, initial-scale=1" />
<meta charset="<?php bloginfo( 'charset' ); ?>">

<?php
// Référencemenet title et meta descriptio
if(!is_admin() && isset($post->ID)) {
  // Est-ce que cette page à un parent
  if(!empty($post->post_parent)) $postParentTitle = get_the_title($post->post_parent);

  $metaTitre = get_field('meta_titre', get_the_ID());

  if(empty($metaTitre)) { 

    if(isset($postParentTitle)) { ?>
      <title><?php echo $postParentTitle; wp_title('>', true, 'left'); ?></title>
    <?php
    } else { ?>
      <title><?php wp_title(''); ?></title>
    <?php
    }
  } else { 
    if(isset($postParentTitle)) { ?>
      <title><?php echo $postParentTitle.' > '.$metaTitre; ?></title>
    <?php
    } else { ?>
      <title><?php echo $metaTitre; ?></title>
  <?php }
  }

  $metaDescription = get_field('meta_description', get_the_ID());
  if(empty($metaDescription)) $metaDescription = ''; ?>
  <meta name="description" content="<?php echo $metaDescription; ?>" />

<?php
} ?>


<link rel="shortcut icon" type="image/png" href="<?php bloginfo('stylesheet_directory'); ?>/images/favicon.png" />
<link rel="icon" type="image/vnd.microsoft.icon" href="<?php bloginfo('stylesheet_directory'); ?>/images/favicon.ico" />
<link rel="apple-touch-icon" href="<?php bloginfo('stylesheet_directory'); ?>/images/favicon-iphone.png" />
<link href='http://fonts.googleapis.com/css?family=Yanone+Kaffeesatz:400,700' rel='stylesheet' type='text/css'>

<?php wp_head(); ?>
<!--[if LTE IE 9]>
<script type='text/javascript' src='<?php echo get_template_directory_uri().'/js/html5shiv.js'; ?>'></script>
<![endif]-->


</head>

<body <?php body_class(); ?>>


  <header id="header">
	
	<div class="header-bk">
		<div class="center">
			<a href="/" class="logo">
				<img class="logo" src="<?php header_image(); ?>" height="<?php echo get_custom_header()->height; ?>" width="<?php echo get_custom_header()->width; ?>" alt="<?php echo get_bloginfo('name'); ?>">
			</a>
		</div>
	</div>

	
    <div class="menu-wrapper">
      <div class="center">
        <?php wp_nav_menu(array('theme_location' => 'main', 'menu' => 'main', 'menu_class' => 'main-menu')); ?>
  	 
    		<div class="search">
    			<?php get_search_form(); ?>
    		</div>
      </div>
    </div>
    

  </header><?php // #header ?>


  <div class="main-wrapper">
    
    <?php
    if(!is_front_page()) { ?>
      <div class="main-bk">
      <?php
    } ?>
    
    <!--[if lt IE 7]>
    <p class="chromeframe">You are using an outdated browser. <a href="http://browsehappy.com/">Upgrade your browser today</a> or <a href="http://www.google.com/chromeframe/?redirect=true">install Google Chrome Frame</a> to better experience this site.</p>
    <![endif]-->
    <?php
    if ( !is_front_page() && function_exists('yoast_breadcrumb') ) {
      yoast_breadcrumb('<div id="breadcrumbs">','</div>');
    }
     ?>   
    <div id="main" role="main">
        

