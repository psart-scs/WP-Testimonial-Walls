<?php
/**
 * Debug plugin loading and post type registration
 */

// WordPress Bootstrap
require_once('../../../wp-config.php');

echo "<h2>Plugin Debug Information</h2>\n";

// Check if plugin is active
$active_plugins = get_option('active_plugins');
$plugin_file = 'WP Testimonial Walls/wp-testimonial-walls.php';
$is_active = in_array($plugin_file, $active_plugins);

echo "<p><strong>Plugin Active:</strong> " . ($is_active ? "✅ Yes" : "❌ No") . "</p>\n";

// Check if main class exists
echo "<p><strong>Main Class Exists:</strong> " . (class_exists('WP_Testimonial_Walls') ? "✅ Yes" : "❌ No") . "</p>\n";

// Check if post type class exists
echo "<p><strong>Post Types Class Exists:</strong> " . (class_exists('WP_Testimonial_Walls_Post_Types') ? "✅ Yes" : "❌ No") . "</p>\n";

// Check all registered post types
$all_post_types = get_post_types(array(), 'objects');
echo "<h3>All Registered Post Types:</h3>\n<ul>\n";
foreach ($all_post_types as $post_type) {
    $show_ui = $post_type->show_ui ? 'show_ui=true' : 'show_ui=false';
    echo "<li><strong>{$post_type->name}</strong> - {$post_type->label} ({$show_ui})</li>\n";
}
echo "</ul>\n";

// Check specifically for our post types
echo "<h3>Our Post Types Status:</h3>\n";
$testimonial_exists = post_type_exists('testimonial');
$wall_exists = post_type_exists('wall');

echo "<p><strong>Testimonial Post Type:</strong> " . ($testimonial_exists ? "✅ Registered" : "❌ Not Registered") . "</p>\n";
echo "<p><strong>Wall Post Type:</strong> " . ($wall_exists ? "✅ Registered" : "❌ Not Registered") . "</p>\n";

// Check current hooks
echo "<h3>Current Init Hooks:</h3>\n";
global $wp_filter;
if (isset($wp_filter['init'])) {
    echo "<ul>\n";
    foreach ($wp_filter['init']->callbacks as $priority => $callbacks) {
        foreach ($callbacks as $callback) {
            $callback_name = '';
            if (is_array($callback['function'])) {
                if (is_object($callback['function'][0])) {
                    $callback_name = get_class($callback['function'][0]) . '::' . $callback['function'][1];
                } else {
                    $callback_name = $callback['function'][0] . '::' . $callback['function'][1];
                }
            } else {
                $callback_name = $callback['function'];
            }
            echo "<li>Priority {$priority}: {$callback_name}</li>\n";
        }
    }
    echo "</ul>\n";
}

// Try to manually register post types
echo "<h3>Manual Registration Test:</h3>\n";
$result1 = register_post_type('test_testimonial', array(
    'public' => false,
    'show_ui' => true,
    'labels' => array('name' => 'Test Testimonials')
));

$result2 = register_post_type('test_wall', array(
    'public' => false,
    'show_ui' => true,
    'labels' => array('name' => 'Test Walls')
));

echo "<p><strong>Manual Registration Result:</strong> " . (is_wp_error($result1) ? "❌ Error: " . $result1->get_error_message() : "✅ Success") . "</p>\n";

// Check if we can access the URLs
echo "<h3>URL Tests:</h3>\n";
echo "<p><a href='/wp-admin/post-new.php?post_type=testimonial' target='_blank'>Test Testimonial URL</a></p>\n";
echo "<p><a href='/wp-admin/post-new.php?post_type=wall' target='_blank'>Test Wall URL</a></p>\n";
echo "<p><a href='/wp-admin/post-new.php?post_type=test_testimonial' target='_blank'>Test Manual Testimonial URL</a></p>\n";
echo "<p><a href='/wp-admin/post-new.php?post_type=test_wall' target='_blank'>Test Manual Wall URL</a></p>\n";

echo "<hr><p><em>Debug completed. Check the results above.</em></p>\n";
?>
