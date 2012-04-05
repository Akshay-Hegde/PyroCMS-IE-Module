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
class Module_Import_export extends Module {

	public $version = '1.0';

	public function info()
	{
		return array(
			'name' => array(
				'en' => 'Import Export'
			),
			'description' => array(
				'en' => 'Importer and Exporter for Pages and Layouts'
			),
			'frontend' => FALSE,
			'backend' => TRUE,
			'menu' => 'utilities',
			'shortcuts' => array(
				'write' => array(
					'name' 	=> 'import_export:btnwrite',
					'uri' 	=> 'admin/import_export/write'
					),
				'read' => array(
					'name' 	=> 'import_export:btnread',
					'uri' 	=> 'admin/import_export/read'
					)
				)
		);
	}

	public function install()
	{
		return TRUE;
	}

	public function uninstall()
	{
		return TRUE;
	}

	public function upgrade($old_version)
	{
		return TRUE;
	}

	public function help()
	{
		return "No documentation has been added for this module.<br />Contact the module developer for assistance.";
	}
}