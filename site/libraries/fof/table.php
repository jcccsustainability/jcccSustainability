<?php
/**
 *  @package FrameworkOnFramework
 *  @copyright Copyright (c)2010-2013 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

// Normally this shouldn't be required. Some PHP versions, however, seem to
// require this. Why? No idea whatsoever. If I remove it, FOF crashes on some
// hosts. Same PHP version on another host and no problem occurs. Any takers?
if(class_exists('FOFTable', false)) {
	return;
}

jimport('joomla.database.table');

require_once(dirname(__FILE__).'/input.php');

/**
 * FrameworkOnFramework table class
 *
 * FrameworkOnFramework is a set of classes which extend Joomla! 1.5 and later's
 * MVC framework with features making maintaining complex software much easier,
 * without tedious repetitive copying of the same code over and over again.
 */
abstract class FOFTable_COMMONBASE extends JTable
{
	/**
	 * If this is set to true, it triggers automatically plugin events for
	 * table actions
	 */
	protected $_trigger_events = false;

	/**
	 * Array with alias for "special" columns such as ordering, hits etc etc
	 *
	 * @var    array
	 */
	protected $_columnAlias = array();

	/**
	 * If set to true, it enabled automatic checks on fields based on columns properties
	 *
	 * @var boolean
	 */
	protected $_autoChecks = false;

	/**
	 * Array with fields that should be skipped by automatic checks
	 *
	 * @var array
	 */
	protected $_skipChecks = array();

	/**
	 * Returns a static object instance of a particular table type
	 *
	 * @param string $type The table name
	 * @param string $prefix The prefix of the table class
	 * @param array $config Optional configuration variables
	 * @return FOFTable
	 */
	public static function &getAnInstance($type = null, $prefix = 'JTable', $config = array())
	{
		static $instances = array();

		// Guess the component name
		if(array_key_exists('input', $config)) {
			$option = FOFInput::getCmd('option','',$config['input']);
			FOFInput::setVar('option',$option,$config['input']);
		}

		if(!in_array($prefix,array('Table','JTable'))) {
			preg_match('/(.*)Table$/', $prefix, $m);
			$option = 'com_'.strtolower($m[1]);
		}

		if(array_key_exists('option', $config)) $option = $config['option'];
		$config['option'] = $option;

		if(!array_key_exists('view', $config)) $config['view'] = JRequest::getCmd('view','cpanel');
		if(is_null($type)) {
			if($prefix == 'JTable') $prefix = 'Table';
			$type = $config['view'];
		}

		$type = preg_replace('/[^A-Z0-9_\.-]/i', '', $type);
		$tableClass = $prefix.ucfirst($type);

		if(!array_key_exists($tableClass, $instances)) {
			if (!class_exists( $tableClass )) {
				list($isCLI, $isAdmin) = FOFDispatcher::isCliAdmin();
				if(!$isAdmin) {
					$basePath = JPATH_SITE;
				} else {
					$basePath = JPATH_ADMINISTRATOR;
				}

				$searchPaths = array(
					$basePath.'/components/'.$config['option'].'/tables',
					JPATH_ADMINISTRATOR.'/components/'.$config['option'].'/tables'
				);
				if(array_key_exists('tablepath', $config)) {
					array_unshift($searchPaths, $config['tablepath']);
				}

				jimport('joomla.filesystem.path');
				$path = JPath::find(
					$searchPaths,
					strtolower($type).'.php'
				);

				if ($path) {
					require_once $path;
				}
			}

			if (!class_exists( $tableClass )) {
				$tableClass = 'FOFTable';
			}

			$tbl_common = str_replace('com_', '', $config['option']).'_';
			if(!array_key_exists('tbl', $config)) {
				$config['tbl'] = strtolower('#__'.$tbl_common.strtolower(FOFInflector::pluralize($type)));
			}
			if(!array_key_exists('tbl_key', $config)) {
				$keyName = FOFInflector::singularize($type);
				$config['tbl_key'] = strtolower($tbl_common.$keyName.'_id');
			}
			if(!array_key_exists('db', $config)) {
				$config['db'] = JFactory::getDBO();
			}

			$instance = new $tableClass($config['tbl'],$config['tbl_key'],$config['db']);

			if(array_key_exists('trigger_events', $config)) {
				$instance->setTriggerEvents($config['trigger_events']);
			}

			$instances[$tableClass] = $instance;
		}

		return $instances[$tableClass];
	}

	function __construct( $table, $key, &$db )
	{
		$this->_tbl		= $table;
		$this->_tbl_key	= $key;
		$this->_db		= $db;

		// Initialise the table properties.
		if ($fields = $this->getTableFields()) {
			foreach ($fields as $name => $v)
			{
				// Add the field if it is not already present.
				if (!isset($this->$name)) {
					$this->$name = null;
				}
			}
		}

		if(version_compare(JVERSION, '1.6.0', 'ge')) {
			// If we are tracking assets, make sure an access field exists and initially set the default.
			if (isset($this->asset_id) || property_exists($this, 'asset_id')) {
				jimport('joomla.access.rules');
				$this->_trackAssets = true;
			}

			// If the acess property exists, set the default.
			if (isset($this->access) ||property_exists($this, 'access')) {
				$this->access = (int) JFactory::getConfig()->get('access');
			}
		}
	}

	/**
	 * Sets the events trigger switch state
	 *
	 * @param bool $newState
	 */
	public function setTriggerEvents($newState = false)
	{
		$this->_trigger_events = $newState ? true : false;
	}

	/**
	 * Gets the events trigger switch state
	 *
	 * @return bool
	 */
	public function getTriggerEvents()
	{
		return $this->_trigger_events;
	}

	/**
	 * Sets fields to be skipped from automatic checks.
	 *
	 * @param array/string	$skip	Fields to be skipped by automatic checks
	 */
	function setSkipChecks($skip)
	{
		$this->_skipChecks = (array) $skip;
	}

	/**
	 * Based on fields properties (nullable column), checks if the field is required or not
	 *
	 * @return boolean
	 */
	function check()
	{
		if(!$this->_autoChecks)	return parent::check();

		$fields = $this->getTableFields();
		$result = true;

		foreach($fields as $field)
		{
			//primary key, better skip that
			if($field->Field == $this->_tbl_key) continue;

			$fieldName = $field->Field;

			//field is not nullable but it's null, set error
			if($field->Null == 'NO' && $this->$fieldName == '' && !in_array($fieldName, $this->_skipChecks))
			{
				$text = str_replace('#__', 'COM_', $this->getTableName()).'_ERR_'.$fieldName;
				$this->setError(JText::_(strtoupper($text)));
				$result = false;
			}
		}

		return $result;
	}

	/**
	 * Method to reset class properties to the defaults set in the class
	 * definition. It will ignore the primary key as well as any private class
	 * properties.
	 */
	public function reset()
	{
		if(!$this->onBeforeReset()) return false;
		// Get the default values for the class from the table.
		$fields = $this->getTableFields();
		foreach ($fields as $k => $v)
		{
			// If the property is not the primary key or private, reset it.
			if ($k != $this->_tbl_key && (strpos($k, '_') !== 0)) {
				$this->$k = $v->Default;
			}
		}
		if(!$this->onAfterReset()) return false;
	}

	/**
	 * Generic check for whether dependancies exist for this object in the db schema
	 */
	public function canDelete( $oid=null, $joins=null )
	{
		$k = $this->_tbl_key;
		if ($oid) {
			$this->$k = intval( $oid );
		}

		if (is_array( $joins ))
		{
			$db = $this->_db;
			if(version_compare(JVERSION, '3.0', 'ge')) {
				$query = FOFQueryAbstract::getNew($this->_db)
					->select($db->qn('master').'.'.$db->qn($k))
					->from($db->qn($this->_tbl).' AS '.$db->qn('master'));
			} elseif(version_compare(JVERSION, '1.6.0', 'ge')){
				$query = FOFQueryAbstract::getNew($this->_db)
					->select($db->quoteName('master').'.'.$db->quoteName($k))
					->from($db->quoteName($this->_tbl).' AS '.$db->quoteName('master'));
			} else {
				$query = FOFQueryAbstract::getNew($this->_db)
					->select($db->nameQuote('master').'.'.$db->nameQuote($k))
					->from($db->nameQuote($this->_tbl).' AS '.$db->nameQuote('master'));
			}
			$tableNo = 0;
			foreach( $joins as $table )
			{
				$tableNo++;
				if(version_compare(JVERSION, '3.0', 'ge')) {
					$query->select(array(
						'COUNT(DISTINCT '.$db->qn('t'.$tableNo).'.'.$db->qn($table['idfield']).') AS '.$db->qn($table['idalias'])
					));
					$query->join('LEFT',
							$db->qn($table['name']).
							' AS '.$db->qn('t'.$tableNo).
							' ON '.$db->qn('t'.$tableNo).'.'.$db->qn($table['joinfield']).
							' = '.$db->qn('master').'.'.$db->qn($k)
							);
				} elseif(version_compare(JVERSION, '1.6.0', 'ge')){
					$query->select(array(
						'COUNT(DISTINCT '.$db->quoteName('t'.$tableNo).'.'.$db->quoteName($table['idfield']).') AS '.$db->quoteName($table['idalias'])
					));
					$query->join('LEFT',
							$db->quoteName($table['name']).
							' AS '.$db->quoteName('t'.$tableNo).
							' ON '.$db->quoteName('t'.$tableNo).'.'.$db->quoteName($table['joinfield']).
							' = '.$db->quoteName('master').'.'.$db->quoteName($k)
							);
				} else {
					$query->select(array(
						'COUNT(DISTINCT '.$db->nameQuote('t'.$tableNo).'.'.$db->nameQuote($table['idfield']).') AS '.$db->nameQuote($table['idalias'])
					));
					$query->join('LEFT',
							$db->nameQuote($table['name']).
							' AS '.$db->nameQuote('t'.$tableNo).
							' ON '.$db->nameQuote('t'.$tableNo).'.'.$db->nameQuote($table['joinfield']).
							' = '.$db->nameQuote('master').'.'.$db->nameQuote($k)
							);
				}

			}

			if(version_compare(JVERSION, '3.0', 'ge')) {
				$query->where($db->qn('master').'.'.$db->qn($k).' = '.$db->q($this->$k));
				$query->group($db->qn('master').'.'.$db->qn($k));
			} elseif(version_compare(JVERSION, '1.6.0', 'ge')){
				$query->where($db->quoteName('master').'.'.$db->quoteName($k).' = '.$db->quote($this->$k));
				$query->group($db->quoteName('master').'.'.$db->quoteName($k));
			} else {
				$query->where($db->nameQuote('master').'.'.$db->nameQuote($k).' = '.$db->quote($this->$k));
				$query->group($db->nameQuote('master').'.'.$db->nameQuote($k));
			}
			$this->_db->setQuery( (string)$query );

			if(version_compare(JVERSION, '3.0', 'ge')) {
				try {
					$obj = $this->_db->loadObject();
				} catch(JDatabaseException $e) {
					$this->setError($e->getMessage());
				}
			} else {
				if (!$obj = $this->_db->loadObject())
				{
					$this->setError($this->_db->getErrorMsg());
					return false;
				}
			}
			$msg = array();
			$i = 0;
			foreach( $joins as $table )
			{
				$k = $table['idalias'];
				if ($obj->$k > 0)
				{
					$msg[] = JText::_( $table['label'] );
				}
				$i++;
			}

			if (count( $msg ))
			{
				$option = FOFInput::getCmd('option','com_foobar',$this->input);
				$comName = str_replace('com_','',$option);
				$tview = str_replace('#__'.$comName.'_', '', $this->_tbl);
				$prefix = $option.'_'.$tview.'_NODELETE_';

				foreach($msg as $key) {
					$this->setError(JText::_($prefix.$key));
				}
				return false;
			}
			else
			{
				return true;
			}
		}

		return true;
	}

	public function bind( $from, $ignore=array() )
	{
		if(!$this->onBeforeBind($from)) return false;
		return parent::bind($from, $ignore);
	}

	public function store( $updateNulls=false )
	{
		if(!$this->onBeforeStore($updateNulls)) return false;
		$result = parent::store($updateNulls);
		if($result) {
			$result = $this->onAfterStore();
		}
		return $result;
	}

	public function move( $dirn, $where='' )
	{
		if(!$this->onBeforeMove($dirn, $where)) return false;
		$result = parent::move($dirn, $where);
		if($result) {
			$result = $this->onAfterMove();
		}
		return $result;
	}

	public function reorder( $where='' )
	{
		if(!$this->onBeforeReorder($where)) return false;
		$result = parent::reorder($where);
		if($result) {
			$result = $this->onAfterReorder();
		}
		return $result;
	}

	public function checkout( $who, $oid = null )
	{
		$fldLockedBy = $this->getColumnAlias('locked_by');
		$fldLockedOn = $this->getColumnAlias('locked_on');

		if (!(
			in_array( $fldLockedBy, array_keys($this->getProperties()) ) ||
	 		in_array( $fldLockedOn, array_keys($this->getProperties()) )
		)) {
			return true;
		}

		$k = $this->_tbl_key;
		if ($oid !== null) {
			$this->$k = $oid;
		}

		$date = JFactory::getDate();
		if(version_compare(JVERSION, '3.0', 'ge')) {
			$time = $date->toSql();
		} else {
			$time = $date->toMysql();
		}

		if(version_compare(JVERSION, '3.0', 'ge')) {
			$query = FOFQueryAbstract::getNew($this->_db)
					->update($this->_db->qn( $this->_tbl ))
					->set(array(
						$this->_db->qn($fldLockedBy).' = '.(int)$who,
						$this->_db->qn($fldLockedOn).' = '.$this->_db->q($time)
					))
					->where($this->_db->qn($this->_tbl_key).' = '. $this->_db->q($this->$k));
		} else {
			$query = FOFQueryAbstract::getNew($this->_db)
					->update($this->_db->quoteName( $this->_tbl ))
					->set(array(
						$this->_db->quoteName($fldLockedBy).' = '.(int)$who,
						$this->_db->quoteName($fldLockedOn).' = '.$this->_db->quote($time)
					))
					->where($this->_db->quoteName($this->_tbl_key).' = '. $this->_db->quote($this->$k));
		}
		$this->_db->setQuery( (string)$query );

		$this->$fldLockedBy = $who;
		$this->$fldLockedOn = $time;

		return $this->_db->query();
	}

	function checkin( $oid=null )
	{
		$fldLockedBy = $this->getColumnAlias('locked_by');
		$fldLockedOn = $this->getColumnAlias('locked_on');

		if (!(
			in_array( $fldLockedBy, array_keys($this->getProperties()) ) ||
	 		in_array( $fldLockedOn, array_keys($this->getProperties()) )
		)) {
			return true;
		}

		$k = $this->_tbl_key;

		if ($oid !== null) {
			$this->$k = $oid;
		}

		if ($this->$k == NULL) {
			return false;
		}

		if(version_compare(JVERSION, '3.0', 'ge')) {
			$query = FOFQueryAbstract::getNew($this->_db)
					->update($this->_db->qn( $this->_tbl ))
					->set(array(
						$this->_db->qn($fldLockedBy).' = 0',
						$this->_db->qn($fldLockedOn).' = '.$this->_db->q($this->_db->getNullDate())
					))
					->where($this->_db->qn($this->_tbl_key).' = '. $this->_db->q($this->$k));
		} else {
			$query = FOFQueryAbstract::getNew($this->_db)
					->update($this->_db->quoteName( $this->_tbl ))
					->set(array(
						$this->_db->quoteName($fldLockedBy).' = 0',
						$this->_db->quoteName($fldLockedOn).' = '.$this->_db->quote($this->_db->getNullDate())
					))
					->where($this->_db->quoteName($this->_tbl_key).' = '. $this->_db->quote($this->$k));
		}
		$this->_db->setQuery( (string)$query );

		$this->$fldLockedBy = 0;
		$this->$fldLockedOn = '';

		return $this->_db->query();
	}

	function isCheckedOut( $with = 0, $against = null)
	{
		$fldLockedBy = $this->getColumnAlias('locked_by');

		if(isset($this) && is_a($this, 'JTable') && is_null($against)) {
			$against = $this->get( $fldLockedBy );
		}

		//item is not checked out, or being checked out by the same user
		if (!$against || $against == $with) {
			return  false;
		}

		$session = JTable::getInstance('session');
		return $session->exists($against);
	}

	public function copy($cid = null)
	{
		JArrayHelper::toInteger( $cid );
		$user_id	= (int) $user_id;
		$k			= $this->_tbl_key;

		if(count($cid) < 1)
		{
			if($this->$k) {
				$cid = array($this->$k);
			} else {
				$this->setError("No items selected.");
				return false;
			}
		}

		$created_by		= $this->getColumnAlias('created_by');
		$created_on		= $this->getColumnAlias('created_on');
		$modified_by	= $this->getColumnAlias('modified_by');
		$modified_on	= $this->getColumnAlias('modified_on');

		$locked_byName	= $this->getColumnAlias('locked_by');
		$checkin 		= in_array( $locked_byName, array_keys($this->getProperties()) );

		foreach ($cid as $item)
		{
			// Prevent load with id = 0
			if(!$item) continue;

			$this->load($item);

			if ($checkin){
				// We're using the checkin and the record is used by someone else
				if(!$this->isCheckedOut($item)) continue;
			}

			if(!$this->onBeforeCopy($item)) continue;

			$this->$k 			= null;
			$this->$created_by 	= null;
			$this->$created_on 	= null;
			$this->$modified_on	= null;
			$this->$modified_by = null;

			// Let's fire the event only if everything is ok
			if($this->store()){
				$this->onAfterCopy($item);
			}

			$this->reset();
		}

		return true;
	}

	function publish( $cid=null, $publish=1, $user_id=0 )
	{
		JArrayHelper::toInteger( $cid );
		$user_id	= (int) $user_id;
		$publish	= (int) $publish;
		$k			= $this->_tbl_key;

		if (count( $cid ) < 1)
		{
			if ($this->$k) {
				$cid = array( $this->$k );
			} else {
				$this->setError("No items selected.");
				return false;
			}
		}

		if(!$this->onBeforePublish($cid, $publish)) return false;

		$enabledName	= $this->getColumnAlias('enabled');
		$locked_byName	= $this->getColumnAlias('locked_by');

		if(version_compare(JVERSION, '3.0', 'ge')) {
			$query = FOFQueryAbstract::getNew($this->_db)
					->update($this->_db->qn($this->_tbl))
					->set($this->_db->qn($enabledName).' = '.(int) $publish);
		} elseif(version_compare(JVERSION, '1.6.0', 'ge')) {
			$query = FOFQueryAbstract::getNew($this->_db)
					->update($this->_db->quoteName($this->_tbl))
					->set($this->_db->quoteName($enabledName).' = '.(int) $publish);
		} else {
			$query = FOFQueryAbstract::getNew($this->_db)
					->update($this->_db->nameQuote($this->_tbl))
					->set($this->_db->nameQuote($enabledName).' = '.(int) $publish);
		}

		$checkin = in_array( $locked_byName, array_keys($this->getProperties()) );
		if ($checkin)
		{
			if(version_compare(JVERSION, '3.0', 'ge')) {
				$query->where(
					' ('.$this->_db->qn($locked_byName).
					' = 0 OR '.$this->_db->qn($locked_byName).' = '.(int) $user_id.')',
					'AND'
				);
			} elseif(version_compare(JVERSION, '1.6.0', 'ge')) {
				$query->where(
					' ('.$this->_db->quoteName($locked_byName).
					' = 0 OR '.$this->_db->quoteName($locked_byName).' = '.(int) $user_id.')',
					'AND'
				);
			} else {
				$query->where(
					' ('.$this->_db->nameQuote($locked_byName).
					' = 0 OR '.$this->_db->nameQuote($locked_byName).' = '.(int) $user_id.')',
					'AND'
				);
			}
		}

		if(version_compare(JVERSION, '3.0', 'ge')) {
			$cids = $this->_db->qn($k).' = ' .
					implode(' OR '.$this->_db->qn($k).' = ',$cid);
		} elseif(version_compare(JVERSION, '1.6.0', 'ge')){
			$cids = $this->_db->quoteName($k).' = ' .
					implode(' OR '.$this->_db->quoteName($k).' = ',$cid);
		} else {
			$cids = $this->_db->nameQuote($k).' = ' .
					implode(' OR '.$this->_db->nameQuote($k).' = ',$cid);
		}

		$query->where('('.$cids.')');

		$this->_db->setQuery( (string)$query );
		if(version_compare(JVERSION, '3.0', 'ge')) {
			try {
				$this->_db->query();
			} catch(JDatabaseException $e) {
				$this->setError($e->getMessage());
			}
		} else {
			if (!$this->_db->query())
			{
				$this->setError($this->_db->getErrorMsg());
				return false;
			}
		}

		if (count( $cid ) == 1 && $checkin)
		{
			if ($this->_db->getAffectedRows() == 1) {
				$this->checkin( $cid[0] );
				if ($this->$k == $cid[0]) {
					$this->published = $publish;
				}
			}
		}
		$this->setError('');
		return true;
	}

	public function delete( $oid=null )
	{
		if($oid) $this->load($oid);

		if(!$this->onBeforeDelete($oid)) return false;
		$result = parent::delete($oid);
		if($result) {
			$result = $this->onAfterDelete($oid);
		}
		return $result;
	}

	public function hit( $oid=null, $log=false )
	{
		if(!$this->onBeforeHit($oid, $log)) return false;
		$result = parent::hit($oid, $log);
		if($result) {
			$result = $this->onAfterHit($oid);
		}
		return $result;
	}

	/**
	 * Export item list to CSV
	 */
	function toCSV($separator=',')
	{
		$csv = array();

		foreach (get_object_vars( $this ) as $k => $v)
		{
			if (is_array($v) or is_object($v) or $v === NULL)
			{
				continue;
			}
			if ($k[0] == '_')
			{ // internal field
				continue;
			}
			$csv[] = '"'.str_replace('"', '""', $v).'"';
		}
		$csv = implode($separator, $csv);

		return $csv;
	}

	/**
	 * Exports the table in array format
	 */
	function getData()
	{
		$ret = array();

		foreach (get_object_vars( $this ) as $k => $v)
		{
			if( ($k[0] == '_') || ($k[0] == '*'))
			{ // internal field
				continue;
			}
			$ret[$k] = $v;
		}

		return $ret;
	}

	/**
	 * Get the header for exporting item list to CSV
	 */
	function getCSVHeader($separator=',')
	{
		$csv = array();

		foreach (get_object_vars( $this ) as $k => $v)
		{
			if (is_array($v) or is_object($v) or $v === NULL)
			{
				continue;
			}
			if ($k[0] == '_')
			{ // internal field
				continue;
			}
			$csv[] = '"'.str_replace('"', '\"', $k).'"';
		}
		$csv = implode($separator, $csv);

		return $csv;
	}

	/**
	 * Get the columns from database table.
	 *
	 * @return  mixed  An array of the field names, or false if an error occurs.
	 */
	public function getTableFields()
	{
		static $cache = array();

		if(!array_key_exists($this->_tbl, $cache)) {
			// Lookup the fields for this table only once.
			$name	= $this->_tbl;
			if(version_compare(JVERSION, '3.0', 'ge')) {
				$fields	= $this->_db->getTableColumns($name, false);
				if (empty($fields)) {
					return false;
				}
				$cache[$this->_tbl] = $fields;
			} else {
				$fields	= $this->_db->getTableFields($name, false);
				if (!isset($fields[$name])) {
					return false;
				}
				$cache[$this->_tbl] = $fields[$name];
			}
		}

		return $cache[$this->_tbl];
	}

	/**
	* Method to return the real name of a "special" column such as ordering, hits, published
	* etc etc. In this way you are free to follow your db naming convention and use the
	* built in Joomla functions.
	*
	* @param   string  $column  Name of the "special" column (ie ordering, hits etc etc)
	*
	* @return  string  The string that identify the special
	*/
	public function getColumnAlias($column)
	{
		if (isset($this->_columnAlias[$column]))
		{
			$return = $this->_columnAlias[$column];
		}
		else
		{
			$return = $column;
		}
		$return = preg_replace('#[^A-Z0-9_]#i', '', $return);

		return $return;
	}

	/**
	* Method to register a column alias for a "special" column.
	*
	* @param   string  $column       The "special" column (ie ordering)
	* @param   string  $columnAlias  The real column name (ie foo_ordering)
	*
	* @return  void
	*
	*/
	public function setColumnAlias($column, $columnAlias)
	{
		$column = strtolower($column);

		$column = preg_replace('#[^A-Z0-9_]#i', '', $column);
		$this->_columnAlias[$column] = $columnAlias;
	}

	/**
	 * NOTE TO 3RD PARTY DEVELOPERS:
	 *
	 * When you override the following methods in your child classes,
	 * be sure to call parent::method *AFTER* your code, otherwise the
	 * plugin events do NOT get triggered
	 *
	 * Example:
	 * protected function onAfterStore(){
	 * 	   // Your code here
	 *     return parent::onAfterStore() && $your_result;
	 * }
	 *
	 * Do not do it the other way around, e.g. return $your_result && parent::onAfterStore()
	 * Due to  PHP short-circuit boolean evaluation the parent::onAfterStore()
	 * will not be called if $your_result is false.
	 */
	protected function onBeforeBind(&$from)
	{
		if($this->_trigger_events){
			$name = FOFInflector::pluralize($this->getKeyName());

			$dispatcher = JDispatcher::getInstance();
			$result = $dispatcher->trigger( 'onBeforeBind'.ucfirst($name), array( &$this, &$from ) );

			if(in_array(false, $result, true)){
				return false;
			}
			else{
				return true;
			}
		}
		return true;
	}

	protected function onAfterLoad(&$result)
	{
		if($this->_trigger_events){
			$name = FOFInflector::pluralize($this->getKeyName());

			$dispatcher = JDispatcher::getInstance();
			$dispatcher->trigger( 'onAfterLoad'.ucfirst($name), array( &$this, &$result ) );
		}
	}

	protected function onBeforeStore($updateNulls)
	{
		// Do we have a "Created" set of fields?
		$created_on		= $this->getColumnAlias('created_on');
		$created_by		= $this->getColumnAlias('created_by');
		$modified_on	= $this->getColumnAlias('modified_on');
		$modified_by	= $this->getColumnAlias('modified_by');
		$locked_on		= $this->getColumnAlias('locked_on');
		$locked_by		= $this->getColumnAlias('locked_by');
		$title			= $this->getColumnAlias('title');
		$slug			= $this->getColumnAlias('slug');

		$hasCreatedOn = isset($this->$created_on) || property_exists($this, $created_on);
		$hasCreatedBy = isset($this->$created_by) || property_exists($this, $created_by);
		
		if($hasCreatedOn && $hasCreatedBy) {
			$hasModifiedOn = isset($this->$modified_on) || property_exists($this, $modified_on);
			$hasModifiedBy = isset($this->$modified_by) || property_exists($this, $modified_by);
			if(empty($this->$created_by) || ($this->$created_on == '0000-00-00 00:00:00') || empty($this->$created_on)) {
				$uid = JFactory::getUser()->id;
				if($uid) {
					$this->$created_by = JFactory::getUser()->id;
				}
				jimport('joomla.utilities.date');
				$date = new JDate();
				if(version_compare(JVERSION, '3.0', 'ge')) {
					$this->$created_on = $date->toSql();
				} else {
					$this->$created_on = $date->toMysql();
				}
			} elseif($hasModifiedOn && $hasModifiedBy) {
				$uid = JFactory::getUser()->id;
				if($uid) {
					$this->$modified_by = JFactory::getUser()->id;
				}
				jimport('joomla.utilities.date');
				$date = new JDate();
				if(version_compare(JVERSION, '3.0', 'ge')) {
					$this->$modified_on = $date->toSql();
				} else {
					$this->$modified_on = $date->toMysql();
				}
			}
		}

		// Do we have a set of title and slug fields?
		$hasTitle = isset($this->$title) || property_exists($this, $title);
		$hasSlug = isset($this->$slug) || property_exists($this, $slug);
		if($hasTitle && $hasSlug) {
			if(empty($this->$slug)) {
				// Create a slug from the title
				$this->$slug = FOFStringUtils::toSlug($this->$title);
			} else {
				// Filter the slug for invalid characters
				$this->$slug = FOFStringUtils::toSlug($this->$slug);
			}

			// Make sure we don't have a duplicate slug on this table
			$db = $this->getDbo();
			if(version_compare(JVERSION, '3.0', 'ge')) {
				$query = FOFQueryAbstract::getNew($db)
					->select($db->qn($slug))
					->from($this->_tbl)
					->where($db->qn($slug).' = '.$db->q($this->$slug))
					->where('NOT '.$db->qn($this->_tbl_key).' = '.$db->q($this->{$this->_tbl_key}));
			} elseif(version_compare(JVERSION, '1.6.0', 'ge')) {
				$query = FOFQueryAbstract::getNew($db)
					->select($db->quoteName($slug))
					->from($this->_tbl)
					->where($db->quoteName($slug).' = '.$db->quote($this->$slug))
					->where('NOT '.$db->quoteName($this->_tbl_key).' = '.$db->quote($this->{$this->_tbl_key}));
			} else {
				$query = FOFQueryAbstract::getNew($db)
					->select($db->nameQuote($slug))
					->from($this->_tbl)
					->where($db->nameQuote($slug).' = '.$db->quote($this->$slug))
					->where('NOT '.$db->nameQuote($this->_tbl_key).' = '.$db->quote($this->{$this->_tbl_key}));
			}
			$db->setQuery($query);
			$existingItems = $db->loadAssocList();

			$count = 0;
			$newSlug = $this->$slug;
			while(!empty($existingItems)) {
				$count++;
				$newSlug = $this->$slug .'-'. $count;
				if(version_compare(JVERSION, '3.0', 'ge')) {
					$query = FOFQueryAbstract::getNew($db)
						->select($db->qn($slug))
						->from($this->_tbl)
						->where($db->qn($slug).' = '.$db->q($newSlug))
						->where($db->qn($this->_tbl_key).' = '.$db->q($this->{$this->_tbl_key}), 'AND NOT');
				} elseif(version_compare(JVERSION, '1.6.0', 'ge')) {
					$query = FOFQueryAbstract::getNew($db)
						->select($db->quoteName($slug))
						->from($this->_tbl)
						->where($db->quoteName($slug).' = '.$db->quote($newSlug))
						->where($db->quoteName($this->_tbl_key).' = '.$db->quote($this->{$this->_tbl_key}), 'AND NOT');
				} else {
					$query = FOFQueryAbstract::getNew($db)
						->select($db->nameQuote($slug))
						->from($this->_tbl)
						->where($db->nameQuote($slug).' = '.$db->quote($newSlug))
						->where($db->nameQuote($this->_tbl_key).' = '.$db->quote($this->{$this->_tbl_key}), 'AND NOT');
				}
				$db->setQuery($query);
				$existingItems = $db->loadAssocList();
			}
			$this->$slug = $newSlug;
		}

		// Execute onBeforeStore<tablename> events in loaded plugins
		if($this->_trigger_events){
			$name = FOFInflector::pluralize($this->getKeyName());
			$dispatcher = JDispatcher::getInstance();
			$result = $dispatcher->trigger( 'onBeforeStore'.ucfirst($name), array( &$this, $updateNulls ) );

			if(in_array(false, $result, true)){
				return false;
			}
			else{
				return true;
			}
		}

		return true;
	}

	protected function onAfterStore()
	{
		if($this->_trigger_events){
			$name = FOFInflector::pluralize($this->getKeyName());

			$dispatcher = JDispatcher::getInstance();
			$result =  $dispatcher->trigger( 'onAfterStore'.ucfirst($name), array( &$this ) );

			if(in_array(false, $result, true)){
				return false;
			}
			else{
				return true;
			}
		}
		return true;
	}

	protected function onBeforeMove($updateNulls)
	{
		if($this->_trigger_events){
			$name = FOFInflector::pluralize($this->getKeyName());

			$dispatcher = JDispatcher::getInstance();
			$result = $dispatcher->trigger( 'onBeforeMove'.ucfirst($name), array( &$this, $updateNulls ) );

			if(in_array(false, $result, true)){
				return false;
			}
			else{
				return true;
			}
		}
		return true;
	}

	protected function onAfterMove()
	{
		if($this->_trigger_events){
			$name = FOFInflector::pluralize($this->getKeyName());

			$dispatcher = JDispatcher::getInstance();
			$result = $dispatcher->trigger( 'onAfterMove'.ucfirst($name), array( &$this ) );

			if(in_array(false, $result, true)){
				return false;
			}
			else{
				return true;
			}
		}
		return true;
	}

	protected function onBeforeReorder($where = '')
	{
		if($this->_trigger_events){
			$name = FOFInflector::pluralize($this->getKeyName());

			$dispatcher = JDispatcher::getInstance();
			$result = $dispatcher->trigger( 'onBeforeReorder'.ucfirst($name), array( &$this, $where ) );

			if(in_array(false, $result, true)){
				return false;
			}
			else{
				return true;
			}
		}
		return true;
	}

	protected function onAfterReorder()
	{
		if($this->_trigger_events){
			$name = FOFInflector::pluralize($this->getKeyName());

			$dispatcher = JDispatcher::getInstance();
			$result = $dispatcher->trigger( 'onAfterReorder'.ucfirst($name), array( &$this ) );

			if(in_array(false, $result, true)){
				return false;
			}
			else{
				return true;
			}
		}
		return true;
	}

	protected function onBeforeDelete($oid)
	{
		if($this->_trigger_events){
			$name = FOFInflector::pluralize($this->getKeyName());

			$dispatcher = JDispatcher::getInstance();
			$result = $dispatcher->trigger( 'onBeforeDelete'.ucfirst($name), array( &$this, $oid ) );

			if(in_array(false, $result, true)){
				return false;
			}
			else{
				return true;
			}
		}
		return true;
	}

	protected function onAfterDelete($oid)
	{
		if($this->_trigger_events){
			$name = FOFInflector::pluralize($this->getKeyName());

			$dispatcher = JDispatcher::getInstance();
			$result = $dispatcher->trigger( 'onAfterDelete'.ucfirst($name), array( &$this, $oid ) );

			if(in_array(false, $result, true)){
				return false;
			}
			else{
				return true;
			}
		}
		return true;
	}

	protected function onBeforeHit($oid, $log)
	{
		if($this->_trigger_events){
			$name = FOFInflector::pluralize($this->getKeyName());

			$dispatcher = JDispatcher::getInstance();
			$result = $dispatcher->trigger( 'onBeforeHit'.ucfirst($name), array( &$this, $oid, $log ) );

			if(in_array(false, $result, true)){
				return false;
			}
			else{
				return true;
			}
		}
		return true;
	}

	protected function onAfterHit($oid)
	{
		if($this->_trigger_events){
			$name = FOFInflector::pluralize($this->getKeyName());

			$dispatcher = JDispatcher::getInstance();
			$result = $dispatcher->trigger( 'onAfterHit'.ucfirst($name), array( &$this, $oid ) );

			if(in_array(false, $result, true)){
				return false;
			}
			else{
				return true;
			}
		}
		return true;
	}

	protected function onBeforeCopy($oid)
	{
		if($this->_trigger_events){
			$name = FOFInflector::pluralize($this->getKeyName());

			$dispatcher = JDispatcher::getInstance();
			$result = $dispatcher->trigger( 'onBeforeCopy'.ucfirst($name), array( &$this, $oid ) );

			if(in_array(false, $result, true)){
				return false;
			}
			else{
				return true;
			}
		}
		return true;
	}

	protected function onAfterCopy($oid)
	{
		if($this->_trigger_events){
			$name = FOFInflector::pluralize($this->getKeyName());

			$dispatcher = JDispatcher::getInstance();
			$result = $dispatcher->trigger( 'onAfterCopy'.ucfirst($name), array( &$this, $oid ) );

			if(in_array(false, $result, true)){
				return false;
			}
			else{
				return true;
			}
		}
		return true;
	}

	protected function onBeforePublish(&$cid, $publish)
	{
		if($this->_trigger_events){
			$name = FOFInflector::pluralize($this->getKeyName());

			$dispatcher = JDispatcher::getInstance();
			$result = $dispatcher->trigger( 'onBeforePublish'.ucfirst($name), array( &$this, &$cid, $publish ) );

			if(in_array(false, $result, true)){
				return false;
			}
			else{
				return true;
			}
		}
		return true;
	}

	protected function onAfterReset()
	{
		if($this->_trigger_events){
			$name = FOFInflector::pluralize($this->getKeyName());

			$dispatcher = JDispatcher::getInstance();
			$result = $dispatcher->trigger( 'onAfterReset'.ucfirst($name), array( &$this ) );

			if(in_array(false, $result, true)){
				return false;
			}
			else{
				return true;
			}
		}
		return true;
	}

	protected function onBeforeReset()
	{
		if($this->_trigger_events){
			$name = FOFInflector::pluralize($this->getKeyName());

			$dispatcher = JDispatcher::getInstance();
			$result = $dispatcher->trigger( 'onBeforeReset'.ucfirst($name), array( &$this ) );

			if(in_array(false, $result, true)){
				return false;
			}
			else{
				return true;
			}
		}
		return true;
	}
}

if(version_compare(JVERSION, '1.6.0', 'ge')) {
	class FOFTable extends FOFTable_COMMONBASE
	{
		public function load( $keys=null, $reset=true )
		{
			$result = parent::load($keys, $reset);
			$this->onAfterLoad($result);
			return $result;
		}
	}
} else {
	class FOFTable extends FOFTable_COMMONBASE
	{
		public function load( $oid=null )
		{
			$result = parent::load($oid);
			$this->onAfterLoad($result);
			return $result;
		}
	}
}