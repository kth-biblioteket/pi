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

function bind_search_param($stmt, $key, $value)
{
    $stmt->bindValue($key, "%" . $value . "%", PDO::PARAM_STR);
}

$hasRequestFilters = isset($_REQUEST['Land'])
    || isset($_REQUEST['Lokaltnamn'])
    || isset($_REQUEST['Engelsktnamn'])
    || isset($_REQUEST['Orgtyp'])
    || isset($_REQUEST['Orgid'])
    || isset($_REQUEST['RORid'])
    || isset($_REQUEST['Kommentar'])
    || isset($_REQUEST['Orgtyp_till']);

if ($hasRequestFilters) {
    $land = request_value('Land');
    $lok_namn = request_value('Lokaltnamn');
    $eng_namn = request_value('Engelsktnamn');
    $orgtyp = request_value('Orgtyp');
    $orgid = request_value('Orgid');
    $rorid = request_value('RORid');
    $kommentar = request_value('Kommentar');
    $orgtyp_till = request_value('Orgtyp_till');
    $exaktkoll = isset($_REQUEST['exaktkoll']);

    $_SESSION['land'] = $land;
    $_SESSION['lokaltnamn'] = $lok_namn;
    $_SESSION['engelsktnamn'] = $eng_namn;
    $_SESSION['orgtyp'] = $orgtyp;
    $_SESSION['orgid'] = $orgid;
    $_SESSION['RORid'] = $rorid;
    $_SESSION['Kommentar'] = $kommentar;
    $_SESSION['orgtyp_till'] = $orgtyp_till;
    $_SESSION['exaktkoll'] = $exaktkoll ? '1' : '0';
} else {
    $land = isset($_SESSION['land']) ? $_SESSION['land'] : "";
    $lok_namn = isset($_SESSION['lokaltnamn']) ? $_SESSION['lokaltnamn'] : "";
    $eng_namn = isset($_SESSION['engelsktnamn']) ? $_SESSION['engelsktnamn'] : "";
    $orgtyp = isset($_SESSION['orgtyp']) ? $_SESSION['orgtyp'] : "";
    $orgid = isset($_SESSION['orgid']) ? $_SESSION['orgid'] : "";
    $rorid = isset($_SESSION['RORid']) ? $_SESSION['RORid'] : "";
    $kommentar = isset($_SESSION['Kommentar']) ? $_SESSION['Kommentar'] : "";
    $orgtyp_till = isset($_SESSION['orgtyp_till']) ? $_SESSION['orgtyp_till'] : "";
    $exaktkoll = isset($_SESSION['exaktkoll']) ? $_SESSION['exaktkoll'] === '1' : true;
}

$page = max(1, (int) request_value("page", "1"));
$pageSize = 50;
$offset = ($page - 1) * $pageSize;
$errors = [];
$rows = [];
$totalRows = 0;
$totalPages = 1;
$whereParts = [];
$params = [];
$filters = [];

if ($orgid !== "") {
    if (ctype_digit($orgid)) {
        $whereParts[] = "Unified_org_id = :orgid";
        $params[":orgid"] = (int) $orgid;
        $filters[] = "Orgid: " . $orgid;
    } else {
        $errors[] = "Orgid måste vara ett heltal.";
        $whereParts[] = "1 = 0";
    }
} else {
    if ($land !== "" && $land !== "Ange land") {
        $whereParts[] = "Country_name = :land";
        $params[":land"] = $land;
        $filters[] = "Land: " . $land;
    }

    if ($lok_namn !== "") {
        $whereParts[] = $exaktkoll
            ? "UPPER(Name_local) LIKE UPPER(:lok_namn)"
            : "UPPER(Name_local) COLLATE Latin1_General_CI_AI LIKE UPPER(:lok_namn)";
        $params[":lok_namn"] = "%" . $lok_namn . "%";
        $filters[] = "Lokalt namn: " . $lok_namn;
    }

    if ($eng_namn !== "") {
        $whereParts[] = $exaktkoll
            ? "UPPER(Name_en) LIKE UPPER(:eng_namn)"
            : "UPPER(Name_en) COLLATE Latin1_General_CI_AI LIKE UPPER(:eng_namn)";
        $params[":eng_namn"] = "%" . $eng_namn . "%";
        $filters[] = "Engelskt namn: " . $eng_namn;
    }

    if ($orgtyp_till !== "" && $orgtyp_till !== "Ange organisationstyp") {
        try {
            $stmt = $dbh->prepare("SELECT Org_type_code FROM Organization_type WHERE Org_type_eng = :orgtyp");
            $stmt->bindValue(":orgtyp", $orgtyp_till, PDO::PARAM_STR);
            $stmt->execute();
            $org_type_code = $stmt->fetchColumn();

            if ($org_type_code !== false) {
                $whereParts[] = "UPPER(Org_type_code) LIKE UPPER(:org_type_code)";
                $params[":org_type_code"] = "%" . $org_type_code . "%";
                $filters[] = "Organisationstyp: " . $orgtyp_till;
            }
        } catch (PDOException $e) {
            $errors[] = "Det gick inte att hämta organisationstypen. " . $e->getMessage();
        }
    }

    if ($rorid !== "") {
        $whereParts[] = "UPPER(ROR_id) LIKE UPPER(:rorid)";
        $params[":rorid"] = "%" . $rorid . "%";
        $filters[] = "ROR-id: " . $rorid;
    }

    if ($kommentar !== "") {
        $whereParts[] = "UPPER(Comment) LIKE UPPER(:kommentar)";
        $params[":kommentar"] = "%" . $kommentar . "%";
        $filters[] = "Kommentar: " . $kommentar;
    }
}

$whereSql = $whereParts ? implode(" AND ", $whereParts) : "1 = 1";
$baseSelectSql = "
    SELECT
        Unified_org_id,
        Name_local,
        Name_en,
        Country_name,
        Org_type_code,
        Comment,
        ROR_id
    FROM unified_org_names
    WHERE $whereSql
";

$selectSql = "
    SELECT *
    FROM (
        SELECT
            page_source.*,
            ROW_NUMBER() OVER (ORDER BY Unified_org_id DESC) AS row_number
        FROM (
            $baseSelectSql
        ) AS page_source
    ) AS numbered_results
    WHERE row_number BETWEEN :firstPageRow AND :lastPageRow
    ORDER BY row_number
";

try {
    $countStmt = $dbh->prepare("SELECT COUNT(*) FROM unified_org_names WHERE $whereSql");
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
    $errors[] = "Det gick inte att hämta organisationsnamnen. " . $e->getMessage();
}

$firstRow = $totalRows === 0 ? 0 : $offset + 1;
$lastRow = min($offset + $pageSize, $totalRows);

$columns = [
    "Lokalt namn" => "Name_local",
    "Engelskt namn" => "Name_en",
    "Land" => "Country_name",
    "Org.typ" => "Org_type_code",
    "Kommentar" => "Comment",
    "Orgid" => "Unified_org_id",
    "ROR-id" => "ROR_id",
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
    <title>Visa organisationsnamn</title>
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
                    <h1 class="bibmet-title">Visa organisationsnamn</h1>
                    <p class="bibmet-muted">
                        <?php if ($filters) : ?>
                            Filtrerat på <?php echo h(implode(", ", $filters)); ?>.
                        <?php else : ?>
                            Visar alla organisationsnamn.
                        <?php endif; ?>
                    </p>
                </div>
                <a href="organisationsnamn.php" class="bibmet-button bibmet-button--primary">Till sökning</a>
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
                    <h2 class="bibmet-panel__title">Sökresultat</h2>
                    <p class="bibmet-muted">
                        Visar <?php echo h($firstRow); ?>-<?php echo h($lastRow); ?> av <?php echo h($totalRows); ?> organisationsnamn.
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
                                    Inga organisationsnamn matchar sökningen.
                                </td>
                            </tr>
                        <?php endif; ?>

                        <?php foreach ($rows as $row) : ?>
                            <?php $orgId = $row["Unified_org_id"]; ?>
                            <tr>
                                <td class="bibmet-table__actions">
                                    <div class="bibmet-table__action-row">
                                        <a href="visa_regler_unorgid.php?Unified_org_id=<?php echo h($orgId); ?>" class="bibmet-button bibmet-button--secondary bibmet-button--small">Visa regler</a>
                                        <a href="aendra_organisation.php?Unified_org_id=<?php echo h($orgId); ?>" class="bibmet-button bibmet-button--primary bibmet-button--small">Ändra</a>
                                        <a href="ta_bort_organisation.php?Unified_org_id=<?php echo h($orgId); ?>" class="bibmet-button bibmet-button--danger bibmet-button--small">Ta bort</a>
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
