<?php

use Glial\Synapse\FactoryController;

?>

<?=
    !empty($data['diff_css'])
        ? $data['diff_css']
        : ''
?>

<style>
.schema-compare .schema-pill { display: inline-block; margin: 2px 4px 2px 0; padding: 2px 8px; border-radius: 3px; background: #f5f5f5; font-size: 12px; }
.schema-compare .schema-panel { margin-bottom: 20px; }
.schema-compare .schema-object + .schema-object { margin-top: 20px; }
.schema-compare .schema-object-title { font-weight: 600; margin-bottom: 6px; }
.schema-compare .schema-empty { color: #888; font-style: italic; }
</style>

<div class="schema-compare">
    <form action="" method="get" class="well">
        <div class="row">
            <div class="col-md-5">
                <label><?= __("Left server") ?></label>
                <?php
                FactoryController::addNode(
                    "Common",
                    "getSelectServerAvailable",
                    array("schema_compare", "id_mysql_server__left", array("data-width" => "100%"))
                );
                ?>
            </div>
            <div class="col-md-5">
                <label><?= __("Right server") ?></label>
                <?php
                FactoryController::addNode(
                    "Common",
                    "getSelectServerAvailable",
                    array("schema_compare", "id_mysql_server__right", array("data-width" => "100%"))
                );
                ?>
            </div>
            <div class="col-md-2">
                <label>&nbsp;</label>
                <div class="checkbox" style="margin-top:0;">
                    <label>
                        <input type="checkbox"
                               name="schema_compare[ignore_column_order]"
                               value="1"
                               <?= !empty($data['ignore_column_order']) ? 'checked' : ''; ?>>
                        <?= __("Ignore column order"); ?>
                    </label>
                </div>
                <button type="submit" class="btn btn-primary btn-block">
                    <span class="glyphicon glyphicon-transfer"></span> <?= __("Compare") ?>
                </button>
            </div>
        </div>
    </form>

    <?php if (!empty($data['errors'])): ?>
        <div class="alert alert-danger">
            <ul class="list-unstyled" style="margin:0;">
                <?php foreach ($data['errors'] as $error): ?>
                    <li><?= htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if (!empty($data['comparison'])): ?>
        <?php
        $comparison = $data['comparison'];
        $dbInfo = $comparison['databases'];
        $noDiff = empty($dbInfo['left_only']) && empty($dbInfo['right_only']) && empty($dbInfo['differences']);
        ?>

        <div class="panel panel-default schema-panel">
            <div class="panel-heading">
                <?= sprintf(
                    __("Summary for servers #%d and #%d"),
                    $comparison['left'],
                    $comparison['right']
                ); ?>
            </div>
            <div class="panel-body">
                <?php if (!empty($data['ignore_column_order'])): ?>
                    <p class="text-muted" style="margin-top:0;">
                        <?= __("Column order differences are ignored for this comparison."); ?>
                    </p>
                <?php endif; ?>
                <?php if ($noDiff): ?>
                    <div class="alert alert-success" style="margin-bottom:0;">
                        <?= __("No differences detected between the exported schemas."); ?>
                    </div>
                <?php else: ?>
                    <?php if (!empty($dbInfo['left_only'])): ?>
                        <div class="alert alert-info">
                            <strong><?= sprintf(__("Databases only found on server #%d:"), $comparison['left']); ?></strong>
                            <div>
                                <?php foreach ($dbInfo['left_only'] as $database): ?>
                                    <span class="schema-pill"><?= htmlspecialchars($database); ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($dbInfo['right_only'])): ?>
                        <div class="alert alert-info">
                            <strong><?= sprintf(__("Databases only found on server #%d:"), $comparison['right']); ?></strong>
                            <div>
                                <?php foreach ($dbInfo['right_only'] as $database): ?>
                                    <span class="schema-pill"><?= htmlspecialchars($database); ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($dbInfo['differences'])): ?>
                        <table class="table table-condensed table-striped" style="margin-bottom:0;">
                            <thead>
                                <tr>
                                    <th><?= __("Database"); ?></th>
                                    <th><?= __("Missing on left"); ?></th>
                                    <th><?= __("Missing on right"); ?></th>
                                    <th><?= __("Objects with differences"); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($dbInfo['differences'] as $dbName => $diff): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($dbName); ?></td>
                                        <td><?= count($diff['right_only']); ?></td>
                                        <td><?= count($diff['left_only']); ?></td>
                                        <td><?= count($diff['different']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!empty($data['detailed'])): ?>
            <?php foreach ($data['detailed'] as $database): ?>
                <div class="panel panel-info schema-panel">
                    <div class="panel-heading">
                        <?= sprintf(__("Database: %s"), htmlspecialchars($database['name'])); ?>
                    </div>
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-6">
                                <strong><?= __("Missing on right"); ?></strong><br>
                                <?php if (!empty($database['left_only'])): ?>
                                    <?php foreach ($database['left_only'] as $object): ?>
                                        <span class="schema-pill"><?= htmlspecialchars($object); ?></span>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <span class="schema-empty"><?= __("None"); ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <strong><?= __("Missing on left"); ?></strong><br>
                                <?php if (!empty($database['right_only'])): ?>
                                    <?php foreach ($database['right_only'] as $object): ?>
                                        <span class="schema-pill"><?= htmlspecialchars($object); ?></span>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <span class="schema-empty"><?= __("None"); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php if (!empty($database['objects'])): ?>
                            <hr>
                            <?php foreach ($database['objects'] as $object): ?>
                                <div class="schema-object">
                                    <div class="schema-object-title">
                                        <?= sprintf(__("Diff for %s"), htmlspecialchars($object['name'])); ?>
                                    </div>
                                    <div class="table-responsive">
                                        <?= $object['diff']; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    <?php endif; ?>
</div>
