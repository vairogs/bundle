<?php declare(strict_types = 1);

/*
 * This file is part of the Vairogs package.
 *
 * (c) Dāvis Zālītis (k0d3r1s) <davis@vairogs.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Vairogs\Bundle\Service;

use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Vairogs\Component\Functions\Memoize;

#[Autoconfigure(public: true, shared: true)]
final class RequestCache extends Memoize
{
}
