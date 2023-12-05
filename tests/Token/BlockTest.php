<?php
/**
 * Created by PhpStorm.
 * Date: 03-12-2014
 * Time: 11:16.
 */

namespace JazzMan\HtaccessParserTest\Token;

use JazzMan\HtaccessParser\Token\Block;
use JazzMan\HtaccessParser\Token\TokenInterface;
use JazzMan\HtaccessParserTest\BaseTestCase;

/**
 * Class BlockTest.
 *
 * @covers Block
 *
 * @author Estevão Soares dos Santos
 *
 * @property Block $testClass
 *
 * @internal
 */
final class BlockTest extends BaseTestCase {

    public string $blockName = 'SomeBlock';

    protected function setUp(): void {
        $this->testClass = new Block( $this->blockName );
        parent::setUp();
    }

    /**
     * @covers Block::getName
     */
    public function testSetGetName(): void {
        self::assertSame( $this->blockName, $this->testClass->getName(), 'Failed getting block name' );

        $name = 'SomeOtherBlock';
        $this->testClass->setName( $name );
        self::assertSame( $name, $this->getProperty( 'blockName' ), 'Failed setting block name' );
    }

    /**
     * @covers Block::getTokenType
     */
    public function testGetTokenType(): void {
        self::assertSame( TokenInterface::TOKEN_BLOCK, $this->testClass->getTokenType() );
    }

    /**
     * @covers Block::jsonSerialize
     */
    public function testJsonSerialize(): void {
        $expectedArray = [
            'arguments' => [],
            'children' => [],
        ];
        self::assertSame( $expectedArray, $this->testClass->jsonSerialize() );
    }

    /**
     * @covers Block::__toString
     */
    public function testToString(): void {
        $expectedString = "<SomeBlock>\n</SomeBlock>";
        self::assertSame( $expectedString, (string) $this->testClass );
    }

    /**
     * @covers Block::getArguments
     * @covers Block::setArguments
     */
    public function testSetGetArguments(): void {
        $args = [ 'foo', 'bar', 'baz' ];
        $this->testClass->setArguments( ...$args );
        self::assertSame( $args, $this->testClass->getArguments() );
    }

    /**
     * @covers Block::addArgument
     * @covers Block::removeArgument
     */
    public function testAddRemoveArgument(): void {
        $arg = 'foo';
        $this->testClass->addArgument( $arg );
        self::assertContains( $arg, $this->getProperty( 'arguments' ), 'Argument was not added successfully to Block' );

        $this->testClass->removeArgument( 'bar' );
        self::assertContains( $arg, $this->getProperty( 'arguments' ), 'Argument was removed indecently from Block' );

        $this->testClass->removeArgument( $arg );
        self::assertNotContains( $arg, $this->getProperty( 'arguments' ), 'Argument was not removed from Block (and it should)' );
    }

    /**
     * @covers Block::addChild
     */
    public function testAddChild(): void {
        $child = $this->getMockBuilder( TokenInterface::class )
            ->getMock()
        ;

        $this->testClass->addChild( $child );

        self::assertContains( $child, $this->getProperty( 'children' ), 'Child token WAS NOT added successfully' );
    }

    /**
     * @covers Block::removeChild
     */
    public function testRemoveChild(): void {
        $child = $this->getMockBuilder( TokenInterface::class )
            ->getMock()
        ;

        $notChild = $this->getMockBuilder( TokenInterface::class )
            ->getMock()
        ;

        $this->setProperty( 'children', [ $child ] );

        // Test equal removal
        $this->testClass->removeChild( $child );
        self::assertNotContains( $child, $this->getProperty( 'children' ), 'Test EQUAL removal: Child was NOT removed from block but it should' );

        $this->setProperty( 'children', [ $child ] );

        // Test strict removal
        $this->testClass->removeChild( $notChild, true );
        self::assertContains( $child, $this->getProperty( 'children' ), "Test STRICT removal: Child was removed from block but it shouldn't" );

        // Test loose removal
        $this->testClass->removeChild( $notChild, false );
        self::assertNotContains( $child, $this->getProperty( 'children' ), 'Test LOOSE removal: Child was NOT removed from block but it should' );

        $this->setProperty( 'children', [ $child ] );

        // Test loose removal 2
        $child->foo = 'bar';
        $this->testClass->removeChild( $notChild, false );
        self::assertContains( $child, $this->getProperty( 'children' ), "Test LOOSE removal 2: Child was removed from block but it shouldn't" );

        // Test loose removal 3
        $notChild->foo = 'bazinga';
        $this->testClass->removeChild( $notChild, false );
        self::assertContains( $child, $this->getProperty( 'children' ), "Test LOOSE removal 3: Child was removed from block but it shouldn't" );

        // Test loose removal 4
        $notChild->foo = 'bar';
        $this->testClass->removeChild( $notChild, false );
        self::assertNotContains( $child, $this->getProperty( 'children' ), 'Test LOOSE removal 4: Child was NOT removed from block but it should' );
    }
}
