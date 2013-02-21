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
// No direct access
defined('JPATH_BASE') or die();

jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.archive');
jimport('joomla.filesystem.path');
jimport('joomla.base.adapter');

class jaExtUploader extends JAdapter
{
	/**
	 * Array of paths needed by the installer
	 * @var array
	 */
	protected $_paths = array();
	
	/**
	 * True if packakge is an upgrade
	 * @var boolean
	 */
	protected $_upgrade = null;
	
	/**
	 * The manifest trigger class
	 * @var object
	 */
	public $manifestClass = null;
	
	/**
	 * True if existing files can be overwritten
	 * @var boolean
	 */
	protected $_overwrite = true;
	
	/**
	 * Stack of installation steps
	 *	- Used for installation rollback
	 * @var array
	 */
	protected $_stepStack = array();
	
	/**
	 * Extension Table Entry
	 * @var JTableExtension
	 */
	public $extension = null;
	
	/**
	 * The output from the install/uninstall scripts
	 * @var string
	 */
	public $message = null;
	
	/**
	 * The installation manifest XML object
	 * @var object
	 */
	public $manifest = null;
	
	/**
	 * The extension message that appears
	 * @var string
	 */
	protected $extension_message = null;
	
	/**
	 * The redirect URL if this extension (can be null if no redirect)
	 * @var string
	 */
	protected $redirect_url = null;
	
	/**
	 * The output from the install/uninstall scripts
	 * @var string
	 */
	var $errMessage = null;
	
	/**
	 * The output from the upload scripts
	 * @var string
	 */
	var $results = array();
	
	var $_debug = false;


	/**
	 * Constructor
	 *
	 * @access protected
	 */
	public function __construct()
	{
		parent::__construct(dirname(__FILE__), 'jaExtUploader');
	}


	/**
	 * Returns the global Installer object, only creating it
	 * if it doesn't already exist.
	 *
	 * @static
	 * @return	object	An installer object
	 * @since 1.5
	 */
	public static function getInstance()
	{
		static $instance;
		
		if (!isset($instance)) {
			$instance = new jaExtUploader();
		}
		return $instance;
	}


	/**
	 * Get the allow overwrite switch
	 *
	 * @access	public
	 * @return	boolean	Allow overwrite switch
	 * @since	1.5
	 */
	public function getOverwrite()
	{
		return $this->_overwrite;
	}


	/**
	 * Set the allow overwrite switch
	 *
	 * @access	public
	 * @param	boolean	$state	Overwrite switch state
	 * @return	boolean	Previous value
	 * @since	1.5
	 */
	public function setOverwrite($state = false)
	{
		$tmp = $this->_overwrite;
		if ($state) {
			$this->_overwrite = true;
		} else {
			$this->_overwrite = false;
		}
		return $tmp;
	}


	/**
	 * Get the database connector object
	 *
	 * @access	public
	 * @return	object	Database connector object
	 * @since	1.5
	 */
	function getDBO()
	{
		return $this->_db;
	}


	/**
	 * Get the installation manifest object
	 *
	 * @access	public
	 * @return	object	Manifest object
	 * @since	1.5
	 */
	public function getManifest()
	{
		if (!is_object($this->manifest)) {
			$this->findManifest();
		}
		return $this->manifest;
	}


	/**
	 * Get an installer path by name
	 *
	 * @access	public
	 * @param	string	$name		Path name
	 * @param	string	$default	Default value
	 * @return	string	Path
	 * @since	1.5
	 */
	public function getPath($name, $default = null)
	{
		return (!empty($this->_paths[$name])) ? $this->_paths[$name] : $default;
	}


	/**
	 * Sets an installer path by name
	 *
	 * @access	public
	 * @param	string	$name	Path name
	 * @param	string	$value	Path
	 * @return	void
	 * @since	1.5
	 */
	public function setPath($name, $value)
	{
		$this->_paths[$name] = $value;
	}


	/**
	 * !Important: must restarts an upload part before upload next extensions
	 *
	 */
	function resetPath()
	{
		$this->_paths = array();
	}


	/**
	 * Pushes a step onto the installer stack for rolling back steps
	 *
	 * @access	public
	 * @param	array	$step	Installer step
	 * @return	void
	 * @since	1.5
	 */
	public function pushStep($step)
	{
		$this->_stepStack[] = $step;
	}


	/**
	 * get upload adapter for specific extension type
	 *
	 * @param (string) $name - name of extension type
	 * @return (mixed)
	 */
	function getAdapter($name, $options = array())
	{
		if (!isset($this->_adapters[$name]) || !is_object($this->_adapters[$name])) {
			//Try to get adapter
			$file = dirname(__FILE__) . DS . 'adapters' . DS . strtolower($name) . '.php';
			if (!JFile::exists($file)) {
				return false;
			}
			// Try to load the adapter object
			require_once ($file);
			$class = 'jaExtUploader' . ucfirst($name);
			if (!class_exists($class)) {
				return false;
			}
			$adapter = new $class($this);
			$adapter->parent = & $this;
			
			$this->_adapters[$name] = $adapter;
		}
		return $this->_adapters[$name];
	}


	/**
	 * Upload abort is no need rollback to previous steps like install
	 *
	 * @param unknown_type $msg
	 * @param unknown_type $type
	 */
	function abort($msg = null, $type = null)
	{
		$this->errMessage .= $msg . "<br /><br />";
	}


	function upload($packagePath = null)
	{
		$this->message = "";
		$this->errMessage = "";
		$this->resetResult();
		
		$xmlfiles = JFolder::files($packagePath, '.xml$', true, true);
		if (!empty($xmlfiles)) {
			$result = true;
			foreach ($xmlfiles as $file) {
				$manifest = $this->isManifest($file);
				if (!is_null($manifest)) {
					//upload extension depend on $manifest
					// Set the manifest object and path
					$this->manifest = & $manifest;
					$this->setPath('manifest', $file);
					
					// Set the installation source path to that of the manifest file
					$this->setPath('source', dirname($file));
					$result &= $this->uploadItems();
				}
			}
			return $this->getResults();
		} else {
			return false;
		}
	}


	/**
	 * Upload item (extension) of package
	 * Prepare for upload: this method sets the upload directory, finds
	 * and checks the installation file and verifies the installation type
	 *
	 * @access public
	 * @return boolean True on success
	 * @since 1.0
	 */
	function uploadItems()
	{
		// Load the adapter(s) for the install manifest
		$manifest = $this->manifest;
		$type = (string) $manifest->attributes()->type;
		$version = (string) $manifest->attributes()->version;
		$rootName = (string) $manifest->name;
		$config = JFactory::getConfig();
		
		// Needed for legacy reasons ... to be deprecated in next minor release
		if ($type == 'mambot') {
			$type = 'plugin';
		}
		
		/*
		 * LEGACY CHECK
		 */
		/*if ((version_compare($version, '1.5', '<') || $rootName == 'mosinstall') && !$config->getValue('config.legacy')) {
			$this->abort(JText::_('MUSTENABLELEGACY'));
			return false;
		}*/
		
		// Lazy load the adapter
		$adapter = $this->getAdapter($type);
		if ($adapter === false) {
			//$this->abort(JText::_('INVALID_JOOMLA_EXTENSIONS'));
			return false;
		} else {
			return $adapter->upload();
		}
	}


	/**
	 * build product object with JoomlArt Format from $manifest
	 *
	 */
	function buildProduct($pname)
	{
		$manifest = $this->manifest;
		
		$legacy = ($manifest->getName() == 'mosinstall' || $manifest->getName() == 'install') ? 1 : 0;
		$type = (string) $manifest->attributes()->type;
		$group = (string) $manifest->attributes()->group;
		$coreVersion = (string) $manifest->attributes()->version;
		
		$name = (string) $manifest->name;
		$coreVersion = jaGetCoreVersion($coreVersion, $pname);
		$version = (string) $manifest->version;
		$version = JFilterInput::clean($version, 'cmd');
		
		if (!empty($pname) && !empty($type)) {
			
			$jaProduct = new stdClass();
			$jaProduct->coreVersion = $coreVersion;
			$jaProduct->extKey = $pname;
			$jaProduct->name = $name;
			$jaProduct->group = $group;
			$jaProduct->version = $version;
			$jaProduct->type = $type;
			return $jaProduct;
		} else {
			return false;
		}
	}


	/**
	 * Method to parse through a files element of the installation manifest and take appropriate
	 * action.
	 *
	 * @access	public
	 * @param	JXMLElement	$element	The xml node to process
	 * @param	int			$cid		Application ID of application to install to
	 * @param	Array		$oldFiles	List of old files (JXMLElement's)
	 * @param	Array		$oldMD5		List of old MD5 sums (indexed by filename with value as MD5)
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function parseFiles($element, $cid = 0, $oldFiles = null, $oldMD5 = null)
	{
		// Get the array of file nodes to process; we checked this had children above
		if (!$element || !count($element->children())) {
			// Either the tag does not exist or has no children (hence no files to process) therefore we return zero files processed.
			return 0;
		}
		
		// Initialise variables.
		$copyfiles = array();
		
		// Get the client info
		jimport('joomla.application.helper');
		$client = JApplicationHelper::getClientInfo($cid);
		
		/*
		 * Here we set the folder we are going to remove the files from.
		 */
		if ($client) {
			$pathname = 'extension_' . $client->name;
			$destination = $this->getPath($pathname);
		} else {
			$pathname = 'extension_root';
			$destination = $this->getPath($pathname);
		}
		
		/*
		 * Here we set the folder we are going to copy the files from.
		 *
		 * Does the element have a folder attribute?
		 *
		 * If so this indicates that the files are in a subdirectory of the source
		 * folder and we should append the folder attribute to the source path when
		 * copying files.
		 */
		$folder = (string) $element->attributes()->folder;
		if ($folder && file_exists($this->getPath('source') . DS . $folder)) {
			$source = $this->getPath('source') . DS . $folder;
		} else {
			$source = $this->getPath('source');
		}
		
		// Process each file in the $files array (children of $tagName).
		foreach ($element->children() as $file) {
			$path['src'] = $source . DS . $file;
			$path['dest'] = $destination . DS . $file;
			
			// Is this path a file or folder?
			$path['type'] = ($file->getName() == 'folder') ? 'folder' : 'file';
			
			/*
			 * Before we can add a file to the copyfiles array we need to ensure
			 * that the folder we are copying our file to exits and if it doesn't,
			 * we need to create it.
			 */
			if (basename($path['dest']) != $path['dest']) {
				$newdir = dirname($path['dest']);
				
				if (!JFolder::create($newdir)) {
					JError::raiseWarning(1, JText::sprintf('JLIB_INSTALLER_ERROR_CREATE_DIRECTORY', $newdir));
					return false;
				}
			}
			
			// Add the file to the copyfiles array
			$copyfiles[] = $path;
		}
		
		return $this->copyFiles($copyfiles);
	}


	/**
	 * Method to parse the parameters of an extension, build the INI
	 * string for it's default parameters, and return the INI string.
	 *
	 * @access	public
	 * @return	string	INI string of parameter values
	 * @since	1.5
	 */
	public function getParams()
	{
		// Validate that we have a fieldset to use
		if (!isset($this->manifest->config->fields->fieldset)) {
			return '{}';
		}
		// Getting the fieldset tags
		$fieldsets = $this->manifest->config->fields->fieldset;
		
		// Creating the data collection variable:
		$ini = array();
		
		// Iterating through the fieldsets:
		foreach ($fieldsets as $fieldset) {
			if (!count($fieldset->children())) {
				// Either the tag does not exist or has no children therefore we return zero files processed.
				return null;
			}
			
			// Iterating through the fields and collecting the name/default values:
			foreach ($fieldset as $field) {
				// Modified the below if statements to check against the
				// null value since default values like "0" were casuing
				// entire parameters to be skipped.
				if (($name = $field->attributes()->name) === null) {
					continue;
				}
				
				if (($value = $field->attributes()->default) === null) {
					continue;
				}
				
				$ini[(string) $name] = (string) $value;
			}
		}
		
		return json_encode($ini);
	}


	/**
	 * Copy files from source directory to the target directory
	 *
	 * @access	public
	 * @param	array $files array with filenames
	 * @param	boolean $overwrite True if existing files can be replaced
	 * @return	boolean True on success
	 * @since	1.5
	 */
	public function copyFiles($files, $overwrite = null)
	{
		/*
		 * To allow for manual override on the overwriting flag, we check to see if
		 * the $overwrite flag was set and is a boolean value.  If not, use the object
		 * allowOverwrite flag.
		 */
		if (is_null($overwrite) || !is_bool($overwrite)) {
			$overwrite = $this->_overwrite;
		}
		
		/*
		 * $files must be an array of filenames.  Verify that it is an array with
		 * at least one file to copy.
		 */
		if (is_array($files) && count($files) > 0) {
			foreach ($files as $file) {
				// Get the source and destination paths
				$filesource = JPath::clean($file['src']);
				$filedest = JPath::clean($file['dest']);
				$filetype = array_key_exists('type', $file) ? $file['type'] : 'file';
				
				if (!file_exists($filesource)) {
					/*
					 * The source file does not exist.  Nothing to copy so set an error
					 * and return false.
					 */
					JError::raiseWarning(1, JText::sprintf('JLIB_INSTALLER_ERROR_NO_FILE', $filesource));
					return false;
				} elseif (file_exists($filedest) && !$overwrite) {
					/*
					 * It's okay if the manifest already exists
					 */
					if ($this->getPath('manifest') == $filesource) {
						continue;
					}
					
					/*
					 * The destination file already exists and the overwrite flag is false.
					 * Set an error and return false.
					 */
					JError::raiseWarning(1, JText::sprintf('JLIB_INSTALLER_ERROR_FILE_EXISTS', $filedest));
					return false;
				} else {
					// Copy the folder or file to the new location.
					if ($filetype == 'folder') {
						if (!(JFolder::copy($filesource, $filedest, null, $overwrite))) {
							JError::raiseWarning(1, JText::sprintf('JLIB_INSTALLER_ERROR_FAIL_COPY_FOLDER', $filesource, $filedest));
							return false;
						}
						
						$step = array('type' => 'folder', 'path' => $filedest);
					} else {
						if (!(JFile::copy($filesource, $filedest, null))) {
							JError::raiseWarning(1, JText::sprintf('JLIB_INSTALLER_ERROR_FAIL_COPY_FILE', $filesource, $filedest));
							return false;
						}
						
						$step = array('type' => 'file', 'path' => $filedest);
					}
					
					/*
					 * Since we copied a file/folder, we want to add it to the installation step stack so that
					 * in case we have to roll back the installation we can remove the files copied.
					 */
					$this->_stepStack[] = $step;
				}
			}
		} else {
			/*
			 * The $files variable was either not an array or an empty array
			 */
			return false;
		}
		return count($files);
	}


	/**
	 * Method to parse through a files element of the installation manifest and remove
	 * the files that were installed
	 *
	 * @access	public
	 * @param	object	$element 	The xml node to process
	 * @param	int		$cid		Application ID of application to remove from
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	public function removeFiles($element, $cid = 0)
	{
		if (!$element || !count($element->children())) {
			// Either the tag does not exist or has no children therefore we return zero files processed.
			return true;
		}
		// Initialise variables.
		$removefiles = array();
		$retval = true;
		
		$debug = false;
		if (isset($GLOBALS['installerdebug']) && $GLOBALS['installerdebug']) {
			$debug = true;
		}
		
		// Get the client info if we're using a specific client
		jimport('joomla.application.helper');
		if ($cid > -1) {
			$client = JApplicationHelper::getClientInfo($cid);
		} else {
			$client = null;
		}
		
		// Get the array of file nodes to process
		$files = $element->children();
		if (count($files) == 0) {
			// No files to process
			return true;
		}
		
		$folder = '';
		
		/*
		 * Here we set the folder we are going to remove the files from.  There are a few
		 * special cases that need to be considered for certain reserved tags.
		 */
		switch ($element->getName()) {
			case 'media':
				if ((string) $element->attributes()->destination) {
					$folder = (string) $element->attributes()->destination;
				} else {
					$folder = '';
				}
				$source = $client->path . DS . 'media' . DS . $folder;
				break;
			
			case 'languages':
				$lang_client = (string) $element->attributes()->client;
				if ($lang_client) {
					$client = JApplicationHelper::getClientInfo($lang_client, true);
					$source = $client->path . DS . 'language';
				} else {
					if ($client) {
						$source = $client->path . DS . 'language';
					} else {
						$source = '';
					}
				}
				break;
			
			default:
				if ($client) {
					$pathname = 'extension_' . $client->name;
					$source = $this->getPath($pathname);
				} else {
					$pathname = 'extension_root';
					$source = $this->getPath($pathname);
				}
				break;
		}
		
		// Process each file in the $files array (children of $tagName).
		foreach ($files as $file) {
			/*
			 * If the file is a language, we must handle it differently.  Language files
			 * go in a subdirectory based on the language code, ie.
			 *
			 * 		<language tag="en_US">en_US.mycomponent.ini</language>
			 *
			 * would go in the en_US subdirectory of the languages directory.
			 */
			if ($file->getName() == 'language' && (string) $file->attributes()->tag != '') {
				if ($source) {
					$path = $source . DS . $file->attributes()->tag . DS . basename((string) $file);
				} else {
					$target_client = JApplicationHelper::getClientInfo((string) $file->attributes()->client, true);
					$path = $target_client->path . DS . 'language' . DS . $file->attributes()->tag . DS . basename((string) $file);
				}
				
				// If the language folder is not present, then the core pack hasn't been installed... ignore
				if (!JFolder::exists(dirname($path))) {
					continue;
				}
			} else {
				$path = $source . DS . $file;
			}
			
			/*
			 * Actually delete the files/folders
			 */
			if (JFolder::exists($path)) {
				$val = JFolder::delete($path);
			} else {
				$val = JFile::delete($path);
			}
			
			if ($val === false) {
				JError::raiseWarning(43, 'Failed to delete ' . $path);
				$retval = false;
			}
		}
		
		if (!empty($folder)) {
			$val = JFolder::delete($source);
		}
		
		return $retval;
	}


	/**
	 * Copies the installation manifest file to the extension folder in the given client
	 *
	 * @access	public
	 * @param	int		$cid	Where to copy the installfile [optional: defaults to 1 (admin)]
	 * @return	boolean	True on success, False on error
	 * @since	1.5
	 */
	public function copyManifest($cid = 1)
	{
		// Get the client info
		jimport('joomla.application.helper');
		$client = JApplicationHelper::getClientInfo($cid);
		
		$path['src'] = $this->getPath('manifest');
		
		if ($client) {
			$pathname = 'extension_' . $client->name;
			$path['dest'] = $this->getPath($pathname) . DS . basename($this->getPath('manifest'));
		} else {
			$pathname = 'extension_root';
			$path['dest'] = $this->getPath($pathname) . DS . basename($this->getPath('manifest'));
		}
		return $this->copyFiles(array($path), true);
	}


	/**
	 * Tries to find the package manifest file
	 *
	 * @access private
	 * @return boolean True on success, False on error
	 * @since 1.0
	 */
	public function findManifest()
	{
		// Get an array of all the xml files from the installation directory
		$xmlfiles = JFolder::files($this->getPath('source'), '.xml$', 1, true);
		// If at least one xml file exists
		if (!empty($xmlfiles)) {
			foreach ($xmlfiles as $file) {
				// Is it a valid joomla installation manifest file?
				$manifest = $this->isManifest($file);
				if (!is_null($manifest)) {
					// If the root method attribute is set to upgrade, allow file overwrite
					if ((string) $manifest->attributes()->method == 'upgrade') {
						$this->_upgrade = true;
						$this->_overwrite = true;
					}
					
					// If the overwrite option is set, allow file overwriting
					if ((string) $manifest->attributes()->overwrite == 'true') {
						$this->_overwrite = true;
					}
					
					//thanhnv
					//always overwrite files on repository with newly upload files
					$this->_overwrite = true;
					
					// Set the manifest object and path
					$this->manifest = $manifest;
					$this->setPath('manifest', $file);
					
					// Set the installation source path to that of the manifest file
					$this->setPath('source', dirname($file));
					return true;
				}
			}
			
			// None of the xml files found were valid install files
			JError::raiseWarning(1, JText::_('JLIB_INSTALLER_ERROR_NOTFINDJOOMLAXMLSETUPFILE'));
			return false;
		} else {
			// No xml files were found in the install folder
			JError::raiseWarning(1, JText::_('JLIB_INSTALLER_ERROR_NOTFINDXMLSETUPFILE'));
			return false;
		}
	}


	/**
	 * Is the xml file a valid Joomla installation manifest file
	 *
	 * @access	private
	 * @param	string	$file	An xmlfile path to check
	 * @return	mixed	A JXMLElement, or null if the file failed to parse
	 * @since	1.5
	 */
	public function isManifest($file)
	{
		// Initialise variables.
		$xml = JFactory::getXML($file);
		
		// If we cannot load the xml file return null
		if (!$xml) {
			return null;
		}
		
		/*
		 * Check for a valid XML root tag.
		 * @todo: Remove backwards compatability in a future version
		 * Should be 'extension', but for backward compatability we will accept 'extension' or 'install'.
		 */
		
		// 1.5 uses 'install'
		// 1.6 uses 'extension'
		if ($xml->getName() != 'install' && $xml->getName() != 'extension') {
			return null;
		}
		
		// Valid manifest file return the object
		return $xml;
	}


	/**
	 * Generates a manifest cache
	 * @return string serialised manifest data
	 */
	public function generateManifestCache()
	{
		return serialize(JApplicationHelper::parseXMLInstallFile($this->getPath('manifest')));
	}


	/**
	 * set status of extension uploading progress
	 *
	 * @param (object) $ext - extension information
	 * @param (boolean) $error 
	 * @param (string) $message
	 * @param (string) $location - the location where extension is uploaded to
	 */
	function setResult($ext, $error = false, $message = '', $location = '')
	{
		$error = (!$error) ? 0 : 1;
		$this->results[] = array('ext' => $ext, 'error' => $error, 'message' => $message, 'location' => $location);
	}


	function resetResult()
	{
		$this->results = array();
	}


	function getResults()
	{
		$result = "";
		if (count($this->results) > 0) {
			$result .= "
			<table class=\"ja-uc-child\">
		      <tr>
		        <th width=\"30\"> </th>
		        <th width=\"150\">" . JText::_("EXTENSION_NAME") . "</th>
		        <th>" . JText::_("TYPE") . "</th>
		        <th>" . JText::_("VERSION") . "</th>
		        <th>" . JText::_("RESULT") . "</th>
		      </tr>";
			foreach ($this->results as $item) {
				$error = intval($item['error']);
				$ext = $item['ext'];
				if (!$error) {
					$css = "upload-success";
					
					$relLocation = substr($item['location'], strlen(JA_WORKING_DATA_FOLDER));
					$relLocation = FileSystemHelper::clean($relLocation, "/");
					$url = "index.php?option=com_jaextmanager&view=repo&folder={$relLocation}";
					$linkRepo = " <a href=\"{$url}\" onclick=\"opener.location='{$url}'; return false;\" target=\"_parent\" title=\"" . addslashes($item['location']) . "\">" . JText::_("REPOSITORY") . "</a>";
					
					$message = JText::sprintf('THE_S_S__VESION_S_IS_SUCCESSFULLY_UPLOADED_TO_LOCAL_REPOSITORY', $ext->type, $ext->name, $ext->version);
					$message .= JText::sprintf('GO_TO_S_TO_SEE_THE_UPLOADED_FILES_OF_THIS_EXTENSIONSSMALL', $linkRepo);
				
				} else {
					$css = "upload-error";
					$message = $item["message"];
				}
				
				$result .= "
			      <tr class=\"" . $css . "\">
			        <td class=\"icon\"> </td>
			        <td><span title=\"" . $ext->extKey . "\">" . $ext->name . "</span></td>
			        <td>" . $ext->type . "</td>
			        <td>" . $ext->version . "</td>
			        <td>" . $message . "</td>
			      </tr>";
			}
			$result .= "</table>";
		}
		return $result;
	}


	function printResults()
	{
		echo $this->getResults();
	}


	function debug()
	{
		return $this->_debug;
	}
}
?>