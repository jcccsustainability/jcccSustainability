<?php
/**
 *  @package FrameworkOnFramework
 *  @copyright Copyright (c)2010-2013 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

jimport('joomla.application.component.model');

/**
 * Guess what? JModel is an interface in Joomla! 3.0. Holly smoke, Batman!
 */
if(!class_exists('FOFWorksAroundJoomlaToGetAModel')) {
	if(interface_exists('JModel')) {
		abstract class FOFWorksAroundJoomlaToGetAModel extends JModelLegacy {}
	} else {
		class FOFWorksAroundJoomlaToGetAModel extends JModel {}
	}
}

/**
 * FrameworkOnFramework model class
 *
 * FrameworkOnFramework is a set of classes whcih extend Joomla! 1.5 and later's
 * MVC framework with features making maintaining complex software much easier,
 * without tedious repetitive copying of the same code over and over again.
 */
class FOFModel extends FOFWorksAroundJoomlaToGetAModel
{
	/**
	 * The name of the table to use
	 * @var string
	 */
	protected $table = null;

	/**
	 * The table object, populated when saving data
	 * @var FOFTable
	 */
	protected $otable = null;

	/**
	 * Stores a list of IDs passed to the model's state
	 * @var array
	 */
	protected $id_list = array();

	/**
	 * The first row ID passed to the model's state
	 * @var int
	 */
	protected $id = null;

	/**
	 * The table object, populated when retrieving data
	 * @var FOFTable
	 */
	protected $record = null;

	/**
	 * The list of records made available through getList
	 * @var array
	 */
	protected $list = null;

	/**
	 * Pagination object
	 * @var JPagination
	 */
	protected $pagination = null;

	/**
	 * Total rows based on the filters set in the model's state
	 * @var int
	 */
	protected $total = 0;

	/**
	 * Input variables, passed on from the controller, in an associative array
	 * @var array
	 */
	protected $input = array();

	/**
	 * The event to trigger after deleting the data.
	 * @var    string
	 */
	protected $event_after_delete = 'onContentAfterDelete';

	/**
	 * The event to trigger after saving the data.
	 * @var    string
	 */
	protected $event_after_save = 'onContentAfterSave';

	/**
	 * The event to trigger before deleting the data.
	 * @var    string
	 */
	protected $event_before_delete = 'onContentBeforeDelete';

	/**
	 * The event to trigger before saving the data.
	 * @var    string
	 */
	protected $event_before_save = 'onContentBeforeSave';

	/**
	 * The event to trigger after changing the published state of the data.
	 * @var    string
	 */
	protected $event_change_state = 'onContentChangeState';

	/**
	 * Should I save the model's state in the session?
	 * @var bool
	 */
	protected $_savestate = null;

	/**
	 * Returns a new model object. Unless overriden by the $config array, it will
	 * try to automatically populate its state from the request variables.
	 *
	 * @param string $type
	 * @param string $prefix
	 * @param array $config
	 * @return FOFModel
	 */
	public static function &getAnInstance( $type, $prefix = '', $config = array() )
	{
		$type		= preg_replace('/[^A-Z0-9_\.-]/i', '', $type);
		$modelClass	= $prefix.ucfirst($type);
		$result		= false;

		// Guess the component name and include path
		preg_match('/(.*)Model$/', $prefix, $m);
		$component = 'com_'.strtolower($m[1]);

		if(array_key_exists('input', $config)) {
			$component = FOFInput::getCmd('option',$component,$config['input']);
		}
		$config['option'] = $component;

		$needsAView = true;
		if(array_key_exists('view', $config)) {
			if(!empty($config['view'])) $needsAView = false;
		}
		if($needsAView) $config['view'] = strtolower($type);

		if (!class_exists( $modelClass ))
		{
			if(interface_exists('JModel')) {
				$include_paths = JModelLegacy::addIncludePath();
			} else {
				$include_paths = JModel::addIncludePath();
			}

			list($isCLI, $isAdmin) = FOFDispatcher::isCliAdmin();
			if($isAdmin) {
				$extra_paths = array(
					JPATH_ADMINISTRATOR.'/components/'.$component.'/models',
					JPATH_SITE.'/components/'.$component.'/models'
				);
			} else {
				$extra_paths = array(
					JPATH_SITE.'/components/'.$component.'/models',
					JPATH_ADMINISTRATOR.'/components/'.$component.'/models'
				);
			}
			$include_paths = array_merge($extra_paths,$include_paths);

			// Try to load the model file
			jimport('joomla.filesystem.path');
			if(interface_exists('JModel')) {
				$path = JPath::find(
					$include_paths,
					JModelLegacy::_createFileName( 'model', array( 'name' => $type))
				);
			} else {
				$path = JPath::find(
					$include_paths,
					JModel::_createFileName( 'model', array( 'name' => $type))
				);
			}
			if ($path)
			{
				require_once $path;
			}
		}

		if (!class_exists( $modelClass )) {
			$modelClass = 'FOFModel';
		}

		$result = new $modelClass($config);

		return $result;
	}

	/**
	 * Returns a new instance of a model, with the state reset to defaults
	 *
	 * @param string $type
	 * @param string $prefix
	 * @param array $config
	 * @return FOFModel
	 */
	public static function &getTmpInstance( $type, $prefix = '', $config = array() )
	{
		$ret = self::getAnInstance($type, $prefix, $config)
			->getClone()
			->clearState()
			->clearInput()
			->reset()
			->limitstart(0)
			->limit(0)
			->savestate(0);
		return $ret;
	}

	/**
	 * Public class constructor
	 *
	 * @param type $config
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

		// Get the input
		if(array_key_exists('input', $config)) {
			$this->input = $config['input'];
		} else {
			$this->input = JRequest::get('default', 3);
		}

		// Set the $name/$_name variable
		$component = FOFInput::getCmd('option','com_foobar',$this->input);
		if(array_key_exists('option', $config)) $component = $config['option'];
		FOFInput::setVar('option', $component, $this->input);
		$name = str_replace('com_', '', strtolower($component));
		if(array_key_exists('name', $config)) $name = $config['name'];
		if(version_compare(JVERSION, '1.6.0', 'ge')) {
			$this->name = $name;
		} else {
			$this->_name = $name;
		}
		$this->option = $component;

		// Get the view name
		$className = get_class($this);
		if($className == 'FOFModel') {
			if(array_key_exists('view', $config)) {
				$view = $config['view'];
			}
			if(empty($view)) {
				$view = FOFInput::getCmd('view','cpanel',$this->input);
			}
		} else {
			$eliminatePart = ucfirst($name).'Model';
			$view = strtolower(str_replace($eliminatePart, '', $className));
		}

		// Assign the correct table
		if(array_key_exists('table',$config)) {
			$this->table = $config['table'];
		} else {
			$this->table = FOFInflector::singularize($view);
		}

		// Get and store the pagination request variables
		$this->populateSavesate();
		list($isCLI, $isAdmin) = FOFDispatcher::isCliAdmin();
		if($isCLI) {
			$limit = 20;
			$limitstart = 0;
		} else {
			$app = JFactory::getApplication();
			if(method_exists($app, 'getCfg')) {
				$default_limit = $app->getCfg('list_limit');
			} else {
				$default_limit = 20;
			}

			$limit = $this->getUserStateFromRequest($component.'.'.$view.'.limit', 'limit', $default_limit, 'int', $this->_savestate);
			$limitstart = $this->getUserStateFromRequest($component.'.'.$view.'.limitstart', 'limitstart', 0, 'int', $this->_savestate);
		}
		$this->setState('limit',$limit);
		$this->setState('limitstart',$limitstart);

		// Get the ID or list of IDs from the request or the configuration
		if(array_key_exists('cid', $config)) {
			$cid = $config['cid'];
		} else {
			$cid = FOFInput::getVar('cid', null, $this->input, 'array');
		}
		if(array_key_exists('id', $config)) {
			$id = $config['id'];
		} else {
			$id = FOFInput::getInt('id', 0, $this->input);
		}

		if(is_array($cid) && !empty($cid))
		{
			$this->setIds($cid);
		}
		else
		{
			$this->setId($id);
		}

		// Populate the event names from the $config array
		if(isset($config['event_after_delete'])) {
			$this->event_after_delete = $config['event_after_delete'];
		}
		if(isset($config['event_after_save'])) {
			$this->event_after_save = $config['event_after_save'];
		}
		if(isset($config['event_before_delete'])) {
			$this->event_before_delete = $config['event_before_delete'];
		}
		if(isset($config['event_before_save'])) {
			$this->event_before_save = $config['event_before_save'];
		}
		if(isset($config['event_change_state'])) {
			$this->event_change_state = $config['event_change_state'];
		}
	}

	/**
	 * Sets the list of IDs from the request data
	 */
	public function setIDsFromRequest()
	{
		// Get the ID or list of IDs from the request or the configuration
		$cid = FOFInput::getVar('cid', null, $this->input, 'array');
		$id = FOFInput::getInt('id', 0, $this->input);
		$kid = FOFInput::getInt($this->getTable($this->table)->getKeyName(), 0, $this->input);

		if(is_array($cid) && !empty($cid))
		{
			$this->setIds($cid);
		}
		else
		{
			if(empty($id)) {
				$this->setId($kid);
			} else {
				$this->setId($id);
			}
		}

		return $this;
	}

	/**
	 * Sets the ID and resets internal data
	 * @param int $id The ID to use
	 *
	 * @return FOFModel
	 */
	public function setId($id=0)
	{
		$this->reset();
		$this->id = (int)$id;
		$this->id_list = array($this->id);
		return $this;
	}

	/**
	 * Returns the currently set ID
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * Sets a list of IDs for batch operations from an array and resets the model
	 *
	 * @return FOFModel
	 */
	public function setIds($idlist)
	{
		$this->reset();
		$this->id_list = array();
		$this->id = 0;
		if(is_array($idlist) && !empty($idlist)) {
			foreach($idlist as $value)
			{
				$this->id_list[] = (int)$value;
			}
			$this->id = $this->id_list[0];
		}
		return $this;
	}

	/**
	 * Returns the list of IDs for batch operations
	 * @return array An array of integers
	 */
	public function getIds()
	{
		return $this->id_list;
	}

	/**
	 * Resets the model, like it was freshly loaded
	 *
	 * @return FOFModel
	 */
	public function reset()
	{
		$this->id = 0;
		$this->id_list = null;
		$this->record = null;
		$this->list = null;
		$this->pagination = null;
		$this->total = 0;
		$this->otable = null;

		return $this;
	}

	/**
	 * Clears the model state, but doesn't touch the internal lists of records,
	 * record tables or record id variables. To clear these values, please use
	 * reset().
	 *
	 * @return FOFModel
	 */
	public function clearState()
	{
		if(version_compare(JVERSION, '1.6.0', 'ge')) {
			$this->state = new JObject();
		} else {
			$this->_state = new JObject();
		}

		return $this;
	}

	/**
	 * Clears the input array.
	 *
	 * @return FOFModel
	 */
	public function clearInput()
	{
		$this->input = array();

		return $this;
	}

	/**
	 * Resets the saved state for this view
	 *
	 * @return FOFModel
	 */
	public function resetSavedState()
	{
		JFactory::getApplication()->setUserState(substr($this->getHash(),0,-1), null);

		return $this;
	}

	/**
	 * Returns a single item. It uses the id set with setId, or the first ID in
	 * the list of IDs for batch operations
	 *
	 * @param int|null $id Force a primary key ID to the model
	 *
	 * @return FOFTable A copy of the item's JTable array
	 */
	public function &getItem($id = null)
	{
		if(!is_null($id)) {
			$this->record = null;
			$this->setId($id);
		}

		if(empty($this->record))
		{
			$table = $this->getTable($this->table);
			$table->load($this->id);
			$this->record = $table;

			// Do we have saved data?
			$session = JFactory::getSession();
			$serialized = $session->get($this->getHash().'savedata', null);
			if(!empty($serialized))
			{
				$data = @unserialize($serialized);
				if($data !== false)
				{
					$k = $table->getKeyName();
					if(!array_key_exists($k, $data)) $data[$k] = null;
					if($data[$k] != $this->id) {
						$session->set($this->getHash().'savedata', null);
					} else {
						$this->record->bind($data);
					}
				}
			}

			$this->onAfterGetItem($this->record);
		}

		return $this->record;
	}

	/**
	 * Alias for getItemList
	 * @return array
	 */
	public function &getList($overrideLimits = false, $group = '')
	{
		return $this->getItemList($overrideLimits, $group);
	}

	/**
	 * Returns a list of items
	 * @param bool $overrideLimits When true, the limits (pagination) will be ignored
	 * @return array
	 */
	public function &getItemList($overrideLimits = false, $group = '')
	{
		if(empty($this->list)) {
			$query = $this->buildQuery($overrideLimits);
			if(!$overrideLimits) {
				$limitstart = $this->getState('limitstart');
				$limit = $this->getState('limit');
				$this->list = $this->_getList((string)$query, $limitstart, $limit, $group);
			} else {
				$this->list = $this->_getList((string)$query, 0, 0, $group);
			}
		}

		return $this->list;
	}

	/**
	 * A cross-breed between getItem and getItemList. It runs the complete query,
	 * like getItemList does. However, instead of returning an array of ad-hoc
	 * objects, it binds the data from the first item fetched on the list to an
	 * instance of the table object and returns that table object instead.
	 *
	 * @param bool $overrideLimits
	 * @return FOFTable
	 */
	public function &getFirstItem($overrideLimits = false)
	{
		$table = $this->getTable($this->table);

		$list = $this->getItemList($overrideLimits);
		if(!empty($list)) {
			$firstItem = array_shift($list);
			$table->bind($firstItem);
		}
		unset($list);

		return $table;
	}

	/**
	 * Binds the data to the model and tries to save it
	 * @param array|object $data The source data array or object
	 * @return bool True on success
	 */
	public function save($data)
	{
		$this->otable = null;

		$table = $this->getTable($this->table);

		if(is_object($data)) $data = clone($data);

		$key = $table->getKeyName();
		if(array_key_exists($key, (array)$data))
		{
			$aData = (array)$data;
			$oid = $aData[$key];
			$table->load($oid);
		}

		if(!$this->onBeforeSave($data, $table)) {
			return false;
		}

		if(!$table->save($data)) {
			foreach($table->getErrors() as $error) if(!empty($error)) $this->setError($error);
			JFactory::getSession()->set($this->getHash().'savedata', serialize($table->getProperties(true)) );
			return false;
		} else {
			$this->id = $table->$key;
			// Remove the session data
			JFactory::getSession()->set($this->getHash().'savedata', null);
		}

		$this->onAfterSave($table);

		$this->otable = $table;
		return true;
	}

	/**
	 * Copy one or more records
	 */
	public function copy()
	{
		if(is_array($this->id_list) && !empty($this->id_list)) {

			$table = $this->getTable($this->table);

			if(!$this->onBeforeCopy($table)) return false;

			if(!$table->copy($this->id_list) ) {
				$this->setError($table->getError());
				return false;
			} else {
				// Call our internal event
				$this->onAfterCopy($table);

				//@TODO Should we fire the content plugin?
			}
		}
		return true;
	}

	/**
	 * Returns the table object after the last save() operation
	 * @return JTable
	 */
	public function getSavedTable()
	{
		return $this->otable;
	}

	/**
	 * Deletes one or several items
	 * @return bool
	 */
	public function delete()
	{
		if(is_array($this->id_list) && !empty($this->id_list)) {
			$table = $this->getTable($this->table);
			foreach($this->id_list as $id) {
				if(!$this->onBeforeDelete($id, $table)) continue;

				if(!$table->delete($id)) {
					$this->setError($table->getError());
					return false;
				} else {
					$this->onAfterDelete($id);
				}
			}
		}
		return true;
	}

	/**
	 * Toggles the published state of one or several items
	 * @param int $publish The publishing state to set (e.g. 0 is unpublished)
	 * @param int $user The user ID performing this action
	 * @return bool
	 */
	public function publish($publish = 1, $user = null)
	{
		if(is_array($this->id_list) && !empty($this->id_list)) {
			if(empty($user)) {
				$oUser = JFactory::getUser();
				$user = $oUser->id;
			}
			$table = $this->getTable($this->table);

			if(!$this->onBeforePublish($table)) return false;

			if(!$table->publish($this->id_list, $publish, $user) ) {
				$this->setError($table->getError());
				return false;
			} else {
				// Call our itnernal event
				$this->onAfterPublish($table);

				// Call the plugin events
				$dispatcher = JDispatcher::getInstance();
				JPluginHelper::importPlugin('content');
				$name = FOFInput::getCmd('view','cpanel',$this->input);
				$context = $this->option.'.'.$name;
				$result = $dispatcher->trigger($this->event_change_state, array($context, $this->id_list, $publish));
			}
		}
		return true;
	}

	/**
	 * Checks out the current item
	 * @return bool
	 */
	public function checkout()
	{
		$table = $this->getTable($this->table);
		$status = $table->checkout(JFactory::getUser()->id, $this->id);
		if(!$status) $this->setError($table->getError());
		return $status;
	}

	/**
	 * Checks in the current item
	 * @return bool
	 */
	public function checkin(){
		$table = $this->getTable($this->table);
		$status = $table->checkin($this->id);
		if(!$status) $this->setError($table->getError());
		return $status;
	}

	/**
	 * Tells you if the current item is checked out or not
	 * @return bool
	 */
	public function isCheckedOut() {
		$table = $this->getTable($this->table);
		$status = $table->isCheckedOut($this->id);
		if(!$status) $this->setError($table->getError());
		return $status;
	}

	/**
	 * Increments the hit counter
	 * @return bool
	 */
	public function hit() {
		$table = $this->getTable($this->table);
		if(!$this->onBeforeHit($table)) return false;
		$status = $table->hit($this->id);
		if(!$status) {
			$this->setError($table->getError());
		} else {
			$this->onAfterHit($table);
		}
		return $status;
	}

	/**
	 * Moves the current item up or down in the ordering list
	 * @param <type> $dirn
	 * @return bool
	 */
	public function move( $dirn ) {
		$table = $this->getTable($this->table);

		$id = $this->getId();
		$status = $table->load($id);
		if(!$status) $this->setError($table->getError());
		if(!$status) return false;

		if(!$this->onBeforeMove($table)) return false;

		$status = $table->move($dirn);
		if(!$status) {
			$this->setError($table->getError());
		} else {
			$this->onAfterMove($table);
		}

		return $status;
	}

	/**
	 * Reorders all items in the table
	 * @return bool
	 */
	public function reorder()
	{
		$table = $this->getTable($this->table);
		if(!$this->onBeforeReorder($table)) return false;
		$status = $table->reorder( $this->getReorderWhere() );
		if(!$status) {
			$this->setError($table->getError());
		} else {
			if(!$this->onAfterReorder($table)) return false;
		}
		return $status;
	}

	/**
	 * Get a pagination object
	 *
	 * @access public
	 * @return JPagination
	 *
	 */
	public function getPagination()
	{
		if( empty($this->pagination) )
		{
			// Import the pagination library
			jimport('joomla.html.pagination');

			// Prepare pagination values
			$total = $this->getTotal();
			$limitstart = $this->getState('limitstart');
			$limit = $this->getState('limit');

			// Create the pagination object
			$this->pagination = new JPagination($total, $limitstart, $limit);
		}

		return $this->pagination;
	}

	/**
	 * Get the number of all items
	 *
	 * @access public
	 * @return integer
	 */
	public function getTotal()
	{
		if( empty($this->total) )
		{
			$query = $this->buildCountQuery();
			if($query === false) {
				$sql = (string)$this->buildQuery(false);
				$query = FOFQueryAbstract::getNew($this->_db)
						->select('COUNT(*)')
						->from("($sql) AS a");
			}

			$this->_db->setQuery( (string)$query );
			$this->_db->query();

			$this->total = $this->_db->loadResult();
		}

		return $this->total;
	}

	/**
	 * Get a filtered state variable
	 * @param string $key
	 * @param mixed $default
	 * @param string $filter_type
	 * @return mixed
	 */
	public function getState($key = null, $default = null, $filter_type = 'raw')
	{
		if(empty($key)) {
			return parent::getState();
		}

		// Get the savestate status
		$value = parent::getState($key);
		if(is_null($value))
		{
			$value = $this->getUserStateFromRequest($this->getHash().$key,$key,$value,'none',$this->_savestate);
			if(is_null($value))	{
				return $default;
			}
		}

		if( strtoupper($filter_type) == 'RAW' )
		{
			return $value;
		}
		else
		{
			jimport('joomla.filter.filterinput');
			$filter = new JFilterInput();
			return $filter->clean($value, $filter_type);
		}
	}

	public function getHash()
	{
		$option = FOFInput::getCmd('option', 'com_foobar', $this->input);
		$view = FOFInflector::pluralize(FOFInput::getCmd('view','cpanel',$this->input));
		return "$option.$view.";
	}

	/**
	 * Gets the value of a user state variable.
	 *
	 * @access	public
	 * @param	string	The key of the user state variable.
	 * @param	string	The name of the variable passed in a request.
	 * @param	string	The default value for the variable if not found. Optional.
	 * @param	string	Filter for the variable, for valid values see {@link JFilterInput::clean()}. Optional.
	 * @param	bool	Should I save the variable in the user state? Default: true. Optional.
	 * @return	The request user state.
	 */
	protected function getUserStateFromRequest( $key, $request, $default = null, $type = 'none', $setUserState = true )
	{
		list($isCLI, $isAdmin) = FOFDispatcher::isCliAdmin();
		if($isCLI) return $default;

		$app = JFactory::getApplication();
		if(method_exists($app, 'getUserState')) {
			$old_state = $app->getUserState( $key );
		} else {
			$old_state = null;
		}
		$cur_state = (!is_null($old_state)) ? $old_state : $default;
		$new_state = FOFInput::getVar($request, null, $this->input, $type);

		// Save the new value only if it was set in this request
		if($setUserState) {
			if ($new_state !== null) {
				$app->setUserState($key, $new_state);
			} else {
				$new_state = $cur_state;
			}
		} elseif(is_null($new_state)) {
			$new_state = $cur_state;
		}

		return $new_state;
	}

	/**
	 * Returns an object list
	 *
	 * @param	string The query
	 * @param	int Offset
	 * @param	int The number of records
	 * @return	array
	 * @access	protected
	 * @since	1.5
	 */
	function &_getList( $query, $limitstart=0, $limit=0, $group = '' )
	{
		$this->_db->setQuery( $query, $limitstart, $limit );
		$result = $this->_db->loadObjectList($group);

		$this->onProcessList($result);

		return $result;
	}

	/**
	 * Method to get a table object, load it if necessary.
	 *
	 * @param   string   $name     The table name. Optional.
	 * @param   string   $prefix   The class prefix. Optional.
	 * @param   array    $options  Configuration array for model. Optional.
	 *
	 * @return  FOFTable  A FOFTable object
	 * @since   11.1
	 */
	public function getTable($name = '', $prefix = null, $options = array())
	{
		if (empty($name)) {
			$name = $this->table;
			if(empty($name)) {
				$name = FOFInflector::singularize($this->getName());
			}
		}

		if(empty($prefix)) {
			$prefix = ucfirst($this->getName()).'Table';
		}

		if(empty($options)) {
			$options = array('input'=>$this->input);
		}

		if ($table = $this->_createTable($name, $prefix, $options)) {
			return $table;
		}

		JError::raiseError(0, JText::sprintf('JLIB_APPLICATION_ERROR_TABLE_NAME_NOT_SUPPORTED', $name));

		return null;
	}

	/**
	 * Method to load and return a model object.
	 *
	 * @access	private
	 * @param	string	The name of the view
	 * @param   string  The class prefix. Optional.
	 * @return	mixed	Model object or boolean false if failed
	 * @since	1.5
	 */
	function &_createTable( $name, $prefix = 'Table', $config = array())
	{
		$result = null;

		// Clean the model name
		$name	= preg_replace( '/[^A-Z0-9_]/i', '', $name );
		$prefix = preg_replace( '/[^A-Z0-9_]/i', '', $prefix );

		//Make sure we are returning a DBO object
		if (!array_key_exists('dbo', $config))  {
			$config['dbo'] = $this->getDBO();;
		}

		$instance = FOFTable::getAnInstance($name, $prefix, $config );
		return $instance;
	}

	/**
	 * Creates the WHERE part of the reorder query
	 * @return type
	 */
	public function getReorderWhere()
	{
		return '';
	}

	/**
	 * Builds the SELECT query
	 */
	public function buildQuery($overrideLimits = false)
	{
		$table = $this->getTable();
		$tableName = $table->getTableName();
		$tableKey = $table->getKeyName();
		$db = $this->getDBO();

		if(version_compare(JVERSION, '3.0', 'ge')) {
			$query = FOFQueryAbstract::getNew()
				->select('*')
				->from($db->qn($tableName));
		} elseif(version_compare(JVERSION, '1.6', 'ge')) {
			$query = FOFQueryAbstract::getNew()
				->select('*')
				->from($db->quoteName($tableName));
		}else {
			$query = FOFQueryAbstract::getNew()
				->select('*')
				->from($db->nameQuote($tableName));
		}

		$where = array();
		if(version_compare(JVERSION, '3.0', 'ge')) {
			$fields = $db->getTableColumns($tableName, true);
		} else {
			$fieldsArray = $db->getTableFields($tableName, true);
			$fields = array_shift($fieldsArray);
		}
		foreach($fields as $fieldname => $fieldtype) {
			$filterName = ($fieldname == $tableKey) ? 'id' : $fieldname;
			$filterState = $this->getState($filterName, null);
			if(!empty($filterState) || ($filterState === '0')) {
				switch($fieldname) {
					case $table->getColumnAlias('title'):
					case $table->getColumnAlias('description'):
						if(version_compare(JVERSION, '3.0', 'ge')) {
							$query->where('('.$db->qn($fieldname).' LIKE '.$db->q('%'.$filterState.'%').')');
						} elseif(version_compare(JVERSION, '1.6', 'ge')) {
							$query->where('('.$db->quoteName($fieldname).' LIKE '.$db->Quote('%'.$filterState.'%').')');
						} else {
							$query->where('('.$db->nameQuote($fieldname).' LIKE '.$db->Quote('%'.$filterState.'%').')');
						}

						break;

					case $table->getColumnAlias('enabled'):
						if($filterState !== '') {
							if(version_compare(JVERSION, '3.0', 'ge')) {
								$query->where($db->qn($fieldname).' = '.$db->q((int)$filterState));
							} elseif(version_compare(JVERSION, '1.6', 'ge')) {
								$query->where($db->quoteName($fieldname).' = '.$db->Quote((int)$filterState));
							} else {
								$query->where($db->nameQuote($fieldname).' = '.$db->Quote((int)$filterState));
							}
						}
						break;

					default:
						if(version_compare(JVERSION, '3.0', 'ge')) {
							$query->where('('.$db->qn($fieldname).'='.$db->q($filterState).')');
						} elseif(version_compare(JVERSION, '1.6', 'ge')) {
							$query->where('('.$db->quoteName($fieldname).'='.$db->Quote($filterState).')');
						} else {
							$query->where('('.$db->nameQuote($fieldname).'='.$db->Quote($filterState).')');
						}
						break;
				}
			}
		}

		if(!$overrideLimits) {
			$order = $this->getState('filter_order',null,'cmd');
			if(!in_array($order, array_keys($this->getTable()->getData()))) $order = $tableKey;
			$dir = $this->getState('filter_order_Dir', 'ASC', 'cmd');
			if(version_compare(JVERSION, '3.0', 'ge')) {
				$query->order($db->qn($order).' '.$dir);
			} elseif(version_compare(JVERSION, '1.6', 'ge')) {
				$query->order($db->quoteName($order).' '.$dir);
			} else {
				$query->order($db->nameQuote($order).' '.$dir);
			}
		}

		return $query;
	}

	/**
	 * Builds the count query used in getTotal()
	 * @return type
	 */
	public function buildCountQuery()
	{
		return false;
	}

	/**
	 * Clones the model object and returns the clone
	 * @return FOFModel
	 */
	public function &getClone()
	{
		$clone = clone($this);
		return $clone;
	}

	/**
	 * Magic getter; allows to use the name of model state keys as properties
	 * @param string $name
	 * @return mixed
	 */
	public function __get($name) {
		return $this->getState($name);
	}

	/**
	 * Magic setter; allows to use the name of model state keys as properties
	 * @param string $name
	 * @return mixed
	 */
	public function __set($name, $value) {
		return $this->setState($name, $value);
	}

	/**
	 * Magic caller; allows to use the name of model state keys as methods to
	 * set their values.
	 *
	 * @param string $name
	 * @param mixed $arguments
	 * @return FOFModel
	 */
	public function __call($name, $arguments) {
		$arg1 = array_shift($arguments);
		$this->setState($name, $arg1);
		return $this;
	}

	/**
	 * Sets the model state auto-save status. By default the model is set up to
	 * save its state to the session.
	 *
	 * @param bool $newState True to save the state, false to not save it.
	 */
	public function &savestate($newState)
	{
		$this->_savestate = $newState ? true : false;

		return $this;
	}

	public function populateSavesate()
	{
		if(is_null($this->_savestate)) {
			$savestate = FOFInput::getInt('savestate', -999, $this->input);
			if($savestate == -999) {
				$savestate = true;
			}
			$this->savestate($savestate);
		}
	}

	/**
	 * This method can be overriden to automatically do something with the
	 * list results array. You are supposed to modify the list which was passed
	 * in the parameters; DO NOT return a new array!
	 *
	 * @param array $resultArray An array of objects, each row representing a record
	 */
	protected function onProcessList(&$resultArray)
	{
	}

	/**
	 * This method runs after an item has been gotten from the database in a read
	 * operation. You can modify it before it's returned to the MVC triad for
	 * further processing.
	 *
	 * @param JTable $record
	 */
	protected function onAfterGetItem(&$record)
	{
	}

	/**
	 * This method runs before the $data is saved to the $table. Return false to
	 * stop saving.
	 *
	 * @param array $data
	 * @param JTable $table
	 * @return bool
	 */
	protected function onBeforeSave(&$data, &$table)
	{
		list($isCLI, $isAdmin) = FOFDispatcher::isCliAdmin();

		// Let's import the plugin only if we're not in CLI (content plugin needs a user)
		if(!$isCLI){
			JPluginHelper::importPlugin('content');
		}

		$dispatcher = JDispatcher::getInstance();

		try {
			// Do I have a new record?
			$key = $table->getKeyName();
			if($data instanceof FOFTable) {
				$allData = $data->getData();
			} elseif(is_object($data)) {
				$allData = (array)$data;
			} else {
				$allData = $data;
			}
			$pk = (!empty($allData[$key])) ? $allData[$key] : 0;

			$this->_isNewRecord = $pk <= 0;

			// Bind the data
			$table->bind($allData);
			// Call the plugin
			$name = version_compare(JVERSION, '1.6.0', 'ge') ? $this->name : $this->_name;
			$result = $dispatcher->trigger($this->event_before_save, array($this->option.'.'.$name, &$table, $this->_isNewRecord));
			if (in_array(false, $result, true)) {
				// Plugin failed, return false
				$this->setError($table->getError());
				return false;
			} else {
				// Plugin successful, refetch the possibly modified data
				if($data instanceof FOFTable) {
					$data->bind($allData);
				} elseif(is_object($data)) {
					$data = (object)$allData;
				} else {
					$data = $allData;
				}
			}
		} catch(Exception $e) {
			// Oops, an exception occured!
			$this->setError($e->getMessage());
			return false;
		}
		return true;
	}

	/**
	 * This method runs after the data is saved to the $table.
	 *
	 * @param JTable $table
	 */
	protected function onAfterSave(&$table)
	{
		list($isCLI, $isAdmin) = FOFDispatcher::isCliAdmin();

		// Let's import the plugin only if we're not in CLI (content plugin needs a user)
		if(!$isCLI){
			JPluginHelper::importPlugin('content');
		}

		$dispatcher = JDispatcher::getInstance();
		try {
			$name = version_compare(JVERSION, '1.6.0', 'ge') ? $this->name : $this->_name;
			$dispatcher->trigger($this->event_after_save, array($this->option.'.'.$name, &$table, $this->_isNewRecord));
		} catch(Exception $e) {
			// Oops, an exception occured!
			$this->setError($e->getMessage());
			return false;
		}
	}

	/**
	 * This method runs before the record with key value of $id is deleted from $table
	 *
	 * @param JTable $table
	 */
	protected function onBeforeDelete(&$id, &$table)
	{
		list($isCLI, $isAdmin) = FOFDispatcher::isCliAdmin();

		// Let's import the plugin only if we're not in CLI (content plugin needs a user)
		if(!$isCLI){
			JPluginHelper::importPlugin('content');
		}

		$dispatcher = JDispatcher::getInstance();
		try {
			$table->load($id);

			$name = FOFInput::getCmd('view','cpanel',$this->input);
			$context = $this->option.'.'.$name;
			$result = $dispatcher->trigger($this->event_before_delete, array($context, $table));

			if (in_array(false, $result, true)) {
				// Plugin failed, return false
				$this->setError($table->getError());
				return false;
			}

			$this->_recordForDeletion = clone $table;
		} catch(Exception $e) {
			// Oops, an exception occured!
			$this->setError($e->getMessage());
			return false;
		}
		return true;
	}

	protected function onAfterDelete($id)
	{
		list($isCLI, $isAdmin) = FOFDispatcher::isCliAdmin();

		// Let's import the plugin only if we're not in CLI (content plugin needs a user)
		if(!$isCLI){
			JPluginHelper::importPlugin('content');
		}

		$dispatcher = JDispatcher::getInstance();
		try {
			$name = FOFInput::getCmd('view','cpanel',$this->input);
			$context = $this->option.'.'.$name;
			$result = $dispatcher->trigger($this->event_after_delete, array($context, $this->_recordForDeletion));
			unset($this->_recordForDeletion);
		} catch(Exception $e) {
			// Oops, an exception occured!
			$this->setError($e->getMessage());
			return false;
		}
	}

	protected function onBeforeCopy(&$table)
	{
		return true;
	}

	protected function onAfterCopy(&$table)
	{
		return true;
	}

	protected function onBeforePublish(&$table)
	{
		return true;
	}

	protected function onAfterPublish(&$table)
	{
		return true;
	}

	protected function onBeforeHit(&$table)
	{
		return true;
	}

	protected function onAfterHit(&$table)
	{
	}

	protected function onBeforeMove(&$table)
	{
		return true;
	}

	protected function onAfterMove(&$table)
	{
	}

	protected function onBeforeReorder(&$table)
	{
		return true;
	}

	protected function onAfterReorder(&$table)
	{
	}

}