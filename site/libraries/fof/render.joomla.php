<?php
/**
 *  @package FrameworkOnFramework
 *  @copyright Copyright (c)2010-2013 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 */

defined('_JEXEC') or die;

/**
 * Default Joomla! 1.5, 1.7, 2.5 view renderer class
 */
class FOFRenderJoomla extends FOFRenderAbstract
{
	public function __construct() {
		$this->priority = 50;
		$this->enabled = true;
	}

	/**
	 * Echoes any HTML to show before the view template
	 *
	 * @param string $view The current view
	 * @param string $task The current task
	 * @param array $input The input array (request parameters)
	 */
	public function preRender($view, $task, $input, $config=array())
	{
		$format = FOFInput::getCmd('format', 'html', $input);
		if(empty($format)) $format = 'html';
		if($format != 'html') return;

		$this->renderButtons($view, $task, $input, $config);
		$this->renderLinkbar($view, $task, $input, $config);
	}

	/**
	 * Echoes any HTML to show after the view template
	 *
	 * @param string $view The current view
	 * @param string $task The current task
	 * @param array $input The input array (request parameters)
	 */
	public function postRender($view, $task, $input, $config=array())
	{

	}

	protected function renderLinkbar($view, $task, $input, $config=array())
	{
		// Do not render a submenu unless we are in the the admin area
		$toolbar = FOFToolbar::getAnInstance(FOFInput::getCmd('option','com_foobar',$input), $config);
		$renderFrontendSubmenu = $toolbar->getRenderFrontendSubmenu();

		list($isCli, $isAdmin) = FOFDispatcher::isCliAdmin();
		if(!$isAdmin && !$renderFrontendSubmenu) return;

		$links = $toolbar->getLinks();
		if(!empty($links)) {
			foreach($links as $link) {
				JSubMenuHelper::addEntry($link['name'], $link['link'], $link['active']);
			}
		}
	}

	protected function renderButtons($view, $task, $input, $config=array())
	{
		// Do not render buttons unless we are in the the frontend area and we are asked to do so
		$toolbar = FOFToolbar::getAnInstance(FOFInput::getCmd('option','com_foobar',$input), $config);
		$renderFrontendButtons = $toolbar->getRenderFrontendButtons();

		list($isCli, $isAdmin) = FOFDispatcher::isCliAdmin();
		if($isAdmin || !$renderFrontendButtons) return;

		// Load main backend language, in order to display toolbar strings
		// (JTOOLBAR_BACK, JTOOLBAR_PUBLISH etc etc)
		$jlang = JFactory::getLanguage();
		$jlang->load('joomla', JPATH_ADMINISTRATOR, null, true);

		$title = JFactory::getApplication()->get('JComponentTitle');
		$bar = JToolBar::getInstance('toolbar');

		// delete faux links, since if SEF is on, Joomla will follow the link instead of submitting the form
		$bar_content = str_replace('href="#"','', $bar->render());

		echo '<div id="FOFHeaderHolder">' , $bar_content , $title , '<div style="clear:both"></div>', '</div>';
	}
}