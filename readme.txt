=== RHJ4 Notifications ===
Contributors: Bob Jones (rhj4), Stan Lemon (jGrowl)
Donate link: http://bellinghamwordpressdevelopers.com/donate
Tags: diagnostics, error-messages, user-interface, popups
Requires at least: 3.0.1
Tested up to: 3.9
Stable tag: 1.7
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Generate many different types of popup notifications to users from PHP or jQuery with only a single line of code.

== Description ==

RHJ4 Notifications and its sibling, RHJ4 Diagnostics solve problems that have plagued programmers for years: How to communicate with users in a clean, clear and visually consistent fashion regardless of where in the program the message originates.

A notification message can be sent from jQuery code in the browser or from PHP code in the server. Either type of message can be displayed immediately or queued for display after the next page turn.

Notifications appear as popup messages on the bottom right corner of the screen and can stay on the screen until the user clicks on them or they can disappear after five seconds.

Using this plugin, a message can be sent easily from a single line of PHP code:

        rhj4_notification('Warning! Danger, Will Robinson!', NOTIFICATION_TYPE_WARNING);

The same messsage can be sent from jQuery code running in the browser:

        jQuery.notification('Warning! Danger, Will Robinson!',
        jQuery.notification.type('warning'));

By default, notifications will disappear from the screen after five seconds; however, to make a notification "sticky", add true as the third argument to either the PHP or jQuery functions:

        jQuery.notification('Warning! Danger, Will Robinson!',
        jQuery.notification.type('warning'), true);

Notifications can be displayed immediately or queued in the wp_notifications table for display later. The wp_notifications table is created when the plugin is enabled. To "enable" this plugin, see Settings and click on the "Enable" checkbox.

RHJ4 Notifications supports 15 types of notifications

==  Installation ==

1. Upload `RHJ4Notifications.zip` file to your `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. In Settings, check "Enable" and save.

== Frequently Asked Questions ==

= When do jQuery notifications appear on the screen? =

Immediately unless you have requested that they be "queued".

= When do queued messages get displayed? =

On the next page turn or on a call to rhj4_notifications_list_by_session();

= What can I do with shortcodes? =

*Lots!* 

* Enable specific notification types: only enabled types will be displayed.
* Send a notification message
* Present an interactive demonstration of how to send messages from jQuery or PHP

==  Screenshots ==

1. Notifications_Demo.png
2. This is the second screen shot

==  Changelog ==

= 1.2 =

Overhauled demo.php to use AJAX calls to RHJ4 Diagnostics. It now displays the current Diagnostic log file updates as they happen.

= 1.12 = 

Very small cosmetic fixes.

= 1.1 =

Completed testing and documentation. First public release.

== Upgrade Notice ==

= 1.0 = Initial version

==  Usage ==

A notification consists of a:

- **Message** - some text to be shown to the user.
- **Notification Type** - an integer in the range -1..13 that indicates the style of the notification.
- **Sticky** - a boolean that indicates whether the message should disappear after a few seconds or "stick" around.

### Notification Types ###

These constants are in the global name space:

- NOTIFICATION_TYPE_DIAGNOSTIC      = -1;
- NOTIFICATION_TYPE_LOG             = 0;
- NOTIFICATION_TYPE_SYSTEM          = 1;
- NOTIFICATION_TYPE_ERROR           = 2;
- NOTIFICATION_TYPE_WARNING         = 3;
- NOTIFICATION_TYPE_CONFIRMATION    = 4;
- NOTIFICATION_TYPE_MESSAGE         = 5;
- NOTIFICATION_TYPE_TEST            = 6;
- NOTIFICATION_TYPE_DATA            = 7;
- NOTIFICATION_TYPE_COMMENT         = 8;
- NOTIFICATION_TYPE_TIP             = 9;
- NOTIFICATION_TYPE_REMINDER        = 10;
- NOTIFICATION_TYPE_DATE            = 11;
- NOTIFICATION_TYPE_VALIDATION      = 12;
- NOTIFICATION_TYPE_TIMER           = 13;

There are four different interfaces available in the Notifications plugin:

- Global Functions
- Class Methods
- jQuery plugin
- Short Codes

### PHP Global Functions ###

#### Generate a notification ####

    rhj4_notification($message, $type = NOTIFICATION_TYPE_MESSAGE, $sticky = FALSE);

### PHP Class Methods ###

#### RHJ4Notifications::instance() ####

Return an instance of this plugin. This usage allows code in multiple places to use the same instance without having to reinitialize the plugin every time.

*Example:*

        $myInstance = RHJ4Notifications::instance()    
    
#### init($args) ####

Initialize plugin using any of the options defined in $args (see "options" for a list of supported options).

#### kill() ####

Kill the current instance.
     
#### activate() ####

Activate this plugin.

#### deactivate() #### 

Deactivate this plugin.

#### options ($options = null) #### 

Validate options and return valid array. The supported options are:

-**enable**: enable generation of diagnostic messages. 

-**log** = {true|false}: enable log notifications

-**system** = {true|false}: enable system notifications

-**error** = {true|false}: enable error notifications

-**warning** = {true|false}: enable warning notifications

-**confirmation** = {true|false}: enable confirmation notifications

-**message** = {true|false}: enable message notifications

-**test** = {true|false}: enable test notifications

-**data** = {true|false}: enable data notifications

-**comment** = {true|false}: enable comment notifications

-**tip** = {true|false}: enable tip notifications

-**reminder** = {true|false}: enable reminder notifications

-**date** = {true|false}: enable date notifications

-**validation** = {true|false}: enable validation notifications

-**timer** = {true|false}: enable timer notifications

    
#### set($options = NULL) #### 

Establish default options and save them in wp_options. See "options" for a list of supported options.

#### reset() #### 

Reset options to their default values.

#### enable() #### 

Enable this plugin. This function requires admin privileges and should only be called when setting up a site. It creates a database table (first deleting if if it exists) that stores queued notifications.

This function can be accessed from the settings panel or called directly in your code.

All notifications are stored (temporarily) in a common database table that is keyed by Session ID. 

#### save($message_text, $message_type = 1, $is_sticky = 0) #### 

Save a message for display on next page turn or explicit call from jQuery.

     @param int $message_type
     @param bool $is_sticky
     @param string $message_text
    
#### delete_user_notifications ($user_id = null) #### 

Delete all notifications for $user_id.
    
#### delete_session_notifications () #### 

Remove all notifications for the current session.
    
#### delete_all_notifications () #### 

Clear notifications table of all pending notifications.
    
#### user_queue_size() #### 
    
#### list_user_notifications($delete = TRUE) #### 

Return list of all notifications pending for $user_id.
    
#### session_queue_size() #### 
    
#### list_session_notifications($delete = TRUE) #### 

Return a list of all pending notifications for the current session.
    
#### list_all_notifications($delete = TRUE) #### 

Return a list of all notifications.


### jQuery Plugin API ###

The jQuery code included in this plugin is implemented as a jQuery plugin with the class name of "jQuery.notification".

The functions supported by this class are:

#### notification(message, type, isSticky, pluginurl) ####

-**message** (required) may be a string or an object. 

If it is a string, the string is the notification text to be displayed.
If it is an object, the object may contain properties corresponding to any of the options supported by the jQuery.options method.

_**type** (optional) indicates the notification type as either a string or an integer. If missing, type will use the current option.type value.

_**isSticky** (optional) indicates whether the notification is sticky. If missing, isSticky will use the value from msgType.

_**pluginurl** (optional) indicates the url to be called by this function to communicate with the server. If missing, the code will search for a hidden field named #hid_plugin_url. 

Examples:

        jQuery.notification('This is a notification',3,true);

        jQuery.notification({ notification: 'This is a notification', 
                type: "data", 
                sticky: true, 
                queue: false });


#### defaults ####

Returns an object containing the default values of all options used by this plugin:

        version: version,   //  code version
        type: 'log',        //  default message type
        sticky: true,       //  if true, don't clear the message automatically
        queue: false,       //  If true, queue the message for later display
        class: '',          //  jGrowl class that styles this message
        clear: true,        //  Delete saved messages after showing them
        showOnConsole: true,//  Show a copy of the message on the console.log
        glue: 'after',      //  jGrowl property that controls order of displaying messages
        sleep:,             //  sleep in milliseconds before displaying message 
        closeTemplate:      //  image to use in close template
        header:             //  '<span class="icon"></span>',
        notification: ''    //  Message can be included with options


#### sleep() ####

Set notification sleep delay in milliseconds. This value will be remembered by all subsequent calls to $.Notification.


#### option (key, value) ####

Get/Set single option:

-version: (read only)
-type: notification type (string or number)
-sticky: boolean
-queue: boolean - save messages in database until explicit request to drain queue
-clear: boolean - clear queue


#### save (message, code, sticky, handler) ####

-message: the notification text 

-code: notification type

-sticky: stick around for awhile or scram

-handler: (optional) custom function to handle displaying the notification. 


#### messageType (code) ####

Returns array of messageType objects.


#### type (type) ####

Validate notification type value


#### getSessionId() ####

Get current session id.


#### getPluginURL() ####

Return URL of AJAX handler function.

#### queue (handler) ####

Return count of notifications awaiting delivery.

#### show (clear) ####

Show all messages in the queue. If clear===true, delete all messages just just retrieved.


#### erase() ####

Close any open notification messages on screen.

#### clear() ####

Delete all messages to the current session.


#### showUser (userId) ####

Notifications can be sent to a specific userId. The messages will be held until that user logs in.


#### clearUser (userId) ####

Delete all user notifications from database.












### Short Codes ###

- *enable=1,3,6* will enable notification types 1,3 and 6.

- *notify* message=<message> type=<notification_type> sticky={true|false}

- *verbose* will cause actions to be echoed on the page.

- *demo* will display a demonstration of using the Notifications plugin.

### SUPPORT ##

I will attempt to answer your email as quickly as I can, but cannot promise immediate response.

I will entertain ideas for enhancements, especially if I hear the same request from multiple people.

Donations will encourage my support... and my thanks.


### CONSULTING ###

I make my living by helping WordPress developers. If I can help you, please contact me.

Bob Jones

[bob@rhj4.com](mailto:bob@rhj4.com)

[http://bellinghamwordpressdevelopers.com/](http://bellinghamwordpressdevelopers.com/)

