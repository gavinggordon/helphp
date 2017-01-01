<?php

namespace GGG\ConsoleApp\Command\Create;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use GGG\ConsoleApp\Service\DocumentCreator as DocumentCreator;

class ClassCommand extends Command
{
	const NL = "\n";
	const NLNL = "\n\n";
	const DIR_SEP = DIRECTORY_SEPARATOR;
	const BASE_DIR = dirname( dirname( dirname( dirname( __DIR__ ) ) ) );
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
		// --private-properties, -v
		'private-properties' => 'The private property or properties specific to your class.',
		// --public-static-properties, -P
		'public-static-properties' => 'The public static property or properties specific to your class.',
		// --protected-static-properties, -R
		'protected-static-properties' => 'The protected static property or properties specific to your class.',
		// --private-static-properties, -V
		'private-static-properties' => 'The private static property or properties specific to your class.'
	];
	
	protected $commandName; // = '';
	protected $commandDescription; // = '';
	
	protected $commandArgumentName; // = [];
	protected $commandArgumentDescription; // = [];

	protected $commandOptionName; // = [];
	protected $commandOptionDescription; // = [];
	
	private $documentCreator; // DocumentCreator;
	private $tempSaveDir; // '';
	private $fileContent; // = '';
	
	public function __construct( DocumentCreator $documentCreator )
	{
		$this->documentCreator = $documentCreator;
		$this->setAppropriateSaveDir()
			   ->setCommandInfo()
			   ->setCommandArguments()
			   ->setCommandOptions();
		parent::__construct();
		return $this;
	}
	
	private function setAppropriateSaveDir()
	{
		$baseDir = ( is_dir( static::BASE_DIR . static::DIR_SEP . 'vendor' ) && file_exists( static::BASE_DIR . static::DIR_SEP . 'vendor' . static::DIR_SEP . 'autoload.php' ) ) 
						 ? static::BASE_DIR . static::DIR_SEP : 
						 ( is_dir( static::BASE_DIR . static::DIR_SEP . '..' . static::DIR_SEP . 'vendor' ) && file_exists( static::BASE_DIR . static::DIR_SEP . '..' . static::DIR_SEP . 'vendor' . static::DIR_SEP . 'autoload.php' ) ) 
						 ? static::BASE_DIR . static::DIR_SEP . '..' . static::DIR_SEP : 
						 dirname( dirname( __DIR__ ) ) . static::DIR_SEP;
		if(! is_dir( $baseDir . 'tmp' ) )
		{
			if( mkdir( $baseDir . 'tmp' . static::DIR_SEP . 'helphp', 0755, true ) )
			{
				chmod( $baseDir . 'tmp', 0755 );
				chmod( $baseDir . 'tmp' . static::DIR_SEP . 'helphp', 0755 );
			}
		}
		else
		{	
			if(! is_dir( $baseDir . 'tmp' . static::DIR_SEP . 'helphp' ) )
			{
				if( mkdir( $baseDir . 'tmp' . static::DIR_SEP . 'helphp', 0755 ) )
				{
					chmod( $baseDir . 'tmp' . static::DIR_SEP . 'helphp', 0755 );
				}
			}
		}
		$this->tempSaveDir = $baseDir . 'tmp' . static::DIR_SEP . 'helphp' . static::DIR_SEP;
		return $this;
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
			if( in_array( $optionName, ['namespace','implements'] ) )
			{
				$shortcode = substr( $optionName, 0, 1 );
				$this->addOption(
					$optionName,
					$shortcode,
					InputOption::VALUE_REQUIRED,
					$this->commandOptionDescription[ $index ],
					NULL
				);
			}
			elseif( in_array( $optionName, ['singleton','magic-get-set'] ) )
			{
				$this->addOption(
					$optionName,
					$shortcode,
					InputOption::VALUE_OPTIONAL,
					$this->commandOptionDescription[ $index ],
					NULL
				);
			}
			else
			{
				if( in_array( $optionName, ['public-properties','protected-properties','private-properties','public-static-properties','protected-static-properties','private-static-properties'] ) )
				{
					switch( $optionName )
					{
						case 'public-properties':
							$shortcode = 'p';
							break;
						case 'protected-properties':
							$shortcode = 'r'; 
							break;
						case 'private-properties':
							$shortcode = 'v';
							break;
						case 'public-static-properties':
							$shortcode = 'P';
							break;
						case 'protected-static-properties':
							$shortcode = 'R';
							break;
						case 'private-static-properties':
							$shortcode = 'V';
							break;	
					}
				}
				$this->addOption(
					$optionName,
					$shortcode,
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
		$savedir = $input->getArgument( $this->commandArgumentName[1] ) || NULL;
		
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
					$options[ $optionName ] = 'namespace ' . $input->getOption( $optionName ) . ';' . static::NLNL;
				}
			}
			if( $optionName === 'uses' )
			{
				if( is_array( $input->getOption( $optionName ) ) && (! empty( $input->getOption( $optionName ) ) ) )
				{
					$uses = '';
					foreach( $input->getOption( $optionName ) as $index => $use )
					{
						$uses .= ( $index === count( $input->getOption( $optionName ) ) - 1 ) ? 'use ' . $use . ';' . static::NLNL : 'use ' . $use . ';' . static::NL;
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
						$traits['external'] .= ( $index === count( $input->getOption( $optionName ) ) - 1 ) ? 'use ' . $trait . ';' . static::NLNL : 'use ' . $trait . ';' . static::NL;
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
						$traits['internal'] .= ( $index === count( $input->getOption( $optionName ) ) - 1 ) ? ( ( preg_match( '/[\s]+?as[\s]+?[A-Za-z0-9]+?[\;]?$/', $trait ) ) ? str_replace( ';', '', substr( $trait, strripos( $trait, 'as ' ) + 3 ) ) . ';' . static::NLNL : str_replace( ';', '', substr( $trait, strripos( $trait, trim('\\ ') ) + 1 ) ) . ';' . static::NLNL ) : ( ( preg_match( '/[\s]+?as[\s]+?[A-Za-z0-9]+?[\;]?$/', $trait ) ) ? str_replace( ';', '', substr( $trait, strripos( $trait, 'as ' ) + 3 ) ) . ', ' : str_replace( ';', '', substr( $trait, strripos( $trait, trim('\\ ') ) + 1 ) ) . ', ' );
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
								$constants .= ( $index === count( $input->getOption( $optionName ) ) - 1 ) ? "const {$key} = '{$value}';" . static::NLNL : "const {$key} = '{$value}';" . static::NL;
								break;
								
							case 'Float':
							case 'Array':
							case 'Object':
							case 'Integer':
							case 'Boolean':
							case 'Resource':
								$constants .= ( $index === count( $input->getOption( $optionName ) ) - 1 ) ? "const {$key} = {$value};" . static::NLNL :  "const {$key} = {$value};" . static::NL;
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
								$public_properties .= ( $index === count( $input->getOption( $optionName ) ) - 1 ) ? "public \${$key} = '{$value}';" . static::NLNL :  "public \${$key} = '{$value}';" . static::NL;
								break;
								
							case 'Float':
							case 'Array':
							case 'Object':
							case 'Integer':
							case 'Boolean':
							case 'Resource':
								$public_properties .= ( $index === count( $input->getOption( $optionName ) ) - 1 ) ? "public \${$key} = {$value};" . static::NLNL :  "public \${$key} = {$value};" . static::NL;
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
								$protected_properties .= ( $index === count( $input->getOption( $optionName ) ) - 1 ) ? "protected \${$key} = '{$value}';" . static::NLNL :  "protected \${$key} = '{$value}';" . static::NL;
								break;
								
							case 'Float':
							case 'Array':
							case 'Object':
							case 'Integer':
							case 'Boolean':
							case 'Resource':
								$protected_properties .= ( $index === count( $input->getOption( $optionName ) ) - 1 ) ? "protected \${$key} = {$value};" . static::NLNL :  "protected \${$key} = {$value};" . static::NL;
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
								$private_properties .= ( $index === count( $input->getOption( $optionName ) ) - 1 ) ? "private \${$key} = '{$value}';" . static::NLNL :  "private \${$key} = '{$value}';" . static::NL;
								break;
								
							case 'Float':
							case 'Array':
							case 'Object':
							case 'Integer':
							case 'Boolean':
							case 'Resource':
								$private_properties .= ( $index === count( $input->getOption( $optionName ) ) - 1 ) ? "private \${$key} = {$value};" . static::NLNL :  "private \${$key} = {$value};" . static::NL;
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
								$public_static_properties .= ( $index === count( $input->getOption( $optionName ) ) - 1 ) ? "public static \${$key} = '{$value}';" . static::NLNL :  "public static \${$key} = '{$value}';" . static::NL;
								break;
								
							case 'Float':
							case 'Array':
							case 'Object':
							case 'Integer':
							case 'Boolean':
							case 'Resource':
								$public_static_properties .= ( $index === count( $input->getOption( $optionName ) ) - 1 ) ? "public static \${$key} = {$value};" . static::NLNL :  "public static \${$key} = {$value};" . static::NL;
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
								$protected_static_properties .= ( $index === count( $input->getOption( $optionName ) ) - 1 ) ? "protected static\${$key} = '{$value}';" . static::NLNL :  "protected static\${$key} = '{$value}';" . static::NL;
								break;
								
							case 'Float':
							case 'Array':
							case 'Object':
							case 'Integer':
							case 'Boolean':
							case 'Resource':
								$protected_static_properties .= ( $index === count( $input->getOption( $optionName ) ) - 1 ) ? "protected static\${$key} = {$value};" . static::NLNL :  "protected static\${$key} = {$value};" . static::NL;
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
								$private_static_properties .= ( $index === count( $input->getOption( $optionName ) ) - 1 ) ? "private static \${$key} = '{$value}';" . static::NLNL :  "private static \${$key} = '{$value}';" . static::NL;
								break;
								
							case 'Float':
							case 'Array':
							case 'Object':
							case 'Integer':
							case 'Boolean':
							case 'Resource':
								$private_static_properties .= ( $index === count( $input->getOption( $optionName ) ) - 1 ) ? "private static \${$key} = {$value};" . static::NLNL :  "private static \${$key} = {$value};" . static::NL;
								break;
						}
					}
					$options[ str_replace( '-', '_', $optionName ) ] = $private_static_properties;
				}
			}
		}
		$this->documentCreator->create( 'class', $classname, $options );
	/**
		$classname = ucwords( $classname );
		$savedir = preg_replace( '/[\/\\\\]/', DIRECTORY_SEPARATOR, $savedir );
		$savedir = ( preg_match( '/[\/\\\\]$/', $savedir ) ) ? $savedir : $savedir . DIRECTORY_SEPARATOR;
			
		$this->fileContent .=<<<EOT

{$namespace}
{$uses}
{$traits['external']}
class {$classname}{$extends}{$implements}
{
	{$traits['internal']}
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
			$this->fileContent .=<<<'EOT'
	
	public function __construct()
	{
		return $this;
	}

EOT;

		}
		else
		{
			$this->fileContent .=<<<'EOT'
	
	protected static $instance = NULL;
	
	private function __construct()
	{
		return $this;
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
		if( self::$instance === NULL )
		{
			self::$instance = new self;
		}
		return self::$instance;
	}

EOT;

		}
		
		if( $magic_get_set !== NULL )
		{
			$this->fileContent .=<<<'EOT'

	public function __set( $property, $value )
	{
		$this->$property = $value;
		return $this;
	}

	public function __get( $property )
	{
		return $this->$property;
	}

EOT;

		}
			
		$this->fileContent .=<<<EOT
			
}

EOT;
		
		// original regex: "/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n"
		$this->fileContent = preg_replace( "/(^[\r\n]{2,})[\s\t]*[\r\n]+/", static::NLNL, $this->fileContent );
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
	**/
}