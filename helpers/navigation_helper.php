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

/*
	File: Navigation Helper
	
	Provides various helper functions when working with displaying the page navigation.
*/


if (!function_exists('show_navigation'))
{

	/**
	 * Returns the navigation html for a given navigation group
	 * 
	 * @param string  $abbrev        Abbreviated name for the navigation group.
	 * @param boolean $show_children Whether to show the child menu items or not.
	 * @param array   $attributes    Array of attributes - used for id and class athe moment.
	 * @param string  $selected      Menu option to set as the current
	 * 
	 * @return boolean A string with the full html for the navigation items. 
	 */
	function show_navigation($abbrev, $show_children = TRUE, $attributes=array(), $selected = '')
	{
		$ci =& get_instance();

		if ($ci->db->table_exists('navigation_group')) {
			$query = $ci->db->select('nav_group_id')->where('abbr',$abbrev)->get('navigation_group');

			if (!$query || $query->num_rows() == 0)
			{
				return;
			}

			$group_details = $query->result();

			$ci->load->model('navigation/navigation_model');

			$group_links = $ci->navigation_model->load_group($group_details[0]->nav_group_id);

			list($output, $cur) = show_level($group_links, TRUE, $show_children, $attributes, $selected);

			return $output;
		}
		else {
			return false;
		}
	}
}

if (!function_exists('show_level'))
{
	/**
	 * Returns the navigation html for a given navigation level and is used recursively.
	 * 
	 * @param array   $links         Array of links to display.
	 * @param boolean $top           Whether this is the top level or not.
	 * @param boolean $show_children Whether to show the child menu items or not.
	 * @param array   $attributes    Array of attributes - used for id and class, active class and wrapper at the moment. Set wrap to true inside of attributes to output tags wrapped in spans
	 * @param string  $selected      Menu option to set as the current
	 * 
	 * @return type A string with the full html for the navigation items. 
	 */
	function show_level($links, $top=FALSE, $show_children=TRUE, $attributes=array(), $selected = '')
	{
		$has_current = FALSE;

		$wrap        = ( isset( $attributes['wrap'] ) && ( $attributes['wrap'] == true ) ) ? true : false;
		$act_class   = isset ( $attributes['active'] ) ? $attributes['active'] : 'current';
		$output      = '<ul';

		if ($top)
		{
			$output .= empty($attributes['id']) ? '' : ' id="'.$attributes['id'].'"';
			$output .= empty($attributes['class']) ? '' : ' class="'.$attributes['class'].'"';
		}
		$output .= '>';
		
		foreach ($links as $link)
		{
			$child_html = '';
			$child_current = FALSE;
			$attributes = array();

			if ($show_children && !empty($link->children) AND is_array($link->children) AND count($link->children))
			{
				list($child_html, $child_current) = show_level($link->children, FALSE);
				
			}

			if ($selected == $link->url || "/".trim(uri_string(), '/') == $link->url || current_url() == $link->url || $child_current)
			{
				$attributes['class'] = $act_class;
				$has_current = TRUE;
			}

			$output .= "<li";
			$output .= !empty($attributes['class']) ? ' class="'.$attributes['class'].'"' : '';
			
			//check for full urls
			if (FALSE === strpos($link->url, 'http'))
			{
				// allow for relative paths
				$ltitle  = ( $wrap == true ) ? '<span>' . $link->title . '</span>' : $link->title;
				$output .= ">".anchor(site_url($link->url), $ltitle, $attributes);
			}
			else
			{
				$ltitle  = ( $wrap == true ) ? '<span>' . $link->title . '</span>' : $link->title;
				$output .= ">".anchor($link->url, $ltitle, $attributes);
			}
			
			$output .= $child_html;
			$output .= "</li>" . PHP_EOL;
		}
		$output .= "</ul>" . PHP_EOL;
		
		return array($output, $has_current);
	}
}