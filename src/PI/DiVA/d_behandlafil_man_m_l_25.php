<?php
   session_start();
   require_once('config.php.inc');
?>

<!DOCTYPE html PUBLIC "-//w3c//DTD XHTMLm 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<! Författare: Cecilia Wiklander>
<! Syfte: DiVA-hantering>
<! Ändringar: >

<html xmlns="http://www.w3.org/1999/xhtml">

<head>

<meta charset="utf-8">

<title>MANUELLT UTTAGEN FRÅN WOS ELLER SCOPUS</title>

<link href="Site_utan_storlek.css" rel="stylesheet">

<?php include('include_diva.html'); ?>

<?php

$doneMessage = '';
$wasPost = $_SERVER['REQUEST_METHOD'] === 'POST';
$submittedImportToken = $_POST['import_token'] ?? '';
$currentImportToken = $_SESSION['diva_import_token'] ?? '';
$validImportToken = isset($_POST['behandla']) && $submittedImportToken !== '' && $currentImportToken !== '' && hash_equals($currentImportToken, $submittedImportToken);

if (isset($_POST['behandla']) && !$validImportToken) {
    $doneMessage = 'Filen behandlas redan eller formuläret har redan skickats. Skicka inte samma fil igen.';
}

if ($validImportToken) {
    unset($_SESSION['diva_import_token']);

    $pdo = null;
    $mail = null;
    $fh_in = null;
    $fp_ut = null;
    $fp_lista = null;

    try {
    // Large WoS files can take longer than PHP's default 30s limit,
    // especially when split into multiple output files and emails.
    // Keep the default finite; set DIVA_IMPORT_TIMEOUT_SECONDS=0 to keep PHP's current limit.
    $processingTimeoutEnv = getenv('DIVA_IMPORT_TIMEOUT_SECONDS');
    $processingTimeoutSeconds = $processingTimeoutEnv !== false ? (int) $processingTimeoutEnv : 900;
    if ($processingTimeoutSeconds > 0) {
        set_time_limit($processingTimeoutSeconds);
    }

    $timingEnabled = getenv('DIVA_IMPORT_TIMING') !== false;
    $timingStart = microtime(true);
    $timingLast = $timingStart;
    $logTiming = function ($label) use ($timingEnabled, &$timingStart, &$timingLast) {
       if ($timingEnabled) {
          $now = microtime(true);
          error_log(sprintf('DiVA import timing: %s +%.3fs total %.3fs', $label, $now - $timingLast, $now - $timingStart));
          $timingLast = $now;
       }
    };

    //$hostname = "localhost";
    $hostname = $hostname ?? (getenv('PI_DB_HOST') ?: 'pi-db');
    $dbname = "bibmet";
    $username = $_SESSION['anv'] ?? getenv('PI_DB_USER');
    $password = $_SESSION['ord'] ?? getenv('PI_DB_PASSWORD');

    if (!$username || !$password) {
        $doneMessage = 'Du måste logga in innan filen behandlas.';
        throw new RuntimeException('DiVA import attempted without database credentials');
    }

    $pdo = new PDO("mysql:host=$hostname;dbname=$dbname", $username, $password);

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
        $Epost = $_POST['Epost'];
    
        require_once($_SERVER['DOCUMENT_ROOT'] . '/PHPMailer/PHPMailerAutoload.php');
        $mail = new PHPMailer; 
	$mail->isSMTP(); 
	$mail->Host = getenv('SMTP_HOST') ?: "relayhost.sys.kth.se";
        $mail->Port = (int) (getenv('SMTP_PORT') ?: 587);
	$mail->SMTPAuth   = FALSE; 
	$mail->SMTPSecure = getenv('SMTP_SECURE') !== false ? getenv('SMTP_SECURE') : "tls";
        $mail->SMTPKeepAlive = true;
        $mail->Timeout = 60;
        $mail->CharSet = 'UTF-8';
        $message = 'Alla behandlade filer finns bifogade.';
        $generatedFileCount = 0;
        $emailDeliveryAttempted = false;
        $emailSent = false;
        $addGeneratedFile = function ($fileName, $handle) use ($mail, &$generatedFileCount) {
           rewind($handle);
           $mail->addStringAttachment(stream_get_contents($handle), basename($fileName));
           $generatedFileCount = $generatedFileCount + 1;
           fclose($handle);
        };

    $sql = "SELECT CURRENT_TIMESTAMP() AS DatumTid";
    $stmt = $pdo->query( $sql );
    foreach ($stmt as $row) {
            $DatumTid = $row['DatumTid'];        
    }




    $Filtyp = $_POST['Filtyp'];
    
    $KTH_l_t = $_POST['KTH_l_t'];
    if ($KTH_l_t == 'med') {
        $KTH_led_tr = TRUE;       
    }
    else {
        $KTH_led_tr = FALSE;         
    }
    
    $KTH_d_f = $_POST['KTH_d_f'];
    if ($KTH_d_f == 'ja') {
        $KTH_dela_fil = TRUE;       
    }
    else {
        $KTH_dela_fil = FALSE;         
    }

    $handlaggare = $_POST['Handl'] ?? '';
    $filtypLabel = $Filtyp == 'wos' ? 'WoS' : 'Scopus';
    $kthLedtradLabel = $KTH_l_t == 'med' ? 'Med' : 'Utan';
    $kthDelaFilLabel = $KTH_d_f == 'ja' ? 'Ja' : 'Nej';



    // Limit uploads because generated output files are attached to one email.
    // Override with DIVA_IMPORT_MAX_UPLOAD_BYTES if needed.
    $maxUploadSizeBytes = (int) (getenv('DIVA_IMPORT_MAX_UPLOAD_BYTES') ?: 10000000);

    $uploadOk = 1;
    $uploadedFile = $_FILES["fileToUpload"] ?? null;
    $uploadError = $uploadedFile['error'] ?? UPLOAD_ERR_NO_FILE;

    if ($uploadError !== UPLOAD_ERR_OK) {
          $uploadMessages = array(
              UPLOAD_ERR_INI_SIZE => 'Tyvärr, filen är större än serverns tillåtna maxstorlek.',
              UPLOAD_ERR_FORM_SIZE => 'Tyvärr, filen är för stor.',
              UPLOAD_ERR_PARTIAL => 'Tyvärr, filen laddades bara upp delvis.',
              UPLOAD_ERR_NO_FILE => 'Tyvärr, ingen fil laddades upp.',
              UPLOAD_ERR_NO_TMP_DIR => 'Tyvärr, servern saknar temporär uppladdningskatalog.',
              UPLOAD_ERR_CANT_WRITE => 'Tyvärr, servern kunde inte spara den uppladdade filen.',
              UPLOAD_ERR_EXTENSION => 'Tyvärr, uppladdningen stoppades av servern.'
          );
          echo $uploadMessages[$uploadError] ?? 'Tyvärr, filen gick inte att ladda upp.';
          $uploadOk = 0;
    }
    else {
          $uploadedFileName = basename($uploadedFile["name"]);
          $target_file = $uploadedFile["tmp_name"];
          $imageFileType = strtolower(pathinfo($uploadedFileName,PATHINFO_EXTENSION));

          // Kontrollera filtyp
          if($imageFileType != "txt" ) {
                 echo "Tyvärr, enbart txt-filer tillåts";
                 $uploadOk = 0;
          }
          else {
                // Kontrollera filstorlek
                if ($uploadedFile["size"] > $maxUploadSizeBytes) {
                    echo "Tyvärr, filen är för stor.";
                    $uploadOk = 0;
                }
                elseif (!is_uploaded_file($target_file)) {
                    echo "Tyvärr, filen gick inte att ladda upp.";
                    $uploadOk = 0;
                }
                else {
                      if (strpos($uploadedFileName, ' ') !== false) {
                         echo "Tyvärr, filnamnet får inte innehålla blanktecken.";
                         $uploadOk = 0;                        
                      }
                      else {
                         echo "Filen " . htmlspecialchars($uploadedFileName) . " har laddats upp.";
                      }
                }
          }
    }    


    
 if ($uploadOk == 1) {
    // Fil att läsa in
    $filnamn_in = $target_file;
    $filnamn_in = str_replace(' ', '', $filnamn_in);   

    // TEST Max antal författare, 4 som testvärde TEST
    $Maxff = 30;

    // Öppna infil
    $fh_in = fopen($filnamn_in,'r');
    $radnr = 0;
    $postnr = 0;
    $tilldela_af = 0;
    $tilldela_au = 0;
    $tilldela_c1 = 0;

    // Fil att skriva ut
    $filnamn_ut = preg_replace('/\.txt$/i', '', $uploadedFileName);
    $handl = $handlaggare;
    if (strlen($handl) > 0) {
       $handl = '_' . $handl;
    }
    
    $filnamn_ut = $filnamn_ut . $handl . '_UT_' . substr((string) $DatumTid,0,10);
  
    // Fil att lista antal författare
    $filnamn_lista = preg_replace('/\.txt$/i', '', $uploadedFileName);
    $filnamn_lista = $filnamn_lista . $handl . '_ANTAL_FF.txt';
    $radnr_lista = 0;

    // *** WoS ***
    if ($Filtyp == 'wos') { // Behandla WoS-fil
      $pdo->beginTransaction();
      $filradBatch = array();
      $flushFilradBatch = function () use (&$filradBatch, $pdo) {
          if (count($filradBatch) == 0) {
              return;
          }

          $placeholders = array();
          $params = array();
          foreach ($filradBatch as $row) {
              $placeholders[] = '(?,?,?,?)';
              $params[] = $row[0];
              $params[] = $row[1];
              $params[] = $row[2];
              $params[] = $row[3];
          }

          try {
              $stmt = $pdo->prepare('INSERT INTO filrad (Persondatum,Radnr,Postnr,Rad) VALUES ' . implode(',', $placeholders));
              $stmt->execute($params);
          }
          catch (PDOException $e) {
              $sqlState = $e->errorInfo[0] ?? $e->getCode();
              $driverCode = isset($e->errorInfo[1]) ? (int) $e->errorInfo[1] : 0;
              $isExpectedRowError = in_array(substr((string) $sqlState, 0, 2), array('22', '23'), true)
                  || in_array($driverCode, array(1264, 1366, 1406), true);

              if (!$isExpectedRowError) {
                  throw $e;
              }

              $stmt = $pdo->prepare('INSERT INTO filrad (Persondatum,Radnr,Postnr,Rad) VALUES (?,?,?,?)');
              $stmt_fel = $pdo->prepare("INSERT INTO filrad (Persondatum,Radnr,Postnr,Rad) VALUES (?,?,?,'FEL')");
              foreach ($filradBatch as $row) {
                  try {
                      $stmt->execute($row);
                  }
                  catch (PDOException $singleException) {
                      $singleSqlState = $singleException->errorInfo[0] ?? $singleException->getCode();
                      $singleDriverCode = isset($singleException->errorInfo[1]) ? (int) $singleException->errorInfo[1] : 0;
                      $isExpectedSingleRowError = in_array(substr((string) $singleSqlState, 0, 2), array('22', '23'), true)
                          || in_array($singleDriverCode, array(1264, 1366, 1406), true);

                      if (!$isExpectedSingleRowError) {
                          throw $singleException;
                      }

                      $stmt_fel->execute(array($row[0], $row[1], $row[2]));
                  }
              }
          }

          $filradBatch = array();
      };
    // Loopa genom filen för kontroll - början
        while ($line = fgets($fh_in)) {
            $radnr = $radnr + 1;       
            $taggen = substr($line, 0, 2);
            // Räkna upp postens nummer
            if ($taggen == "PT") {
                $postnr = $postnr + 1;
            }
            // Tilldela författare AF
            if ($taggen == "AF") {
                $tilldela_af = 1;
            }
            // Tilldela författare AU
            if ($taggen == "AU") {
                $tilldela_au = 1;
            }
            // Tilldela adresser C1
            if ($taggen == "C1") {
                $tilldela_c1 = 1;
            }
            // Tilldelning författare AF
            if ($tilldela_af == 1 and $taggen <> "AF") {
                if ($taggen <> "  ") {
                    $tilldela_af = 0;                 
                }
                else {
                    $line = "AF" . substr($line,2);
                }
            }

            // Tilldelning författare AU
            if ($tilldela_au == 1 and $taggen <> "AU") {
                if ($taggen <> "  ") {
                    $tilldela_au = 0;                 
                }
                else {
                    $line = "AU" . substr($line,2);
                }
            }
            // Tilldelning adresser C1
            if ($tilldela_c1 == 1 and $taggen <> "C1") {
                if ($taggen <> "  ") {
                    $tilldela_c1 = 0;                 
                }
                else {
                    $line = "C1" . substr($line,2);
                }
            }
            // Rensa abstract från copyright
            if ($taggen == "AB") { 
                $lgd = strlen($line);
                $pos = stripos($line,'(c)'); // function is case-insensitive
                if ($pos > 0) {
                   $line = substr($line,0,$lgd-($lgd-$pos)) . PHP_EOL;
                }
            }       
            $filradBatch[] = array($DatumTid, $radnr, $postnr, $line);
            if (count($filradBatch) >= 100) {
                $flushFilradBatch();
            }

            // IDAG 2020-03-03 $stmt_f->execute();  
        // Loopa genom filen för kontroll - slut           
        }

        $flushFilradBatch();
        $pdo->commit();
        $logTiming('read input and insert rows');
        // Stäng infil
        fclose($fh_in);
        

        // Kontrollera om det finns affilieringar
        $stmt_aff = $pdo->prepare("SELECT * FROM filrad WHERE persondatum = :DatumTid AND substring(rad,1,2) = 'C1' AND Rad LIKE '%[%' LIMIT 1");
        $stmt_aff->bindParam(':DatumTid', $DatumTid);
        $stmt_aff->execute();  
        $count = $stmt_aff->rowCount();

        if ($count == 0) {
           echo "<script>alert('Inga affilieringar finns!');</script>";
        }
             
    if ($count > 0) { 
        // AF-poster
        $stmt_i = $pdo->prepare("INSERT INTO tabortff (Persondatum,Postnr,Antalff) VALUES (:DatumTid,:Postnr,:Antalff)");        
 
        $sql = "SELECT Postnr,COUNT(*) AS Antalff FROM filrad WHERE substring(rad,1,2) = 'AF' and Persondatum = '" . $DatumTid . "' GROUP BY postnr";

        $stmt = $pdo->query( $sql );
        
        foreach ($stmt as $row) {
            $Postnr = $row['Postnr']; 
            $Antalff = $row['Antalff'];
            if ($Antalff > $Maxff) {                                    
                $stmt_i->bindParam(':DatumTid', $DatumTid);
                $stmt_i->bindParam(':Postnr', $Postnr); 
                $stmt_i->bindParam(':Antalff', $Antalff);                   
                $stmt_i->execute();                                                                               
            }                                
        }

        // C1-poster
        $sql = "SELECT f1.Postnr,f1.Radnr AS f1_Radnr,f2.Radnr AS C1_radnr
                FROM filrad f1, filrad f2 
                WHERE (
                f2.Rad LIKE '%Royal Institute of Technology%' 
                OR f2.Rad LIKE '%Royal Inst Technol%' 
                OR (f2.Rad LIKE '%KTH%' AND f2.Rad LIKE '%Sweden%')
                OR (f2.Rad LIKE '%Inst Technol%' AND f2.Rad LIKE '%Sweden%')
                OR (f2.Rad LIKE '%Royal Inst%' AND f2.Rad LIKE '%Sweden%')
                OR (f2.Rad LIKE '%Kungliga Tekniska Hgsk%' AND f2.Rad LIKE '%Sweden%')
                ) AND 
                SUBSTRING(f2.Rad,1,2) = 'C1' AND  
                f1.Postnr = f2.Postnr AND 
                SUBSTRING(f1.Rad,1,2) = 'AF' AND 
                f2.Rad LIKE CONCAT('%',SUBSTRING(f1.Rad,4,LENGTH(f1.Rad)-5),'%') AND 
                f1.Persondatum = f2.Persondatum AND 
                f1.Persondatum = '" . $DatumTid . "'";
                
        $stmt_u = $pdo->prepare("UPDATE filrad SET KTHff = 1,C1_radnr = :C1_radnr WHERE Persondatum = :DatumTid AND Postnr = :Postnr AND Radnr = :f1_Radnr");                                 

        $stmt = $pdo->query( $sql );

        foreach ($stmt as $row) {

            $Postnr = $row['Postnr']; 
            $f1_Radnr = $row['f1_Radnr'];    
            $C1_radnr = $row['C1_radnr'];   
               
            $stmt_u->bindParam(':DatumTid', $DatumTid);
            $stmt_u->bindParam(':Postnr', $Postnr); 
            $stmt_u->bindParam(':f1_Radnr', $f1_Radnr);    
            $stmt_u->bindParam(':C1_radnr', $C1_radnr);                                 
            $stmt_u->execute();  
                                                                                                                                         
        }

       if ($KTH_led_tr) { 
        
          $stmt_s = $pdo->prepare("SELECT SUBSTRING(f1.Rad,4,LENGTH(f1.Rad)-5) AS Namnet,f1.C1_radnr FROM filrad f1, filrad f2 WHERE f1.Persondatum = :DatumTid AND f1.KTHff = 1 AND f2.Persondatum = f1.Persondatum AND f1.C1_radnr = f2.Radnr");

          $stmt_u_c1 = $pdo->prepare("UPDATE filrad SET Rad = REPLACE(Rad,:Namnet_F,:Namnet_T) WHERE Persondatum = :DatumTid AND Radnr = :C1_radnr"); 

          $stmt_s->bindParam(':DatumTid', $DatumTid);

          $stmt_s->execute(); 

          foreach ($stmt_s as $row) {
                  $Namnet_F = $row['Namnet'];    
                  $C1_radnr = $row['C1_radnr']; 
                  $Namnet_T = '$$$' . $Namnet_F;   
                  $stmt_u_c1->bindParam(':Namnet_F', $Namnet_F);
                  $stmt_u_c1->bindParam(':Namnet_T', $Namnet_T);                                  
                  $stmt_u_c1->bindParam(':DatumTid', $DatumTid);
                  $stmt_u_c1->bindParam(':C1_radnr', $C1_radnr); 
                  $stmt_u_c1->execute();
          } 

        }

// TILLÄGG 2020-04-06 BÖRJAN

       if ($KTH_led_tr) { 
        
          $stmt_s = $pdo->prepare("SELECT SUBSTRING(f1.Rad,4,LENGTH(f1.Rad)-5) AS Namnet,f2.Radnr AS C1_radnr 
          FROM filrad f1, filrad f2 
          WHERE f1.Persondatum = :DatumTid AND f1.KTHff = 1 AND f2.Persondatum = f1.Persondatum 
          AND SUBSTRING(f2.Rad,1,2) = 'C1' AND f1.Postnr = f2.Postnr AND SUBSTRING(f1.Rad,1,2) = 'AF' 
          AND f2.Radnr <> f1.C1_Radnr AND f2.Rad LIKE CONCAT('%',SUBSTRING(f1.Rad,4,LENGTH(f1.Rad)-5),'%')");

          $stmt_u_c1 = $pdo->prepare("UPDATE filrad SET Rad = REPLACE(Rad,:Namnet_F,:Namnet_T) WHERE Persondatum = :DatumTid AND Radnr = :C1_radnr"); 

          $stmt_s->bindParam(':DatumTid', $DatumTid);

          $stmt_s->execute(); 

          foreach ($stmt_s as $row) {
                  $Namnet_F = $row['Namnet'];    
                  $C1_radnr = $row['C1_radnr']; 
                  $Namnet_T = '$$$' . $Namnet_F;   
                  $stmt_u_c1->bindParam(':Namnet_F', $Namnet_F);
                  $stmt_u_c1->bindParam(':Namnet_T', $Namnet_T);                                  
                  $stmt_u_c1->bindParam(':DatumTid', $DatumTid);
                  $stmt_u_c1->bindParam(':C1_radnr', $C1_radnr); 
                  $stmt_u_c1->execute();
          } 

        }

// TILLÄGG 2020-04-06 SLUT

        // Läs tabellen tabortff                  
        $sql_t = "SELECT Postnr, Antalff FROM tabortff WHERE Persondatum = '" . $DatumTid . "'";
        // 1: Vilket är minsta radnr för AF i posten
        $stmt_m = $pdo->prepare("SELECT KTHff, Radnr FROM filrad WHERE Postnr = :Postnr AND SUBSTRING(rad,1,2) = 'AF' AND Radnr IN (SELECT MIN(Radnr) FROM filrad WHERE Postnr = :Postnr AND SUBSTRING(rad,1,2) = 'AF' AND Persondatum = :DatumTid) AND Persondatum = :DatumTid");
        // 2: Vilket är högsta radnr för AF i posten
        $stmt_sf = $pdo->prepare("SELECT KTHff, Radnr FROM filrad WHERE Postnr = :Postnr AND SUBSTRING(rad,1,2) = 'AF' AND Radnr IN (SELECT MAX(Radnr) FROM filrad WHERE Postnr = :Postnr AND SUBSTRING(rad,1,2) = 'AF') AND Persondatum = :DatumTid");
        // 3: Minsta radnr för AU
        $stmt_m_au = $pdo->prepare("SELECT MIN(Radnr) AS MinRadnrAU FROM filrad WHERE Postnr = :Postnr AND SUBSTRING(rad,1,2) = 'AU' AND Persondatum = :DatumTid");
        // 4: Kontrollera särtrycksförfattare
        $stmt_s_rp = $pdo->prepare("SELECT f1.Radnr, f1.KTHff FROM filrad f1, filrad f2 
        WHERE SUBSTRING(f2.Rad,1,2) = 'RP' AND f2.Postnr = f1.Postnr AND SUBSTRING(f1.Rad,1,2) = 'AU' AND INSTR(SUBSTRING(f2.Rad,4),REPLACE(SUBSTRING(f1.Rad,4),'\n','')) 
        AND f1.Postnr = :Postnr AND f1.Persondatum = :DatumTid");
        // 5: Uppdatera filrad för AF som särtrycksförfattare på KTH
        $stmt_u_rp = $pdo->prepare("UPDATE filrad SET KTHff = 2 WHERE Radnr = :Radnr AND Postnr = :Postnr AND Persondatum = :DatumTid");
        // 6: Sök KTH-författares radnr för AF
        $stmt_s_au = $pdo->prepare("SELECT Radnr, KTHff FROM filrad WHERE KTHff >= 1 AND SUBSTRING(rad,1,2) = 'AF' AND Postnr = :Postnr AND Persondatum = :DatumTid");
        // 7: Uppdatera filrad för AU
        $stmt_u_au = $pdo->prepare("UPDATE filrad SET KTHff = :KTHff WHERE Radnr = :Radnr AND Postnr = :Postnr AND Persondatum = :DatumTid");
        // 8: Uppdatera tabortff
        $stmt_u_t = $pdo->prepare("UPDATE tabortff SET MinRadnrAF = :MinRadnrAF, MaxRadnrAF = :MaxRadnrAF WHERE Postnr = :Postnr AND Persondatum = :DatumTid");

        // Läs tabellen tabortff 
        $stmt = $pdo->query( $sql_t );
        foreach ($stmt as $row) {
            $Postnr = $row['Postnr'];
            $Antalff = $row['Antalff'];

            // 1: Minsta radnr för AF
            $stmt_m->bindParam(':DatumTid', $DatumTid);
            $stmt_m->bindParam(':Postnr', $Postnr);
            $stmt_m->execute();
            foreach ($stmt_m as $row) {  
                $MinRadnrAF = $row['Radnr'];                            
            } 

            // 2: Högsta radnr för AF
            $stmt_sf->bindParam(':DatumTid', $DatumTid);
            $stmt_sf->bindParam(':Postnr', $Postnr);
            $stmt_sf->execute();
            foreach ($stmt_sf as $row) {  
                $MaxRadnrAF = $row['Radnr'];                        
            }

            // 3: Minsta radnr för AU
            $stmt_m_au->bindParam(':DatumTid', $DatumTid);
            $stmt_m_au->bindParam(':Postnr', $Postnr);
            $stmt_m_au->execute();
            foreach ($stmt_m_au as $row) {  
                $MinRadnrAU = $row['MinRadnrAU'];  
            }
                                      
            // 4: Kontrollera om RP-författare kommer med
            $stmt_s_rp->bindParam(':DatumTid', $DatumTid);
            $stmt_s_rp->bindParam(':Postnr', $Postnr);                                 
            $stmt_s_rp->execute(); 
            foreach ($stmt_s_rp as $row) { 

                $RadnrRP = $row['Radnr'];
                $KTHff = $row['KTHff'];

                // 5: Uppdatera filrad för AF som särtrycksförfattare på KTH
                if ($KTHff == NULL) {   
                   $RadnrRP = $RadnrRP + $Antalff;                 
                   $stmt_u_rp->bindParam(':DatumTid', $DatumTid);
                   $stmt_u_rp->bindParam(':Postnr', $Postnr); 
                   $stmt_u_rp->bindParam(':Radnr', $RadnrRP);                                                                                        
                   $stmt_u_rp->execute();                                                          
                }                                        
            }     

            // 6: Sök KTH-författares radnr för AF
            $stmt_s_au->bindParam(':DatumTid', $DatumTid);
            $stmt_s_au->bindParam(':Postnr', $Postnr);                                 
            $stmt_s_au->execute(); 
            foreach ($stmt_s_au as $row) { 
                $RadnrAF = $row['Radnr'];
                $KTHff = $row['KTHff'];
                // 7: Uppdatera filrad för AU
                $RadnrAU = $RadnrAF - $Antalff;
                $stmt_u_au->bindParam(':DatumTid', $DatumTid);
                $stmt_u_au->bindParam(':Postnr', $Postnr); 
                $stmt_u_au->bindParam(':Radnr', $RadnrAU);  
                $stmt_u_au->bindParam(':KTHff', $KTHff);                                                              
                $stmt_u_au->execute();                                          
            } 
  
            // 8: Uppdatera tabortff med raduppgifter
            $stmt_u_t->bindParam(':DatumTid', $DatumTid);
            $stmt_u_t->bindParam(':Postnr', $Postnr);
            $stmt_u_t->bindParam(':MinRadnrAF', $MinRadnrAF);
            $stmt_u_t->bindParam(':MaxRadnrAF', $MaxRadnrAF);                                     
            $stmt_u_t->execute();  

        }

    }

    $logTiming('analyze WoS records');
   
    $sql_d = "SELECT max(Postnr) AS MaxPostnr FROM filrad WHERE Persondatum = '" . $DatumTid . "'";
    $stmt = $pdo->query( $sql_d );
    foreach ($stmt as $row) {
            $MaxPostnr = $row['MaxPostnr'];        
    }     
   
    if ($KTH_dela_fil) { // Posterna delas upp i småfiler om 25 poster, tillägg 2021-04-06 CEWI
    
    	$antal_poster = $MaxPostnr;
    	$antal_per_fil = 25;
    	$antal_filer = (int) ($antal_poster / $antal_per_fil);
    	if ($antal_poster > ($antal_filer * $antal_per_fil)) {
       	   $antal_filer = $antal_filer +  1;	  
    	}
    	$antal_PT = 0;
    	$rad_f_1 = 'FN Clarivate Analytics Web of Science';
    	$rad_f_2 = 'VR 1.0';
    	$rad_s_1 = '';
    	$rad_s_2 = 'EF';   
    	$antal_skrivna_filer = 0; 
    	$filnamnsslut = '1';
    	$filnamn = '';
        $fp_ut = null;
   	   	              
        // Läs tabellerna
        $stmt_ut = $pdo->prepare("SELECT filrad.Radnr, filrad.Rad, filrad.Postnr AS fPostnr, filrad.KTHff, tabortff.Postnr AS tPostnr, 
        tabortff.AntalKTH, tabortff.Antalff, tabortff.MinRadnrAF,   tabortff.MaxRadnrAF 
        FROM filrad LEFT JOIN tabortff ON filrad.Postnr = tabortff.Postnr AND filrad.Persondatum = tabortff.Persondatum 
        WHERE filrad.Persondatum = :DatumTid ORDER BY filrad.Postnr, filrad.Radnr");
        $stmt_ut->bindParam(':DatumTid', $DatumTid);
        $stmt_ut->execute();
        $AktPostnr = -1;

            foreach ($stmt_ut as $row) { 
               $skriv = 1; 
               $Radnr = $row['Radnr'];              
               $Rad = $row['Rad'];  
               $fPostnr = $row['fPostnr']; 
               $KTHff = $row['KTHff']; 
               $tPostnr = $row['tPostnr'];
               $Antalff = $row['Antalff'];
               $MinRadnrAF = $row['MinRadnrAF'];
               $MaxRadnrAF = $row['MaxRadnrAF'];
               if ($fPostnr <> $AktPostnr) {
                  $AktPostnr = $fPostnr;
                  $nypostaf = 1;
                  $nypostau = 1;
                  $nypostc1 = 1;
                  $Antal_AF = 0;
                  $Antal_AU = 0;
                  $Skriv_etal_AF = 0; 
                  $Skriv_etal_AU = 0;
               } 
               
               if (substr($Rad, 0, 2) == 'AF' or substr($Rad, 0, 2) == 'AU' or substr($Rad, 0, 2) == 'C1') {
                  // AF
                  if (substr($Rad, 0, 2) == 'AF') {
                     if ($nypostaf == 1 ) {
                        $nypostaf = 0;   
                        if  ($KTHff == 1 and $KTH_led_tr) { // RÄTTAT HÄR
                             $Rad = str_replace('AF ','AF $$$',$Rad);                             
                        }                             
                     }
                     else {
                        if ($KTHff == null and $tPostnr > 0) {
                            if ($Radnr != $MaxRadnrAF) {
                               $skriv = 0; 
                            }
                        }  
                        if ($KTHff == 1 and $KTH_led_tr) {                                          
                            $Rad = str_replace('AF ','   $$$',$Rad);       
                        }  
                        else {                           
                            $Rad = str_replace('AF','  ',$Rad);                            
                        }                                             
                     }

                     $Antal_AF = $Antal_AF + 1;
                     if ($Antal_AF == $Antalff) {
                        $Skriv_etal_AF = 1; 
                     }
                  }
               
                  // AU
                  elseif (substr($Rad, 0, 2) == 'AU') {
                     if ($nypostau == 1 ) {
                        $nypostau = 0;  
                     }
                     else {
                        if ($KTHff == null and $tPostnr > 0) {
                            if ($MaxRadnrAF-$Antalff != $Radnr) {
                               $skriv = 0;                                                  
                            }
                        }
                        $Rad = str_replace('AU','  ',$Rad);
                     }
                     $Antal_AU = $Antal_AU + 1;
                     if ($Antal_AU == $Antalff) {
                        $Skriv_etal_AU = 1; 
                     }
                  }
               
                  // C1
                  else {
                     if ($nypostc1 == 1 ) {
                        $nypostc1 = 0;                    
                     }
                     else {
                        $Rad = str_replace('C1','  ',$Rad);
                     }
                  }
               }
               
               if ($Radnr == 1) {
                     // Öppna ny fil för utskrift
                     $filnamn = $filnamn_ut . '_' . $filnamnsslut . '.txt';                   
                     $fp_ut = fopen('php://temp', 'w+');   
                                        
               }
               
               if (substr($Rad, 0, 2) == 'PT') {
                  
                  if ($antal_PT == 0 && $antal_skrivna_filer > 0) {
                     // Öppna ny fil för utskrift
                     $filnamnsslut = (string) ($antal_skrivna_filer + 1);
                     $filnamn = $filnamn_ut . '_' . $filnamnsslut . '.txt';                     
                     $fp_ut = fopen('php://temp', 'w+'); 
                     
                     // Inledande rader i filen 
                     $line = $rad_f_1 . PHP_EOL;
                     fwrite($fp_ut, $line); 
                     $line = $rad_f_2 . PHP_EOL;
                     fwrite($fp_ut, $line);                                                                                  
                  } 

                  $antal_PT = $antal_PT + 1;       
                                                    
               }

               if (is_resource($fp_ut)) {
                  if ($skriv == 1) {
                     $line = $Rad;
                     fwrite($fp_ut, $line);
                  }

                  if ($Skriv_etal_AF == 1) {
                     $Skriv_etal_AF = 0; 
                     $line = '   et al.' . PHP_EOL;
                     fwrite($fp_ut, $line);
                  }
                  if ($Skriv_etal_AU == 1) {
                     $Skriv_etal_AU = 0; 
                     $line = '   et al.' . PHP_EOL;
                     fwrite($fp_ut, $line);
                  }
               }
               
               if ($antal_PT == $antal_per_fil && substr($Rad, 0, 2) == 'ER' && $antal_filer > $antal_skrivna_filer + 1) {
                   $line = $rad_s_1 . PHP_EOL;
                   fwrite($fp_ut, $line); 
                   $line = $rad_s_2 . PHP_EOL;
                   fwrite($fp_ut, $line);                          
                   // Stäng utfil
                   $addGeneratedFile($filnamn, $fp_ut);
                   $fp_ut = null;
                   $antal_PT = 0;    
                   $antal_skrivna_filer = $antal_skrivna_filer + 1; 
                                                    
               }
               
               if ($antal_filer == $antal_skrivna_filer + 1 && substr($Rad, 0, 2) == 'EF') {                    
                   // Stäng utfil
                   $addGeneratedFile($filnamn, $fp_ut);
                   $fp_ut = null;
                                                         
               }               

            }    	
    
    }
    else { // En utfil skapas

        // Öppna ny fil för utskrift
        $filnamn_ut = $filnamn_ut . '.txt';         
        $fp_ut = fopen('php://temp', 'w+');
        // Läs tabellerna
        $stmt_ut = $pdo->prepare("SELECT filrad.Radnr, filrad.Rad, filrad.Postnr AS fPostnr, filrad.KTHff, tabortff.Postnr AS tPostnr, 
        tabortff.AntalKTH, tabortff.Antalff, tabortff.MinRadnrAF,   tabortff.MaxRadnrAF 
        FROM filrad LEFT JOIN tabortff ON filrad.Postnr = tabortff.Postnr AND filrad.Persondatum = tabortff.Persondatum 
        WHERE filrad.Persondatum = :DatumTid ORDER BY filrad.Postnr, filrad.Radnr");
        $stmt_ut->bindParam(':DatumTid', $DatumTid);
        $stmt_ut->execute();
        $AktPostnr = -1;

            foreach ($stmt_ut as $row) { 
               $skriv = 1; 
               $Radnr = $row['Radnr'];              
               $Rad = $row['Rad'];  
               $fPostnr = $row['fPostnr']; 
               $KTHff = $row['KTHff']; 
               $tPostnr = $row['tPostnr'];
               $Antalff = $row['Antalff'];
               $MinRadnrAF = $row['MinRadnrAF'];
               $MaxRadnrAF = $row['MaxRadnrAF'];
               if ($fPostnr <> $AktPostnr) {
                  $AktPostnr = $fPostnr;
                  $nypostaf = 1;
                  $nypostau = 1;
                  $nypostc1 = 1;
                  $Antal_AF = 0;
                  $Antal_AU = 0;
                  $Skriv_etal_AF = 0; 
                  $Skriv_etal_AU = 0;
               } 
               
               if (substr($Rad, 0, 2) == 'AF' or substr($Rad, 0, 2) == 'AU' or substr($Rad, 0, 2) == 'C1') {
                  // AF
                  if (substr($Rad, 0, 2) == 'AF') {
                     if ($nypostaf == 1 ) {
                        $nypostaf = 0;   
                        if  ($KTHff == 1 and $KTH_led_tr) { // RÄTTAT HÄR
                             $Rad = str_replace('AF ','AF $$$',$Rad);                             
                        }                             
                     }
                     else {
                        if ($KTHff == null and $tPostnr > 0) {
                            if ($Radnr != $MaxRadnrAF) {
                               $skriv = 0; 
                            }
                        }  
                        if ($KTHff == 1 and $KTH_led_tr) {                                          
                            $Rad = str_replace('AF ','   $$$',$Rad);       
                        }  
                        else {                           
                            $Rad = str_replace('AF','  ',$Rad);                            
                        }                                             
                     }

                     $Antal_AF = $Antal_AF + 1;
                     if ($Antal_AF == $Antalff) {
                        $Skriv_etal_AF = 1; 
                     }
                  }
               
                  // AU
                  elseif (substr($Rad, 0, 2) == 'AU') {
                     if ($nypostau == 1 ) {
                        $nypostau = 0;  
                     }
                     else {
                        if ($KTHff == null and $tPostnr > 0) {
                            if ($MaxRadnrAF-$Antalff != $Radnr) {
                               $skriv = 0;                                                  
                            }
                        }
                        $Rad = str_replace('AU','  ',$Rad);
                     }
                     $Antal_AU = $Antal_AU + 1;
                     if ($Antal_AU == $Antalff) {
                        $Skriv_etal_AU = 1; 
                     }
                  }
               
                  // C1
                  else {
                     if ($nypostc1 == 1 ) {
                        $nypostc1 = 0;                    
                     }
                     else {
                        $Rad = str_replace('C1','  ',$Rad);
                     }
                  }
               }

               if ($skriv == 1) {
                  $line = $Rad;
                  fwrite($fp_ut, $line);
               }

               if ($Skriv_etal_AF == 1) {
                  $Skriv_etal_AF = 0; 
                  $line = '   et al.' . PHP_EOL;
                  fwrite($fp_ut, $line);
               }
               if ($Skriv_etal_AU == 1) {
                  $Skriv_etal_AU = 0; 
                  $line = '   et al.' . PHP_EOL;
                  fwrite($fp_ut, $line);
               }

            }
        // Stäng utfil
        $addGeneratedFile($filnamn_ut, $fp_ut);
        
     } // Slut på dela utfil i flera eller ej 2021-04-06 CEWI    
        
        // Öppna listfil för antal författare
        $fp_lista = fopen('php://temp', 'w+');
        $stmt_lista = $pdo->prepare("SELECT SUBSTRING(f.Rad,4) AS Rad_utan, t.Antalff FROM tabortff t JOIN filrad f ON t.Postnr = f.Postnr 
        WHERE SUBSTRING(f.Rad,1,2) = 'TI' AND t.Persondatum = :DatumTid AND t.Persondatum = f.Persondatum ORDER BY t.Postnr");
        $stmt_lista->bindParam(':DatumTid', $DatumTid);
        $stmt_lista->execute();

        foreach ($stmt_lista as $row) { 
           $radnr_lista = $radnr_lista + 1; 
           $Rad_utan = $row['Rad_utan']; 
           $Antalff = $row['Antalff'];
           $line = $radnr_lista . "  " . $Rad_utan . "  " . $Antalff . PHP_EOL; 
           fwrite($fp_lista, $line);           
        }     
        
        // Stäng listfil
        $addGeneratedFile($filnamn_lista, $fp_lista);
        $logTiming('generate email attachments');
        $message = "Din WoS-fil har behandlats klart." . PHP_EOL . PHP_EOL .
                   "Inskickad fil: " . $uploadedFileName . PHP_EOL .
                   "Vald filtyp: " . $filtypLabel . PHP_EOL .
                   "Vald KTH-ledtråd: " . $kthLedtradLabel . PHP_EOL .
                   "Vald delning i filer med 25 poster: " . $kthDelaFilLabel . PHP_EOL .
                   "Vald handläggare: " . ($handlaggare !== '' ? $handlaggare : '-') . PHP_EOL .
                   "Antal skapade filer: " . $generatedFileCount . PHP_EOL . PHP_EOL .
                   "Alla behandlade filer finns bifogade.";

        $mail->setFrom('biblioteket@kth.se');
        $mail->clearAddresses();
        $mail->addAddress($Epost);
        $mail->Subject  = 'Behandlade filer: ' . $uploadedFileName;
        $mail->Body     = $message;
        $emailDeliveryAttempted = true;
        if(!$mail->send()) {
           $emailSent = false;
           $logTiming('send email failed');
           error_log('DiVA import email delivery failed: ' . $mail->ErrorInfo);
           $doneMessage = 'Filen är klar, men e-postleveransen misslyckades.';
        }
        else {
           $emailSent = true;
           $logTiming('send email');
           echo ' Nya filer har skickats.';
           $doneMessage = 'Filen är klar. Behandlade filer har skickats via e-post.';
        }
     
    // *** Slut Wos-delen ***
    }
    // *** Börja Scopus-delen ***
    else { // Behandla Scopus-fil
    // Loopa genom filen för kontroll - början
        $filnamn = $filnamn_ut;
        $fp_ut = fopen($filnamn, 'w'); // CEWI 2021-09-21

        while ($line = fgets($fh_in)) {
            $radnr = $radnr + 1;       
            $taggen = substr($line, 0, 2);
            // Rensa abstract från copyright
            if ($taggen == "AB") { // rensa abstract från copyright

                $lgd = strlen($line);
                $pos = stripos($line,'©'); // function is case-insensitive
                if ($pos > 0) {
                   $line = substr($line,0,$lgd-($lgd-$pos)) . PHP_EOL;
                }
            }   
            fwrite($fp_ut, $line);   
  
        // Loopa genom filen för kontroll - slut  
        }

        fclose($fh_in);
        fclose($fp_ut);

    // *** Slut Scopus-delen ***
    } 

    $stmt = $pdo->prepare("DELETE FROM filrad WHERE Persondatum = :DatumTid");
    $stmt->bindParam(':DatumTid', $DatumTid);
    $stmt->execute();
    $stmt = $pdo->prepare("DELETE FROM tabortff WHERE Persondatum = :DatumTid");
    $stmt->bindParam(':DatumTid', $DatumTid);
    $stmt->execute();

    if ($doneMessage === '') {
        $doneMessage = $emailDeliveryAttempted && $emailSent
            ? 'Filen är klar. Behandlade filer har skickats via e-post.'
            : 'Filen är klar.';
    }

 }
    }
    catch (Throwable $e) {
        if ($pdo instanceof PDO && $pdo->inTransaction()) {
            try {
                $pdo->rollBack();
            }
            catch (Throwable $rollbackException) {
                error_log('DiVA import rollback failed: ' . $rollbackException->getMessage());
            }
        }

        error_log('DiVA import failed: ' . $e->getMessage());
        if ($doneMessage === '') {
            $doneMessage = 'Filen kunde inte behandlas. Kontakta systemansvarig om felet kvarstår.';
        }
    }
    finally {
        if (is_resource($fp_lista)) {
            fclose($fp_lista);
        }
        if (is_resource($fp_ut)) {
            fclose($fp_ut);
        }
        if (is_resource($fh_in)) {
            fclose($fh_in);
        }
        if (isset($mail) && $mail instanceof PHPMailer) {
            $mail->smtpClose();
        }
        if (!isset($_SESSION['diva_import_token'])) {
            $_SESSION['diva_import_token'] = bin2hex(random_bytes(16));
        }
    }
}

if (!isset($_SESSION['diva_import_token'])) {
    $_SESSION['diva_import_token'] = bin2hex(random_bytes(16));
}
$formToken = $_SESSION['diva_import_token'];

?>

<script type="text/javascript">

<?php if ($wasPost) { ?>
if (window.history && window.history.replaceState) {
     window.history.replaceState(null, document.title, window.location.pathname);
}
<?php } ?>

var formSubmitting = false;

function handleSubmit() {
     if (formSubmitting) {
        return false;
     }

     formSubmitting = true;

     var status = document.getElementById("processingStatus");
     var doneStatus = document.getElementById("doneStatus");
     var button = document.getElementById("behandlaButton");

     if (doneStatus) {
        doneStatus.style.display = 'none';
     }

     if (status) {
        status.style.display = 'block';
     }

     if (button) {
        button.disabled = true;
        button.value = 'Behandlar...';
        button.style.backgroundColor = '#999999';
        button.style.cursor = 'not-allowed';
     }

     return true;
}

function handleClick(Typ) {
      
     var a = document.getElementById("DIV_Ledtr");
     var b = document.getElementById("DIV_Dela");  
 
     if (document.getElementById('r2').checked) {
        a.style.display = 'none';
        b.style.display = 'none'; 
     }
     else {
        a.style.display = 'block';
        b.style.display = 'block';         
     }
            
}

</script>

</head>

<body>

<h2>MANUELLT UTTAGEN FRÅN WOS ELLER SCOPUS</h2>
<br />
<h3>FÖR FILTYPEN WOS GÖRS:</h3>
1) Tar bort författare om de är fler än 30. Då skrivs första författare, KTH-författare och sista författare med tillägget "et al." <br />
2) Val av KTH-ledtråd ger $$$ framför KTH-författare <br />
3) Tar bort copyright-texter i Abstract <br />
<h3>FÖR FILTYPEN SCOPUS GÖRS:</h3>
Tar bort copyright-texter i Abstract <br />
<br />

<form action="d_behandlafil_man_m_l_25.php" method="post" enctype="multipart/form-data" onsubmit="return handleSubmit();">
    <input type="hidden" name="behandla" value="1" />
    <input type="hidden" name="import_token" value="<?php echo htmlspecialchars($formToken); ?>" />
    <h3>VÄLJ FILTYP WOS ELLER SCOPUS</h3>
    Ange filtyp:
    <label><Input type = 'Radio' id = "r1" Name = 'Filtyp' onclick="javascript:handleClick(this);" value= 'wos' checked>WoS</label>
    <label><Input type = 'Radio' id = "r2" Name = 'Filtyp' onclick="javascript:handleClick(this);" value= 'scopus'>Scopus</label>
    <br /><br />

    <div id="DIV_Ledtr">
    Ange om KTH-ledtråd:
    <label><Input type = 'Radio' Name ='KTH_l_t' value= 'med' checked>Med</label>
    <label><Input type = 'Radio' Name ='KTH_l_t' value= 'utan'>Utan</label>
    </div>
      
    <br />
 
    <div id="DIV_Dela">   
    Ange om delning i filer med 25 poster:
    <label><Input type = 'Radio' Name ='KTH_d_f' value= 'ja' checked>Ja</label>
    <label><Input type = 'Radio' Name ='KTH_d_f' value= 'nej'>Nej</label>    
    </div>
    <br />
    <b>Välj fil att ladda upp:</b>
    <input type="file" name="fileToUpload" id="fileToUpload">
    <br />    
    <br />

                <b>Handläggare:</b><br />
                <select id="Handlista" name="Handl">
                    <option value=""></option>
                    <option value="Anders">Anders</option>
                    <option value="Cecilia">Cecilia</option>
                    <option value="Greta">Greta</option>
                    <option value="Johan">Johan</option>
                    <option value="Michael">Michael</option>
                    <option value="Ulf">Ulf</option>
		    <option value="Margareta">Margareta</option>	
                </select>
                <br /><br />
                
		<b>Skicka till e-post:</b> &nbsp;
		<input type="text" name="Epost" size="30"/>&nbsp;&nbsp; 
                <br /><br /><br />               

    <?php if ($doneMessage != '') { ?>
        <div id="doneStatus" style="font-weight:bold; color:#0b6b0b; margin-bottom:10px;">
            <?php echo htmlspecialchars($doneMessage); ?>
        </div>
    <?php } ?>
    <div id="processingStatus" style="display:none; font-weight:bold; color:#0b6b0b; margin-bottom:10px;">
        Filen behandlas. Knappen aktiveras igen när processen är klar.
    </div>
    <input type="submit" id="behandlaButton" style="background-color:#0fb821" value="Behandla"/><br /><br />

</form>

<br /><br /><br />
<a href='d_importmeny.php'>TILLBAKA</a>

</body>
</html>
