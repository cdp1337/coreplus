<?php
/**
 * General common exceptions used throughout Core
 *
 * @package Core
 * @author Charlie Powell <charlie@evalagency.com>
 * @copyright Copyright (C) 2009-2015  Charlie Powell
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
 * General Model exception thrown from (somewhere in theory, nowhere in practice).
 */
class ModelException extends Exception {

}

/**
 * Validation exceptions caught by the Model for incoming data.
 *
 * Usually sent back to end users for correction on form inputs.
 */
class ModelValidationException extends ModelException {

}

/**
 * General validation exceptions that can be thrown by a Form handler,
 * not necessarily from within a Model.
 *
 * These behave similarly to ModelValidationExceptions as they are displayed to the end user,
 * but they just have a different source.
 */
class GeneralValidationException extends Exception {

}

/**
 * Any exception thrown from within the Core component,
 * usually during application initializing.
 */
class CoreException extends Exception {

}