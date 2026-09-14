<?php
/**
 * The Plugins screen: a link under the plugin's name that views the guide page, or creates it when
 * the site has none. The page reads the site every time it loads, so there is nothing to re-scan.
 */
defined( 'ABSPATH' ) || exit;

/** The guide page this plugin made (any status but trash), or null. */
function wsgp_find_page(): ?WP_Post {
	$ids = get_posts( array( 'post_type' => 'page', 'post_status' => array( 'publish', 'future', 'draft', 'pending', 'private' ), 'meta_key' => WSGP_PAGE_META, 'meta_value' => '1', 'numberposts' => 1, 'fields' => 'ids', 'no_found_rows' => true ) ); // phpcs:ignore WordPress.DB.SlowDBQuery
	return $ids ? get_post( (int) $ids[0] ) : null;
}

/** View the guide, edit it when it isn't published, or create it when there isn't one. */
function wsgp_action_links( array $links ): array {
	if ( ! current_user_can( 'publish_pages' ) ) { return $links; }
	$page = wsgp_find_page();
	if ( $page && 'publish' === $page->post_status ) {
		$link = '<a href="' . esc_url( get_permalink( $page ) ) . '">' . esc_html__( 'View style guide', 'weave-style-guide-gp' ) . '</a>';
	} elseif ( $page ) {
		$link = '<a href="' . esc_url( (string) get_edit_post_link( $page->ID ) ) . '">' . esc_html__( 'Edit style guide page', 'weave-style-guide-gp' ) . '</a>';
	} else {
		$link = '<a href="' . esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=wsgp_create_page' ), 'wsgp_create_page' ) ) . '">' . esc_html__( 'Create style guide page', 'weave-style-guide-gp' ) . '</a>';
	}
	return array_merge( array( 'wsgp' => $link ), $links );
}
add_filter( 'plugin_action_links_' . plugin_basename( WSGP_FILE ), 'wsgp_action_links' );

/** Create the page from that link, then back to the Plugins screen with the result. */
function wsgp_admin_create_page(): void {
	if ( ! current_user_can( 'publish_pages' ) ) { wp_die( esc_html__( 'You are not allowed to create pages.', 'weave-style-guide-gp' ), 403 ); }
	check_admin_referer( 'wsgp_create_page' );
	$id = wsgp_create_page();
	if ( is_wp_error( $id ) ) {
		$data = $id->get_error_data();
		$args = array( 'wsgp_notice' => 'wsgp_slug_taken' === $id->get_error_code() ? 'taken' : 'failed', 'wsgp_page' => is_array( $data ) ? (int) ( $data['page_id'] ?? 0 ) : 0 );
	} else {
		$args = array( 'wsgp_notice' => 'created', 'wsgp_page' => $id );
	}
	wp_safe_redirect( add_query_arg( $args, admin_url( 'plugins.php' ) ) );
	exit;
}
add_action( 'admin_post_wsgp_create_page', 'wsgp_admin_create_page' );

/** The result, shown once on the Plugins screen. Display only: the query values just pick a message, so no nonce. */
function wsgp_admin_notice(): void {
	global $pagenow;
	if ( 'plugins.php' !== $pagenow || empty( $_GET['wsgp_notice'] ) ) { return; } // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$notice = sanitize_key( wp_unslash( $_GET['wsgp_notice'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$page   = absint( $_GET['wsgp_page'] ?? 0 ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( 'created' === $notice && $page ) {
		/* translators: %s: link to the style guide page */
		wp_admin_notice( sprintf( esc_html__( 'Style guide page ready. %s', 'weave-style-guide-gp' ), '<a href="' . esc_url( (string) get_permalink( $page ) ) . '">' . esc_html__( 'View it', 'weave-style-guide-gp' ) . '</a>' ), array( 'type' => 'success', 'dismissible' => true ) );
	} elseif ( 'taken' === $notice ) {
		/* translators: %d: ID of the page already at /style-guide/ */
		wp_admin_notice( sprintf( esc_html__( 'Nothing changed. A page at /style-guide/ already exists (ID %d) and was not made by this plugin. Create the guide at another address with WP-CLI: wp weave-style-guide-gp create --slug=brand', 'weave-style-guide-gp' ), $page ), array( 'type' => 'error', 'dismissible' => true ) );
	} else {
		wp_admin_notice( esc_html__( 'The style guide page could not be created. Try WP-CLI: wp weave-style-guide-gp create', 'weave-style-guide-gp' ), array( 'type' => 'error', 'dismissible' => true ) );
	}
}
add_action( 'admin_notices', 'wsgp_admin_notice' );
