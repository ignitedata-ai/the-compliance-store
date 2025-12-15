<?php
// Load models.
foreach ( glob( __DIR__ . '/models/*.php' ) as $filename ) {
	include_once $filename;
}

// Load custom field.
foreach ( glob( __DIR__ . '/custom-field-type/*.php' ) as $filename ) {
	include_once $filename;
}
