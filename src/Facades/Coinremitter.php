<?php

namespace PrevailExcel\Coinremitter\Facades;

use Illuminate\Support\Facades\Facade;

/*
 * This file is part of the Laravel Coinremitter package.
 *
 * (c) Prevail Ejimadu <prevailexcellent@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Coinremitter extends Facade
{
    /**
     * Get the registered name of the component
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'laravel-coinremitter';
    }
    
    final public const VERSION = '1.0.0';

}