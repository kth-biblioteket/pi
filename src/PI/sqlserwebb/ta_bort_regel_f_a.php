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
    
<script>

    function validateForm() {
        var y = document.forms["taBort"]["Orsak"].value;
        if (y == "") {
            alert("Orsak måste anges!");
            return false;
        }
    }
        
</script>     
	
</head>

<body>

<?php include('include_head_new.html'); ?>

<?php
    
    $regel_id = $_GET["Regel_id"];

    if (intval($regel_id) > 0) {

        $_SESSION['regel_id'] = $regel_id;

        $username = $_SESSION['anv'];
        $password = $_SESSION['ord'];
        $hostname = $_SESSION['hnamn'];
        $dbname = $_SESSION['dbnamn'];

        $dbh = new PDO("sqlsrv:Server=$hostname;Database=$dbname",$username,$password);

        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Visa regeln att ta bort

    	$sql = "SELECT r.R_f_a_m_id,r.Find_country,r.Country_code,r.Find_city,r.Find_str_1,r.Find_str_2,r.Find_str_3,r.Find_str_not_1,r.Find_str_not_2,r.Divide,
        r.Country_1,r.City_1,o1.Name_en + ' [' + o1.Country_name + ']' AS Orgname_1,
        r.Country_2,r.City_2,o2.Name_en + ' [' + o2.Country_name + ']' AS Orgname_2,
        Country_3,City_3,o3.Name_en + ' [' + o3.Country_name + ']' AS Orgname_3,
        r.Org_id_1,r.Org_id_2,r.Org_id_3,r.User_id,r.Rule_date 
        FROM rule_full_address_match r  
        JOIN unified_org_names o1 
        ON r.Org_id_1 = o1.Unified_org_id  
        LEFT JOIN unified_org_names o2 
        ON r.Org_id_2 = o2.Unified_org_id 
        LEFT JOIN unified_org_names o3 
        ON r.Org_id_3 = o3.Unified_org_id 
        WHERE R_f_a_m_id = " . $regel_id;

        $stmt = $dbh->query( $sql );

    	foreach ($stmt as $row) {
            $land_s = $row['Find_country'];
            $org_str_1 = $row['Find_str_1'];
            $org_str_2 = $row['Find_str_2'];
            $org_str_3 = $row['Find_str_3'];
            $org_str_n_1 = $row['Find_str_not_1'];
            $org_str_n_2 = $row['Find_str_not_2']; 
            $land_h_1 = $row['Country_1'];
            $land_h_2 = $row['Country_2'];
            $land_h_3 = $row['Country_3'];
            $stad_s = $row['Find_city'];
            $stad_h_1 = $row['City_1'];
            $stad_h_2 = $row['City_2'];
            $stad_h_3 = $row['City_3'];
            $org_h_1 = $row['Orgname_1'];
            $org_h_2 = $row['Orgname_2'];
            $org_h_3 = $row['Orgname_3'];
            $delas = $row['Divide'];  
            $org_id_1 = $row['Org_id_1'];
            $org_id_2 = $row['Org_id_2'];
            $org_id_3 = $row['Org_id_3'];
            $land_kod = $row['Country_code'];
            $r_f_a_m_id = $row['R_f_a_m_id']; 
            $user_id = $row['User_id'];
            $rule_date = $row['Rule_date'];                    
    	}

        $_SESSION['land_s'] = $land_s;
        $_SESSION['land_1'] = $land_h_1;
        $_SESSION['land_2'] = $land_h_2;
        $_SESSION['land_3'] = $land_h_3;
        $_SESSION['stad_s'] = $stad_s;
        $_SESSION['stad_1'] = $stad_h_1;
        $_SESSION['stad_2'] = $stad_h_2;
        $_SESSION['stad_3'] = $stad_h_3;
        $_SESSION['org_str_1'] = $org_str_1;
        $_SESSION['org_str_2'] = $org_str_2;
        $_SESSION['org_str_3'] = $org_str_3;
        $_SESSION['org_str_n_1'] = $org_str_n_1;
        $_SESSION['org_str_n_2'] = $org_str_n_2;
        $_SESSION['delas'] = $delas;
        $_SESSION['org_id_1'] = $org_id_1;
        $_SESSION['org_id_2'] = $org_id_2;
        $_SESSION['org_id_3'] = $org_id_3;
        $_SESSION['land_kod'] = $land_kod;
        $_SESSION['r_f_a_m_id'] = $r_f_a_m_id;
        $_SESSION['user_id'] = $user_id;
        $_SESSION['rule_date'] = $rule_date;

    }

?>

<h2>TA BORT REGEL FULL ADRESS</h2>	
	                                    
		    <form action="ta_bort_regel_resultat_f_a.php" method="post">

                <input type="submit" name="radera" value="Radera regel"/>&nbsp;&nbsp;
                <a href='regel_full_adress.php'>TILL SÖKNING</a>&nbsp;&nbsp;
                <a href='adressmeny.php'>TILL MENYN</a>
                <br /><br />

                ORSAK:</br> 
				<input type="text" name="orsak" maxlength="100">&nbsp;&nbsp; 
                <br /><br />

                <h3>SÖKFÄLT</h3>    
                
                Land:</br>
                <input type="text" name="Land" id="id_land_s" value="<?php echo $land_s; ?>" disabled/>
                <br />
			    Stad:</br> 
				<input type="text" name="Stad" id="id_s_stad" value="<?php echo $stad_s; ?>" disabled />
                <br />
			    Söksträng 1:</br> 
				<input type="text" name="Str_1" id="id_str_1" value="<?php echo $org_str_1; ?>" disabled /><br />
			    Söksträng 2:</br> 
				<input type="text" name="Str_1" id="id_str_2" value="<?php echo $org_str_2; ?>" disabled /><br />
			    Söksträng 3:</br> 
				<input type="text" name="Str_1" id="id_str_3" value="<?php echo $org_str_3; ?>" disabled /><br />
			    Söksträng ej 1:</br> 
				<input type="text" name="Str_1" id="id_str_n_1" value="<?php echo $org_str_n_1; ?>" disabled /><br />
			    Söksträng ej 2:</br> 
				<input type="text" name="Str_1" id="id_str_n_2" value="<?php echo $org_str_n_2; ?>" disabled /><br />
				
				<h3>ÄNDRINGSFÄLT</h3>
			    Delas i:</br>
				<input type="text" name="Delas" id="id_h_delas" size="1" value="<?php echo $delas; ?>" disabled /><br /><br />
				
				<b>Organisation 1:</b><br />
				Annat organisationsnamn:<br />
                <input type="text" name="Soek_org_h_1" id="id_soek_org_h_1" size="20" value="<?php echo $org_h_1; ?>" disabled />				
                <br />
			    Annat land:<br />
                <input type="text" name="Soek_land_h_1" id="id_soek_land_h_1" size="20" value="<?php echo $land_h_1; ?>" disabled/>				
                <br />				
				Annan stad:<br /> 
				<input type="text" name="Annan_stad_1" id="h_id_stad_1" value="<?php echo $stad_h_1; ?>" disabled /><br />
				<br />
						
				<b>Organisation 2:</b><br />
				Annat organisationsnamn:<br />
                <input type="text" name="Soek_org_h_2" id="id_soek_org_h_2" size="20" value="<?php echo $org_h_2; ?>" disabled />					
                <br />
			    Annat land:<br />
                <input type="text" name="Soek_land_h_2" id="id_soek_land_h_2" size="20" value="<?php echo $land_h_2; ?>" disabled />					
				<br />
				Annan stad:<br /> 
				<input type="text" name="Annan_stad_2" id="h_id_stad_2" value="<?php echo $stad_h_2; ?>" disabled /><br />
				<br />
				
				<b>Organisation 3:</b><br />
				Annat organisationsnamn:<br />
                <input type="text" name="Soek_org_h_3" id="id_soek_org_h_3" size="20" value="<?php echo $org_h_3; ?>" disabled />					
                <br />
			    Annat land:<br />
                <input type="text" name="Soek_land_h_3" id="id_soek_land_h_3" size="20" value="<?php echo $land_h_3; ?>" disabled />					
				<br />
				Annan stad:<br /> 
				<input type="text" name="Annan_stad_3" id="h_id_stad_3" value="<?php echo $stad_h_3; ?>" disabled /><br />
				<br />				
				
		    </form>
								
	</body>
</html>