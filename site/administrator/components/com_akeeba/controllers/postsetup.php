<?php
/**
 * @package AkeebaBackup
 * @copyright Copyright (c)2009-2013 Nicholas K. Dionysopoulos
 * @license GNU General Public License version 3, or later
 * @since 3.3.b1
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

class AkeebaControllerPostsetup extends FOFController
{
	public function  __construct($config = array()) {
		parent::__construct($config);
		// Access check, Joomla! 1.6 style.
		$user = JFactory::getUser();
		if (!$user->authorise('core.manage', 'com_akeeba') || !$user->authorise('akeeba.configure', 'com_akeeba')) {
			$this->setRedirect('index.php?option=com_cpanel');
			return JError::raiseWarning(403, JText::_('JERROR_ALERTNOAUTHOR'));
			$this->redirect();
		}
	}
	
	public function execute($task)
	{
		if($task != 'save') {
			$task = 'browse';
		}
		parent::execute($task);
	}
	
	public function save()
	{
		$enableSRP = FOFInput::getBool('srp', 0, $this->input);
		$enableAutoupdate = FOFInput::getBool('autoupdate', 0, $this->input);
		$runConfwiz = FOFInput::getBool('confwiz', 0, $this->input);
		$minStability = FOFInput::getCmd('minstability', 'stable', $this->input);
		$acceptlicense = FOFInput::getBool('acceptlicense', 0, $this->input);
		$acceptsupport = FOFInput::getBool('acceptsupport', 0, $this->input);
		$acceptbackuptest = FOFInput::getBool('acceptbackuptest', 0, $this->input);
		
		if(!in_array($minStability, array('alpha','beta','rc','stable'))) {
			$minStability = 'stable';
		}
		
		// SRP is only supported on MySQL databases
		if(!$this->isMySQL()) $enableSRP = false;
		
		$db = JFactory::getDBO();
		
		if($enableSRP) {
			$query = $db->getQuery(true)
				->update($db->qn('#__extensions'))
				->set($db->qn('enabled').' = '.$db->q('1'))
				->where($db->qn('element').' = '.$db->q('srp'))
				->where($db->qn('folder').' = '.$db->q('system'));
			$db->setQuery($query);
			$db->query();
		} else {
			$query = $db->getQuery(true)
				->update($db->qn('#__extensions'))
				->set($db->qn('enabled').' = '.$db->q('0'))
				->where($db->qn('element').' = '.$db->q('srp'))
				->where($db->qn('folder').' = '.$db->q('system'));
			$db->setQuery($query);
			$db->query();
		}
		
		if($enableAutoupdate) {
			$query = $db->getQuery(true)
				->update($db->qn('#__extensions'))
				->set($db->qn('enabled').' = '.$db->q('1'))
				->where($db->qn('element').' = '.$db->q('oneclickaction'))
				->where($db->qn('folder').' = '.$db->q('system'));
			$db->setQuery($query);
			$db->query();

			$query = $db->getQuery(true)
				->update($db->qn('#__extensions'))
				->set($db->qn('enabled').' = '.$db->q('1'))
				->where($db->qn('element').' = '.$db->q('akeebaupdatecheck'))
				->where($db->qn('folder').' = '.$db->q('system'));
			$db->setQuery($query);
			$db->query();
		} else {
			$query = $db->getQuery(true)
				->update($db->qn('#__extensions'))
				->set($db->qn('enabled').' = '.$db->q('0'))
				->where($db->qn('element').' = '.$db->q('oneclickaction'))
				->where($db->qn('folder').' = '.$db->q('system'));
			$db->setQuery($query);
			$db->query();

			$query = $db->getQuery(true)
				->update($db->qn('#__extensions'))
				->set($db->qn('enabled').' = '.$db->q('0'))
				->where($db->qn('element').' = '.$db->q('akeebaupdatecheck'))
				->where($db->qn('folder').' = '.$db->q('system'));
			$db->setQuery($query);
			$db->query();
		}
		
		// Update last version check and minstability. DO NOT USE JCOMPONENTHELPER!
		$sql = $db->getQuery(true)
			->select($db->qn('params'))
			->from($db->qn('#__extensions'))
			->where($db->qn('type').' = '.$db->q('component'))
			->where($db->qn('element').' = '.$db->q('com_akeeba'));
		$db->setQuery($sql);
		$rawparams = $db->loadResult();
		if(version_compare(JVERSION, '1.6.0', 'ge')) {
			$params = new JRegistry();
			if(version_compare(JVERSION, '3.0', 'ge')) {
				$params->loadString($rawparams);
			} else {
				$params->loadJSON($rawparams);
			}
		} else {
			$params = new JParameter($rawparams);
		}
		
		if($acceptlicense && $acceptsupport) {
			$version = AKEEBA_VERSION;
		} else {
			$version = '0.0.0';
		}
		if(version_compare(JVERSION, '3.0', 'ge')) {
			$params->set('lastversion', $version);
			$params->set('minstability', $minStability);
			$params->set('acceptlicense', $acceptlicense);
			$params->set('acceptsupport', $acceptsupport);
			$params->set('acceptbackuptest', $acceptbackuptest);
		} else {
			$params->setValue('lastversion', $version);
			$params->setValue('minstability', $minStability);
			$params->setValue('acceptlicense', $acceptlicense);
			$params->setValue('acceptsupport', $acceptsupport);
			$params->setValue('acceptbackuptest', $acceptbackuptest);
		}

		$data = $params->toString('JSON');
		$sql = $db->getQuery(true)
			->update($db->qn('#__extensions'))
			->set($db->qn('params').' = '.$db->q($data))
			->where($db->qn('element').' = '.$db->q('com_akeeba'))
			->where($db->qn('type').' = '.$db->q('component'));
		$db->setQuery($sql);
		$db->query();
		
		// Even better, create the "akeeba.lastversion.php" file with this information
		$fileData = "<"."?php\ndefined('_JEXEC') or die();\ndefine('AKEEBA_LASTVERSIONCHECK','".
			$version."');";
		jimport('joomla.filesystem.file');
		$fileName = JPATH_COMPONENT_ADMINISTRATOR.'/akeeba.lastversion.php';
		JFile::write($fileName, $fileData);
		
		// Force reload the Live Update information
		if($version != '0.0.0') {
			$dummy = LiveUpdate::getUpdateInformation(true);
		}

		// Run the configuration wizard if requested
		$message = '';
		if($runConfwiz) {
			$url = 'index.php?option=com_akeeba&view=confwiz';
		} else {
			$url = 'index.php?option=com_akeeba&view=cpanel';
		}
		
		if(!$acceptlicense) {
			JFactory::getApplication()->enqueueMessage(JText::_('AKEEBA_POSTSETUP_ERR_ACCEPTLICENSE'), 'error');
			$url = 'index.php?option=com_akeeba&view=postsetup';
		}
		if(!$acceptsupport) {
			JFactory::getApplication()->enqueueMessage(JText::_('AKEEBA_POSTSETUP_ERR_ACCEPTSUPPORT'), 'error');
			$url = 'index.php?option=com_akeeba&view=postsetup';
		}
		if(!$acceptbackuptest) {
			JFactory::getApplication()->enqueueMessage(JText::_('AKEEBA_POSTSETUP_ERR_ACCEPTBACKUPTEST'), 'error');
			$url = 'index.php?option=com_akeeba&view=postsetup';
		}
		
		JFactory::getApplication()->redirect($url);
	}
	
	private function isMySQL()
	{
		$db = JFactory::getDbo();
		return strtolower(substr($db->name, 0, 5)) == 'mysql';
	}
}