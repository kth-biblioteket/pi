<?php
require_once __DIR__ . '/sqlsrv_connect.php';
require_once __DIR__ . '/bibmet_ui.php';

$dbh = bibmet_sqlsrv_connect_or_redirect();

$regel_id = isset($_POST['Regel_id']) ? (string) $_POST['Regel_id'] : (isset($_GET['Regel_id']) ? (string) $_GET['Regel_id'] : (isset($_SESSION['regel_id']) ? (string) $_SESSION['regel_id'] : ""));
$reason = isset($_POST['orsak']) ? trim((string) $_POST['orsak']) : "";
$messages = [];
$errors = [];
$archiveWarning = "";
$deleted = false;
$ruleSummary = null;

if (!ctype_digit($regel_id) || (int) $regel_id <= 0) {
    $errors[] = "Ogiltigt regel-id.";
} elseif ($reason === "") {
    $errors[] = "Orsak måste anges.";
} elseif (isset($_SESSION['b_regel_c_id']) && (string) $_SESSION['b_regel_c_id'] === $regel_id) {
    $messages[] = "Regeln är redan borttagen i den här sessionen.";
    $deleted = true;
} else {
    try {
        $dbh->beginTransaction();

        $selectStmt = $dbh->prepare("SELECT r.*, o1.Name_en + ' [' + o1.Country_name + ']' AS Orgname_1,
            o2.Name_en + ' [' + o2.Country_name + ']' AS Orgname_2,
            o3.Name_en + ' [' + o3.Country_name + ']' AS Orgname_3
            FROM rule_center_match r
            JOIN unified_org_names o1 ON r.Org_id_1 = o1.Unified_org_id
            LEFT JOIN unified_org_names o2 ON r.Org_id_2 = o2.Unified_org_id
            LEFT JOIN unified_org_names o3 ON r.Org_id_3 = o3.Unified_org_id
            WHERE r.R_c_m_id = :regel_id");
        $selectStmt->bindValue(":regel_id", (int) $regel_id, PDO::PARAM_INT);
        $selectStmt->execute();
        $rule = $selectStmt->fetch(PDO::FETCH_ASSOC);
        $ruleSummary = $rule ?: null;

        if (!$rule) {
            $errors[] = "Regeln hittades inte eller är redan borttagen.";
            $dbh->rollBack();
        } else {
            try {
                $archiveSql = "INSERT INTO Removed_rules (
                    R_c_m_id, Find_country, Country_code, Find_city, Find_org, Divide,
                    Country_1, City_1, Org_id_1, Country_2, City_2, Org_id_2, Country_3, City_3, Org_id_3,
                    User_id, Rule_date, Remove_user_id, Remove_date, Reason
                ) VALUES (
                    :R_c_m_id, :Find_country, :Country_code, :Find_city, :Find_org, :Divide,
                    :Country_1, :City_1, :Org_id_1, :Country_2, :City_2, :Org_id_2, :Country_3, :City_3, :Org_id_3,
                    :User_id, :Rule_date, :Remove_user_id, CURRENT_TIMESTAMP, :Reason
                )";
                $archiveStmt = $dbh->prepare($archiveSql);
                bibmet_set_query_timeout($archiveStmt, 3);
                foreach ([
                    'R_c_m_id', 'Find_country', 'Country_code', 'Find_city', 'Find_org', 'Divide',
                    'Country_1', 'City_1', 'Org_id_1', 'Country_2', 'City_2', 'Org_id_2', 'Country_3', 'City_3', 'Org_id_3',
                    'User_id', 'Rule_date',
                ] as $column) {
                    $archiveStmt->bindValue(':' . $column, $rule[$column] ?? null);
                }
                $archiveStmt->bindValue(':Remove_user_id', isset($_SESSION['anv']) ? $_SESSION['anv'] : '');
                $archiveStmt->bindValue(':Reason', $reason);
                $archiveStmt->execute();
            } catch (PDOException $e) {
                $archiveWarning = "Regeln kunde inte arkiveras i Removed_rules, men borttagningen fortsatte.";
            }

            $deleteStmt = $dbh->prepare("DELETE FROM rule_center_match WHERE R_c_m_id = :regel_id");
            bibmet_set_query_timeout($deleteStmt, 10);
            $deleteStmt->bindValue(":regel_id", (int) $regel_id, PDO::PARAM_INT);
            $deleteStmt->execute();

            if ($deleteStmt->rowCount() > 0) {
                $_SESSION['b_regel_c_id'] = $regel_id;
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
        $errors[] = "Fel vid borttagande av regeln.";
    }
}
?>

<!DOCTYPE html>
<html lang="sv">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ta bort regel centra</title>
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
                    <h1 class="bibmet-title">Ta bort regel centra</h1>
                    <p class="bibmet-muted">Regel-id: <?php echo bibmet_h($regel_id); ?></p>
                </div>
                <div class="bibmet-action-group">
                    <a href="regel_centra.php" class="bibmet-button bibmet-button--primary">Till sökning</a>
                    <a href="adressmeny.php" class="bibmet-button bibmet-button--secondary">Till menyn</a>
                </div>
            </div>
        </section>

        <?php
        if ($ruleSummary) {
            bibmet_render_summary_panel("Regel", [
                "Regel-id" => $ruleSummary['R_c_m_id'] ?? null,
                "Land" => $ruleSummary['Find_country'] ?? null,
                "Stad" => $ruleSummary['Find_city'] ?? null,
                "Organisationsnamn" => $ruleSummary['Find_org'] ?? null,
                "Delas i" => $ruleSummary['Divide'] ?? null,
                "Organisation 1" => $ruleSummary['Orgname_1'] ?? null,
                "Organisation 2" => $ruleSummary['Orgname_2'] ?? null,
                "Organisation 3" => $ruleSummary['Orgname_3'] ?? null,
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

        <?php bibmet_render_messages_panel($deleted ? "Borttagning klar" : "Resultat", $messages, $deleted ? "success" : ""); ?>
    </main>
</body>

</html>
