
( function( $, window, document, undefined ) {
	"use strict";

	var toggleChildSettings = function( $parent, $children ) {
		var show = false;
		var toggleVal = $parent.data( 'toggleVal' );

		if ( 'radio' === $parent.attr( 'type' ) ) {
			show = $parent.prop( 'checked' ) && toggleVal == $parent.val();
		} else if ( 'checkbox' === $parent.attr( 'type' ) ) {
			if ( typeof toggleVal === 'undefined' || 1 == toggleVal ) {
				show = $parent.prop( 'checked' );
			} else {
				show = !$parent.prop( 'checked' );
			}
		} else {
			show = ( toggleVal == $parent.val() );
		}

		$children.toggle( show );
	};

	var toggleConditionalSettings = function() {
		$( '[data-show-if]' ).each( function() {
			var $field = $( this ).closest( 'tr' );
			var showIfField = $( this ).data( 'show-if' );
			var showIfValue = $( this ).data( 'show-if-value' );
			
			if ( showIfField && showIfValue ) {
				var $parent = $( 'select[name*="[' + showIfField + ']"]' );
				
				if ( $parent.length ) {
					var currentValue = $parent.val();
					var allowedValues = showIfValue.toString().split( ',' );
					
					if ( allowedValues.indexOf( currentValue ) !== -1 ) {
						$field.show();
					} else {
						$field.hide();
					}
				}
			}
		} );
	};

	$( document ).ready( function() {
		$( '.form-table .toggle-parent' ).each( function() {
			var $parent = $( this );
			var $children = $parent.closest( '.form-table' ).find( '.' + $parent.data( 'childClass' ) ).closest( 'tr' );

			toggleChildSettings( $parent, $children );

			$parent.on( 'change', function() {
				toggleChildSettings( $parent, $children );
			} );
		} );

		// Handle conditional show/hide for link_icon based on link_style
		toggleConditionalSettings();
		
		$( 'select[name*="[link_style]"]' ).on( 'change', function() {
			toggleConditionalSettings();
		} );

	} );

} )( jQuery, window, document );

