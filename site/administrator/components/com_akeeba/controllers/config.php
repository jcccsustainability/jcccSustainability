<?php
/**
 * @package AkeebaBackup
 * @copyright Copyright (c)2009-2013 Nicholas K. Dionysopoulos
 * @license GNU General Public License version 3, or later
 * @since 1.3
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

/**
 * The Configuration Editor controller class
 *
 */
class AkeebaControllerConfig extends FOFController
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
		$this->display(false);
	}
	
	/**
	 * Handle the apply task which saves settings and shows the editor again
	 *
	 */
	public function apply()
	{
		// CSRF prevention
		if($this->csrfProtection) {
			$this->_csrfProtection();
		}
		
		// Get the var array from the request
		if($this->input instanceof FOFInput) {
			$data = $this->input->get('var', array(), 'array', 4);
		} else {
			$data = FOFInput::getArray('var', array(), $this->input, 4);
		}
		
		$model = $this->getThisModel();
		$model->setState('engineconfig', $data);
		$model->saveEngineConfig();
		
		$this->setRedirect(JURI::base().'index.php?option=com_akeeba&view=config', JText::_('CONFIG_SAVE_OK'));
	}

	/**
	 * Handle the save task which saves settings and returns to the cpanel
	 *
	 */
	public function save()
	{
		$this->apply();
		$this->setRedirect(JURI::base().'index.php?option=com_akeeba', JText::_('CONFIG_SAVE_OK'));
	}

	/**
	 * Handle the cancel task which doesn't save anything and returns to the cpanel
	 *
	 */
	public function cancel()
	{
		// CSRF prevention
		if($this->csrfProtection) {
			$this->_csrfProtection();
		}
		
		$this->setRedirect(JURI::base().'index.php?option=com_akeeba');
	}
	
	/**
	 * Tests the validity of the FTP connection details
	 */
	public function testftp()
	{
		$model = $this->getThisModel();
		$model->setState('host',	FOFInput::getVar('host', '', $this->input));
		$model->setState('port',	FOFInput::getInt('port', 21, $this->input));
		$model->setState('user',	FOFInput::getVar('user', '', $this->input));
		$model->setState('pass',	FOFInput::getVar('pass', '', $this->input));
		$model->setState('initdir', FOFInput::getVar('initdir', '', $this->input));
		$model->setState('usessl',	FOFInput::getVar('usessl', '', $this->input) == 'true');
		$model->setState('passive', FOFInput::getVar('passive', '', $this->input) == 'true');
		
		@ob_end_clean();
		echo '###'.json_encode( $model->testFTP() ).'###';
		flush();
		JFactory::getApplication()->close();
	}
	
	/**
	 * Tests the validity of the SFTP connection details
	 */
	public function testsftp()
	{
		$model = $this->getThisModel();
		$model->setState('host',	FOFInput::getVar('host', '', $this->input));
		$model->setState('port',	FOFInput::getInt('port', 21, $this->input));
		$model->setState('user',	FOFInput::getVar('user', '', $this->input));
		$model->setState('pass',	FOFInput::getVar('pass', '', $this->input));
		$model->setState('initdir',	FOFInput::getVar('initdir', '', $this->input));
		
		@ob_end_clean();
		echo '###'.json_encode( $model->testSFTP() ).'###';
		flush();
		JFactory::getApplication()->close();
	}
	
	/**
	 * Opens an OAuth window for the selected data processing engine 
	 */
	public function dpeoauthopen()
	{
		$model = $this->getThisModel();
		$model->setState('engine', FOFInput::getVar('engine', '', $this->input));
		if($this->input instanceof FOFInput) {
			$model->setState('params', $this->input->get('params', array(), 'array', 2));
		} else {
			$model->setState('params', FOFInput::getArray('params', array(), $this->input, 2));
		}
		
		@ob_end_clean();
		$model->dpeOuthOpen();
		flush();
		
		jexit();
	}
	
	/**
	 * Runs a custom API call against the selected data processing engine
	 */
	public function dpecustomapi()
	{
		$model = $this->getThisModel();
		$model->setState('engine', FOFInput::getVar('engine', '', $this->input));
		$model->setState('method', FOFInput::getVar('method', '', $this->input));
		if($this->input instanceof FOFInput) {
			$model->setState('params', $this->input->get('params', array(), 'array', 2));
		} else {
			$model->setState('params', FOFInput::getArray('params', array(), $this->input, 2));
		}
		
		@ob_end_clean();
		echo '###'.json_encode( $model->dpeCustomAPICall() ).'###';
		flush();
		
		jexit();
	}
	
	
}