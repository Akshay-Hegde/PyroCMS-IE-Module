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
class import_export {
	public $CI;
	
	private $drop_folder;
	private $tblpages = 'pages';
	private $tblpage_chunks = 'page_chunks';
	private $tblpage_layouts = 'page_layouts';
	private $folder_pages = 'pages';
	private $folder_layouts = 'layouts';

	function __construct() {
		$this->CI = get_instance();
		$this->_find_folder();
	}
	
	function read() {
		/* read page css, js, html (chunks) */
		$rtn = '1';
		$pages_dir = $this->drop_folder.'/'.$this->folder_pages;
		$pages = $this->_glob_recursive($pages_dir.'/*');
		foreach ($pages as $file) {
			$rtn .= '.';
			$file_strip = str_replace($pages_dir,'',$file);
			$file_info = pathinfo($file_strip);
			$file = file_get_contents($file);
			switch(@$file_info['extension']) {
				case 'css':
					// this is a page part
					$uri = trim($file_info['dirname'],'/');
					$page_id = $this->_find_page($uri);
					$this->_update($this->tblpages,$page_id,'css',$file);
				break;
				case 'js':
					// this is a page part
					$uri = trim($file_info['dirname'],'/');
					$page_id = $this->_find_page($uri);
					$this->_update($this->tblpages,$page_id,'js',$file);
				break;
				case 'html':
					// this is a page chunk part
					$uri = trim($file_info['dirname'],'/');
					$page_id = $this->_find_page($uri);
					$chunk_id = $this->_find_chunk($file_info['filename'],$page_id);					
					$this->_update($this->tblpage_chunks,$chunk_id,'body',$file);
				break;
			}
		}

		/* read layouts css, js, html */
		$rtn .= '2';
		$layouts_dir = $this->drop_folder.'/'.$this->folder_layouts;
		$templates = $this->_glob_recursive($layouts_dir.'/*');
		foreach ($templates as $file) {
		 $rtn .= '.';
			$file_strip = str_replace($layouts_dir,'',$file);
			$file_info = pathinfo($file_strip);
			$file = file_get_contents($file);
			switch(@$file_info['extension']) {
				case 'css':
					// this is a layout part
					$title = trim($file_info['dirname'],'/');
					$id = $this->_find_layout($title);
					$this->_update($this->tblpage_layouts,$id,'css',$file);
				break;
				case 'js':
					// this is a layout part
					$title = trim($file_info['dirname'],'/');
					$id = $this->_find_layout($title);
					$this->_update($this->tblpage_layouts,$id,'js',$file);
				break;
				case 'html':
					// this is a layout chunk part
					$title = trim($file_info['dirname'],'/');
					$id = $this->_find_layout($title);
					$this->_update($this->tblpage_layouts,$id,'body',$file);
				break;
			}
		}
		
		// dump the pages cache
		$rtn .= '3';
		$this->CI->pyrocache->delete_all('page_m');
		
		return $rtn.'.'.lang('import_export:complete');
	}
	
	function write() {
	
		/* write page js and css */
		$rtn = '1';
		$dbc = $this->CI->db->get($this->tblpages);
		foreach ($dbc->result() as $dbr) {
			$rtn .= '.';
			$drop = $this->drop_folder.'/'.$this->folder_pages.'/'.$dbr->uri.'/';
			@mkdir($drop,0777,true);
			file_put_contents($drop.'style.css',$dbr->css);		
			file_put_contents($drop.'javascript.js',$dbr->js);
		}
		
		/* write page chunks html */
		$rtn .= '2';
		
		$this->CI->db->select('*, '.SITE_REF.'_'.$this->tblpage_chunks.'.slug chunkslug');
		$this->CI->db->from($this->tblpage_chunks);
		$this->CI->db->join($this->tblpages, $this->tblpages.'.id = '.$this->tblpage_chunks.'.id');		
		$dbc = $this->CI->db->get();
				
		foreach ($dbc->result() as $dbr) {
			$rtn .= '.';
			$drop = $this->drop_folder.'/'.$this->folder_pages.'/'.$dbr->uri.'/';
			file_put_contents($drop.$dbr->chunkslug.'.html',$dbr->body);
		}
		
		/* write layouts html, css, js */
		$rtn .= '3';
		$dbc = $this->CI->db->get($this->tblpage_layouts);
		foreach ($dbc->result() as $dbr) {
			$rtn .= '.';
			$drop = $this->drop_folder.'/'.$this->folder_layouts.'/'.$dbr->title.'/';
			@mkdir($drop,0777,true);
			file_put_contents($drop.'style.css',$dbr->css);		
			file_put_contents($drop.'javascript.js',$dbr->js);
			file_put_contents($drop.'template.html',$dbr->body);
		}

		return $rtn.'.'.lang('import_export:complete');
	}
	
	function _update($table,$primary,$rowname,$rowvalue) {
		$this->CI->db->where('id', $primary);
		$this->CI->db->update($table, array($rowname => $rowvalue)); 
	}
	
	function _glob_recursive($pattern, $flags = 0) {
		$files = glob($pattern, $flags);
		foreach (glob(dirname($pattern).'/*', GLOB_ONLYDIR|GLOB_NOSORT) as $dir) {
			$files = array_merge($files, $this->_glob_recursive($dir.'/'.basename($pattern), $flags));
		}
		return $files;
	}
	
	function _find_chunk($slug,$page_id) {
		return $this->CI->db->get_where($this->tblpage_chunks, array('slug' => $slug, 'page_id' => $page_id), 1)->row()->id;
	}
	
	function _find_page($url) {
		return $this->CI->db->get_where($this->tblpages, array('uri' => $url), 1)->row()->id;
	}
	
	function _find_layout($title) {
		return $this->CI->db->get_where($this->tblpage_layouts, array('title' => $title), 1)->row()->id;
	}
	
	function _find_folder() {
		/*
		This will be in the upload folder since it is read/write but, 
		it is web accessible so we will generate a random folder name so each pyrosite is different

		We also add a empty index.html page to the upload folder so apache doesn't show the 
		directory listing if it's config to (which you should have OFF on a production box!)
		*/
		$look_in= UPLOAD_PATH;
		@touch($look_in.'index.html'); /* add that missing file */

		$folders = glob($look_in.'ie-folder-*',GLOB_ONLYDIR);
		/* there should only be one since WE made it so return the first if we find it */
		if (count($folders) > 0) {
			$this->drop_folder = $folders[0];
		} else {
			/* didn't find it so let's make the folder */
			$this->drop_folder = $look_in.'ie-folder-'.md5(uniqid(true));
			mkdir($this->drop_folder);
		}
	}

} /* end class */