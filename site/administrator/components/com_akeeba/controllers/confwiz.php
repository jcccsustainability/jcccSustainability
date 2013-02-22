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
 * The Configuration Wizard controller class
 */
class AkeebaControllerConfwiz extends FOFController
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
		$this->modelName = 'AkeebaModelConfwiz';
	}
	
	public function add()
	{
		$this->display(false);
	}
	
	public function ajax()
	{
		$act = FOFInput::getCmd('act', '', $this->input);
		$model = FOFModel::getAnInstance('Confwiz', 'AkeebaModel');
		$model->setState('act', $act);
		$ret = $model->runAjax();
		
		@ob_end_clean();
		echo '###' . json_encode( $ret ) . '###';
		flush();
		JFactory::getApplication()->close();
	}	
}