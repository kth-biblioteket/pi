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

function selected_attr($optionValue, $currentValue)
{
    return (string) $optionValue === (string) $currentValue ? ' selected' : '';
}

$errors = [];
$u_org_id = isset($_GET["Unified_org_id"]) ? trim((string) $_GET["Unified_org_id"]) : "";

if ($u_org_id !== "") {
    $_SESSION['u_org_id'] = $u_org_id;
} else {
    $u_org_id = isset($_SESSION['u_org_id_ut']) ? (string) $_SESSION['u_org_id_ut'] : (isset($_SESSION['u_org_id']) ? (string) $_SESSION['u_org_id'] : "");
}

$namn_lok = "";
$namn_eng = "";
$land = "";
$orgtyp = "";
$org_typ_eng = "";
$komm = "";
$rorid = "";
$countries = [];
$organizationTypes = [];

if (!ctype_digit($u_org_id) || (int) $u_org_id <= 0) {
    $errors[] = "Ogiltigt organisations-id.";
} else {
    try {
        $stmt = $dbh->prepare("SELECT Name_local, Name_en, Country_name, Org_type_code, Comment, ROR_id FROM unified_org_names WHERE Unified_org_id = :id");
        $stmt->bindValue(":id", (int) $u_org_id, PDO::PARAM_INT);
        $stmt->execute();
        $organisation = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($organisation) {
            $namn_lok = $organisation['Name_local'];
            $namn_eng = $organisation['Name_en'];
            $land = $organisation['Country_name'];
            $orgtyp = $organisation['Org_type_code'];
            $komm = $organisation['Comment'];
            $rorid = $organisation['ROR_id'];
        } else {
            $errors[] = "Organisationen kunde inte hittas.";
        }

        $stmt = $dbh->query("SELECT Display_name FROM Country ORDER BY Display_name");
        $countries = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $stmt = $dbh->query("SELECT Org_type_eng FROM Organization_type ORDER BY Org_type_eng");
        $organizationTypes = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if (strlen((string) $orgtyp) > 0) {
            $stmt = $dbh->prepare("SELECT Org_type_eng FROM Organization_type WHERE Org_type_code = :orgtyp");
            $stmt->bindValue(":orgtyp", $orgtyp, PDO::PARAM_STR);
            $stmt->execute();
            $org_typ_eng = (string) $stmt->fetchColumn();
        }
    } catch (PDOException $e) {
        $errors[] = "Det gick inte att hämta organisationen. " . $e->getMessage();
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
                    <p class="bibmet-eyebrow">Organisationsnamn</p>
                    <h1 class="bibmet-title">Ändra organisationsnamn</h1>
                    <p class="bibmet-muted">Uppdatera namn, land, organisationstyp, kommentar och ROR-id utan att tappa befintliga värden.</p>
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
                    <p><?php echo h($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (!$errors) : ?>
            <form action="aendra_organisation_resultat.php" method="post">
                <section class="bibmet-panel">
                    <div class="bibmet-panel__header">
                        <h2 class="bibmet-panel__title">Uppgifter</h2>
                        <p class="bibmet-muted">Fälten är förifyllda med nuvarande värden. Ändra bara det som ska uppdateras.</p>
                    </div>

                    <div class="bibmet-form-grid">
                        <label class="bibmet-field">
                            <span class="bibmet-field__label">Orgid</span>
                            <input class="bibmet-input bibmet-input--short" type="text" value="<?php echo h($u_org_id); ?>" disabled>
                        </label>

                        <label class="bibmet-field">
                            <span class="bibmet-field__label">Lokalt namn</span>
                            <input type="hidden" name="Namn_lok_nu" value="<?php echo h($namn_lok); ?>">
                            <input class="bibmet-input" type="text" name="Namn_lok_till" id="id_namn_lok" value="<?php echo h($namn_lok); ?>">
                            <span class="bibmet-field__hint">Nuvarande värde är förifyllt.</span>
                        </label>

                        <label class="bibmet-field">
                            <span class="bibmet-field__label">Engelskt namn</span>
                            <input type="hidden" name="Namn_eng_nu" value="<?php echo h($namn_eng); ?>">
                            <input class="bibmet-input" type="text" name="Namn_eng_till" id="id_namn_eng" value="<?php echo h($namn_eng); ?>">
                            <span class="bibmet-field__hint">Nuvarande värde är förifyllt.</span>
                        </label>

                        <label class="bibmet-field">
                            <span class="bibmet-field__label">Land</span>
                            <input type="hidden" name="Land_nu" value="<?php echo h($land); ?>">
                            <select class="bibmet-select js-bibmet-select" id="id_s_land" name="Land_till">
                                <option value="">Ange land</option>
                                <?php foreach ($countries as $country) : ?>
                                    <option value="<?php echo h($country); ?>"<?php echo selected_attr($country, $land); ?>><?php echo h($country); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <span class="bibmet-field__hint">Nuvarande land är förvalt.</span>
                        </label>

                        <label class="bibmet-field">
                            <span class="bibmet-field__label">Organisationstyp</span>
                            <input type="hidden" name="Orgtyp_nu" value="<?php echo h($org_typ_eng); ?>">
                            <select class="bibmet-select js-bibmet-select" id="id_orgtyp" name="Orgtyp_till">
                                <option value="">Ange organisationstyp</option>
                                <?php foreach ($organizationTypes as $organizationType) : ?>
                                    <option value="<?php echo h($organizationType); ?>"<?php echo selected_attr($organizationType, $org_typ_eng); ?>><?php echo h($organizationType); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <span class="bibmet-field__hint">Nuvarande organisationstyp är förvald.</span>
                        </label>

                        <label class="bibmet-field">
                            <span class="bibmet-field__label">Kommentar</span>
                            <input type="hidden" name="Komm_nu" value="<?php echo h($komm); ?>">
                            <input class="bibmet-input" type="text" name="Komm_till" id="id_komm" value="<?php echo h($komm); ?>">
                            <span class="bibmet-field__hint">Nuvarande värde är förifyllt.</span>
                        </label>

                        <label class="bibmet-field">
                            <span class="bibmet-field__label">ROR-id</span>
                            <input type="hidden" name="RORid_nu" value="<?php echo h($rorid); ?>">
                            <input class="bibmet-input" type="text" name="RORid_till" id="id_rorid" value="<?php echo h($rorid); ?>">
                            <span class="bibmet-field__hint">Nuvarande värde är förifyllt.</span>
                        </label>
                    </div>

                    <div class="bibmet-form-actions">
                        <div class="bibmet-action-group">
                            <input class="bibmet-button bibmet-button--primary" type="submit" name="spara" value="Spara organisation">
                            <a class="bibmet-button bibmet-button--secondary" href="organisationsnamn.php">Avbryt</a>
                        </div>
                    </div>
                </section>
            </form>
        <?php endif; ?>
    </main>
</body>

</html>
