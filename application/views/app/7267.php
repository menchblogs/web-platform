<?php

$icon_keyword = null;

if(isset($_GET['search_for'])){

    $icon_keyword = $_GET['search_for'];

} elseif(isset($_GET['e__id'])){

    $es = $this->E_model->fetch(array(
        'e__id' => $_GET['e__id'],
    ));
    if(!count($es)){
        return view_json(array(
            'status' => 0,
            'message' => 'Invalid Source ID #1'
        ));
    } elseif(!strlen($es[0]['e__cover'])) {
        return view_json(array(
            'status' => 0,
            'message' => 'Source Missing Icon'
        ));
    }

    if(string_is_icon($es[0]['e__cover'])){

        //Exclude Icon settings:
        $icon_keyword = 'fa-'.one_two_explode('fa-',' ',$es[0]['e__cover']);

    } else {

        $icon_keyword = $es[0]['e__cover'];

    }
}


//UI to compose a test message:
echo '<form method="GET" action="">';

echo '<div class="mini-header">Search String:</div>';
echo '<input type="text" class="form-control border maxout" name="search_for" value="'.$icon_keyword.'"><br />';
echo '<input type="submit" class="btn btn-12273" value="Search">';


if($icon_keyword){

    $matching_results = $this->E_model->fetch(array(
        'e__type IN (' . join(',', $this->config->item('n___7358')) . ')' => null, //ACTIVE
        'LOWER(e__cover) LIKE \'%'.strtolower($icon_keyword).'%\'' => null,
    ));

    //List the matching search:
    echo '<table class="table table-sm table-striped stats-table mini-stats-table">';


    echo '<tr class="panel-title down-border">';
    echo '<td style="text-align: left;" colspan="2">'.count($matching_results).' Results found</td>';
    echo '</tr>';


    echo '<tr class="panel-title down-border" style="font-weight:bold !important;">';
    echo '<td style="text-align: left;">#</td>';
    echo '<td style="text-align: left;">Matching Search</td>';
    echo '</tr>';

    if(count($matching_results) > 0){

        $replaced = 0;
        foreach($matching_results as $count=>$en){

            if(isset($_GET['do_replace']) && isset($_GET['replace_with'])){
                $replaced += $this->E_model->update($en['e__id'], array(
                    'e__cover' => str_ireplace($icon_keyword, $_GET['replace_with'], $en['e__cover']),
                ), false, $member_e['e__id']);

            }

            echo '<tr class="panel-title down-border">';
            echo '<td style="text-align: left;">'.($count+1).'</td>';
            echo '<td style="text-align: left;">'.view_cache(6177 /* Source Status */, $en['e__type'], true, 'right').' <span class="icon-block">'.view_cover(12274,$en['e__cover']).'</span><a href="/@'.$en['e__id'].'">'.$en['e__title'].'</a></td>';
            echo '</tr>';

        }

        if($replaced > 0){
            echo '<span class="icon-block"><i class="fas fa-check-circle"></i></span>Updated icons for '.$replaced.' sources.';
        }

    }

    echo '</table>';


    echo '<div class="mini-header">Replace With:</div>';
    echo '<input type="text" class="form-control border maxout" name="replace_with" value="'.@$_GET['replace_with'].'"><br />';
    echo '<input type="submit" name="do_replace" class="btn btn-12273" value="Replace">';
}


echo '</form>';