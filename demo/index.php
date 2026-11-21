<?php

$projectRoot = dirname(__DIR__);
$playgroundReady = false;
$resetError = false;

try {
    require $projectRoot . '/vendor/autoload.php';

    $dotenv = Dotenv\Dotenv::createImmutable($projectRoot);
    $dotenv->safeLoad();

    $database = require $projectRoot . '/config.php';
    $database = $database['database'];
    $connection = new PDO(
        $database['connection'] . ';dbname=' . $database['name'],
        $database['username'],
        $database['password'],
        $database['options']
    );

    require __DIR__ . '/includes/reset-demo.php';
    resetDemoData($connection);
    $playgroundReady = true;
} catch (Throwable $error) {
    error_log('[API playground] Initial reset failed: ' . $error->getMessage());
    http_response_code(503);
    $resetError = true;
}

$scriptPath = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/demo/index.php');
$applicationPath = dirname(dirname($scriptPath));
$applicationPath = $applicationPath === '/' || $applicationPath === '.'
    ? ''
    : rtrim($applicationPath, '/');
$apiBase = $applicationPath . '/api';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#101a2b">
    <title>REST API Playground</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <?php if ($playgroundReady): ?>
        <script defer src="assets/js/api.js"></script>
        <script defer src="assets/js/app.js"></script>
    <?php endif; ?>
</head>
<body data-api-base="<?= htmlspecialchars($apiBase, ENT_QUOTES, 'UTF-8') ?>">
    <header class="topbar">
        <a class="brand" href="./" aria-label="REST API Playground home">
            <span class="brand-mark">R</span>
            <span>REST<span class="brand-light">LAB</span></span>
        </a>
        <div class="topbar-meta">
            <span class="live-dot"></span>
            <span>Interactive API environment</span>
            <span class="version-tag">PHP · MySQL</span>
        </div>
    </header>

    <main class="page-shell">
        <section class="intro">
            <div class="intro-copy">
                <p class="eyebrow">DEVELOPER PLAYGROUND <span> / </span> REST API</p>
                <h1>Explore the API.<br><span>Make it yours.</span></h1>
                <p class="intro-text">Send real requests to a live PHP and MySQL API. Inspect courses, subjects, and the links between them — right here in your browser.</p>
            </div>
            <div class="intro-note">
                <span class="note-icon">↻</span>
                <span><strong>Clean slate on entry</strong><br>Sample data resets whenever this page loads.</span>
            </div>
        </section>

        <?php if ($resetError): ?>
            <section class="setup-error" role="alert">
                <span class="error-symbol">!</span>
                <div>
                    <h2>Playground unavailable</h2>
                    <p>The sample data could not be restored. Check the database configuration and prefixed schema, then reload this page.</p>
                </div>
            </section>
        <?php else: ?>
            <section class="playground" aria-label="API request playground">
                <aside class="sidebar">
                    <div class="sidebar-heading">API REFERENCE <span class="endpoint-count">08</span></div>
                    <p class="sidebar-caption">Choose a resource to explore.</p>
                    <button class="resource-nav is-active" type="button" data-resource-nav="courses">
                        <span class="resource-icon course-icon">▤</span>
                        <span><strong>Courses</strong><small>Collection &amp; records</small></span>
                        <span class="nav-chevron">›</span>
                    </button>
                    <button class="resource-nav" type="button" data-resource-nav="subjects">
                        <span class="resource-icon subject-icon">◈</span>
                        <span><strong>Subjects</strong><small>Collection &amp; records</small></span>
                        <span class="nav-chevron">›</span>
                    </button>

                    <div class="sidebar-divider"></div>
                    <p class="sidebar-label">RELATIONSHIPS</p>
                    <div class="relationship-note">
                        <span class="link-icon">↗</span>
                        <span><strong>Course ↔ Subject</strong><small>Manage linked subject IDs through course requests.</small></span>
                    </div>

                    <div class="sidebar-footer">
                        <span class="status-pip"></span>
                        <span>Same-origin API</span>
                        <span class="footer-lock">⌑</span>
                    </div>
                </aside>

                <div class="workspace">
                    <div class="workspace-heading">
                        <div>
                            <p class="eyebrow">REQUEST BUILDER</p>
                            <h2>Configure your request</h2>
                        </div>
                        <span class="request-count">2 <span>resources</span></span>
                    </div>

                    <div class="request-card panel-card">
                        <div class="card-topline"><span class="step-number">01</span><span>REQUEST</span><span class="topline-rule"></span><span class="http-badge">HTTP</span></div>
                        <div class="request-controls">
                            <label class="field-label" for="method-select">Method</label>
                            <label class="field-label" for="resource-select">Resource</label>
                            <select id="method-select" class="select method-select" aria-label="HTTP method">
                                <option>GET</option><option>POST</option><option>PUT</option><option>DELETE</option>
                            </select>
                            <select id="resource-select" class="select" aria-label="API resource">
                                <option value="courses">Courses</option><option value="subjects">Subjects</option>
                            </select>
                        </div>
                        <div class="route-preview">
                            <span class="route-label">REQUEST URL</span>
                            <code id="route-preview">/api/courses</code>
                            <span id="route-kind" class="route-kind">COLLECTION</span>
                        </div>
                        <p id="endpoint-description" class="endpoint-description"></p>

                        <div id="id-field" class="field-block is-hidden">
                            <label class="field-label" for="record-id">Record ID</label>
                            <input id="record-id" class="text-input" type="number" min="1" placeholder="e.g. 1" inputmode="numeric">
                            <p class="field-help">Used in the URL path for item requests.</p>
                        </div>

                        <div id="filters-field" class="field-block">
                            <div class="section-title-row"><label class="field-label">Query parameters</label><span class="optional-label">OPTIONAL</span></div>
                            <div class="filter-grid">
                                <label class="filter-input"><span>NAME CONTAINS</span><input id="filter-name" type="text" placeholder="e.g. First"></label>
                                <label class="filter-input"><span>SUBJECT IDS</span><input id="filter-subjects" type="text" placeholder="e.g. 1,2"></label>
                                <label class="filter-input"><span>MIN. PLACES</span><input id="filter-places" type="number" min="0" placeholder="e.g. 10"></label>
                            </div>
                            <p class="field-help">Course list filters match the API's <code>name</code>, <code>subjects</code> and <code>places</code> query parameters.</p>
                        </div>

                        <div id="body-field" class="field-block is-hidden">
                            <div class="section-title-row"><label class="field-label" for="request-body">Request body</label><button id="reset-example" class="text-button" type="button">Reset example</button></div>
                            <div class="editor-shell">
                                <div class="editor-gutter">1<br>2<br>3<br>4<br>5</div>
                                <textarea id="request-body" spellcheck="false" aria-label="JSON request body"></textarea>
                            </div>
                            <p class="field-help">Edit the JSON before sending. Course <code>subjects</code> is an array of subject IDs.</p>
                        </div>

                        <div class="request-actions">
                            <span class="request-hint"><span class="keyboard-icon">↵</span> Request is sent to this app's API</span>
                            <button id="send-request" class="send-button" type="button"><span class="send-label">Send request</span><span class="send-arrow">↗</span></button>
                        </div>
                    </div>

                    <section class="response-card panel-card" aria-live="polite">
                        <div class="response-heading">
                            <div class="card-topline"><span class="step-number">02</span><span>RESPONSE</span><span class="topline-rule"></span></div>
                            <span id="response-state" class="response-state"><span class="state-dot"></span> READY</span>
                        </div>
                        <div id="response-placeholder" class="response-placeholder">
                            <span class="placeholder-glyph">{ }</span>
                            <strong>Your response will appear here</strong>
                            <span>Configure a request above and send it to inspect the result.</span>
                        </div>
                        <div id="response-result" class="response-result is-hidden">
                            <div class="response-meta">
                                <div><span class="meta-label">STATUS</span><strong id="response-status">—</strong></div>
                                <div><span class="meta-label">METHOD</span><strong id="response-method">—</strong></div>
                                <div><span class="meta-label">TIME</span><strong id="response-time">—</strong></div>
                                <div class="meta-url-wrap"><span class="meta-label">REQUEST</span><code id="response-url">—</code></div>
                            </div>
                            <div class="response-tabs"><div class="tab-buttons"><button class="tab-active" type="button" data-tab="body">Body</button><button type="button" data-tab="headers">Headers <small id="header-count">0</small></button></div><span id="response-content-type" class="content-type">JSON</span></div>
                            <div id="response-headers" class="response-headers is-hidden"></div>
                            <pre id="response-body" class="response-body" tabindex="0"></pre>
                        </div>
                    </section>
                    <footer class="workspace-footer"><span>Built with PHP, MySQL &amp; vanilla JavaScript</span><span>Disposable demo data <i></i></span></footer>
                </div>
            </section>
        <?php endif; ?>

        <section class="footnote"><span>ℹ</span><p><strong>A note about demo data</strong> — The shared sample dataset resets every time this page is opened. Your changes are temporary and may be cleared when another visitor starts a new session.</p></section>
    </main>
    <div id="toast" class="toast" role="status" aria-live="polite"></div>
</body>
</html>
