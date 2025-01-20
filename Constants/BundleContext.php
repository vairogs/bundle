<?php declare(strict_types = 1);

/*
 * This file is part of the Vairogs package.
 *
 * (c) Dāvis Zālītis (k0d3r1s) <davis@vairogs.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Vairogs\Bundle\Constants;

enum BundleContext: string
{
    case CALLER_CLASS = 'VAIROGS_BUNDLE_CALL_CLASS';
    case PLURAL = 'VAIROGS_BUNDLE_PLURAL';
    case READ_PROPERTY = 'VAIROGS_BUNDLE_READ_PROPERTY';
    case REFLECTION = 'VAIROGS_BUNDLE_REFLECTION';
}
