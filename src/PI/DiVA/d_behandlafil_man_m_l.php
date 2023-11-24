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

if (isset($_POST['behandla'])) {

    $dbname = "bibmet";
    $username = $_SESSION['anv'];
    $password = $_SESSION['ord'];

    $pdo = new PDO("mysql:host=$hostname;dbname=$dbname", $username, $password);

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "SELECT CURRENT_TIMESTAMP() AS DatumTid";
    $stmt = $pdo->query( $sql );
    foreach ($stmt as $row) {
            $DatumTid = $row['DatumTid'];        
    }

    $stmt_f = $pdo->prepare("INSERT INTO filrad (Persondatum,Radnr,Postnr,Rad) VALUES (:DatumTid,:Radnr,:Postnr,:Rad)");

    $Filtyp = $_POST['Filtyp'];
    $KTH_l_t = $_POST['KTH_l_t'];
    if ($KTH_l_t == 'med') {
        $KTH_led_tr = TRUE;       
    }
    else {
        $KTH_led_tr = FALSE;         
    }

    //$Filnamnet = $_POST['Filnamnet'];
    if ($Filtyp == 'wos') {
       $Filnamnet = 'DATAFILER\ISI.txt';        
    }
    else {
       $Filnamnet = 'DATAFILER\Scopus.txt';            
    }

    // TEST Max antal författare, 4 som testvärde TEST
    $Maxff = 30;

    // Fil att läsa in
    $filnamn_in = $Filnamnet;
    // Öppna infil
    $fh_in = fopen($filnamn_in,'r');
    $radnr = 0;
    $postnr = 0;
    $tilldela_af = 0;
    $tilldela_c1 = 0;

    // Fil att skriva ut
    $filnamn_ut = $filnamn_in;
    $filnamn_ut = str_replace('.txt','',$filnamn_ut);
    $handl = $_POST['Handl'];
    if (strlen($handl) > 0) {
       $handl = '_' . $handl;
    }
    //$filnamn_ut = $filnamn_ut . '_UT.txt';
    $filnamn_ut = $filnamn_ut . $handl . '_UT.txt';
    // Öppna utfil
    $fp_ut = fopen($filnamn_ut, 'w');
    // Fil att lista antal författare
    $filnamn_lista = $filnamn_in;
    $filnamn_lista = str_replace('.txt','',$filnamn_lista);
    $filnamn_lista = $filnamn_lista . $handl . '_ANTAL_FF.txt';
    $radnr_lista = 0;

    // *** WoS ***
    if ($Filtyp == 'wos') { // Behandla WoS-fil

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
            $stmt_f->bindParam(':DatumTid', $DatumTid);
            $stmt_f->bindParam(':Radnr', $radnr);
            $stmt_f->bindParam(':Postnr', $postnr);
            $stmt_f->bindParam(':Rad', $line);

try {
            $stmt_f->execute(); 
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
$stmt_x = $pdo->prepare("INSERT INTO filrad (Persondatum,Radnr,Postnr,Rad) VALUES (:DatumTid,:Radnr,:Postnr,'FEL')");
            $stmt_f->bindParam(':DatumTid', $DatumTid);
            $stmt_f->bindParam(':Radnr', $radnr);
            $stmt_f->bindParam(':Postnr', $postnr);
$stmt_x->execute();
}



            // IDAG 2020-03-03 $stmt_f->execute();  
        // Loopa genom filen för kontroll - slut           
        }

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

        // Öppna ny fil för utskrift

        $fp_ut = fopen($filnamn_ut, 'w');
        // Läs tabellerna
        $stmt_ut = $pdo->prepare("SELECT filrad.Radnr, filrad.Rad, filrad.Postnr AS fPostnr, filrad.KTHff, tabortff.Postnr AS tPostnr, tabortff.AntalKTH, tabortff.Antalff, tabortff.MinRadnrAF, tabortff.MaxRadnrAF 
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
        fclose($fh_ut);

        // Öppna listfil för antal författare
        $fp_lista = fopen($filnamn_lista, 'w');
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
        fclose($fh_lista);
     
    // *** Slut Wos-delen ***
    }
    // *** Börja Scopus-delen ***
    else { // Behandla Scopus-fil
    // Loopa genom filen för kontroll - början

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
        fclose($fh_ut);

    // *** Slut Scopus-delen ***
    } 

    $stmt = $pdo->prepare("DELETE FROM filrad WHERE Persondatum = :DatumTid");
    $stmt->bindParam(':DatumTid', $DatumTid);
    $stmt->execute();
    $stmt = $pdo->prepare("DELETE FROM tabortff WHERE Persondatum = :DatumTid");
    $stmt->bindParam(':DatumTid', $DatumTid);
    $stmt->execute();

    echo "<script>alert('Filen är klar!');</script>";

}

?>

</head>

<body>

<h2>MANUELLT UTTAGEN FRÅN WOS ELLER SCOPUS</h2>
<br />
<h3>FÖR FILTYPEN WOS GÖRS:</h3>
<h4>1) Tar bort författare om de är fler än 30. Då skrivs första författare, KTH-författare och sista författare med tillägget "et al."</h4>
<h4>2) Val av KTH-ledtråd ger $$$ framför KTH-författare</h4>
<h4>3) Tar bort copyright-texter i Abstract</h4>
<h3>FÖR FILTYPEN SCOPUS GÖRS:</h3>
<h4>Tar bort copyright-texter i Abstract</h4>
<br />

<form action="d_behandlafil_man_m_l.php" method="post">
    <h3>VÄLJ FILTYP WOS ELLER SCOPUS</h3>
    Ange filtyp:
    <label><Input type = 'Radio' Name ='Filtyp' value= 'wos' checked>WoS</label>
    <label><Input type = 'Radio' Name ='Filtyp' value= 'scopus'>Scopus</label>
    <br /><br />

    Ange om KTH-ledtråd:
    <label><Input type = 'Radio' Name ='KTH_l_t' value= 'med' checked>Med</label>
    <label><Input type = 'Radio' Name ='KTH_l_t' value= 'utan'>Utan</label>
    <br /><br />

                <b>Handläggare:</b><br />
                <select id="Handlista" name="Handl">
                    <option value=""></option>
                    <option value="Anders">Anders</option>
                    <option value="Camilla">Camilla</option>
                    <option value="Cecilia">Cecilia</option>
                    <option value="Greta">Greta</option>
                    <option value="Hamrin">Hamrin</option>
                    <option value="Johan">Johan</option>
                    <option value="Margareta">Margareta</option>
                    <option value="Sam">Sam</option>
                    <option value="Ulf">Ulf</option>
                </select>
                <br /><br /><br /> 

    <input type="submit" name="behandla" style="background-color:#0fb821" value="Behandla"/><br /><br />

</form>

<br /><br /><br />
<a href='d_importmeny.php'>TILL MENYN</a>

</body>
</html>