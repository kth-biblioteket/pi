<?php session_start(); ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml11/DTD/xhtml-transitional.dtd">

<! Författare: Cecilia Wiklander>
<! Syfte: Adressrättnings-hantering>
<! Ändringar: >

<head>
    
    <meta charset="utf-8">

    <title>VISA REGLER FULL ADRESS UTAN TRÄFF</title>

    <link href="Site.css" rel="stylesheet">

</head>

<body>

<?php include('include_head_new.html'); ?>

<h2>VISA REGLER FULL ADRESS UTAN TRÄFF</h2>

<a href='regel_full_adress.php'>TILL SÖKNING</a>
</br>
</br>

<?php

    $username = $_SESSION['anv'];
    $password = $_SESSION['ord'];
    $hostname = $_SESSION['hnamn'];
    $dbname = $_SESSION['dbnamn'];

    $dbh = new PDO("sqlsrv:Server=$hostname;Database=$dbname",$username,$password);

    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "SELECT COUNT(*) AS Antal FROM Rule_full_address_match r WHERE NOT EXISTS (SELECT * FROM Unified_address ua WHERE ua.R_f_a_m_id = r.R_f_a_m_id)";

	// Execute it, or let it throw an error message if there's a problem.

	$stmt = $dbh->query( $sql );

	foreach ($stmt as $row) {
        echo "Antal funna: " . $row['Antal'];
    }
    
    echo "<br /><br />";
    
	$sql = "SELECT r.R_f_a_m_id,r.Find_country,r.Find_city,r.Find_str_1,r.Find_str_2,r.Find_str_3,r.Find_str_not_1,r.Find_str_not_2,r.Divide,r.Country_1,r.City_1,r.Org_id_1,
    (SELECT Name_en FROM Unified_org_names WHERE Unified_org_id = Org_id_1) AS Org_1,    
    r.Country_2,r.City_2,r.Org_id_2,
    (SELECT Name_en FROM Unified_org_names WHERE Unified_org_id = Org_id_2) AS Org_2,    
    r.Country_3,r.City_3,r.Org_id_3,
    (SELECT Name_en FROM Unified_org_names WHERE Unified_org_id = Org_id_3) AS Org_3     
    FROM Rule_full_address_match r WHERE NOT EXISTS (SELECT * FROM Unified_address ua WHERE ua.R_f_a_m_id = r.R_f_a_m_id)";
    
	// Execute it, or let it throw an error message if there's a problem.

	$stmt = $dbh->query( $sql );

	echo "<table border='1'>";

	// Rubrikerna
	echo "<tr>";
	echo "<th>Ändra</th> <th>Ta bort</th><th>Land</th> <th>Stad</th> 
        <th>Söksträng 1</th> <th>Söksträng 2</th> <th>Söksträng 3</th> <th>Söksträng ej 1</th> <th>Söksträng ej 2</th> <th>Delas</th>  
        <th>Land 1</th> <th>Stad 1</th> <th>Org_id 1</th> <th>Org 1</th> <th>Land 2</th> <th>Stad 2</th> <th>Org_id 2</th> <th>Org 2</th> 
        <th>Land 3</th> <th>Stad 3</th> <th>Org_id 3</th> <th>Org 3</th> <th>Regelid</th>";
	echo "</tr>";
		 
	// Lägg ut resultatet
	foreach ($stmt as $row) {
			echo "<tr>";
			echo "<td><a href=aendra_regel_f_a.php?Regel_id=" . $row['R_f_a_m_id'] . ">ÄNDRA</a></td>";
			echo "<td><a href=ta_bort_regel_f_a.php?Regel_id=" . $row['R_f_a_m_id'] . ">TA BORT</a></td>";  
			echo "<td style = 'white-space:PRE'>" . $row['Find_country'] . "</td>";
			echo "<td style = 'white-space:PRE'>" . $row['Find_city'] . "</td>";
			echo "<td style = 'white-space:PRE'>" . $row['Find_str_1'] . "</td>"; 
			echo "<td style = 'white-space:PRE'>" . $row['Find_str_2'] . "</td>"; 
			echo "<td style = 'white-space:PRE'>" . $row['Find_str_3'] . "</td>"; 
			echo "<td style = 'white-space:PRE'>" . $row['Find_str_not_1'] . "</td>"; 
			echo "<td style = 'white-space:PRE'>" . $row['Find_str_not_2'] . "</td>";                                                        
			echo "<td>" . $row['Divide'] . "</td>";
			echo "<td style = 'white-space:PRE'>" . $row['Country_1'] . "</td>";
			echo "<td style = 'white-space:PRE'>" . $row['City_1'] . "</td>";
			echo "<td>" . $row['Org_id_1'] . "</td>"; 
            		echo "<td style = 'white-space:PRE'>" . $row['Org_1'] . "</td>";                    
			echo "<td style = 'white-space:PRE'>" . $row['Country_2'] . "</td>";
			echo "<td style = 'white-space:PRE'>" . $row['City_2'] . "</td>";
			echo "<td>" . $row['Org_id_2'] . "</td>";
            		echo "<td style = 'white-space:PRE'>" . $row['Org_2'] . "</td>"; 
			echo "<td style = 'white-space:PRE'>" . $row['Country_3'] . "</td>";        
			echo "<td style = 'white-space:PRE'>" . $row['City_3'] . "</td>";
			echo "<td>" . $row['Org_id_3'] . "</td>";
            		echo "<td style = 'white-space:PRE'>" . $row['Org_3'] . "</td>";      
           		echo "<td>" . $row['R_f_a_m_id'] . "</td>";                                                                              						
			echo "</tr>";
	}

	echo "</table>";
	echo "<br /><br /><br />";

?>

</body>
</html>