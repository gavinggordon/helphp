<?php

namespace GGG;

use Composer\Script\Event;
use Composer\Installer\PackageEvent;

class InitHelper
{

	// post-update
	public static function updateConsolePackage( Event $event )
    {
		$io = $event->getIO();
		$baseDir = $event->getComposer()->getConfig()->get('vendor-dir') . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;
		$dest = ( preg_match( '/[\/\\\\]$/', $baseDir ) ) ? $baseDir . 'helphp' : $baseDir . DIRECTORY_SEPARATOR . 'helphp';
		if( file_put_contents( $dest, file_get_contents( dirname( dirname( __FILE__ ) ) . DIRECTORY_SEPARATOR . 'helphp' ) ) )
		{
			$io->write( [ 'Update process complete!', 'PHP CLI Package "helphp" has been updated.' ], true );
		}
		else
		{
			$io->writeError( [ 'There seems to be a problem...', 'Failed to update "helphp" PHP CLI Package.' ], true );
		}
    }

	// post-package-install
    public static function installConsolePackage( PackageEvent $event )
    {
		$io = $event->getIO();
		$baseDir = $event->getComposer()->getConfig()->get('vendor-dir') . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;
		$dest = ( preg_match( '/[\/\\\\]$/', $baseDir ) ) ? $baseDir . 'helphp' : $baseDir . DIRECTORY_SEPARATOR . 'helphp';
		if( file_put_contents( $dest, file_get_contents( dirname( dirname( __FILE__ ) ) . DIRECTORY_SEPARATOR . 'helphp' ) ) )
		{
			$io->write( [ 'Installation process complete!', 'PHP CLI Package "helphp" is now accessible.' ], true );
		}
		else
		{
			$io->writeError( [ 'There seems to be a problem...', 'Failed to install "helphp" PHP CLI Package.' ], true );
		}
    }
	
}