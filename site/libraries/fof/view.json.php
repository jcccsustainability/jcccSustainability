<?php
/**
 *  @package FrameworkOnFramework
 *  @copyright Copyright (c)2010-2013 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

jimport('joomla.application.component.view');

/**
 * FrameworkOnFramework JSON View class
 *
 * FrameworkOnFramework is a set of classes whcih extend Joomla! 1.5 and later's
 * MVC framework with features making maintaining complex software much easier,
 * without tedious repetitive copying of the same code over and over again.
 */
class FOFViewJson extends FOFViewHtml
{
	protected function onDisplay($tpl=null)
	{
		// Load the model
		$model = $this->getModel();

		$items = $model->getItemList();
		$this->assignRef( 'items',		$items );

		$document = JFactory::getDocument();
		$document->setMimeEncoding('application/json');

		JError::setErrorHandling(E_ALL,'ignore');
		if(is_null($tpl)) $tpl = 'json';
		$result = $this->loadTemplate($tpl);
		JError::setErrorHandling(E_WARNING,'callback');

		if($result instanceof JException) {
			// Default JSON behaviour in case the template isn't there!
			$json = json_encode($items);

			// JSONP support
			$callback = FOFInput::getVar('callback', null, $this->input);
			if(!empty($callback)) {
				echo $callback . '('.$json.')';
			} else {
				$defaultName = FOFInput::getCmd('view', 'joomla', $this->input);
				$filename = FOFInput::getCmd('basename', $defaultName, $this->input);

				//On Joomla! 1.5 there is no setName method
				if(version_compare(JVERSION, '1.6', 'ge')){
					$document->setName($filename);
				}
				echo $json;
			}

			return false;
		} else {
			echo $result;
			return false;
		}
	}

	protected function onRead($tpl = null)
	{
		$model = $this->getModel();

		$item = $model->getItem();
		$this->assign('item', $item );

		$document = JFactory::getDocument();
		$document->setMimeEncoding('application/json');

		JError::setErrorHandling(E_ALL,'ignore');
		if(is_null($tpl)) $tpl = 'json';
		$result = $this->loadTemplate($tpl);
		JError::setErrorHandling(E_WARNING,'callback');

		if($result instanceof JException) {
			// Default JSON behaviour in case the template isn't there!
			$json = json_encode($item);

			// JSONP support
			$callback = FOFInput::getVar('callback', null, $this->input);
			if(!empty($callback)) {
				echo $callback . '('.$json.')';
			} else {
				$defaultName = FOFInput::getCmd('view', 'joomla', $this->input);
				$filename = FOFInput::getCmd('basename', $defaultName, $this->input);
				$document->setName($filename);
				echo $json;
			}

			return false;
		} else {
			echo $result;
			return false;
		}
	}
}