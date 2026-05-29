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

    function parse_org_label($label)
    {
        $label = trim((string) $label);
        $start = strrpos($label, '[');
        $end = strrpos($label, ']');

        if ($start === false || $end === false || $end <= $start) {
            return [$label, ""];
        }

        return [trim(substr($label, 0, $start)), trim(substr($label, $start + 1, $end - $start - 1))];
    }

    function find_org_id(PDO $dbh, $label)
    {
        [$name, $country] = parse_org_label($label);

        if ($name === "" || $label === "Ange organisation") {
            return null;
        }

        if ($country !== "") {
            $stmt = $dbh->prepare("SELECT Unified_org_id FROM Unified_org_names WHERE TRIM(Name_en) = TRIM(:name) AND Country_name = :country");
            $stmt->bindValue(':country', $country, PDO::PARAM_STR);
        } else {
            $stmt = $dbh->prepare("SELECT Unified_org_id FROM Unified_org_names WHERE TRIM(Name_en) = TRIM(:name)");
        }

        $stmt->bindValue(':name', $name, PDO::PARAM_STR);
        $stmt->execute();
        $orgId = $stmt->fetchColumn();

        return $orgId === false ? null : (int) $orgId;
    }

    function normalize_optional_value($value, $placeholder)
    {
        $value = trim((string) $value);
        return $value === "" || $value === $placeholder ? null : $value;
    }

    function bind_nullable(PDOStatement $stmt, $name, $value, $type = PDO::PARAM_STR)
    {
        if ($value === null || $value === "") {
            $stmt->bindValue($name, null, PDO::PARAM_NULL);
        } else {
            $stmt->bindValue($name, $value, $type);
        }
    }

    function insert_org_rule(PDO $dbh, array $data)
    {
        $isSqlite = $dbh->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite';
        $sql = $isSqlite
            ? "INSERT INTO Rule_org_match (
                R_o_m_id, Find_country, Country_code, Find_city, Find_org, Divide,
                Country_1, City_1, Org_id_1,
                Country_2, City_2, Org_id_2,
                Country_3, City_3, Org_id_3,
                User_id, Rule_date, Run_status, Valid_from, Valid_to
            ) VALUES (
                :R_o_m_id, :Find_country, :Country_code, :Find_city, :Find_org, :Divide,
                :Country_1, :City_1, :Org_id_1,
                :Country_2, :City_2, :Org_id_2,
                :Country_3, :City_3, :Org_id_3,
                :User_id, CURRENT_TIMESTAMP, 1, :Valid_from, :Valid_to
            )"
            : "INSERT INTO Rule_org_match (
                Find_country, Country_code, Find_city, Find_org, Divide,
                Country_1, City_1, Org_id_1,
                Country_2, City_2, Org_id_2,
                Country_3, City_3, Org_id_3,
                User_id, Rule_date, Run_status, Valid_from, Valid_to
            ) VALUES (
                :Find_country, :Country_code, :Find_city, :Find_org, :Divide,
                :Country_1, :City_1, :Org_id_1,
                :Country_2, :City_2, :Org_id_2,
                :Country_3, :City_3, :Org_id_3,
                :User_id, CURRENT_TIMESTAMP, 1, :Valid_from, :Valid_to
            )";

        $stmt = $dbh->prepare($sql);
        if ($isSqlite) {
            $idStmt = $dbh->query("SELECT COALESCE(MAX(R_o_m_id), 0) + 1 FROM Rule_org_match");
            $stmt->bindValue(':R_o_m_id', (int) $idStmt->fetchColumn(), PDO::PARAM_INT);
        }
        $stmt->bindValue(':Find_country', $data['Find_country'], PDO::PARAM_STR);
        $stmt->bindValue(':Country_code', $data['Country_code'], PDO::PARAM_STR);
        bind_nullable($stmt, ':Find_city', $data['Find_city']);
        $stmt->bindValue(':Find_org', $data['Find_org'], PDO::PARAM_STR);
        $stmt->bindValue(':Divide', (int) $data['Divide'], PDO::PARAM_INT);
        bind_nullable($stmt, ':Country_1', $data['Country_1']);
        bind_nullable($stmt, ':City_1', $data['City_1']);
        bind_nullable($stmt, ':Org_id_1', $data['Org_id_1'], PDO::PARAM_INT);
        bind_nullable($stmt, ':Country_2', $data['Country_2']);
        bind_nullable($stmt, ':City_2', $data['City_2']);
        bind_nullable($stmt, ':Org_id_2', $data['Org_id_2'], PDO::PARAM_INT);
        bind_nullable($stmt, ':Country_3', $data['Country_3']);
        bind_nullable($stmt, ':City_3', $data['City_3']);
        bind_nullable($stmt, ':Org_id_3', $data['Org_id_3'], PDO::PARAM_INT);
        $stmt->bindValue(':User_id', $data['User_id'], PDO::PARAM_STR);
        bind_nullable($stmt, ':Valid_from', $data['Valid_from']);
        bind_nullable($stmt, ':Valid_to', $data['Valid_to']);
        $stmt->execute();
    }

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

        if ($land_s === 'Ange land' || $land_s === "") {
            $errors[] = 'Land måste anges som sökfält.';
        }

        if ($org_s_1 === "") {
            $errors[] = 'Organisation måste anges som sökfält.';
        }

        if (!in_array($delas, ['1', '2', '3'], true)) {
            $errors[] = 'Antalet i Delas kan vara mellan 1 och 3.';
        }

        if ($org_1 === 'Ange organisation') {
            $errors[] = 'Organisation 1 måste anges som ändringsfält.';
        }

        if ($delas === '1' && ($org_2 !== 'Ange organisation' || $org_3 !== 'Ange organisation')) {
            $errors[] = 'Antalet i Delas stämmer inte med antal angivna organisationer.';
        }
        if ($delas === '2' && ($org_2 === 'Ange organisation' || $org_3 !== 'Ange organisation')) {
            $errors[] = 'Antalet i Delas stämmer inte med antal angivna organisationer.';
        }
        if ($delas === '3' && ($org_2 === 'Ange organisation' || $org_3 === 'Ange organisation')) {
            $errors[] = 'Antalet i Delas stämmer inte med antal angivna organisationer.';
        }

        foreach (['Gäller från' => $fr, 'Gäller till' => $ti] as $label => $dateValue) {
            if ($dateValue !== "" && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateValue)) {
                $errors[] = $label . ' måste anges som ÅÅÅÅ-MM-DD.';
            }
        }

        $n_regel_o = isset($_SESSION['n_regel_o']) ? $_SESSION['n_regel_o'] : "";
        $regel_o_fingerprint = hash('sha256', json_encode([
            $land_s, $stad_s, $org_s_1, $org_s_2, $org_s_3, $delas,
            $land_1, $land_2, $land_3, $stad_1, $stad_2, $stad_3,
            $org_1, $org_2, $org_3, $fr, $ti
        ], JSON_UNESCAPED_UNICODE));

        if (!$errors && $n_regel_o === $regel_o_fingerprint) {
            $errors[] = 'Regeln har redan sparats. Ändra något fält innan du sparar igen.';
        }

        if (!$errors) {
            try {
                $countryStmt = $dbh->prepare("SELECT Country_code FROM Country WHERE Display_name = :land");
                $countryStmt->bindValue(':land', $land_s, PDO::PARAM_STR);
                $countryStmt->execute();
                $country_code = $countryStmt->fetchColumn();

                if ($country_code === false) {
                    $errors[] = 'Ogiltigt land.';
                }

                $org_id_1 = find_org_id($dbh, $org_1);
                $org_id_2 = $delas > 1 ? find_org_id($dbh, $org_2) : null;
                $org_id_3 = $delas > 2 ? find_org_id($dbh, $org_3) : null;

                if ($org_id_1 === null || ($delas > 1 && $org_id_2 === null) || ($delas > 2 && $org_id_3 === null)) {
                    $errors[] = 'En eller flera valda organisationer kunde inte hittas.';
                }

                if (!$errors) {
                    $searchOrgs = array_values(array_filter([$org_s_1, $org_s_2, $org_s_3], function ($value) {
                        return trim((string) $value) !== '';
                    }));

                    $ruleData = [
                        'Find_country' => $land_s,
                        'Country_code' => $country_code,
                        'Find_city' => $stad_s,
                        'Divide' => (int) $delas,
                        'Country_1' => normalize_optional_value($land_1, 'Ange land'),
                        'City_1' => $stad_1,
                        'Org_id_1' => $org_id_1,
                        'Country_2' => $delas > 1 ? normalize_optional_value($land_2, 'Ange land') : null,
                        'City_2' => $delas > 1 ? $stad_2 : null,
                        'Org_id_2' => $delas > 1 ? $org_id_2 : null,
                        'Country_3' => $delas > 2 ? normalize_optional_value($land_3, 'Ange land') : null,
                        'City_3' => $delas > 2 ? $stad_3 : null,
                        'Org_id_3' => $delas > 2 ? $org_id_3 : null,
                        'User_id' => $username,
                        'Valid_from' => $fr,
                        'Valid_to' => $ti,
                    ];

                    $dbh->beginTransaction();
                    foreach ($searchOrgs as $searchOrg) {
                        $ruleData['Find_org'] = $searchOrg;
                        insert_org_rule($dbh, $ruleData);
                    }
                    $dbh->commit();

                    $_SESSION['n_regel_o'] = $regel_o_fingerprint;
                    $messages[] = 'Regeln är sparad.';

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
            } catch (PDOException $e) {
                if ($dbh->inTransaction()) {
                    $dbh->rollBack();
                }
                $errors[] = 'Fel vid sparande av regeln. ' . $e->getMessage();
            }
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