<?php

$community_pills = '';
$is_open = true;

foreach($this->E_model->scissor_e(website_setting(0), 13207) as $e_item) {

    //Community Members?
    foreach($this->X_model->fetch(array(
        'x__up' => $e_item['e__id'],
        'x__type IN (' . join(',', $this->config->item('n___4592')) . ')' => null, //SOURCE LINKS
        'x__privacy IN (' . join(',', $this->config->item('n___7359')) . ')' => null, //PUBLIC
    ), array('x__down'), 0, 0, array('x__weight' => 'ASC', 'x__id' => 'DESC')) as $x) {

        $total_count = view_coins_e(12274, $x['e__id'], 0, false);

        if($total_count){

            $ui = '<div class="row justify-content">';
            foreach(view_coins_e(12274, $x['e__id'], 1, false) as $count=>$e) {
                $ui .= view_card_e(13207, $e, null);
            }
            $ui .= '</div>';

            $community_pills .= view_pill(12274, $x['e__id'], $total_count, array(
                'm__cover' => view_cover(12274,$x['e__cover'], true),
                'm__title' => $x['e__title'],
                'm__message' => $x['x__message'],
            ), $ui, $is_open);

            $is_open = false;
        }
    }
}


if(strlen($community_pills)){

    //Community
    echo '<ul class="nav nav-tabs nav12274"></ul>';
    echo $community_pills;

} else {

    echo 'Community settings not yet setup for your website';

}

