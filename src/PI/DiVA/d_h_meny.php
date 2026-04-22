<?php 
    session_start(); 
    require_once('config.php.inc');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml11/DTD/xhtml-transitional.dtd">

<! Författare: Cecilia Wiklander>
<! Syfte: DiVA-hantering>
<! Ändringar: >

<head>

    <meta charset="utf-8">

    <title>MENY</title>

    <link href="Site_utan_storlek.css" rel="stylesheet">

    <?php include('include_meny.html'); ?>

</head>

<body>

<?php

    $_SESSION['granskad'] = "";

        try {
            
	    $username = $db_user;
            $password = $db_pass;
            $dbname = $diva_dbname;
            $dbh = new PDO("mysql:host=$hostname;dbname=$dbname", $username, $password);
            $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $_SESSION['anv'] = $username;
            $_SESSION['ord'] = $password;
            $_SESSION['hnamn'] = $hostname;
            $_SESSION['dbnamn'] = $dbname;
            $_SESSION['granskad'] = "KONTROLL";		

        } catch (PDOException $e){
            echo '<script language="javascript">';
            echo 'alert("Det gick inta att ansluta till databasen!")';
            echo '</script>';
        }   

?>

<br/>

<br /><br />

<ul>


	<li><h3><a href='d_importmeny.php'>BEHANDLA IMPORTFIL</a></h3></li>		


</ul>	

</body>

</html>
