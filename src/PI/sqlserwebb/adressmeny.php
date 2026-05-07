<?php session_start(); ?>

<!DOCTYPE html>
<html lang="sv">

<! Författare: Cecilia Wiklander>
<! Syfte: Adressrättnings-hantering>
<! Ändringar: >
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Meny</title>
    <link href="Site.css" rel="stylesheet">
    <?php include("include_bibmet_kth.html"); ?>
</head>

<body class="bibmet-body">
    <?php include("include_head_new.html"); ?>

    <main class="bibmet-main">
        <section class="bibmet-hero">
            <div>
                <p class="bibmet-eyebrow">Bibmet</p>
                <h1 class="bibmet-title">Adressrättning</h1>
                <p class="bibmet-muted">
                    Välj arbetsflöde för regler, organisationsnamn, inläsning eller kontroller.
                </p>
            </div>
        </section>

        <section class="bibmet-menu-grid">
            <section class="bibmet-panel">
                <div class="bibmet-panel__header">
                    <h2 class="bibmet-panel__title">Adressregler</h2>
                </div>

                <div class="bibmet-panel__body bibmet-menu-list">
                    <a
                        href="regel_organisation.php"
                        class="bibmet-menu-card">
                        <span class="bibmet-menu-card__title">Organisation</span>
                        <span class="bibmet-menu-card__text">Sök och hantera organisationsregler.</span>
                    </a>

                    <a
                        href="regel_full_adress.php"
                        class="bibmet-menu-card">
                        <span class="bibmet-menu-card__title">Full adress</span>
                        <span class="bibmet-menu-card__text">Regler för fullständiga adressmatchningar.</span>
                    </a>

                    <a
                        href="regel_centra.php"
                        class="bibmet-menu-card">
                        <span class="bibmet-menu-card__title">Centra</span>
                        <span class="bibmet-menu-card__text">Sök och hantera centrumregler.</span>
                    </a>

                    <a
                        href="regel_organisation_typ.php"
                        class="bibmet-menu-card">
                        <span class="bibmet-menu-card__title">Organisationstyp</span>
                        <span class="bibmet-menu-card__text">Regler som matchar organisationstyper.</span>
                    </a>
                </div>
            </section>

            <section class="bibmet-panel">
                <div class="bibmet-panel__header">
                    <h2 class="bibmet-panel__title">Data</h2>
                </div>

                <div class="bibmet-panel__body bibmet-menu-list">
                    <a
                        href="organisationsnamn.php"
                        class="bibmet-menu-card">
                        <span class="bibmet-menu-card__title">Organisationsnamn</span>
                        <span class="bibmet-menu-card__text">Underhåll och kontrollera organisationsnamn.</span>
                    </a>

                    <a
                        href="laddaorgregler.php"
                        class="bibmet-menu-card">
                        <span class="bibmet-menu-card__title">Ladda filer med regler</span>
                        <span class="bibmet-menu-card__text">Importera filer som innehåller adressregler.</span>
                    </a>
                </div>
            </section>

            <section class="bibmet-panel">
                <div class="bibmet-panel__header">
                    <h2 class="bibmet-panel__title">Kontroller</h2>
                </div>

                <div class="bibmet-panel__body bibmet-menu-list">
                    <a
                        href="senaste_koerning.php"
                        class="bibmet-menu-card">
                        <span class="bibmet-menu-card__title">Senaste adressrättning</span>
                        <span class="bibmet-menu-card__text">Visa information från den senaste körningen.</span>
                    </a>

                    <a
                        href="https://bibliometrics.lib.kth.se/checks-bibliometrics.html"
                        class="bibmet-menu-card">
                        <span class="bibmet-menu-card__title">Kontroller för bibliometridata</span>
                        <span class="bibmet-menu-card__text">Öppna externa kontroller för bibliometridata.</span>
                    </a>
                </div>
            </section>
        </section>
    </main>
</body>

</html>
