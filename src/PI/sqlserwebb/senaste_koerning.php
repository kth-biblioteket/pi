<?php session_start(); ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml11/DTD/xhtml-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<! Författare: Cecilia Wiklander>
<! Syfte: Adressrättnings-hantering>
<! Ändringar: >

<head>

    <meta charset="utf-8">

    <title>SENASTE ADRESSRÄTTNING</title>

    <link href="Site_utan_storlek.css" rel="stylesheet">

<script>

</script>

</head>

<body>

<?php include('include_head_new.html'); ?>

<?php

    $username = $_SESSION['anv'];
    $password = $_SESSION['ord'];
    $hostname = $_SESSION['hnamn'];
    //$dbname = $_SESSION['dbnamn'];
    $dbname = "BIBMET";

    $dbh = new PDO("sqlsrv:Server=$hostname;Database=$dbname",$username,$password);

    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

	// Write out our query.

	$query = "SELECT Run_date FROM rule_center_rundate 
    WHERE Run_date = (SELECT MAX(Run_date) FROM rule_center_rundate)";

	// Execute it, or let it throw an error message if there's a problem.

	$stmt = $dbh->query( $query );

    foreach ($stmt as $row) {
        $imp_dat_c = $row['Run_date'];        
    }

	$query = "SELECT Run_date FROM rule_full_address_rundate 
    WHERE Run_date = (SELECT MAX(Run_date) FROM rule_full_address_rundate)";

	// Execute it, or let it throw an error message if there's a problem.

	$stmt = $dbh->query( $query );

    foreach ($stmt as $row) {
        $imp_dat_f_a = $row['Run_date'];        
    }

	$query = "SELECT Run_date FROM rule_org_rundate 
    WHERE Run_date = (SELECT MAX(Run_date) FROM rule_org_rundate)";

	// Execute it, or let it throw an error message if there's a problem.

	$stmt = $dbh->query( $query );

    foreach ($stmt as $row) {
        $imp_dat_o = $row['Run_date'];        
    }

	$query = "SELECT Run_date FROM rule_org_type_rundate 
    WHERE Run_date = (SELECT MAX(Run_date) FROM rule_org_type_rundate)";

	// Execute it, or let it throw an error message if there's a problem.

	$stmt = $dbh->query( $query );

    foreach ($stmt as $row) {
        $imp_dat_o_typ = $row['Run_date'];        
    }

?>

<h2>SENASTE ADRESSRÄTTNING</h2>

<br />

<form action="senaste_koerning.php" method="post">

<a href='adressmeny.php'>TILL MENYN</a>
<br /><br /><br />

Senaste adressrättning för organisation: <br />
<input type="text" name="Improve_date_o" size="10" value="<?php echo $imp_dat_o; ?>" disabled /> 

<br /><br />
Senaste adressrättning för centra: <br />
<input type="text" name="Improve_date_c" size="10" value="<?php echo $imp_dat_c; ?>" disabled /> 

<br /><br />
Senaste adressrättning för full adress: <br />
<input type="text" name="Improve_date_f_a" size="10" value="<?php echo $imp_dat_f_a; ?>" disabled/> 

<br /><br />
Senaste adressrättning för organisationstyp: <br />
<input type="text" name="Improve_date_o_typ" size="10" value="<?php echo $imp_dat_o_typ; ?>" disabled/> 

<br /><br />

<h3>Körningar av adressregler görs tre gånger i veckan: måndag, onsdag och fredag. Körningarna börjar klockan 18.</h3>

<h3>Måndag och onsdag: alla regler utom fulladressregler körs.</h3>

<h3>Fredag: alla regler körs.</h3> 

<h3>Om flera regler får träff på en forskaradress, så sker en prioritering utifrån hög splittringsfaktor, senaste regeldatum, högsta regelid.</h3>

</form>

</body>
</html>