<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
function rhj4_notifications_uninstall() {
    global $wpdb;

    $status = $wpdb->query('DROP TABLE IF EXISTS `rhj4_notifications`;');
}
