<?php
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
?>
<form name="rhj4_notifications_demo" form method="post" action="">
<table border="1" cellspacing="10">
    <tr>
        <td style="vertical-align: top;">
            <strong>Notification Type:</strong>
            <table>
                <tr>
                    <td>
                        System Error:
                    </td>
                    <td>
                        <input type='radio' name='messageTypes' value='System' />
                    </td>
                </tr>
                <tr>
                    <td>
                        Error:
                    </td>
                    <td>
                        <input type='radio' name='messageTypes' value='Error' />
                    </td>
                </tr>
                <tr>
                    <td>
                        Warning:
                    </td>
                    <td>
                        <input type='radio' name='messageTypes' value='Warning' />
                    </td>
                </tr>
                <tr>
                    <td>
                        Confirmation:
                    </td>
                    <td>
                        <input type='radio' name='messageTypes' value='Confirmation' />
                    </td>
                </tr>
                <tr>
                    <td>
                        Test:
                    </td>
                    <td>
                        <input type='radio' name='messageTypes' value='Test' />
                    </td>
                </tr>
                <tr>
                    <td>
                        Data:
                    </td>
                    <td>
                        <input type='radio' name='messageTypes' value='Data' />
                    </td>
                </tr>
                <tr>
                    <td>
                        Comment:
                    </td>
                    <td>
                        <input type='radio' name='messageTypes' value='Comment' />
                    </td>
                </tr>
                <tr>
                    <td>
                        Tip:
                    </td>
                    <td>
                        <input type='radio' name='messageTypes' value='Tip' />
                    </td>
                </tr>
                <tr>
                    <td>
                        Reminder:
                    </td>
                    <td>
                        <input type='radio' name='messageTypes' value='Reminder' />
                    </td>
                </tr>
                <tr>
                    <td>
                        Timer:
                    </td>
                    <td>
                        <input type='radio' name='messageTypes' value='Timer' />
                    </td>
                </tr>
            </table>
        </td>
        <td style="vertical-align: top;">
            <strong>Other Properties:</strong>
            <table>
                <tr>
                    <td>
                        Sticky?
                    </td>
                    <td>
                        <input type='checkbox' name='sticky' value='sticky' />
                    </td>
                </tr>
                <tr>
                    <td>
                        Queue?
                    </td>
                    <td>
                        <input type='checkbox' name='queue' value='queue' />
                    </td>
                </tr>
                <tr>
                    <td>
                        Show on Console?
                    </td>
                    <td>
                        <input type='checkbox' name='console' value='console' />
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        &nbsp;
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <b>Notification Source:</b>
                    </td>
                </tr>
                <tr>
                    <td>
                        Browser:
                    </td>
                    <td>
                        <input type='radio' name='notificationSource' value='browser' />
                    </td>
                </tr>
                <tr>
                    <td>
                        Code-Behind:
                    </td>
                    <td>
                        <input type='radio' name='notificationSource' value='codebehind' />
                    </td>
                </tr>
            </table>
            </fieldset>
        </td>
        <td style="vertical-align: top;">
            <strong>Send Batch From:</strong>
            <table>
                <tr>
                    <td>
                        <input type="button" name="batch_browser" 
                               value="Browser"
                               onclick="jgrowl_demo_showBrowserMessages(); return false;" />
                    </td>
                </tr>
                <tr>
                    <td>
                        <input type="submit" name="batch_server" 
                               value="Server"
                               onclick="jgrowl_demo_SendExamplesFromCodeBehind(); return false;" />
                    </td>
                </tr>
                <tr><td><br /><b>Show All:</b></td></tr>
                <tr><td>
                        <input type="submit" name="show_all"
                               value="Show All"
                               onclick="jgrowl_demo_showAllMessages(false);return false;" />
                    </td></tr>
            </table>
            </fieldset>
        </td>
    </tr>
</table><br />
<table>
<tr><td><b>Notifications in Queue:</b></td><td><span id='queueSize'>&nbsp;&nbsp;</span></td></tr>
<tr><td><b>Notification Text:</b></td><td><input type='text' id='notification' style='width: 300px;' /></td></tr>
</table>

<br />
<input type="button" value='Send Notification' onclick='jgrowl_demo_sendNotification()' title="Send a single notification based on the selected message type, source and properties" />
<input type="button" value='Drain Queue' onclick='jgrowl_demo_showAllMessages(true)' title="Show all notifications in the queue and delete them from the queue" />
<input type="button" value='Clear Notifications' onclick='jQuery.notification.erase();'
    title="Erase any notifications still on the screen" />
</form>
<?php
