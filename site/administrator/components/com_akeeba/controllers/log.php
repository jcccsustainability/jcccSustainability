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
 * Log view controller class
 *
 */
class AkeebaControllerLog extends FOFController
{
	public function  __construct($config = array()) {
		parent::__construct($config);
		// Access check, Joomla! 1.6 style.
		$user = JFactory::getUser();
		if (!$user->authorise('akeeba.download', 'com_akeeba')) {
			$this->setRedirect('index.php?option=com_akeeba');
			return JError::raiseWarning(403, JText::_('JERROR_ALERTNOAUTHOR'));
			$this->redirect();
		}
	}

	public function execute($task)
	{
		if(!in_array($task, array('iframe','download'))) {
			$task = 'browse';
		}
		parent::execute($task);
	}
	
	/**
	 * Display the log page
	 *
	 */
	public function browse($cachable = false, $urlparams = false)
	{
		$tag = FOFInput::getCmd('tag', null, $this->input);
		if(empty($tag)) $tag = null;
		$model = $this->getThisModel();
		$model->setState('tag', $tag);
		
		AEPlatform::getInstance()->load_configuration(AEPlatform::getInstance()->get_active_profile());

		parent::display($cachable, $urlparams);
	}

	// Renders the contents of the log's iframe
	public function iframe($cachable = false, $urlparams = false)
	{
		$tag = FOFInput::getCmd('tag', null, $this->input);
		if(empty($tag)) $tag = null;
		$model = $this->getThisModel();
		$model->setState('tag', $tag);
		
		AEPlatform::getInstance()->load_configuration(AEPlatform::getInstance()->get_active_profile());

		parent::display();
		
		flush();
		JFactory::getApplication()->close();
	}

	public function download($cachable = false, $urlparams = false)
	{
		AEPlatform::getInstance()->load_configuration(AEPlatform::getInstance()->get_active_profile());

		$tag = FOFInput::getCmd('tag', null, $this->input);
		if(empty($tag)) $tag = null;

		@ob_end_clean(); // In case some braindead plugin spits its own HTML
		header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
		header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
		header("Content-Description: File Transfer");
		header('Content-Type: text/plain');
		header('Content-Disposition: attachment; filename="Akeeba Backup Debug Log.txt"');
		
		$model = $this->getThisModel();
		$model->setState('tag', $tag);
		$model->echoRawLog();
		
		flush();
		JFactory::getApplication()->close();
	}
}