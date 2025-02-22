<?php
/**
 * -- PHP Htaccess Parser --
 * HtaccessContainerTest.php created at 06-12-2014.
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
use JazzMan\HtaccessParser\Parser;
use SplFileObject;

/**
 * @internal
 *
 * @coversNothing
 *
 * @property Parser $testClass
 */
#[AllowDynamicProperties]
final class LibraryCompositeTest extends BaseTestCase {

    public array $testCase = [];

    public int $numberOfTests = 2;

    protected function setUp(): void {
        $this->testClass = new Parser();
        $max = $this->numberOfTests;
        $basePath = __DIR__.'/resources/testcase';

        for ( $i = 1; $i <= $max; ++$i ) {
            $fname = sprintf('%s%d', $basePath, $i);
            $this->testCase[] = [
                'file' => new SplFileObject( sprintf('%s/htaccess', $fname) ),
                'txt' => [
                    0 => sprintf('%s/txt/normal.txt', $fname),
                    Parser::IGNORE_COMMENTS => sprintf('%s/txt/no_comments.txt', $fname),
                    Parser::IGNORE_WHITELINES => sprintf('%s/txt/no_whitelines.txt', $fname),
                    Parser::IGNORE_COMMENTS | Parser::IGNORE_WHITELINES => sprintf('%s/txt/no_comments_no_whitelines.txt', $fname),
                ],
            ];
        }

        parent::setUp();
    }

    /**
     * @covers \HtaccessContainer::__toString
     * @covers \HtaccessContainer::txtSerialize
     * @covers \Parser::ignoreComments
     * @covers \Parser::ignoreWhitelines
     * @covers \Parser::parse
     * @covers \Parser::setFile
     */
    public function testCompareToExample(): void {
        for ( $i = 0; $i < $this->numberOfTests; ++$i ) {
            $htaccessFile = $this->testCase[$i]['file'];
            $this->testClass->setFile( $htaccessFile );

            /**
             * @var int           $type
             * @var SplFileObject $file
             */
            foreach ( $this->testCase[$i]['txt'] as $type => $filename ) {
                $parsed = $this->testClass->setMode( $type )->parse();
                self::assertSame( file_get_contents( $filename ), $parsed->txtSerialize(), sprintf('Failed test (PARSE MODIFIED) with %s', $filename) );

                $parsed = $this->testClass->setMode( 0 )->parse();
                self::assertSame(
                    file_get_contents( $filename ),
                    $parsed->txtSerialize(
                        4,
                        Parser::IGNORE_WHITELINES & $type,
                        Parser::IGNORE_COMMENTS & $type
                    ),
                    sprintf('Failed test with %s', $filename)
                );
            }
        }
    }
}
