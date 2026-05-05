<?php

$totals  = $data['totals'];
$reasons = $data['reasons'];
$events  = $data['events'];

echo '<div style="margin-bottom:15px">';
echo '<b>Log file:</b> <code>'.htmlspecialchars($data['log_file']).'</code> ';
if (!$data['log_exists']) {
    echo '<span class="label label-warning">missing</span>';
} else {
    echo '<span class="muted">('.number_format($data['log_size']).' bytes)</span>';
}
echo '</div>';

if (!$data['log_exists']) {
    echo '<div class="alert alert-warning">No PHP error log found — nothing to parse. Is <code>error_log</code> writing to <code>tmp/log/error_php.log</code>?</div>';
    return;
}

if (empty($events)) {
    echo '<div class="alert alert-success">No PersistentAuthSession revoke or rotation_lost_race event in the log. The opaque-cookie pipeline has not had to log anything since this file was rotated.</div>';
    echo '<p class="muted">If you expected events here, double-check that the live deployment runs the post-issue-#773 code (file <code>App/Library/Security/PersistentAuthSession.php</code> must contain <code>logRevoke</code> / <code>logRotationLostRace</code>) and that the migration <code>sql/incremental_v2/20260506_persistent_auth_grace_window.sql</code> was applied (columns <code>previous_token_hash</code> / <code>previous_token_expires</code> on <code>user_persistent_auth_session</code>).</p>';
    return;
}

echo '<div class="row" style="margin-bottom:20px">';

$reasonLabel = function (string $reason): string {
    static $labels = [
        'token_mismatch'       => 'label-danger',
        'fingerprint_mismatch' => 'label-warning',
        'expired'              => 'label-default',
        'auth_apply_failed'    => 'label-danger',
        'unknown_selector'     => 'label-default',
        'invalid_session_id'   => 'label-default',
        'rotation_lost_race'   => 'label-info',
    ];
    $cls = $labels[$reason] ?? 'label-default';
    return '<span class="label '.$cls.'">'.htmlspecialchars($reason).'</span>';
};

echo '<div class="col-md-6">';
echo '<table class="display-tab table table-condensed">';
echo '<thead><tr><th colspan="2">Summary</th></tr></thead>';
echo '<tbody>';
echo '<tr><td>Total PersistentAuthSession events</td><td><b>'.(int) $totals['total'].'</b></td></tr>';
echo '<tr><td>token_mismatch (real revokes — investigate if &gt; 0)</td><td><b>'.(int) $totals['token_mismatch_total'].'</b></td></tr>';
echo '<tr><td>rotation_lost_race (benign, grace window absorbed it)</td><td><b>'.(int) $totals['rotation_lost_race_total'].'</b></td></tr>';
echo '<tr><td>Last 24 h</td><td><b>'.(int) $totals['last_24h'].'</b></td></tr>';
echo '<tr><td>Last 7 days</td><td><b>'.(int) $totals['last_7d'].'</b></td></tr>';
echo '</tbody>';
echo '</table>';
echo '</div>';

echo '<div class="col-md-6">';
echo '<table class="display-tab table table-condensed">';
echo '<thead><tr><th>Reason</th><th style="text-align:right">Count</th><th>Last seen (UTC)</th></tr></thead>';
echo '<tbody>';
foreach ($reasons as $reason => $stats) {
    echo '<tr>';
    echo '<td>'.$reasonLabel($reason).'</td>';
    echo '<td style="text-align:right">'.(int) $stats['count'].'</td>';
    echo '<td><small>'.htmlspecialchars((string) $stats['last_seen_text']).'</small></td>';
    echo '</tr>';
}
echo '</tbody>';
echo '</table>';
echo '</div>';

echo '</div>';

if ((int) $totals['token_mismatch_total'] === 0 && (int) $totals['rotation_lost_race_total'] > 0) {
    echo '<div class="alert alert-info"><b>Healthy.</b> Only <code>rotation_lost_race</code> events present — the grace window is doing its job. No real revoke. ';
    echo 'See issue <a href="https://git.istosia.com/pmacontrol/pmacontrol/issues/773">#773</a> for the design.</div>';
}

if ((int) $totals['token_mismatch_total'] > 0) {
    echo '<div class="alert alert-warning"><b>Active <code>token_mismatch</code> revokes.</b> A request arrived with a verifier that matches neither the current <code>token_hash</code> nor an unexpired <code>previous_token_hash</code>. ';
    echo 'Likely causes: a request stalled longer than <code>PREVIOUS_TOKEN_GRACE_SECONDS</code> (10 s) between cookie read and server-side authenticate, two consecutive rotations chained while the request was in flight, or a stolen-cookie replay. ';
    echo 'If recurrent, increase <code>PREVIOUS_TOKEN_GRACE_SECONDS</code> in <code>App/Library/Security/PersistentAuthSession.php</code> or chase the latency issue.</div>';
}

echo '<h3 style="margin-top:25px">Recent events <small>(latest 200)</small></h3>';
echo '<table class="display-tab table table-condensed" width="100%" style="table-layout:fixed; word-wrap:break-word;">';
echo '<colgroup>'
    .'<col style="width:170px">'
    .'<col style="width:160px">'
    .'<col style="width:140px">'
    .'<col style="width:130px">'
    .'<col>'
    .'</colgroup>';
echo '<thead><tr>';
echo '<th>Timestamp (UTC)</th>';
echo '<th>Reason</th>';
echo '<th>Selector prefix</th>';
echo '<th>Remote IP</th>';
echo '<th>User-Agent prefix</th>';
echo '</tr></thead>';
echo '<tbody>';
foreach ($events as $event) {
    echo '<tr>';
    echo '<td><small>'.htmlspecialchars((string) $event['ts_text']).'</small></td>';
    echo '<td>'.$reasonLabel($event['reason']).'</td>';
    echo '<td><code>'.htmlspecialchars($event['selector']).'…</code></td>';
    echo '<td><small>'.htmlspecialchars((string) $event['remote']).'</small></td>';
    echo '<td><small>'.htmlspecialchars((string) $event['ua']).'</small></td>';
    echo '</tr>';
}
echo '</tbody>';
echo '</table>';
