<?php

$guide = 'First Column Full Name, 2nd Column Email, 3rd Column Phone Number (If any)... Also no headline row, start with data! ';
$default_val = '';

if(isset($_POST['import_sources']) && strlen($_POST['import_sources'])>0){

    echo 'Begind Processing Import Data:<hr />';

    //Guide:
    $default_val = $_POST['import_sources'];
    $duplicate_name = array();
    $stats = array(
        'new_lines' => 0,
        'unique_lines' => 0,
        'errors' => 0,
    );

    foreach(explode(PHP_EOL, $_POST['import_sources']) as $count => $new_line){

        //Go through each column of this new line:
        $tabs = preg_split('/[\t,]/', $new_line);
        $full_name = $tabs[0];
        $email_address = $tabs[1];
        $phone_number = $tabs[2];
        $stats['new_lines']++;
        $md5 = md5($full_name);

        if(!strlen($full_name) || isset($duplicate_name[$md5])){
            //This is a duplicate line:
            continue;
        }

        $duplicate_name[$md5] = 1;

        $clean_list .= $new_line.'<br />';
        $stats['unique_lines']++; continue;

        //New line to insert:
        $member_result = $this->E_model->add_member($full_name, $email_address, $phone_number, null, 0, true);
        if(!$member_result['status']) {
            $stats['errors']++;
        } else {
            $stats['unique_lines']++;
        }

        break;

    }


    print_r($stats);

    echo '<hr />';

    echo $clean_list;

    echo '<hr />';

}

echo $guide;
echo '<form method="POST" action="">';
echo '<textarea class="border padded full-text" placeholder="Paste List Here" name="import_sources">'.$default_val.'</textarea>';
echo '<button type="submit" class="btn btn-lrg top-margin"><i class="fas fa-plus-circle zq12274"></i> IMPORT</button>';
echo '</form>';