<?php
/**
 *  @package FrameworkOnFramework
 *  @copyright Copyright (c)2010-2013 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

/**
 * FrameworkOnFramework query element class; backported from Joomla! 1.7
 * 
 * FrameworkOnFramework is a set of classes whcih extend Joomla! 1.5 and later's
 * MVC framework with features making maintaining complex software much easier,
 * without tedious repetitive copying of the same code over and over again.
 */
class FOFQueryElement
{
	/**
	 * @var    string  The name of the element.
	 * @since  11.1
	 */
	protected $name = null;

	/**
	 * @var    array  An array of elements.
	 * @since  11.1
	 */
	protected $elements = null;

	/**
	 * @var    string  Glue piece.
	 * @since  11.1
	 */
	protected $glue = null;

	/**
	 * Constructor.
	 *
	 * @param   string  $name      The name of the element.
	 * @param   mixed   $elements  String or array.
	 * @param   string  $glue      The glue for elements.
	 *
	 * @return  JDatabaseQueryElement
	 *
	 * @since   11.1
	 */
	public function __construct($name, $elements, $glue = ',')
	{
		$this->elements	= array();
		$this->name		= $name;
		$this->glue		= $glue;

		$this->append($elements);
	}

	/**
	 * Magic function to convert the query element to a string.
	 *
	 * @return  string
	 *
	 * @since   11.1
	 */
	public function __toString()
	{
		if (substr($this->name, -2) == '()') {
			return PHP_EOL.substr($this->name, 0, -2).'('.implode($this->glue, $this->elements).')';
		}
		else {
			return PHP_EOL.$this->name.' '.implode($this->glue, $this->elements);
		}
	}

	/**
	 * Appends element parts to the internal list.
	 *
	 * @param   mixed  String or array.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function append($elements)
	{
		if (is_array($elements)) {
			$this->elements = array_merge($this->elements, $elements);
		}
		else {
			$this->elements = array_merge($this->elements, array($elements));
		}
	}

	/**
	 * Gets the elements of this element.
	 *
	 * @return  string
	 *
	 * @since   11.1
	 */
	public function getElements()
	{
		return $this->elements;
	}
}