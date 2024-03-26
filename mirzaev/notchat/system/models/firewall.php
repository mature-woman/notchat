<?php

declare(strict_types=1);

namespace mirzaev\notchat\models;

// Files of the project
use mirzaev\notchat\models\enumerations\log as type,
	mirzaev\notchat\models\traits\file,
	mirzaev\notchat\models\traits\log as read;

// Built-in libraries
use exception,
	datetime;

/**
 * Firewall
 *
 * @package mirzaev\notchat\models
 * @author Arsen Mirzaev Tatyano-Muradovich <arsen@mirzaev.sexy>
 */
class firewall extends core
{
	use file, read {
		file::read as protected file;
		read::ban as protected _ban;
	}

	/**
	 * Write
	 *
	 * @param int $range Time range for reading last connections (seconds)
	 * @param int $limit Limit on the number of requests in the allotted time
	 * @param array &$errors Buffer of errors
	 *
	 * @return void
	 *
	 * @todo 
	 * 1. Dividing logs based on volume achieved
	 */
	public static function analyze(int $range = 10, int $limit = 20, &$errors = []): void
	{
		try {
			// Initializing of path to the file with connections
			$path = log::LOGS . DIRECTORY_SEPARATOR . type::CONNECTIONS->value;

			if (file_exists($path)) {
				// The file exists

				// Initializing of current time
				$current = new datetime();

				// Initializing of target past time
				$past = (clone $current)->modify("-$range seconds");

				// Initializing of the buffer of IP-addresses found in the connections log [ip => amount of connections per $time]
				$ips = [];

				// Open file with connections
				$connections = fopen($path, 'r');

				foreach (static::file($connections, 300, 0, -1) as $row) {
					// Reading a file backwards (rows from end)

					// Skipping of empty rows
					if (empty($row)) continue;

					try {
						// Deserializing a row
						$parameters = static::connection($row, $errors);

						if ($parameters !== null && is_array($parameters)) {
							// Parameters have been initialized

							// Initializing of parameters of connection
							[, $date, $ip, $forwarded, $referer, $useragent] = $parameters;

							// Initializing of date of connection
							$date = DateTime::createFromFormat('Y.m.d H:i:s', $date);

							if (0 <= $elapsed = $date->getTimestamp() - $past->getTimestamp()) {
								// No more than $range seconds have passed since connection

								// Initializing of counter of connections
								$ips[$ip] ??= 0;

								// Incrementing of counter of connections
								++$ips[$ip];
							}
						}
					} catch (exception $e) {
						continue;
					}
				}

				// Close file with connections
				fclose($connections);
			}

			// Ban IP-addresses that do not meet the conditions
			foreach ($ips ?? [] as $ip => $connections) if ($connections >= $limit) static::ban($ip, new datetime('+2 minutes'));
		} catch (exception $e) {
			// Write to buffer of errors
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
	 * Ban
	 *
	 * @param string $ip IP-address
	 * @param datetime $end Date for unban
	 * @param array &$errors Buffer of errors
	 *
	 * @return void
	 */
	public static function ban(string $ip, datetime $end, &$errors = []): void
	{
		try {
			// Write to the log of bans
			log::write(type::BANS, "[{$end->format('Y.m.d H:i:s')}] [$ip]");
		} catch (exception $e) {
			// Write to buffer of errors
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
	 * Check for ban
	 *
	 * Search in the ban log
	 *
	 * @param string $ip IP-address
	 * @param array &$errors Buffer of errors
	 *
	 * @return bool IP-address is banned? (null - has errors)
	 *
	 * @todo Count rows of file for reading instead of constant value (500 rows)
	 */
	public static function banned(string $ip, &$errors = []): ?bool
	{
		try {
			// Initializing of path to the file with bans
			$path = log::LOGS . DIRECTORY_SEPARATOR . type::BANS->value;

			if (file_exists($path)) {
				// The file exists

				// Open file with bans
				$bans = fopen($path, 'r');

				foreach (static::file($bans, 500, 0, -1) as $row) {
					// Reading a file backwards (rows from end)

					// Skipping of empty rows
					if (empty($row)) continue;

					try {
						// Deserializing a row
						$parameters = static::_ban($row, $errors);

						if ($parameters !== null && is_array($parameters)) {
							// Parameters have been initialized

							// Initializing of parameters of connection
							[, $from, $to, $_ip] = static::_ban($row, $errors);

							// Check for ban and exit (success)
							if ($ip === $_ip && (new datetime)->getTimestamp() - DateTime::createFromFormat('Y.m.d H:i:s', $to)->getTimestamp() < 0) return true;
						}
					} catch (exception $e) {
						continue;
					}
				}
			}

			// Exit (success)
			return false;
		} catch (exception $e) {
			// Write to buffer of errors
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
