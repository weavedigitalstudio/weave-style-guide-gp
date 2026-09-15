<?php
/**
 * Plugin Name:       Weave Style Guide for GeneratePress
 * Plugin URI:        https://github.com/weavedigitalstudio/weave-style-guide-gp
 * Description:       An auto-generated style guide page for GeneratePress and Beaver Builder sites: Customizer logos, global colours, fonts and typography, the Beaver Builder icon set, forms and contrast, all read from the live site so nothing on the page is typed in.
 * Version:           0.1.2
 * Requires at least: 6.6
 * Requires PHP:      8.1
 * Author:            Weave Digital Studio
 * License:           MIT
 * Text Domain:       weave-style-guide-gp
 * GitHub Plugin URI: weavedigitalstudio/weave-style-guide-gp
 * Primary Branch:    main
 */

defined( 'ABSPATH' ) || exit;

define( 'WSGP_VERSION', '0.1.2' );
define( 'WSGP_FILE', __FILE__ );
define( 'WSGP_DIR', plugin_dir_path( __FILE__ ) );
define( 'WSGP_URL', plugin_dir_url( __FILE__ ) );

require_once WSGP_DIR . 'inc/data.php';
require_once WSGP_DIR . 'inc/colour.php';
require_once WSGP_DIR . 'inc/page.php';
require_once WSGP_DIR . 'inc/admin.php';
require_once WSGP_DIR . 'inc/github-updater.php';
\WeaveStyleGuideGP\Updater\GitHubUpdater::init( __FILE__ );

/** Section slug => heading, in page order. Each renders from sections/<slug>.php, which returns its HTML. */
function wsgp_sections(): array {
	return array(
		'intro'    => '',
		'logo'     => __( 'Logo', 'weave-style-guide-gp' ),
		'colours'  => __( 'Colours', 'weave-style-guide-gp' ),
		'type'     => __( 'Fonts', 'weave-style-guide-gp' ),
		'icons'    => __( 'Icons', 'weave-style-guide-gp' ),
		'forms'    => __( 'Forms', 'weave-style-guide-gp' ),
		'contrast' => __( 'Contrast', 'weave-style-guide-gp' ),
	);
}

function wsgp_register_assets(): void {
	wp_register_style( 'wsgp', WSGP_URL . 'assets/style.css', array(), WSGP_VERSION );
	wp_register_script( 'wsgp-copy', WSGP_URL . 'assets/copy.js', array(), WSGP_VERSION, array( 'strategy' => 'defer' ) );
}
add_action( 'init', 'wsgp_register_assets' );

/**
 * Styles, the copy script and the icon set stylesheets. Icon sets use Beaver Builder's own handle
 * (the set key) and version, so a page that also has Beaver Builder icons loads each set once.
 */
function wsgp_enqueue_assets(): void {
	wp_enqueue_style( 'wsgp' );
	wp_enqueue_script( 'wsgp-copy' );
	foreach ( wsgp_icon_sets() as $set ) {
		if ( $set['stylesheet'] ) { wp_enqueue_style( $set['key'], $set['stylesheet'], array(), defined( 'FL_BUILDER_VERSION' ) ? FL_BUILDER_VERSION : WSGP_VERSION ); }
	}
}

/** Load assets in the head on the guide page so nothing flashes unstyled. A shortcode anywhere else loads them late, in the footer. */
function wsgp_maybe_enqueue(): void {
	$post = get_queried_object();
	if ( is_singular() && $post instanceof WP_Post && ( get_post_meta( $post->ID, WSGP_PAGE_META, true ) || has_shortcode( $post->post_content, 'weave_style_guide' ) ) ) {
		wsgp_enqueue_assets();
	}
}
add_action( 'wp_enqueue_scripts', 'wsgp_maybe_enqueue', 20 );

/**
 * [weave_style_guide] renders the whole guide. [weave_style_guide section="colours"] renders one section
 * (a comma list for several) without its heading, so it can sit in a Beaver Builder layout.
 * form="3" picks the Gravity Form.
 */
function wsgp_shortcode( $atts ): string {
	$atts   = shortcode_atts( array( 'section' => '', 'form' => '0' ), $atts, 'weave_style_guide' );
	$all    = wsgp_sections();
	$single = '' !== trim( $atts['section'] );
	$want   = $single ? array_intersect( array_map( 'trim', explode( ',', $atts['section'] ) ), array_keys( $all ) ) : array_keys( $all );
	wsgp_enqueue_assets();
	$out  = '';
	$page = get_queried_object();
	if ( ! $single && $page instanceof WP_Post && get_post_meta( $page->ID, WSGP_PAGE_META, true ) ) {
		$out .= '<h1 class="wsg-title">' . esc_html( get_the_title( $page ) ) . '</h1>'; // GP's title is switched off on the guide page, so the guide prints it
	}
	foreach ( $want as $slug ) {
		if ( $all[ $slug ] && ! $single ) { $out .= '<h2 class="wsg-h2">' . esc_html( $all[ $slug ] ) . '</h2>'; }
		$out .= wsgp_render_section( $slug, $atts );
	}
	return '<div class="wsg-guide">' . $out . '</div>';
}
add_shortcode( 'weave_style_guide', 'wsgp_shortcode' );

/** One section. $slug is always a key of wsgp_sections(), never raw input. */
function wsgp_render_section( string $slug, array $atts = array() ): string {
	$inner = include WSGP_DIR . "sections/$slug.php";
	return '<section class="wsg wsg-' . esc_attr( $slug ) . '">' . ( is_string( $inner ) ? $inner : '' ) . '</section>';
}
