<?php declare(strict_types = 1);

/*
 * This file is part of the Vairogs package.
 *
 * (c) Dāvis Zālītis (k0d3r1s) <davis@vairogs.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Vairogs\Bundle\ApiPlatform\OpenApi;

use ArrayObject;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Vairogs\Bundle\Constants\BundleContext;
use Vairogs\Bundle\Service\RequestCache;
use Vairogs\Component\Functions\Text;

#[AsDecorator(decorates: 'api_platform.openapi.normalizer.api_gateway')]
readonly class OpenApiNormalizer implements NormalizerInterface
{
    final protected const string REF = 'iri-reference';
    final protected const string DEFAULT_EXAMPLE = 'https://example.com/';

    public function __construct(
        private NormalizerInterface $decorated,
        private RequestCache $requestCache,
    ) {
    }

    public function getSupportedTypes(
        ?string $format,
    ): array {
        return $this->decorated->getSupportedTypes($format);
    }

    public function normalize(
        mixed $data,
        ?string $format = null,
        array $context = [],
    ): array|string|int|float|bool|ArrayObject|null {
        return $this->decorated->normalize($data, $format, $context);
    }

    public function supportsNormalization(
        mixed $data,
        ?string $format = null,
        array $context = [],
    ): bool {
        return $this->decorated->supportsNormalization($data, $format, $context);
    }

    protected function pluralize(
        string $word,
    ): string {
        static $_helper = null;

        if (null === $_helper) {
            $_helper = new class {
                use Text\_Pluralize;
            };
        }

        return $this->requestCache->memoize(BundleContext::PLURAL, $word, static fn (): string => $_helper->pluralize($word));
    }

    protected function singularize(
        string $word,
    ): string {
        static $_helper = null;

        if (null === $_helper) {
            $_helper = new class {
                use Text\_Singularize;
            };
        }

        return $this->requestCache->memoize(BundleContext::PLURAL, $word, static fn (): string => $_helper->singularize($word));
    }
}
