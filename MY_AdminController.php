<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Admincontroller extends MY_Controller 
{
	/**
	 * My Base Admin controller.
	 */
	public function __construct()
	{	
		parent::__construct(); 
		
		$this->load->library('ion_auth');

		if (!$this->ion_auth->logged_in())
		{
			redirect('auth/login', 'refresh');
		}
		elseif (!$this->ion_auth->is_admin())
		{
			return show_error('You must be an administrator to view this page.');
		}
	}

	public function login() 
	{

	}

	public function logout() 
	{

	}
	
}
