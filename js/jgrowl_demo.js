(function($) {
    "use strict";
    /* 
    * To change this license header, choose License Headers in Project Properties.
    * To change this template file, choose Tools | Templates
    * and open the template in the editor.
    */
   ///////////////////////////////////////////////////////////////////////////////
   //
   //  This file supports (and is tied tightly to) NotificationDemo.htm.
   //
   ///////////////////////////////////////////////////////////////////////////////

   //
   //  Example of sending messages from the browser using the $.notification plugin
   //
   $.jgrowl_demo = function() {
       
   }
   
   $.jgrowl_demo.showBrowserMessages = function() {
       $.notification({ notification: "This is a System Error notification from the browser", type: "system", sticky: true, queue: true });
       $.notification({ notification: "This is an Error notification from the browser", type: "error", sticky: true, queue: true });
       $.notification({ notification: "This is a Confirmation notification from the browser", type: "confirmation", sticky: true, queue: true });
       $.notification({ notification: "This is a Warning notification from the ASP browser", type: "warning", sticky: true, queue: true });
       $.notification({ notification: "This is a Data notification from the ASP browser", type: "data", sticky: true, queue: true });

       if (!$('input:checkbox[name="queue"]').is(':checked')) {
           $.notification.option('queue', false);
           listHandler();
           clearHandler();
       }
       $('#queueSize').text(countHandler());
   }

   $.jgrowl_demo.showAllMessages = function () {
       $.each($.notification.types(), function (i, type) {
           $.notification(type.type + '(' + type.code + ') sticky=' + type.sticky, type.code, type.sticky);
       });
   }

   $.jgrowl_demo.generateWebServiceMessages = function () {
       $.ajaxWrapper('NotificationService.asmx/GenerateMessages');
       if (!$('input:checkbox[name="queue"]').is(':checked')) {
           $.notification.option('queue', false);
           listHandler();
           clearHandler();
       }
       $('#queueSize').text(countHandler());
   }

   $.jgrowl_demo.sendNotification = function() {
       var notification = $('#notification').val();
       var sticky = $('input:checkbox[name="sticky"]').is(':checked');
       var type = $('input:radio[name="messageTypes"]:checked').val();
       if (type === undefined) {
           alert('Please select a Message Type');
           return;
       }

       var code = $.notification.messageType(type.toLowerCase()).code;
       var queue = $('input:checkbox[name="queue"]').is(':checked');
       var console = $('input:checkbox[name="console"]').is(':checked');

       var options = {
           notification: notification,
           type: type,
           sticky: sticky,
           queue: queue,
           console: console
       };

       switch ($('input:radio[name="notificationSource"]:checked').val()) {
           case 'browser':
               options.notification = 'From browser:<br />' + options.notification;
               if (queue) {
                   // salt the notification away for display later
                   $.ajaxWrapper('NotificationService.asmx/Save', JSON.stringify(options));
               } else {
                   //  display the notification now
                   $.notification(options);
               }
               break;

           case 'codebehind':
               //
               // Do a postback to the page telling it to generate a notification
               //
               options.notification = 'From code-behind:<br />' + options.notification;
               $.ajaxWrapper("NotificationDemo.aspx/SendNotification", JSON.stringify(options));
               break;

           case 'usercontrol':
               //
               //  Do a postback to the user control
               //
               options.notification = 'From user control:<br />' + options.notification;
               $.ajaxWrapper("NotificationService.asmx/Save", JSON.stringify(options));
               break;

           case 'webservice':
               //
               //  Send notification to webservice 
               //
               options.notification = 'From web service:<br />' + options.notification;
               $.ajaxWrapper('NotificationService.asmx/Save', JSON.stringify(options));
               if (!queue) {
                   listHandler();
               }
               break;

           default:
               alert('please select a notification source');
               break;
       }

       if (console) {
           $.diagnostic(notification);
       }

       if (!queue) {
           drainQueue()
       } else {
           $('#queueSize').text(countHandler());
       }
   }

   function log(notification) {
       $('#logOutput').append(notification + '<br />');
   }

   $.jgrowl_demo.drainQueue = function() {
       $.notification.option('queue', false);
       listHandler();
       clearHandler();
       $('#queueSize').text(countHandler());
   }

   $.jgrowl_demo.clearLog = function() {
       $('#logOutput').text('');
   }

   $.jgrowl_demo.saveHandler = function(notification, type, sticky) {
       var data = JSON.stringify({
           notification: notification,
           type: type,
           sticky: sticky
       });

       $.ajaxWrapper("NotificationService.asmx/Send", data);
   }

   $.jgrowl_demo.listHandler = function() {
       var data = JSON.stringify({ clear: true });
       var messages = $.ajaxWrapper("NotificationService.asmx/List", data);
       $.each(messages, function (i, m) {
           $.notification(m.Text, m.Type, m.Sticky);
       })
   }

   $.jgrowl_demo.countHandler = function () {
       return $.ajaxWrapper("NotificationService.asmx/Count");
   }

   $.jgrowl_demo.clearHandler = function () {
       $.ajaxWrapper("NotificationService.asmx/Clear");
   }

})(jQuery);

jQuery(document).ready(function ($) {
    $.notification.show(true);
    //jQuery('#queueSize').text(countHandler());
});
