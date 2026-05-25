<?php
require_once __DIR__ . '/sqlsrv_connect.php';

$dbh = bibmet_sqlsrv_connect_or_redirect();

function h($value)
{
    if ($value instanceof DateTimeInterface) {
        $value = $value->format("Y-m-d");
    }

    return htmlspecialchars((string) $value, ENT_QUOTES, "UTF-8");
}

$u_org_id = isset($_SESSION['u_org_id']) ? $_SESSION['u_org_id'] : "";
$_SESSION['u_org_id_ut'] = $u_org_id;

$username = isset($_SESSION['anv']) ? $_SESSION['anv'] : "";

$namn_l_till = isset($_POST['Namn_lok_till']) ? trim((string) $_POST['Namn_lok_till']) : "";
$namn_e_till = isset($_POST['Namn_eng_till']) ? trim((string) $_POST['Namn_eng_till']) : "";
$land_till = isset($_POST['Land_till']) ? trim((string) $_POST['Land_till']) : "";
$orgtyp_till = isset($_POST['Orgtyp_till']) ? trim((string) $_POST['Orgtyp_till']) : "";
$komm_till = isset($_POST['Komm_till']) ? trim((string) $_POST['Komm_till']) : "";
$rorid_till = isset($_POST['RORid_till']) ? trim((string) $_POST['RORid_till']) : "";
$rorid_sparad = $rorid_till;

if (preg_match('/ror\.org\/([^\/?#]+)/i', $rorid_sparad, $matches)) {
    $rorid_sparad = $matches[1];
}

$rorid_sparad = strtolower($rorid_sparad);

$messages = [];
$errors = [];
$updated = false;

if (!ctype_digit((string) $u_org_id) || (int) $u_org_id <= 0) {
    $errors[] = "Ogiltigt organisations-id.";
}

if (strlen($namn_l_till) == 0 && strlen($namn_e_till) == 0) {
    $errors[] = "Organisationsnamn måste anges.";
}

if ($land_till == 'Ange land' || $land_till == '') {
    $land_till = null;
}
if ($orgtyp_till == 'Ange organisationstyp' || $orgtyp_till == '') {
    $orgtyp_till = null;
}

if ($rorid_sparad !== "" && !preg_match('/^0[a-z0-9]{8}$/', $rorid_sparad)) {
    $errors[] = "ROR-id måste vara tomt eller anges som ett giltigt ROR-id, till exempel 05f950310 eller https://ror.org/05f950310.";
}

if (!$errors) {
    try {
        $org_typ_code = null;
        if ($orgtyp_till !== null && strlen($orgtyp_till) > 0) {
            $stmt = $dbh->prepare("SELECT Org_type_code FROM Organization_type WHERE Org_type_eng = :orgtyp");
            $stmt->bindValue(":orgtyp", $orgtyp_till, PDO::PARAM_STR);
            $stmt->execute();
            $org_typ_code = $stmt->fetchColumn();
        }

        $stmt = $dbh->prepare("
            UPDATE unified_org_names
            SET
                Name_local = :nameLocal,
                Name_en = :nameEnglish,
                Country_name = :countryName,
                Org_type_code = :orgTypeCode,
                Comment = :comment,
                User_id = :userId,
                Latest_date = GETDATE(),
                ROR_id = :rorId
            WHERE Unified_org_id = :orgId
        ");
        $stmt->bindValue(":nameLocal", $namn_l_till, PDO::PARAM_STR);
        $stmt->bindValue(":nameEnglish", $namn_e_till, PDO::PARAM_STR);
        $stmt->bindValue(":countryName", $land_till, $land_till === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(":orgTypeCode", $org_typ_code, $org_typ_code === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(":comment", $komm_till, PDO::PARAM_STR);
        $stmt->bindValue(":userId", $username, PDO::PARAM_STR);
        $stmt->bindValue(":rorId", $rorid_sparad, PDO::PARAM_STR);
        $stmt->bindValue(":orgId", (int) $u_org_id, PDO::PARAM_INT);
        $stmt->execute();

        $verifyStmt = $dbh->prepare("SELECT ROR_id FROM unified_org_names WHERE Unified_org_id = :orgId");
        $verifyStmt->bindValue(":orgId", (int) $u_org_id, PDO::PARAM_INT);
        $verifyStmt->execute();
        $verifiedRorId = $verifyStmt->fetchColumn();

        if ($verifiedRorId !== false) {
            $rorid_sparad = (string) $verifiedRorId;
        }

        $messages[] = "Organisationen är nu sparad.";
        $_SESSION['a_org_id'] = $u_org_id;
        $updated = true;
    } catch (PDOException $e) {
        $errors[] = "Fel vid ändring av organisationen. " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="sv">

<! Författare: Cecilia Wiklander>
<! Syfte: Adressrättnings-hantering>
<! Ändringar: >

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ändra organisationsnamn</title>
    <link href="Site.css" rel="stylesheet">
    <?php include("include_bibmet_kth.html"); ?>
</head>

<body class="bibmet-body">
    <?php include("include_head_new.html"); ?>

    <main class="bibmet-main bibmet-main--form">
        <section class="bibmet-hero">
            <div class="bibmet-hero__row">
                <div>
                    <p class="bibmet-eyebrow">Organisationsnamn</p>
                    <h1 class="bibmet-title">Ändra organisationsnamn</h1>
                    <p class="bibmet-muted">Resultat av uppdateringen.</p>
                </div>
                <div class="bibmet-action-group">
                    <a href="aendra_organisation.php" class="bibmet-button bibmet-button--secondary">Tillbaka</a>
                    <a href="organisationsnamn.php" class="bibmet-button bibmet-button--secondary">Till sökning</a>
                    <a href="adressmeny.php" class="bibmet-button bibmet-button--secondary">Till menyn</a>
                </div>
            </div>
        </section>

        <?php if ($messages) : ?>
            <section class="bibmet-panel">
                <div class="bibmet-panel__body">
                    <?php foreach ($messages as $message) : ?>
                        <p class="bibmet-muted"><?php echo h($message); ?></p>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <?php if ($errors) : ?>
            <div class="bibmet-alert" role="alert">
                <?php foreach ($errors as $error) : ?>
                    <p><?php echo h($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <section class="bibmet-panel">
            <div class="bibmet-panel__header">
                <h2 class="bibmet-panel__title">Sparade värden</h2>
            </div>

            <div class="bibmet-form-grid">
                <label class="bibmet-field">
                    <span class="bibmet-field__label">Orgid</span>
                    <input class="bibmet-input bibmet-input--short" type="text" value="<?php echo h($u_org_id); ?>" disabled>
                </label>

                <label class="bibmet-field">
                    <span class="bibmet-field__label">Lokalt namn</span>
                    <input class="bibmet-input" type="text" value="<?php echo h($namn_l_till); ?>" disabled>
                </label>

                <label class="bibmet-field">
                    <span class="bibmet-field__label">Engelskt namn</span>
                    <input class="bibmet-input" type="text" value="<?php echo h($namn_e_till); ?>" disabled>
                </label>

                <label class="bibmet-field">
                    <span class="bibmet-field__label">Land</span>
                    <input class="bibmet-input" type="text" value="<?php echo h($land_till ?? ""); ?>" disabled>
                </label>

                <label class="bibmet-field">
                    <span class="bibmet-field__label">Organisationstyp</span>
                    <input class="bibmet-input" type="text" value="<?php echo h($orgtyp_till ?? ""); ?>" disabled>
                </label>

                <label class="bibmet-field">
                    <span class="bibmet-field__label">Kommentar</span>
                    <input class="bibmet-input" type="text" value="<?php echo h($komm_till); ?>" disabled>
                </label>

                <label class="bibmet-field">
                    <span class="bibmet-field__label">ROR-id</span>
                    <input class="bibmet-input" type="text" value="<?php echo h($rorid_sparad); ?>" disabled>
                </label>
            </div>
        </section>
    </main>
</body>

</html>
