<?php
/**
 * @version		$Id: controller.php 10381 2008-06-01 03:35:53Z pasamio $
 * @package		Joomla
 * @subpackage	Media
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.controller');

/**
 * Media Manager Component Controller
 *
 * @package		Joomla
 * @subpackage	Media
 * @version 1.5
 */
class JaextmanagerControllerRepo extends JAEMController
{


	/**
	 * Display the view
	 */
	function display($cachable = false, $urlparams = false)
	{
		global $mainframe;
		
		$vName = JRequest::getCmd('view', 'media');
		switch ($vName) {
			case 'images':
				$vLayout = JRequest::getCmd('layout', 'default');
				$mName = 'manager';
				
				break;
			
			case 'imagesList':
				$mName = 'list';
				$vLayout = JRequest::getCmd('layout', 'default');
				
				break;
			
			case 'repolist':
				$mName = 'repolist';
				//$vLayout = $mainframe->getUserStateFromRequest('media.list.layout', 'layout', 'details', 'word');
				$vLayout = "details";
				
				break;
			
			case 'media':
			default:
				$vName = 'repo';
				$vLayout = JRequest::getCmd('layout', 'default');
				$mName = 'repo';
				break;
		}
		
		$document = JFactory::getDocument();
		$vType = $document->getType();
		
		// Get/Create the view
		$view = $this->getView($vName, $vType);
		
		// Get/Create the model
		if ($model = $this->getModel($mName)) {
			// Push the model into the view (as default)
			$view->setModel($model, true);
		}
		
		// Set the layout
		$view->setLayout($vLayout);
		
		// Display the view
		$view->display();
	}


	function ftpValidate()
	{
		// Set FTP credentials, if given
		jimport('joomla.client.helper');
		JClientHelper::setCredentialsFromRequest('ftp');
	}
}
