<?php
/**
 * Eresus Core
 *
 * @version 0.1.2
 *
 * Init module
 *
 * @copyright 2007-2009, Eresus Project, http://eresus.ru/
 * @license http://www.gnu.org/licenses/gpl.txt GPL License 3
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package Core
 * @author Mikhail Krasilnikov <mk@procreat.ru>
 *
 * $Id: eresus-core.php 410 2009-11-23 07:30:22Z mekras $
 */


/*
 * Ensure that we using supported PHP version
 */

/**
 * Required PHP version
 */
if ( !defined('ERESUS_CORE_PHP_VERSION') )
	define('ERESUS_CORE_PHP_VERSION', '5.2.1');

/* Check PHP version */
if (PHP_VERSION < ERESUS_CORE_PHP_VERSION)
	die(
		'Eresus Core: Invalid PHP version '.PHP_VERSION.
		'. Needed '.ERESUS_CORE_PHP_VERSION." or later.\n"
	);

if (!defined('ERESUS_CORE_USE_COMPILED'))
	define('ERESUS_CORE_USE_COMPILED', 'yes' == 'true' || 'yes' == 'yes');

/**
 * Including kernel
 */
require_once 'kernel.php';

Core::init();
