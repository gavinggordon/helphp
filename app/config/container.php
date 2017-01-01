<?php

use Pimple\Container as PimpleContainer;

$container = new PimpleContainer();

$container['parameters'] = [
	'manifest.file'  => __DIR__ . DIRECTORY_SEPARATOR . 'manifest.json',
	'tmp.save.dir'  => dirname( dirname( __DIR__ ) ) . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR
];

$container['service.documentcreator'] = function($container)
{
	return new GGG\ConsoleApp\Service\DocumentCreator( $container['parameters']['manifest.file'], $container['parameters']['tmp.save.dir'] );
};

$container['command.create.class'] = function($container)
{
	return new GGG\ConsoleApp\Command\Create\ClassCommand( $container['service.documentcreator'] );
};

$container['commands'] = function( $container )
{
	return [
		$container['command.create.class']
	];
};

$container['application'] = function( $container )
{
	$application = new \Symfony\Component\Console\Application();
	$application->addCommands( $container['commands'] );
	return $application;
};

return $container;
