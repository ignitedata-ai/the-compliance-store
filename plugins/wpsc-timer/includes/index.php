<?php
// Load settings.
foreach ( glob( __DIR__ . '/settings/*.php' ) as $filename ) {
	include_once $filename;
}

// Load custom field type classes.
foreach ( glob( __DIR__ . '/custom-field-types/*.php' ) as $filename ) {
	include_once $filename;
}

// Load model classes.
foreach ( glob( __DIR__ . '/model/*.php' ) as $filename ) {
	include_once $filename;
}

// Load widget classes.
foreach ( glob( __DIR__ . '/widget/*.php' ) as $filename ) {
	include_once $filename;
}

// Load workflow classes.
foreach ( glob( __DIR__ . '/workflow/*.php' ) as $filename ) {
	include_once $filename;
}

// Load dashboard classes.
foreach ( glob( __DIR__ . '/dashboard/*.php' ) as $filename ) {
	include_once $filename;
}
