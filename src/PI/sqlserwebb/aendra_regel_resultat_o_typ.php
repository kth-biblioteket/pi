<?php session_start(); ?>

<!DOCTYPE html PUBLIC "-//w3c//DTD XHTMLm 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<! Författare: Cecilia Wiklander>
<! Syfte: Adressrättnings-hantering>
<! Ändringar: >

<head>

    <meta charset="utf-8">

    <title>ÄNDRA REGEL ORGANISATIONSTYP</title>
	
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

    $a_regel_o_typ_id = $_SESSION['a_regel_o_typ_id'];

    $land_till = $_POST['Land_ut_2'];

    $stad_till = $_POST['Stad_ut_2'];

    $org_till_1 = $_POST['Org_till_1'];

    $org_till_2 = $_POST['Org_till_2'];

    $org_till_ej = $_POST['Org_till_ej'];

    $land_1_till = $_POST['Land_1_ut_2'];

    $stad_1_till = $_POST['Stad_1_till'];

    $orgtyp_till = $_POST['Orgtyp_ut_2'];

    $Sk = "'";
    $Ers = "''";

    $stad_till = str_replace($Sk, $Ers, $stad_till);
    $org_till_1 = str_replace($Sk, $Ers, $org_till_1);
    $org_till_2 = str_replace($Sk, $Ers, $org_till_2);
    $org_till_ej = str_replace($Sk, $Ers, $org_till_ej);
    $stad_1_till = str_replace($Sk, $Ers, $stad_1_till);
    $land_till = str_replace($Sk, $Ers, $land_till);
    $land_1_till = str_replace($Sk, $Ers, $land_1_till);
 
    // Ändra regeln

    $koll_svar = false;

    if ($orgtyp_till == 'Ange organisationstyp' || strlen($orgtyp_till) == 0){
       echo "<script>alert('Organisationstyp måste anges som sökfält!');</script>";
    }
    else {

         if (strlen($org_till_1) == 0) {
            echo "<script>alert('Organisation sträng 1 måste anges som sökfält!');</script>";                  
         }
         else {
            $koll_svar = true;                 
         }        
    }

    if ($koll_svar) {

        if ($koll_svar && $a_regel_o_typ_id <> $regel_id) {
            if (strlen($land_till) > 0) {
                $sql_country = "SELECT Country_code FROM Country WHERE Display_name = '" . $land_till . "'";                
                $stmt = $dbh->query( $sql_country );
                foreach ($stmt as $row) {
                    $country_code = $row['Country_code'];      
                } 
            }
            else {
                $country_code = NULL;
            }
    
            $org_typ_code = NULL;
            $sql_orgtyp = "SELECT Org_type_code FROM Organization_type WHERE Org_type_eng = '" . $orgtyp_till . "'";
            $stmt = $dbh->query( $sql_orgtyp );
            foreach ($stmt as $row) {
                $org_typ_code = $row['Org_type_code'];      
            }
          
       		$foere = 0;
       // Land_s
       // Country_code
       if ($land_till == 'Ange land') {
          	$sql_stad_till = "Find_country = null,";
	  	$sql_country_code = "Country_code = null,";
       }
       else {
          	$sql_land_till = "Find_country = '" . $land_till . "',";
          	$sql_country_code = "Country_code = '" . $country_code . "',";
          	$foere = 1;
       }
       // Stad_s
       if (strlen($stad_till) == 0) {
                $sql_stad_s = "Find_city = null,";
       }
       	else {
                $sql_stad_s = "Find_city = '" . $stad_till . "',";
       }
       // Org_s_2
       if (strlen($org_till_2) == 0) {
           	$sql_org_till_2 = "Find_org_2 = null,";
       }
       else {
                $sql_org_till_2 = "Find_org_2 = '" . $org_till_2 . "',";
       }
       // Org_s_ej
       if (strlen($org_till_ej) == 0) {
           	$sql_org_till_ej = "Find_org_not = null,";
       }
       else {
                $sql_org_till_ej = "Find_org_not = '" . $org_till_ej . "',";
       }
       // Land_1
       if ($land_1_till == 'Ange land') {
           	$sql_land_1_till = "Country = null,";
       }
       else {
                $sql_land_1_till = "Country = '" . $land_1_till . "',";
       }
       // Stad_1
       if (strlen($stad_1_till) == 0) {
           	$sql_stad_1_till = "City = null,";
       }
       else {
                $sql_stad_1_till = "City = '" . $stad_1_till . "',";
       }

            $sql_u = "UPDATE Rule_org_type_match SET " . $sql_land_till . $sql_country_code 
            . $sql_stad_s . $sql_land_1_till . $sql_stad_1_till . "Org_type_code = '" . $org_typ_code   
            . "',Find_org_1 = '" . $org_till_1 . "', " . $sql_org_till_2 . $sql_org_till_ej  
            . "User_id = '" . $username . "',Rule_date = GETDATE(),Run_status = 1 WHERE R_o_t_m_id = " . $regel_id;  
 
            $stmt = $dbh->query( $sql_u );
            
            if ($count = $stmt->rowCount() > 0) {                
                echo '<script language="javascript">';
                echo 'alert("Regeln är nu ändrad!")';
                echo '</script>'; 
                $_SESSION['a_regel_o_typ_id'] = $regel_id;           
            }
            else {        
                echo '<script language="javascript">';
                echo 'alert("Fel vid ändring av regeln!")';
                echo '</script>';            
            }
           
        }
        
    }

?>

<h2>ÄNDRA REGEL ORGANISATIONSTYP</h2>	
	                                    
		<form action="aendra_regel_resultat_o_typ.php" method="post">
                <a href='aendra_regel_o_typ.php'>TILLBAKA</a>&nbsp;&nbsp;
                <a href='regel_organisation_typ.php'>TILL SÖKNING</a>&nbsp;&nbsp;
                <a href='adressmeny.php'>TILL MENYN</a>
                <br /><br />

                <h3>SÖKFÄLT</h3>    
                
                Land:</br>
                <input type="text" name="Land" id="id_land_s" value="<?php echo $land_till; ?>" disabled />
                <br />
			    Stad:</br> 
				<input type="text" name="Stad" id="id_s_stad" value="<?php echo $stad_till; ?>" disabled />
                <br />
			    Organisation, sträng 1:</br> 
				<input type="text" name="Organisation1" id="id_s_org_1" value="<?php echo $org_till_1; ?>" disabled /><br />
			    Organisation, sträng 2:</br> 
				<input type="text" name="Organisation2" id="id_s_org_2" value="<?php echo $org_till_2; ?>" disabled /><br />
			    Organisation, sträng ej:</br> 
				<input type="text" name="Organisationej" id="id_s_org_ej" value="<?php echo $org_till_ej; ?>" disabled /><br />
				
				<h3>ÄNDRINGSFÄLT</h3>
			    
                Organisationstyp:</br>
                <input type="text" name="Orgtyp_ut" value="<?php echo $orgtyp_till; ?>" disabled />&nbsp;&nbsp;
                </br>
			    Annat land:<br />
                <input type="text" name="Soek_land_h_1" id="id_soek_land_h_1" size="20" value="<?php echo $land_1_till; ?>" disabled />				
                <br />				
				Annan stad:<br /> 
				<input type="text" name="Annan_stad_1" id="h_id_stad_1" value="<?php echo $stad_1_till; ?>" disabled /><br />
				<br />		
				
		    </form>
								
	</body>
</html>