<?php session_start(); ?>

<!DOCTYPE html PUBLIC "-//w3c//DTD XHTMLm 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<! Författare: Cecilia Wiklander>
<! Syfte: Adressrättnings-hantering>
<! Ändringar: >

<head>

    <meta charset="utf-8">

    <title>NY REGEL ORGANISATIONSTYP</title>
	
    <link href="Site.css" rel="stylesheet"> 
		
<script type="text/javascript">

    function f_populera_Land() {
        f_populera_Orgtyp();
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

if (isset($_POST['spara'])) {
        $land_s = $_POST['Land'];
        $stad_s = $_POST['Stad'];
        $org_s_1 = $_POST['Org_1'];
        $org_s_2 = $_POST['Org_2'];
        $org_s_ej = $_POST['Org_ej'];
        $land_1 = $_POST['Land_1'];
        $stad_1 = $_POST['Stad_1'];
        $orgtyp = $_POST['Orgtyp'];

	$Sk = "'";
	$Ers = "''";

	$stad_s = str_replace($Sk, $Ers, $stad_s);
	$org_s_1 = str_replace($Sk, $Ers, $org_s_1);
	$org_s_2 = str_replace($Sk, $Ers, $org_s_2);
	$org_s_ej = str_replace($Sk, $Ers, $org_s_ej);
	$stad_1 = str_replace($Sk, $Ers, $stad_1);
	$land_s = str_replace($Sk, $Ers, $land_s);
	$land_1 = str_replace($Sk, $Ers, $land_1);

    $koll_svar = false;
        
    if (strlen($org_s_1) == 0) {
        echo "<script>alert('Organisation, sträng 1 måste anges som sökfält!');</script>";
    }
    else {
         if ($orgtyp == 'Ange organisationstyp') {
             echo "<script>alert('Organisationstyp måste anges som ändringsfält!');</script>";                    
         }
         else {
             $koll_svar = true;
         }                
    }
       
    $n_regel_o_typ = $_SESSION['n_regel_o_typ'];

    if ($koll_svar && $n_regel_o_typ <> $org_s_1) {

       if ($land_s == 'Ange land') {
          $land_s = NULL;
       }
       else {
            $sql_country = "SELECT Country_code FROM Country WHERE Display_name = '" . $land_s . "'";
	        $stmt = $dbh->query( $sql_country );
	        foreach ($stmt as $row) {
                $country_code = $row['Country_code'];      
	        }            
       }
    
       $sql_o = "SELECT Org_type_code FROM organization_type WHERE Org_type_eng = '" . $orgtyp . "'";
       $stmt = $dbh->query( $sql_o );
       foreach ($stmt as $row) {
          $org_type_code = $row['Org_type_code']; 
       }

       $foere = 0;
       // Land_s
       if (strlen($land_s) == 0) {
          $sql_land_s = "";
          $sql_v_land_s = NULL;
       }
       else {
          $sql_land_s = "Find_country,";
          $sql_v_land_s = "'" . $land_s . "','";
          $foere = 1;
       }
       // Stad_s
       if (strlen($stad_s) == 0) {
          $sql_stad_s = "";
          $sql_v_stad_s = NULL;
       }
       else {
          $sql_stad_s = "Find_city,";
          $sql_v_stad_s = $stad_s . "','";
          $foere = 1;
       }
       // Country_code
       if (strlen($country_code) == 0) {
          $sql_country_code = "";
          $sql_v_country_code = NULL;
       }
       else {
          $sql_country_code = "Country_code,";
          $sql_v_country_code = $country_code . "','";
       }
       // Org_s_2
       if (strlen($org_s_2) == 0) {
          $sql_org_s_2 = "";
          $sql_v_org_s_2 = NULL;
       }
       else {
          $sql_org_s_2 = "Find_org_2,";
          $sql_v_org_s_2 = $org_s_2 . "','";
       }
      // Org_s_ej
      if (strlen($org_s_ej) == 0) {
          $sql_org_s_ej = "";
          $sql_v_org_s_ej = NULL;
       }
       else {
          $sql_org_s_ej = "Find_org_not,";
          $sql_v_org_s_ej = $org_s_ej . "','";
       }
      // Land_1
      if ($land_1 == 'Ange land') {
          $sql_land_1 = "";
          $sql_v_land_1 = NULL;
       }
       else {
          $sql_land_1 = "Country,";
          $sql_v_land_1 = $land_1 . "','";
       }
      // Stad_1
      if (strlen($stad_1) == 0) {
          $sql_stad_1 = "";
          $sql_v_stad_1 = NULL;
       }
       else {
          $sql_stad_1 = "City,";
          $sql_v_stad_1 = $stad_1 . "','";
       }

       if ($foere == 0) {
       	  $sql_i = "INSERT INTO Rule_org_type_match (" . $sql_land_s . $sql_country_code  
          . $sql_stad_s
          . "Find_org_1," . $sql_org_s_2 . $sql_org_s_ej . $sql_land_1 . $sql_stad_1 . "Org_type_code,User_id,Rule_date,Run_status) VALUES (" . $sql_v_land_s .  $sql_v_country_code
          . $sql_v_stad_s 
          . "'" . $org_s_1 . "','" . $sql_v_org_s_2 . $sql_v_org_s_ej . $sql_v_land_1 . $sql_v_stad_1 . $org_type_code . "','" . $username . "',GETDATE(),1)";  
       }
       else {
       	  $sql_i = "INSERT INTO Rule_org_type_match (" . $sql_land_s . $sql_country_code  
          . $sql_stad_s
          . "Find_org_1," . $sql_org_s_2 . $sql_org_s_ej . $sql_land_1 . $sql_stad_1 . "Org_type_code,User_id,Rule_date,Run_status) VALUES (" . $sql_v_land_s .  $sql_v_country_code
          . $sql_v_stad_s 
          . $org_s_1 . "','" . $sql_v_org_s_2 . $sql_v_org_s_ej . $sql_v_land_1 . $sql_v_stad_1 . $org_type_code . "','" . $username . "',GETDATE(),1)";  
       }

       $stmt = $dbh->query( $sql_i );

       if ($count = $stmt->rowCount() > 0) {
          echo '<script language="javascript">';
          echo 'alert("Regeln är sparad!")';
          echo '</script>';  
          $_SESSION['n_regel_o_typ'] = $org_s_1;          
       }
       else {
          echo '<script language="javascript">';
          echo 'alert("Fel vid sparande av regeln!")';
          echo '</script>';            
       }

       // Blanka sparad regels textfält
       $stad_s = "";
       $org_s_1 = "";
       $org_s_2 = "";
       $org_s_ej = "";
       $stad_1 = "";

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

<h2>NY REGEL ORGANISATIONSTYP</h2>	
	                                    
		    <form action="ny_regel_o_typ.php" method="post">

                <input type="submit" name="spara" value="Spara regel"/>&nbsp;&nbsp;
                <a href='regel_organisation_typ.php'>TILL SÖKNING</a>&nbsp;&nbsp;
                <a href='adressmeny.php'>TILL MENYN</a>
                <br /><br />

                <h3>SÖKFÄLT</h3>    

                Land:</br>
                <select id="id_s_land" name="Land">
                    <option>Ange land</option>
                </select>
                &nbsp;<input type="text" name="Soek_land_s" id="id_soek_land_s" onchange="f_populera_soek_Land_S()" value="<?php echo $land_s; ?>"/>
                <br />
			    Stad:</br> 
				<input type="text" name="Stad" id="id_s_stad" value="<?php echo $stad_s; ?>" /><br />
			    Organisation, sträng 1 #:</br> 
				<input type="text" name="Org_1" id="id_s_org_1" value="<?php echo $org_s_1; ?>" /><br />
			    Organisation, sträng 2:</br> 
				<input type="text" name="Org_2" id="id_s_org_2" value="<?php echo $org_s_2; ?>" /><br />
			    Organisation, sträng ej:</br> 
				<input type="text" name="Org_ej" id="id_s_org_ej" value="<?php echo $org_s_ej; ?>" /><br />
				
				<h3>ÄNDRINGSFÄLT</h3>

                Organisationstyp #:</br>
                <select id="id_s_orgtyp" name="Orgtyp">
                    <option>Ange organisationstyp</option>
                </select>
                <br />
			    Annat land:<br />
                <select id="id_h_land_1" name="Land_1">
                    <option>Ange land</option>
                </select>
                &nbsp;<input type="text" name="Soek_land_h_1" id="id_soek_land_h_1" onchange="f_populera_soek_Land_H_1()" />				
                <br />				
				Annan stad:<br /> 
				<input type="text" name="Stad_1" id="h_id_stad_1" value="<?php echo $stad_1; ?>" /><br />
				<br />	
				
		    </form>

<p>De fält som har en # efter är obligatoriska.</p>
								
	</body>
</html>