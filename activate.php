<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
function rhj4_notifications_activate() {
    rhj4_notification('RHJ4 Notifications Activated', NOTIFICATION_TYPE_CONFIRMATION, $sticky = FALSE);
}