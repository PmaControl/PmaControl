<?php

declare(strict_types=1);

namespace App\Library\ProxySqlAudit;

/**
 * Immutable record returned by an AuditCheck (#895 / #896).
 *
 * Severity vocabulary is fixed to keep the home-page card and the
 * /ProxySQL/audit/<id>/ view in lockstep:
 *   - critical: traffic-impacting misconfig — operator must act now.
 *   - warning:  drift / degraded posture — operator should act soon.
 *   - info:     audit metadata (a check failed to execute, etc.).
 */
final class Finding
{
    public const SEVERITY_CRITICAL = 'critical';
    public const SEVERITY_WARNING  = 'warning';
    public const SEVERITY_INFO     = 'info';

    public function __construct(
        public readonly string $severity,
        public readonly string $category,
        public readonly string $checkId,
        public readonly string $title,
        public readonly string $evidence = '',
        public readonly string $fix = '',
        public readonly ?string $ticket = null
    ) {
        if (!in_array($severity, [self::SEVERITY_CRITICAL, self::SEVERITY_WARNING, self::SEVERITY_INFO], true)) {
            throw new \InvalidArgumentException("Unknown severity: {$severity}");
        }
        if ($category === '' || $checkId === '' || $title === '') {
            throw new \InvalidArgumentException('category, checkId and title are required');
        }
    }

    /**
     * JSON-serializable shape (used by the home page and the
     * "Copy as JSON" export).
     *
     * @return array{
     *   severity:string, category:string, check_id:string, title:string,
     *   evidence:string, fix:string, ticket:?string
     * }
     */
    public function toArray(): array
    {
        return [
            'severity' => $this->severity,
            'category' => $this->category,
            'check_id' => $this->checkId,
            'title'    => $this->title,
            'evidence' => $this->evidence,
            'fix'      => $this->fix,
            'ticket'   => $this->ticket,
        ];
    }

    public static function fromArray(array $a): self
    {
        return new self(
            (string) ($a['severity'] ?? self::SEVERITY_INFO),
            (string) ($a['category'] ?? ''),
            (string) ($a['check_id'] ?? ''),
            (string) ($a['title']    ?? ''),
            (string) ($a['evidence'] ?? ''),
            (string) ($a['fix']      ?? ''),
            isset($a['ticket']) && $a['ticket'] !== '' ? (string) $a['ticket'] : null,
        );
    }
}
