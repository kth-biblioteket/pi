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

    $b_regel_o_typ_id = $_SESSION['b_regel_o_typ_id'];

    if ($b_regel_o_typ_id <> $regel_id) { 

        // Ta bort regeln

	    $sql_d = "DELETE FROM rule_org_type_match WHERE R_o_t_m_id = " . $regel_id;

        $stmt = $dbh->query( $sql_d );

        if ($count = $stmt->rowCount() > 0) {
            echo '<script language="javascript">';
            echo 'alert("Regeln är borttagen!")';
            echo '</script>'; 
            $_SESSION['b_regel_o_typ_id'] = $regel_id;           
        }
        else {
            echo '<script language="javascript">';
            echo 'alert("Fel vid borttagande av regeln!")';
            echo '</script>';            
        }
    }

?>

<h2>TA BORT REGEL ORGANISATIONSTYP</h2>	
	                                    
		    <form action="ta_bort_regel_resultat_o_typ.php" method="post">
                <a href='regel_organisation_typ.php'>TILL SÖKNING</a>&nbsp;&nbsp;
                <a href='adressmeny.php'>TILL MENYN</a>
                <br /><br />

                <h3>SÖKFÄLT</h3>    
                
                Land:</br>
                <input type="text" name="Land" id="id_land_s" disabled />
                <br />
			    Stad:</br> 
				<input type="text" name="Stad" id="id_s_stad" disabled />
                <br />
			    Organisation, sträng 1:</br> 
				<input type="text" name="Organisation1" id="id_s_org_1" disabled /><br />
			    Organisation, sträng 2:</br> 
				<input type="text" name="Organisation2" id="id_s_org_2" disabled /><br />
			    Organisation, sträng ej:</br> 
				<input type="text" name="Organisationej" id="id_s_org_ej" disabled /><br />
				
				<h3>ÄNDRINGSFÄLT</h3>

			    Organisationstyp:<br />
                <input type="text" name="Orgtypkod" id="id_h_org_kod" disabled />				
                <br />
			    Annat land:<br />
                <input type="text" name="Soek_land_h_1" id="id_soek_land_h_1" size="20" disabled />				
                <br />				
				Annan stad:<br /> 
				<input type="text" name="Annan_stad_1" id="h_id_stad_1" disabled /><br />
				<br />       
				
		    </form>
								
	</body>
</html>