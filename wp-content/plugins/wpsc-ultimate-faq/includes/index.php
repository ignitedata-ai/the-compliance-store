<?php
// Load common classes.
foreach ( glob( WPSC_FAQ_ABSPATH . 'includes/integrations/*.php' ) as $filename ) {
	include_once $filename;
}
