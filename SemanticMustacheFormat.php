<?php

if ( !defined( 'MEDIAWIKI' ) ) {
	exit;
}

$dir = __DIR__;

# Mustache
require "$dir/vendor/mustache.php/src/Mustache/Autoloader.php";
Mustache_Autoloader::register();

