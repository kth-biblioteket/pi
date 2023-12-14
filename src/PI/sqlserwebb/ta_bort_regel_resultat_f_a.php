<?php session_start(); ?>

<!DOCTYPE html PUBLIC "-//w3c//DTD XHTMLm 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<! Författare: Cecilia Wiklander>
<! Syfte: Adressrättnings-hantering>
<! Ändringar: >

<head>

    <meta charset="utf-8">

    <title>TA BORT REGEL FULL ADRESS</title>
	
    <link href="Site.css" rel="stylesheet"> 
	
</head>

<body>

<?php include('include_head_new.html'); ?>

<?php
    
    $regel_id = $_SESSION['regel_id'];

    $username = $_SESSION['anv'];
    $password = $_SESSION['ord'];
    $hostname = $_SESSION['hnamn'];
    $dbname = $_SESSION['dbnamn'];

    $dbh = new PDO("sqlsrv:Server=$hostname;Database=$dbname",$username,$password);

    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $b_regel_f_a_id = $_SESSION['b_regel_f_a_id'];

    if ($b_regel_f_a_id <> $regel_id) {

        // Spara undan regeln

        $land_s = $_SESSION['land_s'];
        $land_1 = $_SESSION['land_1'];
        $land_2 = $_SESSION['land_2'];
        $land_3 = $_SESSION['land_3'];
        $stad_s = $_SESSION['stad_s'];
        $stad_1 = $_SESSION['stad_1'];
        $stad_2 = $_SESSION['stad_2'];
        $stad_3 = $_SESSION['stad_3'];
        $org_str_1 = $_SESSION['org_str_1'];
        $org_str_2 = $_SESSION['org_str_2'];
        $org_str_3 = $_SESSION['org_str_3'];
        $org_str_n_1 = $_SESSION['org_str_n_1'];
        $org_str_n_2 = $_SESSION['org_str_n_2'];
        $delas = $_SESSION['delas'];
        $org_id_1 = $_SESSION['org_id_1'];
        $org_id_2 = $_SESSION['org_id_2'];
        $org_id_3 = $_SESSION['org_id_3'];
        $land_kod = $_SESSION['land_kod'];
        $r_f_a_m_id = $_SESSION['r_f_a_m_id'];
        $user_id = $_SESSION['user_id'];
        $rule_date = $_SESSION['rule_date']; 
        $orsak = $_POST["orsak"];

	$Sk = "'";
	$Ers = "''";

	$stad_s = str_replace($Sk, $Ers, $stad_s);
	$org_str_1 = str_replace($Sk, $Ers, $org_str_1);
	$org_str_2 = str_replace($Sk, $Ers, $org_str_2);
	$org_str_3 = str_replace($Sk, $Ers, $org_str_3);
	$org_str_n_1 = str_replace($Sk, $Ers, $org_str_n_1);
	$org_str_n_2 = str_replace($Sk, $Ers, $org_str_n_2);
	$stad_1 = str_replace($Sk, $Ers, $stad_1);
	$stad_2 = str_replace($Sk, $Ers, $stad_2);
	$stad_3 = str_replace($Sk, $Ers, $stad_3);
	$land_s = str_replace($Sk, $Ers, $land_s);
	$land_1 = str_replace($Sk, $Ers, $land_1);
	$land_2 = str_replace($Sk, $Ers, $land_2);
	$land_3 = str_replace($Sk, $Ers, $land_3);

        if ($delas = 1) {
            $sql_i = "INSERT INTO Removed_rules (R_f_a_m_id,
            Find_country,Country_code,Find_city,Find_str_1,Find_str_2,Find_str_3,Find_str_not_1,Find_str_not_2,Divide,
            Country_1,City_1,Org_id_1,
            User_id,Rule_date,Remove_user_id,Remove_date,Reason) VALUES
            (" . $r_f_a_m_id . ",'" . $land_s . "','" . $land_kod . "','" . $stad_s . "','" . $org_str_1 . "','" 
            . $org_str_2 . "','" . $org_str_3 . "','" . $org_str_n_1 . "','" . $org_str_n_2 . "'," 
            . $delas . ",'" . $land_1 . "','" . $stad_1 . "'," . $org_id_1 . ",'" . $user_id . "','" 
            . $rule_date . "','" . $username . "',GETDATE(),'" . $orsak . "')";
        }
        elseif ($delas = 2) {
            $sql_i = "INSERT INTO Removed_rules (R_f_a_m_id,
            Find_country,Country_code,Find_city,Find_str_1,Find_str_2,Find_str_3,Find_str_not_1,Find_str_not_2,Divide,
            Country_1,City_1,Org_id_1,Country_2,City_2,Org_id_2,
            User_id,Rule_date,Remove_user_id,Remove_date,Reason) VALUES
            (" . $r_f_a_m_id . ",'" . $land_s . "','" . $land_kod . "','" . $stad_s . "','" . $org_str_1 . "','" 
            . $org_str_2 . "','" . $org_str_3 . "','" . $org_str_n_1 . "','" . $org_str_n_2 . "'," 
            . $delas . ",'" . $land_1 . "','" . $stad_1 . "'," . $org_id_1 . ",'" . $land_2 . "','" . $stad_2 . 
            "'," . $org_id_2 . ",'" . $user_id . "','" 
            . $rule_date . "','" . $username . "',GETDATE(),'" . $orsak . "')";
        }
        else {
            $sql_i = "INSERT INTO Removed_rules (R_f_a_m_id,
            Find_country,Country_code,Find_city,Find_str_1,Find_str_2,Find_str_3,Find_str_not_1,Find_str_not_2,Divide,
            Country_1,City_1,Org_id_1,Country_2,City_2,Org_id_2,Country_3,City_3,Org_id_3,
            User_id,Rule_date,Remove_user_id,Remove_date,Reason) VALUES
            (" . $r_f_a_m_id . ",'" . $land_s . "','" . $land_kod . "','" . $stad_s . "','" . $org_str_1 . "','" 
            . $org_str_2 . "','" . $org_str_3 . "','" . $org_str_n_1 . "','" . $org_str_n_2 . "'," 
            . $delas . ",'" . $land_1 . "','" . $stad_1 . "'," . $org_id_1 . ",'" . $land_2 . "','" . $stad_2 . 
            "'," . $org_id_2 . ",'" . $land_3 . "','" . $stad_3 . "'," . $org_id_3 . ",'" . $user_id . "','" 
            . $rule_date . "','" . $username . "',GETDATE(),'" . $orsak . "')";
        }

        $stmt = $dbh->query( $sql_i );

        // Ta bort regeln

	    $sql_d = "DELETE FROM rule_full_address_match WHERE R_f_a_m_id = " . $regel_id;

        $stmt = $dbh->query( $sql_d );
        
        if ($count = $stmt->rowCount() > 0) {
            echo '<script language="javascript">';
            echo 'alert("Regeln är borttagen!")';
            echo '</script>'; 
            $_SESSION['b_regel_f_a_id'] = $regel_id;           
        }
        else {
            echo '<script language="javascript">';
            echo 'alert("Fel vid borttagande av regeln!")';
            echo '</script>';            
        }
    }

?>

<h2>TA BORT REGEL FULL ADRESS</h2>	
	                                    
		    <form action="ta_bort_regel_resultat_f_a.php" method="post">

                <a href='regel_full_adress.php'>TILL SÖKNING</a>&nbsp;&nbsp;
                <a href='adressmeny.php'>TILL MENYN</a>
                <br /><br />

                <h3>SÖKFÄLT</h3>    
                
                Land:</br>
                <input type="text" name="Land" id="id_land_s" disabled />
                <br />
			    Stad:</br> 
				<input type="text" name="Stad" id="id_s_stad" disabled />
                <br />
			    Organisationsnamn:</br> 
				<input type="text" name="Organisation" id="id_s_org" disabled /><br />
				
				<h3>ÄNDRINGSFÄLT</h3>
			    Delas i:</br>
				<input type="text" name="Delas" id="id_h_delas" size="1" disabled /><br /><br />
				
				<b>Organisation 1:</b><br />
				Annat organisationsnamn:<br />
                <input type="text" name="Soek_org_h_1" id="id_soek_org_h_1" size="20" disabled />				
                <br />
			    Annat land:<br />
                <input type="text" name="Soek_land_h_1" id="id_soek_land_h_1" size="20" disabled />				
                <br />				
				Annan stad:<br /> 
				<input type="text" name="Annan_stad_1" id="h_id_stad_1" disabled /><br />
				<br />
						
				<b>Organisation 2:</b><br />
				Annat organisationsnamn:<br />
                <input type="text" name="Soek_org_h_2" id="id_soek_org_h_2" size="20" disabled />					
                <br />
			    Annat land:<br />
                <input type="text" name="Soek_land_h_2" id="id_soek_land_h_2" disabled />					
				<br />
				Annan stad:<br /> 
				<input type="text" name="Annan_stad_2" id="h_id_stad_2" disabled /><br />
				<br />
				
				<b>Organisation 3:</b><br />
				Annat organisationsnamn:<br />
                <input type="text" name="Soek_org_h_3" id="id_soek_org_h_3" size="20" disabled />					
                <br />
			    Annat land:<br />
                <input type="text" name="Soek_land_h_3" id="id_soek_land_h_3" size="20" disabled />					
				<br />
				Annan stad:<br /> 
				<input type="text" name="Annan_stad_3" id="h_id_stad_3" disabled /><br />
				<br />				
				
		    </form>
								
	</body>
</html>