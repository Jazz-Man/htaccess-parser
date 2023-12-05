<?php
/**
 * Created by PhpStorm.
 * Date: 03-12-2014
 * Time: 00:51.
 */

namespace JazzMan\HtaccessParserTest\Token;

use JazzMan\HtaccessParser\Token\Directive;
use JazzMan\HtaccessParser\Token\TokenInterface;
use JazzMan\HtaccessParserTest\BaseTestCase;


/**
 * Class DirectiveTest.
 *
 * @covers Directive
 *
 * @author Estevão Soares dos Santos
 *
 * @property Directive $testClass
 *
 * @internal
 */
final class DirectiveTest extends BaseTestCase {

    public string $key = 'myDirective';

    protected function setUp(): void {
        $this->testClass = new Directive( $this->key );
        parent::setUp();
    }

    /**
     * @covers Directive::getName
     */
    public function testGetName(): void {
        self::assertSame( $this->key, $this->testClass->getName() );
    }

    /**
     * @covers Directive::setName
     */
    public function testSetName(): void {
        $newKey = 'myNewDIrective';
        $this->testClass->setName( $newKey );

        self::assertSame( $newKey, $this->testClass->getName() );
    }

    /**
     * @covers Directive::getTokenType
     */
    public function testGetTokenType(): void {
        self::assertSame( TokenInterface::TOKEN_DIRECTIVE, $this->testClass->getTokenType() );
    }

    /**
     * @covers Directive::jsonSerialize
     */
    public function testJsonSerialize(): void {
        $args = ['foo', 'bar', 'baz'];
        $this->setProperty( 'arguments', $args );

        $expectedOtp = json_encode( $args );
        self::assertSame( $expectedOtp, json_encode( $this->testClass ) );

    }

    /**
     * @covers Directive::__toString
     */
    public function testToString(): void {
        $args = ['foo', 'bar', 'baz'];
        $this->setProperty( 'arguments', $args );

        $expectedOtp = "{$this->key} foo bar baz";
        self::assertSame( $expectedOtp, (string) $this->testClass, 'Casting Directive to string does not produce the expected value' );
    }

    /**
     * @covers Directive::getArguments
     * @covers Directive::setArguments
     */
    public function testSetGetArguments(): void {
        $args = ['foo', 'bar', 'baz'];
        $this->testClass->setArguments( ... $args );
        self::assertSame( $args, $this->testClass->getArguments() );
    }

    /**
     * @covers Directive::addArgument
     * @covers Directive::removeArgument
     */
    public function testAddRemoveArgument(): void {
        $arg = 'foo';
        $this->testClass->addArgument( $arg );
        self::assertContains( $arg, $this->testClass->getArguments(), 'Argument was not added successfully' );

        $this->testClass->removeArgument( 'bar' );
        self::assertContains( $arg, $this->testClass->getArguments(), 'Argument was removed indecently' );

        $this->testClass->removeArgument( $arg );
        self::assertNotContains( $arg, $this->testClass->getArguments(), 'Argument was not removed' );
    }
}
