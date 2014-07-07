				
			</div> <?php /* #main */ ?>
			
		<?php
    	if(!is_front_page()) { ?>
			</div> <?php /* #main-bk */ ?>
	      <?php
	    } ?>

			
		<footer class="site-footer" role="contentinfo">

			<div class="center">

				<div class="infos">
					<h2><?php _e('NOUS CONTACTER', 'crealocal'); ?></h2>
					<div class="address">
						<p>AGENCE CREASIT</p>
						<p>86 rue de la Ville en Pierre</p>
						<p>Tél : 02 40 37  01 77</p>
						<p>Web : <a href="http://www.creasit.fr/">www.creasit.fr</a></p>
					</div>
					<a href="<?php echo esc_url( get_permalink( get_page_by_title('Contact'))); ?>" class="nous-ecrire">
						<img src="<?php echo get_template_directory_uri(); ?>/images/ecrire.png" alt="nous ecrire">
						<span><?php _e('Nous écrire', 'crealocal'); ?></span>
					</a>
				</div>

				<div class="follow">
					<h2><?php _e('SUIVEZ NOUS AUSI SUR', 'crealocal'); ?> :</h2>
					<ul class="links">
						<li><a target="_blank" href="https://fr-fr.facebook.com/agence.creasit" class="fb"></a></li>
						<li><a target="_blank" href="https://twitter.com/creasit" class="twitter"></a></li>
						<li><a target="_blank" href="https://plus.google.com/+CreasitFr/posts" class="gplus"></a></li>
						<li><a target="_blank" href="https://www.youtube.com/user/creasit" class="youtube"></a></li>
						<li><a target="_blank" href="http://instagram.com/agencecreasit" class="instagram"></a></li>
						<li><a target="_blank" href="http://www.pinterest.com/creasit/" class="pinterest"></a></li>
					</ul>
				</div>

				<?php wp_nav_menu(array('theme_location' => 'foot', 'menu' => 'foot', 'menu_class' => 'foot-menu')) ; ?>
			</div>

		</footer>
	</div>

	<?php wp_footer(); ?>
</body>
</html>