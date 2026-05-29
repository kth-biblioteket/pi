<?php
require_once __DIR__ . '/sqlsrv_connect.php';
require_once __DIR__ . '/bibmet_ui.php';

$dbh = bibmet_sqlsrv_connect_or_redirect();

if (isset($_GET['clear'])) {
    unset(
        $_SESSION['land'],
        $_SESSION['lokaltnamn'],
        $_SESSION['engelsktnamn'],
        $_SESSION['orgid'],
        $_SESSION['RORid'],
        $_SESSION['Kommentar'],
        $_SESSION['orgtyp_till'],
        $_SESSION['exaktkoll']
    );
}

$hasRequestFilters = !isset($_GET['clear']) && (isset($_REQUEST['Land'])
    || isset($_REQUEST['Lokaltnamn'])
    || isset($_REQUEST['Engelsktnamn'])
    || isset($_REQUEST['Orgid'])
    || isset($_REQUEST['RORid'])
    || isset($_REQUEST['Kommentar'])
    || isset($_REQUEST['Orgtyp_till']));

if ($hasRequestFilters) {
    $land = bibmet_request_value('Land');
    $lok_namn = bibmet_request_value('Lokaltnamn');
    $eng_namn = bibmet_request_value('Engelsktnamn');
    $orgid = bibmet_request_value('Orgid');
    $rorid = bibmet_request_value('RORid');
    $kommentar = bibmet_request_value('Kommentar');
    $orgtyp_till = bibmet_request_value('Orgtyp_till');
    $exaktkoll = isset($_REQUEST['exaktkoll']);

    $_SESSION['land'] = $land;
    $_SESSION['lokaltnamn'] = $lok_namn;
    $_SESSION['engelsktnamn'] = $eng_namn;
    $_SESSION['orgid'] = $orgid;
    $_SESSION['RORid'] = $rorid;
    $_SESSION['Kommentar'] = $kommentar;
    $_SESSION['orgtyp_till'] = $orgtyp_till;
    $_SESSION['exaktkoll'] = $exaktkoll ? '1' : '0';
} else {
    $land = isset($_SESSION['land']) ? $_SESSION['land'] : "";
    $lok_namn = isset($_SESSION['lokaltnamn']) ? $_SESSION['lokaltnamn'] : "";
    $eng_namn = isset($_SESSION['engelsktnamn']) ? $_SESSION['engelsktnamn'] : "";
    $orgid = isset($_SESSION['orgid']) ? $_SESSION['orgid'] : "";
    $rorid = isset($_SESSION['RORid']) ? $_SESSION['RORid'] : "";
    $kommentar = isset($_SESSION['Kommentar']) ? $_SESSION['Kommentar'] : "";
    $orgtyp_till = isset($_SESSION['orgtyp_till']) ? $_SESSION['orgtyp_till'] : "";
    $exaktkoll = isset($_SESSION['exaktkoll']) ? $_SESSION['exaktkoll'] === '1' : true;
}

$page = max(1, (int) bibmet_request_value("page", "1"));
$pageSize = 50;
$offset = ($page - 1) * $pageSize;
$errors = [];
$rows = [];
$totalRows = 0;
$totalPages = 1;
$whereParts = [];
$params = [];
$filters = [];
$countries = [];
$organizationTypes = [];

try {
    $stmt = $dbh->query("SELECT Display_name FROM Country ORDER BY Display_name");
    $countries = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $stmt = $dbh->query("SELECT Org_type_eng FROM Organization_type ORDER BY Org_type_eng");
    $organizationTypes = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $errors[] = "Det gick inte att hämta söklistorna. " . $e->getMessage();
}

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
    <title>Organisationsnamn</title>
    <link href="Site.css" rel="stylesheet">
    <link href="vendor/tom-select/tom-select.css" rel="stylesheet">
    <?php include("include_bibmet_kth.html"); ?>
    <script src="vendor/tom-select/tom-select.complete.min.js"></script>
    <script src="bibmet-selects.js"></script>
</head>

<body class="bibmet-body">
    <?php include("include_head_new.html"); ?>

    <main class="bibmet-main">
        <section class="bibmet-hero">
            <div class="bibmet-hero__row">
                <div>
                    <p class="bibmet-eyebrow">Organisationsnamn</p>
                    <h1 class="bibmet-title">Organisationsnamn</h1>
                    <p class="bibmet-muted">
                        <?php if ($filters) : ?>
                            Filtrerat på <?php echo bibmet_h(implode(", ", $filters)); ?>.
                        <?php else : ?>
                            Visar alla organisationsnamn.
                        <?php endif; ?>
                    </p>
                </div>
                <div class="bibmet-action-group">
                    <a href="ny_organisation.php" class="bibmet-button bibmet-button--primary">Ny organisation</a>
                    <a href="ny_regel_o.php" class="bibmet-button bibmet-button--secondary">Ny regel organisation</a>
                    <a href="ny_regel_f_a.php" class="bibmet-button bibmet-button--secondary">Ny regel full adress</a>
                    <a href="ny_regel_c.php" class="bibmet-button bibmet-button--secondary">Ny regel centra</a>
                    <a href="adressmeny.php" class="bibmet-button bibmet-button--secondary">Till menyn</a>
                </div>
            </div>
        </section>

        <form action="organisationsnamn.php" method="get">
            <section class="bibmet-panel bibmet-panel--collapsible">
                <details class="bibmet-disclosure" open>
                    <summary class="bibmet-disclosure__summary">
                        <span class="bibmet-panel__title">Sökurval</span>
                        <span class="bibmet-disclosure__hint">Visa/dölj sökfilter</span>
                    </summary>

                    <div class="bibmet-form-grid bibmet-form-grid--compact">
                    <label class="bibmet-field">
                        <span class="bibmet-field__label">Orgid</span>
                        <input class="bibmet-input bibmet-input--short" type="text" name="Orgid" value="<?php echo bibmet_h($orgid); ?>">
                    </label>

                    <label class="bibmet-field">
                        <span class="bibmet-field__label">Land</span>
                        <select class="bibmet-select js-bibmet-select" name="Land">
                            <option value="">Ange land</option>
                            <?php foreach ($countries as $country) : ?>
                                <option value="<?php echo bibmet_h($country); ?>"<?php echo bibmet_selected_attr($country, $land); ?>><?php echo bibmet_h($country); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>

                    <label class="bibmet-field">
                        <span class="bibmet-field__label">Lokalt namn</span>
                        <input class="bibmet-input" type="text" name="Lokaltnamn" value="<?php echo bibmet_h($lok_namn); ?>">
                    </label>

                    <label class="bibmet-field">
                        <span class="bibmet-field__label">Engelskt namn</span>
                        <input class="bibmet-input" type="text" name="Engelsktnamn" value="<?php echo bibmet_h($eng_namn); ?>">
                    </label>

                    <label class="bibmet-field">
                        <span class="bibmet-field__label">ROR-id</span>
                        <input class="bibmet-input" type="text" name="RORid" value="<?php echo bibmet_h($rorid); ?>">
                    </label>

                    <label class="bibmet-field">
                        <span class="bibmet-field__label">Organisationstyp</span>
                        <select class="bibmet-select js-bibmet-select" name="Orgtyp_till">
                            <option value="">Ange organisationstyp</option>
                            <?php foreach ($organizationTypes as $organizationType) : ?>
                                <option value="<?php echo bibmet_h($organizationType); ?>"<?php echo bibmet_selected_attr($organizationType, $orgtyp_till); ?>><?php echo bibmet_h($organizationType); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>

                    <label class="bibmet-field">
                        <span class="bibmet-field__label">Kommentar</span>
                        <input class="bibmet-input" type="text" name="Kommentar" value="<?php echo bibmet_h($kommentar); ?>">
                    </label>

                    <label class="bibmet-field">
                        <span class="bibmet-field__label">Exakt namnsökning</span>
                        <input type="checkbox" name="exaktkoll" value="checkbox_value"<?php echo $exaktkoll ? ' checked' : ''; ?>>
                    </label>
                </div>

                    <div class="bibmet-form-actions">
                        <div class="bibmet-action-group">
                            <input class="bibmet-button bibmet-button--primary" type="submit" name="soek" value="Sök organisation">
                            <a class="bibmet-button bibmet-button--secondary" href="organisationsnamn.php?clear=1">Rensa sökning</a>
                        </div>
                    </div>
                </details>
            </section>
        </form>

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
                        Visar <?php echo bibmet_h($firstRow); ?>-<?php echo bibmet_h($lastRow); ?> av <?php echo bibmet_h($totalRows); ?> organisationsnamn.
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
                                    Inga organisationsnamn matchar sökningen.
                                </td>
                            </tr>
                        <?php endif; ?>

                        <?php foreach ($rows as $row) : ?>
                            <?php $orgId = $row["Unified_org_id"]; ?>
                            <tr>
                                <td class="bibmet-table__actions">
                                    <div class="bibmet-table__action-row">
                                        <a href="visa_regler_unorgid.php?Unified_org_id=<?php echo bibmet_h($orgId); ?>" class="bibmet-button bibmet-button--secondary bibmet-button--small">Visa regler</a>
                                        <a href="aendra_organisation.php?Unified_org_id=<?php echo bibmet_h($orgId); ?>" class="bibmet-button bibmet-button--primary bibmet-button--small">Ändra</a>
                                        <a href="ta_bort_organisation.php?Unified_org_id=<?php echo bibmet_h($orgId); ?>" class="bibmet-button bibmet-button--danger bibmet-button--small">Ta bort</a>
                                    </div>
                                </td>
                                <?php foreach ($columns as $key) : ?>
                                    <td><?php echo bibmet_h($row[$key] ?? ""); ?></td>
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
