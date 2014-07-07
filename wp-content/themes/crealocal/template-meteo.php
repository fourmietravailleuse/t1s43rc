<?php
/*
Template Name Posts: Météo
*/

$content = get_option('creasit_dev_informations');
$meteo_ville = $content['meteo_ville'];
$maree_ville = $content['maree_ville'];
$meteo_national_aujourdhui = $content['meteo_national_aujourdhui'];
$meteo_national_demain = $content['meteo_national_demain'];
?>


<?php get_header(); ?>

<div id="main-content" class="main-content">

	<div id="content" class="site-content" role="main">

		  <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
				<?php /* the_thumbnail(); */ ?>

				<header class="entry-header">
					<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
					<?php 

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
						the_content();
					?>


					<?php 

					echo '<h2 class="title" style="color: #353535;">PRÉVISIONS LOCALES À 3 JOURS</h2>';

					echo $meteo_ville;

					echo '<h2 class="title" style="color: #353535;">LES MARÉES</h2>';

					echo $maree_ville;

					$url = $meteo_national_aujourdhui;
					$headers = @get_headers($url);
					if(strpos($headers[0],'404') === false)
					{
					  $img_aujourdhui = true;
					}
					else
					{
					  $img_aujourdhui = false;
					}

					$url = $meteo_national_demain;
					$headers = @get_headers($url);
					if(strpos($headers[0],'404') === false)
					{
					  $img_demain = true;
					}
					else
					{
					  $img_demain = false;
					}


					if ($img_demain && $img_aujourdhui){

						echo '<h2 class="title" style="color: #353535;">MÉTÉO NATIONALE</h2>';

						echo '
							<div class="meteo-nationale block">
								<div class="content">
								<div class="france24h"><img src="'.$meteo_national_aujourdhui.'" alt="Prévisions météo nationales à 24 heures" title="Prévisions météo nationales à 24 heures" /></div>
								<div class="france48h"><img src="'.$meteo_national_demain.'" alt="Prévisions météo nationales à 48 heures" title="Prévisions météo nationales à 48 heures" /></div>
							</div>
						';
					}

					?>

				</div>

			</article>
	</div>

</div>

<?php get_footer(); ?>