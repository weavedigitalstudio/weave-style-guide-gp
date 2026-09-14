<?php
defined( 'ABSPATH' ) || exit;
$logos = wsgp_logos();
$icon  = get_site_icon_url( 512 );
if ( ! $logos && ! $icon ) {
	return '<p class="wsg-note">' . esc_html__( 'No logo yet. Add the Logo and Site Icon under Appearance > Customize > Site Identity, even when a Themer header shows the logo. GP Premium\'s mobile header and sticky navigation logos show here too once they are set.', 'weave-style-guide-gp' ) . '</p>';
}
// White and the site's background colour. When that background is white anyway, one panel says so.
[ $bg, $bg_name ] = wsgp_site_background();
$same = wsgp_hex_to_rgb( $bg ) && wsgp_contrast( $bg, '#ffffff' ) < 1.05;
/* translators: %s: where the site background comes from, e.g. surface or neutral-white */
$bg_label = sprintf( __( 'Site background, %s', 'weave-style-guide-gp' ), $bg_name );
$stages   = $same ? array( array( $bg, $bg_label ) ) : array( array( '#ffffff', __( 'White', 'weave-style-guide-gp' ) ), array( $bg, $bg_label ) );
$about    = $same
	? __( 'The logos set in the Customizer, on the site\'s background colour, which is white. Download gives the original file.', 'weave-style-guide-gp' )
	: __( 'The logos set in the Customizer, on white and on the site\'s background colour. Download gives the original file.', 'weave-style-guide-gp' );
$inner = '<p class="wsg-meta">' . esc_html( $about ) . '</p><div class="wsg-grid wsg-grid-logos">';
foreach ( $logos as $l ) {
	$panels = '';
	foreach ( $stages as $i => [ $colour, $label ] ) {
		$panels .= '<div class="wsg-logo-stage" style="background:' . esc_attr( $colour ) . ';color:' . esc_attr( wsgp_readable_on( $colour ) ) . '"><img src="' . esc_url( $l['url'] ) . '" alt="' . esc_attr( 0 === $i ? $l['label'] : '' ) . '" loading="lazy" decoding="async"><span class="wsg-stage-label">' . esc_html( $label ) . '</span></div>';
	}
	/* translators: %s: other logo slots using the same image, e.g. "sticky navigation logo" */
	$also = $l['also'] ? ' <span class="wsg-small">' . esc_html( sprintf( __( 'Also the %s', 'weave-style-guide-gp' ), strtolower( implode( __( ' and ', 'weave-style-guide-gp' ), $l['also'] ) ) ) ) . '</span>' : '';
	$inner .= '<figure class="wsg-card wsg-logo-card"><div class="wsg-logo-stages' . ( 1 === count( $stages ) ? ' is-single' : '' ) . '">' . $panels . '</div><figcaption><span class="wsg-name">' . esc_html( $l['label'] ) . '</span>' . $also . ' <a class="wsg-small" href="' . esc_url( $l['url'] ) . '" download>' . esc_html__( 'Download', 'weave-style-guide-gp' ) . '</a></figcaption></figure>';
}
if ( $icon ) {
	$inner .= '<figure class="wsg-card wsg-logo-card wsg-site-icon"><div class="wsg-logo-stages"><div class="wsg-logo-stage">' . implode( '', array_map( fn( $w ) => '<img class="wsg-icon-img" src="' . esc_url( $icon ) . '" style="width:' . $w . 'px;height:' . $w . 'px" width="' . $w . '" height="' . $w . '" alt="">', array( 96, 48, 32, 16 ) ) ) . '</div></div><figcaption><span class="wsg-name">' . esc_html__( 'Site icon', 'weave-style-guide-gp' ) . '</span> <span class="wsg-small">' . esc_html__( 'Favicon and app icon, at 96, 48, 32 and 16 pixels', 'weave-style-guide-gp' ) . '</span> <a class="wsg-small" href="' . esc_url( $icon ) . '" download>' . esc_html__( 'Download', 'weave-style-guide-gp' ) . '</a></figcaption></figure>';
}
return $inner . '</div>';
