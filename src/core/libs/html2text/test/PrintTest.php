<?php

namespace Html2Text;
use PHPUnit\Framework\TestCase;

class PrintTest extends TestCase
{
	const TEST_HTML = 'Hello, &quot;<b>world</b>&quot;';
	const EXPECTED = 'Hello, "WORLD"';

	public function setUp() {
        $this->html = new Html2Text(self::TEST_HTML);
        $this->expectOutputString(self::EXPECTED);		
	}

    public function testP()
    {
        $this->html->p();
    }

    public function testPrint_text()
    {
        $this->html->print_text();
    }
}
