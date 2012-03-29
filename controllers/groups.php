<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/*
	Copyright (c) 2011 Sean Downey

	Permission is hereby granted, free of charge, to any person obtaining a copy
	of this software and associated documentation files (the "Software"), to deal
	in the Software without restriction, including without limitation the rights
	to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
	copies of the Software, and to permit persons to whom the Software is
	furnished to do so, subject to the following conditions:
	
	The above copyright notice and this permission notice shall be included in
	all copies or substantial portions of the Software.
	
	THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
	IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
	FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
	AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
	LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
	OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
	THE SOFTWARE.
*/

class Groups extends Admin_Controller {
               
	function __construct()
	{
 		parent::__construct();

		$this->auth->restrict('Navigation.Content.View');
		$this->load->model('navigation_group_model');
		$this->load->model('navigation_model');
		$this->lang->load('navigation_group');
				
		Template::set_block('sub_nav', 'content/_sub_nav');
	}
	
	
	/** 
	 * function index
	 *
	 * list form data
	 */
	public function index()
	{
		$data = array();
		$data["records"] = $this->navigation_group_model->find_all();

		Assets::add_js($this->load->view('groups/js', null, true), 'inline');
		Template::set_view("groups/index");
		Template::set("data", $data);
		Template::set("toolbar_title", "Manage Navigation Groups");
		Template::render();
	}
	
	//--------------------------------------------------------------------
	
	/**
	 * Creates a new navigation group
	 */
	public function create() 
	{
		$this->auth->restrict('Navigation.Content.Create');

		if ($this->input->post('submit'))
		{
			if ($this->save_navigation())
			{
				Template::set_message(lang("navigation_create_success"), 'success');
				Template::redirect(SITE_AREA.'/content/navigation/groups');
			}
			else 
			{
				Template::set_message(lang("navigation_create_failure") . $this->navigation_group_model->error, 'error');
			}
		}
	
		Template::set('toolbar_title', lang("navigation_create_new_button"));
		Template::set_view('groups/create');
		Template::set("toolbar_title", "Manage Navigation Groups");
		Template::render();
	}
	//--------------------------------------------------------------------
	
	
	/**
	 * Edits a group
	 */
	public function edit() 
	{
		$this->auth->restrict('Navigation.Content.Edit');

		$id = (int)$this->uri->segment(6);
		
		if (empty($id))
		{
			Template::set_message(lang("navigation_invalid_id"), 'error');
			redirect(SITE_AREA.'/content/navigation/groups');
		}
	
		if ($this->input->post('submit'))
		{
			if ($this->save_navigation('update', $id))
			{
				Template::set_message(lang("navigation_edit_success"), 'success');
			}
			else 
			{
				Template::set_message(lang("navigation_edit_failure") . $this->navigation_group_model->error, 'error');
			}
		}
		
		Template::set('navigation', $this->navigation_group_model->find($id));
	
		Template::set('toolbar_title', lang("navigation_edit_heading"));
		Template::set_view('groups/edit');
		Template::set("toolbar_title", "Manage Navigation Groups");
		Template::render();
	}
	
	
	/**
	 * Deletes a group
	 */
	public function delete() 
	{	
		$this->auth->restrict('Navigation.Content.Delete');

		$id = $this->uri->segment(6);
	
		if (!empty($id))
		{	
			if ($this->navigation_group_model->delete($id))
			{
				// delete the nav items in the group
				$this->navigation_model->delete_where(array('nav_group_id' => $id));
				Template::set_message(lang("navigation_delete_success"), 'success');
			}
			else
			{
				Template::set_message(lang("navigation_delete_failure") . $this->navigation_group_model->error, 'error');
			}
		}
		
		redirect(SITE_AREA.'/content/navigation/groups');
	}
	
	
	/**
	 * Save a group's details
	 * 
	 * @param string  $type The request type - either Insert or Update
	 * @param integer $id ID of the group
	 * 
	 * @return boolean Successful save or not 
	 */
	public function save_navigation($type='insert', $id=0) 
	{	
			
		$this->form_validation->set_rules('title','Title','required|trim|xss_clean|max_length[30]');			
		$this->form_validation->set_rules('abbr','Abbreviation','required|trim|xss_clean|max_length[20]');
		if ($this->form_validation->run() === false)
		{
			return false;
		}
		
		if ($type == 'insert')
		{
			$id = $this->navigation_group_model->insert($_POST);
			
			if (is_numeric($id))
			{
				$return = true;
			}
			else
			{
				$return = false;
			}
		}
		elseif ($type == 'update')
		{
			$return = $this->navigation_group_model->update($id, $_POST);
		}
		
		return $return;
	}

}
