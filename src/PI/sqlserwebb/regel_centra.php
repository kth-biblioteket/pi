<?php session_start(); ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml11/DTD/xhtml-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<! Författare: Cecilia Wiklander>
<! Syfte: Adressrättnings-hantering>
<! Ändringar: >

<head>

    <meta charset="utf-8">

    <title>REGLER CENTRA</title>

    <link href="Site_utan_storlek.css" rel="stylesheet">

<script>

    function f_populera_Land() {
        // Populera
        var e = document.getElementById("id_country");
        var strUser = e.options[3].text;
        document.getElementById("id_soek_land_s").value = strUser;
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

<h2>REGLER CENTRA</h2>

<form action="visa_regler_c.php" method="post">

<input type="submit" name="soek" value="Sök regel"/>&nbsp;&nbsp;
<input type="submit" formaction="ny_regel_c.php" name="test" value="Ny regel"/>&nbsp;&nbsp;
<input type="submit" formaction="regler_c_utan.php" name="utan" value="Regel utan träff"/>&nbsp;&nbsp;
<a href='adressmeny.php'>TILL MENYN</a>
<a href='aendra_TEST.php'>test</a>
<br /><br />

<h3>SÖKURVAL</h3>

Regelid: <br />
<input type="text" name="Regelid" size="10" /><br /><br />
Land: <br />
<select id="id_s_land" name="Land">
    <option>Ange land</option>
</select>
&nbsp;<input type="text" name="Soek_land_s" id="id_soek_land_s" size="20" font size = "2" onchange="f_populera_soek_Land_S()"/>
<br /><br />
Stad: <br />
<input type="text" name="Stad" size="70" /><br /><br />
Organisation: <br />
<input type="text" name="Org" size="70" />
<br /><br />

</form>

</body>
</html>