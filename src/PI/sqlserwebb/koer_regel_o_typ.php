<?php
require_once __DIR__ . '/sqlsrv_connect.php';

$dbh = bibmet_sqlsrv_connect_or_redirect();
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml11/DTD/xhtml-transitional.dtd">

<! Författare: Cecilia Wiklander>
<! Syfte: Adressrättnings-hantering>
<! Ändringar: >

<head>

    <meta charset="utf-8">

    <title>FUNNA ADRESSER - REGLER ORGANISATIONSTYP</title>

    <link href="Site.css" rel="stylesheet">

</head>

<body>

<?php include('include_head_new.html'); ?>

<h2>FUNNA ADRESSER - REGLER ORGANISATIONSTYP</h2>

<a href='regel_organisation_typ.php'>TILL SÖKNING</a>
&nbsp;&nbsp;<a href='aendra_regel_o_typ.php'>TILL ÄNDRA REGEL</a>
</br>
</br>

<?php

    $regel_id = $_SESSION['regel_id'];

    $_SESSION['regel_id_ut'] = $regel_id;

    $land = isset($_POST['Land_ut']) ? trim((string) $_POST['Land_ut']) : "";
    $stad = isset($_POST['Stad_nu']) ? trim((string) $_POST['Stad_nu']) : "";
    $org = isset($_POST['Org_nu']) ? trim((string) $_POST['Org_nu']) : "";
    $landkod = isset($_POST['Land_kod_nu']) ? trim((string) $_POST['Land_kod_nu']) : "";

    $sql = "
        SELECT
            ua.Name_en AS Name,
            ua.City AS City,
            ua.Country_name AS Country_name,
            ua.Org_type_code AS Org_type_code
        FROM Unified_address ua
        WHERE ua.Country_name = :land
            AND UPPER(ua.Name_en) = UPPER(:org)
    ";

    if (strlen($stad) > 0) {
        $sql .= " AND UPPER(ua.City) = UPPER(:stad)";
    }

    $stmt = $dbh->prepare($sql);
    $stmt->bindValue(":land", $land, PDO::PARAM_STR);
    $stmt->bindValue(":org", $org, PDO::PARAM_STR);

    if (strlen($stad) > 0) {
        $stmt->bindValue(":stad", $stad, PDO::PARAM_STR);
    }

    $stmt->execute();
    
    echo "<table border='1'>";

    // Rubrikerna
    echo "<tr>";
    echo "<th>Organisation</th> <th>Stad</th> <th>Land</th> <th>Organisationstyp</th>";
     echo "</tr>";

    // Lägg ut resultatet
    foreach ($stmt as $row) {
            echo "<tr>";  
            echo "<td>" . $row['Name'] . "</td>";
            echo "<td>" . $row['City'] . "</td>";
            echo "<td>" . $row['Country_name'] . "</td>";
            echo "<td>" . $row['Org_type_code'] . "</td>";
            echo "</tr>";
    }

    echo "</table>";
    echo "<br /><br /><br />";

?>

</body>
</html>