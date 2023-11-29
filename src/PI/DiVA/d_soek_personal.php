<?php 
    session_start(); 
    require_once('config.php.inc');
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml11/DTD/xhtml-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<! Författare: Cecilia Wiklander>
<! Syfte: Leta_KTH-anställda-hantering>
<! Ändringar: >

<head>

    <meta charset="utf-8">

    <title>LETA KTH-ANSTÄLLDA</title>

    <link href="Site_utan_storlek.css" rel="stylesheet">

</head>

<body>

<?php include('include_personal.html'); 

    $username = $_SESSION['anv'];
    $password = $_SESSION['ord'];
    $dbname = "hant_diva";
    
    $pdo = new PDO("mysql:host=$hostname;dbname=$dbname", $username, $password);

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 
    
    $sql = "SELECT max(Fil_datum) as Fil_datum FROM personal";
    
    $stmt = $pdo->query( $sql );           
    
    foreach ($stmt as $row) {
    	$max_datum_personal = $row['Fil_datum'];        
    } 

    $sql = "SELECT updat_datum FROM orcid_updatering";
    
    $stmt = $pdo->query( $sql );           
    
    foreach ($stmt as $row) {
    	$max_datum_orcid = $row['updat_datum'];        
    }     
    
?>

<br />

<form action="d_visa_personal.php" method="post">

<a href='d_meny.php'>TILL MENYN</a>

<br /><br /><br />

<input type="submit" name="soek" style="background-color:#0fb821" value="Sökning"/><br /><br />

<br />

<b>Ange</b>
<br /><br />

Hela namnet: <br />
<input type="text" name="Namn" size="50" />&nbsp;&nbsp;<i><b>Format: Efternamn, Förnamn</b></i>
<br /><br /><br />

<b>eller</b> 
<br /><br />

<i>Förnamn och/eller efternamn går att söka på</i><br /><br />

Förnamn: <br />
<input type="text" name="Fnamn" size="50" />&nbsp;&nbsp;<i><b>Ange * före eller (och) efter för sökning på del av namn</b></i>
<br /><br />

Efternamn: <br />
<input type="text" name="Enamn" size="50" />&nbsp;&nbsp;<i><b>Ange * före eller (och) efter för sökning på del av namn</b></i>

<br /><br /><br />

<b>eller</b> 
<br /><br />

KTH-id: <br />
<input type="text" name="KTH_id" size="8" />
<br /><br /><br />

<b>Senaste uppdatering personaldata:</B>
<input type="text" name="Maxdatum" value="<?php echo $max_datum_personal; ?>" disabled /></br>
<b>Senaste uppdatering ORCID-data:&nbsp;&nbsp;</b>
<input type="text" name="Maxdatum" value="<?php echo $max_datum_orcid; ?>" disabled />

</form>

</body>
</html>