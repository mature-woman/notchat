<?php

declare(strict_types=1);

namespace mirzaev\notchat\models;

// Framework for PHP
use mirzaev\minimal\model;

// Built-in libraries
use exception,
	DirectoryIterator as parser;

/**
 * Core of models
 *
 * @package mirzaev\notchat\controllers
 * @author Arsen Mirzaev Tatyano-Muradovich <arsen@mirzaev.sexy>
 */
class server extends core
{
	/**
	 * Path to storage of servers
	 */
	final public const SERVERS = core::STORAGE . DIRECTORY_SEPARATOR . 'servers';

	/**
	 * Write
	 *
	 * Create the file with server settings
	 *
	 * @param string $hash Name of the file (unique hash)
	 * @param string $json Data of the server with JSON format
	 * @param array $errors Buffer of errors
	 *
	 * @return void
	 */
	public static function write(string $hash, string $json = '', array &$errors = []): void
	{
		try {
			if (strlen($hash) > 512) throw new exception('Hash cannot be longer than 512 characters');

			$file = fopen(static::SERVERS . DIRECTORY_SEPARATOR . "$hash.json", "w");
			fwrite($file, $json);
			fclose($file);
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

	/**
	 * Read all
	 *
	 * Read JSON from all files of servers
	 *
	 * @param int $amount Number of servers per page
	 * @param int $page Page of list of servers
	 * @param int $time Number of seconds since the file was last edited (86400 seconds is 1 day)
	 * @param array $errors Buffer of errors
	 *
	 * @return ?array Array with JSON entries, if found
	 */
	public static function all(int $amount = 100, int $page = 1, int $time = 86400, array &$errors = []): ?array
	{
		try {
			// Initializing of the output buffer
			$buffer = [];

			// Initializing the minimum value of amount
			if ($amount < 1) $amount = 1;

			// Initializing the minimum value of page
			if ($page < 1) $page = 1;

			// Initializing of amount to skip
			$skip = $page * $amount;

			foreach (new parser(static::SERVERS) as $file) {
				// Skipping unnecessary files
				if (--$skip > $amount) continue;

				if ($file->isDot()) continue;

				if (time() - $file->getCTime() > $time && $file->isReadable()) {
					$server = $file->openFile('r');
					$buffer[] = json_decode($server->fread($file->getSize()));
				}

				if (--$amount < 1) break;
			}

			// Exit (success)
			return $buffer;
		} catch (exception $e) {
			// Write to buffer of errors
			$errors[] = [
				'text' => $e->getMessage(),
				'file' => $e->getFile(),
				'line' => $e->getLine(),
				'stack' => $e->getTrace()
			];
		}

		// Exit (fail)
		return null;
	}
}
