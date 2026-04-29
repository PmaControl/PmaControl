<?php

$h = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$config = $data['config'] ?? [];
$result = $data['result'] ?? null;
$error = $data['error'] ?? null;
$enabled = !empty($config['enabled']);

?>

<form action="<?= LINK ?>llm/index" method="POST">
    <input type="hidden" name="<?= $h($data['llm_analyze_csrf_field']) ?>" value="<?= $h($data['llm_analyze_csrf_token']) ?>" />

    <div class="panel panel-primary">
        <div class="panel-heading">
            <h3 class="panel-title"><?= __('SQL Assistant') ?></h3>
        </div>
        <div class="panel-body">
            <?php if (!$enabled) : ?>
                <div class="alert alert-warning">
                    <?= __('LLM assistant is disabled in configuration.') ?>
                </div>
            <?php endif; ?>

            <div class="form-group">
                <label for="llm-input"><?= __('SHOW CREATE TABLE + EXPLAIN') ?></label>
                <textarea
                    id="llm-input"
                    name="llm[input]"
                    class="form-control"
                    rows="12"
                    maxlength="<?= (int) ($config['max_input_bytes'] ?? 20000) ?>"
                ><?= $h($data['input'] ?? '') ?></textarea>
            </div>

            <p class="text-muted">
                <?= __('Endpoint') ?>: <?= $h($config['endpoint'] ?? '') ?> |
                <?= __('Model') ?>: <?= $h($config['model'] ?? '') ?>
            </p>

            <button type="submit" class="btn btn-primary"<?= $enabled ? '' : ' disabled="disabled"' ?>>
                <?= __('Analyze') ?>
            </button>
        </div>
    </div>
</form>

<?php if ($error !== null) : ?>
    <div class="alert alert-danger"><?= $h($error) ?></div>
<?php endif; ?>

<?php if (is_array($result)) : ?>
    <?php if (($result['status'] ?? '') !== 'OK') : ?>
        <div class="alert alert-warning">
            <?= $h($result['error'] ?? __('LLM returned an incomplete answer.')) ?>
            <?php if (!empty($result['missing_input'])) : ?>
                <ul>
                    <?php foreach ($result['missing_input'] as $missingInput) : ?>
                        <li><?= $h($missingInput) ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    <?php elseif (empty($result['indexes'])) : ?>
        <div class="alert alert-info"><?= __('No missing index detected.') ?></div>
    <?php else : ?>
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title"><?= __('Index suggestions') ?></h3>
            </div>
            <table class="table table-striped table-condensed">
                <thead>
                    <tr>
                        <th><?= __('Table') ?></th>
                        <th><?= __('Columns') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($result['indexes'] as $index) : ?>
                        <tr>
                            <td><code><?= $h($index['table']) ?></code></td>
                            <td><code><?= $h(implode(', ', $index['columns'])) ?></code></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if (!empty($result['alter_statements'])) : ?>
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title"><?= __('ALTER TABLE statements') ?></h3>
                </div>
                <div class="panel-body">
                    <pre><?php foreach ($result['alter_statements'] as $statement) : ?><?= $h($statement) . "\n" ?><?php endforeach; ?></pre>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
<?php endif; ?>
