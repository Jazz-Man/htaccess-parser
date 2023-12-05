<?php
/**
 * Created by PhpStorm.
 * Date: 03-12-2014
 * Time: 00:46.
 */

namespace JazzMan\HtaccessParserTest;

use JazzMan\HtaccessParser\Parser;

/**
 * Class ParserTest.
 *
 * @covers \JazzMan\HtaccessParser\Parser
 *
 * @author Estevão Soares dos Santos
 *
 * @property Parser $testClass
 *
 * @internal
 */
final class ParserTest extends BaseTestCase {

    protected function setUp(): void {
        $this->testClass = new Parser();
        parent::setUp();
    }

    // /////////////////////////////////////////////////////////////////////////
    // Check Methods Tests
    //

    /**
     * @covers \Parser::isWhiteLine
     */
    public function testIsWhiteLine(): void {
        $name = 'isWhiteLine';
        $method = $this->getMethod( $name );

        $this->regexTesterMaker( 'TRUE', $name, '' );
        $this->regexTesterMaker( 'TRUE', $name, '    ' );
        $this->regexTesterMaker( 'TRUE', $name, "\r\n\r\n\r\n" );
        $this->regexTesterMaker( 'TRUE', $name, "\r\n   \r\n" );
        $this->regexTesterMaker( 'TRUE', $name, "\r\n   \r\n   " );
        $this->regexTesterMaker( 'FALSE', $name, ' \\' );
        $this->regexTesterMaker( 'FALSE', $name, 'something' );
        $this->regexTesterMaker( 'FALSE', $name, '  something  ' );
        $this->regexTesterMaker( 'FALSE', $name, "\r\n   something \r\n   " );
    }

    /**
     * @covers \Parser::isMultiLine
     */
    public function testIsMultiLine(): void {
        $name = 'isMultiLine';
        $this->regexTesterMaker( 'FALSE', $name, '' );
        $this->regexTesterMaker( 'FALSE', $name, "\r\n   \r\n" );
        $this->regexTesterMaker( 'FALSE', $name, '# comment' );
        $this->regexTesterMaker( 'TRUE', $name, '  \ ' );
        $this->regexTesterMaker( 'TRUE', $name, '     \\' );
    }

    /**
     * @covers \Parser::isComment
     */
    public function testIsComment(): void {
        $name = 'isComment';
        $this->regexTesterMaker( 'FALSE', $name, '' );
        $this->regexTesterMaker( 'FALSE', $name, ' ' );
        $this->regexTesterMaker( 'FALSE', $name, '     \\' );
        $this->regexTesterMaker( 'FALSE', $name, "\r\n   \r\n" );
        $this->regexTesterMaker( 'FALSE', $name, 'not # a comment' );
        $this->regexTesterMaker( 'TRUE', $name, '# comment' );
    }

    /**
     * @covers \Parser::isDirective
     */
    public function testIsDirective(): void {
        $name = 'isDirective';
        $this->regexTesterMaker( 'FALSE', $name, '' );
        $this->regexTesterMaker( 'FALSE', $name, '# comment' );
        $this->regexTesterMaker( 'FALSE', $name, '<someBlock>' );
        $this->regexTesterMaker( 'FALSE', $name, '<someBlock with args>' );
        $this->regexTesterMaker( 'FALSE', $name, '# comment' );
        $this->regexTesterMaker( 'TRUE', $name, 'someDirective' );
        $this->regexTesterMaker( 'TRUE', $name, 'someDirective with args' );
        $this->regexTesterMaker( 'TRUE', $name, 'someDirective with #symbol' );
    }

    /**
     * @covers \Parser::isBlock
     */
    public function testIsBlock(): void {
        $name = 'isBlock';
        $this->regexTesterMaker( 'FALSE', $name, '' );
        $this->regexTesterMaker( 'FALSE', $name, '# comment' );
        $this->regexTesterMaker( 'FALSE', $name, 'someDirective' );
        $this->regexTesterMaker( 'FALSE', $name, 'someDirective with args' );
        $this->regexTesterMaker( 'FALSE', $name, '<some> block with bad args' );
        $this->regexTesterMaker( 'FALSE', $name, '</some> block with bad args' );
        $this->regexTesterMaker( 'FALSE', $name, '</someblock with args>' );
        $this->regexTesterMaker( 'FALSE', $name, '</ blockEnd>' );
        $this->regexTesterMaker( 'FALSE', $name, '</blockEnd >' );
        $this->regexTesterMaker( 'FALSE', $name, '<notClosed' );
        $this->regexTesterMaker( 'FALSE', $name, '</notClosed arg' );
        $this->regexTesterMaker( 'FALSE', $name, '</blockEnd>' );
        $this->regexTesterMaker( 'TRUE', $name, '<someBlock>' );
        $this->regexTesterMaker( 'TRUE', $name, '<someBlock with args>' );
    }

    /**
     * @covers \Parser::isBlockEnd
     */
    public function testIsBlockEnd(): void {
        $name = 'isBlockEnd';
        $this->regexTesterMaker( 'FALSE', $name, '' );
        $this->regexTesterMaker( 'FALSE', $name, '# comment' );
        $this->regexTesterMaker( 'FALSE', $name, 'someDirective' );
        $this->regexTesterMaker( 'FALSE', $name, 'someDirective with args' );
        $this->regexTesterMaker( 'FALSE', $name, '<someBlockThatIsNotClosed' );
        $this->regexTesterMaker( 'FALSE', $name, '</someBlockNotClosed' );
        $this->regexTesterMaker( 'FALSE', $name, '</someBlock Not Closed with args' );
        $this->regexTesterMaker( 'FALSE', $name, '<someBlock> with bad args' );
        $this->regexTesterMaker( 'FALSE', $name, '<someBlock>' );
        $this->regexTesterMaker( 'FALSE', $name, '<someBlock with args>' );
        $this->regexTesterMaker( 'FALSE', $name, '</someBlock> with bad args' );
        $this->regexTesterMaker( 'FALSE', $name, '</someBlock with bad args>' );
        $this->regexTesterMaker( 'TRUE', $name, '</someBlock>' );
    }

    /**
     * @covers \Parser::isBlockEnd
     */
    public function testIsBlockEndWithBlockNameParam(): void {
        $name = 'isBlockEnd';
        $method = $this->getMethod( $name );
        $blockName = 'MyBlock';

        $line = '</otherBlock>';
        self::assertFalse(
            $method->invoke( $this->testClass, $line, $blockName ),
            "Failed asserting that {$name} returns FALSE with {$line} (passing blockName {$blockName})"
        );

        $line = "</{$blockName}>";
        self::assertTrue(
            $method->invoke( $this->testClass, $line, $blockName ),
            "Failed asserting that {$name} returns TRUE with {$line} (passing blockName {$blockName})"
        );
    }

    // /////////////////////////////////////////////////////////////////////////
    // Parse Methods Tests
    //

    /**
     * @covers \Parser::blockRegex
     */
    public function testBlockRegex(): void {
        $name = 'blockRegex';
        $method = $this->getMethod( $name );

        $line = '<myBlock>';
        $expArray = ['myBlock'];
        self::assertSame( $expArray, $method->invoke( $this->testClass, $line ), "Block Regex failed for {$line}" );

        $line1 = '<myBlock with args>';
        $expArray1 = ['myBlock', 'with', 'args'];
        self::assertSame( $expArray1, $method->invoke( $this->testClass, $line1 ), "Block Regex failed for {$line1}" );

        $line2 = '<myBlock "with args" several indeed "and quoted">';
        $expArray2 = ['myBlock', '"with args"', 'several', 'indeed', '"and quoted"'];
        self::assertSame( $expArray2, $method->invoke( $this->testClass, $line2 ), "Block Regex failed for {$line2}" );
    }

    /**
     * @covers \Parser::directiveRegex
     */
    public function testDirectiveRegex(): void {
        $name = 'directiveRegex';
        $method = $this->getMethod( $name );

        $line = 'myDirective';
        $expArray = ['myDirective'];
        self::assertSame( $expArray, $method->invoke( $this->testClass, $line ), "Directive Regex failed for line: '{$line}'" );

        $line1 = 'myDirective with args';
        $expArray1 = ['myDirective', 'with', 'args'];
        self::assertSame( $expArray1, $method->invoke( $this->testClass, $line1 ), "Directive Regex failed for line: '{$line1}'" );

        $line2 = 'myDirective "with args" several indeed "and quoted"';
        $expArray2 = ['myDirective', '"with args"', 'several', 'indeed', '"and quoted"'];
        self::assertSame( $expArray2, $method->invoke( $this->testClass, $line2 ), "Directive Regex failed for line: '{$line2}'" );
    }

    // /////////////////////////////////////////////////////////////////////////
    // Helper Methods
    //
    /**
     * @coversNothing
     */
    private function regexTesterMaker( string $expectedBool, string $name, string $line, array $args = [] ): void {
        $bool = strtoupper( $expectedBool );
        $method = $this->getMethod( $name );
        $assertion = [];

        switch ( $bool ) {
            case 'TRUE':
                $assertion = [$this, 'assertTrue'];

                break;

            case 'FALSE':
                $assertion = [$this, 'assertFalse'];

                break;

            default:
                trigger_error( 'PHPUnit test ERROR: $expected error must be either the string TRUE or FALSE' );
        }

        array_unshift( $args, $line );

        \call_user_func_array(
            $assertion,
            [
                $method->invokeArgs( $this->testClass, $args ),
                "Failed asserting that {$name} returns {$bool} with {$line}",
            ]
        );
    }
}
