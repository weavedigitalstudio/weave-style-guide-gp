<?php
defined( 'ABSPATH' ) || exit;
$palette = wsgp_palette();
if ( ! $palette ) { return '<p class="wsg-note">' . esc_html__( 'No global colours yet. Add them under Appearance > Customize > Colors > Global Colors, named after the job each one does (surface, text, primary and the rest).', 'weave-style-guide-gp' ) . '</p>'; }
/* translators: %s: value or name being copied */
$copy = fn( string $value, string $label = '' ) => '<button type="button" class="wsg-copy" data-copy="' . esc_attr( $value ) . '" aria-label="' . esc_attr( sprintf( __( 'Copy %s', 'weave-style-guide-gp' ), $label ?: $value ) ) . '"><code>' . esc_html( $value ) . '</code></button>';
$css  = ''; $json = array();
$card = function ( array $c, string $job = '' ) use ( $copy, &$css, &$json ): string {
	$rgb  = wsgp_hex_to_rgb( $c['value'] );
	$hex  = '#' . strtoupper( ltrim( $c['value'], '#' ) );
	$hsl  = wsgp_rgb_to_hsl( $rgb );
	$name = $c['name'] ?? $c['slug'];
	$css .= "  --{$c['slug']}: $hex;\n";
	$json[ $c['slug'] ] = array_filter( array( 'name' => $name, 'job' => $job, 'alias' => $c['alias'] ?? '', 'hex' => $hex, 'rgb' => $rgb, 'hsl' => $hsl ) );
	/* translators: %s: slug of the palette colour this one points at */
	$alias = empty( $c['alias'] ) ? '' : '<p class="wsg-job">' . esc_html( sprintf( __( 'Points at %s', 'weave-style-guide-gp' ), $c['alias'] ) ) . '</p>';
	return '<div class="wsg-card wsg-colour' . ( wsgp_needs_border( $hex ) ? ' wsg-has-border' : '' ) . '"><div class="wsg-swatch" style="background:' . esc_attr( $hex ) . '"></div><p class="wsg-colour-name wsg-name">' . esc_html( $name ) . '</p>' . ( $job ? '<p class="wsg-job">' . esc_html( $job ) . '</p>' : '' ) . $alias . '<div class="wsg-values">' . $copy( $hex ) . $copy( sprintf( 'rgb(%d %d %d)', ...$rgb ) ) . $copy( sprintf( 'hsl(%d %d%% %d%%)', ...$hsl ) ) . $copy( wsgp_css_var( $c['slug'] ), $c['slug'] ) . '</div></div>';
};
$solid    = array_values( array_filter( $palette, fn( $c ) => (bool) wsgp_hex_to_rgb( $c['value'] ) ) );
$overlays = array_values( array_filter( $palette, fn( $c ) => ! wsgp_hex_to_rgb( $c['value'] ) ) );
$where    = function_exists( 'GPBeaverIntegration\Colors\get_formatted_gp_colors' ) ? ' ' . __( 'In Beaver Builder they sit in the colour picker\'s Presets tab.', 'weave-style-guide-gp' ) : '';
/* translators: %s: the CSS variable pattern, var(--name) */
$inner = '<p class="wsg-meta">' . sprintf( esc_html__( 'The global colours from the Customizer. Click any value to copy it. In CSS, use the variable: GeneratePress writes each colour as %s.', 'weave-style-guide-gp' ), '<code>var(--name)</code>' ) . esc_html( $where ) . '</p>';

if ( wsgp_uses_colour_names() ) {
	// Built to the Weave colour names: the seventeen jobs first, in the contract's order, then the extra colours
	// the layouts use (named for their job too), then any GeneratePress starter colours still waiting to come out.
	$jobs     = wsgp_colour_jobs();
	$starters = wsgp_gp_default_colours();
	$by_slug  = array_column( $solid, null, 'slug' );
	$named    = ''; $rest = ''; $leftover = array();
	foreach ( $jobs as $slug => $job ) { if ( isset( $by_slug[ $slug ] ) ) { $named .= $card( $by_slug[ $slug ], $job ); } }
	foreach ( $solid as $c ) {
		if ( isset( $jobs[ $c['slug'] ] ) ) { continue; }
		if ( in_array( $c['slug'], $starters, true ) ) { $leftover[] = $c['slug']; continue; }
		$rest .= $card( $c );
	}
	$inner .= '<h3 class="wsg-h3">' . esc_html__( 'Colour jobs', 'weave-style-guide-gp' ) . '</h3><p class="wsg-meta">' . esc_html__( 'The seventeen Weave colour names, shared by the Figma file and the build. Use these first and pick by job: surface for backgrounds, text-body for copy, primary for headings, accent for buttons and links.', 'weave-style-guide-gp' ) . '</p><div class="wsg-grid wsg-grid-colours">' . $named . '</div>';
	if ( $rest ) { $inner .= '<h3 class="wsg-h3">' . esc_html__( 'Other colours', 'weave-style-guide-gp' ) . '</h3><p class="wsg-meta">' . esc_html__( 'Extra colours the layouts use, each named for the job it does. The brand\'s own name for a swatch is the label on the card.', 'weave-style-guide-gp' ) . '</p><div class="wsg-grid wsg-grid-colours">' . $rest . '</div>'; }
	if ( $leftover ) {
		$uses  = wsgp_colour_setting_uses();
		/* translators: %s: comma list of GeneratePress settings */
		$items = array_map( fn( string $slug ) => '<code>' . esc_html( $slug ) . '</code>' . ( empty( $uses[ $slug ] ) ? '' : ' ' . esc_html( sprintf( __( '(used by %s)', 'weave-style-guide-gp' ), implode( ', ', $uses[ $slug ] ) ) ) ), $leftover );
		/* translators: %s: comma list of colour slugs */
		$inner .= '<p class="wsg-note">' . sprintf( esc_html__( 'GeneratePress starter colours still in the palette: %s. Remove them once nothing points at them, so the colour picker only offers Weave names.', 'weave-style-guide-gp' ), implode( ', ', $items ) ) . '</p>';
	}
} else {
	// Older sites: main colours, with lighter and darker versions grouped under the colour they're named after.
	$slugs      = array_column( $solid, 'slug' );
	$is_variant = fn( string $slug ): bool => (bool) preg_match( '/^(.+)-(light|lighter|dark|darker|strong|soft|muted|tint|shade|[0-9]{1,3})$/', $slug, $m ) && in_array( $m[1], $slugs, true );
	$mains      = ''; $variants = '';
	foreach ( $solid as $c ) { if ( $is_variant( $c['slug'] ) ) { $variants .= $card( $c ); } else { $mains .= $card( $c ); } }
	$inner .= '<div class="wsg-grid wsg-grid-colours">' . $mains . '</div>';
	if ( $variants ) { $inner .= '<h3 class="wsg-h3">' . esc_html__( 'Tints and variants', 'weave-style-guide-gp' ) . '</h3><p class="wsg-meta">' . esc_html__( 'Lighter, darker and hover versions of the main colours. Use the main colour first; reach for these for hover states, soft backgrounds and borders.', 'weave-style-guide-gp' ) . '</p><div class="wsg-grid wsg-grid-colours">' . $variants . '</div>'; }
}

if ( $overlays ) {
	$inner .= '<h3 class="wsg-h3">' . esc_html__( 'Overlays', 'weave-style-guide-gp' ) . '</h3><p class="wsg-meta">' . esc_html__( 'Translucent helpers for panels over photos and dark bands. Not brand colours. Shown as a panel over a dark to light band so the transparency reads.', 'weave-style-guide-gp' ) . '</p><div class="wsg-grid wsg-grid-overlays">';
	$band = 'linear-gradient(90deg,' . wsgp_darkest() . ' 0%,' . wsgp_darkest() . ' 30%,' . wsgp_accent() . ' 60%,#ffffff 100%)';
	foreach ( $overlays as $c ) { $inner .= '<div class="wsg-card wsg-colour"><div class="wsg-overlay-stage" style="background:' . esc_attr( $band ) . '"><span class="wsg-overlay-panel" style="background:' . esc_attr( $c['value'] ) . '"></span></div><p class="wsg-colour-name wsg-name">' . esc_html( $c['name'] ?? $c['slug'] ) . '</p><div class="wsg-values">' . $copy( $c['color'] ) . $copy( wsgp_css_var( $c['slug'] ), $c['slug'] ) . '</div></div>'; }
	$inner .= '</div>';
}
$inner .= '<p class="wsg-actions"><button type="button" class="wsg-copy" data-label="' . esc_attr__( 'palette as CSS', 'weave-style-guide-gp' ) . '" data-copy="' . esc_attr( ":root {\n$css}" ) . '">' . esc_html__( 'Copy palette as CSS', 'weave-style-guide-gp' ) . '</button> <button type="button" class="wsg-copy" data-label="' . esc_attr__( 'palette as JSON', 'weave-style-guide-gp' ) . '" data-copy="' . esc_attr( (string) wp_json_encode( $json, JSON_PRETTY_PRINT ) ) . '">' . esc_html__( 'Copy palette as JSON', 'weave-style-guide-gp' ) . '</button></p>';
return $inner;
