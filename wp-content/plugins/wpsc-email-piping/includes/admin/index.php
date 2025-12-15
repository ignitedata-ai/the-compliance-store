<?php

foreach ( glob( __DIR__ . '/email-notifications/*.php' ) as $filename ) {
	include_once $filename;
}
