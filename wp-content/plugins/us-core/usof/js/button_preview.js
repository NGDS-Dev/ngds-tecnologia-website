/**
 * USOF Button Preview
 */
;! function( $ ) {
	var _window = window,
		_document = document;

	if ( _window.$usof === undefined ) {
		return;
	}

	$usof.ButtonPreview = function( container ) {
		this.init( container );
	};
	$usof.ButtonPreview.prototype = {
		init: function( container ) {

			// Elements
			this.$container = $( container );
			this.$btn = this.$container.find( '.usof-btn' );
			this.$groupParams = this.$container.closest( '.usof-form-group-item' );
			this.$style = $( 'style:first', this.$groupParams );

			// Variables
			this.groupParams = this.$groupParams.data( 'usofGroupParams' );
			this.dependsOn = [
				'h1_font_family',
				'h2_font_family',
				'h3_font_family',
				'h4_font_family',
				'h5_font_family',
				'h6_font_family',
				'body_font_family',
			];

			// Apply style to button preview on dependant fields change
			for ( var fieldId in $usof.instance.fields ) {
				if ( ! $usof.instance.fields.hasOwnProperty( fieldId ) ) {
					continue;
				}
				if ( $.inArray( $usof.instance.fields[ fieldId ].name, this.dependsOn ) === - 1 ) {
					continue;
				}
				$usof.instance.fields[ fieldId ]
					.on( 'change', this.applyStyle.bind( this ) );
			}
			// Apply style to button preview on button's group params change
			for ( var fieldId in this.groupParams.fields ) {
				if ( ! this.groupParams.fields.hasOwnProperty( fieldId ) ) {
					continue;
				}
				this.groupParams.fields[ fieldId ]
					.on( 'change', this.applyStyle.bind( this ) );
			}

			// Apply style to button preview on the init
			this.applyStyle();
		},
		/**
		 * Get the color value.
		 * @param {String} key
		 * @return string.
		 */
		_getColorValue: function( key ) {
			if (
				this.groupParams instanceof $usof.GroupParams
				&& this.groupParams.fields[ key ] !== undefined
				&& this.groupParams.fields[ key ].type === 'color'
				&& this.groupParams.fields[ key ].hasOwnProperty( 'getColor' )
			) {
				return this.groupParams.fields[ key ].getColor();
			}
			return '';
		},
		/**
		 * Apply styles for form elements a preview
		 */
		applyStyle: function() {
			var self = this,
				classRandomPart = $ush.uniqid(), // // get unique class
				className = '.usof-btn_' + classRandomPart,
				style = {
					default: '',
					hover: '',
				};
			self.$btn.usMod( 'usof-btn', classRandomPart );

			// Font family
			var buttonFont = self.groupParams.getValue( 'font' ),
				typographyOptions = $usof.getData('typographyOptions') || {},
				fontFamily;

			if ( $.inArray( buttonFont, Object.keys( typographyOptions ) ) !== - 1 ) {
				fontFamily = ( typographyOptions[ buttonFont ] || {} )['font-family'] || ( ( typographyOptions[ buttonFont ] || {} ).default || {} )['font-family'] || '';
			} else {
				fontFamily = buttonFont;
			}
			if ( fontFamily !== 'none' && fontFamily !== '' && fontFamily !== 'null' ) {
				style.default += 'font-family: ' + fontFamily + '!important;';
			}

			// Text style
			if ( self.groupParams.getValue( 'text_style' ).indexOf( 'italic' ) !== - 1 ) {
				style.default += 'font-style: italic !important;';
			} else {
				style.default += 'font-style: normal !important;';
			}

			if ( self.groupParams.getValue( 'text_style' ).indexOf( 'uppercase' ) !== - 1 ) {
				style.default += 'text-transform: uppercase !important;';
			} else {
				style.default += 'text-transform: none !important;';
			}

			// Font size
			// Used min() for correct appearance of the button with huge font-size value, e.g. 20em
			style.default += 'font-size: min(' + self.groupParams.getValue( 'font_size' ) + ', 50px) !important;';

			// Line height
			style.default += 'line-height:' + self.groupParams.getValue( 'line_height' ) + ' !important;';

			// Font weight
			style.default += 'font-weight:' + self.groupParams.getValue( 'font_weight' ) + ' !important;';

			// Height & Width
			style.default += 'padding:' + self.groupParams.getValue( 'height' ) + ' ' + self.groupParams.getValue( 'width' ) + ' !important;';

			// Corners radius
			style.default += 'border-radius:' + self.groupParams.getValue( 'border_radius' ) + ' !important;';

			// Letter spacing
			style.default += 'letter-spacing:' + self.groupParams.getValue( 'letter_spacing' ) + ' !important;';

			// Colors
			var colorBg = self._getColorValue( 'color_bg' ),
				colorBorder = self._getColorValue( 'color_border' ),
				colorBgHover = self._getColorValue( 'color_bg_hover' ),
				colorBorderHover = self._getColorValue( 'color_border_hover' ),
				color;

			// Set default values if colors are empty
			if ( colorBg == '' ) {
				colorBg = 'transparent';
			}
			if ( colorBorder == '' ) {
				colorBorder = 'transparent';
			}
			if ( colorBgHover == '' ) {
				colorBgHover = 'transparent';
			}
			if ( colorBorderHover == '' ) {
				colorBorderHover = 'transparent';
			}

			style.default += 'background:' + colorBg + ' !important;';
			if ( colorBorder.indexOf( 'gradient' ) !== - 1 ) {
				style.default += 'border-image:' + colorBorder + ' 1 !important;';
			} else {
				style.default += 'border-color:' + colorBorder + ' !important;';
			}

			if ( self._getColorValue( 'color_text' ).indexOf( 'gradient' ) !== - 1 ) {
				color = $.usof_colorPicker.gradientParser( self._getColorValue( 'color_text' ) ).hex;
				style.default += 'color:' + color + ' !important;';
			} else {
				self.$btn.css( 'color', self._getColorValue( 'color_text' ) );
			}

			// Shadow
			if ( self._getColorValue( 'color_shadow' ) != '' ) {
				style.default += 'box-shadow:'
					+ self.groupParams.getValue( 'shadow_offset_h' ) + ' '
					+ self.groupParams.getValue( 'shadow_offset_v' ) + ' '
					+ self.groupParams.getValue( 'shadow_blur' ) + ' '
					+ self.groupParams.getValue( 'shadow_spread' ) + ' '
					+ self._getColorValue( 'color_shadow' ) + ' ';
				if ( $.inArray( '1', self.groupParams.getValue( 'shadow_inset' ) ) !== - 1 ) {
					style.default += 'inset';
				}
				style.default += '!important;';
			}

			// Hover class
			self.$container.usMod( 'hov', self.groupParams.getValue( 'hover' ) );

			// Background;
			if ( self.groupParams.getValue( 'hover' ) == 'fade' ) {
				style.hover += 'background:' + colorBgHover + ' !important;';
			} else if ( colorBgHover == 'transparent' ) {
				style.hover += 'background:' + colorBgHover + ' !important;';
			}

			// Shadow
			if ( self._getColorValue( 'color_shadow_hover' ) != '' ) {
				style.hover += 'box-shadow:'
					+ self.groupParams.getValue( 'shadow_hover_offset_h' ) + ' '
					+ self.groupParams.getValue( 'shadow_hover_offset_v' ) + ' '
					+ self.groupParams.getValue( 'shadow_hover_blur' ) + ' '
					+ self.groupParams.getValue( 'shadow_hover_spread' ) + ' '
					+ self._getColorValue( 'color_shadow_hover' ) + ' ';
				if ( $.inArray( '1', self.groupParams.getValue( 'shadow_hover_inset' ) ) !== - 1 ) {
					style.hover += 'inset';
				}
				style.hover += '!important;';
			}

			// Border color
			if ( colorBorderHover.indexOf( 'gradient' ) !== - 1 ) {
				style.hover += 'border-image:' + colorBorderHover + ' 1 !important;';
			} else {
				style.hover += 'border-color:' + colorBorderHover + ' !important;';
			}

			// Text color
			var colorHover;
			if ( self._getColorValue( 'color_text_hover' ).indexOf( 'gradient' ) !== - 1 ) {
				colorHover = ( $.usof_colorPicker.gradientParser( self._getColorValue( 'color_text_hover' ) ) ).hex;
			} else {
				colorHover = self._getColorValue( 'color_text_hover' );
			}
			style.hover += 'color:' + colorHover + ' !important;';

			var compiledStyle = className + '{%s}'.replace( '%s', style.default );

			// Border Width
			compiledStyle += className + ':before {border-width:' + self.groupParams.getValue( 'border_width' ) + ' !important;}';
			compiledStyle += className + ':hover{%s}'.replace( '%s', style.hover );

			// Extra layer for "Slide" hover type OR for gradient backgrounds (cause gradients don't support transition)
			if (
				self.groupParams.getValue( 'hover' ) == 'slide'
				|| (
					colorBorder.indexOf( 'gradient' ) !== - 1
					|| colorBgHover.indexOf( 'gradient' ) !== - 1
				)
			) {
				compiledStyle += className + ':after {background:' + colorBgHover + '!important;}';
			}

			self.$style.text( compiledStyle );
		}
	};
}( jQuery );
