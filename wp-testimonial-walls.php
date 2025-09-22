<?php
/**
 * Plugin Name: WP Testimonial Walls
 * Plugin URI: https://github.com/psart-scs/WP-Testimonial-Walls
 * Description: WordPress-Plugin zum Erstellen und Anzeigen mehrerer Testimonial-Wände. Jede Wall zeigt Aussagen von Personen oder Unternehmen, wobei Name im Vordergrund steht und bei Firmen optional ein Logo erscheint.
 * Version: 1.0.0
 * Author: psart-scs
 * Author URI: https://github.com/psart-scs
 * Text Domain: wp-testimonial-walls
 * Domain Path: /languages
 * Requires at least: 6.0
 * Tested up to: 6.4
 * Requires PHP: 8.1
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Network: false
 *
 * @package WP_Testimonial_Walls
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('WP_TESTIMONIAL_WALLS_VERSION', '1.0.0');
define('WP_TESTIMONIAL_WALLS_PLUGIN_FILE', __FILE__);
define('WP_TESTIMONIAL_WALLS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WP_TESTIMONIAL_WALLS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WP_TESTIMONIAL_WALLS_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Main plugin class
 */
class WP_Testimonial_Walls {
    
    /**
     * Single instance of the plugin
     *
     * @var WP_Testimonial_Walls
     */
    private static $instance = null;
    
    /**
     * Get single instance
     *
     * @return WP_Testimonial_Walls
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        // Set up hooks
        register_activation_hook(WP_TESTIMONIAL_WALLS_PLUGIN_FILE, array($this, 'activate'));
        register_deactivation_hook(WP_TESTIMONIAL_WALLS_PLUGIN_FILE, array($this, 'deactivate'));
        
        // Load dependencies immediately
        $this->load_dependencies();
        
        // Load simple post types file as backup
        require_once WP_TESTIMONIAL_WALLS_PLUGIN_DIR . 'simple-post-types.php';
        
        // Register post types directly in constructor as fallback
        add_action('init', array($this, 'register_post_types_direct'), 0);
        add_action('init', array($this, 'init'), 5);
        add_action('plugins_loaded', array($this, 'load_textdomain'));
    }
    
    /**
     * Initialize plugin
     */
    public function init() {
        // Check if we need to flush rewrite rules
        if (get_option('wp_testimonial_walls_flush_rewrite_rules')) {
            flush_rewrite_rules(true);
            delete_option('wp_testimonial_walls_flush_rewrite_rules');
        }
        
        // Initialize components
        $this->init_components();
    }
    
    /**
     * Load plugin dependencies
     */
    private function load_dependencies() {
        require_once WP_TESTIMONIAL_WALLS_PLUGIN_DIR . 'includes/class-post-types.php';
        require_once WP_TESTIMONIAL_WALLS_PLUGIN_DIR . 'includes/class-database.php';
        require_once WP_TESTIMONIAL_WALLS_PLUGIN_DIR . 'includes/class-admin.php';
        require_once WP_TESTIMONIAL_WALLS_PLUGIN_DIR . 'includes/class-frontend.php';
        require_once WP_TESTIMONIAL_WALLS_PLUGIN_DIR . 'includes/class-shortcode.php';
        require_once WP_TESTIMONIAL_WALLS_PLUGIN_DIR . 'includes/class-gutenberg-block.php';
        require_once WP_TESTIMONIAL_WALLS_PLUGIN_DIR . 'includes/class-assets.php';
        require_once WP_TESTIMONIAL_WALLS_PLUGIN_DIR . 'includes/class-cache.php';
    }
    
    /**
     * Initialize plugin components
     */
    private function init_components() {
        new WP_Testimonial_Walls_Post_Types();
        new WP_Testimonial_Walls_Database();
        new WP_Testimonial_Walls_Admin();
        new WP_Testimonial_Walls_Frontend();
        new WP_Testimonial_Walls_Shortcode();
        new WP_Testimonial_Walls_Gutenberg_Block();
        new WP_Testimonial_Walls_Assets();
        new WP_Testimonial_Walls_Cache();
    }
    
    /**
     * Load plugin textdomain
     */
    public function load_textdomain() {
        load_plugin_textdomain(
            'wp-testimonial-walls',
            false,
            dirname(plugin_basename(__FILE__)) . '/languages/'
        );
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        // Load dependencies first
        $this->load_dependencies();
        
        // Create database tables
        $database = new WP_Testimonial_Walls_Database();
        $database->create_tables();
        
        // Register post types and flush rewrite rules
        $post_types = new WP_Testimonial_Walls_Post_Types();
        $post_types->register_post_types();
        
        // Force flush rewrite rules
        flush_rewrite_rules(true);
        
        // Set default options
        $this->set_default_options();
        
        // Set flag to flush rules on next init
        update_option('wp_testimonial_walls_flush_rewrite_rules', true);
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        flush_rewrite_rules();
    }
    
    /**
     * Direct post type registration as fallback
     */
    public function register_post_types_direct() {
        // Register testimonial post type directly
        register_post_type('testimonial', array(
            'labels' => array(
                'name' => __('Testimonials', 'wp-testimonial-walls'),
                'singular_name' => __('Testimonial', 'wp-testimonial-walls'),
                'add_new' => __('Add New', 'wp-testimonial-walls'),
                'add_new_item' => __('Add New Testimonial', 'wp-testimonial-walls'),
                'edit_item' => __('Edit Testimonial', 'wp-testimonial-walls'),
                'new_item' => __('New Testimonial', 'wp-testimonial-walls'),
                'view_item' => __('View Testimonial', 'wp-testimonial-walls'),
                'search_items' => __('Search Testimonials', 'wp-testimonial-walls'),
                'not_found' => __('No testimonials found', 'wp-testimonial-walls'),
                'not_found_in_trash' => __('No testimonials found in Trash', 'wp-testimonial-walls'),
            ),
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => 'testimonial-walls',
            'supports' => array('title', 'editor', 'thumbnail'),
            'capability_type' => 'post',
            'has_archive' => false,
            'rewrite' => false,
            'show_in_rest' => true,
        ));

        // Register wall post type directly
        register_post_type('wall', array(
            'labels' => array(
                'name' => __('Testimonial Walls', 'wp-testimonial-walls'),
                'singular_name' => __('Wall', 'wp-testimonial-walls'),
                'add_new' => __('Add New', 'wp-testimonial-walls'),
                'add_new_item' => __('Add New Wall', 'wp-testimonial-walls'),
                'edit_item' => __('Edit Wall', 'wp-testimonial-walls'),
                'new_item' => __('New Wall', 'wp-testimonial-walls'),
                'view_item' => __('View Wall', 'wp-testimonial-walls'),
                'search_items' => __('Search Walls', 'wp-testimonial-walls'),
                'not_found' => __('No walls found', 'wp-testimonial-walls'),
                'not_found_in_trash' => __('No walls found in Trash', 'wp-testimonial-walls'),
            ),
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => 'testimonial-walls',
            'supports' => array('title'),
            'capability_type' => 'post',
            'has_archive' => false,
            'rewrite' => false,
            'show_in_rest' => true,
        ));
    }

    /**
     * Set default plugin options
     */
    private function set_default_options() {
        $defaults = array(
            'version' => WP_TESTIMONIAL_WALLS_VERSION,
            'cache_duration' => 3600, // 1 hour
            'lazy_loading' => true,
            'rtl_support' => true,
            'structured_data' => true,
        );
        
        add_option('wp_testimonial_walls_options', $defaults);
    }
}

/**
 * Initialize the plugin
 */
function wp_testimonial_walls_init() {
    return WP_Testimonial_Walls::get_instance();
}

// Start the plugin immediately
wp_testimonial_walls_init();
