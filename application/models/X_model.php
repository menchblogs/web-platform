<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
class X_model extends CI_Model
{

    /*
     *
     * Member related database functions
     *
     * */

    function __construct()
    {
        parent::__construct();
    }


    function create($add_fields, $external_sync = false)
    {

        //Set some defaults:
        if (!isset($add_fields['x__creator']) || intval($add_fields['x__creator']) < 1) {
            $add_fields['x__creator'] = 14068; //GUEST MEMBER
        }

        //Only require transaction type:
        if (detect_missing_columns($add_fields, array('x__type'), $add_fields['x__creator'])) {
            return false;
        }

        if(!in_array($add_fields['x__type'], $this->config->item('n___4593'))){
            $this->X_model->create(array(
                'x__message' => 'x->create() failed to create because of invalid transaction type @'.$add_fields['x__type'],
                'x__type' => 4246, //Platform Bug Reports
                'x__creator' => $add_fields['x__creator'],
                'x__metadata' => $add_fields,
            ));
            return false;
        }

        //Clean metadata is provided:
        if (isset($add_fields['x__metadata']) && is_array($add_fields['x__metadata'])) {
            $add_fields['x__metadata'] = serialize($add_fields['x__metadata']);
        } else {
            $add_fields['x__metadata'] = null;
        }

        //Set some defaults:
        if (!isset($add_fields['x__message'])) {
            $add_fields['x__message'] = null;
        }

        //Set some defaults:
        if (!isset($add_fields['x__website']) || $add_fields['x__website']<1) {
            $add_fields['x__website'] = website_setting(0, $add_fields['x__creator']);
        }


        if (!isset($add_fields['x__time']) || is_null($add_fields['x__time'])) {
            //Time with milliseconds:
            $t = microtime(true);
            $micro = sprintf("%06d", ($t - floor($t)) * 1000000);
            $d = new DateTime(date('Y-m-d H:i:s.' . $micro, $t));
            $add_fields['x__time'] = $d->format("Y-m-d H:i:s.u");
        }

        if (!isset($add_fields['x__privacy'])|| is_null($add_fields['x__privacy'])) {
            $add_fields['x__privacy'] = 6176; //Transaction Published
        }

        //Set some zero defaults if not set:
        foreach(array('x__right', 'x__left', 'x__down', 'x__up', 'x__reference', 'x__weight') as $dz) {
            if (!isset($add_fields[$dz])) {
                $add_fields[$dz] = 0;
            }
        }

        //Lets log:
        $this->db->insert('table__x', $add_fields);


        //Fetch inserted id:
        $add_fields['x__id'] = $this->db->insert_id();


        //All good huh?
        if ($add_fields['x__id'] < 1) {

            //This should not happen:
            $this->X_model->create(array(
                'x__type' => 4246, //Platform Bug Reports
                'x__creator' => $add_fields['x__creator'],
                'x__message' => 'create() Failed to create',
                'x__metadata' => array(
                    'input' => $add_fields,
                ),
            ));

            return false;
        }

        //Sync algolia?
        if ($external_sync) {
            if ($add_fields['x__up'] > 0) {
                flag_for_search_indexing(12274, $add_fields['x__up']);
            }

            if ($add_fields['x__down'] > 0) {
                flag_for_search_indexing(12274, $add_fields['x__down']);
            }

            if ($add_fields['x__left'] > 0) {
                flag_for_search_indexing(12273, $add_fields['x__left']);
            }

            if ($add_fields['x__right'] > 0) {
                flag_for_search_indexing(12273, $add_fields['x__right']);
            }
        }


        //See if this transaction type has any followers that are essentially subscribed to it:
        $tr_watchers = $this->E_model->fetch_recursive(42381, $add_fields['x__type'], $this->config->item('n___30820'), array(), 1);
        if(is_array($tr_watchers) && count($tr_watchers)){

            //yes, start drafting email to be sent to them
            $u_name = 'Unknown';
            if($add_fields['x__creator'] > 0){
                //Fetch member details:
                $add_e = $this->E_model->fetch(array(
                    'e__id' => $add_fields['x__creator'],
                ));
                if(count($add_e)){
                    $u_name = $add_e[0]['e__title'];
                }
            }


            //Email Subject:
            $e___4593 = $this->config->item('e___4593'); //Transaction Types
            $subject = 'Notification: '  . $u_name . ' ' . $e___4593[$add_fields['x__type']]['m__title'];

            //Compose email body, start with transaction content:
            $html_message = ( strlen($add_fields['x__message']) > 0 ? $add_fields['x__message'] : '') . "\n";

            $e___32088 = $this->config->item('e___32088'); //Platform Variables

            //Append transaction object transactions:
            foreach($this->config->item('e___4341') as $e__id => $m) {

                if (in_array(6202 , $m['m__following'])) {

                    //IDEA
                    foreach($this->I_model->fetch(array( 'i__id' => $add_fields[$e___32088[$e__id]['m__message']] )) as $this_i){
                        $html_message .= $m['m__title'] . ': '.view_i_title($this_i, true).':'."\n".$this->config->item('base_url').'/~' . $this_i['i__hashtag']."\n\n";
                    }

                } elseif (in_array(6160 , $m['m__following'])) {

                    //SOURCE
                    foreach($this->E_model->fetch(array( 'e__id' => $add_fields[$e___32088[$e__id]['m__message']] )) as $this_e){
                        $html_message .= $m['m__title'] . ': '.$this_e['e__title']."\n".$this->config->item('base_url').'/@' . $this_e['e__handle'] . "\n\n";
                    }

                } elseif (in_array(4367 , $m['m__following'])) {

                    //DISCOVERY
                    $html_message .= $m['m__title'] . ':'."\n".$this->config->item('base_url').view_app_link(12722).'?x__id=' . $add_fields[$e___32088[$e__id]['m__message']]."\n\n";

                }

            }

            //Finally append DISCOVERY ID:
            $html_message .= 'TRANSACTION: #'.$add_fields['x__id']."\n".$this->config->item('base_url').view_app_link(12722).'?x__id=' . $add_fields['x__id']."\n\n";

            //Send to all Watchers:
            foreach($tr_watchers as $tr_watcher) {
                //Do not inform the member who just took the action:
                if($tr_watcher['e__id']!=$add_fields['x__creator'] || 1){
                    $this->X_model->send_dm($tr_watcher['e__id'], $subject, $html_message, array(
                        'x__reference' => $add_fields['x__id'], //Save transaction
                        'x__right' => $add_fields['x__right'],
                        'x__left' => $add_fields['x__left'],
                        'x__down' => $add_fields['x__down'],
                        'x__up' => $add_fields['x__up'],
                    ));
                }
            }
        }

        //Return:
        return $add_fields;

    }

    function fetch($query_filters = array(), $joins_objects = array(), $limit = 100, $limit_offset = 0, $order_columns = array('x__id' => 'DESC'), $select = '*', $group_by = null)
    {

        $this->db->select($select);
        $this->db->from('table__x');

        //IDA JOIN?
        if (in_array('x__left', $joins_objects)) {
            $this->db->join('table__i', 'x__left=i__id','left');
        } elseif (in_array('x__right', $joins_objects)) {
            $this->db->join('table__i', 'x__right=i__id','left');
        }

        //SOURCE JOIN?
        if (in_array('x__up', $joins_objects)) {
            $this->db->join('table__e', 'x__up=e__id','left');
        } elseif (in_array('x__down', $joins_objects)) {
            $this->db->join('table__e', 'x__down=e__id','left');
        } elseif (in_array('x__type', $joins_objects)) {
            $this->db->join('table__e', 'x__type=e__id','left');
        } elseif (in_array('x__creator', $joins_objects)) {
            $this->db->join('table__e', 'x__creator=e__id','left');
        }

        foreach($query_filters as $key => $value) {
            if (!is_null($value)) {
                $this->db->where($key, $value);
            } else {
                $this->db->where($key);
            }
        }

        if ($group_by) {
            $this->db->group_by($group_by);
        }

        foreach($order_columns as $key => $value) {
            $this->db->order_by($key, $value);
        }

        if ($limit > 0) {
            $this->db->limit($limit, $limit_offset);
        }
        $q = $this->db->get();
        return $q->result_array();
    }

    function update($id, $update_columns, $x__creator = 0, $x__type = 0, $x__message = '')
    {

        $id = intval($id);
        if (count($update_columns)==0) {
            return false;
        } elseif ($x__type>0 && !in_array($x__type, $this->config->item('n___4593'))) {
            $this->X_model->create(array(
                'x__message' => 'x->update() failed to update because of invalid transaction type @'.$x__type,
                'x__type' => 4246, //Platform Bug Reports
                'x__creator' => $x__creator,
                'x__metadata' => $update_columns,
            ));
            return false;
        }

        //Fetch transaction before updating:
        $before_data = $this->X_model->fetch(array(
            'x__id' => $id,
        ));

        //Update metadata if needed:
        if(isset($update_columns['x__metadata']) && is_array($update_columns['x__metadata'])){
            //Merge this update into existing metadata:
            if(strlen($before_data[0]['x__metadata'])){

                //We have something, merge:
                $x__metadata = unserialize($before_data[0]['x__metadata']);
                $merged_array = array_merge($x__metadata, $update_columns['x__metadata']);
                $update_columns['x__metadata'] = serialize($merged_array);

            } else {
                //We have nothing, insert entire thing:
                $update_columns['x__metadata'] = serialize($update_columns['x__metadata']);
            }
        }

        //Set content to null if defined as empty:
        if(isset($update_columns['x__message']) && !strlen($update_columns['x__message'])){
            $update_columns['x__message'] = null;
        }

        //Update:
        $this->db->where('x__id', $id);
        $this->db->update('table__x', $update_columns);
        $affected_rows = $this->db->affected_rows();

        //Log changes if successful:
        if ($affected_rows > 0 && $x__creator > 0 && $x__type > 0) {

            if(strlen($x__message)==0){
                //Log modification transaction for every field changed:
                foreach($update_columns as $key => $value) {

                    if($before_data[0][$key]==$value){
                        continue;
                    }

                    //Now determine what type is this:
                    if($key=='x__privacy'){

                        $e___6186 = $this->config->item('e___6186'); //Transaction Status
                        $x__message .= view_db_field($key) . ' updated from [' . $e___6186[$before_data[0][$key]]['m__title'] . '] to [' . $e___6186[$value]['m__title'] . ']'."\n";

                    } elseif($key=='x__type'){

                        $e___4593 = $this->config->item('e___4593'); //Transaction Types
                        $x__message .= view_db_field($key) . ' updated from [' . $e___4593[$before_data[0][$key]]['m__title'] . '] to [' . $e___4593[$value]['m__title'] . ']'."\n";

                    } elseif(in_array($key, array('x__up', 'x__down'))) {

                        //Fetch new/old source names:
                        $befores = $this->E_model->fetch(array(
                            'e__id' => $before_data[0][$key],
                        ));
                        $after_e = $this->E_model->fetch(array(
                            'e__id' => $value,
                        ));

                        $x__message .= view_db_field($key) . ' updated from [' . $befores[0]['e__title'] . '] to [' . $after_e[0]['e__title'] . ']' . "\n";

                    } elseif(in_array($key, array('x__left', 'x__right'))) {

                        //Fetch new/old Idea outcomes:
                        $before_i = $this->I_model->fetch(array(
                            'i__id' => $before_data[0][$key],
                        ));
                        $after_i = $this->I_model->fetch(array(
                            'i__id' => $value,
                        ));

                        $x__message .= view_db_field($key) . ' updated from [' . $before_i[0]['i__message'] . '] to [' . $after_i[0]['i__message'] . ']' . "\n";

                    } elseif(in_array($key, array('x__message', 'x__weight'))){

                        $x__message .= view_db_field($key) . ' updated from [' . $before_data[0][$key] . '] to [' . $value . ']'."\n";

                    } else {

                        //Should not log updates since not specifically programmed:
                        continue;

                    }
                }
            }

            //Determine fields that have changed:
            $fields_changed = array();
            foreach($update_columns as $key => $value) {
                if($before_data[0][$key]!=$value){
                    array_push($fields_changed, array(
                        'field' => $key,
                        'before' => $before_data[0][$key],
                        'after' => $value,
                    ));
                }
            }

            if(strlen($x__message) > 0 && count($fields_changed) > 0){
                //Value has changed, log transaction:
                $this->X_model->create(array(
                    'x__reference' => $id, //Transaction Reference
                    'x__creator' => $x__creator,
                    'x__type' => $x__type,
                    'x__message' => $x__message,
                    'x__metadata' => array(
                        'x__id' => $id,
                        'fields_changed' => $fields_changed,
                    ),
                    //Copy old values:
                    'x__up' => $before_data[0]['x__up'],
                    'x__down'  => $before_data[0]['x__down'],
                    'x__left' => $before_data[0]['x__left'],
                    'x__right'  => $before_data[0]['x__right'],
                ));
            }
        }

        return $affected_rows;
    }


    function update_select_single($focus_id, $o__id, $element_id, $new_e__id, $migrate_s__handle, $x__id = 0) {


        //Authenticate Member:
        $migrate_s__handle = ( substr($migrate_s__handle, 0, 1)=='@' ? trim(substr($migrate_s__handle, 1)) :  $migrate_s__handle);
        $member_e = superpower_unlocked();
        if (!$member_e) {
            return array(
                'status' => 0,
                'message' => view_unauthorized_message(),
            );
        } elseif (intval($o__id) < 1) {
            return array(
                'status' => 0,
                'message' => 'Missing Target ID',
            );
        } elseif (intval($element_id) < 1 || !count($this->config->item('n___'.$element_id))) {
            return array(
                'status' => 0,
                'message' => 'Invalid Variable ID ['.$element_id.']',
            );
        } elseif (intval($new_e__id) < 1 || !in_array($new_e__id, $this->config->item('n___'.$element_id))) {
            return array(
                'status' => 0,
                'message' => 'Invalid Value ID',
            );
        }


        //See if anything is being deleted:
        $auto_open_i_editor_modal = 0;
        $deletion_redirect = null;
        $delete_element = null;
        $links_removed = -1;
        $status = 0;

        if($element_id==4486 && $x__id > 0){

            //IDEA LINK TYPE
            $status = $this->X_model->update($x__id, array(
                'x__type' => $new_e__id,
            ), $member_e['e__id'], 13962);

        } elseif($element_id==13550 && $x__id > 0){

            //SOURCE LINK TYPE
            $status = $this->X_model->update($x__id, array(
                'x__type' => $new_e__id,
            ), $member_e['e__id'], 28799);

        } elseif($element_id==32292 && $x__id > 0){

            //SOURCE/SOURCE LINK
            $status = $this->X_model->update($x__id, array(
                'x__type' => $new_e__id,
            ), $member_e['e__id'], 28799);

        } elseif($element_id==6177){

            //SOURCE ACCESS

            //Delete?
            if(!in_array($new_e__id, $this->config->item('n___7358'))){

                //Determine what to do after deleted:
                if($o__id==$focus_id){

                    //Find Published Followings:
                    foreach($this->X_model->fetch(array(
                        'x__type IN (' . join(',', $this->config->item('n___32292')) . ')' => null, //SOURCE LINKS
                        'x__privacy IN (' . join(',', $this->config->item('n___7360')) . ')' => null, //ACTIVE
                        'e__privacy IN (' . join(',', $this->config->item('n___7358')) . ')' => null, //ACTIVE
                        'x__down' => $o__id,
                    ), array('x__up'), 1, 0, array('e__title' => 'DESC')) as $up_e) {
                        $deletion_redirect = '/@'.$up_e['e__handle'];
                    }

                    //If still not found, go to main page if no followings found:
                    if(!$deletion_redirect){
                        foreach($this->E_model->fetch(array('e__id' => $o__id)) as $e2){
                            $deletion_redirect = '/@'.e2['e__handle'];
                        }
                    }

                } else {

                    //Just delete from UI using JS:
                    $delete_element = '.s__12274_' . $o__id;

                }

                //Delete all transactions:
                $links_removed = $this->E_model->remove($o__id, $member_e['e__id'], $migrate_s__handle);

            }

            //Update:
            if(!strlen($migrate_s__handle) || count($this->E_model->fetch(array(
                    'LOWER(e__handle)' => strtolower($migrate_s__handle),
                    'e__privacy IN (' . join(',', $this->config->item('n___7358')) . ')' => null, //ACTIVE
                )))){
                $status = $this->E_model->update($o__id, array(
                    'e__privacy' => $new_e__id,
                ), true, $member_e['e__id']);
            }

            //Update Search Index:
            flag_for_search_indexing(12274,  $o__id);

        } elseif($element_id==31004){

            //IDEA ACCESS

            //Delete?
            if(!in_array($new_e__id, $this->config->item('n___31871'))){

                //Determine what to do after deleted:
                if($o__id==$focus_id){

                    //Find Published Followings:
                    foreach($this->X_model->fetch(array(
                        'x__privacy IN (' . join(',', $this->config->item('n___7359')) . ')' => null, //PUBLIC
                        'i__privacy IN (' . join(',', $this->config->item('n___31871')) . ')' => null, //ACTIVE
                        'x__type IN (' . join(',', $this->config->item('n___42268')) . ')' => null, //IDEA LINKS
                        'x__right' => $o__id,
                    ), array('x__left'), 1) as $previous_i) {
                        $deletion_redirect = '/~'.$previous_i['i__hashtag'];
                    }

                    //If not found, find active followings:
                    if(!$deletion_redirect){
                        foreach($this->X_model->fetch(array(
                            'x__privacy IN (' . join(',', $this->config->item('n___7360')) . ')' => null, //ACTIVE
                            'i__privacy IN (' . join(',', $this->config->item('n___31871')) . ')' => null, //ACTIVE
                            'x__type IN (' . join(',', $this->config->item('n___42268')) . ')' => null, //IDEA LINKS
                            'x__right' => $o__id,
                        ), array('x__left'), 1) as $previous_i) {
                            $deletion_redirect = '/~'.$previous_i['i__hashtag'];
                        }
                    }

                    //If still not found, go to main page if no followings found:
                    if(!$deletion_redirect){
                        foreach($this->I_model->fetch(array(
                            'i__id' => $o__id,
                        )) as $i){
                            $deletion_redirect = '/~'.$i['i__hashtag'];
                        }
                    }

                } else {

                    //Just delete from UI using JS:
                    $delete_element = '.s__12273_' . $o__id;

                }

                //Delete all transactions:
                $links_removed = $this->I_model->remove($o__id , $member_e['e__id'], $migrate_s__handle);

            }

            //Update Idea:
            $status = $this->I_model->update($o__id, array(
                'i__privacy' => $new_e__id,
            ), true, $member_e['e__id']);

            //Update Search Index:
            flag_for_search_indexing(12273,  $o__id);

        } elseif($element_id==4737){

            //IDEA TYPE
            $status = $this->I_model->update($o__id, array(
                'i__type' => $new_e__id,
            ), true, $member_e['e__id']);

            //See if we need to popup the idea edit modal here:

            $e___42179 = $this->config->item('e___42179'); //Dynamic Input Fields
            foreach(array_intersect($this->config->item('n___'.$new_e__id), $this->config->item('n___42179')) as $dynamic_e__id){

                //Let's determine the data type:
                $data_types = array_intersect($e___42179[$dynamic_e__id]['m__following'], $this->config->item('n___4592'));

                //ASSUME that we found 1 match as expected:
                foreach($data_types as $data_type_this){
                    $data_type = $data_type_this;
                    break;
                }
                $is_required = in_array($dynamic_e__id, $this->config->item('n___42174')); //Required Settings
                
                if(!$is_required){
                    //We are only interested in what is required
                    continue;
                }
                
                //See if we are missing value:
                if(in_array($data_type, $this->config->item('n___42188'))){

                    //Single or Multiple Choice:
                    $already_responded = count($this->X_model->fetch(array(
                        'x__up IN (' . join(',', $this->config->item('n___'.$dynamic_e__id)) . ')' => null, //All possible answers
                        'x__right' => $o__id,
                        'x__type IN (' . join(',', $this->config->item('n___33602')) . ')' => null, //Idea/Source Links Active
                        'x__privacy IN (' . join(',', $this->config->item('n___7359')) . ')' => null, //PUBLIC
                    )));

                } else {

                    $already_responded = count($this->X_model->fetch(array(
                        'x__up' => $dynamic_e__id,
                        'x__right' => $o__id,
                        'x__type IN (' . join(',', $this->config->item('n___33602')) . ')' => null, //Idea/Source Links Active
                        'x__privacy IN (' . join(',', $this->config->item('n___7359')) . ')' => null, //PUBLIC
                    )));

                }

                if(!$already_responded){
                    //We are missing a required response, auto open modal:
                    $auto_open_i_editor_modal = 1;
                }

            }

        }

        return array(
            'status' => intval($status) && ($links_removed<0 || $links_removed>0),
            'message' => 'Delete status ['.$status.'] with '.$links_removed.' Links removed',
            'deletion_redirect' => $deletion_redirect,
            'delete_element' => $delete_element,
            'auto_open_i_editor_modal' => $auto_open_i_editor_modal,
        );

    }
    function send_dm($e__id, $subject, $html_message, $x_data = array(), $template_i__id = 0, $x__website = 0, $log_tr = true)
    {

        $sms_subscriber = false;
        $bypass_notifications = count($this->X_model->fetch(array(
            'x__privacy IN (' . join(',', $this->config->item('n___7359')) . ')' => null, //PUBLIC
            'x__type IN (' . join(',', $this->config->item('n___42256')) . ')' => null, //Writes
            'x__up' => 31779, //Mandatory Emails
            'x__right' => $template_i__id,
        )));

        if(!$bypass_notifications){
            $notification_levels = $this->X_model->fetch(array(
                'x__up IN (' . join(',', $this->config->item('n___30820')) . ')' => null, //Active Subscriber
                'x__down' => $e__id,
                'x__type IN (' . join(',', $this->config->item('n___32292')) . ')' => null, //SOURCE LINKS
                'x__privacy IN (' . join(',', $this->config->item('n___7359')) . ')' => null, //PUBLIC
            ));
            if (!count($notification_levels)) {
                return array(
                    'status' => 0,
                    'message' => 'User is not an active subscriber',
                );
            }
            $sms_subscriber = in_array($notification_levels[0]['x__up'], $this->config->item('n___28915'));
        }

        $stats = array(
            'email_addresses' => array(),
            'phone_count' => 0,
        );


        //Send Emails:
        foreach($this->X_model->fetch(array(
            'x__privacy IN (' . join(',', $this->config->item('n___7359')) . ')' => null, //PUBLIC
            'x__type IN (' . join(',', $this->config->item('n___32292')) . ')' => null, //SOURCE LINKS
            'x__up' => 3288, //Email
            'x__down' => $e__id,
        )) as $e_data){

            if(!filter_var($e_data['x__message'], FILTER_VALIDATE_EMAIL)){
                $this->X_model->update($e_data['x__id'], array(
                    'x__privacy' => 6173, //Transaction Deleted
                ), $e__id, 27890 /* Website Archive */);
                continue;
            }

            array_push($stats['email_addresses'], $e_data['x__message']);

        }

        if(count($stats['email_addresses']) > 0){
            //Send email:
            send_email($stats['email_addresses'], $subject, $html_message, $e__id, $x_data, $template_i__id, $x__website, $log_tr);
        }



        //Should we send SMS?
        $twilio_account_sid = website_setting(30859);
        $twilio_auth_token = website_setting(30860);
        $twilio_from_number = website_setting(27673);
        if($sms_subscriber && $twilio_account_sid && $twilio_auth_token && $twilio_from_number){

            //Yes, generate message
            $sms_message  = 'Update: We emailed ['.$subject.'] to '.join(' & ',$stats['email_addresses']);

            //Breakup into smaller SMS friendly messages
            $sms_message = str_replace("\n"," ",$sms_message);

            //Send SMS
            foreach($this->X_model->fetch(array(
                'x__privacy IN (' . join(',', $this->config->item('n___7359')) . ')' => null, //PUBLIC
                'x__type IN (' . join(',', $this->config->item('n___32292')) . ')' => null, //SOURCE LINKS
                'x__up' => 4783, //Phone
                'x__down' => $e__id,
            )) as $e_data){

                foreach(explode('|||',wordwrap($sms_message, view_memory(6404,27891), "|||")) as $single_message){

                    $sms_sent = send_sms($e_data['x__message'], $single_message, $e__id, $x_data, $template_i__id, $x__website, $log_tr);

                    if(!$sms_sent){
                        //bad number, remove it:
                        $this->X_model->update($e_data['x__id'], array(
                            'x__privacy' => 6173, //Transaction Deleted
                        ), $e__id, 27890 /* Website Archive */);
                    }

                }

                $stats['phone_count']++;

            }

        }

        return array(
            'status' => ( $stats['phone_count']>0 || count($stats['email_addresses'])>0 ? 1 : 0 ),
            'email_count' => count($stats['email_addresses']),
            'phone_count' => $stats['phone_count'],
            'message' => 'Message sent',
        );

    }



    function send_i_dm($list_of_e__id, $i, $x__right = 0, $x__website = 0, $ensure_undiscovered = true){

        $top_i__hashtag = '';
        foreach($this->X_model->fetch(array(
            'x__privacy IN (' . join(',', $this->config->item('n___7360')) . ')' => null, //ACTIVE
            'x__type' => 32426, //PINNED IDEA
            '(x__right = '.$i['i__id'].' OR x__left = '.$i['i__id'].')' => null,
            'x__left >' => 0,
            'x__right >' => 0,
        )) as $top_i){
            foreach($this->I_model->fetch(array(
                'i__id' => ( $top_i['x__right']==$i['i__id'] ? $top_i['x__left'] : $top_i['x__right'] ),
            )) as $sel_i){
                $top_i__hashtag = '/'.$sel_i['i__hashtag'];
                break;
            }
            if($top_i__hashtag){
                break;
            }
        }

        $children = $this->X_model->fetch(array(
            'x__privacy IN (' . join(',', $this->config->item('n___7360')) . ')' => null, //ACTIVE
            'i__privacy IN (' . join(',', $this->config->item('n___31871')) . ')' => null, //ACTIVE
            'x__type IN (' . join(',', $this->config->item('n___42267')) . ')' => null, //Sequence Down
            'x__left' => $i['i__id'],
        ), array('x__right'), 0, 0, array('x__weight' => 'ASC'));

        $total_sent = 0;
        $x__website = ( $x__website>0 ? $x__website : ( isset($i['x__website']) ? $i['x__website'] : 0 ) );
        $subject_line = view_i_title($i, true);
        $content_message = view_i_links($i, true); //Hide the show more content if any
        if(!(substr($subject_line, 0, 1)=='#' && !substr_count($subject_line, ' '))){
            //Let's remove the first line since it's used in the title:
            $content_message = delete_all_between('<div class="line first_line">','</div>', $content_message);
        }

        foreach($list_of_e__id as $x) {

            if(!isset($x['e__id'])){
                //Invalid input for sending:
                $this->X_model->create(array(
                    'x__type' => 4246, //Platform Bug Reports
                    'x__message' => 'send_i_dm() Invalid user row',
                    'x__metadata' => array(
                        '$i' => $i,
                        '$list_of_e__id' => $list_of_e__id,
                        '$x' => $x,
                    ),
                ));
                continue;
            }

            //Send to all of them IF NOT DISCOVERED
            if(!$ensure_undiscovered || !count($this->X_model->fetch(array(
                'x__left' => $i['i__id'],
                'x__creator' => $x['e__id'],
                'x__type IN (' . join(',', $this->config->item('n___6255')) . ')' => null, //DISCOVERIES
                'x__privacy IN (' . join(',', $this->config->item('n___7359')) . ')' => null, //PUBLIC
            )))){

                //Append children as options:
                $html_message = '';
                foreach($children as $down_or){
                    //Has this user discovered this idea or no?
                    $html_message .= '<div class="line">'.view_i_title($down_or, true).':</div>';
                    $html_message .= '<div class="line">'.'https://'.get_domain('m__message', $x['e__id'], $x__website).$top_i__hashtag.'/'.$down_or['i__hashtag'].'?e__handle='.$x['e__handle'].'&e__time='.time().'&e__hash='.view__hash(time().$x['e__handle']).'</div>';
                }

                $send_dm = $this->X_model->send_dm($x['e__id'], $subject_line, $content_message.$html_message, array(
                    'x__right' => $x__right,
                    'x__left' => $i['i__id'],
                ), $i['i__id'], $x__website, true);

                $total_sent += ( $send_dm['status'] ? 1 : 0 );

            }
        }

        return $total_sent;
    }


    function find_previous($e__id, $top_i__hashtag, $focus_i__id, $loop_breaker_ids = array())
    {

        if(count($loop_breaker_ids)>0 && in_array($focus_i__id, $loop_breaker_ids)){
            return array();
        }
        array_push($loop_breaker_ids, intval($focus_i__id));

        //Fetch followings:
        foreach($this->X_model->fetch(array(
            'i__privacy IN (' . join(',', $this->config->item('n___31871')) . ')' => null, //ACTIVE
            'x__privacy IN (' . join(',', $this->config->item('n___7359')) . ')' => null, //PUBLIC
            'x__type IN (' . join(',', $this->config->item('n___42268')) . ')' => null, //Active Sequence Up
            'x__right' => $focus_i__id,
        ), array('x__left')) as $i_previous) {

            //Validate Selection:
            $is_or_i = in_array($i_previous['i__type'], $this->config->item('n___7712'));
            $is_fixed_x = in_array($i_previous['x__type'], $this->config->item('n___42268')); //Active Sequence UP
            $is_selected = count($this->X_model->fetch(array(
                'x__privacy IN (' . join(',', $this->config->item('n___7359')) . ')' => null, //PUBLIC
                'x__type IN (' . join(',', $this->config->item('n___7704')) . ')' => null, //Discovery Expansion
                'x__left' => $i_previous['i__id'],
                'x__right' => $focus_i__id,
                'x__creator' => $e__id,
            )));

            if($e__id>0 && !$is_selected && ($is_or_i || !$is_fixed_x)){
                continue;
            }

            //Did we find it?
            if($i_previous['i__hashtag']==$top_i__hashtag){
                return array($i_previous);
            }

            //Keep looking:
            $top_finder = $this->X_model->find_previous($e__id, $top_i__hashtag, $i_previous['i__id'], $loop_breaker_ids);
            if(count($top_finder)){
                array_push($top_finder, $i_previous);
                return $top_finder;
            }
        }

        //Did not find any followings:
        return array();

    }




    function find_previous_discovered($focus_i__id, $x__creator, $loop_breaker_ids = array()){

        /*
         *
         * Returns hashtag if discovered upwards
         *
         * */

        if(count($loop_breaker_ids)>0 && in_array($focus_i__id, $loop_breaker_ids)){
            return false;
        }
        array_push($loop_breaker_ids, intval($focus_i__id));

        foreach($this->X_model->fetch(array(
            'i__privacy IN (' . join(',', $this->config->item('n___31871')) . ')' => null, //ACTIVE
            'x__privacy IN (' . join(',', $this->config->item('n___7359')) . ')' => null, //PUBLIC
            'x__type IN (' . join(',', $this->config->item('n___42268')) . ')' => null, //Active Sequence Up
            'x__right' => $focus_i__id,
        ), array('x__left')) as $prev_i){

            foreach($this->X_model->fetch(array(
                'x__privacy IN (' . join(',', $this->config->item('n___7359')) . ')' => null, //PUBLIC
                'x__type IN (' . join(',', $this->config->item('n___6255')) . ')' => null, //DISCOVERIES
                'x__creator' => $x__creator,
                'x__left' => $prev_i['i__id'],
                'i__privacy IN (' . join(',', $this->config->item('n___31871')) . ')' => null, //ACTIVE
            ), array('x__right')) as $x){
                return $x['i__hashtag'];
            }

            return $this->X_model->find_previous_discovered($prev_i['i__id'], $x__creator, $loop_breaker_ids);
        }

        //Did not find!
        return false;

    }







    function find_next($e__id, $top_i__hashtag, $i, $find_after_i__id = 0, $search_up = true, $top_completed = false, $loop_breaker_ids = array())
    {

        if(count($loop_breaker_ids)>0 && in_array($i['i__id'], $loop_breaker_ids)){
            return false;
        }
        array_push($loop_breaker_ids, intval($i['i__id']));

        $is_or_i = in_array($i['i__type'], $this->config->item('n___7712'));
        $found_trigger = false;

        foreach ($this->X_model->fetch(array(
            'x__left' => $i['i__id'],
            'x__type IN (' . join(',', $this->config->item('n___42267')) . ')' => null, //Active Sequence Down
            'x__privacy IN (' . join(',', $this->config->item('n___7359')) . ')' => null, //PUBLIC
            'i__privacy IN (' . join(',', $this->config->item('n___31871')) . ')' => null, //ACTIVE
        ), array('x__right'), 0, 0, array('x__weight' => 'ASC')) as $next_i) {

            //Validate Find After:
            if ($find_after_i__id && !$found_trigger) {
                if ($next_i['i__id']==$find_after_i__id) {
                    $found_trigger = true;
                }
                continue;
            }

            //Validate Selection:
            $is_fixed_x = in_array($next_i['x__type'], $this->config->item('n___42267')); //Active Sequence Down
            $is_selected = count($this->X_model->fetch(array(
                'x__privacy IN (' . join(',', $this->config->item('n___7359')) . ')' => null, //PUBLIC
                'x__type IN (' . join(',', $this->config->item('n___7704')) . ')' => null, //Discovery Expansion
                'x__left' => $i['i__id'],
                'x__right' => $next_i['i__id'],
                'x__creator' => $e__id,
            )));
            if(($is_or_i || !$is_fixed_x) && !$is_selected){
                continue;
            }


            //Return this if everything is completed, or if this is incomplete:
            if($top_completed || !count($this->X_model->fetch(array(
                    'x__privacy IN (' . join(',', $this->config->item('n___7359')) . ')' => null, //PUBLIC
                    'x__type IN (' . join(',', $this->config->item('n___6255')) . ')' => null, //DISCOVERIES
                    'x__creator' => $e__id,
                    'x__left' => $next_i['i__id'],
                )))){
                return $next_i['i__hashtag'];
            }

            //Keep looking deeper:
            $next_i__hashtag = $this->X_model->find_next($e__id, $top_i__hashtag, $next_i, 0, false, $top_completed, $loop_breaker_ids);
            if ($next_i__hashtag) {
                return $next_i__hashtag;
            }

        }

        if ($search_up && $top_i__hashtag!=$i['i__hashtag']) {
            //Check Previous/Up
            $current_previous = $i['i__id'];
            foreach (array_reverse($this->X_model->find_previous($e__id, $top_i__hashtag, $i['i__id'])) as $p_i) {
                //Find the next siblings:
                $next_i__hashtag = $this->X_model->find_next($e__id, $top_i__hashtag, $p_i, $current_previous, false, $top_completed);
                if ($next_i__hashtag) {
                    return $next_i__hashtag;
                }
                $current_previous = $p_i['i__id'];
            }
        }


        //Nothing found:
        return false;

    }






    function read_only_complete($x__creator, $top_i__id, $i, $x_data = array()){

        //Try to auto complete:
        $x__type = 0;
        if (in_array($i['i__type'], $this->config->item('n___34826'))) {
            if ($i['i__type'] == 6677) {
                $x__type = 4559;
            } elseif ($i['i__type'] == 30874) {
                $x__type = 31810;
            } elseif ($i['i__type'] == 42392) {
                $x__type = 42402;
            } elseif ($i['i__type'] == 42399) {
                $x__type = 42400;
            } elseif ($i['i__type'] == 42394) {
                $x__type = 42397;
            }
        }

        if($x__type > 0){
            return $this->X_model->mark_complete($x__type, $x__creator, $top_i__id, $i, $x_data);
        } else {
            return false;
        }

    }


    function mark_complete($x__type, $x__creator, $top_i__id, $i, $x_data = array()) {

        if(!in_array($x__type, $this->config->item('n___31777'))){
            $this->X_model->create(array(
                'x__creator' => $x__creator,
                'x__type' => 4246, //Platform Bug Reports
                'x__message' => 'mark_complete() Invalid x__type @'.$x__type.' missing in @31777',
                'x__metadata' => array(
                    '$top_i__id' => $top_i__id,
                    '$i' => $i,
                    '$x_data' => $x_data,
                ),
            ));
        }

        $member_e = superpower_unlocked();
        $x_data['x__creator'] = ( $x__creator ? $x__creator : $member_e['e__id'] );
        $x_data['x__type'] = $x__type;
        $x_data['x__left'] = $i['i__id'];

        //Always add Idea to x__left
        if($top_i__id>0 && (!isset($x_data['x__right']) || !intval($x_data['x__right']))){
            $x_data['x__right'] = $top_i__id;
        }

        if (!isset($x_data['x__message'])) {
            $x_data['x__message'] = null;
        }

        $es_creator = $this->E_model->fetch(array(
            'e__id' => $x_data['x__creator'],
        ));

        //Make sure not duplicate:
        foreach($this->X_model->fetch(array(
            'x__privacy IN (' . join(',', $this->config->item('n___7359')) . ')' => null, //PUBLIC
            'x__type IN (' . join(',', $this->config->item('n___6255')) . ')' => null, //DISCOVERIES
            'x__type NOT IN (' . join(',', $this->config->item('n___30469')) . ')' => null, //TICKETS
            'x__left' => ( isset($x_data['x__left']) ? $x_data['x__left'] : 0 ),
            'x__right' => ( isset($x_data['x__right']) ? $x_data['x__right'] : 0 ),
            'x__creator' => $x__creator,
            'x__message' => $x_data['x__message'],
        )) as $already_discovered){
            //Already discovered! Return this:
            return $already_discovered;
        }

        //Add new transaction:
        $domain_url = get_domain('m__message', $x__creator);
        $new_x = $this->X_model->create($x_data);


        //Auto Complete OR Answers:
        if(in_array($i['i__type'], $this->config->item('n___7712'))){
            foreach($this->X_model->fetch(array(
                'x__privacy IN (' . join(',', $this->config->item('n___7359')) . ')' => null, //PUBLIC
                'x__type IN (' . join(',', $this->config->item('n___7704')) . ')' => null, //Discovery Expansion
                'x__creator' => $x_data['x__creator'],
                'x__left' => $i['i__id'],
            ), array('x__right'), 0) as $next_i){
                //Mark as complete:
                $this->X_model->read_only_complete($x_data['x__creator'], $top_i__id, $next_i, $x_data);
            }
        }

        //Ticket Email?
        if(isset($new_x['x__id']) && isset($new_x['x__creator']) && in_array($new_x['x__type'], $this->config->item('n___32014'))){
            email_ticket($new_x['x__id'], $i['i__hashtag'],$new_x['x__creator']);
        }


        if ($x_data['x__creator'] && in_array($x_data['x__type'], $this->config->item('n___40986'))) {

            //Discovery Triggers?
            $clone_urls = '';
            foreach($this->X_model->fetch(array(
                'x__privacy IN (' . join(',', $this->config->item('n___7359')) . ')' => null, //PUBLIC
                'i__privacy IN (' . join(',', $this->config->item('n___31871')) . ')' => null, //ACTIVE
                'x__type IN (' . join(',', $this->config->item('n___32275')) . ')' => null, //DISCOVERY TRIGGERS
                'x__left' => $i['i__id'],
            ), array('x__right'), 0, 0, array('x__weight' => 'ASC')) as $clone_i){

                if($clone_i['x__type']==32247){

                    //Discovery Clone
                    $new_title = $es_creator[0]['e__title'].' '.$clone_i['i__message'];
                    $result = $this->I_model->recursive_clone($clone_i['i__id'], 0, $x_data['x__creator'], null, $new_title);
                    if($result['status']){

                        //Add as watcher:
                        $this->X_model->create(array(
                            'x__type' => 10573, //WATCHERS
                            'x__creator' => $x_data['x__creator'],
                            'x__up' => $x_data['x__creator'],
                            'x__right' => $result['new_i__id'],
                        ));

                        //New link:
                        $clone_urls .= $new_title.':'."\n".'https://'.get_domain('m__message', $x_data['x__creator']).'/'.$result['new_i__hashtag']."\n\n";
                    }

                } elseif($clone_i['x__type']==32304){

                    //Discovery Forget: Remove all Discoveries made by this user:
                    foreach($this->X_model->fetch(array(
                        'x__privacy IN (' . join(',', $this->config->item('n___7359')) . ')' => null, //PUBLIC
                        'x__type IN (' . join(',', $this->config->item('n___6255')) . ')' => null, //DISCOVERIES
                        'x__left' => $i['i__id'],
                        'x__creator' => $x_data['x__creator'],
                    )) as $remove_x){
                        $this->X_model->update($remove_x['x__id'], array(
                            'x__privacy' => 6173, //Remove this discovery
                        ), $x_data['x__creator'], 29431 /* Play Auto Removed */);
                    }

                }

            }

            if(strlen($clone_urls)){
                //Send DM with all the new clone idea URLs:
                $clone_urls = $clone_urls.'You have been added as a subscriber so you will be notified when anyone start using your link.';
                $i_title = view_i_title($i, true);
                $this->X_model->send_dm($x_data['x__creator'], $i_title , $clone_urls);
                //Also DM all watchers of the idea:
                foreach($this->X_model->fetch(array(
                    'x__privacy IN (' . join(',', $this->config->item('n___7359')) . ')' => null, //PUBLIC
                    'x__type' => 10573, //WATCHERS
                    'x__right' => $i['i__id'],
                ), array(), 0) as $watcher){
                    $this->X_model->send_dm($watcher['x__up'], $i_title, $clone_urls);
                }
            }



            //ADD PROFILE?
            foreach($this->X_model->fetch(array(
                'x__privacy IN (' . join(',', $this->config->item('n___7359')) . ')' => null, //PUBLIC
                'x__type' => 7545, //Following Add
                'x__right' => $i['i__id'],
            ), array('x__up')) as $x_tag){

                //Check if special profile add?

                if($x_tag['x__up']==6197 && strlen(trim($x_data['x__message']))>=2){

                    //Update Source Title:
                    $this->E_model->update($x_data['x__creator'], array(
                        'e__title' => $x_data['x__message'],
                    ), true, $x_data['x__creator']);

                    //Update live session as well:
                    $es_creator[0]['e__title'] = $x_data['x__message'];
                    $this->E_model->activate_session($es_creator[0], true);

                } elseif($x_tag['x__up']==6198 && view_valid_handle_e($x_data['x__message'])){

                    //Update Source Cover:
                    foreach($this->E_model->fetch(array(
                        'LOWER(e__handle)' => strtolower(view_valid_handle_e($x_data['x__message'])),
                    )) as $e){
                        foreach($this->X_model->fetch(array(
                            'x__type IN (' . join(',', $this->config->item('n___32292')) . ')' => null, //SOURCE LINKS
                            'x__privacy IN (' . join(',', $this->config->item('n___7359')) . ')' => null, //PUBLIC
                            'x__down' => $e['e__id'],
                        ), array('x__up'), 1, 0, array('e__weight' => 'DESC')) as $following){

                            //Valid Image URL?
                            $view_links = view_sync_links($following['x__message'], true);
                            if(count($view_links['i__references'][4260])){
                                //Update profile picture for current user:
                                $this->E_model->update($x_data['x__creator'], array(
                                    'e__cover' => $following['x__message'],
                                ), true, $x_data['x__creator']);

                                //Update live session as well:
                                $es_creator[0]['e__cover'] = $following['x__message'];
                                $this->E_model->activate_session($es_creator[0], true);
                            }
                        }
                    }

                } else {

                    //Generate stats:
                    $x_added = 0;
                    $x_edited = 0;
                    $x_deleted = 0;

                    //Assign tag if following/follower transaction NOT previously assigned:
                    $existing_x = $this->X_model->fetch(array(
                        'x__privacy IN (' . join(',', $this->config->item('n___7359')) . ')' => null, //PUBLIC
                        'x__type IN (' . join(',', $this->config->item('n___32292')) . ')' => null, //SOURCE LINKS
                        'x__up' => $x_tag['x__up'],
                        'x__down' => $x_data['x__creator'],
                    ));

                    if(count($existing_x)){

                        //Transaction previously exists, see if content value is the same:
                        if(strtolower($existing_x[0]['x__message'])==strtolower($x_data['x__message'])){
                            //Everything is the same, nothing to do here:
                            continue;
                        }

                        $x_edited++;

                        //Content value has changed, update the transaction:
                        $this->X_model->update($existing_x[0]['x__id'], array(
                            'x__message' => $x_data['x__message'],
                        ), $x_data['x__creator'], 10657 /* SOURCE LINK CONTENT UPDATE  */);

                        $this->X_model->create(array(
                            'x__type' => 12197, //Following Added
                            'x__creator' => $x_data['x__creator'],
                            'x__up' => $x_tag['x__up'],
                            'x__down' => $x_data['x__creator'],
                            'x__left' => $i['i__id'],
                            'x__message' => $x_added.' added, '.$x_edited.' edited & '.$x_deleted.' deleted with new content ['.$x_data['x__message'].']',
                        ));

                    } else {

                        //Create transaction:
                        $x_added++;
                        $this->X_model->create(array(
                            'x__type' => 4230, //Follow Source
                            'x__message' => $x_data['x__message'],
                            'x__creator' => $x_data['x__creator'],
                            'x__up' => $x_tag['x__up'],
                            'x__down' => $x_data['x__creator'],
                        ));

                        $this->X_model->create(array(
                            'x__type' => 12197, //Following Added
                            'x__creator' => $x_data['x__creator'],
                            'x__up' => $x_tag['x__up'],
                            'x__down' => $x_data['x__creator'],
                            'x__left' => $i['i__id'],
                            'x__message' => $x_added.' added, '.$x_edited.' edited & '.$x_deleted.' deleted with new content ['.$x_data['x__message'].']',
                        ));

                    }

                    //See if Session needs to be updated:
                    if($member_e && $member_e['e__id']==$x_data['x__creator'] && ($x_added>0 || $x_edited>0 || $x_deleted>0)){
                        $this->E_model->activate_session($es_creator[0], true);
                    }
                }
            }


            //REMOVE PROFILE?
            foreach($this->X_model->fetch(array(
                'x__privacy IN (' . join(',', $this->config->item('n___7359')) . ')' => null, //PUBLIC
                'x__type' => 26599, //Following Remove
                'x__right' => $i['i__id'],
            )) as $x_tag){

                //Remove Following IF previously assigned:
                foreach($this->X_model->fetch(array(
                    'x__privacy IN (' . join(',', $this->config->item('n___7359')) . ')' => null, //PUBLIC
                    'x__type IN (' . join(',', $this->config->item('n___32292')) . ')' => null, //SOURCE LINKS
                    'x__up' => $x_tag['x__up'], //CERTIFICATES saved here
                    'x__down' => $x_data['x__creator'],
                )) as $existing_x){

                    $this->X_model->update($existing_x['x__id'], array(
                        'x__privacy' => 6173,
                    ), $x_data['x__creator'], 12197 /* Following Removed */);

                    //See if Session needs to be updated:
                    if($member_e && $member_e['e__id']==$x_data['x__creator']){
                        //Yes, update session:
                        $this->E_model->activate_session($es_creator[0], true);
                    }
                }
            }


            //Notify watchers IF any:
            $watchers = $this->X_model->fetch(array(
                'x__privacy IN (' . join(',', $this->config->item('n___7359')) . ')' => null, //PUBLIC
                'x__type' => 10573, //WATCHERS
                'x__right' => $i['i__id'],
            ), array(), 0);
            if(count($watchers)){

                $es_discoverer = $this->E_model->fetch(array(
                    'e__id' => $x_data['x__creator'],
                ));
                if(count($es_discoverer)){

                    //Fetch Discoverer contact:
                    $discoverer_contact = '';
                    foreach($this->config->item('e___34541') as $x__type => $m) {
                        foreach($this->X_model->fetch(array(
                            'x__privacy IN (' . join(',', $this->config->item('n___7359')) . ')' => null, //PUBLIC
                            'x__type IN (' . join(',', $this->config->item('n___32292')) . ')' => null, //SOURCE LINKS
                            'x__down' => $x_data['x__creator'],
                            'x__up' => $x__type,
                            'LENGTH(x__message)>0' => null,
                        )) as $x_progress){
                            $discoverer_contact .= $m['m__title'].':'."\n".$x_progress['x__message']."\n\n";
                        }
                    }

                    //Notify Idea Watchers
                    $sent_watchers = array();
                    foreach($watchers as $watcher){
                        if(!in_array(intval($watcher['x__up']), $sent_watchers)){
                            array_push($sent_watchers, intval($watcher['x__up']));

                            $this->X_model->send_dm($watcher['x__up'], $es_discoverer[0]['e__title'].' Discovered: '.view_i_title($i, true),
                                //Message Body:
                                view_i_title($i, true).':'."\n".'https://'.$domain_url.'/~'.$i['i__hashtag']."\n\n".
                                ( strlen($x_data['x__message']) ? $x_data['x__message']."\n\n" : '' ).
                                $es_discoverer[0]['e__title'].':'."\n".'https://'.$domain_url.'/@'.$es_discoverer[0]['e__handle']."\n\n".
                                $discoverer_contact
                            );
                        }
                    }
                }
            }
        }
        


        return $new_x;

    }



    function tree_progress($e__id, $i, $current_level = 0, $loop_breaker_ids = array())
    {

        if(count($loop_breaker_ids)>0 && in_array($i['i__id'], $loop_breaker_ids)){
            return false;
        }

        $recursive_down_ids = $this->I_model->recursive_down_ids($i, 'AND');
        if(!isset($recursive_down_ids['recursive_i_ids']) || !count($recursive_down_ids['recursive_i_ids'])){
            return false;
        }

        $current_level++;
        array_push($loop_breaker_ids, intval($i['i__id']));

        //Count completed:
        $list_discovered = array();
        foreach($this->X_model->fetch(array(
            'x__type IN (' . join(',', $this->config->item('n___6255')) . ')' => null, //DISCOVERIES
            'x__creator' => $e__id, //Belongs to this Member
            'x__left IN (' . join(',', $recursive_down_ids['recursive_i_ids'] ) . ')' => null,
            'x__privacy IN (' . join(',', $this->config->item('n___7359')) . ')' => null, //PUBLIC
            'i__privacy IN (' . join(',', $this->config->item('n___31871')) . ')' => null, //ACTIVE
        ), array('x__left'), 0) as $completed){
            if(!in_array(intval($completed['i__id']), $list_discovered)){
                array_push($list_discovered, intval($completed['i__id']));
            }
        }


        //Calculate common steps and expansion steps recursively for this u:
        $metadata_this = array(
            'fixed_total' => count($recursive_down_ids['recursive_i_ids']),
            'list_total' => $recursive_down_ids['recursive_i_ids'],
            'fixed_discovered' => count($list_discovered),
            'list_discovered' => $list_discovered,
        );

        //Now let's check possible expansions:
        if(count($recursive_down_ids['recursive_i_ids'])){
            foreach($this->X_model->fetch(array(
                'x__type IN (' . join(',', $this->config->item('n___7704')) . ')' => null, //Discovery Expansion
                'x__creator' => $e__id, //Belongs to this Member
                'x__left IN (' . join(',', $recursive_down_ids['recursive_i_ids'] ) . ')' => null,
                'x__privacy IN (' . join(',', $this->config->item('n___7359')) . ')' => null, //PUBLIC
                'i__privacy IN (' . join(',', $this->config->item('n___31871')) . ')' => null, //ACTIVE
            ), array('x__right')) as $expansion_in) {

                //Fetch recursive:
                $tree_progress = $this->X_model->tree_progress($e__id, $expansion_in, $current_level, $loop_breaker_ids);

                //Addup completion stats for this:
                $metadata_this['fixed_total'] += $tree_progress['fixed_total'];
                $metadata_this['fixed_discovered'] += $tree_progress['fixed_discovered'];

                if($tree_progress['list_total'] && count($tree_progress['list_total'])){
                    foreach($tree_progress['list_total'] as $tree_id){
                        if(!in_array($tree_id, $metadata_this['list_total'])){
                            array_push($metadata_this['list_total'], $tree_id);
                        }
                    }
                }

                if($tree_progress['list_discovered'] && count($tree_progress['list_discovered'])){
                    foreach($tree_progress['list_discovered'] as $tree_id){
                        if(!in_array($tree_id, $metadata_this['list_discovered'])){
                            array_push($metadata_this['list_discovered'], $tree_id);
                        }
                    }
                }
            }
        }

        if($current_level==1){

            /*
             *
             * Completing an discoveries depends on two factors:
             *
             * 1) number of steps (some may have 0 time estimate)
             * 2) estimated seconds (usual ly accurate)
             *
             * To increase the accurate of our completion % function,
             * We would also assign a default time to the average step
             * so we can calculate more accurately even if none of the
             * steps have an estimated time.
             *
             * */

            //Set default seconds per step:
            $metadata_this['fixed_completed_percentage'] = 0;

            //Calculate completion rate based on estimated time cost:
            if($metadata_this['fixed_total'] > 0){
                $metadata_this['fixed_completed_percentage'] = intval(ceil( $metadata_this['fixed_discovered'] / $metadata_this['fixed_total'] * 100 ));
            }


        }

        //Return results:
        return $metadata_this;

    }


    function started_ids($e__id, $i__hashtag = null){

        //Simply returns all the idea IDs for a users starting points
        if($i__hashtag){

            if(!$e__id){
                return false;
            }

            return count($this->X_model->fetch(array(
                'x__left = x__right' => NULL,
                'LOWER(i__hashtag)' => strtolower($i__hashtag),
                'x__creator' => $e__id,
                'x__type IN (' . join(',', $this->config->item('n___6255')) . ')' => null, //DISCOVERIES
                'x__privacy IN (' . join(',', $this->config->item('n___7359')) . ')' => null, //PUBLIC
            ), array('x__right')));

        } else {

            $u_x_ids = array();
            if($e__id > 0){
                foreach($this->X_model->fetch(array(
                    'x__left=x__right' => null,
                    'x__creator' => $e__id,
                    'x__type IN (' . join(',', $this->config->item('n___6255')) . ')' => null, //DISCOVERIES
                    'x__privacy IN (' . join(',', $this->config->item('n___7359')) . ')' => null, //PUBLIC
                )) as $u_in){
                    array_push($u_x_ids, intval($u_in['x__left']));
                }
            }
            return $u_x_ids;

        }
    }




    function x_select($top_i__id, $focus_i__id, $answer_i__ids){

        $member_e = superpower_unlocked();
        if (!$member_e) {
            return array(
                'status' => 0,
                'message' => view_unauthorized_message(),
            );
        }

        $is = $this->I_model->fetch(array(
            'i__id' => $focus_i__id,
            'i__privacy IN (' . join(',', $this->config->item('n___31871')) . ')' => null, //ACTIVE
        ));
        $es = $this->E_model->fetch(array(
            'e__id' => $member_e['e__id'],
            'e__privacy IN (' . join(',', $this->config->item('n___7358')) . ')' => null, //ACTIVE
        ));
        if (!count($is)) {
            return array(
                'status' => 0,
                'message' => 'Invalid idea ID',
            );
        } elseif (!count($es)) {
            return array(
                'status' => 0,
                'message' => 'Invalid Source Input',
            );
        } elseif (!in_array($is[0]['i__type'], $this->config->item('n___7712'))) {
            return array(
                'status' => 0,
                'message' => 'Invalid Idea type [Must be Answer]',
            );
        }

        $is_single_selection = $is[0]['i__type']==6684;

        //Can they skip without selecting anything?
        $can_skip = count($this->X_model->fetch(array(
            'x__privacy IN (' . join(',', $this->config->item('n___7359')) . ')' => null, //PUBLIC
            'x__type IN (' . join(',', $this->config->item('n___42350')) . ')' => null, //Active Writes
            'x__right' => $focus_i__id,
            'x__up' => 28239, //Can Skip
        )));
        if(!$can_skip && !count($answer_i__ids)){
            return array(
                'status' => 0,
                'message' => 'You must select an item before going next.',
            );
        }
        $did_skip = ( $can_skip && !count($answer_i__ids) );





        //How about the min selection?
        if(!$can_skip && !$is_single_selection){
            foreach($this->X_model->fetch(array(
                'x__privacy IN (' . join(',', $this->config->item('n___7359')) . ')' => null, //PUBLIC
                'x__type IN (' . join(',', $this->config->item('n___42350')) . ')' => null, //Active Writes
                'x__right' => $focus_i__id,
                'x__up' => 40834, //Min Selection
            ), array(), 1) as $limit){
                if(intval($limit['x__message']) > 0 && count($answer_i__ids) < intval($limit['x__message'])){
                    return array(
                        'status' => 0,
                        'message' => 'You must select '.$limit['x__message'].' items or more.',
                    );
                }
            }
        }


        //How about max selection?
        if(!$is_single_selection){
            foreach($this->X_model->fetch(array(
                'x__privacy IN (' . join(',', $this->config->item('n___7359')) . ')' => null, //PUBLIC
                'x__type IN (' . join(',', $this->config->item('n___42350')) . ')' => null, //Active Writes
                'x__right' => $focus_i__id,
                'x__up' => 40833, //Max Selection
            ), array(), 1) as $limit){
                if(intval($limit['x__message']) > 0 && count($answer_i__ids) > intval($limit['x__message'])){
                    return array(
                        'status' => 0,
                        'message' => 'You cannot select more than '.$limit['x__message'].' items.',
                    );
                }
            }
        }



        //Delete ALL previous answers:
        foreach($this->X_model->fetch(array(
            'x__privacy IN (' . join(',', $this->config->item('n___7359')) . ')' => null, //PUBLIC
            'x__type IN (' . join(',', $this->config->item('n___32234')) . ')' => null, //DISCOVERY ANSWERED
            'x__creator' => $member_e['e__id'],
            'x__left' => $is[0]['i__id'],
        ), array('x__right')) as $x_selection){

            $this->X_model->update($x_selection['x__id'], array(
                'x__privacy' => 6173, //Transaction Deleted
            ), $member_e['e__id'], 12129 /* DISCOVERY ANSWER DELETED */);

            if(!in_array($x_selection['i__type'], $this->config->item('n___41055'))){

                //Remove discovery of the selected since its not a payment type:
                foreach($this->X_model->fetch(array(
                    'x__privacy IN (' . join(',', $this->config->item('n___7359')) . ')' => null, //PUBLIC
                    'x__type IN (' . join(',', $this->config->item('n___6255')) . ')' => null, //DISCOVERIES
                    'x__left' => $x_selection['i__id'],
                    'x__creator' => $member_e['e__id'],
                ), array(), 0) as $x_discovery){

                    $this->X_model->update($x_discovery['x__id'], array(
                        'x__privacy' => 6173, //Transaction Deleted
                    ), $member_e['e__id'], 12129 /* DISCOVERY ANSWER DELETED */);

                }
            }
        }

        //Add New Answers
        $answers_newly_added = 0;
        if(count($answer_i__ids)){
            foreach($answer_i__ids as $answer_i__id){
                $answers_newly_added++;
                $this->X_model->create(array(
                    'x__type' => 12336, //Link Selection
                    'x__creator' => $member_e['e__id'],
                    'x__left' => $is[0]['i__id'],
                    'x__right' => $answer_i__id,
                ));
            }
        }

        //Issue DISCOVERY/IDEA COIN:
        $this->X_model->mark_complete(( count($answer_i__ids) ? ( $is_single_selection ? 6157 : 41940 ) : 31022 /* Skipped */ ), $member_e['e__id'], $top_i__id, $is[0]);

        //All good, something happened:
        return array(
            'status' => 1,
            'message' => $answers_newly_added.' Selected. Going Next',
        );

    }



}