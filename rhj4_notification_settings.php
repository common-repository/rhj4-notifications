<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Enable Settings management in dashboard
 * 
 * Code modeled on article from: 
 * http://ottopress.com/2009/wordpress-settings-api-tutorial/
 * 
 */
class RHJ4NotificationOptions {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'admin_page'));
        add_action('admin_init', array($this, 'admin_init'));
    }

    /**
     * Define the options page
     */
    function admin_page() {

        // Create a single entry in the Settings menu
        // If multiple menu items are wanted here, use _add_menu_page
        //
        add_options_page(
                'RHJ4 Plugin Administration',           // Page Title
                'RHJ4 Notifications',                   // Settings Link
                'manage_options',                       // required capability
                'rhj4_notifications',                   // Menu slug
                array($this,'settings'));               // Page function
    }

    /**
     * Generate the output for the settings page.
     */
    function settings() {
        echo '<div><form action="options.php" method="post">';
        settings_fields('general_settings'); 
        settings_fields('notification_type_settings');
        do_settings_sections('rhj4_notifications'); 
        submit_button(); 
        echo '</form></div>';
    }


    function admin_init() {
        $notify = RHJ4Notifications::instance();
        $option_name = $notify->option_name;
        
        //  Save current option values in temporary memory
        $db_options = get_option($option_name);
        if (!is_array($db_options)) {
            $db_options = array($db_options['option'], $db_options['value']);
        }
        $this->options = array_merge($notify->default_values(), $db_options);

        /**
         * The first section controls whether the plugin is enabled
         * locally and globally
         */
        add_settings_section(
                'general_settings',                // id of section
                'Manage Plugin Status',            // title of section
                array($this,'general_section_text'),    // function to display fields
                'rhj4_notifications');                  // page name

        add_settings_field(
                'enabled',
                'Enable Plugin:',
                array($this,'show_plugin_enabled'),
                'rhj4_notifications', 
                'general_settings');
        
        register_setting(
                'general_settings',                     // section id
                'enabled',                               // field id
                array($this,'validate_enabled'));       // validation function callback

        /**
         * This section identifies each type of notification and allows
         * it to be enabled or disabled.
         */
        add_settings_section(
                'notification_type_settings',                // id of section
                'Manage Notification Types',            // title of section
                array($this,'type_section_text'),       // function to display fields
                'rhj4_notifications');                  // page name

        foreach ($notify->defaults as $item) {
            //  Skip any options that have a non-numeric type value
            if (!is_numeric($item['type'])) {
                continue;
            }
            
            $option = $item['option'];
            add_settings_field(
                    'option_'.$option.'_enabled',
                    'Enable notification type '.strtoupper($option).':',
                    array($this,'show'),
                    'rhj4_notifications', 
                    'notification_type_settings', 
                    $option);
            
            /**
             * regiser the individual fields
             */
            register_setting(
                'notification_type_settings',       // section id
                'option_'.$option.'_enabled',
                array($this,'validate_'.$option));       // validation function callback
        }
        
    }

    function general_section_text () {
        echo '<p>This plugin must be enabled to generate any notifications.'
        . 'It may be enabled using this panel or using the plugin API.</p>';
    }

    function type_section_text() {
        echo '<p>Each notification type can be enabled or disabled.</p>';
    }
    
    function show_plugin_enabled($args) {
        $this->show('enabled');
    }

    function validate_enabled($input) {
        if (empty($input)) {
            $input = $_REQUEST['enabled'];
        }

        //  Get the running instance of this plugin
        $notify = RHJ4Notifications::instance();

        $option_name = $notify->option_name;
        
        //  Get current option values from wp_?_options table
        //$options = get_option($notify->option_name);
        $options = $this->options;  
        
        //  Enabled is rendered as a checkbox which returns nothing if 
        //  not checked, and turn off the enabled value in memory.
        $options['enabled'] = 0;
        $notify->enabled = false;

        //  Validate the options currently in the database
        $options = $notify->options($options);
        
        //  Update the options with the new values
        if ($input) {
            $options = $notify->options($input);
        }
        
        //  Kill current instance, forcing it to re-initialize itself
        $notify->kill();
        
        //$options = $instance->set($input);

        return $options;
    }
    
    function validate_log($new_value) {
        $this->validate($new_value, 'option_log_enabled', 'log');
    }

    function validate_system($new_value) {
        $this->validate($new_value, 'option_system_enabled', 'system');
    }

    function validate_error($new_value) {
        $this->validate($new_value, 'option_error_enabled', 'error');
    }

    function validate_warning($new_value) {
        $this->validate($new_value, 'option_warning_enabled', 'warning');
    }

    function validate_confirmation($new_value) {
       $this->validate($new_value, 'option_confirmation_enabled', 'confirmation');
    }

    function validate_message($new_value) {
        $this->validate($new_value, 'option_message_enabled', 'message');
    }

    function validate_test($new_value) {
        $this->validate($new_value, 'option_test_enabled', 'test');
    }

    function validate_data($new_value) {
        $this->validate($new_value, 'option_data_enabled', 'data');
    }
    
    function validate_comment($new_value) {
        $this->validate($new_value, 'option_comment_enabled', 'comment');
    }

    function validate_tip($new_value) {
        $this->validate($new_value, 'option_tip_enabled', 'tip');
    }

    function validate_reminder($new_value) {
        $this->validate($new_value, 'option_reminder_enabled', 'reminder');
    }

    function validate_date($new_value) {
        $this->validate($new_value, 'option_date_enabled', 'date');
    }

    function validate_validation($new_value) {
        $this->validate($new_value, 'option_validation_enabled', 'validation');
    }
    
   function validate_timer($new_value) {
        $this->validate($new_value, 'option_timer_enabled', 'timer');
    }

    /**
     * Render a checkbox for a notification type
     * @param string $id - control id
     * @param $option - notification type
     */
    function show($option) {
        $notifications = RHJ4Notifications::instance();
        $options = get_option($notifications->option_name);
        $enabled = $notifications->boolify($options[$option]);
        $control = "<input type='checkbox' id='option_".$option."_enabled' name='option_".$option."_enabled' "
                ."value='1' "
                .checked(1, $enabled, false)
                ."/>";
        echo $control;
    }
    
    function validate($new_value, $id, $option) {
        if (empty($new_value)) {
            $new_value = $_REQUEST[$id];
        }
        $notifications = RHJ4Notifications::instance();
        $new_value = (empty($new_value)) ? false : $notifications->boolify($new_value);
        $old_value = $this->options[$option];
        $old_value = (empty($old_value)) ? false : $notifications->boolify($old_value);
        
        if ($new_value !== $old_value) {
            $this->options[$option] = $new_value;
            update_option($notifications->option_name, $this->options);
            $message = 'Notification Type '.strtoupper($option).' ';
            $message .= ($new_value) ? "Enabled" : "Disabled";
            $notifications->save($message, NOTIFICATION_TYPE_CONFIRMATION);
        }
    }
}
