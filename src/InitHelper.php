<?php

namespace GGG;

use Composer\Script\Event;
use Composer\Installer\PackageEvent;

class InitHelper
{

	const SRC_HELPHP_FILE = dirname( __DIR__ ) . DIRECTORY_SEPARATOR . 'helphp';

	// post-update
	public static function updateConsoleFile( Event $event )
    {
		$baseDir = $event->getComposer()->getConfig()->get('base-dir');
		$dest = ( preg_match( '#[\\/\]$#', $baseDir ) ) ? $baseDir . 'helphp' : $baseDir . DIRECTORY_SEPARATOR . 'helphp';
		copy( static::SRC_HELPHP_FILE, $dest );
		return;
    }

	// post-package-install
    public static function preparePackage( PackageEvent $event )
    {
		$baseDir = $event->getComposer()->getConfig()->get('base-dir');
		$dest = ( preg_match( '#[\\/\]$#', $baseDir ) ) ? $baseDir . 'helphp' : $baseDir . DIRECTORY_SEPARATOR . 'helphp';
		copy( static::SRC_HELPHP_FILE, $dest );
		return;
    }
	
}