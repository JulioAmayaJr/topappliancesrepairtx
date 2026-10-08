/**
 * Generic editor UI for Top Appliance dynamic blocks: live server preview + inspector
 * controls generated from each block.json "attributes". No build step, no UI libraries.
 */
( function ( wp ) {
	const { registerBlockType } = wp.blocks;
	const { createElement: el, Fragment } = wp.element;
	const { InspectorControls, useBlockProps } = wp.blockEditor;
	const { PanelBody, TextControl, ToggleControl, SelectControl } = wp.components;
	const ServerSideRender = wp.serverSideRender;

	function controlFor( name, def, props ) {
		const value = props.attributes[ name ];
		const set = ( v ) => props.setAttributes( { [ name ]: v } );
		const label = def.label || name.replace( /([A-Z])/g, ' $1' ).replace( /^./, ( c ) => c.toUpperCase() );
		if ( def.enum ) {
			return el( SelectControl, { key: name, label, value, options: def.enum.map( ( v ) => ( { label: v, value: v } ) ), onChange: set } );
		}
		if ( def.type === 'boolean' ) {
			return el( ToggleControl, { key: name, label, checked: !! value, onChange: set } );
		}
		if ( def.type === 'number' || def.type === 'integer' ) {
			return el( TextControl, { key: name, label, type: 'number', value: value ?? '', onChange: ( v ) => set( v === '' ? undefined : Number( v ) ) } );
		}
		return el( TextControl, { key: name, label, value: value ?? '', onChange: set } );
	}

	function makeEdit( name, attributes ) {
		return function Edit( props ) {
			const blockProps = useBlockProps( { className: 'top-ssr' } );
			const editable = Object.keys( attributes ).filter( ( k ) => ! [ 'className', 'lock', 'metadata', 'style' ].includes( k ) );
			return el(
				Fragment,
				null,
				editable.length &&
					el( InspectorControls, null, el( PanelBody, { title: 'Settings', initialOpen: true }, editable.map( ( k ) => controlFor( k, attributes[ k ], props ) ) ) ),
				el( 'div', blockProps, el( ServerSideRender, { block: name, attributes: props.attributes, skipBlockSupportAttributes: true } ) )
			);
		};
	}

	// Show real Business Info values in bound core blocks (instead of the source label).
	if ( wp.blocks.registerBlockBindingsSource && window.topCoreBusiness ) {
		try {
			wp.blocks.registerBlockBindingsSource( {
				name: 'top/business',
				label: 'Business Info',
				getValues( { bindings } ) {
					const out = {};
					Object.entries( bindings ).forEach( ( [ attr, b ] ) => {
						out[ attr ] = window.topCoreBusiness[ b?.args?.key ] ?? '';
					} );
					return out;
				},
				canUserEditValue: () => false,
			} );
		} catch ( e ) {} // already registered
	}

	// Server-registered block types are already known to the editor; attach the edit UI.
	( window.topCoreBlocks || [] ).forEach( function ( meta ) {
		registerBlockType( meta.name, {
			edit: makeEdit( meta.name, meta.attributes || {} ),
			save: () => null,
		} );
	} );
} )( window.wp );
