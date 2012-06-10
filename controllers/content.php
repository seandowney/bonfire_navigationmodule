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

class Content extends Admin_Controller {
               
	function __construct()
	{
 		parent::__construct();

		$this->auth->restrict('Navigation.Content.View');
		$this->load->model('navigation_model');
		$this->load->model('navigation_group_model');
		$this->lang->load('navigation');
		
		Assets::add_css('flick/jquery-ui-1.8.13.custom.css');
		Assets::add_js('jquery-ui-1.8.8.min.js');
		
		Template::set_block('sub_nav', 'content/_sub_nav');
	}
	
	
	/** 
	 * function index
	 *
	 * list form data
	 */
	public function index()
	{
	
		$groups = $this->navigation_group_model->find_all();
		Template::set('groups', $groups);

		$offset = $this->uri->segment(5);

		// Do we have any actions?
		if ($action = $this->input->post('submit'))
		{
			$checked = $this->input->post('checked');

			switch(strtolower($action))
			{
				case 'delete':
					$this->delete($checked);
					break;
			}
		}

		$where = array();

		// Filters
		$filter = $this->input->get('filter');
		switch($filter)
		{
			case 'group':
				$group_id = (int)$this->input->get('group_id');
				$where['navigation.nav_group_id'] = $group_id;
				$this->navigation_model->where('nav_group_id',(int)$this->input->get('group_id'));

				foreach ($groups as $group)
				{
					if ($group->nav_group_id == $group_id)
					{
						Template::set('filter_group', $group->title);
						break;
					}
				}
				break;
			default:
				break;
		}

		$this->load->helper('ui/ui');

		$this->navigation_model->limit($this->limit, $offset)->where($where);
		$this->navigation_model->select('*');

		$nav_items = $this->navigation_model->order_by('nav_group_id, parent_id, position')->find_all();
		$records = array();
		if (is_array($nav_items) && count($nav_items))
		{
			foreach($nav_items as $record)
			{
				$records[$record->nav_id] = $record;
			}
		}
		Template::set('records', $records);

		// Pagination
		$this->load->library('pagination');

		$this->navigation_model->where($where);
		$total_records = $this->navigation_model->count_all();
		Template::set('total_records', $total_records);

		$this->pager['base_url'] = site_url(SITE_AREA .'/content/navigation/index');
		$this->pager['total_rows'] = $total_records;
		$this->pager['per_page'] = $this->limit;
		$this->pager['uri_segment']	= 5;

		$this->pagination->initialize($this->pager);

		Template::set('current_url', current_url());
		Template::set('filter', $filter);
		Template::set_view('navigation/content/index');

		Template::set('toolbar_title', lang('navigation_manage'));

		Assets::add_js('jquery-ui-1.8.13.min.js');
		Assets::add_module_js('navigation', 'navigation');

		Template::render();
	}
	
	//--------------------------------------------------------------------
	
	
	public function create() 
	{
		$this->auth->restrict('Navigation.Content.Create');

		$nav_items = $this->navigation_model->order_by('nav_group_id, position')->find_all();
		$parents = array();
		$parents[] = '';
		if (is_array($nav_items) && count($nav_items)) 
		{
			foreach($nav_items as $key => $record)
			{
				$parents[$record->nav_id] = $record->title;
			}
		}

		$groups = $this->navigation_group_model->find_all();
		$dropdown_groups = array();
		if (is_array($groups) && count($groups))
		{
			foreach($groups as $record)
			{
				$dropdown_groups[$record->nav_group_id] = $record->title;
			}
		}

		Template::set("groups", $dropdown_groups);
		Template::set("parents", $parents);
		//Template::set("data", $data);

		if ($this->input->post('submit'))
		{
			if ($this->save_navigation())
			{
				Template::set_message(lang("navigation_create_success"), 'success');
				Template::redirect(SITE_AREA.'/content/navigation');
			}
			else 
			{
				Template::set_message(lang("navigation_create_failure") . $this->navigation_model->error, 'error');
			}
		}
	
		Template::set_view('content/form');
		Template::set('toolbar_title', lang("navigation_create_new_button"));
		Template::render();
	}

	//--------------------------------------------------------------------

	public function edit() 
	{
		$this->auth->restrict('Navigation.Content.Edit');

		$id = (int)$this->uri->segment(5);
		
		if (empty($id))
		{
			Template::set_message(lang("navigation_invalid_id"), 'error');
			redirect(SITE_AREA.'/content/navigation');
		}

		if ($this->input->post('submit'))
		{
			if ($this->save_navigation('update', $id))
			{
				Template::set_message(lang("navigation_edit_success"), 'success');
			}
			else 
			{
				Template::set_message(lang("navigation_edit_failure") . $this->navigation_model->error, 'error');
			}
		}

		$nav_record = $this->navigation_model->find($id);
		$nav_items = $this->navigation_model->order_by('nav_group_id, position')->find_all_by('nav_group_id', $nav_record->nav_group_id);
		$parents[] = '';
		foreach($nav_items as $key => $record)
		{
			// remove the current link
			if($id != $record->nav_id)
			{
				$parents[$record->nav_id] = $record->title;
			}
		}

		$groups = $this->navigation_group_model->find_all();
		foreach($groups as $group_id => $record)
		{
			$groups[$group_id] = $record->title;
		}
		//Template::set("data", $data);

		Template::set('groups', $groups);
		Template::set('parents', $parents);
		Template::set('navigation', $nav_record);

		Template::set('toolbar_title', lang("navigation_edit_heading"));
		Template::set_view('content/form');
		Template::render();		
	}

	//--------------------------------------------------------------------

	public function delete($navs)
	{

		if (empty($navs))
		{
			$nav_id = $this->uri->segment(5);

			if(!empty($nav_id))
			{
				$navs = array($nav_id);
			}
		}

		if (!empty($navs))
		{
			$this->auth->restrict('Navigation.Content.Delete');

			foreach ($navs as $nav_id)
			{
				$nav = $this->navigation_model->find($nav_id);

				if (isset($nav))
				{
					$this->navigation_model->update_parent($nav_id, 0);
					$this->navigation_model->un_parent_kids($nav_id);

					if ($this->navigation_model->delete($nav_id))
					{
						Template::set_message(lang('navigation_delete_success'), 'success');
					}
					  else
					{
						Template::set_message(lang('navigation_delete_failure'). $this->navigation_model->error, 'error');
					}
				}
				else
				{
					Template::set_message(lang('navigation_not_found'), 'error');

				}
			}
		}
		else
		{
			Template::set_message(lang('navigation_empty_list'), 'error');
		}

		redirect(SITE_AREA .'/content/navigation');
	}

	//--------------------------------------------------------------------

	public function save_navigation($type='insert', $id=0) 
	{	
		if ($type == 'insert')
		{
			$_POST['has_kids'] = 0;
			$_POST['position'] = 99;
		}

		$this->form_validation->set_rules('title','Title','required|trim|xss_clean|max_length[30]');			
		$this->form_validation->set_rules('url','URL','required|trim|xss_clean|max_length[150]');			
		$this->form_validation->set_rules('nav_group_id','Group','required|trim|xss_clean|is_numeric|max_length[11]');			
		$this->form_validation->set_rules('parent_id','Parent','required|trim|xss_clean|is_numeric|max_length[11]');			
		if ($this->form_validation->run() === false)
		{
			return false;
		}
		
		if ($type == 'insert')
		{
			$id = $this->navigation_model->insert($_POST);
			
			if (is_numeric($id))
			{
				$this->navigation_model->update_parent($id, $this->input->post('parent_id'));
				$return = true;
			}
			else
			{
				$return = false;
			}
		}
		else if ($type == 'update')
		{
			// check if there is a parent
			$this->navigation_model->update_parent($id, $this->input->post('parent_id'));
			$return = $this->navigation_model->update($id, $_POST);
		}
		
//		if ($this->input->post('parent_id') != 0) {
//			// there is a parent so update it to set the has_kids field
//			$data = array('has_kids' => 1);
//			$parent_updated = $this->navigation_model->update($this->input->post('parent_id'), $data);
//
//		}
		return $return;
	}

	//--------------------------------------------------------------------

	public function ajax_update_positions()
	{
		// Create an array containing the IDs
		$ids = explode(',', $this->input->post('order'));

		// Counter variable
		$pos = 1;

		foreach($ids as $id)
		{
			// Update the position
			$data['position'] = $pos;
			$this->navigation_model->update($id, $data);
			++$pos;
		}
	}
}
