<?php
/**
 * The style guide page: one shortcode, one creator, noindex.
 */
defined( 'ABSPATH' ) || exit;

const WSGP_PAGE_META = '_wsgp_page';

function wsgp_page_content(): string {
	return "<!-- wp:shortcode -->\n[weave_style_guide]\n<!-- /wp:shortcode -->";
}

/**
 * Create or refresh the style guide page. Returns the page ID, or a WP_Error when the slug belongs to
 * a page this plugin did not make (the hand-built /styles/ pages on older sites, for one).
 */
function wsgp_create_page( string $slug = 'style-guide', bool $refresh = false ): int|WP_Error {
	$existing = get_page_by_path( $slug );
	if ( $existing && ! get_post_meta( $existing->ID, WSGP_PAGE_META, true ) ) {
		/* translators: 1: page slug, 2: page ID */
		return new WP_Error( 'wsgp_slug_taken', sprintf( __( 'A page at /%1$s/ already exists (ID %2$d) and was not made by this plugin. Pick another slug.', 'weave-style-guide-gp' ), $slug, $existing->ID ) );
	}
	$args = array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => __( 'Style guide', 'weave-style-guide-gp' ), 'post_name' => $slug, 'post_content' => wsgp_page_content() );
	if ( $existing ) {
		if ( $refresh ) { wp_update_post( array_merge( array( 'ID' => $existing->ID ), $args ) ); }
		$id = $existing->ID;
	} else {
		$id = wp_insert_post( $args, true );
		if ( is_wp_error( $id ) ) { return $id; }
	}
	update_post_meta( $id, WSGP_PAGE_META, '1' );
	update_post_meta( $id, '_generate-sidebar-layout-meta', 'no-sidebar' ); // GeneratePress layout: the grids get the whole container
	update_post_meta( $id, '_generate-disable-headline', 'true' ); // the shortcode prints the page title, so GP's would be a second H1
	return (int) $id;
}

function wsgp_noindex(): void {
	if ( is_page() && get_post_meta( get_queried_object_id(), WSGP_PAGE_META, true ) ) {
		echo '<meta name="robots" content="noindex, nofollow">' . "\n";
	}
}
add_action( 'wp_head', 'wsgp_noindex', 1 );

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	WP_CLI::add_command( 'weave-style-guide-gp create', function ( $args, $assoc ) {
		$id = wsgp_create_page( $assoc['slug'] ?? 'style-guide', ! empty( $assoc['refresh'] ) );
		if ( is_wp_error( $id ) ) { WP_CLI::error( $id->get_error_message() ); }
		WP_CLI::success( 'Style guide page ' . $id . ': ' . get_permalink( $id ) );
	} );
}

/**
 * Ability for the build sweep, when weave-abilities (WP Abilities API) is present. Same name as the
 * block-theme plugin's, so one sweep covers both kinds of site.
 */
function wsgp_register_ability(): void {
	if ( ! function_exists( 'wp_register_ability' ) ) { return; }
	if ( function_exists( 'wp_has_ability' ) && wp_has_ability( 'weave/style-guide-create' ) ) { return; }
	if ( function_exists( 'wp_has_ability_category' ) && ! wp_has_ability_category( 'weave-content' ) ) { return; } // weave-abilities registers the category
	wp_register_ability( 'weave/style-guide-create', array(
		'label'       => __( 'Create the style guide page', 'weave-style-guide-gp' ),
		'description' => __( 'Creates or refreshes the noindex style guide page built from the live GeneratePress and Beaver Builder settings.', 'weave-style-guide-gp' ),
		'category'    => 'weave-content',
		'input_schema'  => array( 'type' => 'object', 'properties' => array( 'slug' => array( 'type' => 'string' ), 'refresh' => array( 'type' => 'boolean' ) ) ),
		'output_schema' => array( 'type' => 'object', 'properties' => array( 'id' => array( 'type' => 'integer' ), 'url' => array( 'type' => 'string' ) ) ),
		'execute_callback' => function ( $input ) { $id = wsgp_create_page( $input['slug'] ?? 'style-guide', ! empty( $input['refresh'] ) ); return is_wp_error( $id ) ? $id : array( 'id' => $id, 'url' => get_permalink( $id ) ); },
		'permission_callback' => fn() => current_user_can( 'edit_pages' ),
	) );
}
add_action( 'wp_abilities_api_init', 'wsgp_register_ability' );
