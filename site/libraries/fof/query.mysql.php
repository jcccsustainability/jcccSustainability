<?php
/**
 *  @package FrameworkOnFramework
 *  @copyright Copyright (c)2010-2013 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 */

defined('_JEXEC') or die;

/**
 * FrameworkOnFramework query building class; backported from Joomla! 1.7
 * 
 * FrameworkOnFramework is a set of classes whcih extend Joomla! 1.5 and later's
 * MVC framework with features making maintaining complex software much easier,
 * without tedious repetitive copying of the same code over and over again.
 */
class FOFQueryMysql extends FOFQueryAbstract
{
	/**
	 * Concatenates an array of column names or values.
	 *
	 * @param   array   $values     An array of values to concatenate.
	 * @param   string  $separator  As separator to place between each value.
	 *
	 * @return  string  The concatenated values.
	 *
	 * @since   11.1
	 */
   function concatenate($values, $separator = null)
   {
		if ($separator) {
			$concat_string = 'CONCAT_WS('.$this->quote($separator);

			foreach($values as $value)
			{
				$concat_string .= ', '.$value;
			}

			return $concat_string.')';
		}
		else {
			return 'CONCAT('.implode(',', $values).')';
		}
	}
}