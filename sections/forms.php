<?php
defined( 'ABSPATH' ) || exit;
if ( ! class_exists( 'GFAPI' ) ) {
	return '<p class="wsg-note">' . esc_html__( 'No form plugin active. With Gravity Forms active, this section shows the first form as the site styles it.', 'weave-style-guide-gp' ) . '</p>';
}
$id = (int) ( $atts['form'] ?? 0 );
if ( ! $id ) {
	// The shortest form: a contact form shows every style without a multi-page flow or a save-progress prompt.
	$forms = array_filter( GFAPI::get_forms( true ), fn( $f ) => ! empty( $f['fields'] ) );
	usort( $forms, fn( $a, $b ) => count( $a['fields'] ) <=> count( $b['fields'] ) );
	$id = (int) ( $forms[0]['id'] ?? 0 );
}
if ( ! $id ) { return '<p class="wsg-note">' . esc_html__( 'Gravity Forms is active but has no forms yet.', 'weave-style-guide-gp' ) . '</p>'; }
$form = GFAPI::get_form( $id );
/* translators: 1: form title, 2: form ID */
return '<p class="wsg-meta">' . esc_html( sprintf( __( 'Gravity Form "%1$s" (id %2$d) as the site styles it: fields, labels, help text, errors and the button.', 'weave-style-guide-gp' ), $form['title'] ?? '', $id ) ) . '</p><div class="wsg-form-stage">' . do_shortcode( '[gravityform id="' . $id . '" title="false" description="false" ajax="false"]' ) . '</div>';
