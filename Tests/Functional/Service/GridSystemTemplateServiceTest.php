<?php
namespace Arndtteunissen\ColumnLayout\Tests\Functional\Service;

/*
 * This file is part of the package arndtteunissen/column-layout.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

use Arndtteunissen\ColumnLayout\Service\GridSystemTemplateService;
use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use TYPO3\CMS\Core\Cache\Backend\NullBackend;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Frontend\StringFrontend;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\TypoScript\Parser\TypoScriptParser;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\ContentObject\DataProcessorInterface;
use TYPO3Fluid\Fluid\Core\Rendering\RenderableClosure;

class GridSystemTemplateServiceTest extends FunctionalTestCase
{
    const ROW_CACHE_IDENTIFIER = 'GridSystemTemplateServiceTest_0';

    /**
     * @var array
     */
    protected $coreExtensionsToLoad = [
        'core',
        'frontend',
        'fluid'
    ];

    /**
     * @var array
     */
    protected $testExtensionsToLoad = [
        'typo3conf/ext/column_layout'
    ];

    /**
     * @var GridSystemTemplateService
     */
    protected $subject;

    public static function setUpBeforeClass()
    {
        $GLOBALS['TSFE'] = new \stdClass();
        $GLOBALS['TSFE']->tmpl = new \stdClass();
        $typoScriptParser = new TypoScriptParser();
        $typoScriptParser->parse(file_get_contents(__DIR__ . '/../../../Configuration/TypoScript/setup.typoscript'));
        $GLOBALS['TSFE']->tmpl->setup = $typoScriptParser->setup;
    }

    protected function setUp()
    {
        parent::setUp();

        $this->subject = new GridSystemTemplateService();

        $nullBackend = new NullBackend('Testing');
        $cacheFrontend = new StringFrontend(GridSystemTemplateService::TEMPLATES_CACHE_NAME, $nullBackend);

        $cacheManager = self::createMock(CacheManager::class);
        $cacheManager->method('getCache')
            ->willReturn($cacheFrontend);

        $this->subject->injectCacheManager($cacheManager);
    }

    public function testRendersDefaultRowByDefault()
    {
        $row = $this->subject->renderRowBeginHtml(self::ROW_CACHE_IDENTIFIER);
        $row .= $this->subject->renderRowEndHtml(self::ROW_CACHE_IDENTIFIER);

        $expected = '
    <div class="row-container">
        <div class="row">
            
        </div>
    </div>
';

        $this->assertXmlStringEqualsXmlString($expected, $row);
    }

    public function testRendersColumnContent()
    {
        $testContentString = 'Test content for GridSystemTemplateServiceTest';

        $content = new RenderableClosure();
        $content->setClosure(function () use ($testContentString) {
            return $testContentString;
        });

        $rendered = $this->subject->renderColumnHtml(['column_content' => $content]);

        $this->assertContains($testContentString, $rendered);
    }

    public function testDataProcessorsAreCalled()
    {
        $cObj = $this->createMock(ContentObjectRenderer::class);
        $this->subject->injectContentObjectRenderer($cObj);

        $dataProcessorMock = $this->getMockBuilder([DataProcessorInterface::class, SingletonInterface::class])->getMock();
        $dataProcessorMock->expects($this->once())
            ->method('process')
            ->willReturn([]);
        GeneralUtility::setSingletonInstance(get_class($dataProcessorMock), $dataProcessorMock);

        $GLOBALS['TSFE']->tmpl->setup['lib.']['tx_column_layout.']['rendering.']['row.']['dataProcessing.'][5] = get_class($dataProcessorMock);

        $this->subject->renderRowBeginHtml(self::ROW_CACHE_IDENTIFIER);
    }
}
