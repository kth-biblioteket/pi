<?php session_start(); ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml11/DTD/xhtml-transitional.dtd">

<! Författare: Cecilia Wiklander>
<! Syfte: Adressrättnings-hantering>
<! Ändringar: >

<head>

    <meta charset="utf-8">

    <title>VISA REGLER ORGANISATIONSNAMN</title>

    <link href="Site.css" rel="stylesheet">

</head>

<body>

<?php include('include_head_new.html'); ?>

<h2>VISA REGLER ORGANISATIONSNAMN</h2>

<a href='organisationsnamn.php'>TILL SÖKNING</a>&nbsp;&nbsp;<a href='visa_organisation.php'>TILL SÖKRESULTAT</a>
</br>
</br>

<?php

    $u_org_id = $_GET["Unified_org_id"];

    $username = $_SESSION['anv'];
    $password = $_SESSION['ord'];
    $hostname = $_SESSION['hnamn'];
    $dbname = $_SESSION['dbnamn'];
    $orgid = $_SESSION['orgid'];
    echo $orgid;

    $dbh = new PDO("sqlsrv:Server=$hostname;Database=$dbname",$username,$password);

    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql_1 = "SELECT COUNT(*) AS Antal
    FROM Rule_center_match 
    WHERE Org_id_1 = " . $u_org_id . " OR Org_id_2 = " . $u_org_id ." OR Org_id_3 = " . $u_org_id;
    $stmt = $dbh->query( $sql_1 ); 
	foreach ($stmt as $row) {
		$antal_1 = $row['Antal'];
    }

    $sql_2 = "SELECT COUNT(*) AS Antal    
    FROM Rule_org_match WHERE Org_id_1 = " . $u_org_id . " OR Org_id_2 = " . $u_org_id ." OR Org_id_3 = " . $u_org_id; 
    $stmt = $dbh->query( $sql_2 ); 
	foreach ($stmt as $row) {
		$antal_2 =  $row['Antal'];
    }

    $sql_3 = "SELECT COUNT(*) AS Antal   
    FROM Rule_full_address_match WHERE Org_id_1 = " . $u_org_id . " OR Org_id_2 = " . $u_org_id ." OR Org_id_3 = " . $u_org_id;
    $stmt = $dbh->query( $sql_3 ); 
	foreach ($stmt as $row) {
		$antal_3 =  $row['Antal'];
    }

    $ant_regler = $antal_1 + $antal_2 + $antal_3;

    $sql_n = "SELECT Name_en FROM Unified_org_names WHERE Unified_org_id = " . $u_org_id . ""; 
 
    $stmt = $dbh->query( $sql_n );

    echo '<b>' . 'Organisation: ' . '</b>' . $u_org_id . ' ';
	foreach ($stmt as $row) {
		echo $row['Name_en'];
    }
    echo '&nbsp;&nbsp;&nbsp;&nbsp;' . '<b>' .' Antal regler: ' . '</b>' . $ant_regler;
    echo "<br /><br />";

    $sql_c = "SELECT 'C' AS Regeltyp,R_c_m_id AS Regelid,Find_country,Find_city,Find_org AS Find_org_or_str_1,'' AS Find_str_2, '' AS Find_str_3, '' AS Find_str_not_1, '' AS Find_str_not_2,
    Divide,Country_1,City_1,Org_id_1 AS Un_org_id_1,
    (SELECT Name_en FROM Unified_org_names WHERE Unified_org_id = Org_id_1) AS Un_org_1,    
    Country_2,City_2,Org_id_2 AS Un_org_id_2,
    (SELECT Name_en FROM Unified_org_names WHERE Unified_org_id = Org_id_2) AS Un_org_2,    
    Country_3,City_3,Org_id_3 AS Un_org_id_3,
    (SELECT Name_en FROM Unified_org_names WHERE Unified_org_id = Org_id_3) AS Un_org_3     
    FROM Rule_center_match WHERE Org_id_1 = " . $u_org_id . " OR Org_id_2 = " . $u_org_id ." OR Org_id_3 = " . $u_org_id; 

    $sql_o = "SELECT 'O' AS Regeltyp,R_o_m_id AS Regelid,Find_country,Find_city,Find_org AS Find_org_or_str_1,'' AS Find_str_2, '' AS Find_str_3, '' AS Find_str_not_1, '' AS Find_str_not_2,
    Divide,Country_1,City_1,Org_id_1 AS Un_org_id_1,
    (SELECT Name_en FROM Unified_org_names WHERE Unified_org_id = Org_id_1) AS Un_org_1,    
    Country_2,City_2,Org_id_2 AS Un_org_id_2,
    (SELECT Name_en FROM Unified_org_names WHERE Unified_org_id = Org_id_2) AS Un_org_2,    
    Country_3,City_3,Org_id_3 AS Un_org_id_3,
    (SELECT Name_en FROM Unified_org_names WHERE Unified_org_id = Org_id_3) AS Un_org_3     
    FROM Rule_org_match WHERE Org_id_1 = " . $u_org_id . " OR Org_id_2 = " . $u_org_id ." OR Org_id_3 = " . $u_org_id; 

    $sql_fa = "SELECT 'FA' AS Regeltyp,R_f_a_m_id AS Regelid,Find_country,Find_city,Find_str_1 AS Find_org_or_str_1, Find_str_2, Find_str_3, Find_str_not_1, Find_str_not_2,
    Divide,Country_1,City_1,Org_id_1 AS Un_org_id_1,
    (SELECT Name_en FROM Unified_org_names WHERE Unified_org_id = Org_id_1) AS Un_org_1,    
    Country_2,City_2,Org_id_2 AS Un_org_id_2,
    (SELECT Name_en FROM Unified_org_names WHERE Unified_org_id = Org_id_2) AS Un_org_2,    
    Country_3,City_3,Org_id_3 AS Un_org_id_3,
    (SELECT Name_en FROM Unified_org_names WHERE Unified_org_id = Org_id_3) AS Un_org_3     
    FROM Rule_full_address_match WHERE Org_id_1 = " . $u_org_id . " OR Org_id_2 = " . $u_org_id ." OR Org_id_3 = " . $u_org_id;

    $sql = $sql_fa . " UNION " . $sql_o . " UNION " . $sql_c . "ORDER BY Regeltyp DESC,Find_org_or_str_1";

    $stmt = $dbh->query( $sql );
	
	echo "<table border='1'>";

	// Rubrikerna
	echo "<tr>";
	echo "<th>Regeltyp</th> <th>Regelid</th> <th>Land</th> <th>Stad</th> <th>Org eller sträng 1</th> <th>Sträng 2</th> <th>Sträng 3</th> <th>Ej sträng 1</th> <th>Ej sträng 2</th> <th>Delas</th>  
        <th>Land 1</th> <th>Stad 1</th> <th>Un_org_id 1</th> <th>Un_org 1</th> <th>Land 2</th> <th>Stad 2</th> <th>Un_org_id 2</th> <th>Un_org 2</th> 
        <th>Land 3</th> <th>Stad 3</th> <th>Un_org_id 3</th> <th>Un_org 3</th>";
	echo "</tr>";
		 
	// Lägg ut resultatet
	foreach ($stmt as $row) {
			echo "<tr>"; 
			echo "<td>" . $row['Regeltyp'] . "</td>";
			echo "<td>" . $row['Regelid'] . "</td>";
			echo "<td style = 'white-space:PRE'>" . $row['Find_country'] . "</td>";
			echo "<td style = 'white-space:PRE'>" . $row['Find_city'] . "</td>";
			echo "<td style = 'white-space:PRE'>" . $row['Find_org_or_str_1'] . "</td>";    
			echo "<td style = 'white-space:PRE'>" . $row['Find_str_2'] . "</td>";
			echo "<td style = 'white-space:PRE'>" . $row['Find_str_3'] . "</td>";
    			echo "<td style = 'white-space:PRE'>" . $row['Find_str_not_1'] . "</td>";
			echo "<td style = 'white-space:PRE'>" . $row['Find_str_not_2'] . "</td>";
			echo "<td>" . $row['Divide'] . "</td>";
			echo "<td style = 'white-space:PRE'>" . $row['Country_1'] . "</td>";
			echo "<td style = 'white-space:PRE'>" . $row['City_1'] . "</td>";
			echo "<td>" . $row['Un_org_id_1'] . "</td>";  
			echo "<td style = 'white-space:PRE'>" . $row['Un_org_1'] . "</td>";                  
			echo "<td style = 'white-space:PRE'>" . $row['Country_2'] . "</td>";
			echo "<td style = 'white-space:PRE'>" . $row['City_2'] . "</td>";
			echo "<td>" . $row['Un_org_id_2'] . "</td>";
			echo "<td style = 'white-space:PRE'>" . $row['Un_org_2'] . "</td>";  
			echo "<td style = 'white-space:PRE'>" . $row['Country_3'] . "</td>";        
			echo "<td style = 'white-space:PRE'>" . $row['City_3'] . "</td>";
			echo "<td>" . $row['Un_org_id_3'] . "</td>";
			echo "<td style = 'white-space:PRE'>" . $row['Un_org_3'] . "</td>";                                                                               						
			echo "</tr>";
	}

	echo "</table>";
	echo "<br /><br /><br />";

?>

</body>
</html>