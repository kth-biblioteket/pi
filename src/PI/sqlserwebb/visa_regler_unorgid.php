<?php
require_once __DIR__ . '/sqlsrv_connect.php';

$dbh = bibmet_sqlsrv_connect_or_redirect();

function request_value($key, $default = "")
{
    return isset($_REQUEST[$key]) ? trim((string) $_REQUEST[$key]) : $default;
}

function h($value)
{
    if ($value instanceof DateTimeInterface) {
        $value = $value->format("Y-m-d");
    }

    return htmlspecialchars((string) $value, ENT_QUOTES, "UTF-8");
}

function build_page_url($page)
{
    $params = $_REQUEST;
    $params["page"] = $page;

    return $_SERVER["PHP_SELF"] . "?" . http_build_query($params);
}

$u_org_id = request_value("Unified_org_id");
$page = max(1, (int) request_value("page", "1"));
$pageSize = 50;
$offset = ($page - 1) * $pageSize;
$errors = [];
$rows = [];
$totalRows = 0;
$totalPages = 1;
$organisationName = "";

$columns = [
    "Regeltyp" => "Regeltyp",
    "Regelid" => "Regelid",
    "Land" => "Find_country",
    "Stad" => "Find_city",
    "Org eller sträng 1" => "Find_org_or_str_1",
    "Sträng 2" => "Find_str_2",
    "Sträng 3" => "Find_str_3",
    "Ej sträng 1" => "Find_str_not_1",
    "Ej sträng 2" => "Find_str_not_2",
    "Delas" => "Divide",
    "Land 1" => "Country_1",
    "Stad 1" => "City_1",
    "Un_org_id 1" => "Un_org_id_1",
    "Un_org 1" => "Un_org_1",
    "Land 2" => "Country_2",
    "Stad 2" => "City_2",
    "Un_org_id 2" => "Un_org_id_2",
    "Un_org 2" => "Un_org_2",
    "Land 3" => "Country_3",
    "Stad 3" => "City_3",
    "Un_org_id 3" => "Un_org_id_3",
    "Un_org 3" => "Un_org_3",
];

if (!ctype_digit($u_org_id) || (int) $u_org_id <= 0) {
    $errors[] = "Ogiltigt organisations-id.";
} else {
    $orgId = (int) $u_org_id;

    $baseUnionSql = "
        SELECT
            'C' AS Regeltyp,
            R_c_m_id AS Regelid,
            Find_country,
            Find_city,
            Find_org AS Find_org_or_str_1,
            '' AS Find_str_2,
            '' AS Find_str_3,
            '' AS Find_str_not_1,
            '' AS Find_str_not_2,
            Divide,
            Country_1,
            City_1,
            Org_id_1 AS Un_org_id_1,
            (SELECT Name_en FROM Unified_org_names WHERE Unified_org_id = Org_id_1) AS Un_org_1,
            Country_2,
            City_2,
            Org_id_2 AS Un_org_id_2,
            (SELECT Name_en FROM Unified_org_names WHERE Unified_org_id = Org_id_2) AS Un_org_2,
            Country_3,
            City_3,
            Org_id_3 AS Un_org_id_3,
            (SELECT Name_en FROM Unified_org_names WHERE Unified_org_id = Org_id_3) AS Un_org_3
        FROM Rule_center_match
        WHERE Org_id_1 = :orgIdC1 OR Org_id_2 = :orgIdC2 OR Org_id_3 = :orgIdC3

        UNION ALL

        SELECT
            'O' AS Regeltyp,
            R_o_m_id AS Regelid,
            Find_country,
            Find_city,
            Find_org AS Find_org_or_str_1,
            '' AS Find_str_2,
            '' AS Find_str_3,
            '' AS Find_str_not_1,
            '' AS Find_str_not_2,
            Divide,
            Country_1,
            City_1,
            Org_id_1 AS Un_org_id_1,
            (SELECT Name_en FROM Unified_org_names WHERE Unified_org_id = Org_id_1) AS Un_org_1,
            Country_2,
            City_2,
            Org_id_2 AS Un_org_id_2,
            (SELECT Name_en FROM Unified_org_names WHERE Unified_org_id = Org_id_2) AS Un_org_2,
            Country_3,
            City_3,
            Org_id_3 AS Un_org_id_3,
            (SELECT Name_en FROM Unified_org_names WHERE Unified_org_id = Org_id_3) AS Un_org_3
        FROM Rule_org_match
        WHERE Org_id_1 = :orgIdO1 OR Org_id_2 = :orgIdO2 OR Org_id_3 = :orgIdO3

        UNION ALL

        SELECT
            'FA' AS Regeltyp,
            R_f_a_m_id AS Regelid,
            Find_country,
            Find_city,
            Find_str_1 AS Find_org_or_str_1,
            Find_str_2,
            Find_str_3,
            Find_str_not_1,
            Find_str_not_2,
            Divide,
            Country_1,
            City_1,
            Org_id_1 AS Un_org_id_1,
            (SELECT Name_en FROM Unified_org_names WHERE Unified_org_id = Org_id_1) AS Un_org_1,
            Country_2,
            City_2,
            Org_id_2 AS Un_org_id_2,
            (SELECT Name_en FROM Unified_org_names WHERE Unified_org_id = Org_id_2) AS Un_org_2,
            Country_3,
            City_3,
            Org_id_3 AS Un_org_id_3,
            (SELECT Name_en FROM Unified_org_names WHERE Unified_org_id = Org_id_3) AS Un_org_3
        FROM Rule_full_address_match
        WHERE Org_id_1 = :orgIdFa1 OR Org_id_2 = :orgIdFa2 OR Org_id_3 = :orgIdFa3
    ";

    $selectSql = "
        SELECT *
        FROM (
            SELECT
                rule_source.*,
                ROW_NUMBER() OVER (ORDER BY Regeltyp DESC, Find_org_or_str_1, Regelid DESC) AS row_number
            FROM (
                $baseUnionSql
            ) AS rule_source
        ) AS numbered_results
        WHERE row_number BETWEEN :firstPageRow AND :lastPageRow
        ORDER BY row_number
    ";

    try {
        $nameStmt = $dbh->prepare("SELECT Name_en FROM Unified_org_names WHERE Unified_org_id = :orgId");
        $nameStmt->bindValue(":orgId", $orgId, PDO::PARAM_INT);
        $nameStmt->execute();
        $organisationName = (string) $nameStmt->fetchColumn();

        $countStmt = $dbh->prepare("SELECT COUNT(*) FROM ($baseUnionSql) AS all_rules");
        foreach ([
            ':orgIdC1', ':orgIdC2', ':orgIdC3',
            ':orgIdO1', ':orgIdO2', ':orgIdO3',
            ':orgIdFa1', ':orgIdFa2', ':orgIdFa3',
        ] as $param) {
            $countStmt->bindValue($param, $orgId, PDO::PARAM_INT);
        }
        $countStmt->execute();
        $totalRows = (int) $countStmt->fetchColumn();
        $totalPages = max(1, (int) ceil($totalRows / $pageSize));

        if ($page > $totalPages) {
            $page = $totalPages;
            $offset = ($page - 1) * $pageSize;
        }

        $stmt = $dbh->prepare($selectSql);
        foreach ([
            ':orgIdC1', ':orgIdC2', ':orgIdC3',
            ':orgIdO1', ':orgIdO2', ':orgIdO3',
            ':orgIdFa1', ':orgIdFa2', ':orgIdFa3',
        ] as $param) {
            $stmt->bindValue($param, $orgId, PDO::PARAM_INT);
        }
        $stmt->bindValue(":firstPageRow", $offset + 1, PDO::PARAM_INT);
        $stmt->bindValue(":lastPageRow", $offset + $pageSize, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $errors[] = "Det gick inte att hämta reglerna. " . $e->getMessage();
    }
}

$firstRow = $totalRows === 0 ? 0 : $offset + 1;
$lastRow = min($offset + $pageSize, $totalRows);
?>

<!DOCTYPE html>
<html lang="sv">

<! Författare: Cecilia Wiklander>
<! Syfte: Adressrättnings-hantering>
<! Ändringar: >

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Visa regler organisationsnamn</title>
    <link href="Site.css" rel="stylesheet">
    <?php include("include_bibmet_kth.html"); ?>
</head>

<body class="bibmet-body">
    <?php include("include_head_new.html"); ?>

    <main class="bibmet-main">
        <section class="bibmet-hero">
            <div class="bibmet-hero__row">
                <div>
                    <p class="bibmet-eyebrow">Organisationsnamn</p>
                    <h1 class="bibmet-title">Visa regler organisationsnamn</h1>
                    <p class="bibmet-muted">
                        Organisation: <?php echo h($u_org_id); ?>
                        <?php if ($organisationName !== "") : ?>
                            — <?php echo h($organisationName); ?>
                        <?php endif; ?>
                    </p>
                </div>
                <a href="organisationsnamn.php" class="bibmet-button bibmet-button--primary">Till sökning/sökresultat</a>
            </div>
        </section>

        <?php if ($errors) : ?>
            <div class="bibmet-alert" role="alert">
                <?php foreach ($errors as $error) : ?>
                    <p><?php echo h($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <section class="bibmet-panel">
            <div class="bibmet-result-header">
                <div>
                    <h2 class="bibmet-panel__title">Regler</h2>
                    <p class="bibmet-muted">
                        Visar <?php echo h($firstRow); ?>-<?php echo h($lastRow); ?> av <?php echo h($totalRows); ?> regler.
                    </p>
                </div>
                <p class="bibmet-page-pill">Sida <?php echo h($page); ?> av <?php echo h($totalPages); ?></p>
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
                                <th><?php echo h($label); ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!$rows) : ?>
                            <tr>
                                <td colspan="<?php echo h(count($columns) + 1); ?>" class="bibmet-empty">
                                    Inga regler hittades för organisationsnamnet.
                                </td>
                            </tr>
                        <?php endif; ?>

                        <?php foreach ($rows as $row) : ?>
                            <?php
                            $regeltyp = (string) $row["Regeltyp"];
                            $regelId = (string) $row["Regelid"];
                            $visaPage = [
                                "C" => "visa_regler_c.php",
                                "O" => "visa_regler_o.php",
                                "FA" => "visa_regler_f_a.php",
                            ][$regeltyp] ?? "";
                            $andraPage = [
                                "C" => "aendra_regel_c.php",
                                "O" => "aendra_regel_o.php",
                                "FA" => "aendra_regel_f_a.php",
                            ][$regeltyp] ?? "";
                            ?>
                            <tr>
                                <td class="bibmet-table__actions bibmet-table__actions--middle">
                                    <div class="bibmet-table__action-row">
                                        <?php if ($visaPage !== "") : ?>
                                            <a href="<?php echo h($visaPage); ?>?Regelid=<?php echo h($regelId); ?>" class="bibmet-button bibmet-button--secondary bibmet-button--small">Visa</a>
                                        <?php endif; ?>
                                        <?php if ($andraPage !== "") : ?>
                                            <a href="<?php echo h($andraPage); ?>?Regel_id=<?php echo h($regelId); ?>" class="bibmet-button bibmet-button--primary bibmet-button--small">Ändra</a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <?php foreach ($columns as $key) : ?>
                                    <td><?php echo h($row[$key] ?? ""); ?></td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($totalPages > 1) : ?>
                <nav class="bibmet-pagination" aria-label="Sidnavigering">
                    <a
                        href="<?php echo h(build_page_url(max(1, $page - 1))); ?>"
                        class="<?php echo $page <= 1 ? "bibmet-disabled " : ""; ?>bibmet-button bibmet-button--secondary"
                        aria-disabled="<?php echo $page <= 1 ? "true" : "false"; ?>">
                        Föregående
                    </a>

                    <div class="bibmet-page-list">
                        <?php
                        $startPage = max(1, $page - 2);
                        $endPage = min($totalPages, $page + 2);
                        for ($i = $startPage; $i <= $endPage; $i++) :
                            $isCurrent = $i === $page;
                        ?>
                            <a
                                href="<?php echo h(build_page_url($i)); ?>"
                                class="bibmet-page-link<?php echo $isCurrent ? " bibmet-page-link--current" : ""; ?>"
                                aria-current="<?php echo $isCurrent ? "page" : "false"; ?>">
                                <?php echo h($i); ?>
                            </a>
                        <?php endfor; ?>
                    </div>

                    <a
                        href="<?php echo h(build_page_url(min($totalPages, $page + 1))); ?>"
                        class="<?php echo $page >= $totalPages ? "bibmet-disabled " : ""; ?>bibmet-button bibmet-button--primary"
                        aria-disabled="<?php echo $page >= $totalPages ? "true" : "false"; ?>">
                        Nästa
                    </a>
                </nav>
            <?php endif; ?>
        </section>
    </main>
</body>

</html>
