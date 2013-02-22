<?php
/**
 *  @package FrameworkOnFramework
 *  @copyright Copyright (c)2010-2013 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 */

defined('_JEXEC') or die;

/**
 * ABstract view renderer class 
 */
abstract class FOFRenderAbstract
{
	/** @var int Priority of this renderer. Higher means more important */
	protected $priority = 50;
	
	/** @var int Is this renderer enabled? */
	protected $enabled = false;
	
	/**
	 * Returns the information about this renderer
	 * 
	 * @return object
	 */
	public function getInformation()
	{
		return (object)array(
			'priority'	=> $this->priority,
			'enabled'	=> $this->enabled,
		);
	}
	
	/**
	 * Echoes any HTML to show before the view template
	 * 
	 * @param string $view The current view
	 * @param string $task The current task
	 * @param array $input The input array (request parameters)
	 * @param array $config The view configuration array
	 */
	abstract public function preRender($view, $task, $input, $config=array());
	
	/**
	 * Echoes any HTML to show after the view template
	 * 
	 * @param string $view The current view
	 * @param string $task The current task
	 * @param array $config The view configuration array
	 */
	abstract public function postRender($view, $task, $input, $config=array());
}