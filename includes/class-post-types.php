<?php
/**
 * Custom Post Types Handler
 *
 * @package WP_Testimonial_Walls
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class WP_Testimonial_Walls_Post_Types
 */
class WP_Testimonial_Walls_Post_Types {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('init', array($this, 'register_post_types'), 0);
        add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
        add_action('save_post', array($this, 'save_meta_boxes'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
    }
    
    /**
     * Register custom post types
     */
    public function register_post_types() {
        $this->register_testimonial_post_type();
        $this->register_wall_post_type();
    }
    
    /**
     * Register testimonial post type
     */
    private function register_testimonial_post_type() {
        $labels = array(
            'name'                  => __('Testimonials', 'wp-testimonial-walls'),
            'singular_name'         => __('Testimonial', 'wp-testimonial-walls'),
            'menu_name'             => __('Testimonials', 'wp-testimonial-walls'),
            'name_admin_bar'        => __('Testimonial', 'wp-testimonial-walls'),
            'add_new'               => __('Add New', 'wp-testimonial-walls'),
            'add_new_item'          => __('Add New Testimonial', 'wp-testimonial-walls'),
            'new_item'              => __('New Testimonial', 'wp-testimonial-walls'),
            'edit_item'             => __('Edit Testimonial', 'wp-testimonial-walls'),
            'view_item'             => __('View Testimonial', 'wp-testimonial-walls'),
            'all_items'             => __('All Testimonials', 'wp-testimonial-walls'),
            'search_items'          => __('Search Testimonials', 'wp-testimonial-walls'),
            'parent_item_colon'     => __('Parent Testimonials:', 'wp-testimonial-walls'),
            'not_found'             => __('No testimonials found.', 'wp-testimonial-walls'),
            'not_found_in_trash'    => __('No testimonials found in Trash.', 'wp-testimonial-walls'),
        );
        
        $args = array(
            'labels'             => $labels,
            'description'        => __('Testimonials for testimonial walls', 'wp-testimonial-walls'),
            'public'             => false,
            'publicly_queryable' => false,
            'show_ui'            => true,
            'show_in_menu'       => false, // Will be handled by admin menu
            'query_var'          => true,
            'rewrite'            => false,
            'capability_type'    => 'post',
            'has_archive'        => false,
            'hierarchical'       => false,
            'menu_position'      => null,
            'menu_icon'          => 'dashicons-format-quote',
            'supports'           => array('title', 'editor', 'thumbnail'),
            'show_in_rest'       => true,
        );
        
        register_post_type('testimonial', $args);
    }
    
    /**
     * Register wall post type
     */
    private function register_wall_post_type() {
        $labels = array(
            'name'                  => __('Testimonial Walls', 'wp-testimonial-walls'),
            'singular_name'         => __('Wall', 'wp-testimonial-walls'),
            'menu_name'             => __('Walls', 'wp-testimonial-walls'),
            'name_admin_bar'        => __('Wall', 'wp-testimonial-walls'),
            'add_new'               => __('Add New', 'wp-testimonial-walls'),
            'add_new_item'          => __('Add New Wall', 'wp-testimonial-walls'),
            'new_item'              => __('New Wall', 'wp-testimonial-walls'),
            'edit_item'             => __('Edit Wall', 'wp-testimonial-walls'),
            'view_item'             => __('View Wall', 'wp-testimonial-walls'),
            'all_items'             => __('All Walls', 'wp-testimonial-walls'),
            'search_items'          => __('Search Walls', 'wp-testimonial-walls'),
            'parent_item_colon'     => __('Parent Walls:', 'wp-testimonial-walls'),
            'not_found'             => __('No walls found.', 'wp-testimonial-walls'),
            'not_found_in_trash'    => __('No walls found in Trash.', 'wp-testimonial-walls'),
        );
        
        $args = array(
            'labels'             => $labels,
            'description'        => __('Testimonial walls containing multiple testimonials', 'wp-testimonial-walls'),
            'public'             => false,
            'publicly_queryable' => false,
            'show_ui'            => true,
            'show_in_menu'       => false, // Will be handled by admin menu
            'query_var'          => true,
            'rewrite'            => false,
            'capability_type'    => 'post',
            'has_archive'        => false,
            'hierarchical'       => false,
            'menu_position'      => null,
            'menu_icon'          => 'dashicons-grid-view',
            'supports'           => array('title'),
            'show_in_rest'       => true,
        );
        
        register_post_type('wall', $args);
    }
    
    /**
     * Add meta boxes
     */
    public function add_meta_boxes() {
        // Testimonial meta boxes
        add_meta_box(
            'testimonial_details',
            __('Testimonial Details', 'wp-testimonial-walls'),
            array($this, 'testimonial_details_callback'),
            'testimonial',
            'normal',
            'high'
        );
        
        // Wall meta boxes
        add_meta_box(
            'wall_settings',
            __('Wall Settings', 'wp-testimonial-walls'),
            array($this, 'wall_settings_callback'),
            'wall',
            'normal',
            'high'
        );
        
        add_meta_box(
            'wall_testimonials',
            __('Testimonials', 'wp-testimonial-walls'),
            array($this, 'wall_testimonials_callback'),
            'wall',
            'normal',
            'high'
        );
        
        add_meta_box(
            'wall_shortcode',
            __('Shortcode', 'wp-testimonial-walls'),
            array($this, 'wall_shortcode_callback'),
            'wall',
            'side',
            'high'
        );
    }
    
    /**
     * Testimonial details meta box callback
     */
    public function testimonial_details_callback($post) {
        wp_nonce_field('testimonial_details_nonce', 'testimonial_details_nonce');
        
        $person_name = get_post_meta($post->ID, '_testimonial_person_name', true);
        $company = get_post_meta($post->ID, '_testimonial_company', true);
        $logo_id = get_post_meta($post->ID, '_testimonial_logo_id', true);
        
        ?>
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="testimonial_person_name"><?php _e('Person Name', 'wp-testimonial-walls'); ?> *</label>
                </th>
                <td>
                    <input type="text" id="testimonial_person_name" name="testimonial_person_name" 
                           value="<?php echo esc_attr($person_name); ?>" class="regular-text" required />
                    <p class="description"><?php _e('Name of the person giving the testimonial (required)', 'wp-testimonial-walls'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="testimonial_company"><?php _e('Company', 'wp-testimonial-walls'); ?></label>
                </th>
                <td>
                    <input type="text" id="testimonial_company" name="testimonial_company" 
                           value="<?php echo esc_attr($company); ?>" class="regular-text" />
                    <p class="description"><?php _e('Company or organization (optional)', 'wp-testimonial-walls'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="testimonial_logo"><?php _e('Company Logo', 'wp-testimonial-walls'); ?></label>
                </th>
                <td>
                    <div class="testimonial-logo-wrapper">
                        <input type="hidden" id="testimonial_logo_id" name="testimonial_logo_id" value="<?php echo esc_attr($logo_id); ?>" />
                        <div class="testimonial-logo-preview">
                            <?php if ($logo_id): ?>
                                <?php echo wp_get_attachment_image($logo_id, 'thumbnail'); ?>
                            <?php endif; ?>
                        </div>
                        <p>
                            <button type="button" class="button testimonial-logo-upload" data-target="testimonial_logo_id">
                                <?php _e('Select Logo', 'wp-testimonial-walls'); ?>
                            </button>
                            <button type="button" class="button testimonial-logo-remove" style="<?php echo $logo_id ? '' : 'display:none;'; ?>">
                                <?php _e('Remove Logo', 'wp-testimonial-walls'); ?>
                            </button>
                        </p>
                        <p class="description"><?php _e('Company logo (optional, recommended size: 200x100px)', 'wp-testimonial-walls'); ?></p>
                    </div>
                </td>
            </tr>
        </table>
        <?php
    }
    
    /**
     * Wall settings meta box callback
     */
    public function wall_settings_callback($post) {
        wp_nonce_field('wall_settings_nonce', 'wall_settings_nonce');
        
        $layout = (get_post_meta($post->ID, '_wall_layout', true) ?: 'grid');
        $columns = (get_post_meta($post->ID, '_wall_columns', true) ?: '3');
        $show_logos = get_post_meta($post->ID, '_wall_show_logos', true) !== '0';
        
        ?>
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="wall_layout"><?php _e('Layout', 'wp-testimonial-walls'); ?></label>
                </th>
                <td>
                    <select id="wall_layout" name="wall_layout">
                        <option value="grid" <?php selected($layout, 'grid'); ?>><?php _e('Grid', 'wp-testimonial-walls'); ?></option>
                        <option value="slider" <?php selected($layout, 'slider'); ?>><?php _e('Slider', 'wp-testimonial-walls'); ?></option>
                        <option value="masonry" <?php selected($layout, 'masonry'); ?>><?php _e('Masonry', 'wp-testimonial-walls'); ?></option>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="wall_columns"><?php _e('Columns', 'wp-testimonial-walls'); ?></label>
                </th>
                <td>
                    <select id="wall_columns" name="wall_columns">
                        <option value="1" <?php selected($columns, '1'); ?>>1</option>
                        <option value="2" <?php selected($columns, '2'); ?>>2</option>
                        <option value="3" <?php selected($columns, '3'); ?>>3</option>
                        <option value="4" <?php selected($columns, '4'); ?>>4</option>
                    </select>
                    <p class="description"><?php _e('Number of columns for grid and masonry layouts', 'wp-testimonial-walls'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="wall_show_logos"><?php _e('Show Company Logos', 'wp-testimonial-walls'); ?></label>
                </th>
                <td>
                    <label>
                        <input type="checkbox" id="wall_show_logos" name="wall_show_logos" value="1" <?php checked($show_logos); ?> />
                        <?php _e('Display company logos when available', 'wp-testimonial-walls'); ?>
                    </label>
                </td>
            </tr>
        </table>
        <?php
    }
    
    /**
     * Wall testimonials meta box callback
     */
    public function wall_testimonials_callback($post) {
        wp_nonce_field('wall_testimonials_nonce', 'wall_testimonials_nonce');
        
        // Get current testimonials for this wall
        global $wpdb;
        $table_name = $wpdb->prefix . 'testimonial_wall_relations';
        
        $current_testimonials = $wpdb->get_col($wpdb->prepare(
            "SELECT testimonial_id FROM {$table_name} WHERE wall_id = %d ORDER BY sort_order ASC",
            $post->ID
        ));
        
        // Get all testimonials
        $testimonials = get_posts(array(
            'post_type' => 'testimonial',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'orderby' => 'title',
            'order' => 'ASC'
        ));
        
        ?>
        <div class="wall-testimonials-manager">
            <div class="testimonials-available">
                <h4><?php _e('Available Testimonials', 'wp-testimonial-walls'); ?></h4>
                <div class="testimonials-list available-list">
                    <?php foreach ($testimonials as $testimonial): ?>
                        <?php if (!in_array($testimonial->ID, $current_testimonials)): ?>
                            <div class="testimonial-item" data-id="<?php echo $testimonial->ID; ?>">
                                <span class="testimonial-title"><?php echo esc_html($testimonial->post_title); ?></span>
                                <button type="button" class="button button-small add-testimonial">
                                    <?php _e('Add', 'wp-testimonial-walls'); ?>
                                </button>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="testimonials-selected">
                <h4><?php _e('Selected Testimonials', 'wp-testimonial-walls'); ?></h4>
                <div class="testimonials-list selected-list sortable">
                    <?php foreach ($current_testimonials as $testimonial_id): ?>
                        <?php $testimonial = get_post($testimonial_id); ?>
                        <?php if ($testimonial): ?>
                            <div class="testimonial-item" data-id="<?php echo $testimonial->ID; ?>">
                                <span class="dashicons dashicons-menu drag-handle"></span>
                                <span class="testimonial-title"><?php echo esc_html($testimonial->post_title); ?></span>
                                <button type="button" class="button button-small remove-testimonial">
                                    <?php _e('Remove', 'wp-testimonial-walls'); ?>
                                </button>
                                <input type="hidden" name="wall_testimonials[]" value="<?php echo $testimonial->ID; ?>" />
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        
        <style>
        .wall-testimonials-manager {
            display: flex;
            gap: 20px;
        }
        .testimonials-available,
        .testimonials-selected {
            flex: 1;
        }
        .testimonials-list {
            border: 1px solid #ddd;
            min-height: 200px;
            padding: 10px;
            background: #f9f9f9;
        }
        .testimonial-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px;
            margin-bottom: 5px;
            background: white;
            border: 1px solid #ddd;
            border-radius: 3px;
        }
        .testimonial-item .testimonial-title {
            flex: 1;
        }
        .drag-handle {
            cursor: move;
            color: #666;
        }
        .sortable .testimonial-item {
            cursor: move;
        }
        </style>
        <?php
    }
    
    /**
     * Wall shortcode meta box callback
     */
    public function wall_shortcode_callback($post) {
        if ($post->ID) {
            ?>
            <p><?php _e('Use this shortcode to display the wall:', 'wp-testimonial-walls'); ?></p>
            <input type="text" readonly value='[wp_testimonial_wall id="<?php echo $post->ID; ?>"]' 
                   class="regular-text" onclick="this.select();" />
            <p class="description"><?php _e('Copy and paste this shortcode into any post or page.', 'wp-testimonial-walls'); ?></p>
            <?php
        } else {
            ?>
            <p><?php _e('Save the wall to get the shortcode.', 'wp-testimonial-walls'); ?></p>
            <?php
        }
    }
    
    /**
     * Save meta boxes data
     */
    public function save_meta_boxes($post_id) {
        // Check if this is an autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        // Check user permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        $post_type = get_post_type($post_id);
        
        if ($post_type === 'testimonial') {
            $this->save_testimonial_meta($post_id);
        } elseif ($post_type === 'wall') {
            $this->save_wall_meta($post_id);
        }
    }
    
    /**
     * Save testimonial meta data
     */
    private function save_testimonial_meta($post_id) {
        if (!isset($_POST['testimonial_details_nonce']) || 
            !wp_verify_nonce($_POST['testimonial_details_nonce'], 'testimonial_details_nonce')) {
            return;
        }
        
        $fields = array(
            'testimonial_person_name' => '_testimonial_person_name',
            'testimonial_company' => '_testimonial_company',
            'testimonial_logo_id' => '_testimonial_logo_id',
        );
        
        foreach ($fields as $field => $meta_key) {
            if (isset($_POST[$field])) {
                update_post_meta($post_id, $meta_key, sanitize_text_field($_POST[$field]));
            }
        }
    }
    
    /**
     * Save wall meta data
     */
    private function save_wall_meta($post_id) {
        if (!isset($_POST['wall_settings_nonce']) || 
            !wp_verify_nonce($_POST['wall_settings_nonce'], 'wall_settings_nonce')) {
            return;
        }
        
        // Save wall settings
        $settings = array(
            'wall_layout' => '_wall_layout',
            'wall_columns' => '_wall_columns',
        );
        
        foreach ($settings as $field => $meta_key) {
            if (isset($_POST[$field])) {
                update_post_meta($post_id, $meta_key, sanitize_text_field($_POST[$field]));
            }
        }
        
        // Save show logos setting
        update_post_meta($post_id, '_wall_show_logos', isset($_POST['wall_show_logos']) ? '1' : '0');
        
        // Save testimonials relationships
        if (isset($_POST['wall_testimonials_nonce']) && 
            wp_verify_nonce($_POST['wall_testimonials_nonce'], 'wall_testimonials_nonce')) {
            $this->save_wall_testimonials($post_id);
        }
    }
    
    /**
     * Save wall testimonials relationships
     */
    private function save_wall_testimonials($post_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'testimonial_wall_relations';
        
        // Delete existing relationships
        $wpdb->delete($table_name, array('wall_id' => $post_id), array('%d'));
        
        // Add new relationships
        if (isset($_POST['wall_testimonials']) && is_array($_POST['wall_testimonials'])) {
            $sort_order = 0;
            foreach ($_POST['wall_testimonials'] as $testimonial_id) {
                $wpdb->insert(
                    $table_name,
                    array(
                        'wall_id' => $post_id,
                        'testimonial_id' => intval($testimonial_id),
                        'sort_order' => $sort_order++
                    ),
                    array('%d', '%d', '%d')
                );
            }
        }
        
        // Clear cache for this wall
        wp_cache_delete("wall_testimonials_{$post_id}", 'wp_testimonial_walls');
    }
    
    /**
     * Enqueue admin scripts
     */
    public function enqueue_admin_scripts($hook) {
        global $post_type;
        
        if (($post_type === 'testimonial' || $post_type === 'wall') && 
            ($hook === 'post.php' || $hook === 'post-new.php')) {
            
            wp_enqueue_media();
            wp_enqueue_script('jquery-ui-sortable');
            
            wp_enqueue_script(
                'wp-testimonial-walls-admin',
                WP_TESTIMONIAL_WALLS_PLUGIN_URL . 'assets/js/admin.js',
                array('jquery', 'jquery-ui-sortable'),
                WP_TESTIMONIAL_WALLS_VERSION,
                true
            );
        }
    }
}
