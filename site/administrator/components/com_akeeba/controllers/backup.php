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
 * The Backup controller class
 *
 */
class AkeebaControllerBackup extends FOFController
{
	public function  __construct($config = array()) {
		parent::__construct($config);
		// Access check, Joomla! 1.6 style.
		$user = JFactory::getUser();
		if (!$user->authorise('akeeba.backup', 'com_akeeba')) {
			$this->setRedirect('index.php?option=com_akeeba');
			return JError::raiseWarning(403, JText::_('JERROR_ALERTNOAUTHOR'));
			$this->redirect();
		}
	}
	
	public function execute($task) {
		if($task != 'ajax') {
			$task = 'add';
		}
		parent::execute($task);
	}

	public function add()
	{
		$this->display(false);
	}

	/**
	 * Default task; shows the initial page where the user selects a profile
	 * and enters description and comment
	 *
	 */
	public function onBeforeAdd() {
		$result = parent::onBeforeEdit();
		if($result) {
			// Push models to view
			$model = $this->getThisModel();
			$view = $this->getThisView();
			$view->setModel($model,	true);
			
			$newProfile = FOFInput::getInt('profileid', -10, $this->input);
			if(is_numeric($newProfile) && ($newProfile > 0))
			{
				$this->_csrfProtection();

				$session = JFactory::getSession();
				$session->set('profile', $newProfile, 'akeeba');
			}

			// Deactivate the menus
			JRequest::setVar('hidemainmenu', 1);

			// Push data to the model
			$model->setState('profile',		FOFInput::getInt('profileid', -10, $this->input));
			$model->setState('ajax',		FOFInput::getCmd('ajax', '', $this->input));
			$model->setState('autostart',	FOFInput::getInt('autostart', 0, $this->input));
			if($this->input instanceof FOFInput) {
				$srpinfo = array(
					'tag'				=> $this->input->getCmd('tag', 'backend'),
					'type'				=> $this->input->getCmd('type', ''),
					'name'				=> $this->input->getCmd('name', ''),
					'group'				=> $this->input->getCmd('group', ''),
					'customdirs'		=> $this->input->get('customdirs', array(), 'array', 2),
					'extraprefixes'		=> $this->input->get('extraprefixes', array(), 'array', 2),
					'customtables'		=> $this->input->get('customtables', array(), 'array', 2),
					'skiptables'		=> $this->input->get('skiptables', array(), 'array', 2),
					'xmlname'			=> $this->input->getString('xmlname','')
				);
			} else {
				$srpinfo = array(
					'tag'				=> FOFInput::getCmd('tag', 'backend', $this->input),
					'type'				=> FOFInput::getCmd('type', '', $this->input),
					'name'				=> FOFInput::getCmd('name', '', $this->input),
					'group'				=> FOFInput::getCmd('group', '', $this->input),
					'customdirs'		=> FOFInput::getArray('customdirs', array(), $this->input, 2),
					'extraprefixes'		=> FOFInput::getArray('extraprefixes', array(), $this->input, 2),
					'customtables'		=> FOFInput::getArray('customtables', array(), $this->input, 2),
					'skiptables'		=> FOFInput::getArray('skiptables', array(), $this->input, 2),
					'xmlname'			=> FOFInput::getString('xmlname','', $this->input)
				);
			}
				
			$model->setState('srpinfo',	$srpinfo);
			
			$description = FOFInput::getString('description', null, $this->input, 2);
			if(!empty($description)) {
				$model->setState('description',	$description);
			}
			$comment = FOFInput::getString('comment', null, $this->input, 2);
			if(!empty($comment)) {
				$model->setState('comment',	$comment);
			}
			$model->setState('jpskey',		FOFInput::getVar('jpskey', '', $this->input));
			$model->setState('returnurl',	FOFInput::getVar('returnurl', '', $this->input));
		}
		return $result;		
	}

	public function ajax()
	{
		$model = $this->getThisModel();

		$model->setState('profile',		FOFInput::getInt('profileid', -10, $this->input));
		$model->setState('ajax',		FOFInput::getCmd('ajax', '', $this->input));
		$model->setState('description',	FOFInput::getString('description', '', $this->input));
		$model->setState('comment',		FOFInput::getString('comment', '','default', $this->input, 4));
		$model->setState('jpskey',		FOFInput::getVar('jpskey', '', $this->input));
		
		// System Restore Point backup state variables
		$model->setState('tag',			FOFInput::getCmd('tag', 'backend', $this->input));
		$model->setState('type',		strtolower(FOFInput::getCmd('type', '', $this->input)));
		$model->setState('name',		strtolower(FOFInput::getCmd('name', '', $this->input)));
		$model->setState('group',		strtolower(FOFInput::getCmd('group', '', $this->input)));
		if($this->input instanceof FOFInput) {
			$model->setState('customdirs',	$this->input->get('customdirs', array(),'array' ,2));
			$model->setState('customfiles',	$this->input->get('customfiles', array(),'array' ,2));
			$model->setState('extraprefixes',$this->input->get('extraprefixes', array(),'array' ,2));
			$model->setState('customtables',$this->input->get('customtables', array(),'array' ,2));
			$model->setState('skiptables',	$this->input->get('skiptables', array(),'array' ,2));
			$model->setState('langfiles',	$this->input->get('langfiles', array(),'array' ,2));
			$model->setState('xmlname',		$this->input->getString('xmlname', ''));
		} else {
			$model->setState('customdirs',	FOFInput::getArray('customdirs', array(),$this->input ,2));
			$model->setState('customfiles',	FOFInput::getArray('customfiles', array(),$this->input ,2));
			$model->setState('extraprefixes',FOFInput::getArray('extraprefixes', array(),$this->input ,2));
			$model->setState('customtables',FOFInput::getArray('customtables', array(),$this->input ,2));
			$model->setState('skiptables',	FOFInput::getArray('skiptables', array(),$this->input ,2));
			$model->setState('langfiles',	FOFInput::getArray('langfiles', array(),$this->input ,2));
			$model->setState('xmlname',		FOFInput::getString('xmlname', '', $this->input));
		}
		
		define('AKEEBA_BACKUP_ORIGIN', FOFInput::getCmd('tag', 'backend', $this->input));
		
		$ret_array = $model->runBackup();

		@ob_end_clean();
		header('Content-type: text/plain');
		echo '###' . json_encode($ret_array) . '###';
		flush();
		JFactory::getApplication()->close();
	}
}