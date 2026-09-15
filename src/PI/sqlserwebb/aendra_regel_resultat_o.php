<?php
require_once __DIR__ . '/sqlsrv_connect.php';
require_once __DIR__ . '/bibmet_ui.php';

$dbh = bibmet_sqlsrv_connect_or_redirect();

$regel_id = isset($_SESSION['regel_id']) ? (string) $_SESSION['regel_id'] : "";
$_SESSION['regel_id_ut'] = $regel_id;
$username = isset($_SESSION['anv']) ? $_SESSION['anv'] : "";
$a_regel_o_id = isset($_SESSION['a_regel_o_id']) ? (string) $_SESSION['a_regel_o_id'] : "";

$land_till = bibmet_post_value('Land_ut_2');
$stad_till = bibmet_post_value('Stad_till');
$org_till = bibmet_post_value('Org_till');
$delas_till = bibmet_post_value('Delas_ut_2');
$land_1_till = bibmet_post_value('Land_1_ut_2');
$land_2_till = bibmet_post_value('Land_2_ut_2');
$land_3_till = bibmet_post_value('Land_3_ut_2');
$stad_1_till = bibmet_post_value('Stad_1_till');
$stad_2_till = bibmet_post_value('Stad_2_till');
$stad_3_till = bibmet_post_value('Stad_3_till');
$org_1_till = bibmet_post_value('Org_1_ut_2');
$org_2_till = bibmet_post_value('Org_2_ut_2');
$org_3_till = bibmet_post_value('Org_3_ut_2');
$fr = bibmet_post_value('Fr');
$ti = bibmet_post_value('Ti');

$alerts = [];
$updated = false;
$koll_svar = false;

if (!ctype_digit($regel_id) || (int) $regel_id <= 0) { $alerts[] = 'Ogiltigt regel-id!'; }
elseif ($land_till == 'Ange land') { $alerts[] = 'Land måste anges som sökfält!'; }
elseif (strlen($org_till) == 0) { $alerts[] = 'Organisation måste anges som sökfält!'; }
elseif ($org_1_till == 'Ange organisation' || strlen($org_1_till) == 0) { $alerts[] = 'Organisation 1 måste anges som ändringsfält!'; }
elseif ($delas_till == 1) {
    if (bibmet_has_org_value($org_2_till) || bibmet_has_org_value($org_3_till)) { $alerts[] = 'Antalet i Delas stämmer inte med antal angivna organisationer!'; }
    else { $koll_svar = true; }
} elseif ($delas_till == 2) {
    if (!bibmet_has_org_value($org_2_till) || bibmet_has_org_value($org_3_till)) { $alerts[] = 'Antalet i Delas stämmer inte med antal angivna organisationer!'; }
    else { $koll_svar = true; }
} elseif ($delas_till == 3) {
    if (!bibmet_has_org_value($org_2_till) || !bibmet_has_org_value($org_3_till)) { $alerts[] = 'Antalet i Delas stämmer inte med antal angivna organisationer!'; }
    else { $koll_svar = true; }
} else { $alerts[] = 'Antalet i Delas kan vara mellan 1 och 3!'; }

$land_1_db = bibmet_normalize_select_value($land_1_till, 'Ange land');
$land_2_db = bibmet_normalize_select_value($land_2_till, 'Ange land');
$land_3_db = bibmet_normalize_select_value($land_3_till, 'Ange land');

if ($koll_svar && $a_regel_o_id !== $regel_id) {
    try {
        $org_id_1 = bibmet_find_org_id($dbh, $org_1_till);
        $org_id_2 = ((int) $delas_till > 1 && bibmet_has_org_value($org_2_till)) ? bibmet_find_org_id($dbh, $org_2_till) : null;
        $org_id_3 = ((int) $delas_till > 2 && bibmet_has_org_value($org_3_till)) ? bibmet_find_org_id($dbh, $org_3_till) : null;

        $countryStmt = $dbh->prepare("SELECT Country_code FROM Country WHERE Display_name = :land");
        $countryStmt->bindValue(':land', $land_till, PDO::PARAM_STR);
        $countryStmt->execute();
        $country_code = $countryStmt->fetchColumn();

        $sql = "UPDATE Rule_org_match SET Find_country = :find_country, Country_code = :country_code, Find_city = :find_city,
            Find_org = :find_org, Divide = :divide, Country_1 = :country_1, City_1 = :city_1, Org_id_1 = :org_id_1,
            Country_2 = :country_2, City_2 = :city_2, Org_id_2 = :org_id_2, Country_3 = :country_3, City_3 = :city_3,
            Org_id_3 = :org_id_3, User_id = :user_id, Rule_date = GETDATE(), Run_status = 1,
            Valid_from = :valid_from, Valid_to = :valid_to WHERE R_o_m_id = :regel_id";
        $stmt = $dbh->prepare($sql);
        $stmt->bindValue(':find_country', $land_till, PDO::PARAM_STR);
        bibmet_bind_nullable($stmt, ':country_code', $country_code);
        bibmet_bind_nullable($stmt, ':find_city', $stad_till);
        $stmt->bindValue(':find_org', $org_till, PDO::PARAM_STR);
        $stmt->bindValue(':divide', (int) $delas_till, PDO::PARAM_INT);
        bibmet_bind_nullable($stmt, ':country_1', $land_1_db);
        bibmet_bind_nullable($stmt, ':city_1', $stad_1_till);
        bibmet_bind_nullable($stmt, ':org_id_1', $org_id_1, PDO::PARAM_INT);
        bibmet_bind_nullable($stmt, ':country_2', (int) $delas_till >= 2 ? $land_2_db : null);
        bibmet_bind_nullable($stmt, ':city_2', (int) $delas_till >= 2 ? $stad_2_till : null);
        bibmet_bind_nullable($stmt, ':org_id_2', (int) $delas_till >= 2 ? $org_id_2 : null, PDO::PARAM_INT);
        bibmet_bind_nullable($stmt, ':country_3', (int) $delas_till >= 3 ? $land_3_db : null);
        bibmet_bind_nullable($stmt, ':city_3', (int) $delas_till >= 3 ? $stad_3_till : null);
        bibmet_bind_nullable($stmt, ':org_id_3', (int) $delas_till >= 3 ? $org_id_3 : null, PDO::PARAM_INT);
        $stmt->bindValue(':user_id', $username, PDO::PARAM_STR);
        bibmet_bind_nullable($stmt, ':valid_from', $fr === '' ? null : (int) $fr, PDO::PARAM_INT);
        bibmet_bind_nullable($stmt, ':valid_to', $ti === '' ? null : (int) $ti, PDO::PARAM_INT);
        $stmt->bindValue(':regel_id', (int) $regel_id, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() > 0) { $alerts[] = 'Regeln är nu ändrad!'; $_SESSION['a_regel_o_id'] = $regel_id; }
        else { $alerts[] = 'Fel vid ändring av regeln!'; }
    } catch (PDOException $e) { $alerts[] = 'Fel vid ändring av regeln!'; }
}
?>


<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ändra regel organisation</title>
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
                    <h1 class="bibmet-title">Ändra regel organisation</h1>
                    <p class="bibmet-muted">Resultat av ändringen.</p>
                </div>
                <div class="bibmet-action-group">
                    <a href="aendra_regel_o.php" class="bibmet-button bibmet-button--secondary">Tillbaka</a>
                    <a href="regel_organisation.php" class="bibmet-button bibmet-button--primary">Till sökning</a>
                    <a href="adressmeny.php" class="bibmet-button bibmet-button--secondary">Till menyn</a>
                </div>
            </div>
        </section>

        <?php if ($alerts) : ?>
            <div class="bibmet-alert<?php echo $updated ? ' bibmet-alert--success' : ''; ?>" role="<?php echo $updated ? 'status' : 'alert'; ?>">
                <?php foreach ($alerts as $alert) : ?>
                    <p><?php echo bibmet_h($alert); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php
        bibmet_render_summary_panel("Regel", [
            "Regel-id" => $regel_id,
            "Land" => $land_till,
            "Stad" => $stad_till,
            "Organisationsnamn" => $org_till,
            "Delas i" => $delas_till,
            "Gäller från" => $fr,
            "Gäller till" => $ti,
            "Organisation 1" => $org_1_till,
            "Land 1" => $land_1_till,
            "Stad 1" => $stad_1_till,
            "Organisation 2" => $org_2_till,
            "Land 2" => $land_2_till,
            "Stad 2" => $stad_2_till,
            "Organisation 3" => $org_3_till,
            "Land 3" => $land_3_till,
            "Stad 3" => $stad_3_till,
        ]);
        ?>
    </main>
</body>
</html>
