<?php
/**
 * Plugin Name: Conditional display for Product ID in WooCommerce
 * Description: A custom block that displays content conditionally based on whether a user has purchased a specific WooCommerce product.
 * Version: 1.1.0
 * Author: Stefan Van der Vyver
 * Author URI: https://github.com/stefangub
 * License: Split License
 * License URI: http://codecanyon.net/licenses/standard
 * Text Domain: cdpid-woocommerce
 * Domain Path: /languages
 * Requires at least: 5.0
 * Requires PHP: 7.2
 * WC requires at least: 3.0
 * WC tested up to: 7.0
 *
 * This plugin is released under a commercial license and is intended for use in accordance with the CodeCanyon/Envato licensing terms.
 * Parts of this plugin may incorporate GPL-licensed code due to WordPress ecosystem requirements.
 * 
 * @package ConditionalDisplayForProductID
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

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
    if (!is_user_logged_in() || !function_exists('wc_customer_bought_product')) {
        return '<div class="conditional-display-product-id-block">' . $content . '</div>';
    }

    $product_id = isset($attributes['productId']) ? intval($attributes['productId']) : 0;
    $alternate_text = isset($attributes['alternateText']) ? sanitize_text_field($attributes['alternateText']) : esc_html__('Alternate text for this product.', 'cdpid-woocommerce');
    $display_if_purchased = isset($attributes['displayIfPurchased']) ? $attributes['displayIfPurchased'] : true;
    $display_if_not_purchased = isset($attributes['displayIfNotPurchased']) ? $attributes['displayIfNotPurchased'] : true;
    
    if ($product_id === 0) {
        return '';
    }

    $current_user = wp_get_current_user();
    $user_purchased = wc_customer_bought_product($current_user->user_email, $current_user->ID, $product_id);
    
    if ($user_purchased) {
        if ($display_if_purchased) {
            return '<div class="conditional-display-product-id-block">' . $content . '</div>';
        } else {
            return '<div class="conditional-display-product-id-block"><p>' . $alternate_text . '</p></div>';
        }
    } else {
        if ($display_if_not_purchased) {
            return '<div class="conditional-display-product-id-block">' . $content . '</div>';
        } else {
            return '<div class="conditional-display-product-id-block"><p>' . $alternate_text . '</p></div>';
        }
    }
}

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