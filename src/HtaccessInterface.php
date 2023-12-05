<?php
/**
 * -- PHP Htaccess Parser --
 * HtaccessInterface.php created at 03-12-2014.
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

use JsonSerializable;

/**
 * Interface HtaccessInterface.
 *
 * @copyright 2014 Estevão Soares dos Santos
 */
interface HtaccessInterface extends \ArrayAccess, \Countable, \IteratorAggregate, \Serializable, JsonSerializable {

    /**
         * Get a string representation of this ArrayObject.
         *
         * @api
         */
    public function __toString(): string;

    /**
     * Returns a representation of the htaccess, ready for inclusion in a file.
     *
     * @api
     */
    public function txtSerialize(): string;
}
