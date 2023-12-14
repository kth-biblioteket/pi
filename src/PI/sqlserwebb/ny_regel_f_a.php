<?php session_start(); ?>

<!DOCTYPE html PUBLIC "-//w3c//DTD XHTMLm 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<! Författare: Cecilia Wiklander>
<! Syfte: Adressrättnings-hantering>
<! Ändringar: >

<head>

    <meta charset="utf-8">

    <title>NY REGEL FULL ADRESS</title>
	
    <link href="Site.css" rel="stylesheet"> 

<script type="text/javascript">

    function f_populera_Land() {
        // Populera
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
        var v_text = document.getElementById("id_soek_org_h_1").value;
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
        var v_text = document.getElementById("id_soek_org_h_2").value;
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
        var v_text = document.getElementById("id_soek_org_h_3").value;
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

    function f_Ladda_sida() {
        f_populera_Land();
        f_populera_Org();
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
        $land_s = $_POST['Land'];
        $stad_s = $_POST['Stad'];
        $str_1 = $_POST['Str_1'];
        $str_2 = $_POST['Str_2'];
        $str_3 = $_POST['Str_3'];
        $str_ej_1 = $_POST['Str_ej_1'];
        $str_ej_2 = $_POST['Str_ej_2'];
        $delas = $_POST['Delas'];
        $land_1 = $_POST['Land_1'];
        $land_2 = $_POST['Land_2'];
        $land_3 = $_POST['Land_3'];
        $stad_1 = $_POST['Stad_1'];
        $stad_2 = $_POST['Stad_2'];
        $stad_3 = $_POST['Stad_3'];
        $org_1 = $_POST['Org_1'];
        $org_2 = $_POST['Org_2'];
        $org_3 = $_POST['Org_3'];

	$Sk = "'";
	$Ers = "''";

	$stad_s = str_replace($Sk, $Ers, $stad_s);
	$str_1 = str_replace($Sk, $Ers, $str_1);
	$str_2 = str_replace($Sk, $Ers, $str_2);
	$str_3 = str_replace($Sk, $Ers, $str_3);
	$str_ej_1 = str_replace($Sk, $Ers, $str_ej_1);
	$str_ej_2 = str_replace($Sk, $Ers, $str_ej_2);
	$stad_1 = str_replace($Sk, $Ers, $stad_1);
	$stad_2 = str_replace($Sk, $Ers, $stad_2);
	$stad_3 = str_replace($Sk, $Ers, $stad_3);
        $org_1 = str_replace($Sk, $Ers, $org_1);
        $org_2 = str_replace($Sk, $Ers, $org_2);
        $org_3 = str_replace($Sk, $Ers, $org_3);
        $land_s = str_replace($Sk, $Ers, $land_s);
        $land_1 = str_replace($Sk, $Ers, $land_1);
        $land_2 = str_replace($Sk, $Ers, $land_2);
        $land_3 = str_replace($Sk, $Ers, $land_3);

        $koll_svar = false;

        if ($land_s == 'Ange land') {
            echo "<script>alert('Land måste anges som sökfält!');</script>";
        }
        else {
            if (strlen($str_1) == 0){
                echo "<script>alert('Söksträng 1 måste anges som sökfält!');</script>";
            }
            else {
                if ($org_1 == 'Ange organisation'){
                    echo "<script>alert('Organisation 1 måste anges som ändringsfält!');</script>";
                }
                else {
                    if ($delas == 1) {
                        if ($org_2 != 'Ange organisation' || $org_3 != 'Ange organisation') {
                            echo "<script>alert('Antalet i Delas stämmer inte med antal angivna organisationer!');</script>";
                        }
                        else {
                            $koll_svar = true;
                        }
                    }
                    else if ($delas == 2) {
                        if ($org_2 == 'Ange organisation' || $org_3 != 'Ange organisation'){
                            echo "<script>alert('Antalet i Delas stämmer inte med antal angivna organisationer!');</script>";
                        }  
                        else {
                            $koll_svar = true;                                           
                        }
                    }
                    else if ($delas == 3) {
                        if ($org_2 == 'Ange organisation' || $org_3 == 'Ange organisation'){
                            echo "<script>alert('Antalet i Delas stämmer inte med antal angivna organisationer!');</script>";
                        }  
                        else {
                            $koll_svar = true;
                        }
                    }
                    else {
                        echo "<script>alert('Antalet i Delas kan vara mellan 1 och 3!');</script>";
                    }
                }
            }
        }

        $n_regel_f_a = $_SESSION['n_regel_f_a'];

        if ($koll_svar && $n_regel_f_a <> $str_1) {

            $pos_f = strpos($org_1, '[' );
            $pos_e = strpos($org_1, ']' );
            $org_1_o = substr($org_1, 0, $pos_f - 1);
            $org_1_c = substr($org_1, $pos_f + 1, $pos_e - $pos_f - 1);            
          
            if (strlen($org_1_c) > 0) {
            	$sql_org_1 = "SELECT Unified_org_id FROM Unified_org_names WHERE Name_en = '" . $org_1_o . "' AND Country_name = '" . $org_1_c . "'";
            } 
            else {
            	$sql_org_1 = "SELECT Unified_org_id FROM Unified_org_names WHERE Name_en = '" . $org_1_o . "'";
            }

	        $stmt = $dbh->query( $sql_org_1 );
	        foreach ($stmt as $row) {
                $org_id_1 = $row['Unified_org_id'];      
	        }
            if ($land_1 == 'Ange land') {
                $land_1 = NULL;
            }
            if ($delas > 1) {
                if ($org_2 != 'Ange organisation') {

                   $pos_f = strpos($org_2, '[' );
                   $pos_e = strpos($org_2, ']' );
                   $org_2_o = substr($org_2, 0, $pos_f - 1);
                   $org_2_c = substr($org_2, $pos_f + 1, $pos_e - $pos_f - 1);            
          
                   if (strlen($org_2_c) > 0) {
            	      $sql_org_2 = "SELECT Unified_org_id FROM Unified_org_names WHERE Name_en = '" . $org_2_o . "' AND Country_name = '" . $org_2_c . "'";
                   } 
                   else {
            	      $sql_org_2 = "SELECT Unified_org_id FROM Unified_org_names WHERE Name_en = '" . $org_2_o . "'";
                   }

                    $stmt = $dbh->query( $sql_org_2 );
                    foreach ($stmt as $row) {
                        $org_id_2 = $row['Unified_org_id'];      
                    }
                }
                else {
                    $org_id_2 = NULL;
                }
                if ($land_2 == 'Ange land') {
                    $land_2 = NULL;
                }
            }
            if ($delas > 2) {
                if ($org_3 != 'Ange organisation') {

                   $pos_f = strpos($org_3, '[' );
                   $pos_e = strpos($org_3, ']' );
                   $org_3_o = substr($org_3, 0, $pos_f - 1);
                   $org_3_c = substr($org_3, $pos_f + 1, $pos_e - $pos_f - 1);            
          
                   if (strlen($org_3_c) > 0) {
            	      $sql_org_3 = "SELECT Unified_org_id FROM Unified_org_names WHERE Name_en = '" . $org_3_o . "' AND Country_name = '" . $org_3_c . "'";
                   } 
                   else {
            	      $sql_org_3 = "SELECT Unified_org_id FROM Unified_org_names WHERE Name_en = '" . $org_3_o . "'";
                   }

                    $stmt = $dbh->query( $sql_org_3 );
                    foreach ($stmt as $row) {
                        $org_id_3 = $row['Unified_org_id'];      
                    }
                }
                else {
                    $org_id_3 = NULL;
                }
                if ($land_3 == 'Ange land') {
                    $land_3 = NULL;
                }
            }

            $sql_country = "SELECT Country_code FROM Country WHERE Display_name = '" . $land_s . "'";
	        $stmt = $dbh->query( $sql_country );
	        foreach ($stmt as $row) {
                $country_code = $row['Country_code'];      
	        } 

            if (strlen($stad_s) == 0) {
               $sql_stad_s = "";
               $sql_v_stad_s = "";
            }
            else {
               $sql_stad_s = "Find_city,";
               $sql_v_stad_s = $stad_s . "','";
            }

            if (strlen($str_2) == 0) {
               $sql_str_2 = "";
               $sql_v_str_2 = "";
            }
            else {
               $sql_str_2 = "Find_str_2,";
               $sql_v_str_2 = "'" . $str_2 . "',";
            }

            if (strlen($str_3) == 0) {
               $sql_str_3 = "";
               $sql_v_str_3 = "";
            }
            else {
               $sql_str_3 = "Find_str_3,";
               $sql_v_str_3 = "'" . $str_3 . "',";
            }

            if (strlen($str_ej_1) == 0) {
               $sql_str_ej_1 = "";
               $sql_v_str_ej_1 = "";
            }
            else {
               $sql_str_ej_1 = "Find_str_not_1,";
               $sql_v_str_ej_1 = "'" . $str_ej_1 . "',";
            }

            if (strlen($str_ej_2) == 0) {
               $sql_str_ej_2 = "";
               $sql_v_str_ej_2 = "";
            }
            else {
               $sql_str_ej_2 = "Find_str_not_2,";
               $sql_v_str_ej_2 = "'" . $str_ej_2 . "',";
            }

            if ($delas == 1) {
                $sql_i = "INSERT INTO Rule_full_address_match (Find_country,Country_code,"
                . $sql_stad_s
                . "Find_str_1,"
                . $sql_str_2 . $sql_str_3 . $sql_str_ej_1 . $sql_str_ej_2 . 
                "Divide,
                Country_1,City_1,Org_id_1,User_id,Rule_date,Run_status) VALUES
                ('" . $land_s . "','" . $country_code . "','" 
                . $sql_v_stad_s 
                . $str_1 . "'," . $sql_v_str_2 . $sql_v_str_3 . $sql_v_str_ej_1 . $sql_v_str_ej_2 . $delas . 
                ",'" . $land_1 . "','" . $stad_1 . "'," . $org_id_1 . ",'" . $username . "',GETDATE(),1)";
            }
            else if ($delas == 2) {
                $sql_i = "INSERT INTO Rule_full_address_match (Find_country,Country_code,"
                . $sql_stad_s 
                . "Find_str_1," 
                . $sql_str_2 . $sql_str_3 . $sql_str_ej_1 . $sql_str_ej_2 . 
                "Divide,
                Country_1,City_1,Org_id_1,Country_2,City_2,Org_id_2,User_id,Rule_date,Run_status) VALUES
                ('" . $land_s . "','" . $country_code . "','" 
                . $sql_v_stad_s
                . $str_1 . "'," . $sql_v_str_2 . $sql_v_str_3 . $sql_v_str_ej_1 . $sql_v_str_ej_2 
                . $delas . 
                ",'" . $land_1 . "','" . $stad_1 . "'," . $org_id_1 . ",'" . $land_2 . "','" . $stad_2 . 
                "'," . $org_id_2 . ",'" . $username . "',GETDATE(),1)";        
            }
            else {
                $sql_i = "INSERT INTO Rule_full_address_match (Find_country,Country_code,"
                . $sql_stad_s
                . "Find_str_1," 
                . $sql_str_2 . $sql_str_3 . $sql_str_ej_1 . $sql_str_ej_2 . 
                "Divide,
                Country_1,City_1,Org_id_1,Country_2,City_2,Org_id_2,Country_3,City_3,Org_id_3,
                User_id,Rule_date,Run_status) VALUES
                ('" . $land_s . "','" . $country_code . "','" 
                . $sql_v_stad_s
                . $str_1 . "'," . $sql_v_str_2 . $sql_v_str_3 . $sql_v_str_ej_1 . $sql_v_str_ej_2 . $delas . 
                ",'" . $land_1 . "','" . $stad_1 . "'," . $org_id_1 . ",'" . $land_2 . "','" . $stad_2 . 
                "'," . $org_id_2 . ",'" . $land_3 . "','" . $stad_3 . "'," . $org_id_3 . ",'" . 
                $username . "',GETDATE(),1)";        
            }
            
	        $stmt = $dbh->query( $sql_i );

            if ($count = $stmt->rowCount() > 0) {
                echo '<script language="javascript">';
                echo 'alert("Regeln är sparad!")';
                echo '</script>'; 
                $_SESSION['n_regel_f_a'] = $str_1;           
            }
            else {
                echo '<script language="javascript">';
                echo 'alert("Fel vid sparande av regeln!")';
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

?>

<h2>NY REGEL FULL ADRESS</h2>	
	                                    
		    <form action="ny_regel_f_a.php" method="post">

                <input type="submit" name="spara" value="Spara regel"/>&nbsp;&nbsp;
                <a href='regel_full_adress.php'>TILL SÖKNING</a>&nbsp;&nbsp;
                <a href='adressmeny.php'>TILL MENYN</a>
                <br /><br /><br />

                <h3>SÖKFÄLT</h3>    
                
                Land #:</br>
                <select id="id_s_land" name="Land">
                    <option>Ange land</option>
                </select>
                &nbsp;<input type="text" name="Soek_land_s" id="id_soek_land_s" onchange="f_populera_soek_Land_S()" />
                <br />
			    Stad:</br> 
				<input type="text" name="Stad" id="id_s_stad" />
                <br />
			    Söksträng 1 #:</br>  
				<input type="text" name="Str_1" id="id_s_1" />
                <br />
			    Söksträng 2:</br>  
				<input type="text" name="Str_2" id="id_s_2" />
                <br />
			    Söksträng 3:</br> 
				<input type="text" name="Str_3" id="id_s_3" />
                <br />
			    Söksträng ej 1:</br>   
				<input type="text" name="Str_ej_1" id="id_s_ej_1" />
                <br />
			    Söksträng ej 2:</br>   
				<input type="text" name="Str_ej_2" id="id_s_ej_2" />
                <br />
				
				<h3>ÄNDRINGSFÄLT</h3>

			    Delas i #:</br>
                <select id="id_delas" name="Delas">
                  <option value=""></option>
                  <option value="1">1</option>
                  <option value="2">2</option>
                  <option value="3">3</option>
                </select>
                <br /><br />
				
				<b>Organisation 1 #:</b><br />
				Annat organisationsnamn:<br />
                <select id="id_h_org_1" name="Org_1">
                    <option>Ange organisation</option>
                </select>
                &nbsp;<input type="text" name="Soek_org_h_1" id="id_soek_org_h_1" onchange="f_populera_soek_Org_H_1()" />				                				
                <br />
			    Annat land:<br />
                <select id="id_h_land_1" name="Land_1">
                    <option>Ange land</option>
                </select>
                &nbsp;<input type="text" name="Soek_land_h_1" id="id_soek_land_h_1" onchange="f_populera_soek_Land_H_1()" />				
                <br />                								
				Annan stad:<br /> 
				<input type="text" name="Stad_1" id="h_id_stad_1" /><br />
				<br />
						
				<b>Organisation 2:</b><br />
				Annat organisationsnamn:<br />
                <select id="id_h_org_2" name="Org_2">
                    <option>Ange organisation</option>
                </select>
                &nbsp;<input type="text" name="Soek_org_h_2" id="id_soek_org_h_2" onchange="f_populera_soek_Org_H_2()" />				                				                					
                <br />
			    Annat land:<br />
                <select id="id_h_land_2" name="Land_2">
                    <option>Ange land</option>
                </select>
                &nbsp;<input type="text" name="Soek_land_h_2" id="id_soek_land_h_2" onchange="f_populera_soek_Land_H_2()" />				               					
				<br />
				Annan stad:<br /> 
				<input type="text" name="Stad_2" id="h_id_stad_2" /><br />
				<br />
				
				<b>Organisation 3:</b><br />
				Annat organisationsnamn:<br />
                <select id="id_h_org_3" name="Org_3">
                    <option>Ange organisation</option>
                </select>
                &nbsp;<input type="text" name="Soek_org_h_3" id="id_soek_org_h_3" onchange="f_populera_soek_Org_H_3()" />				                				                					
                <br />
			    Annat land:<br />
                <select id="id_h_land_3" name="Land_3">
                    <option>Ange land</option>
                </select>
                &nbsp;<input type="text" name="Soek_land_h_3" id="id_soek_land_h_3" onchange="f_populera_soek_Land_H_3()" />				
				<br />
				Annan stad:<br /> 
				<input type="text" name="Stad_3" id="h_id_stad_3" /><br />
				<br />				
				
		    </form>
								
	</body>
</html>