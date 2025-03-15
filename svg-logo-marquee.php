<?php
/**
 * Plugin Name: SVG Logo Marquee
 * Plugin URI: https://miix.dev/wp/svg-logo-marquee
 * Description: Display SVG logos in a seamless marquee
 * Version: 1.0
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * Author: Fred Klopper
 * Author URI: https://miix.dev
 * Text Domain: svg-logo-marquee
 * Domain Path: /languages
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

// Prevent direct access to this file
if (!defined('ABSPATH')) {
  exit;
}

// Define plugin constants
define('SVG_LOGO_MARQUEE_VERSION', '1.0');
define('SVG_LOGO_MARQUEE_PLUGIN_FILE', __FILE__);
define('SVG_LOGO_MARQUEE_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('SVG_LOGO_MARQUEE_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('SVG_LOGO_MARQUEE_PLUGIN_URL', plugin_dir_url(__FILE__));
define('SVG_LOGO_MARQUEE_DEFAULT_LIGHT', '#212529');  // Bootstrap dark color
define('SVG_LOGO_MARQUEE_DEFAULT_DARK', '#ffffff');   // White

/**
 * Initialize plugin
 */
function svg_logo_marquee_init()
{
  // Load text domain
  load_plugin_textdomain(
    'svg-logo-marquee',
    false,
    dirname(SVG_LOGO_MARQUEE_PLUGIN_BASENAME) . '/languages/'
  );
}
add_action('plugins_loaded', 'svg_logo_marquee_init');

// Add instructions
function svg_logo_marquee_add_instructions()
{
  $screen = get_current_screen();
  if ($screen->post_type === 'svg_logo_marquee') {
    if ($screen->base === 'edit') {
      // Show shortcode instructions on listing page
      echo '<div class="notice notice-info">
                <h3>Shortcode Usage:</h3>
                <p><code>[svg_logo_marquee]</code> - Basic usage with default settings</p>
                <p>Options:</p>
                <ul>
                    <li><code>size="80"</code> - Set logo size (default: 100px)</li>
                    <li><code>speed="10000"</code> - Animation duration in milliseconds (default: 20000ms = 20 seconds)</li>
                    <li><code>light_color="#333333"</code> - Override all logo colors in light mode (default: #212529)</li>
                    <li><code>dark_color="#dddddd"</code> - Override all logo colors in dark mode (default: #ffffff)</li>
                    <li><code>random="true"</code> - Display logos in random order (default: false)</li>
                    <li><code>pause_on_hover="false"</code> - Pause animation on hover (default: true)</li>
                    <li><code>reverse="true"</code> - Reverse animation direction (default: false)</li>
                    <li><code>gap="20"</code> - Space between logos in pixels (default: 40)</li>
                    <li><code>duplicate="false"</code> - Duplicate logos for seamless scrolling (default: true)</li>
                    <li><code>category="category-slug"</code> - Show only logos from specific category (comma-separated for multiple)</li>
                </ul>
                <p>Example: <code>[svg_logo_marquee size="80" speed="10000" category="clients,partners"]</code></p>
            </div>';
    } else {
      // Show only logo instructions on add/edit page
      echo '<div class="notice notice-info">
                    <p>To add a new logo, give it a title and paste your SVG code in the box below.</p>
                </div>';
    }
  }
}
add_action('admin_notices', 'svg_logo_marquee_add_instructions');

// Create custom post type for SVG logos
function svg_logo_marquee_post_type()
{
  register_post_type(
    'svg_logo_marquee',
    array(
      'labels' => array(
        'name' => __('SVG Logos'),
        'singular_name' => __('SVG Logo'),
        'add_new' => __('Add New Logo'),
        'add_new_item' => __('Add New Logo'),
        'edit_item' => __('Edit Logo'),
        'new_item' => __('New Logo'),
        'view_item' => __('View Logo'),
        'search_items' => __('Search Logos'),
        'not_found' => __('No logos found'),
        'not_found_in_trash' => __('No logos found in Trash'),
        'all_items' => __('All Logos'),
        'menu_name' => __('SVG Logos')
      ),
      'public' => false,
      'show_ui' => true,
      'show_in_menu' => true,
      'has_archive' => false,
      'supports' => array('title', 'page-attributes'),
      'menu_icon' => 'dashicons-images-alt2',
      'hierarchical' => false,
    )
  );
}
add_action('init', 'svg_logo_marquee_post_type');

// Add sorting support
// Add visibility column to admin list
function svg_logo_marquee_add_columns($columns)
{
  $new_columns = array();
  foreach ($columns as $key => $title) {
    if ($key === 'title') { // Add after title
      $new_columns[$key] = $title;
      $new_columns['visibility'] = 'Visible';
    } else {
      $new_columns[$key] = $title;
    }
  }
  return $new_columns;
}
add_filter('manage_svg_logo_marquee_posts_columns', 'svg_logo_marquee_add_columns');

// Fill the visibility column
function svg_logo_marquee_manage_columns($column, $post_id)
{
  if ($column === 'visibility') {
    $is_visible = get_post_meta($post_id, '_svg_logo_marquee_visible', true);
    $is_visible = $is_visible === '' ? '1' : $is_visible; // Default to visible
    echo $is_visible === '1' ? '✓' : '✗';
  }
}
add_action('manage_svg_logo_marquee_posts_custom_column', 'svg_logo_marquee_manage_columns', 10, 2);

// Add custom taxonomy for logo categories
function svg_logo_marquee_category_taxonomy()
{
  register_taxonomy(
    'svg_logo_marquee_category',
    'svg_logo_marquee',
    array(
      'label' => __('Category'),
      'hierarchical' => true,
      'show_ui' => true,
      'show_admin_column' => true,
      'query_var' => true,
      'rewrite' => array('slug' => 'svg-logo-marquee-category'),
    )
  );
}
add_action('init', 'svg_logo_marquee_category_taxonomy');

// Replace both svg_logo_category_filter and svg_logo_category_filter_query functions with:
function svg_logo_marquee_add_taxonomy_filters()
{
  global $typenow;

  // Only add filter to our post type
  if ($typenow === 'svg_logo_marquee') {
    // Display a dropdown for 'svg_logo_marquee_category'
    $tax_slug = 'svg_logo_marquee_category';
    $current_tax_slug = isset($_GET[$tax_slug]) ? $_GET[$tax_slug] : '';
    $tax_obj = get_taxonomy($tax_slug);

    wp_dropdown_categories(array(
      'show_option_all' => __("All Categories"),
      'taxonomy' => $tax_slug,
      'name' => $tax_slug,
      'orderby' => 'name',
      'selected' => $current_tax_slug,
      'hierarchical' => true,
      'show_count' => true,
      'hide_empty' => true,
      'value_field' => 'slug'
    ));
  }
}
add_action('restrict_manage_posts', 'svg_logo_marquee_add_taxonomy_filters');

// Enqueue admin scripts for sorting
function svg_logo_marquee_admin_scripts()
{
  $screen = get_current_screen();
  if ($screen->post_type === 'svg_logo_marquee') {
    // First load all required styles
    wp_enqueue_style('wp-color-picker');
    wp_enqueue_style(
      'svg-logo-marquee-admin',
      plugins_url('assets/css/admin.css', __FILE__),
      array(),
      SVG_LOGO_MARQUEE_VERSION
    );

    // Then load all scripts in correct order
    wp_enqueue_script('jquery');
    wp_enqueue_script('jquery-ui-sortable');
    wp_enqueue_script('wp-color-picker');

    // Load your admin script last with all dependencies
    wp_enqueue_script(
      'svg-logo-marquee-admin',
      plugins_url('assets/js/admin.js', __FILE__),
      array('jquery', 'jquery-ui-sortable', 'wp-color-picker'),
      '1.0',
      true
    );

    // Initialize the color picker in your admin.js file instead of inline
    wp_localize_script('svg-logo-marquee-admin', 'svgLogoMarqueeAdmin', array(
      'nonce' => wp_create_nonce('svg_logo_marquee_order'),
      'ajaxurl' => admin_url('admin-ajax.php')
    ));
  }
}
add_action('admin_enqueue_scripts', 'svg_logo_marquee_admin_scripts');

// Set default admin order
function svg_logo_marquee_set_admin_order($query)
{
  global $pagenow;

  // Check if we're in the admin and looking at our custom post type
  if (
    is_admin() && 'edit.php' === $pagenow &&
    isset($query->query['post_type']) &&
    'svg_logo_marquee' === $query->query['post_type']
  ) {

    // Don't override if user has selected a different ordering
    if (!isset($_GET['orderby'])) {
      $query->set('orderby', 'menu_order');
      $query->set('order', 'ASC');
    }
  }
}
add_action('pre_get_posts', 'svg_logo_marquee_set_admin_order');

// Set default menu order for new logos
function svg_logo_marquee_set_default_order($post_id, $post, $update)
{
  // Only run for new posts of our type
  if (!$update && 'svg_logo_marquee' === $post->post_type) {
    // Get the highest menu_order currently in use
    $highest = get_posts(array(
      'post_type' => 'svg_logo_marquee',
      'posts_per_page' => 1,
      'orderby' => 'menu_order',
      'order' => 'DESC',
      'fields' => 'ids'
    ));

    // Set new post's menu_order to highest + 1
    $new_order = !empty($highest) ? get_post_field('menu_order', $highest[0]) + 1 : 0;

    wp_update_post(array(
      'ID' => $post_id,
      'menu_order' => $new_order
    ));
  }
}
add_action('wp_insert_post', 'svg_logo_marquee_set_default_order', 10, 3);

// Add meta box for SVG code
function svg_logo_marquee_add_svg_meta_box()
{
  add_meta_box(
    'svg_logo_marquee_code',
    'SVG Code',
    'svg_logo_marquee_meta_box_callback',
    'svg_logo_marquee'
  );
}
add_action('add_meta_boxes', 'svg_logo_marquee_add_svg_meta_box');

function svg_logo_marquee_meta_box_callback($post)
{
  $svg_code = get_post_meta($post->ID, '_svg_logo_marquee_code', true);
  $light_color = get_post_meta($post->ID, '_svg_logo_marquee_light_color', true);
  $dark_color = get_post_meta($post->ID, '_svg_logo_marquee_dark_color', true);

  wp_nonce_field('svg_logo_marquee_meta_box', 'svg_logo_marquee_meta_box_nonce');
  ?>
  <textarea name="svg_logo_marquee_code"
    style="width: 100%; height: 200px;"><?php echo esc_textarea($svg_code); ?></textarea>
  <p>Paste your SVG code here.</p>

  <p>
    <label for="svg_logo_marquee_light_color">Light Mode Color:</label>
    <input type="text" class="color-picker" id="svg_logo_marquee_light_color" name="svg_logo_marquee_light_color"
      value="<?php echo esc_attr($light_color ?: SVG_LOGO_MARQUEE_DEFAULT_LIGHT); ?>">
  </p>
  <p>
    <label for="svg_logo_marquee_dark_color">Dark Mode Color:</label>
    <input type="text" class="color-picker" id="svg_logo_marquee_dark_color" name="svg_logo_marquee_dark_color"
      value="<?php echo esc_attr($dark_color ?: SVG_LOGO_MARQUEE_DEFAULT_DARK); ?>">
  </p>
  <?php
}

function svg_logo_marquee_is_valid_svg($svg_code)
{
  if (empty($svg_code)) {
    return false;
  }

  // Basic checks
  if (strpos($svg_code, '<svg') === false || strpos($svg_code, '</svg>') === false) {
    return false;
  }

  // Check for potentially harmful content
  $suspicious = array('javascript:', 'data:', 'alert(', '<script', 'onclick');
  foreach ($suspicious as $pattern) {
    if (stripos($svg_code, $pattern) !== false) {
      return false;
    }
  }

  return true;
}

// Add visibility meta box
function svg_logo_marquee_add_visibility_meta_box()
{
  add_meta_box(
    'svg_logo_marquee_visibility',
    'Logo Visibility',
    'svg_logo_marquee_visibility_meta_box_callback',
    'svg_logo_marquee',
    'side', // Put it in the sidebar
    'high' // High priority to place it at the top
  );
}
add_action('add_meta_boxes', 'svg_logo_marquee_add_visibility_meta_box');

function svg_logo_marquee_visibility_meta_box_callback($post)
{
  $is_visible = get_post_meta($post->ID, '_svg_logo_marquee_visible', true);
  $is_visible = $is_visible === '' ? '1' : $is_visible; // Default to visible

  wp_nonce_field('svg_logo_marquee_visibility_meta_box', 'svg_logo_marquee_visibility_meta_box_nonce');
  ?>
  <p>
    <label class="selectit">
      <input type="checkbox" name="svg_logo_marquee_visible" value="1" <?php checked($is_visible, '1'); ?>>
      <strong>Display this logo in marquee</strong>
    </label>
  </p>
  <p class="description">
    Uncheck this option to temporarily hide this logo from all marquees without deleting it.
  </p>
  <?php
}

// Add TinyMCE popover content meta box
function svg_logo_marquee_add_popover_meta_box()
{
  add_meta_box(
    'svg_logo_marquee_popover',
    'Logo Popover Content',
    'svg_logo_marquee_popover_meta_box_callback',
    'svg_logo_marquee',
    'normal',
    'high'
  );
}
add_action('add_meta_boxes', 'svg_logo_marquee_add_popover_meta_box');

function svg_logo_marquee_popover_meta_box_callback($post)
{
  $popover_content = get_post_meta($post->ID, '_svg_logo_marquee_popover_content', true);
  wp_editor($popover_content, 'svg_logo_marquee_popover_content', array(
    'media_buttons' => false,
    'textarea_rows' => 5,
    'teeny' => true
  ));
  echo '<p>Enter the content to show when users hover over this logo.</p>';
}

// Save meta box data
function svg_logo_marquee_save_meta_box($post_id)
{
  // Check for and verify the main meta box nonce
  if (
    isset($_POST['svg_logo_marquee_meta_box_nonce']) &&
    wp_verify_nonce($_POST['svg_logo_marquee_meta_box_nonce'], 'svg_logo_marquee_meta_box')
  ) {

    // Save SVG code and colors
    if (isset($_POST['svg_logo_marquee_code'])) {
      // Save colors
      if (isset($_POST['svg_logo_marquee_light_color'])) {
        update_post_meta($post_id, '_svg_logo_marquee_light_color', sanitize_hex_color($_POST['svg_logo_marquee_light_color']));
      }
      if (isset($_POST['svg_logo_marquee_dark_color'])) {
        update_post_meta($post_id, '_svg_logo_marquee_dark_color', sanitize_hex_color($_POST['svg_logo_marquee_dark_color']));
      }
      update_post_meta($post_id, '_svg_logo_marquee_code', wp_kses($_POST['svg_logo_marquee_code'], array(
        'svg' => array(
          'xmlns' => array(),
          'viewBox' => array(),
          'width' => array(),
          'height' => array(),
          'fill' => array(),
          'class' => array(),
          'style' => array(),
          'preserveAspectRatio' => array()
        ),
        'path' => array(
          'd' => array(),
          'fill' => array(),
          'stroke' => array(),
          'stroke-width' => array(),
          'style' => array()
        ),
        'g' => array(
          'transform' => array(),
          'style' => array(),
          'fill' => array()
        ),
        'rect' => array(
          'x' => array(),
          'y' => array(),
          'width' => array(),
          'height' => array(),
          'fill' => array(),
          'style' => array()
        ),
        'circle' => array(
          'cx' => array(),
          'cy' => array(),
          'r' => array(),
          'fill' => array(),
          'style' => array()
        ),
        'polygon' => array(
          'points' => array(),
          'fill' => array(),
          'style' => array()
        )
      )));
    }
  }

  // Check for and verify the visibility meta box nonce
  if (
    isset($_POST['svg_logo_marquee_visibility_meta_box_nonce']) &&
    wp_verify_nonce($_POST['svg_logo_marquee_visibility_meta_box_nonce'], 'svg_logo_marquee_visibility_meta_box')
  ) {

    // Save visibility status
    $is_visible = isset($_POST['svg_logo_marquee_visible']) ? '1' : '0';
    update_post_meta($post_id, '_svg_logo_marquee_visible', $is_visible);
  }

  // The popover content saving remains unchanged
  if (isset($_POST['svg_logo_marquee_popover_content'])) {
    update_post_meta(
      $post_id,
      '_svg_logo_marquee_popover_content',
      wp_kses_post($_POST['svg_logo_marquee_popover_content'])
    );
  }
}
add_action('save_post', 'svg_logo_marquee_save_meta_box');

// Enqueue required styles and scripts
function svg_logo_marquee_enqueue_scripts()
{
  wp_enqueue_style(
    'svg-logo-marquee-style',
    plugins_url('assets/css/style.css', __FILE__),
    array(),
    SVG_LOGO_MARQUEE_VERSION
  );

  wp_enqueue_script(
    'svg-logo-marquee-script',
    plugins_url('assets/js/script.js', __FILE__),
    array('jquery'),
    SVG_LOGO_MARQUEE_VERSION,
    true
  );
}
add_action('wp_enqueue_scripts', 'svg_logo_marquee_enqueue_scripts');

// Create shortcode
function svg_logo_marquee_shortcode($atts)
{
  // Generate cache key based on attributes
  $cache_key = 'svg_logo_marquee_' . md5(serialize($atts));
  $cached_output = wp_cache_get($cache_key);

  if ($cached_output !== false) {
    return $cached_output;
  }

  $atts = shortcode_atts(array(
    'light_color' => '',
    'dark_color' => '',
    'size' => '100px',
    'speed' => '20000',
    'random' => 'false',
    'pause_on_hover' => 'true',
    'reverse' => 'false',
    'gap' => '40',
    'duplicate' => 'true',
    'category' => '' // Add category parameter
  ), $atts);

  $args = array(
    'post_type' => 'svg_logo_marquee',
    'posts_per_page' => -1,
    'meta_key' => '_svg_logo_marquee_visible',
    'meta_value' => '1',
  );

  // Add taxonomy query if category is specified
  if (!empty($atts['category'])) {
    $args['tax_query'] = array(
      array(
        'taxonomy' => 'svg_logo_marquee_category',
        'field' => 'slug',
        'terms' => explode(',', $atts['category'])
      )
    );
  }

  // Set order based on random parameter
  if ($atts['random'] === 'true') {
    $args['orderby'] = 'rand';
  } else {
    $args['orderby'] = 'menu_order';
    $args['order'] = 'ASC';
  }

  // Get posts with optimized query
  $logos = get_posts($args);

  // Sanitize values
  $light_color = sanitize_hex_color($atts['light_color']);
  $dark_color = sanitize_hex_color($atts['dark_color']);
  $size = sanitize_text_field($atts['size']);
  $speed = absint($atts['speed']);
  $gap = absint($atts['gap']);
  $reverse = $atts['reverse'];
  $pause_on_hover = $atts['pause_on_hover'];
  $duplicate = $atts['duplicate'];

  ob_start();
  require plugin_dir_path(__FILE__) . 'templates/marquee.php';
  $output = ob_get_clean();

  // Cache the output
  wp_cache_set($cache_key, $output);

  return $output;
}
add_shortcode('svg_logo_marquee', 'svg_logo_marquee_shortcode');

// Add bulk actions
function svg_logo_marquee_add_bulk_actions($bulk_actions)
{
  $bulk_actions['show_logos'] = 'Show Logos';
  $bulk_actions['hide_logos'] = 'Hide Logos';
  return $bulk_actions;
}
add_filter('bulk_actions-edit-svg_logo_marquee', 'svg_logo_marquee_add_bulk_actions');

// Handle bulk actions
function svg_logo_marquee_handle_bulk_actions($redirect_to, $doaction, $post_ids)
{
  if ($doaction !== 'show_logos' && $doaction !== 'hide_logos') {
    return $redirect_to;
  }

  $visibility = ($doaction === 'show_logos') ? '1' : '0';

  foreach ($post_ids as $post_id) {
    update_post_meta($post_id, '_svg_logo_marquee_visible', $visibility);
  }

  $redirect_to = add_query_arg('bulk_toggled_logos', count($post_ids), $redirect_to);
  return $redirect_to;
}
add_filter('handle_bulk_actions-edit-svg_logo_marquee', 'svg_logo_marquee_handle_bulk_actions', 10, 3);

// Show admin notice after bulk action
function svg_logo_marquee_bulk_action_admin_notice()
{
  if (!empty($_REQUEST['bulk_toggled_logos'])) {
    $count = intval($_REQUEST['bulk_toggled_logos']);
    $message = sprintf(
      _n(
        'Visibility updated for %s logo.',
        'Visibility updated for %s logos.',
        $count
      ),
      number_format_i18n($count)
    );
    echo '<div class="updated"><p>' . esc_html($message) . '</p></div>';
  }
}
add_action('admin_notices', 'svg_logo_marquee_bulk_action_admin_notice');

// Handle AJAX order update
function svg_logo_marquee_update_order()
{
  if (!check_ajax_referer('svg_logo_marquee_order', 'nonce', false)) {
    wp_send_json_error('Invalid nonce');
    return;
  }

  $order = $_POST['order'];
  if (!empty($order)) {
    foreach ($order as $position => $id) {
      wp_update_post(array(
        'ID' => $id,
        'menu_order' => $position
      ));
    }
  }
  wp_send_json_success();
}
add_action('wp_ajax_update_svg_logo_marquee_order', 'svg_logo_marquee_update_order');

register_uninstall_hook(__FILE__, 'svg_logo_marquee_uninstall');

function svg_logo_marquee_uninstall()
{
  // Get all logo posts
  $logos = get_posts(array(
    'post_type' => 'svg_logo_marquee',
    'posts_per_page' => -1,
    'fields' => 'ids'
  ));

  // Delete all logos and their meta
  foreach ($logos as $logo_id) {
    wp_delete_post($logo_id, true);
  }

  // Clean up options if you have any
  delete_option('svg_logo_marquee_settings');
}

// No activation setup needed since files are distributed with the plugin

/**
 * Run a one-time migration to update existing data to the new naming convention
 */
function svg_logo_marquee_migrate_data()
{
  global $wpdb;

  // Start transaction to ensure data integrity
  $wpdb->query('START TRANSACTION');

  try {
    // 1. Update post type
    $wpdb->query("
          UPDATE {$wpdb->posts} 
          SET post_type = 'svg_logo_marquee' 
          WHERE post_type = 'svg_logo'
      ");

    // 2. Get all posts that need meta updates
    $posts = $wpdb->get_col("
          SELECT ID FROM {$wpdb->posts} 
          WHERE post_type = 'svg_logo_marquee'
      ");

    if (!empty($posts)) {
      // Define meta keys to migrate
      $meta_keys = array(
        '_svg_code' => '_svg_logo_marquee_code',
        '_svg_light_color' => '_svg_logo_marquee_light_color',
        '_svg_dark_color' => '_svg_logo_marquee_dark_color',
        '_svg_visible' => '_svg_logo_marquee_visible',
        '_svg_popover_content' => '_svg_logo_marquee_popover_content'
      );

      // Process each post
      foreach ($posts as $post_id) {
        foreach ($meta_keys as $old_key => $new_key) {
          // Get the old meta value
          $meta_value = get_post_meta($post_id, $old_key, true);

          // Only proceed if we have a value
          if ($meta_value !== '') {
            // Add the new meta key
            update_post_meta($post_id, $new_key, $meta_value);

            // Delete the old meta key
            delete_post_meta($post_id, $old_key);
          }
        }
      }
    }

    // 3. Update term taxonomy
    $wpdb->query("
          UPDATE {$wpdb->term_taxonomy} 
          SET taxonomy = 'svg_logo_marquee_category' 
          WHERE taxonomy = 'svg_logo_category'
      ");

    // 4. Update options if needed
    $old_options = get_option('svg_logo_settings');
    if ($old_options) {
      update_option('svg_logo_marquee_settings', $old_options);
      delete_option('svg_logo_settings');
    }

    // Commit the transaction
    $wpdb->query('COMMIT');

    // Add an admin notice to inform about successful migration
    add_action('admin_notices', function () {
      echo '<div class="notice notice-success"><p>SVG Logo Marquee data has been successfully migrated to the new naming convention.</p></div>';
    });

    return true;
  } catch (Exception $e) {
    // Rollback the transaction if something went wrong
    $wpdb->query('ROLLBACK');

    // Log error or show admin notice
    add_action('admin_notices', function () use ($e) {
      echo '<div class="notice notice-error"><p>Error migrating SVG Logo Marquee data: ' . esc_html($e->getMessage()) . '</p></div>';
    });

    return false;
  }
}

/**
 * Add migration button to plugin page
 */
function svg_logo_marquee_add_migration_button($links)
{
  // Add migration link
  $migration_link = '<a href="' . wp_nonce_url(admin_url('admin.php?page=svg_logo_marquee_migrate'), 'svg_logo_marquee_migrate_nonce') . '">Migrate Data</a>';
  array_unshift($links, $migration_link);
  return $links;
}
add_filter('plugin_action_links_' . SVG_LOGO_MARQUEE_PLUGIN_BASENAME, 'svg_logo_marquee_add_migration_button');

/**
 * Register admin page for migration
 */
function svg_logo_marquee_register_migration_page()
{
  add_submenu_page(
    null, // Hidden from menu
    'Migrate SVG Logo Data',
    'Migrate SVG Logo Data',
    'manage_options',
    'svg_logo_marquee_migrate',
    'svg_logo_marquee_migration_page_callback'
  );
}
add_action('admin_menu', 'svg_logo_marquee_register_migration_page');

/**
 * Migration page callback
 */
function svg_logo_marquee_migration_page_callback()
{
  // Check nonce
  if (!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], 'svg_logo_marquee_migrate_nonce')) {
    wp_die('Security check failed');
  }

  // Check capabilities
  if (!current_user_can('manage_options')) {
    wp_die('You do not have sufficient permissions to access this page.');
  }

  // Run the migration
  $result = svg_logo_marquee_migrate_data();

  echo '<div class="wrap">';
  echo '<h1>SVG Logo Marquee Data Migration</h1>';

  if ($result) {
    echo '<div class="notice notice-success"><p>Migration completed successfully!</p></div>';
  } else {
    echo '<div class="notice notice-error"><p>Migration failed. Please check the error log for more details.</p></div>';
  }

  echo '<p><a href="' . admin_url('edit.php?post_type=svg_logo_marquee') . '" class="button button-primary">Return to SVG Logos</a></p>';
  echo '</div>';
}