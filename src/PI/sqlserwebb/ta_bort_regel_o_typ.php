<?php
require_once __DIR__ . '/sqlsrv_connect.php';
require_once __DIR__ . '/bibmet_ui.php';

$dbh = bibmet_sqlsrv_connect_or_redirect();

$regel_id = isset($_GET["Regel_id"]) ? (string) $_GET["Regel_id"] : "";
$errors = [];
$rule = null;

if (!ctype_digit($regel_id) || (int) $regel_id <= 0) {
    $errors[] = "Ogiltigt regel-id.";
} else {
    $_SESSION['regel_id'] = $regel_id;

    try {
        $sql = "SELECT R_o_t_m_id, Find_country, Country_code, Find_city, Find_org_1, Find_org_2,
            Find_org_not, Country, City, Org_type_code, User_id, Rule_date
            FROM rule_org_type_match
            WHERE R_o_t_m_id = :regel_id";

        $stmt = $dbh->prepare($sql);
        $stmt->bindValue(":regel_id", (int) $regel_id, PDO::PARAM_INT);
        $stmt->execute();
        $rule = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$rule) {
            $errors[] = "Regeln hittades inte.";
        }
    } catch (PDOException $e) {
        $errors[] = "Regeln kunde inte hämtas.";
    }
}
?>

<!DOCTYPE html>
<html lang="sv">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ta bort regel organisationstyp</title>
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
                    <h1 class="bibmet-title">Ta bort regel organisationstyp</h1>
                    <p class="bibmet-muted">Kontrollera regeln innan borttagning.</p>
                </div>
                <div class="bibmet-action-group">
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

        <?php
        if ($rule) {
            bibmet_render_summary_panel("Regel", [
                "Regel-id" => $rule['R_o_t_m_id'] ?? null,
                "Land" => $rule['Find_country'] ?? null,
                "Stad" => $rule['Find_city'] ?? null,
                "Organisation, sträng 1" => $rule['Find_org_1'] ?? null,
                "Organisation, sträng 2" => $rule['Find_org_2'] ?? null,
                "Organisation, sträng ej" => $rule['Find_org_not'] ?? null,
                "Organisationstyp" => $rule['Org_type_code'] ?? null,
                "Annat land" => $rule['Country'] ?? null,
                "Annan stad" => $rule['City'] ?? null,
            ]);
        }
        ?>

        <?php if ($rule) : ?>
            <section class="bibmet-panel">
                <div class="bibmet-panel__header">
                    <h2 class="bibmet-panel__title">Bekräfta borttagning</h2>
                </div>
                <div class="bibmet-panel__body">
                    <form action="ta_bort_regel_resultat_o_typ.php" method="post" class="bibmet-form-grid bibmet-form-grid--narrow">
                        <input type="hidden" name="Regel_id" value="<?php echo bibmet_h($regel_id); ?>">

                        <div class="bibmet-action-group">
                            <button type="submit" name="radera" value="1" class="bibmet-button bibmet-button--danger">Radera regel</button>
                            <a href="regel_organisation_typ.php" class="bibmet-button bibmet-button--secondary">Avbryt</a>
                        </div>
                    </form>
                </div>
            </section>
        <?php endif; ?>
    </main>
</body>

</html>
