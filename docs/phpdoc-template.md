## PHPDoc Inline Documentation Template

Use PHPDoc-style inline documentation for your code.  Below is an example of a file header for a class.

    <?php
    /**
     * File for class ExampleClass.
     *
     * @package Example
     * @author Author <email@domain.tld>
     * @copyright Copyright (C) 2009-2014  Author
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
     * ## Usage
     *
     * This class is used in XYZ ways and can do something.
     *
     * Class tags all start with as an H2 (two `#` signs).
     *
     * Example 1 does XYZ
     * <pre>
     * // Some code for example 1
     * $a = $b;
     * </pre>
     *
     * Example 2 does something too
     * <pre>
     * // Some code for example 2
     * $b = $a;
     * </pre>
     *
     *
     * @package Example
     * @author Author <email@domain.tld>
     *
     */
    class ExampleClass {
    	// ...

    	/**
    	 * Do some example thing (short 1-line description)
    	 *
    	 * This method does some example operation (long description)
    	 *
    	 * #### Usage
    	 *
    	 * Usage examples and other tags in methods all start with an H4 tag.
    	 *
    	 * @param string $param1 Param 1 does something useful,
    	 * and is important because foo (continuations start at the beginning of the line).
    	 */
    	public function example_method($param1){
    	    // do some logic here
    	}
    }

PHP functions are identical to the class file, except the top-level phpdoc, (for the function), mimics the formatting of a class's method, (use H4's instead of H2's).

## Core Plus Packages

Core Plus functions should all be under the `@package Core` namespace.  If it is a top-level utility, simply leaving it at
Core is acceptable.  For sub-namespaces, `@package Core\Blah` is preferred, as appropriate.