( function ( blocks, element, blockEditor, components ) {
    var el = element.createElement;
    var InspectorControls = blockEditor.InspectorControls;
    var TextControl = components.TextControl;
    var PanelBody = components.PanelBody;

    blocks.registerBlockType( 'crea-api/office-list', {
        title: 'CREA Office List',
        icon: 'building',
        category: 'widgets',
        attributes: {
            columns: {
                type: 'number',
                default: 3,
            },
            rows: {
                type: 'number',
                default: 5,
            },
        },
        edit: function ( props ) {
            var attributes = props.attributes;

            function onChangeColumns( value ) {
                props.setAttributes( { columns: parseInt( value, 10 ) || 1 } );
            }

            function onChangeRows( value ) {
                props.setAttributes( { rows: parseInt( value, 10 ) || 1 } );
            }

            return [
                el(
                    InspectorControls,
                    null,
                    el(
                        PanelBody,
                        { title: 'Settings', initialOpen: true },
                        el( TextControl, {
                            label: 'Columns',
                            type: 'number',
                            value: attributes.columns,
                            onChange: onChangeColumns,
                        } ),
                        el( TextControl, {
                            label: 'Rows',
                            type: 'number',
                            value: attributes.rows,
                            onChange: onChangeRows,
                        } )
                    )
                ),
                el( 'div', { className: props.className }, 'CREA Office List Block' )
            ];
        },
        save: function () {
            // Rendering is done via PHP
            return null;
        },
    } );
} )( window.wp.blocks, window.wp.element, window.wp.blockEditor, window.wp.components );
