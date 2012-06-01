<?php
/**
 * PDO Wrapper
 *
 * @author              JREAM
 * @link                http://jream.com
 * @copyright           2011 Jesse Boyer (contact@jream.com)
 * @license             GNU General Public License 3 (http://www.gnu.org/licenses/)
 *
 * This program is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details:
 * http://www.gnu.org/licenses/
*/

class SF_Database extends PDO {
	//------------
	private $SF;
	//------------

	/** @var mixed $_currentSelect Allows me fetch the same query multiple times */
	private $_currentSelect = NULL;
	
	/** @var mixed $_errorLog Whether or not to enable logging, if it's set it's a path to a file */
	private $_errorLog = false;
			
	/**
	* Instantiates the Database Object
	* @param mixed $db	string: The type of DB connection (mysql) 
	*					OR an associative array for the DB configuration containing: type, host, name, user, and pass.
	* @param string $dbHost Host location, often 'localhost'
	* @param string $dbName Name of the Database
	* @param string $dbUser User for the Database
	* @param string $dbPass Password for the User
	*/
	public function __construct() {
		$this->SF = &get_instance();
		//load configs
		$db = $this->SF->config->group('Database');
		
		/** Construct using the array */
		parent::__construct("{$db['type']}:host={$db['host']};dbname={$db['name']}", $db['user'], $db['pass']);
		parent::setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	}
	
	/**
	 * setLoggng - Enables error logging into a file, REQUIRES Log class
	 * @param string $filePath The location to write errors to
	 */
	public function setLogging($filePath) {
		/** Make sure they have the log class if they enable this */
		if (!class_exists('Log'))
		throw new Exception('You have enabled Logging, yet the Log class cannot be found.');
		
		/** Set the path */
		$this->_errorLog = $filePath;
	}

	/**
	* query - Allows you to run a regular query.
	* @param string $str The query string to pass.
	*/
	public function query($str) {
		/** Reset the currentSelect values so a fresh set will be placed in */
		$this->_currentSelect = NULL;
		
		$this->stmt = parent::query($str);
	}
	
	/**
	* select - Shorthand Select query
	* @param string $fromTable	What table to select from
	* @param string $select		What rows to select
	* @param string $where		Any other conditions (Optional)
	*/
	public function select($fromTable, $select, $where = NULL) {
		/** Reset the currentSelect values so a fresh set will be placed in */
		$this->_currentSelect = NULL;
		
		if ($where != NULL)
		$where = "WHERE $where";
		
		$this->query("SELECT $select FROM $fromTable $where");
	}
	
	/**
	 * _prepareInsertString - Handles an array and turns it into SQL code
	 * @param array $data The data to turn into an SQL friendly string
	 * @return array
	 */
	private function _prepareInsertString($data)  {
		ksort($data);
		/** 
		* @ Incoming $data looks like:
		* $data = array('field' => 'value', 'field2'=> 'value2');
		*/
		return array(
			'names' => implode("`, `",array_keys($data)),
			'values' => ':'.implode(', :',array_keys($data))
		);
	}
	
	/**
	 * _prepareUpdateString - Handles an array and turn it into SQL code
	 * @param array $data
	 * @return string
	 */
	private function _prepareUpdateString($data)  {
		ksort($data);
		/**
		* @ Incoming $data looks like:
		* $data = array('field' => 'value', 'field2'=> 'value2');
		*/

		$fieldDetails = NULL;
		foreach($data as $key => $value)
		{
			$fieldDetails .= "`$key`=:$key, "; /** Notice the space after the comma */
		}
		$fieldDetails = rtrim($fieldDetails, ', '); /** Notice the space after the comma */
		return $fieldDetails;
	}
	
	/**
	* insert - Convenience method to insert data
	* @param string $table	The table to insert into
	* @param array $data	An associative array of data: field => value
	*/
	public function insert($table, $data) {
		$insertString = $this->_prepareInsertString($data);

		$this->stmt = parent::prepare("INSERT INTO $table (`{$insertString['names']}`) VALUES({$insertString['values']})");

		foreach ($data as $key => $value)
		{
			$this->stmt->bindValue(":$key", $value);
		}

		$this->stmt->execute();
		$this->_handleError();
	}
	
	/**
	* update - Convenience method to update the database
	* @param string $table The table to update
	* @param array $data An associative array of fields to change: field => value
	* @param string $where A condition on where to apply this update
	*/
	public function update($table, $data, $where) {
		$updateString = $this->_prepareUpdateString($data);

		$this->stmt = parent::prepare("UPDATE $table SET $updateString WHERE $where");

		foreach ($data as $key => $value) {
			$this->stmt->bindValue(":$key", $value);
		}

		$this->stmt->execute();
		$this->_handleError();
	}
	
	/**
	* delete - Convenience method to delete rows
	* @param string $table The table to delete from
	* @param string $where A condition on where to apply this call
	* @return boolean
	*/
	public function delete($table, $where) {
		return parent::exec("DELETE FROM $table WHERE $where");
	}
	
	/**
	 * fetch - Return a single row, or a single row's field
	 * @param string $singleRow The name of a row if fetching only one field
	 * @param constant $fetchMode Default is PDO::FETCH_OBJ (http://www.php.net/manual/en/pdo.constants.php)
	 * @return mixed Either false, NULL, string, or object 
	 */
	public function fetch($singleRow = false, $fetchMode = PDO::FETCH_OBJ) {
		/** PDO::query() returns PDOStatement object or FALSE on failure */
		if ($this->stmt == false)
		return false;
		
		if ($singleRow == true) {
			/** Allows me to use fetch('name') more than once */
			if ($this->_currentSelect == NULL) {
				$this->_currentSelect = $this->stmt->fetch($fetchMode);
				
				/** @TODO: Why was I unsetting the stmt? It was causing problems when doing a count(), fetch('item') after another) **/
				//$this->stmt = NULL;
				//unset($this->stmt);
			}
		
			if (isset($this->_currentSelect->{$singleRow}))
			return $this->_currentSelect->{$singleRow};
			
			else
			return NULL;
		} else {
			$this->_currentSelect = $this->stmt->fetch($fetchMode);
			//$this->stmt = NULL;
			//unset($this->stmt);
			
			/** Allows me to prevent checking isset */
			if (empty($this->_currentSelect)) 
			return new StdClass();
			
			else
			return $this->_currentSelect;
		}

	}
	
	/**
	 * fetchAll - Fetches all records from a selection
	 * @param constant $fetchMode Default is PDO::FETCH_OBJ (http://www.php.net/manual/en/pdo.constants.php)
	 * @return object
	 */
	public function fetchAll($fetchMode = PDO::FETCH_OBJ) {
		/** PDO::query() returns PDOStatement object or FALSE on failure */
		if ($this->stmt == false)
		return false;
		
		$current = $this->stmt->fetchAll($fetchMode);
		//$this->stmt = NULL;
		//unset($this->stmt);
		
		if (empty($current))
		return new StdClass();
		
		else
		return $current;
	}

	/**
	* count - Counts the SQL Rows from the $result Query.
	* @return integer
	*/
	public function count() {
		return $this->stmt->rowCount();
	}

	/**
	* id - Gets the last inserted ID
	* @return integer
	*/
	public function id() {
		return parent::lastInsertId();
	}
	
	/**
	* _handleError - Handles errors with PDO and throws an exception.
	*/
	private function _handleError() {
		if ($this->errorCode() != '00000')
		{
			if ($this->_errorLog == true)
			Log::write($this->_errorLog, "Error: " . implode(',', $this->errorInfo()));

			throw new Exception("Error: " . implode(',', $this->errorInfo()));
		}
	}
	
}
