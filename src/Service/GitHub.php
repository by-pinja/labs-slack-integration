<?php
declare(strict_types = 1);
/**
 * /src/Service/GitHub.php
 *
 * @author  TLe, Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */
namespace App\Service;

use App\Helper\LoggerAwareTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Github\Api\Organization;
use Github\Api\RateLimit;
use Github\Api\Repo;
use Github\Client as GitHubClient;
use Github\ResultPager;

/**
 * Class GitHub
 *
 * @package App\Service
 * @author  TLe, Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */
class GitHub
{
    // Traits
    use LoggerAwareTrait;

    /**
     * @var GitHubClient
     */
    private $gitHubClient;

    /**
     * GitHub constructor.
     *
     * @param GitHubClient $gitHubClient
     */
    public function __construct(GitHubClient $gitHubClient)
    {
        $this->gitHubClient = $gitHubClient;
    }

    /**
     * Method to return remaining rate limit value.
     *
     * @return int
     *
     * @throws \Github\Exception\InvalidArgumentException
     */
    public function getRemainingRateLimit(): int
    {
        /** @var RateLimit $api */
        $api = $this->gitHubClient->api('rate_limit');

        return (int)$api->getRateLimits()['rate']['remaining'];
    }

    /**
     * Method to get current rate limit expiration as a \DateTime object.
     *
     * @return \DateTime
     *
     * @throws \Github\Exception\InvalidArgumentException
     */
    public function getRateLimitExpiration(): \DateTime
    {
        /** @var RateLimit $api */
        $api = $this->gitHubClient->api('rate_limit');

        return \DateTime::createFromFormat('U', (string)$api->getRateLimits()['rate']['reset']);
    }

    /**
     * Method to fetch specified organization repositories and return those as an ArrayCollection.
     *
     * TODO: should we generate real objects of those repositories?
     *
     * @param string $organization
     *
     * @return ArrayCollection
     *
     * @throws \Github\Exception\InvalidArgumentException
     */
    public function getRepositoriesOrganization(string $organization): ArrayCollection
    {
        /** @var Organization $api */
        $api = $this->gitHubClient->api('organization');

        $repositories = (new ResultPager($this->gitHubClient))->fetchAll($api, 'repositories', [$organization]);

        return new ArrayCollection($repositories);
    }

    /**
     * Method to fetch specified repository README.md file.
     *
     * TODO: Should return value to be real object instead of simple array?
     *
     * @param array $repository
     *
     * @return array
     *
     * @throws \Github\Exception\InvalidArgumentException
     */
    public function getReadMe(array $repository): array
    {
        /** @var Repo $api */
        $api = $this->gitHubClient->api('repo');

        return $api->contents()->readme($repository['owner']['login'], $repository['name']);
    }
}
