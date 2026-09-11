<?php
require_once __DIR__ . '/sqlsrv_connect.php';
require_once __DIR__ . '/bibmet_ui.php';

$dbh = bibmet_sqlsrv_connect_or_redirect();

$page = max(1, (int) bibmet_request_value('page', '1'));
$pageSize = 50;
$offset = ($page - 1) * $pageSize;
$errors = [];
$rows = [];
$totalRows = 0;
$totalPages = 1;

$whereSql = "NOT EXISTS (
    SELECT 1
    FROM BIBMET.dbo.Unified_address ua
    WHERE ua.R_o_t_m_id = r.R_o_t_m_id
)";

$selectSql = "
    SELECT *
    FROM (
        SELECT
            r.R_o_t_m_id,
            r.Find_country,
            r.Find_city,
            r.Find_org_1,
            r.Find_org_2,
            r.Find_org_not,
            r.Country,
            r.City,
            r.Org_type_code,
            r.Rule_date,
            ROW_NUMBER() OVER (ORDER BY r.R_o_t_m_id DESC) AS row_number
        FROM Rule_org_type_match r
        WHERE $whereSql
    ) AS numbered_results
    WHERE row_number BETWEEN :firstPageRow AND :lastPageRow
    ORDER BY row_number
";

try {
    $countStmt = $dbh->query("SELECT COUNT(*) FROM Rule_org_type_match r WHERE $whereSql");
    $totalRows = (int) $countStmt->fetchColumn();
    $totalPages = max(1, (int) ceil($totalRows / $pageSize));

    if ($page > $totalPages) {
        $page = $totalPages;
        $offset = ($page - 1) * $pageSize;
    }

    $stmt = $dbh->prepare($selectSql);
    $stmt->bindValue(':firstPageRow', $offset + 1, PDO::PARAM_INT);
    $stmt->bindValue(':lastPageRow', $offset + $pageSize, PDO::PARAM_INT);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $errors[] = 'Det gick inte att hämta organisationstypsregler utan träff.';
}

$firstRow = $totalRows === 0 ? 0 : $offset + 1;
$lastRow = min($offset + $pageSize, $totalRows);

$columns = [
    ['label' => 'Sökland', 'key' => 'Find_country'],
    ['label' => 'Sökstad', 'key' => 'Find_city'],
    ['label' => 'Organisation 1', 'key' => 'Find_org_1'],
    ['label' => 'Organisation 2', 'key' => 'Find_org_2'],
    ['label' => 'Organisation ej', 'key' => 'Find_org_not'],
    ['label' => 'Land', 'key' => 'Country'],
    ['label' => 'Stad', 'key' => 'City'],
    ['label' => 'Orgtypkod', 'key' => 'Org_type_code'],
    ['label' => 'Regel-id', 'key' => 'R_o_t_m_id'],
    ['label' => 'Datum', 'key' => 'Rule_date'],
];
?>

<!DOCTYPE html>
<html lang="sv">

<! Författare: Cecilia Wiklander>
<! Syfte: Adressrättnings-hantering>
<! Ändringar:>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Visa organisationstypsregler utan träff</title>
    <link href="Site.css" rel="stylesheet">
    <?php include("include_bibmet_kth.html"); ?>
</head>

<body class="bibmet-body">
    <?php include("include_head_new.html"); ?>

    <main class="bibmet-main">
        <section class="bibmet-hero">
            <div class="bibmet-hero__row">
                <div>
                    <p class="bibmet-eyebrow">Adressrättningsregler</p>
                    <h1 class="bibmet-title">Organisationstypsregler utan träff</h1>
                    <p class="bibmet-muted">
                        Visar organisationstypsregler som inte har producerat någon träff i adressrättningen.
                    </p>
                </div>
                <a href="regel_organisation_typ.php" class="bibmet-button bibmet-button--primary">Till sökning</a>
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
                    <h2 class="bibmet-panel__title">Sökresultat</h2>
                    <p class="bibmet-muted">
                        Visar <?php echo bibmet_h($firstRow); ?>-<?php echo bibmet_h($lastRow); ?> av <?php echo bibmet_h($totalRows); ?> regler.
                    </p>
                </div>
                <p class="bibmet-page-pill">Sida <?php echo bibmet_h($page); ?> av <?php echo bibmet_h($totalPages); ?></p>
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
                            <th class="bibmet-table__actions">Åtgärder</th>
                            <?php foreach ($columns as $column) : ?>
                                <th><?php echo bibmet_h($column['label']); ?></th>
                            <?php endforeach; ?>
                            <th>Testa</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!$rows) : ?>
                            <tr>
                                <td colspan="<?php echo bibmet_h(count($columns) + 2); ?>" class="bibmet-empty">
                                    Inga organisationstypsregler utan träff hittades.
                                </td>
                            </tr>
                        <?php endif; ?>

                        <?php foreach ($rows as $row) : ?>
                            <tr>
                                <td class="bibmet-table__actions">
                                    <div class="bibmet-table__action-row">
                                        <a
                                            href="aendra_regel_o_typ.php?Regel_id=<?php echo bibmet_h($row['R_o_t_m_id']); ?>"
                                            class="bibmet-button bibmet-button--primary bibmet-button--small">
                                            Ändra
                                        </a>
                                        <a
                                            href="ta_bort_regel_o_typ.php?Regel_id=<?php echo bibmet_h($row['R_o_t_m_id']); ?>"
                                            class="bibmet-button bibmet-button--danger bibmet-button--small">
                                            Ta bort
                                        </a>
                                    </div>
                                </td>
                                <?php foreach ($columns as $column) : ?>
                                    <td><?php echo bibmet_h($row[$column['key']] ?? ''); ?></td>
                                <?php endforeach; ?>
                                <td>
                                    <a
                                        href="traeff_regler_o_typ.php?Regel_id=<?php echo bibmet_h($row['R_o_t_m_id']); ?>"
                                        class="bibmet-button bibmet-button--secondary bibmet-button--small">
                                        Testa
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php bibmet_render_pagination($page, $totalPages); ?>
        </section>
    </main>
</body>

</html>
