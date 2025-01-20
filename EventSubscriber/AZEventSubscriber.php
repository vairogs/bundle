<?php declare(strict_types = 1);

/*
 * This file is part of the Vairogs package.
 *
 * (c) Dāvis Zālītis (k0d3r1s) <davis@vairogs.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Vairogs\Bundle\EventSubscriber;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use ReflectionException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Vairogs\Bundle\ApiPlatform\Functions;
use Vairogs\Bundle\Service\RequestCache;
use Vairogs\Bundle\Traits\_GetReadProperty;
use Vairogs\Component\Functions\Iteration;

use function array_key_exists;
use function array_map;
use function array_slice;
use function ksort;
use function stripos;

use const JSON_PRETTY_PRINT;

readonly class AZEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private Functions $functions,
        private RequestCache $requestCache,
    ) {
    }

    public function onKernelResponse(
        ResponseEvent $event,
    ): void {
        $response = $event->getResponse();

        $contentType = $response->headers->get('Content-Type');

        if (!$contentType || (false === stripos($contentType, 'application/json') && false === stripos($contentType, 'application/ld+json')) || !$response->getContent()) {
            return;
        }

        static $_helper = null;

        if (null === $_helper) {
            $_helper = new class {
                use Iteration\_JsonDecode;
                use Iteration\_JsonEncode;
            };
        }

        $data = $_helper->jsonDecode($response->getContent());

        if (isset($data->paths)) {
            foreach ($data->paths as $path) {
                foreach ($path as $operation) {
                    if (isset($operation->parameters)) {
                        foreach ($operation->parameters as $parameter) {
                            unset($parameter->allowReserved, $parameter->allowEmptyValue);
                        }
                    }
                }
            }
        }

        $response->setContent($_helper->jsonEncode($data, JSON_PRETTY_PRINT));
    }

    /**
     * @throws ReflectionException
     */
    public function onKernelView(
        ViewEvent $event,
    ): void {
        $request = $event->getRequest();

        if ($request->attributes->has('_api_resource_class')) {
            $class = $request->attributes->get('_api_resource_class');

            if ($this->functions->isResource($class)) {
                $serialized = $event->getControllerResult();

                static $_helper = null;

                if (null === $_helper) {
                    $_helper = new class {
                        use _GetReadProperty;
                        use Iteration\_JsonDecode;
                        use Iteration\_JsonEncode;
                    };
                }

                $data = $_helper->jsonDecode($serialized, Iteration::ASSOCIATIVE);
                $rp = $_helper->getReadProperty($class, $this->requestCache);

                if (null !== $data && array_key_exists('@type', $data)) {
                    if ('Collection' === $data['@type']) {
                        $data['member'] = array_map(function ($item) use ($rp) {
                            ksort($item);

                            return $this->moveTopLevelKey($item, $rp, 2);
                        }, $data['member']);
                    } else {
                        ksort($data);

                        $data = $this->moveTopLevelKey($data, $rp, 3);
                    }

                    $event->setControllerResult($_helper->jsonEncode($data));
                }
            }
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => [
                'onKernelView',
                EventPriorities::POST_SERIALIZE,
            ],
            KernelEvents::RESPONSE => ['onKernelResponse'],
        ];
    }

    private function moveTopLevelKey(
        array $array,
        string $keyToMove,
        int $position,
    ): array {
        if (!array_key_exists($keyToMove, $array)) {
            return $array;
        }

        $keyValue = [$keyToMove => $array[$keyToMove]];
        unset($array[$keyToMove]);

        $start = array_slice($array, 0, $position, true);
        $end = array_slice($array, $position, null, true);

        return $start + $keyValue + $end;
    }
}
