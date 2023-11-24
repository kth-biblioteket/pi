<?php session_start(); ?>

<!DOCTYPE html PUBLIC "-//w3c//DTD XHTMLm 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<! Författare: Cecilia Wiklander>
<! Syfte: DiVA-hantering>
<! Ändringar: >

<html xmlns="http://www.w3.org/1999/xhtml">

<head>

<meta charset="utf-8">

<title>VAL AV FILTYP</title>

<link href="Site_utan_storlek.css" rel="stylesheet">

<?php include('include_diva.html'); ?>

</head>

<body>

<br />

<h2>&nbsp;&nbsp;&nbsp;&nbsp;VÄLJ FILTYP:</h2>

<ul>

        <li><h3><a href='d_behandlafil_man_m_l_25.php'>MANUELLT UTTAGEN FIL</a></h3></li>                    	

</ul>

<br /> 

<h3>SÅ HÄR GÖR DU:</h3>
* Lägg den fil du vill behandla i den uppkopplade mappen DATAFILER.<br />
* För att behandla en fil från WoS måste den döpas om till ISI.txt.<br />
* För att behandla en fil från Scopus måste den döpas om till Scopus.txt.<br />
* När programmet meddelar att filen är färdigbehandlad, skapas en utfil som kan importeras till DiVA.<br />
* Finns det poster med alltför många författare i WoS-filen, så skapas även en utfil där totala antalet författare och titel anges.<br />
* Om du anger ditt namn genom att välja handläggare från listan, så får utfilerna ditt namn som en del av filnamnet så att det syns att det är dina filer.<br />
* Alla dina filer kan du sedan lägga i mappen med ditt namn som finns i mappen DATAFILER.
<br /><br />
NY VERSION - valfritt filnamn, men måste ha ändelsen txt. Välj fil och ladda upp för bearbetning. Utfilerna skickas till angiven e-postadress.
<br /><br /><br /><br />
<a href='d_meny.php'>TILL HUVUDMENYN</a>

</body>
</html>