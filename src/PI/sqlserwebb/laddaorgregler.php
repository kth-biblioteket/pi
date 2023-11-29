<?php session_start(); ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml11/DTD/xhtml-transitional.dtd">

<! Författare: Cecilia Wiklander>
<! Syfte: Adressrättnings-hantering>
<! Ändringar: >

<head>

    <meta charset="utf-8">

    <title>LADDA REGLER - ORGANISATION</title>

    <link href="Site.css" rel="stylesheet">

</head>

<body>

<?php include('include_head_new.html'); ?>

<h2>LADDA REGLER - ORGANISATION</h2>

</br>
</br>

<?php

if (isset($_POST['ladda'])) {  

    $username = $_SESSION['anv'];
    $password = $_SESSION['ord'];
    $hostname = $_SESSION['hnamn'];
    $dbname = "BIBSTAT";
    
    $target_dir = "DATAFILER/";
    $target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
    $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
    $uploadOk = 1;

    // Kontrollera filtyp
    if($imageFileType != "csv" ) {
           echo "Tyvärr, enbart csv-filer tillåts";
           $uploadOk = 0;
    }
    else {
          // Kontrollera om filen redan finns
          if (file_exists($target_file)) {
              echo "Tyvärr, filen finns redan";
              $uploadOk = 0;
          } 
          else {
                // Kontrollera filstorlek
                if ($_FILES["fileToUpload"]["size"] > 500000) {
                    echo "Tyvärr, filen är för stor.";
                    $uploadOk = 0;
                }
                else {
                      if (strpos($target_file, ' ') !== false) {
                         echo "Tyvärr, filnamnet får inte innehålla blanktecken.";
                         $uploadOk = 0;                        
                      }
                      else {
                            
                      		if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
                          	    echo "Filen " . htmlspecialchars( basename( $_FILES["fileToUpload"]["name"])) . " har laddats upp.";
                      		} 
                      		else {
                                    echo "Tyvärr, filen gick inte att ladda upp.";
                            	    $uploadOk = 0;
                      		}  
                    }
                }      
          }
    }
  
    if ($uploadOk == 1) { // Riktigt filnamn

       $filnamnet = $target_file;
       $filnamnet = str_replace(' ', '', $filnamnet);
    
       $dbh = new PDO("sqlsrv:Server=$hostname;Database=$dbname",$username,$password);
       $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);      
 
       $felantal = false; 
       $importstatus = 99;          
       // Kontrollera antal kolumner i filen och om inläsning redan har gjorts      
       $fh_in = fopen($filnamnet,'r');  
       if ($fh_in) {
         
          $sql = "SELECT FORMAT (getdate(), 'yyyy-MM-dd') AS Datum";         
          try {
              $stmt = $dbh->query( $sql );
          } catch (Exception $e) {
              echo 'Caught exception: ',  $e->getMessage(), "\n";  
          }          
    
          foreach ($stmt as $row) {
            $Datum = $row['Datum'];        
          }     
         
          if ($linearr = fgetcsv($fh_in,1000,";")) {
 
             $num = count($linearr);  
             if ($num != 8) {
                echo "<script>alert('Filen har inte rätt antal kolumner!');</script>";      
                $felantal = true;       
             } else {
             
                  if ($linearr = fgetcsv($fh_in,1000,";")) {
                     $stmt_k = $dbh->prepare("SELECT COUNT(*) AS antal FROM Rule_org_match WHERE Find_org = :Find_org AND Find_country = :Find_country AND User_id = :User_id AND Rule_date = :Rule_date AND Divide = :Divide AND Org_id_1 = :Org_id_1");                
                     $stmt_k->bindParam(':Find_org', $linearr[0]);      
                     $stmt_k->bindParam(':Find_country', $linearr[1]);
                     $stmt_k->bindParam(':Divide', $linearr[3]); 
                     $stmt_k->bindParam(':Org_id_1', $linearr[4]);  
                     $stmt_k->bindParam(':Rule_date', $Datum); 
                     $stmt_k->bindParam(':User_id', $linearr[7]);                               
                     try {
                          $stmt_k->execute();
                         } catch (Exception $e) {
                           echo 'Caught exception: ',  $e->getMessage(), "\n";  
                         }   
                         
                     foreach ($stmt_k as $row) {
                         $Antal = $row['antal']; 
     
                     } 
                     
                }                                                       
             }
                        
          } 
                      
          fclose($fh_in);
                          
       }                    
       
       if ($Antal > 0) { 
          echo "<script>alert('Filen har redan lästs in!');</script>";             
       } else {                                      
     
       $fh_in = fopen($filnamnet,'r');  
             
       if ($fh_in and !$felantal) { // Filen gick att öppna     
                           
          $stmt_f_1 = $dbh->prepare("INSERT INTO Rule_org_match (Find_country,Country_code,Find_org,Divide,Org_id_1,Rule_date,User_id,Run_status) VALUES    (:Find_country,:Country_code,:Find_org,:Divide,:Org_id_1,:Rule_date,:User_id,:Run_status)"); 
          
          $stmt_f_2 = $dbh->prepare("INSERT INTO Rule_org_match (Find_country,Country_code,Find_org,Divide,Org_id_1,Org_id_2,Rule_date,User_id,Run_status) VALUES    (:Find_country,:Country_code,:Find_org,:Divide,:Org_id_1,:Org_id_2,:Rule_date,:User_id,:Run_status)"); 
          
          $stmt_f_3 = $dbh->prepare("INSERT INTO Rule_org_match (Find_country,Country_code,Find_org,Divide,Org_id_1,Org_id_2,Org_id_3,Rule_date,User_id,Run_status) VALUES    (:Find_country,:Country_code,:Find_org,:Divide,:Org_id_1,:Org_id_2,:Org_id_3,:Rule_date,:User_id,:Run_status)");                     
                              
          $rad = 0;
          $antalposter = 0;
          $antalfelposter = 0;
          $rubrikskriven = false;

          while ($linearr = fgetcsv($fh_in,1000,";")) {
 
            $felrad = false;   
            $rad++;           
            
            if (is_numeric($linearr[3]) and is_numeric($linearr[4])) { // Splittringsvärdet och nytt org-id
               if ($linearr[3] == 2 and (!is_numeric($linearr[5]))) { // Felaktig rad saknas org_id för två org att ändra till
               
                 $felrad = true;
               } 
               if ($linearr[3] > 2 and (!is_numeric($linearr[5])) and (!is_numeric($linearr[6]))) { // Felaktig rad saknas org_id för tre org att ändra till
                 $felrad = true;
                 
               }  
               
               if (!$felrad) { // Riktig post
               
                  $antalposter++; 
 
                  if ($linearr[3] == 1) {
                     $stmt_f_1->bindParam(':Find_country', $linearr[1]);
                     $stmt_f_1->bindParam(':Country_code', $linearr[2]);
                     $stmt_f_1->bindParam(':Find_org', $linearr[0]);
                     $stmt_f_1->bindParam(':Divide', $linearr[3]); 
                     $stmt_f_1->bindParam(':Org_id_1', $linearr[4]);  
                     $stmt_f_1->bindParam(':Rule_date', $Datum); 
                     $stmt_f_1->bindParam(':User_id', $linearr[7]);  
                     $stmt_f_1->bindParam(':Run_status', $importstatus);                                          
                  }
                  if ($linearr[3] == 2) {   
                     $stmt_f_2->bindParam(':Find_country', $linearr[1]);
                     $stmt_f_2->bindParam(':Country_code', $linearr[2]);
                     $stmt_f_2->bindParam(':Find_org', $linearr[0]);
                     $stmt_f_2->bindParam(':Divide', $linearr[3]); 
                     $stmt_f_2->bindParam(':Org_id_1', $linearr[4]); 
                     $stmt_f_2->bindParam(':Org_id_2', $linearr[5]);                             
                     $stmt_f_2->bindParam(':Rule_date', $Datum);                     
                     $stmt_f_2->bindParam(':User_id', $linearr[7]);  
                     $stmt_f_2->bindParam(':Run_status', $importstatus);                                                            
                  }             
                  if ($linearr[3] == 3) {   
                     $stmt_f_3->bindParam(':Find_country', $linearr[1]);
                     $stmt_f_3->bindParam(':Country_code', $linearr[2]);
                     $stmt_f_3->bindParam(':Find_org', $linearr[0]);
                     $stmt_f_3->bindParam(':Divide', $linearr[3]); 
                     $stmt_f_3->bindParam(':Org_id_1', $linearr[4]);  
                     $stmt_f_3->bindParam(':Org_id_2', $linearr[5]);  
                     $stmt_f_3->bindParam(':Org_id_3', $linearr[6]);
                     $stmt_f_3->bindParam(':Rule_date', $Datum);                                               
                     $stmt_f_3->bindParam(':User_id', $linearr[7]);  
                     $stmt_f_3->bindParam(':Run_status', $importstatus);                                         
                  }                   

                  try {       
                          
                      if ($linearr[3] == 1) {
                         $stmt_f_1->execute(); 
                      } elseif ($linearr[3] == 2) {                        
                         $stmt_f_2->execute();                         
                      } else {
                         $stmt_f_3->execute();                         
                      }                    
                                             
                  } catch (Exception $e) {
                    echo 'Caught exception: ',  $e->getMessage(), "\n";  
                  }                                                                   
               } 
                                  
            } else { // Rubrikrad eller felaktig post
                 if ($rad != 1) { // Ej rubrikrad, felaktig rad
                    $felrad = true;
                 }                  
            }  
            
            if ($felrad) {
               if (!$rubrikskriven) { 
 	          // Rubrikerna
 	          echo "<table border='1'>";
	          echo "<tr>";
	          echo "<b><th>FELAKTIGA POSTER - EJ INLÄSTA</th></h3></b></br></br>";
	          echo "</tr>";
	          echo "<tr>";
	          echo "<th>Sökt organisation</th> <th>Landsnamn</th><th>Landskod</th> <th>Delas</th> <th>Org-id 1</th> <th>Org-id 2</th>  
                  <th>Org-id 3</th><th>Användarnamn</th>";
	          echo "</tr></br>";    
	          $rubrikskriven = true;          
               } 
               if ($rad != 1) {       
               $antalfelposter++;         
               echo "<tr>";
               echo "<td style = 'white-space:PRE'>" . $linearr[0] . "</td>";
               echo "<td style = 'white-space:PRE'>" . $linearr[1] . "</td>";   
               echo "<td style = 'white-space:PRE'>" . $linearr[2] . "</td>";
               echo "<td style = 'white-space:PRE'>" . $linearr[3] . "</td>";      
               echo "<td style = 'white-space:PRE'>" . $linearr[4] . "</td>";
               echo "<td style = 'white-space:PRE'>" . $linearr[5] . "</td>";      
               echo "<td style = 'white-space:PRE'>" . $linearr[6] . "</td>";  
               echo "<td style = 'white-space:PRE'>" . $linearr[7] . "</td>";                                                                           
               echo "</tr>";              
               }   
            }      
         }
   
         if ($rubrikskriven) {
	    echo "</table>";
	    echo "<br /><br /><br />";           
         }
              
         fclose($fh_in);    
         
         echo "<script>alert('Filen är inläst!');</script>";         
         
         echo "<tr>";
         echo "<b><th>ANTAL INLÄSTA POSTER: </th><th>$antalposter</th></h3>";
	 echo "</tr></b></br></br>";         

         echo "<tr>";
         echo "<b><th>ANTAL FELAKTIGA EJ INLÄSTA POSTER: </th><th>$antalfelposter</th></h3>";
	 echo "</tr></b></br></br>";  	
	 
	 echo "Regler inlästa via uppladdade filer får värdet 99 i kolumnen Run_status i tabellen Rule_org_match"; 	 	 	 
           
       } else { // Filen gick inte att öppna
          if ($felantal){
             echo "<script>alert('Filen går inte att ladda!');</script>";             
          }
          else {
             echo "<script>alert('Filen går inte att öppna!');</script>";             
          }    
       }                    
       
     }
       
            
    } else { // Felaktigt filnamn

      echo "<script>alert('Det går inte att ladda filen!');</script>"; 
      
    } // felifilen

} // ladda

?>

<form action="laddaorgregler.php" method="post" enctype="multipart/form-data">

Välj fil att ladda upp:
<input type="file" name="fileToUpload" id="fileToUpload">
<input type="submit" value="Ladda upp" name="ladda" style="background-color:#0fb821">
<br /><br />
  
<h2>Filen måste vara av typen csv</h2> 

- behöver inte ha kolumnrubriker<br /> 
- måste vara semikolonseparerad 

<h3>Filutseende, ordningsföljd:</h3>
1) Organisationsamn att söka på
<br />
2) Landsnamn
<br />
3) Landskod
<br />
4) Splittringsvärde (1-3)
<br />
5) Organisationsid 1
<br />
6) Organisationsid 2
<br />
7) Organisationsid 3
<br />
8) Användarnamn
<br />  

<br /> <br /> <br />

</form>

<a href='adressmeny.php'>TILL MENYN</a>

</body>
</html>