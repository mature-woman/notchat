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
 * @package mirzaev\notchat\models
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
	 * @param string $domain Domain of the server (unique)
	 * @param string $json Data of the server with JSON format
	 * @param array &$errors Buffer of errors
	 *
	 * @return void
	 */
	public static function write(string $domain, string $json = '', array &$errors = []): void
	{
		try {
			if (strlen($domain) > 32) throw new exception('Domain cannot be longer than 32 characters');

			// Initializing of path to file
			$path = static::SERVERS . DIRECTORY_SEPARATOR . "$domain.json";

			// Initializing of host parameter
			$host = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'];

			if (empty($host)) throw new exception('ты что дохуя фокусник блять');

			// Initializing of data of server
			$new = ['domain' => $domain, 'ip' => $host] + json_decode($json, true, 8);

			if (strlen($new['key']) > 512) throw new exception('Public key cannot be longer than 512 characters');

			if (file_exists($path)) {
				// File found

				// Open file with server data
				$file = fopen($path, "r");

				// Read server data
				$old = json_decode(fread($file, filesize($path)), true, 8);

				// Close file with server data
				fclose($file);

				if ($new['key'] === $old['key'] || time() - filectime($path) > 259200) {
					// The keys match or the file has not been updated for more than 3 days

					// Open file with server data
					$file = fopen($path, "w");

					// Write server data
					fwrite($file, json_encode($new));

					// Close file with server data
					fclose($file);

					// Write DNS record
					dns::write(domain: $new['domain'], ip: $new['ip'], port: $new['port'], errors: $errors);
				} else throw new exception('Public keys do not match');
			} else {
				// File is not found

				// Open file with server data
				$file = fopen($path, "w");

				// Write server data
				fwrite($file, json_encode($new));

				// Close file with server data
				fclose($file);

				// Write DNS record
				dns::write(domain: $new['domain'], ip: $new['ip'], port: $new['port'], errors: $errors);
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

	/**
	 * Read
	 *
	 * Read JSON from file of server
	 *
	 * @param string $domain Domain of the server
	 * @param array &$errors Buffer of errors
	 *
	 * @return string|null JSON with data of the server
	 */
	public static function read(string $domain, &$errors = []): ?string
	{
		try {
			// Initializing of path to file
			$path = static::SERVERS . DIRECTORY_SEPARATOR . "$domain.json";

			// Open file with server data
			$file = fopen($path, "r");

			// Read server data
			$server = fread($file, filesize($path));

			// Close file with server data
			fclose($file);

			// Exit (success)
			return $server;
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

	/**
	 * Read all
	 *
	 * Read JSON from all files of servers
	 *
	 * @param int $amount Number of servers per page
	 * @param int $page Page of list of servers
	 * @param int $time Number of seconds since the file was last edited (86400 seconds is 1 day)
	 * @param array &$errors Buffer of errors
	 *
	 * @return array|null Array with JSON entries, if found
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

				// Skipping system shortcuts
				if ($file->isDot()) continue;

				if (time() - $file->getCTime() < $time && $file->isReadable()) {
					// The file is actual (3 days by default) and writable

					// Open file with server data
					$server = $file->openFile('r');

					// Write server data to the output buffer
					$buffer[] = json_decode($server->fread($file->getSize()));

					// Close file with server data
					unset($file);
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
