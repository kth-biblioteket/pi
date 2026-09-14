<?php
require_once __DIR__ . '/sqlsrv_connect.php';
require_once __DIR__ . '/bibmet_ui.php';

$dbh = bibmet_sqlsrv_connect_or_redirect();

function rule_post_value($key) { return isset($_POST[$key]) ? trim((string) $_POST[$key]) : ""; }
function normalize_select_value($value, $placeholder) { return $value === $placeholder ? null : $value; }
function parse_org_label($label) {
    $pos_f = strpos($label, '['); $pos_e = strpos($label, ']');
    if ($pos_f === false || $pos_e === false || $pos_e <= $pos_f) { return [trim($label), ""]; }
    return [trim(substr($label, 0, $pos_f)), trim(substr($label, $pos_f + 1, $pos_e - $pos_f - 1))];
}
function find_org_id(PDO $dbh, $label) {
    [$name, $country] = parse_org_label($label);
    if ($name === "") { return null; }
    if ($country !== "") {
        $stmt = $dbh->prepare("SELECT Unified_org_id FROM Unified_org_names WHERE Name_en = :name AND Country_name = :country");
        $stmt->bindValue(':country', $country, PDO::PARAM_STR);
    } else {
        $stmt = $dbh->prepare("SELECT Unified_org_id FROM Unified_org_names WHERE Name_en = :name");
    }
    $stmt->bindValue(':name', $name, PDO::PARAM_STR);
    $stmt->execute();
    $value = $stmt->fetchColumn();
    return $value === false ? null : (int) $value;
}
function bind_nullable(PDOStatement $stmt, $name, $value, $type = PDO::PARAM_STR) {
    if ($value === null || $value === "") { $stmt->bindValue($name, null, PDO::PARAM_NULL); }
    else { $stmt->bindValue($name, $value, $type); }
}

$regel_id = isset($_SESSION['regel_id']) ? (string) $_SESSION['regel_id'] : "";
$_SESSION['regel_id_ut'] = $regel_id;
$username = isset($_SESSION['anv']) ? $_SESSION['anv'] : "";
$a_regel_o_id = isset($_SESSION['a_regel_o_id']) ? (string) $_SESSION['a_regel_o_id'] : "";

$land_till = rule_post_value('Land_ut_2');
$stad_till = rule_post_value('Stad_till');
$org_till = rule_post_value('Org_till');
$delas_till = rule_post_value('Delas_ut_2');
$land_1_till = rule_post_value('Land_1_ut_2');
$land_2_till = rule_post_value('Land_2_ut_2');
$land_3_till = rule_post_value('Land_3_ut_2');
$stad_1_till = rule_post_value('Stad_1_till');
$stad_2_till = rule_post_value('Stad_2_till');
$stad_3_till = rule_post_value('Stad_3_till');
$org_1_till = rule_post_value('Org_1_ut_2');
$org_2_till = rule_post_value('Org_2_ut_2');
$org_3_till = rule_post_value('Org_3_ut_2');
$fr = rule_post_value('Fr');
$ti = rule_post_value('Ti');

$alerts = [];
$koll_svar = false;

if (!ctype_digit($regel_id) || (int) $regel_id <= 0) { $alerts[] = 'Ogiltigt regel-id!'; }
elseif ($land_till == 'Ange land') { $alerts[] = 'Land måste anges som sökfält!'; }
elseif (strlen($org_till) == 0) { $alerts[] = 'Organisation måste anges som sökfält!'; }
elseif ($org_1_till == 'Ange organisation' || strlen($org_1_till) == 0) { $alerts[] = 'Organisation 1 måste anges som ändringsfält!'; }
elseif ($delas_till == 1) {
    if (($org_2_till != 'Ange organisation' || $org_3_till != 'Ange organisation') && (strlen($org_2_till) > 0 || strlen($org_3_till) > 0)) { $alerts[] = 'Antalet i Delas stämmer inte med antal angivna organisationer!'; }
    else { $koll_svar = true; }
} elseif ($delas_till == 2) {
    if ($org_2_till == 'Ange organisation' || strlen($org_2_till) == 0 || ($org_3_till != 'Ange organisation' && strlen($org_3_till) > 0)) { $alerts[] = 'Antalet i Delas stämmer inte med antal angivna organisationer!'; }
    else { $koll_svar = true; }
} elseif ($delas_till == 3) {
    if ($org_2_till == 'Ange organisation' || $org_3_till == 'Ange organisation' || strlen($org_2_till) == 0 || strlen($org_3_till) == 0) { $alerts[] = 'Antalet i Delas stämmer inte med antal angivna organisationer!'; }
    else { $koll_svar = true; }
} else { $alerts[] = 'Antalet i Delas kan vara mellan 1 och 3!'; }

$land_1_db = normalize_select_value($land_1_till, 'Ange land');
$land_2_db = normalize_select_value($land_2_till, 'Ange land');
$land_3_db = normalize_select_value($land_3_till, 'Ange land');

if ($koll_svar && $a_regel_o_id !== $regel_id) {
    try {
        $org_id_1 = find_org_id($dbh, $org_1_till);
        $org_id_2 = ((int) $delas_till > 1 && $org_2_till != 'Ange organisation') ? find_org_id($dbh, $org_2_till) : null;
        $org_id_3 = ((int) $delas_till > 2 && $org_3_till != 'Ange organisation') ? find_org_id($dbh, $org_3_till) : null;

        $countryStmt = $dbh->prepare("SELECT Country_code FROM Country WHERE Display_name = :land");
        $countryStmt->bindValue(':land', $land_till, PDO::PARAM_STR);
        $countryStmt->execute();
        $country_code = $countryStmt->fetchColumn();

        $sql = "UPDATE Rule_org_match SET Find_country = :find_country, Country_code = :country_code, Find_city = :find_city,
            Find_org = :find_org, Divide = :divide, Country_1 = :country_1, City_1 = :city_1, Org_id_1 = :org_id_1,
            Country_2 = :country_2, City_2 = :city_2, Org_id_2 = :org_id_2, Country_3 = :country_3, City_3 = :city_3,
            Org_id_3 = :org_id_3, User_id = :user_id, Rule_date = GETDATE(), Run_status = 1,
            Valid_from = :valid_from, Valid_to = :valid_to WHERE R_o_m_id = :regel_id";
        $stmt = $dbh->prepare($sql);
        $stmt->bindValue(':find_country', $land_till, PDO::PARAM_STR);
        bind_nullable($stmt, ':country_code', $country_code);
        bind_nullable($stmt, ':find_city', $stad_till);
        $stmt->bindValue(':find_org', $org_till, PDO::PARAM_STR);
        $stmt->bindValue(':divide', (int) $delas_till, PDO::PARAM_INT);
        bind_nullable($stmt, ':country_1', $land_1_db);
        bind_nullable($stmt, ':city_1', $stad_1_till);
        bind_nullable($stmt, ':org_id_1', $org_id_1, PDO::PARAM_INT);
        bind_nullable($stmt, ':country_2', (int) $delas_till >= 2 ? $land_2_db : null);
        bind_nullable($stmt, ':city_2', (int) $delas_till >= 2 ? $stad_2_till : null);
        bind_nullable($stmt, ':org_id_2', (int) $delas_till >= 2 ? $org_id_2 : null, PDO::PARAM_INT);
        bind_nullable($stmt, ':country_3', (int) $delas_till >= 3 ? $land_3_db : null);
        bind_nullable($stmt, ':city_3', (int) $delas_till >= 3 ? $stad_3_till : null);
        bind_nullable($stmt, ':org_id_3', (int) $delas_till >= 3 ? $org_id_3 : null, PDO::PARAM_INT);
        $stmt->bindValue(':user_id', $username, PDO::PARAM_STR);
        bind_nullable($stmt, ':valid_from', $fr === '' ? null : (int) $fr, PDO::PARAM_INT);
        bind_nullable($stmt, ':valid_to', $ti === '' ? null : (int) $ti, PDO::PARAM_INT);
        $stmt->bindValue(':regel_id', (int) $regel_id, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() > 0) { $alerts[] = 'Regeln är nu ändrad!'; $_SESSION['a_regel_o_id'] = $regel_id; }
        else { $alerts[] = 'Fel vid ändring av regeln!'; }
    } catch (PDOException $e) { $alerts[] = 'Fel vid ändring av regeln!'; }
}
?>

<!DOCTYPE html PUBLIC "-//w3c//DTD XHTMLm 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<head><meta charset="utf-8"><title>ÄNDRA REGEL ORGANISATION</title><link href="Site_utan_storlek.css" rel="stylesheet"></head>
<body>
<?php include('include_head_new.html'); ?>
<?php foreach ($alerts as $alert) : ?><script>alert("<?php echo bibmet_h($alert); ?>");</script><?php endforeach; ?>
<h2>ÄNDRA REGEL ORGANISATION</h2>
<form action="aendra_regel_resultat_o.php" method="post">
<a href='aendra_regel_o.php'>TILLBAKA</a>&nbsp;&nbsp;<a href='regel_organisation.php'>TILL SÖKNING</a>&nbsp;&nbsp;<a href='adressmeny.php'>TILL MENYN</a><br /><br />
<h3>SÖKFÄLT</h3>
Land:</br><input type="text" value="<?php echo bibmet_h($land_till); ?>" disabled size="40" /><br />
Stad:</br><input type="text" value="<?php echo bibmet_h($stad_till); ?>" disabled size="40" /><br />
Organisationsnamn:</br><input type="text" value="<?php echo bibmet_h($org_till); ?>" disabled size="40" /><br />
<h3>ÄNDRINGSFÄLT</h3>
Delas i:</br><input type="text" size="1" value="<?php echo bibmet_h($delas_till); ?>" disabled />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Gäller från: <input type="text" value="<?php echo bibmet_h($fr); ?>" size="4" disabled />&nbsp;&nbsp; till: <input type="text" value="<?php echo bibmet_h($ti); ?>" size="4" disabled /><br />
<b>Organisation 1:</b><br />Annat organisationsnamn:<br /><input type="text" size="40" value="<?php echo bibmet_h($org_1_till); ?>" disabled /><br />Annat land:<br /><input type="text" size="40" value="<?php echo bibmet_h($land_1_till); ?>" disabled /><br />Annan stad:<br /><input type="text" size="40" value="<?php echo bibmet_h($stad_1_till); ?>" disabled /><br /><br />
<b>Organisation 2:</b><br />Annat organisationsnamn:<br /><input type="text" size="40" value="<?php echo bibmet_h($org_2_till); ?>" disabled /><br />Annat land:<br /><input type="text" size="40" value="<?php echo bibmet_h($land_2_till); ?>" disabled /><br />Annan stad:<br /><input type="text" size="40" value="<?php echo bibmet_h($stad_2_till); ?>" disabled /><br /><br />
<b>Organisation 3:</b><br />Annat organisationsnamn:<br /><input type="text" size="40" value="<?php echo bibmet_h($org_3_till); ?>" disabled /><br />Annat land:<br /><input type="text" size="40" value="<?php echo bibmet_h($land_3_till); ?>" disabled /><br />Annan stad:<br /><input type="text" size="40" value="<?php echo bibmet_h($stad_3_till); ?>" disabled /><br /><br />
</form>
</body>
</html>
