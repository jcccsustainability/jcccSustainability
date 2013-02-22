<?php
/**
 * @package AkeebaBackup
 * @copyright Copyright (c)2009-2013 Nicholas K. Dionysopoulos
 * @license GNU General Public License version 3, or later
 * @since 3.5
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

class AkeebaDispatcher extends FOFDispatcher
{
	public function onBeforeDispatch() {
		$result = parent::onBeforeDispatch();
		
		if($result) {
			// Load Akeeba Strapper
			include_once JPATH_ROOT.'/media/akeeba_strapper/strapper.php';
			AkeebaStrapper::$tag = AKEEBAMEDIATAG;
			AkeebaStrapper::bootstrap();
			AkeebaStrapper::jQueryUI();
			AkeebaStrapper::addJSfile('media://com_akeeba/js/gui-helpers.js');
			AkeebaStrapper::addJSfile('media://com_akeeba/js/akeebaui.js');
			AkeebaStrapper::addJSfile('media://com_akeeba/plugins/js/akeebaui.js');
			AkeebaStrapper::addCSSfile('media://com_akeeba/theme/akeebaui.css');
		}
		
		return $result;
	}
	
	public function dispatch() {
		// Merge the language overrides
		$paths = array(JPATH_ROOT, JPATH_ADMINISTRATOR);
		$jlang = JFactory::getLanguage();
		$jlang->load($this->component, $paths[0], 'en-GB', true);
		$jlang->load($this->component, $paths[0], null, true);
		$jlang->load($this->component, $paths[1], 'en-GB', true);
		$jlang->load($this->component, $paths[1], null, true);

		$jlang->load($this->component.'.override', $paths[0], 'en-GB', true);
		$jlang->load($this->component.'.override', $paths[0], null, true);
		$jlang->load($this->component.'.override', $paths[1], 'en-GB', true);
		$jlang->load($this->component.'.override', $paths[1], null, true);
		// Live Update translation
		$jlang->load('liveupdate', JPATH_COMPONENT_ADMINISTRATOR.'/liveupdate', 'en-GB', true);
		$jlang->load('liveupdate', JPATH_COMPONENT_ADMINISTRATOR.'/liveupdate', $jlang->getDefault(), true);
		$jlang->load('liveupdate', JPATH_COMPONENT_ADMINISTRATOR.'/liveupdate', null, true);

		// Timezone fix; avoids errors printed out by PHP 5.3.3+ (thanks Yannick!)
		if(function_exists('date_default_timezone_get') && function_exists('date_default_timezone_set')) {
			if(function_exists('error_reporting')) {
				$oldLevel = error_reporting(0);
			}
			$serverTimezone = @date_default_timezone_get();
			if(empty($serverTimezone) || !is_string($serverTimezone)) $serverTimezone = 'UTC';
			if(function_exists('error_reporting')) {
				error_reporting($oldLevel);
			}
			@date_default_timezone_set( $serverTimezone);
		}

		// Necessary defines for Akeeba Engine
		if(!defined('AKEEBAENGINE')) {
			define('AKEEBAENGINE', 1); // Required for accessing Akeeba Engine's factory class
			define('AKEEBAROOT', dirname(__FILE__).'/akeeba'); 
		}

		// Setup Akeeba's ACLs, honoring laxed permissions in component's parameters, if set
		// Access check, Joomla! 1.6 style.
		$user = JFactory::getUser();
		if (!$user->authorise('core.manage', 'com_akeeba')) {
			return JError::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));
		}

		// Make sure we have a profile set throughout the component's lifetime
		$session = JFactory::getSession();
		$profile_id = $session->get('profile', null, 'akeeba');
		if(is_null($profile_id))
		{
			// No profile is set in the session; use default profile
			$session->set('profile', 1, 'akeeba');
		}

		// Load the factory
		require_once JPATH_COMPONENT_ADMINISTRATOR.'/akeeba/factory.php';

		// Load the Akeeba Backup configuration and check user access permission
		$aeconfig = AEFactory::getConfiguration();
		AEPlatform::getInstance()->load_configuration();
		unset($aeconfig);

		// Preload helpers
		require_once JPATH_COMPONENT_ADMINISTRATOR.'/helpers/includes.php';
		require_once JPATH_COMPONENT_ADMINISTRATOR.'/helpers/escape.php';

		// Load the utils helper library
		AEPlatform::getInstance()->load_version_defines();

		// Create a versioning tag for our static files
		$staticFilesVersioningTag = md5(AKEEBA_VERSION.AKEEBA_DATE);
		define('AKEEBAMEDIATAG', $staticFilesVersioningTag);

		// If JSON functions don't exist, load our compatibility layer
		if( (!function_exists('json_encode')) || (!function_exists('json_decode')) )
		{
			require_once JPATH_COMPONENT_ADMINISTRATOR.'/helpers/jsonlib.php';
		}

		// Handle Live Update requests
		if(!class_exists('LiveUpdate')) {
			require_once JPATH_ADMINISTRATOR.'/components/com_akeeba/liveupdate/liveupdate.php';
			if((FOFInput::getCmd('view','',$this->input) == 'liveupdate')) {
				LiveUpdate::handleRequest();
				return true;
			}
		}
		
		// Look for controllers in the plugins folder
		$option = FOFInput::getCmd('option','com_foobar',$this->input);
		$view = FOFInput::getCmd('view',$this->defaultView, $this->input);
		$c = FOFInflector::singularize($view);
		$alt_path = JPATH_ADMINISTRATOR.'/components/'.$option.'/plugins/controllers/'.$c.'.php';
		
		jimport('joomla.filesystem.file');
		if(JFile::exists($alt_path))
		{
			// The requested controller exists and there you load it...
			require_once($alt_path);
		}
		
		FOFInput::setVar('view', $this->view, $this->input);
		
		parent::dispatch();
	}
}