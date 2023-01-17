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

function extract_cover_type($cover_code, $append_zq){
    $CI =& get_instance();
    foreach($CI->config->item('e___14874') as $e__id => $m) {
        if(substr_count($cover_code, 'zq'.$e__id)){
            return ( $append_zq ? 'zq' : '' ).$e__id;
        }
    }
}

function e_x__type($string = null){
    $detected_x_type = x_detect_type($string);
    if ($detected_x_type['status']){
        return $detected_x_type['x__type'];
    }
    return 0;
}

function current_coin_id(){

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


function x_detect_type($string)
{

    /*
     * Detect what type of Source URL type should we create
     * based on options listed in this idea: @4227
     * */

    $string = trim($string);
    $CI =& get_instance();


    if (is_null($string) || !strlen($string)) {
        return array(
            'status' => 1,
            'x__type' => 4230, //Raw
        );
    }

    //Is it a currency?
    foreach($CI->config->item('e___26661') as $x__type_currency => $m_currency) {
        if (substr($string, 0, 4)==$m_currency['m__message'].' ' && is_numeric(substr($string, 4))) {
            return array(
                'status' => 1,
                'x__type' => 26661,
            );
        }
    }

    if ((strlen(intval($string)) == strlen($string) || (in_array(substr($string , 0, 1), array('+','-')) && strlen(intval(substr($string , 1))) == strlen(substr($string , 1)))) && (intval($string) != 0 || $string == '0')) {
        return array(
            'status' => 1,
            'x__type' => 4319, //Number
        );
    }

    if (substr($string, 0, 1)=='/' && substr($string, 0, 2)!='//' && !substr_count($string, ' ')) {
        return array(
            'status' => 1,
            'x__type' => 14728, //Relative URL
        );
    }

    if (filter_var($string, FILTER_VALIDATE_URL)) {
        return $CI->E_model->url($string); //See what type of URL (this could fail if duplicate, etc...)
    }

    if (strlen($string)>9 && is_valid_date($string) && !is_numeric($string)) {
        return array(
            'status' => 1,
            'x__type' => 4318, //Date/time
        );
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

    return array(
        'status' => 1,
        'x__type' => 4255, //Text (Default)
    );
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

function is_emojis_only( $string ) {

    // Array of emoji (v12, 2019) unicodes from https://unicode.org/emoji/charts/emoji-list.html

    $unicodes = array( '1F600','1F603','1F604','1F601','1F606','1F605','1F923','1F602','1F642','1F643','1F609','1F60A','1F607','1F970','1F60D','1F929','1F618','1F617','263A','1F61A','1F619','1F60B','1F61B','1F61C','1F92A','1F61D','1F911','1F917','1F92D','1F92B','1F914','1F910','1F928','1F610','1F611','1F636','1F60F','1F612','1F644','1F62C','1F925','1F60C','1F614','1F62A','1F924','1F634','1F637','1F912','1F915','1F922','1F92E','1F927','1F975','1F976','1F974','1F635','1F92F','1F920','1F973','1F60E','1F913','1F9D0','1F615','1F61F','1F641','2639','1F62E','1F62F','1F632','1F633','1F97A','1F626','1F627','1F628','1F630','1F625','1F622','1F62D','1F631','1F616','1F623','1F61E','1F613','1F629','1F62B','1F971','1F624','1F621','1F620','1F92C','1F608','1F47F','1F480','2620','1F4A9','1F921','1F479','1F47A','1F47B','1F47D','1F47E','1F916','1F63A','1F638','1F639','1F63B','1F63C','1F63D','1F640','1F63F','1F63E','1F648','1F649','1F64A','1F48B','1F48C','1F498','1F49D','1F496','1F497','1F493','1F49E','1F495','1F49F','2763','1F494','2764','1F9E1','1F49B','1F49A','1F499','1F49C','1F90E','1F5A4','1F90D','1F4AF','1F4A2','1F4A5','1F4AB','1F4A6','1F4A8','1F573','1F4A3','1F4AC','1F441','FE0F','200D','1F5E8','FE0F','1F5E8','1F5EF','1F4AD','1F4A4','1F44B','1F91A','1F590','270B','1F596','1F44C','1F90F','270C','1F91E','1F91F','1F918','1F919','1F448','1F449','1F446','1F595','1F447','261D','1F44D','1F44E','270A','1F44A','1F91B','1F91C','1F44F','1F64C','1F450','1F932','1F91D','1F64F','270D','1F485','1F933','1F4AA','1F9BE','1F9BF','1F9B5','1F9B6','1F442','1F9BB','1F443','1F9E0','1F9B7','1F9B4','1F440','1F441','1F445','1F444','1F476','1F9D2','1F466','1F467','1F9D1','1F471','1F468','1F9D4','1F471','200D','2642','FE0F','1F468','200D','1F9B0','1F468','200D','1F9B1','1F468','200D','1F9B3','1F468','200D','1F9B2','1F469','1F471','200D','2640','FE0F','1F469','200D','1F9B0','1F469','200D','1F9B1','1F469','200D','1F9B3','1F469','200D','1F9B2','1F9D3','1F474','1F475','1F64D','1F64D','200D','2642','FE0F','1F64D','200D','2640','FE0F','1F64E','1F64E','200D','2642','FE0F','1F64E','200D','2640','FE0F','1F645','1F645','200D','2642','FE0F','1F645','200D','2640','FE0F','1F646','1F646','200D','2642','FE0F','1F646','200D','2640','FE0F','1F481','1F481','200D','2642','FE0F','1F481','200D','2640','FE0F','1F64B','1F64B','200D','2642','FE0F','1F64B','200D','2640','FE0F','1F9CF','1F9CF','200D','2642','FE0F','1F9CF','200D','2640','FE0F','1F647','1F647','200D','2642','FE0F','1F647','200D','2640','FE0F','1F926','1F926','200D','2642','FE0F','1F926','200D','2640','FE0F','1F937','1F937','200D','2642','FE0F','1F937','200D','2640','FE0F','1F468','200D','2695','FE0F','1F469','200D','2695','FE0F','1F468','200D','1F393','1F469','200D','1F393','1F468','200D','1F3EB','1F469','200D','1F3EB','1F468','200D','2696','FE0F','1F469','200D','2696','FE0F','1F468','200D','1F33E','1F469','200D','1F33E','1F468','200D','1F373','1F469','200D','1F373','1F468','200D','1F527','1F469','200D','1F527','1F468','200D','1F3ED','1F469','200D','1F3ED','1F468','200D','1F4BC','1F469','200D','1F4BC','1F468','200D','1F52C','1F469','200D','1F52C','1F468','200D','1F4BB','1F469','200D','1F4BB','1F468','200D','1F3A4','1F469','200D','1F3A4','1F468','200D','1F3A8','1F469','200D','1F3A8','1F468','200D','2708','FE0F','1F469','200D','2708','FE0F','1F468','200D','1F680','1F469','200D','1F680','1F468','200D','1F692','1F469','200D','1F692','1F46E','1F46E','200D','2642','FE0F','1F46E','200D','2640','FE0F','1F575','1F575','FE0F','200D','2642','FE0F','1F575','FE0F','200D','2640','FE0F','1F482','1F482','200D','2642','FE0F','1F482','200D','2640','FE0F','1F477','1F477','200D','2642','FE0F','1F477','200D','2640','FE0F','1F934','1F478','1F473','1F473','200D','2642','FE0F','1F473','200D','2640','FE0F','1F472','1F9D5','1F935','1F470','1F930','1F931','1F47C','1F385','1F936','1F9B8','1F9B8','200D','2642','FE0F','1F9B8','200D','2640','FE0F','1F9B9','1F9B9','200D','2642','FE0F','1F9B9','200D','2640','FE0F','1F9D9','1F9D9','200D','2642','FE0F','1F9D9','200D','2640','FE0F','1F9DA','1F9DA','200D','2642','FE0F','1F9DA','200D','2640','FE0F','1F9DB','1F9DB','200D','2642','FE0F','1F9DB','200D','2640','FE0F','1F9DC','1F9DC','200D','2642','FE0F','1F9DC','200D','2640','FE0F','1F9DD','1F9DD','200D','2642','FE0F','1F9DD','200D','2640','FE0F','1F9DE','1F9DE','200D','2642','FE0F','1F9DE','200D','2640','FE0F','1F9DF','1F9DF','200D','2642','FE0F','1F9DF','200D','2640','FE0F','1F486','1F486','200D','2642','FE0F','1F486','200D','2640','FE0F','1F487','1F487','200D','2642','FE0F','1F487','200D','2640','FE0F','1F6B6','1F6B6','200D','2642','FE0F','1F6B6','200D','2640','FE0F','1F9CD','1F9CD','200D','2642','FE0F','1F9CD','200D','2640','FE0F','1F9CE','1F9CE','200D','2642','FE0F','1F9CE','200D','2640','FE0F','1F468','200D','1F9AF','1F469','200D','1F9AF','1F468','200D','1F9BC','1F469','200D','1F9BC','1F468','200D','1F9BD','1F469','200D','1F9BD','1F3C3','1F3C3','200D','2642','FE0F','1F3C3','200D','2640','FE0F','1F483','1F57A','1F574','1F46F','1F46F','200D','2642','FE0F','1F46F','200D','2640','FE0F','1F9D6','1F9D6','200D','2642','FE0F','1F9D6','200D','2640','FE0F','1F9D7','1F9D7','200D','2642','FE0F','1F9D7','200D','2640','FE0F','1F93A','1F3C7','26F7','1F3C2','1F3CC','1F3CC','FE0F','200D','2642','FE0F','1F3CC','FE0F','200D','2640','FE0F','1F3C4','1F3C4','200D','2642','FE0F','1F3C4','200D','2640','FE0F','1F6A3','1F6A3','200D','2642','FE0F','1F6A3','200D','2640','FE0F','1F3CA','1F3CA','200D','2642','FE0F','1F3CA','200D','2640','FE0F','26F9','26F9','FE0F','200D','2642','FE0F','26F9','FE0F','200D','2640','FE0F','1F3CB','1F3CB','FE0F','200D','2642','FE0F','1F3CB','FE0F','200D','2640','FE0F','1F6B4','1F6B4','200D','2642','FE0F','1F6B4','200D','2640','FE0F','1F6B5','1F6B5','200D','2642','FE0F','1F6B5','200D','2640','FE0F','1F938','1F938','200D','2642','FE0F','1F938','200D','2640','FE0F','1F93C','1F93C','200D','2642','FE0F','1F93C','200D','2640','FE0F','1F93D','1F93D','200D','2642','FE0F','1F93D','200D','2640','FE0F','1F93E','1F93E','200D','2642','FE0F','1F93E','200D','2640','FE0F','1F939','1F939','200D','2642','FE0F','1F939','200D','2640','FE0F','1F9D8','1F9D8','200D','2642','FE0F','1F9D8','200D','2640','FE0F','1F6C0','1F6CC','1F9D1','200D','1F91D','200D','1F9D1','1F46D','1F46B','1F46C','1F48F','1F469','200D','2764','FE0F','200D','1F48B','200D','1F468','1F468','200D','2764','FE0F','200D','1F48B','200D','1F468','1F469','200D','2764','FE0F','200D','1F48B','200D','1F469','1F491','1F469','200D','2764','FE0F','200D','1F468','1F468','200D','2764','FE0F','200D','1F468','1F469','200D','2764','FE0F','200D','1F469','1F46A','1F468','200D','1F469','200D','1F466','1F468','200D','1F469','200D','1F467','1F468','200D','1F469','200D','1F467','200D','1F466','1F468','200D','1F469','200D','1F466','200D','1F466','1F468','200D','1F469','200D','1F467','200D','1F467','1F468','200D','1F468','200D','1F466','1F468','200D','1F468','200D','1F467','1F468','200D','1F468','200D','1F467','200D','1F466','1F468','200D','1F468','200D','1F466','200D','1F466','1F468','200D','1F468','200D','1F467','200D','1F467','1F469','200D','1F469','200D','1F466','1F469','200D','1F469','200D','1F467','1F469','200D','1F469','200D','1F467','200D','1F466','1F469','200D','1F469','200D','1F466','200D','1F466','1F469','200D','1F469','200D','1F467','200D','1F467','1F468','200D','1F466','1F468','200D','1F466','200D','1F466','1F468','200D','1F467','1F468','200D','1F467','200D','1F466','1F468','200D','1F467','200D','1F467','1F469','200D','1F466','1F469','200D','1F466','200D','1F466','1F469','200D','1F467','1F469','200D','1F467','200D','1F466','1F469','200D','1F467','200D','1F467','1F5E3','1F464','1F465','1F463','1F9B0','1F9B1','1F9B3','1F9B2','1F435','1F412','1F98D','1F9A7','1F436','1F415','1F9AE','1F415','200D','1F9BA','1F429','1F43A','1F98A','1F99D','1F431','1F408','1F981','1F42F','1F405','1F406','1F434','1F40E','1F984','1F993','1F98C','1F42E','1F402','1F403','1F404','1F437','1F416','1F417','1F43D','1F40F','1F411','1F410','1F42A','1F42B','1F999','1F992','1F418','1F98F','1F99B','1F42D','1F401','1F400','1F439','1F430','1F407','1F43F','1F994','1F987','1F43B','1F428','1F43C','1F9A5','1F9A6','1F9A8','1F998','1F9A1','1F43E','1F983','1F414','1F413','1F423','1F424','1F425','1F426','1F427','1F54A','1F985','1F986','1F9A2','1F989','1F9A9','1F99A','1F99C','1F438','1F40A','1F422','1F98E','1F40D','1F432','1F409','1F995','1F996','1F433','1F40B','1F42C','1F41F','1F420','1F421','1F988','1F419','1F41A','1F40C','1F98B','1F41B','1F41C','1F41D','1F41E','1F997','1F577','1F578','1F982','1F99F','1F9A0','1F490','1F338','1F4AE','1F3F5','1F339','1F940','1F33A','1F33B','1F33C','1F337','1F331','1F332','1F333','1F334','1F335','1F33E','1F33F','2618','1F340','1F341','1F342','1F343','1F347','1F348','1F349','1F34A','1F34B','1F34C','1F34D','1F96D','1F34E','1F34F','1F350','1F351','1F352','1F353','1F95D','1F345','1F965','1F951','1F346','1F954','1F955','1F33D','1F336','1F952','1F96C','1F966','1F9C4','1F9C5','1F344','1F95C','1F330','1F35E','1F950','1F956','1F968','1F96F','1F95E','1F9C7','1F9C0','1F356','1F357','1F969','1F953','1F354','1F35F','1F355','1F32D','1F96A','1F32E','1F32F','1F959','1F9C6','1F95A','1F373','1F958','1F372','1F963','1F957','1F37F','1F9C8','1F9C2','1F96B','1F371','1F358','1F359','1F35A','1F35B','1F35C','1F35D','1F360','1F362','1F363','1F364','1F365','1F96E','1F361','1F95F','1F960','1F961','1F980','1F99E','1F990','1F991','1F9AA','1F366','1F367','1F368','1F369','1F36A','1F382','1F370','1F9C1','1F967','1F36B','1F36C','1F36D','1F36E','1F36F','1F37C','1F95B','2615','1F375','1F376','1F37E','1F377','1F378','1F379','1F37A','1F37B','1F942','1F943','1F964','1F9C3','1F9C9','1F9CA','1F962','1F37D','1F374','1F944','1F52A','1F3FA','1F30D','1F30E','1F30F','1F310','1F5FA','1F5FE','1F9ED','1F3D4','26F0','1F30B','1F5FB','1F3D5','1F3D6','1F3DC','1F3DD','1F3DE','1F3DF','1F3DB','1F3D7','1F9F1','1F3D8','1F3DA','1F3E0','1F3E1','1F3E2','1F3E3','1F3E4','1F3E5','1F3E6','1F3E8','1F3E9','1F3EA','1F3EB','1F3EC','1F3ED','1F3EF','1F3F0','1F492','1F5FC','1F5FD','26EA','1F54C','1F6D5','1F54D','26E9','1F54B','26F2','26FA','1F301','1F303','1F3D9','1F304','1F305','1F306','1F307','1F309','2668','1F3A0','1F3A1','1F3A2','1F488','1F3AA','1F682','1F683','1F684','1F685','1F686','1F687','1F688','1F689','1F68A','1F69D','1F69E','1F68B','1F68C','1F68D','1F68E','1F690','1F691','1F692','1F693','1F694','1F695','1F696','1F697','1F698','1F699','1F69A','1F69B','1F69C','1F3CE','1F3CD','1F6F5','1F9BD','1F9BC','1F6FA','1F6B2','1F6F4','1F6F9','1F68F','1F6E3','1F6E4','1F6E2','26FD','1F6A8','1F6A5','1F6A6','1F6D1','1F6A7','2693','26F5','1F6F6','1F6A4','1F6F3','26F4','1F6E5','1F6A2','2708','1F6E9','1F6EB','1F6EC','1FA82','1F4BA','1F681','1F69F','1F6A0','1F6A1','1F6F0','1F680','1F6F8','1F6CE','1F9F3','231B','23F3','231A','23F0','23F1','23F2','1F570','1F55B','1F567','1F550','1F55C','1F551','1F55D','1F552','1F55E','1F553','1F55F','1F554','1F560','1F555','1F561','1F556','1F562','1F557','1F563','1F558','1F564','1F559','1F565','1F55A','1F566','1F311','1F312','1F313','1F314','1F315','1F316','1F317','1F318','1F319','1F31A','1F31B','1F31C','1F321','2600','1F31D','1F31E','1FA90','2B50','1F31F','1F320','1F30C','2601','26C5','26C8','1F324','1F325','1F326','1F327','1F328','1F329','1F32A','1F32B','1F32C','1F300','1F308','1F302','2602','2614','26F1','26A1','2744','2603','26C4','2604','1F525','1F4A7','1F30A','1F383','1F384','1F386','1F387','1F9E8','2728','1F388','1F389','1F38A','1F38B','1F38D','1F38E','1F38F','1F390','1F391','1F9E7','1F380','1F381','1F397','1F39F','1F3AB','1F396','1F3C6','1F3C5','1F947','1F948','1F949','26BD','26BE','1F94E','1F3C0','1F3D0','1F3C8','1F3C9','1F3BE','1F94F','1F3B3','1F3CF','1F3D1','1F3D2','1F94D','1F3D3','1F3F8','1F94A','1F94B','1F945','26F3','26F8','1F3A3','1F93F','1F3BD','1F3BF','1F6F7','1F94C','1F3AF','1FA80','1FA81','1F3B1','1F52E','1F9FF','1F3AE','1F579','1F3B0','1F3B2','1F9E9','1F9F8','2660','2665','2666','2663','265F','1F0CF','1F004','1F3B4','1F3AD','1F5BC','1F3A8','1F9F5','1F9F6','1F453','1F576','1F97D','1F97C','1F9BA','1F454','1F455','1F456','1F9E3','1F9E4','1F9E5','1F9E6','1F457','1F458','1F97B','1FA71','1FA72','1FA73','1F459','1F45A','1F45B','1F45C','1F45D','1F6CD','1F392','1F45E','1F45F','1F97E','1F97F','1F460','1F461','1FA70','1F462','1F451','1F452','1F3A9','1F393','1F9E2','26D1','1F4FF','1F484','1F48D','1F48E','1F507','1F508','1F509','1F50A','1F4E2','1F4E3','1F4EF','1F514','1F515','1F3BC','1F3B5','1F3B6','1F399','1F39A','1F39B','1F3A4','1F3A7','1F4FB','1F3B7','1F3B8','1F3B9','1F3BA','1F3BB','1FA95','1F941','1F4F1','1F4F2','260E','1F4DE','1F4DF','1F4E0','1F50B','1F50C','1F4BB','1F5A5','1F5A8','2328','1F5B1','1F5B2','1F4BD','1F4BE','1F4BF','1F4C0','1F9EE','1F3A5','1F39E','1F4FD','1F3AC','1F4FA','1F4F7','1F4F8','1F4F9','1F4FC','1F50D','1F50E','1F56F','1F4A1','1F526','1F3EE','1FA94','1F4D4','1F4D5','1F4D6','1F4D7','1F4D8','1F4D9','1F4DA','1F4D3','1F4D2','1F4C3','1F4DC','1F4C4','1F4F0','1F5DE','1F4D1','1F516','1F3F7','1F4B0','1F4B4','1F4B5','1F4B6','1F4B7','1F4B8','1F4B3','1F9FE','1F4B9','1F4B1','1F4B2','2709','1F4E7','1F4E8','1F4E9','1F4E4','1F4E5','1F4E6','1F4EB','1F4EA','1F4EC','1F4ED','1F4EE','1F5F3','270F','2712','1F58B','1F58A','1F58C','1F58D','1F4DD','1F4BC','1F4C1','1F4C2','1F5C2','1F4C5','1F4C6','1F5D2','1F5D3','1F4C7','1F4C8','1F4C9','1F4CA','1F4CB','1F4CC','1F4CD','1F4CE','1F587','1F4CF','1F4D0','2702','1F5C3','1F5C4','1F5D1','1F512','1F513','1F50F','1F510','1F511','1F5DD','1F528','1FA93','26CF','2692','1F6E0','1F5E1','2694','1F52B','1F3F9','1F6E1','1F527','1F529','2699','1F5DC','2696','1F9AF','1F517','26D3','1F9F0','1F9F2','2697','1F9EA','1F9EB','1F9EC','1F52C','1F52D','1F4E1','1F489','1FA78','1F48A','1FA79','1FA7A','1F6AA','1F6CF','1F6CB','1FA91','1F6BD','1F6BF','1F6C1','1FA92','1F9F4','1F9F7','1F9F9','1F9FA','1F9FB','1F9FC','1F9FD','1F9EF','1F6D2','1F6AC','26B0','26B1','1F5FF','1F3E7','1F6AE','1F6B0','267F','1F6B9','1F6BA','1F6BB','1F6BC','1F6BE','1F6C2','1F6C3','1F6C4','1F6C5','26A0','1F6B8','26D4','1F6AB','1F6B3','1F6AD','1F6AF','1F6B1','1F6B7','1F4F5','1F51E','2622','2623','2B06','2197','27A1','2198','2B07','2199','2B05','2196','2195','2194','21A9','21AA','2934','2935','1F503','1F504','1F519','1F51A','1F51B','1F51C','1F51D','1F6D0','269B','1F549','2721','2638','262F','271D','2626','262A','262E','1F54E','1F52F','2648','2649','264A','264B','264C','264D','264E','264F','2650','2651','2652','2653','26CE','1F500','1F501','1F502','25B6','23E9','23ED','23EF','25C0','23EA','23EE','1F53C','23EB','1F53D','23EC','23F8','23F9','23FA','23CF','1F3A6','1F505','1F506','1F4F6','1F4F3','1F4F4','2640','2642','2695','267E','267B','269C','1F531','1F4DB','1F530','2B55','2705','2611','2714','2716','274C','274E','2795','2796','2797','27B0','27BF','303D','2733','2734','2747','203C','2049','2753','2754','2755','2757','3030','00A9','00AE','2122','0023','FE0F','20E3','002A','FE0F','20E3','0030','FE0F','20E3','0031','FE0F','20E3','0032','FE0F','20E3','0033','FE0F','20E3','0034','FE0F','20E3','0035','FE0F','20E3','0036','FE0F','20E3','0037','FE0F','20E3','0038','FE0F','20E3','0039','FE0F','20E3','1F51F','1F520','1F521','1F522','1F523','1F524','1F170','1F18E','1F171','1F191','1F192','1F193','2139','1F194','24C2','1F195','1F196','1F17E','1F197','1F17F','1F198','1F199','1F19A','1F201','1F202','1F237','1F236','1F22F','1F250','1F239','1F21A','1F232','1F251','1F238','1F234','1F233','3297','3299','1F23A','1F235','1F534','1F7E0','1F7E1','1F7E2','1F535','1F7E3','1F7E4','26AB','26AA','1F7E5','1F7E7','1F7E8','1F7E9','1F7E6','1F7EA','1F7EB','2B1B','2B1C','25FC','25FB','25FE','25FD','25AA','25AB','1F536','1F537','1F538','1F539','1F53A','1F53B','1F4A0','1F518','1F533','1F532','1F3C1','1F6A9','1F38C','1F3F4','1F3F3','1F3F3','FE0F','200D','1F308','1F3F4','200D','2620','FE0F','1F1E6','1F1E8','1F1E6','1F1E9','1F1E6','1F1EA','1F1E6','1F1EB','1F1E6','1F1EC','1F1E6','1F1EE','1F1E6','1F1F1','1F1E6','1F1F2','1F1E6','1F1F4','1F1E6','1F1F6','1F1E6','1F1F7','1F1E6','1F1F8','1F1E6','1F1F9','1F1E6','1F1FA','1F1E6','1F1FC','1F1E6','1F1FD','1F1E6','1F1FF','1F1E7','1F1E6','1F1E7','1F1E7','1F1E7','1F1E9','1F1E7','1F1EA','1F1E7','1F1EB','1F1E7','1F1EC','1F1E7','1F1ED','1F1E7','1F1EE','1F1E7','1F1EF','1F1E7','1F1F1','1F1E7','1F1F2','1F1E7','1F1F3','1F1E7','1F1F4','1F1E7','1F1F6','1F1E7','1F1F7','1F1E7','1F1F8','1F1E7','1F1F9','1F1E7','1F1FB','1F1E7','1F1FC','1F1E7','1F1FE','1F1E7','1F1FF','1F1E8','1F1E6','1F1E8','1F1E8','1F1E8','1F1E9','1F1E8','1F1EB','1F1E8','1F1EC','1F1E8','1F1ED','1F1E8','1F1EE','1F1E8','1F1F0','1F1E8','1F1F1','1F1E8','1F1F2','1F1E8','1F1F3','1F1E8','1F1F4','1F1E8','1F1F5','1F1E8','1F1F7','1F1E8','1F1FA','1F1E8','1F1FB','1F1E8','1F1FC','1F1E8','1F1FD','1F1E8','1F1FE','1F1E8','1F1FF','1F1E9','1F1EA','1F1E9','1F1EC','1F1E9','1F1EF','1F1E9','1F1F0','1F1E9','1F1F2','1F1E9','1F1F4','1F1E9','1F1FF','1F1EA','1F1E6','1F1EA','1F1E8','1F1EA','1F1EA','1F1EA','1F1EC','1F1EA','1F1ED','1F1EA','1F1F7','1F1EA','1F1F8','1F1EA','1F1F9','1F1EA','1F1FA','1F1EB','1F1EE','1F1EB','1F1EF','1F1EB','1F1F0','1F1EB','1F1F2','1F1EB','1F1F4','1F1EB','1F1F7','1F1EC','1F1E6','1F1EC','1F1E7','1F1EC','1F1E9','1F1EC','1F1EA','1F1EC','1F1EB','1F1EC','1F1EC','1F1EC','1F1ED','1F1EC','1F1EE','1F1EC','1F1F1','1F1EC','1F1F2','1F1EC','1F1F3','1F1EC','1F1F5','1F1EC','1F1F6','1F1EC','1F1F7','1F1EC','1F1F8','1F1EC','1F1F9','1F1EC','1F1FA','1F1EC','1F1FC','1F1EC','1F1FE','1F1ED','1F1F0','1F1ED','1F1F2','1F1ED','1F1F3','1F1ED','1F1F7','1F1ED','1F1F9','1F1ED','1F1FA','1F1EE','1F1E8','1F1EE','1F1E9','1F1EE','1F1EA','1F1EE','1F1F1','1F1EE','1F1F2','1F1EE','1F1F3','1F1EE','1F1F4','1F1EE','1F1F6','1F1EE','1F1F7','1F1EE','1F1F8','1F1EE','1F1F9','1F1EF','1F1EA','1F1EF','1F1F2','1F1EF','1F1F4','1F1EF','1F1F5','1F1F0','1F1EA','1F1F0','1F1EC','1F1F0','1F1ED','1F1F0','1F1EE','1F1F0','1F1F2','1F1F0','1F1F3','1F1F0','1F1F5','1F1F0','1F1F7','1F1F0','1F1FC','1F1F0','1F1FE','1F1F0','1F1FF','1F1F1','1F1E6','1F1F1','1F1E7','1F1F1','1F1E8','1F1F1','1F1EE','1F1F1','1F1F0','1F1F1','1F1F7','1F1F1','1F1F8','1F1F1','1F1F9','1F1F1','1F1FA','1F1F1','1F1FB','1F1F1','1F1FE','1F1F2','1F1E6','1F1F2','1F1E8','1F1F2','1F1E9','1F1F2','1F1EA','1F1F2','1F1EB','1F1F2','1F1EC','1F1F2','1F1ED','1F1F2','1F1F0','1F1F2','1F1F1','1F1F2','1F1F2','1F1F2','1F1F3','1F1F2','1F1F4','1F1F2','1F1F5','1F1F2','1F1F6','1F1F2','1F1F7','1F1F2','1F1F8','1F1F2','1F1F9','1F1F2','1F1FA','1F1F2','1F1FB','1F1F2','1F1FC','1F1F2','1F1FD','1F1F2','1F1FE','1F1F2','1F1FF','1F1F3','1F1E6','1F1F3','1F1E8','1F1F3','1F1EA','1F1F3','1F1EB','1F1F3','1F1EC','1F1F3','1F1EE','1F1F3','1F1F1','1F1F3','1F1F4','1F1F3','1F1F5','1F1F3','1F1F7','1F1F3','1F1FA','1F1F3','1F1FF','1F1F4','1F1F2','1F1F5','1F1E6','1F1F5','1F1EA','1F1F5','1F1EB','1F1F5','1F1EC','1F1F5','1F1ED','1F1F5','1F1F0','1F1F5','1F1F1','1F1F5','1F1F2','1F1F5','1F1F3','1F1F5','1F1F7','1F1F5','1F1F8','1F1F5','1F1F9','1F1F5','1F1FC','1F1F5','1F1FE','1F1F6','1F1E6','1F1F7','1F1EA','1F1F7','1F1F4','1F1F7','1F1F8','1F1F7','1F1FA','1F1F7','1F1FC','1F1F8','1F1E6','1F1F8','1F1E7','1F1F8','1F1E8','1F1F8','1F1E9','1F1F8','1F1EA','1F1F8','1F1EC','1F1F8','1F1ED','1F1F8','1F1EE','1F1F8','1F1EF','1F1F8','1F1F0','1F1F8','1F1F1','1F1F8','1F1F2','1F1F8','1F1F3','1F1F8','1F1F4','1F1F8','1F1F7','1F1F8','1F1F8','1F1F8','1F1F9','1F1F8','1F1FB','1F1F8','1F1FD','1F1F8','1F1FE','1F1F8','1F1FF','1F1F9','1F1E6','1F1F9','1F1E8','1F1F9','1F1E9','1F1F9','1F1EB','1F1F9','1F1EC','1F1F9','1F1ED','1F1F9','1F1EF','1F1F9','1F1F0','1F1F9','1F1F1','1F1F9','1F1F2','1F1F9','1F1F3','1F1F9','1F1F4','1F1F9','1F1F7','1F1F9','1F1F9','1F1F9','1F1FB','1F1F9','1F1FC','1F1F9','1F1FF','1F1FA','1F1E6','1F1FA','1F1EC','1F1FA','1F1F2','1F1FA','1F1F3','1F1FA','1F1F8','1F1FA','1F1FE','1F1FA','1F1FF','1F1FB','1F1E6','1F1FB','1F1E8','1F1FB','1F1EA','1F1FB','1F1EC','1F1FB','1F1EE','1F1FB','1F1F3','1F1FB','1F1FA','1F1FC','1F1EB','1F1FC','1F1F8','1F1FD','1F1F0','1F1FE','1F1EA','1F1FE','1F1F9','1F1FF','1F1E6','1F1FF','1F1F2','1F1FF','1F1FC','1F3F4','E0067','E0062','E0065','E006E','E0067','E007F','1F3F4','E0067','E0062','E0073','E0063','E0074','E007F','1F3F4','E0067','E0062','E0077','E006C','E0073','E007F' );

    return !preg_replace( '/[\x{' . implode( '}\x{', $unicodes ) . '}]/u', '', $string ) ? true : false;

}




function e_count_6194($e__id, $specific_id = 0){

    //NOTE HERE

    //Checks where in the database/platform a source might be referenced
    $e_count_6194 = array(); //Holds return values
    $CI =& get_instance();
    $e___6194 = $CI->config->item('e___6194');
    $query_index = array(
        4364 => 'SELECT count(x__id) as totals FROM table__x WHERE x__privacy IN (' . join(',', $CI->config->item('n___7359')) . ') AND x__creator=',
        4593 => 'SELECT count(x__id) as totals FROM table__x WHERE x__privacy IN (' . join(',', $CI->config->item('n___7359')) . ') AND x__type=',
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


function i__spectrum_calculator($i){

    //TODO Improve later (This is a very basic logic)
    $CI =& get_instance();
    $count_x = $CI->X_model->fetch(array(
        'x__privacy IN (' . join(',', $CI->config->item('n___7360')) . ')' => null, //ACTIVE
        '(x__left='.$i['i__id'].' OR x__right='.$i['i__id'].')' => null,
    ), array(), 0, 0, array(), 'COUNT(x__id) as totals');

    //Should we update?
    if($count_x[0]['totals'] != $i['i__spectrum']){
        return $CI->I_model->update($i['i__id'], array(
            'i__spectrum' => $count_x[0]['totals'],
        ));
    } else {
        return 0;
    }

}

function e__spectrum_calculator($e){

    //TODO Improve later (This is a very basic logic)
    $CI =& get_instance();
    $count_x = $CI->X_model->fetch(array(
        'x__privacy IN (' . join(',', $CI->config->item('n___7360')) . ')' => null, //ACTIVE
        '(x__down='.$e['e__id'].' OR x__up='.$e['e__id'].' OR x__creator='.$e['e__id'].')' => null,
    ), array(), 0, 0, array(), 'COUNT(x__id) as totals');

    //Should we update?
    if($count_x[0]['totals'] != $e['e__spectrum']){
        return $CI->E_model->update($e['e__id'], array(
            'e__spectrum' => $count_x[0]['totals'],
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
    return one_two_explode('class="','"',$fetch[array_rand($fetch)]['m__cover']);
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

function reset_cache($x__creator){
    $CI =& get_instance();
    $count = 0;
    foreach($CI->X_model->fetch(array(
        'x__type' => 14599, //Cache App
        'x__up IN (' . join(',', $CI->config->item('n___14599')) . ')' => null, //Cache Apps
        'x__time >' => date("Y-m-d H:i:s", (time() - view_memory(6404,14599))),
        'x__privacy IN (' . join(',', $CI->config->item('n___7359')) . ')' => null, //PUBLIC
    )) as $delete_cahce){
        //Delete email:
        $count += $CI->X_model->update($delete_cahce['x__id'], array(
            'x__privacy' => 6173, //Transaction Removed
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
    return in_array($i['i__privacy'], $CI->config->item('n___31871') /* PRIVATE */);
}

function i_spots_remaining($i__id){

    $CI =& get_instance();
    $member_e = superpower_unlocked();

    //Any Limits on Selection?
    $spots_remaining = -1; //No limits
    $has_limits = $CI->X_model->fetch(array(
        'x__privacy IN (' . join(',', $CI->config->item('n___7359')) . ')' => null, //PUBLIC
        'x__type IN (' . join(',', $CI->config->item('n___13550')) . ')' => null, //SOURCE IDEAS
        'x__right' => $i__id,
        'x__up' => 26189,
    ), array(), 1);
    if(count($has_limits) && strlen($has_limits[0]['x__message']) && is_numeric($has_limits[0]['x__message'])){
        //We have a limit! See if we've met it already:
        $query_filters = array(
            'x__privacy IN (' . join(',', $CI->config->item('n___7359')) . ')' => null, //PUBLIC
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
            'x__privacy IN (' . join(',', $CI->config->item('n___7359')) . ')' => null, //PUBLIC
            'x__type IN (' . join(',', $CI->config->item('n___7704')) . ')' => null, //Discovery Expansions
            'x__right' => $i__id, //This was select as an answer to x__left
            'x__left > 0' => null,
        ), array('x__left'), 0) as $x_progress) {

            //Find all answers
            foreach($CI->X_model->fetch(array(
                'x__privacy IN (' . join(',', $CI->config->item('n___7359')) . ')' => null, //PUBLIC
                'x__type IN (' . join(',', $CI->config->item('n___6255')) . ')' => null, //DISCOVERIES
                'x__creator' => $x__creator,
                'x__left' => $x_progress['x__left'],
            ), array(), 0) as $x){

                //Delete all Selections:
                foreach($CI->X_model->fetch(array(
                    'x__privacy IN (' . join(',', $CI->config->item('n___7359')) . ')' => null, //PUBLIC
                    'x__type IN (' . join(',', $CI->config->item('n___7704')) . ')' => null, //Discovery Expansions
                    'x__left' => $x_progress['x__left'],
                ), array('x__right'), 0) as $x2){
                    $CI->X_model->update($x2['x__id'], array(
                        'x__privacy' => 6173, //Transaction Removed
                        'x__reference' => $access_blocked['x__id'],
                    ), $x__creator, 29782 );
                }

                //Delete question discovery so the user can re-select:
                $CI->X_model->update($x['x__id'], array(
                    'x__privacy' => 6173, //Transaction Removed
                    'x__reference' => $access_blocked['x__id'],
                ), $x__creator, 29782 );

            }

            //Delete this answer:
            $CI->X_model->update($x_progress['x__id'], array(
                'x__privacy' => 6173, //Transaction Removed
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
        'x__privacy IN (' . join(',', $CI->config->item('n___7359')) . ')' => null, //PUBLIC
        'e__privacy IN (' . join(',', $CI->config->item('n___7357')) . ')' => null, //PUBLIC
    ), array('x__up'), 0);
    if(count($fetch_13865)){
        //Let's see if they meet any of these PREREQUISITES:
        $meets_inc1_prereq = false;
        if($x__creator > 0){
            foreach($fetch_13865 as $e_pre){
                if(( $member_e && $member_e['e__id']==$e_pre['x__up'] ) || count($CI->X_model->fetch(array(
                        'x__type IN (' . join(',', $CI->config->item('n___4592')) . ')' => null, //SOURCE LINKS
                        'x__privacy IN (' . join(',', $CI->config->item('n___7359')) . ')' => null, //PUBLIC
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
        'x__privacy IN (' . join(',', $CI->config->item('n___7359')) . ')' => null, //PUBLIC
        'e__privacy IN (' . join(',', $CI->config->item('n___7357')) . ')' => null, //PUBLIC
    ), array('x__up'), 0);
    if(count($fetch_27984)){
        //There are some requirements, Let's see if they meet all of them:
        $missing_es = '';
        $meets_inc2_prereq = 0;
        if($x__creator > 0){
            foreach($fetch_27984 as $e_pre){
                if($x__creator && (( $member_e && $member_e['e__id']==$e_pre['x__up'] ) || count($CI->X_model->fetch(array(
                        'x__type IN (' . join(',', $CI->config->item('n___4592')) . ')' => null, //SOURCE LINKS
                        'x__privacy IN (' . join(',', $CI->config->item('n___7359')) . ')' => null, //PUBLIC
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
        'x__privacy IN (' . join(',', $CI->config->item('n___7359')) . ')' => null, //PUBLIC
        'e__privacy IN (' . join(',', $CI->config->item('n___7357')) . ')' => null, //PUBLIC
    ), array('x__up'), 0);
    if(count($fetch_26600)){
        //Let's see if they meet any of these PREREQUISITES:
        $excludes_all = false;
        if($x__creator > 0){
            foreach($fetch_26600 as $e_pre){
                if(( $member_e && $member_e['e__id']==$e_pre['x__up'] ) || count($CI->X_model->fetch(array(
                        'x__type IN (' . join(',', $CI->config->item('n___4592')) . ')' => null, //SOURCE LINKS
                        'x__privacy IN (' . join(',', $CI->config->item('n___7359')) . ')' => null, //PUBLIC
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


function redirect_message($url, $message = null, $log_error = false)
{
    //An error handling function that would redirect member to $url with optional $message
    //Do we have a Message?
    $CI =& get_instance();

    if ($message) {
        $CI->session->set_flashdata('flash_message', $message);
    }

    if($log_error){
        //Log thie error:
        $member_e = superpower_unlocked();
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

function cookie_check() {
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



function count_unique_coins($x__type, $x__time_start = null, $x__time_end = null){

    $CI =& get_instance();

    //We need to count this:
    if($x__type==12274){

        //SOURCES
        $query_filters = array(
            'x__type IN (' . join(',', $CI->config->item('n___13548')) . ')' => null, //UNIQUE SOURCES
            'x__privacy IN (' . join(',', $CI->config->item('n___7359')) . ')' => null, //PUBLIC

        );

    } elseif($x__type==12273){

        //IDEAS
        $query_filters = array(
            'x__privacy IN (' . join(',', $CI->config->item('n___7359')) . ')' => null, //PUBLIC
            'x__type IN (' . join(',', $CI->config->item('n___13480')) . ')' => null, //UNIQUE IDEAS
        );

    } elseif($x__type==6255){

        //DISCOVERIES
        $query_filters = array(
            'x__privacy IN (' . join(',', $CI->config->item('n___7359')) . ')' => null, //PUBLIC
            'x__type IN (' . join(',', $CI->config->item('n___6255')) . ')' => null, //DISCOVERIES
        );

    } else {

        return 0;

    }

    if(strtotime($x__time_start) > 0){
        $query_filters['x__time >='] = $x__time_start;
    }
    if(strtotime($x__time_end) > 0){
        $query_filters['x__time <='] = $x__time_end;
    }

    //Fetch Results:
    $query = $CI->X_model->fetch($query_filters, array(), 1, 0, array(), 'COUNT(x__id) as totals');
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
    $member_e = $CI->session->userdata('session_profile');
    $has_session = ( is_array($member_e) && count($member_e) > 0 && $member_e );
    return $member_e;

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

function generateQR($url, $width = 150, $height = 150) {
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


function js_redirect($url, $timer = 13){
    echo '<script>
    $(document).ready(function () {
        setTimeout(function () {
            window.location = \''.$url.'\';
        }, '.$timer.');
    });
</script>';
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


function clean_phone($phone){
    $phone_numbers = preg_replace('/\D/', '', $phone);
    if(strlen($phone_numbers)==10){
        $phone_numbers = '+1'.$phone_numbers;
    }
    return $phone_numbers;
}

function email_send($to_emails, $subject, $email_body, $e__id = 0, $x_data = array(), $template_id = 0, $x__website = 0){

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
                'x__type IN (' . join(',', $CI->config->item('n___4592')) . ')' => null, //SOURCE LINKS
                'x__privacy IN (' . join(',', $CI->config->item('n___7359')) . ')' => null, //PUBLIC
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
            'x__privacy IN (' . join(',', $CI->config->item('n___7359')) . ')' => null, //PUBLIC
            'e__privacy IN (' . join(',', $CI->config->item('n___7357')) . ')' => null, //PUBLIC
            'x__left IN (' . $i__id . ')' => null, //ACTIVE
        ), array('x__creator'), 0, 0, array('x__id' => 'DESC')));
    }

    if(strlen($e__id)){
        $query = array_merge($query, $CI->X_model->fetch(array(
            'x__type IN (' . join(',', $CI->config->item('n___4592')) . ')' => null, //SOURCE LINKS
            'x__privacy IN (' . join(',', $CI->config->item('n___7359')) . ')' => null, //PUBLIC
            'e__privacy IN (' . join(',', $CI->config->item('n___7357')) . ')' => null, //PUBLIC
            'x__up IN (' . $e__id . ')' => null,
        ), array('x__down'), 0, 0, array('x__id' => 'DESC')));
    }

    $already_added = array(); //Prevent duplicates
    foreach($query as $subscriber){

        /*
        //Make sure not already added AND not unsubscribed:
        if(in_array($subscriber['e__id'], $already_added)){
            continue;
        }
        if (!count($CI->X_model->fetch(array(
            'x__up IN (' . join(',', $CI->config->item('n___30820')) . ')' => null, //Active Subscriber
            'x__down' => $subscriber['e__id'],
            'x__type IN (' . join(',', $CI->config->item('n___4592')) . ')' => null, //SOURCE LINKS
            'x__privacy IN (' . join(',', $CI->config->item('n___7359')) . ')' => null, //PUBLIC
        )))) {
            continue;
        }

        //Any exclusions?
        if(strlen($exclude_e) && count($CI->X_model->fetch(array(
                'x__type IN (' . join(',', $CI->config->item('n___4592')) . ')' => null, //SOURCE LINKS
                'x__privacy IN (' . join(',', $CI->config->item('n___7359')) . ')' => null, //PUBLIC
                'x__up IN (' . $exclude_e . ')' => null,
                'x__down' => $subscriber['e__id'],
            )))){
            continue;
        }

        if(strlen($include_e) && !count($CI->X_model->fetch(array(
                'x__type IN (' . join(',', $CI->config->item('n___4592')) . ')' => null, //SOURCE LINKS
                'x__privacy IN (' . join(',', $CI->config->item('n___7359')) . ')' => null, //PUBLIC
                'x__up IN (' . $include_e . ')' => null,
                'x__down' => $subscriber['e__id'],
            )))){
            continue;
        }

        */


        //Fetch email & phone:
        $e_emails = $CI->X_model->fetch(array(
            'x__up' => 3288, //Email
            'x__down' => $subscriber['e__id'],
            'x__type IN (' . join(',', $CI->config->item('n___4592')) . ')' => null, //SOURCE LINKS
            'x__privacy IN (' . join(',', $CI->config->item('n___7359')) . ')' => null, //PUBLIC
        ));
        $e_phones = $CI->X_model->fetch(array(
            'x__up' => 4783, //Phone
            'x__down' => $subscriber['e__id'],
            'x__type IN (' . join(',', $CI->config->item('n___4592')) . ')' => null, //SOURCE LINKS
            'x__privacy IN (' . join(',', $CI->config->item('n___7359')) . ')' => null, //PUBLIC
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
            'x__privacy IN (' . join(',', $CI->config->item('n___7359')) . ')' => null, //PUBLIC
            'x__down' => $subscriber['e__id'],
            'x__type IN (' . join(',', $CI->config->item('n___4592')) . ')' => null, //SOURCE LINKS
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

function source_of_e($e__id, $member_e = array()){

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
            'x__type IN (' . join(',', $CI->config->item('n___4592')) . ')' => null, //SOURCE LINKS
            'x__privacy IN (' . join(',', $CI->config->item('n___7359')) . ')' => null, //PUBLIC
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
                    'x__privacy IN (' . join(',', $CI->config->item('n___7359')) . ')' => null, //PUBLIC
                    'x__type IN (' . join(',', $CI->config->item('n___13550')) . ')' => null, //SOURCE IDEAS
                    'x__right' => $i__id,
                    'x__up' => $member_e['e__id'],
                )))
            )
        )
    );

}

function privacy_converter($privacy_id){
    //Create a map of statuses between transactions and ideas/sources:
    $privacy_converter = array();
    $CI =& get_instance();
    foreach($CI->config->item('n___6186') as $e__id) {
        foreach(array_intersect($CI->config->item('n___'.$privacy_id), $CI->config->item('n___'.$e__id)) as $matching_privacy){
            $privacy_converter[$matching_privacy] = $e__id;
        }
    }
    return $privacy_converter;
}

function boost_power()
{
    //Give php page instance more processing power
    ini_set('memory_limit', '-1');
    ini_set('max_execution_time', 0);
}

function objectToArray($object)
{
    //Transform an object into an array
    if (!is_object($object) && !is_array($object)) {
        return $object;
    }
    if (is_object($object)) {
        $object = (array)$object;
    }
    return array_map('objectToArray', $object);
}

function sources_currently_sorted($e__id){
    $CI =& get_instance();
    return count( $CI->X_model->fetch(array(
        'x__spectrum >' => 0, //Sorted
        'x__up' => $e__id,
        'x__type IN (' . join(',', $CI->config->item('n___4592')) . ')' => null, //SOURCE LINKS
        'x__privacy IN (' . join(',', $CI->config->item('n___7360')) . ')' => null, //ACTIVE
    ), array(), 1) );
}

function first_line($string){
    return $string;
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
        $focus_field_privacy = 'i__privacy';
    } elseif($s__type==12274){
        $focus_field_id = 'e__id';
        $focus_field_privacy = 'e__privacy';
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
        unset($limits);

        //Fetch item(s) for updates including their parents:
        if ($loop_obj == 12273) {

            $limits['x__type'] = 4250;

            if($s__id){
                $limits['x__right'] = $s__id;
            } else {
                $limits['i__privacy IN (' . join(',', $CI->config->item('n___31871')) . ')'] = null; //ACTIVE
                $limits['x__privacy IN (' . join(',', $CI->config->item('n___7360')) . ')'] = null; //ACTIVE
            }

            $db_rows[$loop_obj] = $CI->X_model->fetch($limits, array('x__right'), 0);

        } elseif ($loop_obj == 12274) {

            $limits['x__type'] = 4251;

            if($s__id){
                $limits['x__down'] = $s__id;
            } else {
                $limits['e__privacy IN (' . join(',', $CI->config->item('n___7358')) . ')'] = null; //ACTIVE
                $limits['x__privacy IN (' . join(',', $CI->config->item('n___7360')) . ')'] = null; //ACTIVE
            }

            $db_rows[$loop_obj] = $CI->X_model->fetch($limits, array('x__down'), 0);

        }




        //Build the index:
        foreach($db_rows[$loop_obj] as $s) {

            //Prepare variables:
            unset($export_row);
            $export_row = array();


            //Update Weight if single update:
            if($s__id){
                //Update weight before updating this object:
                if($s__type==12274){
                    e__spectrum_calculator($s);
                } elseif($s__type==12273){
                    i__spectrum_calculator($s);
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

            //To hold parent info
            $export_row['_tags'] = array();

            //Now build object-specific index:
            if ($loop_obj == 12274) {

                //SOURCES
                $export_row['s__type'] = $loop_obj;
                $export_row['s__id'] = intval($s['e__id']);
                $export_row['s__url'] = '/@' . $s['e__id'];
                $export_row['s__privacy'] = intval($s['e__privacy']);
                $export_row['s__cover'] = $s['e__cover'];
                $export_row['s__title'] = $s['e__title'];
                $export_row['s___weight'] = intval($s['e__spectrum']);

                //Add source as their own author:
                array_push($export_row['_tags'], 'alg_e_' . $s['x__creator']);

                if($s['x__creator']!=$s['e__id']){
                    //Also give access to source themselves, in case they can login:
                    array_push($export_row['_tags'], 'alg_e_' . $s['e__id']);
                }

                //Is this an image?
                if(strlen($s['e__cover'])){
                    array_push($export_row['_tags'], 'has_image');
                }

                //Is Featured Source?
                if(in_array($s['e__privacy'], $CI->config->item('n___30977'))){
                    array_push($export_row['_tags'], 'is_featured');
                }

                //Fetch Profiles:
                $export_row['s__keywords'] = '';
                foreach($CI->X_model->fetch(array(
                    'x__type IN (' . join(',', $CI->config->item('n___4592')) . ')' => null, //SOURCE LINKS
                    'x__down' => $s['e__id'], //This child source
                    'x__privacy IN (' . join(',', $CI->config->item('n___7360')) . ')' => null, //ACTIVE
                    'e__privacy IN (' . join(',', $CI->config->item('n___7358')) . ')' => null, //ACTIVE
                ), array('x__up'), 0, 0, array('e__title' => 'DESC')) as $x) {

                    //Always add to tags:
                    array_push($export_row['_tags'], 'alg_e_' . $x['e__id']);

                    //Add content to keywords if any:
                    if (strlen($x['x__message']) > 0) {
                        //Add to keywords:
                        $export_row['s__keywords'] .= $x['x__message'] . ' ';
                    }

                }

                //Fetch Discoveries
                foreach($CI->X_model->fetch(array(
                    'x__privacy IN (' . join(',', $CI->config->item('n___7359')) . ')' => null, //PUBLIC
                    'x__type IN (' . join(',', $CI->config->item('n___29133')) . ')' => null, //Written Responses
                    'x__creator' => $s['e__id'], //This child source
                ), array('x__creator'), 0, 0, array('x__time' => 'DESC')) as $x){
                    if (strlen($x['x__message']) > 0) {
                        $export_row['s__keywords'] .= $x['x__message'] . ' ';
                    }
                }

                $export_row['s__keywords'] = trim(strip_tags($export_row['s__keywords']));

            } elseif ($loop_obj == 12273) {

                //IDEAS
                //See if this idea has a time-range:
                $export_row['s__type'] = $loop_obj;
                $export_row['s__id'] = intval($s['i__id']);
                $export_row['s__url'] = '/i/i_go/' . $s['i__id'];
                $export_row['s__privacy'] = intval($s['i__type']);
                $export_row['s__cover'] = '';
                $export_row['s__title'] = $s['i__title'];
                $export_row['s___weight'] = intval($s['i__spectrum']);

                //Add keywords:
                $export_row['s__keywords'] = '';
                foreach($CI->X_model->fetch(array(
                    'x__privacy IN (' . join(',', $CI->config->item('n___7360')) . ')' => null, //ACTIVE
                    'x__type IN (' . join(',', $CI->config->item('n___13550')) . ')' => null, //Idea Source Links
                    'x__right' => $s['i__id'],
                ), array(), 0, 0, array('x__spectrum' => 'ASC')) as $keyword) {
                    if(strlen($keyword['x__message'])){
                        $export_row['s__keywords'] .= $keyword['x__message'] . ' ';
                    }
                }
                $export_row['s__keywords'] = trim(strip_tags($export_row['s__keywords']));


                //Startable?
                //                    array_push($export_row['_tags'], 'is_featured');

                //Is SOURCE for any IDEA?
                foreach($CI->X_model->fetch(array(
                    'x__privacy IN (' . join(',', $CI->config->item('n___7359')) . ')' => null, //PUBLIC
                    'x__type IN (' . join(',', $CI->config->item('n___13550')) . ')' => null, //SOURCE IDEAS
                    'x__right' => $s['i__id'],
                    'x__up > ' => 0, //MESSAGES MUST HAVE A SOURCE REFERENCE TO ISSUE IDEA COINS
                ), array(), 0) as $e){
                    if($e['x__up']>0){
                        array_push($export_row['_tags'], 'alg_e_' . $e['x__up']);
                    }
                    if($e['x__down']>0){
                        array_push($export_row['_tags'], 'alg_e_' . $e['x__down']);
                    }
                }

            }

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
        if (in_array($all_db_rows[0][$focus_field_privacy], array(6178 /* Member Deleted */, 6182 /* Idea Deleted */))) {

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