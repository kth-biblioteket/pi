<?php session_start(); ?>

<!DOCTYPE html PUBLIC "-//w3c//DTD XHTMLm 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<! Författare: Cecilia Wiklander>
<! Syfte: ISBN-hantering>
<! Ändringar: >

<html xmlns="http://www.w3.org/1999/xhtml">

<head>

<meta charset="utf-8">

<title>MENY</title>

<link href="Site_utan_storlek.css" rel="stylesheet">

<?php include('include_isbn.html'); ?>

</head>

<body>

<h2>MENY</h2>

<h3><a href='d_ladda_isbn.php'>LADDA IN NYA ISBN</a></h3>
<h3><a href='d_soek_isbn.php'>REGISTRERADE ISBN</a></h3>
<h3><a href='d_visa_statistik.php'>STATISTIK</a></h3>
<br /><br />
<a href='d_meny.php'>TILL HUVUDMENYN</a>

<br /> 

</body>
</html>