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
    var messageIsSticky = jQuery('#messageIsSticky').val();

    do_notification_submit_single(messageText, messageType, messageIsSticky);
}

function do_notification_submit_single(messageText, messageType, messageIsSticky) {
    var messageSource = jQuery("input:radio[name='messageSource']:checked").val();
    jQuery.diagnostic('Type: ' + messageType + ', Source: ' + messageSource + ', Sticky: ' + messageIsSticky + ':[' + messageText + ']');
    if (messageSource === 'browser') {
            jQuery.notification(messageText, messageType, messageIsSticky);
    } else {
            jQuery.notification.save(messageText, messageType, messageIsSticky);
    }

    do_notification_check_queue();
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
    var messageIsSticky = jQuery('#messageIsSticky').val();
    jQuery('#messageType > option').each(function() {
        do_notification_submit_single(messageText, this.value, messageIsSticky);
    })
}

function do_notification_drain_queue() {
    jQuery.notification.show();
    do_notification_check_queue();
}

function do_notification_check_queue() {
    jQuery.notification.queue(function(size) {
        jQuery.diagnostic('queue size ' + (size) ? size : 'is undefined' );
        if (size && size > 0) {
            jQuery("#queue_size").text('Queue size: ' + size).show();
            jQuery("#drain").show();
        } else {
            jQuery("#queue_size").hide();
            jQuery("#drain").hide();
        }
    });
}

jQuery(document).ready(function (jQuery) {
    jQuery.diagnostic('Notifications Short Demo loaded');
    do_notification_check_queue();
});
