<?php
if (defined('VENDOR_PATH')) {
	$vendorDir = require VENDOR_PATH;
} else {
	$vendorDir = require BP . '/app/etc/vendor_path.php';
}
$vendorAutoload = BP . "/{$vendorDir}/autoload.php";

if (file_exists($vendorAutoload)) {
    $composerAutoloader = include $vendorAutoload;

    $libDirectory = dirname(dirname(__DIR__));
    $namespaces = [
		'Customweb',
		'Crypt',
		'Math',
		'Net',
		'File',
		'System'
	];
	foreach ($namespaces as $namespace) {
	    $composerAutoloader->set($namespace, $libDirectory);
	}
}