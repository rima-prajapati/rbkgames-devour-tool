( function ( wp ) {
	if ( ! wp || ! wp.blocks || ! wp.element ) { return; }
	var name = 'acf/rbk-featured';
	var fields = [{"k":"heading","l":"Main heading (H1)","t":"text","d":"Онлайн игры и новости из мира игр"},{"k":"section_title","l":"Section title","t":"text","d":"Выбор редакции"},{"k":"count","l":"Number of posts","t":"number","d":5},{"k":"excerpt_length","l":"Excerpt words","t":"number","d":25}];
	var el = wp.element.createElement;
	function register() {
		if ( wp.blocks.unregisterBlockType && wp.blocks.getBlockType( name ) ) { try { wp.blocks.unregisterBlockType( name ); } catch ( e ) {} }
		wp.blocks.registerBlockType( name, {
			apiVersion: 3,
			title: 'RBK Editor\'s choice',
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