<?php session_start(); ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml11/DTD/xhtml-transitional.dtd">

<! Författare: Cecilia Wiklander>
<! Syfte: ISBN-hantering>
<! Ändringar: >

<html xmlns="http://www.w3.org/1999/xhtml">

<head>

    <meta charset="utf-8">

    <title>SÖK UPPGIFTER OM REGISTRERADE ISBN</title>

    <link href="Site_utan_storlek.css" rel="stylesheet">

</head>

<body>

<?php include('include_isbn.html'); ?>

<?php

?>

<h2>SÖK UPPGIFTER OM REGISTRERADE ISBN</h2>

<br />

<form action="d_visa_reg_isbn.php" method="post">
<input type="submit" name="soek" style="background-color:#0fb821" value="Sök"/><br /><br />

<br />
<label><Input type = 'Radio' Name ='Period' value= 'idag' checked>Idag</label>
<label><Input type = 'Radio' Name ='Period' value= 'igaar'>Igår</label>
<label><Input type = 'Radio' Name ='Period' value= 'senastevecka'>Senaste vecka</label>
<label><Input type = 'Radio' Name ='Period' value= 'senastemaanad'>Senaste månad</label>
<label><Input type = 'Radio' Name ='Period' value= 'tremaanad'>De senaste tre månaderna</label>
<label><Input type = 'Radio' Name ='Period' value= 'datumintervall'>Datumintervall</label>

<br /><br /><br />

Titel: <br />
<input type="text" name="Titel" size="100" /><br /><br /><br />
Forskarens <br /><br />
förnamn:&nbsp;&nbsp;&nbsp;&nbsp;<input type="text" name="Fnamn" size="20" /><br /><br />
efternamn:&nbsp;<input type="text" name="Enamn" size="30" /><br /><br /><br />

ISBN:<br />
<input type="text" name="ISBN" size="20" /> Ingen tidsperiod behöver anges
<br /><br /><br />

KTH-id:<br />
<input type="text" name="KTH-id" size="8" /> Ingen tidsperiod behöver anges
<br /><br /><br />

Datumintervall:<br />
<input type="text" name="Datum_F" size="10" />
-
<input type="text" name="Datum_T" size="10" />
&nbsp;&nbsp;Datum anges som ÅÅÅÅ-MM-DD
<br /><br />

<br />

<a href='d_isbn_meny.php'>TILL MENYN</a>

</form>

</body>
</html>