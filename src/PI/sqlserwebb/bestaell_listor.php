<?php session_start(); ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml11/DTD/xhtml-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<! Författare: Cecilia Wiklander>
<! Syfte: Adressrättnings-hantering>
<! Ändringar: >

<head>

    <meta charset="utf-8">

    <title>BESTÄLL LISTOR</title>

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
    $dbname = $_SESSION['dbnamn'];

    $dbh = new PDO("sqlsrv:Server=$hostname;Database=$dbname",$username,$password);

    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (isset($_POST['lista_1']) ||  isset($_POST['lista_2'])) {

       $List_typ = 0;

       if (isset($_POST['lista_1'])) {

          $Aemne = $_POST['Aemne'];

          if (strlen($Aemne) >= 5) {
             $Search_text = $Aemne;
             $List_typ = 1;        
          }  
 
       }
       else {

          $Titel = $_POST['Titel'];

          if (strlen($Titel) >= 10) {  
             $Search_text = $Titel;
             $List_typ = 2;
          } 
        
       }

       if ($List_typ > 0) {

          // Hämta beställningsnummer
          $sqlbestnr = "SELECT NEXT VALUE FOR dbo.ID_Seq AS Bestnr;";
          $stmt = $dbh->query( $sqlbestnr );
          foreach ($stmt as $row) {
             $Bestnr = $row['Bestnr'];        
          } 
             
          if ($List_typ == 1) {
             $Bestnr_1 = $Bestnr;
          }
          else {
             $Bestnr_2 = $Bestnr;
          }

          // Lägg in beställningspost
          $stmt = $dbh->prepare("INSERT INTO Order_List 
          (Order_id,Order_date,List_type,Search_text,Order_user) 
          VALUES (:Bestnr,GETDATE(),:List_typ,:Search_text,:Order_user);");
          $stmt->bindParam(':Bestnr', $Bestnr);     
          $stmt->bindParam(':Search_text', $Search_text);
          $stmt->bindParam(':List_typ', $List_typ);    
          $stmt->bindParam(':Order_user', $username);  
          $stmt->execute();

       }

    }

?>

<h2>BESTÄLL LISTOR</h2>

<form action="bestaell_listor.php" method="post">

<a href='adressmeny.php'>TILL MENYN</a>
<br /><br />

<h2>Publikationslista med utsökning på ämne</h2>
<h4>Söker i Abstract, KeyWordsPlus, Author_keyword, Document</h4>
Ange ämne:&nbsp;&nbsp;
<input type="text" name="Aemne" size="50" /><br /><br />
<input type="submit" name="lista_1" value="Beställ"/>&nbsp;&nbsp;
Din sökning har fått id:&nbsp;&nbsp;<input type="text" name="Soek_id" value="<?php echo $Bestnr_1; ?>" size="10" /><br />

<br /><br />
<h2>Publikationslista med utsökning på titel</h2>
Ange titel:&nbsp;&nbsp;
<input type="text" name="Titel" size="100" /><br /><br />
<input type="submit" name="lista_2" value="Beställ"/>&nbsp;&nbsp;
Din sökning har fått id:&nbsp;&nbsp;<input type="text" name="Soek_id" value="<?php echo $Bestnr_2; ?>" size="10" /><br />

</form>

</body>
</html>