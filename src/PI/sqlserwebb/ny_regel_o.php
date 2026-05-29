<?php
require_once __DIR__ . '/sqlsrv_connect.php';
require_once __DIR__ . '/bibmet_ui.php';

$dbh = bibmet_sqlsrv_connect_or_redirect();
?>

<!DOCTYPE html PUBLIC "-//w3c//DTD XHTMLm 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<! Författare: Cecilia Wiklander>
<! Syfte: Adressrättnings-hantering>
<! Ändringar: >

<head>

    <meta charset="utf-8">

    <title>Ny regel organisation</title>

    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="Site.css" rel="stylesheet">
    <link href="vendor/tom-select/tom-select.css" rel="stylesheet">
    <?php include("include_bibmet_kth.html"); ?>
    <script src="vendor/tom-select/tom-select.complete.min.js"></script>
    <script src="bibmet-selects.js"></script>



</head>

<body class="bibmet-body">

<?php include('include_head_new.html'); ?>

<?php

    $username = $_SESSION['anv'];
    $password = $_SESSION['ord'];
    $hostname = $_SESSION['hnamn'];
    $dbname = $_SESSION['dbnamn'];

    $land_s = "Ange land";
    $stad_s = "";
    $org_s_1 = "";
    $org_s_2 = "";
    $org_s_3 = "";
    $delas = "1";
    $land_1 = "Ange land";
    $land_2 = "Ange land";
    $land_3 = "Ange land";
    $stad_1 = "";
    $stad_2 = "";
    $stad_3 = "";
    $org_1 = "Ange organisation";
    $org_2 = "Ange organisation";
    $org_3 = "Ange organisation";
    $fr = "";
    $ti = "";
    $errors = [];
    $messages = [];

    if (isset($_POST['spara'])) {
        $land_s = isset($_POST['Land']) ? trim((string) $_POST['Land']) : "Ange land";
        $stad_s = isset($_POST['Stad']) ? trim((string) $_POST['Stad']) : "";
        $org_s_1 = isset($_POST['Org_s_1']) ? trim((string) $_POST['Org_s_1']) : "";
        $org_s_2 = isset($_POST['Org_s_2']) ? trim((string) $_POST['Org_s_2']) : "";
        $org_s_3 = isset($_POST['Org_s_3']) ? trim((string) $_POST['Org_s_3']) : "";
        $delas = isset($_POST['Delas']) ? trim((string) $_POST['Delas']) : "1";
        $land_1 = isset($_POST['Land_1']) ? trim((string) $_POST['Land_1']) : "Ange land";
        $land_2 = isset($_POST['Land_2']) ? trim((string) $_POST['Land_2']) : "Ange land";
        $land_3 = isset($_POST['Land_3']) ? trim((string) $_POST['Land_3']) : "Ange land";
        $stad_1 = isset($_POST['Stad_1']) ? trim((string) $_POST['Stad_1']) : "";
        $stad_2 = isset($_POST['Stad_2']) ? trim((string) $_POST['Stad_2']) : "";
        $stad_3 = isset($_POST['Stad_3']) ? trim((string) $_POST['Stad_3']) : "";
        $org_1 = isset($_POST['Org_1']) ? trim((string) $_POST['Org_1']) : "Ange organisation";
        $org_2 = isset($_POST['Org_2']) ? trim((string) $_POST['Org_2']) : "Ange organisation";
        $org_3 = isset($_POST['Org_3']) ? trim((string) $_POST['Org_3']) : "Ange organisation";
        $fr = isset($_POST['Fr']) ? trim((string) $_POST['Fr']) : "";
        $ti = isset($_POST['Ti']) ? trim((string) $_POST['Ti']) : "";

        if ($org_1 === "") {
            $org_1 = "Ange organisation";
        }
        if ($org_2 === "") {
            $org_2 = "Ange organisation";
        }
        if ($org_3 === "") {
            $org_3 = "Ange organisation";
        }

        $Sk = "'";
        $Ers = "''";

        $stad_s = str_replace($Sk, $Ers, $stad_s);
        $org_s_1 = str_replace($Sk, $Ers, $org_s_1);
        $org_s_2 = str_replace($Sk, $Ers, $org_s_2);
        $org_s_3 = str_replace($Sk, $Ers, $org_s_3);
        $stad_1 = str_replace($Sk, $Ers, $stad_1);
        $stad_2 = str_replace($Sk, $Ers, $stad_2);
        $stad_3 = str_replace($Sk, $Ers, $stad_3);
        $org_1 = str_replace($Sk, $Ers, $org_1);
        $org_2 = str_replace($Sk, $Ers, $org_2);
        $org_3 = str_replace($Sk, $Ers, $org_3);
        $land_s = str_replace($Sk, $Ers, $land_s);
        $land_1 = str_replace($Sk, $Ers, $land_1);
        $land_2 = str_replace($Sk, $Ers, $land_2);
        $land_3 = str_replace($Sk, $Ers, $land_3);

        $koll_svar = false;

        // NYTT
        if (strlen($fr) == 0) {
               $sql_tid = "";
               $sql_v_tid = "";
        }
        else {
               $sql_tid = ",Valid_from";
               $sql_v_tid = "," . $fr;
        }
        if (strlen($ti) > 0) {
               $sql_tid = $sql_tid . ",Valid_to";
               $sql_v_tid = $sql_v_tid . "," . $ti;
        }
        // SLUTNYTT

        if ($land_s == 'Ange land') {
             $errors[] = 'Land måste anges som sökfält.';
        }
        else {
            if (strlen($org_s_1) == 0){
                 $errors[] = 'Organisation måste anges som sökfält.';
            }
            else {
                if ($org_1 == 'Ange organisation'){
                     $errors[] = 'Organisation 1 måste anges som ändringsfält.';
                }
                else {
                    if ($delas == 1) {
                        if ($org_2 != 'Ange organisation' || $org_3 != 'Ange organisation') {
                             $errors[] = 'Antalet i Delas stämmer inte med antal angivna organisationer.';
                        }
                        else {
                            $koll_svar = true;
                        }
                    }
                    else if ($delas == 2) {
                        if ($org_2 == 'Ange organisation' || $org_3 != 'Ange organisation'){
                             $errors[] = 'Antalet i Delas stämmer inte med antal angivna organisationer.';
                        }
                        else {
                            $koll_svar = true;
                        }
                    }
                    else if ($delas == 3) {
                        if ($org_2 == 'Ange organisation' || $org_3 == 'Ange organisation'){
                            $errors[] = 'Antalet i Delas stämmer inte med antal angivna organisationer.';
                        }
                        else {
                            $koll_svar = true;
                        }
                    }
                    else {
                        $errors[] = 'Antalet i Delas kan vara mellan 1 och 3.';
                    }
                }
            }
        }

        $n_regel_o = isset($_SESSION['n_regel_o']) ? $_SESSION['n_regel_o'] : "";
        $regel_o_fingerprint = hash('sha256', json_encode(array(
            $land_s,
            $stad_s,
            $org_s_1,
            $org_s_2,
            $org_s_3,
            $delas,
            $land_1,
            $land_2,
            $land_3,
            $stad_1,
            $stad_2,
            $stad_3,
            $org_1,
            $org_2,
            $org_3,
            $fr,
            $ti
        ), JSON_UNESCAPED_UNICODE));

        if ($koll_svar && $n_regel_o == $regel_o_fingerprint) {
            $errors[] = 'Regeln har redan sparats. Ändra något fält innan du sparar igen.';
        }

        if ($koll_svar && $n_regel_o <> $regel_o_fingerprint) {

            $pos_f = strpos($org_1, '[' );
            $pos_e = strpos($org_1, ']' );
            $org_1_o = substr($org_1, 0, $pos_f - 1);
            $org_1_c = substr($org_1, $pos_f + 1, $pos_e - $pos_f - 1);

            if (strlen($org_1_c) > 0) {
                $sql_org_1 = "SELECT Unified_org_id FROM Unified_org_names WHERE TRIM(Name_en) = TRIM('" . $org_1_o . "') AND Country_name = '" . $org_1_c . "'";
            }
            else {
                $sql_org_1 = "SELECT Unified_org_id FROM Unified_org_names WHERE TRIM(Name_en) = TRIM('" . $org_1_o . "')";
            }

        $stmt = $dbh->query( $sql_org_1 );
        foreach ($stmt as $row) {
                $org_id_1 = $row['Unified_org_id'];
        }

            if ($land_1 == 'Ange land') {
                $land_1 = NULL;
            }

            if ($delas > 1) {
                if ($org_2 != 'Ange organisation') {

                   $pos_f = strpos($org_2, '[' );
                   $pos_e = strpos($org_2, ']' );
                   $org_2_o = substr($org_2, 0, $pos_f - 1);
                   $org_2_c = substr($org_2, $pos_f + 1, $pos_e - $pos_f - 1);

                   if (strlen($org_2_c) > 0) {
                      $sql_org_2 = "SELECT Unified_org_id FROM Unified_org_names WHERE TRIM(Name_en) = TRIM('" . $org_2_o . "') AND Country_name = '" . $org_2_c . "'";
                   }
                   else {
                      $sql_org_2 = "SELECT Unified_org_id FROM Unified_org_names WHERE TRIM(Name_en) = TRIM('" . $org_2_o . "')";
                   }

                    $stmt = $dbh->query( $sql_org_2 );
                    foreach ($stmt as $row) {
                        $org_id_2 = $row['Unified_org_id'];
                    }
                }
                else {
                    $org_id_2 = NULL;
                }

                if ($land_2 == 'Ange land') {
                    $land_2 = NULL;
                }
            }

            if ($delas > 2) {
                if ($org_3 != 'Ange organisation') {

                   $pos_f = strpos($org_3, '[' );
                   $pos_e = strpos($org_3, ']' );
                   $org_3_o = substr($org_3, 0, $pos_f - 1);
                   $org_3_c = substr($org_3, $pos_f + 1, $pos_e - $pos_f - 1);

                   if (strlen($org_3_c) > 0) {
                      $sql_org_3 = "SELECT Unified_org_id FROM Unified_org_names WHERE TRIM(Name_en) = TRIM('" . $org_3_o . "') AND Country_name = '" . $org_3_c . "'";
                   }
                   else {
                      $sql_org_3 = "SELECT Unified_org_id FROM Unified_org_names WHERE TRIM(Name_en) = TRIM('" . $org_3_o . "')";
                   }

                    $stmt = $dbh->query( $sql_org_3 );
                    foreach ($stmt as $row) {
                        $org_id_3 = $row['Unified_org_id'];
                    }
                }
                else {
                    $org_id_3 = NULL;
                }

                if ($land_3 == 'Ange land') {
                    $land_3 = NULL;
                }
            }

            $sql_country = "SELECT Country_code FROM Country WHERE Display_name = '" . $land_s . "'";
            $stmt = $dbh->query( $sql_country );
            foreach ($stmt as $row) {
                $country_code = $row['Country_code'];
            }

            if (strlen($stad_s) == 0) {
               $sql_stad_s = "";
               $sql_v_stad_s = "";
            }
            else {
               $sql_stad_s = "Find_city,";
               $sql_v_stad_s = $stad_s . "','";
            }

            $regel_sparad = false;
            $regel_2_sparad = false;
            $regel_3_sparad = false;

            try {
                $dbh->beginTransaction();

            if ($delas == 1) {
                $sql_i = "INSERT INTO Rule_org_match (Find_country,Country_code,"
                . $sql_stad_s
                . "Find_org,Divide,Country_1,City_1,Org_id_1,User_id,Rule_date,Run_status" . $sql_tid . ") VALUES ('" . $land_s . "','" . $country_code . "','"
                . $sql_v_stad_s
                . $org_s_1 . "'," . $delas . ",'" . $land_1 . "','" . $stad_1 . "'," . $org_id_1 . ",'" . $username . "',GETDATE(),1" . $sql_v_tid . ")";
            }
            else if ($delas == 2) {
                $sql_i = "INSERT INTO Rule_org_match (Find_country,Country_code,"
                . $sql_stad_s
                . "Find_org,Divide,Country_1,City_1,Org_id_1,Country_2,City_2,Org_id_2,User_id,Rule_date,Run_status" . $sql_tid . ") VALUES ('" . $land_s . "','" . $country_code . "','"
                . $sql_v_stad_s
                . $org_s_1 . "'," . $delas . ",'" . $land_1 . "','" . $stad_1 . "'," . $org_id_1 . ",'" . $land_2 . "','" . $stad_2 .
                "'," . $org_id_2 . ",'" . $username . "',GETDATE(),1" . $sql_v_tid . ")";
            }
            else {
                $sql_i = "INSERT INTO Rule_org_match (Find_country,Country_code,"
                . $sql_stad_s
                . "Find_org,Divide,Country_1,City_1,Org_id_1,Country_2,City_2,Org_id_2,Country_3,City_3,Org_id_3,User_id,
                Rule_date,Run_status" . $sql_tid . ") VALUES
                ('" . $land_s . "','" . $country_code . "','"
                . $sql_v_stad_s
                . $org_s_1 . "'," . $delas .
                ",'" . $land_1 . "','" . $stad_1 . "'," . $org_id_1 . ",'" . $land_2 . "','" . $stad_2 .
                "'," . $org_id_2 . ",'" . $land_3 . "','" . $stad_3 . "'," . $org_id_3 . ",'" .
                $username . "',GETDATE(),1" . $sql_v_tid . ")";
            }
            $stmt = $dbh->query( $sql_i );
                $regel_sparad = true;

            if (strlen($org_s_2) > 0) {

                if ($delas == 1) {
                    $sql_i = "INSERT INTO Rule_org_match (Find_country,Country_code,"
                    . $sql_stad_s
                    . "Find_org,Divide,Country_1,City_1,Org_id_1,User_id,Rule_date,Run_status" . $sql_tid . ") VALUES ('" . $land_s . "','" . $country_code . "','"
                    . $sql_v_stad_s
                    . $org_s_2 . "'," . $delas . ",'" . $land_1 . "','" . $stad_1 . "'," . $org_id_1 . ",'" . $username . "',GETDATE(),1" . $sql_v_tid . ")";
                }
                else if ($delas == 2) {
                    $sql_i = "INSERT INTO Rule_org_match (Find_country,Country_code,"
                    . $sql_stad_s
                    . "Find_org,Divide,Country_1,City_1,Org_id_1,Country_2,City_2,Org_id_2,User_id,Rule_date,Run_status" . $sql_tid . ") VALUES ('" . $land_s . "','" . $country_code . "','"
                    . $sql_v_stad_s
                    . $org_s_2 . "'," . $delas . ",'" . $land_1 . "','" . $stad_1 . "'," . $org_id_1 . ",'" . $land_2 . "','" . $stad_2 .
                    "'," . $org_id_2 . ",'" . $username . "',GETDATE(),1" . $sql_v_tid . ")";
                }
                else {
                    $sql_i = "INSERT INTO Rule_org_match (Find_country,Country_code,"
                    . $sql_stad_s
                    . "Find_org,Divide,Country_1,City_1,Org_id_1,Country_2,City_2,Org_id_2,Country_3,City_3,Org_id_3,User_id,
                    Rule_date,Run_status" . $sql_tid . ") VALUES
                    ('" . $land_s . "','" . $country_code . "','"
                    . $sql_v_stad_s
                    . $org_s_2 . "'," . $delas .
                    ",'" . $land_1 . "','" . $stad_1 . "'," . $org_id_1 . ",'" . $land_2 . "','" . $stad_2 .
                    "'," . $org_id_2 . ",'" . $land_3 . "','" . $stad_3 . "'," . $org_id_3 . ",'" .
                    $username . "',GETDATE(),1" . $sql_v_tid . ")";
                }

                $stmt = $dbh->query( $sql_i );
                    $regel_2_sparad = true;
            }

            if (strlen($org_s_3) > 0) {

                if ($delas == 1) {
                    $sql_i = "INSERT INTO Rule_org_match (Find_country,Country_code,"
                    . $sql_stad_s
                    . "Find_org,Divide,Country_1,City_1,Org_id_1,User_id,Rule_date,Run_status" . $sql_tid . ") VALUES ('" . $land_s . "','" . $country_code . "','"
                    . $sql_v_stad_s
                    . $org_s_3 . "'," . $delas . ",'" . $land_1 . "','" . $stad_1 . "'," . $org_id_1 . ",'" . $username . "',GETDATE(),1" . $sql_v_tid . ")";
                }
                else if ($delas == 2) {
                    $sql_i = "INSERT INTO Rule_org_match (Find_country,Country_code,"
                    . $sql_stad_s
                    . "Find_org,Divide,Country_1,City_1,Org_id_1,Country_2,City_2,Org_id_2,User_id,Rule_date,Run_status" . $sql_tid . ") VALUES ('" . $land_s . "','" . $country_code . "','"
                    . $sql_v_stad_s
                    . $org_s_3 . "'," . $delas . ",'" . $land_1 . "','" . $stad_1 . "'," . $org_id_1 . ",'" . $land_2 . "','" . $stad_2 .
                    "'," . $org_id_2 . ",'" . $username . "',GETDATE(),1" . $sql_v_tid . ")";
                }
                else {
                    $sql_i = "INSERT INTO Rule_org_match (Find_country,Country_code,"
                    . $sql_stad_s
                    . "Find_org,Divide,Country_1,City_1,Org_id_1,Country_2,City_2,Org_id_2,Country_3,City_3,Org_id_3,User_id,
                    Rule_date,Run_status" . $sql_tid . ") VALUES
                    ('" . $land_s . "','" . $country_code . "','"
                    . $sql_v_stad_s
                    . $org_s_3 . "'," . $delas .
                    ",'" . $land_1 . "','" . $stad_1 . "'," . $org_id_1 . ",'" . $land_2 . "','" . $stad_2 .
                    "'," . $org_id_2 . ",'" . $land_3 . "','" . $stad_3 . "'," . $org_id_3 . ",'" .
                    $username . "',GETDATE(),1" . $sql_v_tid . ")";
                }

                $stmt = $dbh->query( $sql_i );
                    $regel_3_sparad = true;
            }


                $dbh->commit();

            } catch (Exception $e) {
                if ($dbh->inTransaction()) {
                    $dbh->rollBack();
                }
                $regel_sparad = false;
                $regel_2_sparad = false;
                $regel_3_sparad = false;
                $errors[] = 'Fel vid sparande av regeln.';
            }

            if ($regel_sparad) {
                $_SESSION['n_regel_o'] = $regel_o_fingerprint;
                $messages[] = 'Regeln är sparad.';
            }

            // Blanka sparad regels textfält
            $stad_s = "";
            $org_s_1 = "";
            $org_s_2 = "";
            $org_s_3 = "";
            $stad_1 = "";
            $stad_2 = "";
            $stad_3 = "";
            $fr = "";
            $ti = "";

        }

    }

    $countries = [];

    try {
        $stmt = $dbh->query("SELECT Display_name FROM country ORDER BY Display_name");
        $countries = $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (PDOException $e) {
        $errors[] = "Det gick inte att hämta listorna. " . $e->getMessage();
    }

?>

<main class="bibmet-main">
    <section class="bibmet-hero">
        <div class="bibmet-hero__row">
            <div>
                <p class="bibmet-eyebrow">Adressrättningsregler</p>
                <h1 class="bibmet-title">Ny regel organisation</h1>
                <p class="bibmet-muted">Skapa en ny organisationsregel. Fält markerade med * är obligatoriska.</p>
            </div>
            <div class="bibmet-action-group">
                <a href="regel_organisation.php" class="bibmet-button bibmet-button--secondary">Till sökning</a>
                <a href="adressmeny.php" class="bibmet-button bibmet-button--secondary">Till menyn</a>
            </div>
        </div>
    </section>

    <?php if ($errors) : ?>
        <div class="bibmet-alert" role="alert">
            <?php foreach ($errors as $error) : ?>
                <p><?php echo bibmet_h($error); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if ($messages) : ?>
        <div class="bibmet-alert bibmet-alert--success" role="status">
            <?php foreach ($messages as $message) : ?>
                <p><?php echo bibmet_h($message); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form action="ny_regel_o.php" method="post" class="bibmet-panel">
        <div class="bibmet-rule-form-layout">
            <div>
                <div class="bibmet-panel__header">
                    <h2 class="bibmet-panel__title">Sökfält</h2>
                </div>

                <div class="bibmet-form-grid bibmet-form-grid--narrow">
            <label class="bibmet-field">
                <span class="bibmet-field__label">Land *</span>
                <select class="bibmet-select js-bibmet-select" id="id_s_land" name="Land">
                    <option value="Ange land">Ange land</option>
                    <?php foreach ($countries as $country) : ?>
                        <option value="<?php echo bibmet_h($country); ?>"<?php echo bibmet_selected_attr($country, $land_s); ?>><?php echo bibmet_h($country); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label class="bibmet-field">
                <span class="bibmet-field__label">Stad</span>
                <input class="bibmet-input" type="text" name="Stad" id="id_s_stad" value="<?php echo isset($stad_s) ? bibmet_h($stad_s) : ''; ?>" />
            </label>

            <label class="bibmet-field">
                <span class="bibmet-field__label">Organisationsnamn 1 *</span>
                <input class="bibmet-input" type="text" name="Org_s_1" id="id_s_org_1" value="<?php echo isset($org_s_1) ? bibmet_h($org_s_1) : ''; ?>" />
            </label>

            <label class="bibmet-field">
                <span class="bibmet-field__label">Organisationsnamn 2</span>
                <input class="bibmet-input" type="text" name="Org_s_2" id="id_s_org_2" value="<?php echo isset($org_s_2) ? bibmet_h($org_s_2) : ''; ?>" />
            </label>

            <label class="bibmet-field">
                <span class="bibmet-field__label">Organisationsnamn 3</span>
                <input class="bibmet-input" type="text" name="Org_s_3" id="id_s_org_3" value="<?php echo isset($org_s_3) ? bibmet_h($org_s_3) : ''; ?>" />
            </label>
        </div>

        <div class="bibmet-panel__header">
            <h2 class="bibmet-panel__title">Ändringsfält</h2>
        </div>

        <div class="bibmet-form-grid bibmet-form-grid--narrow">
            <label class="bibmet-field">
                <span class="bibmet-field__label">Delas i *</span>
                <select class="bibmet-select js-bibmet-select" id="id_delas" name="Delas">
                    <option value="1"<?php echo bibmet_selected_attr("1", $delas); ?>>1</option>
                    <option value="2"<?php echo bibmet_selected_attr("2", $delas); ?>>2</option>
                    <option value="3"<?php echo bibmet_selected_attr("3", $delas); ?>>3</option>
                </select>
            </label>

            <label class="bibmet-field">
                <span class="bibmet-field__label">Gäller från</span>
                <input class="bibmet-input" type="text" name="Fr" id="id_fr" value="<?php echo isset($fr) ? bibmet_h($fr) : ''; ?>" />
            </label>

            <label class="bibmet-field">
                <span class="bibmet-field__label">Gäller till</span>
                <input class="bibmet-input" type="text" name="Ti" id="id_ti" value="<?php echo isset($ti) ? bibmet_h($ti) : ''; ?>" />
            </label>
        </div>

            </div>

            <div class="bibmet-rule-form-layout__targets">
                <?php for ($i = 1; $i <= 3; $i++) : ?>
                    <section class="bibmet-panel bibmet-panel--subtle">
                <div class="bibmet-panel__header">
                    <h3 class="bibmet-panel__title">Organisation <?php echo bibmet_h($i); ?><?php echo $i === 1 ? ' *' : ''; ?></h3>
                </div>
                <div class="bibmet-form-grid bibmet-form-grid--narrow">
                    <label class="bibmet-field">
                        <span class="bibmet-field__label">Annat organisationsnamn<?php echo $i === 1 ? ' *' : ''; ?></span>
                        <?php $selectedOrg = ${'org_' . $i}; ?>
                        <select class="bibmet-select js-bibmet-select" data-remote-url="bibmet-org-options.php" id="id_h_org_<?php echo bibmet_h($i); ?>" name="Org_<?php echo bibmet_h($i); ?>"<?php echo $i === 1 ? ' required' : ''; ?>>
                            <option value="">Ange organisation</option>
                            <?php if ($selectedOrg !== "" && $selectedOrg !== "Ange organisation") : ?>
                                <option value="<?php echo bibmet_h($selectedOrg); ?>" selected><?php echo bibmet_h($selectedOrg); ?></option>
                            <?php endif; ?>
                        </select>
                    </label>

                    <label class="bibmet-field">
                        <span class="bibmet-field__label">Annat land</span>
                        <?php $selectedLand = ${'land_' . $i}; ?>
                        <select class="bibmet-select js-bibmet-select" id="id_h_land_<?php echo bibmet_h($i); ?>" name="Land_<?php echo bibmet_h($i); ?>">
                            <option value="Ange land">Ange land</option>
                            <?php foreach ($countries as $country) : ?>
                                <option value="<?php echo bibmet_h($country); ?>"<?php echo bibmet_selected_attr($country, $selectedLand); ?>><?php echo bibmet_h($country); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>

                    <label class="bibmet-field">
                        <span class="bibmet-field__label">Annan stad</span>
                        <input class="bibmet-input" type="text" name="Stad_<?php echo bibmet_h($i); ?>" id="h_id_stad_<?php echo bibmet_h($i); ?>" value="<?php echo isset(${'stad_' . $i}) ? bibmet_h(${'stad_' . $i}) : ''; ?>" />
                    </label>
                </div>
                    </section>
                <?php endfor; ?>
            </div>
        </div>

        <div class="bibmet-form-actions">
            <div class="bibmet-action-group">
                <input type="submit" name="spara" value="Spara regel" class="bibmet-button bibmet-button--primary">
                <a href="regel_organisation.php" class="bibmet-button bibmet-button--secondary">Avbryt</a>
            </div>
        </div>
    </form>
</main>

    </body>
</html>