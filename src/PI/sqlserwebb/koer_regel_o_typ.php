<?php session_start(); ?>

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

    $land = $_POST['Land_ut'];
    $stad = $_POST['Stad_nu'];
    $org = $_POST['Org_nu'];
    $landkod = $_POST['Land_kod_nu'];

    $username = $_SESSION['anv'];
    $password = $_SESSION['ord'];
    $hostname = $_SESSION['hnamn'];
    $dbname = $_SESSION['dbnamn'];

    $dbh = new PDO("sqlsrv:Server=$hostname;Database=$dbname",$username,$password);

    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $Sk = "'";
    $Ers = "''";

    $stad = str_replace($Sk, $Ers, $stad);
    $org = str_replace($Sk, $Ers, $org);

    $sqldel = "";

	$sql = "SELECT o.Name,o.City,o.Country_name,o.Org_type_code FROM Organization o, Country c 
	WHERE o.Country_code = c.Country_code AND c.Display_name = '" 
	. $land . "' AND UPPER(o.Name) = UPPER('" . $org . "')"; 

    if (strlen($stad) > 0)
    {
		$sqldel = " AND UPPER(City) = UPPER('" . $stad . "')";   
    }

    $sql .= $sqldel;
    
	// Execute it, or let it throw an error message if there's a problem.
    
	$stmt = $dbh->query( $sql );
	
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