/* 
Author: Bob Jones
Author Email: bob@rhj4.com
Description: javaScript functions that support wp_notifications demo
License:

  Copyright 2011 Bob Jones (bob@rhj4.com)

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as 
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  detailsYou should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */
var test_messages = [
    {message:"This is a System Error notification from the browser", 
        type:  "system", 
        sticky:  true},
    {message:"This is an Error notification from the browser", 
        type: "error", 
        sticky: true},
    {message: "This is a Confirmation notification from the browser", 
        type: "confirmation", 
        sticky: false},
    {message: "This is a Warning notification from the ASP browser", 
        type: "warning", 
        sticky: false},
    {message: "This is a Data notification from the ASP browser", 
        type: "data", 
        sticky: false}
]

function jgrowl_demo_showBrowserMessages() {
    var pluginurl = jQuery('input#hid_plugin_url').val();
    test_messages.forEach(function(message) {
        jQuery.notification({
            notification:message.message, 
            type:message.type, 
            sticky:message.sticky,
            queue:jQuery('input:checkbox[name="queue"]').is(':checked'),
            pluginurl:pluginurl});
    })

    if (!jQuery('input:checkbox[name="queue"]').is(':checked')) {
        jQuery.notification.option('queue', false);
    }
}

function jgrowl_demo_SendExamplesFromCodeBehind() {
    test_messages.forEach(function(message) {
        jQuery.notification.save(message.message, message.type, message.sticky);
    });
    jgrowl_demo_showAllMessages(true);
}

function jgrowl_demo_showAllMessages(clear) {
    jQuery.notification.show(clear);
}

function jgrowl_demo_generateWebServiceMessages() {
    $.ajaxWrapper('NotificationService.asmx/GenerateMessages');
    if (!$('input:checkbox[name="queue"]').is(':checked')) {
        $.notification.option('queue', false);
    }
    $('#queueSize').text(countHandler());
}

function jgrowl_demo_sendNotification () {
    var notification = jQuery('#notification').val();
    var sticky = jQuery('input:checkbox[name="sticky"]').is(':checked');
    var type = jQuery('input:radio[name="messageTypes"]:checked').val();
    if (type === undefined) {
        alert('Please select a Message Type');
        return;
    }

    var code = jQuery.notification.messageType(type.toLowerCase()).code;
    var queue = jQuery('input:checkbox[name="queue"]').is(':checked');
    var console = jQuery('input:checkbox[name="console"]').is(':checked');

    var options = {
        notification: notification,
        type: type,
        sticky: sticky,
        queue: queue,
        console: console
    };

    switch (jQuery('input:radio[name="notificationSource"]:checked').val()) {
        case 'browser':
            options.notification = 'From browser:<br />' + options.notification;
            options.pluginurl = jQuery('input#hid_plugin_url').val();

            if (queue) {
                // salt the notification away for display later
                jQuery.notification.save(options.notification, options.type, options.sticky);
            } else {
                //  display the notification now
                jQuery.notification(options);
            }
            break;

        case 'codebehind':
            //
            // Do a postback to the page telling it to generate a notification
            //
            options.notification = 'From code-behind:<br />' + options.notification;
            jQuery.notification.save(options.notification, options.type, options.sticky);
            break;

        default:
            alert('please select a notification source');
            break;
    }

    if (console) {
        jQuery.diagnostic(notification);
    }

    if (!queue) {
        jgrowl_demo_drainQueue()
    }
}

function log(notification) {
    jQuery('#logOutput').append(notification + '<br />');
}

function jgrowl_demo_drainQueue() {
    jQuery.notification.option('queue', false);
}

function jgrowl_demo_clearLog() {
    jQuery('#logOutput').text('');
}

jQuery(document).ready(function () {
});
