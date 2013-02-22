<?php
/**
 * @package AkeebaBackup
 * @copyright Copyright (c)2009-2013 Nicholas K. Dionysopoulos
 * @license GNU General Public License version 3, or later
 * @since 3.6.0
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

/**
 * The Configuration Editor controller class
 *
 */
class AkeebaControllerSchedule extends FOFController
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
	
	public function add()
	{
		$this->layout = 'form';
		$this->display(false);
	}
}