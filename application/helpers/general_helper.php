<?php

function includes_any($string, $items)
{
    //Determines if any of the items in array $items includes $string
    foreach($items as $item) {
        if (substr_count($string, $item) > 0) {
            return $item;
        }
    }
    return false;
}


function load_algolia($index_name)
{
    //Loads up algolia search engine functions
    $CI =& get_instance();
    require_once('application/libraries/algoliasearch.php');
    $client = new \AlgoliaSearch\Client($CI->config->item('cred_algolia_app_id'), $CI->config->item('cred_algolia_api_key'));
    return $client->initIndex($index_name);
}

function detect_missing_columns($add_fields, $required_columns, $x__creator)
{
    //A function used to review and require certain fields when inserting new rows in DB
    foreach($required_columns as $req_field) {
        if (!isset($add_fields[$req_field]) || strlen($add_fields[$req_field]) == 0) {
            return true; //Ooops, we're missing this required field
        }
    }

    //No errors found, all good:
    return false; //Not missing anything
}


function fetch_file_ext($url)
{
    //A function that attempts to fetch the file extension of an input URL:
    //https://cdn.fbsbx.com/v/t59.3654-21/19359558_10158969505640587_4006997452564463616_n.aac/audioclip-1500335487327-1590.aac?oh=5344e3d423b14dee5efe93edd432d245&oe=596FEA95
    $url_parts = explode('?', $url, 2);
    $url_parts = explode('/', $url_parts[0]);
    $file_parts = explode('.', end($url_parts));
    return end($file_parts);
}


function extract_e_references($x__message)
{

    //Analyzes a message text to extract Source References (Like @123) and URLs
    $CI =& get_instance();
    $member_e = superpower_unlocked();

    //Replace non-ascii characters with space:
    $x__message = preg_replace('/[[:^print:]]/', ' ', $x__message);

    //Analyze the message to find referencing URLs and Members in the message text:
    $string_references = array(
        'ref_urls' => array(),
        'ref_e' => array(),
        'ref_time_found' => false,
        'ref_time_start' => 0,
        'ref_time_end' => 0,
    );

    //See what we can find:
    foreach(preg_split('/\s+/', $x__message) as $word) {
        if (filter_var($word, FILTER_VALIDATE_URL)) {

            if(substr_count($word,'|')==2){
                //See if this is it:
                $times = explode('|',$word);
                $ref_time_start = second_calc($times[1]);
                $ref_time_end = second_calc($times[2]);
                if($ref_time_start>=0 && $ref_time_end>0 && $ref_time_start<$ref_time_end && $word==$times[0].'|'.$times[1].'|'.$times[2]){
                    $string_references['ref_time_found'] = true;
                    $string_references['ref_time_start'] = $ref_time_start;
                    $string_references['ref_time_end'] = $ref_time_end;
                    $word = $times[0];
                }
            }

            array_push($string_references['ref_urls'], $word);

        } elseif (substr($word, 0, 1) == '@' && is_numeric(substr($word, 1, 1))) {

            $e__id = intval(substr($word, 1));
            array_push($string_references['ref_e'], $e__id);

            if(substr_count($word,'|')==2){
                //See if this is it:
                $times = explode('|',$word);
                $ref_time_start = second_calc($times[1]);
                $ref_time_end = second_calc($times[2]);
                if($ref_time_start>=0 && $ref_time_end>0 && $ref_time_start<$ref_time_end && $word=='@'.$e__id.'|'.$times[1].'|'.$times[2]){
                    $string_references['ref_time_found'] = true;
                    $string_references['ref_time_start'] = $ref_time_start;
                    $string_references['ref_time_end'] = $ref_time_end;
                }
            }
        }
    }


    //Slicing only supported with a single reference:
    $total_references = count($string_references['ref_e']) + count($string_references['ref_urls']);
    if($total_references > 1){
        $string_references['ref_time_found'] = false;
        $string_references['ref_time_start'] = 0;
        $string_references['ref_time_end'] = 0;
    }

    return $string_references;
}


function second_calc($string){
    $seconds = -1; //Error
    $parts = explode(':',$string);
    if(count($parts)==3 && $parts[0] < 60 && $parts[1] < 60 && $parts[2] < 60){
        //HH:MM:SS
        $seconds = (intval($parts[0]) * 3600) + (intval($parts[1]) * 60) + intval($parts[2]);
    } elseif(count($parts)==2 && $parts[0] < 60 && $parts[1] < 60){
        //MM:SS
        $seconds = (intval($parts[0]) * 60) + intval($parts[1]);
    } elseif(count($parts)==1 && $parts[0] < 60) {
        //SS
        $seconds = intval($parts[0]);
    }
    return $seconds;
}


function is_valid_date($string)
{
    //Determines if the input $string is a valid date
    if (!$string) {
        return false;
    }

    try {
        new \DateTime($string);
        return true;
    } catch (\Exception $e) {
        return false;
    }
}

function current_card_id(){

    /*
     *
     * Detects which of the coins
     * coins is focused on based on
     * the URL which reflects the
     * logic in routes.php
     *
     * */

    $CI =& get_instance();
    $first_segment = $CI->uri->segment(1);
    $first_letter = substr($first_segment, 0, 1);

    if($first_letter!='-' && is_numeric($first_segment)){

        //DISCOVERY
        return 6255;

    } elseif($first_letter=='~'){

        //IDEATION
        return 12273;

    } else {

        //SOURCE
        return 12274;

    }

}


function int_hash($string){
    $int_length = 4;
    $numhash = unpack('N2', md5($string, true));
    $int_val = $numhash[1] & 0x000FFFFF;
    if(strlen($int_val) < $int_length){
        return str_pad($int_val, $int_length, "0", STR_PAD_RIGHT);
    } else {
        return substr($int_val, 0, $int_length);
    }
}


function detect_data_type($string)
{

    /*
     * Detect what type of Source URL type should we create
     * based on options listed in this idea: @4227
     * */

    $string = trim($string);
    $CI =& get_instance();
    $has_space = substr_count($string, ' ');


    if (is_null($string) || !strlen($string)) {

        return array(
            'status' => 1,
            'x__type' => 34162, //Null
        );

    } elseif($has_space){

        //Is it a currency?
        foreach($CI->config->item('e___26661') as $x__type_currency => $m_currency) {
            if (substr($string, 0, 4)==$m_currency['m__message'].' ' && is_numeric(substr($string, 4))) {
                return array(
                    'status' => 1,
                    'x__type' => 26661,
                );
            }
        }

    } elseif(!$has_space) {

        if ((strlen(intval($string)) == strlen($string) || (in_array(substr($string , 0, 1), array('+','-')) && strlen(intval(substr($string , 1))) == strlen(substr($string , 1)))) && (intval($string) != 0 || $string == '0')) {
            return array(
                'status' => 1,
                'x__type' => 4319, //Number
            );
        }

        if (substr($string, 0, 1)=='/' && substr($string, 0, 2)!='//') {
            return array(
                'status' => 1,
                'x__type' => 14728, //Relative URL
            );
        }

        if (filter_var($string, FILTER_VALIDATE_URL)) {
            return $CI->E_model->url($string); //See what type of URL (this could fail if duplicate, etc...)
        }

        if (substr($string, -1)=='%' && is_numeric(substr($string, 0, (strlen($string)-1)))) {
            return array(
                'status' => 1,
                'x__type' => 7657, //Percent
            );
        }

        if (preg_match('/^([a-f0-9]{64})$/', $string)) {
            return array(
                'status' => 1,
                'x__type' => 32102, //MD5 Hash
            );
        }

        if (filter_var(trim($string), FILTER_VALIDATE_EMAIL)) {
            return array(
                'status' => 1,
                'x__type' => 32097, //Email
            );
        }

    }

    if (validateDate($string, 'Y-m-d H:i:s')) {
        return array(
            'status' => 1,
            'x__type' => 4318, //Date/time
        );
    }

    return array(
        'status' => 1,
        'x__type' => 4255, //Text (Default)
    );

}

function validateDate($date, $format)
{
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) == $date;
}

function current_link(){
    return 'https://' .get_server('SERVER_NAME') . get_server('REQUEST_URI');
}


function is_https_url($url){
    return substr($url, 0, 8) == 'https://';
}

function is_valid_e_string($string){
    return substr($string, 0, 1) == '@' && is_numeric(one_two_explode('@',' ',$string));
}

function is_valid_i_string($string){
    return substr($string, 0, 1) == '#' && is_numeric(one_two_explode('#',' ',$string));
}




function e_count_6194($e__id, $specific_id = 0){

    //NOTE HERE

    //Checks where in the database/platform a source might be referenced
    $e_count_6194 = array(); //Holds return values
    $CI =& get_instance();
    $e___6194 = $CI->config->item('e___6194');
    $query_index = array(
        4364 => 'SELECT count(x__id) as totals FROM table__x WHERE x__access IN (' . join(',', $CI->config->item('n___7359')) . ') AND x__creator=',
        4593 => 'SELECT count(x__id) as totals FROM table__x WHERE x__access IN (' . join(',', $CI->config->item('n___7359')) . ') AND x__type=',
    );

    foreach($query_index as $e_app_id => $query){

        if($specific_id && $specific_id!=$e_app_id){
            continue;
        }

        $query = $CI->db->query( $query . $e__id );
        foreach($query->result() as $row)
        {
            if($row->totals > 0){
                $e_count_6194[$e_app_id] = $row->totals;
            }
        }

    }

    return $e_count_6194;

}


function string_is_icon($icon_code){
    return !filter_var($icon_code, FILTER_VALIDATE_URL) && substr_count($icon_code,'fa');
}


function i__weight_calculator($i){

    //TODO Improve later (This is a very basic logic)
    $CI =& get_instance();
    $count_x = $CI->X_model->fetch(array(
        'x__access IN (' . join(',', $CI->config->item('n___7360')) . ')' => null, //ACTIVE
        '(x__left='.$i['i__id'].' OR x__right='.$i['i__id'].')' => null,
    ), array(), 0, 0, array(), 'COUNT(x__id) as totals');

    //Should we update?
    if($count_x[0]['totals'] != $i['i__weight']){
        return $CI->I_model->update($i['i__id'], array(
            'i__weight' => $count_x[0]['totals'],
        ));
    } else {
        return 0;
    }

}

function e__weight_calculator($e){

    //TODO Improve later (This is a very basic logic)
    $CI =& get_instance();
    $count_x = $CI->X_model->fetch(array(
        'x__access IN (' . join(',', $CI->config->item('n___7360')) . ')' => null, //ACTIVE
        '(x__down='.$e['e__id'].' OR x__up='.$e['e__id'].' OR x__creator='.$e['e__id'].')' => null,
    ), array(), 0, 0, array(), 'COUNT(x__id) as totals');

    //Should we update?
    if($count_x[0]['totals'] != $e['e__weight']){
        return $CI->E_model->update($e['e__id'], array(
            'e__weight' => $count_x[0]['totals'],
        ));
    } else {
        return 0;
    }

}

function filter_cache_group($search_e__id, $cache_e__id){

    //Determines which category an source belongs to

    $CI =& get_instance();
    foreach($CI->config->item('e___'.$cache_e__id) as $e__id => $m) {
        if(in_array($search_e__id, $CI->config->item('n___'.$e__id))){
            return $m;
        }
    }
    return false;
}

function update_description($before_string, $after_string){
    return 'Updated from ['.$before_string.'] to ['.$after_string.']';
}


function random_cover($e__id){
    $CI =& get_instance();
    $fetch = $CI->config->item('e___'.$e__id);
    $colors = array(' ',' ',' ',' ',' ',' ',' zq12273',' zq12274',' zq12274',' zq6255',' zq6255',' zq6255');
    return trim(one_two_explode('class="','"',$fetch[array_rand($fetch)]['m__cover']).$colors[array_rand($colors)]);
}

function format_percentage($percent){
    return number_format($percent, ( $percent < 10 ? 1 : 0 ));
}


function new_member_redirect($e__id, $sign_i__id){
    //Is there a redirect app?
    if($sign_i__id > 0) {
        return '/' . $sign_i__id;
    } elseif(isset($_GET['url'])) {
        return $_GET['url'];
    } else {
        return '/';
    }
}

function prefix_common_words($strs) {

    $prefix_common_words = array();

    if(count($strs)>=2){
        foreach($strs as $string){

            $words = explode(' ',$string);

            if(!count($prefix_common_words)){

                //Initialize the first title:
                $prefix_common_words = $words;

            } else {

                foreach($words as $word_count => $word){
                    if(!isset($prefix_common_words[$word_count]) || $prefix_common_words[$word_count]!=$word){

                        //We have some common words left, continue to remove these words onwards:
                        for($i=$word_count;$i<count($prefix_common_words);$i++){
                            unset($prefix_common_words[$i]);
                        }

                        break;  //No common words, terminate
                    }
                }

                if(!count($prefix_common_words)){
                    break;  //No common words, terminate
                }

            }
        }
    }

    return ( count($prefix_common_words) ? join(' ',$prefix_common_words).' '  : false );

}


function reset_cache($x__creator){
    $CI =& get_instance();
    $count = 0;
    foreach($CI->X_model->fetch(array(
        'x__type' => 14599, //Cache App
        'x__up IN (' . join(',', $CI->config->item('n___14599')) . ')' => null, //Cache Apps
        'x__time >' => date("Y-m-d H:i:s", (time() - view_memory(6404,14599))),
        'x__access IN (' . join(',', $CI->config->item('n___7359')) . ')' => null, //PUBLIC
    )) as $delete_cahce){
        //Delete email:
        $count += $CI->X_model->update($delete_cahce['x__id'], array(
            'x__access' => 6173, //Transaction Removed
        ), $x__creator, 14600 /* Delete Cache */);
    }
    return $count;
}

function filter_array($array, $match_key, $match_value, $return_all = false)
{

    //Searches through $array and attempts to find $array[$match_key] = $match_value
    if (!is_array($array) || count($array) < 1) {
        return false;
    }

    $all_matches = array();
    foreach($array as $key => $value) {
        if (isset($value[$match_key]) && ( is_array($match_value) ? in_array($value[$match_key], $match_value) : $value[$match_key]==$match_value )) {
            if($return_all){
                array_push($all_matches, $value[$match_key]);
            } else {
                return $array[$key];
            }
        }
    }


    if($return_all){

        return $all_matches;

    } else {
        //Could not find it!
        return false;
    }
}

function i_unlockable($i){
    $CI =& get_instance();
    return in_array($i['i__access'], $CI->config->item('n___31871') /* ACTIVE */);
}

function i_spots_remaining($i__id){

    $CI =& get_instance();
    $member_e = superpower_unlocked();

    //Any Limits on Selection?
    $spots_remaining = -1; //No limits
    $has_limits = $CI->X_model->fetch(array(
        'x__access IN (' . join(',', $CI->config->item('n___7359')) . ')' => null, //PUBLIC
        'x__type IN (' . join(',', $CI->config->item('n___33602')) . ')' => null, //Idea/Source Links Active
        'x__right' => $i__id,
        'x__up' => 26189,
    ), array(), 1);
    if(count($has_limits) && strlen($has_limits[0]['x__message']) && is_numeric($has_limits[0]['x__message'])){
        //We have a limit! See if we've met it already:
        $query_filters = array(
            'x__access IN (' . join(',', $CI->config->item('n___7359')) . ')' => null, //PUBLIC
            'x__type IN (' . join(',', $CI->config->item('n___6255')) . ')' => null, //DISCOVERIES
            'x__left' => $i__id,
        );
        if($member_e){
            //Do not count current user to give them option to edit & resubmit:
            $query_filters['x__creator !='] = $member_e['e__id'];
        }
        $query = $CI->X_model->fetch($query_filters, array(), 1, 0, array(), 'COUNT(x__id) as totals');
        $spots_remaining = intval($has_limits[0]['x__message'])-$query[0]['totals'];
        if($spots_remaining < 0){
            $spots_remaining = 0;
        }
    }
    
    return $spots_remaining;
}

function access_blocked($log_tnx, $log_message, $x__creator, $i__id, $x__up, $x__down){

    $return_i__id = $i__id;

    //Log Access Block:
    if($log_tnx){

        $CI =& get_instance();
        $access_blocked = $CI->X_model->create(array(
            'x__type' => ( $x__creator>0 ? 29737 : 30341 ), //Access Blocked
            'x__creator' => $x__creator,
            'x__left' => $i__id,
            'x__up' => $x__up,
            'x__down' => $x__down,
            'x__message' => $log_message,
        ));

        //Delete Current Selection:
        foreach($CI->X_model->fetch(array(
            'x__access IN (' . join(',', $CI->config->item('n___7359')) . ')' => null, //PUBLIC
            'x__type IN (' . join(',', $CI->config->item('n___32234')) . ')' => null, //Discovery Expansions
            'x__right' => $i__id, //This was select as an answer to x__left
            'x__left > 0' => null,
        ), array('x__left'), 0) as $x_progress) {

            //Find all answers
            foreach($CI->X_model->fetch(array(
                'x__access IN (' . join(',', $CI->config->item('n___7359')) . ')' => null, //PUBLIC
                'x__type IN (' . join(',', $CI->config->item('n___6255')) . ')' => null, //DISCOVERIES
                'x__creator' => $x__creator,
                'x__left' => $x_progress['x__left'],
            ), array(), 0) as $x){

                //Delete all Selections:
                foreach($CI->X_model->fetch(array(
                    'x__access IN (' . join(',', $CI->config->item('n___7359')) . ')' => null, //PUBLIC
                    'x__type IN (' . join(',', $CI->config->item('n___32234')) . ')' => null, //Discovery Expansions
                    'x__left' => $x_progress['x__left'],
                ), array('x__right'), 0) as $x2){
                    $CI->X_model->update($x2['x__id'], array(
                        'x__access' => 6173, //Transaction Removed
                        'x__reference' => $access_blocked['x__id'],
                    ), $x__creator, 29782 );
                }

                //Delete question discovery so the user can re-select:
                $CI->X_model->update($x['x__id'], array(
                    'x__access' => 6173, //Transaction Removed
                    'x__reference' => $access_blocked['x__id'],
                ), $x__creator, 29782 );

            }

            //Delete this answer:
            $CI->X_model->update($x_progress['x__id'], array(
                'x__access' => 6173, //Transaction Removed
                'x__reference' => $access_blocked['x__id'],
            ), $x__creator, 29782 );

            //Guide them back to the top:
            $return_i__id = $x_progress['x__left'];

            //We can only handle 1 question for now
            //TODO If multiple questions found, see which one is within top_i__id
            break;

        }

    }

    //Return false:
    return array(
        'status' => false,
        'return_i__id' => $return_i__id,
        'message' => $log_message,
    );


}

function i_is_available($i__id, $log_tnx, $check_inventory = true){

    $CI =& get_instance();
    $member_e = superpower_unlocked();
    $x__creator = ( $member_e ? $member_e['e__id'] : 0 );

    //Any Inclusion Any Requirements?
    $fetch_13865 = $CI->X_model->fetch(array(
        'x__right' => $i__id,
        'x__type' => 13865, //Must Include Any
        'x__access IN (' . join(',', $CI->config->item('n___7359')) . ')' => null, //PUBLIC
        'e__access IN (' . join(',', $CI->config->item('n___7358')) . ')' => null, //ACTIVE
    ), array('x__up'), 0);
    if(count($fetch_13865)){
        //Let's see if they meet any of these PREREQUISITES:
        $meets_inc1_prereq = false;
        if($x__creator > 0){
            foreach($fetch_13865 as $e_pre){
                if(( $member_e && $member_e['e__id']==$e_pre['x__up'] ) || count($CI->X_model->fetch(array(
                        'x__type IN (' . join(',', $CI->config->item('n___32292')) . ')' => null, //SOURCE LINKS
                        'x__access IN (' . join(',', $CI->config->item('n___7359')) . ')' => null, //PUBLIC
                        'x__up' => $e_pre['x__up'],
                        'x__down' => $x__creator,
                    )))){
                    $meets_inc1_prereq = true;
                    break;
                }
            }
        }
        if(!$meets_inc1_prereq && $x__creator > 0){
            return access_blocked($log_tnx, "You cannot play this note because you are missing a requirement, make sure you are logged in with the same email address that we sent you the email.",$x__creator, $i__id, 13865, ( isset($e_pre['x__up']) ? $e_pre['x__up'] : 0 ));
        }
    }

    //Any Inclusion All Requirements?
    $fetch_27984 = $CI->X_model->fetch(array(
        'x__right' => $i__id,
        'x__type' => 27984, //Must Include All
        'x__access IN (' . join(',', $CI->config->item('n___7359')) . ')' => null, //PUBLIC
        'e__access IN (' . join(',', $CI->config->item('n___7358')) . ')' => null, //ACTIVE
    ), array('x__up'), 0);
    if(count($fetch_27984)){
        //There are some requirements, Let's see if they meet all of them:
        $missing_es = '';
        $meets_inc2_prereq = 0;
        if($x__creator > 0){
            foreach($fetch_27984 as $e_pre){
                if($x__creator && (( $member_e && $member_e['e__id']==$e_pre['x__up'] ) || count($CI->X_model->fetch(array(
                        'x__type IN (' . join(',', $CI->config->item('n___32292')) . ')' => null, //SOURCE LINKS
                        'x__access IN (' . join(',', $CI->config->item('n___7359')) . ')' => null, //PUBLIC
                        'x__up' => $e_pre['x__up'],
                        'x__down' => $x__creator,
                    ))))){
                    $meets_inc2_prereq++;
                } else {
                    //Missing:
                    $missing_es .= ( strlen($missing_es) ? ' & ' : '' ).$e_pre['e__title'];
                }
            }
        }
        if($meets_inc2_prereq < count($fetch_27984) && $x__creator > 0){
            //Did not meet all requirements:
            return access_blocked($log_tnx, "You cannot play this note because you are ".( $x__creator ? "missing [".$missing_es."]" : "not logged in" ).", make sure you are logged in with the same email address that we sent you the email.",$x__creator, $i__id, 27984, ( isset($e_pre['x__up']) ? $e_pre['x__up'] : 0 ));
        }
    }

    //Any Exclusion All Requirements?
    $fetch_26600 = $CI->X_model->fetch(array(
        'x__right' => $i__id,
        'x__type' => 26600, //Must Exclude All
        'x__access IN (' . join(',', $CI->config->item('n___7359')) . ')' => null, //PUBLIC
        'e__access IN (' . join(',', $CI->config->item('n___7358')) . ')' => null, //ACTIVE
    ), array('x__up'), 0);
    if(count($fetch_26600)){
        //Let's see if they meet any of these PREREQUISITES:
        $excludes_all = false;
        if($x__creator > 0){
            foreach($fetch_26600 as $e_pre){
                if(( $member_e && $member_e['e__id']==$e_pre['x__up'] ) || count($CI->X_model->fetch(array(
                        'x__type IN (' . join(',', $CI->config->item('n___32292')) . ')' => null, //SOURCE LINKS
                        'x__access IN (' . join(',', $CI->config->item('n___7359')) . ')' => null, //PUBLIC
                        'x__up' => $e_pre['x__up'],
                        'x__down' => $x__creator,
                    )))){
                    //Found an exclusion, so skip this:
                    $excludes_all = false;
                    break;
                } else {
                    $excludes_all = true;
                }
            }
        }

        if(!$excludes_all && $x__creator > 0){
            return access_blocked($log_tnx, "You cannot play this note because you belong to [".$e_pre['e__title']."]",$x__creator, $i__id, 26600, ( isset($e_pre['x__up']) ? $e_pre['x__up'] : 0 ));
        }
    }


    //Any Limits on Selection?
    if($check_inventory && !i_spots_remaining($i__id)){
        //Limit is reached, cannot complete this at this time:
        return access_blocked($log_tnx, "You cannot play this note because there are no spots remaining.", $x__creator, $i__id, 26189, 0);
    }
    

    //All good:
    return array(
        'status' => true,
    );

}

function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[random_int(0, $charactersLength - 1)];
    }
    return $randomString;
}

function redirect_message($url, $message = null, $log_error = false)
{
    //An error handling function that would redirect member to $url with optional $message
    //Do we have a Message?
    $CI =& get_instance();
    $member_e = superpower_unlocked();

    if ($message) {
        $CI->session->set_flashdata('flash_message', $message);
    }

    if($log_error){
        //Log thie error:
        $CI->X_model->create(array(
            'x__message' => $url.' '.stripslashes($message),
            'x__type' => 4246, //Platform Bug Reports
            'x__creator' => ( $member_e ? $member_e['e__id'] : 0 ),
        ));
    }

    if (!$message) {
        //Do a permanent redirect if message not available:
        header("Location: " . $url, true, 301);
        exit;
    } else {
        header("Location: " . $url, true);
        exit;
    }
}

function session_delete(){
    $CI =& get_instance();
    $CI->session->sess_destroy();
    cookie_delete();
}

function cookie_delete(){
    unset($_COOKIE['auth_cookie']);
    setcookie('auth_cookie', null, -1, '/');
}

function universal_check() {
    date_default_timezone_set(view_memory(6404,11079));
    $CI =& get_instance();
    $first_segment = $CI->uri->segment(1);
    if(
        !superpower_unlocked()
        && isset($_COOKIE['auth_cookie'])
        && !(substr($first_segment, 0, 1)=='-' && in_array(intval(substr($first_segment, 1)), $CI->config->item('n___14582')))
    ) {
        header("Location: " . '/-4269'.( isset($_SERVER['REQUEST_URI']) ? '?url=' . urlencode($_SERVER['REQUEST_URI']) : '' ), true, 307);
        exit;
    }
}


function superpower_active($superpower_e__id, $boolean_only = false){

    if( intval($superpower_e__id)>0 ){

        $CI =& get_instance();
        $is_match = ( superpower_unlocked($superpower_e__id) ? ( in_array($superpower_e__id, $CI->session->userdata('session_superpowers_activated')) ? true : false ) : false);

        if($boolean_only){
            return $is_match;
        } else {
            return ' superpower-'.$superpower_e__id . ' ' . ( $is_match ? '' : ' hidden ' );
        }

    } else {

        //Ignore calls without a proper superpower:
        return false;

    }
}


function round_minutes($seconds){
    $minutes = round($seconds/60);
    return ($minutes <= 1 ? 1 : $minutes );
}



function count_unique_covers($x__type, $x__time_start = null, $x__time_end = null){

    $CI =& get_instance();

    //We need to count this:
    if($x__type==12274){

        //SOURCES
        $joined_by = array();
        $query_filters = array(
            'x__type IN (' . join(',', $CI->config->item('n___13548')) . ')' => null, //UNIQUE SOURCES
            'x__access IN (' . join(',', $CI->config->item('n___7359')) . ')' => null, //PUBLIC

        );

    } elseif($x__type==12273){

        //IDEAS
        $joined_by = array();
        $query_filters = array(
            'x__access IN (' . join(',', $CI->config->item('n___7359')) . ')' => null, //PUBLIC
            'x__type IN (' . join(',', $CI->config->item('n___13480')) . ')' => null, //UNIQUE IDEAS
        );

    } elseif($x__type==6255){

        //DISCOVERIES
        $joined_by = array();
        $query_filters = array(
            'x__access IN (' . join(',', $CI->config->item('n___7359')) . ')' => null, //PUBLIC
            'x__type IN (' . join(',', $CI->config->item('n___6255')) . ')' => null, //DISCOVERIES
        );

    } elseif($x__type==4341){

        //Ledger Transactions
        $joined_by = array();
        $query_filters = array();

    } else {

        //App Store
        $joined_by = array('x__down');
        $query_filters = array(
            'x__up' => $x__type,
            'x__type IN (' . join(',', $CI->config->item('n___32292')) . ')' => null, //SOURCE LINKS
            'x__access IN (' . join(',', $CI->config->item('n___7359')) . ')' => null, //PUBLIC
            'e__access IN (' . join(',', $CI->config->item('n___7358')) . ')' => null, //ACTIVE
        );

    }

    if(strtotime($x__time_start) > 0){
        $query_filters['x__time >='] = $x__time_start;
    }
    if(strtotime($x__time_end) > 0){
        $query_filters['x__time <='] = $x__time_end;
    }

    //Fetch Results:
    $query = $CI->X_model->fetch($query_filters, $joined_by, 1, 0, array(), 'COUNT(x__id) as totals');
    return intval($query[0]['totals']);

}




function home_url(){
    $CI =& get_instance();
    $member_e = superpower_unlocked();
    return ( $member_e ? '/@'.$member_e['e__id'] : '/' );
}

function superpower_unlocked($superpower_e__id = null, $force_redirect = 0)
{

    //Authenticates logged-in members with their session information
    $CI =& get_instance();
    $member_e = $CI->session->userdata('session_up');
    $has_session = ( is_array($member_e) && count($member_e) > 0 && $member_e );

    //Let's start checking various ways we can give member access:
    if ($has_session && !$superpower_e__id) {

        //No minimum level required, grant access IF member is logged in:
        return $member_e;

    } elseif ($has_session && in_array($superpower_e__id, $CI->session->userdata('session_superpowers_unlocked'))) {

        //They are part of one of the levels assigned to them:
        return $member_e;

    }

    //Still here?!
    //We could not find a reason to give member access, so block them:
    if (!$force_redirect) {

        return false;

    } else {

        //Block access:
        if($has_session){
            $goto_url = '/@'.$member_e['e__id'];
        } else {
            $goto_url = '/-4269'.( isset($_SERVER['REQUEST_URI']) ? '?url=' . urlencode($_SERVER['REQUEST_URI']) : '' );
        }

        //Now redirect:
        return redirect_message($goto_url, '<div class="msg alert alert-danger" role="alert"><span class="icon-block"><i class="fas fa-exclamation-circle zq6255"></i></span>'.view_unauthorized_message($superpower_e__id).'</div>');
    }

}

function get_server($var_name){
    return ( isset($_SERVER[$var_name]) ? $_SERVER[$var_name] : null );
}

function fetch_cookie_order($cookie_name){

    $CI =& get_instance();
    $current_cookie = get_cookie($cookie_name);
    $new_order_value = (is_null($current_cookie) ? 0 : intval($current_cookie)+1 );

    //Set or update the cookie:
    $CI->input->set_cookie(array(
        'name'   => $cookie_name,
        'value'  => $new_order_value."", //Cast to string
        'domain' => '.'.get_server('SERVER_NAME'),
        'expire' => '2592000', //1 Week
        'secure' => FALSE,
    ));

    return $new_order_value;
}

function qr_code($url, $width = 150, $height = 150) {
    $url    = urlencode($url);
    $image  = '<img src="http://chart.apis.google.com/chart?chs='.$width.'x'.$height.'&cht=qr&chl='.$url.'" alt="QR code" width="'.$width.'" height="'.$height.'"/>';
    return $image;
}

function upload_to_cdn($file_url, $x__creator = 0, $x__metadata = null, $is_local = false, $page_title = null)
{

    /*
     * A function that would save a file from URL to our Amazon CDN
     * */

    $CI =& get_instance();

    $file_name = md5($file_url . 'fileSavingSa!t') . '.' . fetch_file_ext($file_url);

    if (!$is_local) {
        //Save this remote file to local first:
        $file_path = 'application/cache/';


        //Fetch Remote:
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $file_url);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $result = curl_exec($ch);
        curl_close($ch);

        //Write in directory:
        $fp = @fopen($file_path . $file_name, 'w');
    }

    //MAKE SURE WE CAN ACCESS AWS:
    if (!($is_local || (isset($fp) && $fp)) || !require_once('application/libraries/aws/aws-autoloader.php')) {
        $CI->X_model->create(array(
            'x__type' => 4246, //Platform Bug Reports
            'x__creator' => $x__creator,
            'x__message' => 'upload_to_cdn() Failed to load AWS S3',
            'x__metadata' => array(
                'file_url' => $file_url,
                'x__metadata' => $x__metadata,
                'is_local' => ( $is_local ? 1 : 0 ),
            ),
        ));
        return array(
            'status' => 0,
            'message' => 'Failed to load AWS S3 module',
        );
    }


    if (isset($fp)) {
        fwrite($fp, $result);
        fclose($fp);
    }

    $s3 = new Aws\S3\S3Client([
        'version' => 'latest',
        'region' => 'us-west-2',
        'credentials' => $CI->config->item('cred_aws'),
    ]);
    $result = $s3->putObject(array(
        'Bucket' => 's3foundation', //Same bucket for now
        'Key' => $file_name,
        'SourceFile' => ($is_local ? $file_url : $file_path . $file_name),
        'ACL' => 'public-read'
    ));


    if (!isset($result['ObjectURL']) || !strlen($result['ObjectURL'])) {
        $CI->X_model->create(array(
            'x__type' => 4246, //Platform Bug Reports
            'x__creator' => $x__creator,
            'x__message' => 'upload_to_cdn() Failed to upload file to CDN',
            'x__metadata' => array(
                'file_url' => $file_url,
                'x__metadata' => $x__metadata,
                'is_local' => ( $is_local ? 1 : 0 ),
            ),
        ));
        return array(
            'status' => 0,
            'message' => 'Failed to upload file to CDN',
        );
    }


    //Delete local file:
    @unlink(($is_local ? $file_url : $file_path . $file_name));

    //Define new URL:
    $cdn_new_url = trim($result['ObjectURL']);

    if($x__creator < 1){
        //Just return URL:
        return array(
            'status' => 1,
            'cdn_url' => $cdn_new_url,
        );
    }

    //Create and transaction new source to CDN and uploader:
    $url_e = $CI->E_model->url($cdn_new_url, $x__creator, 0, $page_title);

    if(isset($url_e['e_url']['e__id']) && $url_e['e_url']['e__id'] > 0){

        //All good:
        return array(
            'status' => 1,
            'cdn_e' => $url_e['e_url'],
            'cdn_url' => $cdn_new_url,
        );

    } else {

        $CI->X_model->create(array(
            'x__type' => 4246, //Platform Bug Reports
            'x__creator' => $x__creator,
            'x__message' => 'upload_to_cdn() Failed to create new source from CDN file',
            'x__metadata' => array(
                'file_url' => $file_url,
                'x__metadata' => $x__metadata,
                'is_local' => ( $is_local ? 1 : 0 ),
            ),
        ));

        return array(
            'status' => 0,
            'message' => 'Failed to create new source from CDN file',
        );
    }
}



function analyze_domain($full_url){

    //Detects the base domain of a URL, and also if the URL is the base domain...

    //Here is a list of 2nd level TLDs that we need to consider so we can find the base domain:
    $second_level_tlds = array('.com.ac', '.edu.ac', '.gov.ac', '.net.ac', '.mil.ac', '.net.ae', '.gov.ae', '.org.ae', '.mil.ae', '.sch.ae', '.ac.ae', '.pro.ae', '.gov.af', '.edu.af', '.net.af', '.com.ag', '.org.ag', '.net.ag', '.co.ag', '.off.ai', '.com.ai', '.net.ai', '.gov.al', '.edu.al', '.org.al', '.com.al', '.net.al', '.tirana.al', '.soros.al', '.upt.al', '.com.an', '.net.an', '.org.an', '.co.ao', '.ed.ao', '.gv.ao', '.it.ao', '.og.ao', '.com.ar', '.gov.ar', '.int.ar', '.mil.ar', '.net.ar', '.e164.arpa', '.in-addr.arpa', '.iris.arpa', '.ip6.arpa', '.uri.arpa', '.gv.at', '.ac.at', '.co.at', '.or.at', '.asn.au', '.com.au', '.net.au', '.id.au', '.org.au', '.csiro.au', '.oz.au', '.info.au', '.conf.au', '.act.au', '.nsw.au', '.nt.au', '.qld.au', '.sa.au', '.tas.au', '.vic.au', '.gov.au', '.com.az', '.net.az', '.int.az', '.gov.az', '.biz.az', '.org.az', '.edu.az', '.mil.az', '.pp.az', '.name.az', '.com.bb', '.edu.bb', '.gov.bb', '.net.bb', '.com.bd', '.edu.bd', '.net.bd', '.gov.bd', '.org.bd', '.com.bm', '.edu.bm', '.org.bm', '.gov.bm', '.com.bn', '.edu.bn', '.org.bn', '.com.bo', '.org.bo', '.net.bo', '.gov.bo', '.gob.bo', '.edu.bo', '.tv.bo', '.mil.bo', '.agr.br', '.am.br', '.art.br', '.edu.br', '.com.br', '.coop.br', '.esp.br', '.far.br', '.fm.br', '.g12.br', '.gov.br', '.imb.br', '.ind.br', '.inf.br', '.mil.br', '.net.br', '.org.br', '.psi.br', '.rec.br', '.srv.br', '.tmp.br', '.tur.br', '.tv.br', '.etc.br', '.adm.br', '.adv.br', '.arq.br', '.ato.br', '.bio.br', '.bmd.br', '.cim.br', '.cng.br', '.cnt.br', '.ecn.br', '.eng.br', '.eti.br', '.fnd.br', '.fot.br', '.fst.br', '.ggf.br', '.jor.br', '.lel.br', '.mat.br', '.med.br', '.mus.br', '.not.br', '.ntr.br', '.odo.br', '.ppg.br', '.pro.br', '.psc.br', '.qsl.br', '.slg.br', '.trd.br', '.vet.br', '.zlg.br', '.dpn.br', '.com.bs', '.net.bs', '.org.bs', '.com.bt', '.edu.bt', '.gov.bt', '.net.bt', '.co.bw', '.org.bw', '.gov.by', '.ab.ca', '.bc.ca', '.mb.ca', '.nb.ca', '.nf.ca', '.nl.ca', '.ns.ca', '.nt.ca', '.nu.ca', '.on.ca', '.pe.ca', '.qc.ca', '.sk.ca', '.com.cd', '.net.cd', '.org.cd', '.com.ch', '.net.ch', '.org.ch', '.co.ck', '.ac.cn', '.com.cn', '.edu.cn', '.gov.cn', '.net.cn', '.org.cn', '.ah.cn', '.bj.cn', '.cq.cn', '.fj.cn', '.gd.cn', '.gs.cn', '.gz.cn', '.gx.cn', '.ha.cn', '.hb.cn', '.he.cn', '.hi.cn', '.hl.cn', '.hn.cn', '.jl.cn', '.js.cn', '.jx.cn', '.ln.cn', '.nm.cn', '.nx.cn', '.qh.cn', '.sc.cn', '.sd.cn', '.sh.cn', '.sn.cn', '.sx.cn', '.tj.cn', '.xj.cn', '.xz.cn', '.yn.cn', '.com.co', '.edu.co', '.org.co', '.gov.co', '.mil.co', '.net.co', '.ac.cr', '.co.cr', '.ed.cr', '.fi.cr', '.go.cr', '.or.cr', '.com.cu', '.edu.cu', '.org.cu', '.net.cu', '.gov.cu', '.com.cy', '.biz.cy', '.info.cy', '.ltd.cy', '.pro.cy', '.net.cy', '.org.cy', '.name.cy', '.tm.cy', '.ac.cy', '.ekloges.cy', '.press.cy', '.com.dm', '.net.dm', '.org.dm', '.edu.dm', '.edu.do', '.gov.do', '.gob.do', '.com.do', '.org.do', '.sld.do', '.web.do', '.net.do', '.mil.do', '.com.dz', '.org.dz', '.net.dz', '.gov.dz', '.edu.dz', '.asso.dz', '.pol.dz', '.com.ec', '.info.ec', '.net.ec', '.fin.ec', '.med.ec', '.pro.ec', '.org.ec', '.edu.ec', '.gov.ec', '.mil.ec', '.com.ee', '.org.ee', '.fie.ee', '.pri.ee', '.eun.eg', '.edu.eg', '.sci.eg', '.gov.eg', '.com.eg', '.org.eg', '.net.eg', '.com.es', '.nom.es', '.org.es', '.gob.es', '.edu.es', '.com.et', '.gov.et', '.org.et', '.edu.et', '.net.et', '.biz.et', '.name.et', '.biz.fj', '.com.fj', '.info.fj', '.name.fj', '.net.fj', '.org.fj', '.pro.fj', '.ac.fj', '.gov.fj', '.mil.fj', '.co.fk', '.org.fk', '.gov.fk', '.ac.fk', '.nom.fk', '.tm.fr', '.asso.fr', '.nom.fr', '.prd.fr', '.presse.fr', '.com.fr', '.com.ge', '.edu.ge', '.gov.ge', '.org.ge', '.mil.ge', '.net.ge', '.co.gg', '.net.gg', '.org.gg', '.com.gh', '.edu.gh', '.gov.gh', '.org.gh', '.com.gi', '.ltd.gi', '.gov.gi', '.mod.gi', '.edu.gi', '.com.gn', '.ac.gn', '.gov.gn', '.org.gn', '.com.gr', '.edu.gr', '.net.gr', '.org.gr', '.com.hk', '.edu.hk', '.gov.hk', '.idv.hk', '.net.hk', '.com.hn', '.edu.hn', '.org.hn', '.net.hn', '.mil.hn', '.iz.hr', '.from.hr', '.name.hr', '.com.ht', '.net.ht', '.firm.ht', '.shop.ht', '.info.ht', '.pro.ht', '.adult.ht', '.org.ht', '.art.ht', '.pol.ht', '.rel.ht', '.asso.ht', '.perso.ht', '.coop.ht', '.med.ht', '.edu.ht', '.co.hu', '.info.hu', '.org.hu', '.priv.hu', '.sport.hu', '.tm.hu', '.agrar.hu', '.bolt.hu', '.casino.hu', '.city.hu', '.erotica.hu', '.erotika.hu', '.film.hu', '.forum.hu', '.games.hu', '.hotel.hu', '.ingatlan.hu', '.jogasz.hu', '.konyvelo.hu', '.lakas.hu', '.media.hu', '.news.hu', '.reklam.hu', '.sex.hu', '.shop.hu', '.suli.hu', '.szex.hu', '.tozsde.hu', '.utazas.hu', '.ac.id', '.co.id', '.or.id', '.ac.il', '.co.il', '.org.il', '.net.il', '.k12.il', '.gov.il', '.muni.il', '.co.im', '.ltd.co.im', '.plc.co.im', '.net.im', '.gov.im', '.org.im', '.nic.im', '.co.in', '.firm.in', '.net.in', '.org.in', '.gen.in', '.ind.in', '.nic.in', '.ac.in', '.edu.in', '.res.in', '.gov.in', '.ac.ir', '.co.ir', '.gov.ir', '.net.ir', '.org.ir', '.gov.it', '.co.je', '.net.je', '.edu.jm', '.gov.jm', '.com.jm', '.net.jm', '.com.jo', '.org.jo', '.net.jo', '.edu.jo', '.gov.jo', '.ac.jp', '.ad.jp', '.co.jp', '.ed.jp', '.go.jp', '.gr.jp', '.lg.jp', '.ne.jp', '.hokkaido.jp', '.aomori.jp', '.iwate.jp', '.miyagi.jp', '.akita.jp', '.yamagata.jp', '.fukushima.jp', '.ibaraki.jp', '.tochigi.jp', '.gunma.jp', '.saitama.jp', '.chiba.jp', '.tokyo.jp', '.kanagawa.jp', '.niigata.jp', '.toyama.jp', '.ishikawa.jp', '.fukui.jp', '.yamanashi.jp', '.nagano.jp', '.gifu.jp', '.shizuoka.jp', '.aichi.jp', '.mie.jp', '.shiga.jp', '.kyoto.jp', '.osaka.jp', '.hyogo.jp', '.nara.jp', '.wakayama.jp', '.tottori.jp', '.shimane.jp', '.okayama.jp', '.hiroshima.jp', '.yamaguchi.jp', '.tokushima.jp', '.kagawa.jp', '.ehime.jp', '.kochi.jp', '.fukuoka.jp', '.saga.jp', '.nagasaki.jp', '.kumamoto.jp', '.oita.jp', '.miyazaki.jp', '.kagoshima.jp', '.okinawa.jp', '.sapporo.jp', '.sendai.jp', '.yokohama.jp', '.kawasaki.jp', '.nagoya.jp', '.kobe.jp', '.per.kh', '.com.kh', '.edu.kh', '.gov.kh', '.mil.kh', '.net.kh', '.co.kr', '.or.kr', '.com.kw', '.edu.kw', '.gov.kw', '.net.kw', '.org.kw', '.edu.ky', '.gov.ky', '.com.ky', '.org.ky', '.org.kz', '.edu.kz', '.net.kz', '.gov.kz', '.mil.kz', '.net.lb', '.org.lb', '.gov.lb', '.edu.lb', '.com.lc', '.org.lc', '.edu.lc', '.com.li', '.net.li', '.org.li', '.gov.li', '.gov.lk', '.sch.lk', '.net.lk', '.int.lk', '.com.lk', '.org.lk', '.edu.lk', '.ngo.lk', '.soc.lk', '.web.lk', '.ltd.lk', '.assn.lk', '.grp.lk', '.com.lr', '.edu.lr', '.gov.lr', '.org.lr', '.org.ls', '.gov.lt', '.mil.lt', '.gov.lu', '.mil.lu', '.org.lu', '.net.lu', '.com.lv', '.edu.lv', '.gov.lv', '.org.lv', '.mil.lv', '.id.lv', '.net.lv', '.asn.lv', '.com.ly', '.net.ly', '.gov.ly', '.plc.ly', '.edu.ly', '.sch.ly', '.med.ly', '.org.ly', '.co.ma', '.net.ma', '.gov.ma', '.org.ma', '.tm.mc', '.org.mg', '.nom.mg', '.gov.mg', '.prd.mg', '.tm.mg', '.com.mg', '.edu.mg', '.mil.mg', '.army.mil', '.navy.mil', '.com.mk', '.org.mk', '.com.mo', '.net.mo', '.org.mo', '.edu.mo', '.weather.mobi', '.music.mobi', '.org.mt', '.com.mt', '.gov.mt', '.edu.mt', '.com.mu', '.co.mu', '.aero.mv', '.biz.mv', '.com.mv', '.coop.mv', '.edu.mv', '.gov.mv', '.info.mv', '.int.mv', '.mil.mv', '.museum.mv', '.name.mv', '.net.mv', '.org.mv', '.ac.mw', '.co.mw', '.com.mw', '.coop.mw', '.edu.mw', '.gov.mw', '.int.mw', '.museum.mw', '.net.mw', '.com.mx', '.net.mx', '.org.mx', '.edu.mx', '.com.my', '.net.my', '.org.my', '.gov.my', '.edu.my', '.mil.my', '.edu.ng', '.com.ng', '.gov.ng', '.org.ng', '.gob.ni', '.com.ni', '.edu.ni', '.org.ni', '.nom.ni', '.000.nl', '.mil.no', '.stat.no', '.kommune.no', '.herad.no', '.priv.no', '.vgs.no', '.fhs.no', '.museum.no', '.fylkesbibl.no', '.folkebibl.no', '.idrett.no', '.com.np', '.org.np', '.edu.np', '.net.np', '.gov.np', '.gov.nr', '.edu.nr', '.biz.nr', '.info.nr', '.org.nr', '.com.nr', '.ac.nz', '.co.nz', '.cri.nz', '.gen.nz', '.geek.nz', '.govt.nz', '.iwi.nz', '.maori.nz', '.mil.nz', '.net.nz', '.org.nz', '.com.om', '.co.om', '.edu.om', '.ac.com', '.sch.om', '.gov.om', '.net.om', '.org.om', '.mil.om', '.museum.om', '.biz.om', '.pro.om', '.com.pa', '.ac.pa', '.sld.pa', '.gob.pa', '.edu.pa', '.org.pa', '.net.pa', '.abo.pa', '.ing.pa', '.med.pa', '.com.pe', '.org.pe', '.net.pe', '.edu.pe', '.mil.pe', '.gob.pe', '.com.pf', '.org.pf', '.com.pg', '.com.ph', '.gov.ph', '.com.pk', '.net.pk', '.edu.pk', '.org.pk', '.fam.pk', '.biz.pk', '.web.pk', '.gov.pk', '.gob.pk', '.gok.pk', '.gon.pk', '.gop.pk', '.com.pl', '.biz.pl', '.net.pl', '.art.pl', '.edu.pl', '.org.pl', '.ngo.pl', '.gov.pl', '.info.pl', '.mil.pl', '.waw.pl', '.warszawa.pl', '.wroc.pl', '.wroclaw.pl', '.krakow.pl', '.poznan.pl', '.lodz.pl', '.gda.pl', '.gdansk.pl', '.slupsk.pl', '.szczecin.pl', '.lublin.pl', '.bialystok.pl', '.olsztyn.pl', '.torun.pl', '.biz.pr', '.com.pr', '.edu.pr', '.gov.pr', '.info.pr', '.isla.pr', '.name.pr', '.net.pr', '.org.pr', '.law.pro', '.med.pro', '.edu.ps', '.gov.ps', '.sec.ps', '.plo.ps', '.com.ps', '.org.ps', '.com.pt', '.edu.pt', '.gov.pt', '.int.pt', '.net.pt', '.nome.pt', '.org.pt', '.net.py', '.org.py', '.gov.py', '.edu.py', '.com.ro', '.org.ro', '.tm.ro', '.nt.ro', '.nom.ro', '.info.ro', '.rec.ro', '.arts.ro', '.firm.ro', '.store.ro', '.www.ro', '.com.ru', '.net.ru', '.org.ru', '.pp.ru', '.msk.ru', '.int.ru', '.ac.ru', '.gov.rw', '.net.rw', '.edu.rw', '.ac.rw', '.com.rw', '.co.rw', '.int.rw', '.mil.rw', '.com.sa', '.edu.sa', '.sch.sa', '.med.sa', '.gov.sa', '.net.sa', '.org.sa', '.com.sb', '.gov.sb', '.net.sb', '.edu.sb', '.com.sc', '.gov.sc', '.net.sc', '.org.sc', '.com.sd', '.net.sd', '.org.sd', '.edu.sd', '.med.sd', '.tv.sd', '.gov.sd', '.org.se', '.pp.se', '.tm.se', '.brand.se', '.parti.se', '.press.se', '.komforb.se', '.kommunalforbund.se', '.komvux.se', '.lanarb.se', '.lanbib.se', '.naturbruksgymn.se', '.sshn.se', '.fhv.se', '.fhsk.se', '.fh.se', '.ab.se', '.c.se', '.d.se', '.e.se', '.f.se', '.g.se', '.h.se', '.i.se', '.k.se', '.m.se', '.n.se', '.o.se', '.s.se', '.t.se', '.u.se', '.w.se', '.x.se', '.y.se', '.z.se', '.ac.se', '.com.sg', '.net.sg', '.org.sg', '.gov.sg', '.edu.sg', '.per.sg', '.edu.sv', '.com.sv', '.gob.sv', '.org.sv', '.gov.sy', '.com.sy', '.net.sy', '.ac.th', '.co.th', '.in.th', '.go.th', '.mi.th', '.or.th', '.ac.tj', '.biz.tj', '.com.tj', '.co.tj', '.edu.tj', '.int.tj', '.name.tj', '.net.tj', '.org.tj', '.web.tj', '.gov.tj', '.go.tj', '.com.tn', '.intl.tn', '.gov.tn', '.org.tn', '.ind.tn', '.nat.tn', '.tourism.tn', '.info.tn', '.ens.tn', '.fin.tn', '.gov.to', '.gov.tp', '.com.tr', '.info.tr', '.biz.tr', '.net.tr', '.org.tr', '.web.tr', '.gen.tr', '.av.tr', '.dr.tr', '.bbs.tr', '.name.tr', '.tel.tr', '.gov.tr', '.bel.tr', '.pol.tr', '.mil.tr', '.k12.tr', '.co.tt', '.com.tt', '.org.tt', '.net.tt', '.biz.tt', '.info.tt', '.pro.tt', '.name.tt', '.edu.tt', '.gov.tv', '.edu.tw', '.gov.tw', '.mil.tw', '.com.tw', '.net.tw', '.org.tw', '.idv.tw', '.game.tw', '.ebiz.tw', '.club.tw', '.co.tz', '.ac.tz', '.go.tz', '.or.tz', '.com.ua', '.gov.ua', '.net.ua', '.edu.ua', '.cherkassy.ua', '.ck.ua', '.chernigov.ua', '.cn.ua', '.chernovtsy.ua', '.cv.ua', '.crimea.ua', '.dnepropetrovsk.ua', '.dp.ua', '.donetsk.ua', '.dn.ua', '.ivano-frankivsk.ua', '.if.ua', '.kharkov.ua', '.kh.ua', '.kherson.ua', '.ks.ua', '.khmelnitskiy.ua', '.km.ua', '.kiev.ua', '.kv.ua', '.kirovograd.ua', '.kr.ua', '.lugansk.ua', '.lg.ua', '.lutsk.ua', '.lviv.ua', '.nikolaev.ua', '.mk.ua', '.odessa.ua', '.od.ua', '.poltava.ua', '.pl.ua', '.rovno.ua', '.rv.ua', '.sebastopol.ua', '.sumy.ua', '.ternopil.ua', '.te.ua', '.uzhgorod.ua', '.vinnica.ua', '.vn.ua', '.zaporizhzhe.ua', '.zp.ua', '.zhitomir.ua', '.co.ug', '.ac.ug', '.sc.ug', '.go.ug', '.ne.ug', '.ac.uk', '.co.uk', '.gov.uk', '.ltd.uk', '.me.uk', '.mil.uk', '.mod.uk', '.net.uk', '.nic.uk', '.nhs.uk', '.org.uk', '.plc.uk', '.police.uk', '.sch.uk', '.bl.uk', '.british-library.uk', '.icnet.uk', '.jet.uk', '.nel.uk', '.nls.uk', '.national-library-scotland.uk', '.parliament.sch.uk', '.ak.us', '.al.us', '.ar.us', '.az.us', '.ca.us', '.co.us', '.ct.us', '.dc.us', '.de.us', '.dni.us', '.fed.us', '.fl.us', '.ga.us', '.hi.us', '.ia.us', '.id.us', '.il.us', '.in.us', '.isa.us', '.kids.us', '.ks.us', '.ky.us', '.la.us', '.ma.us', '.md.us', '.me.us', '.mi.us', '.mn.us', '.mo.us', '.ms.us', '.mt.us', '.nc.us', '.nd.us', '.ne.us', '.nh.us', '.nj.us', '.nm.us', '.nsn.us', '.nv.us', '.ny.us', '.oh.us', '.ok.us', '.or.us', '.pa.us', '.ri.us', '.sc.us', '.sd.us', '.tn.us', '.tx.us', '.ut.us', '.vt.us', '.va.us', '.wa.us', '.wi.us', '.wv.us', '.edu.uy', '.gub.uy', '.org.uy', '.com.uy', '.net.uy', '.com.ve', '.net.ve', '.org.ve', '.info.ve', '.co.ve', '.com.vi', '.org.vi', '.edu.vi', '.com.vn', '.net.vn', '.org.vn', '.edu.vn', '.gov.vn', '.int.vn', '.ac.vn', '.biz.vn', '.info.vn', '.name.vn', '.pro.vn', '.com.ye', '.net.ye', '.ac.yu', '.co.yu', '.org.yu', '.ac.za', '.city.za', '.co.za', '.edu.za', '.gov.za', '.law.za', '.mil.za', '.nom.za', '.org.za', '.school.za', '.alt.za', '.net.za', '.ngo.za', '.tm.za', '.co.zm', '.org.zm', '.gov.zm', '.sch.zm', '.co.zw', '.org.zw', '.gov.zw');


    $url_file_extension = null;

    //Parse domain:
    $full_url = str_replace('www.' , '', $full_url);
    $analyze = parse_url($full_url);
    $url_parts = explode('.', $analyze['host']);

    if(isset($analyze['path']) && strlen($analyze['path']) > 0){
        $path_parts = explode('.', $analyze['path']);
        if(count($path_parts) >= 2){
            $possible_extension = array_values(array_slice($path_parts, -1))[0];
            if(strlen($possible_extension) >= 2 && strlen($possible_extension) <= 4){
                //Yes, this seems like an extension:
                $url_file_extension = strtolower($possible_extension);
            }
        }
    }

    //Delete the TLD:
    $tld = null;
    foreach($second_level_tlds as $second_level_tld){
        if(substr_count($analyze['host'], $second_level_tld)==1){
            $tld = $second_level_tld;
            break;
        }
    }

    //Did we find it? Likely not...
    if(!$tld){
        $tld = '.'.end($url_parts);
    }

    $no_tld_domain = str_replace($tld, '', $analyze['host']);
    $no_tld_url_parts = explode('.', $no_tld_domain);
    $url_subdomain = trim(rtrim(str_replace(end($no_tld_url_parts), '', $no_tld_domain), '.'));

    //Return results:
    return array(
        'url_root' => ( !$url_subdomain && !isset($analyze['query']) && ( !isset($analyze['path']) || $analyze['path']=='/' ) ? 1 : 0 ),
        'url_domain' => end($no_tld_url_parts),
        'url_clean_domain' => 'http://'.end($no_tld_url_parts).$tld,
        'url_subdomain' => $url_subdomain,
        'url_tld' => end($no_tld_url_parts).$tld,
        'url_file_extension' => $url_file_extension,
    );

}


function js_php_redirect($url, $timer = 0){
    echo '<script> $(document).ready(function () { js_redirect(\''.$url.'\', '.$timer.'); }); </script>';
}

function i__validate_title($string){

    $title_clean = trim($string);
    while(substr_count($title_clean , '  ') > 0){
        $title_clean = str_replace('  ',' ',$title_clean);
    }

    //Validate:
    if(!strlen(trim($string))){

        return array(
            'status' => 0,
            'message' => 'Title missing',
        );

    } elseif (strlen($string) > view_memory(6404,4736)) {

        return array(
            'status' => 0,
            'message' => 'Title must be '.view_memory(6404,4736).' characters or less',
        );

    }

    //All good, return success:
    return array(
        'status' => 1,
        'i_clean_title' => $title_clean,
    );

}

function e__title_validate($string, $x__type = 0){

    //Validate:
    $CI =& get_instance();
    $e___4592 = $CI->config->item('e___4592');
    $errors = false;
    $title_clean = trim($string);
    while(substr_count($title_clean , '  ') > 0){
        $title_clean = str_replace('  ',' ',$title_clean);
    }

    if(!strlen(trim($string))){

        if($x__type){
            $title_clean = $e___4592[$x__type]['m__title'].' '.substr(md5(time() . rand(1,99999)), 0, 8);
        }

        $errors = array(
            'status' => 0,
            'message' => 'Name missing',
        );

    } elseif(strlen(trim($string)) < view_memory(6404,12232)){

        if($x__type){
            $title_clean = $e___4592[$x__type]['m__title'].' '.substr(md5(time() . rand(1,99999)), 0, 8);
        }

        $errors = array(
            'status' => 0,
            'message' => 'Name is shorter than the minimum ' . view_memory(6404,12232) . ' characters.',
        );

    } elseif (strlen($string) > view_memory(6404,6197)) {

        if($x__type){
            $title_clean = substr($string, 0, view_memory(6404,6197));
        }

        $errors = array(
            'status' => 0,
            'message' => 'Name must be '.view_memory(6404,6197).' characters or less',
        );

    }

    $title_clean = trim($title_clean);

    //Just the clean name?
    if($x__type){
        return $title_clean;
    }


    if($errors){

        return $errors;

    } else {
        //All good, return success:
        return array(
            'status' => 1,
            'e__title_clean' => $title_clean,
        );
    }
}

function send_qr($x__id,$x__creator){
    $CI =& get_instance();
    $CI->X_model->send_dm($x__creator, 'Your Atlas QR Code', 'To get your wristband upon arrival simply show a screenshot of your QR code that you can see here:'.
        "\n\n".'https://'.get_domain('m__message', $x__creator).'/-26560?x__id='.$x__id.'&x__creator='.$x__creator."\n\n".
        'Everyone needs a QR code to enter Atlas Camp and Anyone with your QR code can check-in on your behalf.'."\n");
}

function clean_phone($phone){
    $phone_numbers = preg_replace('/\D/', '', $phone);
    if(strlen($phone_numbers)==10){
        $phone_numbers = '+1'.$phone_numbers;
    }
    return $phone_numbers;
}

function random_adjective(){

    $adjectives = array('Amazing', 'Awesome', 'Adventurous', 'Ambitious', 'Adorable', 'Artistic', 'Agile', 'Acrobatic', 'Attractive', 'Alluring', 'Astonishing', 'Authentic', 'Awkward', 'Ancient', 'American', 'Australian', 'Austrian', 'African', 'Asian', 'Brave', 'Beautiful', 'Bright', 'Busy', 'Big', 'Bold', 'Basic', 'Blissful', 'Bouncy', 'Beneficial', 'Bashful', 'Black', 'Brown', 'Burgundy', 'Broad', 'British', 'Belgian', 'Brazilian', 'Creative', 'Confident', 'Cheerful', 'Calm', 'Cute', 'Clever', 'Curious', 'Charming', 'Courageous', 'Clean', 'Cool', 'Considerate', 'Caring', 'Crazy', 'Classic', 'Chic', 'Cloudy', 'Colombian', 'Chinese', 'Delightful', 'Dreamy', 'Daring', 'Dynamic', 'Dark', 'Decent', 'Drastic', 'Defiant', 'Dedicated', 'Deep', 'Desirable', 'Dirty', 'Dramatic', 'Dizzy', 'Demanding', 'Diligent', 'Dutch', 'Danish', 'Delicious', 'Dazzling', 'Easy', 'Elegant', 'Enthusiastic', 'Eager', 'Efficient', 'Empathetic', 'Excellent', 'Exciting', 'Effective', 'Extravagant', 'Entertaining', 'Exotic', 'Expressive', 'Expensive', 'Elaborate', 'European', 'Egyptian', 'Eastern', 'Elderly', 'Educational', 'Fantastic', 'Fabulous', 'Friendly', 'Funny', 'Fearless', 'Fresh', 'Fascinating', 'Fluffy', 'Fierce', 'Fine', 'Free', 'Frugal', 'French', 'Futuristic', 'Fast', 'Flat', 'Famous', 'Flawless', 'Formal', 'Frizzy', 'Gorgeous', 'Great', 'Gentle', 'Generous', 'Gracious', 'Genuine', 'Glorious', 'Graceful', 'Golden', 'Grand', 'Green', 'Growing', 'Groovy', 'Greek', 'Grumpy', 'Gothic', 'Gargantuan', 'Gigantic', 'German', 'Georgian', 'Happy', 'Hot', 'Humble', 'Honest', 'Healthy', 'Heavy', 'Handsome', 'High', 'Helpful', 'Hilarious', 'Heavenly', 'Harmonious', 'Hardworking', 'Historical', 'Heartfelt', 'Homey', 'Hungry', 'Huge', 'Hispanic', 'Hindu', 'Interesting', 'Intelligent', 'Incredible', 'Inspiring', 'Impressive', 'Imaginative', 'Inquisitive', 'Iconic', 'Indigo', 'Industrious', 'Inevitable', 'Inexpensive', 'Incomparable', 'Idealistic', 'Illustrious', 'Indian', 'Italian', 'Irresistible', 'Irrelevant', 'Icy', 'Joyful', 'Jolly', 'Jovial', 'Jaunty', 'Jaded', 'Jazzy', 'Jumpy', 'Juicy', 'Judgmental', 'Jumbled', 'Japanese', 'Javanese', 'Jewish', 'Jittery', 'Junior', 'Justified', 'Jubilant', 'Jade', 'Jumbo', 'Joint', 'Kind', 'Knowledgeable', 'Keen', 'Kooky', 'Knotty', 'Kinetic', 'Known', 'Keen-eyed', 'Knightly', 'Keen-witted', 'Kempt', 'Knockout', 'Knackered', 'Kindhearted', 'Kenyan', 'Kiddy', 'Knotted', 'Kyrgyzstani', 'Kindred', 'Kentuckian', 'Loud', 'Lively', 'Lazy', 'Loyal', 'Long', 'Lonely', 'Lovely', 'Large', 'Light', 'Low', 'Luxurious', 'Lasting', 'Literal', 'Learned', 'Lucky', 'Magnificent', 'Mysterious', 'Modern', 'Moody', 'Musical', 'Mighty', 'Masculine', 'Mesmerizing', 'Mindful', 'Memorable', 'Multicultural', 'Moral', 'Majestic', 'Mischievous', 'Mouthwatering', 'Mellow', 'Modest', 'Magical', 'Melodic', 'Mature', 'Nervous', 'Natural', 'New', 'Nice', 'Noble', 'Naughty', 'Neat', 'Nonchalant', 'Noisy', 'Narrow', 'Nostalgic', 'Needy', 'Negative', 'Nutritious', 'Nonstop', 'Noteworthy', 'Numerous', 'Notable', 'Nurturing', 'Nifty', 'Obvious', 'Original', 'Optimistic', 'Ordinary', 'Official', 'Outstanding', 'Open', 'Organic', 'Odd', 'Observant', 'Obedient', 'Opaque', 'Obsolete', 'Offensive', 'Oily', 'Old-fashioned', 'Ornate', 'Onyx', 'Overwhelming', 'Oceanic', 'Perfect', 'Patient', 'Positive', 'Powerful', 'Popular', 'Polite', 'Peaceful', 'Playful', 'Pleasant', 'Precious', 'Practical', 'Private', 'Proud', 'Profound', 'Pretty', 'Painful', 'Priceless', 'Puzzled', 'Persistent', 'Passionate', 'Quaint', 'Quick', 'Quiet', 'Quirky', 'Quizzical', 'Queenly', 'Quivering', 'Quotable', 'Qualified', 'Quantifiable', 'Questionable', 'Quarrelsome', 'Queasy', 'Quenched', 'Quack', 'Quilted', 'Quizzing', 'Reliable', 'Responsible', 'Romantic', 'Rich', 'Rude', 'Real', 'Radiant', 'Royal', 'Rough', 'Respectful', 'Red', 'Rational', 'Rustic', 'Radiant', 'Robust', 'Rare', 'Resilient', 'Reckless', 'Ready', 'Rambunctious', 'Strong', 'Smart', 'Serious', 'Sad', 'Special', 'Simple', 'Super', 'Sincere', 'Safe', 'Stunning', 'Sweet', 'Shy', 'Successful', 'Satisfied', 'Shiny', 'Silent', 'Sparkling', 'Strong-willed', 'Scary', 'Surprised', 'Tall', 'Talkative', 'Tasty', 'Tender', 'Terrific', 'Terrible', 'Thoughtful', 'Thrifty', 'Timely', 'Tough', 'Traditional', 'Trustworthy', 'Tremendous', 'Tricky', 'Tolerant', 'Tenacious', 'Tiny', 'Tired', 'Top', 'Trembling', 'Ugly', 'Ultimate', 'Unbelievable', 'Uncertain', 'Uncommon', 'Unconditional', 'Unconscious', 'Understanding', 'Unforgettable', 'Unhappy', 'Unique', 'United', 'Universal', 'Unusual', 'Upbeat', 'Uplifting', 'Urbane', 'Urgent', 'Useful', 'Useless', 'Valuable', 'Vague', 'Valid', 'Vast', 'Various', 'Vengeful', 'Vibrant', 'Victorious', 'Vigorous', 'Villainous', 'Vital', 'Vivacious', 'Vocal', 'Volatile', 'Volcanic', 'Voracious', 'Vulnerable', 'Vicious', 'Velvet', 'Verbal', 'Warm', 'Wild', 'Witty', 'Wise', 'Wonderful', 'Worried', 'Wondrous', 'Wealthy', 'Whimsical', 'Wicked', 'Wide', 'Wavy', 'Watery', 'Weighty', 'Wooden', 'Weak', 'Wary', 'Winning', 'Well-groomed', 'Wholesome', 'Xeric', 'Xerophytic', 'Xerotic', 'Xyloid', 'Xylonic', 'Xylophagous', 'Xanthic', 'Xanthous', 'Xerarch', 'Xylotomous', 'Xerographic', 'Xenial', 'Xenogenetic', 'Xenolithic', 'Xylophilous', 'Yellow', 'Young', 'Yielding', 'Yearly', 'Yummy', 'Yawning', 'Yucky', 'Yearning', 'Yeasty', 'Yielding', 'Youthful', 'Yare', 'Yclept', 'Yellowish', 'Yearlong', 'Youth', 'Zealous', 'Zesty', 'Zigzag', 'Zillionth', 'Zinciferous', 'Zingy', 'Zippered', 'Zippy', 'Zoological', 'Zonal', 'Ambitious', 'Amiable', 'Analytical', 'Assertive', 'Authentic', 'Bold', 'Calm', 'Charismatic', 'Charming', 'Cheerful', 'Compassionate', 'Confident', 'Conscientious', 'Considerate', 'Creative', 'Curious', 'Dependable', 'Diligent', 'Disciplined', 'Easygoing', 'Empathetic', 'Enthusiastic', 'Extraverted', 'Flexible', 'Friendly', 'Generous', 'Genuine', 'Gracious', 'Hardworking', 'Honest', 'Humble', 'Independent', 'Innovative', 'Insightful', 'Intelligent', 'Kind', 'Logical', 'Loyal', 'Open-minded', 'Optimistic', 'Outgoing', 'Passionate', 'Patient', 'Persistent', 'Practical', 'Rational', 'Reliable', 'Resourceful', 'Responsible', 'Self-confident', 'Happy', 'Sad', 'Angry', 'Fearful', 'Anxious', 'Excited', 'Frustrated', 'Nostalgic', 'Hopeful', 'Envious', 'Jealous', 'Empathetic', 'Curious', 'Surprised', 'Disappointed', 'Grateful', 'Confused', 'Content', 'Lonely', 'Loved', 'Joyful', 'Melancholic', 'Irritated', 'Apprehensive', 'Restless', 'Ecstatic', 'Distraught', 'Panicked', 'Annoyed', 'Numb', 'Scared', 'Enraged', 'Heartbroken', 'Amused', 'Overwhelmed', 'Grateful', 'Conflicted', 'Peaceful', 'Devastated', 'Empowered');

    return $adjectives[array_rand($adjectives)];
}

function send_sms($to_phone, $single_message, $e__id = 0, $x_data = array(), $template_id = 0, $x__website = 0, $log_tr = true){

    $CI =& get_instance();
    $twilio_account_sid = website_setting(30859);
    $twilio_auth_token = website_setting(30860);
    $twilio_from_number = website_setting(27673);
    if(!$twilio_from_number || !$twilio_auth_token || !$twilio_account_sid){

        //No way to send an SMS:
        if($log_tr){
            $CI->X_model->create(array(
                'x__message' => 'send_sms() missing either: '.$twilio_account_sid.' / '.$twilio_auth_token.' / '.$twilio_from_number,
                'x__type' => 4246, //Platform Bug Reports
                'x__creator' => $e__id,
                'x__website' => $x__website,
                'x__metadata' => array(
                    '$to_phone' => $to_phone,
                    '$single_message' => $single_message,
                    '$template_id' => $template_id,
                    '$x_data' => $x_data,
                ),
            ));
        }

        return false;
    }

    $post = array(
        'From' => $twilio_from_number,
        'Body' => $single_message,
        'To' => $to_phone,
    );

    $x = curl_init("https://api.twilio.com/2010-04-01/Accounts/".$twilio_account_sid."/SMS/Messages");
    curl_setopt($x, CURLOPT_POST, true);
    curl_setopt($x, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($x, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($x, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($x, CURLOPT_USERPWD, $twilio_account_sid.":".$twilio_auth_token);
    curl_setopt($x, CURLOPT_POSTFIELDS, http_build_query($post));
    $y = curl_exec($x);
    curl_close($x);


    if(substr_count($y, '<Code>21211</Code>')){

        //Invalid input, must be returned:
        return false;

    }

    $sms_success = substr_count($y, '<SMSMessage><Sid>');

    //Log transaction:
    if($log_tr){
        $CI->X_model->create(array_merge($x_data, array(
            'x__type' => ( $sms_success ? 27676 : 27678 ), //SMS Success/Fail
            'x__creator' => $e__id,
            'x__message' => $single_message,
            'x__down' => $template_id,
            'x__metadata' => array(
                'post' => $post,
                'response' => $y,
            ),
        )));
    }

    return true;

}

function send_email($to_emails, $subject, $email_body, $e__id = 0, $x_data = array(), $template_id = 0, $x__website = 0, $log_tr = true){

    $CI =& get_instance();
    $domain_email = '"'.get_domain('m__title', $e__id, $x__website).'" <'.website_setting(28614, $e__id, $x__website).'>';

    $name = 'New User';
    $ReplyToAddresses = array($domain_email);

    if($e__id > 0){
        $es = $CI->E_model->fetch(array(
            'e__id' => $e__id,
        ));
        if(count($es)){

            $name = $es[0]['e__title'];

            //Also fetch email for this user to populate the reply to:
            $e_emails = $CI->X_model->fetch(array(
                'x__up' => 3288, //Email
                'x__down' => $e__id,
                'x__type IN (' . join(',', $CI->config->item('n___32292')) . ')' => null, //SOURCE LINKS
                'x__access IN (' . join(',', $CI->config->item('n___7359')) . ')' => null, //PUBLIC
            ));
            if(count($e_emails) && filter_var($e_emails[0]['x__message'], FILTER_VALIDATE_EMAIL)){
                array_push($ReplyToAddresses, trim($e_emails[0]['x__message']));
            }
        }
    }

    //Email has no word limit to add header & footer:
    $e___6287 = $CI->config->item('e___6287'); //APP
    $email_message = view_shuffle_message(29749).' '.$name.' '.view_shuffle_message(29750)."\n\n";
    $email_message .= str_replace('e__id',$e__id,$email_body)."\n\n";
    $email_message .= view_shuffle_message(12691)."\n";
    $email_message .= get_domain('m__title', $e__id, $x__website);
    if($e__id > 0 && !in_array($template_id, $CI->config->item('n___31779'))){
        //User specific notifications:
        $email_message .= '<div><a href="https://'.get_domain('m__message', $e__id, $x__website).'/-28904?e__id='.$e__id.'&e__hash='.md5($e__id.view_memory(6404,30863)).'" style="font-size:10px;">'.$e___6287[28904]['m__title'].'</a></div>';
    }

    //Loadup amazon SES:
    require_once('application/libraries/aws/aws-autoloader.php');

    $client = new Aws\Ses\SesClient([
        //'profile' => 'default',
        'version' => 'latest',
        'region' => 'us-west-2',
        'credentials' => $CI->config->item('cred_aws'),
    ]);

    $response = $client->sendEmail(array(
        // Source is required
        'Source' => $domain_email,
        // Destination is required
        'Destination' => array(
            'ToAddresses' => $to_emails,
            'CcAddresses' => array(),
            'BccAddresses' => array(),
        ),
        // Message is required
        'Message' => array(
            // Subject is required
            'Subject' => array(
                // Data is required
                'Data' => $subject,
                'Charset' => 'UTF-8',
            ),
            // Body is required
            'Body' => array(
                'Text' => array(
                    // Data is required
                    'Data' => strip_tags($email_message),
                    'Charset' => 'UTF-8',
                ),
                'Html' => array(
                    // Data is required
                    'Data' => nl2br($email_message),
                    'Charset' => 'UTF-8',
                ),
            ),
        ),
        'ReplyToAddresses' => $ReplyToAddresses,
        'ReturnPath' => $domain_email,
    ));

    //Log transaction:
    if($log_tr){
        $CI->X_model->create(array_merge($x_data, array(
            'x__type' => 29399,
            'x__down' => $template_id,
            'x__creator' => $e__id,
            'x__message' => $subject."\n\n".$email_message,
            'x__metadata' => array(
                'to' => $to_emails,
                'subject' => $subject,
                'message' => $email_message,
                'response' => $response,
            ),
        )));
    }


    return $response;

}

function website_setting($setting_id = 0, $initiator_e__id = 0, $x__website = 0){

    $CI =& get_instance();
    $source_id = 0; //Assume no domain unless found below...
    $server_name = get_server('SERVER_NAME');

    if(!$initiator_e__id){
        $member_e = superpower_unlocked();
        if($member_e && $member_e['e__id']>0){
            $initiator_e__id = $member_e['e__id'];
        }
    }

    if(strlen($server_name)){
        foreach($CI->config->item('e___14870') as $x__type => $m) {
            if ($server_name == $m['m__message']){
                $source_id = $x__type;
                break;
            }
        }
    }

    $source_id = ( $source_id ? $source_id : ( $x__website > 0 ? $x__website : 13601 /* Atlas */ ) );

    if(!$setting_id){
        return $source_id;
    }


    $e___domain_sett = $CI->config->item('e___'.$setting_id); //DOMAINS

    if(!isset($e___domain_sett[$source_id]) || !strlen($e___domain_sett[$source_id]['m__message'])){
        $target_return = ( in_array($setting_id, $CI->config->item('n___6404')) ? view_memory(6404,$setting_id) : false );
    } else {
        $target_return = $e___domain_sett[$source_id]['m__message'];
    }

    return $target_return;

}



function message_list($i__id, $e__id, $exclude_e, $include_e){

    $CI =& get_instance();
    $message_list = array(
        'unique_users_id' => array(),
        'unique_users_count' => 0,
        'full_list' => '',
        'email_list' => '',
        'email_count' => 0,
        'phone_count' => 0,
        'phone_array' => array(),
        'email_array' => array(),
    );

    $query = array();
    if(strlen($i__id)){
        $query = array_merge($query, $CI->X_model->fetch(array(
            'x__type IN (' . join(',', $CI->config->item('n___6255')) . ')' => null, //DISCOVERIES
            'x__access IN (' . join(',', $CI->config->item('n___7359')) . ')' => null, //PUBLIC
            'e__access IN (' . join(',', $CI->config->item('n___7358')) . ')' => null, //ACTIVE
            'x__left IN (' . $i__id . ')' => null, //ACTIVE
        ), array('x__creator'), 0, 0, array('x__id' => 'DESC')));
    }

    if(strlen($e__id)){
        $query = array_merge($query, $CI->X_model->fetch(array(
            'x__type IN (' . join(',', $CI->config->item('n___32292')) . ')' => null, //SOURCE LINKS
            'x__access IN (' . join(',', $CI->config->item('n___7359')) . ')' => null, //PUBLIC
            'e__access IN (' . join(',', $CI->config->item('n___7358')) . ')' => null, //ACTIVE
            'x__up IN (' . $e__id . ')' => null,
        ), array('x__down'), 0, 0, array('x__id' => 'DESC')));
    }

    $already_added = array(); //Prevent duplicates
    foreach($query as $subscriber){

        //Make sure not already added AND not unsubscribed:
        if(in_array($subscriber['e__id'], $already_added)){
            continue;
        }
        if (!count($CI->X_model->fetch(array(
            'x__up IN (' . join(',', $CI->config->item('n___30820')) . ')' => null, //Active Subscriber
            'x__down' => $subscriber['e__id'],
            'x__type IN (' . join(',', $CI->config->item('n___32292')) . ')' => null, //SOURCE LINKS
            'x__access IN (' . join(',', $CI->config->item('n___7359')) . ')' => null, //PUBLIC
        )))) {
            continue;
        }

        //Any exclusions?
        if(strlen($exclude_e) && count($CI->X_model->fetch(array(
                'x__type IN (' . join(',', $CI->config->item('n___32292')) . ')' => null, //SOURCE LINKS
                'x__access IN (' . join(',', $CI->config->item('n___7359')) . ')' => null, //PUBLIC
                'x__up IN (' . $exclude_e . ')' => null,
                'x__down' => $subscriber['e__id'],
            )))){
            continue;
        }

        if(strlen($include_e) && !count($CI->X_model->fetch(array(
                'x__type IN (' . join(',', $CI->config->item('n___32292')) . ')' => null, //SOURCE LINKS
                'x__access IN (' . join(',', $CI->config->item('n___7359')) . ')' => null, //PUBLIC
                'x__up IN (' . $include_e . ')' => null,
                'x__down' => $subscriber['e__id'],
            )))){
            continue;
        }




        //Fetch email & phone:
        $e_emails = $CI->X_model->fetch(array(
            'x__up' => 3288, //Email
            'x__down' => $subscriber['e__id'],
            'x__type IN (' . join(',', $CI->config->item('n___32292')) . ')' => null, //SOURCE LINKS
            'x__access IN (' . join(',', $CI->config->item('n___7359')) . ')' => null, //PUBLIC
        ));
        $e_phones = $CI->X_model->fetch(array(
            'x__up' => 4783, //Phone
            'x__down' => $subscriber['e__id'],
            'x__type IN (' . join(',', $CI->config->item('n___32292')) . ')' => null, //SOURCE LINKS
            'x__access IN (' . join(',', $CI->config->item('n___7359')) . ')' => null, //PUBLIC
        ));

        $e_email = ( count($e_emails) && filter_var($e_emails[0]['x__message'], FILTER_VALIDATE_EMAIL) ? $e_emails[0]['x__message'] : false );
        $e_phone = ( count($e_phones) && strlen($e_phones[0]['x__message'])>=10 ? $e_phones[0]['x__message'] : false );

        $contacrt_forms = ( $e_email ? 1 : 0 ) + ( $e_phone ? 1 : 0 );

        array_push($already_added, $subscriber['e__id']);



        if(!$e_email){
            //No way to reach them:
            //continue;
        }

        $message_list['unique_users_count']++;
        if($e_email){
            $message_list['email_count']++;
            $message_list['email_list'] .= ( strlen($message_list['email_list']) ? ", " : '' ).$e_email;
        }
        if($e_phone){
            $message_list['phone_count']++;
        }

        $first_name = one_two_explode('',' ', $subscriber['e__title']);
        array_push( $message_list['unique_users_id'],  intval($subscriber['e__id']));

        $u_names = $CI->X_model->fetch(array(
            'x__access IN (' . join(',', $CI->config->item('n___7359')) . ')' => null, //PUBLIC
            'x__down' => $subscriber['e__id'],
            'x__type IN (' . join(',', $CI->config->item('n___32292')) . ')' => null, //SOURCE LINKS
            'x__up' => 30198, //Full Name
        ));

        $message_list['full_list'] .= ( count($u_names) ? $u_names[0]['x__message'] : $subscriber['e__title'] )."\t".$e_email."\t".$e_phone."\n";

    }

    return $message_list;

}

function get_domain($var_field, $initiator_e__id = 0, $x__website = 0){
    $CI =& get_instance();
    $domain_source = website_setting(0, $initiator_e__id, $x__website);
    $e___14870 = $CI->config->item('e___14870'); //DOMAINS
    return $e___14870[$domain_source][$var_field];
}



function e_of_e($e__id, $member_e = array()){

    if(!$member_e){
        //Fetch from session:
        $member_e = superpower_unlocked();
    }

    if(!$member_e || $e__id < 1){
        return false;
    }

    //Ways a Member can modify a source:
    $CI =& get_instance();
    return (

        //Member is the source
        $e__id==$member_e['e__id']

        //Member has Advance source editing superpower
        || superpower_active(13422, true)

        //Member created the source
        || count($CI->X_model->fetch(array(
            'x__creator' => $member_e['e__id'],
            'x__down' => $e__id,
            'x__type' => 4251, //New Source Created
        )))

        //If Source Follows this Member
        || count($CI->X_model->fetch(array(
            'x__type IN (' . join(',', $CI->config->item('n___32292')) . ')' => null, //SOURCE LINKS
            'x__access IN (' . join(',', $CI->config->item('n___7359')) . ')' => null, //PUBLIC
            'x__up' => $member_e['e__id'],
            'x__down' => $e__id,
        )))
    );

}

function e_of_i($i__id, $member_e = array()){

    if(!$member_e){
        //Fetch from session:
        $member_e = superpower_unlocked();
    }

    if(!$member_e || $i__id < 1){
        return false;
    }

    //Ways a member can modify an idea:
    $CI =& get_instance();
    return (
        superpower_active(12700, true) || //WALKIE TALKIE
        (
            superpower_active(10939, true) && //PEN
            (
                count($CI->X_model->fetch(array( //Member created the idea
                    'x__type' => 4250, //IDEA CREATOR
                    'x__right' => $i__id,
                    'x__creator' => $member_e['e__id'],
                ))) ||
                count($CI->X_model->fetch(array( //IDEA SOURCE
                    'x__access IN (' . join(',', $CI->config->item('n___7359')) . ')' => null, //PUBLIC
                    'x__type IN (' . join(',', $CI->config->item('n___31919')) . ')' => null, //IDEA AUTHOR
                    'x__right' => $i__id,
                    'x__up' => $member_e['e__id'],
                )))
            )
        )
    );

}


function boost_power()
{
    //Give php page instance more processing power
    ini_set('memory_limit', '-1');
    ini_set('max_execution_time', 0);
}

function sources_currently_sorted($e__id){
    $CI =& get_instance();
    return count( $CI->X_model->fetch(array(
        'x__weight >' => 0, //Sorted
        'x__up' => $e__id,
        'x__type IN (' . join(',', $CI->config->item('n___32292')) . ')' => null, //SOURCE LINKS
        'x__access IN (' . join(',', $CI->config->item('n___7360')) . ')' => null, //ACTIVE
    ), array(), 1) );
}

function first_line($string){
    return $string;
}

function public_app($e){
    $CI =& get_instance();
    return in_array($e['e__access'], $CI->config->item('n___7357')) && !in_array($e['e__id'], $CI->config->item('n___32141'));
}

function update_algolia($s__type = null, $s__id = 0, $return_row_only = false)
{

    if(!intval(view_memory(6404,12678))){
        return false;
    }

    $CI =& get_instance();

    /*
     *
     * Syncs data with Algolia Index
     *
     * */

    if($s__type && !in_array($s__type , $CI->config->item('n___12761'))){
        return array(
            'status' => 0,
            'message' => 'Object type is invalid',
        );
    } elseif(($s__type && !$s__id) || ($s__id && !$s__type)){
        return array(
            'status' => 0,
            'message' => 'Must define both object type and ID',
        );
    }


    $e___4737 = $CI->config->item('e___4737'); //Idea Status

    //Define the support objects indexed on algolia:
    $s__id = intval($s__id);
    $limits = array();


    if($s__type==12273){
        $focus_field_id = 'i__id';
        $focus_field_privacy = 'i__access';
    } elseif($s__type==12274 || $s__type==6287){
        $focus_field_id = 'e__id';
        $focus_field_privacy = 'e__access';
    }


    if (!$return_row_only) {
        //Load Algolia Index
        $search_index = load_algolia('alg_index');
    }


    //Which objects are we fetching?
    if ($s__type) {

        //We'll only fetch a specific type:
        $fetch_objects = array($s__type);

    } else {

        //Do both ideas and sources:
        $fetch_objects = $CI->config->item('n___12761');
        $batch_command = array(); //TODO To be populated:
        /*
        array_push($batch_command, array(
            'action' => 'addObject',
            'indexName' => 'alg_index',
            'body' => $export_row,
        ));
        */

        if (!$return_row_only) {

            //We need to update the entire index, so let's truncate it first:
            $search_index->clearIndex();

            //Boost processing power:
            boost_power();
        }
    }

    //Featured Tree for all Domains:
    /*
    $features_sources = array();
    foreach($CI->config->item('e___30829') as $x__type => $m) {
        if(in_array($x__type , $CI->config->item('n___14870')) && strlen($m['m__message']) && is_array($CI->config->item('n___'.substr($m['m__message'], 1))) && count($CI->config->item('n___'.substr($m['m__message'], 1)))){
            foreach($CI->config->item('n___'.substr($m['m__message'], 1)) as $featured_e){
                $features_sources[$featured_e] = $x__type;
            }
        }
    }
    */


    $all_export_rows = array();
    $all_db_rows = array();
    $synced_count = 0;

    foreach($fetch_objects as $loop_obj){

        //Reset limits:
        unset($filters);

        //Fetch item(s) for updates including their followings:
        if ($loop_obj == 12273) {

            $filters['x__type'] = 4250;

            if($s__id){
                $filters['x__right'] = $s__id;
            } else {
                $filters['i__access IN (' . join(',', $CI->config->item('n___31871')) . ')'] = null; //ACTIVE
                $filters['x__access IN (' . join(',', $CI->config->item('n___7360')) . ')'] = null; //ACTIVE
            }

            $db_rows[$loop_obj] = $CI->X_model->fetch($filters, array('x__right'), 0);

        } elseif ($loop_obj == 12274) {

            $filters['x__type'] = 4251;

            if($s__id){
                $filters['x__down'] = $s__id;
            } else {
                $filters['e__access IN (' . join(',', $CI->config->item('n___7358')) . ')'] = null; //ACTIVE
                $filters['x__access IN (' . join(',', $CI->config->item('n___7360')) . ')'] = null; //ACTIVE
            }

            $db_rows[$loop_obj] = $CI->X_model->fetch($filters, array('x__down'), 0);

        } elseif (!$s__id && $loop_obj == 6287) {

            $db_rows[$loop_obj] = $CI->X_model->fetch(array(
                'x__up' => 6287, //Featured Apps
                'x__type IN (' . join(',', $CI->config->item('n___32292')) . ')' => null, //SOURCE LINKS
                'x__access IN (' . join(',', $CI->config->item('n___7359')) . ')' => null, //PUBLIC
                'e__access IN (' . join(',', $CI->config->item('n___7358')) . ')' => null, //ACTIVE
            ), array('x__down'), 0);

        }




        //Build the index:
        foreach($db_rows[$loop_obj] as $s) {

            //Prepare variables:
            unset($export_row);
            $export_row = array();


            //Update Weight if single update:
            if($s__id){
                //Update weight before updating this object:
                if($s__type==12273){
                    i__weight_calculator($s);
                } elseif($s__type==12274){
                    e__weight_calculator($s);
                }
            }


            //Attempt to fetch Algolia object ID from object Metadata:
            if($s__type){

                if (intval($s['algolia__id']) > 0) {
                    //We found it! Let's just update existing algolia record
                    $export_row['objectID'] = intval($s['algolia__id']);
                }

            } else {

                //Clear possible metadata algolia ID's that have been cached:
                if ($loop_obj == 12273) {
                    $CI->I_model->update($s['i__id'], array(
                        'algolia__id' => null,
                    ));
                } elseif ($loop_obj == 12274) {
                    $CI->E_model->update($s['e__id'], array(
                        'algolia__id' => null,
                    ));
                }

            }

            //To hold followings info
            $export_row['_tags'] = array();
            $export_row['s__keywords'] = '';

            //Now build object-specific index:
            if ($loop_obj == 12273) {

                //IDEAS
                //See if this idea has a time-range:
                $export_row['s__type'] = $loop_obj;
                $export_row['s__id'] = intval($s['i__id']);
                //$export_row['s__url'] = '/~' . $s['i__id'];
                $export_row['s__url'] = '/' . $s['i__id'];
                $export_row['s__access'] = intval($s['i__access']);
                $export_row['s__cover'] = '';
                $export_row['s__title'] = $s['i__title'];
                $export_row['s__weight'] = intval($s['i__weight']);

                if(in_array($s['i__access'], $CI->config->item('n___31874'))){
                    array_push($export_row['_tags'], 'publicly_searchable');
                }

                //Top/Bottom Idea Keywords
                foreach ($CI->X_model->fetch(array(
                    'x__access IN (' . join(',', $CI->config->item('n___7359')) . ')' => null, //PUBLIC
                    'i__access IN (' . join(',', $CI->config->item('n___31870')) . ')' => null, //PUBLIC
                    'x__type IN (' . join(',', $CI->config->item('n___12840')) . ')' => null, //IDEA LINKS TWO-WAY
                    'x__left' => $s['i__id'],
                ), array('x__right'), 0, 0, array('x__weight' => 'ASC')) as $i) {
                    $export_row['s__keywords'] .= $i['i__title'] . ' ';
                }
                foreach ($CI->X_model->fetch(array(
                    'x__access IN (' . join(',', $CI->config->item('n___7359')) . ')' => null, //PUBLIC
                    'i__access IN (' . join(',', $CI->config->item('n___31870')) . ')' => null, //PUBLIC
                    'x__type IN (' . join(',', $CI->config->item('n___12840')) . ')' => null, //IDEA LINKS TWO-WAY
                    'x__right' => $s['i__id'],
                ), array('x__left'), 0, 0, array('x__weight' => 'ASC')) as $i) {
                    $export_row['s__keywords'] .= $i['i__title'] . ' ';
                }

                //Idea Sources Keywords
                foreach($CI->X_model->fetch(array(
                    'x__access IN (' . join(',', $CI->config->item('n___7359')) . ')' => null, //PUBLIC
                    'x__type IN (' . join(',', $CI->config->item('n___33602')) . ')' => null, //Idea/Source Links Active
                    'x__right' => $s['i__id'],
                    'LENGTH(x__message)>0' => null,
                ), array(), 0) as $x){
                    $export_row['s__keywords'] .= $x['x__message'] . ' ';
                }

                //Idea Authors access tag
                foreach($CI->X_model->fetch(array(
                    'x__access IN (' . join(',', $CI->config->item('n___7359')) . ')' => null, //PUBLIC
                    'x__type IN (' . join(',', $CI->config->item('n___31919')) . ')' => null, //SOURCE AUTHORS
                    'x__right' => $s['i__id'],
                ), array('x__up'), 0) as $x){
                    array_push($export_row['_tags'], 'z_' . $x['e__id']);
                }

            } elseif ($loop_obj == 12274) {

                //SOURCES
                $export_row['s__type'] = $loop_obj;
                $export_row['s__id'] = intval($s['e__id']);
                $export_row['s__url'] = '/@' . $s['e__id'];
                $export_row['s__access'] = intval($s['e__access']);
                $export_row['s__cover'] = $s['e__cover'];
                $export_row['s__title'] = $s['e__title'];
                $export_row['s__weight'] = intval($s['e__weight']);

                //Add source as their own author:
                array_push($export_row['_tags'], 'z_' . $s['x__creator']);

                if($s['x__creator']!=$s['e__id']){
                    //Also give access to source themselves, in case they can login:
                    array_push($export_row['_tags'], 'z_' . $s['e__id']);
                }

                //Is this an image?
                if(strlen($s['e__cover'])){
                    array_push($export_row['_tags'], 'has_image');
                }
                if(in_array($s['e__access'], $CI->config->item('n___7357'))){
                    array_push($export_row['_tags'], 'publicly_searchable');
                }

                //Fetch Following:
                foreach($CI->X_model->fetch(array(
                    'x__type IN (' . join(',', $CI->config->item('n___32292')) . ')' => null, //SOURCE LINKS
                    'x__down' => $s['e__id'], //This follower source
                    'x__access IN (' . join(',', $CI->config->item('n___7360')) . ')' => null, //ACTIVE
                    'e__access IN (' . join(',', $CI->config->item('n___7358')) . ')' => null, //ACTIVE
                ), array('x__up'), 0, 0, array('e__title' => 'DESC')) as $x) {

                    //Add tags:
                    array_push($export_row['_tags'], 'z_' . $x['e__id']);

                    //Add Keywords:
                    $export_row['s__keywords'] .= $x['e__title']. ( strlen($x['x__message']) ? ' '.$x['x__message'] : '' ) . ' ';

                }

                //Append Discovery Written Responses to Keywords
                foreach($CI->X_model->fetch(array(
                    'x__access IN (' . join(',', $CI->config->item('n___7359')) . ')' => null, //PUBLIC
                    'x__type IN (' . join(',', $CI->config->item('n___29133')) . ')' => null, //Written Responses
                    'x__creator' => $s['e__id'], //This follower source
                ), array('x__creator'), 0, 0, array('x__time' => 'DESC')) as $x){
                    $export_row['s__keywords'] .= $x['x__message'] . ' ';
                }

            } elseif ($loop_obj == 6287) {

                //Non-Hidden APPS
                $export_row['s__type'] = $loop_obj;
                $export_row['s__id'] = intval($s['e__id']);
                $export_row['s__url'] = '/-' . $s['e__id'];
                $export_row['s__access'] = intval($s['e__access']);
                $export_row['s__cover'] = $s['e__cover'];
                $export_row['s__title'] = $s['e__title'];
                $export_row['s__weight'] = intval($s['e__weight']);

                array_push($export_row['_tags'], 'is_app');

                if(public_app($s)){
                    array_push($export_row['_tags'], 'publicly_searchable');
                }

                //Fetch Following:
                foreach($CI->X_model->fetch(array(
                    'x__type IN (' . join(',', $CI->config->item('n___32292')) . ')' => null, //SOURCE LINKS
                    'x__down' => $s['e__id'], //This follower source
                    'x__access IN (' . join(',', $CI->config->item('n___7360')) . ')' => null, //ACTIVE
                    'e__access IN (' . join(',', $CI->config->item('n___7358')) . ')' => null, //ACTIVE
                ), array('x__up'), 0, 0, array('e__title' => 'DESC')) as $x) {

                    //Add tags:
                    array_push($export_row['_tags'], 'z_' . $x['e__id']);

                    //Add Keywords:
                    $export_row['s__keywords'] .= $x['e__title']. ( strlen($x['x__message']) ? ' '.$x['x__message'] : '' ) . ' ';
                }

            }

            //Prep Keywords:
            $export_row['s__keywords'] = trim(strip_tags($export_row['s__keywords']));

            //Add to main array
            array_push($all_export_rows, $export_row);
            array_push($all_db_rows, $s);

        }
    }

    //Did we find anything?
    if(count($all_export_rows) < 1){

        return false;

    } elseif($return_row_only){

        if($s__id > 0){
            //We  have a specific item we're looking for...
            return $all_export_rows[0];
        } else {
            return $all_export_rows;
        }

    }

    //Now let's see what to do with the index (Update, Create or delete)
    if ($s__type) {

        //We should have fetched a single item only, meaning $all_export_rows[0] is what we are focused on...

        //What's the status? Is it active or should it be deleted?
        if (in_array($all_db_rows[0][$focus_field_privacy], array(6178 /* Source Deleted */, 6182 /* Idea Deleted */))) {

            if (isset($all_export_rows[0]['objectID'])) {

                //Object is deleted locally but still indexed remotely on Algolia, so let's delete it from Algolia:

                //Delete from algolia:
                $algolia_results = $search_index->deleteObject($all_export_rows[0]['objectID']);

                $synced_count += 1;

            } else {
                //Nothing to do here since we don't have the Algolia object locally!
            }

        } else {

            if (isset($all_export_rows[0]['objectID'])) {

                //Update existing index:
                $algolia_results = $search_index->saveObjects($all_export_rows);

            } else {

                //We do not have an index to an Algolia object locally, so create a new index:
                $algolia_results = $search_index->addObjects($all_export_rows);


                //also set its algolia_id to 0 locally:


                //Now update local database with the new objectIDs:
                if (isset($algolia_results['objectIDs']) && count($algolia_results['objectIDs']) == 1 ) {
                    foreach($algolia_results['objectIDs'] as $key => $algolia_id) {
                        if ($s__type == 12273) {
                            $CI->I_model->update($all_db_rows[$key][$focus_field_id], array(
                                'algolia__id' => $algolia_id,
                            ));
                        } elseif ($s__type == 12274) {
                            $CI->E_model->update($all_db_rows[$key][$focus_field_id], array(
                                'algolia__id' => $algolia_id,
                            ));
                        }
                    }
                }

            }

            $synced_count += 1;
        }

    } else {



        /*
         *
         * This is a mass update request.
         *
         * All remote objects have previously been deleted from the Algolia
         * index & metadata algolia_ids have all been set to zero!
         *
         * Create new items and update local
         *
         * */

        $algolia_results = $search_index->addObjects($all_export_rows);

        //Now update database with the objectIDs:
        if (isset($algolia_results['objectIDs']) && count($algolia_results['objectIDs']) == count($all_db_rows) ) {

            foreach($algolia_results['objectIDs'] as $key => $algolia_id) {

                if (isset($all_db_rows[$key]['i__id'])) {
                    $CI->I_model->update($all_db_rows[$key][( isset($all_db_rows[$key]['i__id']) ? 'i__id' : 'e__id')], array(
                        'algolia__id' => intval($algolia_id),
                    ));
                } else {
                    $CI->E_model->update($all_db_rows[$key][( isset($all_db_rows[$key]['i__id']) ? 'i__id' : 'e__id')], array(
                        'algolia__id' => intval($algolia_id),
                    ));
                }

            }
        }

        $synced_count += count($algolia_results['objectIDs']);

    }



    //Return results:
    return array(
        'status' => ( $synced_count > 0 ? 1 : 0),
        'message' => $synced_count . ' objects sync with Algolia',
    );

}

function x__metadata_update($x__id, $new_fields, $x__creator = 0)
{

    $CI =& get_instance();

    /*
     *
     * Enables the easy manipulation of the text metadata field which holds cache data for developers
     *
     *
     * $obj:                    The Member, Idea or Transaction itself.
     *                          We're looking for the $obj ID and METADATA
     *
     * $new_fields:             The new array of metadata fields to be Set,
     *                          Updated or Deleted (If set to null)
     *
     * */

    if ($x__id < 1 || count($new_fields) < 1) {
        return false;
    }

    //Fetch metadata for this object:
    $db_objects = $CI->X_model->fetch(array(
        'x__id' => $x__id,
    ));

    if (count($db_objects) < 1) {
        return false;
    }


    //Prepare newly fetched metadata:
    $metadata = (strlen($db_objects[0]['x__metadata']) > 0 ? unserialize($db_objects[0]['x__metadata']) : array() );

    //Go through all the new fields and see if they differ from current metadata fields:
    foreach($new_fields as $metadata_key => $metadata_value) {

        //We are doing an absolute adjustment if needed:
        if (is_null($metadata_value)) {

            //Member asked to delete this value:
            unset($metadata[$metadata_key]);

        } else {

            //Set Value
            $metadata[$metadata_key] = $metadata_value;

        }
    }

    //Should be all good:
    return $CI->X_model->update($x__id, array(
        'x__metadata' => $metadata,
    ));

}


function one_two_explode($one, $two, $string)
{
    //A quick function to extract a subset of $string between $one and $two
    if (strlen($one) > 0) {
        if (substr_count($string, $one) < 1) {
            return NULL;
        }
        $temp = explode($one, $string, 2);
        if (strlen($two) > 0) {
            $temp = explode($two, $temp[1], 2);
            return trim($temp[0]);
        } else {
            return trim($temp[1]);
        }
    } else {
        $temp = explode($two, $string, 2);
        return trim($temp[0]);
    }
}



function extract_youtube_id($url)
{

    //Attemp to extract YouTube ID from URL:
    $video_id = null;

    if (substr_count($url, 'youtube.com/embed/') == 1) {

        //We might have start and end here too!
        $video_id = trim(one_two_explode('youtube.com/embed/', '?', $url));

    } elseif (substr_count($url, 'youtube.com/watch?v=') == 1) {

        $video_id = trim(one_two_explode('youtube.com/watch?v=', '&', $url));

    } elseif (substr_count($url, 'youtube.com/watch') == 1 && substr_count($url, '&v=') == 1) {

        $video_id = trim(one_two_explode('&v=', '&', $url));

    } elseif (substr_count($url, 'youtu.be/') == 1) {

        $video_id = trim(one_two_explode('youtu.be/', '?', $url));

    }

    //This should be 11 characters!
    if (strlen($video_id) == 11) {
        return $video_id;
    } else {
        return false;
    }
}