<?php

function bibmet_mssql_host()
{
    $host = getenv('MSSQL_HOST') ?: 'bibmet01.ug.kth.se';
    $trustServerCertificate = getenv('MSSQL_TRUST_SERVER_CERTIFICATE') ?: 'true';

    if (strtolower($trustServerCertificate) === 'true') {
        $host .= ';TrustServerCertificate=true';
    }

    return $host;
}

function bibmet_login_url($reason = "")
{
    $url = '/PI/sqlserwebb/loggain.php';
    return strlen($reason) > 0 ? $url . '?reason=' . urlencode($reason) : $url;
}

function bibmet_redirect_to_login($reason = "")
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_destroy();
    }

    header('Location: ' . bibmet_login_url($reason));
    exit;
}

function bibmet_require_login()
{
    $had_session_cookie = isset($_COOKIE[session_name()]);

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $username = isset($_SESSION['anv']) ? $_SESSION['anv'] : "";
    $password = isset($_SESSION['ord']) ? $_SESSION['ord'] : "";

    if (strlen($username) == 0 || strlen($password) == 0) {
        bibmet_redirect_to_login($had_session_cookie ? 'timeout' : '');
    }
}

function bibmet_sqlsrv_connect_or_redirect($dbname_override = "")
{
    $had_session_cookie = isset($_COOKIE[session_name()]);

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $username = isset($_SESSION['anv']) ? $_SESSION['anv'] : "";
    $password = isset($_SESSION['ord']) ? $_SESSION['ord'] : "";
    $hostname = isset($_SESSION['hnamn']) ? $_SESSION['hnamn'] : "";
    $dbname = strlen($dbname_override) > 0 ? $dbname_override : (isset($_SESSION['dbnamn']) ? $_SESSION['dbnamn'] : "");

    if (strlen($username) == 0 || strlen($password) == 0 || strlen($hostname) == 0 || strlen($dbname) == 0) {
        bibmet_redirect_to_login($had_session_cookie ? 'timeout' : '');
    }

    try {
        $dbh = new PDO("sqlsrv:Server=$hostname;Database=$dbname", $username, $password);
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $dbh;
    } catch (PDOException $e) {
        bibmet_redirect_to_login('timeout');
    }
}
