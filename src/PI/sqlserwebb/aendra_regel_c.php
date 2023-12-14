<?php session_start(); ?>

<!DOCTYPE html PUBLIC "-//w3c//DTD XHTMLm 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<! Författare: Cecilia Wiklander>
<! Syfte: Adressrättnings-hantering>
<! Ändringar: >

<head>

    <meta charset="utf-8">

    <title>ÄNDRA REGEL CENTRA</title>
	
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

        // Ändrafältet Land 2
        document.getElementById("id_soek_land_h_2x").value = "*";
        var soeklista = document.getElementById("id_h_land_2");
        var laengd = soeklista.length;
        for (i = 1; i < laengd; i++) {
            soeklista.remove(1);
        }
        var soeklista = document.getElementById("id_h_land_2");
        for (var i = 0; i < landlista.length; i++) {
            var opt = landlista[i];
            var el = document.createElement("option");
            el.textContent = opt;
            el.value = opt;
            soeklista.appendChild(el);
        }       

        // Ändrafältet Land 3
        document.getElementById("id_soek_land_h_3x").value = "*";
        var soeklista = document.getElementById("id_h_land_3");
        var laengd = soeklista.length;
        for (i = 1; i < laengd; i++) {
            soeklista.remove(1);
        }
        var soeklista = document.getElementById("id_h_land_3");
        for (var i = 0; i < landlista.length; i++) {
            var opt = landlista[i];
            var el = document.createElement("option");
            el.textContent = opt;
            el.value = opt;
            soeklista.appendChild(el);
        }       

        // Populera orglistan
        f_populera_Org();

        // Ändrafältet Org 1
        var x_text = document.getElementById("id_soek_org_h_1x").value;
        var v_text = x_text.trim();
        if (v_text > "") {
            var soeklista = document.getElementById("id_h_org_1");
            // Skapa orglista med urval                   
            for (var i = 0; i < orglista.length; i++) {
                    var opt = orglista[i];
                    if (opt.toUpperCase().indexOf(v_text.toUpperCase()) > -1) {
                        var el = document.createElement("option");
                        el.textContent = opt;
                        el.value = opt;
                        soeklista.appendChild(el);
                    }
            }
                   
        }         
 
        // Ändrafältet Org 2
        var x_text = document.getElementById("id_soek_org_h_2x").value;
        var v_text = x_text.trim();
        if (v_text > "") {
            var soeklista = document.getElementById("id_h_org_2");
            // Skapa orglista med urval                   
            for (var i = 0; i < orglista.length; i++) {
                    var opt = orglista[i];
                    if (opt.toUpperCase().indexOf(v_text.toUpperCase()) > -1) {
                        var el = document.createElement("option");
                        el.textContent = opt;
                        el.value = opt;
                        soeklista.appendChild(el);
                    }
            }
                   
        } 

        // Ändrafältet Org 3
        var x_text = document.getElementById("id_soek_org_h_3x").value;
        var v_text = x_text.trim();
        if (v_text > "") {
            var soeklista = document.getElementById("id_h_org_3");
            // Skapa orglista med urval                   
            for (var i = 0; i < orglista.length; i++) {
                    var opt = orglista[i];
                    if (opt.toUpperCase().indexOf(v_text.toUpperCase()) > -1) {
                        var el = document.createElement("option");
                        el.textContent = opt;
                        el.value = opt;
                        soeklista.appendChild(el);
                    }
            }
                   
        } 

    }

    function f_populera_soek_Land_S() {
        var x_text = document.getElementById("id_soek_land_s").value;
        var v_text = x_text.trim();
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
        var x_text = document.getElementById("id_soek_land_h_1").value;
        var v_text = x_text.trim();
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
        var x_text = document.getElementById("id_soek_land_h_2").value;
        var v_text = x_text.trim();
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
        var x_text = document.getElementById("id_soek_land_h_3").value;
        var v_text = x_text.trim();
        if (v_text > "") {
            var soeklista = document.getElementById("id_h_land_3");
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

    function f_populera_Org() {
        // Populera
        var e = document.getElementById("id_unified_org_names");
        orglista = [];
        org_test = "";
        var x_antal = document.getElementById("id_unified_org_names").length;
        var e = document.getElementById("id_unified_org_names");
        for (i = 0; i < x_antal; i++) {
            org_test = e.options[i].text;
            orglista.push(org_test);
        }
        return true;
    }

    function f_populera_soek_Org_H_1() {
        var x_text = document.getElementById("id_soek_org_h_1").value;
        var v_text = x_text.trim();
        if (v_text > "") {
            var soeklista = document.getElementById("id_h_org_1");
            var laengd = soeklista.length;
            for (i = 1; i < laengd; i++) {
                soeklista.remove(1);
            }
            // Skapa orglista utan urval
            if (v_text == "*") {
                for (var i = 0; i < orglista.length; i++) {
                    var opt = orglista[i];
                    var el = document.createElement("option");
                    el.textContent = opt;
                    el.value = opt;
                    soeklista.appendChild(el);
                }
            }
            // Skapa orglista med urval
            else {
                for (var i = 0; i < orglista.length; i++) {
                    var opt = orglista[i];
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

    function f_populera_soek_Org_H_2() {
        var x_text = document.getElementById("id_soek_org_h_2").value;
        var v_text = x_text.trim();
        if (v_text > "") {
            var soeklista = document.getElementById("id_h_org_2");
            var laengd = soeklista.length;
            for (i = 1; i < laengd; i++) {
                soeklista.remove(1);
            }
            // Skapa orglista utan urval
            if (v_text == "*") {
                for (var i = 0; i < orglista.length; i++) {
                    var opt = orglista[i];
                    var el = document.createElement("option");
                    el.textContent = opt;
                    el.value = opt;
                    soeklista.appendChild(el);
                }
            }
            // Skapa orglista med urval
            else {
                for (var i = 0; i < orglista.length; i++) {
                    var opt = orglista[i];
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
    
    function f_populera_soek_Org_H_3() {
        var x_text = document.getElementById("id_soek_org_h_3").value;
        var v_text = x_text.trim();
        if (v_text > "") {
            var soeklista = document.getElementById("id_h_org_3");
            var laengd = soeklista.length;
            for (i = 1; i < laengd; i++) {
                soeklista.remove(1);
            }
            // Skapa orglista utan urval
            if (v_text == "*") {
                for (var i = 0; i < orglista.length; i++) {
                    var opt = orglista[i];
                    var el = document.createElement("option");
                    el.textContent = opt;
                    el.value = opt;
                    soeklista.appendChild(el);
                }
            }
            // Skapa orglista med urval
            else {
                for (var i = 0; i < orglista.length; i++) {
                    var opt = orglista[i];
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

    function f_S_Land() {
       selectElement = document.querySelector('#id_s_land');
       document.getElementById("id_soek_land_s_2").value = selectElement.value;
    }
    
    function f_H_Dela() {
       selectElement = document.querySelector('#id_delas');
       document.getElementById("id_delas_h_2").value = selectElement.value; 
    }
    
    function f_H_Land_1() {     
       selectElement = document.querySelector('#id_h_land_1');
       document.getElementById("id_soek_land_h_1_2").value = selectElement.value;
    }
    
    function f_H_Land_2() {
       selectElement = document.querySelector('#id_h_land_2');
       document.getElementById("id_soek_land_h_2_2").value = selectElement.value;
    }
    
    function f_H_Land_3() {
       selectElement = document.querySelector('#id_h_land_3');
       document.getElementById("id_soek_land_h_3_2").value = selectElement.value;
    }
    
     function f_H_Org_1() {
       selectElement = document.querySelector('#id_h_org_1');
       document.getElementById("id_org_h_1_2").value = selectElement.value;
    }
    
    function f_H_Org_2() {
       selectElement = document.querySelector('#id_h_org_2');
       document.getElementById("id_org_h_2_2").value = selectElement.value;
    }
    
    function f_H_Org_3() {
       selectElement = document.querySelector('#id_h_org_3');
       document.getElementById("id_org_h_3_2").value = selectElement.value;
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

    	$sql = "SELECT r.Find_country,r.Find_city,r.Find_org,r.Divide,
        r.Country_1,r.City_1,o1.Name_en + ' [' + o1.Country_name + ']' AS Orgname_1,
        r.Country_2,r.City_2,o2.Name_en + ' [' + o2.Country_name + ']' AS Orgname_2,
        Country_3,City_3,o3.Name_en + ' [' + o3.Country_name + ']' AS Orgname_3 
        FROM rule_center_match r  
        JOIN unified_org_names o1 
        ON r.Org_id_1 = o1.Unified_org_id  
        LEFT JOIN unified_org_names o2 
        ON r.Org_id_2 = o2.Unified_org_id 
        LEFT JOIN unified_org_names o3 
        ON r.Org_id_3 = o3.Unified_org_id 
        WHERE R_c_m_id = " . $regel_id;

        $stmt = $dbh->query( $sql );

    	foreach ($stmt as $row) {
            $land_s = $row['Find_country'];
            $land_s_2 = $row['Find_country'];
            $land_h_1 = $row['Country_1'];
            $land_h_1_2 = $row['Country_1'];            
            $land_h_2 = $row['Country_2'];
            $land_h_2_2 = $row['Country_2'];            
            $land_h_3 = $row['Country_3'];
            $land_h_3_2 = $row['Country_3'];            
            $stad_s = $row['Find_city'];
            $stad_h_1 = $row['City_1'];
            $stad_h_2 = $row['City_2'];
            $stad_h_3 = $row['City_3'];
            $org_s = $row['Find_org'];
            $org_h_1 = $row['Orgname_1'];
            $org_h_1_2 = $row['Orgname_1'];            
            $org_h_2 = $row['Orgname_2'];
            $org_h_2_2 = $row['Orgname_2'];            
            $org_h_3 = $row['Orgname_3'];
            $org_h_3_2 = $row['Orgname_3'];            
            $delas = $row['Divide'];   
            $delas_2 = $row['Divide'];                 
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

    	// Hämta enhetliga organisationsnamn ur tabellen Unified_org_names

    	$sql_o = "SELECT Name_en + ' [' + Country_name + ']' AS Name FROM Unified_org_names";

    	// Execute it, or let it throw an error message if there's a problem.

    	$stmt = $dbh->query( $sql_o );

        $dropdown = "<select name='unified_org_names' hidden id='id_unified_org_names'>";

    	foreach ($stmt as $row) {

        $dropdown .= "\r\n<option value='{$row['Name']}'>{$row['Name']}</option>";

    	}

    	$dropdown .= "\r\n</select>";

    	echo $dropdown;

    }

?>

<h2>ÄNDRA REGEL CENTRA</h2>	
	                                    
                <form action="aendra_regel_resultat_c.php" method="post">

                <input type="submit" name="spara" value="Spara regel"/>&nbsp;&nbsp;
                <input type="submit" formaction="koer_regel_c.php" name="koer" value="Provkör regel"/>&nbsp;&nbsp;
                <a href='regel_centra.php'>TILL SÖKNING</a>&nbsp;&nbsp;
                <a href='adressmeny.php'>TILL MENYN</a>
                <br /><br /><br />

                <input type="text" value="Nuvarande utseende:" style="background-color:#7c9fc1;font-size:150%;color:black;border:0"  disabled/>&nbsp;&nbsp;
                <input type="text" value="Nytt utseende (förvalda):" style="background-color:#7c9fc1;font-size:150%;color:black;border:0"  disabled/>

                <br />

                <h3>SÖKFÄLT</h3>    
                
                Land #:</br>
                <input type="text" name="Land_ut" value="<?php echo $land_s; ?>" style="background-color:grey;color:white;border:0" />&nbsp;&nbsp;
                <input type="text" name="Land_ut_2" id="id_soek_land_s_2" value="<?php echo $land_s_2; ?>" style="background-color:green;color:white;border:0"  />&nbsp;&nbsp;
                <select id="id_s_land" name="Land_till" onchange="f_S_Land()">
                    <option>Ange land</option>
                </select>
                &nbsp;<input type="text" name="Soek_land_s" id="id_soek_land_s" onchange="f_populera_soek_Land_S()" hidden />

                <br />
			    Stad:</br> 
				<input type="text" name="Stad_ut" value="<?php echo $stad_s; ?>" style="background-color:grey;color:white;border:0" disabled />&nbsp;&nbsp;
				<input type="text" name="Stad_nu" value="<?php echo $stad_s; ?>" hidden />
				<input type="text" name="Stad_till" id="id_s_stad" value="<?php echo $stad_s; ?>" style="background-color:green;color:white;border:0" />
                <br />
			    Organisationsnamn #:</br> 
				<input type="text" name="Org_ut" value="<?php echo $org_s; ?>" style="background-color:grey;color:white;border:0" disabled />&nbsp;&nbsp; 
			    <input type="text" name="Org_nu" value="<?php echo $org_s; ?>" hidden /> 
				<input type="text" name="Org_till" id="id_s_org" value="<?php echo $org_s; ?>" style="background-color:green;color:white;border:0" />
                <br />
				
				<h3>ÄNDRINGSFÄLT</h3>  

			    Delas i #:</br>
				<input type="text" name="Delas_ut" value="<?php echo $delas; ?>" style="background-color:grey;color:white;border:0" disabled />&nbsp;&nbsp;
				<input type="text" name="Delas_nu" value="<?php echo $delas; ?>" hidden />
				<input type="text" name="Delas_ut_2" id="id_delas_h_2" value="<?php echo $delas_2; ?>" style="background-color:green;color:white;border:0" readonly="readonly" /> &nbsp;&nbsp;				  
                <select id="id_delas" name="Delas_till" onchange="f_H_Dela()">
                  <option value="Ange delningsfaktor">Ange delningsfaktor</option>                  
                  <option value="1">1</option>
                  <option value="2">2</option>
                  <option value="3">3</option>
                </select>
                <br /><br />
				
				<b>Organisation 1:</b><br />
				Annat organisationsnamn #:<br />
                <input type="text" name="Org_1_ut" value="<?php echo $org_h_1; ?>" style="background-color:grey;color:white;border:0" disabled />&nbsp;&nbsp;
                <input type="text" name="Org_1_ut_2" id="id_org_h_1_2" value="<?php echo $org_h_1_2; ?>" style="background-color:green;color:white;border:0"  />&nbsp;&nbsp;                
                <input type="text" name="Org_1_nu" id="id_soek_org_h_1x" value="<?php echo $org_h_1; ?>" hidden />
                <select id="id_h_org_1" name="Org_1_till" onchange="f_H_Org_1()">
                    <option>Ange organisation</option>
                </select>
                &nbsp;<input type="text" name="Soek_org_h_1" id="id_soek_org_h_1" onchange="f_populera_soek_Org_H_1()" />				                				
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
						
				<b>Organisation 2:</b><br />
				Annat organisationsnamn:<br />
                <input type="text" name="Org_2_ut" value="<?php echo $org_h_2; ?>" style="background-color:grey;color:white;border:0" disabled />&nbsp;&nbsp;
                <input type="text" name="Org_2_ut_2" id="id_org_h_2_2" value="<?php echo $org_h_2_2; ?>" style="background-color:green;color:white;border:0"  />&nbsp;&nbsp;                 
                <input type="text" name="Org_2_nu" id="id_soek_org_h_2x" value="<?php echo $org_h_2; ?>" hidden />
                <select id="id_h_org_2" name="Org_2_till" onchange="f_H_Org_2()">
                    <option>Ange organisation</option>
                </select>
                &nbsp;<input type="text" name="Soek_org_h_2" id="id_soek_org_h_2" onchange="f_populera_soek_Org_H_2()" />				                				                					
                <br />
			    Annat land:<br />
                <input type="text" name="Land_2_ut" value="<?php echo $land_h_2; ?>" style="background-color:grey;color:white;border:0" disabled />&nbsp;&nbsp;
                <input type="text" name="Land_2_ut_2" id="id_soek_land_h_2_2" value="<?php echo $land_h_2_2; ?>" style="background-color:green;color:white;border:0"  />&nbsp;&nbsp;                  
                <input type="text" name="Land_2_nu" id="id_soek_land_h_2x" value="<?php echo $land_h_2; ?>" hidden />
                <select id="id_h_land_2" name="Land_2_till" onchange="f_H_Land_2()">
                    <option>Ange land</option>
                </select>
                &nbsp;<input type="text" name="Soek_land_h_2" id="id_soek_land_h_2" onchange="f_populera_soek_Land_H_2()" hidden />				               					
				<br />
				Annan stad:<br /> 
				<input type="text" name="Stad_2_ut" value="<?php echo $stad_h_2; ?>" style="background-color:grey;color:white;border:0" disabled />&nbsp;&nbsp;
				<input type="text" name="Stad_2_nu" value="<?php echo $stad_h_2; ?>" hidden />
				<input type="text" name="Stad_2_till" id="h_id_stad_2" value="<?php echo $stad_h_2; ?>" style="background-color:green;color:white;border:0" />
                <br />
				<br />
				
				<b>Organisation 3:</b><br />
				Annat organisationsnamn:<br />
                <input type="text" name="Org_3_ut" value="<?php echo $org_h_3; ?>" style="background-color:grey;color:white;border:0" disabled />&nbsp;&nbsp;
                <input type="text" name="Org_3_ut_2" id="id_org_h_3_2" value="<?php echo $org_h_3_2; ?>" style="background-color:green;color:white;border:0"  />&nbsp;&nbsp;                 
                <input type="text" name="Org_3_nu" id="id_soek_org_h_3x" value="<?php echo $org_h_3; ?>" hidden />
                <select id="id_h_org_3" name="Org_3_till" onchange="f_H_Org_3()">
                    <option>Ange organisation</option>
                </select>
                &nbsp;<input type="text" name="Soek_org_h_3" id="id_soek_org_h_3" onchange="f_populera_soek_Org_H_3()" />				                				                					
                <br />
			    Annat land:<br />
                <input type="text" name="Land_3_ut" value="<?php echo $land_h_3; ?>" style="background-color:grey;color:white;border:0" disabled />&nbsp;&nbsp;
                <input type="text" name="Land_3_ut_2" id="id_soek_land_h_3_2" value="<?php echo $land_h_3_2; ?>" style="background-color:green;color:white;border:0" d />&nbsp;&nbsp;                 
                <input type="text" name="Land_3_nu" id="id_soek_land_h_3x" value="<?php echo $land_h_3; ?>" hidden />
                <select id="id_h_land_3" name="Land_3_till" onchange="f_H_Land_3()">
                    <option>Ange land</option>
                </select>
                &nbsp;<input type="text" name="Soek_land_h_3" id="id_soek_land_h_3" onchange="f_populera_soek_Land_H_3()" hidden />				
				<br />
				Annan stad:<br /> 
				<input type="text" name="Stad_3_ut" value="<?php echo $stad_h_3; ?>" style="background-color:grey;color:white;border:0" disabled />&nbsp;&nbsp;
				<input type="text" name="Stad_3_nu" value="<?php echo $stad_h_3; ?>" hidden />
				<input type="text" name="Stad_3_till" id="h_id_stad_3" value="<?php echo $stad_h_3; ?>" style="background-color:green;color:white;border:0" />
                <br />
				<br />				
				
		    </form>
								
	</body>
</html>