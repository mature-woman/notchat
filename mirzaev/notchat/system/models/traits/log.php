<?php

declare(strict_types=1);

namespace mirzaev\notchat\models\traits;

// Files of the project
use mirzaev\notchat\models\log as model,
	mirzaev\notchat\models\enumerations\log as type;

// Built-in libraries
use exception;

/**
 * Trait of the log handler
 *
 * @package mirzaev\notchat\models\traits
 * @author Arsen Mirzaev Tatyano-Muradovich <arsen@mirzaev.sexy>
 */
trait log
{
	/**
	 * Deserializing of type::CONNECTIONS
	 *
	 * @param string $row Row from the log type::CONNECTIONS
	 * @param array &$errors Buffer of errors
	 *
	 * @return array [$row, $date, $ip, $forwarded, $referer, $useragent]
	 */
	private static function connection(string $row, &$errors = []): ?array
	{
		try {
			// Search for parameters of connection
			preg_match('/(?:^\[(\d{4}\.\d{2}\.\d{2}\s\d{2}:\d{2}:\d{2})\]\s?)(?:\[(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})\]\s?)(?:\[(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})\]\s?)?(?:\[([^\]]+)\]\s[^$]?)?(?:\[([^\]]+)\]\s?)?$/', trim($row, PHP_EOL), $matches);

			// Have all 5 parameters been detected?
			if (count($matches) !== 6) throw new exception('Failed to deserialize row');

			// Exit (success)
			return $matches;
		} catch (exception $e) {
			// Write to the buffer of errors
			$errors[] = [
				'text' => $e->getMessage(),
				'file' => $e->getFile(),
				'line' => $e->getLine(),
				'stack' => $e->getTrace()
			];

			// Write to the log of errors
			model::write(type::ERRORS, "[{$_SERVER['REMOTE_ADDR']}] " . (empty($_SERVER['HTTP_X_FORWARDED_FOR']) ? '' : "[{$_SERVER['HTTP_X_FORWARDED_FOR']}] ") . $e->getMessage());
		}

		// Exit (fail)
		return null;
	}

	/**
	 * Deserializing of type::BANS
	 *
	 * @param string $row Row from the log type::BANS
	 * @param array &$errors Buffer of errors
	 *
	 * @return array [$row, $from, $to, $ip]
	 */
	private static function ban(string $row, &$errors = []): ?array
	{
		try {
			// Search for parameters of ban
			preg_match('/(?:^\[(\d{4}\.\d{2}\.\d{2}\s\d{2}:\d{2}:\d{2})\]\s?)(?:\[(\d{4}\.\d{2}\.\d{2}\s\d{2}:\d{2}:\d{2})\]\s?)(?:\[([^\]]+)\]\s?)$/', trim($row, PHP_EOL), $matches);

			// Have all 3 parameters been detected?
			if (count($matches) !== 4) throw new exception('Failed to deserialize row');

			// Exit (success)
			return $matches;
		} catch (exception $e) {
			// Write to the buffer of errors
			$errors[] = [
				'text' => $e->getMessage(),
				'file' => $e->getFile(),
				'line' => $e->getLine(),
				'stack' => $e->getTrace()
			];

			// Write to the log of errors
			model::write(type::ERRORS, "[{$_SERVER['REMOTE_ADDR']}] " . (empty($_SERVER['HTTP_X_FORWARDED_FOR']) ? '' : "[{$_SERVER['HTTP_X_FORWARDED_FOR']}] ") . $e->getMessage());
		}

		// Exit (fail)
		return null;
	}
}
