<?php

$commission_rate = doubleval(view_memory(6404,27017))/100;

$e___6287 = $this->config->item('e___6287'); //APP
$gross_transactions = 0;
$gross_tickets = 0;
$gross_revenue = 0;
$gross_paypal_fee = 0;
$gross_commission = 0;
$gross_payout = 0;
$gross_currencies = array();
$i_query = array();
$daily_sales = array();
$origin_sales = array();

//Generate list of payments:
$payment_es = $this->X_model->fetch(array(
    'x__up' => 27004,
    'x__status IN (' . join(',', $this->config->item('n___7359')) . ')' => null, //PUBLIC
    'x__type IN (' . join(',', $this->config->item('n___4592')) . ')' => null, //SOURCE LINKS
    'e__type IN (' . join(',', $this->config->item('n___7357')) . ')' => null, //ACTIVE
), array('x__down'), 0, 0, array('x__spectrum' => 'ASC', 'e__title' => 'ASC'));


if (isset($_GET['e__id'])) {

    //Show header:
    echo '<div style="padding: 0 0 0 10px; font-weight: bold; margin-bottom: -13px;"><a href="/-27004"><b>'.$e___6287[27004]['m__title'].'</b></a></div>';

    foreach($payment_es as $e){
        if($e['e__id']==$_GET['e__id']){

            echo '<h2>'.$e['e__title'].'</h2>';

            $i_query = $this->X_model->fetch(array(
                'x__status IN (' . join(',', $this->config->item('n___7359')) . ')' => null, //PUBLIC
                'x__type IN (' . join(',', $this->config->item('n___13550')) . ')' => null, //SOURCE IDEAS
                'i__type IN (' . join(',', $this->config->item('n___27005')) . ')' => null, //Payment Idea
                'x__up' => $e['e__id'],
            ), array('x__right'), 0, 0, array('x__spectrum' => 'ASC', 'i__title' => 'ASC'));
            break;
        }
    }

} else {

    //Fetch all assigned ideas:
    $assigned_i_ids = array();
    echo '<h1>'.$e___6287[27004]['m__title'].'</h1>';
    echo '<div class="list-group" style="max-width: 600px; margin: 0 auto; display: block;">';
    foreach($payment_es as $e){

        echo '<a href="/-27004?e__id='.$e['e__id'].'" class="list-group-item list-group-item-action" style="border:1px solid #999;">'.$e['e__title'].' &nbsp;<i class="far fa-chevron-right"></i></a>';

        foreach($this->X_model->fetch(array(
            'x__status IN (' . join(',', $this->config->item('n___7359')) . ')' => null, //PUBLIC
            'x__type IN (' . join(',', $this->config->item('n___13550')) . ')' => null, //SOURCE IDEAS
            'i__type IN (' . join(',', $this->config->item('n___27005')) . ')' => null, //Expanded Payment Idea
            'x__up' => $e['e__id'],
        ), array('x__right'), 0, 0, array('x__spectrum' => 'ASC', 'i__title' => 'ASC')) as $i_assigned){
            array_push($assigned_i_ids, $i_assigned['x__right']);
        }
    }
    echo '</div>';

    //Show all non-assigned payment ideas:
    $i_query = $this->I_model->fetch(array(
        'i__id NOT IN (' . join(',', $assigned_i_ids) . ')' => null,
        'i__type IN (' . join(',', $this->config->item('n___30469')) . ')' => null, //Strict Payment Idea
    ), 0, 0, array('i__title' => 'ASC'));

}


//List all payment Ideas and their total earnings
$x_updated = 0;
$ticket_type_content = '';
foreach($i_query as $i){

    //Total earnings:
    $transaction_content = '';
    $total_transactions = 0;
    $total_tickets = 0;
    $total_revenue = 0;
    $total_paypal_fee = 0;
    $currencies = array();

    foreach($this->X_model->fetch(array(
        'x__status IN (' . join(',', $this->config->item('n___7359')) . ')' => null, //PUBLIC
        'x__type IN (' . join(',', $this->config->item('n___6255')) . ')' => null, //DISCOVERY COIN
        'x__left' => $i['i__id'],
    ), array(), 0) as $x){

        if(isset($_GET['include_e']) && strlen($_GET['include_e']) && !count($this->X_model->fetch(array(
                'x__type IN (' . join(',', $this->config->item('n___4592')) . ')' => null, //SOURCE LINKS
                'x__status IN (' . join(',', $this->config->item('n___7359')) . ')' => null, //PUBLIC
                'x__up IN (' . $_GET['include_e'] . ')' => null,
                'x__down' => $x['x__source'],
            )))){
            continue;
        }
        if(isset($_GET['exclude_e']) && intval($_GET['exclude_e']) && count($this->X_model->fetch(array(
                'x__up IN (' . $_GET['exclude_e'] . ')' => null, //All of these
                'x__down' => $x['x__source'],
                'x__type IN (' . join(',', $this->config->item('n___4592')) . ')' => null, //SOURCE LINKS
                'x__status IN (' . join(',', $this->config->item('n___7359')) . ')' => null, //PUBLIC
            )))){
            continue;
        }

        $x__metadata = unserialize($x['x__metadata']);
        $total_transactions++;

        $this_tickets = 1;//Default assumption:
        for($t=2;$t<=20;$t++){
            if(substr_count(strtolower($i['i__title']),$t.'x')==1){
                $this_tickets = $t;
                break;
            }
        }

        $total_tickets += $this_tickets;
        $total_paypal_fee += doubleval($x__metadata['mc_fee']);
        $total_revenue += doubleval($x__metadata['mc_gross']);
        if(!in_array($x__metadata['mc_currency'], $currencies) && strlen($x__metadata['mc_currency'])>0){
            array_push($currencies, $x__metadata['mc_currency']);
        }
        if(!in_array($x__metadata['mc_currency'], $gross_currencies) && strlen($x__metadata['mc_currency'])>0){
            array_push($gross_currencies, $x__metadata['mc_currency']);
        }


        //Half only if not halfed before
        if(isset($_GET['half']) && !isset($x__metadata['mc_gross_old'])){
            $this->X_model->update($x['x__id'], array(
                'x__message' => number_format(($x__metadata['mc_gross']/2),2),
            ));
            update_metadata(6255, $x['x__id'], array(
                'mc_fee' => number_format(($x__metadata['mc_fee']/2),2),
                'mc_gross' => number_format(($x__metadata['mc_gross']/2),2),
                'mc_gross_old' => $x__metadata['mc_gross'],
                'mc_fee_old' => $x__metadata['mc_fee'],
            ));
            $x_updated++;
        }



        $item_parts = explode('-',$x__metadata['item_number']);

        $es = $this->E_model->fetch(array(
            'e__id' => (isset($item_parts[3]) ? $item_parts[3] : $x['x__source'] ),
        ));
        $this_commission = $x__metadata['mc_gross']*$commission_rate;
        $this_payout = $x__metadata['mc_gross']-$x__metadata['mc_fee']-$this_commission;

        $transaction_content .= '<tr class="transaction_columns transactions_'.$i['i__id'].' hidden" title="Transaction #'.$x['x__id'].'">';
        $transaction_content .= '<td>'.( count($es) ? '<span class="icon-block source_cover_micro">'.view_cover(12274,$es[0]['e__cover'],true).'</span><a href="/@'.$es[0]['e__id'].'" style="font-weight:bold; display: inline-block;"><u>'.$es[0]['e__title'].'</u></a> ' : '' ).$x__metadata['first_name'].' '.$x__metadata['last_name'].'<a href="/-4341?x__id='.$x['x__id'].'" class="advance_columns hidden" style="margin-left:8px;" target="_blank">'.$x['x__id'].'</a></td>';
        $transaction_content .= '<td style="text-align: right;" class="advance_columns hidden">1</td>';
        $transaction_content .= '<td style="text-align: right;" class="advance_columns hidden">&nbsp;</td>';
        $transaction_content .= '<td style="text-align: right;">'.$this_tickets.'x</td>';
        $transaction_content .= '<td class="advance_columns hidden" style="text-align: right;">$'.number_format($x__metadata['mc_gross'], 2).'</td>';
        $transaction_content .= '<td class="advance_columns hidden" style="text-align: right;" title="'.($commission_rate*100).'%">-$'.number_format($this_commission, 2).'</td>';
        $transaction_content .= '<td class="advance_columns hidden" style="text-align: right;" title="'.( $x__metadata['mc_gross'] > 0 ? ($x__metadata['mc_fee']/$x__metadata['mc_gross']*100) : 0 ).'%">-$'.number_format($x__metadata['mc_fee'], 2).'</td>';
        $transaction_content .= '<td style="text-align: right;">$'.number_format($this_payout, 2).'</td>';
        $transaction_content .= '<td style="text-align: right;" class="advance_columns hidden">'.$x__metadata['mc_currency'].'</td>';
        $transaction_content .= '<td style="text-align: right;"><b>$'.number_format(($this_payout/$this_tickets), 2).'</b></td>';
        $transaction_content .= '<td style="text-align: right;" id="refund_'.$x['x__id'].'"><a href="#" onclick="paypal_refund('.$x['x__id'].', '.number_format($x__metadata['mc_gross'], 2).')" style="font-weight:bold;"><u>Refund</u></a> <a href="https://www.paypal.com/activity/payment/'.$x__metadata['txn_id'].'" target="_blank"><i class="fas fa-info-circle"></i></a> <a href="/-4341?x__id='.$x['x__id'].'" target="_blank"><i class="fas fa-atlas"></i></a></td>';

        $transaction_content .= '</tr>';

        if($x__metadata['mc_gross'] > 0){
            $date = date("md", strtotime($x['x__time']));
            if(isset($daily_sales[$date])){
                $daily_sales[$date] += $x__metadata['mc_gross'];
            } else {
                $daily_sales[$date] = $x__metadata['mc_gross'];
            }

            $origin_source = $x['x__right'];
            if(isset($origin_sales[$origin_source])){
                $origin_sales[$origin_source] += number_format($x__metadata['mc_gross'], 0, '','');
            } else {
                $origin_sales[$origin_source] = number_format($x__metadata['mc_gross'], 0, '','');
            }

        }

    }
    $total_commission = ( $commission_rate * $total_revenue );
    $payout = $total_revenue-$total_commission-$total_paypal_fee;


    if($i['i__type']==6183 && !$total_transactions){
        continue;
    }



    $gross_tickets += $total_tickets;
    $gross_transactions += $total_transactions;
    $gross_revenue += $total_revenue;
    $gross_paypal_fee += $total_paypal_fee;
    $gross_commission += $total_commission;
    $gross_payout += $payout;

    $has_limits = $this->X_model->fetch(array(
        'x__status IN (' . join(',', $this->config->item('n___7359')) . ')' => null, //PUBLIC
        'x__type' => 4983, //References
        'x__right' => $i['i__id'],
        'x__up' => 26189,
    ), array(), 1);
    $available_transactions = (count($has_limits) && is_numeric($has_limits[0]['x__message']) ? intval($has_limits[0]['x__message']) : '∞');

    if(fmod($total_transactions, 2)==1){
        $transaction_content .= '<tr class="transaction_columns hidden"></tr>';
    }

    $ticket_type_content .= '<tr class="css__title">';
    $ticket_type_content .= '<td>'.( $total_tickets>0 ? '<a href="javascript:void(0)" onclick="$(\'.transactions_'.$i['i__id'].'\').toggleClass(\'hidden\');" style="font-weight:bold;"><u>'.$i['i__title'].'</u></a>' : $i['i__title'] ).'</td>';
    $ticket_type_content .= '<td style="text-align: right;" class="advance_columns hidden">'.$total_transactions.'</td>';
    $ticket_type_content .= '<td style="text-align: right;" class="advance_columns hidden">'.$available_transactions.'</td>';
    $ticket_type_content .= '<td style="text-align: right;">'.( $total_tickets>0 ? $total_tickets.'x' : '&nbsp;' ).'</td>';
    $ticket_type_content .= '<td class="advance_columns hidden" style="text-align: right;">'.( $total_tickets>0 ? '$'.number_format($total_revenue, 2) : '&nbsp;' ).'</td>';
    $ticket_type_content .= '<td class="advance_columns hidden" style="text-align: right;">'.( $total_tickets>0 ? '-$'.number_format($total_commission, 2) : '&nbsp;' ).'</td>';
    $ticket_type_content .= '<td class="advance_columns hidden" style="text-align: right;">'.( $total_tickets>0 ? '-$'.number_format($total_paypal_fee, 2) : '&nbsp;').'</td>';
    $ticket_type_content .= '<td style="text-align: right;"><b>'.( $total_tickets>0 ? '$'.number_format($payout, 2) : '' ).'</b></td>';
    $ticket_type_content .= '<td style="text-align: right;" class="advance_columns hidden">'.join(', ',$currencies).'</td>';
    $ticket_type_content .= '<td style="text-align: right;">'.( $total_tickets>0 ? '$'.number_format(($payout/$total_tickets), 2) : '&nbsp;' ).'</td>';
    $ticket_type_content .= '<td style="text-align: right;"><a href="/~'.$i['i__id'].'"><i class="fal fa-cog" style="font-size: 0.8em;"></i></a></td>';
    $ticket_type_content .= '</tr>';
    $ticket_type_content .= $transaction_content;

}


if(count($i_query)){

    echo '<table id="sortable_table" class="table table-sm table-striped image-mini" style="margin: 0 5px; width:calc(100% - 10px) !important;">';
    echo '<tr style="vertical-align: baseline;" class="css__title">';
    echo '<th id="th_primary">&nbsp;&nbsp;&nbsp;<a href="javascript:void(0)" onclick="$(\'.transaction_columns\').toggleClass(\'hidden\');" style="font-weight:bold;" data-toggle="tooltip" data-placement="top" title="Toggle Transactions"><i class="fas fa-arrows-v"></i></a>&nbsp;&nbsp;<a href="javascript:void(0)" onclick="$(\'.advance_columns\').toggleClass(\'hidden\');" style="font-weight:bold;" data-toggle="tooltip" data-placement="top" title="Toggle Advanced Columns"><i class="fas fa-arrows-h"></i></a></th>';
    echo '<th style="text-align: right;" id="th_paid" class="advance_columns hidden">Transactions</th>';
    echo '<th style="text-align: right;" id="th_paid" class="advance_columns hidden">Limit</th>';
    echo '<th style="text-align: right;" id="th_paid">Tickets<br />Sold</th>';
    echo '<th style="text-align: right;" class="advance_columns hidden" id="th_rev">Sales</th>';
    echo '<th style="text-align: right;" class="advance_columns hidden" id="th_payout">Platform<br />Fee</th>';
    echo '<th style="text-align: right;" class="advance_columns hidden" id="th_payout">Paypal<br />Fee</th>';
    echo '<th style="text-align: right;" id="th_payout">NET<br />Payout</th>';
    echo '<th style="text-align: right;" id="th_currency" class="advance_columns hidden">&nbsp;</th>';
    echo '<th style="text-align: right;" id="th_average">Average<br />Ticket</th>';
    echo '<th style="text-align: right;">&nbsp;</th>';
    echo '</tr>';

    echo $ticket_type_content;

    echo '<tr class="css__title" style="font-size:1.1em;">';
    echo '<th style="text-align: left; font-weight: bold;" id="th_primary">Totals</th>';
    echo '<th style="text-align: right; font-weight: bold;" class="advance_columns hidden">'.$gross_transactions.'</th>';
    echo '<th style="text-align: right; font-weight: bold;" class="advance_columns hidden">&nbsp;</th>';
    echo '<th style="text-align: right; font-weight: bold;">'.$gross_tickets.'x</th>';
    echo '<th style="text-align: right; font-weight: bold;" class="advance_columns hidden">'.'$'.number_format($gross_revenue, 2).'</th>';
    echo '<th style="text-align: right; font-weight: bold;" class="advance_columns hidden">-$'.number_format($gross_commission, 2).'</th>';
    echo '<th style="text-align: right; font-weight: bold;" class="advance_columns hidden">-$'.number_format($gross_paypal_fee, 2).'</th>';
    echo '<th style="text-align: right; font-weight: bold;"><b>$'.number_format($gross_payout, 2).'</b></th>';
    echo '<th style="text-align: right; font-weight: bold;" class="advance_columns hidden">'.join(', ',$gross_currencies).'</th>';
    echo '<th style="text-align: right; font-weight: bold;">$'.number_format(( $gross_tickets > 0 ? $gross_payout / $gross_tickets : 0 ), 2).'</th>';
    echo '<th style="text-align: right; font-weight: bold;">&nbsp;</th>';
    echo '</tr>';
    echo '</table>';
    echo ( $x_updated > 0 ? '<div>'.$x_updated.' Halfed!<hr /></div>' : '' );




    //Show Charts:
    echo '<div id="chart_div" style="margin:0 0 21px;"></div>';
    echo '<div id="chart_origin_div" style="margin:0 0 21px;"></div>';
    ?>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">

        // Load the Visualization API and the corechart package.
        google.charts.load('current', {'packages':['corechart']});


        google.charts.setOnLoadCallback(drawChart2);
        function drawChart2() {
            var chart = new google.visualization.ColumnChart(document.getElementById('chart_div'));
            var options = {
                title: 'Sales by Day',
                hAxis: {showTextEvery:1, slantedText:true, slantedTextAngle:45}
            }
            var data = google.visualization.arrayToDataTable([
                ['Day', 'Sales'],
                <?php
                ksort($daily_sales);
                foreach($daily_sales as $day => $sales){
                    if($sales > 0){
                        echo "['".$day."', ".number_format($sales, 0, '.', '')."],";
                    }
                }
                ?>
            ]);
            chart.draw(data, options);
        }


        google.charts.setOnLoadCallback(drawChart3);
        function drawChart3() {
            var chart = new google.visualization.PieChart(document.getElementById('chart_origin_div'));
            var options = {
                title: 'Sales by Promoter',
                hAxis: {showTextEvery:1, slantedText:true, slantedTextAngle:45}
            }
            var data = google.visualization.arrayToDataTable([
                ['Origin', 'Sales'],
                <?php
                arsort($origin_sales);
                foreach($origin_sales as $origin => $sales){
                    if(($sales/$gross_revenue)>=0.5 || count($this->X_model->fetch(array(
                            'x__status IN (' . join(',', $this->config->item('n___7359')) . ')' => null, //PUBLIC
                            'x__type IN (' . join(',', $this->config->item('n___13550')) . ')' => null, //SOURCE IDEAS
                            'x__right' => $origin,
                            'x__up' => 30564, //None Promoter
                        )))){
                        //This item has more than 50% of sales, remove it:
                        continue;
                    }
                    if($sales > 0){
                        //Fetch this origin:
                        $is = $this->I_model->fetch(array(
                            'i__id' => $origin,
                        ));
                        echo "['".( count($is) ? '$'.number_format($sales, 0).' '.str_replace('\'','`',$is[0]['i__title']) : 'Unknown' )."', ".number_format($sales, 0, '.', '')."],";
                    }
                }
                ?>
            ]);
            chart.draw(data, options);
        }
    </script>
    <?php



}

?>


<style>
    /* CSS Adjustments for Printing View */
    .fixed-top{
        background-color: transparent !important;
    }
    .top_nav{
        display:none !important;
    }
    .table-striped tr:nth-of-type(odd) td {
        background-color: #FFFFFF !important;
        -webkit-print-color-adjust:exact;
    }
    .table-striped td {
        border-bottom: 1px dotted #FFFFFF !important;
    }
    .fa-filter, .fa-sort{
        font-size: 1.01em !important;
        margin-bottom: 3px;
    }
    th{
        cursor: ns-resize !important;
        border: 0 !important;
    }
    tr th{
       padding: 8px 0 !important;
        font-weight: bold;
        font-size: 1.12em;
    }
    tr td {
        padding: 5px 0 !important;
        font-size: 1.01em;
    }
    .transaction_columns td{
        padding: 1px 0 !important;
        font-size: 0.9em;
    }

    .vertical_col {
        writing-mode: tb-rl;
        white-space: nowrap;
        display: block;
        padding-bottom: 8px;
    }
    .col_stat{
        height:55px;
        display:inline-block;
        text-align: left;
        width: 8px;
    }
</style>
<script>

    function paypal_refund(x__id, transaction_total){
        var confirm_refund = confirm("Process a full refund of "+transaction_total+"?");
        if(confirm_refund){
            $.post("/x/paypal_refund", {
                x__id: x__id,
                refund_total: transaction_total
            }, function (data) {
                if(data.status){
                    $('#refund_'+x__id).html('✅ Refunded');
                } else {
                    alert(data.message);
                }
            });
        }
    }

</script>