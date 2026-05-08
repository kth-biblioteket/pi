<?php

function bibmet_sqlsrv_connect_or_redirect($dbname_override = "")
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $username = isset($_SESSION['anv']) ? $_SESSION['anv'] : "";
    $password = isset($_SESSION['ord']) ? $_SESSION['ord'] : "";
    $hostname = isset($_SESSION['hnamn']) ? $_SESSION['hnamn'] : "";
    $dbname = strlen($dbname_override) > 0 ? $dbname_override : (isset($_SESSION['dbnamn']) ? $_SESSION['dbnamn'] : "");

    if (strlen($username) == 0 || strlen($password) == 0 || strlen($hostname) == 0 || strlen($dbname) == 0) {
        session_destroy();
        header('Location: /PI/sqlserwebb/loggain.php?timeout=1');
        exit;
    }

    try {
        $dbh = new PDO("sqlsrv:Server=$hostname;Database=$dbname", $username, $password);
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $dbh;
    } catch (PDOException $e) {
        session_destroy();
        header('Location: /PI/sqlserwebb/loggain.php?timeout=1');
        exit;
    }
}
