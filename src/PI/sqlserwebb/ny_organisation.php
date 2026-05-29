<?php
require_once __DIR__ . '/sqlsrv_connect.php';
require_once __DIR__ . '/bibmet_ui.php';

$dbh = bibmet_sqlsrv_connect_or_redirect();

$errors = [];
$messages = [];
$countries = [];
$organizationTypes = [];

$namn_l = "";
$namn_e = "";
$land = "";
$orgtyp = "";
$komm = "";
$rorid = "";

try {
    $stmt = $dbh->query("SELECT Display_name FROM country ORDER BY Display_name");
    $countries = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $stmt = $dbh->query("SELECT Org_type_eng FROM organization_type ORDER BY Org_type_eng");
    $organizationTypes = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $errors[] = "Det gick inte att hämta listorna. " . $e->getMessage();
}

if (isset($_POST['spara'])) {
    $namn_l = isset($_POST['Namn_lok']) ? trim((string) $_POST['Namn_lok']) : "";
    $namn_e = isset($_POST['Namn_eng']) ? trim((string) $_POST['Namn_eng']) : "";
    $land = isset($_POST['Land']) ? trim((string) $_POST['Land']) : "";
    $orgtyp = isset($_POST['Orgtyp']) ? trim((string) $_POST['Orgtyp']) : "";
    $komm = isset($_POST['Komm']) ? trim((string) $_POST['Komm']) : "";
    $rorid = isset($_POST['RORid']) ? trim((string) $_POST['RORid']) : "";

    if ($namn_l === "" && $namn_e === "") {
        $errors[] = "Organisationsnamn måste anges.";
    }

    if ($namn_e === "") {
        $errors[] = "Engelskt organisationsnamn måste anges.";
    }

    if ($orgtyp === "") {
        $errors[] = "Organisationstyp måste anges.";
    }

    if ($rorid !== "") {
        $rorid = preg_replace('/^https?:\/\/ror\.org\//i', '', $rorid);
        $rorid = trim($rorid, "/ \t\n\r\0\x0B");
        if (!preg_match('/^0[a-z0-9]{8}$/', $rorid)) {
            $errors[] = "ROR-id måste vara tomt eller anges som ett giltigt ROR-id, till exempel 05f950310 eller https://ror.org/05f950310.";
        }
    }

    if (!$errors) {
        try {
            $typeStmt = $dbh->prepare("SELECT Org_type_code FROM organization_type WHERE Org_type_eng = :orgtyp");
            $typeStmt->bindValue(':orgtyp', $orgtyp, PDO::PARAM_STR);
            $typeStmt->execute();
            $org_type_code = $typeStmt->fetchColumn();

            if ($org_type_code === false) {
                $errors[] = "Ogiltig organisationstyp.";
            } else {
                $dbh->beginTransaction();

                $idStmt = $dbh->query("SELECT COALESCE(MAX(Unified_org_id), 0) + 1 AS Unified_org_id FROM Unified_org_names");
                $unif_org_id = (int) $idStmt->fetchColumn();

                $insertSql = "INSERT INTO Unified_org_names
                    (Unified_org_id, Name_local, Name_en, Country_name, Org_type_code, Comment, User_id, Latest_date, ROR_id)
                    VALUES
                    (:org_id, :name_local, :name_en, :country_name, :org_type_code, :comment, :user_id, CURRENT_TIMESTAMP, :ror_id)";
                $insertStmt = $dbh->prepare($insertSql);
                $insertStmt->bindValue(':org_id', $unif_org_id, PDO::PARAM_INT);
                $insertStmt->bindValue(':name_local', $namn_l, PDO::PARAM_STR);
                $insertStmt->bindValue(':name_en', $namn_e, PDO::PARAM_STR);
                $insertStmt->bindValue(':country_name', $land === "" ? null : $land, $land === "" ? PDO::PARAM_NULL : PDO::PARAM_STR);
                $insertStmt->bindValue(':org_type_code', $org_type_code, PDO::PARAM_STR);
                $insertStmt->bindValue(':comment', $komm, PDO::PARAM_STR);
                $insertStmt->bindValue(':user_id', isset($_SESSION['anv']) ? $_SESSION['anv'] : '', PDO::PARAM_STR);
                $insertStmt->bindValue(':ror_id', $rorid === "" ? null : $rorid, $rorid === "" ? PDO::PARAM_NULL : PDO::PARAM_STR);
                $insertStmt->execute();

                $dbh->commit();
                $messages[] = "Organisationen är sparad med org-id " . $unif_org_id . ".";

                $namn_l = "";
                $namn_e = "";
                $land = "";
                $orgtyp = "";
                $komm = "";
                $rorid = "";
            }
        } catch (PDOException $e) {
            if ($dbh->inTransaction()) {
                $dbh->rollBack();
            }
            $errors[] = "Fel vid sparande av organisationen. " . $e->getMessage();
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
    <title>Ny organisation</title>
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
                    <p class="bibmet-eyebrow">Organisationsnamn</p>
                    <h1 class="bibmet-title">Ny organisation</h1>
                    <p class="bibmet-muted">Skapa ett nytt organisationsnamn. Fält markerade som obligatoriska måste fyllas i.</p>
                </div>
                <div class="bibmet-action-group">
                    <a href="organisationsnamn.php" class="bibmet-button bibmet-button--secondary">Till sökning</a>
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

        <form action="ny_organisation.php" method="post" class="bibmet-panel">
            <div class="bibmet-panel__header">
                <h2 class="bibmet-panel__title">Organisationsuppgifter</h2>
            </div>

            <div class="bibmet-form-grid bibmet-form-grid--narrow">
                <label class="bibmet-field">
                    <span class="bibmet-field__label">Lokalt namn</span>
                    <input class="bibmet-input" type="text" name="Namn_lok" value="<?php echo bibmet_h($namn_l); ?>">
                    <span class="bibmet-field__hint">Ange lokalt namn om det finns.</span>
                </label>

                <label class="bibmet-field">
                    <span class="bibmet-field__label">Engelskt namn *</span>
                    <input class="bibmet-input" type="text" name="Namn_eng" value="<?php echo bibmet_h($namn_e); ?>" required>
                </label>

                <label class="bibmet-field">
                    <span class="bibmet-field__label">Land</span>
                    <select class="bibmet-select js-bibmet-select" id="id_s_land" name="Land">
                        <option value="">Ange land</option>
                        <?php foreach ($countries as $country) : ?>
                            <option value="<?php echo bibmet_h($country); ?>"<?php echo bibmet_selected_attr($country, $land); ?>><?php echo bibmet_h($country); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <label class="bibmet-field">
                    <span class="bibmet-field__label">Organisationstyp *</span>
                    <select class="bibmet-select js-bibmet-select" id="id_s_orgtyp" name="Orgtyp" required>
                        <option value="">Ange organisationstyp</option>
                        <?php foreach ($organizationTypes as $organizationType) : ?>
                            <option value="<?php echo bibmet_h($organizationType); ?>"<?php echo bibmet_selected_attr($organizationType, $orgtyp); ?>><?php echo bibmet_h($organizationType); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <label class="bibmet-field">
                    <span class="bibmet-field__label">Kommentar</span>
                    <input class="bibmet-input" type="text" name="Komm" value="<?php echo bibmet_h($komm); ?>">
                </label>

                <label class="bibmet-field">
                    <span class="bibmet-field__label">ROR-id</span>
                    <input class="bibmet-input" type="text" name="RORid" value="<?php echo bibmet_h($rorid); ?>" placeholder="05f950310 eller https://ror.org/05f950310">
                    <span class="bibmet-field__hint">Lämna tomt om ROR-id saknas.</span>
                </label>
            </div>

            <div class="bibmet-form-actions">
                <div class="bibmet-action-group">
                    <input type="submit" name="spara" value="Spara organisation" class="bibmet-button bibmet-button--primary">
                    <a href="organisationsnamn.php" class="bibmet-button bibmet-button--secondary">Avbryt</a>
                </div>
            </div>
        </form>
    </main>
</body>

</html>
