<?php 
//Fetch some variables:
$sprint_units = $this->config->item('sprint_units');
$application_status_salt = $this->config->item('application_status_salt');
$start_times = $this->config->item('start_times');

$class_start_time = strtotime($admission['r_start_date']) + (intval($admission['r_start_time_mins'])*60);

//Do some time calculations for the point system:
$due_date = time_format($admission['r_start_date'],2,(($sprint_index+$sprint_duration_multiplier-1) * ( $admission['b_sprint_unit']=='week' ? 7 : 1 )));
$due_late_date = time_format($admission['r_start_date'],2,(($sprint_index+$sprint_duration_multiplier) * ( $admission['b_sprint_unit']=='week' ? 7 : 1 )));

$ontime_secs_left = ( strtotime($due_date) + (intval($admission['r_start_time_mins'])*60) - time());
$alittle_late_secs = ( $admission['b_sprint_unit']=='week' ? 7 : 1 )*24*3600; //"A little late" = 1x Milestone Duration
$qualify_for_little_late = ( abs($ontime_secs_left) < $alittle_late_secs );

?>
<script>


$( document ).ready(function() {
    $("#ontime_dueby").countdowntimer({
        startDate : "<?= date('Y/m/d H:i:s'); ?>",
        dateAndTime : "<?= date('Y/m/d H:i:s' , $ontime_secs_left+time()); ?>",
        size : "lg",
        regexpMatchFormat: "([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2})",
        regexpReplaceWith: "<b>$1</b><sup>Days</sup><b>$2</b><sup>H</sup><b>$3</b><sup>M</sup><b>$4</b><sup>S</sup>"
    });
    $("#late_dueby").countdowntimer({
        startDate : "<?= date('Y/m/d H:i:s'); ?>",
        dateAndTime : "<?= date('Y/m/d H:i:s' , $alittle_late_secs+time()); ?>",
        size : "lg",
        regexpMatchFormat: "([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2})",
        regexpReplaceWith: "<b>$1</b><sup>Days</sup><b>$2</b><sup>H</sup><b>$3</b><sup>M</sup><b>$4</b><sup>S</sup>"
    });
});


function mark_done(){

	//Inactive for now! Maybe introduce later...
	/*
	if($('#us_notes').val().length<1){
		alert('Missing report content.');
		return false;
	}
	*/

	var us_notes = $('#us_notes').val(); //This is needed otherwise we lose the variable!
	
	//Show spinner:
	$('.mark_done').hide();
	$('#save_report').html('<img src="/img/round_yellow_load.gif" class="loader" />').hide().fadeIn();
	
	//Save the rest of the content:
	$.post("/api_v1/completion_report", {

		page_loaded:<?= time() ?>,
		us_notes:us_notes,
		us_on_time_score:<?= $ontime_secs_left>0 ? '1.00' : ( $qualify_for_little_late ? '0.50' : '0.00' ) ?>,
		u_id:$('#u_id').val(),
		b_id:$('#b_id').val(),
		r_id:$('#r_id').val(),
        c_id:$('#c_id').val(),
        next_c_id: <?= ( isset($next_intent['c_id']) ? intval($next_intent['c_id']) : 0 ) ?>,
        next_level: <?= ( isset($next_level) ? intval($next_level) : 0 ) ?>,
        require_notes:<?= ( $intent['c_complete_notes_required']=='t' ? 1 : 0 ) ?>,
        require_url:<?= ( $intent['c_complete_url_required']=='t' ? 1 : 0 ) ?>,

	} , function(data) {
		//Update UI to confirm with user:
		$('#save_report').html(data).hide().fadeIn();

		//Reposition to top:
		$('html,body').animate({
			scrollTop: $('#save_report').offset().top
		}, 150);
    });

}


function start_report(){
	if(!parseInt($('#checklist_complete').val())){

		$('#initiate_done').html('<span style="color:#FF0000;"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> You must first mark all sub-tasks (below) as done before being able to mark this task as done.</span>');
		
	} else {
		$('.mark_done').toggle();

		//Reposition to top:
		$('html,body').animate({
			scrollTop: $('#save_report').offset().top
		}, 150);

		$('#us_notes').focus();
	}
}
</script>

<input type="hidden" id="u_id" value="<?= $admission['u_id'] ?>" />
<input type="hidden" id="b_id" value="<?= $admission['b_id'] ?>" />
<input type="hidden" id="r_id" value="<?= $admission['r_id'] ?>" />
<input type="hidden" id="c_id" value="<?= $intent['c_id'] ?>" />


<?php
//Display Breadcrumb:
echo '<ol class="breadcrumb">';
foreach($breadcrumb_p as $link){
    if($link['link']){
        echo '<li><a href="'.$link['link'].'">'.$link['anchor'].'</a></li>';
    } else {
        echo '<li>'.$link['anchor'].'</li>';
    }
}
echo '</ol>';

if($class_start_time>time()){
    //Class has not yet started:
    ?>
    <script>
        $( document ).ready(function() {
            $("#bootcamp_start").countdowntimer({
                startDate : "<?= date('Y/m/d H:i:s'); ?>",
                dateAndTime : "<?= date('Y/m/d H:i:s' , $class_start_time); ?>",
                size : "lg",
                regexpMatchFormat: "([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2})",
                regexpReplaceWith: "<b>$1</b><sup>Days</sup><b>$2</b><sup>H</sup><b>$3</b><sup>M</sup><b>$4</b><sup>S</sup>"
            });
        });
    </script>
    <div class="alert alert-info" role="alert"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> Bootcamp starts in <span id="bootcamp_start"></span></div>
    <?php
}

//Count active Messages:
$displayed_messages = 0;
if(count($i_messages)>0){
    foreach($i_messages as $i){
        if($i['i_status']==1){
            $displayed_messages++;
        }
    }
}


//Overview:
if($displayed_messages>0){
    $load_open = ( $level>=3 && !isset($us_data[$intent['c_id']]) );
    //Messages:
    echo '<h4 style="margin-top:20px;"><a href="javascript:$(\'.messages_ap\').toggle();"><i class="pointer fa fa-caret-right messages_ap" style="display:'.( $load_open ? 'none' : 'inline-block' ).';" aria-hidden="true"></i><i class="pointer fa fa-caret-down messages_ap" style="display:'.( $load_open ? 'inline-block' : 'none' ).';" aria-hidden="true"></i> <i class="fa fa-commenting" aria-hidden="true"></i> '.$displayed_messages.' Message'.($displayed_messages==1?'':'s').'</a></h4>';
    echo '<div class="tips_content messages_ap" style="display:'.( $load_open ? 'block' : 'none' ).';">';
    foreach($i_messages as $i){
        if($i['i_status']==1){
            echo '<div class="tip_bubble">';
            echo echo_i( array_merge( $i , array(
                'messenger_webview' => 1, //TO embed the video
                'e_b_id'=>$admission['b_id'],
                'e_recipient_u_id'=>$admission['u_id'],
            )) , $admission['u_fname'] );
            echo '</div>';
        }
    }
    echo '</div>';
}



if($level>=3){
    
    echo '<h4><i class="fa fa-check-square" aria-hidden="true"></i> Completion</h4>';


    echo '<div id="save_report" class="quill_content">';
    if(isset($us_data[$intent['c_id']])){

        echo_us($us_data[$intent['c_id']]);

    } else {

        if($intent['c_complete_url_required']=='t' && $intent['c_complete_notes_required']=='t'){
            $red_note = 'a URL & Completion Notes';
            $textarea_note = 'Include a URL & completion notes (and optional instructor feedback) to mark as complete';
        } elseif($intent['c_complete_url_required']=='t'){
            $red_note = 'a URL';
            $textarea_note = 'Include a URL (and optional instructor feedback) to mark as complete';
        } elseif($intent['c_complete_notes_required']=='t'){
            $red_note = 'Completion Notes';
            $textarea_note = 'Include completion notes (and optional instructor feedback) to mark as complete';
        } else {
            $red_note = null;
            $textarea_note = 'Include optional instructor feedback to mark as complete';
        }

        //What instructions do we need to give?
        if(strlen($intent['c_complete_instructions'])>0){
            echo '<div>'.$intent['c_complete_instructions'].'</div>';
        }
        if($red_note) {
            echo '<div style="color:#FF0000;">Completing this task requires ' . $red_note . '.</div>';
        }
        echo '<div>Estimated completion time is '.echo_time($intent['c_time_estimate'],1).'which equals <b>'.round($intent['c_time_estimate']*60).' Points</b> if completed on-time.</div>';
        echo '<div class="mark_done" id="initiate_done"><a href="javascript:start_report();" class="btn btn-black"><i class="fa fa-check-circle initial"></i>Mark as Complete</a></div>';


        //Submission button visible after first button was clicked:
        echo '<div class="mark_done" style="display:none;">';
            echo '<textarea id="us_notes" class="form-control maxout" placeholder="'.$textarea_note.'"></textarea>';
            echo '<a href="javascript:mark_done();" class="btn btn-black"><i class="fa fa-check-circle" aria-hidden="true"></i>Submit</a>';
        echo '</div>';


        if($ontime_secs_left>0){
            //Still on time:
            echo '&nbsp;<i class="fa fa-calendar" aria-hidden="true"></i> Due '.$due_date.' '.$start_times[$admission['r_start_time_mins']].' PST in <span id="ontime_dueby"></span>';
        } else {
            echo '<span style="text-decoration: line-through;">&nbsp;<i class="fa fa-calendar" aria-hidden="true"></i> Was due '.$due_date.' '.$start_times[$admission['r_start_time_mins']].' PST</span>';
            if($qualify_for_little_late && $sprint_index>0 && $intent['c_time_estimate']>0){
                echo '<div style="padding-left:22px;"><b>Earn '.floor($intent['c_time_estimate']*30).' late points</b> by '.$due_late_date.' '.$start_times[$admission['r_start_time_mins']].' PST in <span id="late_dueby"></span></div>';
            }
        }
    }
    echo '</div>';
}





//Display Milestone list:
if($level<3){
    echo '<h4>';
        if($level==1){
            echo '<i class="fa fa-flag" aria-hidden="true"></i> Milestones';
        } elseif($level==2){
            echo '<i class="fa fa-list-ul" aria-hidden="true"></i> Tasks';
        }
        echo ' <span class="sub-title">'.echo_time($intent['c__estimated_hours'],1).'</span>';
    echo '</h4>';

    echo '<div id="list-outbound" class="list-group">';
    
    $sprint_index = 0;
    $done_count = 0;
    foreach($intent['c__child_intents'] as $key=>$sub_intent){
        if($sub_intent['c_status']<1){
            continue;
        }
        $sprint_index += 1; //One step of increment for the start:
        if(isset($us_data[$sub_intent['c_id']]) && $us_data[$sub_intent['c_id']]['us_status']>=0){
            $done_count++;
        }


        //Find the next and previous items:
        $previous_item = null;
        $next_item = null;
        $previous_key = $key;
        $next_key = $key;

        while(!$previous_item){
            $previous_key--;
            if(!isset($intent['c__child_intents'][$previous_key])){
                break;
            } elseif($intent['c__child_intents'][$previous_key]['c_status']>=1){
                $previous_item = $intent['c__child_intents'][$previous_key];
                break;
            }
        }
        while(!$next_item){
            $next_key++;
            if(!isset($intent['c__child_intents'][$next_key])){
                break;
            } elseif($intent['c__child_intents'][$next_key]['c_status']>=1){
                $next_item = $intent['c__child_intents'][$next_key];
                break;
            }
        }

        //Show line:
        echo echo_c($admission,$sub_intent,($level+1),$us_data,$sprint_index,$previous_item,$next_item);
        //Now increment more for next round:
        $sprint_index += ($sub_intent['c_duration_multiplier']-1);
    }
    $checklist_done = ( $done_count == count($intent['c__child_intents']) );
    echo '</div>';
} else {
    $checklist_done = true;
}
?>
<input type="hidden" id="checklist_complete" value="<?= ( $checklist_done ? 1 : 0 ) ?>" />
