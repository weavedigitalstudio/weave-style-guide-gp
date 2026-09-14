<?php
/**
 * Colour maths, ported from gp-beaver-integration and weave-accessibility-colour-matrix. Shared with weave-style-guide.
 */
defined( 'ABSPATH' ) || exit;

function wsgp_hex_to_rgb( string $hex ): ?array {
	$hex = ltrim( trim( $hex ), '#' );
	if ( 3 === strlen( $hex ) ) {
		$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
	}
	if ( ! preg_match( '/^[0-9a-f]{6}$/i', $hex ) ) {
		return null;
	}
	return array( hexdec( substr( $hex, 0, 2 ) ), hexdec( substr( $hex, 2, 2 ) ), hexdec( substr( $hex, 4, 2 ) ) );
}

function wsgp_rgb_to_hsl( array $rgb ): array {
	list( $r, $g, $b ) = array_map( fn( $v ) => $v / 255, $rgb );
	$max = max( $r, $g, $b ); $min = min( $r, $g, $b ); $l = ( $max + $min ) / 2; $d = $max - $min;
	if ( 0.0 === (float) $d ) { return array( 0, 0, round( $l * 100 ) ); }
	$s = $l > 0.5 ? $d / ( 2 - $max - $min ) : $d / ( $max + $min );
	switch ( $max ) { case $r: $h = ( $g - $b ) / $d + ( $g < $b ? 6 : 0 ); break; case $g: $h = ( $b - $r ) / $d + 2; break; default: $h = ( $r - $g ) / $d + 4; }
	return array( round( $h * 60 ), round( $s * 100 ), round( $l * 100 ) );
}

function wsgp_luminance( string $hex ): float {
	$rgb = wsgp_hex_to_rgb( $hex );
	if ( ! $rgb ) { return 0.0; }
	$lin = array_map( function ( $v ) { $v /= 255; return $v <= 0.03928 ? $v / 12.92 : pow( ( $v + 0.055 ) / 1.055, 2.4 ); }, $rgb );
	return 0.2126 * $lin[0] + 0.7152 * $lin[1] + 0.0722 * $lin[2];
}

function wsgp_contrast( string $a, string $b ): float {
	$l1 = wsgp_luminance( $a ); $l2 = wsgp_luminance( $b );
	return ( max( $l1, $l2 ) + 0.05 ) / ( min( $l1, $l2 ) + 0.05 );
}

function wsgp_rating( float $ratio ): string {
	if ( $ratio >= 7 ) { return 'AAA'; }
	if ( $ratio >= 4.5 ) { return 'AA'; }
	if ( $ratio >= 3 ) { return 'AA large'; }
	return 'Fail';
}

/** Black or white text on a colour, by luminance (the GP integration rule). */
function wsgp_readable_on( string $hex ): string {
	return wsgp_luminance( $hex ) > 0.5 ? '#000000' : '#ffffff';
}

function wsgp_needs_border( string $hex ): bool {
	return wsgp_luminance( $hex ) > 0.85;
}
