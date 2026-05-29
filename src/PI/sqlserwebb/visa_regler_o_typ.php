<?php
require_once __DIR__ . '/sqlsrv_connect.php';
require_once __DIR__ . '/bibmet_ui.php';

$dbh = bibmet_sqlsrv_connect_or_redirect();

$land = bibmet_request_value("Land");
$stad = bibmet_request_value("Stad");
$org1 = bibmet_request_value("Org_1");
$org2 = bibmet_request_value("Org_2");
$orgEj = bibmet_request_value("Org_ej");
$regelid = bibmet_request_value("Regelid");
$page = max(1, (int) bibmet_request_value("page", "1"));
$pageSize = 50;
$offset = ($page - 1) * $pageSize;
$errors = [];
$rows = [];
$totalRows = 0;
$totalPages = 1;

$whereParts = [];
$params = [];

if ($regelid !== "") {
    if (ctype_digit($regelid)) {
        $whereParts[] = "R_o_t_m_id = :regelid";
        $params[":regelid"] = (int) $regelid;
    } else {
        $errors[] = "Regelid måste vara ett heltal.";
        $whereParts[] = "1 = 0";
    }
} else {
    if ($land !== "" && $land !== "Ange land") {
        $whereParts[] = "Country_code IN (
            SELECT Country_code
            FROM Country
            WHERE Display_name = :land
        )";
        $params[":land"] = $land;
    }

    if ($stad !== "") {
        $whereParts[] = "UPPER(Find_city) LIKE UPPER(:stad)";
        $params[":stad"] = "%" . $stad . "%";
    }

    if ($org1 !== "") {
        $whereParts[] = "UPPER(Find_org_1) LIKE UPPER(:org1)";
        $params[":org1"] = "%" . $org1 . "%";
    }

    if ($org2 !== "") {
        $whereParts[] = "UPPER(Find_org_2) LIKE UPPER(:org2)";
        $params[":org2"] = "%" . $org2 . "%";
    }

    if ($orgEj !== "") {
        $whereParts[] = "UPPER(Find_org_not) LIKE UPPER(:orgEj)";
        $params[":orgEj"] = "%" . $orgEj . "%";
    }
}

$whereSql = $whereParts ? implode(" AND ", $whereParts) : "1 = 1";

$baseSelectSql = "
    SELECT
        R_o_t_m_id,
        Find_country,
        Find_city,
        Find_org_1,
        Find_org_2,
        Find_org_not,
        Country,
        City,
        Org_type_code,
        Rule_date
";

$selectSql = "
    SELECT *
    FROM (
        SELECT
            page_source.*,
            ROW_NUMBER() OVER (ORDER BY R_o_t_m_id DESC) AS row_number
        FROM (
            $baseSelectSql
            FROM Rule_org_type_match
            WHERE $whereSql
        ) AS page_source
    ) AS numbered_results
    WHERE row_number BETWEEN :firstPageRow AND :lastPageRow
    ORDER BY row_number
";

try {
    $countStmt = $dbh->prepare("SELECT COUNT(*) FROM Rule_org_type_match WHERE $whereSql");
    foreach ($params as $key => $value) {
        $countStmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
    }
    $countStmt->execute();
    $totalRows = (int) $countStmt->fetchColumn();
    $totalPages = max(1, (int) ceil($totalRows / $pageSize));

    if ($page > $totalPages) {
        $page = $totalPages;
        $offset = ($page - 1) * $pageSize;
    }

    $stmt = $dbh->prepare($selectSql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
    }
    $stmt->bindValue(":firstPageRow", $offset + 1, PDO::PARAM_INT);
    $stmt->bindValue(":lastPageRow", $offset + $pageSize, PDO::PARAM_INT);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $errors[] = "Det gick inte att hämta reglerna. " . $e->getMessage();
}

$firstRow = $totalRows === 0 ? 0 : $offset + 1;
$lastRow = min($offset + $pageSize, $totalRows);
$filters = [];

if ($regelid !== "") {
    $filters[] = "Regelid: " . $regelid;
} else {
    if ($land !== "" && $land !== "Ange land") {
        $filters[] = "Land: " . $land;
    }
    if ($stad !== "") {
        $filters[] = "Stad: " . $stad;
    }
    if ($org1 !== "") {
        $filters[] = "Organisation, söksträng 1: " . $org1;
    }
    if ($org2 !== "") {
        $filters[] = "Organisation, söksträng 2: " . $org2;
    }
    if ($orgEj !== "") {
        $filters[] = "Organisation, söksträng ej: " . $orgEj;
    }
}

$columns = [
    "Land" => "Find_country",
    "Stad" => "Find_city",
    "Organisation, sträng 1" => "Find_org_1",
    "Organisation, sträng 2" => "Find_org_2",
    "Organisation, sträng ej" => "Find_org_not",
    "Land" => "Country",
    "Stad" => "City",
    "Orgtypkod" => "Org_type_code",
    "Regel-id" => "R_o_t_m_id",
    "Datum" => "Rule_date",
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
                <title>Visa regler organisationstyp</title>
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
                                <h1 class="bibmet-title">Visa regler organisationstyp</h1>
                                <p class="bibmet-muted">
                                    <?php if ($filters) : ?>
                                        Filtrerat på <?php echo bibmet_h(implode(", ", $filters)); ?>.
                                    <?php else : ?>
                                        Visar alla organisationstypsregler.
                                    <?php endif; ?>
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
                                        <?php foreach ($columns as $label => $key) : ?>
                                            <th><?php echo bibmet_h($label); ?></th>
                                        <?php endforeach; ?>
                                        <th>Testa</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!$rows) : ?>
                                        <tr>
                                            <td colspan="<?php echo bibmet_h(count($columns) + 2); ?>" class="bibmet-empty">
                                                Inga regler matchar sökningen.
                                            </td>
                                        </tr>
                                    <?php endif; ?>

                                    <?php foreach ($rows as $row) : ?>
                                        <tr>
                                            <td class="bibmet-table__actions">
                                                <div class="bibmet-table__action-row">
                                                    <a
                                                        href="aendra_regel_o_typ.php?Regel_id=<?php echo bibmet_h($row["R_o_t_m_id"]); ?>"
                                                        class="bibmet-button bibmet-button--primary bibmet-button--small">
                                                        Ändra
                                                    </a>
                                                    <a
                                                        href="ta_bort_regel_o_typ.php?Regel_id=<?php echo bibmet_h($row["R_o_t_m_id"]); ?>"
                                                        class="bibmet-button bibmet-button--danger bibmet-button--small">
                                                        Ta bort
                                                    </a>
                                                </div>
                                            </td>
                                            <?php foreach ($columns as $key) : ?>
                                                <td><?php echo bibmet_h($row[$key] ?? ""); ?></td>
                                            <?php endforeach; ?>
                                            <td>
                                                <a
                                                    href="aendra_regel_o_typ_xxx.php?Regel_id=<?php echo bibmet_h($row["R_o_t_m_id"]); ?>"
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
