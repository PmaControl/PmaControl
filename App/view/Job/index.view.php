<?php

//use Glial\Html\Form\Form;

use App\Library\Security\CsrfRender;
use App\Library\Security\SecretRedactor;
use SensioLabs\AnsiConverter\AnsiToHtmlConverter;

$jobRestartCsrfInput = CsrfRender::hiddenInput($data, 'job_restart');

/*
 * Issue #580 — bound log/error cells to ~10 lines × 1000 px with a
 * scrollbar, monospace + black background preserved. The auto-scroll
 * JS below pins the view on the LAST line (most relevant: current
 * status / latest CRITICAL).
 */
function getBadge($status)
{

    switch ($status) {
        case "ERROR":
            $color = "danger";
            break;

        case "INTERRUPTED":
            $color = "default";
            break;

        case "SUCCESS":
            $color = "success";
            break;

        case "WARNING":
            $color = "warning";
            break;


        case "RUNNING":
            $color = "primary";
            break;


        default:
            break;
    }

    return $color;
}
?>
<style>
.job-log {
    background-color: #000;
    color: #ddd;
    padding: 5px;
    margin: 0;
    font-family: monospace;
    /* ~10 lines (1.4em line-height × 10) + padding */
    max-height: 14em;
    max-width: 1000px;
    overflow: auto;       /* both axes — vertical scroll for >10 lines, horizontal for long mydumper command lines */
    white-space: pre;     /* keep mydumper lines on one line; the horizontal scrollbar handles long lines */
}
/* Issue #597: same height/width contract for the param column, but
 * with a light viewer-style background — the param JSON pretty-print
 * is structured data, not console output. */
.job-param {
    background-color: #f8f9fa;
    color: #1f2937;
    padding: 5px;
    margin: 0;
    font-family: monospace;
    max-height: 14em;
    max-width: 350px;
    overflow: auto;
    white-space: pre;
}
</style>
<script>
document.addEventListener('DOMContentLoaded', function () {
    /* Issue #580: focus the LAST line of every log/error pre, so the
     * operator sees the current status / latest CRITICAL without scrolling. */
    document.querySelectorAll('.job-log').forEach(function (el) {
        el.scrollTop = el.scrollHeight;
    });
});
</script>
<div class="panel panel-primary">
    <div class="panel-heading">
        <h3 class="panel-title"><?= __('Listing') ?></h3>
    </div>


    <table class="table table-bordered table-striped" id="table">
        <tr>
            <th><?= __('Top') ?></th>
            <th><?= __('class') ?></th>
            <th><?= __('method') ?></th>
            <th><?= __('param') ?></th>
            <th><?= __('date start') ?></th>
            <th><?= __('date end') ?></th>
            <th><?= __('pid') ?></th>
            <th><?= __('status') ?></th>
            <th><?= __('log') ?></th>
            <th><?= __('error') ?></th>
        </tr>

        <?php
        $i = 0;
        foreach ($data['jobs'] as $job):
            $i++;
            $redactedParam = SecretRedactor::jsonPayload((string) ($job['param'] ?? ''));
            $decodedParam = json_decode($redactedParam, true);
            $displayParam = json_last_error() === JSON_ERROR_NONE
                ? (json_encode($decodedParam, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '')
                : $redactedParam;
            ?>

            <tr>
                <td><?= $i ?></td>
                <td><?= $job['class'] ?></td>
                <td><?= $job['method'] ?></td>
                <td><pre class="job-param"><?= htmlspecialchars(
                    (string) $displayParam,
                    ENT_QUOTES | ENT_SUBSTITUTE,
                    'UTF-8'
                ) ?></pre></td>
                <td><?= $job['date_start'] ?></td>
                <td><?= $job['date_end'] ?></td>
                <td><?= $job['pid'] ?></td>
                <td><big><span class="label label-<?= getBadge($job['status']) ?>"><?= $job['status'] ?></span></big>

                <?php

                if ($job['status'] !== "RUNNING")
                {
                    echo '<br />';
                    echo '<br />';

                    echo __("options : ")."<br />";
                    $jobId = (int) $job['id'];
                    echo '<form method="post" action="'.LINK.'job/restart/'.$jobId.'/" style="display:inline">'.$jobRestartCsrfInput.'<button type="submit" class="btn btn-primary btn-xs"><i class="fa fa-refresh fa-spin"></i> '.__('Restart').'</button></form><br/>';
                    echo '<form method="post" action="'.LINK.'job/restart/'.$jobId.'/--debug" style="display:inline">'.$jobRestartCsrfInput.'<button type="submit" style="margin-top:4px;" class="btn btn-warning btn-xs"><i class="fa fa-cog fa-spin"></i> '.__('Debug').'</button></form><br/>';

                    // Issue #583: load-only restart, only when the dump succeeded
                    // and the artefact is still on disk and not expired.
                    if (!empty($job['can_restart_load_only'])) {
                        echo '<a href="'.LINK.'database/restartLoadOnly/'.(int) $job['id'].'/"'
                            .' type="button" style="margin-top:4px;"'
                            .' class="btn btn-success btn-xs"'
                            .' title="'.htmlspecialchars((string) ($job['artifact_path'] ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8').'">'
                            .'<i class="fa fa-fast-forward"></i> '
                            .__('Restart load only').'</a><br/>';
                    }
                }

                ?>

                </td>


            <td><?php
                if (!empty($job['log_msg'])) {
                    echo '<pre class="job-log">'.$job['log_msg'].'</pre>';
                }
                ?></td>
            <td><?php
                if (!empty($job['error_msg'])) {
                    echo '<pre class="job-log">'.$job['error_msg'].'</pre>';
                }
                ?></td>

            </tr>

            <?php
        endforeach;
        ?>

    </table>
