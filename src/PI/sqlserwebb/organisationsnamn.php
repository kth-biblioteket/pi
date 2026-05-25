<?php
require_once __DIR__ . '/sqlsrv_connect.php';

$dbh = bibmet_sqlsrv_connect_or_redirect();

function h($value)
{
    if ($value instanceof DateTimeInterface) {
        $value = $value->format("Y-m-d");
    }

    return htmlspecialchars((string) $value, ENT_QUOTES, "UTF-8");
}

$errors = [];
$countries = [];
$organizationTypes = [];
$org_typ_eng = "";

try {
    $stmt = $dbh->query("SELECT Display_name FROM Country ORDER BY Display_name");
    $countries = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $stmt = $dbh->query("SELECT Org_type_eng FROM Organization_type ORDER BY Org_type_eng");
    $organizationTypes = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $errors[] = "Det gick inte att hämta söklistorna. " . $e->getMessage();
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
    <title>Organisationsnamn</title>
    <link href="Site_utan_storlek.css" rel="stylesheet">
    <?php include("include_bibmet_kth.html"); ?>

    <script>
        function f_populera_Land() {
            f_populera_Orgtyp();
            var e = document.getElementById("id_country");
            landlista = [];
            land_test = "";
            var x_antal = e.length;

            for (i = 0; i < x_antal; i++) {
                land_test = e.options[i].text;
                landlista.push(land_test);
            }

            document.getElementById("id_soek_land_s").value = "*";
            var soeklista = document.getElementById("id_s_land");
            var laengd = soeklista.length;

            for (i = 1; i < laengd; i++) {
                soeklista.remove(1);
            }

            for (var i = 0; i < landlista.length; i++) {
                var opt = landlista[i];
                var el = document.createElement("option");
                el.textContent = opt;
                el.value = opt;
                soeklista.appendChild(el);
            }
        }

        function f_populera_soek_Land_S() {
            var v_text = document.getElementById("id_soek_land_s").value;
            if (v_text > "") {
                var soeklista = document.getElementById("id_s_land");
                var laengd = soeklista.length;

                for (i = 1; i < laengd; i++) {
                    soeklista.remove(1);
                }

                if (v_text == "*") {
                    for (var i = 0; i < landlista.length; i++) {
                        var opt = landlista[i];
                        var el = document.createElement("option");
                        el.textContent = opt;
                        el.value = opt;
                        soeklista.appendChild(el);
                    }
                } else {
                    for (var i = 0; i < landlista.length; i++) {
                        var opt = landlista[i];
                        if (opt.toUpperCase().indexOf(v_text.toUpperCase()) > -1) {
                            var el = document.createElement("option");
                            el.textContent = opt;
                            el.value = opt;
                            soeklista.appendChild(el);
                        }
                    }
                }
            }
            return true;
        }

        function f_populera_Orgtyp() {
            orgtyplista = [];
            orgtyppost = "";
            var e = document.getElementById("id_orgtyp_dold");
            var x_antal = e.length;

            for (i = 0; i < x_antal; i++) {
                orgtyppost = e.options[i].text;
                orgtyplista.push(orgtyppost);
            }

            var soeklista = document.getElementById("id_orgtyp");
            for (var i = 0; i < orgtyplista.length; i++) {
                var opt = orgtyplista[i];
                var el = document.createElement("option");
                el.textContent = opt;
                el.value = opt;
                soeklista.appendChild(el);
            }
        }

        function f_Ladda_sida() {
            f_populera_Land();
            document.getElementById("id_soek_land_s").value = "*";
        }
    </script>
</head>

<body class="bibmet-body" onload="f_Ladda_sida()">
    <?php include("include_head_new.html"); ?>

    <select name="country" hidden id="id_country">
        <?php foreach ($countries as $country) : ?>
            <option value="<?php echo h($country); ?>"><?php echo h($country); ?></option>
        <?php endforeach; ?>
    </select>

    <select name="organization_type" hidden id="id_orgtyp_dold">
        <?php foreach ($organizationTypes as $organizationType) : ?>
            <option value="<?php echo h($organizationType); ?>"><?php echo h($organizationType); ?></option>
        <?php endforeach; ?>
    </select>

    <main class="bibmet-main bibmet-main--form">
        <section class="bibmet-hero">
            <div class="bibmet-hero__row">
                <div>
                    <p class="bibmet-eyebrow">Bibmet</p>
                    <h1 class="bibmet-title">Organisationsnamn</h1>
                    <p class="bibmet-muted">Sök, skapa och hantera enhetliga organisationsnamn.</p>
                </div>
                <a href="adressmeny.php" class="bibmet-button bibmet-button--secondary">Till menyn</a>
            </div>
        </section>

        <?php if ($errors) : ?>
            <div class="bibmet-alert" role="alert">
                <?php foreach ($errors as $error) : ?>
                    <p><?php echo h($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form action="visa_organisation.php" method="post">
            <section class="bibmet-panel">
                <div class="bibmet-panel__header">
                    <h2 class="bibmet-panel__title">Sökurval</h2>
                </div>

                <div class="bibmet-form-grid">
                    <label class="bibmet-field">
                        <span class="bibmet-field__label">Orgid</span>
                        <input class="bibmet-input bibmet-input--short" type="text" name="Orgid">
                    </label>

                    <label class="bibmet-field">
                        <span class="bibmet-field__label">Land</span>
                        <span class="bibmet-field-row">
                            <select class="bibmet-select" id="id_s_land" name="Land">
                                <option>Ange land</option>
                            </select>
                            <input
                                class="bibmet-input bibmet-input--short"
                                type="text"
                                name="Soek_land_s"
                                id="id_soek_land_s"
                                onchange="f_populera_soek_Land_S()"
                                aria-label="Filtrera land">
                        </span>
                    </label>

                    <label class="bibmet-field">
                        <span class="bibmet-field__label">Lokalt namn</span>
                        <input class="bibmet-input" type="text" name="Lokaltnamn">
                    </label>

                    <label class="bibmet-field">
                        <span class="bibmet-field__label">Engelskt namn</span>
                        <input class="bibmet-input" type="text" name="Engelsktnamn">
                    </label>

                    <label class="bibmet-field">
                        <span class="bibmet-field__label">ROR-id</span>
                        <input class="bibmet-input" type="text" name="RORid">
                    </label>

                    <label class="bibmet-field">
                        <span class="bibmet-field__label">Organisationstyp</span>
                        <input type="text" name="Orgtyp_nu" value="<?php echo h($org_typ_eng); ?>" hidden>
                        <select class="bibmet-select" id="id_orgtyp" name="Orgtyp_till">
                            <option>Ange organisationstyp</option>
                        </select>
                    </label>

                    <label class="bibmet-field">
                        <span class="bibmet-field__label">Kommentar</span>
                        <input class="bibmet-input" type="text" name="Kommentar">
                    </label>

                    <label class="bibmet-field">
                        <span class="bibmet-field__label">Exakt namnsökning</span>
                        <input type="checkbox" name="exaktkoll" value="checkbox_value" checked>
                    </label>
                </div>

                <div class="bibmet-form-actions">
                    <div class="bibmet-action-group">
                        <input class="bibmet-button bibmet-button--primary" type="submit" name="soek" value="Sök organisation">
                        <input class="bibmet-button bibmet-button--secondary" type="submit" formaction="ny_organisation.php" name="test" value="Ny organisation">
                    </div>
                    <div class="bibmet-action-group">
                        <a href="ny_regel_o.php" class="bibmet-button bibmet-button--secondary">Ny regel organisation</a>
                        <a href="ny_regel_f_a.php" class="bibmet-button bibmet-button--secondary">Ny regel full adress</a>
                        <a href="ny_regel_c.php" class="bibmet-button bibmet-button--secondary">Ny regel centra</a>
                    </div>
                </div>
            </section>
        </form>
    </main>
</body>

</html>
