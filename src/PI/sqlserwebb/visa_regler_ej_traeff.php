<?php session_start(); ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml11/DTD/xhtml-transitional.dtd">

<! Författare: Cecilia Wiklander>
<! Syfte: Adressrättnings-hantering>
<! Ändringar: >

<head>

    <meta charset="utf-8">

    <title>LISTA REGLER SOM EJ TRÄFFAR</title>

    <link href="Site.css" rel="stylesheet">

</head>

<body>

<?php include('include_head_new.html'); ?>

<h2>LISTA REGLER SOM EJ TRÄFFAR</h2>

<a href='regel_ej_traeff.php'>TILL SÖKNING</a>
</br>
</br>

<?php

    $land = $_POST['Land'];
    $stad = $_POST['Stad'];
    $org = $_POST['Org'];

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

    $sql = "SELECT R_o_m_id,Find_country,Find_city,Find_org,Divide,Country_1,City_1,Org_id_1,Country_2,City_2,Org_id_2,Country_3,City_3,Org_id_3 
    FROM Rule_org_match WHERE "; 

    if ($land <> 'Ange land')
    {
        $sqldel .= "Country_code IN (SELECT Country_code FROM Country WHERE Display_name = '" . $land . "')";
    }
    if (strlen($stad) > 0)
    {
        if (strlen($sqldel) > 0)
    	{
    		$sqldel .= " AND upper(Find_city) like upper('%$stad%')";
    	}	
        else
        {
		$sqldel .= " upper(Find_city) like upper('%$stad%')";    
        }
    }
     if (strlen($org) > 0)
    {
        if (strlen($sqldel) > 0)
    	{
    		$sqldel .= " AND upper(Find_org) like upper('%$org%')";
    	}	
        else
        {
		$sqldel .= " upper(Find_org) like upper('%$org%')";        
        }
    }
    if (strlen($sqldel) == 0)
    {
    	$sqldel .= "1=1";        
    }

    $sql .= $sqldel;

	// Execute it, or let it throw an error message if there's a problem.

	//$stmt = $dbh->query( $sql );
	
	echo "<table border='1'>";

	// Rubrikerna
	echo "<tr>";
	echo "<th>Ändra</th> <th>Ta bort</th><th>Land</th> <th>Stad</th> <th>Organisation</th> <th>Delas</th>  
        <th>Land 1</th> <th>Stad 1</th> <th>Org_id 1</th> <th>Land 2</th> <th>Stad 2</th> <th>Org_id 2</th> <th>Land 3</th> <th>Stad 3</th> <th>Org_id 3</th>";
	echo "</tr>";
		 
	// Lägg ut resultatet
	foreach ($stmt as $row) {
			echo "<tr>";
			echo "<td><a href=aendra_regel_o.php?Regel_id=" . $row['R_o_m_id'] . ">ÄNDRA</a></td>";
			echo "<td><a href=ta_bort_regel_o.php?Regel_id=" . $row['R_o_m_id'] . ">TA BORT</a></td>";  
			echo "<td>" . $row['Find_country'] . "</td>";
			echo "<td>" . $row['Find_city'] . "</td>";
			echo "<td>" . $row['Find_org'] . "</td>";        
			echo "<td>" . $row['Divide'] . "</td>";
			echo "<td>" . $row['Country_1'] . "</td>";
			echo "<td>" . $row['City_1'] . "</td>";
			echo "<td>" . $row['Org_id_1'] . "</td>";        
			echo "<td>" . $row['Country_2'] . "</td>";
			echo "<td>" . $row['City_2'] . "</td>";
			echo "<td>" . $row['Org_id_2'] . "</td>";
			echo "<td>" . $row['Country_3'] . "</td>";        
			echo "<td>" . $row['City_3'] . "</td>";
			echo "<td>" . $row['Org_id_3'] . "</td>";                                                                 						
			echo "</tr>";
	}

	echo "</table>";
	echo "<br /><br /><br />";

?>

</body>
</html>