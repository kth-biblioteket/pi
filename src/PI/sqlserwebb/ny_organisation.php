<?php session_start(); ?>

<!DOCTYPE html PUBLIC "-//w3c//DTD XHTMLm 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<! Författare: Cecilia Wiklander>
<! Syfte: Adressrättnings-hantering>
<! Ändringar: >

<head>

    <meta charset="utf-8">

    <title>NYTT ORGANISATIONSNAMN</title>
	
    <link href="Site.css" rel="stylesheet"> 
		
<script type="text/javascript">

    function f_populera_Land() {
        // Populera
        f_populera_Orgtyp();
        var e = document.getElementById("id_country");
        landlista = [];
        land_test = "";
        var x_antal = document.getElementById("id_country").length;
        var e = document.getElementById("id_country");
        for (i = 0; i < x_antal; i++) {
            land_test = e.options[i].text;
            landlista.push(land_test);
        }
        // Sökfälten Land
        document.getElementById("id_soek_land_s").value = "*";
        var soeklista = document.getElementById("id_s_land");
        var laengd = soeklista.length;
        for (i = 1; i < laengd; i++) {
            soeklista.remove(1);
        }
        var soeklista = document.getElementById("id_s_land");
        for (var i = 0; i < landlista.length; i++) {
            var opt = landlista[i];
            var el = document.createElement("option");
            el.textContent = opt;
            el.value = opt;
            soeklista.appendChild(el);
        }
    }

    function f_populera_soek_Land_S() {
        var v_text = document.getElementById("id_soek_land_s").value;
        if (v_text > "") {
            var soeklista = document.getElementById("id_s_land");
            var laengd = soeklista.length;
            for (i = 1; i < laengd; i++) {
                soeklista.remove(1);
            }
            // Skapa landlista utan urval
            if (v_text == "*") {
                for (var i = 0; i < landlista.length; i++) {
                    var opt = landlista[i];
                    var el = document.createElement("option");
                    el.textContent = opt;
                    el.value = opt;
                    soeklista.appendChild(el);
                }
            }
            // Skapa landlista med urval
            else {
                for (var i = 0; i < landlista.length; i++) {
                    var opt = landlista[i];
                    if (opt.toUpperCase().indexOf(v_text.toUpperCase()) > -1) {
                        var el = document.createElement("option");
                        el.textContent = opt;
                        el.value = opt;
                        soeklista.appendChild(el);
                    }
                }
            }
        }
        return true;
    }

    function f_populera_Orgtyp() {
        // Populera
        orglista = [];
        org_test = "";
        var x_antal = document.getElementById("id_organization_type").length;
        var e = document.getElementById("id_organization_type");
        for (i = 0; i < x_antal; i++) {
            org_test = e.options[i].text;
            orglista.push(org_test);
        }
        // Sökfälten Land
        var soeklista = document.getElementById("id_s_orgtyp");
        var laengd = soeklista.length;
        for (i = 1; i < laengd; i++) {
            soeklista.remove(1);
        }
        var soeklista = document.getElementById("id_s_orgtyp");
        for (var i = 0; i < orglista.length; i++) {
            var opt = orglista[i];
            var el = document.createElement("option");
            el.textContent = opt;
            el.value = opt;
            soeklista.appendChild(el);
        }
    }

    function f_Ladda_sida() {
        f_populera_Land();
    }

</script>	
	
</head>

<body onload="f_Ladda_sida()">

<?php include('include_head_new.html'); ?>

<?php

    $username = $_SESSION['anv'];
    $password = $_SESSION['ord'];
    $hostname = $_SESSION['hnamn'];
    $dbname = $_SESSION['dbnamn'];

    $dbh = new PDO("sqlsrv:Server=$hostname;Database=$dbname",$username,$password);

    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (isset($_POST['spara'])) {
        $namn_l = $_POST['Namn_lok'];
        $namn_e = $_POST['Namn_eng'];
        $land = $_POST['Land'];
        $orgtyp = $_POST['Orgtyp'];
        $komm = $_POST['Komm'];
        $rorid = $_POST['RORid'];

        $Sk = "'";
        $Ers = "''";

        $namn_l = str_replace($Sk, $Ers, $namn_l);
        $namn_e = str_replace($Sk, $Ers, $namn_e);
        $komm = str_replace($Sk, $Ers, $komm);

        if (strlen($namn_l) == 0 && strlen($namn_e) == 0){
            echo "<script>alert('Organisationsnamn måste anges!');</script>";
            $koll_svar = false;
        }
        else {
            $koll_svar = true;
        }

        if (strlen($namn_e) == 0){
            echo "<script>alert('Engelskt organisationsnamn måste anges!');</script>";
            $koll_svar = false;
        }
        else {
            $koll_svar = true;
        } 

        if (strlen($orgtyp) == 0 || $orgtyp == 'Ange organisationstyp'){
            echo "<script>alert('Organisationstyp måste anges!');</script>";
            $koll_svar = false;
        }
        else {
            $koll_svar = true;
        }
 
        if ($land == 'Ange land') {
            $land = NULL;
        }

        $n_org = $_SESSION['n_org'];

        if (strlen($namn_l) == 0) {
            $org_koll = $namn_e;
        }
        else {
            $org_koll = $namn_l;
        }

        if ($koll_svar && $n_org <> $org_koll) {

            $sql_s = "SELECT MAX(Unified_org_id)+1 AS Unified_org_id FROM Unified_org_names";

            $stmt = $dbh->query( $sql_s );

            foreach ($stmt as $row) {
               $unif_org_id = $row['Unified_org_id']; 
            }

            $sql_o = "SELECT Org_type_code FROM organization_type WHERE Org_type_eng = '" . $orgtyp . "'";

            $stmt = $dbh->query( $sql_o );

            foreach ($stmt as $row) {
               $org_type_code = $row['Org_type_code']; 
            }
                  
            $sql_i = "INSERT INTO Unified_org_names (Unified_org_id,Name_local,Name_en,Country_name,Org_type_code,Comment,User_id,Latest_date,ROR_id) VALUES (" 
            . $unif_org_id . ",'" . $namn_l . "','" . $namn_e . "','" . $land . "','" . $org_type_code . "','" . $komm . "','" . $username . "',GETDATE(),SUBSTRING('" . $rorid . "',1,9))";

         																			            
            $stmt = $dbh->query( $sql_i );

            if ($count = $stmt->rowCount() > 0) {
                echo '<script language="javascript">';
                echo 'alert("Organisationen är sparad!")';
                echo '</script>';  
                $_SESSION['n_org'] = $org_koll;          
            }
            else {
                echo '<script language="javascript">';
                echo 'alert("Fel vid sparande av organisationen!")';
                echo '</script>';            
            }

        }

    }

	// Hämta länder ur tabellen Country

	$sql_c = "SELECT Display_name FROM country";

	// Execute it, or let it throw an error message if there's a problem.

	$stmt = $dbh->query( $sql_c );

    $dropdown = "<select name='country' hidden id='id_country'>";

	foreach ($stmt as $row) {

    $dropdown .= "\r\n<option value='{$row['Display_name']}'>{$row['Display_name']}</option>";

	}

	$dropdown .= "\r\n</select>";

	echo $dropdown;

	// Hämta organisationstyp ur tabellen Organization_type

	$sql_o = "SELECT Org_type_eng FROM organization_type";

	// Execute it, or let it throw an error message if there's a problem.

	$stmt = $dbh->query( $sql_o );

    $dropdown = "<select name='organization_type' hidden id='id_organization_type'>";

	foreach ($stmt as $row) {

    $dropdown .= "\r\n<option value='{$row['Org_type_eng']}'>{$row['Org_type_eng']}</option>";

	}

	$dropdown .= "\r\n</select>";

	echo $dropdown;

?>

<h2>NYTT ORGANISATIONSNAMN</h2>	
	                                    
		    <form action="ny_organisation.php" method="post">

                <br />
                <input type="submit" name="spara" value="Spara organisation"/>&nbsp;&nbsp;
                <a href='organisationsnamn.php'>TILL SÖKNING</a>&nbsp;&nbsp;
                <a href='adressmeny.php'>TILL MENYN</a>
                <br /><br /><br /><br />   

		Lokalt namn (#):</br> 
		<input type="text" name="Namn_lok" /></br>
		Engelskt namn (#):</br> 
		<input type="text" name="Namn_eng" /></br>
                Land:</br>
                <select id="id_s_land" name="Land">
                    <option>Ange land</option>
                </select>
                &nbsp;<input type="text" name="Soek_land_s" id="id_soek_land_s" onchange="f_populera_soek_Land_S()" />
                <br />
                Organisationstyp:</br>
                <select id="id_s_orgtyp" name="Orgtyp">
                    <option>Ange organisationstyp</option>
                </select>
                <br />	
		Kommentar:<br /> 
		<input type="text" name="Komm" />
		<br />

		ROR-id:<br /> 
		<input type="text" name="RORid" />
		<br />
		<br />

		    </form>

            <p>De fält som har en # efter är obligatoriska.</p>
								
	</body>
</html>