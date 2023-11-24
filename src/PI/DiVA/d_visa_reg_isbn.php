<?php 
    session_start(); 
    require_once('config.php.inc');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml11/DTD/xhtml-transitional.dtd">

<! Författare: Cecilia Wiklander>
<! Syfte: ISBN-hantering>
<! Ändringar: >

<head>

    <meta charset="utf-8">

    <title>REGISTRERADE UPPGIFTER OM ISBN</title>

    <link href="Site_utan_storlek.css" rel="stylesheet">

</head>

<body>

<?php include('include_isbn.html'); ?>

<h2>REGISTRERADE UPPGIFTER OM ISBN</h2>

<a href='d_soek_isbn.php'>TILL SÖKNING</a>
<br /><br />

<?php

    $ISBN = $_POST['ISBN'];
    $Titel = $_POST['Titel'];
    $Fnamn = $_POST['Fnamn'];
    $Enamn = $_POST['Enamn'];
    $Period = $_POST['Period'];
    $Datum_F = $_POST['Datum_F'];
    $Datum_T = $_POST['Datum_T'];
    $KTH_id = $_POST['KTH-id'];

    $username = $_SESSION['anv'];
    $password = $_SESSION['ord'];
    $dbname = "hant_isbn";

    $Sk = "'";
    $Ers = "''";

    $Fnamn = str_replace($Sk, $Ers, $Fnamn);
    $Enamn = str_replace($Sk, $Ers, $Enamn);
    $Titel = str_replace($Sk, $Ers, $Titel);

    $sql = "SELECT * FROM reg_isbn WHERE ";

    $sqldel = "";

    if (strlen($Titel) > 0) {
        if (strlen($sqldel) > 0) {
            $sqldel .= " AND UPPER(Titel) LIKE UPPER(CONCAT('%',:Titel,'%'))";
        }
        else {
            $sqldel .= " UPPER(Titel) LIKE UPPER(CONCAT('%',:Titel,'%'))";            
        }  
    }
    if (strlen($Fnamn) > 0) {
        if (strlen($sqldel) > 0) {
            $sqldel .= " AND UPPER(Fnamn) LIKE UPPER(CONCAT('%',:Fnamn,'%'))";
        }
        else {
            $sqldel .= " UPPER(Fnamn) LIKE UPPER(CONCAT('%',:Fnamn,'%'))";            
        }  
    }
    if (strlen($Enamn) > 0) {
        if (strlen($sqldel) > 0) {
            $sqldel .= " AND UPPER(Enamn) LIKE UPPER(CONCAT('%',:Enamn,'%'))";
        }
        else {
            $sqldel .= " UPPER(Enamn) LIKE UPPER(CONCAT('%',:Enamn,'%'))";            
        }  
    }
    if (strlen($ISBN) > 0 || strlen($KTH_id) > 0 ) {
        if (strlen($sqldel) > 0) {
	    if (strlen($ISBN) > 0) {
            	$sqldel .= " AND ISBN = :ISBN";
	    }
	    if (strlen($KTH_id) > 0) {
            	$sqldel .= " AND KTH_id = :KTH_id";
	    } 
        }
        else {
            if (strlen($ISBN) > 0) {
            	$sqldel .= " ISBN = :ISBN";
            }
            if (strlen($KTH_id) > 0) {
	    	if (strlen($sqldel) > 0) {
            		$sqldel .= " AND KTH_id = :KTH_id";
            	}
            	else {
            		$sqldel .= " KTH_id = :KTH_id"; 
            	}  
            }          
        }  
    }
    else {
    	if (strlen($Period) > 0) {
        	if (strlen($sqldel) > 0) {
            		$sqldel .= " AND";
	    	}
            	if ($Period == 'datumintervall') {
            		$sqldel .= " Regdatum >= :Datum_F AND Regdatum <= :Datum_T";
        	}
        	if ($Period == 'tremaanad') {
            		$sqldel .= " Regdatum > DATE_SUB(CURDATE(), INTERVAL 3 MONTH)";
        	}        	
        	if ($Period == 'senastemaanad') {
            		$sqldel .= " Regdatum > DATE_SUB(CURDATE(), INTERVAL 1 MONTH)";
        	}
        	if ($Period == 'idag') {
            		$sqldel .= " Regdatum = CURDATE()";             
        	}
        	else if ($Period == 'igaar') {
            		$sqldel .= " Regdatum = DATE_SUB(CURDATE(), INTERVAL 1 DAY)";            
        	}
        	else if ($Period == 'senastevecka') {
            		$sqldel .= " Regdatum > DATE_SUB(CURDATE(), INTERVAL 7 DAY)";            
        	}
        	else {
            		$sqldel .= "";            
        	}
    	}
    }
    if (strlen($sqldel) == 0)
    {
    	$sqldel .= "1=1";        
    }

    $sql .= $sqldel;
   
    try {
        $pdo = new PDO("mysql:host=$hostname;dbname=$dbname", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $stmt = $pdo->prepare($sql);
        if (strlen($Titel) > 0) {
        $stmt->bindParam(':Titel', $Titel);
        }
        if (strlen($Fnamn) > 0) {
        $stmt->bindParam(':Fnamn', $Fnamn);
        }
        if (strlen($Enamn) > 0) {
        $stmt->bindParam(':Enamn', $Enamn);
        }        
        if (strlen($ISBN) > 0) {
        $stmt->bindParam(':ISBN', $ISBN);
        }
        if (strlen($KTH_id) > 0) {
        $stmt->bindParam(':KTH_id', $KTH_id);
        }
        if (strlen($Datum_F) > 0) {
        $stmt->bindParam(':Datum_F', $Datum_F);
        }
        if (strlen($Datum_T) > 0) {
        $stmt->bindParam(':Datum_T', $Datum_T);
        }
        
        $stmt->execute();
        //echo $sql;
    } 
    catch (PDOException $e) {
          echo '<script language="javascript">';
          echo 'alert("Fel vid sökning mot databasen!")';
          echo '</script>';
    }
	
	echo "<table border='1'>";

	// Rubrikerna
	echo "<tr>";
	echo "<th>Välj</th> <th>ISBN</th> <th>Pubtyp</th> <th>Forskare</th> <th>Titel</th> ";
	echo "</tr>";
		 
	// Lägg ut resultatet
	foreach ($stmt as $row) {
			echo "<tr>";
			echo "<td><a href=d_visa_valt_isbn.php?ISBN=" . $row['ISBN'] . ">VÄLJ</a></td>";  
			echo "<td>" . $row['ISBN'] . "</td>";
			echo "<td>" . $row['Pubtyp'] . "</td>";
			echo "<td>" . $row['Enamn'] . ", " . $row['Fnamn'] . "</td>";        
			echo "<td>" . $row['Titel'] . "</td>";                                                             						
			echo "</tr>";
	}

	echo "</table>";
	echo "<br /><br /><br />";

?>

</body>
</html>