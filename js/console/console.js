

//Loadup Algolia:
client = algoliasearch('49OCX1ZXLJ', 'ca3cf5f541daee514976bc49f8399716');
algolia_b_index = client.initIndex('alg_bootcamps');
algolia_u_index = client.initIndex('alg_entities');


//To update fancy dropdown which is usually used for STATUS updates:
function update_dropdown(name,intvalue,count){
	//Update hidden field with value:
	$('#'+name).val(intvalue);
	//Update dropdown UI:
	$('#ui_'+name).html( $('#'+name+'_'+count).html() + '<b class="caret"></b>' );
	//Reload tooldip:
	$('[data-toggle="tooltip"]').tooltip();
}

//Define tip style:
var tips_button = '<span class="badge tip-badge"><i class="fas fa-info-circle"></i></span>';

function open_tip(intent_id){
	
	//See if this tip needs to be loaded:
	if(!$("div#content_"+intent_id).html().length){
		
		//Show loader:
		$("div#content_"+intent_id).html('<img src="/img/round_yellow_load.gif" class="loader" />');
		
		//Let's check to see if this user has already seen this:
		$.post("/api_v1/c_tip", { intent_id:intent_id } , function(data) {
			//Let's see what we got:
			if(data.success){
				//Load the content:
				$("div#content_"+data.intent_id).html('<div class="row"><div class="col-xs-6"><a href="javascript:close_tip('+data.intent_id+')">'+tips_button+'</a></div><div class="col-xs-6" style="text-align:right;"><a href="javascript:close_tip('+data.intent_id+')"><i class="fas fa-times"></i></a></div></div>'); //Show the same button at top for UX
				$("div#content_"+data.intent_id).append(data.help_content);
				
				//Reload tooldip:
				$('[data-toggle="tooltip"]').tooltip();
			}
	    });
	}
	
	//Expand the tip:
	$('#hb_'+intent_id).hide();
	$("div#content_"+intent_id).fadeIn();
}

function close_tip(intent_id){
	$("div#content_"+intent_id).hide();
	$('#hb_'+intent_id).fadeIn('slow');
}


jQuery.fn.extend({
    insertAtCaret: function(myValue){
        return this.each(function(i) {
            if (document.selection) {
                //For browsers like Internet Explorer
                this.focus();
                sel = document.selection.createRange();
                sel.text = myValue;
                this.focus();
            }
            else if (this.selectionStart || this.selectionStart == '0') {
                //For browsers like Firefox and Webkit based
                var startPos = this.selectionStart;
                var endPos = this.selectionEnd;
                var scrollTop = this.scrollTop;
                this.value = this.value.substring(0, startPos)+myValue+this.value.substring(endPos,this.value.length);
                this.focus();
                this.selectionStart = startPos + myValue.length;
                this.selectionEnd = startPos + myValue.length;
                this.scrollTop = scrollTop;
            } else {
                this.value += myValue;
                this.focus();
            }
        })
    }
});

function switch_to(hashtag_name){
	$('#topnav a[href="#'+hashtag_name+'"]').tab('show');
}

function view_el(u_id,c_id){
    //This function toggles the student card report
    //Determine its current state:
    if($('#c_el_'+u_id+'_'+c_id).hasClass('hidden')){
        //Need to show it now:
        $('#c_el_'+u_id+'_'+c_id).removeClass('hidden');
        $('#pointer_'+u_id+'_'+c_id).removeClass('fa-caret-right');
        $('#pointer_'+u_id+'_'+c_id).addClass('fa-caret-down');
    } else {
        //Need to hide it now:
        $('#c_el_'+u_id+'_'+c_id).addClass('hidden');
        $('#pointer_'+u_id+'_'+c_id).removeClass('fa-caret-down');
        $('#pointer_'+u_id+'_'+c_id).addClass('fa-caret-right');
    }
}

function ms_toggle(c_id,new_state=-1){

    if(new_state<0){
        //Detect new state:
        new_state = ( $('#list-outbound-'+c_id).hasClass('hidden') ? 1 : 0 );
    }

    if(new_state){
        //open:
        $('#list-outbound-'+c_id).removeClass('hidden');
        $('#handle-'+c_id).removeClass('fa-plus-square-o').addClass('fa-minus-square-o');
    } else {
        //Close:
        $('#list-outbound-'+c_id).addClass('hidden');
        $('#handle-'+c_id).removeClass('fa-minus-square-o').addClass('fa-plus-square-o');
    }
}

function load_help(intent_id){
    //Loads the help button:
    $('#hb_'+intent_id).html('<a class="tipbtn" href="javascript:open_tip('+intent_id+')">'+tips_button+'</a>');
}



//Function to load all help messages throughout the console:
$(document).ready(function() {


    $( "#console_search" ).on('autocomplete:selected', function(event, suggestion, dataset) {

        if(dataset==1){
            window.location = "/console/"+suggestion.b_id;
        } else if(dataset==2){
            window.location = "/entities/"+suggestion.u_id;
        }


    }).autocomplete({ hint: false, keyboardShortcuts: ['s'] }, [
        {
            source: function(q, cb) {
                algolia_b_index.search(q, {

                    hitsPerPage: 7,
                    filters: '(b_status>=2)' + ( parseInt($('#u_inbound_u_id').val())==1281 ? '' : ' AND (alg_owner_id=' + $('#u_id').val() + ')' ),

                }, function(error, content) {
                    if (error) {
                        cb([]);
                        return;
                    }
                    cb(content.hits, content);
                });
            },
            displayKey: function(suggestion) { return "" },
            templates: {
                suggestion: function(suggestion) {
                    return '<i class="fas '+( parseInt(suggestion.b_is_parent)==0 ? 'fa-dot-circle' : 'fa-folder-open' )+'"></i> '+ suggestion._highlightResult.alg_name.value + ' <i class="fas '+( parseInt(suggestion.b_status)==3 ? 'fa-bullhorn' : 'fa-link' )+'"></i>' + ( parseInt(suggestion.b_old_format)==1 ? ' <i class="fas fa-lock"></i>' : '' );
                },
            }
        },
        {
            source: function(q, cb) {
                algolia_u_index.search(q, {

                    hitsPerPage: 7,
                    filters: ( parseInt($('#u_inbound_u_id').val())==1281 ? '' : ' AND u_id='+$('#u_id').val() ),

                }, function(error, content) {
                    if (error) {
                        cb([]);
                        return;
                    }
                    cb(content.hits, content);
                });
            },
            displayKey: function(suggestion) { return "" },
            templates: {
                suggestion: function(suggestion) {
                    return '<i class="fas fa-at"></i> '+ suggestion._highlightResult.alg_name.value + ' ('+suggestion.u_inbound_name+')';
                },
            }
        }
    ]);

    //Watch the expand/close all buttons:
    $('#task_view .expand_all').click(function (e) {
        $( "#list-outbound>.is_sortable" ).each(function() {
            ms_toggle($( this ).attr('intent-id'),1);
        });
    });
    $('#task_view .close_all').click(function (e) {
        $( "#list-outbound>.is_sortable" ).each(function() {
            ms_toggle($( this ).attr('intent-id'),0);
        });
    });



    if($("span.help_button")[0]){
        var loaded_messages = [];
        var intent_id = 0;
        $( "span.help_button" ).each(function() {
            intent_id = parseInt($( this ).attr('intent-id'));
            if(intent_id>0 && $("div#content_"+intent_id)[0] && !(jQuery.inArray(intent_id,loaded_messages)!=-1)){
                //Its valid as all elements match! Let's continue:
                loaded_messages.push(intent_id);
                //Load the Tip icon so they can access the tip if they like:
                load_help(intent_id);
            }
        });
    }


    $('#topnav li a').click(function(event) {
        event.preventDefault();
        var hash = $(this).attr('href').replace('#', '');
        window.location.hash = hash;
        adjust_hash(hash);
    });

});


function adjust_hash(hash){
    if(hash.length>0 && $('#tab'+hash).length && !$('#tab'+hash).hasClass("hidden")){
        //Adjust Header:
        $('#topnav>li').removeClass('active');
        $('#nav_'+hash).addClass('active');
        //Adjust Tab:
        $('.tab-pane').removeClass('active');
        $('#tab'+hash).addClass('active');
    }
}

//To keep state of the horizontal menu using URL hashtags:
function focus_hash(the_hash){
	if(!the_hash.length){
		return false;
	} else {
        var hash = the_hash.substring(1); //Puts hash in variable, and removes the # character
        //Open specific menu with a 100ms delay to fix TOP NAV bug
        //Detect if this Exists:
        adjust_hash(hash);
	}
}


function ucwords(str) {
    return (str + '').replace(/^([a-z])|\s+([a-z])/g, function ($1) {
        return $1.toUpperCase();
    });
}

function copyToClipboard(elem) {
  // create hidden text element, if it doesn't already exist
  var targetId = "_hiddenCopyText_";
  var isInput = elem.tagName === "INPUT" || elem.tagName === "TEXTAREA";
  var origSelectionStart, origSelectionEnd;
  if (isInput) {
      // can just use the original source element for the selection and copy
      target = elem;
      origSelectionStart = elem.selectionStart;
      origSelectionEnd = elem.selectionEnd;
  } else {
      // must use a temporary form element for the selection and copy
      target = document.getElementById(targetId);
      if (!target) {
          var target = document.createElement("textarea");
          target.style.position = "absolute";
          target.style.left = "-9999px";
          target.style.top = "0";
          target.id = targetId;
          document.body.appendChild(target);
      }
      target.textContent = elem.textContent;
  }
  // select the content
  var currentFocus = document.activeElement;
  target.focus();
  target.setSelectionRange(0, target.value.length);
  
  // copy the selection
  var succeed;
  try {
  	  succeed = document.execCommand("copy");
  } catch(e) {
      succeed = false;
  }
  // restore original focus
  if (currentFocus && typeof currentFocus.focus === "function") {
      currentFocus.focus();
  }
  
  if (isInput) {
      // restore prior selection
      elem.setSelectionRange(origSelectionStart, origSelectionEnd);
  } else {
      // clear temporary content
      target.textContent = "";
  }
  return succeed;
}


