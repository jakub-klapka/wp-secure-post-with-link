( function( $ ){

	var AdminUi = {

		/**
		 * Holds added elements
		 */
		new_input: undefined,
		new_label: undefined,

		/**
		 * Constructor
		 */
		init: function () {

			//Bootstrap
			this.addVisibilitySettings();

			//Events
			$( '.save-post-visibility' ).on( 'click', $.proxy( this.okButtonHandler, this ) );

		},

		/**
		 * Add new checkbox in publish metabox
		 */
		addVisibilitySettings: function () {

			var private_label = $( '#post-visibility-select label[for="visibility-radio-private"]' );

			var new_input = $( "<input/>")
				.attr( 'name', 'visibility' )
				.attr( 'id', 'visibility-radio-secured' )
				.attr( 'value', 'secured' )
				.attr( 'type', 'radio' );

			var new_label = $( "<label/>" )
				.attr( 'for', 'visibility-radio-secured' )
				.attr( 'class', 'selectit' )
				.text( ' Skrytý odkaz' );

			private_label.after( new_label ).after( new_input ).after( $( "<br/>" ) );

			this.new_input = new_input[0];
			this.new_label = new_label[0];

		},

		/**
		 * Change text of visibility, when secured link is selected
		 */
		okButtonHandler: function() {

			if( this.new_input.checked ) {

				$( '#post-visibility-display' ).text( 'Skrytý odkaz' ); //TODO: translate

			}

		}

	};

	/*
	Init on DOM Ready
	 */
	$( function() {
		Object.create( AdminUi ).init();
	} );

})( jQuery );