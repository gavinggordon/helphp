# Helphp PHP CLI

[![Latest Release](https://img.shields.io/github/release/gavinggordon/helphp.svg)](https://github.com/gavinggordon/helphp)  [![Version Tag](https://img.shields.io/github/tag/gavinggordon/helphp.svg)](https://github.com/gavinggordon/helphp)  [![Usage License](https://img.shields.io/github/license/gavinggordon/helphp.svg)](https://github.com/gavinggordon/helphp/blob/master/LICENSE.txt)

## Description
This is a PHP CLI package which provides some helpful functions that make creating PHP-related files easier and faster, whilst also maintaining strict visual file data continuity.

## Dependencies
	- [php](http://www.php.net "PHP.net Homepage") ^5.5
	- [symfony/config](https://github.com/symfony/config "Symfony/Config Github Page") ^3.2
	- [symfony/console](https://github.com/symfony/console "Symfony/ConsoleGithub Page") ^3.2
	- [symfony/dependency-injection](https://github.com/symfony/dependency-injection "Symfony/Dependency-Injection Github Page") ^3.2
	- [symfony/filesystem](https://github.com/symfony/filesystem "Symfony/Filesystem Github Page") ^3.2
	- [symfony/yaml](https://github.com/symfony/yaml "Symfony/Yaml Github Page") ^3.2
	- [pimple/pimple](https://github.com/pimple/pimple "Pimple/Pimple Github Page") ~3.0
	- [spatie/array-to-xml](https://github.com/spatie/array-to-xml "Spatie/Array-to-XML Github Page") ^2.2

## Installation
```shellscript
	$	composer require gavinggordon/helphp ^1.0.0-alpha1
```

## Usage
```shellscript
	$	php helphp create:class Test
```

## Commands Overview
	* Create
		- Generic Class
			* Arguments
				- 'classname'
				- 'savedir'
			* Options
				- '--namespace, -n'
				- '--uses, -u'
				- '--extends, -e'
				- '--implements, -i'
				- '--traits, -t'
				- '--singleton, -s'
				- '--magic-set-get, -n'
				- '--constants, -c'
				- '--public-properties, -p'
				- '--protected-properties, -r'
				- '--private-properties, -v'
				- '--public-static-properties, -P'
				- '--protected-static-properties, -R'
				- '--private-static-properties, -V'
		- Abstract Class (*Still In Production*)
		- Interface (*Still In Production*)
		- Trait (*Still In Production*)
	* Review (*Still In Production*)
	* Update (*Still In Production*)	
	* Delete (*Still In Production*)

## Issues
If you have any issues at all, please post your findings in the issues page at [https://github.com/gavinggordon/helphp/issues](https://github.com/gavinggordon/helphp/issues).

## License
This package utilizes the MIT License.