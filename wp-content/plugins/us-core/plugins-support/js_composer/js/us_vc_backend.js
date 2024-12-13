/**
 * Since WPBakery's customization options are limited, it implements
 * add-on functionality on side JS. This approach is not stable,
 * but there are no other solutions yet.
 */
jQuery( function( $, undefined ) {
	"use strict";

	// Check loaded Visual Composer
	if ( window.vc === undefined ) {
		return;
	}

	/**
	 * The handler is called every time the panel for adding elements is opened.
	 *
	 * Note: The implementation of this functionality is based on the inner workings
	 * of the script and has no public documentation or support.
	 *
	 * @deps /wp-content/plugins/js_composer/assets/js/dist/backend.min.js
	 */
	function showAddElementPanel() {
		var self = this,
			model = self.model,
			iteration = 0; // Current iteration
		// Get all parents relative to current tag
		var parents = [];
		while( !! model && iteration++ < /* number of max iterations */1000 ) {
			var tag = model.get( 'shortcode' );
			if ( ( vc.getMapped( tag ) || {} ).is_container ) {
				parents.push( tag );
			}
			model = vc.shortcodes.get( model.get( 'parent_id' ) ); // next parent model
		}
		// Hide all containers of the same type since nesting in itself is prohibited
		$( '[data-vc-ui-element][data-is-container=true]', self.$el ).each( function( _, node ) {
			var $node = $( node ),
				tag = $node.data( 'element' );
			// Check the nesting of elements `vc_tta_*`
			// if ( tag.indexOf( 'vc_tta_' ) > -1 && parents.join().indexOf( 'vc_tta_' ) > -1 ) {
			// 	$node.css( 'display', 'none' );
			// 	return;
			// }
			// Support for nesting of the second row through `vc_row_inner`
			if ( tag === 'vc_row' && parents.indexOf( tag ) > -1 ) {
				tag = 'vc_row_inner';
			}
			// Determine has same tag parent
			$node.css( 'display', parents.indexOf( tag ) > -1 ? 'none' : 'block' );
		} );
	}

	/**
	 * After initialization, subscribe to WPBakery events.
	 */
	vc.events.on( 'app.render', function() {
		vc.add_element_block_view.on( 'show', showAddElementPanel );

		// Copied the functionality from the 'attach_image' field type to 'us_upload'
		// for displaying a preview image for admin. Note: Important field name must be 'image';
		if ( vc.atts[ 'attach_image' ] ) {
			vc.atts[ 'us_upload' ] = _.clone( vc.atts[ 'attach_image' ] );
		}

	} );
} );
