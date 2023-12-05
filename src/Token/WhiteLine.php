<?php
/**
 * -- PHP Htaccess Parser --
 * WhiteLine.php created at 02-12-2014.
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

use Stringable;

/**
 * Class WhiteLine
 * A Token corresponding to a white line (blank line) segment of .htaccess.
 *
 * @copyright 2014 Estevão Soares dos Santos
 */
class WhiteLine extends BaseToken {

    /**
         * Get a string representation of the Token.
         */
    public function __toString(): string {
        return '';
    }

    /**
     * Get the Token's name.
     */
    public function getName(): string {
        return 'WhiteLine';
    }

    /**
     * Get the Token's type.
     */
    public function getTokenType(): int {
        return TokenInterface::TOKEN_WHITELINE;
    }

    /**
     * Specify data which should be serialized to JSON.
     *
     * @see http://php.net/manual/en/jsonserializable.jsonserialize.php
     *
     * @return mixed data which can be serialized by <b>json_encode</b>,
     *               which is a value of any type other than a resource
     */
    public function jsonSerialize(): mixed {
        return '';
    }

    /**
     * Get the array representation of the Token.
     */
    public function toArray(): array {
        return [
            'type' => $this->getTokenType(),
            'WhiteLine' => '',
        ];
    }

    /**
     * Get the Token's arguments.
     */
    public function getArguments(): array {
        return [''];
    }

    /**
     * Set the Token's arguments.
     *
     * @return $this
     */
    public function setArguments( string|Stringable ...$arguments ): static {
        return $this;
    }

    /**
     * A helper method that returns a string corresponding to the Token's value
     * (or its arguments concatenated).
     */
    public function getValue(): string {
        return '';
    }
}
