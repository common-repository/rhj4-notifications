<div>
    <table>
        <tbody>
        <tr>
            <td>
                <div class="rhj4_plugins_demo">
                    <table>
                        <tr>
                            <td>
                            Text: <input id="messageText" type="text" placeholder='Enter notification text...'/></td>
                        </tr>   
                        <tr>
                            <td class='rhj4_plugins_input'>Type: <select id="messageType">
<?php
                //  Get list of currently enabled notification types
                $notif = RHJ4Notifications::instance();

                // Get defaults array - this defines the notification types
                $types = $notif->defaults;
                $enabled_options = get_option($notif->option_name);
                $options = "<option value='?'>[SELECT]</option>";
                foreach ($enabled_options as $key => $value) {
                    if ($key !== 'enabled') {
                        foreach ($types as $type) {
                            if ($type['option'] == $key) {
                                $match = $type;
                                break;
                            }
                        }
                        if (!empty($match)) {
                            $options .= "<option value='".$match['type']."'>".$key."</option>";
                        }
                    }
                }
                echo $options;
?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td class='rhj4_plugins_input'>
                                <span>Browser:<input name="messageSource" type="radio" value="browser" class='rhj4_radio' /></span>
                                <span>Server:<input name="messageSource" type="radio" value="server" class='rhj4_radio' checked="checked" />
                            </td>
                        </tr>
                        <tr>	 	 
                            <td class='rhj4_plugins_input'>Sticky:
                                <input id="messageIsSticky" type="checkbox" class="rhj4_checkbox" />
                        Auto-Drain:
                                <input id="autoDrainQueue" type="checkbox" class="rhj4_checkbox" /></td>
                        </tr>	 	
                    </table>
                </div>
                <div>
                    <table class='rhj4_plugins_buttons'>
                        <tr><td>Generate Notifications: 
                            <input type="button" value="Single" onclick="do_notification_submit();return false;" />
                            <input type="button" value="Multiple" onclick="do_notification_submit_all();return false;" />
                            <span id="queue_size"></span>
                            <input id="drain" type="button" value="Drain Queue" onclick="do_notification_drain_queue();return false;"/>
                            <span id='queuing' class='blink'>Queuing...</span>
                            <input type="button" value="Clear" onclick="do_clear_diagnostic_log();return false;" />
                            </td>
                        </tr>
                    </table>
                </div>
                </td>
            <td>
                <div class="rhj4_plugins_log">
<?php
        if (is_plugin_active('rhj4-diagnostics/rhj4_diagnostics.php')) {
            $diags = RHJ4Diagnostics::instance();
            echo $diags->show(); 
        } else {
            echo "This demonstration requires the RHJ4 Diagnostics plugin which is not currently enabled.";
        }
?>        
                </div>
            </td>
        </tr>
    </table>
</div>
<hr />
<div class='notification_comments'>
<p>This is a simple demonstration of some of the capabilities of the RHJ4 Notifications plugin.</p>
<p>Notifications can be generated with javascript (jQuery) code in the browser or with PHP code in the server. 
Notifications generated in the browser can be displayed immediately or queued for later display by saving them in the database.
</p>
<p>Notifications generated in the server will always be queued, and the queue will be drained and any pending notifications displayed on every page refresh.
</p>
</div>
<?php
//add_action('init', 'rhj4_plugins_demo_initialize', 1, 1);

//function rhj4_plugins_demo_initialize() {
    $args = array('enabled'     => true, 
                'output'        => 'error_log',
                'source'        => 'Notification Demo');
    //  Initialize the plugin
    if (is_plugin_active('rhj4-diagnostics/rh4_diagnostics.php')) {
        $diags = RHJ4Diagnostics::instance();
    
        // Set error handler to process all errors
        error_reporting(E_ALL & ~E_STRICT & ~E_NOTICE);
        set_error_handler(array($diags,'error_handler'));
    }
    
    //  Write the plugin name into the output stream
    rhj4_notification('RHJ4 Notifications demo loaded', NOTIFICATION_TYPE_CONFIRMATION);
//}
?>