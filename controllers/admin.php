<?php defined('BASEPATH') or exit('No direct script access allowed');
/**
 * Import Export Module
 *
 * @package		Import Export Module
 * @author		Don Myers
 * @copyright	Copyright (c) 2012, Don Myers
 * @license		http://www.opensource.org/licenses/osl-3.0.php
 * @link			http://pyro.projectorangebox.com
 */
class Admin extends Admin_Controller
{
	public function __construct()
	{
		parent::__construct();
		
		$this->lang->load('import_export');
		
		$this->load->library('import_export');
	}

	/**
	 * Handle default (do nothing)
	 */
	public function index()
	{
		// Build the page
		$this->data['title'] = lang('import_export:helptitle');
		$this->data['action'] = lang('import_export:help');		
		$this->data['msg'] = '';
		
		$this->template->title($this->module_details['name'])->build('admin/index', $this->data);
	}
	
	/**
	 * Handle read from file system and add to db
	 * this is all or nothing
	 */
	public function read()
	{
		// Build the page
		$this->data['title'] = lang('import_export:output');
		$this->data['action'] = lang('import_export:read');
		$this->data['msg'] = $this->import_export->read();

		$this->template->title($this->module_details['name'])->build('admin/index', $this->data);
	
	}
		
	/**
	 * Handle write to file system from db
	 * this is also all or nothing
	 */
	public function write()
	{
		// Build the page
		$this->data['title'] = lang('import_export:output');
		$this->data['action'] = lang('import_export:write');
		$this->data['msg'] = $this->import_export->write();

		$this->template->title($this->module_details['name'])->build('admin/index', $this->data);
	}

}