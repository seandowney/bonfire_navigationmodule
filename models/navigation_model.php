<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Navigation_model extends BF_Model {

	protected $table		= "navigation";
	protected $key			= "nav_id";
	protected $soft_deletes	= false;
	protected $date_format	= "datetime";
	protected $set_created	= false;
	protected $set_modified = false;
	
	private $persist_items = null;
	
	/**
	 * Override to format the groups selection into a storable format. 
	 * Choosing json since serialize has more overhead and no class info needs to be stored, only data.
	 * 
	 * @access public
	 * @param array $data The data to be inserted to the db - passed to the parent insert method.
	 * @return int|bool returns false if failed, otherwise the id of the row inserted
	 * @see BF_Model::insert()
	 */
	public function insert($data)
	{
		if (isset($data['groups']) && !empty($data['groups']) && is_array($data['groups']))
		{
			if (sizeof($data['groups']) > 1)
			{
				$data['nav_group_id'] = 0;
			} else {
				$data['nav_group_id'] = $data['groups'][0];
			}
			
			$data['groups'] = json_encode($data['groups']);
			
			
		} else {
			$data['groups'] = json_encode(array());
		}
		
		return parent::insert($data);
	}
	
	/**
	 * Override to format the groups selection into a storable format. 
	 * Choosing json since serialize has more overhead and no class info needs to be stored, only data.
	 * 
	 * @access public
	 * @param int $id id of the row to update
	 * @param array $data The data to be inserted to the db - passed to the parent insert method.
	 * @return bool True or false whether it was updated
	 * @see BF_Model::update()
	 */
	public function update($id, $data)
	{
		if (isset($data['groups']))
		{
			if (!empty($data['groups']) && is_array($data['groups']))
			{
				if (sizeof($data['groups']) > 1)
				{
					$data['nav_group_id'] = 0;
				} else {
					$data['nav_group_id'] = $data['groups'][0];
				}
				
				$data['groups'] = json_encode($data['groups']);
			
			} else {
				$data['groups'] = json_encode(array());
			}
		}
		
		return parent::update($id, $data);
	}
	
	/**
	 * Overrides the find all method to transform json encoded data into an array automatically
	 * 
	 * @see BF_Model::find_all()
	 */
	public function find_all($show_deleted=false)
	{
		$data = parent::find_all($show_deleted);
		
		foreach($data as $key=>$row)
		{
			if(is_null($row->groups))
			{
				$data[$key]->groups = array($row->nav_group_id);
			}
			else $data[$key]->groups = json_decode($row->groups);
		}
		
		return $data;
	}
	
	public function find($id)
	{
		$row = parent::find($id);
		
		if(is_null($row->groups))
		{
			$row->groups = array($row->nav_group_id);
		}
		else $row->groups = json_decode($row->groups);
		
		return $row;
	}
	
	/**
	 * Load a group
	 * 
	 * @access public
	 * @param string $abbrev The group abbrevation
	 * @return mixed
	 */
	public function load_group($nav_group_id)
	{

		//$this->db->where(array('nav_group_id' => $nav_group_id, 'parent_id' => "0"));
		//$group_links = $this->navigation_model->order_by('position, title')->find_all();
		
		$group_links = $this->get_group_links_persist($nav_group_id, true);
		
		$has_current_link = false;
			
		// Loop through all links and add a "current_link" property to show if it is active
		if( ! empty($group_links) )
		{
			foreach($group_links as &$link)
			{
				$full_match 	= site_url($this->uri->uri_string()) == $link->url;
				$segment1_match = site_url($this->uri->rsegment(1, '')) == $link->url;
				
				// Either the whole URI matches, or the first segment matches
				if($link->current_link = $full_match || $segment1_match)
				{
					$has_current_link = true;
				}
				
				//build a multidimensional array for submenus
				if($link->has_kids > 0 AND $link->parent_id == 0)
				{
					$link->children = $this->get_children($link->nav_id);
					
					foreach($link->children as $key => $child)
					{
						//what is this world coming to?
						if($child->has_kids > 0)
						{
							$link->children[$key]->children = $this->get_children($child->nav_id);
							
							foreach($link->children[$key]->children as $index => $item)
							{
								if($item->has_kids > 0)
								{
									$link->children[$key]->children[$index]->children = $this->get_children($item->nav_id);
								}
							}
						}
					}
				}
			}
			
		}

		// Assign it 
	    return $group_links;
	}
	
	/**
	 * Gets only the links that belong to a group
	 * Stores all the links in a private variable so that multiple calls for multiple groups wont need to make multiple db queries.
	 * 
	 * @param int $group_id group id of which the links belong to
	 * @param bool $only_top_level Optionally set to true to return only links without children. otherwise all.
	 * @return array the links from the persistant variable that belong to the group.
	 * @author Chris Churchwell
	 */
	private function get_group_links_persist($group_id, $only_top_level=false)
	{
		if (is_null($this->persist_items))
		{
			$this->persist_items = $this->navigation_model->order_by('position, title')->find_all();
		}
		
		$group_items = array();
		
		foreach($this->persist_items as $k=>$r)
		{
			if ($only_top_level === true && $r->parent_id != 0) continue;
			
			if (in_array($group_id, $r->groups))
			{
				$group_items[] = $r;
			}
		}
		
		return $group_items;
	}
	
	/**
	 * Get children
	 *
	 * @access public
	 * @param integer $id Parent ID of which the items returned should belong to
	 * @return mixed
	 */
	public function get_children($id)
	{
		if (is_null($this->persist_items))
		{
			$this->persist_items = $this->navigation_model->order_by('position, title')->find_all();
		}
		
		$children = array();
		
		foreach($this->persist_items as $r)
		{
			if ($r->parent_id == $id) $children[] = $r;
		}
		
		return $children;
	}

	/**
	 * Update the current link's parent
	 * 
	 * @access public
	 * @param int $id        The ID of the link item
	 * @param int $parent_id ID of the parent
	 * @return void
	 */
	public function update_parent($id = 0, $parent_id = 0) 
	{
		if($parent_id == 0)
		{
			// if they're trying to clear the parent selection we need to get the parent's id
			$current = $this->db->get_where('navigation', array('nav_id' => $id))->row();

			// check if the parent has more than one kid
			$siblings = $this->get_children($current->parent_id);
			if (count($siblings) == 1)
			{
				//mark that it has no children
				$this->db->update('navigation', array('has_kids' => 0), array('nav_id' => $current->parent_id));
			}
		}
		else
		{
			$this->db->update('navigation', array('has_kids' => 1), array('nav_id' => $parent_id));
		}
		
		return $this->db->update('navigation', array('parent_id' => $parent_id), array('nav_id' => $id));
	}

	/**
	 * Remove the parent id from kids
	 * 
	 * @access public
	 * @param int $id        The ID of the link item
	 * @param int $parent_id ID of the parent
	 * @return void
	 */
	public function un_parent_kids($id) 
	{
		if($id != 0)
		{
			// check if the parent has more than one kid
			$children = $this->get_children($id);
			foreach ($children as $child)
			{
				$this->db->update('navigation', array('parent_id' => 0), array('nav_id' => $child->nav_id));
			}
		}
	}
}
