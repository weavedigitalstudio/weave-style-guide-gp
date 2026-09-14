<?php
defined( 'ABSPATH' ) || exit;
$inner    = '';
$families = wsgp_font_families();
if ( $families ) {
	$inner .= '<div class="wsg-grid wsg-grid-families">';
	foreach ( $families as $f ) {
		$meta = array();
		if ( $f['variable'] ) { $meta[] = '<button type="button" class="wsg-copy" data-copy="' . esc_attr( 'var(' . $f['variable'] . ')' ) . '"><code>' . esc_html( 'var(' . $f['variable'] . ')' ) . '</code></button>'; }
		$meta[] = '<span class="wsg-small">' . esc_html( $f['stack'] ) . '</span>';
		/* translators: %s: comma list of font weights */
		if ( $f['weights'] ) { $meta[] = '<span class="wsg-small">' . esc_html( sprintf( __( 'Weights: %s', 'weave-style-guide-gp' ), implode( ', ', $f['weights'] ) ) ) . '</span>'; }
		$inner .= '<div class="wsg-card wsg-family" style="font-family:' . esc_attr( $f['css'] ) . '"><p class="wsg-family-name">' . esc_html( $f['name'] ) . '</p><p class="wsg-family-big">' . esc_html__( 'The quick brown fox jumps over the lazy dog', 'weave-style-guide-gp' ) . '</p><p class="wsg-family-alpha">ABCDEFGHIJKLMNOPQRSTUVWXYZ abcdefghijklmnopqrstuvwxyz 0123456789 &amp; ? ! $ %</p><p class="wsg-family-meta">' . implode( ' ', $meta ) . '</p></div>';
	}
	$inner .= '</div>';
} else {
	$inner .= '<p class="wsg-note">' . esc_html__( 'No fonts in the GP Premium Font Library or the Font Manager, so the site uses the system font.', 'weave-style-guide-gp' ) . '</p>';
}

// Real headings and body text, with the settings behind each. All headings sits under each heading's own settings, the way GP's CSS applies them.
$rules = array();
foreach ( wsgp_type_rules() as $r ) { $rules[ $r['selector'] ] = $r; }
$clean    = fn( array $r ) => array_filter( $r, fn( $v ) => is_scalar( $v ) && '' !== (string) $v );
$describe = function ( array $r ): string {
	$lh = wsgp_responsive( $r, 'lineHeight' );
	$ls = wsgp_responsive( $r, 'letterSpacing' );
	/* translators: %s: line height value(s) */
	$bits = array( wsgp_font_label( (string) ( $r['fontFamily'] ?? '' ) ), (string) ( $r['fontWeight'] ?? '' ), wsgp_responsive( $r, 'fontSize' ), $lh ? sprintf( __( 'line height %s', 'weave-style-guide-gp' ), $lh ) : '', $ls ? sprintf( __( 'letter spacing %s', 'weave-style-guide-gp' ), $ls ) : '' );
	return implode( '  ·  ', array_filter( $bits, fn( $b ) => '' !== $b ) );
};
$inner .= '<h3 class="wsg-h3">' . esc_html__( 'Headings and body', 'weave-style-guide-gp' ) . '</h3><p class="wsg-meta">' . esc_html__( 'Real headings and body text, styled by the site. Under each: its settings from Customize > Typography, and the live size at this screen width. Resize the window and it changes.', 'weave-style-guide-gp' ) . '</p><div class="wsg-elements">';
foreach ( array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' ) as $el ) {
	$r = array_merge( $clean( $rules['all-headings'] ?? array() ), $clean( $rules[ $el ] ?? array() ) );
	/* translators: %d: heading level */
	$inner .= "<$el class=\"wsg-el\" aria-hidden=\"true\">" . esc_html( sprintf( __( 'Heading %d as the site sets it', 'weave-style-guide-gp' ), (int) substr( $el, 1 ) ) ) . "</$el><p class=\"wsg-small wsg-el-meta\">" . esc_html( $describe( $r ) ) . ' <span class="wsg-now" data-now="font-size"></span></p>';
}
$inner .= '<p class="wsg-el">' . esc_html__( 'Body copy. This paragraph uses the site\'s body text settings, so it shows the font, size and line height visitors read most.', 'weave-style-guide-gp' ) . ' <a href="#">' . esc_html__( 'A text link', 'weave-style-guide-gp' ) . '</a></p><p class="wsg-small wsg-el-meta">' . esc_html( $describe( $clean( $rules['body'] ?? array() ) ) ) . ' <span class="wsg-now" data-now="font-size"></span></p></div>';

if ( $rules ) {
	$inner .= '<h3 class="wsg-h3">' . esc_html__( 'Typography settings', 'weave-style-guide-gp' ) . '</h3><p class="wsg-meta">' . esc_html__( 'Every element with typography set in Customize > Typography. A blank cell means the element inherits.', 'weave-style-guide-gp' ) . '</p><div class="wsg-scroll"><table class="wsg-table"><thead><tr>';
	foreach ( array( __( 'Element', 'weave-style-guide-gp' ), __( 'Font', 'weave-style-guide-gp' ), __( 'Weight', 'weave-style-guide-gp' ), __( 'Size', 'weave-style-guide-gp' ), __( 'Line height', 'weave-style-guide-gp' ), __( 'Letter spacing', 'weave-style-guide-gp' ), __( 'Other', 'weave-style-guide-gp' ) ) as $th ) { $inner .= '<th scope="col">' . esc_html( $th ) . '</th>'; }
	$inner .= '</tr></thead><tbody>';
	foreach ( $rules as $r ) {
		$mb    = wsgp_responsive( $r, 'marginBottom' );
		/* translators: %s: margin value(s) */
		$other = array_filter( array( (string) ( $r['textTransform'] ?? '' ), (string) ( $r['fontStyle'] ?? '' ), (string) ( $r['textDecoration'] ?? '' ), $mb ? sprintf( __( 'margin bottom %s', 'weave-style-guide-gp' ), $mb ) : '' ), fn( $s ) => '' !== $s );
		$inner .= '<tr><th scope="row">' . esc_html( wsgp_type_label( $r ) ) . '</th><td>' . esc_html( wsgp_font_label( (string) ( $r['fontFamily'] ?? '' ) ) ) . '</td><td>' . esc_html( (string) ( $r['fontWeight'] ?? '' ) ) . '</td><td>' . esc_html( wsgp_responsive( $r, 'fontSize' ) ) . '</td><td>' . esc_html( wsgp_responsive( $r, 'lineHeight' ) ) . '</td><td>' . esc_html( wsgp_responsive( $r, 'letterSpacing' ) ) . '</td><td>' . esc_html( implode( ', ', $other ) ) . '</td></tr>';
	}
	$inner .= '</tbody></table></div>';
}

$bb = wsgp_bb_global_type();
/* translators: %s: comma list of elements, e.g. "H1, buttons" */
if ( $bb ) { $inner .= '<p class="wsg-note">' . esc_html( sprintf( __( 'Beaver Builder Global Styles also set type for %s. Those apply inside Beaver Builder layouts and can differ from the settings above.', 'weave-style-guide-gp' ), implode( ', ', $bb ) ) ) . '</p>'; }
return $inner;
