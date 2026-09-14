<?php
/**
 * Read the live site: GeneratePress global colours, fonts and typography, the Customizer logos,
 * and the icon sets uploaded to Beaver Builder.
 */
defined( 'ABSPATH' ) || exit;

/** One GeneratePress setting, over GP's defaults. */
function wsgp_gp_option( string $key ): mixed {
	static $settings = null;
	$settings ??= wp_parse_args( (array) get_option( 'generate_settings', array() ), function_exists( 'generate_get_defaults' ) ? generate_get_defaults() : array() );
	return $settings[ $key ] ?? null;
}

/**
 * Global colours (Customize > Colors > Global Colors), read from the setting because
 * generate_get_global_colors() drops the names. GP writes each one as --{slug}. Each entry gains
 * 'value', the real colour after following var(--other) references, and 'alias', the slug it points at.
 */
function wsgp_palette(): array {
	static $p = null;
	if ( null !== $p ) { return $p; }
	$p       = array_values( array_filter( (array) wsgp_gp_option( 'global_colors' ), fn( $c ) => is_array( $c ) && ! empty( $c['slug'] ) && ! empty( $c['color'] ) ) );
	$by_slug = array_column( $p, 'color', 'slug' );
	foreach ( $p as &$c ) {
		$value = trim( (string) $c['color'] );
		for ( $i = 0; $i < 3 && preg_match( '/^var\(\s*--([A-Za-z0-9_-]+)\s*\)$/', $value, $m ) && isset( $by_slug[ $m[1] ] ); $i++ ) {
			$c['alias'] ??= $m[1];
			$value        = trim( (string) $by_slug[ $m[1] ] );
		}
		$c['value'] = $value;
	}
	unset( $c );
	return $p;
}

function wsgp_css_var( string $slug ): string { return "var(--$slug)"; }

/** A palette colour's real value by slug, or '' when the site doesn't have that slug. */
function wsgp_colour( string $slug ): string {
	foreach ( wsgp_palette() as $c ) {
		if ( $c['slug'] === $slug ) { return $c['value']; }
	}
	return '';
}

/**
 * The site's background colour as [ value, name ]: surface on sites using the Weave colour names,
 * otherwise GP's Customizer background colour (a var(--slug) reference followed to the palette),
 * otherwise white.
 */
function wsgp_site_background(): array {
	$surface = wsgp_colour( 'surface' );
	if ( '' !== $surface ) { return array( $surface, 'surface' ); }
	$bg = trim( (string) wsgp_gp_option( 'background_color' ) );
	if ( preg_match( '/^var\(\s*--([A-Za-z0-9_-]+)\s*\)$/', $bg, $m ) ) {
		$value = wsgp_colour( $m[1] );
		return '' !== $value ? array( $value, $m[1] ) : array( '#ffffff', 'white' );
	}
	return '' !== $bg ? array( $bg, $bg ) : array( '#ffffff', 'white' );
}

/**
 * The seventeen Weave colour names and the job each one does, in order. From the boilerplate token
 * contract 0.2.1 (weave-blocks docs/scaffold/boilerplate-tokens.json), which weave-playbook
 * sops/40-design/weave-figma-boilerplate.md follows. New builds put these slugs in the GP palette.
 */
function wsgp_colour_jobs(): array {
	return array(
		'surface'         => __( 'Page background', 'weave-style-guide-gp' ),
		'surface-subtle'  => __( 'Alternate section band', 'weave-style-guide-gp' ),
		'surface-raised'  => __( 'Cards and panels', 'weave-style-guide-gp' ),
		'surface-inverse' => __( 'Dark sections', 'weave-style-guide-gp' ),
		'text'            => __( 'Body copy', 'weave-style-guide-gp' ),
		'text-muted'      => __( 'Secondary copy, captions', 'weave-style-guide-gp' ),
		'text-inverse'    => __( 'Copy on dark', 'weave-style-guide-gp' ),
		'border'          => __( 'Rules and dividers', 'weave-style-guide-gp' ),
		'primary'         => __( 'Brand colour, headings', 'weave-style-guide-gp' ),
		'primary-strong'  => __( 'Primary hover and pressed', 'weave-style-guide-gp' ),
		'accent'          => __( 'Buttons, links, highlights', 'weave-style-guide-gp' ),
		'accent-strong'   => __( 'Accent hover and pressed', 'weave-style-guide-gp' ),
		'success'         => __( 'Form success', 'weave-style-guide-gp' ),
		'warning'         => __( 'Form warning', 'weave-style-guide-gp' ),
		'error'           => __( 'Form error', 'weave-style-guide-gp' ),
		'white'           => __( 'Pure white where the design needs it', 'weave-style-guide-gp' ),
		'black'           => __( 'Pure black where the design needs it', 'weave-style-guide-gp' ),
	);
}

/** Whether the site's palette follows the Weave colour names: it has at least surface and text. Older sites don't. */
function wsgp_uses_colour_names(): bool {
	return '' !== wsgp_colour( 'surface' ) && '' !== wsgp_colour( 'text' );
}

/** Background and text pairs that must pass for body text, from the boilerplate's Pairings board. */
function wsgp_pairings(): array {
	return array( array( 'surface', 'text' ), array( 'surface-subtle', 'text' ), array( 'surface-raised', 'text' ), array( 'surface-inverse', 'text-inverse' ), array( 'primary', 'text-inverse' ), array( 'accent', 'text-inverse' ) );
}

/**
 * GeneratePress's own starter colours (contrast, base and their numbered versions), read from GP's
 * defaults. accent is left out: the Weave palette overwrites it. build-15 says to remove the rest
 * once nothing points at them.
 */
function wsgp_gp_default_colours(): array {
	$defaults = function_exists( 'generate_get_defaults' ) ? (array) ( generate_get_defaults()['global_colors'] ?? array() ) : array();
	$slugs    = $defaults ? array_column( $defaults, 'slug' ) : array( 'contrast', 'contrast-2', 'contrast-3', 'base', 'base-2', 'base-3', 'accent' );
	return array_values( array_diff( $slugs, array_keys( wsgp_colour_jobs() ) ) );
}

/** GP settings that point at a palette colour through var(--slug), grouped by slug: [ 'contrast' => [ 'text_color' ] ]. */
function wsgp_colour_setting_uses(): array {
	$uses = array();
	foreach ( (array) get_option( 'generate_settings', array() ) as $key => $value ) {
		if ( is_string( $value ) && preg_match( '/^var\(\s*--([A-Za-z0-9_-]+)\s*\)$/', trim( $value ), $m ) ) { $uses[ $m[1] ][] = (string) $key; }
	}
	return $uses;
}

/**
 * Font families: GP Premium's Font Library, or GP's Font Manager (Google and system fonts) on sites
 * without it. Each has name, css (the font-family to preview with), variable, stack and weights.
 */
function wsgp_font_families(): array {
	static $out = null;
	if ( null !== $out ) { return $out; }
	$out = array();
	if ( class_exists( 'GeneratePress_Pro_Font_Library' ) ) {
		foreach ( (array) GeneratePress_Pro_Font_Library::get_fonts() as $f ) {
			if ( ! empty( $f['disabled'] ) ) { continue; }
			$weights = array();
			foreach ( (array) ( $f['variants'] ?? array() ) as $v ) {
				if ( is_array( $v ) && empty( $v['disabled'] ) && ! empty( $v['fontWeight'] ) ) { $weights[] = $v['fontWeight'] . ( 'italic' === ( $v['fontStyle'] ?? '' ) ? ' italic' : '' ); }
			}
			$weights = array_unique( $weights );
			sort( $weights, SORT_NATURAL );
			$var   = (string) ( $f['cssVariable'] ?? '' );
			$out[] = array( 'name' => (string) $f['name'], 'css' => $var ? "var($var)" : (string) $f['fontFamily'], 'variable' => $var, 'stack' => (string) $f['fontFamily'], 'weights' => $weights );
		}
	}
	if ( ! $out ) {
		foreach ( (array) wsgp_gp_option( 'font_manager' ) as $f ) {
			if ( ! is_array( $f ) || empty( $f['fontFamily'] ) ) { continue; }
			$stack = class_exists( 'GeneratePress_Typography' ) ? (string) GeneratePress_Typography::get_font_family( $f['fontFamily'] ) : $f['fontFamily'];
			$out[] = array( 'name' => $f['fontFamily'], 'css' => $stack, 'variable' => '', 'stack' => $stack, 'weights' => array_map( 'strval', (array) ( $f['variants'] ?? array() ) ) );
		}
	}
	return $out;
}

/** The family name behind a typography value: var(--gp-font--x) maps back to its Font Library name. */
function wsgp_font_label( string $value ): string {
	foreach ( wsgp_font_families() as $f ) {
		if ( $f['variable'] && "var({$f['variable']})" === $value ) { return $f['name']; }
	}
	return $value;
}

/** The Customize > Typography entries that set something, in the order the Customizer lists them. */
function wsgp_type_rules(): array {
	$skip  = array_flip( array( 'selector', 'customSelector', 'module', 'group', 'marginBottomUnit' ) );
	$rules = array();
	foreach ( (array) wsgp_gp_option( 'typography' ) as $r ) {
		if ( ! is_array( $r ) || empty( $r['selector'] ) ) { continue; }
		foreach ( array_diff_key( $r, $skip ) as $v ) {
			if ( is_scalar( $v ) && '' !== (string) $v ) { $rules[] = $r; break; }
		}
	}
	return $rules;
}

/** The element name the Customizer shows for a typography entry. */
function wsgp_type_label( array $r ): string {
	if ( 'custom' === $r['selector'] ) {
		/* translators: %s: CSS selector */
		return sprintf( __( 'Custom: %s', 'weave-style-guide-gp' ), $r['customSelector'] ?? '' );
	}
	$labels = array(
		'body'                   => __( 'Body', 'weave-style-guide-gp' ),
		'all-headings'           => __( 'All headings', 'weave-style-guide-gp' ),
		'h1'                     => __( 'Heading 1 (H1)', 'weave-style-guide-gp' ),
		'h2'                     => __( 'Heading 2 (H2)', 'weave-style-guide-gp' ),
		'h3'                     => __( 'Heading 3 (H3)', 'weave-style-guide-gp' ),
		'h4'                     => __( 'Heading 4 (H4)', 'weave-style-guide-gp' ),
		'h5'                     => __( 'Heading 5 (H5)', 'weave-style-guide-gp' ),
		'h6'                     => __( 'Heading 6 (H6)', 'weave-style-guide-gp' ),
		'buttons'                => __( 'Buttons', 'weave-style-guide-gp' ),
		'main-title'             => __( 'Site title', 'weave-style-guide-gp' ),
		'site-description'       => __( 'Site description', 'weave-style-guide-gp' ),
		'primary-menu-items'     => __( 'Primary menu items', 'weave-style-guide-gp' ),
		'primary-sub-menu-items' => __( 'Primary sub-menu items', 'weave-style-guide-gp' ),
		'primary-menu-toggle'    => __( 'Primary mobile menu toggle', 'weave-style-guide-gp' ),
		'single-content-title'   => __( 'Single content title (H1)', 'weave-style-guide-gp' ),
		'archive-content-title'  => __( 'Archive content title (H2)', 'weave-style-guide-gp' ),
		'top-bar'                => __( 'Top bar', 'weave-style-guide-gp' ),
		'widget-titles'          => __( 'Widget titles', 'weave-style-guide-gp' ),
		'footer'                 => __( 'Footer bar', 'weave-style-guide-gp' ),
	);
	return $labels[ $r['selector'] ] ?? ucfirst( str_replace( '-', ' ', $r['selector'] ) );
}

/** A typography value with its tablet and mobile versions: "88px / 44px mobile". */
function wsgp_responsive( array $r, string $key ): string {
	$parts = array();
	foreach ( array( '' => '', 'Tablet' => __( 'tablet', 'weave-style-guide-gp' ), 'Mobile' => __( 'mobile', 'weave-style-guide-gp' ) ) as $suffix => $label ) {
		$v = $r[ $key . $suffix ] ?? '';
		if ( ! is_scalar( $v ) || '' === (string) $v ) { continue; }
		if ( 'marginBottom' === $key && is_numeric( $v ) && ! empty( $r['marginBottomUnit'] ) ) { $v .= $r['marginBottomUnit']; }
		$parts[] = trim( $v . ' ' . $label );
	}
	return implode( ' / ', $parts );
}

/** Elements Beaver Builder Global Styles set type for. Those apply inside Beaver Builder layouts, on top of the GP settings. */
function wsgp_bb_global_type(): array {
	if ( ! class_exists( 'FLBuilderGlobalStyles' ) ) { return array(); }
	$s      = (array) FLBuilderGlobalStyles::get_settings( false );
	$labels = array( 'text' => __( 'body text', 'weave-style-guide-gp' ), 'link' => __( 'links', 'weave-style-guide-gp' ), 'h1' => 'H1', 'h2' => 'H2', 'h3' => 'H3', 'h4' => 'H4', 'h5' => 'H5', 'h6' => 'H6', 'button' => __( 'buttons', 'weave-style-guide-gp' ) );
	$out    = array();
	foreach ( $labels as $k => $label ) {
		foreach ( array( '', '_large', '_medium', '_responsive' ) as $bp ) {
			if ( wsgp_has_value( $s[ "{$k}_typography{$bp}" ] ?? null ) ) { $out[] = $label; break; }
		}
	}
	return $out;
}

/** Whether a Beaver Builder setting holds a real value: units and "default" on their own don't count. */
function wsgp_has_value( mixed $v ): bool {
	if ( is_object( $v ) ) { $v = (array) $v; }
	if ( is_array( $v ) ) {
		foreach ( $v as $k => $child ) {
			if ( 'unit' !== $k && wsgp_has_value( $child ) ) { return true; }
		}
		return false;
	}
	return is_scalar( $v ) && ! in_array( strtolower( (string) $v ), array( '', 'default' ), true );
}

/**
 * Logos from the Customizer: the Site Identity logo, GP's retina logo, and GP Premium's mobile header
 * and sticky navigation logos. Slots hold an attachment ID or a URL. An image used in more than one
 * slot shows once, with the other slots listed under "also".
 */
function wsgp_logos(): array {
	$menu  = (array) get_option( 'generate_menu_plus_settings', array() );
	$slots = array(
		'logo'   => array( __( 'Logo', 'weave-style-guide-gp' ), get_theme_mod( 'custom_logo' ) ),
		'retina' => array( __( 'Retina logo', 'weave-style-guide-gp' ), wsgp_gp_option( 'retina_logo' ) ),
		'mobile' => array( __( 'Mobile header logo', 'weave-style-guide-gp' ), $menu['mobile_header_logo'] ?? '' ),
		'sticky' => array( __( 'Sticky navigation logo', 'weave-style-guide-gp' ), $menu['sticky_navigation_logo'] ?? '' ),
	);
	$out  = array();
	$seen = array();
	foreach ( $slots as $slug => [ $label, $value ] ) {
		$url = is_numeric( $value ) ? (string) wp_get_attachment_url( (int) $value ) : ( is_string( $value ) ? $value : '' );
		if ( '' === $url ) { continue; }
		if ( isset( $seen[ $url ] ) ) { $out[ $seen[ $url ] ]['also'][] = $label; continue; }
		$seen[ $url ] = $slug;
		$out[ $slug ] = array( 'label' => $label, 'url' => $url, 'also' => array() );
	}
	return $out;
}

/** Icon sets uploaded to Beaver Builder (an IcoMoon or Fontello zip) and enabled. The built-in Font Awesome and Dashicons sets are left out. */
function wsgp_icon_sets(): array {
	static $sets = null;
	if ( null !== $sets ) { return $sets; }
	$sets = array();
	if ( ! class_exists( 'FLBuilderIcons' ) || ! class_exists( 'FLBuilderModel' ) ) { return $sets; }
	$enabled = (array) FLBuilderModel::get_enabled_icons();
	foreach ( (array) FLBuilderIcons::get_sets() as $key => $set ) {
		if ( 'core' === ( $set['type'] ?? 'core' ) || empty( $set['icons'] ) || ! in_array( (string) $key, $enabled, true ) ) { continue; }
		$sets[] = array( 'key' => (string) $key, 'name' => (string) $set['name'], 'stylesheet' => (string) ( $set['stylesheet'] ?? '' ), 'icons' => array_values( $set['icons'] ), 'prefix' => wsgp_icon_prefix( $set['icons'] ) );
	}
	return $sets;
}

/** The class prefix every icon in a set shares, cut back to a hyphen: "wst-icon-". */
function wsgp_icon_prefix( array $classes ): string {
	$names = array_map( fn( $c ) => (string) substr( (string) strrchr( ' ' . $c, ' ' ), 1 ), array_values( $classes ) );
	if ( count( $names ) < 2 ) { return ''; }
	$prefix = array_shift( $names );
	foreach ( $names as $n ) {
		while ( '' !== $prefix && ! str_starts_with( $n, $prefix ) ) { $prefix = substr( $prefix, 0, -1 ); }
	}
	$cut = strrpos( $prefix, '-' );
	return false === $cut ? '' : substr( $prefix, 0, $cut + 1 );
}

/** An icon's name without the set prefix: "wst-icon-phone" shows as "phone". */
function wsgp_icon_label( string $class, string $prefix ): string {
	$name = (string) substr( (string) strrchr( ' ' . $class, ' ' ), 1 );
	return '' !== $prefix && str_starts_with( $name, $prefix ) ? substr( $name, strlen( $prefix ) ) : $name;
}

/** The darkest solid colour in the palette, as a hex string (for dark stages). Near-black when the palette has nothing dark. */
function wsgp_darkest(): string {
	$best = '#111111'; $lum = 2;
	foreach ( wsgp_palette() as $c ) { if ( ! wsgp_hex_to_rgb( $c['value'] ) ) { continue; } $l = wsgp_luminance( $c['value'] ); if ( $l < $lum ) { $lum = $l; $best = $c['value']; } }
	return $lum > 0.2 ? '#111111' : $best;
}

/** The most saturated mid-tone in the palette (for the overlay demo band). */
function wsgp_accent(): string {
	$best = '#888888'; $score = -1;
	foreach ( wsgp_palette() as $c ) { $rgb = wsgp_hex_to_rgb( $c['value'] ); if ( ! $rgb ) { continue; } $hsl = wsgp_rgb_to_hsl( $rgb ); $l = wsgp_luminance( $c['value'] ); if ( $l < 0.08 || $l > 0.7 ) { continue; } if ( $hsl[1] > $score ) { $score = $hsl[1]; $best = $c['value']; } }
	return $best;
}
