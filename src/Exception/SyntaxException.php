<?php

declare( strict_types=1 );

/**
 * -- PHP Htaccess Parser --
 * SyntaxException.php created at 02-12-2014.
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

namespace JazzMan\HtaccessParser\Exception;

/**
 * Class SyntaxException
 * An exception thrown if there's a syntax error in .htaccess.
 *
 * @copyright 2014 Estevão Soares dos Santos
 */
class SyntaxException extends \Exception {

	/**
	 * Create a new generic HtaccessParser Exception.
	 *
	 * @param  int  $lineNum  [required] Line number of the parsed file that raised the parse error
	 * @param  string  $line  [required] Line literal that raised the parse error
	 * @param  string  $message  [optional] Exception message
	 * @param  int  $code  [optional] Code of the exception
	 * @param  \Exception|null  $exception  [optional] Previous Exception
	 */
    public function __construct( int $lineNum, string $line, string $message = '', int $code = 0, ?\Exception $exception = null ) {

        $message = sprintf('Parsing Error in line %d: %s. %s', $lineNum, $line, $message);

        parent::__construct( $message, $code, $exception );
    }
}
