<?php
require_once __DIR__ . '/sqlsrv_connect.php';
require_once __DIR__ . '/bibmet_ui.php';

$dbh = bibmet_sqlsrv_connect_or_redirect();

$regelId = bibmet_request_value('Regel_id', bibmet_request_value('Regelid', isset($_SESSION['regel_id']) ? (string) $_SESSION['regel_id'] : ''));
if ($regelId !== '') {
    $_SESSION['regel_id'] = $regelId;
    $_SESSION['regel_id_ut'] = $regelId;
}

$land = bibmet_request_value('Land_ut');
$stad = bibmet_request_value('Stad_nu');
$org = bibmet_request_value('Org_nu');
$page = max(1, (int) bibmet_request_value('page', '1'));
$pageSize = 50;
$offset = ($page - 1) * $pageSize;
$errors = [];
$rows = [];
$totalRows = 0;
$totalPages = 1;

if (($land === '' || $org === '') && $regelId !== '' && ctype_digit($regelId)) {
    try {
        $ruleStmt = $dbh->prepare('
            SELECT Find_country, Find_city, Find_org
            FROM Rule_center_match
            WHERE R_c_m_id = :regelId
        ');
        $ruleStmt->bindValue(':regelId', (int) $regelId, PDO::PARAM_INT);
        $ruleStmt->execute();
        $rule = $ruleStmt->fetch(PDO::FETCH_ASSOC);

        if ($rule) {
            $land = $land !== '' ? $land : trim((string) ($rule['Find_country'] ?? ''));
            $stad = $stad !== '' ? $stad : trim((string) ($rule['Find_city'] ?? ''));
            $org = $org !== '' ? $org : trim((string) ($rule['Find_org'] ?? ''));
        }
    } catch (PDOException $e) {
        $errors[] = 'Det gick inte att hämta regeln.';
    }
}

if ($regelId !== '' && !ctype_digit($regelId)) {
    $errors[] = 'Regelid måste vara ett heltal.';
}

$canRun = $land !== '' && $org !== '' && (!$regelId || ctype_digit($regelId));
$params = [
    ':land' => $land,
    ':org' => $org,
];

$whereSql = '
    ua.Country_name = :land
    AND UPPER(ua.Name_en) = UPPER(:org)
';

if ($stad !== '') {
    $whereSql .= ' AND UPPER(ua.City) = UPPER(:stad)';
    $params[':stad'] = $stad;
}

$selectSql = "
    SELECT *
    FROM (
        SELECT
            ua.Name_en AS Name,
            ua.City AS City,
            ua.Country_name AS Country_name,
            ua.Org_type_code AS Org_type_code,
            ROW_NUMBER() OVER (ORDER BY ua.Name_en, ua.City, ua.Country_name, ua.Org_type_code) AS row_number
        FROM BIBMET.dbo.Unified_address ua
        WHERE $whereSql
    ) AS numbered_results
    WHERE row_number BETWEEN :firstPageRow AND :lastPageRow
    ORDER BY row_number
";

try {
    if ($canRun) {
        $countStmt = $dbh->prepare("SELECT COUNT(*) FROM BIBMET.dbo.Unified_address ua WHERE $whereSql");
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
    }
} catch (PDOException $e) {
    $errors[] = 'Det gick inte att provköra centrumregeln.';
}

$firstRow = $totalRows === 0 ? 0 : $offset + 1;
$lastRow = min($offset + $pageSize, $totalRows);
$filters = [];

if ($regelId !== '') {
    $filters[] = 'Regelid: ' . $regelId;
}
if ($land !== '') {
    $filters[] = 'Land: ' . $land;
}
if ($stad !== '') {
    $filters[] = 'Stad: ' . $stad;
}
if ($org !== '') {
    $filters[] = 'Organisation: ' . $org;
}

$columns = [
    ['label' => 'Organisation', 'key' => 'Name'],
    ['label' => 'Stad', 'key' => 'City'],
    ['label' => 'Land', 'key' => 'Country_name'],
    ['label' => 'Organisationstyp', 'key' => 'Org_type_code'],
];
$paginationParams = [
    'Land_ut' => $land,
    'Stad_nu' => $stad,
    'Org_nu' => $org,
];
if ($regelId !== '') {
    $paginationParams['Regel_id'] = $regelId;
}
?>

<!DOCTYPE html>
<html lang="sv">

<! Författare: Cecilia Wiklander>
<! Syfte: Adressrättnings-hantering>
<! Ändringar:>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Funna adresser - centraregler</title>
    <link href="Site.css" rel="stylesheet">
    <?php include("include_bibmet_kth.html"); ?>
</head>

<body class="bibmet-body">
    <?php include("include_head_new.html"); ?>

    <main class="bibmet-main">
        <section class="bibmet-hero">
            <div class="bibmet-hero__row">
                <div>
                    <p class="bibmet-eyebrow">Provkör regel</p>
                    <h1 class="bibmet-title">Funna adresser - centraregler</h1>
                    <p class="bibmet-muted">
                        <?php if ($filters) : ?>
                            Filtrerat på <?php echo bibmet_h(implode(', ', $filters)); ?>.
                        <?php else : ?>
                            Ingen regel är vald för provkörning.
                        <?php endif; ?>
                    </p>
                </div>
                <div class="bibmet-action-group">
                    <a href="regel_centra.php" class="bibmet-button bibmet-button--primary">Till sökning</a>
                    <?php if ($regelId !== '' && ctype_digit($regelId)) : ?>
                        <a href="aendra_regel_c.php?Regel_id=<?php echo bibmet_h($regelId); ?>" class="bibmet-button bibmet-button--secondary">Till ändra regel</a>
                    <?php endif; ?>
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

        <?php if (!$canRun && !$errors) : ?>
            <section class="bibmet-panel">
                <div class="bibmet-panel__header">
                    <h2 class="bibmet-panel__title">Välj en regel först</h2>
                </div>
                <div class="bibmet-panel__body">
                    <p class="bibmet-muted" style="margin-top: 0;">
                        Den här sidan används för att provköra en centrumregel. Gå till sökningen, hitta regeln, klicka på <strong>Ändra</strong> och välj sedan <strong>Provkör regel</strong> på ändra-sidan.
                    </p>
                    <div class="bibmet-action-group" style="margin-top: 16px;">
                        <a href="regel_centra.php" class="bibmet-button bibmet-button--primary">Sök centrumregler och välj Ändra</a>
                        <a href="adressmeny.php" class="bibmet-button bibmet-button--secondary">Till menyn</a>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <?php if ($canRun || $errors) : ?>
        <section class="bibmet-panel">
            <div class="bibmet-result-header">
                <div>
                    <h2 class="bibmet-panel__title">Sökresultat</h2>
                    <p class="bibmet-muted">
                        Visar <?php echo bibmet_h($firstRow); ?>-<?php echo bibmet_h($lastRow); ?> av <?php echo bibmet_h($totalRows); ?> adresser.
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
                            <?php foreach ($columns as $column) : ?>
                                <th><?php echo bibmet_h($column['label']); ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!$rows) : ?>
                            <tr>
                                <td colspan="<?php echo bibmet_h(count($columns)); ?>" class="bibmet-empty">
                                    Inga adresser hittades.
                                </td>
                            </tr>
                        <?php endif; ?>

                        <?php foreach ($rows as $row) : ?>
                            <tr>
                                <?php foreach ($columns as $column) : ?>
                                    <td><?php echo bibmet_h($row[$column['key']] ?? ''); ?></td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php bibmet_render_pagination($page, $totalPages, $paginationParams); ?>
        </section>
        <?php endif; ?>
    </main>
</body>

</html>
