<?php

declare( strict_types=1 );

/**
 * -- PHP Htaccess Parser --
 * Directive.php created at 02-12-2014.
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

/**
 * Class Directive
 * A Token corresponding to a directive segment of htaccess.
 *
 * @copyright 2014 Estevão Soares dos Santos
 */
class Directive extends BaseToken {

    private array $arguments = [];

    /**
     * @param string|null $name      [optional]
     * @param array       $arguments [optional]
     */
    public function __construct( private ?string $name = null, string ...$arguments ) {

        foreach ( $arguments as $arg ) {

            $this->arguments[] = $arg;
        }
    }

    public function __toString(): string {
        $str = $this->getName();

        foreach ( $this->arguments as $arg ) {
            $str .= " {$arg}";
        }

        return $str;
    }

    /**
     * Get the Token's name.
     */
    public function getName(): string {
        return $this->name;
    }

    /**
     * Set the Token's name.
     *
     * @return $this
     */
    public function setName( string $name ): static {
        $this->name = $name;

        return $this;
    }

    /**
     * Get the Directive's arguments.
     */
    public function getArguments(): array {
        return $this->arguments;
    }

    /**
     * Set the Directive's arguments.
     *
     * @return $this
     */
    public function setArguments( string ...$arguments ): static {
        foreach ( $arguments as $arg ) {

            $this->addArgument( $arg );
        }

        return $this;
    }

    /**
     * Add an argument to the Directive arguments array.
     *
     * @param mixed $arg    [required] A scalar
     * @param bool  $unique [optional] If this argument is unique
     *
     * @return $this
     */
    public function addArgument( string $arg, bool $unique = false ): static {

        // escape arguments with spaces
        if ( str_contains( $arg, ' ' ) && ( ! str_contains( $arg, '"' ) ) ) {
            $arg = "\"{$arg}\"";
        }

        if ( \in_array( $arg, $this->arguments, true ) && $unique ) {
            return $this;
        }

        $this->arguments[] = $arg;

        return $this;
    }

    /**
     * Remove an argument from the Directive's arguments array.
     *
     * @return $this
     */
    public function removeArgument( string $arg ) {
        if ( false !== ( $name = array_search( $arg, $this->arguments, true ) ) ) {
            unset( $this->arguments[$name] );
        }

        return $this;
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
        return $this->arguments;
    }

    /**
     * Get the Token's type.
     */
    public function getTokenType(): int {
        return TokenInterface::TOKEN_DIRECTIVE;
    }

    /**
     * Get the array representation of the Token.
     */
    public function toArray(): array {
        return [
            'type' => $this->getTokenType(),
            'name' => $this->getName(),
            'arguments' => $this->getArguments(),
        ];
    }

    /**
     * A helper method that returns a string corresponding to the Token's value
     * (or its arguments concatenated).
     */
    public function getValue(): string {
        return implode( ' ', $this->getArguments() );
    }
}
