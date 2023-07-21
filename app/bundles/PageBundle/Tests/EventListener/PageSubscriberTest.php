<?php

namespace Mautic\PageBundle\Tests\EventListener;

use Mautic\CoreBundle\Helper\CoreParametersHelper;
use Mautic\CoreBundle\Helper\IpLookupHelper;
use Mautic\CoreBundle\Model\AuditLogModel;
use Mautic\CoreBundle\Translation\Translator;
use Mautic\CoreBundle\Twig\Helper\AssetsHelper;
use Mautic\LeadBundle\Entity\Lead;
use Mautic\LeadBundle\Entity\LeadRepository;
use Mautic\PageBundle\Entity\Hit;
use Mautic\PageBundle\Entity\HitRepository;
use Mautic\PageBundle\Event\PageBuilderEvent;
use Mautic\PageBundle\EventListener\PageSubscriber;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Asset\Packages;
use Symfony\Component\HttpFoundation\Request;

class PageSubscriberTest extends TestCase
{
    public function testGetTokensWhenCalledReturnsValidTokens()
    {
        $translator       = $this->createMock(Translator::class);
        $pageBuilderEvent = new PageBuilderEvent($translator);
        $pageBuilderEvent->addToken('{token_test}', 'TOKEN VALUE');
        $tokens = $pageBuilderEvent->getTokens();
        $this->assertArrayHasKey('{token_test}', $tokens);
        $this->assertEquals($tokens['{token_test}'], 'TOKEN VALUE');
    }

    /**
     * Get page subscriber with mocked dependencies.
     */
    protected function getPageSubscriber(): PageSubscriber
    {
        /** @var Packages&MockObject $packagesMock */
        $packagesMock = $this->createMock(Packages::class);

        /** @var CoreParametersHelper&MockObject $coreParametersHelper */
        $coreParametersHelper = $this->createMock(CoreParametersHelper::class);

        $assetsHelperMock   = new AssetsHelper($packagesMock, $coreParametersHelper);
        $ipLookupHelperMock = $this->createMock(IpLookupHelper::class);
        $auditLogModelMock  = $this->createMock(AuditLogModel::class);
        $hitRepository      = $this->createMock(HitRepository::class);
        $contactRepository  = $this->createMock(LeadRepository::class);
        $hitMock            = $this->createMock(Hit::class);
        $leadMock           = $this->createMock(Lead::class);

        $hitRepository->expects($this->any())
            ->method('find')
            ->will($this->returnValue($hitMock));

        $contactRepository->expects($this->any())
            ->method('find')
            ->will($this->returnValue($leadMock));

        return new PageSubscriber(
            $assetsHelperMock,
            $ipLookupHelperMock,
            $auditLogModelMock
        );
    }

    /**
     * Get non empty payload, having a Request and non-null entity IDs.
     *
     * @return array<string, bool|int|MockObject>
     */
    protected function getNonEmptyPayload(): array
    {
        $requestMock = $this->createMock(Request::class);

        return [
            'request' => $requestMock,
            'isNew'   => true,
            'hitId'   => 123,
            'pageId'  => 456,
            'leadId'  => 789,
        ];
    }

    /**
     * Get empty payload with all null entity IDs.
     *
     * @return array<string, null>
     */
    protected function getEmptyPayload(): array
    {
        return array_fill_keys(['request', 'isNew', 'hitId', 'pageId', 'leadId'], null);
    }
}
