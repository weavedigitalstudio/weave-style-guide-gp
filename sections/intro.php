<?php
defined( 'ABSPATH' ) || exit;
$intro = __( 'Everything on this page is read from the site when the page loads, so it always matches the Customizer.', 'weave-style-guide-gp' );
if ( wsgp_uses_colour_names() ) { $intro .= ' ' . __( 'The colours use the Weave colour names, so each one shows the job it does.', 'weave-style-guide-gp' ); }
return '<p class="wsg-intro">' . esc_html( $intro ) . '</p>';
