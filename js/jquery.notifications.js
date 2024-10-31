/*
 * Dual licensed under the MIT 
 *      (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 *
 * Notification uses jGrowl to display various types of messages 
 *
 * Written by Bob Jones <bob@rhj4.com>
 *
 *  Update History:
 *      V1.0    2014.05.03  Resurrected from very old code
 *
 *  Dependencies:
 *      Notifications sits on top of jGrowl and controls the display 
 *      of jGrowl messages.
 * 
 *
 *  The functions in this file pull messages from the WordPressdatabase 
 *  and hand themto jGrowl.
 *
 *  Usage:
 * 
 *
 */
(function ($) { // Hide scope, no $ conflict
    var version = '0.5';
    
    ///////////////////////////////////////////////////////////////////////
    //
    //  NOTIFICATION 
    //
    //  Display notification on screen
    //
    //      messageText = message to display
    //      messageType = number (see message types)
    //      isSticky = false to erase messages after display
    //      pluginurl = path to this plugin
    //
    ///////////////////////////////////////////////////////////////////////
    $.notification = function (
            message, 
            type, 
            isSticky, 
            pluginurl) {
                
        var options = $.notification.defaults();

        //
        //  Update the settings and return the current settings
        //
        if (typeof message === 'object') {
            __extend(message);
        } else if (typeof message === 'string') {
            options.notification = message;
            options.type = type;
            options.isSticky = isSticky;
            options.pluginurl = pluginurl;
        } else if (message === undefined) {
            $.diagnostic('message is undefined', 
            { source: '$.notification', exception: false });
            return;
        }
        
        if (options.pluginurl === undefined || options.pluginurl == '') {
            options.pluginurl = $('input#hid_plugin_url').val();
        }
        if (options.pluginurl === undefined || options.pluginurl == '') {
            $.diagnostic('pluginurl is undefined', 
            { source: '$.notification', exception: false });
            return;
        }
        
        options.closeTemplate = options.closeTemplate.replace('@pluginurl@', options.pluginurl);

        //
        //  if message type is specified, use it.
        //  Otherwise, if the default type is specified in options, use that.
        //  Otherwise, use type from message definition
        //
        if (type === undefined) {
            type = options.type;
        }

        //
        //  Code must be defined by now
        //
        if (type === undefined) {
            $.diagnostic('notification code undefined', 
            { source: '$.notification', exception: false });
            return;
        }

        var msgType = $.notification.messageType(type);
        if (msgType === undefined || msgType === null) {
            $.diagnostic('notification code is invalid', 
            { source: '$.notification', exception: false });
            return;
        }

        // Get numeric form for code
        var code = msgType.code;

        //  If isSticky is defined, use it; otherwise get value from table
        var sticky = isSticky;
        if (sticky === undefined) {
            sticky = msgType.sticky;
        }

        message = options.notification;

        if (options.showOnConsole) {
            $.diagnostic(message);
        }

        //  Negative message code means do not display with jGrowl
        if (code < 0) {
            return;
        }

        if (options.queue) {
            $.notification.save(message, code.toString(), sticky);
        } else {
            setTimeout(_notify, options.sleep);
        }

        //
        //  Support functions
        //
        function _notify() {
            
            $.jGrowl(message, {
                theme: msgType.theme,
                header: options.header + msgType.header,
                closeTemplate: options.closeTemplate,
                glue: options.glue,
                sticky: sticky
            });
        }

        function _sleep(ms) {
            var start = new Date().getTime();
            for (var i = 0; i < 1e7; i++) {
                if ((new Date().getTime() - start) > ms) {
                    break;
                }
            }
        }

        function __extend(settings) {
            $.each(settings, function (key, value) {
                switch (key) {
                    case 'version': // Version is read-only
                        break;

                    case 'notification':
                        options[key] = value;
                        break;

                    case 'queue':
                    case 'sticky':
                    case 'clear':
                        if (typeof value === 'boolean') {
                            options[key] = value;
                        }
                        break;

                    case 'pluginurl':
                        options[key] = value;
                        break;
                        
                    case 'sleep':
                        if (options[key] === undefined) {
                            options[key] = $.persistent('notificationSleep');
                        }
                        if (typeof value === 'number') {
                            options[key] = value;
                        }
                        break;

                    default:
                        if (typeof value === 'string') {
                            options[key] = value;
                        }
                }
            })
        }
    }

    ///////////////////////////////////////////////////////////////////////
    //
    //  DEFAULTS
    //
    //  Return default option values
    //
    ///////////////////////////////////////////////////////////////////////
    $.notification.defaults = function () {
        return {
            version: version,   //  code version
            type: 'log',        //  default message type
            sticky: true,       //  if true, don't clear the message automatically
            queue: false,       //  If true, queue the message for later display
            class: '',          //  jGrowl class that styles this message
            clear: true,        //  Delete saved messages after showing them
            showOnConsole: true,//  Show a copy of the message on the console.log
            glue: 'after',      //  jGrowl property that controls order of displaying messages
            sleep: $.persistent('notificationSleep'), //  sleep in milliseconds before displaying message 
            closeTemplate: '<img src="@pluginurl@images/Delete-24x24.png" title="Close">',
            header: '<span class="icon"></span>',
            notification: 'undefined' //  Message can be included with options
        }
    }

    ///////////////////////////////////////////////////////////////////////
    //
    //  SLEEP
    //
    //  Set notification sleep delay in milliseconds. 
    //  This value will be remembered by all
    //  subsequent calls to $.Notification.
    //
    ///////////////////////////////////////////////////////////////////////
    $.notification.sleep = function (milliseconds) {
        if (milliseconds === undefined) {
            $.persistent('notificationSleep', 0);
        } else {
            $.persistent('notificationSleep', milliseconds);
        }
    }

    ///////////////////////////////////////////////////////////////////////
    //
    //  OPTION
    //
    //  Get/Set single option
    //
    ///////////////////////////////////////////////////////////////////////
    $.notification.option = function (key, value) {
        var options = $.notification.defaults();
        switch (key.toLowerCase()) {
            case 'version':
                break;

            case 'type':
                if (typeof value === 'string' || typeof value === 'number') {
                    options[key] = value;
                }
                break;

            case 'sticky':
            case 'queue':
            case 'clear':
                if (typeof value === 'boolean') {
                    options[key] = value;
                }
                break;

            case 'sleep':
                if (typeof value === 'number') {
                    $.persistent('notificationSleep', value);
                    options[key] = value;
                } else if (value === undefined) {
                    return $.persistent('notificationSleep')
                }
                break;

            default:
                if (typeof value === 'string') {
                    options[key] = value;
                }
                break;
        }
        return options[key];
    }

    ///////////////////////////////////////////////////////////////////////
    //
    //  SAVE
    //
    //  Queue a message for display the next time the code
    //  asks for messages. This can be useful during page turns
    //
    $.notification.save = function (message, code, sticky, handler) {
        do_ajax({
            action: "rhj4_notifications_save", 
            notification: message, 
            type: code, 
            sticky: sticky
        }, function(result) {
            if (handler) {
                handler(result);
            } else if (result) {
                $.diagnostic('Save result: [' + result + ']');
            }
        });
    }

    ///////////////////////////////////////////////////////////////////////
    //
    //  MESSAGETYPE 
    //
    //  Lookup message type and return definition
    //
    //  Code can be a string or a number
    //
    ///////////////////////////////////////////////////////////////////////
    $.notification.messageType = function (code) {
        var messageTypes = [
            { code: -1, type: "diagnostic", header: undefined, theme: undefined, sticky: false} ,
            { code: 0,  type: "log", header: "log", theme: "information", sticky: true },
            { code: 1,  type: "system", header: "System Error", theme: "error", sticky: true },
            { code: 2,  type: "error", header: "Error", theme: "error", sticky: true },
            { code: 3,  type: "warning", header: "Warning", theme: "warning", sticky: false },
            { code: 4,  type: "confirmation", header: "Confirmation", theme: "confirmation", sticky: false },
            { code: 5,  type: "message", header: "Message", theme: "information", sticky: true },
            { code: 6,  type: "test", header: "Testing", theme: "comment", sticky: true },
            { code: 7,  type: "data", header: "Data", theme: "data", sticky: true },
            { code: 8,  type: "comment", header: "Comment", theme: "comment", sticky: true },
            { code: 9,  type: "tip", header: "Tip", theme: "tip", sticky: true },
            { code: 10, type: "reminder", header: "Reminder", theme: "reminder", sticky: true },
            { code: 11, type: "date", header: "Reminder Date/Time", theme: "reminder datetime", sticky: true },
            { code: 12, type: "validation", header: "Data Validation", theme: "data", sticky: true },
            { code: 13, type: "timer", header: "Timer", theme: "timer", sticky: true },
        ];

        var msgType = undefined;
        $.each(messageTypes, function (i, v) {
            if (v.code == code || v.type == code) {
                msgType = v;
                return false; // break out of $.each loop
            }
        });

        if (msgType == null) {
            $.diagnostic('Unable to find message type: ' + code, 'error');
        }
        return msgType;
    }

    $.notification.type = function (type) {
        var msgType = $.notification.messageType(type);
        if (msgType) {
            return msgType.code;
        }
        
        return -1;
    }
    
    ///////////////////////////////////////////////////////////////////////
    //
    //  GETSESSIONID 
    //
    //  Get current session id
    //
    ///////////////////////////////////////////////////////////////////////
    $.notification.getSessionId = function () {
        do_ajax({action: "rhj4_notifications_session_id"}, function(result) {
            return result;
        });
    }

    $.notification.getPluginUrl = function () {
        return do_ajax({action: "rhj4_notifications_plugin_url"});
    }

    ///////////////////////////////////////////////////////////////////////
    //
    //  QUEUE
    //
    //  Return count of number of messages waiting to be displayed
    //
    ///////////////////////////////////////////////////////////////////////
    $.notification.queue = function (handler) {
        var pluginurl = jQuery('input#hid_plugin_url').val();
        if (pluginurl === undefined) {
            return;
        }
        
        do_ajax({action: "rhj4_notifications_session_queue_size"},
            function (notifications) {
                if (handler) {
                    handler(notifications);
                }
            }
        );
    };
    
    ///////////////////////////////////////////////////////////////////////
    //
    //  SHOW 
    //
    //  Show all messages in message session queue.
    //  Messages are deleted after they have been shown.
    //
    ///////////////////////////////////////////////////////////////////////
    $.notification.show = function (clear) {
        var pluginurl = jQuery('input#hid_plugin_url').val();
        if (pluginurl === undefined) {
            return;
        }
        
        do_ajax({action: "rhj4_notifications_list_by_session", clear:clear},
            function (notifications) {
                if (notifications === undefined 
                        || notifications === null
                        || notifications == 'null') {
                    return;
                }
                notifications = JSON.parse(notifications);
                if (notifications == null) {
                    return;
                }
                
                jQuery.diagnostic('notification count: ' + notifications.length);
                for (i=0; i<notifications.length; i++) {
                    jQuery.notification(
                        (notifications[i].notification_text.length > 0)         
                            ? notifications[i].notification_text
                            : 'notification has no text',
                        notifications[i].notification_type,
                        (notifications[i].is_sticky == "1"),
                        pluginurl);
                }

            });
    }

    ///////////////////////////////////////////////////////////////////////
    //
    //  ERASE
    //
    //  Close any open jGrowl messages on screen
    //
    ///////////////////////////////////////////////////////////////////////
    $.notification.erase = function () {
        try {
            $("div.jGrowl").jGrowl("close");
            $.diagnostic("clearing jGrowl messages", "jGrowl");
        }
        catch (err) {
            $.diagnostic("Error clearing jGrowl messages: " + err, 'jGrowl');
        }
    }

    ///////////////////////////////////////////////////////////////////////
    //
    //  CLEAR
    //
    //  Delete all messages to the current session
    //
    ///////////////////////////////////////////////////////////////////////
    $.notification.clear = function () {
        do_ajax({action: "rhj4_notifications_delete"});
    }

    ///////////////////////////////////////////////////////////////////////
    //
    //  SHOWUSER
    //
    //  Notifications can be sent to a specific userId.
    //  The messages will be held until that user logs in
    //
    ///////////////////////////////////////////////////////////////////////
    $.notification.showUser = function (userId) {
        
        var list = $.ajaxWrapper("WebServices/NotificationService.asmx/ListUserNotifications", JSON.stringify({ UserId: userId }));
        for (var i = 0; i < $list.length - 1; i++) {
            $.notification($list[i].MessageText, $list[i].MessageType, $list[i].IsSticky);
        };
    }

    ///////////////////////////////////////////////////////////////////////
    //
    //  CLEARUSER
    //
    //  delete all user notifications from database
    //
    ///////////////////////////////////////////////////////////////////////
    $.notification.clearUser = function (userId) {
        $.ajaxWrapper("WebServices/NotificationService.asmx/DeleteUserNotifications", JSON.stringify({ UserId: userId }));
    }

    ///////////////////////////////////////////////////////////////////////
    //
    //  PRIVATE FUNCTIONS
    //
    //  do_ajax (args, handler)
    //  args: data passed to server
    //  handler: function to call with returned data
    //
    ///////////////////////////////////////////////////////////////////////
    function do_ajax(args, handler) {
        var ajaxurl = jQuery('input#hid_admin_url').val();
        if (ajaxurl === undefined) {
            alert('ajaxurl undefined');
            return;
        }
        
//        jQuery.post(ajaxurl,args,function(results) {
//            if (handler!== undefined) {
//                handler(results);
//            }
//        })
//        return false;
        
        try {
            $.diagnostic('ajax url: [' + ajaxurl + '] data: [' + args + '] ');
            jQuery.ajax({
                type: "post",
                data: args,
                async: false,
                url: ajaxurl,
                success: function(results) {
                    if (handler!== undefined) {
                        handler(results);
                    }
                },
                error: function(xhr, textStatus, errorThrown) {
                    if (xhr.status != 0) {
                        var msg = ' (' + xhr.status + ') ';
                        if (textStatus) msg += ': ' + textStatus;
                        if (xhr.status < 200) {
                            msg = 'AJAX Informational ' + msg;
                        } else if (xhr.status < 300) {
                            msg = 'AJAX Success ' + msg;
                        } else if (xhr.status < 400) {
                            msg = 'AJAX Redirection ' + msg;
                        } else if (xhr.status < 500) {
                            msg = 'AJAX Client Error' + msg;
                        } else {
                            msg = 'AJAX Server Error' + msg;
                        }
                        jQuery.diagnostic(msg);
                    } else {
                        jQuery.diagnostic(errorThrown);
                    }
                }
            });
        }
        catch (err) {
            //Handle errors here
            return -1;
        }
    }
})(jQuery);

///////////////////////////////////////////////////////////////////////////
//  
//  On Document Ready, show all pending notifications
//
///////////////////////////////////////////////////////////////////////////
jQuery(document).ready(function ($) {
    $.notification.show(true);
});
