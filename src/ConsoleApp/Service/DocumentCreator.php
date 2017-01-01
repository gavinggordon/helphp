<?php

namespace GGG\ConsoleApp\Service;

use Symfony\Component\Yaml\Yaml;
use Spatie\ArrayToXml\ArrayToXml;

class DocumentCreator
{
	
	/*
	 * @var string $manifestFile
	 */
	protected $manifestFile;
	
	/*
	 * @var string $manifestFileType
	 */
	protected $manifestFileType;
	
	/*
	 * @var string $tmpSaveDir
	 */
	protected $tmpSaveDir;
	
	/*
	 * @var array $files
	 */
	 protected $files;
	 
	/*
	 * Constructor
	 * 
	 * @param string $manifestFile
	 * @param string $tmpSaveDir 
	 *
	 */
 	 public function __construct( $manifestFile, $tmpSaveDir )
	 {
	 	$this->manifestFile = $manifestFile;
		if( file_exists( $manifestFile ) )
		{
			$filetype = trim( strtolower( substr( $manifestFile, strripos( $manifestFile, '.' ) + 1 ) ) );
			switch( $filetype )
			{
				case 'yaml':
				case 'yml':
					$this->manifestFileType = 'yaml';
					$this->files = Yaml::parse( file_get_contents( $manifestFile ) );
					break;
				case 'xml':
					$this->manifestFileType = 'xml';
					$xmlparser = xml_parser_create();
					xml_parse_into_struct( $xmlparser, file_get_contents( $manifestFile ), $values );
					xml_parser_free( $xmlparser );
					$this->files = $values;
					break;
				case 'php':
					$this->manifestFileType = 'php';
					$this->files = require( $manifestFile );
					break;
				case 'json':
					$this->manifestFileType = 'json';
					$this->files = json_decode( file_get_contents( $manifestFile ), true );
					break;
				default:
					$this->files = [];
			}
		}
		if( is_dir( $tmpSaveDir ) )
		{
			$tmpSaveDir = preg_replace( '/[\/\\\\]/', DIRECTORY_SEPARATOR, $tmpSaveDir );
			$this->tmpSaveDir = ( preg_match( '/[\/\\\\]$/', $tmpSaveDir ) ) ? $tmpSaveDir : $tmpSaveDir . DIRECTORY_SEPARATOR;
		}
		return $this;
	 }
	 
	/*
	 * Destructor
	 */
	 public function __destruct()
	 {
	 	switch( $this->manifestFileType )
		{
			case 'yaml':
			case 'yml':
				file_put_contents( $this->manifestFile, Yaml::dump( $this->files ) );
				break;
			case 'xml':
				file_put_contents( $this->manifestFile, ArrayToXml::convert( $this->files ) );
				break;
			case 'php':
				$phpcode =<<<'EOT'
<?php

return [

EOT;
				
				$controlIndex = 0;
				foreach( $this->files as $fileIndex => $fileValue )
				{
					$comma = ( $controlIndex === count( $this->files ) - 1 ) ? "\n" : ",\n";
					$phpcode .=<<<EOT
	'{$fileIndex}' => '{$fileValue}'{$comma}
EOT;

					$controlIndex++;
				}
				$phpcode .=<<<EOT
];
EOT;

				file_put_contents( $this->manifestFile, $phpcode );
				break;
			case 'json':
				file_put_contents( $this->manifestFile, json_encode( $this->files, JSON_PRETTY_PRINT ) );
				break;
			default:
				file_put_contents( $this->manifestFile, print_r( $this->files ) );
		}
	 }
	 
	/*
	 * Create
	 * 
	 * @param string $type 'type of PHP file to create' ex. Class | Abstract | Trait | Interface
	 * @param string $classname 'name of the class to create' ex. TestClass | Test\TestClass
	 * @param assoc-array $options 'array of input options' ex. ['namespace' => 'TestNamespace']
	 * 
	 */
	 public function create( $type, $classname, $options )
	 {
	 	$classname = ucwords( $classname );
		if( isset( $options['savedir'] ) && (! empty( $options['savedir'] ) ) )
		{
			$options['savedir'] = preg_replace( '/[\/\\\\]/', DIRECTORY_SEPARATOR, $options['savedir'] );
			$this->tmpSaveDir = ( preg_match( '/[\/\\\\]$/', $options['savedir']  ) ) ? $options['savedir'] : $options['savedir']  . DIRECTORY_SEPARATOR;
		}
	 	if( (! isset( $this->files[ $this->tmpSaveDir . $classname . '.php' ] ) ) && (! file_exists( $this->tmpSaveDir . $classname . '.php' ) ) )
		{
			
			$fileContent .=<<<EOT

{$options['namespace']}
{$options['uses']}
{$options['traits']['external']}
class {$options['classname']} {$options['extends']} {$options['implements']}
{
	{$options['traits']['internal']}
	{$options['constants']}
	{$options['public_properties']}
	{$options['protected_properties']}
	{$options['private_properties']}
	{$options['public_static_properties']}
	{$options['protected_static_properties']}
	{$options['private_static_properties']}
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
		}
	 }
}