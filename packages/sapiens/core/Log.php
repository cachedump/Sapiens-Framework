<?php
/**
 * Log
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

class Log
{

	private static $_handle;
	
	/**
	* write - Appends text to a file for logging purposes
	*
	* @param resource $file Location of an existing file, or where one with that name should be created.
	* @param string $data The message to place inside the log file (Timestamp is automatically there)
	*/
	public static function write($file, $data) {	
		/** Open the file, or attempt to create it if it doesn't exist */
		self::$_handle = fopen($file, 'a');
		
		$date = date("m-d-Y g:ia");
		
		$contents = "[$date] $data\r\n";
		fwrite(self::$_handle, $contents);
		fclose(self::$_handle);
	}
	
}
