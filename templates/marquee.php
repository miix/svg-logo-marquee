<?php
/**
 * Template for displaying the SVG logo marquee
 *
 * @var array $logos Array of logo post objects
 * @var string $light_color Global light mode color from shortcode
 * @var string $dark_color Global dark mode color from shortcode
 * @var string $size Size from shortcode
 * @var string $pause_on_hover Pause animation on hover
 * @var string $reverse Animation direction
 * @var string $gap Space between logos
 * @var string $duplicate Whether to duplicate logos
 */

if (empty($logos)) {
    return;
}

function process_svg($svg_code, $size, $logo_id, $global_light_color = null, $global_dark_color = null)
{
    // Validate SVG input
    if (empty($svg_code) || stripos($svg_code, '<svg') === false) {
        // error_log(sprintf('Invalid SVG code for logo ID: %d', $logo_id));
        return '';
    }

    try {
        // Get individual logo colors if set, unless global colors are provided
        $logo_light_color = $global_light_color ?: (get_post_meta($logo_id, '_svg_logo_marquee_light_color', true) ?: SVG_LOGO_MARQUEE_DEFAULT_LIGHT);
        $logo_dark_color = $global_dark_color ?: (get_post_meta($logo_id, '_svg_logo_marquee_dark_color', true) ?: SVG_LOGO_MARQUEE_DEFAULT_DARK);

        // Convert size to pixels if it's not already
        $size_px = preg_replace('/[^0-9.]/', '', $size);
        $size_style = sprintf('width: %spx; height: %spx;', esc_attr($size_px), esc_attr($size_px));

        // Better viewBox handling
        if (strpos($svg_code, 'viewBox') === false) {
            // Try to extract width/height from SVG
            preg_match('/width="([^"]*)"/', $svg_code, $width_matches);
            preg_match('/height="([^"]*)"/', $svg_code, $height_matches);

            $viewBox = '0 0 1200 1200'; // Default
            if (!empty($width_matches[1]) && !empty($height_matches[1])) {
                $viewBox = sprintf(
                    '0 0 %s %s',
                    preg_replace('/[^0-9.]/', '', $width_matches[1]),
                    preg_replace('/[^0-9.]/', '', $height_matches[1])
                );
            }

            $svg_code = str_replace('<svg', '<svg viewBox="' . $viewBox . '"', $svg_code);
        }

        // Add performance attributes and preserveAspectRatio
        $svg_code = str_replace(
            '<svg',
            sprintf('<svg class="theme-aware-svg" style="%s" loading="lazy" decoding="async" preserveAspectRatio="xMidYMid meet"', $size_style),
            $svg_code
        );

        // Add theme-aware class to all relevant SVG elements
        $svg_code = str_replace([
            '<path d="',
            '<circle ',
            '<rect ',
            '<polygon ',
            '<polyline '
        ], [
            '<path class="theme-aware-path" d="',
            '<circle class="theme-aware-path" ',
            '<rect class="theme-aware-path" ',
            '<polygon class="theme-aware-path" ',
            '<polyline class="theme-aware-path" '
        ], $svg_code);

        // More thorough fill attribute removal
        $svg_code = preg_replace('/(?<=\s)fill="[^"]*"/', '', $svg_code);
        $svg_code = preg_replace('/(?<=\s)fill:\s*[^;"]*[;"]/', '', $svg_code);

        // Get logo title for accessibility
        $logo_title = get_the_title($logo_id);

        // Get interaction type and related data
        $interaction_type = get_post_meta($logo_id, '_svg_logo_marquee_interaction_type', true) ?: 'popover';
        $logo_url = get_post_meta($logo_id, '_svg_logo_marquee_url', true);
        $logo_target = get_post_meta($logo_id, '_svg_logo_marquee_url_target', true) ?: '_self';
        $popover_content = get_post_meta($logo_id, '_svg_logo_marquee_popover_content', true);
        
        // Base wrapper with common attributes
        $base_style = sprintf('--logo-light-color: %s; --logo-dark-color: %s;', 
            esc_attr($logo_light_color), 
            esc_attr($logo_dark_color)
        );
        
        // Handle different interaction types
        if ($interaction_type === 'link' && !empty($logo_url)) {
            // Use link
            $wrapper_template = '<a href="%s" target="%s" rel="noopener" class="svg-logo-wrapper svg-logo-link" style="%s" aria-label="%s">%s</a>';
            $wrapper = sprintf(
                $wrapper_template,
                esc_url($logo_url),
                esc_attr($logo_target),
                $base_style,
                esc_attr($logo_title),
                $svg_code
            );
        } else if (!empty($popover_content)) {
            // Use popover
            $wrapper_template = '<div class="svg-logo-wrapper" style="%s" role="img" aria-label="%s" data-bs-toggle="popover" data-bs-html="true" data-bs-content="%s">%s<i class="bi bi-info-circle-fill info-icon text-primary"></i>';
            $wrapper = sprintf(
                $wrapper_template,
                $base_style,
                esc_attr($logo_title),
                esc_attr($popover_content),
                $svg_code
            );
        } else {
            // No interaction
            $wrapper_template = '<div class="svg-logo-wrapper" style="%s" role="img" aria-label="%s">%s';
            $wrapper = sprintf(
                $wrapper_template,
                $base_style,
                esc_attr($logo_title),
                $svg_code
            );
        }

        // Only add closing div tag when the wrapper is a div (not for links)
        if ($interaction_type !== 'link' || empty($logo_url)) {
            $wrapper .= '</div>';
        }
        
        return $wrapper;

    } catch (Exception $e) {
        // error_log(sprintf('Error processing SVG for logo ID %d: %s', $logo_id, $e->getMessage()));
        return '';
    }
}

// Process SVGs once
$processed_logos = array();
foreach ($logos as $logo) {
    $svg_code = get_post_meta($logo->ID, '_svg_logo_marquee_code', true);
    if (!empty($svg_code)) {
        $processed_logos[] = process_svg($svg_code, $size, $logo->ID, $light_color, $dark_color);
    }
}

if (empty($processed_logos)) {
    return;
}

// Build style string with all animation options
$marquee_style = sprintf(
    '--marquee-duration: %sms; --marquee-gap: %spx; %s',
    esc_attr($speed),
    esc_attr($gap),
    $reverse === 'true' ? '--marquee-direction: reverse;' : ''
);

// Add pause on hover class to container if enabled
$container_class = 'svg-marquee-container';
if ($pause_on_hover === 'true') {
    $container_class .= ' pause-on-hover';
}
if ($duplicate === 'false') {
    $container_class .= ' no-duplicate';
}
?>
<div class="<?php echo esc_attr($container_class); ?>">
    <div class="svg-marquee" id="marquee" style="<?php echo $marquee_style; ?>">
        <?php
        // Output original set
        foreach ($processed_logos as $svg): ?>
            <div class="svg-logo"><?php echo $svg; ?></div>
        <?php endforeach;

        // Add initial duplicates (minimum 2 sets) if duplication is enabled
        if ($duplicate !== 'false'):
            for ($i = 0; $i < 2; $i++):
                foreach ($processed_logos as $svg): ?>
                    <div class="svg-logo"><?php echo $svg; ?></div>
                <?php endforeach;
            endfor;
        endif;
        ?>
    </div>
</div>