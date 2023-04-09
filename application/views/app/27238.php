<?php

if(!isset($_GET['e__id']) || !intval($_GET['e__id'])) {
    return view_json(array(
        'status' => 0,
        'message' => 'Missing e__id',
    ));
} else {

    //Login as user:
    $es = $this->E_model->fetch(array(
        'e__id' => intval($_GET['e__id']),
    ));

    if (!count($es) || !in_array($es[0]['e__access'], $this->config->item('n___7358'))) {
        return view_json(array(
            'status' => 0,
            'message' => 'Source is not active.',
        ));
    } else {

        //Make sure member:
        if(!count($this->X_model->fetch(array(
            'x__access IN (' . join(',', $this->config->item('n___7359')) . ')' => null, //PUBLIC
            'x__type IN (' . join(',', $this->config->item('n___32292')) . ')' => null, //SOURCE LINKS
            'x__up IN (' . join(',', $this->config->item('n___32537')) . ')' => null, //Interested Member
            'x__down' => $es[0]['e__id'],
        )))){

            return view_json(array(
                'status' => 0,
                'message' => 'Source is not an interested member',
            ));

        } else {

            session_delete();

            //Assign session & log transaction:
            $this->E_model->activate_session($es[0]);

            js_php_redirect('/@'.$es[0]['e__id']);
        }


    }
}