<?php session_start(); ?>

<!DOCTYPE html PUBLIC "-//w3c//DTD XHTMLm 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<! Författare: Cecilia Wiklander>
<! Syfte: Adressrättnings-hantering>
<! Ändringar: >

<head>

    <meta charset="utf-8">

    <title>TA BORT ORGANISATIONSNAMN</title>
	
    <link href="Site.css" rel="stylesheet"> 
	
</head>

<body>

<?php include('include_head_new.html'); ?>

<?php
    
    $u_org_id = $_SESSION['u_org_id'];

    $username = $_SESSION['anv'];
    $password = $_SESSION['ord'];
    $hostname = $_SESSION['hnamn'];
    $dbname = $_SESSION['dbnamn'];

    $dbh = new PDO("sqlsrv:Server=$hostname;Database=$dbname",$username,$password);

    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $b_org_id = $_SESSION['b_org_id'];
    
    if ($b_org_id <> $u_org_id) {        

        // Spara undan organisationen

        $unified_org_id = $_SESSION['unified_org_id'];
        $namn_lok = $_SESSION['namn_lok'];
        $namn_eng = $_SESSION['namn_eng'];
        $land = $_SESSION['land'];
        $orgtyp = $_SESSION['orgtyp'];
        $komm = $_SESSION['komm'];
        $user_id = $_SESSION['user_id'];
        $latest_date = $_SESSION['latest_date'];
        $orsak = $_POST['orsak'];
        $rorid = $_POST['rorid'];

        $Sk = "'";
        $Ers = "''";

        $namn_lok = str_replace($Sk, $Ers, $namn_lok);
        $namn_eng = str_replace($Sk, $Ers, $nnamn_eng);
        $komm = str_replace($Sk, $Ers, $komm);

        $sql_i = "INSERT INTO removed_un_org_names (Unified_org_id,Name_local,Name_en,Country_name,Org_type_code,Comment,User_id,Latest_date,Remove_user_id,Remove_date,Reason,ROR_id) 
        VALUES (" . $unified_org_id . ",'" . $namn_lok . "','" . $namn_eng . "','" . $land . "','" . $orgtyp . "','" . $komm . "','" . $user_id . "','" . $latest_date . 
        "','" . $username . "',GETDATE(),'" . $orsak . "','" . $rorid . "')";

        $stmt = $dbh->query( $sql_i );

        // Ta bort regeln

	    $sql_d = "DELETE FROM unified_org_names WHERE Unified_org_id = " . $u_org_id;

        $stmt = $dbh->query( $sql_d );
             
        if ($count = $stmt->rowCount() > 0) {
            echo '<script language="javascript">';
            echo 'alert("Organisationen är borttagen!")';
            echo '</script>'; 
            $_SESSION['b_org_id'] = $u_org_id;           
        }
        else {
            echo '<script language="javascript">';
            echo 'alert("Fel vid borttagande av organisationen!")';
            echo '</script>';            
        }
    }

?>

<h2>TA BORT ORGANISATIONSNAMN</h2>	
	                                    
		    <form action="ta_bort_organisation_resultat.php" method="post">

                <input type="submit" name="radera" value="Radera organisation" disabled />&nbsp;&nbsp;
                <a href='organisationsnamn.php'>TILL SÖKNING</a>&nbsp;&nbsp;
                <a href='adressmeny.php'>TILL MENYN</a>
                <br /><br />

			    Orgid:</br> 
				<input type="text" name="Orgid" disabled />&nbsp;&nbsp; 
                <br />

			    Lokalt namn:</br> 
				<input type="text" name="Namn_lok_ut" disabled />&nbsp;&nbsp; 
                <br />

			    Engelskt namn:</br> 
				<input type="text" name="Namn_eng_ut" disabled />&nbsp;&nbsp; 
                <br />

                Land:</br>
                <input type="text" name="Land_ut" disabled />&nbsp;&nbsp;
                <br />

                Organisationstyp:</br>
                <input type="text" name="Orgtyp_ut" disabled />&nbsp;&nbsp;
                <br />

			    Kommentar:</br> 
				<input type="text" name="Komm_ut" disabled />&nbsp;&nbsp; 
                <br />
			
			    ROR-id:</br> 
				<input type="text" name="RORid" disabled />&nbsp;&nbsp; 
                <br />
	
		    </form>
								
	</body>
</html>