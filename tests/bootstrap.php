<?php
/**
 *
 * EventBoard extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 _Vinny_ <https://github.com/vinny>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

if (!file_exists(__DIR__ . '/../vendor/autoload.php')) {
	throw new \RuntimeException('Install dependencies using Composer before running tests.');
}

require_once __DIR__ . '/../vendor/autoload.php';
