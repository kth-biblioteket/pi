<?php
require_once __DIR__ . '/sqlsrv_connect.php';

$dbh = bibmet_sqlsrv_connect_or_redirect();

header('Content-Type: application/json; charset=utf-8');

$query = isset($_GET['q']) ? trim((string) $_GET['q']) : '';
$items = [];
$isSqlite = $dbh->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite';
$nameExpression = $isSqlite
    ? "Name_en || ' [' || Country_name || ']'"
    : "Name_en + ' [' + Country_name + ']'";
$limitPrefix = $isSqlite ? "" : "TOP 50 ";
$limitSuffix = $isSqlite ? " LIMIT 50" : "";

try {
    if ($query === '') {
        $stmt = $dbh->query("SELECT {$limitPrefix}{$nameExpression} AS Name FROM Unified_org_names ORDER BY Name_en, Country_name{$limitSuffix}");
    } else {
        $stmt = $dbh->prepare("SELECT {$limitPrefix}{$nameExpression} AS Name
            FROM Unified_org_names
            WHERE UPPER(Name_en) LIKE UPPER(:query)
                OR UPPER(Country_name) LIKE UPPER(:query)
                OR UPPER({$nameExpression}) LIKE UPPER(:query)
            ORDER BY Name_en, Country_name{$limitSuffix}");
        $stmt->bindValue(':query', '%' . $query . '%', PDO::PARAM_STR);
        $stmt->execute();
    }

    foreach ($stmt as $row) {
        $name = (string) $row['Name'];
        $items[] = ['value' => $name, 'text' => $name];
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Det gick inte att hämta organisationer.']);
    exit;
}

echo json_encode($items, JSON_UNESCAPED_UNICODE);
