=== SVG Logo Marquee ===
Contributors: miix
Tags: svg, logo, marquee, animation
Requires at least: 5.0
Tested up to: 6.7.2
Stable tag: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Display SVG logos in a seamless marquee.

== Description ==

Display SVG logos in a seamless marquee with customizable settings for animation speed, colors, and more.

The plugin allows you to:
* Create a collection of SVG logos
* Categorize logos for different displays
* Control colors for light and dark modes
* Customize animation speed, direction, and behavior
* Add informational popover content when users hover over logos

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/svg-logo-marquee` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Add logos via the SVG Logos menu item in your admin panel.
4. Place the shortcode `[svg_logo_marquee]` in your content where you want the logo marquee to appear.

== Usage ==

**Basic Usage:**

    [svg_logo_marquee]

**Shortcode Options:**

* `size="80"` - Set logo size (default: 100px)
* `speed="10000"` - Animation duration in milliseconds (default: 20000ms = 20 seconds)
* `light_color="#333333"` - Override all logo colors in light mode (default: #212529)
* `dark_color="#dddddd"` - Override all logo colors in dark mode (default: #ffffff)
* `random="true"` - Display logos in random order (default: false)
* `pause_on_hover="false"` - Pause animation on hover (default: true)
* `reverse="true"` - Reverse animation direction (default: false)
* `gap="20"` - Space between logos in pixels (default: 40)
* `duplicate="false"` - Duplicate logos for seamless scrolling (default: true)
* `category="category-slug"` - Show only logos from specific category (comma-separated for multiple)

**Example:**

    [svg_logo_marquee size="80" speed="10000" category="clients,partners"]

== Adding SVG Logos ==

To add a new logo:

1. Go to SVG Logos → Add New
2. Give your logo a title
3. Paste your SVG code in the SVG Code box

**Example SVG format:**

    <svg xmlns="http://www.w3.org/2000/svg">
      <g>
        <path d="M10,10 L50,10 L50,50 L10,50 Z" />
      </g>
    </svg>

Note: Make sure your SVG includes the xmlns attribute and uses path elements for compatibility.

== Frequently Asked Questions ==

= Does this plugin use the WordPress media library? =
No, SVG logos are directly stored as code.

= Can I categorize my logos? =
Yes, you can create categories and assign logos to them, then use the category parameter in the shortcode to display specific groups of logos.

= Does the marquee support dark mode? =
Yes, you can set different colors for light and dark mode, either globally through the shortcode or individually for each logo.

== Changelog ==

= 1.0.0 =
* Initial release