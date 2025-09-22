<?php
/**
 * Cache Handler
 *
 * @package WP_Testimonial_Walls
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class WP_Testimonial_Walls_Cache
 */
class WP_Testimonial_Walls_Cache {
    
    /**
     * Cache group
     */
    const CACHE_GROUP = 'wp_testimonial_walls';
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('save_post', array($this, 'clear_wall_cache'));
        add_action('delete_post', array($this, 'clear_wall_cache'));
        add_action('wp_trash_post', array($this, 'clear_wall_cache'));
        add_action('untrash_post', array($this, 'clear_wall_cache'));
    }
    
    /**
     * Get cached wall data
     *
     * @param int $wall_id Wall ID
     * @return mixed Cached data or false
     */
    public function get_wall_cache($wall_id) {
        $options = get_option('wp_testimonial_walls_options', array());
        $cache_duration = intval($options['cache_duration'] ?? 3600);
        
        if ($cache_duration <= 0) {
            return false;
        }
        
        $cache_key = "wall_data_{$wall_id}";
        return wp_cache_get($cache_key, self::CACHE_GROUP);
    }
    
    /**
     * Set wall cache
     *
     * @param int $wall_id Wall ID
     * @param mixed $data Data to cache
     * @return bool Success
     */
    public function set_wall_cache($wall_id, $data) {
        $options = get_option('wp_testimonial_walls_options', array());
        $cache_duration = intval($options['cache_duration'] ?? 3600);
        
        if ($cache_duration <= 0) {
            return false;
        }
        
        $cache_key = "wall_data_{$wall_id}";
        return wp_cache_set($cache_key, $data, self::CACHE_GROUP, $cache_duration);
    }
    
    /**
     * Clear wall cache
     *
     * @param int $post_id Post ID
     */
    public function clear_wall_cache($post_id) {
        $post_type = get_post_type($post_id);
        
        if ($post_type === 'wall') {
            // Clear cache for this wall
            wp_cache_delete("wall_data_{$post_id}", self::CACHE_GROUP);
            wp_cache_delete("wall_testimonials_{$post_id}", self::CACHE_GROUP);
            
        } elseif ($post_type === 'testimonial') {
            // Clear cache for all walls containing this testimonial
            $database = new WP_Testimonial_Walls_Database();
            $walls = $database->get_testimonial_walls($post_id);
            
            foreach ($walls as $wall) {
                wp_cache_delete("wall_data_{$wall->ID}", self::CACHE_GROUP);
                wp_cache_delete("wall_testimonials_{$wall->ID}", self::CACHE_GROUP);
            }
            
            // Clear testimonial walls cache
            wp_cache_delete("testimonial_walls_{$post_id}", self::CACHE_GROUP);
        }
    }
    
    /**
     * Clear all cache
     */
    public function clear_all_cache() {
        wp_cache_flush_group(self::CACHE_GROUP);
        
        // Also clear transients
        global $wpdb;
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_wp_testimonial_walls_%'");
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_wp_testimonial_walls_%'");
    }
    
    /**
     * Get cached testimonial data with fallback to transients
     *
     * @param int $wall_id Wall ID
     * @return array|false Testimonial data or false
     */
    public function get_testimonials_cache($wall_id) {
        // Try object cache first
        $cache_key = "wall_testimonials_{$wall_id}";
        $testimonials = wp_cache_get($cache_key, self::CACHE_GROUP);
        
        if (false !== $testimonials) {
            return $testimonials;
        }
        
        // Fallback to transients
        $transient_key = "wp_testimonial_walls_testimonials_{$wall_id}";
        $testimonials = get_transient($transient_key);
        
        if (false !== $testimonials) {
            // Store in object cache for this request
            wp_cache_set($cache_key, $testimonials, self::CACHE_GROUP, 300); // 5 minutes
            return $testimonials;
        }
        
        return false;
    }
    
    /**
     * Set testimonials cache with transient fallback
     *
     * @param int $wall_id Wall ID
     * @param array $testimonials Testimonials data
     */
    public function set_testimonials_cache($wall_id, $testimonials) {
        $options = get_option('wp_testimonial_walls_options', array());
        $cache_duration = intval($options['cache_duration'] ?? 3600);
        
        if ($cache_duration <= 0) {
            return;
        }
        
        // Set object cache
        $cache_key = "wall_testimonials_{$wall_id}";
        wp_cache_set($cache_key, $testimonials, self::CACHE_GROUP, $cache_duration);
        
        // Set transient as fallback
        $transient_key = "wp_testimonial_walls_testimonials_{$wall_id}";
        set_transient($transient_key, $testimonials, $cache_duration);
    }
    
    /**
     * Get cache statistics
     *
     * @return array Cache statistics
     */
    public function get_cache_stats() {
        global $wpdb;
        
        $stats = array();
        
        // Count transients
        $transient_count = $wpdb->get_var("
            SELECT COUNT(*) 
            FROM {$wpdb->options} 
            WHERE option_name LIKE '_transient_wp_testimonial_walls_%'
        ");
        
        $stats['transient_count'] = intval($transient_count);
        
        // Get total size (approximate)
        $transient_size = $wpdb->get_var("
            SELECT SUM(LENGTH(option_value)) 
            FROM {$wpdb->options} 
            WHERE option_name LIKE '_transient_wp_testimonial_walls_%'
        ");
        
        $stats['transient_size'] = intval($transient_size);
        $stats['transient_size_formatted'] = size_format($stats['transient_size']);
        
        return $stats;
    }
    
    /**
     * Preload cache for popular walls
     */
    public function preload_popular_walls() {
        global $wpdb;
        
        // Get most viewed walls (if you have view tracking)
        $popular_walls = get_posts(array(
            'post_type' => 'wall',
            'posts_per_page' => 10,
            'post_status' => 'publish',
            'meta_key' => '_wall_views',
            'orderby' => 'meta_value_num',
            'order' => 'DESC'
        ));
        
        if (empty($popular_walls)) {
            // Fallback to recent walls
            $popular_walls = get_posts(array(
                'post_type' => 'wall',
                'posts_per_page' => 5,
                'post_status' => 'publish',
                'orderby' => 'date',
                'order' => 'DESC'
            ));
        }
        
        $database = new WP_Testimonial_Walls_Database();
        
        foreach ($popular_walls as $wall) {
            // Check if already cached
            if (false === $this->get_testimonials_cache($wall->ID)) {
                // Load and cache testimonials
                $testimonials = $database->get_wall_testimonials($wall->ID);
                $this->set_testimonials_cache($wall->ID, $testimonials);
            }
        }
    }
    
    /**
     * Schedule cache cleanup
     */
    public function schedule_cache_cleanup() {
        if (!wp_next_scheduled('wp_testimonial_walls_cache_cleanup')) {
            wp_schedule_event(time(), 'daily', 'wp_testimonial_walls_cache_cleanup');
        }
        
        add_action('wp_testimonial_walls_cache_cleanup', array($this, 'cleanup_expired_cache'));
    }
    
    /**
     * Cleanup expired cache
     */
    public function cleanup_expired_cache() {
        global $wpdb;
        
        // Clean up expired transients
        $wpdb->query("
            DELETE a, b FROM {$wpdb->options} a, {$wpdb->options} b
            WHERE a.option_name LIKE '_transient_wp_testimonial_walls_%'
            AND a.option_name NOT LIKE '_transient_timeout_wp_testimonial_walls_%'
            AND b.option_name = CONCAT('_transient_timeout_', SUBSTRING(a.option_name, 12))
            AND b.option_value < UNIX_TIMESTAMP()
        ");
    }
}
