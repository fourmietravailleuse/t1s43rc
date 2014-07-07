var noopFindPosts;
( function( $ ){
	noopFindPosts = {
		open: function(af_name, af_val, id, multiple, title) {
			var st = document.documentElement.scrollTop || $(document).scrollTop(),
				overlay = $( '.ui-find-overlay' ),
				$affected = $('#noop-affected');

			if ( overlay.length === 0 ) {
				$( 'body' ).append( '<div class="ui-find-overlay"></div>' );
				noopFindPosts.overlay();
			}

			overlay.show();

			if ( af_name && af_val ) {
				$affected.attr('name', af_name).val(af_val);
			}
			if ( typeof id != 'undefined' ) {
				$affected.data('affected', id.slice(0, -7));
			}
			if ( typeof multiple != 'undefined' ) {
				$affected.data('multiple', multiple);
			}

			var boxHead = $('#noop-find-posts-head');
			boxHead.data('title', boxHead.text()).text(title);

			$('#noop-find-posts').show();

			if ( ! noopAttachMediaBoxL10n.is39 && typeof $.fn.draggable == 'function' ) {
				$('#noop-find-posts').draggable({
					handle: '#noop-find-posts-head'
				}).css({'top':st + 50 + 'px','left':'50%','marginLeft':'-328px'});
			}

			$('#noop-find-posts').find('#find-posts-input').focus().on('keyup', function(e){
				if (e.which == 27) { this.close(); } // close on Escape
			});

			// Pull some results up by default
			this.send();

			return false;
		},

		close: function() {
			$('#noop-find-posts').hide().find('#find-posts-response').html('');
			if ( ! noopAttachMediaBoxL10n.is39 && typeof $.fn.draggable == 'function' ) {
				$('#noop-find-posts').draggable('destroy');
			}
			$( '.ui-find-overlay' ).hide();
			var boxHead = $('#noop-find-posts-head'),
				boxTitle = boxHead.data('title');
			if ( boxTitle ) {
				boxHead.text(boxTitle);
				boxHead.data('title', null);
			}
		},

		overlay: function() {
			if ( ! noopAttachMediaBoxL10n.is39 ) {
				$( '.ui-find-overlay' ).css( { 'z-index': '999', 'width': $( document ).width() + 'px', 'height': $( document ).height() + 'px' } );
			}
			$( '.ui-find-overlay' ).on('click', function () {
				noopFindPosts.close();
			});
		},

		send: function() {

			var $affected = $('#noop-affected'),
				post = {
					ps: $('#noop-find-posts').find('#find-posts-input').val(),
					action: 'noop_find_posts',
					_ajax_nonce: $('#noop-find-posts-nonce').val(),
					what: $affected.attr('name'),
					type: $affected.val(),
					multiple: $affected.data('multiple')
				},
				spinner = $( '.find-box-search .spinner' );

			spinner.show();

			if ( noopAttachMediaBoxL10n.is39 ) {
				$.ajax( ajaxurl, {
					type: 'POST',
					data: post,
					dataType: 'json'
				}).always( function() {
					spinner.hide();
				}).done( function( x ) {
					if ( ! x.success ) {
						$('#noop-find-posts').find( '#find-posts-response' ).text( noopAttachMediaBoxL10n.error );
					}
					$('#noop-find-posts').find( '#find-posts-response' ).html( x.data );
				}).fail( function() {
					$('#noop-find-posts').find( '#find-posts-response' ).text( noopAttachMediaBoxL10n.error );
				});
			}
			else {
				$.ajax({
					type : 'POST',
					url : window.ajaxurl,
					data : post,
					success : function(x) { noopFindPosts.show(x); spinner.hide(); },
					error : function(r) { noopFindPosts.error(r); spinner.hide(); }
				});
			}
		},

		show : function(x) {

			if ( typeof(x) == 'string' ) {
				this.error({'responseText': x});
				return;
			}

			var r = wpAjax.parseAjaxResponse(x);

			if ( r.errors ) {
				this.error({'responseText': wpAjax.broken});
			}
			r = r.responses[0];
			$('#find-posts-response').html(r.data);

			// Enable whole row to be clicked
			$( '.found-posts td' ).on( 'click', function () {
				$( this ).parent().find( '.found-radio input, .found-checkbox input' ).prop( 'checked', true );
			});
		},

		error : function(r) {
			var er = r.statusText;

			if ( r.responseText ) {
				er = r.responseText.replace( /<.[^<>]*?>/g, '' );
			}
			if ( er ) {
				$('#find-posts-response').html(er);
			}
		}
	};

	noopAttachMediaBoxL10n.is39 = parseInt( noopAttachMediaBoxL10n.is39, 10 );

	$( document ).ready( function() {

		var $findPostsButtons = $('.find-post-or-user');

		if ( $findPostsButtons.length ) {

			// Open the window
			$findPostsButtons.on('click', function(e){
				e.preventDefault();
				var $this = $(this);

				noopFindPosts.open($this.data('what'), $this.data('type'), $this.attr('id'), $this.data('multiple'), $this.text(), true);
			});

			// Send values
			$('#noop-find-posts-submit').on('click', function(e) {
				var $response_html = $('#noop-find-posts').find('#find-posts-response'),
					$affected = $('#noop-affected'),
					affected = $affected.data('affected'),
					multiple = $affected.data('multiple'),
					affected_input = document.getElementById(affected),
					insert = [],
					ids = [];

				$response_html.find('.found-radio :checked, .found-checkbox :checked').each(function(i,el){
					var $this = $(this),
						id = $this.val(),
						image = $this.data('image');
					if ( image && image.length ) {
						image = image.split('|');
						insert.push('<li data-id="'+id+'" class="found-item attachment media-attachment"><div class="attachment-preview type-image subtype-'+image[2]+' '+image[1]+'"><div class="thumbnail"><div class="centered"><img src="'+image[0]+'" alt=""/></div></div><div class="filename"><div>'+$this.parent().next().text()+'</div></div><button title="'+window.NoopSettingsL10n.del+'" class="close media-modal-icon">&#160;</button></div></li>');
					}
					else if ( multiple )
						insert.push('<tr class="found-item" data-id="'+id+'"><td class="tagchecklist hide-if-no-js"><span><a class="ntdelbutton">X</a></span></td><td>'+$this.parent().next().text()+'</td><td>'+$this.parent().next().next().text()+'</td><td>'+id+'</td></tr>');
					else
						insert.push('<span class="found-item" data-id="'+id+'"><a class="ntdelbutton hide-if-no-js">X</a> '+$this.parent().next().children().text()+'</span>');
					ids.push(id);
				});

				if ( ids.length ) {
					ids = ids.join(',');
					insert = insert.join(' ');
					if ( !multiple || !affected_input.value ) {
						$('#'+affected+'-response').html(insert);
						affected_input.value = ids;
					} else {
						$('#'+affected+'-response').append(' '+insert);
						affected_input.value += ','+ids;
					}
				}

				if ( ! multiple ) {
					noopFindPosts.close();
				}
			});

			// Search
			$( '#noop-find-posts .find-box-search :input' ).off('keypress').on('keypress', function( event ) {
				if ( 13 == event.which ) {
					noopFindPosts.send();
					return false;
				}
			} );
			$('#noop-find-posts').find( '#find-posts-search' ).off('click').on( 'click', noopFindPosts.send );

			// Close button
			$('#noop-find-posts').find( '#find-posts-close' ).off('click').on( 'click', noopFindPosts.close );

			// Remove items
			$('.find-item-response').on('click', '.ntdelbutton', function(e){
				var $this = $(this),
					$item = $this.parents('.found-item'),
					id = $item.data('id'),
					$input = $item.parents('.find-item-container').siblings('.response-input'),
					input_val = $input.val().split(','),
					idx = input_val.indexOf(""+id);

				if ( idx != -1 ) {
					input_val.splice(idx, 1);
					$input.val(input_val.join(','));
				}

				$item.remove();
			});

			// Checkboxes - Enable whole row to be clicked
			$('#noop-find-posts').find('#find-posts-response').on('click', '.found-posts td', function(e) {
				$(this).parent().find( '.found-checkbox input' ).trigger( 'click' );
			}).on('click', '.found-checkbox input, .found-checkbox + td label', function(e) {
				e.stopPropagation();
			});

		}

	});
})( jQuery );