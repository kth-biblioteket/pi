<?php

// Read a request value from GET/POST, trim it, and fall back to a default.
function bibmet_request_value($key, $default = "")
{
    return isset($_REQUEST[$key]) ? trim((string) $_REQUEST[$key]) : $default;
}

// Escape a value for safe HTML output; DateTime values are shown as YYYY-MM-DD.
function bibmet_h($value)
{
    if ($value instanceof DateTimeInterface) {
        $value = $value->format("Y-m-d");
    }

    return htmlspecialchars((string) $value, ENT_QUOTES, "UTF-8");
}

// Return the HTML selected attribute when a select option matches the current value.
function bibmet_selected_attr($optionValue, $currentValue)
{
    return (string) $optionValue === (string) $currentValue ? ' selected' : '';
}

// Build a pagination URL, preserving current filters while dropping reset/session params.
function bibmet_page_url($page, array $extraParams = [])
{
    $params = $_REQUEST;
    unset($params["clear"], $params["PHPSESSID"]);
    if (function_exists('session_name')) {
        unset($params[session_name()]);
    }
    $params = array_merge($params, $extraParams);
    $params["page"] = $page;

    return $_SERVER["PHP_SELF"] . "?" . http_build_query($params);
}

// Bind an array of named SQL parameters to a PDO statement with basic int/string typing.
function bibmet_bind_all(PDOStatement $stmt, array $params)
{
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
    }
}

// Show an empty value consistently in read-only summaries.
function bibmet_display_value($value)
{
    return $value === null || $value === "" ? "—" : (string) $value;
}

// Set a statement timeout when the active PDO driver supports it.
function bibmet_set_query_timeout(PDOStatement $stmt, $seconds)
{
    if (!defined('PDO::SQLSRV_ATTR_QUERY_TIMEOUT')) {
        return;
    }

    try {
        $stmt->setAttribute(constant('PDO::SQLSRV_ATTR_QUERY_TIMEOUT'), (int) $seconds);
    } catch (Throwable $e) {
        // Timeout attributes are driver-specific; ignore unsupported drivers.
    }
}

// Render a reusable read-only summary panel using the standard Bibmet field/grid style.
function bibmet_render_summary_panel($title, array $fields)
{
    ?>
    <section class="bibmet-panel">
        <div class="bibmet-panel__header">
            <h2 class="bibmet-panel__title"><?php echo bibmet_h($title); ?></h2>
        </div>
        <div class="bibmet-panel__body">
            <div class="bibmet-form-grid bibmet-form-grid--compact">
                <?php foreach ($fields as $label => $value) : ?>
                    <div class="bibmet-field">
                        <span class="bibmet-field__label"><?php echo bibmet_h($label); ?></span>
                        <p class="bibmet-summary-value"><?php echo bibmet_h(bibmet_display_value($value)); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php
}

// Render standard result/status messages for action pages.
function bibmet_render_messages_panel($title, array $messages, $variant = "")
{
    if (!$messages) {
        return;
    }

    if ($variant === "success") {
        ?>
        <div class="bibmet-alert bibmet-alert--success" role="status" aria-label="<?php echo bibmet_h($title); ?>">
            <?php foreach ($messages as $message) : ?>
                <p><?php echo bibmet_h($message); ?></p>
            <?php endforeach; ?>
        </div>
        <?php
        return;
    }

    ?>
    <section class="bibmet-panel">
        <div class="bibmet-panel__header">
            <h2 class="bibmet-panel__title"><?php echo bibmet_h($title); ?></h2>
        </div>
        <div class="bibmet-panel__body">
            <?php foreach ($messages as $message) : ?>
                <p class="bibmet-muted"><?php echo bibmet_h($message); ?></p>
            <?php endforeach; ?>
        </div>
    </section>
    <?php
}

// Render the standard Bibmet pagination controls; extra params preserve required page context.
function bibmet_render_pagination($page, $totalPages, array $extraParams = [])
{
    if ($totalPages <= 1) {
        return;
    }

    ?>
    <nav class="bibmet-pagination" aria-label="Sidnavigering">
        <a
            href="<?php echo bibmet_h(bibmet_page_url(max(1, $page - 1), $extraParams)); ?>"
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
                    href="<?php echo bibmet_h(bibmet_page_url($i, $extraParams)); ?>"
                    class="bibmet-page-link<?php echo $isCurrent ? " bibmet-page-link--current" : ""; ?>"
                    aria-current="<?php echo $isCurrent ? "page" : "false"; ?>">
                    <?php echo bibmet_h($i); ?>
                </a>
            <?php endfor; ?>
        </div>

        <a
            href="<?php echo bibmet_h(bibmet_page_url(min($totalPages, $page + 1), $extraParams)); ?>"
            class="<?php echo $page >= $totalPages ? "bibmet-disabled " : ""; ?>bibmet-button bibmet-button--primary"
            aria-disabled="<?php echo $page >= $totalPages ? "true" : "false"; ?>">
            Nästa
        </a>
    </nav>
    <?php
}
