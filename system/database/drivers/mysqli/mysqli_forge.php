<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP 5.2.4 or newer
 *
 * NOTICE OF LICENSE
 *
 * Licensed under the Open Software License version 3.0
 *
 * This source file is subject to the Open Software License (OSL 3.0) that is
 * bundled with this package in the files license.txt / license.rst.  It is
 * also available through the world wide web at this URL:
 * http://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to obtain it
 * through the world wide web, please send an email to
 * licensing@ellislab.com so we can send you a copy immediately.
 *
 * @package		CodeIgniter
 * @author		EllisLab Dev Team
 * @copyright	Copyright (c) 2008 - 2012, EllisLab, Inc. (http://ellislab.com/)
 * @license		http://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * @link		http://codeigniter.com
 * @since		Version 1.0
 * @filesource
 */

/**
 * MySQLi Forge Class
 *
 * @category	Database
 * @author		EllisLab Dev Team
 * @link		http://codeigniter.com/user_guide/database/
 */
class CI_DB_mysqli_forge extends CI_DB_forge {

	protected $_create_database	= 'CREATE DATABASE %s CHARACTER SET %s COLLATE %s';
	protected $_unsigned		= array(
		'TINYINT',
		'SMALLINT',
		'MEDIUMINT',
		'INT',
		'INTEGER',
		'BIGINT',
		'REAL',
		'DOUBLE',
		'DOUBLE PRECISION',
		'FLOAT',
		'DECIMAL',
		'NUMERIC'
	);
	protected $_null		= 'NULL';

	/**
	 * Constructor
	 *
	 * @return	void
	 */
	public function __construct()
	{
		parent::__construct();

		$this->_create_table .= ' DEFAULT CHARSET '.$this->db->char_set.' COLLATE '.$this->db->dbcollat;
	}

	// --------------------------------------------------------------------

	/**
	 * Alter table query
	 *
	 * Generates a platform-specific query so that a table can be altered
	 * Called by add_column(), drop_column(), and column_alter(),
	 *
	 * @param	string	the ALTER type (ADD, DROP, CHANGE)
	 * @param	string	the column name
	 * @param	mixed	the column definition
	 * @return	mixed
	 */
	protected function _alter_table($alter_type, $table, $field)
	{
		if ($alter_type === 'DROP')
		{
			return parent::_alter_table($alter_type, $table, $field);
		}

		$sql = 'ALTER TABLE '.$this->db->escape_identifiers($table);
		for ($i = 0, $c = count($field); $i < $c; $i++)
		{
			if ($field[$i]['_literal'] !== FALSE)
			{
				$field[$i] = ($alter_type === 'ADD')
						? "\n\tADD ".$field[$i]['_literal']
						: "\n\tMODIFY ".$field[$i]['_literal'];
			}
			else
			{
				if ($alter_type === 'ADD')
				{
					$field[$i]['_literal'] = "\n\tADD ";
				}
				else
				{
					$field[$i]['_literal'] = empty($field['new_name']) ? "\n\tMODIFY " : "\n\tCHANGE ";
				}

				$field[$i] = $field['_literal'].$this->_process_column($field[$i]);
			}
		}

		return array($sql.implode(',', $field));
	}

	// --------------------------------------------------------------------

	/**
	 * Process a single column
	 *
	 * @param	array
	 * @return	string
	 */
	protected function _process_column($field)
	{
		return $this->db->escape_identifiers($field['name'])
			.(empty($field['new_name']) ? '' : $this->db->escape_identifiers($field['new_name']))
			.' '.$field['type'].$field['length']
			.$field['unsigned']
			.$field['null']
			.$field['default']
			.$field['auto_increment']
			.$field['unique'];
	}

	// --------------------------------------------------------------------

	/**
	 * Process indexes
	 *
	 * @param	string	(ignored)
	 * @return	string
	 */
	protected function _process_indexes($table = NULL)
	{
		$sql = '';

		for ($i = 0, $c = count($this->keys); $i < $c; $i++)
		{
			if ( ! isset($this->fields[$this->keys[$i]]))
			{
				unset($this->keys[$i]);
				continue;
			}

			is_array($this->keys[$i]) OR $this->keys[$i] = array($this->keys[$i]);

			$sql .= ",\n\tKEY ".$this->db->escape_identifiers(implode('_', $this->keys[$i]))
				.' ('.implode(', ', $this->db->escape_identifiers($this->keys[$i])).')';
		}

		$this->keys = array();

		return $sql;
	}

}

/* End of file mysqli_forge.php */
/* Location: ./system/database/drivers/mysqli/mysqli_forge.php */