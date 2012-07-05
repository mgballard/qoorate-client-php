<?php
/*
Author: Seth Murphy
Author URI: http://sethmurphy.com
License: GPL2
Copyright 2011	Seth Murphy	(email : seth@sethmurphy.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, AS 
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.	See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA	02110-1301	USA
*/

class Qoorate_Install {
	
	function Qoorate_Install() {
		$this->__construct();
	}
	function __construct() {
	}


	/**********************************************************************
	 *  create the default options
	 **********************************************************************/
	function create_options() {
		add_option('qoorate_api_key', '');
		add_option('qoorate_api_secret', '');
	}
	
	/**********************************************************************
	 *  Delete the options
	 **********************************************************************/
	function delete_options() {
		delete_option( 'qoorate_api_key', '' );
		delete_option( 'qoorate_api_secret', '' );
	}
}
?>