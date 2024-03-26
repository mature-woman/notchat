<?php

declare(strict_types=1);

namespace mirzaev\notchat\models\traits;

// Files of the project
use mirzaev\notchat\models\log,
	mirzaev\notchat\models\enumerations\log as type;

// Built-in libraries
use exception,
	generator;

/**
 * Trait of the file handler
 *
 * @package mirzaev\notchat\models\traits
 * @author Arsen Mirzaev Tatyano-Muradovich <arsen@mirzaev.sexy>
 */
trait file
{
	/**
	 * Read
	 *
	 * @param resource $file File pointer (fopen())
	 * @param int $limit Maximum limit of iterations (rows)
	 * @param int $position Initial cursor position
	 * @param int $step Row reading step
	 * @param array &$errors Buffer of errors
	 *
	 * @return generator|string|null
	 */
	private static function read($file, int $limit = 500, int $position = 0, int $step = 1, &$errors = []): ?generator
	{
		try {
			while ($limit-- > 0) {
				// Recursive execution until $limit reaches 0

				// Initializing of the buffer of row
				$row = '';

				// Initializing the character buffer to generate $row
				$character = '';

				do {
					// Iterate over rows

					// End (or beginning) of file reached (success)
					if (feof($file)) return;

					// Reading a row
					$row = $character . $row;

					// Move to next position
					fseek($file, $position += $step, SEEK_END);

					// Read a character
					$character = fgetc($file);

					// Is the character a carriage return? (end of row)
				} while ($character != PHP_EOL);

				// Exit (success)
				yield $row;
			}

			// Exit (success)
			return null;
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
