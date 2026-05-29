<?php
require_once __DIR__ . '/sqlsrv_connect.php';
require_once __DIR__ . '/bibmet_ui.php';

$dbh = bibmet_sqlsrv_connect_or_redirect();

$errors = [];
$countryOptions = "";

try {
    $stmt = $dbh->query("SELECT Display_name FROM country ORDER BY Display_name");

    foreach ($stmt as $row) {
        $displayName = bibmet_h($row["Display_name"]);
        $countryOptions .= "\n<option value=\"{$displayName}\">{$displayName}</option>";
    }
} catch (PDOException $e) {
    $errors[] = "Det gick inte att hämta landlistan. " . $e->getMessage();
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
    <title>Regler full adress</title>
    <link href="Site_utan_storlek.css" rel="stylesheet">
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
                    <h1 class="bibmet-title">Regler full adress</h1>
                    <p class="bibmet-muted">Sök, skapa och granska regler för fullständig adressmatchning.</p>
                </div>
                <a href="adressmeny.php" class="bibmet-button bibmet-button--secondary">Till menyn</a>
            </div>
        </section>

        <?php if ($errors) : ?>
            <div class="bibmet-alert" role="alert">
                <?php foreach ($errors as $error) : ?>
                    <p><?php echo bibmet_h($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form action="visa_regler_f_a.php" method="get" class="bibmet-panel">
            <div class="bibmet-panel__header">
                <h2 class="bibmet-panel__title">Sökurval</h2>
                <p class="bibmet-muted">Ange ett eller flera fält för att hitta fulladressregler.</p>
            </div>

            <div class="bibmet-form-grid">
                <label class="bibmet-field">
                    <span class="bibmet-field__label">Regelid</span>
                    <input type="text" name="Regelid" class="bibmet-input bibmet-input--short" inputmode="numeric">
                </label>

                <label class="bibmet-field">
                    <span class="bibmet-field__label">Land</span>
                    <select id="id_s_land" name="Land" class="bibmet-select js-bibmet-select">
                        <option value="">Ange land</option>
                        <?php echo $countryOptions; ?>
                    </select>
                </label>

                <label class="bibmet-field">
                    <span class="bibmet-field__label">Stad</span>
                    <input type="text" name="Stad" class="bibmet-input">
                </label>

                <label class="bibmet-field">
                    <span class="bibmet-field__label">Söksträng 1</span>
                    <input type="text" name="Org_str_1" class="bibmet-input">
                </label>

                <label class="bibmet-field">
                    <span class="bibmet-field__label">Söksträng 2</span>
                    <input type="text" name="Org_str_2" class="bibmet-input">
                </label>

                <label class="bibmet-field">
                    <span class="bibmet-field__label">Söksträng 3</span>
                    <input type="text" name="Org_str_3" class="bibmet-input">
                </label>

                <label class="bibmet-field">
                    <span class="bibmet-field__label">Söksträng ej 1</span>
                    <input type="text" name="Org_str_not_1" class="bibmet-input">
                </label>

                <label class="bibmet-field">
                    <span class="bibmet-field__label">Söksträng ej 2</span>
                    <input type="text" name="Org_str_not_2" class="bibmet-input">
                </label>
            </div>

            <div class="bibmet-form-actions">
                <button type="submit" name="soek" class="bibmet-button bibmet-button--primary">Sök regel</button>
                <div class="bibmet-action-group">
                    <button type="submit" formaction="ny_regel_f_a.php" name="test" class="bibmet-button bibmet-button--secondary">Ny regel</button>
                    <button type="submit" formaction="regler_f_a_utan.php" name="utan" class="bibmet-button bibmet-button--secondary">Regel utan träff</button>
                </div>
            </div>
        </form>
    </main>
</body>

</html>
