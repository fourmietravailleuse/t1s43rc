// Unify gmaps and osm
var noopMap = {
	// Shorthand
	getEl: function(id) {
		return document.getElementById(id);
	},

	// Set latitude and longitude into "lato" and "lngo" fields, display a green "OK" message, open the left sub-panel, and display the map
	setLatLng: function(m, result) {
		var lato		= this.getEl(m.prefix+'-lato');
		var lngo		= this.getEl(m.prefix+'-lngo');
		var errcnt		= this.getEl(m.prefix+'-geo-msg');

		if ( !m.source || m.source == 'gmaps' ) {
			lato.value	= result.geometry.location.lat();
			lngo.value	= result.geometry.location.lng();
		}
		else if ( m.source == 'osm' ) {
			lato.value	= result.lat;
			lngo.value	= result.lon;
		}
		errcnt.style.display = 'inline-block';
		errcnt.style.color = 'green';
		errcnt.innerHTML = 'OK!';
		jQuery(this.getEl(m.prefix+'-show-map')).removeClass('hidden').trigger('click');
	},

	// Display an error message
	errorMsg: function(m, msg) {
		var errcnt		= this.getEl(m.prefix+'-geo-msg');

		errcnt.style.display = 'block';
		errcnt.style.color = 'red';
		errcnt.innerHTML = msg;
	},

	// !Geocode the address
	getCoords: function(m) {
		var address		= '';
		var adr_nogeo	= this.getEl(m.address + '_nogeo');
		if ( adr_nogeo === null || !adr_nogeo.checked ) {
			var adr		= this.getEl(m.address);
			address		= adr !== null		&& adr.value.length			? adr.value : '';
		}
		var adr_2_nogeo	= this.getEl(m.address_2 + '_nogeo');
		if ( adr_2_nogeo === null || !adr_2_nogeo.checked ) {
			var adr_2	= this.getEl(m.address_2);
			address		+= adr_2 !== null	&& adr_2.value.length		? (address ? ',' : '') + adr_2.value : '';
		}
		var state		= this.getEl(m.state);
			address		+= state !== null	&& state.value.length		? (address ? ',' : '') + state.value : '';
		var zip			= this.getEl(m.zip);
			address		+= zip !== null		&& zip.value.length			? (address ? ',' : '') + zip.value : '';
		var city		= this.getEl(m.city);
			address		+= city !== null		&& city.value.length	? (address ? ',' : '') + city.value : '';
		var country		= this.getEl(m.country);
			address		+= country !== null	&& country.value.length		? (address ? ',' : '') + country.value : '';

		if ( !m.source || m.source == 'gmaps' ) {
			var geocoder	= new window.google.maps.Geocoder();

			if (geocoder) {
				geocoder.geocode({ 'address': address },
					function(result, status) {
						if (status == 'OK' && result.length > 0) {
							noopMap.setLatLng(m, result[0]);
						} else if (status == 'OK') {
							noopMap.errorMsg(m, window.mapl10n.not_found);
						} else {
							noopMap.errorMsg(m, status);
						}
					}
				);
			} else {
				this.errorMsg(m, window.mapl10n.error);
			}
		}
		else if ( m.source == 'osm' ) {
			jQuery.getJSON( 'http://nominatim.openstreetmap.org/search?format=json&q='+(address.replace(/ /g,'+')) )
				.done( function( result ) {
					if ( result.length > 0 ) {
						noopMap.setLatLng(m, result[0]);
					} else {
						noopMap.errorMsg(m, window.mapl10n.not_found);
					}
				} )
				.fail( function(){
					noopMap.errorMsg(m, window.mapl10n.error);
				} )/*
				.always( function(){ } )*/;
		}
	},

	// Build a LatLng object
	LatLng: function(m, lat, lng) {
		if ( !m.source || m.source == 'gmaps' ) {
			return new window.google.maps.LatLng(lat, lng);
		}
		else if ( m.source == 'osm' ) {
			return new window.L.LatLng(lat, lng);
		}
	},

	// Build a map
	map: function(m) {
		var opt = {
			center:		m.center,
			zoom:		m.zoomval,
			minZoom:	2,
			maxZoom:	18
		};
		if ( !m.source || m.source == 'gmaps' ) {
			opt.mapTypeId	= window.google.maps.MapTypeId.ROADMAP;
			opt.panControl	= false;
			opt.maxZoom		= 20;
			opt.scrollwheel	= false;
			return new window.google.maps.Map(m.div, opt);
		}
		else if ( m.source == 'osm' ) {
			// Map
			m.map = window.L.map(m.div, opt);
			// Copyrights
			window.L.tileLayer(m.tileLayer, {
				attribution: 'Data &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors, <a href="http://www.openstreetmap.org/copyright">ODbL 1.0.</a>'
			}).addTo(m.map);
			// Scale Control
			window.L.control.scale().addTo(m.map);
			// Disable zoom with mouse wheel
			m.map.scrollWheelZoom.disable();
			return m.map;
		}
	},

	// Build a marker and add it to the map
	marker: function(m) {
		if ( !m.source || m.source == 'gmaps' ) {
			m.marker	= new window.google.maps.Marker({'position':m.markerPos, 'map':m.map, 'draggable':true});
			if (typeof window.mapl10n.icon != 'undefined') {
				m.marker.setIcon(window.mapl10n.icon);
			}
			return m.marker;
		}
		else if ( m.source == 'osm' ) {
			return window.L.marker(m.markerPos, {draggable: true}).addTo(m.map);
		}
	},

	// Left sub-panel: refresh the marker Lat/Lng values on move
	displayMarkerPosOnMove: function(m) {
		if ( !m.source || m.source == 'gmaps' ) {
			window.google.maps.event.addListener(m.marker,'position_changed',function(){
				m.markerPos = m.marker.getPosition();
				m.mlat.value = m.markerPos.lat();
				m.mlng.value = m.markerPos.lng();
			});
			return m;
		}
		else if ( m.source == 'osm' ) {
			m.marker.on('drag move',function() {
				m.markerPos = m.marker.getLatLng();
				m.mlat.value = m.markerPos.lat;
				m.mlng.value = m.markerPos.lng;
			});
			return m;
		}
	},

	// Left sub-panel: refresh the map Lat/Lng values on move (center)
	displayMapCenterOnMove: function(m) {
		if ( !m.source || m.source == 'gmaps' ) {
			window.google.maps.event.addListener(m.map,'center_changed',function(){
				m.center = m.map.getCenter();
				m.clat.value = m.center.lat();
				m.clng.value = m.center.lng();
			});
			return m;
		}
		else if ( m.source == 'osm' ) {
			m.map.on('move',function() {
				m.center = m.map.getCenter();
				m.clat.value = m.center.lat;
				m.clng.value = m.center.lng;
			});
			return m;
		}
	},

	// Left sub-panel: refresh the map zoom value on change
	displayZoomValueOnChange: function(m) {
		if ( !m.source || m.source == 'gmaps' ) {
			window.google.maps.event.addListener(m.map,'zoom_changed',function(){
				m.mzoom.value = m.map.getZoom();
			});
			return m;
		}
		else if ( m.source == 'osm' ) {
			m.map.on('zoomend',function() {
				m.mzoom.value = m.map.getZoom();
			});
			return m;
		}
	},

	// Refresh the map with new center, zoom, and marker position
	refreshMap: function(m) {
		if ( !m.source || m.source == 'gmaps' ) {
			m.map.panTo( m.center );
			m.map.setZoom( m.zoomval );
			m.marker.setPosition( m.markerPos );
			return m;
		}
		else if ( m.source == 'osm' ) {
			m.marker.setLatLng( m.markerPos );
			m.map.setView(m.center, m.zoomval);
			return m;
		}
	},

	// Display the map
	showMap: function(m) {
		m.latoval	= m.lato.value.length ? parseFloat( m.lato.value ) : 0;
		m.lngoval	= m.lngo.value.length ? parseFloat( m.lngo.value ) : 0;
		m.latval	= m.lat.value.length  ? parseFloat( m.lat.value )  : m.latoval;
		m.lngval	= m.lng.value.length  ? parseFloat( m.lng.value )  : m.lngoval;
		m.latcval	= m.latc.value.length ? parseFloat( m.latc.value ) : m.latval;
		m.lngcval	= m.lngc.value.length ? parseFloat( m.lngc.value ) : m.lngval;
		m.zoomval	= m.zoom.value.length ? parseInt( m.zoom.value, 10): 15;

		if ( !m.latval || !m.lngval )
			return false;

		m.center	= this.LatLng(m, m.latcval, m.lngcval);		// Center
		m.markerPos	= this.LatLng(m, m.latval, m.lngval);		// Marker

		if ( !m.map ) {
			jQuery(m.div)
				.css('height', function(){ return Math.floor( jQuery(m.coordsDiv).outerHeight(false) )+'px'; })
				.animate({'width': '50%', 'margin-right': '2%'}, 'normal', function(){
					// Map
					m.map = noopMap.map(m);
					// Add the marker
					m.marker = noopMap.marker(m);

					// Events
					noopMap.displayMarkerPosOnMove(m);

					noopMap.displayMapCenterOnMove(m);

					noopMap.displayZoomValueOnChange(m);
				});

			m.mlat.value = m.latval;
			m.mlng.value = m.lngval;
			m.clat.value = m.latcval;
			m.clng.value = m.lngcval;
			m.mzoom.value = m.zoomval;
			jQuery(m.infos).removeClass('hidden');
		} else {
			this.refreshMap(m);
		}
	}
};



jQuery(document).ready(function($){

	if ( window.maps && window.mapl10n ) {
		$.each( window.maps, function(i, m) {
			m.map		= false;
			m.marker	= false;
			// Boxes
			m.infos		= noopMap.getEl(m.prefix+'-map-infos');
			m.div		= noopMap.getEl(m.prefix+'-map');
			m.coordsDiv	= noopMap.getEl(m.prefix+'-map-coords');
			// Fields
			m.lato		= noopMap.getEl(m.prefix+'-lato');
			m.lngo		= noopMap.getEl(m.prefix+'-lngo');
			m.lat		= noopMap.getEl(m.prefix+'-lat');
			m.lng		= noopMap.getEl(m.prefix+'-lng');
			m.latc		= noopMap.getEl(m.prefix+'-latc');
			m.lngc		= noopMap.getEl(m.prefix+'-lngc');
			m.zoom		= noopMap.getEl(m.prefix+'-zoom');
			// Left infos panel
			m.mlat		= noopMap.getEl(m.prefix+'-marker-lat');
			m.mlng		= noopMap.getEl(m.prefix+'-marker-lng');
			m.clat		= noopMap.getEl(m.prefix+'-center-lat');
			m.clng		= noopMap.getEl(m.prefix+'-center-lng');
			m.mzoom		= noopMap.getEl(m.prefix+'-map-zoom');
			$(m.coordsDiv)
				.on('click', '.get-coords', function(e){
					noopMap.getCoords(m);
				}).on('click', '.show-map', function(e){
					noopMap.showMap(m);
				});
			$(m.infos)
				.on('click', '.marker-send', function(e){
					m.lat.value = m.mlat.value;
					m.lng.value = m.mlng.value;
				}).on('click', '.center-send', function(e){
					m.latc.value = m.clat.value;
					m.lngc.value = m.clng.value;
				}).on('click', '.zoom-send', function(e){
					m.zoom.value = m.mzoom.value;
				});
			if ( m.lato.value && m.lngo.value ) {
				$(noopMap.getEl(m.prefix+'-show-map')).removeClass('hidden').trigger('click');
			}
		});
	}

});