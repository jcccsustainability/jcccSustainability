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
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

function isJAProduct($extname)
{
	//Eg: jat3
	if (preg_match("/^ja/i", $extname)) {
		return true;
	}
	
	//Eg: com_jaextmanager
	if (preg_match("/[_\-\s]ja/i", $extname)) {
		return true;
	}
	
	return false;
}


function jaGetCoreVersion($jVersion, $extname = '')
{
	$isJAProduct = isJAProduct($extname);
	
	if (preg_match("/2\.5.*/i", $jVersion)) {
		$coreVersion = $isJAProduct ? 'j16' : 'j25';
	} elseif (preg_match("/1\.7.*/i", $jVersion)) {
		$coreVersion = $isJAProduct ? 'j16' : 'j17';
	} elseif (preg_match("/1\.6.*/i", $jVersion)) {
		$coreVersion = 'j16';
	} else {
		$coreVersion = 'j15';
	}
	return $coreVersion;
}


function jaGetListServices()
{
	$db = JFactory::getDBO();
	
	$sql = "SELECT * FROM #__jaem_services AS t WHERE 1 ORDER BY t.ws_name";
	$db->setQuery($sql);
	return $db->loadObjectList();
}


function jaGetDefaultService()
{
	$services = jaGetListServices();
	$default = new stdClass();
	foreach ($services as $id => $sv) {
		if ($id == 0 || $sv->ws_default) {
			$default = $sv;
		}
	}
	//set default values
	if (!isset($default->ws_mode)) {
		$default->ws_mode = 'local';
	}
	if (!isset($default->ws_uri)) {
		$default->ws_uri = 'http://update.joomlart.com/service/';
	}
	if (!isset($default->ws_user)) {
		$default->ws_user = 'joomlart';
	}
	if (!isset($default->ws_pass)) {
		$default->ws_pass = '';
	}
	
	return $default;
}


function jaEMTooltips($tipid, $title)
{
	$title = preg_replace("/\r\n/", "", $title);
	$title = addslashes($title);
	$script = "
			<script type=\"text/javascript\">
			/*<![CDATA[*/
			window.addEvent('domready', function(){
				new JATooltips ([$('{$tipid}')], {
						content: '{$title}'
				});
			});
			/*]]>*/
			</script>
			";
	return $script;
}


/**
 * Create file with unique file name
 *
 */
function jaTempnam($dir, $prefix)
{
	$dir = JPath::clean($dir . DS);
	if (!JFolder::exists($dir)) {
		$dir = JPath::clean(ja_sys_get_temp_dir() . DS);
	}
	
	$sand = md5(microtime());
	$fileName = $prefix . date("YmdHis") . $sand;
	$i = 0;
	$fileNameTest = $fileName . ".tmp";
	while (JFile::exists($dir . $fileNameTest)) {
		$i++;
		$fileNameTest = $fileName . "_{$i}.tmp";
	}
	$file = $dir . $fileNameTest;
	//$content = '';
	//JFile::write($file, $content);
	//chmod
	//@chmod($file, '0755');
	return $file;
}

?>