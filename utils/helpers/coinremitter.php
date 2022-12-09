<?php

/*
 * This file is part of the Laravel Coinremitter package.
 *
 * (c) Prevail Ejimadu <prevailexcellent@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

if (! function_exists("coinremitter"))
{
    function coinremitter() {
        
        return app()->make('laravel-coinremitter');
    }
}