<?php
require_once __DIR__ . '/sqlsrv_connect.php';
require_once __DIR__ . '/bibmet_ui.php';

$dbh = bibmet_sqlsrv_connect_or_redirect();

$regel_id = bibmet_request_value('Regel_id');
$errors = [];
$rule = null;

if (!ctype_digit($regel_id) || (int) $regel_id <= 0) {
    $errors[] = "Ogiltigt regel-id.";
} else {
    $_SESSION['regel_id'] = $regel_id;

    try {
        $sql = "SELECT r.R_o_m_id, r.Find_country, r.Country_code, r.Find_city, r.Find_org, r.Divide,
            r.Country_1, r.City_1, o1.Name_en + ' [' + o1.Country_name + ']' AS Orgname_1,
            r.Country_2, r.City_2, o2.Name_en + ' [' + o2.Country_name + ']' AS Orgname_2,
            r.Country_3, r.City_3, o3.Name_en + ' [' + o3.Country_name + ']' AS Orgname_3,
            r.Org_id_1, r.Org_id_2, r.Org_id_3, r.User_id, r.Rule_date, r.Valid_from, r.Valid_to
            FROM rule_org_match r
            JOIN unified_org_names o1 ON r.Org_id_1 = o1.Unified_org_id
            LEFT JOIN unified_org_names o2 ON r.Org_id_2 = o2.Unified_org_id
            LEFT JOIN unified_org_names o3 ON r.Org_id_3 = o3.Unified_org_id
            WHERE r.R_o_m_id = :regel_id";
        $stmt = $dbh->prepare($sql);
        $stmt->bindValue(':regel_id', (int) $regel_id, PDO::PARAM_INT);
        $stmt->execute();
        $rule = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$rule) {
            $errors[] = "Regeln hittades inte.";
        } else {
            $_SESSION['land_s'] = $rule['Find_country'];
            $_SESSION['land_1'] = $rule['Country_1'];
            $_SESSION['land_2'] = $rule['Country_2'];
            $_SESSION['land_3'] = $rule['Country_3'];
            $_SESSION['stad_s'] = $rule['Find_city'];
            $_SESSION['stad_1'] = $rule['City_1'];
            $_SESSION['stad_2'] = $rule['City_2'];
            $_SESSION['stad_3'] = $rule['City_3'];
            $_SESSION['org_s'] = $rule['Find_org'];
            $_SESSION['delas'] = $rule['Divide'];
            $_SESSION['org_id_1'] = $rule['Org_id_1'];
            $_SESSION['org_id_2'] = $rule['Org_id_2'];
            $_SESSION['org_id_3'] = $rule['Org_id_3'];
            $_SESSION['land_kod'] = $rule['Country_code'];
            $_SESSION['r_o_m_id'] = $rule['R_o_m_id'];
            $_SESSION['user_id'] = $rule['User_id'];
            $_SESSION['rule_date'] = $rule['Rule_date'];
            $_SESSION['fr'] = $rule['Valid_from'];
            $_SESSION['ti'] = $rule['Valid_to'];
        }
    } catch (PDOException $e) {
        $errors[] = "Det gick inte att hämta regeln. " . $e->getMessage();
    }
}

function render_readonly_field($label, $value)
{
    ?>
    <label class="bibmet-field">
        <span class="bibmet-field__label"><?php echo bibmet_h($label); ?></span>
        <input class="bibmet-input" type="text" value="<?php echo bibmet_h($value); ?>" disabled>
    </label>
    <?php
}
?>

<!DOCTYPE html>
<html lang="sv">

<! Författare: Cecilia Wiklander>
<! Syfte: Adressrättnings-hantering>
<! Ändringar: >

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ta bort regel organisation</title>
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
                    <h1 class="bibmet-title">Ta bort regel organisation</h1>
                    <p class="bibmet-muted">Granska regeln och ange orsak innan borttagning.</p>
                </div>
                <div class="bibmet-action-group">
                    <a href="regel_organisation.php" class="bibmet-button bibmet-button--secondary">Till sökning</a>
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

        <?php if ($rule) : ?>
            <form action="ta_bort_regel_resultat_o.php" method="post" class="bibmet-panel">
                <input type="hidden" name="Regel_id" value="<?php echo bibmet_h($regel_id); ?>">
                <div class="bibmet-panel__header">
                    <h2 class="bibmet-panel__title">Bekräfta borttagning</h2>
                </div>

                <div class="bibmet-form-grid bibmet-form-grid--compact">
                    <label class="bibmet-field">
                        <span class="bibmet-field__label">Orsak</span>
                        <input class="bibmet-input" type="text" name="orsak" maxlength="100" required>
                    </label>
                    <?php render_readonly_field('Regel-id', $rule['R_o_m_id']); ?>
                    <?php render_readonly_field('Land', $rule['Find_country']); ?>
                    <?php render_readonly_field('Stad', $rule['Find_city']); ?>
                    <?php render_readonly_field('Organisation', $rule['Find_org']); ?>
                    <?php render_readonly_field('Delas i', $rule['Divide']); ?>
                    <?php render_readonly_field('Gäller från', $rule['Valid_from']); ?>
                    <?php render_readonly_field('Gäller till', $rule['Valid_to']); ?>
                </div>

                <div class="bibmet-form-grid bibmet-form-grid--compact">
                    <?php for ($i = 1; $i <= 3; $i++) : ?>
                        <section class="bibmet-panel bibmet-panel--subtle">
                            <h3 class="bibmet-panel__title">Organisation <?php echo bibmet_h($i); ?></h3>
                            <?php render_readonly_field('Organisationsnamn', $rule['Orgname_' . $i] ?? ''); ?>
                            <?php render_readonly_field('Land', $rule['Country_' . $i] ?? ''); ?>
                            <?php render_readonly_field('Stad', $rule['City_' . $i] ?? ''); ?>
                        </section>
                    <?php endfor; ?>
                </div>

                <div class="bibmet-form-actions">
                    <div class="bibmet-action-group">
                        <input type="submit" name="radera" value="Radera regel" class="bibmet-button bibmet-button--danger">
                        <a href="regel_organisation.php" class="bibmet-button bibmet-button--secondary">Avbryt</a>
                    </div>
                </div>
            </form>
        <?php endif; ?>
    </main>
</body>

</html>
