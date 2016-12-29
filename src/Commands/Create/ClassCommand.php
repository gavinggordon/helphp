<?php

namespace Helphp\Commands\Create;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ClassCommand extends Command
{
	const COMMAND_INFO = [
		'create:class' => 'Creates a new class file, prepared just the way you like.'
	];
	protected $commandName; // = '';
	protected $commandDescription; // = '';
	
	const COMMAND_ARGUMENTS_INFO = [
		"classname" => "The name to be given to your class.",
		"savedir" => "The directory in which to save your class."
	];
	protected $commandArgumentName; // = [];
	protected $commandArgumentDescription; // = [];

// (\t\t\'([a-z\-]*)\'\s\=\>\s\'([\w\s\d\"\.\_\[\]\(\)]+)\'\,?)*
// $1\n\t\tcase '$1[0]':\n\n\t\t\tbreak;\n

	const COMMAND_OPTIONS_INFO = [
		'namespace' => 'The namespace to use for your class.',
		'imports' => 'The class or classes to import via the "use" statement.',
		'extends' => 'The class or classes or abstract class or classes from your class will extend.',
		'implements' => 'The interface which your class will implement.',	
		'traits' => 'The trait or traits which your class will inherit.',
		'singleton' => 'Utilize the "Singleton" design pattern in your class.',
		'magic-get-set' => 'Include magic "getter" [ __get() ] and "setter" [ __set() ] methods.',
		'constants' => 'The constant or constants specific to your class.',
		'public-properties' => 'The public property or properties specific to your class.',
		'protected-properties' => 'The protected property or properties specific to your class.',
		'private-properties' => 'The private property or properties specific to your class.',
		'public-static-properties' => 'The public static property or properties specific to your class.',
		'protected-static-properties' => 'The protected static property or properties specific to your class.',
		'private-static-properties' => 'The private static property or properties specific to your class.'

	];
	protected $commandOptionName; // = [];
	protected $commandOptionDescription; // = [];
	
	private $fileContent; // = '';
	
	public function __construct()
	{
		$this->setCommandInfo()
			   ->setCommandArguments()
			   ->setCommandOptions();
		parent::__construct();
		return $this;
	}
	
	protected function configure()
	{
		$this->setName( $this->commandName )
			   ->setDescription( $this->commandDescription );
		foreach( $this->commandArgumentName as $index => $argumentName )
		{
			if( $argumentName === 'classname' )
			{
				$this->addArgument(
					$argumentName,
					InputArgument::REQUIRED,
					$this->commandArgumentDescription[ $index ]
				);
			}
			if( $argumentName === 'savedir' )
			{
				$this->addArgument(
					$argumentName,
					InputArgument::OPTIONAL,
					$this->commandArgumentDescription[ $index ],
					dirname( dirname( dirname( __DIR__ ) ) ) . DIRECTORY_SEPARATOR
				);
			}
		}
		foreach( $this->commandOptionName as $index => $optionName )
		{
			if( in_array( $optionName, ['namespace','implements'] ) )
			{
				$this->addOption(
					$optionName,
					NULL,
					InputOption::VALUE_REQUIRED,
					$this->commandOptionDescription[ $index ],
					NULL
				);
			}
			elseif( in_array( $optionName, ['singleton','magic-get-set'] ) )
			{
				$this->addOption(
					$optionName,
					NULL,
					InputOption::VALUE_OPTIONAL,
					$this->commandOptionDescription[ $index ],
					NULL
				);
			}
			else
			{
				$this->addOption(
					$optionName,
					NULL,
					InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
					$this->commandOptionDescription[ $index ],
					[]
				);
			}
		}
	}
	
	protected function execute( InputInterface $input, OutputInterface $output )
	{
		$classname = $input->getArgument( $this->commandArgumentName[0] );
		$savedir = $input->getArgument( $this->commandArgumentName[1] );
		
		$namespace = $imports = $extends = $implements = $traits = $singleton = $magic_get_set = $constants = $public_properties = $protected_properties = $private_properties = $public_static_properties = $protected_static_properties = $private_static_properties = NULL;
		
		foreach( $this->commandOptionName as $index => $optionName )
		{

			if( $optionName === 'namespace' )
			{
				if( is_string( $input->getOption( $optionName ) ) && strlen( $input->getOption( $optionName ) ) > 1 )
				{
					$namespace = 'namespace ' . $input->getOption( $optionName ) . ";\n";
				}
			}
			if( $optionName === 'imports' )
			{
				if( is_array( $input->getOption( $optionName ) ) && (! empty( $input->getOption( $optionName ) ) ) )
				{
					$imports = '';
					foreach( $input->getOption( $optionName ) as $index => $import )
					{
						$imports .= ( $index === count( $input->getOption( $optionName ) ) - 1 ) ? 'use ' . $import : 'use ' . $import . ";\n";
					}
				}
			}
			if( $optionName === 'extends' )
			{
				if( is_array( $input->getOption( $optionName ) ) && (! empty( $input->getOption( $optionName ) ) ) )
				{
					$extends = ' extends ';
					foreach( $input->getOption( $optionName ) as $index => $extend )
					{
						$extends .= ( $index === count( $input->getOption( $optionName ) ) - 1 ) ? $extend : $extend . ', ';
					}
				}
			}
			if( $optionName === 'implements' )
			{
				if( is_string( $input->getOption( $optionName ) ) && strlen( $input->getOption( $optionName ) ) > 1 )
				{
					$implements = ' implements ' . $input->getOption( $optionName ) . ";\n";
				}
			}
			if( $optionName === 'traits' )
			{
				if( is_array( $input->getOption( $optionName ) ) && (! empty( $input->getOption( $optionName ) ) ) )
				{
					$traits = 'use ';
					foreach( $input->getOption( $optionName ) as $index => $trait )
					{
						$traits .= ( $index === count( $input->getOption( $optionName ) ) - 1 ) ? $trait : $trait . ', ';
					}
				}
			}
			if( $optionName === 'singleton' )
			{
				if( $input->getOption( $optionName ) !== NULL )
				{
					$singleton = true;
				}
			}
			if( $optionName === 'magic-get-set' )
			{
				if( $input->getOption( $optionName ) !== NULL )
				{
					$magic_get_set = true;
				}
			}
			if( $optionName === 'constants' )
			{
				if( is_array( $input->getOption( $optionName ) ) && (! empty( $input->getOption( $optionName ) ) ) )
				{
					$constants = '';
					foreach( $input->getOption( $optionName ) as $index => $constant )
					{
						$key_value = explode( '=', $constant );
						$key = $key_value[0];
						$value = $key_value[1];
						$constants .= ( $index === count( $input->getOption( $optionName ) ) - 1 ) ? "const ". strtoupper( $key ) . " = {$value};" :  "const ". strtoupper( $key ) . " = '{$value}';\n";
					}
				}
			}
			if( $optionName === 'public-properties' )
			{
				if( is_array( $input->getOption( $optionName ) ) && (! empty( $input->getOption( $optionName ) ) ) )
				{
					$public_properties = '';
					foreach( $input->getOption( $optionName ) as $index => $public_property )
					{
						$key_value = explode( '=', $public_property );
						$key = $key_value[0];
						$value = $key_value[1];
						$public_properties .= ( $index === count( $input->getOption( $optionName ) ) - 1 ) ? "public \$". $key . " = {$value};" :  "public \$". $key . " = {$value};\n";
					}
				}
			}
			if( $optionName === 'protected-properties' )
			{
				if( is_array( $input->getOption( $optionName ) ) && (! empty( $input->getOption( $optionName ) ) ) )
				{
					$protected_properties = '';
					foreach( $input->getOption( $optionName ) as $index => $protected_property )
					{
						$key_value = explode( '=', $protected_property );
						$key = $key_value[0];
						$value = $key_value[1];
						$protected_properties .= ( $index === count( $input->getOption( $optionName ) ) - 1 ) ? "protected \$". $key . " = {$value};" :  "protected \$". $key . " = {$value};\n";
					}
				}
			}
			if( $optionName === 'private-properties' )
			{
				if( is_array( $input->getOption( $optionName ) ) && (! empty( $input->getOption( $optionName ) ) ) )
				{
					$private_properties = '';
					foreach( $input->getOption( $optionName ) as $index => $private_property )
					{
						$key_value = explode( '=', $private_property );
						$key = $key_value[0];
						$value = $key_value[1];
						$private_properties .= ( $index === count( $input->getOption( $optionName ) ) - 1 ) ? "private \$". $key . " = {$value};" :  "private \$". $key . " = {$value};\n";
					}
				}
			}
			if( $optionName === 'public-static-properties' )
			{
				if( is_array( $input->getOption( $optionName ) ) && (! empty( $input->getOption( $optionName ) ) ) )
				{
					$public_static_properties = '';
					foreach( $input->getOption( $optionName ) as $index => $public_static_property )
					{
						$key_value = explode( '=', $public_static_property );
						$key = $key_value[0];
						$value = $key_value[1];
						$public_static_properties .= ( $index === count( $input->getOption( $optionName ) ) - 1 ) ? "public static \$". $key . " = {$value};" :  "public static \$". $key . " = {$value};\n";
					}
				}
			}
			if( $optionName === 'protected-static-properties' )
			{
				if( is_array( $input->getOption( $optionName ) ) && (! empty( $input->getOption( $optionName ) ) ) )
				{
					$protected_static_properties = '';
					foreach( $input->getOption( $optionName ) as $index => $protected_static_property )
					{
						$key_value = explode( '=', $protected_static_property );
						$key = $key_value[0];
						$value = $key_value[1];
						$protected_static_properties .= ( $index === count( $input->getOption( $optionName ) ) - 1 ) ? "protected static \$". $key . " = {$value};" :  "protected static \$". $key . " = {$value};\n";
					}
				}
			}
			if( $optionName === 'private-static-properties' )
			{
				if( is_array( $input->getOption( $optionName ) ) && (! empty( $input->getOption( $optionName ) ) ) )
				{
					$private_static_properties = '';
					foreach( $input->getOption( $optionName ) as $index => $private_static_property )
					{
						$key_value = explode( '=', $private_static_property );
						$key = $key_value[0];
						$value = $key_value[1];
						$private_static_properties .= ( $index === count( $input->getOption( $optionName ) ) - 1 ) ? "private static \$". $key . " = {$value};" :  "private static \$". $key . " = {$value};\n";
					}
				}
			}
		}	
		$classname = ucwords( $classname );
		$savedir = preg_replace( '/[\/\\\\]/', DIRECTORY_SEPARATOR, $savedir );
		$savedir = ( preg_match( '/[\/\\\\]$/', $savedir ) ) ? $savedir : $savedir . DIRECTORY_SEPARATOR;
			
		$this->fileContent .=<<<EOT

{$namespace}

{$imports}
{$traits}

class {$classname} {$extends} {$implements}
{
	
	{$traits}
	
	{$constants}
	
	{$public_properties}
	{$protected_properties}
	{$private_properties}
	
	{$public_static_properties}
	{$protected_static_properties}
	{$private_static_properties}
	
EOT;
			
		if( $singleton === NULL )
		{
			$this->fileContent .=<<<EOT
	
	public function __construct()
	{
		return \$this;
	}

EOT;

		}
		else
		{
			$this->fileContent .=<<<EOT
	
	protected static \$instance = \N\U\L\L;
	
	private function __construct()
	{
		return \$this;
	}
	
	private function __invoke()
	{
		//
	}
	
	private function __clone()
	{
		//
	}
	
	private function __sleep()
	{
		//
	}
	
	private function __wake()
	{
		//
	}
	
	public static function getInstance()
	{
		if( self::\$instance === \N\U\L\L )
		{
			self::\$instance = new self;
		}
		return self::\$instance;
	}

EOT;

		}
		
		if( $magic_get_set !== NULL )
		{
			$this->fileContent .=<<<EOT

	public function __set( \$property, \$value )
	{
		\$this->\$property = \$value;
		return \$this;
	}

	public function __get( \$property )
	{
		return \$this->\$property;
	}

EOT;

		}
			
		$this->fileContent .=<<<EOT
			
}

EOT;
			
		$this->fileContent = preg_replace( "/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $this->fileContent );
		if(! file_exists( $savedir . $classname . '.php' ) )
		{
			$file = fopen( $savedir . $classname . '.php', 'w' );
			if( $file )
			{
				fwrite( $file, html_entity_decode( $this->fileContent, ENT_HTML5 ) );
				$output->writeln( 'Your class ' . $classname . ' got initialized, was populated with your specified data, and has been saved in ' . $savedir . ' successfully.' );
			}
			else
			{
				$output->writeln( 'Your class ' . $classname . ' could not be saved in ' . $savedir . ', due to this error: ' . error_get_last() . '.' );
			}
			fclose( $file );
		}
		else
		{
			$output->writeln( 'A file with the same name as ' . $classname . ' already exists in ' . $savedir . '.' );
		}
	}
	
	private function setCommandInfo()
	{
		$this->fileContent = '&lt;?php';
		foreach( static::COMMAND_INFO as $name => $description )
		{
			if(! isset( $this->commandName, $this->commandDescription ) )
			{
				$this->commandName = $name;
				$this->commandDescription = $description;
			}
		}
		return $this;
	}
	
	private function setCommandArguments()
	{
		foreach( static::COMMAND_ARGUMENTS_INFO as $argumentName => $argumentDescription )
		{
			if(! isset( $this->commandArgumentName, $this->commandArgumentDescription ) )
			{
				$this->commandArgumentName = [];
				$this->commandArgumentDescription = [];
			}
			array_push( $this->commandArgumentName, $argumentName );
			array_push( $this->commandArgumentDescription, $argumentDescription );
		}
		return $this;
	}
	
	private function setCommandOptions()
	{
		foreach( static::COMMAND_OPTIONS_INFO as $optionName => $optionDescription )
		{
			if(! isset( $this->commandOptionName, $this->commandOptionDescription ) )
			{
				$this->commandOptionName = [];
				$this->commandOptionDescription = [];
			}
			array_push( $this->commandOptionName, $optionName );
			array_push( $this->commandOptionDescription, $optionDescription );
		}
		return $this;
	}
	
}