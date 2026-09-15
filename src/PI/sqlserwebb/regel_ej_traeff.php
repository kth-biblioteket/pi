<?php
require_once __DIR__ . '/sqlsrv_connect.php';
require_once __DIR__ . '/bibmet_ui.php';

$dbh = bibmet_sqlsrv_connect_or_redirect();

$errors = [];
$countries = [];

try {
    $countryStmt = $dbh->query("SELECT Display_name FROM country ORDER BY Display_name");
    $countries = $countryStmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $errors[] = "Det gick inte att hämta länder. " . $e->getMessage();
}

$land = bibmet_request_value('Land');
$typ = bibmet_request_value('Typ', 'org');
$page = max(1, (int) bibmet_request_value('page', '1'));
$pageSize = 50;
$offset = ($page - 1) * $pageSize;
$rows = [];
$totalRows = 0;
$totalPages = 1;

$configs = [
    'org' => [
        'title' => 'Organisation',
        'table' => 'Rule_org_match',
        'id' => 'R_o_m_id',
        'ua_id' => 'R_o_m_id',
        'edit' => 'aendra_regel_o.php',
        'delete' => 'ta_bort_regel_o.php',
        'select' => "
            SELECT
                r.R_o_m_id,
                r.Rule_date,
                r.Find_country,
                r.Find_city,
                r.Find_org,
                r.Divide,
                r.Valid_from,
                r.Valid_to,
                r.Country_1,
                r.City_1,
                r.Org_id_1,
                (SELECT Name_en + ' [' + Country_name + ']' FROM Unified_org_names WHERE Unified_org_id = Org_id_1) AS Org_1,
                r.Country_2,
                r.City_2,
                r.Org_id_2,
                (SELECT Name_en + ' [' + Country_name + ']' FROM Unified_org_names WHERE Unified_org_id = Org_id_2) AS Org_2,
                r.Country_3,
                r.City_3,
                r.Org_id_3,
                (SELECT Name_en + ' [' + Country_name + ']' FROM Unified_org_names WHERE Unified_org_id = Org_id_3) AS Org_3
        ",
        'columns' => [
            'Land' => 'Find_country',
            'Stad' => 'Find_city',
            'Organisation' => 'Find_org',
            'Delas' => 'Divide',
            'Land 1' => 'Country_1',
            'Stad 1' => 'City_1',
            'Org-id 1' => 'Org_id_1',
            'Org 1' => 'Org_1',
            'Land 2' => 'Country_2',
            'Stad 2' => 'City_2',
            'Org-id 2' => 'Org_id_2',
            'Org 2' => 'Org_2',
            'Land 3' => 'Country_3',
            'Stad 3' => 'City_3',
            'Org-id 3' => 'Org_id_3',
            'Org 3' => 'Org_3',
            'Regelid' => 'R_o_m_id',
            'Datum' => 'Rule_date',
            'Gäller från' => 'Valid_from',
            'Gäller till' => 'Valid_to',
        ],
    ],
    'centra' => [
        'title' => 'Centra',
        'table' => 'Rule_center_match',
        'id' => 'R_c_m_id',
        'ua_id' => 'R_c_m_id',
        'edit' => 'aendra_regel_c.php',
        'delete' => 'ta_bort_regel_c.php',
        'select' => "
            SELECT
                r.R_c_m_id,
                r.Find_country,
                r.Find_city,
                r.Find_org,
                r.Divide,
                r.Country_1,
                r.City_1,
                r.Org_id_1,
                (SELECT Name_en FROM Unified_org_names WHERE Unified_org_id = Org_id_1) AS Org_1,
                r.Country_2,
                r.City_2,
                r.Org_id_2,
                (SELECT Name_en FROM Unified_org_names WHERE Unified_org_id = Org_id_2) AS Org_2,
                r.Country_3,
                r.City_3,
                r.Org_id_3,
                (SELECT Name_en FROM Unified_org_names WHERE Unified_org_id = Org_id_3) AS Org_3
        ",
        'columns' => [
            'Land' => 'Find_country',
            'Stad' => 'Find_city',
            'Organisation' => 'Find_org',
            'Delas' => 'Divide',
            'Land 1' => 'Country_1',
            'Stad 1' => 'City_1',
            'Org-id 1' => 'Org_id_1',
            'Org 1' => 'Org_1',
            'Land 2' => 'Country_2',
            'Stad 2' => 'City_2',
            'Org-id 2' => 'Org_id_2',
            'Org 2' => 'Org_2',
            'Land 3' => 'Country_3',
            'Stad 3' => 'City_3',
            'Org-id 3' => 'Org_id_3',
            'Org 3' => 'Org_3',
            'Regelid' => 'R_c_m_id',
        ],
    ],
    'fa' => [
        'title' => 'Full adress',
        'table' => 'Rule_full_address_match',
        'id' => 'R_f_a_m_id',
        'ua_id' => 'R_f_a_m_id',
        'edit' => 'aendra_regel_f_a.php',
        'delete' => 'ta_bort_regel_f_a.php',
        'select' => "
            SELECT
                r.R_f_a_m_id,
                r.Find_country,
                r.Find_city,
                r.Find_str_1,
                r.Find_str_2,
                r.Find_str_3,
                r.Find_str_not_1,
                r.Find_str_not_2,
                r.Divide,
                r.Country_1,
                r.City_1,
                r.Org_id_1,
                (SELECT Name_en FROM Unified_org_names WHERE Unified_org_id = Org_id_1) AS Org_1,
                r.Country_2,
                r.City_2,
                r.Org_id_2,
                (SELECT Name_en FROM Unified_org_names WHERE Unified_org_id = Org_id_2) AS Org_2,
                r.Country_3,
                r.City_3,
                r.Org_id_3,
                (SELECT Name_en FROM Unified_org_names WHERE Unified_org_id = Org_id_3) AS Org_3
        ",
        'columns' => [
            'Land' => 'Find_country',
            'Stad' => 'Find_city',
            'Söksträng 1' => 'Find_str_1',
            'Söksträng 2' => 'Find_str_2',
            'Söksträng 3' => 'Find_str_3',
            'Söksträng ej 1' => 'Find_str_not_1',
            'Söksträng ej 2' => 'Find_str_not_2',
            'Delas' => 'Divide',
            'Land 1' => 'Country_1',
            'Stad 1' => 'City_1',
            'Org-id 1' => 'Org_id_1',
            'Org 1' => 'Org_1',
            'Land 2' => 'Country_2',
            'Stad 2' => 'City_2',
            'Org-id 2' => 'Org_id_2',
            'Org 2' => 'Org_2',
            'Land 3' => 'Country_3',
            'Stad 3' => 'City_3',
            'Org-id 3' => 'Org_id_3',
            'Org 3' => 'Org_3',
            'Regelid' => 'R_f_a_m_id',
        ],
    ],
    'orgtyp' => [
        'title' => 'Organisationstyp',
        'table' => 'Rule_org_type_match',
        'id' => 'R_o_t_m_id',
        'ua_id' => 'R_o_t_m_id',
        'edit' => 'aendra_regel_o_typ.php',
        'delete' => 'ta_bort_regel_o_typ.php',
        'select' => "
            SELECT
                r.R_o_t_m_id,
                r.Find_country,
                r.Find_city,
                r.Find_org_1,
                r.Find_org_2,
                r.Find_org_not,
                r.Country,
                r.City,
                r.Org_type_code
        ",
        'columns' => [
            'Land' => 'Find_country',
            'Stad' => 'Find_city',
            'Organisation 1' => 'Find_org_1',
            'Organisation 2' => 'Find_org_2',
            'Organisation ej' => 'Find_org_not',
            'Land mål' => 'Country',
            'Stad mål' => 'City',
            'Orgtypkod' => 'Org_type_code',
            'Regelid' => 'R_o_t_m_id',
        ],
    ],
];

if (!isset($configs[$typ])) {
    $errors[] = 'Ogiltig regeltyp.';
    $typ = 'org';
}

$config = $configs[$typ];
$params = [];
$whereParts = ["NOT EXISTS (SELECT 1 FROM BIBMET.dbo.Unified_address ua WHERE ua." . $config['ua_id'] . " = r." . $config['id'] . ")"];

if ($land !== '' && $land !== 'Ange land') {
    $whereParts[] = "r.Country_code IN (SELECT Country_code FROM Country WHERE Display_name = :land)";
    $params[':land'] = $land;
}

$whereSql = implode(' AND ', $whereParts);
$baseSelectSql = $config['select'];
$idColumn = $config['id'];
$tableName = $config['table'];

$selectSql = "
    SELECT *
    FROM (
        SELECT
            page_source.*,
            ROW_NUMBER() OVER (ORDER BY $idColumn DESC) AS row_number
        FROM (
            $baseSelectSql
            FROM $tableName r
            WHERE $whereSql
        ) AS page_source
    ) AS numbered_results
    WHERE row_number BETWEEN :firstPageRow AND :lastPageRow
    ORDER BY row_number
";

try {
    $countStmt = $dbh->prepare("SELECT COUNT(*) FROM $tableName r WHERE $whereSql");
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
    $errors[] = 'Det gick inte att hämta reglerna. ' . $e->getMessage();
}

$firstRow = $totalRows === 0 ? 0 : $offset + 1;
$lastRow = min($offset + $pageSize, $totalRows);
$filters = [];
if ($land !== '' && $land !== 'Ange land') {
    $filters[] = 'Land: ' . $land;
}
$filters[] = 'Regeltyp: ' . $config['title'];
$columns = $config['columns'];
?>

<!DOCTYPE html>
<html lang="sv">

<! Författare: Cecilia Wiklander>
<! Syfte: Adressrättnings-hantering>
<! Ändringar: >

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Regler som ej träffar</title>
    <link href="Site.css" rel="stylesheet">
    <link href="vendor/tom-select/tom-select.css" rel="stylesheet">
    <?php include("include_bibmet_kth.html"); ?>
    <script src="vendor/tom-select/tom-select.complete.min.js"></script>
    <script src="bibmet-selects.js"></script>
</head>

<body class="bibmet-body">
    <?php include('include_head_new.html'); ?>

    <main class="bibmet-main">
        <section class="bibmet-hero">
            <div class="bibmet-hero__row">
                <div>
                    <p class="bibmet-eyebrow">Adressrättningsregler</p>
                    <h1 class="bibmet-title">Regler som ej träffar</h1>
                    <p class="bibmet-muted">Lista regler utan träffar per land och regeltyp.</p>
                </div>
                <div class="bibmet-action-group">
                    <a href="regel_ej_traeff.php" class="bibmet-button bibmet-button--secondary">Rensa sökning</a>
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

        <form action="regel_ej_traeff.php" method="get" class="bibmet-panel">
            <div class="bibmet-panel__header">
                <h2 class="bibmet-panel__title">Sökurval</h2>
            </div>

            <div class="bibmet-form-grid bibmet-form-grid--narrow">
                <label class="bibmet-field">
                    <span class="bibmet-field__label">Land</span>
                    <select class="bibmet-select js-bibmet-select" id="id_s_land" name="Land">
                        <option value="">Alla länder</option>
                        <?php foreach ($countries as $country) : ?>
                            <option value="<?php echo bibmet_h($country); ?>"<?php echo bibmet_selected_attr($country, $land); ?>><?php echo bibmet_h($country); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <span class="bibmet-field__hint">Lämna tomt för att visa alla länder.</span>
                </label>

                <fieldset class="bibmet-field bibmet-choice-field">
                    <legend class="bibmet-field__label">Regeltyp</legend>
                    <div class="bibmet-choice-list">
                        <?php foreach ($configs as $value => $ruleConfig) : ?>
                            <label class="bibmet-choice">
                                <input type="radio" name="Typ" value="<?php echo bibmet_h($value); ?>"<?php echo (string) $value === (string) $typ ? ' checked' : ''; ?>>
                                <span><?php echo bibmet_h($ruleConfig['title']); ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </fieldset>
            </div>

            <div class="bibmet-form-actions">
                <div class="bibmet-action-group">
                    <input type="submit" name="soek" value="Lista regler" class="bibmet-button bibmet-button--primary">
                    <a href="regel_ej_traeff.php" class="bibmet-button bibmet-button--secondary">Rensa</a>
                </div>
            </div>
        </form>

        <section class="bibmet-panel">
            <div class="bibmet-result-header">
                <div>
                    <h2 class="bibmet-panel__title">Sökresultat</h2>
                    <p class="bibmet-muted">
                        Visar <?php echo bibmet_h($firstRow); ?>-<?php echo bibmet_h($lastRow); ?> av <?php echo bibmet_h($totalRows); ?> regler utan träff.
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
                            <?php foreach ($columns as $label => $key) : ?>
                                <th><?php echo bibmet_h($label); ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!$rows) : ?>
                            <tr>
                                <td colspan="<?php echo bibmet_h(count($columns) + 1); ?>" class="bibmet-empty">
                                    Inga regler utan träff matchar sökningen.
                                </td>
                            </tr>
                        <?php endif; ?>

                        <?php foreach ($rows as $row) : ?>
                            <?php $regelId = $row[$idColumn]; ?>
                            <tr>
                                <td class="bibmet-table__actions">
                                    <div class="bibmet-table__action-row">
                                        <a href="<?php echo bibmet_h($config['edit']); ?>?Regel_id=<?php echo bibmet_h($regelId); ?>" class="bibmet-button bibmet-button--primary bibmet-button--small">Ändra</a>
                                        <a href="<?php echo bibmet_h($config['delete']); ?>?Regel_id=<?php echo bibmet_h($regelId); ?>" class="bibmet-button bibmet-button--danger bibmet-button--small">Ta bort</a>
                                    </div>
                                </td>
                                <?php foreach ($columns as $key) : ?>
                                    <td><?php echo bibmet_h($row[$key] ?? ''); ?></td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php bibmet_render_pagination($page, $totalPages, ['Land' => $land, 'Typ' => $typ]); ?>
        </section>
    </main>
</body>

</html>
