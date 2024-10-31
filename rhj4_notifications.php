<?php
/*
Plugin Name: RHJ4 Notifications
Plugin URI: http://bellinghamwordpressdevelopers.com/donate/
Description: Integrates jGrowl jQuery plugin into WordPress, enabling creation of jGrowl messages from anywhere in the code.
Version: 1.2
Author: Bob Jones
Author Email: bob@rhj4.com
License:

  Copyright 2014 Bob Jones (bob@rhj4.com)

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

 This plugin supports use of jGrowl to present various types of popup
 messages on the screen. In most cases, messages are generated by php code
 that calls the save function in this class which in turn copies the message
 to the rhj4_notifications table.

 JavaScript running after on doc ready will use ajax calls to request any
 pending messages (notifications) and pass each to jGrowl for screen display.

*/

class RHJ4Notifications {

    /*--------------------------------------------*
     * Constants
     *--------------------------------------------*/
    const name = 'RHJ4 Notifications';
    const slug = 'rhj4_notifications';

    public $plugin_name = 'RHJ4 Notifications';
    public $plugin_slug = 'rhj4_notifications';
    public $plugin_url = '';
    public $plugin_path = '';
    public $admin_url = '';

    public $initialized = FALSE;

    /**
     *  Name of WP Option in which all default values will be stored
     *
     *  @var string = option name
     */
    public $option_name = 'rhj4_notification_options';

    /**
     * Default option values
     */
    public $defaults = array(
        array('option' => 'enabled',      'type' => false, 'enabled' => true),
        array('option' => 'log',          'type' => 0,  'enabled' => true),
        array('option' => 'system',       'type' => 1,  'enabled' => true),
        array('option' => 'error',        'type' => 2,  'enabled' => true),
        array('option' => 'warning',      'type' => 3,  'enabled' => true),
        array('option' => 'confirmation', 'type' => 4,  'enabled' => true),
        array('option' => 'message',      'type' => 5,  'enabled' => false),
        array('option' => 'test',         'type' => 6,  'enabled' => false),
        array('option' => 'data',         'type' => 7,  'enabled' => false),
        array('option' => 'comment',      'type' => 8,  'enabled' => false),
        array('option' => 'tip',          'type' => 9,  'enabled' => false),
        array('option' => 'reminder',     'type' => 10, 'enabled' => false),
        array('option' => 'date',         'type' => 11, 'enabled' => false),
        array('option' => 'validation',   'type' => 12, 'enabled' => false),
        array('option' => 'timer',        'type' => 13, 'enabled' => false)
    );

    /**
     * Return defaults as key-value pair
     */
    public function default_values() {
        $options = array();
        foreach($this->defaults as $option) {
            $key = $option['option'];
            $value = $option['enabled'];
            $options[$key] = $value;
        }

        return $options;
    }

    public function type_name_map() {
        $map = array();
        foreach($this->defaults as $option) {
            if ($option['option'] !== 'enabled') {
                $key = $option['type'];
                $value = $option['option'];
                $map[$key] = $value;
            }
        }

        return $map;
    }

    /**
     * Access this plugin's working instance
     *
     * Use the example below instead of new wp-notifications();
     * Example: $rhj4_notifications = RHJ4Notifications::instance();
     *
     * @return instance of class
     */
    protected static $instance = NULL;

    public static function instance() {
        NULL === self::$instance and self::$instance = new self;
        return self::$instance;
    }

    /**
     * Kill the current instance
     */
    public function kill() {
        self::$instance = NULL;
    }

    /**
     * Setup plugin variables
     */
    public function init($args) {
        if ($this->initialized) {
            return $this;
        }

        global $blog_id;

        $this->plugin_url = plugins_url('/',__FILE__);
        $this->plugin_path = plugin_dir_path(__FILE__);
        $this->admin_url = admin_url('admin-ajax.php');
        $this->initialized = TRUE;

        //
        //  Get default option values and override them with the options
        //  currently defined in the wp_options table.
        //
        $this->options = get_option($this->option_name);
        if (empty($this->options) || !is_array($this->options)) {
            $this->options = $this->default_values();
        } else {
            $this->options = array_merge($this->default_values(), $this->options);
        }

        $this->enabled = $this->boolify($this->options['enabled']);

        /**
         * Load JavaScript and stylesheets
         */
        $this->register_scripts_and_styles();

        /**
         * Generate hidden fields required to connect with server code
         */
        add_action('wp_head', 'rhj4_notifications_generate_hidden_inputs');

        /**
         * Enable saving of notifications from javaScript code
         */
        add_action('wp_ajax_nopriv_rhj4_notifications_save','rhj4_notifications_save');

        /**

         * Enable listing of session notifications
         */
        add_action('wp_ajax_nopriv_rhj4_notifications_list_by_session','rhj4_notifications_list_by_session');
        add_action('wp_ajax_nopriv_rhj4_notifications_session_queue_size','rhj4_notifications_session_queue_size');

        add_action('wp_ajax_nopriv_rhj4_notifications_delete_by_session','rhj4_notifications_delete_by_session');

        /**
         * Enable return of plugin url
         */
        add_action('wp_ajax_nopriv_rhj4_notifications_plugin_url','rhj4_notifications_plugin_url');

        /**
         * Enable return of session id
         */
        add_action('wp_ajax_nopriv_rhj4_notifications_session_id','rhj4_notifications_session_id');

        //this will run when on the frontend

        if (is_admin() ) {
            //this will run when in the WordPress admin
            add_action('admin_head', 'rhj4_notifications_generate_hidden_inputs');
            add_action('wp_ajax_rhj4_notifications_save','rhj4_notifications_save');
            add_action('wp_ajax_rhj4_notifications_session_queue_size','rhj4_notifications_session_queue_size');
            add_action('wp_ajax_rhj4_notifications_list_by_session','rhj4_notifications_list_by_session');
            add_action('wp_ajax_rhj4_notifications_delete_by_session','rhj4_notifications_delete_by_session');
            add_action('wp_ajax_rhj4_notifications_plugin_url','rhj4_notifications_plugin_url');
            add_action('wp_ajax_rhj4_notifications_session_id','rhj4_notifications_session_id');
        }

        /**
         * Add shortcode that will instantiate demo
         */
        add_shortcode( $this->plugin_slug, array( $this, 'render_shortcode' ) );
        return $this;
    }

    public function activate() {
        if (is_plugin_active('rhj4-diagnostics/rhj4_diagnostics.php')) {
            rhj4_diagnostic('RHJ4 Notifications Activated');
        } else {
            echo 'RHJ4 Diagnostics not installed and active';
        }
    }

    public function deactivate() {
        //  We cannot use notification here because it has been deactivated
    }

    private function diag($message) {
        if (is_plugin_active('rhj4-diagnostics/rhj4_diagnostics.php')) {
            rhj4_diagnostic($message);
        } 
    }
    
    public function render_shortcode($atts) {
        //
        //  Format is "enable=<type>,<type>.."
        //  Where type is a number representing the Notification Type
        //
        $verbose = $this->boolify($atts['verbose']);
        $prefix = ($verbose) ? "<strong>RHJ4 Notifications Shortcode output: </strong>" : "";

        //  Capture output to buffer
        ob_start();

        //
        //  The site must be enabled before any specific notification types
        //  can be enabled. Only an admin can enable the plugin on the site
        //  because it creates a new database table.
        //
        if (!$this->enabled) {
            echo $prefix.'Plugin is not enabled for this site<br />';

            if (!is_admin()) {
                echo $prefix.'Only and admin can enable this site<br />';
            }
            return ob_get_clean();
        }


        if ($atts['enable']) {
            //  Enable notification types by type code (e.g.-1..13)
            $types = split(',',$atts['enable']);

            $new_options = array();
            $new_options['enabled'] = true;

            if ($verbose) {
                echo $prefix.'Plugin enabled for these notification types: ';
            }
            //  Get the map of type # to type name
            $map = $this->type_name_map();
            foreach($types as $key => $value) {
                $type = $map[$key];
                $new_options[$type] = true;
                if ($verbose) {
                    echo '['.$key.']'.$type.' ';
                }
            }
            if ($verbose) {
                echo '<br />';
            }

            $this->options = $new_options;

            if ($atts['save']) {
                update_option($this->option_name, $new_options);
                if ($verbose) {
                    echo $prefix.'Options updated in database<br />';
                }
            }
        }

        //  Send a notification
        if ($atts['notify']) {
            $message = $atts['message'];
            $type = (int)$atts['type'];
            $sticky = $atts['sticky'];
            $ok = true;
            if (empty($message)) {
                echo $prefix.'missing "message=<message>"<br />';
                $ok = false;
            }
            if (empty($type)) {
                echo $prefix.'missing "type=<type>"<br />';
                $ok = false;
            } else if (!is_int($type)) {
                echo $prefix.'type must an integer in the range 0..13<br />';
                $ok = false;
            }

            $sticky = $this->boolify($sticky);

            if ($ok) {
                $this->save($message, $type, $sticky);
                if ($verbose) {
                    echo $prefix.'notification saved</br />';
                }
            }
        }

        // Present demo panel
        if ($atts['demo']) {
            $this->diag('Rendering Notifications Demo',
                array('enabled' => true, 'source' => 'RHJ4 Notifications: '));
            $path = plugin_dir_path(__FILE__);
            require_once $path.'include/demo.php';
        }

        return ob_get_clean();
    }

    /**
     * Validate options and return valid array. These options will apply
     * only to this instance of the Notifications plugin. Use "set" to
     * make these options persist.
     *
     * @param array $options
     * @return array
     */
    public function options ($options = null) {
        if (empty($options)) {
            if (!empty($this->option_name)) {
                $options = get_option($this->option_name);
            }
            if (empty($options)) {
                $options = $this->default_values();
            }
        }

        if ($options && is_array($options)) {
            foreach($options as $key=>$value) {
                switch($key) {
                    // if 0, no output will be generated on output stream
                    case 'enabled':
                        if (!$this->enabled && $this->boolify($value)) {
                            if (!$this->enable()) {
                                //  If the enable failed, disable the
                                $value = false;
                            }
                        }

                        $options['enabled'] = ($this->boolify($value)) ? 1 : 0;
                        break;

                    case 'log':
                        $this->log_enabled = $this>boolify($value);
                        break;

                    case 'system':
                        $this->system_enabled = $this>boolify($value);
                        break;

                    case 'error':
                        $this->error_enabled = $this>boolify($value);
                        break;

                    case 'warning':
                        $this->warning_enabled = $this>boolify($value);
                        break;

                    case 'confirmation':
                        $this->confirmation_enabled = $this>boolify($value);
                        break;

                    case 'message':
                        $this->message_enabled = $this>boolify($value);
                        break;

                    case 'test':
                        $this->test_enabled = $this>boolify($value);
                        break;

                    case 'data':
                        $this->data_enabled = $this>boolify($value);
                        break;

                    case 'comment':
                        $this->comment_enabled = $this>boolify($value);
                        break;

                    case 'tip':
                        $this->tip_enabled = $this>boolify($value);
                        break;

                    case 'reminder':
                        $this->reminder_enabled = $this>boolify($value);
                        break;

                    case 'date':
                        $this->data_enabled = $this>boolify($value);
                        break;

                    case 'validation':
                        $this->validation_enabled = $this>boolify($value);
                        break;

                    case 'timer':
                        $this->timer_enabled = $this>boolify($value);
                        break;

                    default:
                        echo 'unknown/invalid option key: ['.$key.']';
                }
            }
        }

        return $options;
    }

    /**
     * Establish default options and make them persistent
     */
    public function set($options = NULL) {
        /**
         * If there are options in wp_options, get them and override
         * existing option values
         */
        $options = $this->options($options);
        if ($options['enabled'] && !$this->boolify($this->enabled)) {
            if ($this->enable()) {
                $this->enabled = $options['enabled'];
            }
        }

        update_option($this->option_name, $options);
    }

    /**
     * reset to defaults and make them persistent
     */
    public function reset() {

        $this->options = $this->default;

        $defaults = $this->default_values();

        $this->enabled = $defaults['enabled'];
        $this->source = $defaults['source'];
        $this->output = $defaults['output'];

        if ($this->option_name) {
            update_option($this->option_name, $defaults);
        }
    }

    /**
     * Enable Notifications
     *
     * All notifications are stored (temporarily) in a common database table
     * that is keyed by Session ID.
     *
     * Enabling this plugin can only be done by an admin user because this
     * function creates a new database table.
     *
     * @global type $wpdb
     * @return boolean true if operation succeeded
     */
    public function enable() {

        if (!is_admin) {
            $diags = RHJ4Diagnostics::instance();
            $diags->diagnostic('Enable function requires admin access');
            return false;
        }

        global $wpdb;

        $wpdb->hide_errors();
        $status = $wpdb->query("DROP TABLE IF EXISTS ".$wpdb->base_prefix."notifications;");
        $this->report_database_error('deleting notifications table');

        $sql = "CREATE TABLE ".$wpdb->base_prefix."notifications (
            `ID` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Primary Key',
            `session_id` varchar(100) NOT NULL,
            `user_id` int(11) DEFAULT NULL,
            `notification_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `is_sticky` bit(1) NOT NULL DEFAULT b'0',
            `notification_type` int(4) NOT NULL,
            `notification_text` varchar(2048) NOT NULL,
            PRIMARY KEY (`ID`)
          ) ENGINE=InnoDB AUTO_INCREMENT=1119 DEFAULT CHARSET=latin1;";
        $status = $wpdb->query($sql);
        $this->report_database_error('creating notifications table');

        if (!$status) {
            $this->set(array('enabled', FALSE));
            die ();
        }

        return $status;
    }

    /**
     * Save a message
     *
     * @param int $message_type
     * @param bool $is_sticky
     * @param string $message_text
     */
    public function save($message_text, $message_type = 1, $is_sticky = 0) {
        if (!$this->enabled) {
            return;
        }

        $notification_type = null;
        foreach ($this->defaults as $value) {
            if($value['type'] == $message_type) {
                $notification_type = $value;
                break;
            }
        }

        //  Check for no match - indicates programming error
        if (empty($notification_type)) {
            return;
        }

        //  See if this notification type is enabled
        $type = $notification_type['option'];
        if (FALSE == $this->options[$type]) {
            return;
        }

        $session_id = session_id();
        if(!$session_id || strlen($session_id) == 0){
            session_start();
            $session_id = session_id();
        }

        if ( function_exists('wp_get_current_user') ) {
            $user = wp_get_current_user();
            $user_id = $user->ID;
        } else {
            $user_id = 0;
        }

        $is_sticky = $this->boolify($is_sticky);

        global $wpdb;
        $wpdb->hide_errors();
        $query = $wpdb->prepare("INSERT INTO ".$wpdb->base_prefix."notifications"
            . "(session_id,user_id,notification_type,is_sticky,notification_text)"
            . "VALUES(%s,%d,%d,%d,%s)",
                $session_id,
                $user_id,
                $message_type,
                $is_sticky,
                $message_text);

        $status = $wpdb->query($query);
        $this->report_database_error('inserting new notifications');
        if (!$status) {
            $this->set(array('enabled', FALSE));
            die ();
        }

        return $status;
    }

    /**
     * Delete all notifications for $user_id
     *
     * @param int $user_id
     */
    public function delete_user_notifications ($user_id = null) {
        if (!$this->enabled) {
            return;
        }

        global $wpdb;

        if (!$user_id) {
            if ( function_exists('wp_get_current_user') ) {
                $user = wp_get_current_user();
                $user_id = $user->ID;
            } else {
                $user_id = 0;
            }
        }

        global $wpdb;
        $wpdb->hide_errors();
        $query = $wpdb->prepare("DELETE FROM ".$wpdb->base_prefix."notifications"
                ."WHERE user_id='".$user_id."'");
        $status = $wpdb->query($query);

        $this->report_database_error('delete_user_notifications');

        return $status;
    }

    /**
     * Remove all notifications for the current session
     *
     * @global type $wpdb
     * @param type $session_id
     * @return type
     */
    public function delete_session_notifications () {
        if (!$this->enabled) {
            return;
        }

        $session_id = session_id();
        if(!$session_id || strlen($session_id) == 0){
            session_start();
        }

        global $wpdb;
        $wpdb->hide_errors();
        $query = $wpdb->prepare("DELETE FROM ".$wpdb->base_prefix."notifications"
                ." WHERE session_id='%s'", $session_id);
        $status = $wpdb->query($query);

        $this->report_database_error('delete_session_notifications');

        return $status;
    }

    /**
     * Clear notifications table of all pending notifications
     *
     * @global type $wpdb
     * @return type
     */
    public function delete_all_notifications () {
        if (!$this->enabled) {
            return;
        }

        global $wpdb;
        $wpdb->hide_errors();
        $query = $wpdb->prepare("DELETE FROM ".$wpdb->base_prefix."notifications ");
        $status = $wpdb->query($query);

        $this->report_database_error('delete_all_notifications');

        return $status;
    }

    public function user_queue_size() {
        if (!$this->enabled) {
            return;
        }
        if ( function_exists('wp_get_current_user') ) {
            $user = wp_get_current_user();
            $user_id = $user->ID;
        } else {
            $user_id = 0;
        }

        global $wpdb;
        $wpdb->hide_errors();
        $query = $wpdb->prepare("SELECT COUNT(*) FROM "
                .$wpdb->base_prefix."notifications"
                ." where user_id=".$user_id
                ." order by notification_date");
        $results = $wpdb->get_results($query);
        $this->report_database_error('rhj4_notifications->list user notifications');

        return $results;
    }

    /**
     * Return list of all notifications pending for $user_id
     *
     * @param int $user_id
     * @param bool $delete
     * @return array of notifications
     */
    public function list_user_notifications($delete = TRUE) {
        if (!$this->enabled) {
            return;
        }

        if ( function_exists('wp_get_current_user') ) {
            $user = wp_get_current_user();
            $user_id = $user->ID;
        } else {
            $user_id = 0;
        }

        global $wpdb;
        $wpdb->hide_errors();
        $query = $wpdb->prepare("SELECT * FROM "
                .$wpdb->base_prefix."notifications"
                ." where user_id=".$user_id
                ." order by notification_date");
        $results = $wpdb->get_results($query);
        $this->report_database_error($this->option_name.'->list user notifications');

        //
        //  Delete any messags that we have just retrieved
        //
        if ($delete === TRUE && count($results) > 0) {
            $status = $this->delete_user_notifications($user_id);
        }

        return $results;
    }

    public function session_queue_size() {
        if (!$this->enabled) {
            return;
        }
        $session_id = session_id();
        if(!$session_id || strlen($session_id) == 0){
            session_start();
            $session_id = session_id();
        }

        global $wpdb;
        $wpdb->hide_errors();
        $results = $wpdb->get_var("SELECT COUNT(*) FROM "
                .$wpdb->base_prefix."notifications"
                ." where session_id='".$session_id."' "
                ."order by notification_date", 0);

        $this->report_database_error($this->option_name.'->session queue size');

        return $results;
    }

    /**
     * Return a list of all pending notifications for the current session
     *
     * @global type $wpdb
     * @param type $delete
     * @return type
     */
    public function list_session_notifications($delete = TRUE) {
        if (!$this->enabled) {
            return;
        }

        $session_id = session_id();
        if(!$session_id || strlen($session_id) == 0){
            session_start();
            $session_id = session_id();
        }

        global $wpdb;
        $wpdb->hide_errors();
        $results = $wpdb->get_results("SELECT * FROM "
                .$wpdb->base_prefix."notifications"
                ." where session_id='".$session_id."' "
                ."order by notification_date", ARRAY_A);

        $this->report_database_error($this->option_name.'->list session notifications');

        //
        //  Delete any messags that we have just retrieved
        //
        if ($delete === TRUE && count($results) > 0) {
            $status = $this->delete_session_notifications($session_id);
        }

        return $results;
    }

    /**
     * return a list of all notifications
     */
    public function list_all_notifications($delete = TRUE) {
        if (!$this->enabled) {
            return;
        }

        global $wpdb;
        $wpdb->hide_errors();
        $query = $wpdb->prepare("SELECT * FROM "
                .$wpdb->base_prefix."notifications"
                ." order by notification_date");
        $results = $wpdb->get_results($query);

        $this->report_database_error($this->option_name.'->list all notifications');

        //
        //  Delete any messags that we have just retrieved
        //
        if ($delete === TRUE && count($results) > 0) {
            $status = $this->delete_all_notifications($user_id);
        }

        return $results;
    }

    ///////////////////////////////////////////////////////////////////////////
    //
    //  PRIVATE FUNCTIONS
    //
    ///////////////////////////////////////////////////////////////////////////

    /**
     * Registers and enqueues stylesheets for the administration panel and the
     * public facing site.
     */
    private function register_scripts_and_styles() {
        if ( is_admin() ) {
        } else {
        } // end if/else

//        wp_enqueue_script('jquery-ui-tabs');

        $this->load_file( self::slug . '-jgrowl', 'js/jquery.jgrowl.js', true );
        $this->load_file( self::slug . '-notifications','js/jquery.notifications.js', true );
        $this->load_file( self::slug . '-demo','js/demo.js', true );
        $this->load_file( self::slug . '-jgrowl-style', 'css/jquery.jgrowl.css' );
        $this->load_file( self::slug . '-notifications-style', 'css/rhj4-notifications.css' );

    } // end register_scripts_and_styles

    /**
     * Helper function for registering and enqueueing scripts and styles.
     *
     * @name	The 	ID to register with WordPress
     * @file_path		The path to the actual file
     * @is_script		Optional argument for if the incoming file_path is a JavaScript source file.
     */
    public function load_file( $name, $file_path, $is_script = false ) {

        $url = plugins_url($file_path, __FILE__);
        $file = plugin_dir_path(__FILE__) . $file_path;

        if( file_exists( $file ) ) {
                if( $is_script ) {
                        wp_register_script( $name, $url, array('jquery','jquery-ui-tabs') ); //depends on jquery
                        wp_enqueue_script( $name );
                } else {
                        wp_register_style( $name, $url );
                        wp_enqueue_style( $name );
                } // end if
        } // end if

    } // end load_file

    /**
     * Convert what should be a boolean value into a boolean value
     *
     * @param string $option
     * @return boolean
     */
    public function boolify ($option) {
        if (is_array($option)) {
            return FALSE;
        }
        if (!$option || strlen($option) === 0) {
            return FALSE;
        }
        if ($option === 'on') {
            return TRUE;
        }

        if ($option == 'true'
                || $option == 'on'
                || $option == '1'
                || $option == 1 ) {
            return TRUE;
        }

        return false;
    }

    /**
     * Report a message using both Notifications and Diagnostics
     *
     * @param type $message_text
     * @param type $message_type
     * @param type $is_sticky
     */
    public function report(
        $message_text,
        $message_type = NOTIFICATION_TYPE_MESSAGE,
        $is_sticky = FALSE) {

        $diags = RHJ4Diagnostics::instance();
        $diags->diagnostic($this->option_name.'->'.$message_text);
        $this->save($message_text, $message_type, $is_sticky);
    }

    /**
     * Report a SQL error
     *
     * It will only report if there is an error
     *
     * @param string $message
     * @return true if error detected
     */
    public function report_database_error($message = NULL) {
        global $wpdb;

        //  Check for database error
        $db_error = ($wpdb->use_mysqli)
                ? mysqli_error( $wpdb->dbh )
                : mysql_error( $wpdb->dbh );

        if ( !$db_error || strlen($db_error) == 0) {
            return false;
        }

        if ($message) {
            $message .= ':'.$db_error;
        } else {
            $message = $db_error;
        }

        if (strlen($message) > 0) {
            $this->report($message, NOTIFICATION_TYPE_ERROR, TRUE);
        }

        return true;
    }
} // end class

/**
 * Runs when the plugin is activated
 */

const NOTIFICATION_TYPE_DIAGNOSTIC      = -1;
const NOTIFICATION_TYPE_LOG             = 0;
const NOTIFICATION_TYPE_SYSTEM          = 1;
const NOTIFICATION_TYPE_ERROR           = 2;
const NOTIFICATION_TYPE_WARNING         = 3;
const NOTIFICATION_TYPE_CONFIRMATION    = 4;
const NOTIFICATION_TYPE_MESSAGE         = 5;
const NOTIFICATION_TYPE_TEST            = 6;
const NOTIFICATION_TYPE_DATA            = 7;
const NOTIFICATION_TYPE_COMMENT         = 8;
const NOTIFICATION_TYPE_TIP             = 9;
const NOTIFICATION_TYPE_REMINDER        = 10;
const NOTIFICATION_TYPE_DATE            = 11;
const NOTIFICATION_TYPE_VALIDATION      = 12;
const NOTIFICATION_TYPE_TIMER           = 13;

///////////////////////////////////////////////////////////////////////////////
//
//  UTILITY FUNCTIONS
//
///////////////////////////////////////////////////////////////////////////////
/**
 * Report a SQL database error (if any occurred)
 *
 * @param type $message will prefix the SQL error if an error actually
 * exists. If no error, nothing is displayed.
 */
function rhj4_notifications_report_database_error($message = NULL) {
    $notify = RHJ4Notifications::instance();
    return $notify->report_database_error($message);
}

/**
 * Generate hidden fields that contain values of admin url and plugin url
 */
function rhj4_notifications_generate_hidden_inputs() {

    //
    //  Create hidden fields that contain critical AJAX variables
    //
    $notify = RHJ4Notifications::instance();
    echo "<input type='hidden' id='hid_admin_url' value='"
        .$notify->admin_url."'/input>";
    echo "<input type='hidden' id='hid_plugin_url' value='"
        .$notify->plugin_url."'/input>";
//    $notify->report('generated hidden inputs');
}

function rhj4_notification($message, $type = NOTIFICATION_TYPE_MESSAGE, $sticky = FALSE) {
    $notify = RHJ4Notifications::instance();
    $notify->save($message, $type, $sticky);
}

///////////////////////////////////////////////////////////////////////////////
//
//  AJAX FUNCTIONS - All must end in death. Sorry, but that's the way it works.
//
///////////////////////////////////////////////////////////////////////////////
/**
 * AJAX Listener function that will save a message for display later
 *
 * This function is called by jQuery code in the browser
 *
 * @param string $message
 * @param int $type (see NOTIFICATION types)
 * @param type $is_sticky
 */
function rhj4_notifications_save() {
    $post = $_POST;

    $notify = RHJ4Notifications::instance();
    $notify->report('saving notification...');
    if (count($post) == 0 || FALSE === $post['notification']) {
        $notify->report('Notification text is missing', NOTIFICATION_TYPE_ERROR,TRUE);
        die();
    }
    $message = (FALSE === $post['notification'])
            ? "Message is empty" : $post['notification'];

    $type = (FALSE === $post['type']) ? NOTIFICATION_TYPE_MESSAGE : $post['type'];
    $is_sticky = (FALSE === $post['sticky']) ? FALSE : $notify->boolify($post['sticky']);

    $notify->save($message, $type, $is_sticky);
    $notify->report("Notification [".$message."]");
    
    // If an error was found, report_database_error will return true
    if (!$notify->report_database_error('saving notification')) {
        $notify->report('notification saved successfully');
        echo 1;
    } else {
        echo 0;
    }

    die();
}

/**
 * AJAX Listener function that will get all pending notifications
 * for this user and return them to the broweser in json format.
 *
 * This function is called by jQuery code in the browser
 */
function rhj4_notifications_list_by_user () {
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])
    && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        $notify = RHJ4Notifications::instance();
        $notify->report('list_by_user');

        $notifications = $notify->list_user_notifications();

        foreach($notifications as $notification):
            $this->report($notification['notification_text']);
        endforeach;

        echo json_encode($notifications);
    }

    die();
}

/**
 * AJAX Listener function that will return number of notifications
 * currently in database queue.
 *
 * This function is called by jQuery code in the browser
 */
function rhj4_notifications_session_queue_size() {
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])
    && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        $notify = RHJ4Notifications::instance();
        $notifications = $notify->session_queue_size();
        $notify->report('queue size: '.$notifications);

        echo (FALSE === $notifications) ? 0 : $notifications;
    }

    die();
}

/**
 * AJAX Listener function that will get all pending notifications
 * for the current session and return them to the broweser in json format.
 *
 * This function is called by jQuery code in the browser
 */
function rhj4_notifications_list_by_session() {
    $request = $_SERVER['HTTP_X_REQUESTED_WITH'];
    if (!empty($request) && strtolower($request) == 'xmlhttprequest') {
        $notify = RHJ4Notifications::instance();
        $diags = RHJ4Diagnostics::instance();
        $notifications = $notify->list_session_notifications(TRUE);
        $count = ($notifications) ? count($notifications) : 0;
        if ($count > 0) {
//            foreach($notifications as $notification):
//                $notify->report($notification['notification_text']);
//            endforeach;

            echo json_encode($notifications);
            die();
        }
        echo json_encode(NULL);
    }
    die();
}

/**
 * AJAX Listener function that will delete all notifications for this session.
 *
 * This function is called by jQuery code in the browser
 */
function rhj4_notifications_delete_by_session() {
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])
    && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        $notify = RHJ4Notifications::instance();
        $notify->report('delete_by_session');

        echo $notify->delete_session_notifications();
    }

    die();
}

/**
 * AJAX Listener function that will get plugin url value from server
 *
 * This function is called by jQuery code in the browser
 */
function rhj4_notifications_plugin_url() {

    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])
    && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        $notify = RHJ4Notifications::instance();
        $notify->report('plugin_url');
        echo $notify->plugin_url;
    }

    die();
}

/**
 * AJAX Listener function that will get current session id
 *
 * This function is called by jQuery code in the browser
 */
function rhj4_notifications_session_id() {
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])
    && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        $notify = RHJ4Notifications::instance();
        $notify->report('session_id');
        $session_id = session_id();
        if(!$session_id || strlen($session_id) == 0){
            session_start();
            $session_id = session_id();
        }

        echo $session_id;
    }

    die();
}

function rhj4_notifications_start_session() {
    if(!session_id()) {
        session_start();
    }
}

function rhj4_notifications_logout() {
    session_destroy ();
}

function rhj4_notifications_login() {
    if(!session_id()) {
        session_start();
    }
}

function rhj4_notifications_initialize($args = NULL) {
    $instance = RHJ4Notifications::instance();
    if (!$instance->initialized) {
        $instance->init($args);
    }
}

/**
 * Actions to be performed under various conditions
 */

add_action('wp_logout', 'rhj4_notifications_logout');
add_action('wp_login', 'rhj4_notifications_login');
add_action('init', 'rhj4_notifications_start_session', 1);
add_action('init','rhj4_notifications_initialize');

/**
 * Register activation and deactivation hooks
 */
register_activation_hook(__FILE__, array('RHJ4Notifications','activate'));
register_deactivation_hook(__FILE__, array('RHJ4Notifications','deactivate'));

if (is_admin()) {
    require_once 'rhj4_notification_settings.php';
    $plugin_settings = new RHJ4NotificationOptions();
}

