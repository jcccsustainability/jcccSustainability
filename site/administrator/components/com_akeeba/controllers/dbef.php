<?php
/**
 * @package AkeebaBackup
 * @copyright Copyright (c)2009-2013 Nicholas K. Dionysopoulos
 * @license GNU General Public License version 3, or later
 *
 * @since 1.3
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

/**
 * MVC controller class for Database Table filters
 *
 */
class AkeebaControllerDbef extends FOFController
{
	public function  __construct($config = array()) {
		parent::__construct($config);
		// Access check, Joomla! 1.6 style.
		$user = JFactory::getUser();
		if (!$user->authorise('akeeba.configure', 'com_akeeba')) {
			$this->setRedirect('index.php?option=com_akeeba');
			return JError::raiseWarning(403, JText::_('JERROR_ALERTNOAUTHOR'));
			$this->redirect();
		}
	}

	public function execute($task)
	{
		if($task != 'ajax') {
			$task = 'browse';
		}
		
		parent::execute($task);
	}
	
	/**
	 * Handles the "display" task, which displays a folder and file list
	 *
	 */
	public function browse($cachable = false, $urlparams = false)
	{
		$task = FOFInput::getCmd('task', 'normal', $this->input);
		$this->getThisModel()->setState('browse_task', $task);
		
		parent::display($cachable, $urlparams);
	}

	/**
	 * AJAX proxy.
	 */
	public function ajax()
	{
		// Parse the JSON data and reset the action query param to the resulting array
		$action_json = FOFInput::getVar('action', '', $this->input, 'none', 2);
		$action = json_decode($action_json);
		
		$model = $this->getThisModel();
		$model->setState('action', $action);
		
		$ret = $model->doAjax();

		@ob_end_clean();
		echo '###' . json_encode($ret) . '###';
		flush();
		JFactory::getApplication()->close();
	}
}