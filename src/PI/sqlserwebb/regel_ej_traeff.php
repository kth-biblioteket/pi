<?php session_start(); ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml11/DTD/xhtml-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<! Författare: Cecilia Wiklander>
<! Syfte: Adressrättnings-hantering>
<! Ändringar: >

<head>

    <meta charset="utf-8">

    <title>REGLER SOM EJ TRÄFFAR</title>

    <link href="Site.css" rel="stylesheet">

<script>

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

    function getValueFromRadioButton(name) {
        //Get all elements with the name
        var buttons = document.getElementsByName(name);
        for(var i = 0; i < buttons.length; i++) {
            //Check if button is checked
            var button = buttons[i];
            if(button.checked) {
                //Return value
                return button.value;
            }
        }
        //No radio button is selected. 
        return null;
    }

    function f_Ladda_sida() {
        f_populera_Land();
        document.getElementById("id_soek_land_s").value = "*";
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

	// Write out our query.

	$query = "SELECT Display_name FROM country";

	// Execute it, or let it throw an error message if there's a problem.

	$stmt = $dbh->query( $query );

    $dropdown = "<select name='country' hidden id='id_country'>";

	foreach ($stmt as $row) {

    $dropdown .= "\r\n<option value='{$row['Display_name']}'>{$row['Display_name']}</option>";

	}

	$dropdown .= "\r\n</select>";

	echo $dropdown;

?>

<h2>REGLER SOM EJ TRÄFFAR</h2>

<form action="visa_regler_ej_traeff.php" method="post">

<input type="submit" name="soek" value="Lista regler"/>&nbsp;&nbsp;
<a href='adressmeny.php'>TILL MENYN</a>
<br /><br />

<h3>SÖKURVAL</h3>

Land: <br />
<select id="id_s_land" name="Land">
    <option>Ange land</option>
</select>
&nbsp;<input type="text" name="Soek_land_s" id="id_soek_land_s" size="20" font size = "2" onchange="f_populera_soek_Land_S()"/>
<br /><br /><br />
Regeltyp:
<br />
<label><Input type = 'Radio' Name ='Typ' value= 'org' checked><b>Organisation</b></label><br />
<label><Input type = 'Radio' Name ='Typ' value= 'fa'><b>Full adress</b></label><br />
<label><Input type = 'Radio' Name ='Typ' value= 'centra'><b>Centra</b></label><br />
<label><Input type = 'Radio' Name ='Typ' value= 'orgtyp'><b>Organisationstyp</b></label>
<br /><br />

</form>

</body>
</html>