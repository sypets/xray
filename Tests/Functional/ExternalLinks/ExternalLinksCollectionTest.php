<?php

namespace B13\Xrax\Tests\Functional\ExternalLinks;

use B13\Xray\ExternalLinks\Configuration\DefaultConfiguration;
use B13\Xray\ExternalLinks\Converter\FileLinkConverter;
use B13\Xray\ExternalLinks\Converter\PageLinkConverter;
use B13\Xray\ExternalLinks\ExternalLinkCollectionFactory;
use Prophecy\Prophecy\ObjectProphecy;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\LinkHandling\LinkService;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Redirects\Repository\RedirectRepository;
use TYPO3\CMS\Redirects\Service\IntegrityService;
use TYPO3\CMS\Redirects\Service\RedirectCacheService;
use TYPO3\CMS\Redirects\Service\RedirectService;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

class ExternalLinksCollectionTest extends FunctionalTestCase
{
    /**
     * @var bool Reset singletons created by subject
     */
    protected bool $resetSingletonInstances = true;

    /**
     * @var ExternalLinkCollectionFactory
     */
    protected $collection;

    protected function setUp(): void
    {
        parent::setUp();
        $siteFinderProphecy = $this->prophesizeSiteFinder();
        /*
        $factory = new ExternalLinkCollectionFactory(GeneralUtility::makeInstance(ConnectionPool::class),
            $siteFinderProphecy,
            new PageLinkConverter(new PageRepository()),
            new FileLinkConverter()
        );
        */
        $factory = self::getContainer()->get(ExternalLinkCollectionFactory::class);

        $this->collection = $factory->fromConfiguration(new DefaultConfiguration());
    }

    /**
     * @test
     */
    public function externalLinks()
    {
        $this->collection->convertAll();
    }

    /**
     * Create SiteFinder prophecy
     *
     * Adapted from EXT:redirect IntegrityServiceTests
     *
     * @return ObjectProphecy
     * @throws \TYPO3\CMS\Core\Exception\SiteNotFoundException
     */
    private function prophesizeSiteFinder(): ObjectProphecy
    {
        $siteFinderProphecy = $this->prophesize(SiteFinder::class);

        $simpleSite = new Site('simple-page', 1, [
            'base' => 'https://example.com',
            'languages' => [
                [
                    'languageId' => 0,
                    'title' => 'United States',
                    'locale' => 'en_US.UTF-8',
                ],
            ],
        ]);
        $localizedSite = new Site('localized-page', 100, [
            'base' => 'https://another.example.com',
            'languages' => [
                [
                    'languageId' => 0,
                    'title' => 'United States',
                    'locale' => 'en_US.UTF-8',
                ],
                [
                    'base' => 'https://de.another.example.com',
                    'languageId' => 1,
                    'title' => 'DE',
                    'locale' => 'de_DE.UTF-8',
                ],
                [
                    'base' => '/fr/',
                    'languageId' => 2,
                    'title' => 'FR',
                    'locale' => 'fr_FR.UTF-8',
                ],
            ],
        ]);

        $siteFinderProphecy->getSiteByIdentifier('simple-page')->willReturn($simpleSite);
        $siteFinderProphecy->getSiteByIdentifier('localized-page')->willReturn($localizedSite);
        $siteFinderProphecy->getAllSites()->willReturn([$simpleSite, $localizedSite]);

        return $siteFinderProphecy;
    }


}
