<?php session_start(); ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml11/DTD/xhtml-transitional.dtd">

<! Författare: Cecilia Wiklander>
<! Syfte: Adressrättnings-hantering>
<! Ändringar: >

<head>
    
    <meta charset="utf-8">

    <title>VISA REGELTRÄFFAR ORGANISATIONSTYP</title>

    <link href="Site.css" rel="stylesheet">

</head>

<body>

<?php include('include_head_new.html'); ?>

<h2>VISA REGELTRÄFFAR ORGANISATIONSTYP</h2>

<a href="regel_organisation_typ.php">TILL SÖKNING</a>
<a href="javascript:history.back()">TILL REGELN</a>

</br>
</br>

<?php

    $username = $_SESSION['anv'];
    $password = $_SESSION['ord'];
    $hostname = $_SESSION['hnamn'];
    $dbname = $_SESSION['dbnamn'];
    $regel_id = $_SESSION['regel_id'];
    
    $dbh = new PDO("sqlsrv:Server=$hostname;Database=$dbname",$username,$password);

    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "SELECT COUNT(*) AS Antal FROM Unified_address ua WHERE R_o_t_m_id = " . $regel_id . "";

	// Execute it, or let it throw an error message if there's a problem.

	$stmt = $dbh->query( $sql );

	foreach ($stmt as $row) {
        echo "Antal funna: " . $row['Antal'];
    }

    echo "<br /><br />";

    $sql = "SELECT TOP 100 Unified_address_id,Org_id,Name_en,City,Country_name,Org_type_code,Insert_date
    FROM Unified_address WHERE R_o_t_m_id = " . $regel_id . " ORDER BY NEWID()";

	// Execute it, or let it throw an error message if there's a problem.

	$stmt = $dbh->query( $sql );
	
	echo "<table border='1'>";

	// Rubrikerna
	echo "<tr>";
	echo "<th>Unified_address-id</th> <th>Org-id</th><th>Engelskt namn</th> <th>Stad</th> <th>Land</th> <th>Orgtypkod</th> <th>Körningsdatum</th>";
	echo "</tr>";
		 
	// Lägg ut resultatet
	foreach ($stmt as $row) {
			echo "<tr>"; 
			echo "<td style = 'white-space:PRE'>" . $row['Unified_address_id'] . "</td>";
			echo "<td style = 'white-space:PRE'>" . $row['Org_id'] . "</td>";
			echo "<td style = 'white-space:PRE'>" . $row['Name_en'] . "</td>"; 
			echo "<td style = 'white-space:PRE'>" . $row['City'] . "</td>";
			echo "<td style = 'white-space:PRE'>" . $row['Country_name'] . "</td>";                               
			echo "<td style = 'white-space:PRE'>" . $row['Org_type_code'] . "</td>";
			echo "<td style = 'white-space:PRE'>" . $row['Insert_date'] . "</td>";                                                                            						
			echo "</tr>";
	}

	echo "</table>";
	echo "<br /><br /><br />";

?>

</body>
</html>