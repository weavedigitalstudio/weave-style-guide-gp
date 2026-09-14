<?php
defined( 'ABSPATH' ) || exit;
$sets = wsgp_icon_sets();
if ( ! $sets ) { return '<p class="wsg-note">' . esc_html__( 'No icon set uploaded to Beaver Builder. Upload the IcoMoon zip under Settings > Beaver Builder > Icons and make sure it is enabled.', 'weave-style-guide-gp' ) . '</p>'; }
$inner = '';
foreach ( $sets as $set ) {
	if ( count( $sets ) > 1 ) { $inner .= '<h3 class="wsg-h3">' . esc_html( $set['name'] ) . '</h3>'; }
	/* translators: 1: number of icons, 2: icon set name */
	$inner .= '<p class="wsg-meta">' . esc_html( sprintf( _n( '%1$d icon in %2$s. Click one to copy its class.', '%1$d icons in %2$s. Click one to copy its class.', count( $set['icons'] ), 'weave-style-guide-gp' ), count( $set['icons'] ), $set['name'] ) ) . '</p><div class="wsg-grid wsg-grid-icons">';
	foreach ( $set['icons'] as $class ) {
		/* translators: %s: icon class */
		$inner .= '<button type="button" class="wsg-card wsg-icon wsg-copy" data-copy="' . esc_attr( $class ) . '" aria-label="' . esc_attr( sprintf( __( 'Copy %s', 'weave-style-guide-gp' ), $class ) ) . '"><i class="' . esc_attr( $class ) . '" aria-hidden="true"></i><span class="wsg-small">' . esc_html( wsgp_icon_label( $class, $set['prefix'] ) ) . '</span></button>';
	}
	$inner .= '</div>';
}
return $inner;
