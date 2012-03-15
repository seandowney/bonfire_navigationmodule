<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

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

		$where = array();

		// Filters
		$filter = $this->input->get('filter');
		switch($filter)
		{
			case 'group':
				$where['navigation.nav_group_id'] = (int)$this->input->get('group_id');
				break;
			default:
				break;
		}

		$this->load->helper('ui/ui');

		$this->navigation_model->limit($this->limit, $offset)->where($where);
		$this->navigation_model->select('*');

		Template::set('records', $this->navigation_model->find_all());

		// Pagination
		$this->load->library('pagination');

		$this->navigation_model->where($where);
		$total_records = $this->navigation_model->count_all();

		$this->pager['base_url'] = site_url(SITE_AREA .'/content/navigation/index');
		$this->pager['total_rows'] = $total_records;
		$this->pager['per_page'] = $this->limit;
		$this->pager['uri_segment']	= 5;

		$this->pagination->initialize($this->pager);

		Template::set('current_url', current_url());
		Template::set('filter', $filter);

		Template::set('toolbar_title', lang('navigation_manage'));
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

		$groups = $this->navigation_group_model->find_all('nav_group_id');
		$groups = array();
		if (is_array($groups) && count($groups))
		{
			foreach($groups as $group_id => $record)
			{
				$groups[$group_id] = $record->title;
			}
		}
		Template::set("groups", $groups);
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

		$groups = $this->navigation_group_model->find_all('nav_group_id');
		foreach($groups as $group_id => $record)
		{
			$groups[$group_id] = $record->title;
		}
		Template::set("data", $data);

		Template::set('navigation', $nav_record);
	
		Template::set('toolbar_title', lang("navigation_edit_heading"));
		Template::set_view('content/form');
		Template::render();		
	}
	
			
	public function delete() 
	{	
		$this->auth->restrict('Navigation.Content.Delete');

		$id = $this->uri->segment(5);
	
		if (!empty($id))
		{	
			$this->navigation_model->update_parent($id, 0);
			$this->navigation_model->un_parent_kids($id);
			
			if ($this->navigation_model->delete($id))
			{
				Template::set_message(lang("navigation_delete_success"), 'success');
			} else
			{
				Template::set_message(lang("navigation_delete_failure") . $this->navigation_model->error, 'error');
			}
		}
		
		redirect(SITE_AREA.'/content/navigation');
	}
		
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
