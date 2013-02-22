<?php
/**
 *  @package FrameworkOnFramework
 *  @copyright Copyright (c)2010-2013 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

/**
 * Automatic registration of FrameworkOnFramework's classes with JLoader
 * 
 * FrameworkOnFramework is a set of classes whcih extend Joomla! 1.5 and later's
 * MVC framework with features making maintaining complex software much easier,
 * without tedious repetitive copying of the same code over and over again.
 */

if(!defined('FOF_INCLUDED'))
{
	define('FOF_INCLUDED','revBEEDF0C');
	
	function _fof_autoloader($class_name) {
		static $fofPath = null;
		
		// Make sure the class has a FOF prefix
		if(substr($class_name,0,3) != 'FOF') return;
		
		if(is_null($fofPath)) {
			$fofPath = dirname(__FILE__);
		}
		
		// Remove the prefix
		$class = substr($class_name, 3);
		
		// Change from camel cased (e.g. ViewHtml) into a lowercase array (e.g. 'view','html')
		$class = preg_replace('/(\s)+/', '_', $class);
		$class = strtolower(preg_replace('/(?<=\\w)([A-Z])/', '_\\1', $class));
		$class = explode('_', $class);
		
		// First try finding in structured directory format (preferred)
		$path = $fofPath . '/' . implode('/', $class) . '.php';
		if(@file_exists($path)) {
			include_once $path;
		}
		
		// Then try the duplicate last name structured directory format (not recommended)
		if(!class_exists($class_name, false)) {
			$lastPart = array_pop($class);
			array_push($class, $lastPart);
			$path = $fofPath . '/' . implode('/', $class) . '/' . $lastPart . '.php';
			if(@file_exists($path)) {
				include_once $path;
			}
		}
		
		// If it still fails, try looking in the legacy folder (will be used in FOF2 for backwards compatibility)
		if(!class_exists($class_name, false)) {
			$path = $fofPath . '/legacy/' . implode('/', $class) . '.php';
			if(@file_exists($path)) {
				include_once $path;
			}
		}
		
		// If that failed, try the legacy flat directory structure (will be removed in FOF2)
		if(!class_exists($class_name, false)) {
			$path = $fofPath . '/' . implode('.', $class) . '.php';
			if(@file_exists($path)) {
				include_once $path;
			}
		}
	}

	// Register FOF's autoloader
	if( function_exists('spl_autoload_register') ) {
		// Joomla! is using its own autoloader function which has to be registered first...
		if(function_exists('__autoload')) spl_autoload_register('__autoload');
		// ...and then register ourselves.
		spl_autoload_register('_fof_autoloader');
	}  else {
		// Guys, 2012 is almost over at the time of this writing. If you have a
		// host which doesn't support SPL yet, SWITCH HOSTS!
		throw new Exception('Framework-on-Framework requires the SPL extension to be loaded and activated', 500);
	}
	
	// This is the old method (using JLoader). It is now obsolete. JLoader might be removed in future versions of Joomla!.
	function fofRegisterClasses()
	{
		throw new Exception('fofRegisterClasses was never designed to be called directly. Moreover, it is now obsolete and will be removed in FOF2. Please remove the call to it from your code.', 500);
	}
}
?>
