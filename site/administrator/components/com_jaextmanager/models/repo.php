<?php
/**
 * @desc Modify from component Media Manager of Joomla
 *
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');
/**
 * Weblinks Component Weblink Model
 *
 * @package		Joomla
 * @subpackage	Content
 * @since 1.5
 */
class JaextmanagerModelRepo extends JAEMModel
{


	function getState($property = null,$default = null)
	{
		static $set;
		
		if (!$set) {
			$folder = JRequest::getVar('folder', '', '', 'none');
			//$folder = JPath::clean($folder);
			$this->setState('folder', $folder);
			
			$parent = str_replace("\\", "/", dirname($folder));
			$parent = ($parent == '.') ? null : $parent;
			$this->setState('parent', $parent);
			$set = true;
		}
		return parent::getState($property);
	}


	/**
	 * Image Manager Popup
	 *
	 * @param string $listFolder The image directory to display
	 * @since 1.5
	 */
	function getFolderList($base = null)
	{
		global $mainframe;
		
		// Get some paths from the request
		if (empty($base)) {
			$base = JA_WORKING_DATA_FOLDER;
		}
		
		// Get the list of folders
		jimport('joomla.filesystem.folder');
		$folders = JFolder::folders($base, '.', 5, true);
		
		// Load appropriate language files
		$lang = JFactory::getLanguage();
		$lang->load(JRequest::getCmd('option'), JPATH_ADMINISTRATOR);
		
		$document = JFactory::getDocument();
		$document->setTitle(JText::_('INSERT_IMAGE'));
		
		// Build the array of select options for the folder list
		$options[] = JHTML::_('select.option', "", "/");
		foreach ($folders as $folder) {
			$folder = str_replace(JA_WORKING_DATA_FOLDER, "", $folder);
			$value = substr($folder, 1);
			$text = str_replace(DS, "/", $folder);
			$options[] = JHTML::_('select.option', $value, $text);
		}
		
		// Sort the folder list array
		if (is_array($options)) {
			sort($options);
		}
		
		// Create the drop-down folder select list
		$list = JHTML::_('select.genericlist', $options, 'folderlist', "class=\"inputbox\" size=\"1\" onchange=\"ImageManager.setFolder(this.options[this.selectedIndex].value)\" ", 'value', 'text', $base);
		return $list;
	}


	function getFolderTree($base = null)
	{
		// Get some paths from the request
		if (empty($base)) {
			$base = JA_WORKING_DATA_FOLDER;
		}
		$base = JPath::clean($base . DS);
		$mediaBase = str_replace(DS, '/', $base);
		
		// Get the list of folders
		jimport('joomla.filesystem.folder');
		$folders = JFolder::folders($base, '.', 5, true);
		
		$tree = array();
		foreach ($folders as $folder) {
			$folder = JPath::clean($folder);
			$folder = str_replace(DS, '/', $folder);
			$name = substr($folder, strrpos($folder, '/') + 1);
			$relative = str_replace($mediaBase, '', $folder);
			$absolute = $folder;
			$path = explode('/', $relative);
			$node = (object) array('name' => $name, 'relative' => $relative, 'absolute' => $absolute);
			
			$tmp = &$tree;
			for ($i = 0, $n = count($path); $i < $n; $i++) {
				if (!isset($tmp['children'])) {
					$tmp['children'] = array();
				}
				if ($i == $n - 1) {
					// We need to place the node
					$tmp['children'][$relative] = array('data' => $node, 'children' => array());
					break;
				}
				if (array_key_exists($key = implode('/', array_slice($path, 0, $i + 1)), $tmp['children'])) {
					$tmp = &$tmp['children'][$key];
				}
			}
		}
		$tree['data'] = (object) array('name' => JText::_('REPOSITORY'), 'relative' => '', 'absolute' => $base);
		return $tree;
	}
}