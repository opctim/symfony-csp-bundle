<?php
declare(strict_types=1);

namespace Opctim\CspBundle\Service;

class CspHeaderBuilderService
{
    private CspNonceService $cspNonceService;
    private array $alwaysAdd = [];
    private array $directives = [];

    public function __construct(
        CspNonceService $cspNonceService,
        array $alwaysAdd = [],
        array $directives = []
    )
    {
        $this->directives = $directives;
        $this->alwaysAdd = $alwaysAdd;
        $this->cspNonceService = $cspNonceService;
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

        return implode(' ', $lines);
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

                    return 'nonce-' . $this->cspNonceService->addNonce($handle);
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