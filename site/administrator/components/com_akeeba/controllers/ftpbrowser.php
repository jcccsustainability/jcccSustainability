<?php
/**
 * @package AkeebaBackup
 * @copyright Copyright (c)2009-2013 Nicholas K. Dionysopoulos
 * @license GNU General Public License version 3, or later
 *
 * @since 2.2
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

/**
 * Folder bowser controller
 *
 */
class AkeebaControllerFtpbrowser extends FOFController
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
		$task = 'browse';
		parent::execute($task);
	}
	
	public function browse($cachable = false, $urlparams = false)
	{
		$model = $this->getThisModel();

		// Grab the data and push them to the model
		$model->host =		FOFInput::getString('host', '', $this->input);
		$model->port =		FOFInput::getInt('port', 21, $this->input);
		$model->passive =	FOFInput::getInt('passive', 1, $this->input);
		$model->ssl =		FOFInput::getInt('ssl', 0, $this->input);
		$model->username =	FOFInput::getVar('username', '', $this->input);
		$model->password =	FOFInput::getVar('password', '', $this->input);
		$model->directory =	FOFInput::getVar('directory', '', $this->input);

		$ret = $model->doBrowse();

		@ob_end_clean();
		echo '###'.json_encode($ret).'###';
		flush();
		JFactory::getApplication()->close();
	}
}