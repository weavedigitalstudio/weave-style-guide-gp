<?php
defined( 'ABSPATH' ) || exit;
$logos = wsgp_logos();
$icon  = get_site_icon_url( 512 );
if ( ! $logos && ! $icon ) {
	return '<p class="wsg-note">' . esc_html__( 'No logo yet. Add the Logo and Site Icon under Appearance > Customize > Site Identity, even when a Themer header shows the logo. GP Premium\'s mobile header and sticky navigation logos show here too once they are set.', 'weave-style-guide-gp' ) . '</p>';
}
// Light and dark stages: the site's surface and surface-inverse colours when it uses the Weave colour names, white and the darkest palette colour when it doesn't.
$light = wsgp_colour( 'surface' ) ?: '#ffffff';
$dark  = wsgp_colour( 'surface-inverse' ) ?: wsgp_darkest();
$about = wsgp_colour( 'surface' ) && wsgp_colour( 'surface-inverse' )
	? __( 'The logos set in the Customizer, on surface and surface-inverse: the page background and the dark section colour. Download gives the original file.', 'weave-style-guide-gp' )
	: __( 'The logos set in the Customizer, each on a light and a dark background so any version reads. Download gives the original file.', 'weave-style-guide-gp' );
$inner = '<p class="wsg-meta">' . esc_html( $about ) . '</p><div class="wsg-grid wsg-grid-logos">';
foreach ( $logos as $l ) {
	$img  = fn( string $alt ) => '<img src="' . esc_url( $l['url'] ) . '" alt="' . esc_attr( $alt ) . '" loading="lazy" decoding="async">';
	/* translators: %s: other logo slots using the same image, e.g. "sticky navigation logo" */
	$also = $l['also'] ? ' <span class="wsg-small">' . esc_html( sprintf( __( 'Also the %s', 'weave-style-guide-gp' ), strtolower( implode( __( ' and ', 'weave-style-guide-gp' ), $l['also'] ) ) ) ) . '</span>' : '';
	$inner .= '<figure class="wsg-card wsg-logo-card"><div class="wsg-logo-stages"><div class="wsg-logo-stage" style="background:' . esc_attr( $light ) . '">' . $img( $l['label'] ) . '</div><div class="wsg-logo-stage" style="background:' . esc_attr( $dark ) . '">' . $img( '' ) . '</div></div><figcaption><span class="wsg-name">' . esc_html( $l['label'] ) . '</span>' . $also . ' <a class="wsg-small" href="' . esc_url( $l['url'] ) . '" download>' . esc_html__( 'Download', 'weave-style-guide-gp' ) . '</a></figcaption></figure>';
}
if ( $icon ) {
	$inner .= '<figure class="wsg-card wsg-logo-card wsg-site-icon"><div class="wsg-logo-stages"><div class="wsg-logo-stage">' . implode( '', array_map( fn( $w ) => '<img class="wsg-icon-img" src="' . esc_url( $icon ) . '" style="width:' . $w . 'px;height:' . $w . 'px" width="' . $w . '" height="' . $w . '" alt="">', array( 96, 48, 32, 16 ) ) ) . '</div></div><figcaption><span class="wsg-name">' . esc_html__( 'Site icon', 'weave-style-guide-gp' ) . '</span> <span class="wsg-small">' . esc_html__( 'Favicon and app icon, at 96, 48, 32 and 16 pixels', 'weave-style-guide-gp' ) . '</span> <a class="wsg-small" href="' . esc_url( $icon ) . '" download>' . esc_html__( 'Download', 'weave-style-guide-gp' ) . '</a></figcaption></figure>';
}
return $inner . '</div>';
