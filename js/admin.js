(function($) {

	/**
	 * Document ready.
	 */
	$(function() {
		var $sortable = $('.sortable tbody');
		$sortable.sortable({
			helper: sortHelper
		});
		// $sortable.disableSelection();

		$sortable.on('sortupdate', function(event) {
			numbering();
			update_order();
		})

		$(document).on('click', '.delete_featured_post', function(event) {
			event.preventDefault();
			var postId = $(this).attr('rel');
			$('.row-' + postId).remove();

			numbering();
			update_order();
		});

		function sortHelper( e, tr ) {
			var $originals = tr.children();
			var $row = tr.clone();
			$row.children().each( function( index ) {
				var $td = $originals.eq( index );
				$( this ).width( $td.width() );

			}); // each

			var bgColor = tr.css( 'background-color' );
			$row.css( { 'background-color': bgColor } );

			return $row;
		}

		function numbering() {
			$('.order', $sortable).each(function(i) {
				i = i + 1;
				$(this).text(i);
			});
		}

		function update_order() {
			var posts = $sortable.sortable('toArray', {attribute: 'data-post-id'});
			$( '#laffp_order' ).val( posts );
		}

	}); // Document ready

})(jQuery);
