<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Rating Plugin
 *
 * Allow registered users to vote an item up or down
 *
 * @package		PyroCMS
 * @author		Jerel Unruh = PyroCMS Dev Team
 * @copyright	Copyright (c) 2009 - 2012, Jerel Unruh
 * @license		Apache 2.0
 *
 */
class Plugin_Ratings extends Plugin
{

	/**
	 * Install
	 *
	 * Usage:
	 *	{{ ratings:install }}
	 *
	 *	Before you can use {{ ratings:vote }} you must place this tag into your page content 
	 * and refresh the page. This will create the needed SQL and you may then remove this 
	 * tag and insert the permanent {{ ratings:vote }} code from the example below
	 *
	 * @param	array
	 * @return	array
	 */
	public function install()
	{
		if ( ! $this->db->table_exists('ratings'))
		{

			$this->db->query("CREATE TABLE IF NOT EXISTS ".$this->db->dbprefix('ratings')." (
				`id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
				`user_id` INT(11) NOT NULL DEFAULT 0,
				`module` VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT  '',
				`module_item_id` INT(11) NOT NULL DEFAULT 0,
				`voted` INT(2) NOT NULL DEFAULT 0,
				`ip_address` VARCHAR(39) NOT NULL DEFAULT  '',
				`created_on` INT(11) NOT NULL DEFAULT 0,
				INDEX (`module_item_id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Stores rating votes';
			");

			$this->session->set_flashdata('success', 'The database table for Ratings has been created. You may now remove the install code and insert the rating code');
			redirect(current_url());
		}
	}

	/**
	 * Ratings
	 *
	 * Usage:
	 *	{{ ratings:vote steps="5" module="pages" item-id=page:id }}
	 *		<a href="{{ url }}">{{ theme:image file="{{ status }}.jpg" alt="vote {{item}}" }}</a>
	 *	{{ /ratings:vote }}
	 *
	 *	The module attribute is optional and will be determined automatically if omitted.
	 *	The steps attribute is optional and defaults to 5 stars, thumbs, etc.
	 *	Status will either be "on" or "off". In the example above you would need on.jpg and off.jpg in your theme.
	 *
	 * @param	array
	 * @return	array
	 */
	public function vote()
	{

		$steps 		= $this->attribute('steps', 5);
		$item_id 	= (int) $this->attribute('item-id', 0);
		$module 	= $this->attribute('module', $this->module);
		$vote 		= $this->input->get('vote');

		// can't do nothin if ya'll ain't a member
		if ( ! $this->current_user AND $vote)
		{
			$this->session->set_flashdata('notice', 'You must be logged in to place your vote');
			redirect('users/login/'.$this->uri->uri_string());
		}

		// if they're trying to vote then hand it over to that method
		if ( ! empty($vote) AND $vote <= $steps)
		{
			$this->_cast_vote($module, $item_id, $vote);
		}

		return $this->_build_results($module, $item_id, $steps);
	}

	public function _cast_vote($module, $item_id, $vote)
	{
		// see if this user has ever voted on this item before
		$votes = $this->db->where('user_id', $this->current_user->id)
			->where('module_item_id', $item_id)
			->where('module', $module)
			->get('ratings')
			->row();

		$data = array('user_id' => $this->current_user->id,
					  'module' => $module,
					  'module_item_id' => $item_id,
					  'voted' => $vote,
					  'ip_address' => $this->input->ip_address,
					  'created_on' => now()
					  );

		// no sense in updating it if they aren't changing their vote
		if ( ! $votes OR $votes->voted != $vote)
		{
			if (count($votes) > 0)
			{
				// they've already voted but now they've changed their minds
				$this->db->update('ratings', $data, 'id = '.$votes->id);
				$this->session->set_flashdata('success', 'Your vote has been updated!');
				redirect(current_url());
			}
			else
			{
				// voting for the first time
				$this->db->insert('ratings', $data);
				$this->session->set_flashdata('success', 'Thank you! Your vote has been recorded.');
				redirect(current_url());
			}
		}
	}

	public function _build_results($module, $item_id, $steps)
	{
		$i 			= 1;
		$status 	= 'on';
		$output 	= array();
		$ratings 	= array(0);
		$total 		= 0;

		$results = $this->db->query("SELECT voted, COUNT(*) AS count 
									 FROM ".$this->db->dbprefix('ratings')." 
									 WHERE module_item_id = ".$item_id." 
									 AND module = '".$module."' 
									 GROUP BY voted 
									 ORDER BY count"
									 )->result();

		if ($results)
		{
			foreach ($results AS $item)
			{
				$ratings[$item->voted] = $item->count;
				$total += $item->count;
			}
		}

		// this is the maximum votes that any item has received
		$max = max($ratings);

		// keep the first star from showing if no votes have been cast
		if ($max < 1) $status = 'off';
		
		while ($i <= $steps) 
		{
			$output[$i]['item'] 	= $i;
			$output[$i]['url'] 		= current_url().'?vote='.$i;
			$output[$i]['count'] 	= isset($ratings[$i]) ? $ratings[$i] : 0;
			$output[$i]['status'] 	= $status;
			$output[$i]['total']	= $total;

			if (isset($ratings[$i]) AND $ratings[$i] == $max OR $max == 0)
			{
				$status = 'off';
			}

			$i++;
		}

		return $output;
	}
}

/* End of file example.php */