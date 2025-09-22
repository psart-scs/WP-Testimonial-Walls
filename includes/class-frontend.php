<?php
/**
 * Frontend Handler
 *
 * @package WP_Testimonial_Walls
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class WP_Testimonial_Walls_Frontend
 */
class WP_Testimonial_Walls_Frontend {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('wp_head', array($this, 'add_structured_data'));
        add_action('wp_footer', array($this, 'add_inline_scripts'));
        add_filter('body_class', array($this, 'add_body_classes'));
    }
    
    /**
     * Add structured data to head
     */
    public function add_structured_data() {
        global $post;
        
        if (!$post || !has_shortcode($post->post_content, 'wp_testimonial_wall')) {
            return;
        }
        
        $options = get_option('wp_testimonial_walls_options', array());
        if (!($options['structured_data'] ?? true)) {
            return;
        }
        
        // Extract wall IDs from shortcodes
        $pattern = get_shortcode_regex(array('wp_testimonial_wall'));
        preg_match_all('/' . $pattern . '/s', $post->post_content, $matches);
        
        if (empty($matches[3])) {
            return;
        }
        
        $structured_data = array();
        
        foreach ($matches[3] as $shortcode_atts) {
            $atts = shortcode_parse_atts($shortcode_atts);
            $wall_id = intval($atts['id'] ?? 0);
            
            if (!$wall_id) {
                continue;
            }
            
            $wall = get_post($wall_id);
            if (!$wall || $wall->post_type !== 'wall' || $wall->post_status !== 'publish') {
                continue;
            }
            
            $database = new WP_Testimonial_Walls_Database();
            $testimonials = $database->get_wall_testimonials($wall_id);
            
            if (empty($testimonials)) {
                continue;
            }
            
            $wall_data = array(
                '@context' => 'https://schema.org',
                '@type' => 'ItemList',
                'name' => $wall->post_title,
                'numberOfItems' => count($testimonials),
                'itemListElement' => array(),
            );
            
            foreach ($testimonials as $index => $testimonial) {
                $person_name = get_post_meta($testimonial->ID, '_testimonial_person_name', true);
                $company = get_post_meta($testimonial->ID, '_testimonial_company', true);
                
                $review_data = array(
                    '@type' => 'Review',
                    'position' => $index + 1,
                    'reviewBody' => strip_tags($testimonial->post_content),
                    'author' => array(
                        '@type' => 'Person',
                        'name' => $person_name,
                    ),
                    'reviewRating' => array(
                        '@type' => 'Rating',
                        'ratingValue' => 5,
                        'bestRating' => 5,
                    ),
                );
                
                if ($company) {
                    $review_data['author']['worksFor'] = array(
                        '@type' => 'Organization',
                        'name' => $company,
                    );
                }
                
                $wall_data['itemListElement'][] = $review_data;
            }
            
            $structured_data[] = $wall_data;
        }
        
        if (!empty($structured_data)) {
            foreach ($structured_data as $data) {
                echo '<script type="application/ld+json">' . wp_json_encode($data, JSON_UNESCAPED_SLASHES) . '</script>' . "\n";
            }
        }
    }
    
    /**
     * Add inline scripts for enhanced functionality
     */
    public function add_inline_scripts() {
        if (!wp_script_is('wp-testimonial-walls-frontend', 'enqueued')) {
            return;
        }
        
        $options = get_option('wp_testimonial_walls_options', array());
        
        $config = array(
            'autoplay' => true,
            'autoplayDelay' => 5000,
            'pauseOnHover' => true,
            'keyboardNavigation' => true,
            'touchSwipe' => true,
            'lazyLoading' => $options['lazy_loading'] ?? true,
        );
        
        ?>
        <script type="text/javascript">
        if (typeof wpTestimonialWalls !== 'undefined') {
            wpTestimonialWalls.config = <?php echo wp_json_encode($config); ?>;
        }
        </script>
        <?php
    }
    
    /**
     * Add body classes
     */
    public function add_body_classes($classes) {
        global $post;
        
        if ($post && has_shortcode($post->post_content, 'wp_testimonial_wall')) {
            $classes[] = 'has-testimonial-walls';
        }
        
        if (is_singular('wall')) {
            $classes[] = 'single-testimonial-wall';
        }
        
        return $classes;
    }
    
    /**
     * Get wall data for JavaScript
     */
    public function get_wall_js_data($wall_id) {
        $wall = get_post($wall_id);
        if (!$wall) {
            return array();
        }
        
        $database = new WP_Testimonial_Walls_Database();
        $testimonials = $database->get_wall_testimonials($wall_id);
        
        return array(
            'id' => $wall_id,
            'layout' => (get_post_meta($wall_id, '_wall_layout', true) ?: 'grid'),
            'columns' => (intval(get_post_meta($wall_id, '_wall_columns', true)) ?: 3),
            'testimonials_count' => count($testimonials),
        );
    }
}
