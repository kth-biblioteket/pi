<?php
$params = $_REQUEST;
unset($params['PHPSESSID']);
if (function_exists('session_name')) {
    unset($params[session_name()]);
}

$target = 'regel_ej_traeff.php';
if ($params) {
    $target .= '?' . http_build_query($params);
}

header('Location: ' . $target, true, 302);
exit;
