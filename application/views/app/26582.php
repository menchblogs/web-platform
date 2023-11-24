<?php

if(!$is_u_request || isset($_GET['cron'])){

    foreach($this->X_model->fetch(array(
        'x__access IN (' . join(',', $this->config->item('n___7359')) . ')' => null, //PUBLIC
        'x__type' => 33600, //Drafting Link
        'x__up' => 26582,
    ), array('x__right'), 0) as $drafting_message){

        //Determine if it's time to send this message:
        $start_sending = 0;
        foreach($this->X_model->fetch(array(
            'x__access IN (' . join(',', $this->config->item('n___7359')) . ')' => null, //PUBLIC
            'x__type IN (' . join(',', $this->config->item('n___33602')) . ')' => null, //Idea/Source Links Active
            'x__right' => $drafting_message['i__id'],
            'x__up' => 26556, //Time Starts
        )) as $time){
            $start_sending = strtotime($time['x__message']);
            break;
        }

        if($start_sending>0 && $start_sending>time()){
            //Still not time, go next:
            continue;
        }

        //Does it have an end time?
        $end_sending = 0;
        foreach($this->X_model->fetch(array(
            'x__access IN (' . join(',', $this->config->item('n___7359')) . ')' => null, //PUBLIC
            'x__type IN (' . join(',', $this->config->item('n___33602')) . ')' => null, //Idea/Source Links Active
            'x__right' => $drafting_message['i__id'],
            'x__up' => 26557, //Time Ends
        )) as $time){
            $end_sending = strtotime($time['x__message']);
            break;
        }


        $children = $this->X_model->fetch(array(
            'x__access IN (' . join(',', $this->config->item('n___7360')) . ')' => null, //ACTIVE
            'i__access IN (' . join(',', $this->config->item('n___31871')) . ')' => null, //ACTIVE
            'x__type IN (' . join(',', $this->config->item('n___12840')) . ')' => null, //IDEA LINKS TWO-WAY
            'x__left' => $drafting_message['i__id'],
        ), array('x__right'), 0, 0, array('x__weight' => 'ASC'));


        $top_link = '';
        foreach($this->X_model->fetch(array(
            'x__access IN (' . join(',', $this->config->item('n___7360')) . ')' => null, //ACTIVE
            'x__type' => 32426, //PINNED IDEA
            '(x__right = '.$drafting_message['i__id'].' OR x__left = '.$drafting_message['i__id'].')' => null,
            'x__left >' => 0,
            'x__right >' => 0,
        )) as $top_i){
            $top_link = '/'.( $top_i['x__right']==$drafting_message['i__id'] ? $top_i['x__left'] : $top_i['x__right'] );
            break;
        }


        //Now let's see who will receive this:
        $total_sent = 0;
        $list_settings = list_settings($drafting_message['i__id']);
        $i_plain_message = view_i__message($drafting_message, true);

        foreach($list_settings['query_string'] as $x) {
            //Send to all of them IF NOT SENT
            if(!count($this->X_model->fetch(array(
                'x__left' => $drafting_message['i__id'],
                'x__creator' => $x['e__id'],
                'x__type' => 29399, //Idea Email
                'x__access IN (' . join(',', $this->config->item('n___7359')) . ')' => null, //PUBLIC
            )))){

                //Append children as options:
                $plain_message = '';
                foreach($children as $down_or){

                    $discoveries = $this->X_model->fetch(array(
                        'x__access IN (' . join(',', $this->config->item('n___7360')) . ')' => null, //ACTIVE
                        'x__type IN (' . join(',', $this->config->item('n___6255')) . ')' => null, //DISCOVERIES
                        'x__creator' => $x['e__id'],
                        'x__left' => $down_or['i__id'],
                    ));
                    //Has this user discovered this idea or no?
                    $plain_message .= view_i_title($down_or, true).":\n";
                    $plain_message .= 'https://'.get_domain('m__message', $x['e__id'], $drafting_message['x__website']).$top_link.'/'.$down_or['i__id'].( !count($discoveries) ? '/'.$x['e__id'].'/'.view_hash($x['e__id']) : '' )."\n\n";

                }

                $send_dm = $this->X_model->send_dm($x['e__id'], view_i_title($drafting_message, true), $i_plain_message."\n\n".trim($plain_message), array(
                    'x__right' => $list_settings['list_config'][32426],
                    'x__left' => $drafting_message['i__id'],
                ), 0, $drafting_message['x__website'], true);
                $total_sent += ( $send_dm['status'] ? 1 : 0 );

            }
        }

        //Mark this as complete?
        if(!$end_sending || $end_sending<time()){
            //Ready to be done:
            $this->X_model->update($drafting_message['x__id'], array(
                'x__type' => ( $total_sent > 0 ? 32264 /* Agree */ : 31840 /* Disagree */ ),
            ));
        }

    }

} else {

    echo 'Nothing to see here...';

}