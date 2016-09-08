<?php
/**
 * Created by PhpStorm.
 * User: pomir
 * Date: 9/8/2016
 * Time: 1:43 PM
 */

namespace Pomirleanu\ImageColors\Facades;


use Illuminate\Support\Facades\Facade;

class ImageColors extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor() { return 'ImageColors'; }
}