<?php


function view_show_more($see_more_type, $class, $href_link = null){
    $CI =& get_instance();
    $e___11035 = $CI->config->item('e___11035'); //NAVIGATION
    return '<div class="coin_cover coin_reverse col-xl-2 col-lg-3 col-md-4 col-6 no-padding '.$class.'">
                                <div class="cover-wrapper"><a '.( $href_link ? 'href="'.$href_link.'"' : 'href="javascript:void(0);" onclick="$(\'.'.$class.'\').toggleClass(\'hidden\')"' ).' class="black-background-obs cover-link"><div class="cover-btn">'.$e___11035[$see_more_type]['m__cover'].'</div></a></div>
                            </div>';
}


function view_db_field($field_name){

    //Takes a database field name and returns a human-friendly version
    return ucwords(str_replace('i__', '', str_replace('e__', '', str_replace('x__', '', $field_name))));

}


function view_x__message($x__message, $x__type, $full_message = null, $has_discovery_mode = false)
{

    /*
     *
     * Displays Source Transactions @4592
     *
     * $full_message Would be the entire message
     * in an idea message that would be passed down
     * to the source profile $x__message value.
     *
     * */

    $CI =& get_instance();

    if ($x__type == 4256 /* Generic URL */) {

        return '<div class="block"><a href="' . $x__message . '" target="_blank" class="ignore-click"><span class="url_truncate">' . view_url_clean($x__message) . '</span></a></div>';

    } elseif ($x__type == 4257 /* Embed Widget URL? */) {

        return view_url_embed($x__message, $full_message);

    } elseif ($x__type == 26092 /* CAD */) {

        return str_replace('CAD ','$',$x__message);

    } elseif ($x__type == 26091 /* USD */) {

        return str_replace('USD ','$',$x__message);

    } elseif ($x__type == 4260 /* Image URL */) {

        return '<img src="' . $x__message . '" class="content-image" />';

    } elseif ($x__type == 4259 /* Audio URL */) {

        return  '<audio controls src="' . $x__message . '">Your Browser Does Not Support Audio</audio>' ;

    } elseif ($x__type == 4258 /* Video URL */) {

        return  '<video width="100%" class="play_video" onclick="this.play()" controls poster="https://s3foundation.s3-us-west-2.amazonaws.com/9988e7bc95f25002b40c2a376cc94806.png"><source src="' . $x__message . '" type="video/mp4"></video>' ;

    } elseif ($x__type == 4261 /* File URL */) {

        $e___11035 = $CI->config->item('e___11035'); //NAVIGATION
        return '<a href="' . $x__message . '" class="btn btn-12273" target="_blank" class="ignore-click">'.$e___11035[13573]['m__cover'].' '.$e___11035[13573]['m__title'].'</a>';

    } elseif(strlen($x__message) > 0) {

        return nl2br(htmlentities($x__message));

    } else {

        //UNKNOWN
        return false;

    }
}




function view_url_embed($url, $full_message = null, $return_array = false)
{


    /*
     *
     * Detects and displays URLs from supported website with an embed widget
     *
     * Alert: Changes to this function requires us to re-calculate all current
     *       values for x__type as this could change the equation for those
     *       transaction types. Change with care...
     *
     * */



    $clean_url = null;
    $embed_html_code = null;
    $prefix__message = null;
    $CI =& get_instance();
    $e___11035 = $CI->config->item('e___11035');

    if(is_https_url($url)){

        //See if $url has a valid embed video in it, and transform it if it does:
        $has_embed = (substr_count($url, 'youtube.com/embed/') == 1);

        if (!substr_count($url, '&list=') && ((substr_count($url, 'youtube.com/watch') == 1) || substr_count($url, 'youtu.be/') == 1 || $has_embed)) {

            $start_time = 0;
            $end_time = 0;
            $video_id = extract_youtube_id($url);

            if ($video_id) {

                //See if we have start & end time
                $string_references = extract_e_references($full_message);
                if($string_references['ref_time_found']){
                    $start_time = $string_references['ref_time_start'];
                    $end_time = $string_references['ref_time_end'];
                }

                //Set the Clean URL:
                $clean_url = 'https://www.youtube.com/watch?v=' . $video_id;


                //Header For Time
                if($end_time){
                    $seconds = $end_time-$start_time;
                    $embed_html_code .= '<div class="css__title subtle-line mini-grey">'.( $seconds<60 ? $seconds.' SEC.' : round_minutes($seconds).' MIN' ).' <span class="inline-block">FROM '.view_time_hours($start_time, true).' TO '.view_time_hours($end_time, true).'</span></div>';
                }

                $embed_html_code .= '<div class="media-content ignore-click"><div class="ytframe video-sorting" style="margin-top:5px;"><iframe id="youtubeplayer'.$video_id.'"  src="//www.youtube.com/embed/' . $video_id . '?wmode=opaque&theme=light&color=white&keyboard=1&autohide=2&modestbranding=1&showinfo=0&rel=0&iv_load_policy=3&start=' . $start_time . ($end_time ? '&end=' . $end_time : '') . '" frameborder="0" allowfullscreen class="yt-video"></iframe></div><div class="doclear">&nbsp;</div></div>';

            }

        } elseif (substr_count($url, 'facebook.com/') == 1 && substr_count($url, '/videos/') == 1 && is_numeric(one_two_explode('/videos/','/',$url))) {

            $video_id = trim(one_two_explode('/videos/','/',$url));
            $clean_url = $url;
            $embed_html_code = '<div class="media-content ignore-click"><div class="ytframe video-sorting" style="margin-top:5px;"><iframe src="https://www.facebook.com/plugins/video.php?href='.urlencode($url).'&show_text=false&t=0" style="border:none;overflow:hidden" scrolling="no" frameborder="0" allowfullscreen="true" allow="autoplay; clipboard-write; encrypted-media; picture-in-picture; web-share" allowFullScreen="true"></iframe></div></div>';

        } elseif (substr_count($url, 'vimeo.com/') == 1 && is_numeric(one_two_explode('vimeo.com/','?',$url))) {

            //Seems to be Vimeo:
            $video_id = trim(one_two_explode('vimeo.com/', '?', $url));
            $clean_url = 'https://vimeo.com/' . $video_id;
            $embed_html_code = '<div class="media-content ignore-click"><div class="ytframe video-sorting" style="margin-top:5px;"><iframe src="https://user.vimeo.com/video/' . $video_id . '?title=0&byline=0" class="vm-video" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe></div><div class="doclear">&nbsp;</div></div>';

        } elseif (substr_count($url, 'wistia.com/medias/') == 1) {

            //Seems to be Wistia:
            $video_id = trim(one_two_explode('wistia.com/medias/', '?', $url));
            $clean_url = trim(one_two_explode('', '?', $url));
            $embed_html_code = '<script src="https://fast.wistia.com/embed/medias/' . $video_id . '.jsonp" async></script><script src="https://fast.wistia.com/assets/external/E-v1.js" async></script><div class="wistia_responsive_padding video-sorting ignore-click" style="padding:56.25% 0 0 0;position:relative;"><div class="wistia_responsive_wrapper" style="height:100%;left:0;position:absolute;top:0;width:100%;"><div class="wistia_embed wistia_async_' . $video_id . ' seo=false videoFoam=true" style="height:100%;width:100%">&nbsp;</div></div></div>';

        }
    }


    if ($return_array) {

        //Return all aspects of this parsed URL:
        return array(
            'status' => ( $embed_html_code ? 1 : 0 ),
            'embed_code' => $embed_html_code,
            'clean_url' => $clean_url,
        );

    } else {

        //Just return the embed code:
        return $embed_html_code;

    }
}

function view_i_title($i){

    $CI =& get_instance();
    $hide_title = false;
    return '<span class="text__4736_'.$i['i__id'].' css__title '.( $hide_title ? ' hidden ' : '').'">'.htmlentities(trim($i['i__title'])).'</span>';
}


function view_cover($coin__type, $cover_code, $noicon_default = null, $icon_prefix = '')
{

    $valid_url = ( filter_var($cover_code, FILTER_VALIDATE_URL) || substr($cover_code, 0, 2)=='//' );

    //A simple function to display the Member Icon OR the default icon if not available:
    if($valid_url && $noicon_default){

        return $icon_prefix.'<div class="img" style="background-image:url(\''.$cover_code.'\');"></div>';

    } elseif($valid_url){

        return $icon_prefix.'<img src="'.$cover_code.'"'.( substr_count($cover_code, 'class=') ? ' class="'.str_replace(',',' ',one_two_explode('class=','&', $cover_code)).'" ' : '' ).'/>';

    } elseif (string_is_icon($cover_code)) {

        return $icon_prefix.'<i class="'.$cover_code.'"></i>';

    } elseif(strlen($cover_code)) {

        return $icon_prefix.$cover_code;

    } elseif($noicon_default && $noicon_default!=1) {

        return $icon_prefix.$noicon_default;

    } else {

        //Standard Icon if none:
        return null;
        //return '<i class="fas fa-circle zq'.$coin__type.'"></i>';
        //return '<img src="/img/'.$coin__type.'.png" />';

    }
}


function view_number($number)
{

    if(intval($number) < 1){
        return '&nbsp;';
    }

    //Round & format numbers

    if ($number < 950) {
        return intval($number).'&nbsp;';
    }

    if ($number >= 950000000) {
        $formatting = array(
            'multiplier' => (1 / 1000000000),
            'decimals' => 0,
            'suffix' => 'B',
        );
    } elseif ($number >= 9500000) {
        $formatting = array(
            'multiplier' => (1 / 1000000),
            'decimals' => 0,
            'suffix' => 'M',
        );
    } elseif ($number >= 950000) {
        $formatting = array(
            'multiplier' => (1 / 1000000),
            'decimals' => 0,
            'suffix' => 'M',
        );
    } elseif ($number >= 9500) {
        $formatting = array(
            'multiplier' => (1 / 1000),
            'decimals' => 0,
            'suffix' => 'K',
        );
    } else {
        $formatting = array(
            'multiplier' => (1 / 1000),
            'decimals' => 1,
            'suffix' => 'K',
        );
    }

    return round(($number * $formatting['multiplier']), $formatting['decimals']) . $formatting['suffix'].'&nbsp;';

}


function view_x($x, $has_x__reference = false)
{

    $CI =& get_instance();
    $ui = '<div class="x-list">';
    foreach($CI->config->item('e___4341') as $e__id => $m) {

        if(in_array(6160 , $m['m__following']) && intval($x[$m['m__message']])>0){

            //SOURCE
            foreach($CI->E_model->fetch(array('e__id' => $x[$m['m__message']])) as $this_e){
                $ui .= '<div class="simple-line"><a href="/@'.$this_e['e__id'].'" data-toggle="tooltip" data-placement="top" title="'.$m['m__title'].'" class="css__title"><span class="icon-block">'.$m['m__cover']. '</span>'.'<span class="icon-block">'.view_cover(12274,$this_e['e__cover'], true). '</span>'.$this_e['e__title'].'</a></div>';
            }

        } elseif(in_array(6202 , $m['m__following']) && intval($x[$m['m__message']])>0){

            //IDEA
            foreach($CI->I_model->fetch(array('i__id' => $x[$m['m__message']])) as $this_i){
                $ui .= '<div class="simple-line"><a href="/i/i_go/'.$this_i['i__id'].'" data-toggle="tooltip" data-placement="top" title="'.$m['m__title'].'" class="css__title"><span class="icon-block">'.$m['m__cover']. '</span><span class="icon-block">'.view_cache(4737 /* Idea Type */, $this_i['i__type'], true, 'right', $this_i['i__id']).'</span>'.view_i_title($this_i).'</a></div>';
            }


        } elseif(in_array(4367 , $m['m__following']) && intval($x[$m['m__message']])>0){

            //TRANSACTION
            if(!$has_x__reference){
                foreach($CI->X_model->fetch(array('x__id' => $x[$m['m__message']])) as $ref_x){
                    $ui .= '<div class="simple-line"><span class="icon-block" data-toggle="tooltip" data-placement="top" title="'.$m['m__title'].'">'.$m['m__cover']. '</span><div class="x-ref hidden x_msg_'.$x['x__id'].'">'.view_x($ref_x, true).'</div><a class="x_msg_'.$x['x__id'].'" href="javascript:void(0);" onclick="$(\'.x_msg_'.$x['x__id'].'\').toggleClass(\'hidden\');"><u>View Referenced Transaction</u></a></div>';
                }
            } else {
                //Simple Reference to avoid Loop:
                $ui .= '<div class="simple-line"><span data-toggle="tooltip" data-placement="top" title="' . $m['m__title'].': '.$x['x__time'] . ' PST"><span class="icon-block">'.$m['m__cover']. '</span>' . view_time_difference(strtotime($x['x__time'])) . ' Ago</span></div>';
            }

        } elseif($e__id==4367){

            //ID
            $ui .= '<div class="simple-line"><a href="/-4341?x__id='.$x['x__id'].'" data-toggle="tooltip" data-placement="top" title="'.$m['m__title'].'" class="mono-space"><span class="icon-block">'.$m['m__cover']. '</span>'.$x['x__id'].'</a></div>';

        } elseif($e__id==4362){

            //TIME
            $ui .= '<div class="simple-line"><span data-toggle="tooltip" data-placement="top" title="' . $m['m__title'].': '.$x['x__time'] . ' PST"><span class="icon-block">'.$m['m__cover']. '</span>' . view_time_difference(strtotime($x['x__time'])) . ' Ago</span></div>';

        } elseif($e__id==4370 && $x['x__spectrum'] > 0){

            //Order
            $ui .= '<div class="simple-line"><span data-toggle="tooltip" data-placement="top" title="'.$m['m__title']. '"><span class="icon-block">'.$m['m__cover']. '</span>'.view_ordinal($x['x__spectrum']).'</span></div>';

        } elseif($e__id==6103 && strlen($x['x__metadata']) > 0){

            //Metadata
            $ui .= '<div class="simple-line"><a href="/-12722?x__id=' . $x['x__id'] . '" target="_blank"><span class="icon-block">'.$m['m__cover']. '</span><u>'.$m['m__title']. '</u></a></div>';

        } elseif($e__id==4372 && strlen($x['x__message']) > 0){

            //Message
            $ui .= '<div class="simple-line" data-toggle="tooltip" data-placement="top" title="'.$m['m__title'].'"><span class="icon-block">'.$m['m__cover'].'</span><div class="title-block x-msg">'.( strip_tags($x['x__message'])==$x['x__message'] || strlen(strip_tags($x['x__message']))<view_memory(6404,6197) ? $x['x__message'] : '<span class="hidden html_msg_'.$x['x__id'].'">'.$x['x__message'].'</span><a class="html_msg_'.$x['x__id'].'" href="javascript:void(0);" onclick="$(\'.html_msg_'.$x['x__id'].'\').toggleClass(\'hidden\');"><u>View HTML Message</u></a>' ).'</div></div>';

        }
    }

    $ui .= '</div>';

    return $ui;
}


function view_url_clean($url)
{
    //Returns the watered-down version of the URL for a cleaner UI:
    return rtrim(str_replace('http://', '', str_replace('https://', '', str_replace('www.', '', $url))), '/');
}


function view_time_difference($t, $micro = false)
{

    $second_time = time(); //Now

    $time = $second_time - (is_int($t) ? $t : strtotime(substr($t, 0, 19))); // to get the time since that moment
    $has_future = ($time < 0);
    $time = abs($time);
    if($micro){
        $time_units = array(
            31536000 => 'y',
            2592000 => 'm',
            604800 => 'w',
            86400 => 'd',
            3600 => 'h',
            60 => 'min',
            1 => 'sec'
        );
    } else {
        $time_units = array(
            31536000 => 'Year',
            2592000 => 'Month',
            604800 => 'Week',
            86400 => 'Day',
            3600 => 'Hour',
            60 => 'Minute',
            1 => 'Second'
        );
    }


    foreach($time_units as $unit => $period) {
        if ($time < $unit && $unit > 1) continue;
        $numberOfUnits = number_format(($time / $unit), 0);
        if ($numberOfUnits < 1 && $unit == 1) {
            $numberOfUnits = 1; //Change "0 seconds" to "1 second"
        }

        return $numberOfUnits . ( $micro ? '' : ' ' ) . $period . (($numberOfUnits > 1 && !$micro) ? 's' : '');
    }
}


function view_memory($parent, $child, $filed = 'm__message'){
    $CI =& get_instance();
    $memory_tree = $CI->config->item('e___'.$parent);
    return $memory_tree[$child][$filed];
}

function view_cache($parent, $e__id, $micro_status = true, $data_placement = 'top', $i__id = 0)
{

    /*
     *
     * UI for Platform Cache sources
     *
     * */

    $CI =& get_instance();
    $config_array = $CI->config->item('e___'.$parent);
    if(!isset($config_array[$e__id])){
        return false;
    }
    $cache = $config_array[$e__id];
    if (!$cache) {
        //Could not find matching item
        return false;
    }


    //We have two skins for displaying Status:
    if (is_null($data_placement)) {
        if($micro_status){
            return $cache['m__cover'];
        } else {
            return $cache['m__cover'].' '.$cache['m__title'];
        }
    } else {
        //data-toggle="tooltip" data-placement="' . $data_placement . '"
        return '<span class="'.( $micro_status ? 'cache_micro_'.$parent.'_'.$i__id : '' ).'" ' . ( $micro_status && !is_null($data_placement) ? ' title="' . ($micro_status ? $cache['m__title'] : '') . (strlen($cache['m__message']) > 0 ? ($micro_status ? ': ' : '') . $cache['m__message'] : '') . '"' : 'style="cursor:pointer;"') . '>' . $cache['m__cover'] . ' ' . ($micro_status ? '' : $cache['m__title']) . '</span>';
    }
}





function view_coins(){
    $CI =& get_instance();
    $e___11035 = $CI->config->item('e___11035'); //NAVIGATION
    $query = $CI->X_model->fetch(array(), array(), 1, 0, array(), 'COUNT(x__id) as totals');
    $ui = '';

    $ui .= '<div class="row justify-content list-coins">';
    $count = 0;
    foreach($CI->config->item('e___14874') as $e__id => $m) {
        $count++;
        $ui .= '<div class="coin_cover no-padding col-12 col-md-4">';
        $ui .= '<div class="large_cover">'.$m['m__cover'].'</div>';
        $ui .= '<div class="css__title large_title zq'.$e__id.' "><b class="coin_count_'.$e__id.'">'.number_format(count_unique_coins($e__id), 0).'</b></div>';
        $ui .= '<div class="css__title large_title zq'.$e__id.'">'.$m['m__title'].'</div>';
        $ui .= '</div>';
    }
    $ui .= '</div>';


    $ui .= '<div class="row justify-content list-coins" style="font-size: 1.8em; padding-top: 55px; line-height: 130%;"><div style="min-height:40px; width: 100%; text-align: center;"><b class="coin_count_x css__title">'.number_format($query[0]['totals'], 0).'</b></div><div class="css__title" style="text-align: center;">Transactions</div></div>';


    return $ui;
}

function view_coin_line($href, $is_current, $x__type, $o__type, $o__cover, $o__title, $x__message = null){
    return '<a href="'.( $is_current ? 'javascript:alert(\'You are here already!\');' : $href ).'" class="dropdown-item css__title '.( $is_current ? ' active ' : '' ).'">'.( $x__type ? '<span class="icon-block-xxs">'.$x__type.'</span>' : '' ).( $o__type ? '<span class="icon-block-xxs">'.$o__type.'</span>' : '' ).( strlen($o__cover) ? '<span class="icon-block-xxs">'.$o__cover.'</span>' : '&nbsp;' ).$o__title./*'<span class="pull-right inline-block">'..'</span>'.*/( strlen($x__message) && superpower_active(12701, true) ? '<div class="message2">'.strip_tags($x__message).'</div>' : '' ).'</a>';
}




function view_body_e($x__type, $counter, $e__id){

    $CI =& get_instance();
    $limit = view_memory(6404,11064);
    $member_e = superpower_unlocked();
    $e___11035 = $CI->config->item('e___11035'); //NAVIGATION
    $superpower_10939 = superpower_active(10939, true);
    $source_of_e = source_of_e($e__id);
    $list_results = view_coins_e($x__type, $e__id, 1);
    $focus_e = ($e__id == $member_e['e__id'] ? $member_e : false);
    $es = $CI->E_model->fetch(array(
        'e__id' => $e__id,
    ));
    if(!count($es)){
        return false;
    }
    $ui = '';

    //Check Permissions to make sure:


    if($x__type==12273){


        if($superpower_10939){
            $ui .= '<div class="new-list-'.$x__type.' list-group"><div class="list-group-item list-adder">
                <div class="input-group border">
                    <a class="input-group-addon addon-lean icon-adder" href="javascript:void(0);" onclick="$(\'.new-list-'.$x__type.' .add-input\').focus();"><span class="icon-block">'.$e___11035[14016]['m__cover'].'</span></a>
                    <input type="text"
                           class="form-control form-control-thick algolia_search dotransparent add-input"
                           maxlength="' . view_memory(6404,4736) . '"
                           placeholder="'.$e___11035[14016]['m__title'].'">
                </div><div class="algolia_pad_search row justify-content"></div></div></div>';
        }



        $ui .= '<div class="row justify-content hideIfEmpty" id="list-in-'.$x__type.'">';
        foreach($list_results as $i){
            $ui .= view_i_card(12273, 0, null, $i, $focus_e, null);
        }
        $ui .= '</div>';

    } elseif($x__type==12274 || $x__type==11030){

        $idea_adder = ( $x__type==12274 ? 31775 : 31774 );

        $add_button = '<div class="new-list-'.$x__type.' list-adder">
                    <div class="input-group border">
                        <a class="input-group-addon addon-lean icon-adder" href="javascript:void(0);" onclick="$(\'.new-list-'.$x__type.' .add-input\').focus();"><span class="icon-block">'.$e___11035[$idea_adder]['m__cover'].'</span></a>
                        <input type="text"
                               class="form-control form-control-thick algolia_search dotransparent add-input"
                               maxlength="' . view_memory(6404,6197) . '"
                               placeholder="'.$e___11035[$idea_adder]['m__title'].'">
                    </div><div class="algolia_pad_search row justify-content"></div></div>';


        if($x__type==12274 && superpower_active(13422, true)){
            $ui .= $add_button;
        }
        $ui .= '<div class="row justify-content hideIfEmpty" id="list-in-'.$x__type.'">';
        foreach($list_results as $e) {
            $ui .= view_e_card($x__type, $e, null);
        }
        $ui .= '</div>';
        if($x__type==11030 && superpower_active(13422, true)){
            $ui .= $add_button;
        }

    } elseif($x__type==6255){

        $ui .= '<div class="row justify-content hideIfEmpty" id="list-in-' . $x__type . '">';
        foreach ($list_results as $i) {
            $ui .= view_i_card($x__type, $i['i__id'], null, $i, $focus_e);
        }
        $ui .= '</div>';

    }

    return $ui;

}



function view_body_i($x__type, $counter, $i__id){

    $CI =& get_instance();
    $e___11035 = $CI->config->item('e___11035'); //NAVIGATION
    $e_of_i = e_of_i($i__id);
    $list_results = view_coins_i($x__type, $i__id, 1);
    $ui = '';
    $is = $CI->I_model->fetch(array(
        'i__id' => $i__id,
    ));
    if(!count($is)){
        return false;
    }


    if($x__type==11019){

        $ui .= '<div class="row justify-content hideIfEmpty" id="list-in-'.$x__type.'">';
        foreach($list_results as $previous_i) {
            $ui .= view_i_card(11019, 0, null, $previous_i);
        }
        $ui .= '</div>';

        if($e_of_i){
            $ui .= '<div class="new-list-11019 list-adder '.superpower_active(10939).'">
                    <div class="input-group border">
                        <a class="input-group-addon addon-lean icon-adder" href="javascript:void(0);" onclick="$(\'.new-list-11019 .add-input\').focus();"><span class="icon-block">'.$e___11035[31773]['m__cover'].'</span></a>
                        <input type="text"
                               class="form-control form-control-thick add-input algolia_search dotransparent"
                               maxlength="' . view_memory(6404,4736) . '"
                               placeholder="'.$e___11035[31773]['m__title'].'">
                    </div><div class="algolia_pad_search row justify-content"></div></div>';
        }

    } elseif($x__type==12273){

        //IDEAS

        if($e_of_i){
            $ui .= '<div class="new-list-12273 list-adder '.superpower_active(10939).'">
                <div class="input-group border">
                    <a class="input-group-addon addon-lean icon-adder" href="javascript:void(0);" onclick="$(\'.new-list-12273 .add-input\').focus();"><span class="icon-block">'.$e___11035[31772]['m__cover'].'</span></a>
                    <input type="text"
                           class="form-control form-control-thick add-input algolia_search dotransparent"
                           maxlength="' . view_memory(6404,4736) . '"
                           placeholder="'.$e___11035[31772]['m__title'].'">
                </div><div class="algolia_pad_search row justify-content"></div></div>';
        }

        $ui .= '<div class="row justify-content hideIfEmpty" id="list-in-12273">';
        foreach($list_results as $next_i) {
            $ui .= view_i_card(12273, 0, $is[0], $next_i);
        }
        $ui .= '</div>';

    } elseif($x__type==6255) {

        //DISCOVERIES
        $ui .= '<div class="row justify-content hideIfEmpty" id="list-in-'.$x__type.'">';
        foreach($list_results as $item){
            $ui .= view_e_card(6255, $item);
        }
        $ui .= '</div>';

    } elseif($x__type==12274){

        //SOURCES

        $ui .= '<div class="new-list-'.$x__type.' list-adder '.superpower_active(10939).'">
                    <div class="input-group border">
                        <a class="input-group-addon addon-lean icon-adder" href="javascript:void(0);" onclick="$(\'.new-list-'.$x__type.' .add-input\').focus();"><span class="icon-block">'.$e___11035[14055]['m__cover'].'</span></a>
                        <input type="text"
                               class="form-control form-control-thick algolia_search dotransparent add-input"
                               maxlength="' . view_memory(6404,6197) . '"
                               placeholder="'.$e___11035[14055]['m__title'].'">
                    </div><div class="algolia_pad_search row justify-content"></div></div>';


        $ui .= '<div class="row justify-content hideIfEmpty" id="list-in-'.$x__type.'">'; //list-in-4983
        foreach($list_results as $e_ref){
            $ui .= view_e_card($e_ref['x__type'], $e_ref, null);
        }
        $ui .= '</div>';

    }

    return $ui;

}

function view_item($e__id, $i__id, $s__title, $s__cover, $link, $desc = null, $m_cover = false){

    //$link = '/-27970?e__id='.$e__id.'&i__id='.$i__id.'&go_to='.urlencode($link);
    if(!$desc && $i__id>0){
        $CI =& get_instance();
        $member_e = superpower_unlocked();
        foreach($CI->X_model->fetch(array(
            'x__status IN (' . join(',', $CI->config->item('n___7359')) . ')' => null, //PUBLIC
            'x__type' => 4231, //IDEA NOTES Messages
            'x__right' => $i__id,
        ), array(), 0, 0, array('x__spectrum' => 'ASC')) as $message_x){
            if(substr($message_x['x__message'], 0, 1)=='@' && is_numeric(substr($message_x['x__message'], 1)) && count($CI->X_model->fetch(array(
                    'x__type' => 4260, //IMAGES
                    'x__status IN (' . join(',', $CI->config->item('n___7359')) . ')' => null, //PUBLIC
                    'x__down' => intval(substr($message_x['x__message'], 1)),
                )))){
                $desc .= $CI->X_model->message_view($message_x['x__message'], true, $member_e, 0, true);
                break;
            }
        }
    }

    return '<a href="'.$link.'" class="list-group-item list-group-item-action flex-column align-items-start">
    <div class="d-flex justify-content-between">
      <h4 class="css__title"><b>'.( strlen($s__cover) ? '<span class="icon-block-lg title-left">'.( $m_cover ? $s__cover : view_cover(($e__id>0 ? 12274 : 12273),$s__cover) ).'</span><span class="title-right">'.$s__title.'</span>' : $s__title ).'</b></h4>
      <small style="padding: 1px 3px 0 0;"><i class="far fa-chevron-right"></i></small>
    </div>
    '.( strlen($desc) ? '<p>'.$desc.'</p>' : '' ) .'
    
  </a>';

}

function view_coins_e($x__type, $e__id, $page_num = 0, $append_coin_icon = true){

    /*
     *
     * Loads Source
     *
     * */


    $CI =& get_instance();
    $first_segment = $CI->uri->segment(1);

    if($x__type==12274){

        //DOWN
        $order_columns = array('x__spectrum' => 'ASC', 'e__title' => 'ASC');
        $join_objects = array('x__down');
        $query_filters = array(
            'x__up' => $e__id,
            'x__type IN (' . join(',', $CI->config->item('n___4592')) . ')' => null, //SOURCE LINKS
            'x__status IN (' . join(',', $CI->config->item('n___7359')) . ')' => null, //PUBLIC
            'e__status IN (' . join(',', $CI->config->item('n___7358')) . ')' => null, //ACTIVE
        );

    } elseif($x__type==11030){

        //UP
        $order_columns = array('e__title' => 'DESC');
        $join_objects = array('x__up');
        $query_filters = array(
            'x__down' => $e__id,
            'x__type IN (' . join(',', $CI->config->item('n___4592')) . ')' => null, //SOURCE LINKS
            'x__status IN (' . join(',', $CI->config->item('n___7360')) . ')' => null, //ACTIVE
            'e__status IN (' . join(',', $CI->config->item('n___7358')) . ')' => null, //ACTIVE
        );
        
    } elseif($x__type==12273){

        //Determine Sort:
        $order_columns = array();
        foreach($CI->config->item('e___13550') as $x__sort_id => $sort) {
            $order_columns['x__type = \''.$x__sort_id.'\' DESC'] = null;
        }
        $order_columns['x__spectrum'] = 'ASC';
        $order_columns['i__title'] = 'ASC';

        //IDEAS
        $join_objects = array('x__right');
        $query_filters = array(
            'i__type IN (' . join(',', $CI->config->item('n___7355')) . ')' => null, //PUBLIC
            'x__status IN (' . join(',', $CI->config->item('n___7359')) . ')' => null, //PUBLIC
            'x__type IN (' . join(',', $CI->config->item('n___13550')) . ')' => null, //SOURCE IDEAS
            'x__up' => $e__id,
        );

    } elseif($x__type==6255){

        //Determine Sort:
        $order_columns = array();
        foreach($CI->config->item('e___6255') as $x__sort_id => $sort) {
            $order_columns['x__type = \''.$x__sort_id.'\' DESC'] = null;
        }
        $order_columns['x__id'] = 'DESC';

        //DISCOVERIES
        $join_objects = array('x__left');
        $query_filters = array(
            'x__source' => $e__id,
            'x__type IN (' . join(',', $CI->config->item('n___6255')) . ')' => null, //DISCOVERIES
            'x__status IN (' . join(',', $CI->config->item('n___7359')) . ')' => null, //PUBLIC
            'i__type IN (' . join(',', $CI->config->item('n___7355')) . ')' => null, //PUBLIC
        );

    } else {

        return null;

    }


    //Return Results:
    if($page_num > 0){

        $limit = view_memory(6404,11064);
        $query = $CI->X_model->fetch($query_filters, $join_objects, $limit, ($page_num-1)*$limit, $order_columns);
        if($x__type==11030){
            $query = array_reverse($query);
        }
        return $query;

    } else {

        $e___11035 = $CI->config->item('e___11035'); //COINS
        $query = $CI->X_model->fetch($query_filters, $join_objects, 1, 0, array(), 'COUNT(x__id) as totals');
        $count_query = $query[0]['totals'];
        $visual_counter = view_number($count_query);
        $title_desc = number_format($count_query, 0).' '.$e___11035[$x__type]['m__title'];

        if($append_coin_icon){

            if(!$count_query){
                return null;
            }

            $current_e = ( substr($first_segment, 0, 1)=='@' ? intval(substr($first_segment, 1)) : 0 );
            $coin_icon = '<span class="icon-block-xxs">'.$e___11035[$x__type]['m__cover'].'</span>';

            $ui = '<div class="dropdown inline-block">';
            $ui .= '<button type="button" class="btn no-left-padding no-right-padding css__title load_e_coins button_of_'.$e__id.'_'.$x__type.'" id="coin_e_group_'.$x__type.'_'.$e__id.'" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" load_x__type="'.$x__type.'" load_e__id="'.$e__id.'" load_counter="'.$count_query.'" load_first_segment="'.$first_segment.'" load_current_e="'.$current_e.'" ><span title="'.$title_desc.'" data-toggle="tooltip" data-placement="top">'.$coin_icon.$visual_counter.'</span></button>';
            $ui .= '<div class="dropdown-menu dropdown_'.$x__type.' coins_e_'.$e__id.'_'.$x__type.'" aria-labelledby="coin_e_group_'.$x__type.'_'.$e__id.'">';
                //Menu To be loaded dynamically via AJAX
            $ui .= '</div>';
            $ui .= '</div>';

            return $ui;

        } else {
            return intval($count_query);
        }
    }

}


function view_coins_i($x__type, $i__id, $page_num = 0, $append_coin_icon = true){

    /*
     *
     * Loads Idea
     *
     * */

    $CI =& get_instance();
    $first_segment = $CI->uri->segment(1);

    if($x__type==12274){

        //SOURCES
        $join_objects = array('x__up');
        $query_filters = array(
            'x__status IN (' . join(',', $CI->config->item('n___7359')) . ')' => null, //PUBLIC
            'x__type IN (' . join(',', $CI->config->item('n___13550')) . ')' => null, //SOURCE IDEAS
            'x__right' => $i__id,
            'x__up >' => 0, //MESSAGES MUST HAVE A SOURCE REFERENCE TO ISSUE IDEA COINS
        );

        $order_columns = array();
        foreach($CI->config->item('e___13550') as $x__sort_id => $sort) {
            $order_columns['x__type = \''.$x__sort_id.'\' DESC'] = null;
        }
        foreach($CI->config->item('e___6177') as $x__sort_id => $sort) {
            $order_columns['e__status = \''.$x__sort_id.'\' DESC'] = null;
        }
        $order_columns['e__title'] = 'ASC';

    } elseif($x__type==11019) {

        //IDEAS PREVIOUS
        $order_columns = array('i__title' => 'ASC');
        $join_objects = array('x__left');
        $query_filters = array(
            'x__status IN (' . join(',', $CI->config->item('n___7360')) . ')' => null, //ACTIVE
            'i__type IN (' . join(',', $CI->config->item('n___7355')) . ')' => null, //PUBLIC
            'x__type IN (' . join(',', $CI->config->item('n___4486')) . ')' => null, //IDEA LINKS
            'x__right' => $i__id,
        );

    } elseif($x__type==12273){

        //IDEAS NEXT
        $order_columns = array('x__spectrum' => 'ASC');
        $join_objects = array('x__right');
        $query_filters = array(
            'x__status IN (' . join(',', $CI->config->item('n___7359')) . ')' => null, //PUBLIC
            'i__type IN (' . join(',', $CI->config->item('n___7355')) . ')' => null, //PUBLIC
            'x__type IN (' . join(',', $CI->config->item('n___4486')) . ')' => null, //IDEA LINKS
            'x__left' => $i__id,
        );

    } elseif($x__type==6255){

        //DISCOVERIES
        $order_columns = array('x__id' => 'DESC');
        $join_objects = array('x__source');
        $query_filters = array(
            'x__status IN (' . join(',', $CI->config->item('n___7359')) . ')' => null, //PUBLIC
            'x__type IN (' . join(',', $CI->config->item('n___6255')) . ')' => null, //DISCOVERIES
            'x__left' => $i__id,
        );
        if(isset($_GET['load__e'])){
            $query_filters['x__source'] = intval($_GET['load__e']);
        }

    } else {

        return null;

    }


    //Return Results:
    if($page_num > 0){

        $limit = view_memory(6404,11064);
        return $CI->X_model->fetch($query_filters, $join_objects, $limit, ($page_num-1)*$limit, $order_columns);

    } else {

        $e___11035 = $CI->config->item('e___11035'); //COINS
        $query = $CI->X_model->fetch($query_filters, $join_objects, 1, 0, array(), 'COUNT(x__id) as totals');
        $count_query = $query[0]['totals'];
        $visual_counter = view_number($count_query);
        $title_desc = number_format($count_query, 0).( isset($e___11035[$x__type]['m__title']) ? ' '.$e___11035[$x__type]['m__title'] : '' );

        if($append_coin_icon){

            if(!$count_query){
                return null;
            }

            $coin_icon = '<span class="icon-block-xxs">'.$e___11035[$x__type]['m__cover'].'</span>';

            $ui = '<div class="dropdown inline-block">';
            $ui .= '<button type="button" class="btn no-left-padding no-right-padding css__title load_i_coins button_of_'.$i__id.'_'.$x__type.'" id="coin_i_group_'.$x__type.'_'.$i__id.'" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" load_x__type="'.$x__type.'" load_i__id="'.$i__id.'" load_counter="'.$count_query.'" load_first_segment="'.$first_segment.'"><span title="'.$title_desc.'" data-toggle="tooltip" data-placement="top">'.$coin_icon.$visual_counter.'</span></button>';

            //Menu To be loaded dynamically via AJAX:
            $ui .= '<div class="dropdown-menu dropdown_'.$x__type.' coins_i_'.$i__id.'_'.$x__type.'" aria-labelledby="coin_i_group_'.$x__type.'_'.$i__id.'"></div>';
            
            $ui .= '</div>';

            return $ui;

        } else {
            return intval($count_query);
        }

    }

}

function view_radio_e($focus_id, $child___id, $enable_mulitiselect){

    /*
     * Print UI for
     * */

    $CI =& get_instance();
    $count = 0;

    $ui = '<div class="list-group list-radio-select radio-'.$focus_id.'">';

    if(!is_array($CI->config->item('n___'.$focus_id)) || !count($CI->config->item('n___'.$focus_id))){
        return false;
    }

    $already_selected = array();
    foreach($CI->X_model->fetch(array(
        'x__up IN (' . join(',', $CI->config->item('n___'.$focus_id)) . ')' => null,
        'x__down' => $child___id,
        'x__type IN (' . join(',', $CI->config->item('n___4592')) . ')' => null, //SOURCE LINKS
        'x__status IN (' . join(',', $CI->config->item('n___7359')) . ')' => null, //PUBLIC
    )) as $sel){
        array_push($already_selected, $sel['x__up']);
    }

    if(!count($already_selected) && in_array($focus_id, $CI->config->item('n___6684')) && superpower_unlocked()){
        //FIND DEFAULT if set in session of this user:
        foreach($CI->config->item('e___'.$focus_id) as $e__id2 => $m2){
            $var_id = @$CI->session->userdata('session_custom_ui_'.$focus_id);
            if($var_id==$e__id2){
                $already_selected = array($e__id2);
                break;
            }
        }
    }

    foreach($CI->config->item('e___'.$focus_id) as $e__id => $m) {
        $ui .= '<span class=""><a href="javascript:void(0);" onclick="e_radio('.$focus_id.','.$e__id.','.$enable_mulitiselect.')" class="list-group-item css__title custom_ui_'.$focus_id.'_'.$e__id.' itemsetting item-'.$e__id.' '.( in_array($e__id, $already_selected) ? ' active ' : '' ). '">'.( strlen($m['m__cover']) ? '<span class="icon-block change-results">'.$m['m__cover'].'</span>' : '' ).$m['m__title'].'</a></span>';
        $count++;
    }

    $ui .= '</div>';

    return $ui;
}


function view_i_list($x__type, $top_i__id, $i, $next_is, $member_e){

    //If no list just return the next step:
    $CI =& get_instance();
    if(!count($next_is)){
        return false;
    } elseif(!in_array($x__type, $CI->config->item('n___13369'))){
        return false;
    }

    $e___13369 = $CI->config->item('e___13369'); //IDEA LISTS

    //Build Body UI:
    $body = '<div class="row">';
    foreach($next_is as $key => $next_i){
        $body .= view_i_card($x__type, $top_i__id, $i, $next_i, $member_e, ( $member_e ? $CI->X_model->tree_progress($member_e['e__id'], $next_i) : null ), null);
    }
    $body .= '</div>';

    return view_headline($x__type, count($next_is), $e___13369[$x__type], $body, isset($_GET['open']));

}


function view_shuffle_message($e__id){
    $CI =& get_instance();
    $e___12687 = $CI->config->item('e___12687');
    $line_messages = explode("\n", $e___12687[$e__id]['m__message']);
    return $line_messages[rand(0, (count($line_messages) - 1))];
}


function view_e_settings($list_id, $is_open){

    $CI =& get_instance();
    $member_e = superpower_unlocked();
    $e___14010 = $CI->config->item('e___14010');
    $ui = null;
    if(!$member_e){
        return false;
    }

    //Display account fields ordered with their SOURCE LINKS:
    foreach($CI->config->item('e___'.$list_id) as $acc_e__id => $acc_detail) {

        //Skip if domain specific:
        $hosted_domains = array_intersect($CI->config->item('n___14870'), $acc_detail['m__following']);
        if(count($hosted_domains) && !in_array(website_setting(0), $hosted_domains)){
            continue;
        }

        //Skip if missing superpower:
        $superpower_actives = array_intersect($CI->config->item('n___10957'), $acc_detail['m__following']);
        if(count($superpower_actives) && !superpower_active(end($superpower_actives), true)){
            continue;
        }

        //Print account fields that are either Single Selectable or Multi Selectable:
        $superpower_actives = array_intersect($CI->config->item('n___10957'), $acc_detail['m__following']);
        $has_multi_selectable = in_array(7231, $acc_detail['m__following']);
        $has_single_selectable = in_array(6684, $acc_detail['m__following']);
        $tab_ui = null;

        //Switch if part of domain settings:
        if(in_array($acc_e__id, $CI->config->item('n___14925'))){
            $domain_specific_id = intval(website_setting($acc_e__id));
            if($domain_specific_id){
                //Replace with domain specific:
                $acc_e__id = $domain_specific_id;
            } else {
                continue;
            }
        }

        //Append description if any:
        if(strlen($acc_detail['m__message']) > 0){
            $tab_ui .= '<div class="regtext" style="text-align: left; padding:0 0 21px 0;">' . nl2br($acc_detail['m__message']) . '</div>';
        }


        if ($acc_e__id == 10957 /* Superpowers */) {

            if(count($CI->session->userdata('session_superpowers_unlocked')) >= 2){
                //Mass Toggle Option:
                $tab_ui .= '<div class="btn-group pull-right" role="group" style="margin:0 0 10px 0;">
                  <a href="javascript:void(0)" onclick="account_toggle_all(1)" class="btn btn-far"><i class="fas fa-toggle-on"></i></a>
                  <a href="javascript:void(0)" onclick="account_toggle_all(0)" class="btn btn-fad"><i class="fas fa-toggle-off"></i></a>
                </div><div class="doclear">&nbsp;</div>';
            }

            //SUPERPOWERS
            $tab_ui .= '<div class="list-group">';
            foreach($CI->config->item('e___10957') as $superpower_e__id => $m3){

                $has_unlocked = in_array($superpower_e__id, $CI->session->userdata('session_superpowers_unlocked'));
                $public_link = in_array($superpower_e__id, $CI->config->item('n___6404'));
                $anchor = '<span class="icon-block main-icon" title="@'.$superpower_e__id.'">'.$m3['m__cover'].'</span><b class="css__title">'.$m3['m__title'].'</b><span class="superpower-message">'.$m3['m__message'].'</span>';

                if($has_unlocked){

                    //SUPERPOWERS UNLOCKED
                    $progress_type_id=14008;
                    $tab_ui .= '<a class="list-group-item itemsetting btn-superpower superpower-frame-'.$superpower_e__id.' '.( superpower_active($superpower_e__id, true) ? ' active ' : '' ).'" en-id="'.$superpower_e__id.'" href="javascript:void();" onclick="e_toggle_superpower('.$superpower_e__id.')"><span class="icon-block pull-right" title="'.$e___14010[$progress_type_id]['m__title'].'">'.$e___14010[$progress_type_id]['m__cover'].'</span>'.$anchor.'</a>';

                } elseif(!$has_unlocked && $public_link){

                    //SUPERPOWERS AVAILABLE
                    $progress_type_id=14011;
                    $tab_ui .= '<a class="list-group-item no-side-padding" href="'.view_memory(6404,$superpower_e__id).'"><span class="icon-block pull-right" title="'.$e___14010[$progress_type_id]['m__title'].'">'.$e___14010[$progress_type_id]['m__cover'].'</span>'.$anchor.'</a>';

                } elseif(!$has_unlocked && !$public_link){

                    //SUPERPOWERS UNAVAILABLE
                    $progress_type_id=14009;
                    $tab_ui .= '<a href="javascript:void();" onclick="alert(\'This superpower is locked & cannot be unlocked at this time. Start by unlocking other available superpowers.\')" class="list-group-item no-side-padding islocked grey '.superpower_active(10939).'"><span class="icon-block pull-right" title="'.$e___14010[$progress_type_id]['m__title'].'">'.$e___14010[$progress_type_id]['m__cover'].'</span>'.$anchor.'</a>';

                }

            }

            $tab_ui .= '</div>';

        } elseif ($acc_e__id == 3288 /* Email */) {

            $u_emails = $CI->X_model->fetch(array(
                'x__status IN (' . join(',', $CI->config->item('n___7359')) . ')' => null, //PUBLIC
                'x__down' => $member_e['e__id'],
                'x__type IN (' . join(',', $CI->config->item('n___4592')) . ')' => null, //SOURCE LINKS
                'x__up' => 3288, //Email
            ));

            $tab_ui .= '<span><input type="email" id="e_email" class="form-control border dotransparent" value="' . (count($u_emails) > 0 ? $u_emails[0]['x__message'] : '') . '" placeholder="you@gmail.com" /></span>
                <a href="javascript:void(0)" onclick="e_email()" class="btn btn-default">Save</a>
                <span class="saving-account save_email"></span>';


        } elseif ($acc_e__id == 4783 /* Phone */) {

            $u_phones = $CI->X_model->fetch(array(
                'x__status IN (' . join(',', $CI->config->item('n___7359')) . ')' => null, //PUBLIC
                'x__down' => $member_e['e__id'],
                'x__type IN (' . join(',', $CI->config->item('n___4592')) . ')' => null, //SOURCE LINKS
                'x__up' => 4783, //Phone
            ));

            $tab_ui .= '<span><input type="number" id="e_phone" class="form-control border dotransparent" value="' . (count($u_phones) > 0 ? $u_phones[0]['x__message'] : '') . '" placeholder="7781234567" /></span>
                <a href="javascript:void(0)" onclick="e_phone()" class="btn btn-default">Save</a>
                <span class="saving-account save_phone"></span>';

        } elseif ($acc_e__id == 30198 /* Full Name */) {

            $u_names = $CI->X_model->fetch(array(
                'x__status IN (' . join(',', $CI->config->item('n___7359')) . ')' => null, //PUBLIC
                'x__down' => $member_e['e__id'],
                'x__type IN (' . join(',', $CI->config->item('n___4592')) . ')' => null, //SOURCE LINKS
                'x__up' => 30198, //Full Name
            ));

            $tab_ui .= '<span><input type="text" id="e_fullname" class="form-control border dotransparent" value="' . (count($u_names) > 0 ? $u_names[0]['x__message'] : '') . '" placeholder="Will Smith" /></span>
                <a href="javascript:void(0)" onclick="e_fullname()" class="btn btn-default">Save</a>
                <span class="saving-account save_name"></span>';

        } elseif ($acc_e__id == 3286 /* Password */) {

            $tab_ui .= '<span><input type="password" id="input_password" class="form-control border dotransparent" data-lpignore="true" autocomplete="new-password" placeholder="New Password..." /></span>
                <a href="javascript:void(0)" onclick="e_password()" class="btn btn-default">Save</a>
                <span class="saving-account save_password"></span>';

        } elseif ($has_multi_selectable || $has_single_selectable) {

            $tab_ui .= view_radio_e($acc_e__id, $member_e['e__id'], ($has_multi_selectable ? 1 : 0));

        }

        $ui .= view_headline($acc_e__id, null, $acc_detail, $tab_ui, $is_open, true);

    }

    return $ui;

}


function view_unauthorized_message($superpower_e__id = 0){

    $member_e = superpower_unlocked($superpower_e__id);

    if(!$member_e){
        if(!$superpower_e__id){

            //Missing Session
            return 'You must login to continue.';

        } else {

            //Missing Superpower:
            $CI =& get_instance();
            $e___10957 = $CI->config->item('e___10957');
            return 'Missing: '.$e___10957[$superpower_e__id]['m__title'];

        }
    }


    return null;

}

function view_time_hours($total_seconds, $hide_hour = false){

    $total_seconds = intval($total_seconds);
    //Turns seconds into HH:MM:SS
    $hours = floor($total_seconds/3600);
    $minutes = floor(fmod($total_seconds, 3600)/60);
    $seconds = fmod($total_seconds, 60);

    return ( $hide_hour && !$hours ? '' : str_pad($hours, 2, "0", STR_PAD_LEFT).':' ).str_pad($minutes, 2, "0", STR_PAD_LEFT).':'.str_pad($seconds, 2, "0", STR_PAD_LEFT);
}

function view__load__e($e){
    $CI =& get_instance();
    $e___11035 = $CI->config->item('e___11035');
    return '<div class="msg alert alert-info no-margin" style="margin-bottom: 10px !important;" title="'.$e___11035[13670]['m__title'].'"><span class="icon-block">'.$e___11035[13670]['m__cover'] . '</span><span class="icon-block-xs">' . view_cover(12274,$e['e__cover'], true) . '</span><a href="/@'.$e['e__id'].'">' . $e['e__title'].'</a>&nbsp;&nbsp;&nbsp;<a href="/'.$CI->uri->segment(1).'" title="'.$e___11035[13671]['m__title'].'">'.$e___11035[13671]['m__cover'].'</a></div>';
}




function view_i_select_card($i, $x__source, $previously_selected){

    //Search to see if an idea has a thumbnail:
    $CI =& get_instance();
    $i_title = view_i_title($i);
    $member_e = superpower_unlocked();
    $spots_remaining = i_spots_remaining($i['i__id']);


    $href = 'href="javascript:void(0);"'.( $spots_remaining==0 && !$previously_selected ? ' onclick="alert(\'This Option is Not Available\')" ' : ' onclick="toggle_answer(' . $i['i__id'] . ')"' );

    $ui  = '<div class="coin_cover col-6 col-md-4 no-padding">';
    $ui .= '<div class="cover-wrapper">';
    $ui .= '<table class="coin_coins"></table>'; //For UI height adjustment
    $ui .= '<a '.$href.' selection_i__id="' . $i['i__id'] . '" class="answer-item black-background-obs cover-link x_select_' . $i['i__id'] . ($previously_selected ? ' isSelected ' : '') . ( $spots_remaining==0 ? ' greyout ' : '' ).'">';

    $ui .= '</a>';
    $ui .= '</div>';

    $ui .= '<div class="cover-content"><div class="inner-content">';
    $ui .= '<a '.$href.'>'.$i_title.'</a>';

    $ui .= '<div class="cover-text">';


    if($spots_remaining >= 0){
        //$ui .= '<a '.$href.' class="doblock" style="padding-bottom:2px;"><span class="mini-font '.( $spots_remaining==0 ? ' grey ' : ' isgreen ' ).'">[' .( $spots_remaining==0 ? 'Not Available' : $spots_remaining . ' Remaining' ) .']</span></a>';
    }

    //Messages:
    $ui .= '<a '.$href.' class="hideIfEmpty doblock">';
    foreach($CI->X_model->fetch(array(
        'x__status IN (' . join(',', $CI->config->item('n___7359')) . ')' => null, //PUBLIC
        'x__type' => 4231, //IDEA NOTES Messages
        'x__right' => $i['i__id'],
    ), array(), 0, 0, array('x__spectrum' => 'ASC')) as $message_x) {
        $ui .= $CI->X_model->message_view($message_x['x__message'], true, $member_e, 0, true);
    }
    $ui .= '</a>';

    $ui .= '</div>';


    $ui .= '</div></div>';
    $ui .= '</div>';

    return $ui;

}


function view_i_card($x__type, $top_i__id = 0, $previous_i = null, $i, $focus_e = false, $tree_progress = null, $extra_class = null){

    //Search to see if an idea has a thumbnail:
    $CI =& get_instance();
    if(!in_array($x__type, $CI->config->item('n___13369'))){
        return 'Invalid x__type i '.$x__type;
    }
    $e___31904 = $CI->config->item('e___31904'); //Idea Card
    $e___13369 = $CI->config->item('e___13369'); //IDEA LIST
    $cache_app = in_array($x__type, $CI->config->item('n___14599'));
    $x__id = ( isset($i['x__id']) && $i['x__id']>0 ? $i['x__id'] : 0 );

    $member_e = superpower_unlocked();
    $e_of_i = ( $cache_app ? false : e_of_i($i['i__id']) );
    $user_input = $focus_e;
    $superpower_10939 = superpower_active(10939, true);

    $primary_icon = in_array($x__type, $CI->config->item('n___14378')); //PRIMARY ICON
    $discovery_mode = $top_i__id>0 || in_array($x__type, $CI->config->item('n___14378')); //DISCOVERY MODE
    $linkbar_visible = in_array($x__type, $CI->config->item('n___20410'));
    $focus_coin = in_array($x__type, $CI->config->item('n___12149')); //NODE COIN
    $has_self = $member_e && $focus_e && $member_e['e__id']==$focus_e['e__id'];

    if(!$focus_e){
        $focus_e = $member_e;
    }

    $load_completion = in_array($x__type, $CI->config->item('n___14501')) && $top_i__id > 0 && $focus_e && $discovery_mode;


    if(0 && is_null($tree_progress)){
        if($load_completion){ //Load Completion Bar
            $tree_progress = $CI->X_model->tree_progress($focus_e['e__id'], $i);
        } else {
            //set zero:
            $tree_progress['fixed_completed_percentage'] = 0;
        }
    } elseif($discovery_mode){
        $tree_progress['fixed_completed_percentage'] = 100;
    }



    $is_completed = ($tree_progress['fixed_completed_percentage']>=100);
    $is_started = ($tree_progress['fixed_completed_percentage']>0);
    $parent_is_or = ( $discovery_mode && $previous_i && in_array($previous_i['i__type'], $CI->config->item('n___7712')) );
    $has_sortable = !$focus_coin && $e_of_i && in_array($x__type, $CI->config->item('n___4603'));
    $i_title = view_i_title($i);

    if($discovery_mode && !$is_completed) {
        if($top_i__id){
            $href = '/x/x_next/'.$top_i__id.'/'.$i['i__id'];
        } elseif($e_of_i) {
            $href = '/~'.$i['i__id'];
        } else {
            $href = '/'.$i['i__id'];
        }
    } elseif(strlen($e___13369[$x__type]['m__message'])){
        $href = $e___13369[$x__type]['m__message'].$i['i__id'];
    } elseif(in_array($x__type, $CI->config->item('n___14742')) && $previous_i && $member_e && $top_i__id){
        //Complete if not already:
        $href = '/x/complete_next/'.$top_i__id.'/'.$previous_i['i__id'].'/'.$i['i__id'];
    } elseif($discovery_mode){
        if($top_i__id > 0 && $top_i__id!=$i['i__id']){
            $href = '/'.$top_i__id.'/'.$i['i__id'];
        } else {
            $href = '/'.$i['i__id'];
        }
    } else {
        $href = '/i/i_go/'.$i['i__id'] . ( isset($_GET['load__e']) ? '?load__e='.intval($_GET['load__e']) : '' );
    }


    $e___4737 = $CI->config->item('e___4737'); // Idea Status
    $first_segment = $CI->uri->segment(1);
    $current_i = ( substr($first_segment, 0, 1)=='~' ? intval(substr($first_segment, 1)) : 0 );
    $can_click = !$focus_coin && (!$e_of_i || $discovery_mode);




    //LOCKED
    $o_menu = '';
    $action_buttons = null;
    $focus_menu = ( $focus_coin ? 11047 : 14955 );

    if(!$cache_app) {

        foreach($CI->config->item('e___'.$focus_menu) as $e__id => $m) {

            //Skip if missing superpower:
            $superpower_actives = array_intersect($CI->config->item('n___10957'), $m['m__following']);
            if(count($superpower_actives) && !superpower_active(end($superpower_actives), true)){
                //Missing Superpower
                continue;
            }

            $anchor = '<span class="icon-block">'.$m['m__cover'].'</span>'.$m['m__title'];

            if($e__id==12589 && !$discovery_mode){
                //Mass Apply
                $action_buttons .= '<a href="javascript:void(0);" onclick="apply_all_load(12589,'.$i['i__id'].')" class="dropdown-item css__title">'.$anchor.'</a>';
            } elseif($e__id==30795 && !$discovery_mode && $superpower_10939){
                //Discovery Mode
                $action_buttons .= '<a href="/'.$i['i__id'].'" class="dropdown-item css__title">'.$anchor.'</a>';
            } elseif($e__id==10673 && $x__id && !in_array($i['x__type'], $CI->config->item('n___31776')) && $e_of_i){
                //Unlink
                $action_buttons .= '<a href="javascript:void(0);" class="dropdown-item css__title x_remove" i__id="'.$i['i__id'].'" x__id="'.$x__id.'">'.$anchor.'</a>';
            } elseif($e__id==30873 && !$discovery_mode){
                //Template:
                $action_buttons .= '<a href="javascript:void(0);" onclick="i_copy('.$i['i__id'].', 1)" class="dropdown-item css__title">'.$anchor.'</a>';
            } elseif($e__id==29771 && !$discovery_mode){
                //Clone:
                $action_buttons .= '<a href="javascript:void(0);" onclick="i_copy('.$i['i__id'].', 0)" class="dropdown-item css__title">'.$anchor.'</a>';
            } elseif($e__id==28636 && $e_of_i && $x__id){
                //Transaction Details
                $action_buttons .= '<a href="/-4341?x__id='.$x__id.'" class="dropdown-item css__title" target="_blank">'.$anchor.'</a>';
            } elseif($e__id==6182 && $e_of_i && !$discovery_mode){
                //Delete
                $action_buttons .= '<a href="javascript:void();" new-en-id="6182" onclick="update_dropdown(4737, 6182, '.$i['i__id'].', '.$x__id.', 0)" class="dropdown-item dropi_4737_'.$i['i__id'].'_'.$x__id.' css__title optiond_6182_'.$i['i__id'].'_'.$x__id.'">'.$anchor.'</a>';
            } elseif($e__id==28637 && isset($i['x__type'])){
                //Paypal Details
                $x__metadata = unserialize($i['x__metadata']);
                if(isset($x__metadata['txn_id'])){
                    $action_buttons .= '<a href="https://www.paypal.com/activity/payment/'.$x__metadata['txn_id'].'" class="dropdown-item css__title" target="_blank">'.$anchor.'</a>';
                }
            } elseif(substr($m['m__message'], 0, 1)=='/' && !$discovery_mode){
                //Standard button
                $action_buttons .= '<a href="'.$m['m__message'].$i['i__id'].'" class="dropdown-item css__title">'.$anchor.'</a>';
            }
        }

        //Any Buttons?
        if($action_buttons){
            //Right Action Menu
            $o_menu .= '<div class="dropdown inline-block">';
            $o_menu .= '<button type="button" class="btn no-left-padding no-right-padding css__title" id="action_menu_i_'.$i['i__id'].'" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="'.$e___31904[$focus_menu]['m__title'].'">'.$e___31904[$focus_menu]['m__cover'].'</button>';
            $o_menu .= '<div class="dropdown-menu" aria-labelledby="action_menu_i_'.$i['i__id'].'">';
            $o_menu .= $action_buttons;
            $o_menu .= '</div>';
            $o_menu .= '</div>';
        }

    }



    //Top action menu:
    $ui = '<div '.( $x__id ? ' x__id="'.$x__id.'" ' : '' ).' class="coin_cover '.( $focus_coin ? ' focus-coin slim_flat col-md-8 col-12 ' : ' edge-coin coin_i_click col-md-4 col-6 ' ).( $parent_is_or ? ' doborderless ' : '' ).' no-padding '.( $is_completed ? ' coin-6255 ' : ' coin-12273 ' ).' coin___12273_'.$i['i__id'].' '.( $has_sortable ? ' cover_sort x_sort ' : '' ).( $x__id ? ' cover_x_'.$x__id.' ' : '' ).' '.$extra_class.'">';


    $ui .= '<table class="coin_coins '.( !$discovery_mode ? ' style="" ' : '' ).'"><tr>';


    //Link Type:
    $ui .= '<td width="20%"><div class="show-on-hover">';
    if($x__id && ($e_of_i || ($x__id>0 && $i['x__source']==$member_e['e__id']))){
        foreach($CI->config->item('e___31770') as $x__type1 => $m1){
            if(in_array($i['x__type'], $CI->config->item('n___'.$x__type1))){
                foreach($CI->X_model->fetch(array(
                    'x__id' => $x__id,
                ), array('x__source')) as $linker){
                    $ui .= view_input_dropdown($x__type1, $i['x__type'], null, $e_of_i && !$discovery_mode, false, $i['i__id'], $x__id);
                }
                break;
            }
        }
    } elseif($focus_coin) {
        //You Are Here
        $ui .= '<span title="'.$e___31904[31914]['m__title'].'" data-toggle="tooltip" data-placement="top">'.$e___31904[31914]['m__cover'].'</span>';
    }
    $ui .= '</div></td>';



    //Idea Type
    $ui .= '<td width="20%"><div class="show-on-hover">';
    if(!$discovery_mode){
        $ui .= view_input_dropdown(4737, $i['i__type'], null, $e_of_i && !$discovery_mode, false, $i['i__id']);
    }
    $ui .= '</div></td>';


    //Idea Status
    $ui .= '<td width="20%"><div class="show-on-hover">';
    if(!$discovery_mode){
        $ui .= view_input_dropdown(31004, $i['i__status'], null, $e_of_i && !$discovery_mode, false, $i['i__id']);
    }
    $ui .= '</div></td>';




    //Edit:
    $ui .= '<td width="20%"><div class="show-on-hover">'.( $e_of_i && !$discovery_mode ? '<a href="javascript:void(0);" onclick="coin__load(12273,'.$i['i__id'].')">'.$e___31904[31911]['m__cover'].'</a>' : '').'</div></td>';

    //Menu:
    $ui .= '<td width="20%"><div class="show-on-hover">'.$o_menu.'</div></td>';
    $ui .= '</tr></table>';

    $ui .= '<div class="cover-wrapper cover_wrapper12273">';






    //Coin Cover
    $ui .= ( !$can_click ? '<div' : '<a href="'.$href.'"' ).' class="'.( $is_completed ? ' coinType6255 ' : ' coinType12273 ' ).' black-background-obs cover-link">';


    $ui .= ( !$can_click ? '</div>' : '</a>' );
    $ui .= '</div>'; //cover-wrapper



    //Title Cover
    $ui .= '<div class="cover-content">';

    if($load_completion && $is_started && !$is_completed){
        $ui .= '<div class="cover-progress">'.view_x_progress($tree_progress, $i).'</div>';
    }

    $ui .= '<div class="inner-content">';


    //TITLE
    if($e_of_i && !$discovery_mode){
        //Editable title:
        $ui .= view_input_text(4736, $i['i__title'], $i['i__id'], $e_of_i, (isset($i['x__spectrum']) ? (($i['x__spectrum']*100)+1) : 0), true);
    } elseif($can_click){
        $ui .= '<a href="'.$href.'">'.$i_title.'</a>';
    } else {
        $ui .= $i_title;
    }



    //IDEAs & Time & Message
    $message_tooltip = '';
    if(isset($i['x__message']) && strlen($i['x__message'])>0){

        if(superpower_active(12701, true)){
            $message_tooltip = '<a href="javascript:void(0);" onclick="x_message_load(' . $x__id . ')" class="mini-font">'.$CI->X_model->message_view( $i['x__message'], true).'</a>';
        } elseif($e_of_i || !$discovery_mode) {
            $message_tooltip = '<p class="mini-font" title="'.$i['x__message'].'">'.$CI->X_model->message_view( $i['x__message'], true).'</p>';
        }

    } else {

        $messages = '';
        foreach($CI->X_model->fetch(array(
            'x__status IN (' . join(',', $CI->config->item('n___7360')) . ')' => null, //ACTIVE
            'x__type' => 4231,
            'x__right' => $i['i__id'],
        ), array('x__source'), 0, 0, array('x__spectrum' => 'ASC')) as $mes){
            $messages .= $CI->X_model->message_view($mes['x__message'], true, $member_e, 0, true);
        }

        if($e_of_i && !$discovery_mode) {
            //Can edit:
            $message_tooltip = '<a href="javascript:void(0);" onclick="load_message_27963(' . $i['i__id'] . ')" class="mini-font messages_4231_' . $i['i__id'] . '">' . (strlen($messages) ? $messages : '<i class="no-message">Write Message...</i>') . '</a>';
        } elseif($can_click){
            $message_tooltip = '<a href="'.$href.'">'.$messages.'</a>';
        } else {
            $message_tooltip = $messages;
        }

    }

    $ui .= '<div class="cover-text">';

    if($message_tooltip){
        $ui .= '<div class="">' . $message_tooltip . '</div>'; //grey
    }

    $ui .= '</div>';

    $ui .= '</div></div>';

    //Bottom Bar
    if(!$focus_coin && !$discovery_mode){
        $ui .= '<div class="coin_coins"><div class="show-on-hover">';
        foreach($CI->config->item('e___31890') as $menu_id => $m) {
            $superpower_actives = array_intersect($CI->config->item('n___10957'), $m['m__following']);
            $ui .= '<span class="hideIfEmpty '.( count($superpower_actives) ? superpower_active(end($superpower_actives)) : '' ).'">';
            if($menu_id==31892 && !$can_click && $member_e && !$focus_coin && !$discovery_mode){
                $ui .= '<a href="'.$href.'" class="right-btn" title="'.$m['m__title'].'">'.$m['m__cover'].'</a>';
            } else {
                $ui .= view_coins_i($menu_id,  $i['i__id']);
            }
            $ui .= '</span>';
        }
        $ui .= '</div></div>';

    }


    $ui .= '</div>';



    return $ui;

}

function view_featured_source($x__source, $x){

    //See if this member also follows this featured source?
    $CI =& get_instance();
    $member_follows = array();
    if($x__source>0){
        $member_follows = $CI->X_model->fetch(array(
            'x__up' => $x['e__id'],
            'x__down' => $x__source,
            'x__type IN (' . join(',', $CI->config->item('n___4592')) . ')' => null, //SOURCE LINKS
            'x__status IN (' . join(',', $CI->config->item('n___7360')) . ')' => null, //ACTIVE
        ));
    }

    $is_featured = in_array($x['e__status'], $CI->config->item('n___30977'));
    if(!$is_featured && !count($member_follows)){
        return false;
    }

    $messages = '';
    foreach($member_follows as $member_follow){
        if(strlen($member_follow['x__message'])){
            $messages .= '<h2 style="padding:0 0 8px;">' . $member_follow['x__message'] . '</h2>';
        }
    }

    if(!$is_featured && !$messages){
        return false;
    }

    if(strlen($messages)){
        $x['x__message'] = ( strlen($x['x__message']) ? $messages.nl2br($x['x__message']) : $messages );
    }


    return '<div class="source-info">'
        . '<span class="icon-block">'.view_cover(12274,$x['e__cover'], true) . '</span>'
        . '<span>'.$x['e__title'] . '</span>'
        . '<div class="payment_box">'. ( $x['e__id']==30976 /* Hack: Location loads with Google Maps */ ? '<a href="https://www.google.com/maps/search/'.urlencode($x['x__message']).'" target="_blank" style="text-decoration:underline;" class="sub_note css__title">'.$x['x__message'].'</a>' : '<div class="sub_note css__title">'.nl2br($x['x__message']).'</div>' ) . '</div>'
        . '</div>';

    /*
     *
     * <div '.( $x__source==1 ? 'id="load_map" style="width:100%;height:200px;"' : '' ).'></div><script>

        $(document).ready(function () {
            let map;
let service;
let infowindow;

function initMap() {
  const sydney = new google.maps.LatLng(-33.867, 151.195);

  infowindow = new google.maps.InfoWindow();
  map = new google.maps.Map(document.getElementById("load_map"), {
    center: sydney,
    zoom: 15,
  });

  const request = {
    query: "Museum of Contemporary Art Australia",
    fields: ["name", "geometry"],
  };

  service = new google.maps.places.PlacesService(map);
  service.findPlaceFromQuery(request, (results, status) => {
    if (status === google.maps.places.PlacesServiceStatus.OK && results) {
      for (let i = 0; i < results.length; i++) {
        createMarker(results[i]);
      }

      map.setCenter(results[0].geometry.location);
    }
  });
}

function createMarker(place) {
  if (!place.geometry || !place.geometry.location) return;

  const marker = new google.maps.Marker({
    map,
    position: place.geometry.location,
  });

  google.maps.event.addListener(marker, "click", () => {
    infowindow.setContent(place.name || "");
    infowindow.open(map);
  });
}
            window.initMap = initMap;
        });


</script><script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAiwKqWXXTs14NsUhqd2B83nzGSDg1VOoU&libraries=places"></script>
     * */
}

function view_headline($x__type, $counter, $m, $ui, $is_open = true, $left_pad = false){

    if(!strlen($ui)){
        return false;
    }

    $CI =& get_instance();
    $e___26006 = $CI->config->item('e___26006'); //Toggle Headline
    return '<a class="headline headline_'.$x__type.'" href="javascript:void(0);" onclick="toggle_headline('.$x__type.')"><span class="icon-block">'.$m['m__cover'].'</span>' .$m['m__title'].( !is_null($counter) ? ' [<span class="xtypecounter'.$x__type.'">'.number_format($counter, 0) . '</span>]' : '' ).'<span class="icon-block pull-right headline_titles headline_title_'.$x__type.'"><span class="icon_26007 '.( !$is_open ? ' hidden ' : '' ).'">'.$e___26006[26008]['m__cover'].'</span><span class="icon_26008 '.( $is_open ? ' hidden ' : '' ).'">'.$e___26006[26007]['m__cover'].'</span></span></a>'.'<div class="headlinebody '.( $left_pad ? ' leftPad  ' : '' ).' headline_body_'.$x__type.( !$is_open ? ' hidden ' : '' ).'">'.$ui.'</div>';

}


function view_pill($focus_coin, $x__type, $counter, $m, $ui = null, $is_open = true){

    return '<script> '.( $is_open ? ' $(document).ready(function () { toggle_pills('.$x__type.'); }); ' : '' ).' $(\'.nav-tabs\').append(\'<li class="nav-item thepill'.$x__type.'"><a class="nav-link '.( $is_open ? ' active ' : '' ).'" x__type="'.$x__type.'" href="javascript:void(0);" data-toggle="tooltip" data-placement="top" title="'.number_format($counter, 0).' '.$m['m__title'].( strlen($m['m__message']) ? ': '.str_replace('\'','',str_replace('"','',$m['m__message'])) : '' ).'" onclick="toggle_pills('.$x__type.')">&nbsp;<span class="icon-block-xxs">'.$m['m__cover'].'</span><span class="css__title hideIfEmpty xtypecounter'.$x__type.'" style="padding-right:4px;">'.view_number($counter) . '</span></a></li>\') </script>' .
        '<div class="headlinebody headline_body_'.$x__type.( !$is_open ? ' hidden ' : '' ).'" read-counter="'.$counter.'">'.$ui.'</div>';

}

function view_x_progress($tree_progress, $i){

    if(!isset($tree_progress['fixed_total'])){
        return '<div class="progress-bg-list progress_'.$i['i__id'].'"><div class="progress-done" style="width:0%" prograte="0"></div></div>';
    }

    return '<div class="progress-bg-list progress_'.$i['i__id'].'" title="'.$tree_progress['fixed_completed_percentage'].'% COMPLETED"><div class="progress-done" style="width:'.$tree_progress['fixed_completed_percentage'].'%" prograte="'.$tree_progress['fixed_completed_percentage'].'"></div></div>';

}

function view_e_line($e)
{

    $ui = '<a href="/@'.$e['e__id'].'" class="doblock">';
    $ui .= '<span class="icon-block">'.view_cover(12274, $e['e__cover'], true).'</span>';
    $ui .= '<span class="css__title">'.$e['e__title'].'<span class="grey" style="padding-left:8px;">' . view_time_difference(strtotime($e['x__time'])) . ' Ago</span></span>';
    $ui .= '</a>';
    return $ui;

}



function view_e_card($x__type, $e, $extra_class = null)
{

    $CI =& get_instance();
    if(!in_array($x__type, $CI->config->item('n___14690'))){
        //Not a valid Source List
        return 'Invalid x__type e '.$x__type;
    }
    if(!isset($e['e__id']) || !isset($e['e__title'])){
        $CI->X_model->create(array(
            'x__type' => 4246, //Platform Bug Reports
            'x__message' => 'view_e_card() Missing core variables',
            'x__metadata' => array(
                '$x__type' => $x__type,
                '$e' => $e,
            ),
        ));
        return 'Missing core variables';
    }

    $source_of_e = source_of_e($e['e__id']);
    $member_e = superpower_unlocked();
    $e___11035 = $CI->config->item('e___11035'); //NAVIGATION
    $superpower_10939 = superpower_active(10939, true);
    $superpower_12706 = superpower_active(12706, true);
    $superpower_13422 = superpower_active(13422, true);
    $superpower_12701 = superpower_active(12701, true);
    $discovery_mode = in_array($x__type, $CI->config->item('n___14378')); //DISCOVERY MODE
    $focus_coin = in_array($x__type, $CI->config->item('n___12149')); //NODE COIN
    $linkbar_visible = in_array($x__type, $CI->config->item('n___20410'));
    $cache_app = in_array($x__type, $CI->config->item('n___14599'));
    $is_cache = in_array($x__type, $CI->config->item('n___14599'));

    $x__id = ( isset($e['x__id']) ? $e['x__id'] : 0);
    $has_note = ( $x__id > 0 && in_array($e['x__type'], $CI->config->item('n___13550')));


    $is_app = $x__type==6287;

    $href = ( $is_app ? '/-'.$e['e__id'] : '/@'.$e['e__id'] );
    $focus_id = ( substr($CI->uri->segment(1), 0, 1)=='@' ? intval(substr($CI->uri->segment(1), 1)) : 0 );
    $has_x_progress = ( $x__id > 0 && (in_array($e['x__type'], $CI->config->item('n___6255')) || $source_of_e));
    $is_public =  in_array($e['e__status'], $CI->config->item('n___7357')); //PUBLIC
    $has_valid_url = filter_var($e['e__cover'], FILTER_VALIDATE_URL);
    $show_custom_image = !$has_valid_url && $e['e__cover'];
    $source_is_e = $focus_id>0 && $e['e__id']==$focus_id;
    $is_featured = in_array($e['e__status'], $CI->config->item('n___30977'));


    //Is Lock/Private?
    $has_hard_lock = in_array($e['e__status'], $CI->config->item('n___30956')) && !$superpower_12701 && (!$member_e || !$source_is_e);
    $has_soft_lock = !$superpower_12701 && ($has_hard_lock || (!$is_public && !$source_of_e && !$superpower_13422));
    $has_any_lock = $is_cache || (!$superpower_12701 && ($has_soft_lock || $has_hard_lock));
    $has_sortable = !$has_soft_lock && in_array($x__type, $CI->config->item('n___13911')) && $superpower_13422 && $x__id > 0;
    $show_text_editor = $source_of_e && !$has_any_lock && !$is_cache;

    //Source UI
    $ui  = '<div e__id="' . $e['e__id'] . '" '.( isset($e['x__id']) ? ' x__id="'.$e['x__id'].'" ' : '' ).' class="coin_cover no-padding coin___12274_'.$e['e__id'].' '.$extra_class.( $is_app ? ' coin-6287 ' : '' ).( $has_sortable ? ' cover_sort e_sort ' : '' ).( $discovery_mode ? ' coinface-6255 coin-6255 coinface-12274 coin-12274 ' : ' coinface-12274 coin-12274  ' ).( $focus_coin ? ' focus-coin slim_flat col-md-8 col-12 ' : ' edge-coin coin_e_click col-md-4 col-6 ' ).( $show_text_editor ? ' doedit ' : '' ).( isset($e['x__id']) ? ' cover_x_'.$e['x__id'].' ' : '' ).( $has_soft_lock ? ' not-allowed ' : '' ).'">';

    $ui .= '<div class="cover-wrapper">';

    $focus_menu = ( $focus_coin ? 12887 : 14956 );

    //LOCKED
    $dropdown_ui = false;
    if($source_of_e && !$cache_app && !$is_app) {

        $action_buttons = null;

        //Generate Buttons:
        foreach($CI->config->item('e___'.$focus_menu) as $e__id => $m) {

            $superpower_actives = array_intersect($CI->config->item('n___10957'), $m['m__following']);
            if(count($superpower_actives) && !superpower_active(end($superpower_actives), true)){
                //Missing Superpower
                continue;
            }
            $anchor = '<span class="icon-block">'.$m['m__cover'].'</span>'.$m['m__title'];


            if($e__id==4997 && superpower_active(12703, true)){

                $action_buttons .= '<a href="javascript:void(0);" onclick="apply_all_load(4997,'.$e['e__id'].')" class="dropdown-item css__title">'.$anchor.'</a>';

            } elseif($e__id==13571 && $x__id > 0 && $superpower_13422){

                //Edit Message
                $action_buttons .= '<a href="javascript:void(0);" onclick="x_message_load(' . $x__id . ')" class="dropdown-item css__title">'.$anchor.'</a>';

            } elseif($e__id==6287 && in_array($e['e__id'], $CI->config->item('n___6287'))){

                //App Store
                $action_buttons .= '<a href="/-'.$e['e__id'].'" class="dropdown-item css__title">'.$anchor.'</a>';

            } elseif($e__id==30873){

                //Template:
                $action_buttons .= '<a href="javascript:void(0);" onclick="e_copy('.$e['e__id'].', 1)" class="dropdown-item css__title">'.$anchor.'</a>';

            } elseif($e__id==29771){

                //Clone:
                $action_buttons .= '<a href="javascript:void(0);" onclick="e_copy('.$e['e__id'].', 0)" class="dropdown-item css__title">'.$anchor.'</a>';

            } elseif($e__id==10673 && $x__id > 0 && $superpower_13422){

                //UNLINK
                $action_buttons .= '<a href="javascript:void(0);" onclick="e_remove(' . $x__id . ', '.$e['x__type'].')" class="dropdown-item css__title">'.$anchor.'</span></a>';

            } elseif($e__id==13007 && $focus_coin){

                //Reset Alphabetic order
                $action_buttons .= '<a href="javascript:void(0);" onclick="e_sort_reset()" class="dropdown-item css__title">'.$anchor.'</a>';

            } elseif($e__id==6415){

                //Reset my discoveries
                $action_buttons .= '<a href="javascript:void(0);" onclick="e_reset_discoveries('.$e['e__id'].')" class="dropdown-item css__title">'.$anchor.'</a>';

            } elseif($e__id=13670 && substr($CI->uri->segment(1), 0, 1)=='~') {

                //Filter applies only when browsing an idea
                $action_buttons .= '<a href="/'.$CI->uri->segment(1). '?load__e=' . $e['e__id'] . '" class="dropdown-item css__title">'.$anchor.'</a>';

            } elseif(substr($m['m__message'], 0, 1)=='/') {

                //Custom Anchor
                $action_buttons .= '<a href="' . $m['m__message'] . $e['e__id'] . '" class="dropdown-item css__title">'.$anchor.'</a>';

            }
        }

        //Any Buttons?
        if($action_buttons){
            //Show menu:
            $dropdown_ui .= '<div class="dropdown inline-block">';
            $dropdown_ui .= '<button type="button" class="btn no-left-padding no-right-padding css__title" id="action_menu_e_'.$e['e__id'].'" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">'.$e___11035[$focus_menu]['m__cover'].'</button>';
            $dropdown_ui .= '<div class="dropdown-menu" aria-labelledby="action_menu_e_'.$e['e__id'].'">';
            $dropdown_ui .= $action_buttons;
            $dropdown_ui .= '</div>';
            $dropdown_ui .= '</div>';
        }
    }



    //Determine coin type: (Hack removed)
    $cointype = 'coinType12274';
    if ($discovery_mode) { // || substr_count($e['e__cover'], 'fas fa-circle zq6255')
        $cointype = 'coinType12274 coinType6255';
    }
    $cointype = $cointype . ' coinStatus'.$e['e__status'].' ';




    //Top action menu:
    if(!$cache_app && !$is_app){


        $ui .= '<table class="coin_coins"><tr>';


        //Source Link
        $ui .= '<td width="25%"><div class="show-on-hover">';
        if($x__id && $superpower_13422){
            foreach($CI->config->item('e___31770') as $x__type1 => $m1){
                if(in_array($e['x__type'], $CI->config->item('n___'.$x__type1))){
                    foreach($CI->X_model->fetch(array(
                        'x__id' => $x__id,
                    ), array('x__source')) as $linker){
                        $ui .= view_input_dropdown($x__type1, $e['x__type'], null, $source_of_e && $superpower_13422, false, $e['e__id'], $x__id);
                    }
                    break;
                }
            }
        } elseif($focus_coin) {
            //You Are Here
            $ui .= '<span title="'.$e___11035[31825]['m__title'].'" data-toggle="tooltip" data-placement="top">'.$e___11035[31825]['m__cover'].'</span>';
        }
        $ui .= '</div></td>';

        //Source Status
        $special_type = in_array($e['e__status'], $CI->config->item('n___31109'));
        $ui .= '<td width="25%"><div class="'.( $special_type ? '' : 'show-on-hover' ).'">'.( $source_of_e || $special_type ? view_input_dropdown(6177, $e['e__status'], null, $source_of_e && $superpower_13422, false, $e['e__id']) : '' ).'</div></td>';

        //Source Edit
        $ui .= '<td width="25%"><div class="show-on-hover">'.( $source_of_e ? '<a href="javascript:void(0);" onclick="coin__load(12274,'.$e['e__id'].')">'.$e___11035[31912]['m__cover'].'</a>' : '').'</div></td>';


        $ui .= '<td width="25%"><div class="show-on-hover">'.$dropdown_ui.'</div></td>';

        $ui .= '</tr></table>';
    }





    //Coin Cover
    $ui .= ( !$focus_coin ? '<a href="'.$href.'"' : '<div' ).' class="'.$cointype.( !$source_of_e ? ' ready-only ' : '' ).' black-background-obs cover-link" '.( $has_valid_url ? 'style="background-image:url(\''.$e['e__cover'].'\');"' : '' ).'>';

    //ICON?
    $ui .= '<div class="cover-btn">'.($show_custom_image ? view_cover(12274,$e['e__cover'], true) : '' ).'</div>';

    $ui .= ( !$focus_coin ? '</a>' : '</div>' );
    $ui .= '</div>';



    //Title Cover
    $ui .= '<div class="cover-content">';
    $ui .= '<div class="inner-content">';


    //TITLE
    if($show_text_editor && !$is_cache && !$is_app){
        //Editable:
        $ui .= view_input_text(6197, $e['e__title'], $e['e__id'], $source_of_e, ( isset($e['x__spectrum']) ? ($e['x__spectrum']*100)+1 : 0  ), true);
    } else {
        //Static:
        $ui .= '<div class="css__title">'.( $is_cache ? '<a href="'.$href.'" class="css__title">'.$e['e__title'].'</a>' : $e['e__title'] ).'</div>';
    }

    //Message
    $grant_access = $is_featured || $source_of_e || ($x__id>0 && $member_e && ($member_e['e__id']==$e['x__up'] || $member_e['e__id']==$e['x__down']));
    if ($x__id > 0 && $grant_access) {
        if(!$has_any_lock || $grant_access){

            $ui .= '<span class="x__message mini-font hideIfEmpty x__message_' . $x__id . '" onclick="x_message_load(' . $x__id . ')">'.view_x__message($e['x__message'] , $e['x__type']).'</span>';

        } elseif(($is_featured || $has_x_progress) && strlen($e['x__message'])){

            //DISCOVERY PROGRESS
            $ui .= '<span class="mini-font">'.$CI->X_model->message_view($e['x__message'], false).'</span>';

        }
    }


    $ui .= '</div></div>';





    //Bottom Bar
    if(!$is_cache && !$is_app && !$focus_coin){
        $ui .= '<div class="coin_coins"><div class="show-on-hover">';
        foreach($CI->config->item('e___31916') as $menu_id => $m) {
            $superpower_actives = array_intersect($CI->config->item('n___10957'), $m['m__following']);
            $ui .= '<span class="hideIfEmpty '.( count($superpower_actives) ? superpower_active(end($superpower_actives)) : '' ).'">';
            $ui .= view_coins_e($menu_id,  $e['e__id']);
            $ui .= '</span>';
        }
        $ui .= '</div></div>';
    }



    $ui .= '</div>';

    return $ui;

}


function view_input_text($cache_e__id, $current_value, $s__id, $e_of_i, $tabindex = 0, $extra_large = false){

    $CI =& get_instance();
    $e___12112 = $CI->config->item('e___12112');
    $current_value = htmlentities($current_value);
    $name = 'input'.substr(md5($cache_e__id.$current_value.$s__id.$e_of_i.$tabindex), 0, 8);

    //Define element attributes:
    $attributes = ( $e_of_i ? '' : 'disabled' ).' spellcheck="false" tabindex="'.$tabindex.'" old-value="'.$current_value.'" id="input_'.$cache_e__id.'_'.$s__id.'" class="form-control 
     inline-block editing-mode x_set_class_text text__'.$cache_e__id.'_'.$s__id.( $extra_large?' texttype__lg ' : ' texttype__sm ').' text_e_'.$cache_e__id.'" cache_e__id="'.$cache_e__id.'" s__id="'.$s__id.'" ';

    //Also Append Counter to the end?
    if($extra_large){

        $focus_element = '<textarea name="'.$name.'" placeholder="'.$e___12112[$cache_e__id]['m__title'].'" '.$attributes.'>'.$current_value.'</textarea>';

    } else {

        $focus_element = '<input type="text" name="'.$name.'" data-lpignore="true" placeholder="__" value="'.$current_value.'" '.$attributes.' />';

    }

    return '<span class="span__'.$cache_e__id.' '.( !$e_of_i ? ' edit-locked ' : '' ).'">'.$focus_element.'</span>';

}




function view_input_dropdown($cache_e__id, $selected_e__id, $btn_class = null, $e_of_i = true, $show_full_name = true, $o__id = 0, $x__id = 0){

    $CI =& get_instance();
    $e___this = $CI->config->item('e___'.$cache_e__id);

    if(!$selected_e__id || !isset($e___this[$selected_e__id])){
        return false;
    }

    $e___12079 = $CI->config->item('e___12079');
    $e_of_i = ( isset($e___12079[$cache_e__id]) ? $e_of_i : false );

    $ui = '<div class="dropdown inline-block dropd_'.$cache_e__id.'_'.$o__id.'_'.$x__id.'" selected-val="'.$selected_e__id.'">';

    $ui .= '<button type="button" '.( $e_of_i ? 'class="btn no-left-padding '.( $show_full_name ? 'dropdown-toggle' : 'no-right-padding dropdown-lock' ).' btn-'.$btn_class.'" id="dropdownMenuButton'.$cache_e__id.'" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"' : 'class="btn adj-btn '.( !$show_full_name ? 'no-padding' : '' ).' edit-locked '.$btn_class.'" '.( !$show_full_name ? ' title="'.$e___this[$selected_e__id]['m__title'].'" data-toggle="tooltip" data-placement="top" ' : '' ) ).' >';

    $ui .= '<span>' .$e___this[$selected_e__id]['m__cover'].'</span>'.( $show_full_name ?  $e___this[$selected_e__id]['m__title'] : '' );

    $ui .= '</button>';

    if($e_of_i){
        $ui .= '<div class="dropdown-menu btn-'.$btn_class.'" aria-labelledby="dropdownMenuButton'.$cache_e__id.'">';

        foreach($e___this as $e__id => $m) {

            $superpower_actives = array_intersect($CI->config->item('n___10957'), $m['m__following']);

            //What type of URL?
            if(substr($m['m__message'], 0, 1)=='/'){

                if(substr_count($m['m__message'], '=$_GET')){
                    //Update URL:
                    $parts = str_replace('&','',$m['m__message']);
                    $parts = one_two_explode('?','',$parts);
                    foreach(explode('=$_GET',$parts) as $part){
                        $m['m__message'] = str_replace($part.'=$_GET',$part.'='.(isset($_GET[$part]) && strlen($_GET[$part])>0 ? $_GET[$part] : ''),$m['m__message']);
                    }
                }

                //Basic transaction:
                $anchor_url = ( $e__id==$selected_e__id ? 'href="javascript:void();"' : 'href="'.$m['m__message'].'" ' );

            } else{

                //Idea Dropdown updater:
                $anchor_url = 'href="javascript:void();" new-en-id="'.$e__id.'" onclick="update_dropdown('.$cache_e__id.', '.$e__id.', '.$o__id.', '.$x__id.', '.intval($show_full_name).')"';

            }

            $ui .= '<a class="dropdown-item dropi_'.$cache_e__id.'_'.$o__id.'_'.$x__id.' css__title optiond_'.$e__id.'_'.$o__id.'_'.$x__id.' '.( $e__id==$selected_e__id ? ' active ' : ( count($superpower_actives) ? superpower_active(end($superpower_actives)) : '' ) ).'" '.$anchor_url.' title="'.$m['m__message'].'"><span class="icon-block">'.$m['m__cover'].'</span>'.$m['m__title'].'</a>';

        }

        $ui .= '</div>';
    }


    $ui .= '</div>';

    return $ui;
}

function view_json($array)
{
    if(!headers_sent()) {
        header('Content-Type: application/json');
    }
    echo json_encode($array);
    return true;
}


function view_ordinal($number)
{
    $ends = array('th', 'st', 'nd', 'rd', 'th', 'th', 'th', 'th', 'th', 'th');
    if (($number % 100) >= 11 && ($number % 100) <= 13) {
        return $number . 'th';
    } else {
        return $number . $ends[$number % 10];
    }
}

function view__s($count, $has_e = 0)
{
    //A cute little function to either display the plural "s" or not based on $count
    return ( intval($count) == 1 ? '' : ($has_e ? 'es' : 's'));
}

