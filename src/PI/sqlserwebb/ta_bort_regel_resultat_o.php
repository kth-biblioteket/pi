<?php session_start(); ?>

<!DOCTYPE html PUBLIC "-//w3c//DTD XHTMLm 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<! Författare: Cecilia Wiklander>
<! Syfte: Adressrättnings-hantering>
<! Ändringar: >

<head>

    <meta charset="utf-8">

    <title>TA BORT REGEL ORGANISATION</title>
	
    <link href="Site_utan_storlek.css" rel="stylesheet"> 
	
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

    $b_regel_o_id = $_SESSION['b_regel_o_id'];

    if ($b_regel_o_id <> $regel_id) {

        // Spara undan regeln

        $land_s = $_SESSION['land_s'];
        $land_1 = $_SESSION['land_1'];
        $land_2 = $_SESSION['land_2'];
        $land_3 = $_SESSION['land_3'];
        $stad_s = $_SESSION['stad_s'];
        $stad_1 = $_SESSION['stad_1'];
        $stad_2 = $_SESSION['stad_2'];
        $stad_3 = $_SESSION['stad_3'];
        $org_s = $_SESSION['org_s'];
        $delas = $_SESSION['delas'];
        $org_id_1 = $_SESSION['org_id_1'];
        $org_id_2 = $_SESSION['org_id_2'];
        $org_id_3 = $_SESSION['org_id_3'];
        $land_kod = $_SESSION['land_kod'];
        $r_o_m_id = $_SESSION['r_o_m_id'];
        $user_id = $_SESSION['user_id'];
        $rule_date = $_SESSION['rule_date'];
        $orsak = $_POST['orsak']; 
        $fr = $_SESSION['fr']; 
        $ti = $_SESSION['ti']; 

	$Sk = "'";
	$Ers = "''";

	$stad_s = str_replace($Sk, $Ers, $stad_s);
	$org_s = str_replace($Sk, $Ers, $org_s);
	$stad_1 = str_replace($Sk, $Ers, $stad_1);
	$stad_2 = str_replace($Sk, $Ers, $stad_2);
	$stad_3 = str_replace($Sk, $Ers, $stad_3);
	$land_s = str_replace($Sk, $Ers, $land_s);
	$land_1 = str_replace($Sk, $Ers, $land_1);
	$land_2 = str_replace($Sk, $Ers, $land_2);
	$land_3 = str_replace($Sk, $Ers, $land_3);

        // NYTT
        if (strlen($fr) == 0) {
               $sql_tid = "";
               $sql_v_tid = "";
        }
        else {
               $sql_tid = ",Valid_from";
               $sql_v_tid = "," . $fr;
        }
        if (strlen($ti) > 0) {
               $sql_tid = $sql_tid . ",Valid_to";
               $sql_v_tid = $sql_v_tid . "," . $ti;
        }
        // SLUTNYTT

        if ($delas = 1) {
            $sql_i = "INSERT INTO Removed_rules (R_o_m_id,
            Find_country,Country_code,Find_city,Find_org,Divide,
            Country_1,City_1,Org_id_1,
            User_id,Rule_date,Remove_user_id,Remove_date,Reason" . $sql_tid . ") VALUES
            (" . $r_o_m_id . ",'" . $land_s . "','" . $land_kod . "','" . $stad_s . "','" . $org_s . "'," 
            . $delas . ",'" . $land_1 . "','" . $stad_1 . "'," . $org_id_1 . ",'" . $user_id . "','" 
            . $rule_date . "','" . $username . "',GETDATE(),'" . $orsak . "'" . $sql_v_tid . ")";
        }
        elseif ($delas = 2) {
            $sql_i = "INSERT INTO Removed_rules (R_o_m_id,
            Find_country,Country_code,Find_city,Find_org,Divide,
            Country_1,City_1,Org_id_1,Country_2,City_2,Org_id_2,
            User_id,Rule_date,Remove_user_id,Remove_date,Reason" . $sql_tid . ") VALUES
            (" . $r_o_m_id . ",'" . $land_s . "','" . $land_kod . "','" . $stad_s . "','" . $org_s . "'," 
            . $delas . ",'" . $land_1 . "','" . $stad_1 . "'," . $org_id_1 . ",'" . $land_2 . "','" . $stad_2 . 
            "'," . $org_id_2 . ",'" . $user_id . "','" 
            . $rule_date . "','" . $username . "',GETDATE(),'" . $orsak . "'" . $sql_v_tid . ")";
        }
        else {
            $sql_i = "INSERT INTO Removed_rules (R_o_m_id,
            Find_country,Country_code,Find_city,Find_org,Divide,
            Country_1,City_1,Org_id_1,Country_2,City_2,Org_id_2,Country_3,City_3,Org_id_3,
            User_id,Rule_date,Remove_user_id,Remove_date,Reason" . $sql_tid . ") VALUES
            (" . $r_o_m_id . ",'" . $land_s . "','" . $land_kod . "','" . $stad_s . "','" . $org_s . "'," 
            . $delas . ",'" . $land_1 . "','" . $stad_1 . "'," . $org_id_1 . ",'" . $land_2 . "','" . $stad_2 . 
            "'," . $org_id_2 . ",'" . $land_3 . "','" . $stad_3 . "'," . $org_id_3 . ",'" . $user_id . "','" 
            . $rule_date . "','" . $username . "',GETDATE(),'" . $orsak . "'" . $sql_v_tid . ")";
        }

        $stmt = $dbh->query( $sql_i );

        // Ta bort regeln

	    $sql_d = "DELETE FROM rule_org_match WHERE R_o_m_id = " . $regel_id;

        $stmt = $dbh->query( $sql_d );

        if ($count = $stmt->rowCount() > 0) {
            echo '<script language="javascript">';
            echo 'alert("Regeln är borttagen!")';
            echo '</script>'; 
            $_SESSION['b_regel_o_id'] = $regel_id;           
        }
        else {
            echo '<script language="javascript">';
            echo 'alert("Fel vid borttagande av regeln!")';
            echo '</script>';            
        }
    }

?>

<h2>TA BORT REGEL ORGANISATION</h2>	
	                                    
		    <form action="ta_bort_regel_resultat_o.php" method="post">
                <a href='regel_organisation.php'>TILL SÖKNING</a>&nbsp;&nbsp;
                <a href='adressmeny.php'>TILL MENYN</a>
                <br /><br />

                <h3>SÖKFÄLT</h3>    
                
                Land:</br>
                <input type="text" name="Land" id="id_land_s" disabled size="40" />
                <br />
			    Stad:</br> 
				<input type="text" name="Stad" id="id_s_stad" disabled size="40" />
                <br />
			    Organisationsnamn:</br> 
				<input type="text" name="Organisation" id="id_s_org" disabled size="40" /><br />
				
				<h3>ÄNDRINGSFÄLT</h3>
			    Delas i:</br>
				<input type="text" name="Delas" id="id_h_delas" size="1" disabled />
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                Gäller från: 
                <input type="text" name="Fr" id="id_fr" size="4" disabled size="4" />
                &nbsp;&nbsp;
                till:  
                <input type="text" name="Ti" id="id_ti" size="4" disabled size="4" /><br />
                
                <br /><br />
				
				<b>Organisation 1:</b><br />
				Annat organisationsnamn:<br />
                <input type="text" name="Soek_org_h_1" id="id_soek_org_h_1" size="40" disabled />				
                <br />
			    Annat land:<br />
                <input type="text" name="Soek_land_h_1" id="id_soek_land_h_1" size="40" disabled />				
                <br />				
				Annan stad:<br /> 
				<input type="text" name="Annan_stad_1" id="h_id_stad_1" disabled size="40" /><br />
				<br />
						
				<b>Organisation 2:</b><br />
				Annat organisationsnamn:<br />
                <input type="text" name="Soek_org_h_2" id="id_soek_org_h_2" size="40" disabled />					
                <br />
			    Annat land:<br />
                <input type="text" name="Soek_land_h_2" id="id_soek_land_h_2" size="40" disabled />					
				<br />
				Annan stad:<br /> 
				<input type="text" name="Annan_stad_2" id="h_id_stad_2" size="40" disabled /><br />
				<br />
				
				<b>Organisation 3:</b><br />
				Annat organisationsnamn:<br />
                <input type="text" name="Soek_org_h_3" id="id_soek_org_h_3" size="40" disabled />					
                <br />
			    Annat land:<br />
                <input type="text" name="Soek_land_h_3" id="id_soek_land_h_3" size="40" disabled />					
				<br />
				Annan stad:<br /> 
				<input type="text" name="Annan_stad_3" id="h_id_stad_3" disabled size="40" /><br />
				<br />				
				
		    </form>
								
	</body>
</html>