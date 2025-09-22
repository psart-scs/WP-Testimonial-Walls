<?php
/**
 * Gutenberg Block Handler
 *
 * @package WP_Testimonial_Walls
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class WP_Testimonial_Walls_Gutenberg_Block
 */
class WP_Testimonial_Walls_Gutenberg_Block {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('init', array($this, 'register_block'));
        add_action('wp_ajax_get_wall_preview', array($this, 'ajax_get_wall_preview'));
        add_action('wp_ajax_nopriv_get_wall_preview', array($this, 'ajax_get_wall_preview'));
    }
    
    /**
     * Register Gutenberg block
     */
    public function register_block() {
        if (!function_exists('register_block_type')) {
            return;
        }
        
        register_block_type('wp-testimonial-walls/wall', array(
            'attributes' => array(
                'wallId' => array(
                    'type' => 'number',
                    'default' => 0,
                ),
                'layout' => array(
                    'type' => 'string',
                    'default' => '',
                ),
                'columns' => array(
                    'type' => 'number',
                    'default' => 0,
                ),
                'showLogos' => array(
                    'type' => 'boolean',
                    'default' => true,
                ),
                'className' => array(
                    'type' => 'string',
                    'default' => '',
                ),
            ),
            'render_callback' => array($this, 'render_block'),
            'editor_script' => 'wp-testimonial-walls-blocks',
            'editor_style' => 'wp-testimonial-walls-blocks',
        ));
    }
    
    /**
     * Render block
     */
    public function render_block($attributes) {
        if (empty($attributes['wallId'])) {
            return '<p class="wp-testimonial-wall-error">' . 
                   __('Please select a testimonial wall to display.', 'wp-testimonial-walls') . 
                   '</p>';
        }
        
        $shortcode_atts = array(
            'id' => $attributes['wallId'],
        );
        
        if (!empty($attributes['layout'])) {
            $shortcode_atts['layout'] = $attributes['layout'];
        }
        
        if (!empty($attributes['columns'])) {
            $shortcode_atts['columns'] = $attributes['columns'];
        }
        
        if (isset($attributes['showLogos'])) {
            $shortcode_atts['show_logos'] = $attributes['showLogos'] ? 'true' : 'false';
        }
        
        if (!empty($attributes['className'])) {
            $shortcode_atts['class'] = $attributes['className'];
        }
        
        $shortcode = new WP_Testimonial_Walls_Shortcode();
        return $shortcode->render_shortcode($shortcode_atts);
    }
    
    /**
     * AJAX get wall preview
     */
    public function ajax_get_wall_preview() {
        check_ajax_referer('wp_testimonial_walls_blocks_nonce', 'nonce');
        
        $wall_id = intval($_POST['wall_id'] ?? 0);
        
        if (!$wall_id) {
            wp_send_json_error(__('Invalid wall ID', 'wp-testimonial-walls'));
        }
        
        $wall = get_post($wall_id);
        
        if (!$wall || $wall->post_type !== 'wall') {
            wp_send_json_error(__('Wall not found', 'wp-testimonial-walls'));
        }
        
        $database = new WP_Testimonial_Walls_Database();
        $testimonials = $database->get_wall_testimonials($wall_id);
        
        $preview_data = array(
            'id' => $wall_id,
            'title' => $wall->post_title,
            'layout' => (get_post_meta($wall_id, '_wall_layout', true) ?: 'grid'),
            'columns' => (intval(get_post_meta($wall_id, '_wall_columns', true)) ?: 3),
            'show_logos' => get_post_meta($wall_id, '_wall_show_logos', true) !== '0',
            'testimonials_count' => count($testimonials),
            'testimonials' => array(),
        );
        
        // Get first 3 testimonials for preview
        $preview_testimonials = array_slice($testimonials, 0, 3);
        
        foreach ($preview_testimonials as $testimonial) {
            $person_name = get_post_meta($testimonial->ID, '_testimonial_person_name', true);
            $company = get_post_meta($testimonial->ID, '_testimonial_company', true);
            
            $preview_data['testimonials'][] = array(
                'id' => $testimonial->ID,
                'content' => wp_trim_words(strip_tags($testimonial->post_content), 20),
                'person_name' => $person_name,
                'company' => $company,
            );
        }
        
        wp_send_json_success($preview_data);
    }
}
