<?php

use PHPUnit\Framework\TestCase;

class ExampleTest extends TestCase
{
    public function testAddNumbers()
    {
        $a = 2;
        $b = 3;
        $this->assertEquals(5, $a + $b);
    }
}
