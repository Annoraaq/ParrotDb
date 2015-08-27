<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * @author J. Baum
 */
// TODO: check include path
//ini_set('include_path', ini_get('include_path'));

use \ParrotDb\Core\PAutoloader;

define('ROOT_PATH', dirname(dirname(__FILE__)) . '/');
define('CORE_PATH', ROOT_PATH . 'ParrotDb/Core/');


echo CORE_PATH;
require_once CORE_PATH . 'PAutoloader.php';

$autoloader = new PAutoloader('ParrotDb');
$autoloader->register();
?>
