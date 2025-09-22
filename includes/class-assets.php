<?php
/**
 * Assets Handler
 *
 * @package WP_Testimonial_Walls
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class WP_Testimonial_Walls_Assets
 */
class WP_Testimonial_Walls_Assets {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        add_action('enqueue_block_editor_assets', array($this, 'enqueue_block_editor_assets'));
    }
    
    /**
     * Enqueue frontend assets
     */
    public function enqueue_frontend_assets() {
        // Only enqueue if we have testimonial walls on the page
        if (!$this->should_enqueue_assets()) {
            return;
        }
        
        // CSS
        wp_enqueue_style(
            'wp-testimonial-walls-frontend',
            WP_TESTIMONIAL_WALLS_PLUGIN_URL . 'assets/css/frontend.css',
            array(),
            WP_TESTIMONIAL_WALLS_VERSION
        );
        
        // RTL support
        $options = get_option('wp_testimonial_walls_options', array());
        if ($options['rtl_support'] ?? true) {
            wp_style_add_data('wp-testimonial-walls-frontend', 'rtl', 'replace');
        }
        
        // JavaScript
        wp_enqueue_script(
            'wp-testimonial-walls-frontend',
            WP_TESTIMONIAL_WALLS_PLUGIN_URL . 'assets/js/frontend.js',
            array('jquery'),
            WP_TESTIMONIAL_WALLS_VERSION,
            true
        );
        
        // Localize script
        wp_localize_script('wp-testimonial-walls-frontend', 'wpTestimonialWalls', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wp_testimonial_walls_nonce'),
            'strings' => array(
                'loading' => __('Loading...', 'wp-testimonial-walls'),
                'error' => __('Error loading testimonials', 'wp-testimonial-walls'),
                'prev' => __('Previous', 'wp-testimonial-walls'),
                'next' => __('Next', 'wp-testimonial-walls'),
                'pause' => __('Pause', 'wp-testimonial-walls'),
                'play' => __('Play', 'wp-testimonial-walls'),
            )
        ));
    }
    
    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook) {
        global $post_type;
        
        // Only load on testimonial walls pages
        if (!in_array($post_type, array('testimonial', 'wall')) && 
            !in_array($hook, array('testimonial-walls_page_testimonial-walls-settings'))) {
            return;
        }
        
        // CSS
        wp_enqueue_style(
            'wp-testimonial-walls-admin',
            WP_TESTIMONIAL_WALLS_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            WP_TESTIMONIAL_WALLS_VERSION
        );
        
        // JavaScript for post edit pages
        if (in_array($hook, array('post.php', 'post-new.php'))) {
            wp_enqueue_media();
            wp_enqueue_script('jquery-ui-sortable');
            
            wp_enqueue_script(
                'wp-testimonial-walls-admin',
                WP_TESTIMONIAL_WALLS_PLUGIN_URL . 'assets/js/admin.js',
                array('jquery', 'jquery-ui-sortable'),
                WP_TESTIMONIAL_WALLS_VERSION,
                true
            );
            
            wp_localize_script('wp-testimonial-walls-admin', 'wpTestimonialWallsAdmin', array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('wp_testimonial_walls_admin_nonce'),
                'strings' => array(
                    'selectLogo' => __('Select Logo', 'wp-testimonial-walls'),
                    'removeLogo' => __('Remove Logo', 'wp-testimonial-walls'),
                    'confirmRemove' => __('Are you sure you want to remove this testimonial from the wall?', 'wp-testimonial-walls'),
                )
            ));
        }
    }
    
    /**
     * Enqueue block editor assets
     */
    public function enqueue_block_editor_assets() {
        wp_enqueue_script(
            'wp-testimonial-walls-blocks',
            WP_TESTIMONIAL_WALLS_PLUGIN_URL . 'assets/js/blocks.js',
            array('wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n'),
            WP_TESTIMONIAL_WALLS_VERSION,
            true
        );
        
        wp_enqueue_style(
            'wp-testimonial-walls-blocks',
            WP_TESTIMONIAL_WALLS_PLUGIN_URL . 'assets/css/blocks.css',
            array('wp-edit-blocks'),
            WP_TESTIMONIAL_WALLS_VERSION
        );
        
        wp_localize_script('wp-testimonial-walls-blocks', 'wpTestimonialWallsBlocks', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wp_testimonial_walls_blocks_nonce'),
        ));
    }
    
    /**
     * Check if we should enqueue assets
     */
    private function should_enqueue_assets() {
        global $post;
        
        // Always enqueue on single wall pages
        if (is_singular('wall')) {
            return true;
        }
        
        // Check if current post/page contains shortcode
        if ($post && has_shortcode($post->post_content, 'wp_testimonial_wall')) {
            return true;
        }
        
        // Check if any widget contains shortcode
        if (is_active_widget(false, false, 'text')) {
            $widget_options = get_option('widget_text');
            if (is_array($widget_options)) {
                foreach ($widget_options as $widget) {
                    if (isset($widget['text']) && has_shortcode($widget['text'], 'wp_testimonial_wall')) {
                        return true;
                    }
                }
            }
        }
        
        // Check for Gutenberg blocks
        if ($post && has_block('wp-testimonial-walls/wall', $post)) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Get inline styles for wall
     */
    public function get_wall_inline_styles($wall_id) {
        $layout = (get_post_meta($wall_id, '_wall_layout', true) ?: 'grid');
        $columns = (get_post_meta($wall_id, '_wall_columns', true) ?: '3');
        
        $styles = array();
        
        if ($layout === 'grid' || $layout === 'masonry') {
            $styles[] = "--testimonial-columns: {$columns}";
        }
        
        return implode('; ', $styles);
    }
    
    /**
     * Add critical CSS inline
     */
    public function add_critical_css() {
        if (!$this->should_enqueue_assets()) {
            return;
        }
        
        $critical_css = '
        .wp-testimonial-wall{display:block;margin:2rem 0}
        .wp-testimonial-wall__item{background:#fff;border:1px solid #e1e1e1;border-radius:8px;padding:1.5rem;margin-bottom:1rem}
        .wp-testimonial-wall__content{font-style:italic;margin-bottom:1rem;line-height:1.6}
        .wp-testimonial-wall__author{display:flex;align-items:center;gap:1rem}
        .wp-testimonial-wall__name{font-weight:600;margin:0}
        .wp-testimonial-wall__company{color:#666;font-size:0.9em;margin:0}
        .wp-testimonial-wall__logo{width:auto;height:40px;object-fit:contain}
        ';
        
        wp_add_inline_style('wp-testimonial-walls-frontend', $critical_css);
    }
    
    /**
     * Preload critical resources
     */
    public function preload_resources() {
        if (!$this->should_enqueue_assets()) {
            return;
        }
        
        // Preload CSS
        echo '<link rel="preload" href="' . WP_TESTIMONIAL_WALLS_PLUGIN_URL . 'assets/css/frontend.css" as="style" onload="this.onload=null;this.rel=\'stylesheet\'">' . "\n";
        
        // Preload JavaScript
        echo '<link rel="preload" href="' . WP_TESTIMONIAL_WALLS_PLUGIN_URL . 'assets/js/frontend.js" as="script">' . "\n";
    }
}
