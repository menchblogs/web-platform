<?php

//Idea List Duplicates


//Do a query to detect Ideas with the exact same title:
$q = $this->db->query('select in1.* from table__i in1 where (select count(*) from table__i in2 where in2.i__message = in1.i__message AND in2.i__access IN (' . join(',', $this->config->item('n___31871')) . ')) > 1 AND in1.i__access IN (' . join(',', $this->config->item('n___31871')) . ') ORDER BY in1.i__message ASC');
$duplicates = $q->result_array();

if(count($duplicates) > 0){

    $prev_title = null;
    $e___4737 = $this->config->item('e___4737'); //Idea Status

    foreach($duplicates as $in) {
        if ($prev_title != $in['i__message']) {
            echo '<hr />';
            $prev_title = $in['i__message'];
        }

        echo '<div><span data-toggle="tooltip" data-placement="right" title="'.$e___4737[$in['i__type']]['m__title'].': '.$e___4737[$in['i__type']]['m__message'].'">' . $e___4737[$in['i__type']]['m__cover'] . '</span> <a href="/' . $in['i__hashtag'] . '"><b>' . $in['i__message'] . '</b></a> #' . $in['i__id'] . '</div>';
    }

} else {

    echo '<span class="icon-block"><i class="fas fa-check-circle"></i></span>No duplicates found!';

}