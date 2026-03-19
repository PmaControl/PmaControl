<?php

use \Glial\Synapse\FactoryController;
use \App\Library\Graphviz;

$idMysqlServer = (int) ($data['id_mysql_server'] ?? 0);
$dot = (string) ($data['dot'] ?? '');
$svg = (string) ($data['svg'] ?? '');
$renderError = (string) ($data['render_error'] ?? '');
$dateInserted = (string) ($data['date_inserted'] ?? '');
$md5 = (string) ($data['md5'] ?? '');
$filename = (string) ($data['filename'] ?? '');
$dotLength = (int) ($data['dot_length'] ?? strlen($dot));
$downloadSvgName = 'cluster-' . $idMysqlServer . '.svg';
if ($md5 !== '') {
    $downloadSvgName = 'cluster-' . $idMysqlServer . '-' . $md5 . '.svg';
}
$downloadSvgHref = '';
if ($svg !== '') {
    $downloadSvgHref = Graphviz::buildSvgDownloadDataUri($svg);
}
$previewKey = (string) ($data['preview_key'] ?? '');
$importBundle = $data['import_bundle'] ?? [];
$selectedImportGraph = $data['import_selected_graph'] ?? [];
$selectedImportGraphIndex = $data['import_selected_graph_index'] ?? null;
$importPayload = (string) ($data['import_payload'] ?? '');
$isImported = !empty($importBundle['graphs']);
?>

<style>
.dot-online-shell {
    padding: 12px 10px 20px 0;
}

.dot-online-toolbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    margin-bottom: 12px;
}

.dot-online-title {
    margin: 0;
    font-size: 22px;
}

.dot-online-subtitle {
    color: #6b7280;
    margin-top: 4px;
}

.dot-online-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 12px;
    align-items: center;
}

.dot-online-render-error {
    display: none;
    white-space: pre-wrap;
}

.dot-online-error-stack {
    display: flex;
    flex-direction: column;
    gap: 10px;
    margin: 0 0 12px 0;
    max-width: 100%;
}

.dot-online-error-card {
    position: relative;
    padding: 12px 16px;
    border: 1px solid #b91c1c;
    background: #fee2e2;
    color: #7f1d1d;
    border-radius: 6px;
    box-shadow: 0 6px 18px rgba(15, 23, 42, 0.12);
}

.dot-online-error-card-title {
    font-weight: 700;
    margin: 0 28px 6px 0;
}

.dot-online-error-card-body {
    white-space: pre-wrap;
    word-break: break-word;
    font-family: monospace;
    font-size: 12px;
    line-height: 1.45;
}

.dot-online-error-card-close {
    position: absolute;
    top: 8px;
    right: 10px;
    border: 0;
    background: transparent;
    color: #7f1d1d;
    font-size: 18px;
    line-height: 1;
    cursor: pointer;
}

.dot-online-chip {
    display: inline-block;
    padding: 5px 10px;
    border-radius: 999px;
    background: #f8fafc;
    border: 1px solid #dbe5ef;
    font-size: 12px;
}

.dot-online-meta-actions {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    margin-left: auto;
}

.dot-online-import-details {
    margin-bottom: 14px;
}

.dot-online-import-details summary {
    display: none;
}

.dot-online-import-details summary::-webkit-details-marker {
    display: none;
}

.dot-online-import-panel {
    margin-top: 10px;
    padding: 12px;
    border: 1px solid #dbe5ef;
    border-radius: 8px;
    background: #f8fafc;
}

.dot-online-dropzone {
    position: relative;
    padding: 18px;
    border: 2px dashed #cbd5e1;
    border-radius: 8px;
    background: #ffffff;
    transition: border-color .15s ease, background .15s ease, box-shadow .15s ease;
}

.dot-online-dropzone.is-dragover {
    border-color: #f59e0b;
    background: #fffbeb;
    box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.12);
}

.dot-online-dropzone-hint {
    margin: 0 0 12px 0;
    color: #64748b;
    font-size: 13px;
}

.dot-online-import-grid {
    display: grid;
    gap: 12px;
    grid-template-columns: minmax(220px, 320px) minmax(280px, 1fr);
    align-items: start;
}

.dot-online-import-panel textarea {
    width: 100%;
    min-height: 180px;
    resize: vertical;
    font-family: monospace;
    font-size: 12px;
}

.dot-online-import-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-top: 10px;
    align-items: center;
}

.dot-online-layout {
    display: flex;
    align-items: stretch;
    gap: 14px;
}

.dot-online-panel {
    background: #fff;
    border: 1px solid #cfd8e3;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
    display: flex;
    flex-direction: column;
}

.dot-online-editor-panel {
    flex: 1.05 1 0;
}

.dot-online-render-panel {
    flex: 1 1 0;
}

.dot-online-panel-head {
    padding: 10px 12px;
    background: linear-gradient(135deg,#0f172a,#1e3a8a);
    color: #fff;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
}

.dot-online-panel-body {
    padding: 12px;
    min-height: 0;
}

.dot-online-editor-panel .dot-online-panel-body {
    display: flex;
    flex-direction: column;
    flex: 1;
    padding: 0 !important;
    min-height: 0;
}

.dot-online-top-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.dot-online-fallback-actions {
    display: flex;
    justify-content: flex-end;
    padding: 10px 12px 12px;
    border-top: 1px solid #dbe5ef;
    background: #f8fafc;
}

.dot-online-code-shell {
    border: 1px solid #dbe5ef;
    border-radius: 6px;
    overflow: hidden;
    background: #0f172a;
    display: flex;
    flex: 1 1 auto;
    width: 100%;
    min-height: 0;
    height: 100%;
    max-height: 72vh;
}

.dot-online-editor-panel form {
    display: flex;
    flex: 1 1 auto;
    flex-direction: column;
    width: 100%;
    min-height: 0;
}

.dot-online-editor,
.dot-online-editor-panel .CodeMirror {
    width: 100%;
    height: 100%;
    box-sizing: border-box;
    font-family: monospace;
    font-size: 12px;
    line-height: 1.45;
}

.dot-online-editor {
    display: none;
}

.dot-online-preview {
    min-height: 72vh;
    max-height: 72vh;
    overflow: auto;
    border: 1px solid #dbe5ef;
    border-radius: 6px;
    padding: 12px;
    background: #fcfdff;
}

.dot-online-preview svg {
    max-width: 100%;
    height: auto;
}

.dot-online-preview .graphviz {
    min-width: max-content;
}

.dot-online-editor-panel .dot-online-editor {
    background: #0f172a;
    color: #e5e7eb;
    border: 1px solid #1f2937;
}

.dot-online-editor-panel .dot-online-code-shell {
    border: 0;
    border-radius: 0;
}

.dot-online-editor-panel .dot-online-editor,
.dot-online-editor-panel .CodeMirror {
    border: 0 !important;
}

.dot-online-editor-panel .CodeMirror {
    background: #0f172a !important;
    color: #e5e7eb !important;
    flex: 1 1 auto;
    display: flex;
    flex-direction: column;
    min-height: 0;
}

.dot-online-editor-panel .CodeMirror-sizer,
.dot-online-editor-panel .CodeMirror-scroll,
.dot-online-editor-panel .CodeMirror-code {
    min-height: 100%;
}

.dot-online-editor-panel .CodeMirror-gutters {
    background: #111827;
    border-right: 1px solid rgba(255,255,255,.08);
}

.dot-online-editor-panel .CodeMirror-lines {
    background: #0f172a;
}

.dot-online-editor-panel .CodeMirror-activeline-background {
    background: rgba(255, 255, 255, 0.18);
}

.dot-online-editor-panel .CodeMirror-linenumber {
    color: #64748b;
}

.dot-online-editor-panel .CodeMirror-cursor {
    border-left: 1px solid #d1d5db !important;
}

.dot-online-editor-panel .CodeMirror-selected {
    background: #17337f !important;
}

.dot-online-editor-panel .CodeMirror-focused .CodeMirror-selected {
    background: #17337f !important;
}

.dot-online-editor-panel .CodeMirror-line::selection,
.dot-online-editor-panel .CodeMirror-line > span::selection,
.dot-online-editor-panel .CodeMirror-line > span > span::selection {
    background: #17337f;
}

.dot-online-editor-panel .CodeMirror-line::-moz-selection,
.dot-online-editor-panel .CodeMirror-line > span::-moz-selection,
.dot-online-editor-panel .CodeMirror-line > span > span::-moz-selection {
    background: #17337f;
}

.dot-online-editor-panel .CodeMirror,
.dot-online-editor-panel .CodeMirror-scroll {
    scrollbar-color: #17337f #111827;
}

.dot-online-editor-panel .CodeMirror-scroll::-webkit-scrollbar {
    width: 12px;
    height: 12px;
}

.dot-online-editor-panel .CodeMirror-scroll::-webkit-scrollbar-track {
    background: #111827;
}

.dot-online-editor-panel .CodeMirror-scroll::-webkit-scrollbar-thumb {
    background: #17337f;
    border-radius: 999px;
    border: 2px solid #111827;
}

.dot-online-editor-panel .CodeMirror-scroll::-webkit-scrollbar-corner {
    background: #111827;
}

.dot-online-editor-panel .CodeMirror::-webkit-scrollbar-corner {
    background: #111827;
}

.dot-online-editor-panel .CodeMirror-scrollbar-filler,
.dot-online-editor-panel .CodeMirror-gutter-filler {
    background: #111827 !important;
}

.cm-dot-keyword {
    color: #93c5fd;
    font-weight: 700;
}

.cm-dot-attr {
    color: #f9a8d4;
}

.cm-dot-string {
    color: #86efac;
}

.cm-dot-number {
    color: #fcd34d;
}

.cm-dot-comment {
    color: #64748b;
}

.cm-dot-operator {
    color: #fca5a5;
}

.cm-dot-brace {
    color: #c4b5fd;
}

.cm-dot-id {
    color: #e2e8f0;
}

@media (max-width: 1200px) {
    .dot-online-layout {
        flex-direction: column;
    }

    .dot-online-import-grid {
        grid-template-columns: 1fr;
    }

    .dot-online-code-shell,
    .dot-online-preview {
        max-height: 48vh;
    }

    .dot-online-preview svg {
        height: auto;
    }
}
</style>

<link rel="stylesheet" href="<?php echo WWW_ROOT; ?>css/vendor/codemirror-5.65.16.min.css" />
<script src="<?php echo WWW_ROOT; ?>js/vendor/codemirror-5.65.16.min.js"></script>
<script src="<?php echo WWW_ROOT; ?>js/vendor/codemirror-simple-5.65.16.min.js"></script>

<div class="dot-online-shell">
    <div style="margin-bottom:10px;">
        <?= FactoryController::addNode("MysqlServer", "menu", [$idMysqlServer]); ?>
    </div>

    <details class="dot-online-import-details"<?= $isImported ? ' open' : '' ?> id="dot-online-import-details">
        <summary class="btn btn-warning" id="dot-online-import-summary">Import JSON</summary>
        <div class="dot-online-import-panel">
            <form method="post" enctype="multipart/form-data" id="dot-import-form">
                <input type="hidden" name="dot_import[preview_key]" value="<?= htmlspecialchars($previewKey, ENT_QUOTES, 'UTF-8') ?>" />
                <div class="dot-online-dropzone" id="dot-online-dropzone">
                    <p class="dot-online-dropzone-hint"><strong>Drop a JSON file here</strong> or use the file picker / paste the payload below.</p>
                    <div class="dot-online-import-grid">
                        <div>
                            <label for="dot-import-file"><strong>JSON file</strong></label>
                            <input type="file" id="dot-import-file" name="dot_import[file]" class="form-control" accept=".json,application/json" />
                            <p style="margin:10px 0 0; color:#64748b; font-size:12px;">Upload a `dot3/download` export or paste the JSON snapshot.</p>
                        </div>
                        <div>
                            <label for="dot-import-payload"><strong>JSON payload</strong></label>
                            <textarea id="dot-import-payload" name="dot_import[payload]" class="form-control" spellcheck="false"><?= htmlspecialchars($importPayload, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></textarea>
                        </div>
                    </div>
                </div>
                <div class="dot-online-import-actions">
                    <button type="submit" class="btn btn-warning">Load JSON</button>
                    <?php if ($isImported): ?>
                        <span class="dot-online-chip">Imported graphs: <?= count($importBundle['graphs']) ?></span>
                        <?php if (!empty($importBundle['source_label'])): ?>
                            <span class="dot-online-chip">Source: <?= htmlspecialchars((string) $importBundle['source_label'], ENT_QUOTES, 'UTF-8') ?></span>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </form>

            <?php if ($isImported): ?>
                <form method="get" action="" class="dot-online-import-actions" style="margin-top:12px;">
                    <input type="hidden" name="preview_key" value="<?= htmlspecialchars($previewKey, ENT_QUOTES, 'UTF-8') ?>" />
                    <label for="dot-import-selected-graph" style="margin:0;"><strong>Imported graph</strong></label>
                    <select
                        id="dot-import-selected-graph"
                        name="selected_graph"
                        class="selectpicker form-control"
                        data-live-search="true"
                        data-size="10"
                        data-width="420px"
                        title="Choose an imported graph"
                    >
                        <option value=""<?= $selectedImportGraphIndex === null ? ' selected' : '' ?>>Choose an imported graph</option>
                        <?php foreach ($importBundle['graphs'] as $index => $graph): ?>
                            <option value="<?= (int) $index ?>"<?= !empty($selectedImportGraph) && ($graph['md5'] ?? '') === ($selectedImportGraph['md5'] ?? '') ? ' selected' : '' ?>>
                                <?= htmlspecialchars((string) ($graph['label'] ?? ('Graph ' . ((int) $index + 1))), ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn btn-default">Load graph</button>
                </form>
            <?php endif; ?>
        </div>
    </details>

    <div class="dot-online-meta">
        <span class="dot-online-chip">Server ID: <?= $idMysqlServer ?></span>
        <?php if ($isImported): ?>
            <span class="dot-online-chip">Mode: Imported JSON</span>
        <?php endif; ?>
        <?php if ($dateInserted !== ''): ?>
            <span class="dot-online-chip">Refresh: <?= htmlspecialchars($dateInserted, ENT_QUOTES, 'UTF-8') ?></span>
        <?php endif; ?>
        <?php if ($md5 !== ''): ?>
            <span class="dot-online-chip">MD5: <?= htmlspecialchars($md5, ENT_QUOTES, 'UTF-8') ?></span>
        <?php endif; ?>
        <?php if ($isImported && !empty($importBundle['snapshot_id'])): ?>
            <span class="dot-online-chip">Snapshot ID: <?= htmlspecialchars((string) $importBundle['snapshot_id'], ENT_QUOTES, 'UTF-8') ?></span>
        <?php endif; ?>
        <?php if ($filename !== ''): ?>
            <span class="dot-online-chip">File: <?= htmlspecialchars(basename($filename), ENT_QUOTES, 'UTF-8') ?></span>
        <?php endif; ?>
        <span class="dot-online-meta-actions" id="dot-online-meta-actions">
            <a href="<?= LINK ?>Cluster/viewDot/<?= $idMysqlServer ?>/" class="btn btn-default">Reset</a>
            <?php if ($downloadSvgHref !== ''): ?>
                <a href="<?= $downloadSvgHref ?>" download="<?= htmlspecialchars($downloadSvgName, ENT_QUOTES, 'UTF-8') ?>" class="btn btn-primary" id="dot-online-download-svg">Download SVG</a>
            <?php endif; ?>
            <a href="<?= LINK ?>dot3/download/" class="btn btn-success">Download JSON</a>
            <button type="button" class="btn btn-warning" id="dot-online-import-trigger">Import JSON</button>
            <a href="<?= LINK ?>Cluster/svg/<?= $idMysqlServer ?>/" class="btn btn-default">Back to Cluster</a>
        </span>
    </div>

    <div class="dot-online-error-stack" id="dot-online-error-stack"></div>

    <div class="dot-online-layout">
        <div class="dot-online-panel dot-online-editor-panel" id="dot-online-editor-panel">
            <div class="dot-online-panel-head">
                <span>DOT Source</span>
                <span style="font-size:12px; opacity:.85;">Graphviz DOT</span>
            </div>
            <div class="dot-online-panel-body">
                <form method="post" action="" id="dot-online-form">
                    <input type="hidden" name="dot_preview[preview_key]" value="<?= htmlspecialchars($previewKey, ENT_QUOTES, 'UTF-8') ?>" id="dot-online-preview-key" />
                    <?php if ($selectedImportGraphIndex !== null): ?>
                        <input type="hidden" name="dot_preview[selected_graph]" value="<?= (int) $selectedImportGraphIndex ?>" id="dot-online-selected-import-graph" />
                    <?php endif; ?>
                    <div class="dot-online-code-shell" id="dot-online-code-shell">
                        <textarea id="dot-online-editor" class="dot-online-editor" name="dot_preview[dot]" spellcheck="false"><?= htmlspecialchars($dot, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></textarea>
                    </div>
                </form>
                <div class="dot-online-fallback-actions" id="dot-online-fallback-actions">
                    <button type="submit" form="dot-online-form" class="btn btn-primary" id="dot-online-render-fallback">Render</button>
                </div>
            </div>
        </div>

        <div class="dot-online-panel dot-online-render-panel">
            <div class="dot-online-panel-head">
                <span>Rendered Graph</span>
                <span style="font-size:12px; opacity:.85;">preview</span>
            </div>
            <div class="dot-online-panel-body">
                <div class="dot-online-preview" id="dot-online-preview">
                    <?= $svg ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
if (window.CodeMirror && window.CodeMirror.defineSimpleMode) {
    CodeMirror.defineSimpleMode("dot-mode", {
        start: [
            {regex: /\/\/.*/, token: "dot-comment"},
            {regex: /#.*$/, token: "dot-comment"},
            {regex: /"(?:[^\\\\"]|\\\\.)*"/, token: "dot-string"},
            {regex: /\b(?:digraph|graph|subgraph|node|edge|strict)\b/, token: "dot-keyword"},
            {regex: /\b(?:label|color|style|shape|rankdir|splines|fontname|fontsize|fillcolor|bgcolor|tooltip|href|target|penwidth|cluster)\b/, token: "dot-attr"},
            {regex: /\b\d+(?:\.\d+)?\b/, token: "dot-number"},
            {regex: /->|--|=|:/, token: "dot-operator"},
            {regex: /[{}[\]();,]/, token: "dot-brace"},
            {regex: /[A-Za-z_][A-Za-z0-9_.-]*/, token: "dot-id"}
        ],
        meta: {
            lineComment: "//"
        }
    });

    var textarea = document.getElementById("dot-online-editor");
    var editor = CodeMirror.fromTextArea(textarea, {
        mode: "dot-mode",
        lineNumbers: true,
        matchBrackets: true,
        indentUnit: 4,
        tabSize: 4,
        lineWrapping: false
    });

    var form = textarea.closest("form");
    if (form) {
        form.addEventListener("submit", function () {
            editor.save();
        });
    }

    var autoRenderTimer = null;
    var changeCount = 0;
    var renderRequest = null;
    var renderRequestCounter = 0;
    var errorStack = document.getElementById("dot-online-error-stack");
    var previewBox = document.getElementById("dot-online-preview");
    var metaActions = document.getElementById("dot-online-meta-actions");
    var downloadSvgLink = document.getElementById("dot-online-download-svg");
    var fallbackActions = document.getElementById("dot-online-fallback-actions");
    var importDetails = document.getElementById("dot-online-import-details");
    var importTrigger = document.getElementById("dot-online-import-trigger");
    var importDropzone = document.getElementById("dot-online-dropzone");
    var importForm = document.getElementById("dot-import-form");
    var importFileInput = document.getElementById("dot-import-file");
    var importPayloadField = document.getElementById("dot-import-payload");
    var importGraphSelect = document.getElementById("dot-import-selected-graph");

    if (importTrigger && importDetails) {
        importTrigger.addEventListener("click", function () {
            importDetails.open = !importDetails.open;
            if (importDetails.open) {
                if (importPayloadField) {
                    importPayloadField.focus();
                }
            }
        });
    }

    if (importGraphSelect && window.jQuery && window.jQuery.fn && window.jQuery.fn.selectpicker) {
        window.jQuery(importGraphSelect).selectpicker();
    }

    if (importForm) {
        importForm.addEventListener("submit", function (event) {
            var hasFile = !!(importFileInput && importFileInput.files && importFileInput.files.length);
            var hasPayload = !!(importPayloadField && importPayloadField.value.trim() !== "");

            if (hasFile || hasPayload) {
                return;
            }

            event.preventDefault();
            if (importDetails) {
                importDetails.open = true;
            }
            if (importPayloadField) {
                importPayloadField.focus();
            }
        });
    }

    if (importDropzone && importFileInput) {
        ["dragenter", "dragover"].forEach(function (eventName) {
            importDropzone.addEventListener(eventName, function (event) {
                event.preventDefault();
                event.stopPropagation();
                importDropzone.classList.add("is-dragover");
                if (importDetails) {
                    importDetails.open = true;
                }
            });
        });

        ["dragleave", "dragend", "drop"].forEach(function (eventName) {
            importDropzone.addEventListener(eventName, function (event) {
                event.preventDefault();
                event.stopPropagation();
                importDropzone.classList.remove("is-dragover");
            });
        });

        importDropzone.addEventListener("drop", function (event) {
            var files = event.dataTransfer && event.dataTransfer.files ? event.dataTransfer.files : null;
            if (!files || !files.length) {
                return;
            }

            importFileInput.files = files;

            var firstFile = files[0];
            if (firstFile && importPayloadField && typeof FileReader !== "undefined") {
                var reader = new FileReader();
                reader.onload = function (loadEvent) {
                    if (loadEvent.target && typeof loadEvent.target.result === "string") {
                        importPayloadField.value = loadEvent.target.result;
                    }
                };
                reader.readAsText(firstFile);
            }
        });
    }

    function ensureDownloadSvgLink() {
        if (downloadSvgLink || !metaActions) {
            return downloadSvgLink;
        }

        downloadSvgLink = document.createElement("a");
        downloadSvgLink.id = "dot-online-download-svg";
        downloadSvgLink.className = "btn btn-primary";
        downloadSvgLink.textContent = "Download SVG";
        downloadSvgLink.setAttribute("download", <?= json_encode($downloadSvgName) ?>);

        var jsonLink = metaActions.querySelector('a[href*="dot3/download"]');
        if (jsonLink) {
            metaActions.insertBefore(downloadSvgLink, jsonLink);
        } else {
            metaActions.appendChild(downloadSvgLink);
        }

        return downloadSvgLink;
    }

    function setDownloadSvgHref(href) {
        if (!href) {
            if (downloadSvgLink) {
                downloadSvgLink.remove();
                downloadSvgLink = null;
            }
            return;
        }

        var link = ensureDownloadSvgLink();
        if (link) {
            link.href = href;
        }
    }

    function pushRenderError(message) {
        if (!errorStack || !message) {
            return;
        }

        var wrapper = document.createElement("div");
        wrapper.className = "dot-online-error-card";
        wrapper.innerHTML =
            '<button type="button" class="dot-online-error-card-close" aria-label="Close">×</button>' +
            '<div class="dot-online-error-card-title"><strong>Graphviz error</strong></div>' +
            '<div class="dot-online-error-card-body"></div>';

        wrapper.querySelector(".dot-online-error-card-body").textContent = String(message);
        wrapper.querySelector(".dot-online-error-card-close").addEventListener("click", function () {
            wrapper.remove();
        });

        errorStack.insertBefore(wrapper, errorStack.firstChild);
    }

    function submitPreview() {
        if (!form) {
            return;
        }

        editor.save();

        if (renderRequest) {
            renderRequest._wasAborted = true;
            renderRequest.abort();
        }

        var xhr = new XMLHttpRequest();
        var requestId = ++renderRequestCounter;
        renderRequest = xhr;
        xhr.open("POST", form.getAttribute("action") || window.location.href, true);
        xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
        xhr.setRequestHeader("Accept", "application/json");
        xhr.onreadystatechange = function () {
            if (xhr.readyState !== 4) {
                return;
            }

            if (xhr._wasAborted || requestId !== renderRequestCounter || xhr.status === 0) {
                return;
            }

            if (xhr.status < 200 || xhr.status >= 300) {
                pushRenderError("Unable to render DOT as SVG.");
                if (renderRequest === xhr) {
                    renderRequest = null;
                }
                return;
            }

            var payload = null;
            try {
                payload = JSON.parse(xhr.responseText);
            } catch (e) {
                pushRenderError("Unable to render DOT as SVG.");
                if (renderRequest === xhr) {
                    renderRequest = null;
                }
                return;
            }

            console.log("Cluster/viewDot preview payload", payload);

            if (payload && !payload.render_error && typeof payload.svg === "string" && payload.svg !== "" && previewBox) {
                previewBox.innerHTML = payload.svg;
                if (payload.download_svg_href) {
                    setDownloadSvgHref(payload.download_svg_href);
                }
            }

            if (payload && payload.render_error) {
                if (previewBox) {
                    previewBox.innerHTML = "";
                }
                setDownloadSvgHref("");
                pushRenderError(payload.render_error);
            }
            if (renderRequest === xhr) {
                renderRequest = null;
            }
        };
        xhr.send(new FormData(form));
    }

    function scheduleAutoRender() {
        if (!form) {
            return;
        }

        changeCount += 1;
        var scheduledCount = changeCount;

        if (autoRenderTimer !== null) {
            clearTimeout(autoRenderTimer);
        }

        autoRenderTimer = window.setTimeout(function () {
            if (scheduledCount === changeCount) {
                submitPreview();
            }
        }, 1000);
    }

    var activeLineHandle = null;

    function updateActiveLine() {
        if (!editor) {
            return;
        }

        if (activeLineHandle !== null) {
            editor.removeLineClass(activeLineHandle, "background", "CodeMirror-activeline-background");
        }

        activeLineHandle = editor.getCursor().line;
        editor.addLineClass(activeLineHandle, "background", "CodeMirror-activeline-background");
    }

    editor.on("cursorActivity", updateActiveLine);
    editor.on("change", scheduleAutoRender);
    updateActiveLine();

    if (fallbackActions) {
        fallbackActions.style.display = "none";
    }

    <?php if ($renderError !== ''): ?>
    pushRenderError(<?= json_encode($renderError) ?>);
    <?php endif; ?>
}
</script>
