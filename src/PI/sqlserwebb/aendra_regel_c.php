<?php
require_once __DIR__ . '/sqlsrv_connect.php';
require_once __DIR__ . '/bibmet_ui.php';

$dbh = bibmet_sqlsrv_connect_or_redirect();

$regel_id = bibmet_request_value('Regel_id', isset($_SESSION['regel_id_ut']) ? (string) $_SESSION['regel_id_ut'] : "");
$errors = [];
$rule = null;
$countries = [];

if (!ctype_digit($regel_id) || (int) $regel_id <= 0) {
    $errors[] = "Ogiltigt regel-id.";
} else {
    $_SESSION['regel_id'] = $regel_id;

    try {
        $stmt = $dbh->prepare("SELECT r.Find_country, r.Find_city, r.Find_org, r.Divide,
            r.Country_1, r.City_1, o1.Name_en + ' [' + o1.Country_name + ']' AS Orgname_1,
            r.Country_2, r.City_2, o2.Name_en + ' [' + o2.Country_name + ']' AS Orgname_2,
            r.Country_3, r.City_3, o3.Name_en + ' [' + o3.Country_name + ']' AS Orgname_3
            FROM rule_center_match r
            JOIN unified_org_names o1 ON r.Org_id_1 = o1.Unified_org_id
            LEFT JOIN unified_org_names o2 ON r.Org_id_2 = o2.Unified_org_id
            LEFT JOIN unified_org_names o3 ON r.Org_id_3 = o3.Unified_org_id
            WHERE r.R_c_m_id = :regel_id");
        $stmt->bindValue(':regel_id', (int) $regel_id, PDO::PARAM_INT);
        $stmt->execute();
        $rule = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$rule) {
            $errors[] = "Regeln hittades inte.";
        }

        $countryStmt = $dbh->query("SELECT Display_name FROM Country ORDER BY Display_name");
        $countries = $countryStmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (PDOException $e) {
        $errors[] = "Det gick inte att hämta regeln.";
    }
}
?>

<!DOCTYPE html>
<html lang="sv">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ändra regel centra</title>
    <link href="Site.css" rel="stylesheet">
    <link href="vendor/tom-select/tom-select.css" rel="stylesheet">
    <?php include("include_bibmet_kth.html"); ?>
    <script src="vendor/tom-select/tom-select.complete.min.js"></script>
    <script src="bibmet-selects.js"></script>
</head>

<body class="bibmet-body">
    <?php include('include_head_new.html'); ?>

    <main class="bibmet-main">
        <section class="bibmet-hero">
            <div class="bibmet-hero__row">
                <div>
                    <p class="bibmet-eyebrow">Adressrättningsregler</p>
                    <h1 class="bibmet-title">Ändra regel centra</h1>
                    <p class="bibmet-muted">Ändra fälten och spara eller provkör regeln.</p>
                </div>
                <div class="bibmet-action-group">
                    <a href="regel_centra.php" class="bibmet-button bibmet-button--primary">Till sökning</a>
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

        <?php if ($rule) : ?>
            <form action="aendra_regel_resultat_c.php" method="post" class="bibmet-panel">
                <div class="bibmet-panel__header">
                    <h2 class="bibmet-panel__title">Regel</h2>
                </div>

                <?php
                bibmet_render_hidden_field('Stad_nu', $rule['Find_city'] ?? '');
                bibmet_render_hidden_field('Org_nu', $rule['Find_org'] ?? '');
                bibmet_render_hidden_field('Delas_nu', $rule['Divide'] ?? '');
                bibmet_render_hidden_field('Org_1_nu', $rule['Orgname_1'] ?? '');
                bibmet_render_hidden_field('Land_1_nu', $rule['Country_1'] ?? '');
                bibmet_render_hidden_field('Stad_1_nu', $rule['City_1'] ?? '');
                bibmet_render_hidden_field('Org_2_nu', $rule['Orgname_2'] ?? '');
                bibmet_render_hidden_field('Land_2_nu', $rule['Country_2'] ?? '');
                bibmet_render_hidden_field('Stad_2_nu', $rule['City_2'] ?? '');
                bibmet_render_hidden_field('Org_3_nu', $rule['Orgname_3'] ?? '');
                bibmet_render_hidden_field('Land_3_nu', $rule['Country_3'] ?? '');
                bibmet_render_hidden_field('Stad_3_nu', $rule['City_3'] ?? '');
                ?>

                <div class="bibmet-panel__body">
                    <h3 class="bibmet-section-title">Sökfält</h3>
                    <div class="bibmet-form-grid bibmet-form-grid--compact">
                        <?php bibmet_render_country_select('Land', 'Land_ut_2', $rule['Find_country'] ?? '', $countries, true); ?>
                        <?php bibmet_render_text_field('Stad', 'Stad_till', $rule['Find_city'] ?? ''); ?>
                        <?php bibmet_render_text_field('Organisationsnamn', 'Org_till', $rule['Find_org'] ?? '', '', true); ?>
                    </div>

                    <h3 class="bibmet-section-title">Ändringsfält</h3>
                    <div class="bibmet-form-grid bibmet-form-grid--compact">
                        <label class="bibmet-field">
                            <span class="bibmet-field__label">Delas i</span>
                            <select class="bibmet-select" name="Delas_ut_2" required>
                                <?php foreach ([1, 2, 3] as $divide) : ?>
                                    <option value="<?php echo bibmet_h($divide); ?>"<?php echo bibmet_selected_attr($divide, $rule['Divide'] ?? ''); ?>><?php echo bibmet_h($divide); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                    </div>

                    <?php for ($i = 1; $i <= 3; $i++) : ?>
                        <section class="bibmet-panel bibmet-panel--subtle">
                            <h3 class="bibmet-panel__title">Organisation <?php echo bibmet_h($i); ?></h3>
                            <div class="bibmet-form-grid bibmet-form-grid--compact">
                                <?php bibmet_render_org_select('Annat organisationsnamn', 'Org_' . $i . '_ut_2', $rule['Orgname_' . $i] ?? '', $i === 1); ?>
                                <?php bibmet_render_country_select('Annat land', 'Land_' . $i . '_ut_2', $rule['Country_' . $i] ?? '', $countries); ?>
                                <?php bibmet_render_text_field('Annan stad', 'Stad_' . $i . '_till', $rule['City_' . $i] ?? ''); ?>
                            </div>
                        </section>
                    <?php endfor; ?>

                    <div class="bibmet-form-actions">
                        <div class="bibmet-action-group">
                            <button type="submit" name="spara" value="1" class="bibmet-button bibmet-button--primary">Spara regel</button>
                            <button type="submit" formaction="koer_regel_c.php" name="koer" value="1" class="bibmet-button bibmet-button--secondary">Provkör regel</button>
                        </div>
                    </div>
                </div>
            </form>
        <?php endif; ?>
    </main>
</body>

</html>
