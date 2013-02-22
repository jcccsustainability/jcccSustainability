<?php
/**
 *  @package FrameworkOnFramework
 *  @copyright Copyright (c)2010-2013 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 */

defined('_JEXEC') or die;

/**
 * Joomla! 3.0.0-alpha2 view renderer class 
 */
class FOFRenderJoomla3 extends FOFRenderStrapper
{
	public function __construct() {
		$this->priority = 55;
		if(version_compare(JVERSION, '3.0', 'ge')) {
			$version = strtolower(JVERSION);
			if(substr($version, -7) == '_alpha2') $this->enabled = true;
		}
	}
	
	public function preRender($view, $task, $input, $config=array())
	{
		$format = FOFInput::getCmd('format', 'html', $input);
		if(empty($format)) $format = 'html';
		if($format != 'html') return;
		
		$this->renderButtons($view, $task, $input, $config);
		$this->renderLinkbar($view, $task, $input, $config);
	}
	
	public function postRender($view, $task, $input, $config=array())
	{
	}
	
	protected function renderButtons($view, $task, $input, $config=array())
	{
		// Do not render buttons unless we are in the the frontend area and we are asked to do so
		$toolbar = FOFToolbar::getAnInstance(FOFInput::getCmd('option','com_foobar',$input), $config);
		$renderFrontendButtons = $toolbar->getRenderFrontendButtons();
		
		list($isCli, $isAdmin) = FOFDispatcher::isCliAdmin();
		if($isAdmin || !$renderFrontendButtons) return;
		
		$bar = JToolBar::getInstance('toolbar');
		echo $bar->render();
	}
}