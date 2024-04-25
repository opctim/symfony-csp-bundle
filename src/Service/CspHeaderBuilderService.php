<?php
declare(strict_types=1);

namespace Opctim\CspBundle\Service;

use Exception;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CspHeaderBuilderService
{
    private CspNonceService $cspNonceService;
    private UrlGeneratorInterface $urlGenerator;
    private array $alwaysAdd;
    private array $directives;
    private array $report;


    public function __construct(
        CspNonceService $cspNonceService,
        UrlGeneratorInterface $urlGenerator,
        array $alwaysAdd = [],
        array $directives = [],
        array $report = []
    )
    {
        $this->cspNonceService = $cspNonceService;
        $this->urlGenerator = $urlGenerator;
        $this->directives = $directives;
        $this->alwaysAdd = $alwaysAdd;
        $this->report = $report;
    }

    public function build(?array $alwaysAddOverride = null, ?array $directivesOverride = null): string
    {
        $alwaysAdd = $alwaysAddOverride ?? $this->alwaysAdd;
        $directives = $directivesOverride ?? $this->directives;

        $lines = [];

        foreach ($directives as $directiveName => $origins) {
            $origins = $this->parseNonceExpressions(
                [ ...$alwaysAdd, ...$origins ]
            );

            $lines[] = $directiveName . ' ' . implode(' ', $origins) . ';';
        }

        $reportDirective = $this->buildReportDirective();

        if ($reportDirective) {
            $lines[] = $reportDirective;
        }

        return implode(' ', $lines);
    }

    protected function buildReportDirective(): ?string
    {
        $reportUrl = $this->getReportUrl($this->report);

        if ($reportUrl && $this->shouldReport($this->report['chance'] ?? 0)) {
            return 'report-uri ' . $reportUrl . '; report-to csp-endpoint;';
        }

        return null;
    }

    public function buildReportingEndpointsHeader(): ?string
    {
        $reportUrl = $this->getReportUrl($this->report);

        if ($reportUrl && $this->shouldReport($this->report['chance'] ?? 0)) {
            return 'csp-endpoint="' . $reportUrl . '"';
        }

        return null;
    }

    protected function getReportUrl(array $report): ?string
    {
        if (!empty($report['url'])) {
            return $report['url'];
        }

        if (!empty($report['route'])) {
            return $this->urlGenerator->generate(
                $report['route'],
                $report['route_params'] ?? [],
                UrlGeneratorInterface::ABSOLUTE_URL
            );
        }

        return null;
    }

    protected function shouldReport(int $chance): bool
    {
        try {
            return random_int(0, 99) < $chance;
        } catch (Exception $e) {
            return true;
        }
    }

    /**
     * @param string[] $origins
     */
    protected function parseNonceExpressions(array $origins): array
    {
        return array_map(
            function(string $origin) {
                if (
                    preg_match('/nonce\((?<HANDLE>[^)]+)\)/ui', $origin, $matches)
                    && !empty($matches['HANDLE'])
                ) {
                    $handle = trim($matches['HANDLE']);

                    return "'nonce-" . $this->cspNonceService->addNonce($handle) . "'";
                }

                return trim($origin);
            },
            $origins
        );
    }

    public function getAlwaysAdd(): array
    {
        return $this->alwaysAdd;
    }

    public function getDirectives(): array
    {
        return $this->directives;
    }
}