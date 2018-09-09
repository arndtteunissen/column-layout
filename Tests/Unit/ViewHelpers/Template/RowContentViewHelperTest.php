<?php
namespace Arndtteunissen\ColumnLayout\Tests\Unit\ViewHelpers\Template;

/*
 * This file is part of the package arndtteunissen/column-layout.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

use Arndtteunissen\ColumnLayout\Service\GridSystemTemplateService;
use Arndtteunissen\ColumnLayout\ViewHelpers\Template\RowContentViewHelper;
use Nimut\TestingFramework\TestCase\ViewHelperBaseTestcase;
use TYPO3Fluid\Fluid\Core\Compiler\TemplateCompiler;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;

class RowContentViewHelperTest extends ViewHelperBaseTestcase
{
    /**
     * @var RowContentViewHelper
     */
    protected $viewHelper;

    protected function setUp()
    {
        parent::setUp();
        $this->viewHelper = new RowContentViewHelper();
        $this->injectDependenciesIntoViewHelper($this->viewHelper);
        $this->viewHelper->initializeArguments();
    }

    public function testOnlyRendersMarker()
    {
        $actualContent = $this->viewHelper->render();
        $expected = GridSystemTemplateService::ROW_SPLIT_MARKER;

        $this->assertEquals($expected, $actualContent, 'The ViewHelper is not rendering the marker');
    }

    public function testCompilesWithStaticMarker()
    {
        $viewHelperNodeMock = $this->createMock(ViewHelperNode::class);
        $templateCompiler = $this->createMock(TemplateCompiler::class);

        $compiledOutput = $this->viewHelper->compile(null, null, $initPhp, $viewHelperNodeMock, $templateCompiler);

        $this->assertContains(GridSystemTemplateService::ROW_SPLIT_MARKER, $compiledOutput, 'The compiled ViewHelper does not include the static marker');
    }
}
