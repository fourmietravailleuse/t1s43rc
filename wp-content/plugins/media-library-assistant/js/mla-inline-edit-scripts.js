/* global ajaxurl */

var mla = {
	// Properties
	settings: {},

	// Utility functions
	utility: {
	},

	// Components
	setParent: null,
	inlineEditAttachment: null
};

( function( $ ) {
	/**
	 * Localized settings and strings
	 */
	mla.settings = typeof mla_inline_edit_vars === 'undefined' ? {} : mla_inline_edit_vars;
	mla_inline_edit_vars = void 0; // delete won't work on Globals

	// The inlineEditAttachment functions are adapted from wp-admin/js/inline-edit-post.js
	mla.inlineEditAttachment = {
		init : function(){
			var t = this, qeRow = $( '#inline-edit' ), bulkRow = $( '#bulk-edit' );

			t.type = 'attachment';
			t.what = '#attachment-';

			// prepare the edit rows
			qeRow.keyup(function(e){
				if (e.which == 27)
					return mla.inlineEditAttachment.revert();
			});
			bulkRow.keyup(function(e){
				if (e.which == 27)
					return mla.inlineEditAttachment.revert();
			});

			$('#inline-edit-post-set-parent', qeRow).on( 'click', function(){
				return mla.inlineEditAttachment.inlineParentOpen(this);
			});
			$('a.cancel', qeRow).click(function(){
				return mla.inlineEditAttachment.revert();
			});
			$('a.save', qeRow).click(function(){
				return mla.inlineEditAttachment.save(this);
			});
			$('td', qeRow).keydown(function(e){
				if ( e.which == 13 )
					return mla.inlineEditAttachment.save(this);
			});

			$('#bulk-edit-set-parent', bulkRow).on( 'click', function(){
				return mla.inlineEditAttachment.bulkParentOpen();
			});
			$('a.cancel', bulkRow).click(function(){
				return mla.inlineEditAttachment.revert();
			});

			// add event to the Quick Edit links
			$( '#the-list' ).on( 'click', 'a.editinline', function(){
				mla.inlineEditAttachment.edit(this);
				return false;
			});

			// hiearchical taxonomies expandable?
			$('span.catshow').click(function(){
				$(this).hide().next().show().parent().next().addClass("cat-hover");
			});

			$('span.cathide').click(function(){
				$(this).hide().prev().show().parent().next().removeClass("cat-hover");
			});

			$('select[name="_status"] option[value="future"]', bulkRow).remove();

			$('#doaction, #doaction2').click(function(e){
				var n = $(this).attr('id').substr(2);

				if ( $('select[name="'+n+'"]').val() == 'edit' ) {
					e.preventDefault();
					t.setBulk();
				} else if ( $('form#posts-filter tr.inline-editor').length > 0 ) {
					t.revert();
				}
			});

			// Filter button (dates, categories) in top nav bar
			$('#post-query-submit').mousedown(function(e){
				t.revert();
				$('select[name^="action"]').val('-1');
			});
		},

		toggle : function(el){
			var t = this;
			$(t.what+t.getId(el)).css('display') == 'none' ? t.revert() : t.edit(el);
		},

		setBulk : function(){
			var te = '', c = true;
			this.revert();

			$('#bulk-edit td').attr('colspan', $('.widefat:first thead th:visible').length);
			$('table.widefat tbody').prepend( $('#bulk-edit') );
			$('#bulk-edit').addClass('inline-editor').show();

			$('tbody th.check-column input[type="checkbox"]').each(function(i){
				if ( $(this).prop('checked') ) {
					c = false;
					var id = $(this).val(), theTitle;
					theTitle = $('#inline_'+id+' .post_title').text() || mla.settings.noTitle;
					te += '<div id="ttle'+id+'"><a id="_'+id+'" class="ntdelbutton" title="'+mla.settings.ntdelTitle+'">X</a>'+theTitle+'</div>';
				}
			});

			if ( c )
				return this.revert();

			$('#bulk-titles').html(te);
			$('#bulk-titles a').click(function(){
				var id = $(this).attr('id').substr(1);

				$('table.widefat input[value="' + id + '"]').prop('checked', false);
				$('#ttle'+id).remove();
			});

			//flat taxonomies
			$('textarea.mla_tags').each(function(){
				var taxname = $(this).attr('name').replace(']', '').replace('tax_input[', '');

				$(this).suggest( ajaxurl + '?action=ajax-tag-search&tax=' + taxname, { delay: 500, minchars: 2, multiple: true, multipleSep: mla.settings.comma + ' ' } );
			});

			$('html, body').animate( { scrollTop: 0 }, 'fast' );
		},

		edit : function(id) {
			var t = this, fields, editRow, rowData, fIndex;
			t.revert();

			if ( typeof(id) == 'object' )
				id = t.getId(id);

			fields = mla.settings.fields;

			// add the new blank row
			editRow = $('#inline-edit').clone(true);
			$('td', editRow).attr('colspan', $('.widefat:first thead th:visible').length);

			if ( $(t.what+id).hasClass('alternate') )
				$(editRow).addClass('alternate');
			$(t.what+id).hide().after(editRow);

			// populate the data
			rowData = $('#inline_'+id);
			if ( !$(':input[name="post_author"] option[value="' + $('.post_author', rowData).text() + '"]', editRow).val() ) {
				// author no longer has edit caps, so we need to add them to the list of authors
				$(':input[name="post_author"]', editRow).prepend('<option value="' + $('.post_author', rowData).text() + '">' + $('#' + t.type + '-' + id + ' .author').text() + '</option>');
			}

			if ( $(':input[name="post_author"] option', editRow).length == 1 ) {
				$('label.inline-edit-author', editRow).hide();
			}

			for ( fIndex = 0; fIndex < fields.length; fIndex++ ) {
				$(':input[name="' + fields[fIndex] + '"]', editRow).val( $('.'+fields[fIndex], rowData).text() );
			}

			if ( $('.image_alt', rowData).length == 0) {
				$('label.inline-edit-image-alt', editRow).hide();
			}

			// hierarchical taxonomies
			$('.mla_category', rowData).each(function(){
				var term_ids = $(this).text();

				if ( term_ids ) {
					taxname = $(this).attr('id').replace('_'+id, '');
					$('ul.'+taxname+'-checklist :checkbox', editRow).val(term_ids.split(','));
				}
			});

			//flat taxonomies
			$('.mla_tags', rowData).each(function(){
				var terms = $(this).text(),
					taxname = $(this).attr('id').replace('_' + id, ''),
					textarea = $('textarea.tax_input_' + taxname, editRow),
					comma = mla.settings.comma;

				if ( terms ) {
					if ( ',' !== comma )
						terms = terms.replace(/,/g, comma);
					textarea.val(terms);
				}

				textarea.suggest( ajaxurl + '?action=ajax-tag-search&tax=' + taxname, { delay: 500, minchars: 2, multiple: true, multipleSep: mla.settings.comma + ' ' } );
			});

			$(editRow).attr('id', 'edit-'+id).addClass('inline-editor').show();
			$('.ptitle', editRow).focus();

			return false;
		},

		save : function( id ) {
			var params, fields, page = $('.post_status_page').val() || '';

			if ( typeof(id) == 'object' )
				id = this.getId(id);

			$('table.widefat .inline-edit-save .waiting').show();

			params = {
				action: mla.settings.ajax_action,
				nonce: mla.settings.ajax_nonce,
				post_type: typenow,
				post_ID: id,
				edit_date: 'true',
				post_status: page
			};

			fields = $('#edit-' + id + ' :input').serialize();
			params = fields + '&' + $.param(params);

			// make ajax request
			$.post( ajaxurl, params,
				function( response ) {
					$( 'table.widefat .inline-edit-save .waiting' ).hide();

					if ( response ) {
						if ( -1 != response.indexOf( '<tr' ) ) {
							$( mla.inlineEditAttachment.what + id ).remove();
							$( '#edit-' + id ).before( response ).remove();
							$( mla.inlineEditAttachment.what + id ).hide().fadeIn();
						} else {
							response = response.replace( /<.[^<>]*?>/g, '' );
							$( '#edit-' + id + ' .inline-edit-save .error' ).html( response ).show();
						}
					} else {
						$( '#edit-' + id + ' .inline-edit-save .error' ).html( mla.settings.error ).show();
					}
				}
			, 'html');
			return false;
		},

		inlineParentOpen : function( id ) {
			var parentId, postId, postTitle;

			if ( typeof( id ) == 'object' ) {
				postId = this.getId( id );
				parentId = $( '#edit-' + postId + ' :input[name="post_parent"]' ).val() || '';
				postTitle = $( '#edit-' + postId + ' :input[name="post_title"]' ).val() || '';
				mla.setParent.open( parentId, postId, postTitle );
				/*
				 * Grab the "Update" button
				 */
				$( '#mla-set-parent-submit' ).on( 'click', function( event ){
					event.preventDefault();
					mla.inlineEditAttachment.inlineParentSave( postId );
					return false;
				});
			}
		},

		inlineParentSave : function( postId ) {
			var foundRow = $( '#mla-set-parent-response-div input:checked' ).closest( 'tr' ), parentId, parentTitle,
				editRow = $( '#edit-' + postId ), newParent, newTitle;

			if ( foundRow.length ) {
				parentId = $( ':radio', foundRow ).val() || '';
				parentTitle = $( 'label', foundRow ).html() || '';
				newParent = $(':input[name="post_parent"]', editRow).clone( true ).val( parentId );
				newTitle = $(':input[name="post_parent_title"]', editRow).clone( true ).val( parentTitle );
				$(':input[name="post_parent"]', editRow).replaceWith( newParent );
				$(':input[name="post_parent_title"]', editRow).replaceWith( newTitle );
			}

			mla.setParent.close();
			$('#mla-set-parent-submit' ).off( 'click' );
		},

		bulkParentOpen : function() {
			var parentId, postId, postTitle;

			postId = -1;
			postTitle = mla.settings.bulkTitle;
			parentId = $( '#bulk-edit :input[name="post_parent"]' ).val() || -1;
			mla.setParent.open( parentId, postId, postTitle );
			/*
			 * Grab the "Update" button
			 */
			$( '#mla-set-parent-submit' ).on( 'click', function( event ){
				event.preventDefault();
				mla.inlineEditAttachment.bulkParentSave();
				return false;
			});
		},

		bulkParentSave : function() {
			var foundRow = $( '#mla-set-parent-response-div input:checked' ).closest( 'tr' ), parentId, newParent;

			if ( foundRow.length ) {
				parentId = $( ':radio', foundRow ).val() || '';
				newParent = $('#bulk-edit :input[name="post_parent"]').clone( true ).val( parentId );
				$('#bulk-edit :input[name="post_parent"]').replaceWith( newParent );
			}

			mla.setParent.close();
			$('#mla-set-parent-submit' ).off( 'click' );
		},

		tableParentOpen : function( parentId, postId, postTitle ) {
			mla.setParent.open( parentId, postId, postTitle );
			/*
			 * Grab the "Update" button
			 */
			$( '#mla-set-parent-submit' ).on( 'click', function( event ){
				event.preventDefault();
				mla.inlineEditAttachment.tableParentSave( postId );
				return false;
			});
		},

		tableParentSave : function( postId ) {
			var foundRow = $( '#mla-set-parent-response-div input:checked' ).closest( 'tr' ),
				parentId = $( ':radio', foundRow ).val() || '-1',
				params, tableCell = $( '#attachment-' + postId + " td.attached_to" ).clone( true );

			if ( foundRow.length && ( parentId >= 0 ) ) {
				tableCell = $( '#attachment-' + postId + " td.attached_to" ).clone( true );
				tableCell.html( '<span class="spinner"></span>' );
				$( '#attachment-' + postId + " td.attached_to" ).replaceWith( tableCell );
				$( '#attachment-' + postId + " td.attached_to .spinner" ).show();

				params = $.param( {
					action: mla.settings.ajax_action + '-set-parent',
					nonce: mla.settings.ajax_nonce,
					post_ID: postId,
					post_parent: parentId,
				} );

				$.post( ajaxurl, params,
					function( response ) {
						var tableCell = $( '#attachment-' + postId + " td.attached_to" ).clone( true );

						if ( response ) {
							if ( -1 == response.indexOf( 'tableParentOpen(' ) ) {
								response = response.replace( /<.[^<>]*?>/g, '' );
							}

							tableCell.html( response );
						} else {
							tableCell.html( mla.settings.ajaxFailError );
						}

						$( '#attachment-' + postId + " td.attached_to" ).replaceWith( tableCell );
					}
				, 'html');
			} else {
				tableCell.html( mla.settings.error );
				$( '#attachment-' + postId + " td.attached_to" ).replaceWith( tableCell );
			}

			$('#mla-set-parent-submit' ).off( 'click' );
			mla.setParent.close();
		},

		revert : function(){
			var id = $('table.widefat tr.inline-editor').attr('id');

			if ( id ) {
				$('table.widefat .inline-edit-save .waiting').hide();

				if ( 'bulk-edit' == id ) {
					$('table.widefat #bulk-edit').removeClass('inline-editor').hide();
					$('#bulk-titles').html('');
					$('#inlineedit').append( $('#bulk-edit') );
				} else {
					$('#'+id).remove();
					id = id.substr( id.lastIndexOf('-') + 1 );
					$(this.what+id).show();
				}
			}

			return false;
		},

		getId : function(o) {
			var id = $(o).closest('tr').attr('id'),
				parts = id.split('-');
			return parts[parts.length - 1];
		}
	}; // mla.inlineEditAttachment

	$( document ).ready( function() {
		mla.inlineEditAttachment.init();
	});
})( jQuery );
