<?php
/**
 *  @package FrameworkOnFramework
 *  @copyright Copyright (c)2010-2013 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

jimport('joomla.application.component.controller');

require_once(dirname(__FILE__).'/input.php');

/**
 * Guess what? JController is an interface in Joomla! 3.0. Holly smoke, Batman!
 */
if(!class_exists('FOFWorksAroundJoomlaToGetAController')) {
	if(interface_exists('JController')) {
		abstract class FOFWorksAroundJoomlaToGetAController extends JControllerLegacy {}
	} else {
		class FOFWorksAroundJoomlaToGetAController extends JController {}
	}
}
/**
 * FrameworkOnFramework controller class
 *
 * FrameworkOnFramework is a set of classes whcih extend Joomla! 1.5 and later's
 * MVC framework with features making maintaining complex software much easier,
 * without tedious repetitive copying of the same code over and over again.
 */
class FOFController extends FOFWorksAroundJoomlaToGetAController
{
	/** @var string Current Joomla! version family (15 or 16) */
	protected $jversion = '15';

	/** @var string The current view name; you can override it in the configuration */
	protected $view = '';

	/** @var string The current component's name; you can override it in the configuration */
	protected $component = 'com_foobar';

	/** @var string The current component's name without the com_ prefix */
	protected $bareComponent = 'foobar';

	/** @var string The current layout; you can override it in the configuration */
	protected $layout = null;

	/** @var array A cached copy of the class configuration parameter passed during initialisation */
	protected $config = array();

	/** @var array The input variables array for this MVC triad; you can override it in the configuration */
	protected $input = array();

	/** @var bool Set to true to enable CSRF protection on selected tasks */
	protected $csrfProtection = true;

	/** @var string Overrides the name of the view's default model */
	protected $modelName = null;

	/** @var string Overrides the name of the view's default view */
	protected $viewName = null;

	/** @var array The tasks for which caching should be enabled by default */
	protected $cacheableTasks = array('browse','read');

	/** @var array ACL permissions to group mapping for Joomla! 1.5; please note that we follow Joomla! 1.6's ACL naming conventions for uniformity in here. */
	protected $aclMapJoomla15 = array(
		'core.admin'		=> 'Super Administrator',
		'core.manage'		=> 'Administrator',
		'core.create'		=> 'Author',
		'core.delete'		=> 'Manager',
		'core.edit'			=> 'Editor',
		'core.edit.state'	=> 'Publisher'
	);

	/**
	 * @var int Bit mask to enable JRouting on redirects.
	 * 0 = never
	 * 1 = frontend only
	 * 2 = backend  only
	 * 3 = always
	 */
	protected $autoRouting = 0;

	private $viewObject = null;

	private $modelObject = null;

	/**
	 * Gets a static (Singleton) instance of a controller class. It loads the
	 * relevant controller file from the component's directory or, if it doesn't
	 * exist, creates a new controller object out of thin air.
	 *
	 * @param string $option Component name, e.g. com_foobar
	 * @param string $view The view name, also used for the controller name
	 * @param array $config Configuration parameters
	 * @return FOFController
	 */
	public static function &getAnInstance($option = null, $view = null, $config = array())
	{
		static $instances = array();

		$hash = $option.$view;
		if(!array_key_exists($hash, $instances)) {
			$instances[$hash] = self::getTmpInstance($option, $view, $config);
		}

		return $instances[$hash];
	}

	public static function &getTmpInstance($option = null, $view = null, $config = array())
	{
		$config['option'] = !is_null($option) ? $option : JRequest::getCmd('option','com_foobar');
		$config['view'] = !is_null($view) ? $view : JRequest::getCmd('view','cpanel');

		$classType = FOFInflector::pluralize($config['view']);
		$className = ucfirst(str_replace('com_', '', $config['option'])).'Controller'.ucfirst($classType);
		if (!class_exists( $className )) {
			list($isCli, $isAdmin) = FOFDispatcher::isCliAdmin();
			if($isAdmin) {
				$basePath = JPATH_ADMINISTRATOR;
			} elseif($isCli) {
				$basePath = JPATH_ROOT;
			} else {
				$basePath = JPATH_SITE;
			}

			$searchPaths = array(
				$basePath.'/components/'.$config['option'].'/controllers',
				JPATH_ADMINISTRATOR.'/components/'.$config['option'].'/controllers'
			);
			if(array_key_exists('searchpath', $config)) {
				array_unshift($searchPaths, $config['searchpath']);
			}

			jimport('joomla.filesystem.path');
			$path = JPath::find(
				$searchPaths,
				strtolower(FOFInflector::pluralize($config['view'])).'.php'
			);

			if ($path) {
				require_once $path;
			}
		}

		if(!class_exists($className)) {
			$classType = FOFInflector::singularize($config['view']);
			$className = ucfirst(str_replace('com_', '', $config['option'])).'Controller'.ucfirst($classType);
		}

		if (!class_exists( $className )) {
			list($isCli, $isAdmin) = FOFDispatcher::isCliAdmin();
			if($isAdmin) {
				$basePath = JPATH_ADMINISTRATOR;
			} elseif($isCli) {
				$basePath = JPATH_ROOT;
			} else {
				$basePath = JPATH_SITE;
			}

			$searchPaths = array(
				$basePath.'/components/'.$config['option'].'/controllers',
				JPATH_ADMINISTRATOR.'/components/'.$config['option'].'/controllers'
			);
			if(array_key_exists('searchpath', $config)) {
				array_unshift($searchPaths, $config['searchpath']);
			}

			jimport('joomla.filesystem.path');
			$path = JPath::find(
				$searchPaths,
				strtolower(FOFInflector::singularize($config['view'])).'.php'
			);

			if ($path) {
				require_once $path;
			}
		}

		if (!class_exists( $className )) {
			$className = 'FOFController';
		}
		$instance = new $className($config);

		return $instance;
	}

	/**
	 * Public constructor of the Controller class
	 *
	 * @param array $config Optional configuration parameters
	 */
	public function __construct($config = array())
	{
		parent::__construct();

		// Do we have Joomla! 1.6 or later?
		if( version_compare( JVERSION, '1.6.0', 'ge' ) ) {
			$this->jversion = '16';
		}

		// Cache the config
		$this->config = $config;

		// Get the input for this MVC triad
		if(array_key_exists('input', $config)) {
			$this->input = $config['input'];
		} else {
			$this->input = JRequest::get('default', 3);
		}

		// Get the default values for the component and view names
		$this->component = FOFInput::getCmd('option','com_foobar',$this->input);
		$this->view      = FOFInput::getCmd('view','cpanel',$this->input);
		$this->layout    = FOFInput::getCmd('layout',null,$this->input);

		// Overrides from the config
		if(array_key_exists('option', $config)) $this->component = $config['option'];
		if(array_key_exists('view', $config))   $this->view      = $config['view'];
		if(array_key_exists('layout', $config)) $this->layout    = $config['layout'];

		FOFInput::setVar('option', $this->component, $this->input);

		// Set the bareComponent variable
		$this->bareComponent = str_replace('com_', '', strtolower($this->component));

		// Set the $name/$_name variable
		if(version_compare(JVERSION, '1.6.0', 'ge')) {
			$this->name = $this->bareComponent;
		} else {
			$this->_name = $this->bareComponent;
		}

		// Set the _basePath / basePath variable
		$isAdmin   = version_compare(JVERSION, '1.6.0', 'ge') ? (!JFactory::$application ? false : JFactory::getApplication()->isAdmin()) : JFactory::getApplication()->isAdmin();
		$basePath  = $isAdmin ? JPATH_ADMINISTRATOR : JPATH_ROOT;
		$basePath .= '/components/'.$this->component;
		if(array_key_exists('base_path', $config)) $basePath = $config['base_path'];
		if(version_compare(JVERSION, '1.6.0', 'ge')) {
			$this->basePath = $basePath;
		} else {
			$this->_basePath = $basePath;
		}

		// Set the CSRF protection
		if(array_key_exists('csrf_protection', $config)) {
			$this->csrfProtection = $config['csrf_protection'];
		}

		// Set any model/view name overrides
		if(array_key_exists('viewName', $config)) $this->setThisViewName($config['viewName']);
		if(array_key_exists('modelName', $config)) $this->setThisModelName($config['modelName']);

		// Set the ACL preferences
		if( !version_compare( JVERSION, '1.6.0', 'ge' ) ) {
			// Joomla! 1.5 ACL mapping
			$acl = JFactory::getACL();
			foreach($this->aclMapJoomla15 as $area => $mingroup) {
				$mingroup = strtolower($mingroup);
				$groups = array();
				switch($mingroup) {
					case 'registered':
						$groups[] = 'registered';
					case 'author':
						$groups[] = 'author';
					case 'editor':
						$groups[] = 'editor';
					case 'publisher':
						$groups[] = 'publisher';
					case 'manager':
						$groups[] = 'manager';
					case 'administrator':
						$groups[] = 'administrator';
					case 'super administrator':
						$groups[] = 'super administrator';
						break;
				}
				if(empty($groups)) continue;
				foreach($groups as $group) {
					$acl->addACL($this->component, $area, 'users', $group );
				}
			}
		}

		// Caching
		if(array_key_exists('cacheableTasks', $config)) {
			if(is_array($config['cacheableTasks'])) $this->cacheableTasks = $config['cacheableTasks'];
		}

		//Bit mask for auto routing on setRedirect
		if(array_key_exists('autoRouting', $config)) $this->autoRouting = $config['autoRouting'];
	}

	/**
	 * Executes a given controller task. The onBefore<task> and onAfter<task>
	 * methods are called automatically if they exist.
	 *
	 * @param string $task
	 * @return null|bool False on execution failure
	 */
	public function execute($task) {
		$method_name = 'onBefore'.ucfirst($task);
		if(method_exists($this, $method_name)) {
			$result = $this->$method_name();
			if(!$result) return false;
		}

		// Do not allow the display task to be directly called
		$task = strtolower($task);
		if (isset($this->taskMap[$task])) {
			$doTask = $this->taskMap[$task];
		}
		elseif (isset($this->taskMap['__default'])) {
			$doTask = $this->taskMap['__default'];
		}
		else {
			$doTask = null;
		}
		if($doTask == 'display') {
			JError::raiseError(400, 'Bad Request');
		}

		parent::execute($task);

		$method_name = 'onAfter'.ucfirst($task);
		if(method_exists($this, $method_name)) {
			$result = $this->$method_name();
			if(!$result) return false;
		}
	}

	/**
	 * Default task. Assigns a model to the view and asks the view to render
	 * itself.
	 *
	 * YOU MUST NOT USETHIS TASK DIRECTLY IN A URL. It is supposed to be
	 * used ONLY inside your code. In the URL, use task=browse instead.
	 *
	 * @param bool $cachable Is this view cacheable?
	 */
	public function display($cachable = false, $urlparams = false)
	{
		$document = JFactory::getDocument();
		$viewType	= $document->getType();

		$view = $this->getThisView();

		// Get/Create the model
		if ($model = $this->getThisModel()) {
			// Push the model into the view (as default)
			$view->setModel($model, true);
		}

		// Set the layout
		$view->setLayout(is_null($this->layout) ? 'default' : $this->layout);

		// Display the view
		if(version_compare(JVERSION, '1.6.0', 'ge')) {
			$conf = JFactory::getConfig();
			if (JFactory::getApplication()->isSite() && $cachable && $viewType != 'feed' && $conf->get('caching') >= 1) {
				$option	= FOFInput::getCmd('option','com_foobar',$this->input);
				$cache	= JFactory::getCache($option, 'view');

				if (is_array($urlparams)) {
					$app = JFactory::getApplication();

					$registeredurlparams = $app->get('registeredurlparams');

					if (empty($registeredurlparams)) {
						$registeredurlparams = new stdClass;
					}

					foreach ($urlparams AS $key => $value)
					{
						// Add your safe url parameters with variable type as value {@see JFilterInput::clean()}.
						$registeredurlparams->$key = $value;
					}

					$app->set('registeredurlparams', $registeredurlparams);
				}
				$cache->get($view, 'display');
			} else {
				$view->display();
			}
		} else {
			if (JFactory::getApplication()->isSite() && $cachable && $viewType != 'feed') {
				$cache = JFactory::getCache($this->component, 'view');
				$cache->get($view, 'display');
			} else {
				$view->display();
			}
		}


	}

	/**
	 * Implements a default browse task, i.e. read a bunch of records and send
	 * them to the browser.
	 *
	 * @param bool $cachable Is this view cacheable?
	 */
	public function browse()
	{
		if(FOFInput::getInt('savestate', -999, $this->input) == -999) {
			FOFInput::setVar('savestate', true, $this->input);
		}
		$this->display(in_array('browse', $this->cacheableTasks));
	}

	/**
	 * Single record read. The id set in the request is passed to the model and
	 * then the item layout is used to render the result.
	 *
	 * @param bool $cachable Is this view cacheable?
	 */
	public function read()
	{
		// Load the model
		$model = $this->getThisModel();
		if(!$model->getId()) $model->setIDsFromRequest();

		// Set the layout to item, if it's not set in the URL
		if(is_null($this->layout)) $this->layout = 'item';

		// Display
		$this->display(in_array('read', $this->cacheableTasks));
	}

	/**
	 * Single record add. The form layout is used to present a blank page.
	 *
	 * @param bool $cachable Is this view cacheable?
	 */
	public function add()
	{
		// Load and reset the model
		$model = $this->getThisModel();
		$model->reset();

		// Set the layout to form, if it's not set in the URL
		if(is_null($this->layout)) $this->layout = 'form';

		// Display
		$this->display(in_array('add', $this->cacheableTasks));
	}

	/**
	 * Single record edit. The ID set in the request is passed to the model,
	 * then the form layout is used to edit the result.
	 *
	 * @param bool $cachable Is this view cacheable?
	 */
	public function edit()
	{
		// Load the model
		$model = $this->getThisModel();
		if(!$model->getId()) $model->setIDsFromRequest();
		$status = $model->checkout();

		if(!$status) {
			// Redirect on error
			if($customURL = FOFInput::getString('returnurl','',$this->input)) $customURL = base64_decode($customURL);
			$url = !empty($customURL) ? $customURL : 'index.php?option='.$this->component.'&view='.FOFInflector::pluralize($this->view);
			$this->setRedirect($url, $model->getError(), 'error');
			return;
		}

		// Set the layout to form, if it's not set in the URL
		if(is_null($this->layout)) $this->layout = 'form';

		// Display
		$this->display(in_array('edit', $this->cacheableTasks));
	}

	/**
	 * Save the incoming data and then return to the Edit task
	 */
	public function apply()
	{
		// CSRF prevention
		if($this->csrfProtection) {
			$this->_csrfProtection();
		}

		$model = $this->getThisModel();
		$result = $this->applySave();

		// Redirect to the edit task
		if($result)
		{
			//check if i'm using an AJAX call, in this case there is no need to redirect
			$format = FOFInput::getString('format','', $this->input);
			if($format == 'json')
			{
				echo json_encode($result);
				return;
			}

			$id = FOFInput::getInt('id', 0, $this->input);
			$textkey = strtoupper($this->component).'_LBL_'.strtoupper($this->view).'_SAVED';
			if($customURL = FOFInput::getString('returnurl','',$this->input)) $customURL = base64_decode($customURL);
			$url = !empty($customURL) ? $customURL : 'index.php?option='.$this->component.'&view='.$this->view.'&task=edit&id='.$id;
			$this->setRedirect($url, JText::_($textkey));
		}
	}

	/**
	 * Duplicates selected items
	 */
	public function copy()
	{
		// CSRF prevention
		if($this->csrfProtection) {
			$this->_csrfProtection();
		}

		$model = $this->getThisModel();
		if(!$model->getId()) $model->setIDsFromRequest();

		$status = $model->copy();

		//check if i'm using an AJAX call, in this case there is no need to redirect
		$format = FOFInput::getString('format','', $this->input);
		if($format == 'json')
		{
			echo json_encode($status);
			return;
		}

		// redirect
		if($customURL = FOFInput::getString('returnurl','',$this->input)) $customURL = base64_decode($customURL);
		$url = !empty($customURL) ? $customURL : 'index.php?option='.$this->component.'&view='.FOFInflector::pluralize($this->view);
		if(!$status)
		{
			$this->setRedirect($url, $model->getError(), 'error');
		}
		else
		{
			$this->setRedirect($url);
		}
	}

	/**
	 * Save the incoming data and then return to the Browse task
	 */
	public function save()
	{
		// CSRF prevention
		if($this->csrfProtection) {
			$this->_csrfProtection();
		}

		$result = $this->applySave();

		// Redirect to the display task
		if($result)
		{
			//check if i'm using an AJAX call, in this case there is no need to redirect
			$format = FOFInput::getString('format','', $this->input);
			if($format == 'json')
			{
				echo json_encode($result);
				return;
			}

			$textkey = strtoupper($this->component).'_LBL_'.strtoupper($this->view).'_SAVED';
			if($customURL = FOFInput::getString('returnurl','',$this->input)) $customURL = base64_decode($customURL);
			$url = !empty($customURL) ? $customURL : 'index.php?option='.$this->component.'&view='.FOFInflector::pluralize($this->view);
			$this->setRedirect($url, JText::_($textkey));
		}
	}

	/**
	 * Save the incoming data and then return to the Add task
	 */
	public function savenew()
	{
		// CSRF prevention
		if($this->csrfProtection) {
			$this->_csrfProtection();
		}

		$result = $this->applySave();

		// Redirect to the display task
		if($result) {
			$textkey = strtoupper($this->component).'_LBL_'.strtoupper($this->view).'_SAVED';
			if($customURL = FOFInput::getString('returnurl','',$this->input)) $customURL = base64_decode($customURL);
			$url = !empty($customURL) ? $customURL : 'index.php?option='.$this->component.'&view='.$this->view.'&task=add';
			$this->setRedirect($url, JText::_($textkey));
		}
	}

	/**
	 * Cancel the edit, check in the record and return to the Browse task
	 */
	public function cancel()
	{
		$model = $this->getThisModel();
		if(!$model->getId()) $model->setIDsFromRequest();
		$model->checkin();

		// Remove any saved data
		JFactory::getSession()->set($model->getHash().'savedata', null );

		// Redirect to the display task
		if($customURL = FOFInput::getString('returnurl','',$this->input)) $customURL = base64_decode($customURL);
		$url = !empty($customURL) ? $customURL : 'index.php?option='.$this->component.'&view='.FOFInflector::pluralize($this->view);
		$this->setRedirect($url);
	}

	public function accesspublic()
	{
		// CSRF prevention
		if($this->csrfProtection) {
			$this->_csrfProtection();
		}

		$this->setaccess(0);
	}

	public function accessregistered()
	{
		// CSRF prevention
		if($this->csrfProtection) {
			$this->_csrfProtection();
		}

		$this->setaccess(1);
	}

	public function accessspecial()
	{
		// CSRF prevention
		if($this->csrfProtection) {
			$this->_csrfProtection();
		}

		$this->setaccess(2);
	}

	public function publish()
	{
		// CSRF prevention
		if($this->csrfProtection) {
			$this->_csrfProtection();
		}

		$this->setstate(1);
	}

	public function unpublish()
	{
		// CSRF prevention
		if($this->csrfProtection) {
			$this->_csrfProtection();
		}

		$this->setstate(0);
	}

	public function saveorder()
	{
		// CSRF prevention
		if($this->csrfProtection) {
			$this->_csrfProtection();
		}

		$model = $this->getThisModel();
		if(!$model->getId()) $model->setIDsFromRequest();

		$ids = $model->getIds();
		$orders = FOFInput::getArray('order', array(), $this->input);

		if($n = count($ids))
		{
			for($i = 0; $i < $n; $i++)
			{
				$model->setId( $ids[$i] );
				$neworder = (int)$orders[$i];

				$item = $model->getItem();
				$key = $item->getKeyName();
				if($item->$key == $ids[$i])
				{
					$item->ordering = $neworder;
					$model->save($item);
				}
			}
		}

		$status = $model->reorder();

		//check if i'm using an AJAX call, in this case there is no need to redirect
		$format = FOFInput::getString('format','', $this->input);
		if($format == 'json')
		{
			echo json_encode($status);
			return;
		}

		// redirect
		if($customURL = FOFInput::getString('returnurl','',$this->input)) $customURL = base64_decode($customURL);
		$url = !empty($customURL) ? $customURL : 'index.php?option='.$this->component.'&view='.FOFInflector::pluralize($this->view);
		$this->setRedirect($url);
		return;
	}

	public function orderdown()
	{
		// CSRF prevention
		if($this->csrfProtection) {
			$this->_csrfProtection();
		}

		$model = $this->getThisModel();
		if(!$model->getId()) $model->setIDsFromRequest();

		$status = $model->move(1);

		//check if i'm using an AJAX call, in this case there is no need to redirect
		$format = FOFInput::getString('format','', $this->input);
		if($format == 'json')
		{
			echo json_encode($status);
			return;
		}

		// redirect
		if($customURL = FOFInput::getString('returnurl','',$this->input)) $customURL = base64_decode($customURL);
		$url = !empty($customURL) ? $customURL : 'index.php?option='.$this->component.'&view='.FOFInflector::pluralize($this->view);
		if(!$status)
		{
			$this->setRedirect($url, $model->getError(), 'error');
		}
		else
		{
			$this->setRedirect($url);
		}
	}

	public function orderup()
	{
		// CSRF prevention
		if($this->csrfProtection) {
			$this->_csrfProtection();
		}

		$model = $this->getThisModel();
		if(!$model->getId()) $model->setIDsFromRequest();

		$status = $model->move(-1);

		//check if i'm using an AJAX call, in this case there is no need to redirect
		$format = FOFInput::getString('format','', $this->input);
		if($format == 'json')
		{
			echo json_encode($status);
			return;
		}

		// redirect
		if($customURL = FOFInput::getString('returnurl','',$this->input)) $customURL = base64_decode($customURL);
		$url = !empty($customURL) ? $customURL : 'index.php?option='.$this->component.'&view='.FOFInflector::pluralize($this->view);
		if(!$status)
		{
			$this->setRedirect($url, $model->getError(), 'error');
		}
		else
		{
			$this->setRedirect($url);
		}
	}

	public function remove()
	{
		// CSRF prevention
		if($this->csrfProtection) {
			$this->_csrfProtection();
		}

		$model = $this->getThisModel();
		if(!$model->getId()) $model->setIDsFromRequest();
		$status = $model->delete();

		//check if i'm deleting using an AJAX call, in this case there is no need to redirect
		$format = FOFInput::getString('format','', $this->input);
		if($format == 'json')
		{
			echo json_encode($status);
			return;
		}

		// redirect
		if($customURL = FOFInput::getString('returnurl','',$this->input)) $customURL = base64_decode($customURL);
		$url = !empty($customURL) ? $customURL : 'index.php?option='.$this->component.'&view='.FOFInflector::pluralize($this->view);
		if(!$status)
		{
			$this->setRedirect($url, $model->getError(), 'error');
		}
		else
		{
			$this->setRedirect($url);
		}
		return;
	}

	public function setRedirect($url, $msg = null, $type = null)
	{
		//do the logic only if we're parsing a raw url (index.php?foo=bar&etc=etc)
		if(strpos($url, 'index.php') === 0)
		{
			//@TODO Should we check for CLI case, too?
			$isCLI   = version_compare(JVERSION, '1.6.0', 'ge') ? (JFactory::getApplication() instanceof JException) : false;
			$isAdmin = version_compare(JVERSION, '1.6.0', 'ge') ? (!JFactory::$application ? false : JFactory::getApplication()->isAdmin()) : JFactory::getApplication()->isAdmin();
			$auto	 = false;

			if(($this->autoRouting == 2 || $this->autoRouting == 3) &&  $isAdmin) {
				$auto = true;
			} elseif(($this->autoRouting == 1 || $this->autoRouting == 3) && !$isAdmin) {
				$auto = true;
			}

			if($auto) $url = JRoute::_($url, false);
		}

		parent::setRedirect($url, $msg, $type);
	}

	protected final function setstate($state = 0)
	{
		$model = $this->getThisModel();
		if(!$model->getId()) $model->setIDsFromRequest();

		$status = $model->publish($state);

		//check if i'm using an AJAX call, in this case there is no need to redirect
		$format = FOFInput::getString('format','', $this->input);
		if($format == 'json')
		{
			echo json_encode($status);
			return;
		}

		// redirect
		if($customURL = FOFInput::getString('returnurl','',$this->input)) $customURL = base64_decode($customURL);
		$url = !empty($customURL) ? $customURL : 'index.php?option='.$this->component.'&view='.FOFInflector::pluralize($this->view);
		if(!$status)
		{
			$this->setRedirect($url, $model->getError(), 'error');
		}
		else
		{
			$this->setRedirect($url);
		}
		return;
	}

	protected final function setaccess($level = 0)
	{
		$model = $this->getThisModel();
		if(!$model->getId()) $model->setIDsFromRequest();
		$id = $model->getId();

		$item = $model->getItem();
		$key = $item->getKeyName();
		$loadedid = $item->$key;

		if($id == $loadedid)
		{
			$item->access = $level;
			$status = $model->save($item);
		}
		else
		{
			$status = false;
		}


		// redirect
		if($customURL = FOFInput::getString('returnurl','',$this->input)) $customURL = base64_decode($customURL);
		$url = !empty($customURL) ? $customURL : 'index.php?option='.$this->component.'&view='.FOFInflector::pluralize($this->view);
		if(!$status)
		{
			$this->setRedirect($url, $model->getError(), 'error');
		}
		else
		{
			$this->setRedirect($url);
		}
		return;
	}

	protected final function applySave()
	{
		// Load the model
		$model = $this->getThisModel();
		if(!$model->getId()) $model->setIDsFromRequest();
		$id = $model->getId();

		$data = $this->input;
		$this->onBeforeApplySave($data);
		$status = $model->save($data);

		if($status && ($id != 0)) {
			// Try to check-in the record if it's not a new one
			$status = $model->checkin();

			if($status)
			{
				$status = $this->onAfterApplySave();
			}
		}

		FOFInput::setVar('id', $model->getId(), $this->input);

		if(!$status) {
			//check if i'm using an AJAX call, in this case there is no need to redirect
			$format = FOFInput::getString('format','', $this->input);
			if($format == 'json')
			{
				echo json_encode($status);
				return;
			}

			// Redirect on error
			$id = $model->getId();
			if($customURL = FOFInput::getString('returnurl','',$this->input)) $customURL = base64_decode($customURL);
			$url = !empty($customURL) ? $customURL : 'index.php?option='.$this->component.'&view='.$this->view.'&task=edit&id='.$id;
			$this->setRedirect($url, '<li>'.implode('</li><li>',$model->getErrors()), 'error').'</li>';
			return false;
		} else {
			$session = JFactory::getSession();
			$session->set($model->getHash().'savedata', null );
			return true;
		}
	}

	/**
	 * Returns the default model associated with the current view
	 * @return FOFModel The global instance of the model (singleton)
	 */
	public final function getThisModel($config = array())
	{
		if(!is_object($this->modelObject)) {
			if(!empty($this->modelName)) {
				$parts = FOFInflector::explode($this->modelName);
				$modelName = ucfirst(array_pop($parts));
				$prefix = FOFInflector::implode($parts);
			} else {
				$prefix = ucfirst($this->bareComponent).'Model';
				$modelName = ucfirst(FOFInflector::pluralize($this->view));
			}

			$this->modelObject = $this->getModel($modelName, $prefix, array_merge(array(
					'input'	=> $this->input
				), $config
			));
		}

		return $this->modelObject;
	}

	/**
	 * Returns current view object
	 * @return FOFView The global instance of the view object (singleton)
	 */
	public final function getThisView($config = array())
	{
		if(!is_object($this->viewObject)) {
			$prefix = null;
			$viewName = null;
			$viewType = null;

			if(!empty($this->viewName)) {
				$parts = FOFInflector::explode($this->viewName);
				$viewName = ucfirst(array_pop($parts));
				$prefix = FOFInflector::implode($parts);
			} else {
				$prefix = ucfirst($this->bareComponent).'View';
				$viewName = ucfirst($this->view);
			}

			$document = JFactory::getDocument();
			$viewType	= $document->getType();

			if(!array_key_exists('input', $config)) {
				$config['input'] = $this->input;
			}

			$basePath = ($this->jversion == '15') ? $this->_basePath : $this->basePath;
			$this->viewObject = $this->getView( $viewName, $viewType, $prefix, array_merge(array(
					'input'		=> $this->input,
					'base_path'	=>$basePath
				), $config)
			);
		}

		return $this->viewObject;
	}

	protected function createModel($name, $prefix = '', $config = array())
	{
		return $this->_createModel($name, $prefix, $config);
	}

	/**
	 * Method to load and return a model object.
	 *
	 * @access	private
	 * @param	string  The name of the model.
	 * @param	string	Optional model prefix.
	 * @param	array	Configuration array for the model. Optional.
	 * @return	mixed	Model object on success; otherwise null
	 * failure.
	 * @since	1.5
	 */
	function &_createModel( $name, $prefix = '', $config = array())
	{
		$result = null;

		// Clean the model name
		$modelName	 = preg_replace( '/[^A-Z0-9_]/i', '', $name );
		$classPrefix = preg_replace( '/[^A-Z0-9_]/i', '', $prefix );

		$result = FOFModel::getAnInstance($modelName, $classPrefix, $config);
		return $result;
	}

	protected function createView($name, $prefix = '', $type = '', $config = array())
	{
		return $this->_createView($name, $prefix, $type, $config);
	}

	function &_createView( $name, $prefix = '', $type = '', $config = array() )
	{
		$result = null;

		// Clean the view name
		$viewName	 = preg_replace( '/[^A-Z0-9_]/i', '', $name );
		$classPrefix = preg_replace( '/[^A-Z0-9_]/i', '', $prefix );
		$viewType	 = preg_replace( '/[^A-Z0-9_]/i', '', $type );

		// Guess the component name and view
		preg_match('/(.*)View$/', $prefix, $m);
		$component = 'com_'.strtolower($m[1]);
		if(array_key_exists('input', $config)) {
			$component = FOFInput::getCmd('option',$component,$config['input']);
		}
		if(array_key_exists('option', $config)) if($config['option']) $component = $config['option'];
		$config['option'] = $component;

		$view = strtolower($viewName);
		if(array_key_exists('input', $config)) {
			$view = FOFInput::getCmd('view',$view,$config['input']);
		}
		if(array_key_exists('view', $config)) if($config['view']) $view = $config['view'];

		$config['view'] = $view;

		if(array_key_exists('input', $config)) {
			FOFInput::setVar('option', $config['option'], $config['input']);
			FOFInput::setVar('view', $config['view'], $config['input']);
		}

		// Build the view class name
		$viewClass = $classPrefix . ucfirst($view);

		if ( !class_exists( $viewClass ) )
		{
			jimport( 'joomla.filesystem.path' );
			$thisPath = version_compare(JVERSION, '1.6.0', 'ge') ? $this->paths : $this->_path;
			if(JFactory::getApplication()->isSite()) {
				$thisPath['view'] = array_merge(array(
					JPATH_SITE.'/components/'.$config['option'].'/views',
					JPATH_ADMINISTRATOR.'/components/'.$config['option'].'/views'
				),$thisPath['view']);
			} else {
				$thisPath['view'] = array_merge(array(
					JPATH_ADMINISTRATOR.'/components/'.$config['option'].'/views',
					JPATH_SITE.'/components/'.$config['option'].'/views'
				),$thisPath['view']);
				$thisPath['view'][] = JPATH_ADMINISTRATOR.'/components/'.$config['option'].'/views';
				$thisPath['view'][] = JPATH_SITE.'/components/'.$config['option'].'/views';
			}

			if(version_compare(JVERSION, '1.6.0', 'ge')) {
				$viewPath = $this->createFileName( 'view', array( 'name' => $viewName, 'type' => $viewType) );
			} else {
				$viewPath = $this->_createFileName( 'view', array( 'name' => $viewName, 'type' => $viewType) );
			}
			$path = JPath::find(
				$thisPath['view'],
				$viewPath
			);
			if(!$path) {
				if(version_compare(JVERSION, '1.6.0', 'ge')) {
					$viewPath = $this->createFileName( 'view', array( 'name' => FOFInflector::singularize($viewName), 'type' => $viewType) );
				} else {
					$viewPath = $this->_createFileName( 'view', array( 'name' => FOFInflector::singularize($viewName), 'type' => $viewType) );
				}

				$path = JPath::find(
					$thisPath['view'],
					$viewPath
				);
				if($path) {
					$viewClass = $classPrefix . FOFInflector::singularize($viewName);
				}
			}
			if ($path) {
				require_once $path;
			}

			if(!class_exists($viewClass) && FOFInflector::isSingular($name)) {
				$name = FOFInflector::pluralize($name);
				$viewClass = $classPrefix . ucfirst($name);
				$result = $this->_createView($name, $prefix, $type, $config);
			}

			if(!class_exists($viewClass)) {
				$viewClass = 'FOFView'.ucfirst($type);

				list($isCli, $isAdmin) = FOFDispatcher::isCliAdmin();
				if($isAdmin) {
					$basePath = JPATH_ADMINISTRATOR;
				} elseif($isCli) {
					$basePath = JPATH_ROOT;
				} else {
					$basePath = JPATH_SITE;
				}

				if(!array_key_exists('template_path', $config)) {
					$config['template_path'] = array(
						$basePath.'/components/'.$config['option'].'/views/'.FOFInflector::pluralize($config['view']).'/tmpl',
						JPATH_BASE.'/templates/'.JFactory::getApplication()->getTemplate().'/html/'.$config['option'].'/'.FOFInflector::pluralize($config['view']),
						$basePath.'/components/'.$config['option'].'/views/'.FOFInflector::singularize($config['view']).'/tmpl',
						JPATH_BASE.'/templates/'.JFactory::getApplication()->getTemplate().'/html/'.$config['option'].'/'.FOFInflector::singularize($config['view']),
						$basePath.'/components/'.$config['option'].'/views/'.$config['view'].'/tmpl',
						JPATH_BASE.'/templates/'.JFactory::getApplication()->getTemplate().'/html/'.$config['option'].'/'.$config['view'],
					);
				}

				if(!array_key_exists('helper_path', $config)) {
					$config['helper_path'] = array(
						$basePath.'/components/'.$config['option'].'/helpers',
						JPATH_ADMINISTRATOR.'/components/'.$config['option'].'/helpers'
					);
				}
			}
		}

		$result = new $viewClass($config);
		return $result;
	}

	public function setThisViewName($viewName)
	{
		$this->viewName = $viewName;
	}

	public function setThisModelName($modelName)
	{
		$this->modelName = $modelName;
	}

	/**
	 * Checks if the current user has enough privileges for the requested ACL
	 * area.
	 *
	 * @param string $area The ACL area, e.g. core.manage.
	 */
	protected function checkACL($area)
	{
		if(version_compare(JVERSION, '1.6.0', 'ge')) {
			return JFactory::getUser()->authorise($area, $this->component);
		} else {
			$user = JFactory::getUser();
			return $user->authorize($this->component, $area);
		}
	}

	protected function onBeforeApplySave(&$data)
	{
		return $data;
	}

	protected function onAfterApplySave()
	{
		return true;
	}

	/**
	 * ACL check before changing the access level; override to customise
	 *
	 * @return bool
	 */
	protected function onBeforeAccesspublic()
	{
		return $this->checkACL('core.edit.state');
	}

	/**
	 * ACL check before changing the access level; override to customise
	 *
	 * @return bool
	 */
	protected function onBeforeAccessregistered()
	{
		return $this->checkACL('core.edit.state');
	}

	/**
	 * ACL check before changing the access level; override to customise
	 *
	 * @return bool
	 */
	protected function onBeforeAccessspecial()
	{
		return $this->checkACL('core.edit.state');
	}

	/**
	 * ACL check before adding a new record; override to customise
	 *
	 * @return bool
	 */
	protected function onBeforeAdd()
	{
		return $this->checkACL('core.create');
	}

	/**
	 * ACL check before saving a new/modified record; override to customise
	 *
	 * @return bool
	 */
	protected function onBeforeApply()
	{
		return $this->checkACL('core.edit');
	}

	/**
	 * ACL check before allowing someone to browse
	 *
	 * @return bool
	 */
	protected function onBeforeBrowse()
	{
		$isAdmin = version_compare(JVERSION, '1.6.0', 'ge') ? (!JFactory::$application ? false : JFactory::getApplication()->isAdmin()) : JFactory::getApplication()->isAdmin();
		if($isAdmin) {
			return $this->checkACL('core.manage');
		} else {
			return true;
		}
	}

	/**
	 * ACL check before cancelling an edit
	 *
	 * @return bool
	 */
	protected function onBeforeCancel()
	{
		return $this->checkACL('core.edit');
	}

	/**
	 * ACL check before editing a record; override to customise
	 *
	 * @return bool
	 */
	protected function onBeforeEdit()
	{
		return $this->checkACL('core.edit');
	}

	/**
	 * ACL check before changing the ordering of a record; override to customise
	 *
	 * @return bool
	 */
	protected function onBeforeOrderdown()
	{
		return $this->checkACL('core.edit.state');
	}

	/**
	 * ACL check before changing the ordering of a record; override to customise
	 *
	 * @return bool
	 */
	protected function onBeforeOrderup()
	{
		return $this->checkACL('core.edit.state');
	}

	/**
	 * ACL check before changing the publish status of a record; override to customise
	 *
	 * @return bool
	 */
	protected function onBeforePublish()
	{
		return $this->checkACL('core.edit.state');
	}

	/**
	 * ACL check before removing a record; override to customise
	 *
	 * @return bool
	 */
	protected function onBeforeRemove()
	{
		return $this->checkACL('core.delete');
	}

	/**
	 * ACL check before saving a new/modified record; override to customise
	 *
	 * @return bool
	 */
	protected function onBeforeSave()
	{
		return $this->checkACL('core.edit');
	}

	/**
	 * ACL check before saving a new/modified record; override to customise
	 *
	 * @return bool
	 */
	protected function onBeforeSavenew()
	{
		return $this->checkACL('core.edit');
	}

	/**
	 * ACL check before changing the ordering of a record; override to customise
	 *
	 * @return bool
	 */
	protected function onBeforeSaveorder()
	{
		return $this->checkACL('core.edit.state');
	}

	/**
	 * ACL check before changing the publish status of a record; override to customise
	 *
	 * @return bool
	 */
	protected function onBeforeUnpublish()
	{
		return $this->checkACL('core.edit.state');
	}

	/**
	 * Applies CSRF protection by means of a standard Joomla! token (nonce) check.
	 * Raises a 403 Access Forbidden error through JError if the check fails.
	 */
	protected function _csrfProtection()
	{
		$hasToken = false;
		$session = JFactory::getSession();
		// Joomla! 1.5/1.6/1.7/2.5 (classic Joomla! API) method
		if(method_exists('JUtility', 'getToken')) {
			$token = JUtility::getToken();
			$hasToken = FOFInput::getVar($token, false, $this->input) == 1;
			if(!$hasToken) $hasToken = FOFInput::getVar('_token', null, $this->input) == $token;
		}
		// Joomla! 2.5+ (Platform 12.1+) method
		if(!$hasToken) {
			if(method_exists($session, 'getToken')) {
				$token = $session->getToken();
				$hasToken = FOFInput::getVar($token, false, $this->input) == 1;
				if(!$hasToken) $hasToken = FOFInput::getVar('_token', null, $this->input) == $token;
			}
		}
		// Joomla! 2.5+ formToken method
		if(!$hasToken) {
			if(method_exists($session, 'getFormToken')) {
				$token = $session->getFormToken();
				$hasToken = FOFInput::getVar($token, false, $this->input) == 1;
				if(!$hasToken) $hasToken = FOFInput::getVar('_token', null, $this->input) == $token;
			}
		}

		if(!$hasToken) {
			JError::raiseError('403', version_compare(JVERSION, '1.6.0', 'ge') ? JText::_('JLIB_APPLICATION_ERROR_ACCESS_FORBIDDEN') : JText::_('Request Forbidden'));
			return false;
		}
	}
}