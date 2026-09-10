<?php
require_once __DIR__ . '/sqlsrv_connect.php';

session_start();
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml11/DTD/xhtml-transitional.dtd">

<! Författare: Cecilia Wiklander>
<! Syfte: Adressrättnings-hantering>
<! Ändringar: >

<head>

    <meta charset="utf-8">

    <title>INLOGGNING</title>

    <link href="Site.css" rel="stylesheet">

<script>

function validateForm() {

    var x = document.forms["myForm"]["Anv"].value;
    var y = document.forms["myForm"]["Ord"].value;
    if (x == null || x == "") {
        alert("Användarid måste anges!");
        return false;
    }
    if (y == null || y == "") {
        alert("Lösenordet måste anges!");
        return false;
    }

}

</script>

</head>

<body class="bibmet-body">

<?php include('include_head_new.html'); ?>

<?php

    $anv = isset($_POST['Anv']) ? $_POST['Anv'] : "";
    $ord = isset($_POST['Ord']) ? $_POST['Ord'] : "";

    if (strlen($anv) > 0 && strlen($ord) > 0) {

        try {
            $username = $anv;
            $password = $ord;
            $hostname = bibmet_mssql_host();
            $dbname = "BIBSTAT";
            $dbh = new PDO("sqlsrv:Server=$hostname;Database=$dbname",$username,$password);
            $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $_SESSION['anv'] = $anv;
            $_SESSION['ord'] = $ord;
            $_SESSION['hnamn'] = $hostname;
            $_SESSION['dbnamn'] = $dbname;
            // Sessionsvariabler för att hindra reload
            $_SESSION['b_org_id'] = "";
            $_SESSION['b_regel_o_id'] = "";
            $_SESSION['b_regel_f_a_id'] = "";
            $_SESSION['b_regel_c_id'] = "";
            $_SESSION['b_regel_o_typ_id'] = "";
            //
            $_SESSION['a_org_id'] = "";
            $_SESSION['a_regel_o_id'] = "";
            $_SESSION['a_regel_f_a_id'] = "";
            $_SESSION['a_regel_c_id'] = "";
            $_SESSION['a_regel_o_typ_id'] = "";
            //
            $_SESSION['n_org'] = "";
            $_SESSION['n_regel_o'] = "";
            $_SESSION['n_regel_f_a'] = "";
            $_SESSION['n_regel_c'] = "";
            $_SESSION['n_regel_o_typ'] = "";
            header('Location: /PI/sqlserwebb/adressmeny.php');
        } catch (PDOException $e){
            echo '<script language="javascript">';
            echo 'alert("Fel vid inloggning till databasen!")';
            echo '</script>';
        }

    }

?>

<main class="bibmet-main bibmet-main--form">
    <section class="bibmet-hero">
        <p class="bibmet-eyebrow">Bibmet</p>
        <h1 class="bibmet-title">Inloggning</h1>
        <p class="bibmet-muted">Logga in för att hantera adressrättning.</p>
    </section>

    <section class="bibmet-panel">
        <div class="bibmet-panel__header">
            <h2 class="bibmet-panel__title">Ange inloggningsuppgifter</h2>
        </div>
        <div class="bibmet-panel__body">
            <?php if (isset($_GET['reason']) && $_GET['reason'] == 'timeout') { ?>
                <p class="bibmet-error">Din session har timeat ut. Logga in igen.</p>
            <?php } ?>

            <form name="myForm" onsubmit="return validateForm()" action="loggain.php" method="post">
                <div class="bibmet-form-grid">
                    <label class="bibmet-field">
                        <span class="bibmet-field__label">Användarnamn</span>
                        <input class="bibmet-input bibmet-input--short" type="text" name="Anv" value="<?php echo htmlspecialchars($anv, ENT_QUOTES, 'UTF-8'); ?>" />
                    </label>

                    <label class="bibmet-field">
                        <span class="bibmet-field__label">Lösenord</span>
                        <input class="bibmet-input bibmet-input--short" type="password" name="Ord" />
                    </label>
                </div>

                <div class="bibmet-form-actions">
                    <input class="bibmet-button bibmet-button--primary" type="submit" name="loggain" value="Logga in"/>
                </div>
            </form>
        </div>
    </section>
</main>

</body>
</html>
