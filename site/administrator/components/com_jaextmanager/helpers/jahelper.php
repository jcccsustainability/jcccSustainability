<?php
/**
 * ------------------------------------------------------------------------
 * JA Extenstion Manager Component for J25 & J30
 * ------------------------------------------------------------------------
 * Copyright (C) 2004-2011 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
 * @license - GNU/GPL, http://www.gnu.org/licenses/gpl.html
 * Author: J.O.O.M Solutions Co., Ltd
 * Websites: http://www.joomlart.com - http://www.joomlancers.com
 * ------------------------------------------------------------------------
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.helper');

class JAFormHelpers
{


	function JAFormHelpers()
	{
	
	}


	function isPostBack()
	{
		if (JRequest::getVar('task') == 'add')
			return FALSE;
		return count($_POST);
	}


	function message($iserror = 1, $messages)
	{
		$content = '<div id="system-message">';
		if ($iserror) {
			$content .= '<dt class="error">Error</dt><dd class="error message"><ul id="jav-error">';
			if ($messages && is_array($messages)) {
				foreach ($messages as $message) {
					$content .= '<li>' . $message . '</li>';
				}
			} else {
				$content .= '<li>' . $messages . '</li>';
			}
			$content .= '</ul></dd>';
		} else {
			$content = '<div id="system-message"><dt class="message">Message</dt><dd class="message message"><ul id="jav-error">';
			if ($messages && is_array($messages)) {
				foreach ($messages as $message) {
					$content .= '<li>' . $message . '</li>';
				}
			} else {
				$content .= '<li>' . $messages . '</li>';
			}
			$content .= '</ul></dd></div>';
		}
		$content .= '</div>';
		return $content;
	}


	function parse_JSON($objects)
	{
		if (!$objects)
			return;
		$db = JFactory::getDBO();
		
		$html = '';
		$item_tem = array();
		foreach ($objects as $i => $row) {
			$tem = array();
			$item_tem[$i] = '{';
			foreach ($row as $k => $value) {
				//$value = $db->Quote($value);
				$tem[$i][] = "\"$k\" : \"" . $db->escape($value) . "\"";
			}
			$item_tem[$i] .= implode(',', $tem[$i]);
			$item_tem[$i] .= '}';
		}
		
		if ($item_tem)
			$html = implode(',', $item_tem);
		
		return $html;
	}


	function parseProperty($type = 'html', $id = 0, $value = '', $reload = 0)
	{
		$object = new stdClass();
		$object->type = $type;
		$object->id = $id;
		$object->value = $value;
		if ($reload)
			$object->reload = $reload;
		return $object;
	}


	function parsePropertyPublish($type = 'html', $id = 0, $default = 0, $number = 0, $function = 'default', $title = 'Publish', $un = 'Undefault')
	{
		$object = new stdClass();
		$object->type = $type;
		$object->id = $id;
		if (!$default) {
			$html = '<a  href="javascript:void(0);" title=\'' . $title . '\'><img id="i5" border="0" src="images/default_x.png" alt="Publish"/></a>';
		} else {
			$function = 'un' . $function;
			$html = '<a  href="javascript:void(0);" title=\'' . $un . '\'><img id="i5" border="0" src="images/tick.png" alt="Undefault"/></a>';
		}
		
		$object->value = $html;
		return $object;
	}
}