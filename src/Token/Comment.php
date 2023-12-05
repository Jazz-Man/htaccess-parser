<?php
/**
 * -- PHP Htaccess Parser --
 * Comment.php created at 02-12-2014.
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
 * Class Comment
 * A Token corresponding to a comment segment of .htaccess.
 *
 * @copyright 2014 Estevão Soares dos Santos
 */
class Comment extends BaseToken {

    /**
         * Create a new Comment token.
         *
         * This token corresponds to the following structure in .htaccess:
         * # ...
         *
         * @param string $text The comment text
         */
    public function __construct( private string $text = '' ) {}

    public function __toString(): string {
        return $this->text;
    }

    /**
     * Get the Token's name.
     * Always returns '#comment', since comments don't have a specific name.
     */
    public function getName(): string {
        return '#comment';
    }

    /**
     * Get the comment's text.
     */
    public function getText(): string {
        return $this->text;
    }

    /**
     * Set the Comment Text.
     *
     * @param string $text The comment new text. A # will be prepended automatically if it isn't found at the beginning
     *                     of the string.
     *
     * @return $this
     */
    public function setText( string $text ): static {

        $text = trim( $text );

        if ( ! str_starts_with( $text, '#' ) ) {
            $text = '# '.$text;
        }

        $this->text = $text;

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
        return $this->__toString();
    }

    /**
     * Get the Token's type.
     */
    public function getTokenType(): int {
        return TokenInterface::TOKEN_COMMENT;
    }

    /**
     * Get the array representation of the Token.
     */
    public function toArray(): array {
        return [
            'type' => $this->getTokenType(),
            'comment' => $this->text,
        ];
    }

    /**
     * Get the Token's arguments.
     */
    public function getArguments(): array {
        return [$this->getText()];
    }

    /**
     * Set the Token's arguments.
     *
     * @param array $arguments
     *
     * @return $this
     */
    public function setArguments( string|Stringable ...$arguments ): static {
        $this->setText( $arguments[0] );

        return $this;
    }

    /**
     * A helper method that returns a string corresponding to the Token's value
     * (or its arguments concatenated).
     */
    public function getValue(): string {
        return $this->getText();
    }
}
