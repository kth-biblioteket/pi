<?php session_start(); ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml11/DTD/xhtml-transitional.dtd">

<! Författare: Cecilia Wiklander>
<! Syfte: Adressrättnings-hantering>
<! Ändringar: >

<head>
    
    <meta charset="utf-8">

    <title>VISA REGLER ORGANISATIONSTYP UTAN TRÄFF</title>

    <link href="Site.css" rel="stylesheet">

</head>

<body>

<?php include('include_head_new.html'); ?>

<h2>VISA REGLER ORGANISATIONSTYP UTAN TRÄFF</h2>

<a href='regel_organisation_typ.php'>TILL SÖKNING</a>
</br>
</br>

<?php

    $username = $_SESSION['anv'];
    $password = $_SESSION['ord'];
    $hostname = $_SESSION['hnamn'];
    $dbname = $_SESSION['dbnamn'];

    $dbh = new PDO("sqlsrv:Server=$hostname;Database=$dbname",$username,$password);

    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "SELECT COUNT(*) AS Antal FROM Rule_org_type_match r WHERE NOT EXISTS (SELECT * FROM Unified_address ua WHERE ua.R_o_t_m_id = r.R_o_t_m_id)";

	// Execute it, or let it throw an error message if there's a problem.

	$stmt = $dbh->query( $sql );

	foreach ($stmt as $row) {
        echo "Antal funna: " . $row['Antal'];
    }

    echo "<br /><br />";

    $sql = "SELECT R_o_t_m_id,Find_country,Find_city,Find_org_1,Find_org_2,Find_org_not,Country,City,Org_type_code
    FROM Rule_org_type_match r WHERE NOT EXISTS (SELECT * FROM Unified_address ua WHERE ua.R_o_t_m_id = r.R_o_t_m_id)";

	// Execute it, or let it throw an error message if there's a problem.

	$stmt = $dbh->query( $sql );
	
	echo "<table border='1'>";

	// Rubrikerna
	echo "<tr>";
	echo "<th>Ändra</th> <th>Ta bort</th><th>Land</th> <th>Stad</th> <th>Organisation 1</th> <th>Organisation 2</th> <th>Organisation ej</th>  
        <th>Land</th> <th>Stad</th> <th>Orgtypkod</th> <th>Regel-id</th>";
	echo "</tr>";
		 
	// Lägg ut resultatet
	foreach ($stmt as $row) {
			echo "<tr>";
			echo "<td><a href=aendra_regel_o_typ.php?Regel_id=" . $row['R_o_t_m_id'] . ">ÄNDRA</a></td>";
			echo "<td><a href=ta_bort_regel_o_typ.php?Regel_id=" . $row['R_o_t_m_id'] . ">TA BORT</a></td>";  
			echo "<td style = 'white-space:PRE'>" . $row['Find_country'] . "</td>";
			echo "<td style = 'white-space:PRE'>" . $row['Find_city'] . "</td>";
			echo "<td style = 'white-space:PRE'>" . $row['Find_org_1'] . "</td>"; 
			echo "<td style = 'white-space:PRE'>" . $row['Find_org_2'] . "</td>";
			echo "<td style = 'white-space:PRE'>" . $row['Find_org_not'] . "</td>";                               
			echo "<td style = 'white-space:PRE'>" . $row['Country'] . "</td>";
			echo "<td style = 'white-space:PRE'>" . $row['City'] . "</td>";
			echo "<td style = 'white-space:PRE'>" . $row['Org_type_code'] . "</td>"; 
			echo "<td style = 'white-space:PRE'>" . $row['R_o_t_m_id'] . "</td>";                                                                            						
			echo "</tr>";
	}

	echo "</table>";
	echo "<br /><br /><br />";

?>

</body>
</html>