<?php
require_once __DIR__ . '/sqlsrv_connect.php';
require_once __DIR__ . '/bibmet_ui.php';

$dbh = bibmet_sqlsrv_connect_or_redirect();

$land = bibmet_request_value('Land');
$stad = bibmet_request_value('Stad');
$org = bibmet_request_value('Org');
$regelid = bibmet_request_value('Regelid');
$page = max(1, (int) bibmet_request_value('page', '1'));
$pageSize = 50;
$offset = ($page - 1) * $pageSize;
$errors = [];
$rows = [];
$totalRows = 0;
$totalPages = 1;
$whereParts = [];
$params = [];

if ($regelid !== '') {
    if (ctype_digit($regelid)) {
        $whereParts[] = 'r.R_c_m_id = :regelid';
        $params[':regelid'] = (int) $regelid;
    } else {
        $errors[] = 'Regelid måste vara ett heltal.';
        $whereParts[] = '1 = 0';
    }
} else {
    if ($land !== '' && $land !== 'Ange land') {
        $whereParts[] = 'r.Country_code IN (
            SELECT Country_code
            FROM Country
            WHERE Display_name = :land
        )';
        $params[':land'] = $land;
    }

    if ($stad !== '') {
        $whereParts[] = 'UPPER(r.Find_city) LIKE UPPER(:stad)';
        $params[':stad'] = '%' . $stad . '%';
    }

    if ($org !== '') {
        $whereParts[] = 'UPPER(r.Find_org) LIKE UPPER(:org)';
        $params[':org'] = '%' . $org . '%';
    }
}

$whereSql = $whereParts ? implode(' AND ', $whereParts) : '1 = 1';

$baseSelectSql = "
    SELECT
        r.R_c_m_id,
        r.Find_country,
        r.Find_city,
        r.Find_org,
        r.Divide,
        r.Country_1,
        r.City_1,
        r.Org_id_1,
        (
            SELECT Name_en
            FROM Unified_org_names
            WHERE Unified_org_id = r.Org_id_1
        ) AS Org_1,
        r.Country_2,
        r.City_2,
        r.Org_id_2,
        (
            SELECT Name_en
            FROM Unified_org_names
            WHERE Unified_org_id = r.Org_id_2
        ) AS Org_2,
        r.Country_3,
        r.City_3,
        r.Org_id_3,
        (
            SELECT Name_en
            FROM Unified_org_names
            WHERE Unified_org_id = r.Org_id_3
        ) AS Org_3,
        r.Rule_date
";

$selectSql = "
    SELECT *
    FROM (
        SELECT
            page_source.*,
            ROW_NUMBER() OVER (ORDER BY R_c_m_id DESC) AS row_number
        FROM (
            $baseSelectSql
            FROM Rule_center_match r
            WHERE $whereSql
        ) AS page_source
    ) AS numbered_results
    WHERE row_number BETWEEN :firstPageRow AND :lastPageRow
    ORDER BY row_number
";

try {
    $countStmt = $dbh->prepare("SELECT COUNT(*) FROM Rule_center_match r WHERE $whereSql");
    bibmet_bind_all($countStmt, $params);
    $countStmt->execute();
    $totalRows = (int) $countStmt->fetchColumn();
    $totalPages = max(1, (int) ceil($totalRows / $pageSize));

    if ($page > $totalPages) {
        $page = $totalPages;
        $offset = ($page - 1) * $pageSize;
    }

    $stmt = $dbh->prepare($selectSql);
    bibmet_bind_all($stmt, $params);
    $stmt->bindValue(':firstPageRow', $offset + 1, PDO::PARAM_INT);
    $stmt->bindValue(':lastPageRow', $offset + $pageSize, PDO::PARAM_INT);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $errors[] = 'Det gick inte att hämta centrumreglerna.';
}

$firstRow = $totalRows === 0 ? 0 : $offset + 1;
$lastRow = min($offset + $pageSize, $totalRows);
$filters = [];

if ($regelid !== '') {
    $filters[] = 'Regelid: ' . $regelid;
} else {
    if ($land !== '' && $land !== 'Ange land') {
        $filters[] = 'Land: ' . $land;
    }
    if ($stad !== '') {
        $filters[] = 'Stad: ' . $stad;
    }
    if ($org !== '') {
        $filters[] = 'Organisation: ' . $org;
    }
}

$columns = [
    ['label' => 'Land', 'key' => 'Find_country'],
    ['label' => 'Stad', 'key' => 'Find_city'],
    ['label' => 'Organisation', 'key' => 'Find_org'],
    ['label' => 'Delas', 'key' => 'Divide'],
    ['label' => 'Land 1', 'key' => 'Country_1'],
    ['label' => 'Stad 1', 'key' => 'City_1'],
    ['label' => 'Org-id 1', 'key' => 'Org_id_1'],
    ['label' => 'Org 1', 'key' => 'Org_1'],
    ['label' => 'Land 2', 'key' => 'Country_2'],
    ['label' => 'Stad 2', 'key' => 'City_2'],
    ['label' => 'Org-id 2', 'key' => 'Org_id_2'],
    ['label' => 'Org 2', 'key' => 'Org_2'],
    ['label' => 'Land 3', 'key' => 'Country_3'],
    ['label' => 'Stad 3', 'key' => 'City_3'],
    ['label' => 'Org-id 3', 'key' => 'Org_id_3'],
    ['label' => 'Org 3', 'key' => 'Org_3'],
    ['label' => 'Regelid', 'key' => 'R_c_m_id'],
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
    <title>Visa dubbletter regler centra</title>
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
                    <h1 class="bibmet-title">Visa dubbletter regler centra</h1>
                    <p class="bibmet-muted">
                        <?php if ($filters) : ?>
                            Filtrerat på <?php echo bibmet_h(implode(', ', $filters)); ?>.
                        <?php else : ?>
                            Visar centrumregler.
                        <?php endif; ?>
                    </p>
                </div>
                <a href="regel_centra.php" class="bibmet-button bibmet-button--primary">Till sökning</a>
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
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!$rows) : ?>
                            <tr>
                                <td colspan="<?php echo bibmet_h(count($columns) + 1); ?>" class="bibmet-empty">
                                    Inga centrumregler hittades.
                                </td>
                            </tr>
                        <?php endif; ?>

                        <?php foreach ($rows as $row) : ?>
                            <tr>
                                <td class="bibmet-table__actions">
                                    <div class="bibmet-table__action-row">
                                        <a
                                            href="aendra_regel_c.php?Regel_id=<?php echo bibmet_h($row['R_c_m_id']); ?>"
                                            class="bibmet-button bibmet-button--primary bibmet-button--small">
                                            Ändra
                                        </a>
                                        <a
                                            href="ta_bort_regel_c.php?Regel_id=<?php echo bibmet_h($row['R_c_m_id']); ?>"
                                            class="bibmet-button bibmet-button--danger bibmet-button--small">
                                            Ta bort
                                        </a>
                                    </div>
                                </td>
                                <?php foreach ($columns as $column) : ?>
                                    <td><?php echo bibmet_h($row[$column['key']] ?? ''); ?></td>
                                <?php endforeach; ?>
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
