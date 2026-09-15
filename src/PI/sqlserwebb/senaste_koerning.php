<?php
require_once __DIR__ . '/sqlsrv_connect.php';
require_once __DIR__ . '/bibmet_ui.php';

$dbh = bibmet_sqlsrv_connect_or_redirect('BIBMET');

$errors = [];
$runDates = [
    'organisation' => null,
    'centra' => null,
    'full_address' => null,
    'organisation_type' => null,
];

function latest_run_date(PDO $dbh, $tableName)
{
    $allowedTables = [
        'rule_org_rundate',
        'rule_center_rundate',
        'rule_full_address_rundate',
        'rule_org_type_rundate',
    ];

    if (!in_array($tableName, $allowedTables, true)) {
        throw new InvalidArgumentException('Invalid run date table.');
    }

    $stmt = $dbh->query("SELECT MAX(Run_date) AS Run_date FROM {$tableName}");
    return $stmt->fetchColumn();
}

try {
    $runDates['organisation'] = latest_run_date($dbh, 'rule_org_rundate');
    $runDates['centra'] = latest_run_date($dbh, 'rule_center_rundate');
    $runDates['full_address'] = latest_run_date($dbh, 'rule_full_address_rundate');
    $runDates['organisation_type'] = latest_run_date($dbh, 'rule_org_type_rundate');
} catch (PDOException $e) {
    $errors[] = 'Det gick inte att hämta senaste körningsdatum.';
}

$items = [
    [
        'label' => 'Organisation',
        'value' => $runDates['organisation'],
        'description' => 'Senaste körning av organisationsregler.',
    ],
    [
        'label' => 'Centra',
        'value' => $runDates['centra'],
        'description' => 'Senaste körning av centrumregler.',
    ],
    [
        'label' => 'Full adress',
        'value' => $runDates['full_address'],
        'description' => 'Senaste körning av fulladressregler.',
    ],
    [
        'label' => 'Organisationstyp',
        'value' => $runDates['organisation_type'],
        'description' => 'Senaste körning av regler för organisationstyp.',
    ],
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
    <title>Senaste adressrättning</title>
    <link href="Site.css" rel="stylesheet">
    <?php include("include_bibmet_kth.html"); ?>
</head>

<body class="bibmet-body">
    <?php include("include_head_new.html"); ?>

    <main class="bibmet-main bibmet-main--form">
        <section class="bibmet-hero">
            <div class="bibmet-hero__row">
                <div>
                    <p class="bibmet-eyebrow">Kontroller</p>
                    <h1 class="bibmet-title">Senaste adressrättning</h1>
                    <p class="bibmet-muted">
                        Här visas när adressreglerna senast kördes för respektive regeltyp.
                    </p>
                </div>
                <a href="adressmeny.php" class="bibmet-button bibmet-button--primary">Till menyn</a>
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
            <div class="bibmet-panel__header">
                <h2 class="bibmet-panel__title">Senaste körningar</h2>
            </div>
            <div class="bibmet-panel__body bibmet-menu-list">
                <?php foreach ($items as $item) : ?>
                    <div class="bibmet-menu-card">
                        <span class="bibmet-menu-card__title"><?php echo bibmet_h($item['label']); ?></span>
                        <span class="bibmet-menu-card__text"><?php echo bibmet_h($item['description']); ?></span>
                        <p class="bibmet-page-pill" style="margin: 12px 0 0;">
                            <?php echo $item['value'] ? bibmet_h($item['value']) : 'Inget datum registrerat'; ?>
                        </p>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="bibmet-panel" style="margin-top: 24px;">
            <div class="bibmet-panel__header">
                <h2 class="bibmet-panel__title">Körschema</h2>
            </div>
            <div class="bibmet-panel__body">
                <p class="bibmet-muted" style="margin-top: 0;">
                    Körningar av adressregler görs tre gånger i veckan: måndag, onsdag och fredag.
                    Körningarna börjar klockan 18.
                </p>
                <ul>
                    <li>Måndag och onsdag: alla regler utom fulladressregler körs.</li>
                    <li>Fredag: alla regler körs.</li>
                    <li>Om flera regler får träff på en forskaradress prioriteras hög splittringsfaktor, senaste regeldatum och högsta regel-id.</li>
                </ul>
            </div>
        </section>
    </main>
</body>

</html>
