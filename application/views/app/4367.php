<?php

die('Retired for now');

$transaction__id = 0;

foreach($this->X_model->fetch(array(), array(), 0, 0, array('x__id' => 'ASC')) as $x){

    //Update ID
    $transaction__id++;
    $this->db->where('x__id', $x['x__id']);
    $this->db->update('table__x', array(
        'x__id' => $transaction__id,
    ));

    //Update ID Reference:
    $this->db->where('x__reference', $x['x__id']);
    $this->db->update('table__x', array(
        'x__reference' => $transaction__id,
    ));

}

echo 'SUCCESS: Updated '.$transaction__id.' TRANSACTIONS with a new ID';