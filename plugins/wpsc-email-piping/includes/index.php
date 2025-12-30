<?php
// Load settings.
foreach ( glob( __DIR__ . '/settings/*.php' ) as $filename ) {
	include_once $filename;
}

// Load settings.
foreach ( glob( __DIR__ . '/models/*.php' ) as $filename ) {
	include_once $filename;
}

// Load settings.
foreach ( glob( __DIR__ . '/admin/*.php' ) as $filename ) {
	include_once $filename;
}
