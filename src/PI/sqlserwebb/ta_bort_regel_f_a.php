<?php
require_once __DIR__ . '/sqlsrv_connect.php';
require_once __DIR__ . '/bibmet_ui.php';

$dbh = bibmet_sqlsrv_connect_or_redirect();

$regel_id = isset($_GET["Regel_id"]) ? (string) $_GET["Regel_id"] : "";
$errors = [];
$rule = null;

if (!ctype_digit($regel_id) || (int) $regel_id <= 0) {
    $errors[] = "Ogiltigt regel-id.";
} else {
    $_SESSION['regel_id'] = $regel_id;

    try {
        $sql = "SELECT r.R_f_a_m_id, r.Find_country, r.Country_code, r.Find_city,
            r.Find_str_1, r.Find_str_2, r.Find_str_3, r.Find_str_not_1, r.Find_str_not_2, r.Divide,
            r.Country_1, r.City_1, o1.Name_en + ' [' + o1.Country_name + ']' AS Orgname_1,
            r.Country_2, r.City_2, o2.Name_en + ' [' + o2.Country_name + ']' AS Orgname_2,
            r.Country_3, r.City_3, o3.Name_en + ' [' + o3.Country_name + ']' AS Orgname_3,
            r.Org_id_1, r.Org_id_2, r.Org_id_3, r.User_id, r.Rule_date
            FROM rule_full_address_match r
            JOIN unified_org_names o1 ON r.Org_id_1 = o1.Unified_org_id
            LEFT JOIN unified_org_names o2 ON r.Org_id_2 = o2.Unified_org_id
            LEFT JOIN unified_org_names o3 ON r.Org_id_3 = o3.Unified_org_id
            WHERE r.R_f_a_m_id = :regel_id";

        $stmt = $dbh->prepare($sql);
        $stmt->bindValue(":regel_id", (int) $regel_id, PDO::PARAM_INT);
        $stmt->execute();
        $rule = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$rule) {
            $errors[] = "Regeln hittades inte.";
        }
    } catch (PDOException $e) {
        $errors[] = "Regeln kunde inte hämtas.";
    }
}
?>

<!DOCTYPE html>
<html lang="sv">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ta bort regel full adress</title>
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
                    <h1 class="bibmet-title">Ta bort regel full adress</h1>
                    <p class="bibmet-muted">Kontrollera regeln och ange orsak innan borttagning.</p>
                </div>
                <div class="bibmet-action-group">
                    <a href="regel_full_adress.php" class="bibmet-button bibmet-button--primary">Till sökning</a>
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

        <?php
        if ($rule) {
            bibmet_render_summary_panel("Regel", [
                "Regel-id" => $rule['R_f_a_m_id'] ?? null,
                "Land" => $rule['Find_country'] ?? null,
                "Stad" => $rule['Find_city'] ?? null,
                "Adress/sträng 1" => $rule['Find_str_1'] ?? null,
                "Adress/sträng 2" => $rule['Find_str_2'] ?? null,
                "Adress/sträng 3" => $rule['Find_str_3'] ?? null,
                "Ej sträng 1" => $rule['Find_str_not_1'] ?? null,
                "Ej sträng 2" => $rule['Find_str_not_2'] ?? null,
                "Delas i" => $rule['Divide'] ?? null,
                "Organisation 1" => $rule['Orgname_1'] ?? null,
                "Organisation 2" => $rule['Orgname_2'] ?? null,
                "Organisation 3" => $rule['Orgname_3'] ?? null,
            ]);
        }
        ?>

        <?php if ($rule) : ?>
            <section class="bibmet-panel">
                <div class="bibmet-panel__header">
                    <h2 class="bibmet-panel__title">Bekräfta borttagning</h2>
                </div>
                <div class="bibmet-panel__body">
                    <form action="ta_bort_regel_resultat_f_a.php" method="post" class="bibmet-form-grid bibmet-form-grid--narrow">
                        <input type="hidden" name="Regel_id" value="<?php echo bibmet_h($regel_id); ?>">

                        <label class="bibmet-field">
                            <span class="bibmet-field__label">Orsak</span>
                            <input class="bibmet-input" type="text" name="orsak" maxlength="100" required>
                            <span class="bibmet-field__hint">Obligatoriskt. Ange varför regeln tas bort.</span>
                        </label>

                        <div class="bibmet-action-group">
                            <button type="submit" name="radera" value="1" class="bibmet-button bibmet-button--danger">Radera regel</button>
                            <a href="regel_full_adress.php" class="bibmet-button bibmet-button--secondary">Avbryt</a>
                        </div>
                    </form>
                </div>
            </section>
        <?php endif; ?>
    </main>
</body>

</html>
