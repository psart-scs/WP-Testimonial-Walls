<?php
/**
 * Uninstall Script for WP Testimonial Walls
 * 
 * This file is executed when the plugin is deleted via the WordPress admin.
 * It removes all plugin data from the database.
 *
 * @package WP_Testimonial_Walls
 */

// If uninstall not called from WordPress, then exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

/**
 * Remove all plugin data
 */
function wp_testimonial_walls_uninstall() {
    global $wpdb;
    
    // Check if user wants to preserve data
    $preserve_data = get_option('wp_testimonial_walls_preserve_data', false);
    
    if ($preserve_data) {
        // Only remove plugin options, keep posts and relationships
        delete_option('wp_testimonial_walls_options');
        delete_option('wp_testimonial_walls_preserve_data');
        delete_option('wp_testimonial_walls_db_version');
        return;
    }
    
    // Remove all testimonial posts
    $testimonials = get_posts(array(
        'post_type' => 'testimonial',
        'posts_per_page' => -1,
        'post_status' => 'any'
    ));
    
    foreach ($testimonials as $testimonial) {
        wp_delete_post($testimonial->ID, true);
    }
    
    // Remove all wall posts
    $walls = get_posts(array(
        'post_type' => 'wall',
        'posts_per_page' => -1,
        'post_status' => 'any'
    ));
    
    foreach ($walls as $wall) {
        wp_delete_post($wall->ID, true);
    }
    
    // Remove custom database table
    $table_name = $wpdb->prefix . 'testimonial_wall_relations';
    $wpdb->query("DROP TABLE IF EXISTS {$table_name}");
    
    // Remove all plugin options
    delete_option('wp_testimonial_walls_options');
    delete_option('wp_testimonial_walls_preserve_data');
    delete_option('wp_testimonial_walls_db_version');
    
    // Remove all transients
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_wp_testimonial_walls_%'");
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_wp_testimonial_walls_%'");
    
    // Remove user meta
    $wpdb->query("DELETE FROM {$wpdb->usermeta} WHERE meta_key LIKE 'wp_testimonial_walls_%'");
    
    // Remove any scheduled events
    wp_clear_scheduled_hook('wp_testimonial_walls_cache_cleanup');
    
    // Clear any cached data
    wp_cache_flush_group('wp_testimonial_walls');
    
    // Remove image sizes
    remove_image_size('testimonial_logo');
    
    // Flush rewrite rules
    flush_rewrite_rules();
}

// Execute uninstall
wp_testimonial_walls_uninstall();
