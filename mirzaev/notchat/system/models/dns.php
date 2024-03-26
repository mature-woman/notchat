<?php

declare(strict_types=1);

namespace mirzaev\notchat\models;

// Files of the project
use mirzaev\notchat\models\enumerations\log as type;

// Built-in libraries
use exception;

/**
 * DNS registry
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
			$dns = fopen(static::DNS, 'c+');

			while (($row = fgets($dns, 512)) !== false) {
				// Iterate over rows

				// Initializing values of the server data
				$record = [$_domain, $_ip, $_port] = explode(' ', $row);

				// Incrementing the line read counter
				++$line;

				if ($domain === $_domain || ($port && $ip === $_ip && $port === $_port) || (!$port && $ip === $_ip || $port === $_port)) {
					// Server found (domain, ip, ip + port)

					// Close file with DNS
					fclose($dns);

					// Exit (success)
					return array_combine(['domain', 'ip', 'port'], $record);
				}
			}

			// Close file with DNS
			fclose($dns);
		} catch (exception $e) {
			// Write to the buffer of errors
			$errors[] = [
				'text' => $e->getMessage(),
				'file' => $e->getFile(),
				'line' => $e->getLine(),
				'stack' => $e->getTrace()
			];

			// Write to the log of errors
			log::write(type::ERRORS, "[{$_SERVER['REMOTE_ADDR']}] " . (empty($_SERVER['HTTP_X_FORWARDED_FOR']) ? '' : "[{$_SERVER['HTTP_X_FORWARDED_FOR']}] ") . $e->getMessage());
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

			// Initializing the status that the DNS record has been found
			$found = false;

			if (file_exists(static::DNS) && filesize(static::DNS) > 0) {
				// File exists and not empty

				// Open file with DNS records
				$dns = fopen(static::DNS, 'c+');

				while (($row = fgets($dns, 512)) !== false) {
					// Iterate over rows

					// Initializing values of the server data
					[$_domain] = explode(' ', $row);

					// Writing the row to the file buffer (except the target record)
					if ($domain === $_domain) $found = $row;
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
				fwrite($dns, (count($before) > 0 ? trim(implode("", $before)) . "\n" : '') . "$domain $ip $port" .  (count($after) ? "\n" . trim(implode("", $after)) : ''));

				// Apply changes
				fflush($dns);

				// Unlock file
				flock($dns, LOCK_UN);
			}

			// Write to the log
			log::write(type::DNS, $found ? "[UPDATE] $found -> $domain $ip $port" : "[CREATE] $domain $ip $port");
		} catch (exception $e) {
			// Write to the buffer of errors
			$errors[] = [
				'text' => $e->getMessage(),
				'file' => $e->getFile(),
				'line' => $e->getLine(),
				'stack' => $e->getTrace()
			];

			// Write to the log of errors
			log::write(type::ERRORS, "[{$_SERVER['REMOTE_ADDR']}] " . (empty($_SERVER['HTTP_X_FORWARDED_FOR']) ? '' : "[{$_SERVER['HTTP_X_FORWARDED_FOR']}] ") . $e->getMessage());
		}
	}

	/**
	 * Domain
	 *
	 * Convert domain or IP-address to domain
	 *
	 * @param string $server Domain or IP-address of the server
	 * @param bool $strict Check for port compliance?
	 * @param array &$errors Buffer of errors
	 *
	 * @return string|null Domain of the server
	 */
	public static function domain(string $server, bool $strict = true, &$errors = []): ?string
	{
		try {
			if (preg_match('/^(?:https:\/\/)?([\d\.]*)(?:$|:?(\d.*\d)?\/?$)/', $server, $matches) === 1) {
				// IP-address

				// Initializing of parts of address
				@[, $ip, $port] = $matches;

				// Exit (success)
				return static::read(ip: $ip, port: $strict ? $port : null, errors: $errors)['domain'] ?? null;
			} else {
				// Domain (implied)

				// Exit (success)
				return $server ?? null;
			}
		} catch (exception $e) {
			// Write to the buffer of errors
			$errors[] = [
				'text' => $e->getMessage(),
				'file' => $e->getFile(),
				'line' => $e->getLine(),
				'stack' => $e->getTrace()
			];

			// Write to the log of errors
			log::write(type::ERRORS, "[{$_SERVER['REMOTE_ADDR']}] " . (empty($_SERVER['HTTP_X_FORWARDED_FOR']) ? '' : "[{$_SERVER['HTTP_X_FORWARDED_FOR']}] ") . $e->getMessage());
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
			if (preg_match('/^(?:https:\/\/)?(\d+\..*):?(\d.*\d)?\/?$/', $server, $matches) === 1) {
				// IP-address

				// Initializing of parts of address
				[, $ip, $port] = $matches;

				// Exit (success)
				return $ip ?? null;
			} else {
				// Domain (implied)

				// Exit (success)
				return static::read(domain: $server, errors: $errors)['ip'] ?? null;
			}
		} catch (exception $e) {
			// Write to the buffer of errors
			$errors[] = [
				'text' => $e->getMessage(),
				'file' => $e->getFile(),
				'line' => $e->getLine(),
				'stack' => $e->getTrace()
			];

			// Write to the log of errors
			log::write(type::ERRORS, "[{$_SERVER['REMOTE_ADDR']}] " . (empty($_SERVER['HTTP_X_FORWARDED_FOR']) ? '' : "[{$_SERVER['HTTP_X_FORWARDED_FOR']}] ") . $e->getMessage());
		}

		// Exit (fail)
		return null;
	}
}
