<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Api extends CI_Controller {
	function __construct() {
		parent::__construct();
	
		//Load our buddies:
		$this->output->enable_profiler(FALSE);
		
		if(!auth_admin(1)){
			//Check for session for all functions here!
			echo_html(0,'Invalid session! Login and try again...');
			exit;
		}
	}
	
	function create_node(){
		
		//Make sure all inputs are find:
		if(intval($_REQUEST['grandpa_id'])<1 || intval($_REQUEST['parent_id'])<1 || strlen($_REQUEST['value'])<1 || strlen($_REQUEST['ui_rank'])<1){
			return echo_html(0,'Invalid inputs.');
		}
		
		//We're good! Insert new link:
		$user_data = $this->session->userdata('user');
		$next_node_id = next_node_id();
		$update_id = $this->Us_model->insert_link(array(
			'us_id' => $user_data['node_id'],
			'timestamp' => date("Y-m-d H:i:s"),
			'status' => 1, //TODO: Consider "0" for non-admins
			'node_id' => $next_node_id,
			'grandpa_id' => intval($_REQUEST['grandpa_id']),
			'parent_id' =>  intval($_REQUEST['parent_id']),
			'value' => trim($_REQUEST['value']),
			'ui_rank' => intval($_REQUEST['ui_rank']),
			'action_type' => 1, //For adding
		));
		
		if(!$update_id){
			//Ooops, some unknown error:
			return echo_html(0,'Unknown Error while adding node.');
		}
		
		
		
		//Return results as a new line:
		$child_link = $this->Us_model->fetch_node($next_node_id);
		echo print_child( $child_link[0] , array(
			'node_id' => intval($_REQUEST['parent_id']),
			'grandpa_id' => intval($_REQUEST['grandpa_id']),
		) , $user_data);
		
		
		//TODO: Update Algolia Search index
		$this->add_algolia_obj($next_node_id);
	}
	
	
	function link_node(){
		
		//Make sure all inputs are find:
		if(intval($_REQUEST['grandpa_id'])<1 || intval($_REQUEST['parent_id'])<1 || intval($_REQUEST['child_node_id'])<1 || strlen($_REQUEST['ui_rank'])<1){
			return echo_html(0,'Invalid inputs.');
		}
		
		//We're good! Insert new link:
		$has_value = (strlen(trim($_REQUEST['value']))>0);
		$user_data = $this->session->userdata('user');
		$next_node_id = next_node_id();
		$update_id = $this->Us_model->insert_link(array(
			'us_id' => $user_data['node_id'],
			'timestamp' => date("Y-m-d H:i:s"),
			'status' => ( $has_value ? 2 : 3), //TODO: Consider "0" for non-admins
			'node_id' => intval($_REQUEST['child_node_id']),
			'grandpa_id' => intval($_REQUEST['grandpa_id']),
			'parent_id' =>  intval($_REQUEST['parent_id']),
			'value' => ( $has_value ? trim($_REQUEST['value']) : null),
			'ui_rank' => intval($_REQUEST['ui_rank']),
			'action_type' => 4, //For linking
		));
		
		if(!$update_id){
			//Ooops, some unknown error:
			return echo_html(0,'Unknown Error while linking nodes.');
		}
		
		//Do we need to update search index?
		if($has_value){
			//TODO: Update Algolia Search index
		}
		
		//Return results as a new line:
		$child_link = $this->Us_model->fetch_node(intval($_REQUEST['child_node_id']));
		echo print_child( $child_link[0] , array(
				'node_id' => intval($_REQUEST['parent_id']),
				'grandpa_id' => intval($_REQUEST['grandpa_id']),
		) , $user_data);
	}
	
	
	
	
	function delete(){
		
		//Make sure all inputs are find:
		if(intval($_REQUEST['parent_id'])<1 || intval($_REQUEST['node_id'])<1 || intval($_REQUEST['id'])<1 || intval($_REQUEST['type'])<-4 || intval($_REQUEST['type'])>=0){
			return echo_html(0,'Invalid inputs.');
		}
		
		
		
		//TODO: Update Algolia Search index
		
		
		//Start deleting:
		if($_REQUEST['type']==-1){
			
			//Simple link delete:
			$status = $this->Us_model->delete_link(intval($_REQUEST['id']),intval($_REQUEST['type']));
			return echo_html($status,($status ? 'Link deleted.' : 'Unknown error.'));
			
		} else {
			
			//This is the deletion of the entire node!
			//Set session variable to show confirmation on redirect:
			$del_message = '<b>'.$_REQUEST['node_name'].'</b> was deleted';
			
			if($_REQUEST['type']==-3){
				
				//Move these nodes to $_REQUEST['parent_id']
				$moved_children = $this->Us_model->move_child_nodes(intval($_REQUEST['node_id']),intval($_REQUEST['parent_id']),intval($_REQUEST['type']));
				$del_message .= ' and '.$moved_children.' children have been moved here';
				
			} elseif($_REQUEST['type']==-4){
				
				//Recursively delete all children/grandchildren
				$deleted_children = $this->Us_model->recursive_node_delete(intval($_REQUEST['node_id']),intval($_REQUEST['type']));
				$del_message .= ' along with '.$deleted_children.' children/grandchildren';
				
				//Reindex search:
				$this->update_algolia();
				
			}
			
			//Main node delete:
			$status = $this->Us_model->delete_node(intval($_REQUEST['node_id']),intval($_REQUEST['type']));
			
			//Set header message for after redirect:
			$this->session->set_flashdata('hm', '<div class="alert alert-success" role="alert">'.$del_message.'.</div>');
			echo $status;
		}
	}
	
	function update_link(){
		if(intval($_REQUEST['id'])<1){
			return echo_html(0,'Invalid link ID.');
		} elseif(!intval($_REQUEST['key']) && strlen($_REQUEST['new_value'])<1){
			return echo_html(0,'You must enter a value for the top link.');
		}
		
		//Fetch link:
		$link = $this->Us_model->fetch_link(intval($_REQUEST['id']));
		$p_update = (intval($_REQUEST['new_parent_id'])>0);
		
		if($link['status']<0){
			return echo_html(0,'You can edit active links only (status>0).');
		} elseif($_REQUEST['new_value']==$link['value'] && !$p_update){
			//Nothing has changed!
			return echo_html(0,'You did not make any changes.');
		}
		
		//We're good! Insert new link:
		$user_data = $this->session->userdata('user');
		$timestamp = date("Y-m-d H:i:s"); //Make sure all action in this batch have the same time
		
		if($p_update){
			//Fetch parent details:
			$parent_node = $this->Us_model->fetch_node(intval($_REQUEST['new_parent_id']), 'fetch_top_plain');
			
			//Did we find this?
			if($parent_node['node_id']<1 || $parent_node['node_id']!=intval($_REQUEST['new_parent_id'])){
				return echo_html(0,'Invalid parent.');
			}
		}
		
		//Valid, insert new row:
		$update_id = $this->Us_model->insert_link(array(
			'us_id' => $user_data['node_id'],
			'timestamp' => $timestamp,
			'status' => ( intval($_REQUEST['key']) ? ( strlen($_REQUEST['new_value'])>0 ? 2 : 3 ) : 1 ), //TODO: Consider "0" for non-admins
			'node_id' => $link['node_id'],
			'grandpa_id' => ( $p_update ? $parent_node['grandpa_id'] : $link['grandpa_id']),
			'parent_id' =>  ( $p_update ? $parent_node['node_id'] : $link['parent_id']),
			'value' => ( strlen($_REQUEST['new_value'])>0 ? $_REQUEST['new_value'] : null),
			'update_id' => $link['id'],
			'ui_rank' => $link['ui_rank'],
			'correlation' => $link['correlation'],
			'action_type' => 2, //For updating
		));
		
		if(!$update_id){
			//Ooops, some unknown error:
			return echo_html(0,'Unknown Error while saving changes.');
		}
		
		//TODO: Update Algolia Search index
		
		
		//Then remove old one:
		$affected_rows = $this->Us_model->update_link($link['id'], array(
			'update_id' => $update_id,
			'status' => -1, //-1 is for updated items.
		));
		
		
		if(!$affected_rows){
			//Ooops, some unknown error:
			return echo_html(0,'Unknown Error while removing old data.');
		} else {
			//All good!
			echo_html(1,'Saved!');
		}
	}
	
	function update_sort(){
		
		if(intval($_REQUEST['node_id'])<1 || !is_array($_REQUEST['new_sort'])){
			//We start with a DIV to prevent errors from being hidden after a few seconds:
			return echo_html(0,'Invalid Input.');
		}
		
		//Awesome Sauce! lets move-on...
		//Fetch child links to update:
		$user_data = $this->session->userdata('user');
		$timestamp = date("Y-m-d H:i:s"); //Make sure all action in this batch have the same time
		$child_links = $this->Us_model->fetch_node(intval($_REQUEST['node_id']), 'fetch_children');
		
		//Inverse key
		$new_sort_index = array();
		foreach($_REQUEST['new_sort'] as $key=>$value){
			$new_sort_index[$value] = $key+1;
		}
		
		
		$success = 0;
		foreach($child_links as $link){
			
			if(!array_key_exists($link['node_id'],$new_sort_index)){
				//Ooops, some unknown error:
				return echo_html(0,'Unknown sort index.');
			}
			
			
			//Valid, insert new row:
			$update_id = $this->Us_model->insert_link(array(
				'us_id' => $user_data['node_id'],
				'timestamp' => $timestamp,
				'status' => $link['status'],
				'node_id' => $link['node_id'],
				'grandpa_id' => $link['grandpa_id'],
				'parent_id' => $link['parent_id'],
				'value' => $link['value'],
				'update_id' => $link['id'],
				'ui_rank' => $new_sort_index[$link['node_id']],
				'correlation' => $link['correlation'],
				'action_type' => 3, //For sorting
			));
			
			if(!$update_id){
				//Ooops, some unknown error:
				return echo_html(0,'Unknown Error.');
			}
			
			//Then remove old one:
			$affected_rows = $this->Us_model->update_link($link['id'], array(
				'update_id' => $update_id,
				'status' => -1, //-1 is for updated items.
			));
			
			if(!$affected_rows){
				//Ooops, some unknown error:
				return echo_html(0,'Unknown Error.');
			} else {
				//All good!
				$success++;
			}
		}
		
		echo_html(1,'Saved!');
	}
	
	function add_algolia_obj($node_id){
		if(!is_production()){ return false; }
		$return = array();
		array_push($return,$this->generate_algolia_obj($node_id));
		$index = load_algolia();
		$index->addObjects(json_decode(json_encode($return), FALSE));
	}
	
	function generate_algolia_obj($node_id){
		if(!is_production()){ return false; }
		//Fetch node:
		$node = $this->Us_model->fetch_node($node_id);
		//CLeanup and prep for search indexing:
		foreach($node as $i=>$link){
			if($i==0){
				//This is the primary link!
				//Lets append some core info:
				$node_search_object = array(
						'node_id' => $link['node_id'],
						'grandpa_id' => $link['grandpa_id'],
						'parent_id' => $link['parent_id'],
						'value' => $link['value'],
						'links_blob' => '',
				);
			} elseif(strlen($link['value'])>0){
				//This is a secondary link with a value attached to it
				//Lets add this to the links blob
				$node_search_object['links_blob'] .= $link['value'].' ';
			}
		}
		return $node_search_object;
	}
	
	//Run this to completely update the "nodes" index
	function update_algolia(){
		
		if(!is_production()){ return false; }
		
		//Buildup this array to save to search index
		$return = array();
		
		//Fetch all nodes:
		$active_node_ids = $this->Us_model->fetch_node_ids();
		foreach($active_node_ids as $node_id){
			//Add to main array
			array_push($return,$this->generate_algolia_obj($node_id));
		}
			
		//print_r($return);exit;
		$obj = json_decode(json_encode($return), FALSE);
		//print_r($obj);
		
		//Include PHP library:
		$index = load_algolia();
		$index->clearIndex();
		$index->addObjects($obj);
	}
	
}

