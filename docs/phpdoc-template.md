## PHPDoc Inline Documentation Template

Use PHPDoc-style inline documentation for your code.  Below is an example of a file header.

    <?php
    /**
     * File for class ExampleClass.
     *
     * @package Example
     * @author Author <email@domain.tld>
     * @copyright Copyright (C) 2009-2012  Author
     * @license GNU Affero General Public License v3 <http://www.gnu.org/licenses/agpl-3.0.txt>
     *
     * This program is free software: you can redistribute it and/or modify
     * it under the terms of the GNU Affero General Public License as
     * published by the Free Software Foundation, version 3.
     *
     * This program is distributed in the hope that it will be useful,
     * but WITHOUT ANY WARRANTY; without even the implied warranty of
     * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
     * GNU Affero General Public License for more details.
     *
     * You should have received a copy of the GNU Affero General Public License
     * along with this program.  If not, see http://www.gnu.org/licenses/agpl-3.0.txt.
     */

    /**
     * A short teaser of what ExampleClass does.
     *
     * More lengthy description of what ExampleClass does and why it's fantastic.
     *
     * <h3>Usage Examples</h3>
     *
     *
     * <h4>Example 1</h4>
     * <p>Description 1</p>
     * <code>
     * // Some code for example 1
     * $a = $b;
     * </code>
     *
     *
     * <h4>Example 2</h4>
     * <p>Description 2</p>
     * <code>
     * // Some code for example 2
     * $b = $a;
     * </code>
     *
     *
     * @package Example
     * @author Author <email@domain.tld>
     *
     */
    class ExampleClass {
    	// ...
    }

## Core Plus Packages

Core Plus functions should all be under the `@package Core` namespace.  If it is a top-level utility, simply leaving it at
Core is acceptable.  For sub-namespaces, `@package Core\Blah` is preferred, as appropriate.