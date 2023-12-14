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

    function validateForm() {
        var x = document.forms["taBort"]["Antal"].value;
        if (x == 1) {
            alert("Organisationen kan inte tas bort, finns i regler!");
            return false;
        }
        var y = document.forms["taBort"]["Orsak"].value;
        if (y == "") {
            alert("Orsak måste anges!");
            return false;
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

    if (intval($u_org_id) > 0) {
 
        $_SESSION['u_org_id'] = $u_org_id;

        $username = $_SESSION['anv'];
        $password = $_SESSION['ord'];
        $hostname = $_SESSION['hnamn'];
        $dbname = $_SESSION['dbnamn'];

        $dbh = new PDO("sqlsrv:Server=$hostname;Database=$dbname",$username,$password);

        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Visa organisationen att ta ändra

        $sql = "SELECT Unified_org_id,Name_local,Name_en,Country_name,Org_type_code,Comment,User_id,Latest_date,ROR_id FROM unified_org_names WHERE Unified_org_id = " . $u_org_id; 

        $stmt = $dbh->query( $sql );

    	foreach ($stmt as $row) {
            $unified_org_id = $row['Unified_org_id'];
            $namn_lok = $row['Name_local'];
            $namn_eng = $row['Name_en'];
            $land = $row['Country_name'];
            $orgtyp = $row['Org_type_code'];
            $komm = $row['Comment']; 
            $user_id = $row['User_id'];
            $latest_date = $row['Latest_date'];
            $rorid = $row['ROR_id'];                                                  
    	}

        $_SESSION['unified_org_id'] = $unified_org_id;
        $_SESSION['namn_lok'] = $namn_lok;
        $_SESSION['namn_eng'] = $namn_eng;
        $_SESSION['land'] = $land;
        $_SESSION['orgtyp'] = $orgtyp;
        $_SESSION['komm'] = $komm;
        $_SESSION['user_id'] = $user_id;
        $_SESSION['latest_date'] = $latest_date;
        $_SESSION['rorid'] = $rorid;

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

        // Kontrollera om organisationsnamnet finns i rättningsregler
        $finns_regel = 0;
        $sql_kolla_o = "SELECT COUNT(*) AS Antal FROM rule_org_match WHERE Org_id_1 = ". $u_org_id ." OR Org_id_2 = ". 
        $u_org_id ." OR Org_id_3 = ". $u_org_id;
        $stmt = $dbh->query( $sql_kolla_o );
        foreach ($stmt as $row) {
            $antal = $row['Antal'];      
        } 
        if ($antal > 0) {
            $finns_regel = 1;
        }
        if (!$finns_regel) {
            $sql_kolla_f_a = "SELECT COUNT(*) AS Antal FROM rule_full_address_match WHERE Org_id_1 = ". $u_org_id ." OR Org_id_2 = ". 
            $u_org_id ." OR Org_id_3 = ". $u_org_id;
            $stmt = $dbh->query( $sql_kolla_f_a );
            foreach ($stmt as $row) {
                $antal = $row['Antal'];      
            } 
            if ($antal > 0) {
                $finns_regel = 1;
            }
        }
        if (!$finns_regel) {
            $sql_kolla_c = "SELECT COUNT(*) AS Antal FROM rule_center_match WHERE Org_id_1 = ". $u_org_id ." OR Org_id_2 = ". 
            $u_org_id ." OR Org_id_3 = ". $u_org_id;
            $stmt = $dbh->query( $sql_kolla_c );
            foreach ($stmt as $row) {
                $antal = $row['Antal'];      
            }
            if ($antal > 0) {
                $finns_regel = 1;
            }
        }

    }

?>

<h2>TA BORT ORGANISATIONSNAMN</h2>	
	                                    
		    <form action="ta_bort_organisation_resultat.php" onsubmit="return validateForm()" name="taBort" method="post">

                <input type="submit" name="spara" value="Radera organisation"/>&nbsp;&nbsp;
                <a href='organisationsnamn.php'>TILL SÖKNING</a>&nbsp;&nbsp;
                <a href='adressmeny.php'>TILL MENYN</a>
                <br />

                <br />  
                
                <input type="text" name="Antal" value="<?php echo $finns_regel; ?>" hidden />&nbsp;&nbsp;  
                <br />
                 
                ORSAK:</br> 
				<input type="text" name="orsak" maxlength="100">&nbsp;&nbsp; 
                <br />

                <br /><br />

			    Orgid:</br> 
				<input type="text" name="Orgid" value="<?php echo $u_org_id; ?>" disabled />&nbsp;&nbsp; 
                <br />

			    Lokalt namn:</br> 
				<input type="text" name="Namn_lok_ut" value="<?php echo $namn_lok; ?>" disabled />&nbsp;&nbsp; 
                <br />

			    Engelskt namn:</br> 
				<input type="text" name="Namn_eng_ut" value="<?php echo $namn_eng; ?>" disabled />&nbsp;&nbsp; 
                <br />

                Land:</br>
                <input type="text" name="Land_ut" value="<?php echo $land; ?>" disabled />&nbsp;&nbsp;
                <br />

                Organisationstyp:</br>
                <input type="text" name="Orgtyp_ut" value="<?php echo $org_typ_eng; ?>" disabled />&nbsp;&nbsp;
                <br />

			    Kommentar:</br> 
				<input type="text" name="Komm_ut" value="<?php echo $komm; ?>" disabled />&nbsp;&nbsp; 
                <br />

			    ROR-id:</br> 
				<input type="text" name="RORid" value="<?php echo $rorid; ?>" disabled />&nbsp;&nbsp; 
                <br />
				
		    </form>
								
	</body>

</html>