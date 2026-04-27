<?php

declare(strict_types=1);

use App\Library\Security\GroupedFormRequest;
use Glial\Security\Csrf;
use PHPUnit\Framework\TestCase;

final class GroupedFormRequestTest extends TestCase
{
    public function testEvaluateAcceptsValidGroupedPost(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'environment.add');

        $outcome = GroupedFormRequest::evaluate(
            [
                Csrf::DEFAULT_FIELD => $token,
                'environment' => [
                    'libelle' => ' Preprod ',
                    'class' => 'warning',
                ],
            ],
            $this->sameSitePostServer(),
            $session,
            'environment.add',
            'environment',
            [
                'libelle' => ['type' => 'string', 'required' => true, 'max' => 20],
                'class' => ['type' => 'enum', 'required' => true, 'values' => ['warning', 'danger']],
                'letter' => ['type' => 'string', 'default' => 'P', 'max' => 1],
            ],
            'Invalid environment add payload'
        );

        $this->assertSame(200, $outcome['status']);
        $this->assertSame(['letter' => 'P', 'libelle' => 'Preprod', 'class' => 'warning'], $outcome['payload']);
    }

    public function testEvaluatePropagatesCsrfGuardFailure(): void
    {
        $outcome = GroupedFormRequest::evaluate(
            [],
            ['REQUEST_METHOD' => 'GET'],
            [],
            'environment.add',
            'environment',
            ['libelle' => ['required' => true]],
            'Invalid environment add payload'
        );

        $this->assertSame(405, $outcome['status']);
        $this->assertSame('POST', $outcome['headers']['Allow']);
        $this->assertNull($outcome['payload']);
    }

    public function testNormalizeRejectsMalformedUnexpectedOrInvalidFields(): void
    {
        $rules = [
            'libelle' => ['type' => 'string', 'required' => true, 'max' => 20],
            'class' => ['type' => 'enum', 'required' => true, 'values' => ['warning', 'danger']],
        ];

        $this->assertNull(GroupedFormRequest::normalize([], 'environment', $rules));
        $this->assertNull(GroupedFormRequest::normalize(['environment' => 'Preprod'], 'environment', $rules));
        $this->assertNull(GroupedFormRequest::normalize(['environment' => []], 'environment', $rules));
        $this->assertNull(GroupedFormRequest::normalize(['environment' => ['id' => '7', 'libelle' => 'Preprod', 'class' => 'warning']], 'environment', $rules));
        $this->assertNull(GroupedFormRequest::normalize(['environment' => ['libelle' => ['Preprod'], 'class' => 'warning']], 'environment', $rules));
        $this->assertNull(GroupedFormRequest::normalize(['environment' => ['libelle' => str_repeat('a', 21), 'class' => 'warning']], 'environment', $rules));
        $this->assertNull(GroupedFormRequest::normalize(['environment' => ['libelle' => 'Preprod', 'class' => 'owned']], 'environment', $rules));
        $this->assertNull(GroupedFormRequest::normalize(['environment' => ['libelle' => '', 'class' => 'warning']], 'environment', $rules));

        $this->assertSame(
            ['libelle' => 'Preprod', 'class' => 'warning'],
            GroupedFormRequest::normalize(['environment' => ['libelle' => ' Preprod ', 'class' => 'warning']], 'environment', $rules)
        );
    }

    private function sameSitePostServer(): array
    {
        return [
            'REQUEST_METHOD' => 'POST',
            'HTTPS' => 'on',
            'HTTP_HOST' => 'pmacontrol.test',
            'HTTP_ORIGIN' => 'https://pmacontrol.test',
        ];
    }
}
