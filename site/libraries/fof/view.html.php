<?php
/**
 *  @package FrameworkOnFramework
 *  @copyright Copyright (c)2010-2013 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

jimport('joomla.application.component.view');

/**
 * FrameworkOnFramework HTML List View class
 *
 * FrameworkOnFramework is a set of classes which extend Joomla! 1.5 and later's
 * MVC framework with features making maintaining complex software much easier,
 * without tedious repetitive copying of the same code over and over again.
 */
class FOFViewHtml extends FOFView
{
	/** @var array Data lists */
	protected $lists = null;

	/** @var array Permissions map */
	protected $perms = null;

	/**
	 * Class constructor
	 *
	 * @param array $config Configuration parameters
	 */
	function  __construct($config = array()) {
		parent::__construct($config);

		$this->config = $config;

		// Get the input
		if(array_key_exists('input', $config)) {
			$this->input = $config['input'];
		} else {
			$this->input = JRequest::get('default', 3);
		}

		$this->lists = new JObject();

		if(version_compare(JVERSION, '1.6.0', 'ge')) {
			$user = JFactory::getUser();
			$perms = (object)array(
				'create'	=> $user->authorise('core.create', FOFInput::getCmd('option','com_foobar',$this->input) ),
				'edit'		=> $user->authorise('core.edit', FOFInput::getCmd('option','com_foobar',$this->input)),
				'editstate'	=> $user->authorise('core.edit.state', FOFInput::getCmd('option','com_foobar',$this->input)),
				'delete'	=> $user->authorise('core.delete', FOFInput::getCmd('option','com_foobar',$this->input)),
			);
		} else {
			$perms = (object)array(
				'create'	=> true,
				'edit'		=> true,
				'editstate'	=> true,
				'delete'	=> true,
			);
		}
		$this->assign('aclperms', $perms);
		$this->perms = $perms;
	}

	/**
	 * Displays the view
	 *
	 * @param string $tpl The template to use
	 *
	 * @return bool
	 */
	function  display($tpl = null)
	{
		// Get the task set in the model
		$model = $this->getModel();
		$task = $model->getState('task','browse');

		// Call the relevant method
		$method_name = 'on'.ucfirst($task);
		if(method_exists($this, $method_name)) {
			$result = $this->$method_name($tpl);
		} else {
			$result = $this->onDisplay();
		}

		if($result === false) { return; }

		$toolbar = FOFToolbar::getAnInstance(FOFInput::getCmd('option','com_foobar',$this->input), $this->config);
		$toolbar->perms = $this->perms;
		$toolbar->renderToolbar(FOFInput::getCmd('view','cpanel',$this->input), $task, $this->input);

		// Show the view
		$this->preRender();
		parent::display($tpl);
		$this->postRender();
	}
	
	/**
	 * Renders the link bar (submenu) using Joomla!'s default JSubMenuHelper::addEntry method (which doesn't work under Joomla! 3.x and the Isis template)
	 */
	private function renderLinkbar()
	{
		// Do not render a submenu unless we are in the the admin area
		list($isCli, $isAdmin) = FOFDispatcher::isCliAdmin();
		if(!$isAdmin) return;
		$toolbar = FOFToolbar::getAnInstance(FOFInput::getCmd('option','com_foobar',$this->input), $this->config);
		$links = $toolbar->getLinks();
		if(!empty($links)) {
			foreach($links as $link) {
				JSubMenuHelper::addEntry($link['name'], $link['link'], $link['active']);
			}
		}
	}
	
	/**
	 * Runs before rendering the view template, echoing HTML to put before the view template's generated HTML
	 */
	protected function preRender()
	{
		$renderer = $this->getRenderer();
		if(!($renderer instanceof FOFRenderAbstract)) {
			$this->renderLinkbar();
		} else {
			$view = FOFInput::getCmd('view','cpanel',$this->input);
			$task = $this->getModel()->getState('task','browse');
			$renderer->preRender($view, $task, $this->input, $this->config);
		}
	}
	
	/**
	 * Runs after rendering the view template, echoing HTML to put after the view template's generated HTML
	 */
	protected function postRender()
	{
		$renderer = $this->getRenderer();
		if($renderer instanceof FOFRenderAbstract) {
			$view = FOFInput::getCmd('view','cpanel',$this->input);
			$task = $this->getModel()->getState('task','browse');
			$renderer->postRender($view, $task, $this->input, $this->config);
		}
	}

	protected function onBrowse($tpl = null)
	{
		// When in interactive browsing mode, save the state to the session
		$this->getModel()->savestate(1);
		return $this->onDisplay($tpl);
	}

	protected function onDisplay($tpl = null)
	{
		$view = FOFInput::getCmd('view','cpanel',$this->input);
		if(in_array($view,array('cpanel','cpanels'))) return;

		// Load the model
		$model = $this->getModel();

		// ...ordering
		$this->lists->set('order',		$model->getState('filter_order', 'id', 'cmd'));
		$this->lists->set('order_Dir',	$model->getState('filter_order_Dir', 'DESC', 'cmd'));

		// Assign data to the view
		$this->assign   ( 'items',		$model->getItemList() );
		$this->assign   ( 'pagination',	$model->getPagination());
		$this->assignRef( 'lists',		$this->lists);

		//pass page params on frontend only
		$isAdmin = version_compare(JVERSION, '1.6.0', 'ge') ? (!JFactory::$application ? false : JFactory::getApplication()->isAdmin()) : JFactory::getApplication()->isAdmin();
		if(!$isAdmin)
		{
			$params = JFactory::getApplication()->getParams();
			$this->assignRef('params', $params);
		}

		return true;
	}

	protected function onAdd($tpl = null)
	{
		JRequest::setVar('hidemainmenu', true);
		$model = $this->getModel();
		$this->assign( 'item',		$model->getItem() );
		return true;
	}

	protected function onEdit($tpl = null)
	{
		// An editor is an editor, no matter if the record is new or old :p
		return $this->onAdd();
	}

	protected function onRead($tpl = null)
	{
		// All I need is to read the record
		return $this->onAdd();
	}
	
	protected function hasAjaxOrderingSupport()
	{
		if(version_compare(JVERSION, '3.0', 'lt')) {
			return false;
		}
		
		$model = $this->getModel();
		
		if(!method_exists($model, 'getTable')) {
			return false;
		}
		
		$table = $this->getModel()->getTable();
		
		if(!method_exists($table, 'getColumnAlias') || !method_exists($table, 'getTableFields')) {
			return false;
		}
		
		$orderingColumn = $table->getColumnAlias('ordering');
		$fields = $table->getTableFields();
		if(!array_key_exists($orderingColumn, $fields)) {
			return false;
		}
		
		$listOrder	= $this->escape($model->getState('filter_order', null, 'cmd'));
		$listDirn	= $this->escape($model->getState('filter_order_Dir', 'ASC', 'cmd'));
		$saveOrder	= $listOrder == $orderingColumn;
		
		if ($saveOrder)
		{
			$saveOrderingUrl = 'index.php?option='.$this->config['option'].'&view='.$this->config['view'].'&task=saveorder&format=json';
			JHtml::_('sortablelist.sortable', 'itemsList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
		}
		
		return array(
			'saveOrder'			=> $saveOrder,
			'orderingColumn'	=> $orderingColumn
		);
	}
}