<?php

//Construct filters based on GET variables:
$query_filters = array();
$joined_by = array();

//We have a special OR filter when combined with any_e__id & any_i__id
$any_i_e_set = ( ( isset($_GET['any_e__id']) && $_GET['any_e__id'] > 0 ) || ( isset($_GET['any_i__id']) && $_GET['any_i__id'] > 0 ) );
$parent_tr_filter = ( isset($_GET['x__reference']) && $_GET['x__reference'] > 0 ? ' OR x__reference = '.$_GET['x__reference'].' ' : false );



//Apply filters:
if(isset($_GET['i__type']) && strlen($_GET['i__type']) > 0){
    if(isset($_GET['x__type']) && $_GET['x__type']==4250){ //IDEA created
        //Filter idea status based on
        $joined_by = array('x__right');

        if (substr_count($_GET['i__type'], ',') > 0) {
            //This is multiple:
            $query_filters['( i__type IN (' . $_GET['i__type'] . '))'] = null;
        } else {
            $query_filters['i__type'] = intval($_GET['i__type']);
        }
    } else {
        unset($_GET['i__type']);
    }
}

if(isset($_GET['e__type']) && strlen($_GET['e__type']) > 0){
    if(isset($_GET['x__type']) && $_GET['x__type']==4251){ //SOURCE Created

        //Filter idea status based on
        $joined_by = array('x__down');

        if (substr_count($_GET['e__type'], ',') > 0) {
            //This is multiple:
            $query_filters['( e__type IN (' . $_GET['e__type'] . '))'] = null;
        } else {
            $query_filters['e__type'] = intval($_GET['e__type']);
        }
    } else {
        unset($_GET['e__type']);
    }
}

if(isset($_GET['x__status']) && strlen($_GET['x__status']) > 0){
    if (substr_count($_GET['x__status'], ',') > 0) {
        //This is multiple:
        $query_filters['( x__status IN (' . $_GET['x__status'] . '))'] = null;
    } else {
        $query_filters['x__status'] = intval($_GET['x__status']);
    }
}

if(isset($_GET['x__source']) && strlen($_GET['x__source']) > 0){
    if (substr_count($_GET['x__source'], ',') > 0) {
        //This is multiple:
        $query_filters['( x__source IN (' . $_GET['x__source'] . '))'] = null;
    } elseif (intval($_GET['x__source']) > 0) {
        $query_filters['x__source'] = $_GET['x__source'];
    }
}


if(isset($_GET['x__up']) && strlen($_GET['x__up']) > 0){
    if (substr_count($_GET['x__up'], ',') > 0) {
        //This is multiple:
        $query_filters['( x__up IN (' . $_GET['x__up'] . '))'] = null;
    } elseif (intval($_GET['x__up']) > 0) {
        $query_filters['x__up'] = $_GET['x__up'];
    }
}

if(isset($_GET['x__down']) && strlen($_GET['x__down']) > 0){
    if (substr_count($_GET['x__down'], ',') > 0) {
        //This is multiple:
        $query_filters['( x__down IN (' . $_GET['x__down'] . '))'] = null;
    } elseif (intval($_GET['x__down']) > 0) {
        $query_filters['x__down'] = $_GET['x__down'];
    }
}

if(isset($_GET['x__left']) && strlen($_GET['x__left']) > 0){
    if (substr_count($_GET['x__left'], ',') > 0) {
        //This is multiple:
        $query_filters['( x__left IN (' . $_GET['x__left'] . '))'] = null;
    } elseif (intval($_GET['x__left']) > 0) {
        $query_filters['x__left'] = $_GET['x__left'];
    }
}

if(isset($_GET['x__right']) && strlen($_GET['x__right']) > 0){
    if (substr_count($_GET['x__right'], ',') > 0) {
        //This is multiple:
        $query_filters['( x__right IN (' . $_GET['x__right'] . '))'] = null;
    } elseif (intval($_GET['x__right']) > 0) {
        $query_filters['x__right'] = $_GET['x__right'];
    }
}

if(isset($_GET['x__reference']) && strlen($_GET['x__reference']) > 0 && !$any_i_e_set){
    if (substr_count($_GET['x__reference'], ',') > 0) {
        //This is multiple:
        $query_filters['( x__reference IN (' . $_GET['x__reference'] . '))'] = null;
    } elseif (intval($_GET['x__reference']) > 0) {
        $query_filters['x__reference'] = $_GET['x__reference'];
    }
}

if(isset($_GET['x__id']) && strlen($_GET['x__id']) > 0){
    if (substr_count($_GET['x__id'], ',') > 0) {
        //This is multiple:
        $query_filters['( x__id IN (' . $_GET['x__id'] . '))'] = null;
    } elseif (intval($_GET['x__id']) > 0) {
        $query_filters['x__id'] = $_GET['x__id'];
    }
}

if(isset($_GET['any_e__id']) && strlen($_GET['any_e__id']) > 0){
    //We need to look for both parent/child
    if (substr_count($_GET['any_e__id'], ',') > 0) {
        //This is multiple:
        $query_filters['( x__down IN (' . $_GET['any_e__id'] . ') OR x__up IN (' . $_GET['any_e__id'] . ') OR x__source IN (' . $_GET['any_e__id'] . ') ' . $parent_tr_filter . ' )'] = null;
    } elseif (intval($_GET['any_e__id']) > 0) {
        $query_filters['( x__down = ' . $_GET['any_e__id'] . ' OR x__up = ' . $_GET['any_e__id'] . ' OR x__source = ' . $_GET['any_e__id'] . $parent_tr_filter . ' )'] = null;
    }
}

if(isset($_GET['any_i__id']) && strlen($_GET['any_i__id']) > 0){
    //We need to look for both parent/child
    if (substr_count($_GET['any_i__id'], ',') > 0) {
        //This is multiple:
        $query_filters['( x__right IN (' . $_GET['any_i__id'] . ') OR x__left IN (' . $_GET['any_i__id'] . ') ' . $parent_tr_filter . ' )'] = null;
    } elseif (intval($_GET['any_i__id']) > 0) {
        $query_filters['( x__right = ' . $_GET['any_i__id'] . ' OR x__left = ' . $_GET['any_i__id'] . $parent_tr_filter . ')'] = null;
    }
}

if(isset($_GET['any_x__id']) && strlen($_GET['any_x__id']) > 0){
    //We need to look for both parent/child
    if (substr_count($_GET['any_x__id'], ',') > 0) {
        //This is multiple:
        $query_filters['( x__id IN (' . $_GET['any_x__id'] . ') OR x__reference IN (' . $_GET['any_x__id'] . '))'] = null;
    } elseif (intval($_GET['any_x__id']) > 0) {
        $query_filters['( x__id = ' . $_GET['any_x__id'] . ' OR x__reference = ' . $_GET['any_x__id'] . ')'] = null;
    }
}

if(isset($_GET['x__message_search']) && strlen($_GET['x__message_search']) > 0){
    $query_filters['LOWER(x__message) LIKE'] = '%'.$_GET['x__message_search'].'%';
}


if(isset($_GET['start_range']) && is_valid_date($_GET['start_range'])){
    $query_filters['x__time >='] = $_GET['start_range'].( strlen($_GET['start_range']) <= 10 ? ' 00:00:00' : '' );
}
if(isset($_GET['end_range']) && is_valid_date($_GET['end_range'])){
    $query_filters['x__time <='] = $_GET['end_range'].( strlen($_GET['end_range']) <= 10 ? ' 23:59:59' : '' );
}








//Fetch unique transaction types recorded so far:
$ini_filter = array();
foreach($query_filters as $key => $value){
    if(!includes_any($key, array('i__type', 'e__type'))){
        $ini_filter[$key] = $value;
    }
}



//Make sure its a valid type considering other filters:
if(isset($_GET['x__type'])){

    if (substr_count($_GET['x__type'], ',') > 0) {
        //This is multiple:
        $query_filters['x__type IN (' . $_GET['x__type'] . ')'] = null;
    } elseif (intval($_GET['x__type']) > 0) {
        $query_filters['x__type'] = intval($_GET['x__type']);
    }

}

$has_filters = ( count($_GET) > 0 );

$e___11035 = $this->config->item('e___11035'); //NAVIGATION

?>

<script>

    var x_filters = '<?= serialize(count($query_filters) > 0 ? $query_filters : array()) ?>';
    var x_joined_by = '<?= serialize(count($joined_by) > 0 ? $joined_by : array()) ?>';
    var x__message_search = '<?= ( isset($_GET['x__message_search']) && strlen($_GET['x__message_search']) > 0 ? $_GET['x__message_search'] : '' ) ?>';
    var x__message_replace = '<?= ( isset($_GET['x__message_replace']) && strlen($_GET['x__message_replace']) > 0 ? $_GET['x__message_replace'] : '' ) ?>';

    $(document).ready(function () {

        check_i_e__type();

        //Watch for Idea status change:
        $("#x__type").change(function () {
            check_i_e__type();
        });

        //Load first page of transactions:
        app_4341(x_filters, x_joined_by, 1);

    });


    function check_i_e__type(){
        //Checks to see if the Idea/Source status filter should be visible
        //Would only make visible if Transaction type is Created Idea/Member

        //Hide both in/en status:
        $(".filter-statuses").addClass('hidden');

        //Show only if creating new in/en Transaction type:
        if($("#x__type").val()==4250){
            $(".filter-in-status").removeClass('hidden');
        } else if($("#x__type").val()==4251){
            $(".e_status_filter").removeClass('hidden');
        }
    }


    function app_4341(x_filters, x_joined_by, page_num){

        //Show spinner:
        $('#x_page_'+page_num).html('<div class="css__title center"><span class="icon-block"><i class="far fa-yin-yang fa-spin"></i></span>' + js_view_shuffle_message(12694) +  '</div>').hide().fadeIn();

        //Load report based on input fields:
        $.post("/app/app_4341", {
            x_filters: x_filters,
            x_joined_by: x_joined_by,
            x__message_search:x__message_search,
            x__message_replace:x__message_replace,
            page_num: page_num,
        }, function (data) {
            if (!data.status) {
                //Show Error:
                $('#x_page_'+page_num).html('<span class="zq6255">'+ data.message +'</span>');
            } else {
                //Load Report:
                $('#x_page_'+page_num).html(data.message);
                $('[data-toggle="tooltip"]').tooltip();
            }
        });

    }

</script>

<?php


echo '<div class="inline-block margin-top-down '.superpower_active(14005).'" style="padding-left:7px;"><span class="icon-block">'.$e___11035[12707]['m__cover'].'</span><a href="javascript:void();" onclick="$(\'.show-filter\').toggleClass(\'hidden\');" class="css__title">'.$e___11035[12707]['m__title'].'</a></div>';


echo '<div class="show-filter '.( $has_filters && 0 ? '' : 'hidden' ).'">';
echo '<form action="" method="GET">';







echo '<table class="table table-sm maxout"><tr>';

//ANY IDEA
echo '<td><div style="padding-right:5px;">';
echo '<span class="mini-header">ANY IDEA:</span>';
echo '<input type="text" name="any_i__id" value="' . ((isset($_GET['any_i__id'])) ? $_GET['any_i__id'] : '') . '" class="form-control border">';
echo '</div></td>';

echo '<td><span class="mini-header">IDEA PREVIOUS:</span><input type="text" name="x__left" value="' . ((isset($_GET['x__left'])) ? $_GET['x__left'] : '') . '" class="form-control border"></td>';

echo '<td><span class="mini-header">IDEA NEXT:</span><input type="text" name="x__right" value="' . ((isset($_GET['x__right'])) ? $_GET['x__right'] : '') . '" class="form-control border"></td>';

echo '</tr></table>';







echo '<table class="table table-sm maxout"><tr>';

//ANY SOURCE
echo '<td><div style="padding-right:5px;">';
echo '<span class="mini-header">ANY SOURCE:</span>';
echo '<input type="text" name="any_e__id" value="' . ((isset($_GET['any_e__id'])) ? $_GET['any_e__id'] : '') . '" class="form-control border">';
echo '</div></td>';

echo '<td><span class="mini-header">SOURCE CREATOR:</span><input type="text" name="x__source" value="' . ((isset($_GET['x__source'])) ? $_GET['x__source'] : '') . '" class="form-control border"></td>';

echo '<td><span class="mini-header">SOURCE PROFILE:</span><input type="text" name="x__up" value="' . ((isset($_GET['x__up'])) ? $_GET['x__up'] : '') . '" class="form-control border"></td>';

echo '<td><span class="mini-header">SOURCE PORTFOLIO:</span><input type="text" name="x__down" value="' . ((isset($_GET['x__down'])) ? $_GET['x__down'] : '') . '" class="form-control border"></td>';

echo '</tr></table>';





echo '<table class="table table-sm maxout"><tr>';

//ANY DISCOVERY
echo '<td><div style="padding-right:5px;">';
echo '<span class="mini-header">ANY TRANSACTION:</span>';
echo '<input type="text" name="any_x__id" value="' . ((isset($_GET['any_x__id'])) ? $_GET['any_x__id'] : '') . '" class="form-control border">';
echo '</div></td>';

echo '<td><span class="mini-header">TRANSACTION ID:</span><input type="text" name="x__id" value="' . ((isset($_GET['x__id'])) ? $_GET['x__id'] : '') . '" class="form-control border"></td>';

echo '<td><span class="mini-header">PARENT TRANSACTION:</span><input type="text" name="x__reference" value="' . ((isset($_GET['x__reference'])) ? $_GET['x__reference'] : '') . '" class="form-control border"></td>';

echo '<td><span class="mini-header">TRANSACTION STATUS:</span><input type="text" name="x__status" value="' . ((isset($_GET['x__status'])) ? $_GET['x__status'] : '') . '" class="form-control border"></td>';

echo '</tr></table>';






echo '<table class="table table-sm maxout"><tr>';


//Search
echo '<td><div style="padding-right:5px;">';
echo '<span class="mini-header">TRANSACTION MESSAGE SEARCH:</span>';
echo '<input type="text" name="x__message_search" value="' . ((isset($_GET['x__message_search'])) ? $_GET['x__message_search'] : '') . '" class="form-control border">';
echo '</div></td>';

if(isset($_GET['x__message_search']) && strlen($_GET['x__message_search']) > 0){
    //Give Option to Replace:
    echo '<td class="' . superpower_active(14005) . '"><div style="padding-right:5px;">';
    echo '<span class="mini-header">TRANSACTION MESSAGE REPLACE:</span>';
    echo '<input type="text" name="x__message_replace" value="' . ((isset($_GET['x__message_replace'])) ? $_GET['x__message_replace'] : '') . '" class="form-control border">';
    echo '</div></td>';
}



//DISCOVERY Type Filter Groups
echo '<td></td>';




//Filters UI:
echo '<table class="table table-sm maxout"><tr>';

echo '<td valign="top" style="vertical-align: top;"><div style="padding-right:5px;">';
echo '<span class="mini-header">START DATE:</span>';
echo '<input type="date" class="form-control border" name="start_range" value="'.( isset($_GET['start_range']) ? $_GET['start_range'] : '' ).'">';
echo '</div></td>';

echo '<td valign="top" style="vertical-align: top;"><div style="padding-right:5px;">';
echo '<span class="mini-header">END DATE:</span>';
echo '<input type="date" class="form-control border" name="end_range" value="'.( isset($_GET['end_range']) ? $_GET['end_range'] : '' ).'">';
echo '</div></td>';



echo '<td>';
echo '<div>';
echo '<span class="mini-header">TRANSACTION TYPE:</span>';

if(isset($_GET['x__type']) && substr_count($_GET['x__type'], ',')>0){

    //We have multiple predefined transaction types, so we must use a text input:
    echo '<input type="text" name="x__type" value="' . $_GET['x__type'] . '" class="form-control border">';

} else {

    echo '<select class="form-control border" name="x__type" id="x__type" class="border" style="width: 100% !important;">';

    if(isset($_GET['x__source'])) {

        //Fetch details for this member:
        $all_x_count = 0;
        $select_ui = '';
        foreach($this->X_model->fetch($ini_filter, array('x__type'), 0, 0, array('e__title' => 'ASC'), 'COUNT(x__type) as total_count, e__title, x__type', 'x__type, e__title') as $x) {
            //Echo drop down:
            $select_ui .= '<option value="' . $x['x__type'] . '" ' . ((isset($_GET['x__type']) && $_GET['x__type'] == $x['x__type']) ? 'selected="selected"' : '') . '>' . $x['e__title'] . ' ('  . number_format($x['total_count'], 0) . ')</option>';
            $all_x_count += $x['total_count'];
        }

        //Now that we know the total show:
        echo '<option value="0">All ('  . number_format($all_x_count, 0) . ')</option>';
        echo $select_ui;

    } else {

        //Load all fast:
        echo '<option value="0">ALL TRANSACTION TYPES</option>';
        foreach($this->config->item('e___4593') /* DISCOVERY Types */ as $e__id => $m){
            //Echo drop down:
            echo '<option value="' . $e__id . '" ' . ((isset($_GET['x__type']) && $_GET['x__type'] == $e__id) ? 'selected="selected"' : '') . '>' . $m['m__title'] . '</option>';
        }

    }

    echo '</select>';


}

echo '</div>';

//Optional IDEA/SOURCE status filter ONLY IF DISCOVERY Type = Create New IDEA/SOURCE

echo '<div class="filter-statuses filter-in-status hidden"><span class="mini-header">IDEA Type(es)</span><input type="text" name="i__type" value="' . ((isset($_GET['i__type'])) ? $_GET['i__type'] : '') . '" class="form-control border"></div>';

echo '<div class="filter-statuses e_status_filter hidden"><span class="mini-header">SOURCE Status(es)</span><input type="text" name="e__type" value="' . ((isset($_GET['e__type'])) ? $_GET['e__type'] : '') . '" class="form-control border"></div>';

echo '</td>';

echo '</tr></table>';




echo '</tr></table>';




echo '<input type="submit" class="btn btn-6255" value="Apply" />';

if($has_filters){
    echo ' &nbsp;<a href="/-4341" style="font-size: 0.8em;">Remove Filters</a>';
}

echo '</form>';
echo '</div>';


//AJAX Would load content here:
echo '<div id="x_page_1"></div>';
