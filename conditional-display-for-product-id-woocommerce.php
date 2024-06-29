<?php

/**
 * Plugin Name: Conditional display for Product ID in WooCommerce
 * Description: A custom block that displays content conditionally based on whether a user has purchased a specific WooCommerce product.
 * Version: 1.2.0
 * Author: Stefan Van der Vyver
 * Author URI: https://github.com/stefangub
 * License: Split License
 * License URI: http://codecanyon.net/licenses/standard
 * Text Domain: cdpid-woocommerce
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.2
 * WC requires at least: 7.1
 * WC tested up to: 7.9
 *
 * This plugin is released under a commercial license and is intended for use in accordance with the CodeCanyon/Envato licensing terms.
 * Parts of this plugin may incorporate GPL-licensed code due to WordPress ecosystem requirements.
 * 
 * @package ConditionalDisplayForProductID
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Declare compatibility with HPOS
    if ( class_exists( FeaturesUtil::class ) ) {
    if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
    }
}
add_action( 'before_woocommerce_init', 'cdpid_declare_hpos_compatibility' );

// Rest of your plugin code...

function cdpid_register_block() {
    if (!function_exists('register_block_type')) {
        error_log('CDPID Block: register_block_type function does not exist');
        return;
    }

    wp_register_script(
        'conditional-display-product-id-block-editor',
        plugins_url('js/conditional-display-product-id-block.js', __FILE__),
        array('wp-blocks', 'wp-element', 'wp-editor', 'wp-components'),
        filemtime(plugin_dir_path(__FILE__) . 'js/conditional-display-product-id-block.js')
    );

    register_block_type('cdpid/conditional-display-product-id-block', array(
        'editor_script' => 'conditional-display-product-id-block-editor',
        'render_callback' => 'cdpid_render_block',
        'attributes' => array(
            'productId' => array(
                'type' => 'number',
                'default' => 0,
            ),
            'alternateText' => array(
                'type' => 'string',
                'default' => 'Alternate text for this product.',
            ),
            'displayIfPurchased' => array(
                'type' => 'boolean',
                'default' => true,
            ),
            'displayIfNotPurchased' => array(
                'type' => 'boolean',
                'default' => true,
            ),
        ),
    ));

    error_log('CDPID Block: Block registered successfully');
}
add_action('init', 'cdpid_register_block');

function cdpid_render_block($attributes, $content) {
    // Check if WooCommerce is active
    if (!class_exists('WooCommerce')) {
        return '<div class="conditional-display-product-id-block cdpid-error">' . esc_html__('WooCommerce is required for this block.', 'cdpid-woocommerce') . '</div>';
    }

    if (!is_user_logged_in()) {
        return '<div class="conditional-display-product-id-block cdpid-not-logged-in">' . wp_kses_post($content) . '</div>';
    }

    $product_id = isset($attributes['productId']) ? absint($attributes['productId']) : 0;
    $alternate_text = isset($attributes['alternateText']) ? sanitize_text_field($attributes['alternateText']) : esc_html__('Alternate text for this product.', 'cdpid-woocommerce');
    $display_if_purchased = isset($attributes['displayIfPurchased']) ? (bool) $attributes['displayIfPurchased'] : true;
    $display_if_not_purchased = isset($attributes['displayIfNotPurchased']) ? (bool) $attributes['displayIfNotPurchased'] : true;
    
    if ($product_id === 0) {
        return '<div class="conditional-display-product-id-block cdpid-no-product">' . esc_html__('No product ID specified.', 'cdpid-woocommerce') . '</div>';
    }

    // Check if the user has purchased the product
    $current_user = wp_get_current_user();
    $user_purchased = cdpid_check_user_purchased($current_user->user_email, $current_user->ID, $product_id);
    
    $output = '<div class="conditional-display-product-id-block" data-product-id="' . esc_attr($product_id) . '">';
    
    if ($user_purchased && $display_if_purchased) {
        $output .= wp_kses_post($content);
    } elseif (!$user_purchased && $display_if_not_purchased) {
        $output .= wp_kses_post($content);
    } else {
        $output .= '<p>' . esc_html($alternate_text) . '</p>';
    }
    
    $output .= '</div>';
    
    return $output;
}

// Helper function to check if user has purchased product with caching
function cdpid_check_user_purchased($user_email, $user_id, $product_id) {
    $cache_key = 'cdpid_user_' . $user_id . '_product_' . $product_id;
    $user_purchased = wp_cache_get($cache_key);
    
    if (false === $user_purchased) {
        $user_purchased = wc_customer_bought_product($user_email, $user_id, $product_id);
        wp_cache_set($cache_key, $user_purchased, '', 3600); // Cache for 1 hour
    }
    
    return $user_purchased;
}

// Add a custom block category for the Conditional Display for Product ID block
function cdpid_add_block_category($categories, $post) {
    return array_merge(
        $categories,
        array(
            array(
                'slug' => 'woocommerce-custom-blocks',
                'title' => __('WooCommerce Custom Blocks', 'cdpid-woocommerce'),
            ),
        )
    );
}
add_filter('block_categories_all', 'cdpid_add_block_category', 10, 2);