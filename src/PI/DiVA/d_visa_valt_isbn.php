<?php 
    session_start(); 
    require_once('config.php.inc');
?>

<!DOCTYPE html PUBLIC "-//w3c//DTD XHTMLm 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<! Författare: Cecilia Wiklander>
<! Syfte: ISBN-hantering>
<! Ändringar: >

<head>

    <meta charset="utf-8">

    <title>REGISTRERAT ISBN</title>
	
    <link href="Site_utan_storlek.css" rel="stylesheet"> 
	
</head>

<body>

<?php include('include_isbn.html'); ?>

<?php
    
    $ISBN_in = $_GET["ISBN"];

    $username = $_SESSION['anv'];
    $password = $_SESSION['ord'];
    $dbname = "hant_isbn";

    try {
        $pdo = new PDO("mysql:host=$hostname;dbname=$dbname", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $stmt = $pdo->prepare("SELECT * FROM reg_isbn WHERE ISBN = :ISBN_in");
        $stmt->bindParam(':ISBN_in', $ISBN_in);
        $stmt->execute();
        
        foreach ($stmt as $row) {
                $isbn = $row['ISBN'];
                $pubtyp = $row['Pubtyp'];
                $titel = $row['Titel'];
                $fnamn = $row['Fnamn'];
                $enamn = $row['Enamn'];
                $epost = $row['Epost'];
                $dispdatum = $row['Dispdatum'];
                $regdatum = $row['Regdatum']; 
		$forskare = $fnamn . " " . $enamn;  
		$trita = $row['TRITA'];  
		$kth_id = $row['KTH_id'];   
        }

        $_SESSION['ISBN'] = $isbn;
        $_SESSION['Pubtyp'] = $pubtyp;
        $_SESSION['Titel'] = $titel;
        $_SESSION['Forskare'] = $forskare;
        $_SESSION['KTH_id'] = $kth_id;
        $_SESSION['TRITA'] = $trita;
        $_SESSION['Epost'] = $epost;
        $_SESSION['Dispdatum'] = $dispdatum;
        $_SESSION['Regdatum'] = $regdatum;
        $_SESSION['Fnamn'] = $fnamn;
        $_SESSION['Enamn'] = $enamn;

    } 
    catch (PDOException $e) {
          echo '<script language="javascript">';
          echo 'alert("Fel vid inloggning till databasen!")';
          echo '</script>';
    }

?>

<h2>REGISTRERAT ISBN</h2>	
	                                    
		    <form action="d_aterstall.php" method="post">

                <a href='d_soek_isbn.php'>TILL SÖK ISBN</a>&nbsp;&nbsp;
                <a href='d_isbn_meny.php'>TILL MENYN</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <input type="submit" name="aaterstaell" style="background-color:#0fb821" value="Återställ ISBN"/>
                <br /><br /><br /> 
                
			    ISBN:</br> 
				<input type="text" name="ISBN" value="<?php echo $isbn; ?>" size="17" disabled/>&nbsp;&nbsp; 
                <br />

			    Titel:</br> 
				<input type="text" name="Titel" value="<?php echo $titel; ?>" size="150" disabled/>&nbsp;&nbsp; 
                <br />

                Forskare:</br>
                <input type="text" name="Forskare" value="<?php echo $forskare; ?>" size="50" disabled/>&nbsp;&nbsp;
                <br />

                KTH-id:</br>
                <input type="text" name="KTH_id" value="<?php echo $kth_id; ?>" size="8" disabled/>&nbsp;&nbsp;
                <br />

                Publikationstyp:</br>
                <input type="text" name="Pubtyp" value="<?php echo $pubtyp; ?>" size="30" disabled/>&nbsp;&nbsp;
                <br />

                TRITA:</br>
                <input type="text" name="TRITA" value="<?php echo $trita; ?>" size="30" disabled/>&nbsp;&nbsp;
                <br />

			    Forskarens epost:</br> 
				<input type="text" name="Epost" value="<?php echo $epost; ?>" size="30" disabled/>&nbsp;&nbsp; 
                <br />

			    Disputationsdatum:</br> 
				<input type="text" name="Dispdatum" value="<?php echo $dispdatum; ?>" size="20" disabled/>&nbsp;&nbsp; 
                <br />

			    Registreringsdatum:</br> 
				<input type="text" name="Regdatum" value="<?php echo $regdatum; ?>" size="20" disabled/>&nbsp;&nbsp; 
                <br />
				
		    </form>
								
	</body>

</html>