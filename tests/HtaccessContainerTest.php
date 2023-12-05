<?php
/**
 * -- PHP Htaccess Parser --
 * HtaccessContainerTest.php created at 05-12-2014.
 *
 * Copyright 2014 Estevão Soares dos Santos
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace JazzMan\HtaccessParserTest;

use AllowDynamicProperties;
use Exception;
use InvalidArgumentException;
use JazzMan\HtaccessParser\HtaccessContainer;
use JazzMan\HtaccessParser\Token\Block;
use JazzMan\HtaccessParser\Token\Comment;
use JazzMan\HtaccessParser\Token\Directive;
use JazzMan\HtaccessParser\Token\TokenInterface;
use JazzMan\HtaccessParser\Token\WhiteLine;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @internal
 *
 * @coversNothing
 *
 * @property HtaccessContainer $testClass
 */
#[AllowDynamicProperties]
final class HtaccessContainerTest extends BaseTestCase {

    public TokenInterface $genericToken;

    protected function setUp(): void {
        $this->testClass = new HtaccessContainer();
        $this->genericToken = $this->createTokenMock();
        parent::setUp();
    }

    /**
     * @covers \HtaccessContainer::offsetSet
     */
    public function testOffsetSet(): void {
        $htaccess = new HtaccessContainer();
        $token = $this->createTokenMock( 1 );
        $htaccess->offsetSet( null, $token );
        self::assertContains( $token, $htaccess );
        self::assertSame( $token, $htaccess[0] );

        $token = $this->createTokenMock();
        $htaccess[] = $token;
        self::assertContains( $token, $htaccess );
        self::assertSame( $token, $htaccess[1] );

        $htaccess = new HtaccessContainer();
        $htaccess->offsetSet( 5, $token );
        self::assertSame( $token, $htaccess[5] );

        $token = $this->createTokenMock( 2 );
        $htaccess[5] = $token;
        self::assertContains( $token, $htaccess );
        self::assertSame( $token, $htaccess[5] );
    }

    /**
     * @covers \HtaccessContainer::offsetSet
     * @covers \InvalidArgumentException
     */
    public function testOffsetSet2(): void {
        $this->expectException( InvalidArgumentException::class );
        $this->testClass[] = 'foobar';
    }

    /**
     * @covers \HtaccessContainer::offsetSet
     * @covers \InvalidArgumentException
     */
    public function testOffsetSet3(): void {
        $this->expectException( InvalidArgumentException::class );
        $this->testClass['foo'] = $this->createTokenMock();
    }

    /**
     * @covers \HtaccessContainer::search
     */
    public function testSearch(): void {
        $this->testClass[] = $this->createTokenMock();
        $this->testClass[] = $this->createTokenMock();

        $mock = $this->getMockBuilder( Block::class )
            ->onlyMethods( ['getName', 'getTokenType', 'hasChildren'] )
            ->getMock()
        ;

        $mock->expects( self::any() )
            ->method( 'getName' )
            ->willReturn( 'fooBlock' )
        ;

        $mock->expects( self::any() )
            ->method( 'getTokenType' )
            ->willReturn( TokenInterface::TOKEN_BLOCK )
        ;

        $mock->expects( self::any() )
            ->method( 'hasChildren' )
            ->willReturn( true )
        ;

        $mockChild = $this->getMockBuilder( Directive::class )
            ->onlyMethods( ['getName', 'getTokenType'] )
            ->getMock()
        ;

        $mockChild->expects( self::any() )
            ->method( 'getName' )
            ->willReturn( 'fooDirective' )
        ;

        $mock[] = $this->testClass[] = $this->createTokenMock();
        $mock[] = $this->testClass[] = $this->createTokenMock();
        $mock[] = $mockChild;

        $this->testClass[] = $mock;

        self::assertSame( $mock, $this->testClass->search( 'fooBlock', TokenInterface::TOKEN_BLOCK ), 'Search method failed to return the correct token' );
        self::assertSame( $mockChild, $this->testClass->search( 'fooDirective' ), 'Search method failed to return the correct token' );
    }

    /**
     * @covers \HtaccessContainer::slice
     */
    public function testSlice(): void {
        $htaccess = $this->fillContainer( $this->testClass, 6, true );

        $offset = 2;

        $length = 3;

        $presKeys = false;

        $array = $htaccess->getArrayCopy();
        $expArr = \array_slice( $array, $offset, $length, $presKeys );

        $htaccessContainer = new HtaccessContainer( $expArr );

        self::assertSame( $expArr, $htaccess->slice( $offset, $length, $presKeys, true ) );
        self::assertEquals( $htaccessContainer, $htaccess->slice( $offset, $length, $presKeys ) );
    }

    /**
     * @covers \HtaccessContainer::splice
     */
    public function testSplice(): void {
        $tokenM1 = $this->genericToken;
        $tokenM2 = $this->createTokenMock( TokenInterface::TOKEN_BLOCK );
        $htaccess = $this->fillContainer( $this->testClass );
        $array = [$tokenM2, $tokenM2, $tokenM2];
        $max = is_countable($htaccess) ? \count( $htaccess ) : 0;
        $spliced = $htaccess->splice( 1, $max, $array );

        self::assertSame( $tokenM1, $htaccess[0] );
        self::assertNotSame( $tokenM1, $htaccess[1] );
        self::assertNotSame( $tokenM1, $htaccess[2] );
        self::assertNotSame( $tokenM1, $htaccess[3] );

        self::assertNotSame( $tokenM2, $htaccess[0] );
        self::assertSame( $tokenM2, $htaccess[1] );
        self::assertSame( $tokenM2, $htaccess[2] );
        self::assertSame( $tokenM2, $htaccess[3] );

        $expReturn = [];

        for ( $i = 0; $max - 1 > $i; ++$i ) {
            $expReturn[] = $tokenM1;
        }

        self::assertSame( $expReturn, $spliced );
    }

    /**
     * @covers \HtaccessContainer::insertAt
     */
    public function testInsertAt(): void {
        $tokenM1 = $this->createTokenMock();
        $testToken = $this->createTokenMock( TokenInterface::TOKEN_DIRECTIVE );

        $htaccess = $this->testClass;
        $htaccess[0] = $tokenM1;
        $htaccess[1] = $tokenM1;
        $htaccess[2] = $tokenM1;
        $htaccess[3] = $tokenM1;

        $htaccess->insertAt( 2, $testToken );

        self::assertContains( $testToken, $htaccess, 'Token was not inserted in HtaccessContainer object' );
        self::assertSame( $testToken, $htaccess[2], 'Token was not inserted in HtaccessContainer at the correc index' );
        self::assertNotSame( $tokenM1, $htaccess[2], 'Failed asserting that TestToken is different from tokenM1' );
    }

    private function fillContainer( mixed $htaccess = null, int $num = 6, bool $rand = false )
    {
        $htaccess = ( $htaccess ) ?: $this->testClass;

        for ( $i = 0; $i < $num; ++$i ) {
            if ( $rand ) {
                try {
                    $htaccess[] = $this->createTokenMock( random_int( 0, 4 ) );
                } catch ( Exception ) {
                }
            } else {
                $htaccess[] = $this->genericToken;
            }
        }

        return $htaccess;
    }

    private function createTokenMock( ?int $type = null ): Block|Comment|Directive|MockObject|TokenInterface|WhiteLine {

        if ( null === $type ) {
            return $this->getMockBuilder( TokenInterface::class )
                ->getMock()
            ;
        }

        return match ( $type ) {
            TokenInterface::TOKEN_DIRECTIVE => $this->getMockBuilder( Directive::class )
                ->getMock(),
            TokenInterface::TOKEN_BLOCK => $this->getMockBuilder( Block::class )
                ->getMock(),
            TokenInterface::TOKEN_COMMENT => $this->getMockBuilder( Comment::class )
                ->getMock(),
            TokenInterface::TOKEN_WHITELINE => $this->getMockBuilder( WhiteLine::class )
                ->getMock(),
            default => $this->getMockBuilder( TokenInterface::class )
                ->getMock()
        };
    }
}
