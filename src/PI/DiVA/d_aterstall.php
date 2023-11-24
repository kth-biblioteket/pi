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

    <title>ÅTERSTÄLL ISBN</title>
	
    <link href="Site_utan_storlek.css" rel="stylesheet"> 

    <script type="text/javascript">

    function validateForm() {

        // Handläggarfältet
        var e = document.getElementById("Handlista");
        var handl = e.options[e.selectedIndex].value;
        if (handl == null || handl == "") {
            alert("Handläggare måste anges!");
            return false;
        }
        document.getElementById("Handl").value = handl;
        // Kommentarsfältet
        var kom = document.forms["ISBNForm"]["Kommentar"].value;
        if (kom == null || kom == "" || kom.length < 10) {
            alert("Kommentar måste anges!");
            return false;
        }

    }

    </script>
	
</head>

<body>

<?php include('include_isbn.html'); ?>

<?php
    
    $username = $_SESSION['anv'];
    $password = $_SESSION['ord'];
    $dbname = "hant_isbn";

    $ISBN = $_SESSION['ISBN'];
    $Titel = $_SESSION['Titel'];
    $Forskare = $_SESSION['Forskare'];
    $Fnamn = $_SESSION['Fnamn'];
    $Enamn = $_SESSION['Enamn'];
    $KTH_id = $_SESSION['KTH_id'];
    $Pubtyp = $_SESSION['Pubtyp'];
    $TRITA = $_SESSION['TRITA'];
    $Epost = $_SESSION['Epost'];
    $Dispdatum = $_SESSION['Dispdatum'];
    $Regdatum = $_SESSION['Regdatum'];
    $Handl = $_POST['Handl'];
    $Kommentar = $_POST['Kommentar'];

    $Sk = "'";
    $Ers = "''";

    $Fnamn = str_replace($Sk, $Ers, $Fnamn);
    $Enamn = str_replace($Sk, $Ers, $Enamn);
    $Titel = str_replace($Sk, $Ers, $Titel);

    if (isset($_POST['spara'])) {
        try {
            $pdo = new PDO("mysql:host=$hostname;dbname=$dbname", $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // PÅBÖRJA TRANSAKTIONEN
	        $pdo->beginTransaction();

	        // SPARA DEN REGISTRERADE PUBLIKATIONENS UPPGIFTER FÖRE HISTORIK OCH LOGGNING
            $stmt = $pdo->prepare("INSERT INTO bort_reg_isbn (ISBN,Titel,Fnamn,Enamn,KTH_id,Pubtyp,TRITA,Epost,Dispdatum,Regdatum,Handl,Kommentar,Returdatum) VALUES 
            (:ISBN,:Titel,:Fnamn,:Enamn,:KTH_id,:Pubtyp,:TRITA,:Epost,:Dispdatum,:Regdatum,:Handl,:Kommentar,CURDATE())"); 
            $stmt->bindParam(':ISBN', $ISBN);
            $stmt->bindParam(':Titel', $Titel);
            $stmt->bindParam(':Fnamn', $Fnamn);
            $stmt->bindParam(':Enamn', $Enamn);
            $stmt->bindParam(':KTH_id', $KTH_id);
            $stmt->bindParam(':Pubtyp', $Pubtyp);
            $stmt->bindParam(':TRITA', $TRITA);
            $stmt->bindParam(':Epost', $Epost);
            $stmt->bindParam(':Dispdatum', $Dispdatum);
            $stmt->bindParam(':Regdatum', $Regdatum); 
            $stmt->bindParam(':Handl', $Handl);   
            $stmt->bindParam(':Kommentar', $Kommentar);
            $stmt->execute();                                                                                       
            
            // TA BORT DEN REGISTRERADE PUBLIKATIONEN
            $stmt = $pdo->prepare("DELETE FROM reg_isbn WHERE ISBN = :ISBN"); 
            $stmt->bindParam(':ISBN', $ISBN);
            $stmt->execute();

            // LÄGG TILLBAKA ISBN
            $stmt = $pdo->prepare("INSERT INTO oanv_isbn (ISBN,Importdatum) VALUES (:ISBN,CURDATE())"); 
            $stmt->bindParam(':ISBN', $ISBN);
            $stmt->execute();
             
            // AVSLUTA TRANSAKTIONEN
            $pdo->commit();  
    	    $ISBN = "";
    	    $Titel = "";
    	    $Forskare = "";
    	    $KTH_id = "";
    	    $Pubtyp = "";
    	    $TRITA = "";
    	    $Epost = "";
            $Dispdatum = "";
            $Regdatum = "";
            $Handl = "";
            $Kommentar = ""; 
            echo '<script language="javascript">';
            echo 'alert("ISBN är nu återställt!")';
            echo '</script>';                            
        }
        catch (PDOException $e) {
            echo '<script language="javascript">';
            echo 'alert("Fel vid återställning!")';
            echo '</script>';

	// RULLA TILLBAKA TRANSAKTION
	    $pdo->rollBack();
        }
    }

?>

<h2>ÅTERSTÄLL ISBN</h2>	
	                                    
	<form name="ISBNForm" onsubmit="return validateForm()" action="d_aterstall.php" method="post">

                <a href='d_soek_isbn.php'>TILL SÖK ISBN</a>&nbsp;&nbsp;
                <a href='d_isbn_meny.php'>TILL MENYN</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <input type="submit" name="spara" style="background-color:#0fb821" value="Återställ"/>
                <br /><br /><br /> 
                
		ISBN:</br> 
		<input type="text" name="ISBN" value="<?php echo $ISBN; ?>" size="17" disabled/>&nbsp;&nbsp; 
                <br />

		Titel:</br> 
		<input type="text" name="Titel" value="<?php echo $Titel; ?>" size="150" disabled/>&nbsp;&nbsp; 
                <br />

                Forskare:</br>
                <input type="text" name="Forskare" value="<?php echo $Forskare; ?>" size="50" disabled/>&nbsp;&nbsp;
                <br />

                KTH-id:</br>
                <input type="text" name="KTH_id" value="<?php echo $KTH_id; ?>" size="8" disabled/>&nbsp;&nbsp;
                <br />

                Publikationstyp:</br>
                <input type="text" name="Pubtyp" value="<?php echo $Pubtyp; ?>" size="30" disabled/>&nbsp;&nbsp;
                <br />

                TRITA:</br>
                <input type="text" name="TRITA" value="<?php echo $TRITA; ?>" size="30" disabled/>&nbsp;&nbsp;
                <br />

		Forskarens epost:</br> 
		<input type="text" name="Epost" value="<?php echo $Epost; ?>" size="30" disabled/>&nbsp;&nbsp; 
                <br />

		Disputationsdatum:</br> 
		<input type="text" name="Dispdatum" value="<?php echo $Dispdatum; ?>" size="20" disabled/>&nbsp;&nbsp; 
                <br />

		Registreringsdatum:</br> 
		<input type="text" name="Regdatum" value="<?php echo $Regdatum; ?>" size="20" disabled/>&nbsp;&nbsp; 
                <br />

                Handläggare:<br />
                <select id="Handlista">
                    <option value=""></option>
                    <option value="Camilla">Camilla</option>
                    <option value="Cecilia">Cecilia</option>
                    <option value="Greta">Greta</option>
                    <option value="Johan">Johan</option>
                    <option value="Anders">Anders</option>
                    <option value="Margareta">Margareta</option>
                    <option value="Ulf">Ulf</option>
                </select>
                <br />

		Kommentar:</br> 
		<input type="text" name="Kommentar" size="100"/> 
                
                <input type="text" name="Handl" id="Handl" size="20"  hidden/>                   
    				
	</form>
								
</body>

</html>