<table id="notifications_demo">
<tr>
    <td><strong>Text:</strong> <input id="messageText" type="text" /></td>
<td>Enter notification text</td>
</tr>
<tr>
<td>
<table>
<tbody>
<tr>
    <td><strong>Type:</strong></td>
<td>
<select id="messageType"><option value="0">Log</option>
    <option value="1">System Error</option>
    <option value="2">Error</option>
    <option value="3">Warning</option>
    <option selected="selected" value="4">Confirmation</option>
    <option value="5">Message</option>
    <option value="6">Test</option>
    <option value="7">Data</option>
    <option value="8">Comment</option>
    <option value="9">Tip</option>
    <option value="10">Reminder</option>
    <option value="11">Date</option>
    <option value="12">Validation</option>
    <option value="13">Timer</option>
</select></td>
</tr>
</tbody>
</table>
</td>
<td>Select notification type</td>
</tr>
<tr>
<td>
<table>
<tbody>
<tr>
    <td><strong>Source:</strong></td>
<td>Browser:<input checked="checked" name="messageSource" type="radio" value="browser" /></td>
<td>Server:<input name="messageSource" type="radio" value="server" /></td>
</tr>
</tbody>
</table>
</td>
<td>Select notification source</td>
</tr>
<tr>	 	 
    <td><strong>Sticky:</strong> <input id="messageIsSticky" checked="checked" type="checkbox" value="true" /></td>	 	 
<td>Check if notification should remain on screen</td></tr>	 	 
<tr><td colspan="2"><hr /></td></tr>
</table>
<input type="button" value="Generate Notification" onclick="do_notification_submit();return false;" />
<input type="button" value="All" onclick="do_notification_submit_all();return false;" />
<span id="queue_size"></span>
<input id="drain" type="button" value="Drain Queue" onclick="do_notification_drain_queue();return false;"/>
<br /><br />
<hr />
<p>This is a simple demonstration of some of the capabilities of the RHJ4 Notifications plugin.</p>
<p>Notifications can be generated with javascript (jQuery) code in the browser or with PHP code in the server. 
Notifications generated in the browser can be displayed immediately or queued for later display by saving them in the database.
</p>
<p>Notifications generated in the server will always be queued, and the queue will be drained and any pending notifications displayed on every page refresh.
</p>