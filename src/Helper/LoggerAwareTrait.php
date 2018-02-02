<?php
declare(strict_types = 1);
/**
 * /src/Helper/LoggerAwareTrait.php
 *
 * @author  TLe, Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */
namespace App\Helper;

use Psr\Log\LoggerInterface;

/**
 * Trait LoggerAwareTrait
 *
 * @package App\Helper
 * @author  TLe, Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */
trait LoggerAwareTrait
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /** @noinspection ReturnTypeCanBeDeclaredInspection */
    /**
     * @see https://symfony.com/doc/current/service_container/autowiring.html#autowiring-other-methods-e-g-setters
     *
     * @required
     *
     * @param LoggerInterface $logger
     *
     * @return self
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;

        return $this;
    }
}