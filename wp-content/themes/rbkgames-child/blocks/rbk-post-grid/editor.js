( function ( wp ) {
	if ( ! wp || ! wp.blocks || ! wp.element ) { return; }
	var name = 'acf/rbk-post-grid';
	var fields = [{"k":"section_title","l":"Section title","t":"text","d":""},{"k":"title_link","l":"Title link","t":"text","d":""},{"k":"layout","l":"Layout","t":"select","d":"cards","o":[{"label":"Article cards","value":"cards"},{"label":"Game posters","value":"games"}]},{"k":"category","l":"Category ID (posts only)","t":"text","d":""},{"k":"count","l":"Number of posts","t":"number","d":9},{"k":"excerpt_length","l":"Excerpt words","t":"number","d":45},{"k":"show_date","l":"Show date","t":"toggle","d":1},{"k":"button_text","l":"Button text","t":"text","d":""},{"k":"button_url","l":"Button URL","t":"text","d":""},{"k":"section_class","l":"Section CSS class","t":"text","d":"rbk-articles"}];
	var el = wp.element.createElement;
	function register() {
		if ( wp.blocks.unregisterBlockType && wp.blocks.getBlockType( name ) ) { try { wp.blocks.unregisterBlockType( name ); } catch ( e ) {} }
		wp.blocks.registerBlockType( name, {
			apiVersion: 3,
			title: 'RBK Post grid',
			category: 'design',
			attributes: { name: { type: 'string' }, data: { type: 'object', default: {} }, mode: { type: 'string' } },
			edit: function ( props ) {
				var data = props.attributes.data || {};
				var C = wp.components;
				function set( k, v ) { var d = Object.assign( {}, data ); d[ k ] = v; props.setAttributes( { data: d } ); }
				var controls = fields.map( function ( f ) {
					var val = data[ f.k ] !== undefined ? data[ f.k ] : f.d;
					if ( f.t === 'toggle' ) { return el( C.ToggleControl, { key: f.k, label: f.l, checked: !! val, onChange: function ( v ) { set( f.k, v ? 1 : 0 ); } } ); }
					if ( f.t === 'select' ) { return el( C.SelectControl, { key: f.k, label: f.l, value: val, options: f.o, onChange: function ( v ) { set( f.k, v ); } } ); }
					return el( C.TextControl, { key: f.k, label: f.l, type: f.t === 'number' ? 'number' : 'text', value: val === undefined ? '' : val, onChange: function ( v ) { set( f.k, f.t === 'number' ? parseInt( v, 10 ) || 0 : v ); } } );
				} );
				return el( wp.element.Fragment, null,
					el( wp.blockEditor.InspectorControls, null, el( C.PanelBody, { title: 'Block settings', initialOpen: true }, controls ) ),
					el( wp.serverSideRender, { block: name, attributes: { name: name, data: data, mode: 'preview' } } )
				);
			},
			save: function () { return null; }
		} );
	}
	wp.domReady( function () {
		if ( window.rbkChildNativeBlocks ) { register(); return; }
		// ACF Pro supplies its own editor UI; only register if nothing exists after a short wait.
		setTimeout( function () { if ( ! wp.blocks.getBlockType( name ) ) { register(); } }, 1500 );
	} );
} )( window.wp );