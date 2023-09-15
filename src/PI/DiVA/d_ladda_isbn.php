<?php 
    session_start(); 
    require_once('config.php.inc');
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml11/DTD/xhtml-transitional.dtd">

<! Författare: Cecilia Wiklander>
<! Syfte: ISBN-hantering>
<! Ändringar: >

<html xmlns="http://www.w3.org/1999/xhtml">

<head>

    <meta charset="utf-8">

    <title>LADDA IN NYA ISBN</title>

    <link href="Site_utan_storlek.css" rel="stylesheet">

</head>

<body>

<?php include('include_isbn.html'); ?>

<?php

    $username = $_SESSION['anv'];
    $password = $_SESSION['ord'];
    $dbname = "hant_isbn";
    
if (isset($_POST['ladda'])) { 

    try {
        $pdo = new PDO("mysql:host=$hostname;dbname=$dbname", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $sql = "SELECT MAX(ISBN) AS ISBN FROM oanv_isbn";
        $stmt = $pdo->query( $sql );
        foreach ($stmt as $row) {
                $max_isbn = $row['ISBN'];        
        }
    } 
    catch (PDOException $e) {
          echo '<script language="javascript">';
          echo 'alert("Fel vid inloggning till databasen!")';
          echo '</script>';
    }

    $fileName = 'DATAFILER\KB_fil.txt';
    if ( file_exists($fileName) && ($fp = fopen($fileName, "r"))!==false ) {
        $antal = 0;
        $finns_ej = TRUE;
        $fungerar = TRUE;
        while(!feof($fp) && $finns_ej && $fungerar) {
            $isbn = fgets($fp);
            if (strlen(trim($isbn)) > 0) {
                $antal = $antal + 1;                
                
                if ($antal == 1 && $max_isbn >= trim($isbn)) {
                    $finns_ej = FALSE;
                    echo '<script language="javascript">';
                    echo 'alert("Filen med ISBN-nummer är redan inladdad!")';
                    echo '</script>';  
                    $antal = 0;              
                }  
                else {                
                    try {
                        if ($max_isbn < trim($isbn)) {
                            $sql_i = "INSERT INTO oanv_isbn (ISBN,Importdatum) 
                            VALUES ('" . trim($isbn) . "',CURDATE())"; 
                        }  
                        else {
                            echo 'Fel vid laddning - ISBN-nummer tidigare än redan inladdade!';
                            $finns_ej = FALSE;
                        }        
                        $stmt = $pdo->query( $sql_i );
                    }
                    catch (PDOException $e) {
                        echo '<script language="javascript">';
                        echo 'alert("Fel vid laddning av ISBN-nummer-filen!")';
                        echo '</script>'; 
                        $fungerar = FALSE;                    
                    }  
                                 
                }

            }                                                 
                   
        }

        fclose($fp);

    }
    else {
         echo '<script language="javascript">';
         echo 'alert("Hittar ej ISBN-nummer-filen!")';
         echo '</script>';        
    }   
       
    if ($antal > 0 && $fungerar) {   
        echo '<script language="javascript">';
        echo 'alert("ISBN-nummer-filen är inladdad!")';
        echo '</script>';            
    }      
    
}

if (isset($_POST['min_nivaa'])) { 

    $MinNivISBN = $_POST['MinNivISBN'];
    
    if ($MinNivISBN >= 50) {
        try {
            $pdo = new PDO("mysql:host=$hostname;dbname=$dbname", $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $stmt = $pdo->prepare("INSERT INTO min_niv_isbn (Antal,Regdatum) VALUES (:MinNivISBN,CURRENT_TIMESTAMP())");
            $stmt->bindParam(':MinNivISBN', $MinNivISBN);
            $stmt->execute();
            echo '<script language="javascript">';
            echo 'alert("Miniminivån för ISBN är nu sparad!")';
            echo '</script>'; 
        }
        catch (PDOException $e) {
            echo '<script language="javascript">';
            echo 'alert("Fel vid lagring av miniminivå för ISBN!")';
            echo '</script>';                     
        }        
    }
    else {
         echo '<script language="javascript">';
         echo 'alert("Minimiantalet ISBN måste vara minst 50!")';
         echo '</script>';
    }
      
}

    $pdo = new PDO("mysql:host=$hostname;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $sql_antal_lediga = "SELECT COUNT(ISBN) AS Antal_lediga FROM oanv_isbn";
    $stmt = $pdo->query( $sql_antal_lediga );
    foreach ($stmt as $row) {
        $antal_lediga_isbn = $row['Antal_lediga'];        
    }

    $pdo = new PDO("mysql:host=$hostname;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $sql_min_niv_isbn = "SELECT Antal FROM min_niv_isbn  WHERE Regdatum = (SELECT MAX(Regdatum) FROM min_niv_isbn)";
    $stmt = $pdo->query( $sql_min_niv_isbn );
    foreach ($stmt as $row) {
        $min_niv_isbn = $row['Antal'];        
    }

?>

<h2>LADDA IN NYA ISBN</h2>

<br />

<form action="d_ladda_isbn.php" method="post">
<input type="submit" name="ladda" style="background-color:#0fb821" value="Ladda in"/><br /><br />

Antal inladdade rader: 
<input type="text" name="Antal" value="<?php echo $antal; ?>" size="5" disabled /><br /><br />

Totalt antal lediga ISBN: 
<input type="text" name="Antal_lediga" value="<?php echo $antal_lediga_isbn; ?>" size="5" disabled /><br /><br />

<input type="submit" name="min_nivaa" style="background-color:#0fb821" value="Ändra miniminivå"/><br /><br />

Miniminivå ISBN: 
<input type="text" name="MinNivISBN" value="<?php echo $min_niv_isbn; ?>" size="5"  /><br /><br />
<br /><br />

<a href='d_isbn_meny.php'>TILL MENYN</a>

</form>

</body>
</html>