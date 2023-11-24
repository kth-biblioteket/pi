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

    <title>STATISTIK ÖVER REGISTRERADE ISBN</title>

    <link href="Site_utan_storlek.css" rel="stylesheet">

</head>

<body>

<?php include('include_isbn.html'); ?>

<h2>STATISTIK ÖVER REGISTRERADE ISBN</h2>

<a href='d_isbn_meny.php'>TILL MENYN</a>

<br /><br />

<?php

    $username = $_SESSION['anv'];
    $password = $_SESSION['ord'];
    $dbname = "hant_isbn";

//    $sql = "SELECT YEAR(d.Regdatum) AS Y,MONTH(d.Regdatum) AS M,
//            SUM(CASE d.Pubtyp WHEN 'Doktorsavhandling' THEN 1 ELSE 0 END) AS D,sum(CASE d.Pubtyp WHEN 'Licentiatavhandling' THEN 1 ELSE 0 END) AS L,sum(CASE d.Pubtyp WHEN 'Rapport' THEN 1 ELSE 0 END) AS R,count(*) AS Total
//            FROM reg_isbn d INNER JOIN reg_isbn d_y ON d.ISBN = d_y.ISBN INNER JOIN reg_isbn d_n ON d.ISBN = d_n.ISBN group by Y, M";

    $sql = "SELECT YEAR(d.Regdatum) AS Y,
            (CASE MONTH(d.Regdatum) WHEN 1 THEN 'Januari' WHEN 2 THEN 'Februari' WHEN 3 THEN 'Mars' WHEN 4 THEN 'April' WHEN 5 THEN 'Maj' WHEN 6 THEN 'Juni' 
             WHEN 7 THEN 'Juli' WHEN 8 THEN 'Augusti' WHEN 9 THEN 'September' WHEN 10 THEN 'Oktober' WHEN 11 THEN 'November' ELSE 'December' END) AS M,
            SUM(CASE d.Pubtyp WHEN 'Doktorsavhandling' THEN 1 ELSE 0 END) AS D,sum(CASE d.Pubtyp WHEN 'Licentiatavhandling' THEN 1 ELSE 0 END) AS L,sum(CASE d.Pubtyp WHEN 'Rapport' THEN 1 ELSE 0 END) AS R,count(*) AS Total
            FROM reg_isbn d INNER JOIN reg_isbn d_y ON d.ISBN = d_y.ISBN INNER JOIN reg_isbn d_n ON d.ISBN = d_n.ISBN WHERE d.Regdatum > '2017-03-31'
            GROUP BY Y, M 
            ORDER BY d.Regdatum";

    try {
        $pdo = new PDO("mysql:host=$hostname;dbname=$dbname", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $pdo->prepare($sql);
        
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
	echo "<th>År</th> <th>Månad</th> <th>Doktorsavhandling</th> <th>Licentiatavhandling</th> <th>Rapport</th> <th>Total</th> ";
	echo "</tr>";
		 
	// Lägg ut resultatet
	foreach ($stmt as $row) {
			echo "<tr>";
			echo "<td>" . $row['Y'] . "</td>";
			echo "<td>" . $row['M'] . "</td>";  
			echo "<td>" . $row['D'] . "</td>";     
			echo "<td>" . $row['L'] . "</td>"; 
 			echo "<td>" . $row['R'] . "</td>";     
			echo "<td>" . $row['Total'] . "</td>";                                                                 						
			echo "</tr>";
	}

	echo "</table>"; 
	echo "<br /><br /><br />";

?>

</body>
</html>