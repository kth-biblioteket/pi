<?php session_start(); ?>

<!DOCTYPE html PUBLIC "-//w3c//DTD XHTMLm 1.0 Transitional//EN" 
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<! Författare: Cecilia Wiklander>
<! Syfte: Adressrättnings-hantering>
<! Ändringar: >

<head>

    <meta charset="utf-8">

    <title>BESTÄLL LISTA</title>
	
    <link href="Site_utan_storlek.css" rel="stylesheet"> 
    	
</head>

<body>

<?php include('include_head_new_ny.html'); ?>

<?php
    
    $Aemne = $_POST['Aemne'];

    if (strlen($Aemne) >= 2) {

       if (strlen($Titel) > 0) {
            echo '<script language="javascript">';
            echo 'alert("Du kan bara beställa en lista i taget!")';
            echo '</script>'; 
       }
       else {
            $from_name = "publicering@kth.se";

            $dbname_2 = 'BIBCOUNT';

            $pdo = new PDO("mysql:host=$hostname;dbname=$dbname_2", $username, $password);

            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Hämta beställningsnummer
            $sqlbestnr = "SELECT NEXT VALUE AS Bestnr FOR dbo.ID_Seq;";
            $stmt = $pdo->query( $sqlbestnr );
            foreach ($stmt as $row) {
               $bestnr = $row['Bestnr'];        
            }           
       }



    // Kontrollera att ISBN-nummer inte är redan är utfärdat för angivet KTH-id och titel



    $stmt = $pdo->prepare("SELECT * FROM reg_isbn WHERE UPPER(REPLACE(Titel,' ','')) = UPPER(:test_titel) 
    AND Pubtyp = :Typ AND KTH_id = :KTHid");
    $stmt->bindParam(':test_titel', $test_titel);
    $stmt->bindParam(':Typ', $Typ);
    $stmt->bindParam(':KTHid', $KTHid);
    $stmt->execute();

   if ($count = $stmt->rowCount() > 0) 
 {
        echo 'Publikationen har redan fått ett ISBN!';              
 }
   else 
 {
        
	$trans_klar = 0;
	$antal = 0;

	while ($antal < 3 && $trans_klar == 0) {
    // Hämta ISBN-nummer
		$sql_isbn = "SELECT MIN(ISBN) AS ISBN FROM oanv_isbn";
                $stmt = $pdo->query( $sql_isbn );
                foreach ($stmt as $row) {
                $isbn = $row['ISBN']; 
                }
		try {

			// BÖRJA TRANSAKTION
			$pdo->beginTransaction();
			// Ta bort ur lediga ISBN-nummer
                        $stmt = $pdo->prepare("DELETE FROM oanv_isbn WHERE ISBN = :isbn");
                        $stmt->bindParam(':isbn', $isbn);
                        $stmt->execute();
			// Spara ISBN och publikationsuppgifter
            if ($Typ == "Rapport") {
                        $stmt = $pdo->prepare("INSERT INTO reg_isbn 
                		(TRITA,Epost,KTH_id,Fnamn,Enamn,ISBN,Pubtyp,Titel,Regdatum) 
                		VALUES (trim(:TRITA),trim(:Epost),:KTHid,:Fnamn,:Enamn,:isbn,:Typ,:Titel,CURDATE())");
                        $stmt->bindParam(':TRITA', $TRITA);              		
			}
			else {
                        $stmt = $pdo->prepare("INSERT INTO reg_isbn 
                		(Dispdatum,Epost,KTH_id,Fnamn,Enamn,ISBN,Pubtyp,Titel,Regdatum) 
                		VALUES (trim(:Ddatum),trim(:Epost),:KTHid,:Fnamn,:Enamn,:isbn,:Typ,:Titel,CURDATE())");
                        $stmt->bindParam(':Ddatum', $Ddatum);     
			}
            $stmt->bindParam(':Epost', $Epost);
            $stmt->bindParam(':KTHid', $KTHid);
            $stmt->bindParam(':Fnamn', $Fnamn);
            $stmt->bindParam(':Enamn', $Enamn);
            $stmt->bindParam(':isbn', $isbn);
            $stmt->bindParam(':Typ', $Typ);
            $stmt->bindParam(':Titel', $Titel);   
                                         
            $stmt->execute();
			// AVSLUTA TRANSAKTION
			$pdo->commit();
			$trans_klar = 1;
		}
		catch (Exception $e) {
			echo $e;
			echo '   ';
			// RULLA TILLBAKA TRANSAKTION
			$pdo->rollBack();
		}

		$antal++;
	} 

	if ($trans_klar == 1) {
		echo '';
        echo 'Din ansökan om ISBN är registrerad!';
		echo '';
		echo '';
		// SKICKA ISBN MED E-POST
                $TitelUt = substr($Titel, 0, 30) . '...';
                $message = 'Publikationen ' . $TitelUt . ' får ISBN: ' . $isbn;
                $headers = "MIME-Version: 1.0\r\n";
                $headers.= "From: =?utf-8?b?".base64_encode($from_name)."?= <".$from_a.">\r\n";
                $headers.= "Content-Type: text/plain;charset=utf-8\r\n";
                $headers.= "Reply-To: $reply\r\n";  
                $headers.= "X-Mailer: PHP/" . phpversion();
                mail($Epost, 'Begärt ISBN', $message, $headers);
                mail('cecwik@kth.se', 'Begärt ISBN', $message, $headers);
		// SKICKA PÅMINNELSE OM ANTAL ISBN NÅTT MINIMINIVÅ
                $sql_antal = "SELECT COUNT(*) AS Antal FROM oanv_isbn";
                $stmt = $pdo->query( $sql_antal );
                foreach ($stmt as $row) {
                	$antal = $row['Antal']; 
                }

                if ($antal <= $minstISBN) {
                        $message = 'Nu är det ' . $antal . ' st kvar av ISBN-numren';
                        $headers = "MIME-Version: 1.0\r\n";
                        $headers.= "From: =?utf-8?b?".base64_encode($from_name)."?= <".$from_a.">\r\n";
                        $headers.= "Content-Type: text/plain;charset=utf-8\r\n";
                        $headers.= "Reply-To: $reply\r\n";  
                        $headers.= "X-Mailer: PHP/" . phpversion();
                        mail('publicering@kth.se', 'Dags att beställa ISBN från KB', $message, $headers);  
                        mail('cecwik@kth.se', 'Dags att beställa ISBN från KB', $message, $headers);                   
                }
	}
	else {
		echo '';
                echo 'Fel vid uttag av ISBN, kontakta PI-enheten på ECE-skolan!'; 
	}

  }

      }

?>

		
<br /><br /><br />

    <input type="button" value="Tillbaka" onClick="history.go(-1);">
								
	</body>
</html>