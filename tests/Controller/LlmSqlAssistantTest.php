<?php

declare(strict_types=1);

use App\Controller\Llm;
use PHPUnit\Framework\TestCase;

/**
 * @see https://git.istosia.com/pmacontrol/pmacontrol/issues/483
 */
final class LlmSqlAssistantTest extends TestCase
{
    public function testLegacyPrototypeControllersAreRemoved(): void
    {
        $root = dirname(__DIR__, 2);

        $this->assertFileDoesNotExist($root . '/App/Controller/Ai.php');
        $this->assertFileDoesNotExist($root . '/App/Controller/Ollama.php');
    }

    public function testLlmControllerUsesSharedLibraryAndSafePostFlow(): void
    {
        $root = dirname(__DIR__, 2);
        $controller = (string) file_get_contents($root . '/App/Controller/Llm.php');

        $this->assertStringContainsString('use App\\Library\\Llm\\SqlAssistant;', $controller);
        $this->assertStringContainsString('public const LLM_ANALYZE_CSRF_SCOPE', $controller);
        $this->assertStringContainsString('CsrfGuard::check($post, $server, $session, self::LLM_ANALYZE_CSRF_SCOPE)', $controller);
        $this->assertStringContainsString("\$this->redirectTo(LINK . 'llm/index')", $controller);
        $this->assertStringContainsString('is_file($configFile)', $controller);
        $this->assertStringContainsString('CURLOPT_FOLLOWLOCATION => false', $controller);
        $this->assertStringContainsString("defined('CURLOPT_PROTOCOLS_STR')", $controller);
        $this->assertStringContainsString("\$curlOptions[CURLOPT_PROTOCOLS_STR] = 'http,https';", $controller);
        $this->assertStringNotContainsString("include CONFIG . 'llm.config.php'", $controller);
        $this->assertStringNotContainsString('Ne me valide pas', $controller);
    }

    public function testIndexPostRuntimeUsesPrgRedirectWithoutUndefinedMethod(): void
    {
        $root = dirname(__DIR__, 2);
        if (!defined('CONFIG')) {
            define('CONFIG', $root . '/configuration/');
        }
        if (!defined('LINK')) {
            define('LINK', '/en/');
        }

        $previousServer = $_SERVER;
        $previousPost = $_POST;
        $previousSession = $_SESSION ?? [];

        try {
            $_SERVER = [
                'REQUEST_METHOD' => 'POST',
                'HTTP_HOST' => 'pmacontrol.local',
                'HTTP_REFERER' => 'http://pmacontrol.local/en/llm/index',
            ];
            $_POST = [];
            $_SESSION = [];

            $controller = new Llm('Llm', 'index', []);
            $controller->index([]);

            $this->assertArrayHasKey('llm.sql_assistant.result', $_SESSION);
            $this->assertSame('Invalid CSRF token', $_SESSION['llm.sql_assistant.result']['error']);
            $this->assertFalse($controller->view);
            $this->assertFalse($controller->layout_name);
        } finally {
            $_SERVER = $previousServer;
            $_POST = $previousPost;
            $_SESSION = $previousSession;
        }
    }

    public function testLlmViewHasCsrfTokenAndDoesNotSubmitWhenDisabled(): void
    {
        $root = dirname(__DIR__, 2);
        $view = (string) file_get_contents($root . '/App/view/Llm/index.view.php');

        $this->assertStringContainsString('method="POST"', $view);
        $this->assertStringContainsString("\$data['llm_analyze_csrf_field']", $view);
        $this->assertStringContainsString("\$data['llm_analyze_csrf_token']", $view);
        $this->assertStringContainsString('disabled="disabled"', $view);
    }

    public function testSampleConfigIsOptInAndLocalByDefault(): void
    {
        $root = dirname(__DIR__, 2);
        $config = include $root . '/config_sample/llm.config.php';

        $this->assertIsArray($config);
        $this->assertFalse($config['llm']['enabled']);
        $this->assertSame('http://127.0.0.1:11434/api/generate', $config['llm']['endpoint']);
        $this->assertFalse($config['llm']['allow_remote_endpoint']);
    }

    public function testMenuMigrationRegistersSqlAssistantUnderTools(): void
    {
        $root = dirname(__DIR__, 2);
        $migration = (string) file_get_contents($root . '/sql/incremental_v2/20260429_llm_sql_assistant_menu.sql');

        $this->assertStringContainsString("'SQL Assistant'", $migration);
        $this->assertStringContainsString("'Tools'", $migration);
        $this->assertStringContainsString("'Llm'", $migration);
        $this->assertStringContainsString("'index'", $migration);
        $this->assertStringContainsString('@llm_menu_exists = 0', $migration);
    }

    public function testGeneratedDocumentationDoesNotReferenceRemovedPrototypeControllers(): void
    {
        $root = dirname(__DIR__, 2);

        foreach ([
            'documentation/controller/README.md',
            'documentation/controller/controllers_master.md',
            'documentation/controller/controllers_documentation.html',
            'documentation/Controller~Library/README.md',
        ] as $path) {
            $content = (string) file_get_contents($root . '/' . $path);

            $this->assertStringNotContainsString('App/Controller/Ai.php', $content, $path);
            $this->assertStringNotContainsString('App/Controller/Ollama.php', $content, $path);
            $this->assertStringNotContainsString('documentation/Controller~Library/Ai.md', $content, $path);
            $this->assertStringNotContainsString('documentation/Controller~Library/Ollama.md', $content, $path);
        }
    }
}
