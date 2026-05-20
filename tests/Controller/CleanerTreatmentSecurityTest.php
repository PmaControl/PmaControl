<?php

declare(strict_types=1);

use App\Controller\Cleaner;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class CleanerTreatmentSecurityTest extends TestCase
{
    public function testCleanerMainIdNormalizerAcceptsPositiveInteger(): void
    {
        $this->assertSame(12, Cleaner::normalizeCleanerMainId('12'));
        $this->assertSame(12, Cleaner::normalizeCleanerMainId(' 12 '));
    }

    #[DataProvider('invalidCleanerMainIdProvider')]
    public function testCleanerMainIdNormalizerRejectsSqlInjectionPayloads($value): void
    {
        $this->assertNull(Cleaner::normalizeCleanerMainId($value));
    }

    public function testTreatmentUsesNormalizedIdBeforeSql(): void
    {
        $controller = (string) file_get_contents(__DIR__ . '/../../App/Controller/Cleaner.php');
        $treatmentStart = strpos($controller, 'public function treatment($param)');
        $detailStart = strpos($controller, 'public function detail($param)', $treatmentStart);

        $this->assertIsInt($treatmentStart);
        $this->assertIsInt($detailStart);

        $treatmentBody = substr($controller, $treatmentStart, $detailStart - $treatmentStart);

        $this->assertStringContainsString('use App\\Library\\Security\\PositiveIntegerSelection;', $controller);
        $this->assertStringContainsString('$idCleaner = self::normalizeCleanerMainId($param[0] ?? null);', $treatmentBody);
        $this->assertStringContainsString("self::sendCleanerTreatmentError(400, 'Invalid cleaner id');", $treatmentBody);
        $this->assertStringContainsString('WHERE `id_cleaner_main` = ".$idCleaner."', $treatmentBody);
        $this->assertStringContainsString('$data[\'id_cleaner\'] = $idCleaner;', $treatmentBody);
        $this->assertStringNotContainsString('WHERE `id_cleaner_main`=\'".$param[0]."\'', $treatmentBody);
        $this->assertStringNotContainsString('$data[\'id_cleaner\'] = $param[0];', $treatmentBody);
    }

    public static function invalidCleanerMainIdProvider(): array
    {
        return [
            'empty' => [''],
            'zero' => ['0'],
            'negative' => ['-1'],
            'classic boolean injection' => ["1' OR '1'='1"],
            'union injection' => ["1' UNION SELECT password FROM user_main-- -"],
            'sql expression' => ['1 OR 1=1'],
            'array' => [['12']],
            'null' => [null],
        ];
    }
}
