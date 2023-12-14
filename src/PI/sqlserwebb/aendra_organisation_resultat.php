<?php session_start(); ?>

<!DOCTYPE html PUBLIC "-//w3c//DTD XHTMLm 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<! Författare: Cecilia Wiklander>
<! Syfte: Adressrättnings-hantering>
<! Ändringar: >

<head>

    <meta charset="utf-8">

    <title>ÄNDRA ORGANISATIONSNAMN</title>
	
    <link href="Site.css" rel="stylesheet"> 
	
</head>

<body>

<?php include('include_head_new.html'); ?>

<?php
    
    $u_org_id = $_SESSION['u_org_id'];

    $_SESSION['u_org_id_ut'] = $u_org_id;

    $username = $_SESSION['anv'];
    $password = $_SESSION['ord'];
    $hostname = $_SESSION['hnamn'];
    $dbname = $_SESSION['dbnamn'];

    $dbh = new PDO("sqlsrv:Server=$hostname;Database=$dbname",$username,$password);

    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $a_org_id = $_SESSION['a_org_id'];

    $namn_l_till = $_POST['Namn_lok_till'];

    $namn_e_till = $_POST['Namn_eng_till'];

    $land_till = $_POST['Land_till'];

    $orgtyp_till = $_POST['Orgtyp_till'];

    $komm_till = $_POST['Komm_till'];

    $rorid_till = $_POST['RORid_till'];

    $Sk = "'";
    $Ers = "''";

    $namn_l_till = str_replace($Sk, $Ers, $namn_l_till);
    $namn_e_till = str_replace($Sk, $Ers, $namn_e_till);
    $komm_till = str_replace($Sk, $Ers, $komm_till);

    // Ändra organisationen

    $koll_svar = false;

    if (strlen($namn_l_till) == 0 && strlen($namn_e_till) == 0){
        echo "<script>alert('Organisationsnamn måste anges!');</script>";
    }
    else {
        $koll_svar = true;
    } 

    if ($land_till == 'Ange land') {
        $land_till = NULL;
    }
    if ($orgtyp_till == 'Ange organisationstyp') {
        $orgtyp_till = NULL;
    }

    if ($koll_svar && $a_org_id <> $u_org_id) {

        $org_typ_code = NULL;
        if (strlen($orgtyp_till) > 0) {
            $sql_orgtyp = "SELECT Org_type_code FROM Organization_type WHERE Org_type_eng = '" . $orgtyp_till . "'";
            $stmt = $dbh->query( $sql_orgtyp );
            foreach ($stmt as $row) {
                $org_typ_code = $row['Org_type_code'];      
            }
        } 
               
        $sql_u = "UPDATE unified_org_names SET Name_local = '" . $namn_l_till . "',Name_en = '" . $namn_e_till . "',
        Country_name = '" . $land_till . "',Org_type_code = '" . $org_typ_code . "',
        Comment = '" . $komm_till . "',User_id = '" . $username . "',Latest_date = GETDATE(),ROR_id = SUBSTRING('" . $rorid_till . "',1,9)   
        WHERE Unified_org_id = " . $u_org_id;

        $stmt = $dbh->query( $sql_u );

        if ($count = $stmt->rowCount() > 0) {
            echo '<script language="javascript">';
            echo 'alert("Organisationen är nu ändrad!")';
            echo '</script>';
            $_SESSION['a_org_id'] = $u_org_id;             
        }
        else {
            echo '<script language="javascript">';
            echo 'alert("Fel vid ändring av organisationen!")';
            echo '</script>';            
        }

    }

?>

<h2>ÄNDRA ORGANISATIONSNAMN</h2>	
	                                    
		    <form action="aendra_organisation_resultat.php" method="post">
                <a href='aendra_organisation.php'>TILLBAKA</a>&nbsp;&nbsp;
                <a href='organisationsnamn.php'>TILL SÖKNING</a>&nbsp;&nbsp;
                <a href='adressmeny.php'>TILL MENYN</a>
                <br /><br /><br /><br />

			    Orgid:</br> 
				<input type="text" name="Orgid" value="<?php echo $u_org_id; ?>" disabled />&nbsp;&nbsp;                  
                <br />
                
			    Lokalt namn:</br> 
				<input type="text" name="Namn_lok_ut" value="<?php echo $namn_l_till; ?>" disabled />&nbsp;&nbsp; 
                </br>

			    Organisationsnamn:</br> 
				<input type="text" name="Namn_eng_ut" value="<?php echo $namn_e_till; ?>" disabled />&nbsp;&nbsp; 
                </br>

                Land:</br>
                <input type="text" name="Land_ut" value="<?php echo $land_till; ?>" disabled />&nbsp;&nbsp;
                </br>

                Organisationstyp:</br>
                <input type="text" name="Orgtyp_ut" value="<?php echo $orgtyp_till; ?>" disabled />&nbsp;&nbsp;
                </br>

			    Kommentar:</br> 
				<input type="text" name="Komm_ut" value="<?php echo $komm_till; ?>" disabled />&nbsp;&nbsp; 
                <br />

			    ROR-id:</br> 
				<input type="text" name="RORid_ut" value="<?php echo $rorid_till; ?>" disabled />&nbsp;&nbsp; 
                <br />
				
		    </form>
								
	</body>
</html>