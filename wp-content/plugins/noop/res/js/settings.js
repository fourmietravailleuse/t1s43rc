jQuery(document).ready(function($){

	// Get the page name (not in use anymore)
	//var pageName = window.adminpage ? window.adminpage.split('_').pop() : 'noop';

	// !Make sure wpActiveEditor exists if we don't have tinyMCE --------------------------------------------------------------------------------------------------------------------------------------------------------------
	if ( typeof(window.tinymce) != 'object' ) {
		$('#wpbody-content .wrap').on('click', '.wp-editor-wrap', function(e) {
			if ( this.id )
				window.wpActiveEditor = this.id.slice(3, -5);
		});
	}

	// !Metaboxes --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
	if ( typeof(window.postboxes) === 'object' && typeof(WPRemoveThumbnail) === 'undefined' ) {
		postboxes.add_postbox_toggles(pagenow);
	}

	// !"Fill" button (placeholder) ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
	var inputPlaceholderSupport = document.createElement('input');
	if ( 'placeholder' in inputPlaceholderSupport ) {
		$('.fill-placeholder-button').on('click', function(e) {
			e.preventDefault();
			var $input = $(this).addClass('hidden').prev('input');
			$input.val($input.attr('placeholder').replace(/\.{3,}$/, '').replace(/\u2026$/, '')).focus();
		}).prev('input').on('blur', function(e) {
			if ( this.value === '' ) {
				$(this).next('.fill-placeholder-button').removeClass('hidden');
			} else {
				$(this).next('.fill-placeholder-button').addClass('hidden');
			}
		}).blur();
	}

	// !Inputs with "auto-select" ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
	$('input.auto-select, textarea.auto-select').on('focus', function(e) {
		$(this).select();
	}).on('mouseup', function(e){
		e.preventDefault();
	});

	// !Color picker ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
	var $pickers = $('.color-picker-hex');
	if ( $pickers.length ) {
		// Color Picker bugfix
		$.wp.wpColorPicker.prototype.options.width = 255;
		$.wp.wpColorPicker.prototype._setOption = function(k, v) {
			this.options[ k ] = v;
			this.element.iris( "option", k, v );
		};
		$.wp.wpColorPicker.prototype._create = function() {
			// bail early for IE < 8
			if ( $.browser.msie && parseInt( $.browser.version, 10 ) < 8 )
				return;
			var _before = '<a tabindex="0" class="wp-color-result" />',
				_after = '<div class="wp-picker-holder" />',
				_wrap = '<div class="wp-picker-container" />',
				_button = '<input type="button" class="button button-small hidden" />';
			var self = this;
			var el = self.element;
			$.extend( self.options, el.data() );

			self.initialValue = el.val();

			// Set up HTML structure, hide things
			el.addClass( 'wp-color-picker' ).hide().wrap( _wrap );
			self.wrap = el.parent();
			self.toggler = $( _before ).insertBefore( el ).css( { backgroundColor: self.initialValue } ).attr( "title", window.wpColorPickerL10n.pick ).attr( "data-current", window.wpColorPickerL10n.current );
			self.pickerContainer = $( _after ).insertAfter( el );
			self.button = $( _button );

			if ( self.options.defaultColor )
				self.button.addClass( 'wp-picker-default' ).val( window.wpColorPickerL10n.defaultString );
			else
				self.button.addClass( 'wp-picker-clear' ).val( window.wpColorPickerL10n.clear );

			el.wrap('<span class="wp-picker-input-wrap" />').after(self.button);

			if ( !self.options.palettes || self.options.palettes === 'false' || self.options.palettes === '0' ) {
				self.options.palettes = false;
			} else if ( self.options.palettes === 'true' || self.options.palettes === '1' || self.options.palettes === 1 ) {
				self.options.palettes = true;
			} else {
				self.options.palettes = self.options.palettes.split(',');
			}

			el.iris( {
				target: self.pickerContainer,
				hide: true,
				width: self.options.width,
				mode: 'hsv',
				palettes: self.options.palettes,
				change: function( event, ui ) {
					self.toggler.css( { backgroundColor: ui.color.toString() } );
					// check for a custom cb
					if ( $.isFunction( self.options.change ) )
						self.options.change.call( this, event, ui );
				}
			} );
			el.val( self.initialValue );
			self._addListeners();
			if ( ! self.options.hide )
				self.toggler.click();
		};

		$pickers.siblings('.default-value').remove();
		$pickers.each(function(i,el){
			var $this = $(this),
				defaultColor	= $this.data('defaultColor'),
				width			= $this.data('width'),
				pickerArgs		= {hide: true, palettes: $this.data('palettes')};
			if ( defaultColor !== '' ) { pickerArgs.defaultColor = defaultColor; }
			if ( width !== '' && typeof width !== 'undefined' ) { pickerArgs.width = parseInt(width,10); }

			$this.wpColorPicker( pickerArgs );
		});
	}

	// !Date -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
	function padZero( v ) {
		v = parseInt(v,10);
		return v > 9 ? v : '0'+v;
	}

	$('.date-now').on('click', function(e) {
		e.preventDefault();
		var $bros = $(this).siblings('input, select'),
			now = new Date();
		$bros.filter('.jj').val(padZero(now.getDate()));
		$bros.filter('.mm').find('option[value="'+padZero(now.getMonth()+1)+'"]').attr('selected', 'selected').siblings().removeAttr('selected');
		$bros.filter('.aa').val(1900+now.getYear());
		$bros.filter('.hh').val(padZero(now.getHours()));
		$bros.filter('.mn').val(padZero(now.getMinutes()));
	});

	// !Uploading files --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
	if ( $('.upload-media-display').length ) {
		var file_frame			= {},
			no_preview_item		= '<li class="attachment no-attachment"><div class="attachment-preview"><span class="icon no-media-icon">&#160;</span></div></li>',
			get_preview_item	= function(attachment) {
				if ( attachment.type == 'image' ) {
					var size = attachment.sizes.medium ? attachment.sizes.medium : attachment.sizes.full;
					return '<li data-id="'+attachment.id+'" class="attachment media-attachment"><div class="attachment-preview type-'+attachment.type+' subtype-'+attachment.subtype+' '+size.orientation+'"><div class="thumbnail"><div class="centered"><img src="'+size.url+'" alt=""/></div></div><button title="'+window.NoopSettingsL10n.del+'" class="close media-modal-icon">&#160;</button></div></li>';
				} else
					return '<li data-id="'+attachment.id+'" class="attachment media-attachment"><div class="attachment-preview type-'+attachment.type+' subtype-'+attachment.subtype+' landscape"><img class="icon" src="'+attachment.icon+'" alt=""/><div class="filename"><div>'+attachment.filename+'</div></div><button title="'+window.NoopSettingsL10n.del+'" class="close media-modal-icon">&#160;</button></div></li>';
			};

		$('#wpbody-content .wrap').on('click', '.upload-media-button', function(e){
			e.preventDefault();

			var $this				= $(this),
				image_size			= $this.data('image_size'),										// If false, that means we have to return an ID, not an url
				multiple			= $this.data('multiple'),
				editor				= $this.data('editor');

			$this.blur();
			window.wpActiveEditor	= editor;

			// If the media frame already exists, reopen it.
			if ( file_frame[editor] ) {
				file_frame[editor].open();
				return;
			}

			// Create the media frame.
			file_frame[editor] = window.wp.media.frames.file_frame = window.wp.media({
				title: $this.text(),	//$this.data('uploader_title'),
				button: {
					text: $this.data('uploader_button_text')
				},
				library: {
					type: $this.data('media_mime')	// '', uploaded, image, audio, video
				},
				multiple: multiple
			});

			// When an image is selected, run a callback.
			file_frame[editor].on( 'select', function() {
				var attachments			= file_frame[editor].state().get('selection').toJSON(),
					ActiveEditor		= document.getElementById(editor),							// The current "editor"
					editorValue			= ActiveEditor.value,										// The input value
					$attachments_wrap	= $(ActiveEditor).siblings('.upload-media-display'),		// Will contain the previews/icons.
					$no_attachment		= $attachments_wrap.children('.no-attachment'),
					response			= [],
					previews			= [],
					separator			= image_size ? "\n" : ',';

				$.each(attachments, function(i, attachment) {
					if ( attachment.type == 'image' ) {
						// Store the data into an array
						if ( !image_size )
							response.push(attachment.id);
						else if ( typeof(attachment.sizes[image_size]) == 'object' )
							response.push(attachment.sizes[image_size].url);
						else
							response.push(attachment.url);
					} else {
						// Store the data into an array
						if ( !image_size )
							response.push(attachment.id);
						else
							response.push(attachment.url);
					}
					// Store the preview
					previews.push(get_preview_item(attachment));
				});

				// Insert data into the input
				response = response.join(separator);
				if ( editorValue && multiple )
					ActiveEditor.value += separator+response;
				else
					ActiveEditor.value = response;

				// Insert previews
				if ( multiple ) {
					$no_attachment.remove();
					$attachments_wrap.append(previews.join(''));
				} else {
					$attachments_wrap.html(previews.join(''));
				}
			});

			// Finally, open the modal
			file_frame[editor].open();
		})

		// Remove an item
		.on('click', '.upload-media-display .close', function(e){
			e.preventDefault();

			var $this		= $(this),
				$item		= $this.parents('.media-attachment').trigger('click'),		// trigger click to activate window.wpActiveEditor
				ActiveEditor= document.getElementById( window.wpActiveEditor );

			if ( !$item.siblings('.media-attachment').length ) {
				$item.replaceWith(no_preview_item);
				ActiveEditor.value = '';
			} else {
				var ids		= ActiveEditor.value.split(','),
					item_id	= $item.data('id'),
					idx		= ids.indexOf(""+item_id);

				if ( idx != -1 ) {
					ids.splice(idx, 1);
					ActiveEditor.value = ids.join(',');
					$item.remove();
				}
			}
		});

		// Sortable medias when "multiple" is active
		var updateEditorSortable = function() {
			var ids = [],
				$ActiveEditor = $(document.getElementById( window.wpActiveEditor )),
				$wrap = $ActiveEditor.siblings('.upload-media-display'),
				$items = $wrap.children('.media-attachment');
			if ( $items.length )
				$items.each(function(i,el) {
					ids.push($(this).data('id'));
				}).siblings('.no-attachment').remove();
			else
				$wrap.html(no_preview_item);
			$ActiveEditor.val(ids.join(','));
		};

		var $sortableMediaCont = $('.upload-media-display.ui-sortable');
		$sortableMediaCont.each(function(i,el) {
			$(this).sortable({
				items: '.media-attachment',
				cancel: '.media-modal-icon',
				revert: 200,
				connectWith: '.upload-media-display.ui-sortable',
				containment: ($sortableMediaCont.length > 1 ? false : 'parent'),
				update: function(e,ui) {
					if ( ui.sender !== null )
						ui.sender.parent('.wp-editor-wrap').trigger('click');					// trigger click to activate window.wpActiveEditor
					else
						ui.item.parents('.wp-editor-wrap').trigger('click');					// trigger click to activate window.wpActiveEditor
					updateEditorSortable();
				},
				receive: function(e,ui) {
					var receiverMime = ui.item.parent().siblings('.upload-media-button').data('media_mime'),
						$maybeCancel = ui.item.parents('.wp-editor-wrap');						// After a "cancel", ui.item change! O__O (wtf!)
					if ( receiverMime && !ui.item.children('.attachment-preview').hasClass('type-'+receiverMime) ) {
						ui.sender.sortable('cancel');
						$maybeCancel.trigger('click');											// trigger click to activate window.wpActiveEditor
						updateEditorSortable();
					}
				}
			});
		}).disableSelection();
	}	// eo Uploading files

	// !Show/Hide fields depending on other fields value ------------------------------------------------------------------------------------------------------------------------------------------------------------------------
	var $depends = $('.form-table').find('tr[class*="depends-"]');
	if ( $depends.length ) {
		var dependsFor = [],
			NoopInputHasValue = function( trClass ) {	// trClass = ['meta-name', 'meta-value']
				var $inps = $('.depfield-'+trClass[0]);
				if( $inps.first().attr('type') == 'radio' ) {
					return $inps.filter(':checked').val() == trClass[1];
				}
				else if ( $inps.first().attr('type') == 'checkbox' ) {
					var out = false;
					$inps.filter(':checked').each(function() {
						if ( $(this).val() == trClass[1] ) {
							out = true;
						}
					});
					return out;
				}
				else {
					return $inps.val() == trClass[1];
				}
			};

		// Get the inputs classes
		$depends.each(function() {
			var cls = $(this).attr('class').split(' ');
			$.each(cls, function(i,v) {
				if ( v.indexOf('depends-') != -1 ) {
					var depv = v.substr(8).split('___');	// ['meta-name', 'meta-value']
					if ( dependsFor.indexOf( '.depfield-' + depv[0] ) == -1 ) {
						dependsFor.push( '.depfield-' + depv[0] )
					}
				}
			});
		});

		// Show/hide rows on inputs change
		if ( dependsFor.length ) {
			$('.form-table').on('change', dependsFor.join(','), function(e) {
				var $this = $(this),
					cls = $this.attr('class').split(' '), cln, clv;
				$.each(cls, function(i,v) {
					if ( v.indexOf('depfield-') != -1 ) {
						cln = v.substr(9);
						return false;
					}
				});
				if ( cln ) {
					$('tr[class*="depends-'+cln+'___"]').each(function() {
						var trClasses = this.className.split(' '),
							NoopDepts = {},
							showRow = true;
						$.each(trClasses, function(i, trClass) {
							if ( trClass.indexOf('depends-') != -1 ) {
								trClass = trClass.substr(8).split('___');
								if ( !NoopDepts[trClass[0]] ) {
									NoopDepts[trClass[0]] = {};
								}
								NoopDepts[trClass[0]][trClass[1]] = NoopInputHasValue(trClass);
							}
						});
						$.each(NoopDepts, function(iName, iVals) {
							var showTemp = false;
							$.each(iVals, function(iVal, show) {
								if ( show ) {
									showTemp = true;
									return false;
								}
							});
							if ( !showTemp ) {
								showRow = false;
								return false;
							}
						});
						if ( showRow ) {
							$(this).removeClass('hide-if-js');
						}
						else {
							$(this).addClass('hide-if-js');
						}
					});
				}
			});
		}
	}

	// !Help pointers -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
	var $help_pointers = $('.help-pointer');
	if ( $help_pointers.length ) {
		$help_pointers.each(function(i,el){

			var $this = $(this);
			$(document.getElementById($this.data('target'))).pointer({
				content: '<h3>'+$this.data('title')+'</h3><p>'+$this.attr('title')+'</p>',
				position: {
					edge: 'top',
					align: 'left'
				}
			});
			$this.attr('title', window.NoopSettingsL10n.help);

		}).on('click', function(e) {

			$(document.getElementById($(this).data('target'))).pointer('toggle');
			e.preventDefault();

		});
	}

	// !Ajax pointers -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
	if ( window.NoopAjaxPointers ) {
		$.each(NoopAjaxPointers, function(i,v){
			if ( window[v] ) {
				v = window[v];
				$(v.target).pointer({
					content: v.content,
					position: {
						edge: v.edge,
						align: v.align
					},
					close: function() {
						$.post( ajaxurl, {
							pointer: v.pointer_id,
							action: 'dismiss-wp-pointer'
						} );
					}
				}).pointer('open');
			}
		});
	}

});