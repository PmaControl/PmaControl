<?php

declare(strict_types=1);

namespace App\Controller;

use App\Library\Chiffrement;
use Glial\Sgbd\Sgbd;
use Glial\Synapse\Controller;

/**
 * REST configuration controller for UI-managed resources.
 *
 * The controller exposes CRUD endpoints for entities that are editable from the
 * legacy web interface while keeping validation logic centralized in static
 * helpers that remain easy to unit test.
 *
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
class Api extends Controller
{
    /**
     * API resource metadata indexed by slug.
     *
     * @var array<string,array{
     *     table:string,
     *     primaryKey:string,
     *     fields:list<string>,
     *     requiredCreate:list<string>,
     *     integerFields:list<string>,
     *     booleanFields:list<string>,
     *     encryptedFields:list<string>,
     *     deleteMode:string,
     *     defaultValues:array<string,mixed>
     * }>
     */
    private const RESOURCE_MAP = [
        'tags' => [
            'table' => 'tag',
            'primaryKey' => 'id',
            'fields' => ['id', 'name', 'color', 'background'],
            'requiredCreate' => ['name', 'color', 'background'],
            'integerFields' => ['id'],
            'booleanFields' => [],
            'encryptedFields' => [],
            'deleteMode' => 'hard',
            'defaultValues' => [
                'color' => '#ffffff',
                'background' => '#337ab7',
            ],
        ],
        'clients' => [
            'table' => 'client',
            'primaryKey' => 'id',
            'fields' => ['id', 'libelle', 'logo', 'date', 'is_monitored'],
            'requiredCreate' => ['libelle'],
            'integerFields' => ['id', 'is_monitored'],
            'booleanFields' => ['is_monitored'],
            'encryptedFields' => [],
            'deleteMode' => 'guarded_client',
            'defaultValues' => [
                'logo' => '',
                'date' => '__NOW__',
                'is_monitored' => 1,
            ],
        ],
        'environments' => [
            'table' => 'environment',
            'primaryKey' => 'id',
            'fields' => ['id', 'libelle', 'key', 'class', 'letter'],
            'requiredCreate' => ['libelle', 'key', 'class', 'letter'],
            'integerFields' => ['id'],
            'booleanFields' => [],
            'encryptedFields' => [],
            'deleteMode' => 'guarded_environment',
            'defaultValues' => [],
        ],
        'aliases' => [
            'table' => 'alias_dns',
            'primaryKey' => 'id',
            'fields' => ['id', 'id_mysql_server', 'dns', 'port'],
            'requiredCreate' => ['id_mysql_server', 'dns', 'port'],
            'integerFields' => ['id', 'id_mysql_server', 'port'],
            'booleanFields' => [],
            'encryptedFields' => [],
            'deleteMode' => 'hard',
            'defaultValues' => [],
        ],
        'storage-areas' => [
            'table' => 'backup_storage_area',
            'primaryKey' => 'id',
            'fields' => [
                'id',
                'id_ssh_key',
                'id_geolocalisation_city',
                'id_geolocalisation_country',
                'ip',
                'port',
                'path',
                'libelle',
            ],
            'requiredCreate' => [
                'id_ssh_key',
                'id_geolocalisation_city',
                'id_geolocalisation_country',
                'ip',
                'port',
                'path',
                'libelle',
            ],
            'integerFields' => ['id', 'id_ssh_key', 'id_geolocalisation_city', 'id_geolocalisation_country', 'port'],
            'booleanFields' => [],
            'encryptedFields' => [],
            'deleteMode' => 'hard',
            'defaultValues' => [
                'port' => 22,
            ],
        ],
        'servers' => [
            'table' => 'mysql_server',
            'primaryKey' => 'id',
            'fields' => [
                'id',
                'id_client',
                'id_environment',
                'name',
                'display_name',
                'ip',
                'hostname',
                'login',
                'passwd',
                'database',
                'is_password_crypted',
                'is_ssl',
                'port',
                'ssh_nat',
                'ssh_port',
                'ssh_login',
                'is_sudo',
                'is_root',
                'is_monitored',
                'is_proxy',
                'is_vip',
                'is_acknowledged',
            ],
            'requiredCreate' => ['id_client', 'id_environment', 'name', 'display_name', 'ip', 'login', 'passwd', 'database', 'port'],
            'integerFields' => [
                'id',
                'id_client',
                'id_environment',
                'is_password_crypted',
                'is_ssl',
                'port',
                'ssh_port',
                'is_sudo',
                'is_root',
                'is_monitored',
                'is_proxy',
                'is_vip',
                'is_acknowledged',
            ],
            'booleanFields' => ['is_password_crypted', 'is_ssl', 'is_sudo', 'is_root', 'is_monitored', 'is_proxy', 'is_vip'],
            'encryptedFields' => ['passwd'],
            'deleteMode' => 'soft_server',
            'defaultValues' => [
                'hostname' => '',
                'database' => 'mysql',
                'is_password_crypted' => 1,
                'is_ssl' => 0,
                'port' => 3306,
                'ssh_nat' => '',
                'ssh_port' => 22,
                'ssh_login' => '',
                'is_sudo' => 0,
                'is_root' => 1,
                'is_monitored' => 1,
                'is_proxy' => 0,
                'is_vip' => 0,
                'is_acknowledged' => 0,
            ],
        ],
        'ssh-keys' => [
            'table' => 'ssh_key',
            'primaryKey' => 'id',
            'fields' => [
                'id',
                'name',
                'added_on',
                'fingerprint',
                'user',
                'public_key',
                'private_key',
                'type',
                'bit',
                'comment',
            ],
            'requiredCreate' => ['name', 'added_on', 'fingerprint', 'user', 'public_key', 'private_key', 'type', 'bit', 'comment'],
            'integerFields' => ['id', 'bit'],
            'booleanFields' => [],
            'encryptedFields' => ['public_key', 'private_key'],
            'deleteMode' => 'hard',
            'defaultValues' => [],
        ],
    ];

    /**
     * Dispatch REST operations for documented configuration resources.
     *
     * @param array<int,string> $param Router parameters where the first item is the resource slug.
     *
     * @return void
     *
     * @throws \InvalidArgumentException When the resource slug is unknown or the payload is invalid.
     * @throws \RuntimeException When the database operation cannot be completed.
     *
     * @see self::openApi()
     * @example /fr/api/config/tags
     *
     * @category PmaControl
     * @package App
     * @subpackage Controller
     * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
     * @license GPL-3.0
     * @since 5.0
     * @version 1.0
     */
    public function config(array $param): void
    {
        $this->view = false;
        $this->layout_name = false;

        $resource = $param[0] ?? '';
        $id = isset($param[1]) ? (int) $param[1] : null;
        $method = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));

        $definition = self::getResourceDefinition($resource);
        $db = Sgbd::sql(DB_DEFAULT);

        try {
            $response = match ($method) {
                'GET' => $this->handleGet($db, $definition, $id),
                'POST' => $this->handleCreate($db, $resource, $definition, self::readJsonInput()),
                'PUT', 'PATCH' => $this->handleUpdate($db, $resource, $definition, $id, self::readJsonInput()),
                'DELETE' => $this->handleDelete($db, $definition, $id),
                default => [
                    'status' => 405,
                    'data' => [
                        'error' => 'Method not allowed',
                        'allowed' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'],
                    ],
                ],
            };
        } catch (\Throwable $exception) {
            $response = [
                'status' => 400,
                'data' => [
                    'error' => $exception->getMessage(),
                ],
            ];
        }

        $this->respondJson($response['data'], $response['status']);
    }

    /**
     * Return the OpenAPI-like documentation payload used by Markdown docs and tests.
     *
     * @param array<int,string> $param Unused router parameters.
     *
     * @return void
     *
     * @see self::getOpenApiDocument()
     * @example /fr/api/openApi
     *
     * @category PmaControl
     * @package App
     * @subpackage Controller
     * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
     * @license GPL-3.0
     * @since 5.0
     * @version 1.0
     */
    public function openApi($param = []): void
    {
        if (!is_array($param)) {
            $param = $param === '' ? [] : [$param];
        }

        $this->view = false;
        $this->layout_name = false;
        $this->respondJson(self::getOpenApiDocument(), 200);
    }

    /**
     * Return metadata for all supported REST resources.
     *
     * @return array<string,array{
     *     table:string,
     *     primaryKey:string,
     *     fields:list<string>,
     *     requiredCreate:list<string>,
     *     integerFields:list<string>,
     *     booleanFields:list<string>,
     *     encryptedFields:list<string>,
     *     deleteMode:string,
     *     defaultValues:array<string,mixed>
     * }>
     */
    public static function getResourceMap(): array
    {
        return self::RESOURCE_MAP;
    }

    /**
     * Resolve a resource definition or fail with a precise error.
     *
     * @param string $resource Resource slug used in the route.
     *
     * @return array{
     *     table:string,
     *     primaryKey:string,
     *     fields:list<string>,
     *     requiredCreate:list<string>,
     *     integerFields:list<string>,
     *     booleanFields:list<string>,
     *     encryptedFields:list<string>,
     *     deleteMode:string,
     *     defaultValues:array<string,mixed>
     * }
     */
    public static function getResourceDefinition(string $resource): array
    {
        if (!isset(self::RESOURCE_MAP[$resource])) {
            throw new \InvalidArgumentException('Unknown API resource: '.$resource);
        }

        return self::RESOURCE_MAP[$resource];
    }

    /**
     * Normalize and validate a JSON payload against the resource schema.
     *
     * @param string $resource Resource slug.
     * @param array<string,mixed> $payload Raw request payload.
     * @param bool $isUpdate Flag telling whether required fields may be omitted.
     *
     * @return array<string,mixed>
     */
    public static function normalizePayload(string $resource, array $payload, bool $isUpdate = false): array
    {
        $definition = self::getResourceDefinition($resource);
        $normalized = [];

        foreach ($definition['defaultValues'] as $field => $value) {
            if (!$isUpdate) {
                $normalized[$field] = $value === '__NOW__' ? date('Y-m-d H:i:s') : $value;
            }
        }

        foreach ($payload as $field => $value) {
            if (!in_array($field, $definition['fields'], true)) {
                continue;
            }

            if (in_array($field, $definition['booleanFields'], true)) {
                $normalized[$field] = self::normalizeBoolean($value);
                continue;
            }

            if (in_array($field, $definition['integerFields'], true)) {
                $normalized[$field] = (int) $value;
                continue;
            }

            if (in_array($field, $definition['encryptedFields'], true)) {
                $normalized[$field] = Chiffrement::encrypt((string) $value);
                continue;
            }

            $normalized[$field] = is_string($value) ? trim($value) : $value;
        }

        if (!$isUpdate) {
            foreach ($definition['requiredCreate'] as $requiredField) {
                if (!array_key_exists($requiredField, $normalized) || $normalized[$requiredField] === '') {
                    throw new \InvalidArgumentException('Missing required field: '.$requiredField);
                }
            }
        }

        return $normalized;
    }

    /**
     * Build the OpenAPI-like document consumed by the Markdown export.
     *
     * @return array<string,mixed>
     */
    public static function getOpenApiDocument(): array
    {
        $paths = [];

        foreach (self::RESOURCE_MAP as $slug => $definition) {
            $paths['/fr/api/config/'.$slug] = [
                'get' => [
                    'summary' => 'List '.$slug,
                    'response' => [
                        'resource' => $slug,
                        'table' => $definition['table'],
                    ],
                ],
                'post' => [
                    'summary' => 'Create '.$slug,
                    'requestBody' => [
                        'required' => $definition['requiredCreate'],
                        'fields' => $definition['fields'],
                    ],
                ],
            ];

            $paths['/fr/api/config/'.$slug.'/{id}'] = [
                'get' => [
                    'summary' => 'Read a single '.$slug.' item',
                ],
                'put' => [
                    'summary' => 'Update a '.$slug.' item',
                ],
                'delete' => [
                    'summary' => 'Delete a '.$slug.' item',
                    'mode' => $definition['deleteMode'],
                ],
            ];
        }

        return [
            'openapi' => '3.1.0',
            'info' => [
                'title' => 'PmaControl Configuration API',
                'version' => '1.0.0',
            ],
            'paths' => $paths,
        ];
    }

    /**
     * Convert common boolean payload variants to database-ready integers.
     *
     * @param mixed $value Incoming JSON value.
     *
     * @return int
     */
    public static function normalizeBoolean(mixed $value): int
    {
        if (is_bool($value)) {
            return $value ? 1 : 0;
        }

        if (is_numeric($value)) {
            return ((int) $value) === 1 ? 1 : 0;
        }

        return in_array(strtolower((string) $value), ['1', 'true', 'yes', 'on'], true) ? 1 : 0;
    }

    /**
     * @param object $db Database adapter exposing the legacy SQL API.
     * @param array{table:string,primaryKey:string,fields:list<string>} $definition Resource definition.
     * @param int|null $id Optional identifier.
     *
     * @return array{status:int,data:array<string,mixed>}
     */
    private function handleGet(object $db, array $definition, ?int $id): array
    {
        $fields = implode(', ', $definition['fields']);
        $sql = 'SELECT '.$fields.' FROM `'.$definition['table'].'`';

        if ($id !== null) {
            $sql .= ' WHERE `'.$definition['primaryKey'].'` = '.(int) $id.' LIMIT 1';
            $res = $db->sql_query($sql);
            $row = $db->sql_fetch_array($res, MYSQLI_ASSOC) ?: null;

            return [
                'status' => $row === null ? 404 : 200,
                'data' => [
                    'item' => $row,
                ],
            ];
        }

        $res = $db->sql_query($sql.' ORDER BY `'.$definition['primaryKey'].'` DESC');
        $items = [];
        while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $items[] = $row;
        }

        return [
            'status' => 200,
            'data' => [
                'items' => $items,
            ],
        ];
    }

    /**
     * @param object $db Database adapter exposing the legacy SQL API.
     * @param string $resource Resource slug.
     * @param array{table:string,primaryKey:string} $definition Resource definition.
     * @param array<string,mixed> $payload Request payload.
     *
     * @return array{status:int,data:array<string,mixed>}
     */
    private function handleCreate(object $db, string $resource, array $definition, array $payload): array
    {
        $normalized = self::normalizePayload($resource, $payload, false);
        $save = [$definition['table'] => $normalized];
        $id = $db->sql_save($save);

        if (!$id) {
            throw new \RuntimeException('Create failed for resource '.$resource);
        }

        return [
            'status' => 201,
            'data' => [
                'id' => (int) $id,
                'resource' => $resource,
            ],
        ];
    }

    /**
     * @param object $db Database adapter exposing the legacy SQL API.
     * @param string $resource Resource slug.
     * @param array{table:string,primaryKey:string} $definition Resource definition.
     * @param int|null $id Route identifier.
     * @param array<string,mixed> $payload Request payload.
     *
     * @return array{status:int,data:array<string,mixed>}
     */
    private function handleUpdate(object $db, string $resource, array $definition, ?int $id, array $payload): array
    {
        if ($id === null || $id <= 0) {
            throw new \InvalidArgumentException('Missing identifier for update');
        }

        $normalized = self::normalizePayload($resource, $payload, true);
        $normalized[$definition['primaryKey']] = $id;
        $save = [$definition['table'] => $normalized];
        $result = $db->sql_save($save);

        if (!$result) {
            throw new \RuntimeException('Update failed for resource '.$resource);
        }

        return [
            'status' => 200,
            'data' => [
                'id' => $id,
                'resource' => $resource,
                'updated' => true,
            ],
        ];
    }

    /**
     * @param object $db Database adapter exposing the legacy SQL API.
     * @param array{table:string,primaryKey:string,deleteMode:string} $definition Resource definition.
     * @param int|null $id Route identifier.
     *
     * @return array{status:int,data:array<string,mixed>}
     */
    private function handleDelete(object $db, array $definition, ?int $id): array
    {
        if ($id === null || $id <= 0) {
            throw new \InvalidArgumentException('Missing identifier for delete');
        }

        $table = $definition['table'];
        $primaryKey = $definition['primaryKey'];

        switch ($definition['deleteMode']) {
            case 'guarded_client':
                if ($id === 99) {
                    throw new \InvalidArgumentException('Client 99 cannot be deleted');
                }
                $db->sql_query('DELETE FROM `'.$table.'` WHERE `'.$primaryKey.'` = '.(int) $id.' LIMIT 1');
                break;
            case 'guarded_environment':
                $db->sql_query('DELETE FROM `'.$table.'` WHERE `'.$primaryKey.'` = '.(int) $id.' AND `'.$primaryKey.'` > 6 LIMIT 1');
                break;
            case 'soft_server':
                $db->sql_query('UPDATE `'.$table.'` SET `is_deleted` = 1 WHERE `'.$primaryKey.'` = '.(int) $id.' LIMIT 1');
                break;
            default:
                $db->sql_query('DELETE FROM `'.$table.'` WHERE `'.$primaryKey.'` = '.(int) $id.' LIMIT 1');
                break;
        }

        return [
            'status' => 200,
            'data' => [
                'id' => $id,
                'deleted' => true,
            ],
        ];
    }

    /**
     * Decode the current JSON request body.
     *
     * @return array<string,mixed>
     */
    private static function readJsonInput(): array
    {
        $raw = self::readRawRequestBody();
        if ($raw === '') {
            return [];
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            throw new \InvalidArgumentException('Invalid JSON payload');
        }

        return $decoded;
    }

    /**
     * Read the raw request body from the active SAPI.
     */
    public static function readRawRequestBody(): string
    {
        $stdin = fopen('php://stdin', 'r');
        if ($stdin === false) {
            return (string) file_get_contents('php://input');
        }

        try {
            return self::readRawRequestBodyFromStreams(
                (string) file_get_contents('php://input'),
                $stdin,
                defined('IS_CLI') && IS_CLI
            );
        } finally {
            fclose($stdin);
        }
    }

    /**
     * Resolve the request body while keeping CLI stdin fallback unit-testable.
     *
     * @param resource $stdin
     */
    public static function readRawRequestBodyFromStreams(string $input, $stdin, bool $isCli): string
    {
        if ($input !== '' || !$isCli) {
            return $input;
        }

        return (string) stream_get_contents($stdin);
    }

    /**
     * Emit a JSON HTTP response.
     *
     * @param array<string,mixed> $payload Response payload.
     * @param int $status HTTP status code.
     *
     * @return void
     */
    private function respondJson(array $payload, int $status): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n";
    }
}
