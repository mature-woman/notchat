<?php

declare(strict_types=1);

namespace mirzaev\notchat\models;

// Framework for PHP
use mirzaev\minimal\model;

// Built-in libraries
use exception,
	DirectoryIterator as parser;

/**
 * Core of DNS registry
 *
 * @package mirzaev\notchat\models
 * @author Arsen Mirzaev Tatyano-Muradovich <arsen@mirzaev.sexy>
 */
class dns extends core
{
	/**
	 * Path to DNS of storaged servers
	 */
	final public const DNS = core::STORAGE . DIRECTORY_SEPARATOR . 'servers' . DIRECTORY_SEPARATOR . 'dns.txt';

	/**
	 * Read
	 *
	 * Find and read server data from DNS registry by one of the values
	 *
	 * @param string|null $domain Domain of the server
	 * @param string|null $ip IP-address of the server
	 * @param string|int|null $port Port of the server
	 * @param int $line Line number on which the search will be stopped
	 * @param array &$errors Buffer of errors
	 *
	 * @return array|null Found DNS record of the server
	 */
	public static function read(?string $domain = null, ?string $ip = null, ?string $port = null, int $line = 0, &$errors = []): ?array
	{
		try {
			// Open file with DNS records
			$dns = fopen(static::DNS, 'r');

			while (($row = fgets($dns)) !== false) {
				// Iterate over rows

				// Initializing values of the server data
				$record = [$_domain, $_ip, $_port] = explode(' ', $row);

				// Incrementing the line read counter
				++$line;

				if ($domain === $_domain || $ip === $_ip || $port === $_port) {
					// Server found

					// Close file with DNS
					fclose($dns);

					// Exit (success)
					return $record;
				}
			}

			// Close file with DNS
			fclose($dns);
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
	 * Write
	 *
	 * Write server data to the end of DNS registry
	 *
	 * @param string $domain Domain of the server
	 * @param string $ip IP-address of the server
	 * @param string|int $port Port of the server
	 * @param array &$errors Buffer of errors
	 *
	 * @return void
	 */
	public static function write(string $domain, string $ip, string|int $port, &$errors = []): void
	{
		try {
			// Initializing part the file buffer (rows before target)
			$before = [];

			// Initializing part the file buffer (rows before target)
			$after = [];

			if (file_exists(static::DNS) && filesize(static::DNS) > 0) {
				// File exists and not empty

				// Initializing the status that the DNS record has been found
				$found = false;

				// Open file with DNS records
				$dns = fopen(static::DNS, 'r');

				while (($row = fgets($dns)) !== false) {
					// Iterate over rows

					// Initializing values of the server data
					[$_domain] = explode(' ', $row);

					// Writing the row to the file buffer (except the target record)
					if ($domain === $_domain) $found = true;
					else ${$found ? 'after' : 'before'}[] = $row;
				}

				// Close file with DNS records
				fclose($dns);
			}

			// Open file with DNS records
			$dns = fopen(static::DNS, 'c');

			if (flock($dns, LOCK_EX)) {
				// File locked

				// Clear file
				ftruncate($dns, 0); 

				// Write a new record to the DNS registry
				fwrite($dns, trim(implode("", $before)) . "\n$domain $ip $port\n" . trim(implode("", $after)));

				// Apply changes
				fflush($dns);

				// Unlock file
				flock($dns, LOCK_UN);
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
	 * Domain
	 *
	 * Convert domain or IP-address to domain
	 *
	 * @param string $server Domain or IP-address of the server
	 * @param array &$errors Buffer of errors
	 *
	 * @return string|null Domain of the server
	 */
	public static function domain(string $server, &$errors = []): ?string
	{
		try {
			if (preg_match('/^(https:\/\/)?\d+\..*\d\/?$/', $server) === 1) {
				// IP-address

				// Exit (success)
				return static::read(ip: $server, errors: $errors)['domain'];
			} else {
				// Domain (implied)

				// Exit (success)
				return $server;
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

		// Exit (fail)
		return null;
	}

	/**
	 * IP-address
	 *
	 * Convert domain or IP-address to IP-address
	 *
	 * @param string $server Domain or IP-address of the server
	 * @param array &$errors Buffer of errors
	 *
	 * @return string|null IP-address of the server
	 */
	public static function ip(string $server, &$errors = []): ?string
	{
		try {
			if (preg_match('/^(https:\/\/)?\d+\..*\d\/?$/', $server) === 1) {
				// IP-address

				// Exit (success)
				return $server;
			} else {
				// Domain (implied)

				// Exit (success)
				return static::read(domain: $server, errors: $errors)['ip'];
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

		// Exit (fail)
		return null;
	}
}
