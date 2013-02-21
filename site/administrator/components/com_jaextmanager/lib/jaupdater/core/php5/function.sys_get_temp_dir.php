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
defined ( '_JEXEC' ) or die ( 'Restricted access' );
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');
 //if ( !function_exists('sys_get_temp_dir') ) {
function ja_sys_get_temp_dir()
{
	// Try to get from environment variable
	if (defined('JPATH_ROOT') && JFolder::exists(JPATH_ROOT . DS . 'tmp' . DS)) {
		return JPATH_ROOT . DS . 'tmp' . DS;
	} elseif (!empty($_ENV['TMPDIR'])) {
		return realpath($_ENV['TMPDIR']);
	} elseif (!empty($_ENV['TEMP'])) {
		return realpath($_ENV['TEMP']);
	} elseif (!empty($_ENV['TMP'])) {
		return realpath($_ENV['TMP']);
	} elseif (function_exists('sys_get_temp_dir')) {
		return sys_get_temp_dir();
	} else {
		// Try to use system's temporary directory
		// as random name shouldn't exist
		

		//thanhnv: dont use function jaTempnam
		//because it maybe a reason for endless loop call
		//if this function and jaTempnam return false too
		$temp_file = tempnam(md5(uniqid(rand(), TRUE)), '');
		if ($temp_file) {
			$temp_dir = realpath(dirname($temp_file));
			JFile::delete($temp_file);
			return $temp_dir;
		} else {
			return null;
		}
	}
}
//}
?>