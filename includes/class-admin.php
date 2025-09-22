<?php
/**
 * Admin Interface Handler
 *
 * @package WP_Testimonial_Walls
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class WP_Testimonial_Walls_Admin
 */
class WP_Testimonial_Walls_Admin {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'admin_init'));
        add_action('admin_notices', array($this, 'admin_notices'));
        add_filter('post_row_actions', array($this, 'add_post_row_actions'), 10, 2);
        add_filter('page_row_actions', array($this, 'add_post_row_actions'), 10, 2);
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        // Main menu - redirect to walls
        add_menu_page(
            __('Testimonial Walls', 'wp-testimonial-walls'),
            __('Testimonial Walls', 'wp-testimonial-walls'),
            'manage_options',
            'edit.php?post_type=wall',
            '',
            'dashicons-format-quote',
            30
        );
        
        // Add New Wall submenu
        add_submenu_page(
            'edit.php?post_type=wall',
            __('Add New Wall', 'wp-testimonial-walls'),
            __('Add New Wall', 'wp-testimonial-walls'),
            'manage_options',
            'post-new.php?post_type=wall'
        );
        
        // Testimonials submenu
        add_submenu_page(
            'edit.php?post_type=wall',
            __('Testimonials', 'wp-testimonial-walls'),
            __('Testimonials', 'wp-testimonial-walls'),
            'manage_options',
            'edit.php?post_type=testimonial'
        );
        
        // Add New Testimonial submenu
        add_submenu_page(
            'edit.php?post_type=wall',
            __('Add New Testimonial', 'wp-testimonial-walls'),
            __('Add New Testimonial', 'wp-testimonial-walls'),
            'manage_options',
            'post-new.php?post_type=testimonial'
        );
        
        // Settings submenu
        add_submenu_page(
            'edit.php?post_type=wall',
            __('Settings', 'wp-testimonial-walls'),
            __('Settings', 'wp-testimonial-walls'),
            'manage_options',
            'testimonial-walls-settings',
            array($this, 'settings_page')
        );
    }
    
    /**
     * Walls overview page
     */
    public function walls_page() {
        $walls = get_posts(array(
            'post_type' => 'wall',
            'posts_per_page' => -1,
            'post_status' => array('publish', 'draft')
        ));
        
        $database = new WP_Testimonial_Walls_Database();
        $stats = $database->get_statistics();
        
        ?>
        <div class="wrap">
            <h1><?php _e('Testimonial Walls', 'wp-testimonial-walls'); ?></h1>
            
            <div class="testimonial-walls-dashboard">
                <div class="stats-cards">
                    <div class="stats-card">
                        <h3><?php echo $stats['total_walls']; ?></h3>
                        <p><?php _e('Total Walls', 'wp-testimonial-walls'); ?></p>
                    </div>
                    <div class="stats-card">
                        <h3><?php echo $stats['total_testimonials']; ?></h3>
                        <p><?php _e('Total Testimonials', 'wp-testimonial-walls'); ?></p>
                    </div>
                    <div class="stats-card">
                        <h3><?php echo $stats['total_relationships']; ?></h3>
                        <p><?php _e('Total Assignments', 'wp-testimonial-walls'); ?></p>
                    </div>
                </div>
                
                <div class="quick-actions">
                    <a href="<?php echo admin_url('post-new.php?post_type=wall'); ?>" class="button button-primary">
                        <?php _e('Create New Wall', 'wp-testimonial-walls'); ?>
                    </a>
                    <a href="<?php echo admin_url('post-new.php?post_type=testimonial'); ?>" class="button">
                        <?php _e('Add New Testimonial', 'wp-testimonial-walls'); ?>
                    </a>
                </div>
                
                <?php if (!empty($walls)): ?>
                <div class="walls-overview">
                    <h2><?php _e('Recent Walls', 'wp-testimonial-walls'); ?></h2>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th><?php _e('Wall', 'wp-testimonial-walls'); ?></th>
                                <th><?php _e('Layout', 'wp-testimonial-walls'); ?></th>
                                <th><?php _e('Testimonials', 'wp-testimonial-walls'); ?></th>
                                <th><?php _e('Shortcode', 'wp-testimonial-walls'); ?></th>
                                <th><?php _e('Status', 'wp-testimonial-walls'); ?></th>
                                <th><?php _e('Actions', 'wp-testimonial-walls'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($walls as $wall): ?>
                                <?php
                                $layout = (get_post_meta($wall->ID, '_wall_layout', true) ?: 'grid');
                                $testimonials = $database->get_wall_testimonials($wall->ID);
                                ?>
                                <tr>
                                    <td>
                                        <strong><?php echo esc_html($wall->post_title); ?></strong>
                                    </td>
                                    <td><?php echo ucfirst($layout); ?></td>
                                    <td><?php echo count($testimonials); ?></td>
                                    <td>
                                        <code>[wp_testimonial_wall id="<?php echo $wall->ID; ?>"]</code>
                                    </td>
                                    <td>
                                        <span class="status-<?php echo $wall->post_status; ?>">
                                            <?php echo ucfirst($wall->post_status); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="<?php echo get_edit_post_link($wall->ID); ?>" class="button button-small">
                                            <?php _e('Edit', 'wp-testimonial-walls'); ?>
                                        </a>
                                        <?php if ($wall->post_status === 'publish'): ?>
                                        <a href="#" class="button button-small preview-wall" data-wall-id="<?php echo $wall->ID; ?>">
                                            <?php _e('Preview', 'wp-testimonial-walls'); ?>
                                        </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <style>
        .testimonial-walls-dashboard {
            margin-top: 20px;
        }
        .stats-cards {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
        }
        .stats-card {
            background: white;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 20px;
            text-align: center;
            flex: 1;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .stats-card h3 {
            font-size: 2em;
            margin: 0 0 10px 0;
            color: #0073aa;
        }
        .stats-card p {
            margin: 0;
            color: #666;
        }
        .quick-actions {
            margin-bottom: 30px;
        }
        .quick-actions .button {
            margin-right: 10px;
        }
        .walls-overview {
            background: white;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 20px;
        }
        .status-publish {
            color: #46b450;
        }
        .status-draft {
            color: #ca4a1f;
        }
        </style>
        <?php
    }
    
    /**
     * Settings page
     */
    public function settings_page() {
        if (isset($_POST['submit'])) {
            $this->save_settings();
        }
        
        $options = get_option('wp_testimonial_walls_options', array());
        
        ?>
        <div class="wrap">
            <h1><?php _e('Testimonial Walls Settings', 'wp-testimonial-walls'); ?></h1>
            
            <form method="post" action="">
                <?php wp_nonce_field('testimonial_walls_settings', 'testimonial_walls_settings_nonce'); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="cache_duration"><?php _e('Cache Duration', 'wp-testimonial-walls'); ?></label>
                        </th>
                        <td>
                            <input type="number" id="cache_duration" name="cache_duration" 
                                   value="<?php echo esc_attr($options['cache_duration'] ?? 3600); ?>" 
                                   min="0" step="1" class="regular-text" />
                            <p class="description">
                                <?php _e('Cache duration in seconds. Set to 0 to disable caching.', 'wp-testimonial-walls'); ?>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="theme_mode"><?php _e('Theme Mode', 'wp-testimonial-walls'); ?></label>
                        </th>
                        <td>
                            <select id="theme_mode" name="theme_mode" class="regular-text">
                                <option value="auto" <?php selected($options['theme_mode'] ?? 'auto', 'auto'); ?>>
                                    <?php _e('System Default', 'wp-testimonial-walls'); ?>
                                </option>
                                <option value="light" <?php selected($options['theme_mode'] ?? 'auto', 'light'); ?>>
                                    <?php _e('Light Mode', 'wp-testimonial-walls'); ?>
                                </option>
                                <option value="dark" <?php selected($options['theme_mode'] ?? 'auto', 'dark'); ?>>
                                    <?php _e('Dark Mode', 'wp-testimonial-walls'); ?>
                                </option>
                            </select>
                            <p class="description">
                                <?php _e('Choose the theme mode for testimonial walls. System Default follows user\'s browser/OS preference.', 'wp-testimonial-walls'); ?>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="quote_color"><?php _e('Quotation Mark Color', 'wp-testimonial-walls'); ?></label>
                        </th>
                        <td>
                            <input type="color" id="quote_color" name="quote_color" 
                                   value="<?php echo esc_attr($options['quote_color'] ?? '#6A0DAD'); ?>" 
                                   class="regular-text" />
                            <p class="description">
                                <?php _e('Choose the color for quotation marks in testimonials.', 'wp-testimonial-walls'); ?>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="lazy_loading"><?php _e('Lazy Loading', 'wp-testimonial-walls'); ?></label>
                        </th>
                        <td>
                            <label>
                                <input type="checkbox" id="lazy_loading" name="lazy_loading" value="1" 
                                       <?php checked($options['lazy_loading'] ?? true); ?> />
                                <?php _e('Enable lazy loading for testimonial images', 'wp-testimonial-walls'); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="rtl_support"><?php _e('RTL Support', 'wp-testimonial-walls'); ?></label>
                        </th>
                        <td>
                            <label>
                                <input type="checkbox" id="rtl_support" name="rtl_support" value="1" 
                                       <?php checked($options['rtl_support'] ?? true); ?> />
                                <?php _e('Enable RTL (Right-to-Left) language support', 'wp-testimonial-walls'); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="structured_data"><?php _e('Structured Data', 'wp-testimonial-walls'); ?></label>
                        </th>
                        <td>
                            <label>
                                <input type="checkbox" id="structured_data" name="structured_data" value="1" 
                                       <?php checked($options['structured_data'] ?? true); ?> />
                                <?php _e('Add structured data (Schema.org) for SEO', 'wp-testimonial-walls'); ?>
                            </label>
                        </td>
                    </tr>
                </table>
                
                <h2><?php _e('Maintenance', 'wp-testimonial-walls'); ?></h2>
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Clear Cache', 'wp-testimonial-walls'); ?></th>
                        <td>
                            <button type="button" class="button" id="clear-cache">
                                <?php _e('Clear All Cache', 'wp-testimonial-walls'); ?>
                            </button>
                            <p class="description">
                                <?php _e('Clear all cached testimonial wall data.', 'wp-testimonial-walls'); ?>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Cleanup Database', 'wp-testimonial-walls'); ?></th>
                        <td>
                            <button type="button" class="button" id="cleanup-database">
                                <?php _e('Cleanup Orphaned Data', 'wp-testimonial-walls'); ?>
                            </button>
                            <p class="description">
                                <?php _e('Remove orphaned relationships and clean up the database.', 'wp-testimonial-walls'); ?>
                            </p>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(); ?>
            </form>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            $('#clear-cache').on('click', function() {
                if (confirm('<?php _e('Are you sure you want to clear all cache?', 'wp-testimonial-walls'); ?>')) {
                    $.post(ajaxurl, {
                        action: 'testimonial_walls_clear_cache',
                        nonce: '<?php echo wp_create_nonce('testimonial_walls_clear_cache'); ?>'
                    }, function(response) {
                        if (response.success) {
                            alert('<?php _e('Cache cleared successfully!', 'wp-testimonial-walls'); ?>');
                        } else {
                            alert('<?php _e('Error clearing cache.', 'wp-testimonial-walls'); ?>');
                        }
                    });
                }
            });
            
            $('#cleanup-database').on('click', function() {
                if (confirm('<?php _e('Are you sure you want to cleanup the database?', 'wp-testimonial-walls'); ?>')) {
                    $.post(ajaxurl, {
                        action: 'testimonial_walls_cleanup_database',
                        nonce: '<?php echo wp_create_nonce('testimonial_walls_cleanup_database'); ?>'
                    }, function(response) {
                        if (response.success) {
                            alert('<?php _e('Database cleaned up successfully!', 'wp-testimonial-walls'); ?>');
                        } else {
                            alert('<?php _e('Error cleaning up database.', 'wp-testimonial-walls'); ?>');
                        }
                    });
                }
            });
        });
        </script>
        <?php
    }
    
    /**
     * Save settings
     */
    private function save_settings() {
        if (!isset($_POST['testimonial_walls_settings_nonce']) || 
            !wp_verify_nonce($_POST['testimonial_walls_settings_nonce'], 'testimonial_walls_settings')) {
            return;
        }
        
        $options = array(
            'cache_duration' => intval($_POST['cache_duration'] ?? 3600),
            'theme_mode' => sanitize_text_field($_POST['theme_mode'] ?? 'auto'),
            'quote_color' => sanitize_hex_color($_POST['quote_color'] ?? '#6A0DAD'),
            'lazy_loading' => isset($_POST['lazy_loading']),
            'rtl_support' => isset($_POST['rtl_support']),
            'structured_data' => isset($_POST['structured_data']),
        );
        
        update_option('wp_testimonial_walls_options', $options);
        
        add_settings_error(
            'testimonial_walls_settings',
            'settings_saved',
            __('Settings saved successfully!', 'wp-testimonial-walls'),
            'success'
        );
    }
    
    /**
     * Admin init
     */
    public function admin_init() {
        // Register AJAX actions
        add_action('wp_ajax_testimonial_walls_clear_cache', array($this, 'ajax_clear_cache'));
        add_action('wp_ajax_testimonial_walls_cleanup_database', array($this, 'ajax_cleanup_database'));
    }
    
    /**
     * AJAX clear cache
     */
    public function ajax_clear_cache() {
        check_ajax_referer('testimonial_walls_clear_cache', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('Insufficient permissions', 'wp-testimonial-walls'));
        }
        
        // Clear all testimonial walls cache
        wp_cache_flush_group('wp_testimonial_walls');
        
        wp_send_json_success();
    }
    
    /**
     * AJAX cleanup database
     */
    public function ajax_cleanup_database() {
        check_ajax_referer('testimonial_walls_cleanup_database', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('Insufficient permissions', 'wp-testimonial-walls'));
        }
        
        $database = new WP_Testimonial_Walls_Database();
        $database->cleanup_orphaned_relationships();
        
        wp_send_json_success();
    }
    
    /**
     * Admin notices
     */
    public function admin_notices() {
        settings_errors('testimonial_walls_settings');
    }
    
    /**
     * Add custom row actions to posts
     */
    public function add_post_row_actions($actions, $post) {
        if ($post->post_type === 'wall' && $post->post_status === 'publish') {
            $actions['preview_wall'] = sprintf(
                '<a href="#" class="preview-wall" data-wall-id="%d">%s</a>',
                $post->ID,
                __('Preview Wall', 'wp-testimonial-walls')
            );
        }
        
        return $actions;
    }
}
