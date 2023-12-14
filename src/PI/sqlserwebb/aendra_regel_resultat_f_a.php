<?php session_start(); ?>

<!DOCTYPE html PUBLIC "-//w3c//DTD XHTMLm 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<! Författare: Cecilia Wiklander>
<! Syfte: Adressrättnings-hantering>
<! Ändringar: >

<head>

    <meta charset="utf-8">

    <title>ÄNDRA REGEL FULL ADRESS</title>
	
    <link href="Site.css" rel="stylesheet"> 
	
</head>

<body>

<?php include('include_head_new.html'); ?>

<?php
    
    $regel_id = $_SESSION['regel_id'];

    $_SESSION['regel_id_ut'] = $regel_id;

    $username = $_SESSION['anv'];
    $password = $_SESSION['ord'];
    $hostname = $_SESSION['hnamn'];
    $dbname = $_SESSION['dbnamn'];

    $dbh = new PDO("sqlsrv:Server=$hostname;Database=$dbname",$username,$password);

    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $a_regel_f_a_id = $_SESSION['a_regel_f_a_id'];

    $land_till = $_POST['Land_ut_2'];

    $stad_till = $_POST['Stad_till'];

    $str_1_till = $_POST['Str_1_till'];

    $str_2_till = $_POST['Str_2_till'];

    $str_3_till = $_POST['Str_3_till'];

    $str_ej_1_till = $_POST['Str_ej_1_till'];

    $str_ej_2_till = $_POST['Str_ej_2_till'];

    $delas_till = $_POST['Delas_ut_2'];

    $land_1_till = $_POST['Land_1_ut_2'];

    $land_2_till = $_POST['Land_2_ut_2'];

    $land_3_till = $_POST['Land_3_ut_2'];

    $stad_1_till = $_POST['Stad_1_till'];

    $stad_2_till = $_POST['Stad_2_till'];

    $stad_3_till = $_POST['Stad_3_till'];

    $org_1_till = $_POST['Org_1_ut_2'];
 
    $org_2_till = $_POST['Org_2_ut_2'];

    $org_3_till = $_POST['Org_3_ut_2'];

    $Sk = "'";
    $Ers = "''";

    $stad_till = str_replace($Sk, $Ers, $stad_till);
    $str_1_till = str_replace($Sk, $Ers, $str_1_till);
    $str_2_till = str_replace($Sk, $Ers, $str_2_till);
    $str_3_till = str_replace($Sk, $Ers, $str_3_till);
    $str_ej_1_till = str_replace($Sk, $Ers, $str_ej_1_till);
    $str_ej_2_till = str_replace($Sk, $Ers, $str_ej_2_till);
    $org_1_till = str_replace($Sk, $Ers, $org_1_till);
    $org_2_till = str_replace($Sk, $Ers, $org_2_till);
    $org_3_till = str_replace($Sk, $Ers, $org_3_till);
    $stad_1_till = str_replace($Sk, $Ers, $stad_1_till);
    $stad_2_till = str_replace($Sk, $Ers, $stad_2_till);
    $stad_3_till = str_replace($Sk, $Ers, $stad_3_till);
    $land_till = str_replace($Sk, $Ers, $land_till);
    $land_1_till = str_replace($Sk, $Ers, $land_1_till);
    $land_2_till = str_replace($Sk, $Ers, $land_2_till);
    $land_3_till = str_replace($Sk, $Ers, $land_3_till);

    // Ändra regeln

    $koll_svar = false;

    if ($land_till == 'Ange land') {
        echo "<script>alert('Land måste anges som sökfält!');</script>";
    }
    else {
        if (strlen($str_1_till) == 0){
            echo "<script>alert('Söksträng 1 måste anges som sökfält!');</script>";
        }
        else {
            if ($org_1_till == 'Ange organisation'){
                echo "<script>alert('Organisation 1 måste anges som ändringsfält!');</script>";
            }
            else {
                if ($delas_till == 1) {
                    if (($org_2_till != 'Ange organisation' || $org_3_till != 'Ange organisation') && (strlen($org_2_till) > 0 || strlen($org_3_till) > 0)) {
                        echo "<script>alert('Antalet i Delas stämmer inte med antal angivna organisationer!');</script>";
                    }
                    else {
                        $koll_svar = true;
                    }
                }
                else if ($delas_till == 2) {
                    if ($org_2_till == 'Ange organisation' || strlen($org_2_till) == 0 || ($org_3_till != 'Ange organisation' && strlen($org_3_till) > 0)){
                        echo "<script>alert('Antalet i Delas stämmer inte med antal angivna organisationer!');</script>";
                    }  
                    else {
                        $koll_svar = true;                                           
                    }
                }
                else if ($delas_till == 3) {
                    if ($org_2_till == 'Ange organisation' || strlen($org_2_till) == 0 || $org_3_till == 'Ange organisation' || strlen($org_3_till) == 0){
                        echo "<script>alert('Antalet i Delas stämmer inte med antal angivna organisationer!');</script>";
                    }  
                    else {
                        $koll_svar = true;
                    }
                }
                else {
                    echo "<script>alert('Antalet i Delas kan vara mellan 1 och 3!');</script>";
                }
            }
        }
    }

    if ($koll_svar) {

        $pos_f = strpos($org_1_till, '[' );
        $pos_e = strpos($org_1_till, ']' );
        $org_1_o = substr($org_1_till, 0, $pos_f - 1);
        $org_1_c = substr($org_1_till, $pos_f + 1, $pos_e - $pos_f - 1);            
          
        if (strlen($org_1_c) > 0) {
            $sql_org_1 = "SELECT Unified_org_id FROM Unified_org_names WHERE Name_en = '" . $org_1_o . "' AND Country_name = '" . $org_1_c . "'";
        } 
        else {
            $sql_org_1 = "SELECT Unified_org_id FROM Unified_org_names WHERE Name_en = '" . $org_1_o . "'";
        }

        $stmt = $dbh->query( $sql_org_1 );
        foreach ($stmt as $row) {
            $org_id_1 = $row['Unified_org_id'];      
        }
        if ($delas_till > 1) {
            if ($org_2_till != 'Ange organisation') {

                $pos_f = strpos($org_2_till, '[' );
                $pos_e = strpos($org_2_till, ']' );
                $org_2_o = substr($org_2_till, 0, $pos_f - 1);
                $org_2_c = substr($org_2_till, $pos_f + 1, $pos_e - $pos_f - 1);            
          
                if (strlen($org_2_c) > 0) {
            	    $sql_org_2 = "SELECT Unified_org_id FROM Unified_org_names WHERE Name_en = '" . $org_2_o . "' AND Country_name = '" . $org_2_c . "'";
                } 
                else {
            	    $sql_org_2 = "SELECT Unified_org_id FROM Unified_org_names WHERE Name_en = '" . $org_2_o . "'";
                }

                $stmt = $dbh->query( $sql_org_2 );
                foreach ($stmt as $row) {
                    $org_id_2 = $row['Unified_org_id'];      
                }
            }
            else {
                $org_id_2 = NULL;
            }
        }
        if ($delas_till > 2) {
            if ($org_3_till != 'Ange organisation') {

                $pos_f = strpos($org_3_till, '[' );
                $pos_e = strpos($org_3_till, ']' );
                $org_3_o = substr($org_3_till, 0, $pos_f - 1);
                $org_3_c = substr($org_3_till, $pos_f + 1, $pos_e - $pos_f - 1);            
          
                if (strlen($org_3_c) > 0) {
            	   $sql_org_3 = "SELECT Unified_org_id FROM Unified_org_names WHERE Name_en = '" . $org_3_o . "' AND Country_name = '" . $org_3_c . "'";
                } 
                else {
            	   $sql_org_3 = "SELECT Unified_org_id FROM Unified_org_names WHERE Name_en = '" . $org_3_o . "'";
                }

                $stmt = $dbh->query( $sql_org_3 );
                foreach ($stmt as $row) {
                    $org_id_3 = $row['Unified_org_id'];      
                }
            }
            else {
                $org_id_3 = NULL;
            }
        }
    }

    if ($land_1_till == 'Ange land') {
        $land_1_till = NULL;
    }
    if ($land_2_till == 'Ange land') {
        $land_2_till = NULL;
    }
    if ($land_3_till == 'Ange land') {
        $land_3_till = NULL;
    }
    if ($org_1_till == 'Ange organisation') {
        $org_1_till = NULL;
    }
    if ($org_2_till == 'Ange organisation') {
        $org_2_till = NULL;
    }
    if ($org_3_till == 'Ange organisation') {
        $org_3_till = NULL;
    }

    if ($koll_svar && $a_regel_f_a_id <> $regel_id) {
        $sql_country = "SELECT Country_code FROM Country WHERE Display_name = '" . $land_till . "'";
        $stmt = $dbh->query( $sql_country );
        foreach ($stmt as $row) {
            $country_code = $row['Country_code'];      
        } 

        if (strlen($stad_till) == 0) {
            $sql_stad_s = "Find_city = null,";
        }
        else {
            $sql_stad_s = "Find_city = '" . $stad_till . "',";
        }
        
        if (strlen($str_2_till) == 0) {
            $sql_str_2 = "Find_str_2 = null,";
            }
        else {
            $sql_str_2 = "Find_str_2 = '" . $str_2_till . "',";
        }

        if (strlen($str_3_till) == 0) {
            $sql_str_3 = "Find_str_3 = null,";
        }
        else {
            $sql_str_3 = "Find_str_3 = '" . $str_3_till . "',";
        }

        if (strlen($str_ej_1_till) == 0) {
            $sql_str_ej_1 = "Find_str_not_1 = null,";
        }
        else {
            $sql_str_ej_1 = "Find_str_not_1 = '" . $str_ej_1_till . "',";
        }

        if (strlen($str_ej_2_till) == 0) {
            $sql_str_ej_2 = "Find_str_not_2 = null,";
        }
        else {
            $sql_str_ej_2 = "Find_str_not_2 = '" . $str_ej_2_till . "',";
        }
        

        if ($delas_till == 1) {
            $sql_u = "UPDATE Rule_full_address_match SET Find_country = '" . $land_till . "',Country_code = '" . $country_code . "',"
            . $sql_stad_s
            . "Find_str_1 = '" . $str_1_till . "'," 
            . $sql_str_2 . $sql_str_3 . $sql_str_ej_1 . $sql_str_ej_2 
            . "Divide = " . $delas_till . ",
            Country_1 = '" . $land_1_till . "',City_1 = '" . $stad_1_till . "',Org_id_1 = " . $org_id_1 .            
            " ,User_id = '" . $username . "',Rule_date = GETDATE(),Run_status = 1, " .
            "Org_id_2 = NULL,Country_2 = NULL,City_2 = NULL,Org_id_3 = NULL,Country_3 = NULL,City_3 = NULL WHERE R_f_a_m_id = " . $regel_id;
        }
        else if ($delas_till == 2) {
            $sql_u = "UPDATE Rule_full_address_match SET Find_country = '" . $land_till . "',Country_code = '" . $country_code . "',"
            . $sql_stad_s
            . "Find_str_1 = '" . $str_1_till . "'," 
            . $sql_str_2 . $sql_str_3 . $sql_str_ej_1 . $sql_str_ej_2 
            . "Divide = " . $delas_till . ",            
            Country_1 = '" . $land_1_till . "',City_1 = '" . $stad_1_till . "',Org_id_1 = " . $org_id_1 . ",
            Country_2 = '" . $land_2_till . "',City_2 = '" . $stad_2_till . "',Org_id_2 = " . $org_id_2 .                             
            " ,User_id = '" . $username . "',Rule_date = GETDATE(),Run_status = 1, " . 
            "Org_id_3 = NULL,Country_3 = NULL,City_3 = NULL WHERE R_f_a_m_id = " . $regel_id;       
        }
        else {
            $sql_u = "UPDATE Rule_full_address_match SET Find_country = '" . $land_till . "',Country_code = '" . $country_code . "',"
            . $sql_stad_s
           . "Find_str_1 = '" . $str_1_till . "'," 
            . $sql_str_2 . $sql_str_3 . $sql_str_ej_1 . $sql_str_ej_2 
            . "Divide = " . $delas_till . ", 
            Country_1 = '" . $land_1_till . "',City_1 = '" . $stad_1_till . "',Org_id_1 = " . $org_id_1 . ",
            Country_2 = '" . $land_2_till . "',City_2 = '" . $stad_2_till . "',Org_id_2 = " . $org_id_2 . ", 
            Country_3 = '" . $land_3_till . "',City_3 = '" . $stad_3_till . "',Org_id_3 = " . $org_id_3 .                                 
            " ,User_id = '" . $username . "',Rule_date = GETDATE(),Run_status = 1 WHERE R_f_a_m_id = " . $regel_id;        
        }     
        
        $stmt = $dbh->query( $sql_u );

        if ($count = $stmt->rowCount() > 0) {
            echo '<script language="javascript">';
            echo 'alert("Regeln är nu ändrad!")';
            echo '</script>'; 
            $_SESSION['a_regel_f_a_id'] = $regel_id;           
        }
        else {
            echo '<script language="javascript">';
            echo 'alert("Fel vid ändring av regeln!")';
            echo '</script>';            
        }

    }

?>

<h2>ÄNDRA REGEL FULL ADRESS</h2>	
	                                    
		    <form action="aendra_regel_resultat_f_a.php" method="post">
                <a href='aendra_regel_f_a.php'>TILLBAKA</a>&nbsp;&nbsp;
                <a href='regel_full_adress.php'>TILL SÖKNING</a>&nbsp;&nbsp;
                <a href='adressmeny.php'>TILL MENYN</a>
                <br /><br />

                <h3>SÖKFÄLT</h3>    
                
                Land:</br>
                <input type="text" name="Land" id="id_land_s" value="<?php echo $land_till; ?>" disabled />
                <br />
			    Stad:</br> 
				<input type="text" name="Stad" id="id_s_stad" value="<?php echo $stad_till; ?>" disabled />
                <br />
			    Söksträng 1:</br> 
				<input type="text" name="Str_1_ut" value="<?php echo $str_1_till; ?>" disabled />&nbsp;&nbsp; 
                <br />
			    Söksträng 2:</br> 
				<input type="text" name="Str_2_ut" value="<?php echo $str_2_till; ?>" disabled />&nbsp;&nbsp; 
                <br />
			    Söksträng 3:</br> 
				<input type="text" name="Str_3_ut" value="<?php echo $str_3_till; ?>" disabled />&nbsp;&nbsp; 
                <br />
			    Söksträng ej 1:</br> 
				<input type="text" name="Str_ej_1_ut" value="<?php echo $str_ej_1_till; ?>" disabled />&nbsp;&nbsp; 
                <br />
			    Söksträng ej 2:</br> 
				<input type="text" name="Str_ej_2_ut" value="<?php echo $str_ej_2_till; ?>" disabled />&nbsp;&nbsp; 
                <br />
		
				<h3>ÄNDRINGSFÄLT</h3>
			    Delas i:</br>
				<input type="text" name="Delas" id="id_h_delas" size="1" value="<?php echo $delas_till; ?>" disabled /><br /><br />
				
				<b>Organisation 1:</b><br />
				Annat organisationsnamn:<br />
                <input type="text" name="Soek_org_h_1" id="id_soek_org_h_1" size="20" value="<?php echo $org_1_till; ?>" disabled />				
                <br />
			    Annat land:<br />
                <input type="text" name="Soek_land_h_1" id="id_soek_land_h_1" size="20" value="<?php echo $land_1_till; ?>" disabled />				
                <br />				
				Annan stad:<br /> 
				<input type="text" name="Annan_stad_1" id="h_id_stad_1" value="<?php echo $stad_1_till; ?>" disabled /><br />
				<br />
						
				<b>Organisation 2:</b><br />
				Annat organisationsnamn:<br />
                <input type="text" name="Soek_org_h_2" id="id_soek_org_h_2" size="20" value="<?php echo $org_2_till; ?>" disabled />					
                <br />
			    Annat land:<br />
                <input type="text" name="Soek_land_h_2" id="id_soek_land_h_2" value="<?php echo $land_2_till; ?>" disabled />					
				<br />
				Annan stad:<br /> 
				<input type="text" name="Annan_stad_2" id="h_id_stad_2" value="<?php echo $stad_2_till; ?>" disabled /><br />
				<br />
				
				<b>Organisation 3:</b><br />
				Annat organisationsnamn:<br />
                <input type="text" name="Soek_org_h_3" id="id_soek_org_h_3" size="20" value="<?php echo $org_3_till; ?>" disabled />					
                <br />
			    Annat land:<br />
                <input type="text" name="Soek_land_h_3" id="id_soek_land_h_3" size="20" value="<?php echo $land_3_till; ?>" disabled />					
				<br />
				Annan stad:<br /> 
				<input type="text" name="Annan_stad_3" id="h_id_stad_3" value="<?php echo $stad_3_till; ?>" disabled /><br />
				<br />				
				
		    </form>
								
	</body>
</html>