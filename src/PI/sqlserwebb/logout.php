<?php
require_once __DIR__ . '/sqlsrv_connect.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

bibmet_redirect_to_login();
