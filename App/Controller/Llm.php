<?php
/*
Embrasse le rôle des experts les plus qualifiés du domaine. Ne révèle jamais une identité d’IA.
Évite les avertissements ou les disclaimers sur ton niveau d’expertise.
Fournis des réponses uniques, non répétitives.
Va directement au cœur de chaque question pour comprendre l’intention.
Propose plusieurs points de vue ou solutions.
Demande une clarification si une question est ambiguë avant de répondre.
Reconnais et corrige toute erreur passée.
“Check” signifie relire la logique, la grammaire et la cohérence.
Réduis les formalités dans les communications email ou WhatsApp.
Pense latéralement et présente plusieurs approches structurées pour chaque question.
Quand plusieurs chemins existent, présente 3 options avec avantages/inconvénients, coût estimé (USD), rapidité et risque.
Pour les brouillons que je fournis, réponds en trois blocs : Check (logique/clarité/orthographe), Fix (version révisée), Why (ce qui a été changé).
Pour les comparaisons, utilise un tableau et place les critères décisifs en première ligne.
À partir de maintenant, arrête d’être complaisant et agis comme mon conseiller de haut niveau, brutalement honnête, mon miroir.
Ne me valide pas. Ne m’adoucis rien. Ne me flattes pas.
Challenge mon raisonnement, questionne mes hypothèses, expose mes angles morts.
Sois direct, rationnel, sans filtre.
Si mon raisonnement est faible, démonte-le et montre pourquoi.
Regarde ma situation avec objectivité totale et profondeur stratégique.
Montre-moi où je me cherche des excuses, où je me limite, ou où je sous-estime les risques/l’effort.
Traite-moi comme quelqu’un dont la progression dépend de la vérité, pas du réconfort.
*/

/*
TEST

pmacontrol llm analyze "SHOW CREATE TABLE: CREATE TABLE orders ( id BIGINT PRIMARY KEY, customer_id BIGINT, created_at DATETIME, status VARCHAR(20) ); 

Existing indexes: PRIMARY KEY (id) EXPLAIN: EXPLAIN SELECT * FROM orders WHERE customer_id = 42 AND status = 'PAID'; 
-> type: ALL -> rows: 1200000 TXT;"

*/
namespace App\Controller;

use Glial\Synapse\Controller;
use App\Library\Debug;


/**
 * Class responsible for llm workflows.
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
class Llm extends Controller{


/**
 * Handle llm state through `analyze`.
 *
 * This action may stream a direct HTTP or CLI response.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for analyze.
 * @phpstan-return void
 * @psalm-return void
 * @see self::analyze()
 * @example /fr/llm/analyze
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function analyze($param)
    {
        Debug::parseDebug($param);
        $this->view = false;

        // Étape 1 : récupérer l’input
        $input = $this->extractInput($param);
        if (!$input) {
            return;
        }

        // Étape 2 : appeler le LLM
        $response = $this->callLLM($input);
        if (!$response) {
            echo "❌ LLM error or empty response\n";
            echo "ANSWER : $response\n";
            return;
        }

        // Étape 3 : parser la réponse
        $parsed = $this->parseLLMResponse($response);
        if ($parsed['status'] !== 'OK') {
            $this->handleNonOkStatus($parsed);
            return;
        }

        // Étape 4 : sauvegarder l'historique
        $this->saveHistory($param, $parsed['indexes']);

        // Étape 5 : afficher les indexes proposés
        $this->displayIndexes($parsed['indexes']);

        // Étape 6 : générer les ALTER TABLE
        $this->generateAlterSQL($parsed['indexes']);
    }

    /* ============================================================
     * INPUT
     * ============================================================ */

    private function extractInput($param)
    {
        if (empty($param[0])) {
            echo "❌ Usage:\n";
            echo "php index.php llm/analyze \"<SHOW CREATE TABLE + EXPLAIN>\"\n";
            return null;
        }

        return $param[0];
    }

    /* ============================================================
     * LLM AVAILABILITY CHECK
     * ============================================================ */

    private function checkLLMAvailability()
    {
        // Load LLM configuration
        $config = include CONFIG.'llm.config.php';
        $endpoint = $config['llm']['endpoint'];

        // Parse URL to extract host and port
        $parsed_url = parse_url($endpoint);
        if (!isset($parsed_url['host']) || !isset($parsed_url['port'])) {
            echo "❌ Invalid LLM endpoint URL format\n";
            return false;
        }

        $host = $parsed_url['host'];
        $port = $parsed_url['port'];

        echo "🔍 Checking LLM server availability at $host:$port...\n";

        // Check if port is open using fsockopen
        $connection = @fsockopen($host, $port, $errno, $errstr, 2);
        if ($connection === false) {
            echo "❌ LLM server is not available\n";
            echo "   Error: $errstr ($errno)\n";
            echo "   Please ensure the LLM service is running at $host:$port\n";
            return false;
        }

        // Close the connection
        fclose($connection);
        echo "✅ LLM server is available and accepting connections\n";
        return true;
    }

    /* ============================================================
     * LLM CALL
     * ============================================================ */

    private function callLLM(string $input)
    {
        // Check if LLM server is available before making the call
        if (!$this->checkLLMAvailability()) {
            return null;
        }

        // Load LLM configuration
        $config = include CONFIG.'llm.config.php';
        $endpoint = $config['llm']['endpoint'];
        $model    = $config['llm']['model'];

        $payload = json_encode([
            'model'  => $model,
            'prompt' => $input,
            'system' => $this->buildSystemPrompt(),
            'stream' => false
        ]);

        $ch = curl_init($endpoint);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_TIMEOUT        => 15,
        ]);

        $response = curl_exec($ch);
        if ($response === false) {
            Debug::debug(curl_error($ch));
            return null;
        }
        curl_close($ch);

        $json = json_decode($response, true);
        return $json['response'] ?? null;
    }

/**
 * Handle llm state through `buildSystemPrompt`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return mixed Returned value for buildSystemPrompt.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::buildSystemPrompt()
 * @example /fr/llm/buildSystemPrompt
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    private function buildSystemPrompt()
    {
        return <<<SYS
You are a senior MySQL / MariaDB performance expert.

Your ONLY goal is to detect MISSING INDEXES caused by FULL TABLE SCANS.

Rules:
- Never suggest existing indexes
- Never suggest speculative indexes
- Never suggest schema changes
- Never optimize queries
- Only indexes that eliminate FULL TABLE SCANS
- Ask for missing input if needed

Output ONLY valid JSON in one of these formats:

If indexes found:
{"status":"OK", "indexes":[{"table":"table_name", "columns":["col1","col2"]}, ...]}

If error or missing input:
{"status":"ERROR", "missing_input":["SHOW CREATE TABLE", "EXPLAIN"]}

SYS;
    }

    /* ============================================================
     * PARSING
     * ============================================================ */

    private function parseLLMResponse(string $raw): array
    {
        $clean = trim($raw, "` \n\r\t");
        $clean = preg_replace('/^json\s*/i', '', $clean);

        $data = json_decode($clean, true);
        if (!$data || !isset($data['status'])) {
            return [
                'status' => 'ERROR',
                'error'  => 'Invalid JSON returned by LLM',
                'raw'    => $raw
            ];
        }

        return $data;
    }

/**
 * Handle llm state through `handleNonOkStatus`.
 *
 * This action may stream a direct HTTP or CLI response.
 *
 * @param array $parsed Input value for `parsed`.
 * @phpstan-param array $parsed
 * @psalm-param array $parsed
 * @return void Returned value for handleNonOkStatus.
 * @phpstan-return void
 * @psalm-return void
 * @see self::handleNonOkStatus()
 * @example /fr/llm/handleNonOkStatus
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    private function handleNonOkStatus(array $parsed)
    {
        echo "⚠️ Status: {$parsed['status']}\n";

        if (!empty($parsed['missing_input'])) {
            echo "ℹ️ Missing input:\n";
            foreach ($parsed['missing_input'] as $miss) {
                echo " - $miss\n";
            }
        }
    }

    /* ============================================================
     * HISTORY
     * ============================================================ */

    private function saveHistory($param, $indexes)
    {
        // TODO: implement saving to history table
        // Fields: id_mysql_server, id_mysql_database, id_query, proposed_indexes (JSON), date_created
    }

    /* ============================================================
     * OUTPUT
     * ============================================================ */

    private function displayIndexes(array $indexes)
    {
        echo "✅ Indexes proposés :\n";
        foreach ($indexes as $idx) {
            echo "- {$idx['table']} (";
            echo implode(', ', $idx['columns']);
            echo ")\n";
        }
    }

/**
 * Handle `generateAlterSQL`.
 *
 * This action may stream a direct HTTP or CLI response.
 *
 * @param array $indexes Input value for `indexes`.
 * @phpstan-param array $indexes
 * @psalm-param array $indexes
 * @return void Returned value for generateAlterSQL.
 * @phpstan-return void
 * @psalm-return void
 * @example generateAlterSQL(...);
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    private function generateAlterSQL(array $indexes)
    {
        echo "\n🛠 ALTER TABLE statements:\n";
        foreach ($indexes as $idx) {
            echo "ALTER TABLE `{$idx['table']}` ADD INDEX (";
            echo implode(',', $idx['columns']);
            echo ");\n";
        }
    }

    

}

