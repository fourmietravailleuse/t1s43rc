function getExtention(fileName){
	 var i = fileName.lastIndexOf('.');
	 if(i === -1 ) return false;
	 return fileName.slice(i)
}

jQuery(function($) {

	$('.entry-content a').each( function(){
		// Si c'est un pdf je l'ouvre en target="_blank"
		if(getExtention($(this).attr('href')) == '.pdf') $(this).attr('target', '_blank');

		// Regex de lien posté
		var sTmp = $(this).attr("href");
		var sReg = /(http|https):\/\/((?:.|\n)*?).(fr|com|org|net|local|tel|es|ch|tv|pm|name|info|dev|paris|in|pl|voyage|construction|eu|co.uk|re|tf|mobi|co|tw|boutique|com.fr|be|nl|ca|wf|biz|me|jp|photo|email|tm.fr|net|de|at|cc|yt|bzh|club|marketing|asso.fr)/;
		var urlLien = sTmp.match(sReg);

		// Regex de l'url du site
		var sTmp2 = document.location.origin;
		var sReg2 = /(http|https):\/\/((?:.|\n)*?).(fr|com|org|net|local|tel|es|ch|tv|pm|name|info|dev|paris|in|pl|voyage|construction|eu|co.uk|re|tf|mobi|co|tw|boutique|com.fr|be|nl|ca|wf|biz|me|jp|photo|email|tm.fr|net|de|at|cc|yt|bzh|club|marketing|asso.fr)/;
		var urlSite = sTmp2.match(sReg2);


		if (sTmp.substr(0,6) == "mailto" ){

			$(this).addClass("email-mailto");

		} else {
			// Récupère les premières lettres des variables
			if(urlLien != null || urlLien != "undefined"){
				var urlTWL = urlLien[2].substr(0,4);
				var urlTWS = urlSite[2].substr(0,4);
			}

			// Test si les variables commencent ou pas par www.
			if(urlTWL == "www." && urlTWS != "www."){
				urlSite[2] = "www."+ urlSite[2];
			}

			// Test si le lien posté est le même que l'URL du site
			if((urlLien != null || urlLien != "undefined") && urlLien[2] != urlSite[2]){
				$(this).addClass("link-external");
				$(this).attr('target','_blank');
			
			}
		}
	});
});


