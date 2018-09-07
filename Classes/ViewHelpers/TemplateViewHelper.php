<?php
namespace Arndtteunissen\ColumnLayout\ViewHelpers;

/*
 * This file is part of the package arndtteunissen/column-layout.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

use TYPO3Fluid\Fluid\ViewHelpers\SectionViewHelper;

/**
 * ViewHelper used to define grid system templates (i.e. Row, Column).
 * It's just a SectionViewHelper but allows custom modification of the actual template rendering or parsing.
 * This ViewHelper is be used to define the templates rather than the SectionViewHelper to avoid future conflicts or
 * breaking changes when the rendering of the templates is changed.
 */
class TemplateViewHelper extends SectionViewHelper
{
}
