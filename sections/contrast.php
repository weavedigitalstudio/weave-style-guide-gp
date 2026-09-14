<?php
defined( 'ABSPATH' ) || exit;
$palette = array_values( array_filter( wsgp_palette(), fn( $c ) => (bool) wsgp_hex_to_rgb( $c['value'] ) ) );
if ( count( $palette ) < 2 ) { return '<p class="wsg-note">' . esc_html__( 'Contrast needs at least two solid global colours.', 'weave-style-guide-gp' ) . '</p>'; }
$by_slug = array_column( $palette, null, 'slug' );
$inner   = '';

// Sites on the Weave colour names: the pairings the design promises come first, pass or fix.
$pairs = array_filter( wsgp_pairings(), fn( $p ) => isset( $by_slug[ $p[0] ], $by_slug[ $p[1] ] ) );
if ( $pairs ) {
	$inner .= '<h3 class="wsg-h3">' . esc_html__( 'Required pairings', 'weave-style-guide-gp' ) . '</h3><p class="wsg-meta">' . esc_html__( 'Each surface with its text colour, and text on the primary and accent colours. Every one needs 4.5:1 for body text. If one falls short, fix the colour, not the pairing.', 'weave-style-guide-gp' ) . '</p><div class="wsg-pairings">';
	foreach ( $pairs as [ $bg, $fg ] ) {
		$r = wsgp_contrast( $by_slug[ $fg ]['value'], $by_slug[ $bg ]['value'] );
		/* translators: 1: text colour slug, 2: background colour slug */
		$inner .= '<div class="wsg-pairing-row"><div class="wsg-pairing-bg"><span class="wsg-pair wsg-pair-sample" style="background:' . esc_attr( $by_slug[ $bg ]['value'] ) . ';color:' . esc_attr( $by_slug[ $fg ]['value'] ) . '">' . esc_html( sprintf( __( '%1$s on %2$s', 'weave-style-guide-gp' ), $fg, $bg ) ) . '</span></div><div class="wsg-pairing-chips"><span class="wsg-name">' . esc_html( number_format( $r, 1 ) . ':1 ' . wsgp_rating( $r ) ) . '</span> <span class="wsg-small">' . esc_html( $r >= 4.5 ? __( 'Passes for body text', 'weave-style-guide-gp' ) : __( 'Fails for body text. Fix the colour.', 'weave-style-guide-gp' ) ) . '</span></div></div>';
	}
	$inner .= '</div><h3 class="wsg-h3">' . esc_html__( 'Every other combination', 'weave-style-guide-gp' ) . '</h3>';
}

$inner .= '<p class="wsg-meta">' . esc_html__( 'What text can go on each colour. AA means 4.5:1, fine for body text; AA large means 3:1, headings only; AAA is 7:1. Colours that pass nothing say so, so nobody has to guess.', 'weave-style-guide-gp' ) . '</p><div class="wsg-pairings">';
foreach ( $palette as $bg ) {
	$chips = '';
	foreach ( $palette as $fg ) {
		if ( $fg['slug'] === $bg['slug'] ) { continue; }
		$r = wsgp_contrast( $fg['value'], $bg['value'] ); if ( $r < 3 ) { continue; }
		$chips .= '<span class="wsg-pair" style="background:' . esc_attr( $bg['value'] ) . ';color:' . esc_attr( $fg['value'] ) . '"><strong>' . esc_html( $fg['name'] ?? $fg['slug'] ) . '</strong> ' . esc_html( number_format( $r, 1 ) . ':1 ' . wsgp_rating( $r ) ) . '</span>';
	}
	$inner .= '<div class="wsg-pairing-row"><div class="wsg-pairing-bg"><span class="wsg-chip" style="background:' . esc_attr( $bg['value'] ) . ';color:' . esc_attr( wsgp_readable_on( $bg['value'] ) ) . '">' . esc_html( $bg['name'] ?? $bg['slug'] ) . '</span></div><div class="wsg-pairing-chips">' . ( $chips ?: '<span class="wsg-small">' . esc_html__( 'Nothing in the palette passes on this colour. Use it for surfaces, not behind text.', 'weave-style-guide-gp' ) . '</span>' ) . '</div></div>';
}
return $inner . '</div>';
