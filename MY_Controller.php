<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Controller extends CI_Controller 
{
	public $data = array();

	/**
	 * My Base controller.
	 */
	public function __construct()
	{	
		parent::__construct(); 
	}

	protected function check_hash($hash) 
	{
		
	}
	
	public function render() 
	{
		$this->load->view('/template/header.php');
		$this->load->view('/template/footer.php');
	}
}
