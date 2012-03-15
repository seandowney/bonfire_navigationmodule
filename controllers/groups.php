<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

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

		$offset = $this->uri->segment(5);

		$this->load->helper('ui/ui');

		$this->navigation_group_model->limit($this->limit, $offset);
		$this->navigation_group_model->select('*');

		Template::set('records', $this->navigation_group_model->find_all());

		// Pagination
		$this->load->library('pagination');

		$total_records = $this->navigation_group_model->count_all();

		$this->pager['base_url'] = site_url(SITE_AREA .'/content/navigation/groups/index');
		$this->pager['total_rows'] = $total_records;
		$this->pager['per_page'] = $this->limit;
		$this->pager['uri_segment']	= 5;

		$this->pagination->initialize($this->pager);

		Template::set('current_url', current_url());

		Template::set('toolbar_title', lang('navigation_manage'));
		Template::render();
	}
	
	//--------------------------------------------------------------------
	
	
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
		Template::set_view('groups/form');
		Template::render();
	}
	
	//--------------------------------------------------------------------
	
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
		Template::set_view('groups/form');
		Template::render();
	}
	
	//--------------------------------------------------------------------
			
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
			} else
			{
				Template::set_message(lang("navigation_delete_failure") . $this->navigation_group_model->error, 'error');
			}
		}
		
		redirect(SITE_AREA.'/content/navigation/groups');
	}
	
	//--------------------------------------------------------------------
			
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
			} else
			{
				$return = false;
			}
		}
		else if ($type == 'update')
		{
			$return = $this->navigation_group_model->update($id, $_POST);
		}
		
		return $return;
	}

}
