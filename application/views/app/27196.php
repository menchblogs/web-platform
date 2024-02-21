<?php

if ($_GET['focus_id']==12273 && superpower_unlocked(12700) && isset($_POST['s__id']) && isset($_POST['mass_action_toggle']) && isset($_POST['mass_value1_'.$_POST['mass_action_toggle']]) && isset($_POST['mass_value2_'.$_POST['mass_action_toggle']])) {

    //Process mass action:
    $process_mass_action = $this->I_model->mass_update($_POST['s__id'], intval($_POST['mass_action_toggle']), $_POST['mass_value1_'.$_POST['mass_action_toggle']], $_POST['mass_value2_'.$_POST['mass_action_toggle']], $member_e['e__id']);

    //Pass-on results to UI:
    $this->session->set_flashdata('flash_message', '<div class="alert '.( $process_mass_action['status'] ? 'alert-warning' : 'alert-danger' ).'" role="alert"><span class="icon-block"><i class="fas fa-check-circle"></i></span>'.$process_mass_action['message'].'</div>');

    foreach($this->I_model->fetch(array('i__id' => $_POST['s__id'])) as $i){
        header("Location: /" . $i['i__hashtag'] );
    }

} elseif ($_GET['focus_id']==12274 && superpower_unlocked(12703) && isset($_POST['s__id']) && isset($_POST['mass_action_toggle']) && isset($_POST['mass_value1_'.$_POST['mass_action_toggle']]) && isset($_POST['mass_value2_'.$_POST['mass_action_toggle']])) {

    //Process mass action:
    $process_mass_action = $this->E_model->mass_update($_POST['s__id'], intval($_POST['mass_action_toggle']), $_POST['mass_value1_'.$_POST['mass_action_toggle']], $_POST['mass_value2_'.$_POST['mass_action_toggle']], $member_e['e__id']);

    //Pass-on results to UI:
    $this->session->set_flashdata('flash_message', '<div class="alert '.( $process_mass_action['status'] ? 'alert-info' : 'alert-danger' ).'" role="alert"><span class="icon-block"><i class="fas fa-info-circle"></i></span>'.$process_mass_action['message'].'</div>');

    foreach($this->E_model->fetch(array('e__id' => $_POST['s__id'])) as $e){
        header("Location: " . view_memory(42903,42902) . $e['e__handle'] );
    }

} else {

    echo 'Missing valid input';

}