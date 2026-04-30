<?php

use App\Controller\Query;

$h = static fn($value): string => htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$idMysqlServer = (int)($data['id_mysql_server'] ?? 0);
$schemaName = (string)($data['schema_name'] ?? '');
$digest = (string)($data['digest'] ?? '');
$digestHref = LINK . (string)($data['digest_href'] ?? Query::buildDigestPath($idMysqlServer, $schemaName, $digest));
?>

<div class="container" style="width:100%">
    <p>
        <a class="btn btn-default btn-sm" href="<?= $h($digestHref) ?>"><?= __("Back to digest") ?></a>
    </p>

    <div class="panel panel-primary">
        <div class="panel-heading">
            <h3 class="panel-title"><?= __("Query graph") ?> - <?= $h($digest) ?></h3>
        </div>
        <div class="panel-body">
            <dl class="dl-horizontal">
                <dt><?= __("Server") ?></dt>
                <dd><?= $idMysqlServer ?></dd>
                <dt><?= __("Schema") ?></dt>
                <dd><?= $schemaName === '' ? 'NULL' : $h($schemaName) ?></dd>
                <dt><?= __("Statement") ?></dt>
                <dd><?= $h($data['graph']['statement_type'] ?? 'UNKNOWN') ?></dd>
            </dl>

            <?php if (!empty($data['graph']['errors'])): ?>
                <div class="alert alert-warning">
                    <strong><?= __("Parser warnings") ?></strong>
                    <ul>
                        <?php foreach ($data['graph']['errors'] as $error): ?>
                            <li><?= $h($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if (!empty($data['graphviz_error'])): ?>
                <div class="alert alert-warning">
                    <strong>Graphviz</strong> <?= $h($data['graphviz_error']) ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($data['svg'])): ?>
                <div style="overflow:auto; border:1px solid #ddd; padding:10px; background:#fff; margin-bottom:15px">
                    <?= $data['svg'] ?>
                </div>
            <?php else: ?>
                <div class="alert alert-info"><?= __("No graph could be rendered.") ?></div>
            <?php endif; ?>
        </div>
    </div>

    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title"><?= __("SQL sample") ?></h3>
        </div>
        <div class="panel-body">
            <pre style="white-space:pre-wrap"><?= $h($data['sql_text'] ?? '') ?></pre>
        </div>
    </div>

    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">DOT</h3>
        </div>
        <div class="panel-body">
            <pre style="white-space:pre-wrap"><?= $h($data['dot'] ?? '') ?></pre>
        </div>
    </div>
</div>
