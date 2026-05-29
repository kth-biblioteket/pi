<?php
require_once __DIR__ . '/sqlsrv_connect.php';
require_once __DIR__ . '/bibmet_ui.php';

$dbh = bibmet_sqlsrv_connect_or_redirect();

$errors = [];
$messages = [];
$invalidRows = [];
$importedRows = 0;
$invalidRowCount = 0;
$runStatus = 99;
$maxUploadSize = 500000;

function bibmet_current_date(PDO $dbh)
{
    return $dbh->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite' ? date('Y-m-d') : null;
}

if (isset($_POST['ladda'])) {
    if (!isset($_FILES['fileToUpload']) || $_FILES['fileToUpload']['error'] !== UPLOAD_ERR_OK) {
        $errors[] = "Välj en csv-fil att ladda upp.";
    } else {
        $originalName = basename($_FILES['fileToUpload']['name']);
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        if ($extension !== 'csv') {
            $errors[] = "Enbart csv-filer tillåts.";
        }

        if ($_FILES['fileToUpload']['size'] > $maxUploadSize) {
            $errors[] = "Filen är för stor. Maxstorlek är 500 kB.";
        }

        if (strpos($originalName, ' ') !== false) {
            $errors[] = "Filnamnet får inte innehålla blanktecken.";
        }

        if (!$errors) {
            $tmpFile = $_FILES['fileToUpload']['tmp_name'];
            $rows = [];

            if (($handle = fopen($tmpFile, 'r')) === false) {
                $errors[] = "Filen går inte att öppna.";
            } else {
                while (($line = fgetcsv($handle, 1000, ';')) !== false) {
                    $rows[] = $line;
                }
                fclose($handle);
            }

            if (!$errors) {
                if (!$rows) {
                    $errors[] = "Filen är tom.";
                } elseif (count($rows[0]) !== 8) {
                    $errors[] = "Filen har inte rätt antal kolumner. Den ska ha 8 semikolonseparerade kolumner.";
                } else {
                    $dataRows = $rows;
                    if (isset($dataRows[0][3]) && !is_numeric($dataRows[0][3])) {
                        array_shift($dataRows);
                    }

                    if (!$dataRows) {
                        $errors[] = "Filen innehåller inga datarader.";
                    } else {
                        try {
                            $dateValue = bibmet_current_date($dbh);
                            if ($dateValue === null) {
                                $dateStmt = $dbh->query("SELECT FORMAT (getdate(), 'yyyy-MM-dd') AS Datum");
                                $dateValue = (string) $dateStmt->fetchColumn();
                            }

                            $first = $dataRows[0];
                            $duplicateStmt = $dbh->prepare("SELECT COUNT(*) AS antal
                                FROM Rule_org_match
                                WHERE Find_org = :Find_org
                                    AND Find_country = :Find_country
                                    AND User_id = :User_id
                                    AND Rule_date = :Rule_date
                                    AND Divide = :Divide
                                    AND Org_id_1 = :Org_id_1");
                            $duplicateStmt->bindValue(':Find_org', $first[0], PDO::PARAM_STR);
                            $duplicateStmt->bindValue(':Find_country', $first[1], PDO::PARAM_STR);
                            $duplicateStmt->bindValue(':User_id', $first[7], PDO::PARAM_STR);
                            $duplicateStmt->bindValue(':Rule_date', $dateValue, PDO::PARAM_STR);
                            $duplicateStmt->bindValue(':Divide', (int) $first[3], PDO::PARAM_INT);
                            $duplicateStmt->bindValue(':Org_id_1', (int) $first[4], PDO::PARAM_INT);
                            $duplicateStmt->execute();

                            if ((int) $duplicateStmt->fetchColumn() > 0) {
                                $errors[] = "Filen har redan lästs in.";
                            } else {
                                $isSqlite = $dbh->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite';
                                $insertSql = $isSqlite
                                    ? "INSERT INTO Rule_org_match
                                        (R_o_m_id, Find_country, Country_code, Find_org, Divide, Org_id_1, Org_id_2, Org_id_3, Rule_date, User_id, Run_status)
                                        VALUES
                                        (:R_o_m_id, :Find_country, :Country_code, :Find_org, :Divide, :Org_id_1, :Org_id_2, :Org_id_3, :Rule_date, :User_id, :Run_status)"
                                    : "INSERT INTO Rule_org_match
                                        (Find_country, Country_code, Find_org, Divide, Org_id_1, Org_id_2, Org_id_3, Rule_date, User_id, Run_status)
                                        VALUES
                                        (:Find_country, :Country_code, :Find_org, :Divide, :Org_id_1, :Org_id_2, :Org_id_3, :Rule_date, :User_id, :Run_status)";
                                $insertStmt = $dbh->prepare($insertSql);

                                $dbh->beginTransaction();
                                $nextRuleId = null;
                                if ($isSqlite) {
                                    $idStmt = $dbh->query("SELECT COALESCE(MAX(R_o_m_id), 0) + 1 FROM Rule_org_match");
                                    $nextRuleId = (int) $idStmt->fetchColumn();
                                }

                                foreach ($dataRows as $index => $line) {
                                    $rowNumber = $index + 1;
                                    $line = array_pad($line, 8, '');
                                    $divide = trim((string) $line[3]);
                                    $orgId1 = trim((string) $line[4]);
                                    $orgId2 = trim((string) $line[5]);
                                    $orgId3 = trim((string) $line[6]);
                                    $invalid = false;

                                    if (!in_array($divide, ['1', '2', '3'], true) || !ctype_digit($orgId1)) {
                                        $invalid = true;
                                    }
                                    if ($divide === '2' && !ctype_digit($orgId2)) {
                                        $invalid = true;
                                    }
                                    if ($divide === '3' && (!ctype_digit($orgId2) || !ctype_digit($orgId3))) {
                                        $invalid = true;
                                    }

                                    if ($invalid) {
                                        $invalidRowCount++;
                                        $invalidRows[] = ['row' => $rowNumber, 'data' => $line];
                                        continue;
                                    }

                                    if ($isSqlite) {
                                        $insertStmt->bindValue(':R_o_m_id', $nextRuleId, PDO::PARAM_INT);
                                        $nextRuleId++;
                                    }
                                    $insertStmt->bindValue(':Find_country', $line[1], PDO::PARAM_STR);
                                    $insertStmt->bindValue(':Country_code', $line[2], PDO::PARAM_STR);
                                    $insertStmt->bindValue(':Find_org', $line[0], PDO::PARAM_STR);
                                    $insertStmt->bindValue(':Divide', (int) $divide, PDO::PARAM_INT);
                                    $insertStmt->bindValue(':Org_id_1', (int) $orgId1, PDO::PARAM_INT);
                                    $insertStmt->bindValue(':Org_id_2', $divide === '1' ? null : (int) $orgId2, $divide === '1' ? PDO::PARAM_NULL : PDO::PARAM_INT);
                                    $insertStmt->bindValue(':Org_id_3', $divide === '3' ? (int) $orgId3 : null, $divide === '3' ? PDO::PARAM_INT : PDO::PARAM_NULL);
                                    $insertStmt->bindValue(':Rule_date', $dateValue, PDO::PARAM_STR);
                                    $insertStmt->bindValue(':User_id', $line[7], PDO::PARAM_STR);
                                    $insertStmt->bindValue(':Run_status', $runStatus, PDO::PARAM_INT);
                                    $insertStmt->execute();
                                    $importedRows++;
                                }

                                $dbh->commit();
                                $messages[] = "Filen är inläst.";
                                $messages[] = "Antal inlästa poster: " . $importedRows . ".";
                                $messages[] = "Antal felaktiga ej inlästa poster: " . $invalidRowCount . ".";
                            }
                        } catch (PDOException $e) {
                            if ($dbh->inTransaction()) {
                                $dbh->rollBack();
                            }
                            $errors[] = "Det gick inte att läsa in filen. " . $e->getMessage();
                        }
                    }
                }
            }
        }
    }
}

$invalidColumns = [
    "Rad",
    "Sökt organisation",
    "Landsnamn",
    "Landskod",
    "Delas",
    "Org-id 1",
    "Org-id 2",
    "Org-id 3",
    "Användarnamn",
];
?>

<!DOCTYPE html>
<html lang="sv">

<! Författare: Cecilia Wiklander>
<! Syfte: Adressrättnings-hantering>
<! Ändringar: >

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ladda regler organisation</title>
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
                    <h1 class="bibmet-title">Ladda regler organisation</h1>
                    <p class="bibmet-muted">Ladda upp en semikolonseparerad csv-fil med organisationsregler.</p>
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

        <?php if ($messages) : ?>
            <div class="bibmet-alert bibmet-alert--success" role="status">
                <?php foreach ($messages as $message) : ?>
                    <p><?php echo bibmet_h($message); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form action="laddaorgregler.php" method="post" enctype="multipart/form-data" class="bibmet-panel">
            <div class="bibmet-panel__header">
                <h2 class="bibmet-panel__title">Välj fil</h2>
            </div>

            <div class="bibmet-form-grid bibmet-form-grid--narrow">
                <label class="bibmet-field">
                    <span class="bibmet-field__label">Csv-fil</span>
                    <input class="bibmet-input" type="file" name="fileToUpload" id="fileToUpload" accept=".csv,text/csv" required>
                    <span class="bibmet-field__hint">Max 500 kB. Filnamnet får inte innehålla blanktecken.</span>
                </label>
            </div>

            <div class="bibmet-form-actions">
                <div class="bibmet-action-group">
                    <input type="submit" value="Ladda upp" name="ladda" class="bibmet-button bibmet-button--primary">
                </div>
            </div>
        </form>

        <section class="bibmet-panel">
            <div class="bibmet-panel__header">
                <h2 class="bibmet-panel__title">Filformat</h2>
            </div>
            <div class="bibmet-panel__body">
                <p class="bibmet-muted">Filen måste vara en semikolonseparerad csv-fil. Kolumnrubriker är valfria.</p>
                <ol>
                    <li>Organisationsnamn att söka på</li>
                    <li>Landsnamn</li>
                    <li>Landskod</li>
                    <li>Splittringsvärde (1-3)</li>
                    <li>Organisationsid 1</li>
                    <li>Organisationsid 2</li>
                    <li>Organisationsid 3</li>
                    <li>Användarnamn</li>
                </ol>
                <p class="bibmet-muted">Regler inlästa via uppladdade filer får värdet 99 i kolumnen Run_status i tabellen Rule_org_match.</p>
            </div>
        </section>

        <?php if ($invalidRows) : ?>
            <section class="bibmet-panel">
                <div class="bibmet-result-header">
                    <div>
                        <h2 class="bibmet-panel__title">Felaktiga poster – ej inlästa</h2>
                        <p class="bibmet-muted">Visar rader som inte kunde importeras.</p>
                    </div>
                </div>

                <div class="bibmet-table-scroll-top-wrap">
                    <div id="rules-scroll-top" class="bibmet-scrollbar bibmet-scroll-top">
                        <div id="rules-scroll-spacer" class="bibmet-scroll-spacer"></div>
                    </div>
                </div>

                <div id="rules-table-scroll" class="bibmet-scrollbar bibmet-table-wrap">
                    <table id="rules-table" class="bibmet-table">
                        <thead>
                            <tr>
                                <?php foreach ($invalidColumns as $column) : ?>
                                    <th><?php echo bibmet_h($column); ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($invalidRows as $invalidRow) : ?>
                                <tr>
                                    <td><?php echo bibmet_h($invalidRow['row']); ?></td>
                                    <?php foreach ($invalidRow['data'] as $cell) : ?>
                                        <td><?php echo bibmet_h($cell); ?></td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        <?php endif; ?>
    </main>
</body>

</html>
