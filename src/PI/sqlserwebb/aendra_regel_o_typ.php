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

<script type="text/javascript">

    function f_populera_Land() {
        // Populera
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

        // Ändrafältet Land 1
        document.getElementById("id_soek_land_h_1x").value = "*";
        var soeklista = document.getElementById("id_h_land_1");
        var laengd = soeklista.length;
        for (i = 1; i < laengd; i++) {
            soeklista.remove(1);
        }
        var soeklista = document.getElementById("id_h_land_1");
        for (var i = 0; i < landlista.length; i++) {
            var opt = landlista[i];
            var el = document.createElement("option");
            el.textContent = opt;
            el.value = opt;
            soeklista.appendChild(el);
        }   

        f_populera_Orgtyp();

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

    function f_S_Land() {
       selectElement = document.querySelector('#id_s_land');
       document.getElementById("id_soek_land_s_2").value = selectElement.value;
    }

    function f_H_Land_1() {     
       selectElement = document.querySelector('#id_h_land_1');
       document.getElementById("id_soek_land_h_1_2").value = selectElement.value;
    }

     function f_H_Org_typ() {
       selectElement = document.querySelector('#id_orgtyp');
       document.getElementById("id_org_h_typ").value = selectElement.value;
    }

    function f_Ladda_sida() {
        f_populera_Land();
    }

</script>
	
</head>

<body onload="f_Ladda_sida()">

<?php include('include_head_new.html'); ?>

<?php

    $regel_id = $_GET["Regel_id"];

    if (strlen($regel_id) > 0) {
        $_SESSION['regel_id'] = $regel_id;
    }
    else {
        $regel_id_ut = $_SESSION['regel_id_ut'];
        $regel_id = $regel_id_ut;
    }

    if (intval($regel_id) > 0) {

        $username = $_SESSION['anv'];
        $password = $_SESSION['ord'];
        $hostname = $_SESSION['hnamn'];
        $dbname = $_SESSION['dbnamn'];

        $dbh = new PDO("sqlsrv:Server=$hostname;Database=$dbname",$username,$password);

        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Visa regeln att ta ändra

    	$sql = "SELECT Find_country,Find_city,Find_org_1,Find_org_2,Find_org_not,
        Country,City,Org_type_code,Country_code 
        FROM rule_org_type_match  
        WHERE R_o_t_m_id = " . $regel_id;

        $stmt = $dbh->query( $sql );

    	foreach ($stmt as $row) {
            $land_s = $row['Find_country'];
            $land_s_2 = $row['Find_country'];
            $land_h_1 = $row['Country'];
            $land_h_1_2 = $row['Country'];
            $stad_s = $row['Find_city'];
            $stad_s_2 = $row['Find_city'];
            $stad_h_1 = $row['City'];
            $stad_h_1_2 = $row['City'];
            $org_s_1 = $row['Find_org_1'];
            $org_s_2 = $row['Find_org_2'];
            $org_s_ej = $row['Find_org_not'];
            $org_typ_kod = $row['Org_type_code'];
            $landkod = $row['Country_code'];                     
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

        $sql_orgtyp = "SELECT Org_type_eng FROM Organization_type WHERE Org_type_code = '" . $org_typ_kod . "'";
        $stmt = $dbh->query( $sql_orgtyp );
        foreach ($stmt as $row) {
            $org_typ_en = $row['Org_type_eng']; 
            $org_typ_en_2 = $row['Org_type_eng'];                  
        } 

    }

?>

<h2>ÄNDRA REGEL ORGANISATIONSTYP</h2>	
	                                    
		<form action="aendra_regel_resultat_o_typ.php" method="post">

                <input type="submit" name="spara" value="Spara regel"/>&nbsp;&nbsp;
                <input type="submit" formaction="traeff_regler_o_typ.php" name="koer" value="Regelträffar"/>&nbsp;&nbsp;
                <a href='regel_organisation_typ.php'>TILL SÖKNING</a>&nbsp;&nbsp;
                <a href='adressmeny.php'>TILL MENYN</a>
                <br /><br /><br />

                <input type="text" value="Nuvarande utseende:" style="background-color:#7c9fc1;font-size:150%;color:black;border:0"  disabled/>&nbsp;&nbsp;
                <input type="text" value="Nytt utseende:" style="background-color:#7c9fc1;font-size:150%;color:black;border:0"  disabled/>

                <br />

                <h3>SÖKFÄLT</h3>    
                
                Land:</br>
                <input type="text" name="Land_ut" value="<?php echo $land_s; ?>" style="background-color:grey;color:white;border:0" disabled />&nbsp;&nbsp;
                <input type="text" name="Land_ut_2" id="id_soek_land_s_2" value="<?php echo $land_s_2; ?>" style="background-color:green;color:white;border:0" />&nbsp;&nbsp;
                                
                <select id="id_s_land" name="Land_till" onchange="f_S_Land()" >
                    <option>Ange land</option>
                </select>
                &nbsp;<input type="text" name="Soek_land_s" id="id_soek_land_s" onchange="f_populera_soek_Land_S()" hidden />
                <br />
			    Stad:</br> 
				<input type="text" name="Stad_ut" value="<?php echo $stad_s; ?>" style="background-color:grey;color:white;border:0" disabled />&nbsp;&nbsp;
				<input type="text" name="Stad_nu" value="<?php echo $stad_s; ?>" hidden />
				<input type="text" name="Stad_ut_2" id="id_s_stad" value="<?php echo $stad_s_2; ?>" style="background-color:green;color:white;border:0" />
                <br />
			    Organisation, sträng 1 #:</br> 
				<input type="text" name="Org_ut_1" value="<?php echo $org_s_1; ?>" style="background-color:grey;color:white;border:0" disabled />&nbsp;&nbsp; 
			    <input type="text" name="Org_nu_1" value="<?php echo $org_s_1; ?>" hidden /> 
				<input type="text" name="Org_till_1" id="id_s_org_1" value="<?php echo $org_s_1; ?>" style="background-color:green;color:white;border:0" />
                <br />
			    Organisation, sträng 2:</br> 
				<input type="text" name="Org_ut_2" value="<?php echo $org_s_2; ?>" style="background-color:grey;color:white;border:0" disabled />&nbsp;&nbsp; 
			    <input type="text" name="Org_nu_2" value="<?php echo $org_s_2; ?>" hidden /> 
				<input type="text" name="Org_till_2" id="id_s_org_2" value="<?php echo $org_s_2; ?>" style="background-color:green;color:white;border:0" />
                <br />
			    Organisation, sträng ej:</br> 
				<input type="text" name="Org_ut_ej" value="<?php echo $org_s_ej; ?>" style="background-color:grey;color:white;border:0" disabled />&nbsp;&nbsp; 
			    <input type="text" name="Org_nu_ej" value="<?php echo $org_s_ej; ?>" hidden /> 
				<input type="text" name="Org_till_ej" id="id_s_org_ej" value="<?php echo $org_s_ej; ?>" style="background-color:green;color:white;border:0" />
                <br />
				
		<h3>ÄNDRINGSFÄLT</h3>
			
                Organisationstyp #:</br>
                <input type="text" name="Orgtyp_ut" value="<?php echo $org_typ_en; ?>" style="background-color:grey;color:white;border:0" disabled />&nbsp;&nbsp;
                <input type="text" name="Orgtyp_ut_2" id="id_org_h_typ" value="<?php echo $org_typ_en_2; ?>" style="background-color:green;color:white;border:0" readonly="readonly" />&nbsp;&nbsp;
                <input type="text" name="Orgtyp_nu" value="<?php echo $org_typ_en; ?>" hidden />
                <select id="id_orgtyp" name="Orgtyp_till" onchange="f_H_Org_typ()" >
                    <option>Ange organisationstyp</option>
                </select>
                <br />                
                
			    Annat land:<br />
                <input type="text" name="Land_1_ut" value="<?php echo $land_h_1; ?>" style="background-color:grey;color:white;border:0" disabled />&nbsp;&nbsp;
                <input type="text" name="Land_1_ut_2" id="id_soek_land_h_1_2" value="<?php echo $land_h_1_2; ?>" style="background-color:green;color:white;border:0"  />&nbsp;&nbsp;                
                <input type="text" name="Land_1_nu" id="id_soek_land_h_1x" value="<?php echo $land_h_1; ?>" hidden />
                <select id="id_h_land_1" name="Land_1_till" onchange="f_H_Land_1()">
                    <option>Ange land</option>
                </select>
                &nbsp;<input type="text" name="Soek_land_h_1" id="id_soek_land_h_1" onchange="f_populera_soek_Land_H_1()" hidden />				
                <br />                								
				Annan stad:<br /> 
				<input type="text" name="Stad_1_ut" value="<?php echo $stad_h_1; ?>" style="background-color:grey;color:white;border:0" disabled />&nbsp;&nbsp;
				<input type="text" name="Stad_1_nu" value="<?php echo $stad_h_1; ?>" hidden />
				<input type="text" name="Stad_1_till" id="h_id_stad_1" value="<?php echo $stad_h_1; ?>" style="background-color:green;color:white;border:0" />
                <br />
				<br />
 
				<input type="text" name="Land_kod_nu" value="<?php echo $landkod; ?>" hidden />
				
		    </form>
								
	</body>
</html>