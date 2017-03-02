<?php

namespace Html2Text;
use PHPUnit\Framework\TestCase;

class BasicTest extends TestCase
{
    public function testBasicUsageInReadme()
    {
        $html = new Html2Text('Hello, &quot;<b>world</b>&quot;');

        $this->assertEquals('Hello, "WORLD"', $html->getText());
    }
}
