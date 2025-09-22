<?php
/**
 * Database Handler
 *
 * @package WP_Testimonial_Walls
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class WP_Testimonial_Walls_Database
 */
class WP_Testimonial_Walls_Database {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('init', array($this, 'maybe_create_tables'));
    }
    
    /**
     * Maybe create tables if they don't exist
     */
    public function maybe_create_tables() {
        $installed_version = get_option('wp_testimonial_walls_db_version');
        
        if ($installed_version !== WP_TESTIMONIAL_WALLS_VERSION) {
            $this->create_tables();
            update_option('wp_testimonial_walls_db_version', WP_TESTIMONIAL_WALLS_VERSION);
        }
    }
    
    /**
     * Create database tables
     */
    public function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Testimonial-Wall relationship table
        $table_name = $wpdb->prefix . 'testimonial_wall_relations';
        
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            wall_id bigint(20) NOT NULL,
            testimonial_id bigint(20) NOT NULL,
            sort_order int(11) NOT NULL DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY wall_id (wall_id),
            KEY testimonial_id (testimonial_id),
            KEY sort_order (sort_order),
            UNIQUE KEY wall_testimonial (wall_id, testimonial_id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        // Add image size for testimonial logos
        add_image_size('testimonial_logo', 200, 100, false);
    }
    
    /**
     * Get testimonials for a wall
     *
     * @param int $wall_id Wall ID
     * @return array Array of testimonial post objects
     */
    public function get_wall_testimonials($wall_id) {
        global $wpdb;
        
        $cache_key = "wall_testimonials_{$wall_id}";
        $testimonials = wp_cache_get($cache_key, 'wp_testimonial_walls');
        
        if (false === $testimonials) {
            $table_name = $wpdb->prefix . 'testimonial_wall_relations';
            
            $testimonial_ids = $wpdb->get_col($wpdb->prepare(
                "SELECT testimonial_id FROM {$table_name} 
                 WHERE wall_id = %d 
                 ORDER BY sort_order ASC",
                $wall_id
            ));
            
            $testimonials = array();
            
            if (!empty($testimonial_ids)) {
                $testimonials = get_posts(array(
                    'post_type' => 'testimonial',
                    'post_status' => 'publish',
                    'posts_per_page' => -1,
                    'post__in' => $testimonial_ids,
                    'orderby' => 'post__in'
                ));
            }
            
            wp_cache_set($cache_key, $testimonials, 'wp_testimonial_walls', 3600);
        }
        
        return $testimonials;
    }
    
    /**
     * Get walls that contain a specific testimonial
     *
     * @param int $testimonial_id Testimonial ID
     * @return array Array of wall post objects
     */
    public function get_testimonial_walls($testimonial_id) {
        global $wpdb;
        
        $cache_key = "testimonial_walls_{$testimonial_id}";
        $walls = wp_cache_get($cache_key, 'wp_testimonial_walls');
        
        if (false === $walls) {
            $table_name = $wpdb->prefix . 'testimonial_wall_relations';
            
            $wall_ids = $wpdb->get_col($wpdb->prepare(
                "SELECT wall_id FROM {$table_name} 
                 WHERE testimonial_id = %d",
                $testimonial_id
            ));
            
            $walls = array();
            
            if (!empty($wall_ids)) {
                $walls = get_posts(array(
                    'post_type' => 'wall',
                    'post_status' => 'publish',
                    'posts_per_page' => -1,
                    'post__in' => $wall_ids
                ));
            }
            
            wp_cache_set($cache_key, $walls, 'wp_testimonial_walls', 3600);
        }
        
        return $walls;
    }
    
    /**
     * Add testimonial to wall
     *
     * @param int $wall_id Wall ID
     * @param int $testimonial_id Testimonial ID
     * @param int $sort_order Sort order
     * @return bool Success
     */
    public function add_testimonial_to_wall($wall_id, $testimonial_id, $sort_order = 0) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'testimonial_wall_relations';
        
        $result = $wpdb->insert(
            $table_name,
            array(
                'wall_id' => $wall_id,
                'testimonial_id' => $testimonial_id,
                'sort_order' => $sort_order
            ),
            array('%d', '%d', '%d')
        );
        
        if ($result) {
            // Clear cache
            wp_cache_delete("wall_testimonials_{$wall_id}", 'wp_testimonial_walls');
            wp_cache_delete("testimonial_walls_{$testimonial_id}", 'wp_testimonial_walls');
        }
        
        return $result !== false;
    }
    
    /**
     * Remove testimonial from wall
     *
     * @param int $wall_id Wall ID
     * @param int $testimonial_id Testimonial ID
     * @return bool Success
     */
    public function remove_testimonial_from_wall($wall_id, $testimonial_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'testimonial_wall_relations';
        
        $result = $wpdb->delete(
            $table_name,
            array(
                'wall_id' => $wall_id,
                'testimonial_id' => $testimonial_id
            ),
            array('%d', '%d')
        );
        
        if ($result) {
            // Clear cache
            wp_cache_delete("wall_testimonials_{$wall_id}", 'wp_testimonial_walls');
            wp_cache_delete("testimonial_walls_{$testimonial_id}", 'wp_testimonial_walls');
        }
        
        return $result !== false;
    }
    
    /**
     * Update testimonial sort order in wall
     *
     * @param int $wall_id Wall ID
     * @param array $testimonial_order Array of testimonial IDs in order
     * @return bool Success
     */
    public function update_testimonial_order($wall_id, $testimonial_order) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'testimonial_wall_relations';
        
        $wpdb->query('START TRANSACTION');
        
        try {
            foreach ($testimonial_order as $index => $testimonial_id) {
                $wpdb->update(
                    $table_name,
                    array('sort_order' => $index),
                    array(
                        'wall_id' => $wall_id,
                        'testimonial_id' => $testimonial_id
                    ),
                    array('%d'),
                    array('%d', '%d')
                );
            }
            
            $wpdb->query('COMMIT');
            
            // Clear cache
            wp_cache_delete("wall_testimonials_{$wall_id}", 'wp_testimonial_walls');
            
            return true;
            
        } catch (Exception $e) {
            $wpdb->query('ROLLBACK');
            return false;
        }
    }
    
    /**
     * Clean up orphaned relationships
     */
    public function cleanup_orphaned_relationships() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'testimonial_wall_relations';
        
        // Remove relationships where wall doesn't exist
        $wpdb->query("
            DELETE r FROM {$table_name} r
            LEFT JOIN {$wpdb->posts} p ON r.wall_id = p.ID
            WHERE p.ID IS NULL OR p.post_type != 'wall'
        ");
        
        // Remove relationships where testimonial doesn't exist
        $wpdb->query("
            DELETE r FROM {$table_name} r
            LEFT JOIN {$wpdb->posts} p ON r.testimonial_id = p.ID
            WHERE p.ID IS NULL OR p.post_type != 'testimonial'
        ");
    }
    
    /**
     * Get statistics
     *
     * @return array Statistics array
     */
    public function get_statistics() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'testimonial_wall_relations';
        
        $stats = array();
        
        // Total testimonials
        $testimonial_counts = wp_count_posts('testimonial');
        $stats['total_testimonials'] = isset($testimonial_counts->publish) ? $testimonial_counts->publish : 0;
        
        // Total walls
        $wall_counts = wp_count_posts('wall');
        $stats['total_walls'] = isset($wall_counts->publish) ? $wall_counts->publish : 0;
        
        // Total relationships
        $stats['total_relationships'] = $wpdb->get_var("SELECT COUNT(*) FROM {$table_name}");
        
        // Most used testimonial
        $most_used = $wpdb->get_row("
            SELECT testimonial_id, COUNT(*) as usage_count 
            FROM {$table_name} 
            GROUP BY testimonial_id 
            ORDER BY usage_count DESC 
            LIMIT 1
        ");
        
        if ($most_used) {
            $testimonial = get_post($most_used->testimonial_id);
            $stats['most_used_testimonial'] = array(
                'id' => $most_used->testimonial_id,
                'title' => $testimonial ? $testimonial->post_title : __('Unknown', 'wp-testimonial-walls'),
                'usage_count' => $most_used->usage_count
            );
        }
        
        return $stats;
    }
    
    /**
     * Drop tables (for uninstall)
     */
    public function drop_tables() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'testimonial_wall_relations';
        $wpdb->query("DROP TABLE IF EXISTS {$table_name}");
        
        // Remove options
        delete_option('wp_testimonial_walls_db_version');
    }
}
