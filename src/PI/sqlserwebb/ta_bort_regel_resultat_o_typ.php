<?php
require_once __DIR__ . '/sqlsrv_connect.php';
require_once __DIR__ . '/bibmet_ui.php';

$dbh = bibmet_sqlsrv_connect_or_redirect();

$regel_id = isset($_POST['Regel_id']) ? (string) $_POST['Regel_id'] : (isset($_GET['Regel_id']) ? (string) $_GET['Regel_id'] : (isset($_SESSION['regel_id']) ? (string) $_SESSION['regel_id'] : ""));
$successMessages = [];
$errors = [];
$deleted = false;
$ruleSummary = null;

if (!ctype_digit($regel_id) || (int) $regel_id <= 0) {
    $errors[] = "Ogiltigt regel-id.";
} elseif (isset($_SESSION['b_regel_o_typ_id']) && (string) $_SESSION['b_regel_o_typ_id'] === $regel_id) {
    $successMessages[] = "Regeln är redan borttagen i den här sessionen.";
    $deleted = true;
} else {
    try {
        $selectStmt = $dbh->prepare("SELECT * FROM rule_org_type_match WHERE R_o_t_m_id = :regel_id");
        $selectStmt->bindValue(":regel_id", (int) $regel_id, PDO::PARAM_INT);
        $selectStmt->execute();
        $rule = $selectStmt->fetch(PDO::FETCH_ASSOC);
        $ruleSummary = $rule ?: null;

        if (!$rule) {
            $errors[] = "Regeln hittades inte eller är redan borttagen.";
        } else {
            $deleteStmt = $dbh->prepare("DELETE FROM rule_org_type_match WHERE R_o_t_m_id = :regel_id");
            bibmet_set_query_timeout($deleteStmt, 10);
            $deleteStmt->bindValue(":regel_id", (int) $regel_id, PDO::PARAM_INT);
            $deleteStmt->execute();

            if ($deleteStmt->rowCount() > 0) {
                $_SESSION['b_regel_o_typ_id'] = $regel_id;
                $deleted = true;
                $successMessages[] = "Regeln är borttagen.";
            } else {
                $errors[] = "Regeln hittades inte eller är redan borttagen.";
            }
        }
    } catch (PDOException $e) {
        $errors[] = "Fel vid borttagande av regeln.";
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
                    <p class="bibmet-muted">Regel-id: <?php echo bibmet_h($regel_id); ?></p>
                </div>
                <div class="bibmet-action-group">
                    <a href="regel_organisation_typ.php" class="bibmet-button bibmet-button--primary">Till sökning</a>
                    <a href="adressmeny.php" class="bibmet-button bibmet-button--secondary">Till menyn</a>
                </div>
            </div>
        </section>

        <?php
        if ($ruleSummary) {
            bibmet_render_summary_panel("Regel", [
                "Regel-id" => $ruleSummary['R_o_t_m_id'] ?? null,
                "Land" => $ruleSummary['Find_country'] ?? null,
                "Stad" => $ruleSummary['Find_city'] ?? null,
                "Organisation, sträng 1" => $ruleSummary['Find_org_1'] ?? null,
                "Organisation, sträng 2" => $ruleSummary['Find_org_2'] ?? null,
                "Organisation, sträng ej" => $ruleSummary['Find_org_not'] ?? null,
                "Organisationstyp" => $ruleSummary['Org_type_code'] ?? null,
                "Annat land" => $ruleSummary['Country'] ?? null,
                "Annan stad" => $ruleSummary['City'] ?? null,
            ]);
        }
        ?>

        <?php if ($errors) : ?>
            <div class="bibmet-alert" role="alert">
                <?php foreach ($errors as $error) : ?>
                    <p><?php echo bibmet_h($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php bibmet_render_messages_panel($deleted ? "Borttagning klar" : "Resultat", $successMessages, "success"); ?>
    </main>
</body>

</html>
