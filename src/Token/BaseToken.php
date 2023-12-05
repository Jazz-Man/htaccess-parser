<?php

declare( strict_types=1 );

/**
 * -- PHP Htaccess Parser --
 * BaseToken.php created at 03-12-2014.
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
 * Class BaseToken
 * An abstract class for Tokens to extend.
 *
 * @copyright 2014 Estevão Soares dos Santos
 */
abstract class BaseToken implements TokenInterface {

    protected array $lineBreaks = [];

    /**
     * Check if this Token spawns across multiple lines.
     */
    public function isMultiLine(): bool {
        return ! empty( $this->lineBreaks );
    }

    /**
     * Get the line breaks.
     *
     * @return int[]
     */
    public function getLineBreaks(): array {
        return $this->lineBreaks;
    }

    /**
     * Set the line breaks.
     *
     * @param int[] $lineBreaks Array of integers
     *
     * @return $this
     */
    public function setLineBreaks( int ...$lineBreaks ): static {
        foreach ( $lineBreaks as $lb ) {

            $this->lineBreaks[] = $lb;
        }

        return $this;
    }

    /**
     * Add linebreak.
     *
     * @return $this
     */
    public function addLineBreak( int $lineBreak ): static {

        $this->lineBreaks[] = $lineBreak;

        return $this;
    }
}
