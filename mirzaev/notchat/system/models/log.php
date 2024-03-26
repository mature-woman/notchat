<?php

declare(strict_types=1);

namespace mirzaev\notchat\models;

// Files of the project
use mirzaev\notchat\models\enumerations\log as type;

// Built-in libraries
use exception;

/**
 * Log
 *
 * @package mirzaev\notchat\models
 * @author Arsen Mirzaev Tatyano-Muradovich <arsen@mirzaev.sexy>
 */
class log extends core
{
	/**
	 * Path to DNS of storaged servers
	 */
	final public const LOGS = core::STORAGE . DIRECTORY_SEPARATOR . 'logs';

	/**
	 * Write
	 *
	 * @param type $type Type of log
	 * @param string $value Text to write
	 * @param array &$errors Buffer of errors
	 *
	 * @return void
	 *
	 * @todo 
	 * 1. Dividing magazines based on volume achieved
	 */
	public static function write(type $type, string $value, &$errors = []): void
	{
		try {
			// Initializing of path to the file of the log
			$path = static::LOGS . DIRECTORY_SEPARATOR . $type->value;

			// Open file of the log
			$log = fopen($path, 'a');

			if (flock($log, LOCK_EX)) {
				// File locked

				// Initializing of date
				$date = date_format(date_create(), 'Y.m.d H:i:s');

				// Write to the log
				fwrite($log, (filesize($path) === 0 ? '' : PHP_EOL) . "[$date] $value");

				// Apply changes
				fflush($log);

				// Unlock file
				flock($log, LOCK_UN);
			}
		} catch (exception $e) {
			// Write to buffer of errors
			$errors[] = [
				'text' => $e->getMessage(),
				'file' => $e->getFile(),
				'line' => $e->getLine(),
				'stack' => $e->getTrace()
			];
		}
	}
}
