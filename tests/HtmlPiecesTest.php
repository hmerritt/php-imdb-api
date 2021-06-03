<?php

use PHPUnit\Framework\TestCase;
use hmerritt\HtmlPieces;

class HtmlPiecesTest extends TestCase {

    public function test_unwrapFormattedNumber()
    {
        $htmlPieces = new HtmlPieces;

        $this->assertEquals("1500000", $htmlPieces->unwrapFormattedNumber("1.5M"));
        $this->assertEquals("1200", $htmlPieces->unwrapFormattedNumber("1.2K"));
        $this->assertEquals("1.8", $htmlPieces->unwrapFormattedNumber("1.8"));
        // $this->assertEquals($expected, $actual);
    }

}
