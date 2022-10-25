<?php

//Called when the paypal payment is complete:
if(isset($_POST)){

    $new_x = array();

    //Remove Server Identity:
    unset($_SERVER['SERVER_NAME']);
    if(!isset($_POST['payment_status']) || $_POST['payment_status']!='Completed' || !isset($_POST['item_number'])){

        //Report issue:
        $this->X_model->create(array(
            'x__type' => 4246, //Platform Bug Reports
            'x__message' => 'Unexpected Paypal Call',
            'x__metadata' => array(
                'new_x' => $new_x,
                'post' => $_POST,
            ),
        ));
        echo 'Bad Paypal Post Data!';

    } else {

        $item_numbers = explode('-',$_POST['item_number']);
        $top_i__id = intval($item_numbers[0]);
        $i__id = intval($item_numbers[1]);
        $currency_type = intval($item_numbers[2]);
        $x__source = intval($item_numbers[3]);
        $pay_amount = doubleval(( $_POST['payment_gross']>$_POST['mc_gross'] ? $_POST['payment_gross'] : $_POST['mc_gross'] ));

        //Seems like a valid Paypal IPN Call:
        $next_is = $this->I_model->fetch(array(
            'i__id' => $i__id,
            'i__type IN (' . join(',', $this->config->item('n___7355')) . ')' => null, //PUBLIC
        ));

        if($top_i__id>0 && $i__id>0 && in_array($currency_type, $this->config->item('n___26661')) && $x__source>0 && count($next_is) && $pay_amount>0){

            //Mark idea as complete if not already:
            $new_x = $this->X_model->mark_complete($top_i__id, $next_is[0], array(
                'x__type' => 26595,
                'x__source' => $x__source,
                'x__up' => $currency_type,
                'x__metadata' => $_POST,
            ));

        } else {

            $this->X_model->create(array(
                'x__type' => 4246, //Platform Bug Reports
                'x__message' => 'Invalid item number',
                'x__metadata' => array(
                    'new_x' => $new_x,
                    'post' => $_POST,
                ),
            ));

            echo 'Invalid item number';
        }
    }

} else {
    echo 'Missing Paypal Post Data!';
}



