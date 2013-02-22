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
class AkeebaControllerBrowser extends FOFController
{
	public function  __construct($config = array()) {
		parent::__construct($config);
		// Access check, Joomla! 1.6 style.
		if (!JFactory::getUser()->authorise('akeeba.configure', 'com_akeeba')) {
			$this->setRedirect('index.php?option=com_akeeba');
			return JError::raiseWarning(403, JText::_('JERROR_ALERTNOAUTHOR'));
			$this->redirect();
		}
	}

	public function display($cachable = false, $urlparams = false)
	{
		$folder = FOFInput::getString('folder', '', $this->input);
		$processfolder = FOFInput::getInt('processfolder', 0, $this->input);
		
		$model = $this->getThisModel();
		$model->setState('folder', $folder);
		$model->setState('processfolder', $processfolder);
		$model->makeListing();
		
		parent::display();
		
		/*
		@ob_end_flush();
		flush();
		JFactory::getApplication()->close();
		*/
	}
}