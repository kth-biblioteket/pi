<?php session_start(); ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml11/DTD/xhtml-transitional.dtd">

<! Författare: Cecilia Wiklander>
<! Syfte: Adressrättnings-hantering>
<! Ändringar: >

<head>

    <meta charset="utf-8">

    <title>VISA ORGANISATIONSNAMN</title>

    <link href="Site.css" rel="stylesheet">

</head>

<body>

<?php include('include_head_new.html'); ?>

<h2>VISA ORGANISATIONSNAMN</h2>

<a href='organisationsnamn.php'>TILL SÖKNING</a>
<br /><br />

<?php

    $land = $_POST['Land'];
    $lok_namn = $_POST['Lokaltnamn'];
    $eng_namn = $_POST['Engelsktnamn'];
    $orgtyp = $_POST['Orgtyp'];
    $orgid = $_POST['Orgid'];
    $rorid = $_POST['RORid'];
    $kommentar = $_POST['Kommentar'];

    $username = $_SESSION['anv'];
    $password = $_SESSION['ord'];
    $hostname = $_SESSION['hnamn'];
    $dbname = $_SESSION['dbnamn'];

    if (isset($_POST['exaktkoll'])) {
       $exaktkoll = true;
    }

    $Sk = "'";
    $Ers = "''";

    $lok_namn = str_replace($Sk, $Ers, $lok_namn);
    $eng_namn = str_replace($Sk, $Ers, $eng_namn);
    $kommentar = str_replace($Sk, $Ers, $kommentar);

    $sqldel = "";

    $dbh = new PDO("sqlsrv:Server=$hostname;Database=$dbname",$username,$password);

    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (strlen($land) > 0 || strlen($lok_namn) > 0 || strlen($eng_namn) > 0 || strlen($orgtyp) > 0 || strlen($orgid) > 0 || strlen($rorid) > 0) {
        $_SESSION['land'] = $land;
        $_SESSION['lokaltnamn'] = $lok_namn;
        $_SESSION['engelsktnamn'] = $eng_namn;
        $_SESSION['orgtyp'] = $orgtyp;
        $_SESSION['orgid'] = $orgid;
        $_SESSION['RORid'] = $rorid;
        $_SESSION['Kommentar'] = $kommentar;
    }
    else {
    $username = $_SESSION['anv']; 
        $land = $_SESSION['land'];
        $lok_namn = $_SESSION['lokaltnamn'];
        $eng_namn = $_SESSION['engelsktnamn'];
        $orgtyp = $_SESSION['orgtyp'];
        $orgid = $_SESSION['orgid']; 
        $rorid = $_SESSION['RORid']; 
        $kommentar = $_SESSION['Kommentar'];           
    }

	// Write out our query.

	$sql = "SELECT Unified_org_id,Name_local,Name_en,Country_name,Org_type_code,Comment,ROR_id FROM unified_org_names WHERE ";
     
    if (strlen($orgid) > 0) 
    {
	$sqldel .= "Unified_org_id = " . $orgid;
    }
    else 
    {

    if (strlen($land) > 0 && $land != 'Ange land')
    {
        $sqldel .= "Country_name = '" . $land . "'";
    }
    if (strlen($lok_namn) > 0)
    {
        if (strlen($sqldel) > 0)
    	{
           if ($exaktkoll) {
    		$sqldel .= " AND upper(Name_local) like upper('%$lok_namn%')";
           }
           else {
    		$sqldel .= " AND upper(Name_local) COLLATE Latin1_General_CI_AI like upper('%$lok_namn%')";
           }

    	}	
        else
        {
           if ($exaktkoll) {
		$sqldel .= " upper(Name_local) like upper('%$lok_namn%')";  
           }
           else {
		$sqldel .= " upper(Name_local) COLLATE Latin1_General_CI_AI like upper('%$lok_namn%')";
           }
  
        }
    }
    if (strlen($eng_namn) > 0)
    {
        if (strlen($sqldel) > 0)
    	{
           if ($exaktkoll) {
    		$sqldel .= " AND upper(Name_en) like upper('%$eng_namn%')";
           }
           else {
    		$sqldel .= " AND upper(Name_en) COLLATE Latin1_General_CI_AI like upper('%$eng_namn%')";
           }

    	}	
        else
        {
           if ($exaktkoll) {
		$sqldel .= " upper(Name_en) like upper('%$eng_namn%')"; 
           }
           else {
		$sqldel .= " upper(Name_en) COLLATE Latin1_General_CI_AI like upper('%$eng_namn%')";
           }
       
        }
    }
    if (strlen($orgtyp) > 0)
    {
        if (strlen($sqldel) > 0)
    	{
    		$sqldel .= " AND upper(Org_type_code) like upper('%$orgtyp%')";
    	}	
        else
        {
		$sqldel .= " upper(Org_type_code) like upper('%$orgtyp%')";        
        }
    }
    if (strlen($rorid) > 0)
    {
        if (strlen($sqldel) > 0)
    	{
    		$sqldel .= " AND upper(ROR_id) like upper('%$rorid%')";
    	}	
        else
        {
		$sqldel .= " upper(ROR_id) like upper('%$rorid%')";        
        }
    }
    if (strlen($kommentar) > 0)
    {
        if (strlen($sqldel) > 0)
    	{
    		$sqldel .= " AND upper(Comment) like upper('%$kommentar%')";
    	}	
        else
        {
		$sqldel .= " upper(Comment) like upper('%$kommentar%')";        
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
	echo "<th>Regler</th> <th>Ändra</th> <th>Ta bort</th> <th>Lokalt namn</th> <th>Engelskt namn</th> <th>Land</th> <th>Org.typ</th> <th>Kommentar</th> <th>Orgid</th> <th>ROR-id</th>";
	echo "</tr>";
		 
	// Lägg ut resultatet
	foreach ($stmt as $row) {
			echo "<tr>";
			echo "<td><a href=visa_regler_unorgid.php?Unified_org_id=" . $row['Unified_org_id'] . ">VISA</a></td>";
			echo "<td><a href=aendra_organisation.php?Unified_org_id=" . $row['Unified_org_id'] . ">ÄNDRA</a></td>";
			echo "<td><a href=ta_bort_organisation.php?Unified_org_id=" . $row['Unified_org_id'] . ">TA BORT</a></td>";  
			echo "<td style = 'white-space:PRE'>" . $row['Name_local'] . "</td>";
			echo "<td style = 'white-space:PRE'>" . $row['Name_en'] . "</td>";
			echo "<td style = 'white-space:PRE'>" . $row['Country_name'] . "</td>";        
			echo "<td style = 'white-space:PRE'>" . $row['Org_type_code'] . "</td>";
			echo "<td style = 'white-space:PRE'>" . $row['Comment'] . "</td>";
			echo "<td style = 'white-space:PRE'>" . $row['Unified_org_id'] . "</td>";
			echo "<td style = 'white-space:PRE'>" . $row['ROR_id'] . "</td>";                                                                            						
			echo "</tr>";
	}

	echo "</table>";
	echo "<br /><br /><br />";

?>

</body>
</html>