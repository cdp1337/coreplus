<?php
/**
 * @license GNU Affero General Public License v3 <http://www.gnu.org/licenses/agpl-3.0.txt>
 * 
 * Copyright (C) 2009  Charlie Powell <powellc@powelltechs.com>
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

// Include the system bootstrap.
// This basically does everything.....
require_once('core/bootstrap.php');

// Anything that needs to fire off *before* the page is rendered.
// This includes widgets, script addons, and anything else that needs a CurrentPage.
HookHandler::DispatchHook('/core/page/prerender');

// Tell the hook handler that I'm ready to begin rendering of the page.
HookHandler::DispatchHook('/core/page/render');

// That's it!  If you're looking for something, chances are it'll be in the bootstrap file!