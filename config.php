<?php
//https://cloud.rpi-virtuell.de/index.php/s/d4EtzCiazRKgdzw
$config = array(
    'baseUri' => 'https://cloud.rpi-virtuell.de/public.php/webdav',   # The URL of your NextCloud
    'userName' => 'd4EtzCiazRKgdzw',                                # Public link token (or user)
    'password' => 'dXASdDwkGZ',                                               # Password for link (or password for user)
    'mod_rewrite_is_enabled' => true,
    'uri_prefix' => '/public.php/webdav/',
	'publicUri' => 'https://cloud.rpi-virtuell.de/s/'
);

$private = array(
	'baseUri' => 'https://cloud.rpi-virtuell.de/remote.php/dav/files/nextcloud/',  # The WebDav URL of your NextCloud
	'userName' => 'nextcloud',                                  # Public link token (or user)
	'password' => '(Z%inU6Pn0nxO&pcJ6fCiu$d',                   # Password for link (or password for user)
	'dir'      => 'Documents/'
);
