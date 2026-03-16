<?php

use \Glial\Synapse\FactoryController;

$idMysqlServer = (int) ($data['id_mysql_server'] ?? 0);
$dot = (string) ($data['dot'] ?? '');
$svg = (string) ($data['svg'] ?? '');
$renderError = (string) ($data['render_error'] ?? '');
$dateInserted = (string) ($data['date_inserted'] ?? '');
$md5 = (string) ($data['md5'] ?? '');
$filename = (string) ($data['filename'] ?? '');
$dotLength = (int) ($data['dot_length'] ?? strlen($dot));
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
}

.dot-online-chip {
    display: inline-block;
    padding: 5px 10px;
    border-radius: 999px;
    background: #f8fafc;
    border: 1px solid #dbe5ef;
    font-size: 12px;
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
    background: linear-gradient(135deg,#111827,#1d4ed8);
    color: #fff;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
}

.dot-online-panel-body {
    padding: 12px;
}

.dot-online-editor-panel .dot-online-panel-body {
    display: flex;
    flex: 1;
    padding: 0 !important;
}

.dot-online-top-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.dot-online-code-shell {
    border: 1px solid #dbe5ef;
    border-radius: 6px;
    overflow: hidden;
    background: #0f172a;
    display: flex;
    flex: 1;
    width: 100%;
}

.dot-online-editor-panel form {
    display: flex;
    flex: 1;
    width: 100%;
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
}

.dot-online-editor-panel .CodeMirror-gutters {
    background: #111827;
    border-right: 1px solid rgba(255,255,255,.08);
}

.dot-online-editor-panel .CodeMirror-lines {
    background: #0f172a;
}

.dot-online-editor-panel .CodeMirror-linenumber {
    color: #64748b;
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

    .dot-online-editor,
    .dot-online-preview {
        min-height: 48vh;
    }

    .dot-online-code-shell,
    .dot-online-editor-panel .CodeMirror {
        max-height: 48vh;
    }
}
</style>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/codemirror.min.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/codemirror.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/addon/mode/simple.min.js"></script>

<div class="dot-online-shell">
    <div style="margin-bottom:10px;">
        <?= FactoryController::addNode("MysqlServer", "menu", [$idMysqlServer]); ?>
    </div>

    <div class="dot-online-toolbar">
        <div>
            <h1 class="dot-online-title">DOT / Graphviz Viewer</h1>
            <div class="dot-online-subtitle">Editor on the left, rendered graph on the right.</div>
        </div>
        <div class="dot-online-top-actions">
            <button type="submit" form="dot-online-form" class="btn btn-primary">Render</button>
            <a href="<?= LINK ?>Cluster/viewDot/<?= $idMysqlServer ?>/" class="btn btn-default">Reset</a>
            <a href="<?= LINK ?>dot3/download/" class="btn btn-success">Download JSON</a>
            <a href="<?= LINK ?>Cluster/svg/<?= $idMysqlServer ?>/" class="btn btn-default">Back to Cluster</a>
        </div>
    </div>

    <div class="dot-online-meta">
        <span class="dot-online-chip">Server ID: <?= $idMysqlServer ?></span>
        <?php if ($dateInserted !== ''): ?>
            <span class="dot-online-chip">Refresh: <?= htmlspecialchars($dateInserted, ENT_QUOTES, 'UTF-8') ?></span>
        <?php endif; ?>
        <?php if ($md5 !== ''): ?>
            <span class="dot-online-chip">MD5: <?= htmlspecialchars($md5, ENT_QUOTES, 'UTF-8') ?></span>
        <?php endif; ?>
        <?php if ($filename !== ''): ?>
            <span class="dot-online-chip">File: <?= htmlspecialchars(basename($filename), ENT_QUOTES, 'UTF-8') ?></span>
        <?php endif; ?>
    </div>

    <?php if ($renderError !== ''): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($renderError, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <div class="dot-online-layout">
        <div class="dot-online-panel dot-online-editor-panel" id="dot-online-editor-panel">
            <div class="dot-online-panel-head">
                <span>DOT Source</span>
                <span style="font-size:12px; opacity:.85;">Graphviz DOT</span>
            </div>
            <div class="dot-online-panel-body">
                <form method="post" action="" id="dot-online-form">
                    <div class="dot-online-code-shell" id="dot-online-code-shell">
                        <textarea id="dot-online-editor" class="dot-online-editor" name="dot_preview[dot]" spellcheck="false"><?= htmlspecialchars($dot, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></textarea>
                    </div>
                </form>
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

    function refreshEditorLayout() {
        var codeShell = document.getElementById("dot-online-code-shell");
        var editorPanel = document.getElementById("dot-online-editor-panel");
        var renderPanel = document.querySelector(".dot-online-render-panel");
        if (!editor || !codeShell || !editorPanel || !renderPanel) {
            return;
        }

        var editorTop = codeShell.getBoundingClientRect().top;
        var viewportLimit = window.innerHeight - editorTop + 24;
        var head = editorPanel.querySelector(".dot-online-panel-head");
        var headHeight = head ? head.offsetHeight : 0;
        var renderBodyHeight = renderPanel.offsetHeight - headHeight;
        var targetHeight = Math.max(320, Math.min(renderBodyHeight, viewportLimit));

        codeShell.style.height = targetHeight + "px";
        editor.setSize(null, targetHeight);
        editor.refresh();
    }

    window.addEventListener("load", refreshEditorLayout);
    window.addEventListener("resize", refreshEditorLayout);
    setTimeout(refreshEditorLayout, 0);
}
</script>
