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

class Qoorate_Admin_Options {
	private $qoorate = null;
	public $api_key_error = '';
	public $api_secret_error = '';
	public $has_error = false;
	public $api_key = '';
	public $api_secret = '';
	
	function Qoorate_Admin_Options( $qoorate = null ) {
		$this->__construct( $qoorate );
	}
	function __construct( $qorate = null ) {
		$this->qoorate = $qoorate;
		$this->api_key = get_option('qoorate_api_key', '');
		$this->api_secret = get_option('qoorate_api_secret', '');
		$this->api_shortname = get_option('qoorate_api_shortname', '');
		
		if( isset( $_POST['submitted'] ) ) {
			$this->do_submit();
		}
	}
	
	function do_submit() {
		$api_key = trim( $_POST['qoorate-api-key'] );
		if( '' === $api_key )	{
			$api_key_error = 'Please enter the API Key.';
			$has_error = true;
		}

		$api_secret = trim( $_POST['qoorate-api-secret'] );
		if( '' === $api_secret )	{
			$api_secret_error = 'Please enter the API Secret.';
			$has_error = true;
		}
	
		$api_shortname = trim( $_POST['qoorate-api-shortname'] );
		if( '' === $api_shortname )	{
			$api_shortname_error = 'Please enter the API Shortname.';
			$has_error = true;
		}
	
	
		if( true == $has_error ) {
			$this->status_message = "Could not save options!";
			$qoorate->show_admin_messages( true );
		} else {
		    update_option('qoorate_api_key', $api_key);
		    update_option('qoorate_api_secret', $api_secret);
		    update_option('qoorate_api_shortname', $api_shortname);

    		$api_key = get_option('qoorate_api_key', '');
	    	$api_secret = get_option('qoorate_api_secret', '');
	    	$api_shortname = get_option('qoorate_api_shortname', '');
		}
	} 

}
?>
