<?php

namespace App\Controller;

use \Glial\I18n\I18n;
use \Glial\Synapse\Controller;
use \Glial\Sgbd\Sgbd;
use \Monolog\Logger;
use \Monolog\Formatter\LineFormatter;
use \Monolog\Handler\StreamHandler;

use \App\Library\Debug;
use App\Library\Graphviz;

/**
 * Class responsible for cluster workflows.
 *
 * This class belongs to the PmaControl application layer and documents the
 * public surface consumed by controllers, services, static analysis tools and IDEs.
 *
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
class Cluster extends Controller
{

    private static function formatDotSource(string $dot): string
    {
        $lines = preg_split('/\R/', $dot) ?: [];
        $formatted = [];
        $indentLevel = 0;
        $inHtmlLabel = false;
        $htmlIndentLevel = 0;

        foreach ($lines as $line) {
            $rawLine = rtrim($line, "\r");
            $trimmed = trim($rawLine);

            if ($trimmed === '') {
                if (!empty($formatted) && end($formatted) !== '') {
                    $formatted[] = '';
                }
                continue;
            }

            if (strpos($trimmed, '<<') !== false) {
                $formatted[] = str_repeat('  ', $indentLevel) . $trimmed;
                $inHtmlLabel = true;
                $htmlIndentLevel = 0;
                continue;
            }

            if ($inHtmlLabel) {
                if ($trimmed === '>>' || str_starts_with($trimmed, '>>')) {
                    $formatted[] = str_repeat('  ', $indentLevel) . $trimmed;
                    $inHtmlLabel = false;
                    $htmlIndentLevel = 0;
                    continue;
                }

                if (preg_match('/^\s*</', $rawLine) === 1) {
                    $htmlLine = $trimmed;

                    if (preg_match('/^<\/(table|tr|td)\b/i', $htmlLine) === 1) {
                        $htmlIndentLevel = max(0, $htmlIndentLevel - 1);
                    }

                    $formatted[] = str_repeat('  ', $indentLevel + $htmlIndentLevel + 1) . $htmlLine;

                    if (preg_match('/^<(table|tr|td)\b/i', $htmlLine) === 1
                        && preg_match('/\/>$/', $htmlLine) !== 1
                        && preg_match('/^<[^>]+>.*<\/[^>]+>$/', $htmlLine) !== 1) {
                        $htmlIndentLevel++;
                    }

                    continue;
                }

                $formatted[] = str_repeat('  ', $indentLevel + $htmlIndentLevel + 1) . $trimmed;
                continue;
            }

            $prefixClosers = 0;
            while (isset($trimmed[$prefixClosers]) && $trimmed[$prefixClosers] === '}') {
                $prefixClosers++;
            }

            if ($prefixClosers > 0) {
                $indentLevel = max(0, $indentLevel - $prefixClosers);
            }

            $formatted[] = str_repeat('  ', $indentLevel) . $trimmed;

            $opens = substr_count($trimmed, '{');
            $closes = substr_count($trimmed, '}');
            $indentLevel += max(0, $opens - $closes + $prefixClosers);
        }

        return trim(implode(PHP_EOL, $formatted)) . PHP_EOL;
    }

    private static function getImportBundlePath(string $previewKey): string
    {
        return TMP . 'dot/view-dot-import-' . $previewKey . '.json';
    }

    private static function getPreviewStatePath(string $previewKey): string
    {
        return TMP . 'dot/view-dot-preview-' . $previewKey . '.json';
    }

    private static function loadImportBundle(string $previewKey): array
    {
        if ($previewKey === '') {
            return array();
        }

        $file = self::getImportBundlePath($previewKey);
        if (!file_exists($file)) {
            return array();
        }

        $payload = file_get_contents($file);
        if ($payload === false || $payload === '') {
            return array();
        }

        $bundle = json_decode($payload, true);
        return is_array($bundle) ? $bundle : array();
    }

    private static function saveImportBundle(string $previewKey, array $bundle): void
    {
        if ($previewKey === '') {
            return;
        }

        self::cleanupStaleImportBundles();
        file_put_contents(self::getImportBundlePath($previewKey), json_encode($bundle, JSON_INVALID_UTF8_SUBSTITUTE));
    }

    private static function deleteImportBundle(string $previewKey): void
    {
        if ($previewKey === '') {
            return;
        }

        $file = self::getImportBundlePath($previewKey);
        if (file_exists($file)) {
            @unlink($file);
        }
    }

    private static function loadPreviewState(string $previewKey): array
    {
        if ($previewKey === '') {
            return array();
        }

        $file = self::getPreviewStatePath($previewKey);
        if (!file_exists($file)) {
            return array();
        }

        $payload = file_get_contents($file);
        if ($payload === false || $payload === '') {
            return array();
        }

        $state = json_decode($payload, true);
        return is_array($state) ? $state : array();
    }

    private static function savePreviewState(string $previewKey, array $state): void
    {
        if ($previewKey === '') {
            return;
        }

        self::cleanupStaleImportBundles();
        file_put_contents(self::getPreviewStatePath($previewKey), json_encode($state, JSON_INVALID_UTF8_SUBSTITUTE));
    }

    private static function deletePreviewState(string $previewKey): void
    {
        if ($previewKey === '') {
            return;
        }

        $file = self::getPreviewStatePath($previewKey);
        if (file_exists($file)) {
            @unlink($file);
        }
    }

    private static function cleanupStaleImportBundles(): void
    {
        $files = glob(TMP . 'dot/view-dot-import-*.json');
        if ($files === false) {
            return;
        }

        $ttl = 86400;
        $now = time();

        foreach ($files as $file) {
            $mtime = @filemtime($file);
            if ($mtime !== false && ($now - $mtime) > $ttl) {
                @unlink($file);
            }
        }

        $previewFiles = glob(TMP . 'dot/view-dot-preview-*.json');
        if ($previewFiles === false) {
            return;
        }

        foreach ($previewFiles as $file) {
            $mtime = @filemtime($file);
            if ($mtime !== false && ($now - $mtime) > $ttl) {
                @unlink($file);
            }
        }
    }

    private static function buildViewDotUrl(int $idMysqlServer, string $previewKey, $selectedGraphIndex = null): string
    {
        $query = array();

        if ($previewKey !== '') {
            $query['preview_key'] = $previewKey;
        }

        if ($selectedGraphIndex !== null && $selectedGraphIndex !== '') {
            $query['selected_graph'] = (string) $selectedGraphIndex;
        }

        $url = LINK . 'Cluster/viewDot/' . $idMysqlServer . '/';
        if (!empty($query)) {
            $url .= '?' . http_build_query($query);
        }

        return $url;
    }

    private static function buildImportedGraphLabel(array $group, array $servers, int $index): string
    {
        $names = array();

        foreach (array_slice($group, 0, 3) as $id_mysql_server) {
            $server = $servers[$id_mysql_server] ?? array();
            $names[] = trim((string) ($server['display_name'] ?? $server['hostname'] ?? $id_mysql_server));
        }

        $label = 'Graph ' . ($index + 1);
        if (!empty($names)) {
            $label .= ' - ' . implode(', ', $names);
        }

        $remaining = count($group) - count($names);
        if ($remaining > 0) {
            $label .= ' +' . $remaining;
        }

        return $label;
    }

    private static function getSelectedImportGraphIndex(array $bundle, int $idMysqlServer, $requestedIndex = null)
    {
        $graphs = $bundle['graphs'] ?? array();

        if ($requestedIndex === '') {
            return null;
        }

        if (is_string($requestedIndex)) {
            $requestedIndex = trim($requestedIndex);
            if ($requestedIndex === '') {
                return null;
            }

            if (!ctype_digit($requestedIndex)) {
                return null;
            }
        }

        if ($requestedIndex !== null && isset($graphs[(int) $requestedIndex])) {
            return (int) $requestedIndex;
        }

        foreach ($graphs as $index => $graph) {
            $group = $graph['group'] ?? array();
            if (in_array((string) $idMysqlServer, array_map('strval', $group), true)) {
                return (int) $index;
            }
        }

        return null;
    }

    private static function sanitizeImportedSvgMarkup(string $svg): string
    {
        if ($svg === '') {
            return '';
        }

        $sanitized = preg_replace('#<script\b[^>]*>.*?</script>#is', '', $svg);
        $sanitized = preg_replace('#<foreignObject\b[^>]*>.*?</foreignObject>#is', '', (string) $sanitized);
        $sanitized = preg_replace('/\son[a-zA-Z0-9_-]+\s*=\s*(["\']).*?\1/is', '', (string) $sanitized);
        $sanitized = preg_replace('/\son[a-zA-Z0-9_-]+\s*=\s*[^>\s]+/is', '', (string) $sanitized);
        $sanitized = preg_replace('/\s(?:href|xlink:href)\s*=\s*(["\'])\s*javascript:[^"\']*\1/is', '', (string) $sanitized);

        return (string) $sanitized;
    }

    private static function extractImportedPayload(): array
    {
        $rawPayload = trim((string) ($_POST['dot_import']['payload'] ?? ''));
        $sourceLabel = 'Pasted JSON';

        if (!empty($_FILES['dot_import']['tmp_name']['file'])) {
            $uploadedPayload = file_get_contents($_FILES['dot_import']['tmp_name']['file']);
            if ($uploadedPayload !== false && trim($uploadedPayload) !== '') {
                $rawPayload = trim($uploadedPayload);
                $sourceLabel = (string) ($_FILES['dot_import']['name']['file'] ?? 'Imported JSON file');
            }
        }

        if ($rawPayload === '') {
            throw new \Exception('Please provide a JSON payload or upload a JSON file.');
        }

        $decoded = json_decode($rawPayload, true);
        if (!is_array($decoded)) {
            throw new \Exception('Invalid JSON payload: ' . json_last_error_msg());
        }

        if (!empty($decoded['information']) && is_array($decoded['information'])) {
            return array(
                'payload' => $decoded,
                'source_label' => $sourceLabel,
                'raw_payload' => $rawPayload,
            );
        }

        if (!empty($decoded['servers']) && is_array($decoded['servers'])) {
            return array(
                'payload' => array(
                    'id' => 'imported',
                    'date_generated' => date('Y-m-d H:i:s'),
                    'md5' => md5($rawPayload),
                    'information' => $decoded,
                ),
                'source_label' => $sourceLabel,
                'raw_payload' => $rawPayload,
            );
        }

        throw new \Exception('Invalid JSON payload: missing information.servers block.');
    }

    private function buildImportBundle(array $payload, string $sourceLabel): array
    {
        $dot3 = new Dot3('Dot3', 'renderImportedGraphs', array());
        $dot3->setDi($this->di);
        $dot3->before(array());
        $rendered = $dot3->renderImportedGraphs($payload);

        $bundle = array(
            'source_label' => $sourceLabel,
            'snapshot_id' => $payload['id'] ?? '',
            'date_generated' => $payload['date_generated'] ?? '',
            'date_inserted' => $payload['date_inserted'] ?? '',
            'md5' => $payload['md5'] ?? '',
            'graphs' => array(),
        );

        $servers = $payload['information']['servers'] ?? array();
        foreach (($rendered['graphs'] ?? array()) as $index => $graph) {
            $graph['svg'] = self::sanitizeImportedSvgMarkup((string) ($graph['svg'] ?? ''));
            $graph['label'] = self::buildImportedGraphLabel($graph['group'] ?? array(), $servers, (int) $index);
            $bundle['graphs'][(int) $index] = $graph;
        }

        if (empty($bundle['graphs'])) {
            throw new \Exception('No graph could be generated from this JSON payload.');
        }

        return $bundle;
    }

/**
 * Stores `$logger` for logger.
 *
 * @var mixed
 * @phpstan-var mixed
 * @psalm-var mixed
 */
    var $logger;

/**
 * Prepare cluster state through `before`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for before.
 * @phpstan-return void
 * @psalm-return void
 * @see self::before()
 * @example /fr/cluster/before
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function before($param)
    {
        $monolog       = new Logger("Cluter");
        $handler      = new StreamHandler(LOG_FILE, Logger::NOTICE);
        $handler->setFormatter(new LineFormatter(null, null, false, true));
        $monolog->pushHandler($handler);
        $this->logger = $monolog;
    }

/**
 * Handle cluster state through `svg`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for svg.
 * @phpstan-return void
 * @psalm-return void
 * @see self::svg()
 * @example /fr/cluster/svg
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function svg($param)
    {
        $id_mysql_server = $param[0] ?? "";
        
        if (empty($id_mysql_server)) {
            $id_mysql_server = 1;
            
            header("location: ".LINK."Cluster/svg/1/");
            exit;
        }

        $_GET['mysql_server']['id'] = $id_mysql_server;

        if (!empty($_GET['ajax']) && $_GET['ajax'] === "true"){
            $this->layout_name = false;
        }

        $db = Sgbd::sql(DB_DEFAULT, "SVG");

        $data = array();

        $sub_query = "select max(z.id) from dot3_cluster__mysql_server z where z.id_mysql_server=".$id_mysql_server;

        $sql = "SELECT c.svg FROM dot3_cluster__mysql_server a
        INNER JOIN dot3_cluster b ON a.id_dot3_cluster = b.id
        INNER JOIN dot3_graph c ON b.id_dot3_graph = c.id
        WHERE a.id_mysql_server = ".$id_mysql_server." AND a.id in (".$sub_query.");";

        //$this->logger->warning($sql);
        //$sql ="select max(id) from dot3_cluster x INNER JOIN dot3_cluster__mysql_server y ON x.id_dot3_cluster = y.id WHERE y.id=".$id_mysql_server."";

        //select max(x.id) from dot3_cluster x INNER JOIN dot3_cluster__mysql_server y ON y.id_dot3_cluster = x.id WHERE y.id=65;
        $res = $db->sql_query($sql);
        
        //$this->logger->warning($db->sql_num_rows($res));
        
        while ($ob = $db->sql_fetch_object($res)) {
            $this->di['js']->code_javascript('
            $(document).ready(function()
            {
                function refresh()
                {
                    var myURL = GLIAL_LINK+GLIAL_URL+"ajax:true";
                    $.ajax({
                        url: myURL,
                        type: "GET",
                        success: function(data) {
                            // Vérifier si les données ne sont pas vides
                            if (data.trim().length > 0) {
                                $("#graph").html(data);
                            } else {
                                console.log("Aucune donnée reçue.");
                            }
                        },
                        error: function(xhr, status, error) {
                            console.log("Erreur lors du chargement des données : ", error);
                        }
                    });
                }
    
                var intervalId = window.setInterval(function(){
                    // call your function here
                    refresh()  
                  }, 1200);
    
            })');
            $data['svg'] = $ob->svg;
        }

        $data['param'] = $param;
        $this->set('data',$data);
        $this->set('param', $param);
    }

    public function viewDot($param)
    {
        $id_mysql_server = (int) ($param[0] ?? 0);
        $isAjaxPreview = !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower((string) $_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

        if ($id_mysql_server <= 0) {
            header("location: " . LINK . "Cluster/svg/1/");
            exit;
        }

        if (!$isAjaxPreview) {
            $this->di['js']->addJavascript(array('bootstrap-select.min.js'));
        }

        $_GET['mysql_server']['id'] = $id_mysql_server;

        $db = Sgbd::sql(DB_DEFAULT, "SVG");
        $sub_query = "select max(z.id) from dot3_cluster__mysql_server z where z.id_mysql_server=".$id_mysql_server;
        $sql = "SELECT c.dot, c.svg, c.filename, c.md5, b.date_inserted
                FROM dot3_cluster__mysql_server a
                INNER JOIN dot3_cluster b ON a.id_dot3_cluster = b.id
                INNER JOIN dot3_graph c ON b.id_dot3_graph = c.id
                WHERE a.id_mysql_server = ".$id_mysql_server." AND a.id in (".$sub_query.")
                LIMIT 1;";

        $res = $db->sql_query($sql);
        $row = $db->sql_fetch_array($res, MYSQLI_ASSOC);

        if (empty($row)) {
            throw new \Exception("No DOT graph found for this server");
        }

        $sourceDot = (string) ($row['dot'] ?? '');
        $previewSvg = (string) ($row['svg'] ?? '');
        $renderError = '';
        $ajaxPreviewSvg = $previewSvg;
        $ajaxDownloadSvgHref = Graphviz::buildSvgDownloadDataUri($previewSvg);
        $previewKey = '';
        $importBundle = array();
        $selectedImportGraph = null;
        $selectedGraphIndex = null;
        $importPayload = '';
        $requestedImportGraphIndex = $_GET['selected_graph'] ?? null;

        if (isset($_GET['preview_key'])) {
            $previewKey = preg_replace('/[^a-f0-9]/', '', (string) $_GET['preview_key']) ?? '';
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['dot_preview']['preview_key'])) {
            $previewKey = preg_replace('/[^a-f0-9]/', '', (string) $_POST['dot_preview']['preview_key']) ?? '';
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['dot_import']['preview_key'])) {
            $previewKey = preg_replace('/[^a-f0-9]/', '', (string) $_POST['dot_import']['preview_key']) ?? '';
        }

        if ($previewKey === '') {
            $previewKey = bin2hex(random_bytes(16));
        }

        $previewState = self::loadPreviewState($previewKey);
        if (isset($previewState['import_payload']) && is_string($previewState['import_payload'])) {
            $importPayload = $previewState['import_payload'];
        }

        if ($previewKey !== '') {
            $importBundle = self::loadImportBundle($previewKey);

            if (!empty($importBundle['graphs'])) {
                if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['dot_preview']['selected_graph'])) {
                    $requestedImportGraphIndex = $_POST['dot_preview']['selected_graph'];
                } elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['dot_import']['selected_graph'])) {
                    $requestedImportGraphIndex = $_POST['dot_import']['selected_graph'];
                } elseif ($requestedImportGraphIndex === null && array_key_exists('selected_graph', $previewState)) {
                    $requestedImportGraphIndex = $previewState['selected_graph'];
                }

                $selectedGraphIndex = self::getSelectedImportGraphIndex(
                    $importBundle,
                    $id_mysql_server,
                    $requestedImportGraphIndex
                );

                if (!empty($importBundle['graphs'][$selectedGraphIndex])) {
                    $selectedImportGraph = $importBundle['graphs'][$selectedGraphIndex];
                    $selectedImportGraph['svg'] = self::sanitizeImportedSvgMarkup((string) ($selectedImportGraph['svg'] ?? ''));
                    $sourceDot = (string) ($selectedImportGraph['dot'] ?? '');
                    $previewSvg = (string) ($selectedImportGraph['svg'] ?? '');
                    $ajaxPreviewSvg = $previewSvg;
                    $ajaxDownloadSvgHref = $previewSvg !== '' ? Graphviz::buildSvgDownloadDataUri($previewSvg) : '';
                    $renderError = (string) ($selectedImportGraph['render_error'] ?? '');
                    $row['date_inserted'] = $importBundle['date_generated'] ?: ($importBundle['date_inserted'] ?? '');
                    $row['filename'] = $selectedImportGraph['filename'] ?? 'import-json';
                    $row['md5'] = $selectedImportGraph['md5'] ?? ($importBundle['md5'] ?? '');
                } else {
                    $sourceDot = '';
                    $previewSvg = '';
                    $ajaxPreviewSvg = '';
                    $ajaxDownloadSvgHref = '';
                }
            }
        }

        $previewStateSelectedGraph = $previewState['selected_graph'] ?? null;
        $canApplyPreviewState = !$isAjaxPreview && !empty($previewState);
        $hasExplicitImportedGraphSelection = $requestedImportGraphIndex !== null;

        if ($canApplyPreviewState && $hasExplicitImportedGraphSelection) {
            $canApplyPreviewState = (string) $previewStateSelectedGraph === (string) $selectedGraphIndex;
        } elseif ($canApplyPreviewState && !empty($selectedImportGraph)) {
            $canApplyPreviewState = (string) $previewStateSelectedGraph === (string) $selectedGraphIndex;
        }

        if ($canApplyPreviewState) {
            if (array_key_exists('dot', $previewState)) {
                $sourceDot = (string) $previewState['dot'];
            }

            if (array_key_exists('svg', $previewState)) {
                $previewSvg = (string) $previewState['svg'];
                $ajaxPreviewSvg = $previewSvg;
                $ajaxDownloadSvgHref = $previewSvg !== '' ? Graphviz::buildSvgDownloadDataUri($previewSvg) : '';
            }

            if (array_key_exists('render_error', $previewState)) {
                $renderError = (string) $previewState['render_error'];
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['dot_import'])) {
            try {
                $hasImportSource = !empty($_FILES['dot_import']['tmp_name']['file'])
                    || trim((string) ($_POST['dot_import']['payload'] ?? '')) !== '';

                if ($hasImportSource) {
                    $importRequest = self::extractImportedPayload();
                    $importBundle = $this->buildImportBundle($importRequest['payload'], $importRequest['source_label']);
                    self::saveImportBundle($previewKey, $importBundle);
                    self::deletePreviewState($previewKey);
                    $importPayload = $importRequest['raw_payload'];
                    $selectedGraphIndex = self::getSelectedImportGraphIndex($importBundle, $id_mysql_server);
                } else {
                    $importBundle = self::loadImportBundle($previewKey);
                    $selectedGraphIndex = self::getSelectedImportGraphIndex(
                        $importBundle,
                        $id_mysql_server,
                        $_POST['dot_import']['selected_graph'] ?? null
                    );
                }

                if ($selectedGraphIndex !== null && !empty($importBundle['graphs'][$selectedGraphIndex])) {
                    $selectedImportGraph = $importBundle['graphs'][$selectedGraphIndex];
                    $selectedImportGraph['svg'] = self::sanitizeImportedSvgMarkup((string) ($selectedImportGraph['svg'] ?? ''));
                    $sourceDot = (string) ($selectedImportGraph['dot'] ?? '');
                    $previewSvg = (string) ($selectedImportGraph['svg'] ?? '');
                    $ajaxPreviewSvg = $previewSvg;
                    $ajaxDownloadSvgHref = Graphviz::buildSvgDownloadDataUri($previewSvg);
                    $renderError = (string) ($selectedImportGraph['render_error'] ?? '');
                    $row['date_inserted'] = $importBundle['date_generated'] ?: ($importBundle['date_inserted'] ?? '');
                    $row['filename'] = $selectedImportGraph['filename'] ?? 'import-json';
                    $row['md5'] = $selectedImportGraph['md5'] ?? ($importBundle['md5'] ?? '');
                } elseif (empty($renderError)) {
                    $sourceDot = '';
                    $previewSvg = '';
                    $ajaxPreviewSvg = '';
                    $ajaxDownloadSvgHref = '';
                    $renderError = 'No imported graph matches this server. Select a graph explicitly.';
                }

                if (!$isAjaxPreview) {
                    if ($renderError !== '' || $selectedGraphIndex === null) {
                        self::savePreviewState($previewKey, array(
                            'dot' => $sourceDot,
                            'svg' => $previewSvg,
                            'render_error' => $renderError,
                            'import_payload' => $importPayload,
                            'selected_graph' => $selectedGraphIndex,
                        ));
                    } else {
                        self::deletePreviewState($previewKey);
                    }

                    header('Location: ' . self::buildViewDotUrl($id_mysql_server, $previewKey, $selectedGraphIndex));
                    exit;
                }
            } catch (\Throwable $e) {
                $renderError = $e->getMessage();
                $importPayload = trim((string) ($_POST['dot_import']['payload'] ?? ''));

                if (!$isAjaxPreview) {
                    self::savePreviewState($previewKey, array(
                        'dot' => $sourceDot,
                        'svg' => $previewSvg,
                        'render_error' => $renderError,
                        'import_payload' => $importPayload,
                        'selected_graph' => $selectedGraphIndex,
                    ));
                    header('Location: ' . self::buildViewDotUrl($id_mysql_server, $previewKey, $selectedGraphIndex));
                    exit;
                }
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['dot_preview']['dot'])) {
            $sourceDot = (string) $_POST['dot_preview']['dot'];
            $isImportedPreview = !empty($importBundle['graphs']) || $importPayload !== '';
            if (!empty($selectedImportGraph) && is_array($selectedImportGraph)) {
                $previewSvg = (string) ($selectedImportGraph['svg'] ?? $previewSvg);
                $ajaxPreviewSvg = $previewSvg;
                $ajaxDownloadSvgHref = Graphviz::buildSvgDownloadDataUri($previewSvg);
            }
            $reference = self::getPreviewReference($id_mysql_server, $previewKey, bin2hex(random_bytes(8)));
            $generatedFile = Graphviz::generateDot($reference, $sourceDot);
            $renderedSvg = false;
            $graphvizError = trim(Graphviz::getLastGenerateDotError());

            if (is_string($generatedFile) && $generatedFile !== '' && file_exists($generatedFile)) {
                $renderedSvg = @file_get_contents($generatedFile);
            }

            if ($graphvizError !== '' || !self::isSvgPreviewPayload($renderedSvg)) {
                $renderError = $graphvizError;
                if ($renderError === '') {
                    $renderError = 'Unable to render DOT as SVG.';
                }
                $ajaxPreviewSvg = '';
                $ajaxDownloadSvgHref = '';
            } else {
                if ($isImportedPreview) {
                    $renderedSvg = self::sanitizeImportedSvgMarkup((string) $renderedSvg);
                }
                $previewSvg = $renderedSvg;
                $ajaxPreviewSvg = $renderedSvg;
                $ajaxDownloadSvgHref = Graphviz::buildSvgDownloadDataUri($renderedSvg);
            }

            self::cleanupPreviewArtifacts($reference);

            if (!$isAjaxPreview) {
                self::savePreviewState($previewKey, array(
                    'dot' => $sourceDot,
                    'svg' => $previewSvg,
                    'render_error' => $renderError,
                    'import_payload' => $importPayload,
                    'selected_graph' => $selectedGraphIndex,
                ));
                header('Location: ' . self::buildViewDotUrl($id_mysql_server, $previewKey, $selectedGraphIndex));
                exit;
            }
        }

        $data = [
            'param' => [$id_mysql_server],
            'id_mysql_server' => $id_mysql_server,
            'date_inserted' => $row['date_inserted'] ?? '',
            'filename' => $row['filename'] ?? '',
            'md5' => $row['md5'] ?? '',
            'dot' => self::formatDotSource($sourceDot),
            'dot_length' => strlen($sourceDot),
            'svg' => $previewSvg,
            'render_error' => $renderError,
            'preview_key' => $previewKey,
            'import_bundle' => $importBundle,
            'import_selected_graph' => $selectedImportGraph,
            'import_payload' => $importPayload,
            'import_selected_graph_index' => $selectedGraphIndex ?? null,
        ];

        if ($isAjaxPreview) {
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode([
                'svg' => $ajaxPreviewSvg,
                'download_svg_href' => $ajaxDownloadSvgHref,
                'render_error' => self::safeJsonString($renderError),
                'dot_length' => strlen($sourceDot),
                'preview_key' => $previewKey,
            ], JSON_INVALID_UTF8_SUBSTITUTE);
            exit;
        }

        $this->set('data', $data);
        $this->set('param', [$id_mysql_server]);
    }

    private static function isSvgPreviewPayload($payload)
    {
        if (!is_string($payload) || $payload === '') {
            return false;
        }

        $trimmed = ltrim($payload);
        $pngSignature = "\x89PNG\r\n\x1a\n";
        if (strncmp($trimmed, $pngSignature, 8) === 0) {
            return false;
        }

        return stripos($trimmed, '<svg') !== false;
    }

    private static function getPreviewReference(int $idMysqlServer, string $previewKey, string $requestNonce): string
    {
        return 'view-dot-preview-' . $idMysqlServer . '-' . $previewKey . '-' . $requestNonce;
    }

    private static function cleanupPreviewArtifacts(string $reference): void
    {
        foreach (['dot', 'svg', 'png'] as $extension) {
            $file = TMP . 'dot/' . $reference . '.' . $extension;
            if (file_exists($file)) {
                @unlink($file);
            }
        }
    }

    private static function safeJsonString($value): string
    {
        if (!is_string($value)) {
            $value = (string) $value;
        }

        return mb_convert_encoding($value, 'UTF-8', 'UTF-8');
    }

    /*

    Enterprise
    */
   public function replay($param)
   {
        $id_mysql_server = $param[0];

        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT date(min(date_inserted)) as date_min, date(max(date_inserted)) as date_max 
        FROM dot3_cluster__mysql_server where id_mysql_server = ".$id_mysql_server.";";

        $res = $db->sql_query($sql);

        $data = [];

        $data['param'] = $param;
        $data['id_mysql_server'] = $id_mysql_server;

        while($ob = $db->sql_fetch_object($res))
        {
            $data['date_min'] = $ob->date_min;
            $data['date_max'] = $ob->date_max;
        }

        $data['list_min'] = [];

        $sql2 = "SELECT DATE_ADD('{$data['date_min']}', INTERVAL seq DAY) AS generated_date
        FROM (
        SELECT @row := @row + 1 AS seq
        FROM (
            SELECT 0 UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4
            UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9
        ) t1,
        (
            SELECT 0 UNION ALL SELECT 10 UNION ALL SELECT 20 UNION ALL SELECT 30 UNION ALL SELECT 40
            UNION ALL SELECT 50 UNION ALL SELECT 60
        ) t2,
        (SELECT @row := -1) AS init
        ) AS seq_gen
        WHERE DATE_ADD('{$data['date_min']}', INTERVAL seq DAY) <= '{$data['date_max']}';";

        Debug::debug($sql2);

        $res2 = $db->sql_query($sql2);
        while($ob2 = $db->sql_fetch_object($res2))
        {

            $tmp            = [];
            $tmp['id']      = $ob2->generated_date;
            $tmp['libelle']   = $ob2->generated_date;

            $data['list_min'][] = $tmp;
        }

        $data['options'] = array("data-style" => "btn-info","data-width" => "auto", "all_selectable"=> "false");


        $this->set('data', $data);

   }

/**
 * Handle `history`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for history.
 * @phpstan-return void
 * @psalm-return void
 * @example history(...);
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
   public function history($param)
   {

        $db = Sgbd::sql(DB_DEFAULT);

        $id_mysql_server = $param[0];

        if ($_SERVER['REQUEST_METHOD'] == "POST") {

            debug($_POST); 
            header("location: ".LINK."Cluster/history/".$id_mysql_server."/".$_POST['dot3_cluster__mysql_server']['date_min']."/".$_POST['dot3_cluster__mysql_server']['date_max']);
            exit;
        }

        $date_min = $param[1];
        $date_max = $param[2];

        $sub_query = "SELECT z.id from dot3_cluster__mysql_server z where z.id_mysql_server={$id_mysql_server} 
        AND date_inserted BETWEEN '".$date_min."' AND '".$date_max."'";

        $sql = "SELECT c.svg,c.date_inserted FROM dot3_cluster__mysql_server a
        INNER JOIN dot3_cluster b ON a.id_dot3_cluster = b.id
        INNER JOIN dot3_graph c ON b.id_dot3_graph = c.id
        WHERE a.id_mysql_server = {$id_mysql_server} AND a.id in ({$sub_query}) 
        GROUP BY c.date_inserted,c.svg";

        $res = $db->sql_query($sql);
        
        //$this->logger->warning($db->sql_num_rows($res));
        $data = [];
        $data['param'] = $param;

        $i = 0;
        while ($ob = $db->sql_fetch_object($res)) {

            //remove debug
            if (stripos($ob->svg, 'Debug') !== false) {
                continue;
            }

            $data['svg'][$i]['svg'] = $ob->svg;
            $data['svg'][$i]['date_inserted'] = $ob->date_inserted;
            $i++;
        }

        $this->set('data', $data);
   }
}
