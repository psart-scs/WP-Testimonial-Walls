<?php
/**
 * Shortcode Handler
 *
 * @package WP_Testimonial_Walls
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class WP_Testimonial_Walls_Shortcode
 */
class WP_Testimonial_Walls_Shortcode {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_shortcode('wp_testimonial_wall', array($this, 'render_shortcode'));
    }
    
    /**
     * Render shortcode
     *
     * @param array $atts Shortcode attributes
     * @return string HTML output
     */
    public function render_shortcode($atts) {
        $atts = shortcode_atts(array(
            'id' => '',
            'layout' => '',
            'columns' => '',
            'show_logos' => '',
            'class' => '',
        ), $atts, 'wp_testimonial_wall');
        
        // Validate wall ID
        if (empty($atts['id']) || !is_numeric($atts['id'])) {
            return '<p class="wp-testimonial-wall-error">' . 
                   __('Error: Wall ID is required and must be numeric.', 'wp-testimonial-walls') . 
                   '</p>';
        }
        
        $wall_id = intval($atts['id']);
        $wall = get_post($wall_id);
        
        if (!$wall || $wall->post_type !== 'wall' || $wall->post_status !== 'publish') {
            return '<p class="wp-testimonial-wall-error">' . 
                   __('Error: Wall not found or not published.', 'wp-testimonial-walls') . 
                   '</p>';
        }
        
        // Get wall settings
        $layout = !empty($atts['layout']) ? $atts['layout'] : (get_post_meta($wall_id, '_wall_layout', true) ?: 'grid');
        $columns = !empty($atts['columns']) ? intval($atts['columns']) : (intval(get_post_meta($wall_id, '_wall_columns', true)) ?: 3);
        $show_logos = !empty($atts['show_logos']) ? ($atts['show_logos'] === 'true' || $atts['show_logos'] === '1') : (get_post_meta($wall_id, '_wall_show_logos', true) !== '0');
        
        // Get testimonials
        $database = new WP_Testimonial_Walls_Database();
        $testimonials = $database->get_wall_testimonials($wall_id);
        
        if (empty($testimonials)) {
            return '<p class="wp-testimonial-wall-empty">' . 
                   __('No testimonials found for this wall.', 'wp-testimonial-walls') . 
                   '</p>';
        }
        
        // Generate unique ID for this wall instance
        $instance_id = 'wp-testimonial-wall-' . $wall_id . '-' . wp_rand(1000, 9999);
        
        // Get settings
        $options = get_option('wp_testimonial_walls_options', array());
        $theme_mode = $options['theme_mode'] ?? 'auto';
        $quote_color = $options['quote_color'] ?? '#6A0DAD';
        
        // Build CSS classes
        $css_classes = array(
            'wp-testimonial-wall',
            'wp-testimonial-wall--' . $layout,
            'wp-testimonial-wall--columns-' . $columns,
            'wp-testimonial-wall--' . $theme_mode
        );
        
        if (!empty($atts['class'])) {
            $css_classes[] = sanitize_html_class($atts['class']);
        }
        
        if (!$show_logos) {
            $css_classes[] = 'wp-testimonial-wall--no-logos';
        }
        
        // Start output buffering
        ob_start();
        
        // Add inline CSS for quote color
        echo '<style>
        #' . esc_attr($instance_id) . ' .wp-testimonial-wall__content::before,
        #' . esc_attr($instance_id) . ' .wp-testimonial-wall__content::after {
            color: ' . esc_attr($quote_color) . ' !important;
        }
        </style>';
        
        // Render wall
        $this->render_wall($instance_id, $testimonials, $layout, $columns, $show_logos, $css_classes);
        
        return ob_get_clean();
    }
    
    /**
     * Render wall HTML
     */
    private function render_wall($instance_id, $testimonials, $layout, $columns, $show_logos, $css_classes) {
        $options = get_option('wp_testimonial_walls_options', array());
        $structured_data = $options['structured_data'] ?? true;
        
        ?>
        <div id="<?php echo esc_attr($instance_id); ?>" 
             class="<?php echo esc_attr(implode(' ', $css_classes)); ?>"
             data-layout="<?php echo esc_attr($layout); ?>"
             data-columns="<?php echo esc_attr($columns); ?>"
             <?php if ($structured_data): ?>
             itemscope itemtype="https://schema.org/ItemList"
             <?php endif; ?>>
            
            <?php if ($structured_data): ?>
            <meta itemprop="numberOfItems" content="<?php echo count($testimonials); ?>">
            <?php endif; ?>
            
            <?php if ($layout === 'slider'): ?>
            <div class="wp-testimonial-wall__slider-controls">
                <button type="button" class="wp-testimonial-wall__control wp-testimonial-wall__control--prev" 
                        aria-label="<?php esc_attr_e('Previous testimonial', 'wp-testimonial-walls'); ?>">
                    <span class="screen-reader-text"><?php _e('Previous', 'wp-testimonial-walls'); ?></span>
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z"/>
                    </svg>
                </button>
                <button type="button" class="wp-testimonial-wall__control wp-testimonial-wall__control--next"
                        aria-label="<?php esc_attr_e('Next testimonial', 'wp-testimonial-walls'); ?>">
                    <span class="screen-reader-text"><?php _e('Next', 'wp-testimonial-walls'); ?></span>
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M10 6L8.59 7.41 13.17 12l-4.58 4.59L10 18l6-6z"/>
                    </svg>
                </button>
                <button type="button" class="wp-testimonial-wall__control wp-testimonial-wall__control--play-pause"
                        aria-label="<?php esc_attr_e('Play/Pause slideshow', 'wp-testimonial-walls'); ?>">
                    <span class="wp-testimonial-wall__play-text screen-reader-text"><?php _e('Play', 'wp-testimonial-walls'); ?></span>
                    <span class="wp-testimonial-wall__pause-text screen-reader-text"><?php _e('Pause', 'wp-testimonial-walls'); ?></span>
                    <svg class="wp-testimonial-wall__play-icon" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M8 5v14l11-7z"/>
                    </svg>
                    <svg class="wp-testimonial-wall__pause-icon" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/>
                    </svg>
                </button>
            </div>
            <?php endif; ?>
            
            <div class="wp-testimonial-wall__container">
                <?php foreach ($testimonials as $index => $testimonial): ?>
                    <?php $this->render_testimonial($testimonial, $index, $show_logos, $structured_data, $layout); ?>
                <?php endforeach; ?>
            </div>
            
            <?php if ($layout === 'slider'): ?>
            <div class="wp-testimonial-wall__dots" role="tablist" aria-label="<?php esc_attr_e('Testimonial navigation', 'wp-testimonial-walls'); ?>">
                <?php for ($i = 0; $i < count($testimonials); $i++): ?>
                <button type="button" class="wp-testimonial-wall__dot <?php echo $i === 0 ? 'wp-testimonial-wall__dot--active' : ''; ?>"
                        role="tab" aria-selected="<?php echo $i === 0 ? 'true' : 'false'; ?>"
                        aria-controls="<?php echo esc_attr($instance_id); ?>-testimonial-<?php echo $i; ?>"
                        data-slide="<?php echo $i; ?>">
                    <span class="screen-reader-text">
                        <?php printf(__('Go to testimonial %d', 'wp-testimonial-walls'), $i + 1); ?>
                    </span>
                </button>
                <?php endfor; ?>
            </div>
            <?php endif; ?>
        </div>
        <?php
    }
    
    /**
     * Render individual testimonial
     */
    private function render_testimonial($testimonial, $index, $show_logos, $structured_data, $layout) {
        $person_name = get_post_meta($testimonial->ID, '_testimonial_person_name', true);
        $company = get_post_meta($testimonial->ID, '_testimonial_company', true);
        $logo_id = get_post_meta($testimonial->ID, '_testimonial_logo_id', true);
        
        $options = get_option('wp_testimonial_walls_options', array());
        $lazy_loading = $options['lazy_loading'] ?? true;
        
        $item_classes = array('wp-testimonial-wall__item');
        if ($layout === 'slider') {
            $item_classes[] = $index === 0 ? 'wp-testimonial-wall__item--active' : 'wp-testimonial-wall__item--hidden';
        }
        
        ?>
        <div class="<?php echo esc_attr(implode(' ', $item_classes)); ?>"
             <?php if ($layout === 'slider'): ?>
             id="<?php echo esc_attr('wp-testimonial-wall-' . wp_rand(1000, 9999) . '-testimonial-' . $index); ?>"
             role="tabpanel"
             aria-hidden="<?php echo $index === 0 ? 'false' : 'true'; ?>"
             <?php endif; ?>
             <?php if ($structured_data): ?>
             itemprop="itemListElement" itemscope itemtype="https://schema.org/Review"
             <?php endif; ?>>
            
            <?php if ($structured_data): ?>
            <meta itemprop="position" content="<?php echo $index + 1; ?>">
            <?php endif; ?>
            
            <?php if ($show_logos && $logo_id): ?>
            <div class="wp-testimonial-wall__logo-wrapper">
                <?php
                $logo_attrs = array(
                    'class' => 'wp-testimonial-wall__logo',
                    'alt' => $company ? sprintf(__('%s logo', 'wp-testimonial-walls'), esc_attr($company)) : '',
                );
                
                if ($lazy_loading) {
                    $logo_attrs['loading'] = 'lazy';
                }
                
                echo wp_get_attachment_image($logo_id, 'testimonial_logo', false, $logo_attrs);
                ?>
            </div>
            <?php endif; ?>
            
            <div class="wp-testimonial-wall__content" 
                 <?php if ($structured_data): ?>itemprop="reviewBody"<?php endif; ?>>
                <?php echo wp_kses_post(wpautop($testimonial->post_content)); ?>
            </div>
            
            <div class="wp-testimonial-wall__author"
                 <?php if ($structured_data): ?>itemprop="author" itemscope itemtype="https://schema.org/Person"<?php endif; ?>>
                
                <div class="wp-testimonial-wall__author-info">
                    <?php if ($company): ?>
                    <h3 class="wp-testimonial-wall__company"
                       <?php if ($structured_data): ?>itemprop="worksFor" itemscope itemtype="https://schema.org/Organization"<?php endif; ?>>
                        <?php if ($structured_data): ?><span itemprop="name"><?php endif; ?>
                        <?php echo esc_html($company); ?>
                        <?php if ($structured_data): ?></span><?php endif; ?>
                    </h3>
                    <?php endif; ?>
                    
                    <?php if ($person_name): ?>
                    <p class="wp-testimonial-wall__name" 
                        <?php if ($structured_data): ?>itemprop="name"<?php endif; ?>>
                        <?php echo esc_html($person_name); ?>
                    </p>
                    <?php endif; ?>
                </div>
            </div>
            
            <?php if ($structured_data): ?>
            <meta itemprop="reviewRating" itemscope itemtype="https://schema.org/Rating">
            <meta itemprop="ratingValue" content="5">
            <meta itemprop="bestRating" content="5">
            <?php endif; ?>
        </div>
        <?php
    }
}
