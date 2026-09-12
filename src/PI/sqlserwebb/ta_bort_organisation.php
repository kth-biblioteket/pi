<?php
require_once __DIR__ . '/sqlsrv_connect.php';
require_once __DIR__ . '/bibmet_ui.php';

$dbh = bibmet_sqlsrv_connect_or_redirect();

$u_org_id = bibmet_request_value('Unified_org_id', isset($_SESSION['u_org_id']) ? (string) $_SESSION['u_org_id'] : "");
$reason = isset($_POST['orsak']) ? trim((string) $_POST['orsak']) : "";
$errors = [];
$messages = [];
$organisation = null;
$orgTypeName = "";
$hasRules = false;
$deleted = false;

function bibmet_org_has_rules(PDO $dbh, $orgId)
{
    foreach ([
        "rule_org_match",
        "rule_full_address_match",
        "rule_center_match",
    ] as $table) {
        $checkStmt = $dbh->prepare("SELECT COUNT(*) FROM $table WHERE Org_id_1 = :org_id_1 OR Org_id_2 = :org_id_2 OR Org_id_3 = :org_id_3");
        $checkStmt->bindValue(':org_id_1', (int) $orgId, PDO::PARAM_INT);
        $checkStmt->bindValue(':org_id_2', (int) $orgId, PDO::PARAM_INT);
        $checkStmt->bindValue(':org_id_3', (int) $orgId, PDO::PARAM_INT);
        $checkStmt->execute();
        if ((int) $checkStmt->fetchColumn() > 0) {
            return true;
        }
    }

    return false;
}

function render_readonly_org_field($label, $value)
{
    ?>
    <label class="bibmet-field">
        <span class="bibmet-field__label"><?php echo bibmet_h($label); ?></span>
        <input class="bibmet-input" type="text" value="<?php echo bibmet_h($value); ?>" disabled>
    </label>
    <?php
}

if (!ctype_digit($u_org_id) || (int) $u_org_id <= 0) {
    $errors[] = "Ogiltigt organisations-id.";
} else {
    $_SESSION['u_org_id'] = $u_org_id;

    try {
        $stmt = $dbh->prepare("SELECT Unified_org_id, Name_local, Name_en, Country_name, Org_type_code, Comment, User_id, Latest_date, ROR_id
            FROM unified_org_names
            WHERE Unified_org_id = :org_id");
        $stmt->bindValue(':org_id', (int) $u_org_id, PDO::PARAM_INT);
        $stmt->execute();
        $organisation = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$organisation) {
            $errors[] = "Organisationen hittades inte eller är redan borttagen.";
        } else {
            $_SESSION['unified_org_id'] = $organisation['Unified_org_id'];
            $_SESSION['namn_lok'] = $organisation['Name_local'];
            $_SESSION['namn_eng'] = $organisation['Name_en'];
            $_SESSION['land'] = $organisation['Country_name'];
            $_SESSION['orgtyp'] = $organisation['Org_type_code'];
            $_SESSION['komm'] = $organisation['Comment'];
            $_SESSION['user_id'] = $organisation['User_id'];
            $_SESSION['latest_date'] = $organisation['Latest_date'];
            $_SESSION['rorid'] = $organisation['ROR_id'];

            $typeStmt = $dbh->prepare("SELECT Org_type_eng FROM Organization_type WHERE Org_type_code = :org_type_code");
            $typeStmt->bindValue(':org_type_code', (string) $organisation['Org_type_code'], PDO::PARAM_STR);
            $typeStmt->execute();
            $orgTypeName = (string) $typeStmt->fetchColumn();

            $hasRules = bibmet_org_has_rules($dbh, $u_org_id);
        }
    } catch (PDOException $e) {
        $errors[] = "Det gick inte att hämta organisationen.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $organisation && !$errors) {
    if ($reason === "") {
        $errors[] = "Orsak måste anges.";
    } elseif ($hasRules) {
        $errors[] = "Organisationen kan inte tas bort eftersom den finns i regler.";
    } elseif (isset($_SESSION['b_org_id']) && (string) $_SESSION['b_org_id'] === $u_org_id) {
        $messages[] = "Organisationen är redan borttagen i den här sessionen.";
        $deleted = true;
        $organisation = null;
    } else {
        try {
            $dbh->beginTransaction();
            $archiveWarning = "";

            try {
                $archiveSql = "INSERT INTO removed_un_org_names
                    (Unified_org_id, Name_local, Name_en, Country_name, Org_type_code, Comment, User_id, Latest_date, Remove_user_id, Remove_date, Reason, ROR_id)
                    VALUES
                    (:Unified_org_id, :Name_local, :Name_en, :Country_name, :Org_type_code, :Comment, :User_id, :Latest_date, :Remove_user_id, CURRENT_TIMESTAMP, :Reason, :ROR_id)";
                $archiveStmt = $dbh->prepare($archiveSql);
                foreach (['Unified_org_id', 'Name_local', 'Name_en', 'Country_name', 'Org_type_code', 'Comment', 'User_id', 'Latest_date', 'ROR_id'] as $column) {
                    $archiveStmt->bindValue(':' . $column, $organisation[$column] ?? null);
                }
                $archiveStmt->bindValue(':Remove_user_id', isset($_SESSION['anv']) ? $_SESSION['anv'] : '');
                $archiveStmt->bindValue(':Reason', $reason);
                $archiveStmt->execute();
            } catch (PDOException $e) {
                $archiveWarning = "Organisationen kunde inte arkiveras eftersom arkivtabellen saknas eller inte är tillgänglig.";
            }

            $deleteStmt = $dbh->prepare("DELETE FROM unified_org_names WHERE Unified_org_id = :org_id");
            $deleteStmt->bindValue(':org_id', (int) $u_org_id, PDO::PARAM_INT);
            $deleteStmt->execute();

            if ($deleteStmt->rowCount() > 0) {
                $_SESSION['b_org_id'] = $u_org_id;
                $deleted = true;
                $organisation = null;
                $messages[] = "Organisationen är borttagen.";
                if ($archiveWarning !== "") {
                    $messages[] = $archiveWarning;
                }
                $dbh->commit();
            } else {
                $errors[] = "Fel vid borttagande av organisationen.";
                $dbh->rollBack();
            }
        } catch (PDOException $e) {
            if ($dbh->inTransaction()) {
                $dbh->rollBack();
            }
            $errors[] = "Fel vid borttagande av organisationen.";
        }
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
    <title>Ta bort organisationsnamn</title>
    <link href="Site.css" rel="stylesheet">
    <?php include("include_bibmet_kth.html"); ?>
</head>

<body class="bibmet-body">
    <?php include('include_head_new.html'); ?>

    <main class="bibmet-main">
        <section class="bibmet-hero">
            <div class="bibmet-hero__row">
                <div>
                    <p class="bibmet-eyebrow">Organisationsnamn</p>
                    <h1 class="bibmet-title">Ta bort organisationsnamn</h1>
                    <p class="bibmet-muted">
                        <?php if ($deleted) : ?>
                            Borttagningen är genomförd.
                        <?php else : ?>
                            Granska organisationen och ange orsak innan borttagning.
                        <?php endif; ?>
                    </p>
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

        <?php if ($hasRules && $organisation) : ?>
            <div class="bibmet-alert" role="alert">
                <p>Organisationen kan inte tas bort eftersom den finns i regler.</p>
            </div>
        <?php endif; ?>

        <?php if ($organisation) : ?>
            <form action="ta_bort_organisation.php" method="post" class="bibmet-panel">
                <input type="hidden" name="Unified_org_id" value="<?php echo bibmet_h($u_org_id); ?>">

                <div class="bibmet-panel__header">
                    <h2 class="bibmet-panel__title">Bekräfta borttagning</h2>
                </div>

                <div class="bibmet-form-grid bibmet-form-grid--narrow">
                    <label class="bibmet-field">
                        <span class="bibmet-field__label">Orsak *</span>
                        <input class="bibmet-input" type="text" name="orsak" maxlength="100" value="<?php echo bibmet_h($reason); ?>" required<?php echo $hasRules ? ' disabled' : ''; ?>>
                        <span class="bibmet-field__hint">Obligatoriskt. Beskriv varför organisationen ska tas bort.</span>
                    </label>
                </div>

                <div class="bibmet-form-grid bibmet-form-grid--narrow">
                    <?php render_readonly_org_field('Orgid', $organisation['Unified_org_id']); ?>
                    <?php render_readonly_org_field('Lokalt namn', $organisation['Name_local']); ?>
                    <?php render_readonly_org_field('Engelskt namn', $organisation['Name_en']); ?>
                    <?php render_readonly_org_field('Land', $organisation['Country_name']); ?>
                    <?php render_readonly_org_field('Organisationstyp', $orgTypeName !== '' ? $orgTypeName : $organisation['Org_type_code']); ?>
                    <?php render_readonly_org_field('Kommentar', $organisation['Comment']); ?>
                    <?php render_readonly_org_field('ROR-id', $organisation['ROR_id']); ?>
                </div>

                <div class="bibmet-form-actions">
                    <div class="bibmet-action-group">
                        <input type="submit" name="spara" value="Radera organisation" class="bibmet-button bibmet-button--danger"<?php echo $hasRules ? ' disabled' : ''; ?>>
                        <a href="organisationsnamn.php" class="bibmet-button bibmet-button--secondary">Avbryt</a>
                    </div>
                </div>
            </form>
        <?php endif; ?>
    </main>
</body>

</html>
