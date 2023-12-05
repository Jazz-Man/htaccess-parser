<?php
/**
 * Created by PhpStorm.
 * Date: 03-12-2014
 * Time: 11:08.
 */

namespace JazzMan\HtaccessParserTest\Token;

use JazzMan\HtaccessParser\Token\Comment;
use JazzMan\HtaccessParser\Token\TokenInterface;
use JazzMan\HtaccessParserTest\BaseTestCase;

/**
 * Class CommentTest.
 *
 * @covers Comment
 *
 * @author Estevão Soares dos Santos
 *
 * @property Comment $testClass
 *
 * @internal
 */
final class CommentTest extends BaseTestCase {

    protected function setUp(): void {
        $this->testClass = new Comment();
        parent::setUp();
    }

    /**
     * @covers Comment::getName
     */
    public function testGetName(): void {
        $name = '#comment';
        self::assertSame( $name, $this->testClass->getName() );
    }

    /**
     * @covers Comment::getTokenType
     */
    public function testGetTokenType(): void {
        self::assertSame( TokenInterface::TOKEN_COMMENT, $this->testClass->getTokenType() );
    }

    /**
     * @covers Comment::__toString
     * @covers Comment::jsonSerialize
     */
    public function testJsonSerializeAnToString(): void {
        $text = 'This is a comment';
        $this->setProperty( 'text', $text );

        $expectedOtp = json_encode( $text );
        self::assertSame( $text, (string) $this->testClass, 'Casting Comment to string does not produce the expected value' );
        self::assertSame( $expectedOtp, json_encode( $this->testClass ) );

    }

    /**
     * @covers Comment::getText
     * @covers Comment::setText
     */
    public function testSetGetText(): void {
        $text = 'This is a comment';
        $this->testClass->setText( $text );
        self::assertSame( '# '.$text, $this->testClass->getText() );
    }
}
