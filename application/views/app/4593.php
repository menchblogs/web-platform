<?php

//List all interactions types and their counts:
$e___11035 = $this->config->item('e___11035'); //NAVIGATION
$table_sortable = array('#th_primary','#th_count','#th_total','#th_points');
$total_count = 0;
$total_access = array();
$total_interactions = 0;
$total_points = 0;
$table_body = '';

foreach($this->config->item('e___4593') as $x__type => $m) {

    $total_count++;

    $table_body .= '<tr class="panel-title down-border" style="font-weight:bold !important;">';
    $table_body .= '<td style="text-align: left;">'.str_pad($total_count, 3, '0', STR_PAD_LEFT).'</td>';
    $table_body .= '<td style="text-align: left;">'.$m['m__cover'].'</td>';
    $table_body .= '<td style="text-align: left;"><a href="/@'.$x__type.'"><u>'.$m['m__title'].'</u></a></td>';

    //List all statuses:
    $interactions_this = 0;
    foreach($this->config->item('e___6186') as $x__type1 => $m1) {
        $list_e_count = $this->X_model->fetch(array(
            'x__type' => $x__type,
            'x__privacy' => $x__type1,
        ), array('x__down'), 0, 0, array(), 'COUNT(x__id) as totals');

        $table_body .= '<td style="text-align: left;">'.str_pad($list_e_count[0]['totals'], 9, '0', STR_PAD_LEFT).'</td>';
        if(!isset($total_access[$m1['m__handle']])){
            $total_access[$m1['m__handle']] = 0;
        }
        $total_access[$m1['m__handle']] += $list_e_count[0]['totals'];
        $interactions_this += $list_e_count[0]['totals'];
    }

    $total_interactions += $interactions_this;
    $table_body .= '<td style="text-align: left;">'.str_pad($interactions_this, 9, '0', STR_PAD_LEFT).'</td>';

    //Points Total
    $points = $this->X_model->fetch(array(
        'x__type' => $x__type,
    ), array(), 0, 0, array(), 'SUM(x__points) as totals');
    $table_body .= '<td style="text-align: left;">'.str_pad($points[0]['totals'], 9, '0', STR_PAD_LEFT).'</td>';
    $total_points += $points[0]['totals'];

    $table_body .= '</tr>';

}


echo '<table class="table table-sm table-striped stats-table mini-stats-table" id="sortable_table">';
echo '<tr class="panel-title down-border" style="font-weight:bold !important;">';

echo '<th style="text-align: left;" id="th_count">#</th>';
echo '<th style="text-align: left;" id="th_count">&nbsp;</th>'; //Icon
echo '<th style="text-align: left;" id="th_primary">Interaction Type</th>';

//List all statuses:
foreach($this->config->item('e___6186') as $x__type1 => $m1) {
    array_push($table_sortable, '#th_e_'.$x__type1);
    echo '<th style="text-align: left;" id="th_e_'.$x__type1.'">'.$m1['m__cover'].' '.$m1['m__title'].'</th>';
}

echo '<th style="text-align: left;" id="th_total">Total Interactions</th>';

//Points Total
echo '<th style="text-align: left;" id="th_points">'.$e___11035[42225]['m__cover'].' '.$e___11035[42225]['m__title'].'</th>';

echo '</tr>';

echo '<tr class="panel-title down-border" style="font-weight:bold !important;">';

echo '<th style="text-align: left;">&nbsp;</th>';
echo '<th style="text-align: left;"><b>TOTALS</b></th>';

//List all statuses:
foreach($this->config->item('e___6186') as $x__type1 => $m1) {
    echo '<th style="text-align: left;">'.str_pad($total_access[$m1['m__handle']], 9, '0', STR_PAD_LEFT).'</th>';
}

echo '<th style="text-align: left;">'.str_pad($total_interactions, 9, '0', STR_PAD_LEFT).'</th>';

//Points Total
echo '<th style="text-align: left;">'.str_pad($total_points, 9, '0', STR_PAD_LEFT).'</th>';

echo '</tr>';

echo $table_body;

echo '</table>';

?>

<style>
    .container{ margin-left: 8px; max-width: calc(100% - 16px) !important; }
</style>

<script>

    $(document).ready(function () {
        var table = $('#sortable_table');
        $('<?= join(', ', $table_sortable) ?>')
            .each(function(){

                var th = $(this),
                    thIndex = th.index(),
                    inverse = false;

                th.click(function(){

                    table.find('td').filter(function(){

                        return $(this).index() === thIndex;

                    }).sortElements(function(a, b){

                        return $.text([a]) < $.text([b]) ?
                            inverse ? -1 : 1
                            : inverse ? 1 : -1;

                    }, function(){

                        return this.parentNode;

                    });

                    inverse = !inverse;

                });

            });
    });
</script>
