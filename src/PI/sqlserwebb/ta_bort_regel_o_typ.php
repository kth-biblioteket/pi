<?php session_start(); ?>

<!DOCTYPE html PUBLIC "-//w3c//DTD XHTMLm 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<! Författare: Cecilia Wiklander>
<! Syfte: Adressrättnings-hantering>
<! Ändringar: >

<head>

    <meta charset="utf-8">

    <title>TA BORT REGEL ORGANISATIONSTYP</title>
	
    <link href="Site.css" rel="stylesheet"> 
        
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

    	$sql = "SELECT R_o_t_m_id,Find_country,Country_code,Find_city,Find_org_1,Find_org_2,Find_org_not,Country,City,Org_type_code,User_id,Rule_date 
        FROM rule_org_type_match    
        WHERE R_o_t_m_id = " . $regel_id;

        $stmt = $dbh->query( $sql );

    	foreach ($stmt as $row) {
            $land_s = $row['Find_country'];
            $land_h_1 = $row['Country'];
            $stad_s = $row['Find_city'];
            $stad_h_1 = $row['City'];
            $org_s_1 = $row['Find_org_1'];
            $org_s_2 = $row['Find_org_2'];
            $org_s_ej = $row['Find_org_ej'];
            $land_kod = $row['Country_code'];
            $r_o_t_m_id = $row['R_o_t_m_id'];
            $org_type_code = $row['Org_type_code'];             
            $user_id = $row['User_id'];
            $rule_date = $row['Rule_date'];                                                                                                       
    	}

        $_SESSION['land_s'] = $land_s;
        $_SESSION['land_1'] = $land_h_1;
        $_SESSION['stad_s'] = $stad_s;
        $_SESSION['stad_1'] = $stad_h_1;
        $_SESSION['org_s_1'] = $org_s_1;
        $_SESSION['org_s_2'] = $org_s_2;
        $_SESSION['org_s_ej'] = $org_s_ej;
        $_SESSION['land_kod'] = $land_kod;
        $_SESSION['org_type_code'] = $org_type_code;
        $_SESSION['r_o_t_m_id'] = $r_o_t_m_id;
        $_SESSION['user_id'] = $user_id;
        $_SESSION['rule_date'] = $rule_date;

    }

?>

<h2>TA BORT REGEL ORGANISATIONSTYP</h2>	
	                                    
		    <form action="ta_bort_regel_resultat_o_typ.php" method="post">

                <input type="submit" name="radera" value="Radera regel"/>&nbsp;&nbsp;
                <a href='regel_organisation_typ.php'>TILL SÖKNING</a>&nbsp;&nbsp;
                <a href='adressmeny.php'>TILL MENYN</a>
                <br /><br />

                <h3>SÖKFÄLT</h3>    
                
                Land:</br>
                <input type="text" name="Land" id="id_land_s" value="<?php echo $land_s; ?>" disabled/>
                <br />
			    Stad:</br> 
				<input type="text" name="Stad" id="id_s_stad" value="<?php echo $stad_s; ?>" disabled />
                <br />
			    Organisation, sträng 1:</br> 
				<input type="text" name="Organisation1" id="id_s_org_1" value="<?php echo $org_s_1; ?>" disabled /><br />
			    Organisation, sträng 2:</br> 
				<input type="text" name="Organisation2" id="id_s_org_2" value="<?php echo $org_s_2; ?>" disabled /><br />
			    Organisation, sträng ej:</br> 
				<input type="text" name="Organisationej" id="id_s_org_ej" value="<?php echo $org_s_ej; ?>" disabled /><br />
				
				<h3>ÄNDRINGSFÄLT</h3>
				               				
                Organisationstyp:<br />
                <input type="text" name="Orgtypkod" id="id_h_org_kod" value="<?php echo $org_type_code; ?>" disabled />				
                <br />
			    Annat land:<br />
                <input type="text" name="Soek_land_h_1" id="id_soek_land_h_1" size="20" value="<?php echo $land_h_1; ?>" disabled/>				
                <br />				
				Annan stad:<br /> 
				<input type="text" name="Annan_stad_1" id="h_id_stad_1" value="<?php echo $stad_h_1; ?>" disabled /><br />
				<br />			
				
		    </form>
								
	</body>
</html>