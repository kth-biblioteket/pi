<?php
require_once __DIR__ . '/sqlsrv_connect.php';
require_once __DIR__ . '/bibmet_ui.php';

$dbh = bibmet_sqlsrv_connect_or_redirect();

$regel_id = isset($_SESSION['regel_id']) ? (string) $_SESSION['regel_id'] : "";
$_SESSION['regel_id_ut'] = $regel_id;

$username = isset($_SESSION['anv']) ? (string) $_SESSION['anv'] : "";
$a_regel_o_typ_id = isset($_SESSION['a_regel_o_typ_id']) ? (string) $_SESSION['a_regel_o_typ_id'] : "";

$land_till = isset($_POST['Land_ut_2']) ? trim((string) $_POST['Land_ut_2']) : "";
$stad_till = isset($_POST['Stad_ut_2']) ? trim((string) $_POST['Stad_ut_2']) : "";
$org_till_1 = isset($_POST['Org_till_1']) ? trim((string) $_POST['Org_till_1']) : "";
$org_till_2 = isset($_POST['Org_till_2']) ? trim((string) $_POST['Org_till_2']) : "";
$org_till_ej = isset($_POST['Org_till_ej']) ? trim((string) $_POST['Org_till_ej']) : "";
$land_1_till = isset($_POST['Land_1_ut_2']) ? trim((string) $_POST['Land_1_ut_2']) : "";
$stad_1_till = isset($_POST['Stad_1_till']) ? trim((string) $_POST['Stad_1_till']) : "";
$orgtyp_till = isset($_POST['Orgtyp_ut_2']) ? trim((string) $_POST['Orgtyp_ut_2']) : "";

$messages = [];
$warningMessages = [];
$errors = [];
$updated = false;

if (!ctype_digit($regel_id) || (int) $regel_id <= 0) {
    $errors[] = "Ogiltigt regel-id.";
}

$isPost = $_SERVER['REQUEST_METHOD'] === 'POST';
if (!$isPost) {
    $warningMessages[] = "Ingen uppdatering skickades. Gå tillbaka och spara regeln igen.";
}

if ($isPost && !$errors) {
    $sk = "'";
    $ers = "''";

    $stad_till = str_replace($sk, $ers, $stad_till);
    $org_till_1 = str_replace($sk, $ers, $org_till_1);
    $org_till_2 = str_replace($sk, $ers, $org_till_2);
    $org_till_ej = str_replace($sk, $ers, $org_till_ej);
    $stad_1_till = str_replace($sk, $ers, $stad_1_till);
    $land_till = str_replace($sk, $ers, $land_till);
    $land_1_till = str_replace($sk, $ers, $land_1_till);

    if ($orgtyp_till === 'Ange organisationstyp' || $orgtyp_till === "") {
        $errors[] = "Organisationstyp måste anges som sökfält.";
    }

    if ($org_till_1 === "") {
        $errors[] = "Organisation sträng 1 måste anges som sökfält.";
    }

    if (!$errors) {
        if ($a_regel_o_typ_id === $regel_id) {
            $warningMessages[] = "Regeln är redan uppdaterad i den här sessionen.";
        } else {
            try {
                $country_code = null;
                if ($land_till !== "" && $land_till !== 'Ange land') {
                    $countryStmt = $dbh->prepare("SELECT Country_code FROM Country WHERE Display_name = :display_name");
                    $countryStmt->bindValue(':display_name', $land_till, PDO::PARAM_STR);
                    $countryStmt->execute();
                    $country_code = $countryStmt->fetchColumn();
                }

                $orgTypStmt = $dbh->prepare("SELECT Org_type_code FROM Organization_type WHERE Org_type_eng = :org_type_eng");
                $orgTypStmt->bindValue(':org_type_eng', $orgtyp_till, PDO::PARAM_STR);
                $orgTypStmt->execute();
                $org_typ_code = $orgTypStmt->fetchColumn();

                if ($org_typ_code === false) {
                    $errors[] = "Ogiltig organisationstyp.";
                } else {
                    $find_country = ($land_till === 'Ange land' || $land_till === "") ? null : $land_till;
                    $find_city = $stad_till === "" ? null : $stad_till;
                    $find_org_2 = $org_till_2 === "" ? null : $org_till_2;
                    $find_org_not = $org_till_ej === "" ? null : $org_till_ej;
                    $country = ($land_1_till === 'Ange land' || $land_1_till === "") ? null : $land_1_till;
                    $city = $stad_1_till === "" ? null : $stad_1_till;

                    $updateStmt = $dbh->prepare("UPDATE Rule_org_type_match
                        SET
                            Find_country = :find_country,
                            Country_code = :country_code,
                            Find_city = :find_city,
                            Country = :country,
                            City = :city,
                            Org_type_code = :org_type_code,
                            Find_org_1 = :find_org_1,
                            Find_org_2 = :find_org_2,
                            Find_org_not = :find_org_not,
                            User_id = :user_id,
                            Rule_date = GETDATE(),
                            Run_status = 1
                        WHERE R_o_t_m_id = :regel_id");
                    $updateStmt->bindValue(':find_country', $find_country, $find_country === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
                    $updateStmt->bindValue(':country_code', $country_code, $country_code === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
                    $updateStmt->bindValue(':find_city', $find_city, $find_city === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
                    $updateStmt->bindValue(':country', $country, $country === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
                    $updateStmt->bindValue(':city', $city, $city === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
                    $updateStmt->bindValue(':org_type_code', $org_typ_code, PDO::PARAM_STR);
                    $updateStmt->bindValue(':find_org_1', $org_till_1, PDO::PARAM_STR);
                    $updateStmt->bindValue(':find_org_2', $find_org_2, $find_org_2 === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
                    $updateStmt->bindValue(':find_org_not', $find_org_not, $find_org_not === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
                    $updateStmt->bindValue(':user_id', $username, PDO::PARAM_STR);
                    $updateStmt->bindValue(':regel_id', (int) $regel_id, PDO::PARAM_INT);
                    $updateStmt->execute();

                    $_SESSION['a_regel_o_typ_id'] = $regel_id;
                    $updated = true;

                    if ($updateStmt->rowCount() > 0) {
                        $messages[] = "Regeln är nu ändrad.";
                    } else {
                        $warningMessages[] = "Inga värden ändrades. Regeln kan redan ha samma innehåll.";
                    }
                }
            } catch (PDOException $e) {
                $errors[] = "Fel vid ändring av regeln.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="sv">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ändra regel organisationstyp</title>
    <link href="Site.css" rel="stylesheet">
    <?php include("include_bibmet_kth.html"); ?>
</head>

<body class="bibmet-body">
    <?php include('include_head_new.html'); ?>

    <main class="bibmet-main">
        <section class="bibmet-hero">
            <div class="bibmet-hero__row">
                <div>
                    <p class="bibmet-eyebrow">Adressrättningsregler</p>
                    <h1 class="bibmet-title">Ändra regel organisationstyp</h1>
                    <p class="bibmet-muted">Regel-id: <?php echo bibmet_h($regel_id); ?></p>
                </div>
                <div class="bibmet-action-group">
                    <a href="aendra_regel_o_typ.php" class="bibmet-button bibmet-button--secondary">Tillbaka</a>
                    <a href="regel_organisation_typ.php" class="bibmet-button bibmet-button--primary">Till sökning</a>
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

        <?php bibmet_render_messages_panel($updated ? "Ändring klar" : "Resultat", $messages, "success"); ?>
        <?php bibmet_render_messages_panel("Varning", $warningMessages, "warning"); ?>

        <?php
        bibmet_render_summary_panel("Sparade värden", [
            "Regel-id" => $regel_id,
            "Land" => $land_till,
            "Stad" => $stad_till,
            "Organisation, sträng 1" => $org_till_1,
            "Organisation, sträng 2" => $org_till_2,
            "Organisation, sträng ej" => $org_till_ej,
            "Organisationstyp" => $orgtyp_till,
            "Annat land" => $land_1_till,
            "Annan stad" => $stad_1_till,
        ]);
        ?>
    </main>
</body>

</html>