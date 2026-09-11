<?php
require_once __DIR__ . '/sqlsrv_connect.php';
require_once __DIR__ . '/bibmet_ui.php';

$dbh = bibmet_sqlsrv_connect_or_redirect();

$errors = [];
$messages = [];
$countryOptions = [];
$orgTypeOptions = [];

$land = bibmet_request_value('Land');
$stad = bibmet_request_value('Stad');
$org1 = bibmet_request_value('Org_1');
$org2 = bibmet_request_value('Org_2');
$orgEj = bibmet_request_value('Org_ej');
$orgtyp = bibmet_request_value('Orgtyp');
$land1 = bibmet_request_value('Land_1');
$stad1 = bibmet_request_value('Stad_1');

function ny_regel_o_typ_normalize_select($value, $placeholder)
{
    $value = trim((string) $value);
    return $value === $placeholder ? '' : $value;
}

function ny_regel_o_typ_lookup_country_code(PDO $dbh, $countryName)
{
    if ($countryName === '') {
        return null;
    }

    $stmt = $dbh->prepare('SELECT Country_code FROM Country WHERE Display_name = :countryName');
    $stmt->bindValue(':countryName', $countryName, PDO::PARAM_STR);
    $stmt->execute();
    $countryCode = $stmt->fetchColumn();

    return $countryCode === false ? null : $countryCode;
}

function ny_regel_o_typ_lookup_org_type_code(PDO $dbh, $orgTypeName)
{
    $stmt = $dbh->prepare('SELECT Org_type_code FROM Organization_type WHERE Org_type_eng = :orgTypeName');
    $stmt->bindValue(':orgTypeName', $orgTypeName, PDO::PARAM_STR);
    $stmt->execute();
    $orgTypeCode = $stmt->fetchColumn();

    return $orgTypeCode === false ? null : $orgTypeCode;
}

function ny_regel_o_typ_bind_nullable(PDOStatement $stmt, $name, $value)
{
    if ($value === null || $value === '') {
        $stmt->bindValue($name, null, PDO::PARAM_NULL);
    } else {
        $stmt->bindValue($name, $value, PDO::PARAM_STR);
    }
}

try {
    $stmt = $dbh->query('SELECT Display_name FROM Country ORDER BY Display_name');
    $countryOptions = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
} catch (PDOException $e) {
    $errors[] = 'Det gick inte att hämta landlistan.';
}

try {
    $stmt = $dbh->query('SELECT Org_type_eng FROM Organization_type ORDER BY Org_type_eng');
    $orgTypeOptions = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
} catch (PDOException $e) {
    $errors[] = 'Det gick inte att hämta organisationstyper.';
}

if (isset($_POST['spara'])) {
    $land = ny_regel_o_typ_normalize_select($land, 'Ange land');
    $land1 = ny_regel_o_typ_normalize_select($land1, 'Ange land');
    $orgtyp = ny_regel_o_typ_normalize_select($orgtyp, 'Ange organisationstyp');

    if ($org1 === '') {
        $errors[] = 'Organisation, sträng 1 måste anges som sökfält.';
    }

    if ($orgtyp === '') {
        $errors[] = 'Organisationstyp måste anges som ändringsfält.';
    }

    $countryCode = null;
    if (!$errors && $land !== '') {
        $countryCode = ny_regel_o_typ_lookup_country_code($dbh, $land);
        if ($countryCode === null) {
            $errors[] = 'Valt sökland finns inte i landlistan.';
        }
    }

    $orgTypeCode = null;
    if (!$errors) {
        $orgTypeCode = ny_regel_o_typ_lookup_org_type_code($dbh, $orgtyp);
        if ($orgTypeCode === null) {
            $errors[] = 'Vald organisationstyp finns inte i listan.';
        }
    }

    $fingerprint = hash('sha256', implode('|', [$land, $stad, $org1, $org2, $orgEj, $land1, $stad1, $orgtyp]));
    $previousFingerprint = isset($_SESSION['n_regel_o_typ']) ? (string) $_SESSION['n_regel_o_typ'] : '';

    if (!$errors && $previousFingerprint === $fingerprint) {
        $messages[] = 'Regeln är redan sparad.';
    }

    if (!$errors && $previousFingerprint !== $fingerprint) {
        try {
            $stmt = $dbh->prepare('
                INSERT INTO Rule_org_type_match (
                    Find_country,
                    Country_code,
                    Find_city,
                    Find_org_1,
                    Find_org_2,
                    Find_org_not,
                    Country,
                    City,
                    Org_type_code,
                    User_id,
                    Rule_date,
                    Run_status
                ) VALUES (
                    :findCountry,
                    :countryCode,
                    :findCity,
                    :findOrg1,
                    :findOrg2,
                    :findOrgNot,
                    :country,
                    :city,
                    :orgTypeCode,
                    :userId,
                    GETDATE(),
                    1
                )
            ');
            ny_regel_o_typ_bind_nullable($stmt, ':findCountry', $land);
            ny_regel_o_typ_bind_nullable($stmt, ':countryCode', $countryCode);
            ny_regel_o_typ_bind_nullable($stmt, ':findCity', $stad);
            ny_regel_o_typ_bind_nullable($stmt, ':findOrg1', $org1);
            ny_regel_o_typ_bind_nullable($stmt, ':findOrg2', $org2);
            ny_regel_o_typ_bind_nullable($stmt, ':findOrgNot', $orgEj);
            ny_regel_o_typ_bind_nullable($stmt, ':country', $land1);
            ny_regel_o_typ_bind_nullable($stmt, ':city', $stad1);
            ny_regel_o_typ_bind_nullable($stmt, ':orgTypeCode', $orgTypeCode);
            $stmt->bindValue(':userId', isset($_SESSION['anv']) ? (string) $_SESSION['anv'] : '', PDO::PARAM_STR);
            $stmt->execute();

            $_SESSION['n_regel_o_typ'] = $fingerprint;
            $messages[] = 'Regeln är sparad.';

            $stad = '';
            $org1 = '';
            $org2 = '';
            $orgEj = '';
            $stad1 = '';
        } catch (PDOException $e) {
            $errors[] = 'Fel vid sparande av regeln.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="sv">

<! Författare: Cecilia Wiklander>
<! Syfte: Adressrättnings-hantering>
<! Ändringar:>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ny regel organisationstyp</title>
    <link href="Site.css" rel="stylesheet">
    <link href="vendor/tom-select/tom-select.css" rel="stylesheet">
    <?php include("include_bibmet_kth.html"); ?>
    <script src="vendor/tom-select/tom-select.complete.min.js"></script>
    <script src="bibmet-selects.js"></script>
</head>

<body class="bibmet-body">
    <?php include("include_head_new.html"); ?>

    <main class="bibmet-main bibmet-main--form">
        <section class="bibmet-hero">
            <div class="bibmet-hero__row">
                <div>
                    <p class="bibmet-eyebrow">Adressrättningsregler</p>
                    <h1 class="bibmet-title">Ny regel organisationstyp</h1>
                    <p class="bibmet-muted">
                        Skapa en regel som klassificerar matchande adresser med en organisationstyp.
                    </p>
                </div>
                <div class="bibmet-action-group">
                    <a href="regel_organisation_typ.php" class="bibmet-button bibmet-button--secondary">Till sökning</a>
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

        <form action="ny_regel_o_typ.php" method="post" class="bibmet-panel">
            <div class="bibmet-panel__header">
                <h2 class="bibmet-panel__title">Regeluppgifter</h2>
                <p class="bibmet-muted">Fält markerade med # är obligatoriska.</p>
            </div>

            <div class="bibmet-panel__body">
                <h3 class="bibmet-panel__title">Sökfält</h3>
                <div class="bibmet-form-grid bibmet-form-grid--compact" style="margin-top: 16px;">
                    <label class="bibmet-field">
                        <span class="bibmet-field__label">Land</span>
                        <select id="id_s_land" name="Land" class="bibmet-select js-bibmet-select">
                            <option value="">Ange land</option>
                            <?php foreach ($countryOptions as $country) : ?>
                                <option value="<?php echo bibmet_h($country); ?>"<?php echo bibmet_selected_attr($country, $land); ?>><?php echo bibmet_h($country); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>

                    <label class="bibmet-field">
                        <span class="bibmet-field__label">Stad</span>
                        <input type="text" name="Stad" id="id_s_stad" value="<?php echo bibmet_h($stad); ?>" class="bibmet-input">
                    </label>

                    <label class="bibmet-field">
                        <span class="bibmet-field__label">Organisation, sträng 1 #</span>
                        <input type="text" name="Org_1" id="id_s_org_1" value="<?php echo bibmet_h($org1); ?>" class="bibmet-input" required>
                    </label>

                    <label class="bibmet-field">
                        <span class="bibmet-field__label">Organisation, sträng 2</span>
                        <input type="text" name="Org_2" id="id_s_org_2" value="<?php echo bibmet_h($org2); ?>" class="bibmet-input">
                    </label>

                    <label class="bibmet-field">
                        <span class="bibmet-field__label">Organisation, sträng ej</span>
                        <input type="text" name="Org_ej" id="id_s_org_ej" value="<?php echo bibmet_h($orgEj); ?>" class="bibmet-input">
                        <span class="bibmet-field__hint">Adresser som innehåller denna sträng undantas.</span>
                    </label>
                </div>

                <h3 class="bibmet-panel__title" style="margin-top: 24px;">Ändringsfält</h3>
                <div class="bibmet-form-grid bibmet-form-grid--compact" style="margin-top: 16px;">
                    <label class="bibmet-field">
                        <span class="bibmet-field__label">Organisationstyp #</span>
                        <select id="id_s_orgtyp" name="Orgtyp" class="bibmet-select js-bibmet-select" required>
                            <option value="">Ange organisationstyp</option>
                            <?php foreach ($orgTypeOptions as $option) : ?>
                                <option value="<?php echo bibmet_h($option); ?>"<?php echo bibmet_selected_attr($option, $orgtyp); ?>><?php echo bibmet_h($option); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>

                    <label class="bibmet-field">
                        <span class="bibmet-field__label">Annat land</span>
                        <select id="id_h_land_1" name="Land_1" class="bibmet-select js-bibmet-select">
                            <option value="">Ange land</option>
                            <?php foreach ($countryOptions as $country) : ?>
                                <option value="<?php echo bibmet_h($country); ?>"<?php echo bibmet_selected_attr($country, $land1); ?>><?php echo bibmet_h($country); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>

                    <label class="bibmet-field">
                        <span class="bibmet-field__label">Annan stad</span>
                        <input type="text" name="Stad_1" id="h_id_stad_1" value="<?php echo bibmet_h($stad1); ?>" class="bibmet-input">
                    </label>
                </div>
            </div>

            <div class="bibmet-form-actions">
                <button type="submit" name="spara" class="bibmet-button bibmet-button--primary">Spara regel</button>
                <div class="bibmet-action-group">
                    <a href="regel_organisation_typ.php" class="bibmet-button bibmet-button--secondary">Avbryt</a>
                </div>
            </div>
        </form>
    </main>
</body>

</html>
