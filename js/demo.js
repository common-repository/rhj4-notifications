/**
Demonstration of jQuery integration with web page
**/
function do_notification_submit() {
    var messageText = jQuery("#messageText").val();
    if (messageText.length == 0) {
            jQuery.notification('Message text is empty', 2, true);
            return;
    }

    var messageType = jQuery('#messageType').val();
    var messageIsSticky = jQuery('#messageIsSticky').prop('checked');
    do_notification_submit_single(messageText, messageType, messageIsSticky,
        function(result) {
            jQuery.diagnostic('message submitted. Result: ' + result);
            hide_queuing();
            check_for_autodrain();
            do_notification_check_queue();
            do_show_diagnostic_log();
        });
    
}

function do_show_diagnostic_log() {
    jQuery.diagnostic.show(function(data) {
        jQuery('.plugin_log').html(data);
    });
}

function do_clear_diagnostic_log() {
    jQuery.diagnostic.clear(function(data) {
        jQuery('.plugin_log').html(data);
    });
}

function do_notification_submit_single(messageText, messageType, messageIsSticky, handler) {
    var messageSource = jQuery("input:radio[name='messageSource']:checked").val();
    try {
        jQuery.diagnostic('Type: ' + messageType + ', Source: ' + messageSource + ', Sticky: ' + messageIsSticky + ':[' + messageText + ']', 'notification demo.single');
        if (messageSource === 'browser') {
            jQuery.notification(messageText, messageType, messageIsSticky);
        } else {
            jQuery.diagnostic('saving message to server', 'notification demo');
        }
    } catch (err) {
    }
    jQuery.notification.save(
        messageText, messageType, messageIsSticky,
        function(result) {
            if (handler) {
                handler(result);
            }
        }
    );
}

/**
 * Generate one example of every type of message
 */
function do_notification_submit_all() {
    var messageText = jQuery("#messageText").val();
    if (messageText.length == 0) {
            jQuery.notification('Message text is empty', 2, true);
            return;
    }
    hide_queue();
    show_queuing();
    var messageIsSticky = jQuery('#messageIsSticky').prop('checked');
    var count = 1;
    var types = [];
    jQuery('#messageType > option').each(function() {
        types.push(this.value);
    });
    var index = 0;
    var autoDrain = jQuery('#autoDrainQueue').prop('checked');
    var timeout = setTimeout(function() {
        jQuery('.blink').css('color','red');
        jQuery.diagnostic('submitting message ' + count++ , 'notification demo');
        do_notification_submit_single(messageText, types[index++], messageIsSticky,
            function(result){
                jQuery.diagnostic('response message ' + count + ':]' + result + ']', 'notification demo');
            });
            
        if (autoDrain) {
            jQuery.notification.show();
        }
        
        if (index < types.length) {
            timeout = setTimeout(arguments.callee, 1000);
        } else {
            clearTimeout(timeout);
            do_notification_check_queue();
        }
    }, 1000);
}

function do_notification_drain_queue() {
    jQuery.notification.show();
    do_notification_check_queue();
}

function check_for_autodrain() {
    var autoDrain = jQuery('#autoDrainQueue').prop('checked');
    if (autoDrain) {
        jQuery.notification.show();
    }
}

function get_queue_size(handler) {
    jQuery.diagnostic('requesting queue size...');
    jQuery.notification.queue(function(size) {
        //  When round trip to server completes, hide the queue again
        //  Because we have no idea what else might have happened.
        hide_queue();
        if (size) {
            if (typeof size == 'string') {
                size = Number(size);
            }
        } else {
            size = 0;
        }
        
        if (handler()) {
            handler(size);
        }
    });
}

function do_notification_check_queue() {
    notification_check_queue(function(size) {} );
}

function notification_check_queue(handler) {
    jQuery.notification.queue(function(size) {
        //  When round trip to server completes, hide the queue again
        //  Because we have no idea what else might have happened.
        if (size) {
            if (handler) {
                handler(size);
            }
            if (typeof size == 'string') {
                size = Number(size);
            }
            if (typeof size == 'number' & size > 0) {
                jQuery.diagnostic('queue size ' + size, 'notification demo');
                jQuery("#queue_size").text('Queue size: ' + size).show();
                jQuery("#drain").show();
            } else {
                hide_queue();
            }
        }
        hide_queuing();
    });
}

function show_queuing() {
    jQuery("#queuing").show();    
}

function hide_queuing() {
    jQuery("#queuing").hide();
}

function hide_queue() {
    jQuery("#queue_size").hide();
    jQuery("#drain").hide();
}

jQuery(document).ready(function (jQuery) {
    try {
        jQuery.diagnostic('Notifications Demo loaded','notification demo');
    } catch (err) {
    }
    
    hide_queuing();
    hide_queue();
});
