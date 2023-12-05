<?php

declare( strict_types=1 );

/**
 * -- PHP Htaccess Parser --
 * HtaccessContainer.php created at 03-12-2014.
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

namespace JazzMan\HtaccessParser;

use ArrayAccess;
use ArrayObject as BaseArrayObject;
use InvalidArgumentException;
use JazzMan\HtaccessParser\Token\Block;
use JazzMan\HtaccessParser\Token\Comment;
use JazzMan\HtaccessParser\Token\TokenInterface;
use JazzMan\HtaccessParser\Token\WhiteLine;

/**
 * Class HtaccessContainer
 * A generic ArrayObject that can be used to store a parsed htaccess. Implements JsonSerializable.
 *
 * @copyright 2014 Estevão Soares dos Santos
 */
class HtaccessContainer extends BaseArrayObject implements HtaccessInterface {

    private int $indentation = 4;

    private bool $ignoreWhiteLines = false;

    private bool $ignoreComments = false;

    /**
     * Create a new HtaccessContainer.
     *
     * @param array $array [optional] An array to populate the ArrayObject
     */
    public function __construct( array $array = [] ) {
        parent::__construct( $array );
    }

    /**
     * Get a string representation of this ArrayObject.
     *
     * @api
     */
    public function __toString(): string {
        return $this->txtSerialize();
    }

    /**
     * Set the indentation level.
     *
     * @param int $spaces [required] The number of spaces to indent lines
     *
     * @return $this
     */
    public function setIdentation( int $spaces ): static {

        $this->indentation = $spaces;

        return $this;
    }

    public function isIgnoreComments(): bool {
        return $this->ignoreComments;
    }

    /**
     * @return $this
     */
    public function setIgnoreComments( bool $ignoreComments ): static {
        $this->ignoreComments = $ignoreComments;

        return $this;
    }

    public function isIgnoreWhiteLines(): bool {
        return $this->ignoreWhiteLines;
    }

    /**
     * @return $this
     */
    public function setIgnoreWhiteLines( bool $ignoreWhiteLines ): self {
        $this->ignoreWhiteLines = $ignoreWhiteLines;

        return $this;
    }

    /**
     * Search this object for a Token with a specific name and return the first match.
     *
     * @param string   $name       [required] Name of the token
     * @param int|null $type       [optional] TOKEN_DIRECTIVE | TOKEN_BLOCK
     * @param bool     $deepSearch [optional] If the search should be multidimensional. Default is true
     *
     * @return TokenInterface|null Returns the Token or null if none is found
     */
    public function search( string $name, ?int $type = null, bool $deepSearch = true ): ?TokenInterface {
        /** @var TokenInterface[] $array */
        $array = $this->getArrayCopy();

        foreach ( $array as $token ) {
            if ( fnmatch( $name, $token->getName() ) ) {
                if ( null === $type ) {
                    return $token;
                }

                if ( $token->getTokenType() === $type ) {
                    return $token;
                }
            }

            if ( $token instanceof Block && $token->hasChildren() && $deepSearch ) {
                if ( $res = $this->deepSearch( $token, $name, $type ) ) {
                    return $res;
                }
            }
        }

        return null;
    }

    /**
     * Search this object for a Token with specific name and return the index(key) of the first match.
     *
     * @param string   $name [required] Name of the token
     * @param int|null $type [optional] TOKEN_DIRECTIVE | TOKEN_BLOCK
     *
     * @return int|null Returns the index or null if Token is not found
     */
    public function getIndex( string $name, ?int $type = null ): ?int {
        /** @var TokenInterface[] $array */
        $array = $this->getArrayCopy();

        foreach ( $array as $index => $token ) {
            if ( $token->getName() === $name ) {
                if ( null === $type ) {
                    return $index;
                }

                if ( $token->getTokenType() === $type ) {
                    return $index;
                }
            }
        }

        return null;
    }

    /**
     * Get a representation ready to be encoded with json_encoded.
     * Note: Whitelines and Comments are ignored and will not be included in the serialization.
     *
     * @return mixed data which can be serialized by <b>json_encode</b>,
     *               which is a value of any type other than a resource
     *
     * @see http://php.net/manual/en/jsonserializable.jsonserialize.php
     *
     * @api
     */
    public function jsonSerialize(): mixed {
        /** @var TokenInterface[] $array */
        $array = $this->getArrayCopy();
        $otp = [];

        foreach ( $array as $arr ) {
            if ( ! $arr instanceof WhiteLine & ! $arr instanceof Comment ) {
                $otp[$arr->getName()] = $arr;
            }
        }

        return $otp;
    }

    /**
     * Returns a representation of the htaccess, ready for inclusion in a file.
     *
     * @param int|null $indentation      [optional] Defaults to null
     * @param bool     $ignoreWhiteLines [optional] Defaults to null
     * @param bool     $ignoreComments   [optional] Defaults to null
     *
     * @api
     */
    public function txtSerialize( ?int $indentation = null, ?bool $ignoreWhiteLines = null, bool $ignoreComments = false ): string {
        /** @var TokenInterface[] $array */
        $array = $this->getArrayCopy();
        $otp = '';

        $this->indentation = ( null === $indentation ) ? $this->indentation : $indentation;
        $ignoreWhiteLines = ( null === $ignoreWhiteLines ) ? $this->ignoreWhiteLines : $ignoreWhiteLines;
        $ignoreComments = ( null === $ignoreComments ) ? $this->ignoreComments : $ignoreComments;

        foreach ( $array as $num => $token ) {
            $otp .= $this->txtSerializeToken( $token, 0, $ignoreWhiteLines, $ignoreComments );
        }

        // remove whitelines at the end
        $otp = rtrim( $otp );
        // and add an empty newline
        $otp .= PHP_EOL;

        return $otp;
    }

    /**
     * Returns the sequence of elements as specified by the offset and length parameters.
     *
     * @param int      $offset       [required] If offset is non-negative, the sequence will start at that offset.
     *                               If offset is negative, the sequence will start that far from the end of the
     *                               array.
     * @param int|null $length       [optional] If length is given and is positive, then the sequence will have up to that
     *                               many elements in it. If the array is shorter than the length, then only the
     *                               available array elements will be present. If length is given and is negative
     *                               then the sequence will stop that many elements from the end of the array.
     *                               If it is omitted, then the sequence will have everything from offset up until
     *                               the end of the array.
     * @param bool     $preserveKeys [optional] Note that arraySlice() will reorder and reset the numeric array indices by
     *                               default. You can change this behaviour by setting preserveKeys to TRUE.
     * @param bool     $asArray      [optional] By default, slice() returns a new instance of HtaccessContainer object.
     *                               If you prefer a basic array instead, set asArray to true
     */
    public function slice( int $offset, ?int $length = null, bool $preserveKeys = false, bool $asArray = false ): array|self {

        $array = $this->getArrayCopy();

        $newArray = \array_slice( $array, $offset, $length, $preserveKeys );

        return $asArray ? $newArray : new self( $newArray );
    }

    /**
     * @param int            $offset [required] If offset is positive then the token will be inserted at that offset from the
     *                               beginning. If offset is negative then it starts that far from the end of the input
     *                               array.
     * @param TokenInterface $token  [required] The token to insert
     *
     * @return $this
     */
    public function insertAt( int $offset, TokenInterface $token ): self {

        $this->splice( $offset, 0, [$token] );

        return $this;
    }

    /**
     * Removes the elements designated by offset and length, and replaces them with the elements of the replacement
     * array, if supplied.
     *
     * @param int               $offset      [required] If offset is positive then the start of removed portion is at that offset
     *                                       from the beginning. If offset is negative then it starts that far from the
     *                                       end of the input array.
     * @param int|null          $length      [optional] If length is omitted, removes everything from offset to the end. If length
     *                                       is specified and is positive, then that many elements will be removed.
     *                                       If length is specified and is negative then the end of the removed portion
     *                                       will be that many elements from the end.
     *                                       Tip: to remove everything from offset to the end of the array when
     *                                       replacement is also specified, use count($input) for length.
     * @param array|ArrayAccess $replacement [optional] If replacement array is specified, then the removed elements
     *                                       are replaced with elements from this array
     *
     * @return array returns the array consisting of the extracted elements
     */
    public function splice( int $offset, ?int $length = null, array|ArrayAccess $replacement = [] ): array {

        $array = $this->getArrayCopy();
        $spliced = array_splice( $array, $offset, $length, $replacement );
        $this->exchangeArray( $array );

        return $spliced;
    }

    /**
     * @inheritDocs
     *
     * @override ArrayObject::offsetSet
     *
     * @throws InvalidArgumentException
     */
    public function offsetSet( mixed $offset, mixed $value ): void {
        if ( null !== $offset && ! \is_int( $offset ) ) {
            throw new InvalidArgumentException( 'integer', 0 );
        }

        if ( ! $value instanceof TokenInterface ) {
            throw new InvalidArgumentException( 'TokenInterface', 1 );
        }
        parent::offsetSet( $offset, $value );
    }

    private function deepSearch( Block $parent, $name, $type ) {
        foreach ( $parent as $token ) {
            if ( fnmatch( $name, $token->getName() ) ) {
                if ( null === $type ) {
                    return $token;
                }

                if ( $token->getTokenType() === $type ) {
                    return $token;
                }
            }

            if ( $token instanceof Block && $token->hasChildren() ) {
                if ( $res = $this->deepSearch( $token, $name, $type ) ) {
                    return $res;
                }
            }
        }

        return null;
    }

    private function txtSerializeToken( TokenInterface $token, int $indentation, bool $ignoreWhiteLines, bool $ignoreComments ): string {

        $ind = str_repeat( ' ', $indentation );

        if ( $token instanceof Block ) {
            return $this->blockToString( $token, $indentation, $ignoreWhiteLines, $ignoreComments );

        }

        if ( $token instanceof WhiteLine ) {
            return ( $ignoreWhiteLines ) ? '' : PHP_EOL;

        }

        if ( $token instanceof Comment ) {
            return ( $ignoreComments ) ? '' : $ind.$token.PHP_EOL;

        }

        return $ind.$token.PHP_EOL;

    }

    private function blockToString( Block $block, int $indentation, bool $ignoreWhiteLines, bool $ignoreComments ): string {
        $otp = '';

        // Calculate indentation
        $ind = str_repeat( ' ', $indentation );

        // Opening Tag
        $otp .= $ind.'<'.$block->getName();

        // Arguments list
        foreach ( $block->getArguments() as $arg ) {
            $otp .= " {$arg}";
        }
        $otp .= '>'.PHP_EOL;

        if ( $block->hasChildren() ) {
            foreach ( $block as $child ) {
                $otp .= $this->txtSerializeToken( $child, $indentation + $this->indentation, $ignoreWhiteLines, $ignoreComments );
            }
        }

        // Closing tag
        $otp .= $ind.'</'.$block->getName().'>'.PHP_EOL;

        return $otp;
    }
}
