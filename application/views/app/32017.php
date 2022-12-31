<?php

if(!isset($_GET['e__id']) || !intval($_GET['e__id'])) {

    //List this members discoveries so they can choose:
    echo '<div>Enter e__id to begin...</div><br />';

} else {


    //Fetch All Tickets of Source:
    $all_ticket_count = 0;
    $all_ticket_transactions = 0;
    $paid_ticket_types = 0;
    $ticket_type_ids = array();
    foreach($this->X_model->fetch(array(
        'x__status IN (' . join(',', $this->config->item('n___7359')) . ')' => null, //PUBLIC
        'x__type IN (' . join(',', $this->config->item('n___13550')) . ')' => null, //SOURCE IDEAS
        'x__up' => $_GET['e__id'], //Time Starts
    ), array('x__right')) as $ticket_type){

        //Count Tickets:
        $ticket_count = 0;
        $ticket_transactions = 0;
        $ticket_holder_ui = '';
        foreach($this->X_model->fetch(array(
            'x__status IN (' . join(',', $this->config->item('n___7359')) . ')' => null, //PUBLIC
            'x__type IN (' . join(',', $this->config->item('n___32014')) . ')' => null, //Ticket Discoveries
            'x__left' => $ticket_type['i__id'],
        ), array('x__source'), 0) as $x){
            $x__metadata = unserialize($x['x__metadata']);
            $this_count = ( (isset($x__metadata['quantity']) && $x__metadata['quantity'] >= 2) ? $x__metadata['quantity'] : 1 );
            $ticket_count += $this_count;
            $ticket_transactions++;
            $ticket_holder_ui .= '<div>'.$ticket_transactions.') <a href="/@'.$x['e__id'].'"><u>'.$x['e__title'].'</u></a> '.$this_count.' Ticket'.view__s($this_count).'</div>';
        }

        $all_ticket_count += $ticket_count;
        $all_ticket_transactions += $ticket_transactions;

        if($ticket_transactions>0){
            array_push($ticket_type_ids, $ticket_type['i__id']);
            echo '<h3>'.$ticket_type['i__title'].' ['.$ticket_count.' Tickets, '.$ticket_transactions.' Trs]</h3>';
            echo $ticket_holder_ui;
            echo '<br /><br /><br />';
        }

    }

    echo '<hr />';

    echo $all_ticket_count.' Tickets sold in '.$all_ticket_transactions.' Transactions';


    /*
     *
     * $this->X_model->send_dm($watcher['x__up'], $es_discoverer[0]['e__title'].' Discovered: '.$i['i__title'],
                                //Message Body:
                                $i['i__title'].':'."\n".'https://'.$domain_url.'/~'.$i['i__id']."\n\n".
                                ( strlen($add_fields['x__message']) ? $add_fields['x__message']."\n\n" : '' ).
                                $es_discoverer[0]['e__title'].':'."\n".'https://'.$domain_url.'/@'.$es_discoverer[0]['e__id']."\n\n".
                                $u_list_name.
                                $u_list_phone
                            );
     * */


}