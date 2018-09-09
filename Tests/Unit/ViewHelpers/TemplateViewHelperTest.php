<?php
namespace Arndtteunissen\ColumnLayout\Tests\Unit\ViewHelpers;

/*
 * This file is part of the package arndtteunissen/column-layout.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

use Arndtteunissen\ColumnLayout\ViewHelpers\TemplateViewHelper;
use Nimut\TestingFramework\MockObject\AccessibleMockObjectInterface;
use Nimut\TestingFramework\TestCase\ViewHelperBaseTestcase;
use PHPUnit\Framework\MockObject\MockObject;

class TemplateViewHelperTest extends ViewHelperBaseTestcase
{
    /**
     * @var TemplateViewHelper|AccessibleMockObjectInterface|MockObject
     */
    protected $viewHelper;

    protected function setUp()
    {
        parent::setUp();
        $this->viewHelper = $this->getAccessibleMock(TemplateViewHelper::class);
        $this->injectDependenciesIntoViewHelper($this->viewHelper);
        $this->viewHelper->initializeArguments();
    }

    public function testRendersNoOutput()
    {
        $this->assertEmpty($this->viewHelper->render());
    }

    public function testHasNameArgumentAndIsValid()
    {
        $this->viewHelper->setArguments(['name' => 'testName']);

        $this->assertNull($this->viewHelper->validateArguments());
    }
}
