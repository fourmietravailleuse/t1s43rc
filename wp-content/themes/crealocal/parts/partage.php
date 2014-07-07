<?php 
$content = get_option('creasit_dev_informations');
$profil_id = $content['profil_id'];

if(!empty($profil_id)) { ?>
	<!-- AddThis Button BEGIN -->
	<div class="addthis_toolbox addthis_default_style addthis_16x16_style">
		<a class="addthis_button_facebook"></a>
		<a class="addthis_button_twitter"></a>
		<a class="addthis_button_pinterest_share"></a>
		<a class="addthis_button_google_plusone_share"></a>
		<a class="addthis_button_compact"></a><a class="addthis_counter addthis_bubble_style"></a>
	</div>
	<script type="text/javascript" src="//s7.addthis.com/js/300/addthis_widget.js#pubid=<?php echo $profil_id; ?>"></script>
	<!-- AddThis Button END -->
<?php
} ?>
