<?php

namespace GGG\ConsoleApp\Command\Create;

use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use GGG\ConsoleApp\Service\DocumentCreator as DocumentCreator;

class ClassCommand extends SymfonyCommand
{
	const COMMAND_INFO = [
		'create:class' => 'Creates a new class file, prepared just the way you like.'
	];
	const COMMAND_ARGUMENTS_INFO = [
		'classname' => 'The name to be given to your class.',
		'savedir' => 'The directory in which to save your class.'
	];
	const COMMAND_OPTIONS_INFO = [
		// --namespace, -n
		'namespace' => 'The namespace to use for your class.',
		// --uses, -u
		'uses' => 'The class or classes of which your class will make use, via the "use" statement.',
		// --extends, -e
		'extends' => 'The class or classes or abstract class or classes from your class will extend.',
		// --implements, -i
		'implements' => 'The interface which your class will implement.',
		// --traits, -t
		'traits' => 'The trait or traits which your class will inherit.',
		// --singleton, -s
		'singleton' => 'Utilize the "Singleton" design pattern in your class.',
		// --magic-get-set, -m
		'magic-get-set' => 'Include magic "getter" [ __get() ] and "setter" [ __set() ] methods.',
		// --constants, -c
		'constants' => 'The constant or constants specific to your class.',
		// --public-properties, -p
		'public-properties' => 'The public property or properties specific to your class.',
		// --protected-properties, -r
		'protected-properties' => 'The protected property or properties specific to your class.',
		// --private-properties, -w
		'private-properties' => 'The private property or properties specific to your class.',
		// --public-static-properties, -x
		'public-static-properties' => 'The public static property or properties specific to your class.',
		// --protected-static-properties, -y
		'protected-static-properties' => 'The protected static property or properties specific to your class.',
		// --private-static-properties, -z
		'private-static-properties' => 'The private static property or properties specific to your class.'
	];
	
	protected $commandName; // = '';
	protected $commandDescription; // = '';
	
	protected $commandArgumentName; // = [];
	protected $commandArgumentDescription; // = [];

	protected $commandOptionName; // = [];
	protected $commandOptionDescription; // = [];
	
	protected $documentCreator; // DocumentCreator;
	protected $tempSaveDir; // '';
	protected $baseDir; // '';
	protected $ds; // DIRECTORY_SEPARATOR; 
	
	public function __construct( DocumentCreator $documentCreator )
	{
		$this->documentCreator = $documentCreator;
		$this->setAppropriateSaveDir()
			   ->setCommandInfo()
			   ->setCommandArguments()
			   ->setCommandOptions();
		parent::__construct( 'helphp', '1.0.0-alpha1.0.6' );
		return $this;
	}
	
	private function setAppropriateBaseDir()
	{
		$this->ds = DIRECTORY_SEPARATOR;
		$this->baseDir = dirname( dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) ); 
		return $this;
	}
	
	private function setAppropriateSaveDir()
	{
		$baseDir = ( is_dir( $this->baseDir . $this->ds . 'vendor' ) && file_exists( $this->baseDir . $this->ds . 'vendor' . $this->ds . 'autoload.php' ) ) 
						 ? $this->baseDir . $this->ds : 
						 ( is_dir( $this->baseDir . $this->ds . '..' . $this->ds . 'vendor' ) && file_exists( $this->baseDir . $this->ds . '..' . $this->ds . 'vendor' . $this->ds . 'autoload.php' ) ) 
						 ? $this->baseDir . $this->ds . '..' . $this->ds : 
						 dirname( dirname( __DIR__ ) ) . $this->ds;
		if(! is_dir( $baseDir . 'tmp' ) )
		{
			if( mkdir( $baseDir . 'tmp' . $this->ds . 'helphp', 0755, true ) )
			{
				chmod( $baseDir . 'tmp', 0755 );
				chmod( $baseDir . 'tmp' . $this->ds . 'helphp', 0755 );
			}
		}
		else
		{	
			if(! is_dir( $baseDir . 'tmp' . $this->ds . 'helphp' ) )
			{
				if( mkdir( $baseDir . 'tmp' . $this->ds . 'helphp', 0755 ) )
				{
					chmod( $baseDir . 'tmp' . $this->ds . 'helphp', 0755 );
				}
			}
		}
		$this->tempSaveDir = $baseDir . 'tmp' . $this->ds . 'helphp' . $this->ds;
		return $this;
	}
	
	private function setCommandInfo()
	{
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
	
	protected function configure()
	{
		$this->setName( $this->commandName )
			   ->setDescription( $this->commandDescription );
		foreach( $this->commandArgumentName as $index => $argumentName )
		{
			// Set required argument "classname"
			if( $argumentName === 'classname' )
			{
				$this->addArgument(
					$argumentName,
					InputArgument::REQUIRED,
					$this->commandArgumentDescription[ $index ]
				);
			}
			// Set optional argument "savedir"
			if( $argumentName === 'savedir' )
			{
				$this->addArgument(
					$argumentName,
					InputArgument::OPTIONAL,
					$this->commandArgumentDescription[ $index ],
					$this->tempSaveDir
				);
			}
		}
		// Set each option accordingly
		foreach( $this->commandOptionName as $index => $optionName )
		{
			$shortcode = NULL;
			$inputoption = NULL;
			$default = NULL;
			$inputoptions = [
				'required' => InputOption::VALUE_REQUIRED,
				'optional' => InputOption::VALUE_OPTIONAL,
				'required_array' => ( InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY )
			];
			
			switch( $optionName )
			{
				case 'namespace':
					$shortcode = 'a';
					$inputoption = $inputoptions['required'];
					$default = NULL;
					break;
				case 'uses': 
					$shortcode = 'u';
					$inputoption = $inputoptions['required_array'];
					$default = [];
					break;
				case 'extends':
					$shortcode = 'e';
					$inputoption = $inputoptions['required_array'];
					$default = [];
					break;
				case 'implements':
					$shortcode = 'i';
					$inputoption = $inputoptions['required'];
					$default = NULL;
					break;
				case 'traits':
					$shortcode = 't';
					$inputoption = $inputoptions['required_array'];
					$default = [];
					break;
				case 'singleton':
					$shortcode = 's';
					$inputoption = $inputoptions['optional'];
					$default = NULL;
					break;
				case 'magic-get-set':
					$shortcode = 'm';
					$inputoption = $inputoptions['optional'];
					$default = NULL;
					break;
				case 'constants':
					$shortcode = 'c';
					$inputoption = $inputoptions['required_array'];
					$default = [];
					break;
				case 'public-properties':
					$shortcode = 'p';
					$inputoption = $inputoptions['required_array'];
					$default = [];
					break;
				case 'protected-properties':
					$shortcode = 'r'; 
					$inputoption = $inputoptions['required_array'];
					$default = [];
					break;
				case 'private-properties':
					$shortcode = 'w';
					$inputoption = $inputoptions['required_array'];
					$default = [];
					break;
				case 'public-static-properties':
					$shortcode = 'x';
					$inputoption = $inputoptions['required_array'];
					$default = [];
					break;
				case 'protected-static-properties':
					$shortcode = 'y';
					$inputoption = $inputoptions['required_array'];
					$default = [];
					break;
				case 'private-static-properties':
					$shortcode = 'z';
					$inputoption = $inputoptions['required_array'];
					$default = [];
					break;	
			}
			$this->addOption(
				$optionName,
				$shortcode,
				$inputoption,
				$this->commandOptionDescription[ $index ],
				$default
			);
		}
	}
	
	protected function execute( InputInterface $input, OutputInterface $output )
	{
		$classname = $input->getArgument( $this->commandArgumentName[0] );
		$savedir = $input->getArgument( $this->commandArgumentName[1] );
		
		// $namespace = $uses = $extends = $implements = $traits = $singleton = $magic_get_set = $constants = $public_properties = $protected_properties = $private_properties = $public_static_properties = $protected_static_properties = $private_static_properties = NULL;
		$options = [
			'savedir' => $savedir,
			'namespace' => NULL,
			'uses' => NULL,
			'extends' => NULL,
			'implements' => NULL,
			'traits' => NULL,
			'singleton' => NULL,
			'magic_get_set' => NULL,
			'constants' => NULL,
			'public_properties' => NULL,
			'protected_properties' => NULL,
			'private_properties' => NULL,
			'public_static_properties' => NULL,
			'protected_static_properties' => NULL,
			'private_static_properties' => NULL
		];
		
		foreach( $this->commandOptionName as $index => $optionName )
		{

			if( $optionName === 'namespace' )
			{
				if( is_string( $input->getOption( $optionName ) ) && strlen( $input->getOption( $optionName ) ) > 1 )
				{
					$options[ $optionName ] = 'namespace ' . $input->getOption( $optionName ) . ';' . "\n\n";
				}
			}
			if( $optionName === 'uses' )
			{
				if( is_array( $input->getOption( $optionName ) ) && (! empty( $input->getOption( $optionName ) ) ) )
				{
					$uses = '';
					foreach( $input->getOption( $optionName ) as $index => $use )
					{
						$uses .= ( $index === count( $input->getOption( $optionName ) ) - 1 ) ? 'use ' . $use . ';' . "\n\n" : 'use ' . $use . ';' . "\n";
					}
					$options[ $optionName ] = $uses;
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
					$options[ $optionName ] = $extends;
				}
			}
			if( $optionName === 'implements' )
			{
				if( is_string( $input->getOption( $optionName ) ) && strlen( $input->getOption( $optionName ) ) > 1 )
				{
					$options[ $optionName ] = ' implements ' . $input->getOption( $optionName );
				}
			}
			if( $optionName === 'traits' )
			{
				if( is_array( $input->getOption( $optionName ) ) && (! empty( $input->getOption( $optionName ) ) ) )
				{
					$traits = [
						'external' => '',
						'internal' => 'use '
					];
					foreach( $input->getOption( $optionName ) as $index => $trait )
					{
						$traits['external'] .= ( $index === count( $input->getOption( $optionName ) ) - 1 ) ? 'use ' . $trait . ';' . "\n\n" : 'use ' . $trait . ';' . "\n";
						/*
						 * if trait is the LAST option:
						 *	  if LAST trait ends with "{...} as {...}":
						 *     "Tests\Test\Class as Class" becomes "Class;\n\n"
						 *   else (for example, if LAST trait ends with ";" or does not end with "{...} as {...}"):
						 *      "Tests\Test\Class;" becomes "Class;\n\n"
						 * else (for example, if trait is NOT the last option):
						 *	  if trait ends with "{...} as {...}":
						 *      "Tests\Test\Class as Class" becomes "Class, "
						 *   else (for example, if trait ends with ";" or does not end with "{...} as {...}"):
						 *      "Tests\Test\Class;" becomes "Class, "
						 */
						$traits['internal'] .= ( $index === count( $input->getOption( $optionName ) ) - 1 ) ? ( ( preg_match( '/[\s]+?as[\s]+?[A-Za-z0-9]+?[\;]?$/', $trait ) ) ? str_replace( ';', '', substr( $trait, strripos( $trait, 'as ' ) + 3 ) ) . ';' . "\n\n" : str_replace( ';', '', substr( $trait, strripos( $trait, trim('\\ ') ) + 1 ) ) . ';' . "\n\n" ) : ( ( preg_match( '/[\s]+?as[\s]+?[A-Za-z0-9]+?[\;]?$/', $trait ) ) ? str_replace( ';', '', substr( $trait, strripos( $trait, 'as ' ) + 3 ) ) . ', ' : str_replace( ';', '', substr( $trait, strripos( $trait, trim('\\ ') ) + 1 ) ) . ', ' );
					}
					$options[ $optionName ] = $traits;
				}
			}
			if( $optionName === 'singleton' )
			{
				if( $input->getOption( $optionName ) !== NULL )
				{
					$options[ $optionName ] = true;
				}
			}
			if( $optionName === 'magic-get-set' )
			{
				if( $input->getOption( $optionName ) !== NULL )
				{
					$options[ str_replace( '-', '_', $optionName ) ] = true;
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
						$key = strtoupper( $key_value[0] );
						$value = $key_value[1];
						switch( gettype( $value ) )
						{
							case 'String':
								$constants .= ( $index === count( $input->getOption( $optionName ) ) - 1 ) ? "const {$key} = '{$value}';" . "\n\n" : "const {$key} = '{$value}';" . "\n";
								break;
								
							case 'Float':
							case 'Array':
							case 'Object':
							case 'Integer':
							case 'Boolean':
							case 'Resource':
								$constants .= ( $index === count( $input->getOption( $optionName ) ) - 1 ) ? "const {$key} = {$value};" . "\n\n" :  "const {$key} = {$value};" . "\n";
								break;
						}
					}
					$options[ $optionName ] = $constants;
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
						switch( gettype( $value ) )
						{
							case 'String':
								$public_properties .= ( $index === count( $input->getOption( $optionName ) ) - 1 ) ? "public \${$key} = '{$value}';" . "\n\n" :  "public \${$key} = '{$value}';" . "\n";
								break;
								
							case 'Float':
							case 'Array':
							case 'Object':
							case 'Integer':
							case 'Boolean':
							case 'Resource':
								$public_properties .= ( $index === count( $input->getOption( $optionName ) ) - 1 ) ? "public \${$key} = {$value};" . "\n\n" :  "public \${$key} = {$value};" . "\n";
								break;
						}
					}
					$options[ str_replace( '-', '_', $optionName ) ] = $public_properties;
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
						switch( gettype( $value ) )
						{
							case 'String':
								$protected_properties .= ( $index === count( $input->getOption( $optionName ) ) - 1 ) ? "protected \${$key} = '{$value}';" . "\n\n" :  "protected \${$key} = '{$value}';" . "\n";
								break;
								
							case 'Float':
							case 'Array':
							case 'Object':
							case 'Integer':
							case 'Boolean':
							case 'Resource':
								$protected_properties .= ( $index === count( $input->getOption( $optionName ) ) - 1 ) ? "protected \${$key} = {$value};" . "\n\n" :  "protected \${$key} = {$value};" . "\n";
								break;
						}
					}
					$options[ str_replace( '-', '_', $optionName ) ] = $protected_properties;
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
						switch( gettype( $value ) )
						{
							case 'String':
								$private_properties .= ( $index === count( $input->getOption( $optionName ) ) - 1 ) ? "private \${$key} = '{$value}';" . "\n\n" :  "private \${$key} = '{$value}';" . "\n";
								break;
								
							case 'Float':
							case 'Array':
							case 'Object':
							case 'Integer':
							case 'Boolean':
							case 'Resource':
								$private_properties .= ( $index === count( $input->getOption( $optionName ) ) - 1 ) ? "private \${$key} = {$value};" . "\n\n" :  "private \${$key} = {$value};" . "\n";
								break;
						}
					}
					$options[ str_replace( '-', '_', $optionName ) ] = $private_properties;
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
						switch( gettype( $value ) )
						{
							case 'String':
								$public_static_properties .= ( $index === count( $input->getOption( $optionName ) ) - 1 ) ? "public static \${$key} = '{$value}';" . "\n\n" :  "public static \${$key} = '{$value}';" . "\n";
								break;
								
							case 'Float':
							case 'Array':
							case 'Object':
							case 'Integer':
							case 'Boolean':
							case 'Resource':
								$public_static_properties .= ( $index === count( $input->getOption( $optionName ) ) - 1 ) ? "public static \${$key} = {$value};" . "\n\n" :  "public static \${$key} = {$value};" . "\n";
								break;
						}
					}
					$options[ str_replace( '-', '_', $optionName ) ] = $public_static_properties;
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
						switch( gettype( $value ) )
						{
							case 'String':
								$protected_static_properties .= ( $index === count( $input->getOption( $optionName ) ) - 1 ) ? "protected static \${$key} = '{$value}';" . "\n\n" :  "protected static \${$key} = '{$value}';" . "\n";
								break;
								
							case 'Float':
							case 'Array':
							case 'Object':
							case 'Integer':
							case 'Boolean':
							case 'Resource':
								$protected_static_properties .= ( $index === count( $input->getOption( $optionName ) ) - 1 ) ? "protected static \${$key} = {$value};" . "\n\n" :  "protected static \${$key} = {$value};" . "\n";
								break;
						}
					}
					$options[ str_replace( '-', '_', $optionName ) ] = $protected_static_properties;
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
						switch( gettype( $value ) )
						{
							case 'String':
								$private_static_properties .= ( $index === count( $input->getOption( $optionName ) ) - 1 ) ? "private static \${$key} = '{$value}';" . "\n\n" :  "private static \${$key} = '{$value}';" . "\n";
								break;
								
							case 'Float':
							case 'Array':
							case 'Object':
							case 'Integer':
							case 'Boolean':
							case 'Resource':
								$private_static_properties .= ( $index === count( $input->getOption( $optionName ) ) - 1 ) ? "private static \${$key} = {$value};" . "\n\n" :  "private static \${$key} = {$value};" . "\n";
								break;
						}
					}
					$options[ str_replace( '-', '_', $optionName ) ] = $private_static_properties;
				}
			}
		}
		$this->documentCreator->create( 'class', $classname, $options );	
	}
}
