<?php 
    session_start(); 
    require_once('config.php.inc');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml11/DTD/xhtml-transitional.dtd">

<! Författare: Cecilia Wiklander>
<! Syfte: DiVA-hantering>
<! Ändringar: >

<head>

    <meta charset="utf-8">

    <title>REGISTRERADE PERSONUPPGIFTER</title>

    <link href="Site_utan_storlek.css" rel="stylesheet">

</head>

<body>

<?php include('include_personal.html'); ?>

<h2>REGISTRERADE PERSONUPPGIFTER</h2>

<a href='d_soek_personal.php'>TILL SÖKNING</a>

<br /><br />

<?php

    //$username = "pi_anv";
    //$password = "svqt%1";
    $username = $_SESSION['anv'];
    $password = $_SESSION['ord'];
    $dbname = "hant_diva";
    $granskad = $_SESSION['granskad'];

    $Namn = $_POST['Namn'];
    $KTH_id = $_POST['KTH_id']; 

    $pdo = new PDO("mysql:host=$hostname;dbname=$dbname", $username, $password);

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $pos = stripos($Namn,','); 
    if ($pos > 0) {
       $Lgd = strlen($Namn);
       $Enamn = substr($Namn,0,$pos);
       $Fnamn = substr($Namn,$pos+1);
    }
    else {
       $Fnamn = $_POST['Fnamn'];
       $Enamn = $_POST['Enamn'];
    }

    $Fnamn = trim($Fnamn);
    $Enamn = trim($Enamn);
    $Test_Fnamn = 'x' . trim($Fnamn);
    $Test_Enamn = 'x' . trim($Enamn);
    $Fnamn = str_replace ("*","",$Fnamn);
    $Enamn = str_replace ("*","",$Enamn);
    $KTH_id = trim($KTH_id); 

    $sql = "SELECT p.KTH_id,CONCAT(p.Enamn,', ',p.Fnamn) AS Namn,
    (SELECT ok.ORCIDid FROM orcid_kthid ok WHERE ok.KTH_id = p.KTH_id) AS ORCIDid,
    (SELECT o1.Orgnamn FROM organisation o1 WHERE o1.Orgkod = p.Orgkod) AS Orgnamn,
    (SELECT o2.Orgnamn FROM organisation o2 WHERE o2.Orgkod = p.Skol_kod) AS Skola,
    p.Bef_ben AS Befattning,p.Anst_nuv_bef AS Fr,p.Bef_t_o_m AS Till,p.Fil_datum AS Datum 
    FROM personal p WHERE ";

    $sql_efter = " ORDER BY p.Enamn, p.Fnamn, p.Anst_nuv_bef, p.Fil_datum";

    // Både förnamn och efternamn ifyllt att söka på

    if (strlen($Enamn) > 0 && strlen($Fnamn) > 0) {
        if (stripos($Test_Fnamn,'*') || stripos($Test_Enamn,'*')) {
             if (stripos($Test_Fnamn,'*')) {
                $F_sql = "p.Fnamn LIKE '";
                $fpos = 0;
                if (substr($Test_Fnamn,1,1) == '*') {
                    $fpos = 1;
                    $F_sql = $F_sql . "%" . $Fnamn;
                }
                if (substr($Test_Fnamn,strlen($Test_Fnamn)-1,1) == '*') {
                    if ($fpos == 1) {
                        $F_sql = $F_sql . "%";                        
                    }
                    else {
                        $fpos = 1;
                        $F_sql = $F_sql . $Fnamn . "%";                         
                    }
                } 

                $F_sql = $F_sql . "'";                               
             } 
             if (stripos($Test_Enamn,'*')) {
               
                $E_sql = "p.Enamn LIKE '";
                $epos = 0;
                if (substr($Test_Enamn,1,1) == '*') {
                  
                    $epos = 1;
                    $E_sql = $E_sql . "%" . $Enamn;
                }
                if (substr($Test_Enamn,strlen($Test_Enamn)-1,1) == '*') {
                  
                    if ($epos == 1) {
                        $E_sql = $E_sql . "%";                        
                    }
                    else {
                        $epos = 1;
                        $E_sql = $E_sql . $Enamn . "%";                         
                    }
                } 
                $E_sql = $E_sql . "'";                               
             }  
            
             if (strlen($F_sql) == 0) { 
             	$F_sql = "p.Fnamn = '" . $Fnamn . "'";
             }
             if (strlen($E_sql) == 0) { 
             	$E_sql = "p.Enamn = '" . $Enamn . "'";
             }             
             $sql = $sql . $F_sql . " AND " . $E_sql;   
             
                                                                               
        }
        // Sökning på hela namn, både för- och efternamn
        else {
             $sql = $sql . "p.Fnamn = '" . $Fnamn . "' AND p.Enamn = '" . $Enamn . "' ";
        }
        $sql = $sql . $sql_efter;

        if ($granskad == 'KONTROLL') {
        $stmt = $pdo->query( $sql );
        }
    }
    else {
        // Enbart att efternamn ifyllt att söka på
        if (strlen($Enamn) > 0 && strlen($Fnamn) == 0) {
             if (stripos($Test_Enamn,'*') > 0) {
                $E_sql = "p.Enamn LIKE '";
                $epos = 0;
                if (substr($Test_Enamn,1,1) == '*') {
                    $epos = 1;
                    $E_sql = $E_sql . "%" . $Enamn;
                }
                if (substr($Test_Enamn,strlen($Test_Enamn)-1,1) == '*') {
                    if ($epos == 1) {
                        $E_sql = $E_sql . "%";                        
                    }
                    else {
                        $epos = 1;
                        $E_sql = $E_sql . $Enamn . "%";                         
                    }
                } 
                $sql = $sql . $E_sql . "'";                               
             }  
             else {
                $sql = $sql . "p.Enamn = '" . $Enamn . "' ";                 
             }
             $sql = $sql . $sql_efter;
             if ($granskad == 'KONTROLL') {
             $stmt = $pdo->query( $sql ); 
             }           
        }
        else {
            // Enbart förnamn ifyllt att söka på
            if (strlen($Fnamn) > 0 && strlen($Enamn) == 0) {
                if (stripos($Test_Fnamn,'*')) {
                    $F_sql = "p.Fnamn LIKE '";
                    $fpos = 0;
                    if (substr($Test_Fnamn,1,1) == '*') {
                        $fpos = 1;
                        $F_sql = $F_sql . "%" . $Fnamn;
                    }
                    if (substr($Test_Fnamn,strlen($Test_Fnamn)-1,1) == '*') {
                        if ($fpos == 1) {
                            $F_sql = $F_sql . "%";                        
                        }
                        else {
                              $fpos = 1;
                              $F_sql = $F_sql . $Fnamn . "%";                         
                        }
                    } 
                     $sql = $sql . $F_sql . "'";                               
                }  
                else {
                      $sql = $sql . "p.Fnamn = '" . $Fnamn . "' ";                 
                }
                $sql = $sql . $sql_efter;   
                if ($granskad == 'KONTROLL') {            
                $stmt = $pdo->query( $sql );   
                }                             
            }
            else {
                 if (strlen($KTH_id) > 0) {
                    $sql = $sql . "p.KTH_id = '" . $KTH_id . "' ";
                    $sql = $sql . $sql_efter;
                    if ($granskad == 'KONTROLL') {
                    $stmt = $pdo->query( $sql ); 
                    }                      
                 }
                 else {
                      echo "<script>alert('Uppgifter är inte rätt ifyllda! Har du följt angivet format?');</script>"; 
                 }                  
            }         
        }
    }
	
	echo "<table border='1'>";

	// Rubrikerna
	echo "<tr>";
	echo "<th>KTH-id</th> <th>Namn</th> <th>ORCIDid</th> <th>Organisation</th> <th>Skola</th> <th>Befattning</th> <th>Från</th> <th>Till</th> <th>Datum</th>";
	echo "</tr>";
		 
	// Lägg ut resultatet
	foreach ($stmt as $row) {
			echo "<tr>";
			echo "<td>" . $row['KTH_id'] . "</td>";
			echo "<td>" . $row['Namn'] . "</td>";
			echo "<td>" . $row['ORCIDid'] . "</td>";
			echo "<td>" . $row['Orgnamn'] . "</td>";
			echo "<td>" . $row['Skola'] . "</td>";
			echo "<td>" . $row['Befattning'] . "</td>";
			echo "<td>" . $row['Fr'] . "</td>";  
			echo "<td>" . $row['Till'] . "</td>";                   
			echo "<td>" . $row['Datum'] . "</td>";                                                             						
			echo "</tr>";
	}

	echo "</table>";
	echo "<br /><br /><br />";

?>

</body>
</html>