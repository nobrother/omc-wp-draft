var apps = apps || {};

(function($, window, doc, bb, _, apps, info, media){
	
	var Attachments = media.model.Attachments,
			Query = media.model.Query,
			original = {};
	
	// Allow custom query
	_.extend(Query.prototype, {
		initialize: function( models, options ) {
			var allowed;

			options = options || {};
			Attachments.prototype.initialize.apply( this, arguments );

			this.args     = options.args;
			this._hasMore = true;
			this.created  = new Date();

			this.filters.order = function( attachment ) {
				var orderby = this.props.get('orderby'),
						order = this.props.get('order');

				if ( ! this.comparator ) {
					return true;
				}

				// We want any items that can be placed before the last
				// item in the set. If we add any items after the last
				// item, then we can't guarantee the set is complete.
				if ( this.length ) {
					return 1 !== this.comparator( attachment, this.last(), { ties: true });

					// Handle the case where there are no items yet and
					// we're sorting for recent items. In that case, we want
					// changes that occurred after we created the query.
				} else if ( 'DESC' === order && ( 'date' === orderby || 'modified' === orderby ) ) {
					return attachment.get( orderby ) >= this.created;

					// If we're sorting by menu order and we have no items,
					// accept any items that have the default menu order (0).
				} else if ( 'ASC' === order && 'menuOrder' === orderby ) {
					return attachment.get( orderby ) === 0;
				}

				// Otherwise, we don't want any items yet.
				return false;
			};

			// Observe the central `wp.Uploader.queue` collection to watch for
			// new matches for the query.
			//
			// Only observe when a limited number of query args are set. There
			// are no filters for other properties, so observing will result in
			// false positives in those queries.
			allowed = info.wpMedia && info.wpMedia.allowedQueryArgs ||
				[ 's', 'order', 'orderby', 'posts_per_page', 'post_mime_type', 'post_parent', 'author', 'tax_query' ];
			if ( wp.Uploader && _( this.args ).chain().keys().difference( allowed ).isEmpty().value() ) {
				this.observe( wp.Uploader.queue );
			}
		}
	});
	
})(jQuery, window, document, Backbone, _, apps, info, wp.media);