<?php
require_once __DIR__ . '/sqlsrv_connect.php';
require_once __DIR__ . '/bibmet_ui.php';

$dbh = bibmet_sqlsrv_connect_or_redirect();

$regel_id = isset($_SESSION['regel_id']) ? (string) $_SESSION['regel_id'] : "";
$reason = isset($_POST['orsak']) ? trim((string) $_POST['orsak']) : "";
$messages = [];
$errors = [];
$archiveWarning = "";
$deleted = false;

if (!ctype_digit($regel_id) || (int) $regel_id <= 0) {
    $errors[] = "Ogiltigt regel-id.";
} elseif ($reason === "") {
    $errors[] = "Orsak måste anges.";
} elseif (isset($_SESSION['b_regel_o_id']) && (string) $_SESSION['b_regel_o_id'] === $regel_id) {
    $messages[] = "Regeln är redan borttagen i den här sessionen.";
    $deleted = true;
} else {
    try {
        $dbh->beginTransaction();

        $selectStmt = $dbh->prepare("SELECT * FROM rule_org_match WHERE R_o_m_id = :regel_id");
        $selectStmt->bindValue(":regel_id", (int) $regel_id, PDO::PARAM_INT);
        $selectStmt->execute();
        $rule = $selectStmt->fetch(PDO::FETCH_ASSOC);

        if (!$rule) {
            $errors[] = "Regeln hittades inte eller är redan borttagen.";
            $dbh->rollBack();
        } else {
            try {
                $archiveSql = "INSERT INTO Removed_rules (
                    R_o_m_id, Find_country, Country_code, Find_city, Find_org, Divide,
                    Country_1, City_1, Org_id_1, Country_2, City_2, Org_id_2, Country_3, City_3, Org_id_3,
                    User_id, Rule_date, Remove_user_id, Remove_date, Reason, Valid_from, Valid_to
                ) VALUES (
                    :R_o_m_id, :Find_country, :Country_code, :Find_city, :Find_org, :Divide,
                    :Country_1, :City_1, :Org_id_1, :Country_2, :City_2, :Org_id_2, :Country_3, :City_3, :Org_id_3,
                    :User_id, :Rule_date, :Remove_user_id, CURRENT_TIMESTAMP, :Reason, :Valid_from, :Valid_to
                )";
                $archiveStmt = $dbh->prepare($archiveSql);
                foreach ([
                    'R_o_m_id', 'Find_country', 'Country_code', 'Find_city', 'Find_org', 'Divide',
                    'Country_1', 'City_1', 'Org_id_1', 'Country_2', 'City_2', 'Org_id_2', 'Country_3', 'City_3', 'Org_id_3',
                    'User_id', 'Rule_date', 'Valid_from', 'Valid_to',
                ] as $column) {
                    $archiveStmt->bindValue(':' . $column, $rule[$column] ?? null);
                }
                $archiveStmt->bindValue(':Remove_user_id', isset($_SESSION['anv']) ? $_SESSION['anv'] : '');
                $archiveStmt->bindValue(':Reason', $reason);
                $archiveStmt->execute();
            } catch (PDOException $e) {
                $archiveWarning = "Regeln kunde inte arkiveras i Removed_rules, men borttagningen fortsatte. " . $e->getMessage();
            }

            $deleteStmt = $dbh->prepare("DELETE FROM rule_org_match WHERE R_o_m_id = :regel_id");
            $deleteStmt->bindValue(":regel_id", (int) $regel_id, PDO::PARAM_INT);
            $deleteStmt->execute();

            if ($deleteStmt->rowCount() > 0) {
                $_SESSION['b_regel_o_id'] = $regel_id;
                $deleted = true;
                $messages[] = "Regeln är borttagen.";
                if ($archiveWarning !== "") {
                    $messages[] = $archiveWarning;
                }
                $dbh->commit();
            } else {
                $errors[] = "Fel vid borttagande av regeln.";
                $dbh->rollBack();
            }
        }
    } catch (PDOException $e) {
        if ($dbh->inTransaction()) {
            $dbh->rollBack();
        }
        $errors[] = "Fel vid borttagande av regeln. " . $e->getMessage();
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
    <title>Ta bort regel organisation</title>
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
                    <h1 class="bibmet-title">Ta bort regel organisation</h1>
                    <p class="bibmet-muted">Regel-id: <?php echo bibmet_h($regel_id); ?></p>
                </div>
                <div class="bibmet-action-group">
                    <a href="regel_organisation.php" class="bibmet-button bibmet-button--primary">Till sökning</a>
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
            <section class="bibmet-panel">
                <div class="bibmet-panel__header">
                    <h2 class="bibmet-panel__title"><?php echo $deleted ? "Borttagning klar" : "Resultat"; ?></h2>
                </div>
                <div class="bibmet-panel__body">
                    <?php foreach ($messages as $message) : ?>
                        <p class="bibmet-muted"><?php echo bibmet_h($message); ?></p>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>
    </main>
</body>

</html>
