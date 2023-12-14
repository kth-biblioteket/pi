<?php session_start(); ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml11/DTD/xhtml-transitional.dtd">

<! Författare: Cecilia Wiklander>
<! Syfte: Adressrättnings-hantering>
<! Ändringar: >

<head>

    <meta charset="utf-8">

    <title>VISA REGLER ORGANISATIONSTYP</title>

    <link href="Site.css" rel="stylesheet">

</head>

<body>

<?php include('include_head_new.html'); ?>

<h2>VISA REGLER ORGANISATIONSTYP</h2>

<a href='regel_organisation_typ.php'>TILL SÖKNING</a>
</br>
</br>

<?php

    $land = $_POST['Land'];
    $stad = $_POST['Stad'];
    $org_1 = $_POST['Org_1'];
    $org_2 = $_POST['Org_2'];
    $org_ej = $_POST['Org_ej'];
    $regelid = $_POST['Regelid'];

    $username = $_SESSION['anv'];
    $password = $_SESSION['ord'];
    $hostname = $_SESSION['hnamn'];
    $dbname = $_SESSION['dbnamn'];

    $dbh = new PDO("sqlsrv:Server=$hostname;Database=$dbname",$username,$password);

    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $Sk = "'";
    $Ers = "''";

    $stad = str_replace($Sk, $Ers, $stad_s);
    $org = str_replace($Sk, $Ers, $org_s);
    $land = str_replace($Sk, $Ers, $land);

    $sqldel = "";

	$sql = "SELECT R_o_t_m_id,Find_country,Find_city,Find_org_1,Find_org_2,Find_org_not,Country,City,Org_type_code,Rule_date 
        FROM Rule_org_type_match WHERE "; 

    if (strlen($regelid) > 0) 
    {
	$sqldel .= "R_o_t_m_id = " . $regelid;
    }
    else 
    {

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
     if (strlen($org_1) > 0)
    {
        if (strlen($sqldel) > 0)
    	{
    		$sqldel .= " AND upper(Find_org_1) like upper('%$org_1%')";
    	}	
        else
        {
		$sqldel .= " upper(Find_org_1) like upper('%$org_1%')";        
        }
    }
     if (strlen($org_2) > 0)
    {
        if (strlen($sqldel) > 0)
    	{
    		$sqldel .= " AND upper(Find_org_2) like upper('%$org_2%')";
    	}	
        else
        {
		$sqldel .= " upper(Find_org_2) like upper('%$org_2%')";        
        }
    }
     if (strlen($org_ej) > 0)
    {
        if (strlen($sqldel) > 0)
    	{
    		$sqldel .= " AND upper(Find_org_not) like upper('%$org_ej%')";
    	}	
        else
        {
		$sqldel .= " upper(Find_org_not) like upper('%$org_ej%')";        
        }
    }
    if (strlen($sqldel) == 0)
    {
    	$sqldel .= "1=1";        
    }

    }
    
    $sql .= $sqldel;

	// Execute it, or let it throw an error message if there's a problem.

	$stmt = $dbh->query( $sql );
	
	echo "<table border='1'>";

	// Rubrikerna
	echo "<tr>";
	echo "<th>Ändra</th> <th>Ta bort</th><th>Land</th> <th>Stad</th> <th>Organisation, sträng 1</th> <th>Organisation, sträng 2</th>  <th>Organisation, sträng ej</th>  
        <th>Land</th> <th>Stad</th> <th>Orgtypkod</th> <th>Regel-id</th>  <th>Datum</th>";
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
			echo "<td style = 'white-space:PRE'>" . $row['Rule_date'] . "</td>";  
			echo "<td><a href=aendra_regel_o_typ_xxx.php?Regel_id=" . $row['R_o_t_m_id'] . ">TESTA</a></td>";                                            						
			echo "</tr>";
	}

	echo "</table>";
	echo "<br /><br /><br />";

?>

</body>
</html>