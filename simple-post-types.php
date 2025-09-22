<?php
/**
 * Simple standalone post type registration
 * This bypasses all class structures and registers post types directly
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Register post types on init with highest priority
add_action('init', 'wp_testimonial_walls_register_simple_post_types', 0);

function wp_testimonial_walls_register_simple_post_types() {
    // Register testimonial post type
    register_post_type('testimonial', array(
        'labels' => array(
            'name' => 'Testimonials',
            'singular_name' => 'Testimonial',
            'add_new' => 'Add New',
            'add_new_item' => 'Add New Testimonial',
            'edit_item' => 'Edit Testimonial',
            'new_item' => 'New Testimonial',
            'view_item' => 'View Testimonial',
            'search_items' => 'Search Testimonials',
            'not_found' => 'No testimonials found',
            'not_found_in_trash' => 'No testimonials found in Trash',
            'all_items' => 'All Testimonials',
            'menu_name' => 'Testimonials',
        ),
        'public' => false,
        'publicly_queryable' => false,
        'show_ui' => true,
        'show_in_menu' => 'testimonial-walls',
        'query_var' => true,
        'rewrite' => false,
        'capability_type' => 'post',
        'has_archive' => false,
        'hierarchical' => false,
        'supports' => array('title', 'editor', 'thumbnail'),
        'show_in_rest' => true,
    ));

    // Register wall post type
    register_post_type('wall', array(
        'labels' => array(
            'name' => 'Testimonial Walls',
            'singular_name' => 'Wall',
            'add_new' => 'Add New',
            'add_new_item' => 'Add New Wall',
            'edit_item' => 'Edit Wall',
            'new_item' => 'New Wall',
            'view_item' => 'View Wall',
            'search_items' => 'Search Walls',
            'not_found' => 'No walls found',
            'not_found_in_trash' => 'No walls found in Trash',
            'all_items' => 'All Walls',
            'menu_name' => 'Walls',
        ),
        'public' => false,
        'publicly_queryable' => false,
        'show_ui' => true,
        'show_in_menu' => 'testimonial-walls',
        'query_var' => true,
        'rewrite' => false,
        'capability_type' => 'post',
        'has_archive' => false,
        'hierarchical' => false,
        'supports' => array('title'),
        'show_in_rest' => true,
    ));
}

// Force flush rewrite rules on activation
register_activation_hook(__FILE__, 'wp_testimonial_walls_simple_activate');
function wp_testimonial_walls_simple_activate() {
    wp_testimonial_walls_register_simple_post_types();
    flush_rewrite_rules();
}

// Flush rewrite rules on deactivation
register_deactivation_hook(__FILE__, 'wp_testimonial_walls_simple_deactivate');
function wp_testimonial_walls_simple_deactivate() {
    flush_rewrite_rules();
}
?>
