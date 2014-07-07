jQuery(function($) {
	
	// -------------------------------------------------------------------------------------------------------------------------------

	// Ajouter l'activ sur les nouveaux menus (Rôles)
	
	if( ($("body").hasClass("media_page_mla-menu")) || ($("body").hasClass("media-new-php")) || ($("body").hasClass("taxonomy-attachment_category")) ){
		
		$("li#toplevel_page_upload-page-mla-menu").removeClass("wp-not-current-submenu");
		$("li#toplevel_page_upload-page-mla-menu").addClass("wp-has-current-submenu wp-menu-open");
		$("li#toplevel_page_upload-page-mla-menu a.menu-top").addClass("wp-has-current-submenu");	

	}

	if ($("body").hasClass("taxonomy-attachment_category")){

		$("li#toplevel_page_upload-page-mla-menu ul li:last-child").addClass("current");
		$("li#toplevel_page_upload-page-mla-menu ul li:last-child a").addClass("current");

	}

	// -------------------------------------------------------------------------------------------------------------------------------
	
	// Modifier l'ordre des boutons mise à jour et déplacer dans la corbeille

	if ($(".submitbox").find()){

		$(".submitbox #publishing-action").insertBefore(".submitbox #delete-action");

	}

	// -------------------------------------------------------------------------------------------------------------------------------

	// Modifier le titre des statistiques de Piwik

	if(jQuery('body').hasClass('dashboard_page_wp-piwik_stats')){

		jQuery('#wp-piwik-stats-general h2').html('Statistiques <span style="font-size:14px;">(<a href="http://'+window.location.hostname+'/piwik/" target="_blank">Accédez directement à Piwik</a>)</span>');

	}

	// -------------------------------------------------------------------------------------------------------------------------------

});



