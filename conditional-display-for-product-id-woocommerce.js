/**
 * Conditional display for Product ID in WooCommerce
 * 
 * @package ConditionalDisplayForProductID
 * @author Stefan Van der Vyver
 * @license Split License - Commercial use requires CodeCanyon license
 */

// Import WordPress dependencies
const { registerBlockType } = wp.blocks;
const { InspectorControls, InnerBlocks } = wp.blockEditor;
const { PanelBody, TextControl, ToggleControl } = wp.components;
const { createElement, Fragment } = wp.element;
const { __ } = wp.i18n;

// Register the block
registerBlockType('cdpid/conditional-display-product-id-block', {
    title: __('Conditional Display for Product ID', 'cdpid-woocommerce'),
    icon: 'cart',
    category: 'woocommerce-custom-blocks',
    attributes: {
        productId: {
            type: 'number',
            default: 0,
        },
        alternateText: {
            type: 'string',
            default: __('Alternate text for this product.', 'cdpid-woocommerce'),
        },
        displayIfPurchased: {
            type: 'boolean',
            default: true,
        },
        displayIfNotPurchased: {
            type: 'boolean',
            default: true,
        },
    },
    edit: function(props) {
        const { attributes, setAttributes } = props;
        
        // Return the block markup
        return createElement(Fragment, null, 
            createElement(InspectorControls, null, 
                // Block settings
                createElement(PanelBody, { title: __("Block Settings", 'cdpid-woocommerce') },
                    createElement(TextControl, {
                        label: __("Product ID", 'cdpid-woocommerce'),
                        value: attributes.productId,
                        onChange: (value) => setAttributes({ productId: parseInt(value) }),
                        type: "number"
                    }),
                    // Alternate text
                    createElement(TextControl, {
                        label: __("Alternate Text", 'cdpid-woocommerce'),
                        value: attributes.alternateText,
                        onChange: (value) => setAttributes({ alternateText: value }),
                    }),
                    // Display if purchased switch control
                    createElement(ToggleControl, {
                        label: __("Display if purchased", 'cdpid-woocommerce'),
                        checked: attributes.displayIfPurchased,
                        onChange: (value) => setAttributes({ displayIfPurchased: value }),
                    }),
                    // Display if not purchased switch control
                    createElement(ToggleControl, {
                        label: __("Display if not purchased", 'cdpid-woocommerce'),
                        checked: attributes.displayIfNotPurchased,
                        onChange: (value) => setAttributes({ displayIfNotPurchased: value }),
                    })
                )
            ),
            createElement('div', { className: "conditional-display-product-id-block" },
                createElement('p', null, __(`This block's content depends on whether the user has purchased product ID: ${attributes.productId}`, 'cdpid-woocommerce')),
                createElement('p', null, __(`Alternate text: ${attributes.alternateText}`, 'cdpid-woocommerce')),
                createElement('p', null, __(`Display if purchased: ${attributes.displayIfPurchased ? 'Yes' : 'No'}`, 'cdpid-woocommerce')),
                createElement('p', null, __(`Display if not purchased: ${attributes.displayIfNotPurchased ? 'Yes' : 'No'}`, 'cdpid-woocommerce')),
                createElement(InnerBlocks, null)
            )
        );
    },
    save: function() {
        return createElement(InnerBlocks.Content, null);
    },
});