<?php
require_once __DIR__ . '/vendor/autoload.php'; // Autoload files using Composer autoload

use JasperPHP\JasperPHP;

JasperPHP::getInstance()
	->compile('examples/hello_world.jrxml')
	->execute();

JasperPHP::getInstance()
	->process(
		'examples/hello_world.jasper', 
	    false, 
	    array("pdf", "rtf"), 
	    array("php_version" => phpversion())
    )
    ->execute();

?>