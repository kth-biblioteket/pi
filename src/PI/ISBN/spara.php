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

    <title>ANSÖK OM ISBN</title>
	
    <link href="Site_utan.css" rel="stylesheet"> 
    	
</head>

<body>

<?php

    header("Access-Control-Allow-Origin: https://apps.lib.kth.se");
    
    $Typ = $_POST['Typ'];
    $Ddatum = $_POST['Dispdatum'];
    $TRITA = $_POST['TRITA'];
    $Titel = $_POST['Titel'];
    $KTHid = $_POST['KTHid'];
    $Enamn = $_POST['Enamn'];
    $Fnamn = $_POST['Fnamn'];
    $Epost = $_POST['Epost'];

    if (strlen($Typ) > 4 and strlen($Titel) > 5 and strlen($Enamn) > 0 and strlen($Fnamn) > 0 and strlen($Epost) > 5 and strlen($KTHid) > 6) {

    // Minimivärde för antal ISBN
    $minstISBN = 100; // Hämtas via tabell, men startvärde här

    $Sk = "'";
    $Ers = "''";

    $Fnamn = str_replace($Sk, $Ers, $Fnamn);
    $Enamn = str_replace($Sk, $Ers, $Enamn);
    $SkickaTitel = $Titel;
    $Titel = str_replace($Sk, $Ers, $Titel);

    //$from_name = "biblioteket@kth.se";

    $pdo = new PDO("mysql:host=$hostname;dbname=$dbname", $username, $password);

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Hämta miniminivå på ISBN
    $sql_min_niv_isbn = "SELECT Antal FROM min_niv_isbn WHERE Regdatum = (SELECT MAX(Regdatum) FROM min_niv_isbn)";
    $stmt = $pdo->query( $sql_min_niv_isbn );
    foreach ($stmt as $row) {
        $minstISBN = $row['Antal'];        
    }
    
    // Kontrollera att ISBN-nummer inte är redan är utfärdat för angivet KTH-id och titel

    $test_titel = preg_replace('/\s+/', '', $Titel); 

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

	while ($antal < 20 && $trans_klar == 0) {
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
                echo 'Din ansökan om ISBN är registrerad: ' . $isbn;
		echo '';
		echo '';
		// SKICKA ISBN MED E-POST
        /*
                $TitelUt = substr($SkickaTitel, 0, 30) . '...';
                $message = 'Publikationen ' . $TitelUt . ' får ISBN: ' . $isbn;
                $headers = 'MIME-Version: 1.0' . "\r\n"; 
                $headers .= 'Content-type: text/plain; charset=utf-8' . "\r\n"; 
                $headers .= 'Content-Transfer-Encoding: 8bit' . "\r\n"; 
                $headers .= 'From: publicering@kth.se' . "\r\n" . 'Reply-To: publicering@kth.se' . "\r\n" . 'X-Mailer: PHP/' . phpversion();
                mail($Epost, 'Nytt ISBN', $message, $headers);
                mail('cecwik@kth.se', 'Nytt ISBN', $message, $headers);
        */
        require_once($_SERVER['DOCUMENT_ROOT'] . '/PHPMailer/PHPMailerAutoload.php');
        $mail = new PHPMailer; 
	$mail->isSMTP(); // NYTT 2022-11-22
	$mail->Host = "relayhost.sys.kth.se"; // NYTT 2022-11-22
	$mail->SMTPAuth   = FALSE; // NYTT 2022-11-22
	$mail->SMTPSecure = "tls"; // NYTT 2022-11-22        
        $mail->CharSet = 'UTF-8';
        $TitelUt = substr($SkickaTitel, 0, 30) . '...';
        $message = 'Publikationen ' . $TitelUt . ' får ISBN: ' . $isbn;
        $mail->setFrom('biblioteket@kth.se');
        $mail->addAddress($Epost);
        $mail->Subject  = 'Nytt ISBN';
        $mail->Body     = $message;
        if(!$mail->send()) {
           echo ' Fel vid skickande av meddelande.';
           echo ' Fel: ' . $mail->ErrorInfo;
        } 
        else {
           echo ' Meddelande har skickats.';
        }
        $mail->addAddress('cecwik@kth.se');
        $mail->send();
		// SKICKA PÅMINNELSE OM ANTAL ISBN NÅTT MINIMINIVÅ
                $sql_antal = "SELECT COUNT(*) AS Antal FROM oanv_isbn";
                $stmt = $pdo->query( $sql_antal );
                foreach ($stmt as $row) {
                	$antal = $row['Antal']; 
                }

                if ($antal <= $minstISBN) {
                    /*
                        $message = 'Nu är det ' . $antal . ' st kvar av ISBN-numren';
                        mail('publicering@kth.se', 'Snart slut ISBN-nr', $message, $headers);  
                        mail('cecwik@kth.se', 'Snart slut ISBN-nr', $message, $headers);
                    */
                    $message = 'Nu är det ' . $antal . ' st kvar av ISBN-numren';
                    $mail->setFrom('biblioteket@kth.se');
                    //$mail->addAddress('cecwik@kth.se');
                    $mail->addAddress('biblioteket@kth.se');
                    $mail->Subject  = 'Dags att beställa ISBN från KB';
                    $mail->Body     = $message;
                    $mail->send();                                           
                }
	}
	else { 
		echo '';
                echo 'Fel vid uttag av ISBN, kontakta PIMS-enheten på Universitetsförvaltningen!'; 
	}

  }

      }

?>

		
<br /><br /><br />

    <input type="button" value="Tillbaka" onClick="history.go(-1);">
								
	</body>
</html>