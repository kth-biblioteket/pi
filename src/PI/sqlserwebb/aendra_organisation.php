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

<script type="text/javascript">

    function f_populera_Land() {
        // Populera
        f_populera_Orgtyp();
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
        // Ändrafältet Land 1
        document.getElementById("id_soek_land_h_1").value = "*";
        var soeklista = document.getElementById("id_h_land_1");
        var laengd = soeklista.length;
        for (i = 1; i < laengd; i++) {
            soeklista.remove(1);
        }
        for (var i = 0; i < landlista.length; i++) {
            var opt = landlista[i];
            var el = document.createElement("option");
            el.textContent = opt;
            el.value = opt;
            soeklista.appendChild(el);
        }
        // Ändrafältet Land 2
        document.getElementById("id_soek_land_h_2").value = "*";
        var soeklista = document.getElementById("id_h_land_2");
        var laengd = soeklista.length;
        for (i = 1; i < laengd; i++) {
            soeklista.remove(1);
        }
        for (var i = 0; i < landlista.length; i++) {
            var opt = landlista[i];
            var el = document.createElement("option");
            el.textContent = opt;
            el.value = opt;
            soeklista.appendChild(el);
        }
        // Ändrafältet Land 3
        document.getElementById("id_soek_land_h_3").value = "*";
        var soeklista = document.getElementById("id_h_land_3");
        var laengd = soeklista.length;
        for (i = 1; i < laengd; i++) {
            soeklista.remove(1);
        }
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

    function f_populera_soek_Land_H_1() {
        var v_text = document.getElementById("id_soek_land_h_1").value;
        if (v_text > "") {
            var soeklista = document.getElementById("id_h_land_1");
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

    function f_populera_soek_Land_H_2() {
        var v_text = document.getElementById("id_soek_land_h_2").value;
        if (v_text > "") {
            var soeklista = document.getElementById("id_h_land_2");
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

    function f_populera_soek_Land_H_3() {
        var v_text = document.getElementById("id_soek_land_h_3").value;
        if (v_text > "") {
            var soeklista = document.getElementById("id_h_land_3");
            var laengd = soeklista.length;
            for (i = 1; i < laengd; i++) {
                soeklista.remove(1);
            }
            // Skapa landlista utan urval
            if (v_text == "*") {
                for (var i = 0; i < landlista.length; i++) {
                    var opt = landlista[i].namn_eng;
                    var el = document.createElement("option");
                    el.textContent = opt;
                    el.value = opt;
                    soeklista.appendChild(el);
                }
            }
            // Skapa landlista med urval
            else {
                for (var i = 0; i < landlista.length; i++) {
                    var opt = landlista[i].namn_eng;
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
        orgtyplista = [];
        orgtyppost = "";
        var x_antal = document.getElementById("id_orgtyp_dold").length;
        var e = document.getElementById("id_orgtyp_dold");
        for (i = 0; i < x_antal; i++) {
            orgtyppost = e.options[i].text;
            orgtyplista.push(orgtyppost);
        }
        var soeklista = document.getElementById("id_orgtyp");
        for (var i = 0; i < orgtyplista.length; i++) {
            var opt = orgtyplista[i];
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
    
    $u_org_id = $_GET["Unified_org_id"];

    if (strlen($u_org_id) > 0) {
        $_SESSION['u_org_id'] = $u_org_id;
    }
    else {
        $u_org_id_ut = $_SESSION['u_org_id_ut'];
        $u_org_id = $u_org_id_ut;
    }

    if (intval($u_org_id) > 0) {

        $username = $_SESSION['anv'];
        $password = $_SESSION['ord'];
        $hostname = $_SESSION['hnamn'];
        $dbname = $_SESSION['dbnamn'];

        $dbh = new PDO("sqlsrv:Server=$hostname;Database=$dbname",$username,$password);

        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Visa organisationen att ta ändra

        $sql = "SELECT Name_local,Name_en,Country_name,Org_type_code,Comment,ROR_id FROM unified_org_names WHERE Unified_org_id = " . $u_org_id; 

        $stmt = $dbh->query( $sql );

    	foreach ($stmt as $row) {
            $namn_lok = $row['Name_local'];
            $namn_eng = $row['Name_en'];
            $land = $row['Country_name'];
            $orgtyp = $row['Org_type_code'];
            $komm = $row['Comment'];
            $rorid = $row['ROR_id'];         
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

    	// Hämta organisationstyper ur tabellen Organization_type

    	$sql_o = "SELECT Org_type_eng FROM Organization_type";

    	// Execute it, or let it throw an error message if there's a problem.

    	$stmt = $dbh->query( $sql_o );

        $dropdown = "<select name='organization_type' hidden id='id_orgtyp_dold'>";

    	foreach ($stmt as $row) {

        $dropdown .= "\r\n<option value='{$row['Org_type_eng']}'>{$row['Org_type_eng']}</option>";

    	}

    	$dropdown .= "\r\n</select>";

    	echo $dropdown;

        $sql_orgtyp = "SELECT Org_type_eng FROM Organization_type WHERE Org_type_code = '" . $orgtyp . "'";
        $stmt = $dbh->query( $sql_orgtyp );
        foreach ($stmt as $row) {
            $org_typ_eng = $row['Org_type_eng'];      
        } 

    }

?>

<h2>ÄNDRA ORGANISATIONSNAMN</h2>	
	                                    
		    <form action="aendra_organisation_resultat.php" method="post">

                <input type="submit" name="spara" value="Spara organisation"/>&nbsp;&nbsp;
                <a href='organisationsnamn.php'>TILL SÖKNING</a>&nbsp;&nbsp;
                <a href='adressmeny.php'>TILL MENYN</a>
                <br /><br /><br />

                <input type="text" value="Nuvarande utseende:" style="background-color:#7c9fc1;font-size:150%;color:black;border:0"  disabled/>&nbsp;&nbsp;
                <input type="text" value="Nytt utseende:" style="background-color:#7c9fc1;font-size:150%;color:black;border:0"  disabled/>

                <br /><br />   
                
			    Orgid:</br> 
				<input type="text" name="Orgid" value="<?php echo $u_org_id; ?>" style="background-color:grey;color:white;border:0" disabled />&nbsp;&nbsp;                  
                <br />

			    Lokalt namn:</br> 
				<input type="text" name="Namn_lok_ut" value="<?php echo $namn_lok; ?>" style="background-color:grey;color:white;border:0" size="10" disabled />&nbsp;&nbsp; 
			    <input type="text" name="Namn_lok_nu" value="<?php echo $namn_lok; ?>" hidden /> 
				<input type="text" name="Namn_lok_till" id="id_namn_lok" value="<?php echo $namn_lok; ?>" />
                <br />

			    Engelskt namn:</br> 
				<input type="text" name="Namn_eng_ut" value="<?php echo $namn_eng; ?>" style="background-color:grey;color:white;border:0" disabled />&nbsp;&nbsp; 
			    <input type="text" name="Namn_eng_nu" value="<?php echo $namn_eng; ?>" hidden /> 
				<input type="text" name="Namn_eng_till" id="id_namn_eng" value="<?php echo $namn_eng; ?>" />
                <br />

                Land:</br>
                <input type="text" name="Land_ut" value="<?php echo $land; ?>" style="background-color:grey;color:white;border:0" disabled />&nbsp;&nbsp;
                <input type="text" name="Land_nu" value="<?php echo $land; ?>" hidden />
                <select id="id_s_land" name="Land_till">
                    <option>Ange land</option>
                </select>
                &nbsp;<input type="text" name="Soek_land_s" id="id_soek_land_s" onchange="f_populera_soek_Land_S()" />
                <br />

                Organisationstyp:</br>
                <input type="text" name="Orgtyp_ut" value="<?php echo $org_typ_eng; ?>" style="background-color:grey;color:white;border:0" disabled />&nbsp;&nbsp;
                <input type="text" name="Orgtyp_nu" value="<?php echo $org_typ_eng; ?>" hidden />
                <select id="id_orgtyp" name="Orgtyp_till">
                    <option>Ange organisationstyp</option>
                </select>
                <br />

			    Kommentar:</br> 
				<input type="text" name="Komm_ut" value="<?php echo $komm; ?>" style="background-color:grey;color:white;border:0" disabled />&nbsp;&nbsp; 
			    <input type="text" name="Komm_nu" value="<?php echo $komm; ?>" hidden /> 
				<input type="text" name="Komm_till" id="id_komm" value="<?php echo $komm; ?>" />
                <br />

			    ROR-id:</br> 
				<input type="text" name="RORid_ut" value="<?php echo $rorid; ?>" style="background-color:grey;color:white;border:0" disabled />&nbsp;&nbsp; 
			    <input type="text" name="RORid_nu" value="<?php echo $rorid; ?>" hidden /> 
				<input type="text" name="RORid_till" id="id_rorid" value="<?php echo $rorid; ?>" />
                <br />
				
		    </form>
								
	</body>

</html>