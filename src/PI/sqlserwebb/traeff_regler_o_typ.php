<?php
require_once __DIR__ . '/sqlsrv_connect.php';
require_once __DIR__ . '/bibmet_ui.php';

$dbh = bibmet_sqlsrv_connect_or_redirect();

$regel_id = isset($_GET['Regel_id']) ? trim((string) $_GET['Regel_id']) : (isset($_SESSION['regel_id']) ? (string) $_SESSION['regel_id'] : "");
$errors = [];
$rows = [];
$totalRows = 0;

$columns = [
    "Unified address-id" => "Unified_address_id",
    "Org-id" => "Org_id",
    "Engelskt namn" => "Name_en",
    "Stad" => "City",
    "Land" => "Country_name",
    "Orgtypkod" => "Org_type_code",
    "Körningsdatum" => "Insert_date",
];

if (!ctype_digit($regel_id) || (int) $regel_id <= 0) {
    $errors[] = "Ogiltigt regel-id.";
} else {
    $_SESSION['regel_id'] = $regel_id;

    try {
        $countStmt = $dbh->prepare("SELECT COUNT(*) AS Antal FROM Unified_address WHERE R_o_t_m_id = :regel_id");
        $countStmt->bindValue(":regel_id", (int) $regel_id, PDO::PARAM_INT);
        $countStmt->execute();
        $totalRows = (int) $countStmt->fetchColumn();

        $stmt = $dbh->prepare("SELECT TOP 100 Unified_address_id, Org_id, Name_en, City, Country_name, Org_type_code, Insert_date
            FROM Unified_address
            WHERE R_o_t_m_id = :regel_id
            ORDER BY NEWID()");
        $stmt->bindValue(":regel_id", (int) $regel_id, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $errors[] = "Det gick inte att hämta regelträffarna. " . $e->getMessage();
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
    <title>Visa regelträffar organisationstyp</title>
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
                    <h1 class="bibmet-title">Visa regelträffar organisationstyp</h1>
                    <p class="bibmet-muted">
                        Regel-id: <?php echo bibmet_h($regel_id); ?>. Visar upp till 100 slumpade träffar.
                    </p>
                </div>
                <div class="bibmet-action-group">
                    <a href="regel_organisation_typ.php" class="bibmet-button bibmet-button--secondary">Till sökning</a>
                    <a href="visa_regler_o_typ.php?Regelid=<?php echo bibmet_h($regel_id); ?>" class="bibmet-button bibmet-button--primary">Till regeln</a>
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

        <section class="bibmet-panel">
            <div class="bibmet-result-header">
                <div>
                    <h2 class="bibmet-panel__title">Regelträffar</h2>
                    <p class="bibmet-muted">
                        Antal funna: <?php echo bibmet_h($totalRows); ?>. Tabellen visar max 100 rader.
                    </p>
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
                            <?php foreach ($columns as $label => $key) : ?>
                                <th><?php echo bibmet_h($label); ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!$rows) : ?>
                            <tr>
                                <td colspan="<?php echo bibmet_h(count($columns)); ?>" class="bibmet-empty">
                                    Inga regelträffar hittades.
                                </td>
                            </tr>
                        <?php endif; ?>

                        <?php foreach ($rows as $row) : ?>
                            <tr>
                                <?php foreach ($columns as $key) : ?>
                                    <td><?php echo bibmet_h($row[$key] ?? ""); ?></td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</body>

</html>
