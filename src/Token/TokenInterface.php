<?php
/**
 * -- PHP Htaccess Parser --
 * TokenInterface.php created at 02-12-2014.
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

namespace JazzMan\HtaccessParser\Token;

use JsonSerializable;
use Stringable;

/**
 * Interface TokenInterface
 * An Interface for Tokens to implement.
 *
 * @copyright 2014 Estevão Soares dos Santos
 */
interface TokenInterface extends JsonSerializable {

    public const TOKEN_DIRECTIVE = 0;
    public const TOKEN_BLOCK = 1;
    public const TOKEN_COMMENT = 2;
    public const TOKEN_WHITELINE = 3;

    /**
     * Get a string representation of the Token.
     */
    public function __toString(): string;

    /**
     * Get the Token's type.
     */
    public function getTokenType(): int;

    /**
     * Get the Token's name.
     */
    public function getName(): string;

    /**
     * Get the Token's arguments.
     */
    public function getArguments(): array;

    /**
     * Set the Token's arguments.
     *
     * @return $this
     */
    public function setArguments( string|Stringable ...$arguments ): static;

    /**
     * A helper method that returns a string corresponding to the Token's value
     * (or its arguments concatenated).
     */
    public function getValue(): string;

    /**
     * Check if this Token spawns across multiple lines.
     */
    public function isMultiLine(): bool;

    /**
     * Get the line breaks.
     *
     * @return int[]
     */
    public function getLineBreaks(): array;

    /**
     * Set the line breaks.
     *
     * @param int[] $lineBreaks Array of integers
     *
     * @return $this
     */
    public function setLineBreaks( int ...$lineBreaks ): static;

    /**
     * @return $this
     */
    public function addLineBreak( int $lineBreak ): static;

    /**
     * Get the array representation of the Token.
     */
    public function toArray(): array;
}
