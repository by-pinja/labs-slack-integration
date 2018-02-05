<?php
declare(strict_types=1);
/**
 * /src/Handler/HelperTrait.php
 *
 * @author TLe, Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */
namespace App\Handler;

/**
 * Trait HelperTrait
 *
 * @package App\Handler
 * @author TLe, Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */
trait HelperTrait
{
    /**
     * @param string $filename
     *
     * @return string
     */
    protected function getSourceLink(string $filename): string
    {
        return '<https://github.com/protacon/labs-slack-integration/blob/master/src/Handler/' . $filename . '|source>';
    }
}