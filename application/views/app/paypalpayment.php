<?php

$new_x = array();
$is_good = false;

//Called when the paypal payment is complete:
if(isset($_POST['payment_status'])){

    //Log New Payment:
    $item_numbers = explode('@', $_POST['item_number']);
    $idea_refs = explode('#', $item_numbers[0]);
    $hash_target = count($idea_refs)>=3 && strlen($idea_refs[2]);


    $player_es = $this->E_model->fetch(array(
        'LOWER(e__handle)' => strtolower($item_numbers[1]),
    ));
    $website_es = $this->E_model->fetch(array(
        'LOWER(e__handle)' => strtolower($item_numbers[2]),
    ));
    $next_is = $this->I_model->fetch(array(
        'LOWER(i__hashtag)' => strtolower(( $hash_target ? $idea_refs[2] : $idea_refs[1] )),
        'i__privacy IN (' . join(',', $this->config->item('n___31871')) . ')' => null, //ACTIVE
    ));
    $target_is = ($hash_target ? $this->I_model->fetch(array(
        'LOWER(i__hashtag)' => strtolower($idea_refs[1]),
        'i__privacy IN (' . join(',', $this->config->item('n___31871')) . ')' => null, //ACTIVE
    )) : false);



    $x__player = intval($item_numbers[3]);
    $pay_amount = doubleval(( strlen($_POST['payment_gross']) ? $_POST['payment_gross'] : $_POST['mc_gross']));



    if(count($player_es) && count($next_is)) {

        $is_pending = ($_POST['payment_status']=='Pending');
        $is_good = true;

        //, $x__player, $target_i__id, $i

        if($pay_amount > 0){

            //Paid:

            $x__type = ( $is_pending ? 35572 /* Pending Payment */ : 26595 );

            //Log Payment:
            $new_x = $this->X_model->mark_complete($x__type, $x__player, $target_i__id, $next_is[0], array(), array(
                'x__weight' => intval($_POST['quantity']),
                'x__metadata' => $_POST,
            ));

        } else {

            $x__type = ( $is_pending ? 39597 /* Pending Refund */ : 31967 );


            //Find issued tickets:
            $original_payment = $this->X_model->fetch(array(
                'x__type' => 26595,
                'x__player' => $x__player,
                'x__previous' => $next_is[0]['i__id'],
            ));

            //Log Refund:
            $new_x = $this->X_model->mark_complete($x__type, $x__player, $target_i__id, $next_is[0], array(), array(
                'x__weight' => (-1 * ( isset($original_payment[0]['x__weight']) ? $original_payment[0]['x__weight'] : 1 )),
                'x__metadata' => $_POST,
                'x__reference' => ( isset($original_payment[0]['x__id']) ? $original_payment[0]['x__id'] : 0 ),
                'x__website' => ( isset($original_payment[0]['x__website']) && $original_payment[0]['x__website']>0 ? $original_payment[0]['x__website'] : 0 ),
            ));

        }
    }
}


if(!$is_good){

    echo '<div class="alert alert-danger" role="alert">Missing Paypal API Variables.</div>';

    if(isset($_POST) && count($_POST)){
        $this->X_model->create(array(
            'x__type' => 4246, //Platform Bug Reports
            'x__message' => 'Invalid item number',
            'x__metadata' => array(
                'new_x' => $new_x,
                'post' => $_POST,
            ),
        ));
    }
}



