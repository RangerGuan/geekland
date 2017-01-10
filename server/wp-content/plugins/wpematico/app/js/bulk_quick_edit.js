(function($) {

	// we create a copy of the WP inline edit post function
	var $wp_inline_edit = inlineEditPost.edit;
	
	// and then we overwrite the function with our own code
	inlineEditPost.edit = function( id ) {
	
		// "call" the original WP edit function
		// we don't want to leave WordPress hanging
		$wp_inline_edit.apply( this, arguments );
		
		// get the post ID
		var $post_id = 0;
		if ( typeof( id ) == 'object' )
			$post_id = parseInt( this.getId( id ) );
			
		if ( $post_id > 0 ) {
		
			// define the edit row
                    var $edit_row = $( '#edit-' + $post_id );
                    var $wc_inline_data = $('#inline_' + $post_id );

                    var $campaign_max     = $wc_inline_data.find('.campaign_max').text();
			$('input[name="campaign_max"]', '.inline-edit-row').val($campaign_max);

                    var $campaign_feeddate = $wc_inline_data.find('.campaign_feeddate').text();
			//$('input[name="campaign_feeddate"]', '.inline-edit-row').val($campaign_feeddate);
			if ($campaign_feeddate=='1') {  // checkbox
				$('input[name="campaign_feeddate"]', '.inline-edit-row').attr('checked', 'checked');
			} else {
				$('input[name="campaign_feeddate"]', '.inline-edit-row').removeAttr('checked');
			}
                    var $campaign_author = $wc_inline_data.find('.campaign_author').text();
			$edit_row.find( 'select[name="campaign_author"]' ).val( $campaign_author );
                    var $campaign_commentstatus = $wc_inline_data.find('.campaign_commentstatus').text();
			$edit_row.find( 'select[name="campaign_commentstatus"]' ).val( $campaign_commentstatus );
                    var $campaign_allowpings = $wc_inline_data.find('.campaign_allowpings').text();
			if ($campaign_allowpings=='1') {  // checkbox
				$('input[name="campaign_allowpings"]', '.inline-edit-row').attr('checked', 'checked');
			} else {
				$('input[name="campaign_allowpings"]', '.inline-edit-row').removeAttr('checked');
			}
                    var $campaign_linktosource = $wc_inline_data.find('.campaign_linktosource').text();
			if ($campaign_linktosource=='1') {  // checkbox
				$('input[name="campaign_linktosource"]', '.inline-edit-row').attr('checked', 'checked');
			} else {
				$('input[name="campaign_linktosource"]', '.inline-edit-row').removeAttr('checked');
			}
                    var $campaign_strip_links = $wc_inline_data.find('.campaign_strip_links').text();
			if ($campaign_strip_links=='1') {  // checkbox
				$('input[name="campaign_strip_links"]', '.inline-edit-row').attr('checked', 'checked');
			} else {
				$('input[name="campaign_strip_links"]', '.inline-edit-row').removeAttr('checked');
			}

			// get the campaign_posttype (posts_status)
                    var $campaign_posttype = $wc_inline_data.find('.campaign_posttype').text();
			$edit_row.find( 'input[name="campaign_posttype"][value="' + $campaign_posttype + '"]' ).prop( 'checked', true );

			// get the campaign_customposttype (posttype or custom posttype)
                    var $campaign_customposttype = $wc_inline_data.find('.campaign_customposttype').text();
			$edit_row.find( 'input[name="campaign_customposttype"][value="' + $campaign_customposttype + '"]' ).prop( 'checked', true );

                        // get the campaign_post_format (posts formats)
                    var $campaign_post_format = $wc_inline_data.find('.campaign_post_format').text();
			$edit_row.find( 'input[name="campaign_post_format"][value="' + $campaign_post_format + '"]' ).prop( 'checked', true );

                 	// hierarchical categories
                    var $campaign_categories = $wc_inline_data.find('.campaign_categories').text();
                        $('ul.category-checklist :checkbox').val($campaign_categories.split(','));
                        
                    var $campaign_tags = $wc_inline_data.find('.campaign_tags').text();
                        $edit_row.find( 'textarea[name="campaign_tags"]' ).text( $campaign_tags );


			// get the release date and set the release date
//			var $release_date = $( '#release_date-' + $post_id ).text();
//			$edit_row.find( 'input[name="release_date"]' ).val( $release_date );
			
			// get the film rating and set the film rating
//			var $film_rating = $( '#film_rating-' + $post_id ).text();
//			$edit_row.find( 'select[name="film_rating"]' ).val( $film_rating );
			
		}
		
	};
	
    
        
//        $( '#inline-edit' ).live( 'click', function() {
//		var $post_id = 0;
//		if ( typeof( id ) == 'object' )
//			$post_id = parseInt( this.getId( id ) );
//			
//		if ( $post_id > 0 ) {
//                    var $wc_inline_data = $('inline-edit-wpematico' + $post_id );
//                }
//        });
        
    $( '.submit.inline-edit-save .save' ).live( 'click', function() {
//		inlineEditPost.revert();
		var post_id = $(this).closest('tr').attr('id');

		post_id = post_id.replace("post-", "");

		var $wc_inline_data = $('#post-' + post_id);
                
                var $campaign_max = $wc_inline_data.find( 'input[name="campaign_max"]' ).val();

	});

        
    $( '#bulk_edit' ).live( 'click', function() {
		// define the bulk edit row
		var $bulk_row = $( '#bulk-edit' );
		
		// get the selected post ids that are being edited
		var $post_ids = new Array();
		$bulk_row.find( '#bulk-titles' ).children().each( function() {
			$post_ids.push( $( this ).attr( 'id' ).replace( /^(ttle)/i, '' ) );
		});
		
		// get the custom fields
		var $campaign_max = $bulk_row.find( 'input[name="campaign_max"]' ).val();
		var $campaign_author = $bulk_row.find( 'select[name="campaign_author"]' ).val();
		var $campaign_feeddate = $bulk_row.find( 'input[name="campaign_feeddate"]:checked' ).length;
		var $campaign_commentstatus = $bulk_row.find( 'select[name="campaign_commentstatus"]' ).val();
		var $campaign_allowpings = $bulk_row.find( 'input[name="campaign_allowpings"]:checked' ).length;
		var $campaign_linktosource = $bulk_row.find( 'input[name="campaign_linktosource"]:checked' ).length;
		var $campaign_strip_links = $bulk_row.find( 'input[name="campaign_strip_links"]:checked' ).length;
		var $post_category = $bulk_row.find('input[name="post_category[]"]:checked').map(function(){return $(this).val();}).get();
		
		// save the data
		$.ajax({
			url: ajaxurl, // this is a variable that WordPress has already defined for us
			type: 'POST',
			async: false,
			cache: false,
			data: {
				action: 'manage_wpematico_save_bulk_edit', // this is the name of our WP AJAX function that we'll set up next
				post_ids: $post_ids, // and these are the 2 parameters we're passing to our function
				campaign_max: $campaign_max,
				campaign_author: $campaign_author,
				campaign_feeddate: $campaign_feeddate,
				campaign_commentstatus: $campaign_commentstatus,
				campaign_allowpings: $campaign_allowpings,
				campaign_linktosource: $campaign_linktosource,
				campaign_strip_links: $campaign_strip_links,
				post_category: $post_category
			}
		});
		
	});
	
})(jQuery);