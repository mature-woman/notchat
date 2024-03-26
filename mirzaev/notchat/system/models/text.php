<?php

declare(strict_types=1);

namespace mirzaev\notchat\models;

// Files of the project
use mirzaev\notchat\models\enumerations\log as type;

// Built-in libraries
use exception,
	DirectoryIterator as parser;

/**
 * Text
 *
 * @package mirzaev\notchat\models
 * @author Arsen Mirzaev Tatyano-Muradovich <arsen@mirzaev.sexy>
 */
class text extends core
{
	/**
	 * Path to the directory with translation files (json)
	 */
	final public const LANGUAGES = core::PUBLIC . DIRECTORY_SEPARATOR . 'languages';

	/**
	 * Default language
	 */
	final public const LANGUAGE = 'english';


	/**
	 * Read
	 *
	 * @param string $id Identifier
	 * @param string $language Language (name of thw file without ".json")
	 * @param array &$errors Buffer of errors
	 *
	 * @return string|null Text, if found
	 */
	public static function read(string $id, string $language = 'english', &$errors = []): ?string
	{
		try {
			// Initializing of path to the file of the log
			$path = static::LANGUAGES . DIRECTORY_SEPARATOR . "$language.json";

			if (file_exists($path)) {
				// The file exists

				// Open the file of translation
				$json = file_get_contents($path);

				if (!empty($json)) {
					// The file is not empty

					// Decoding JSON to Array
					$text = json_decode($json, true, 8);

					// Exit (success)
					return $text[$id] ?? throw new exception('Could not find the text in translation file');
				}
			}
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

	/**
	 * Generate a list of available languages
	 *
	 * @param array &$errors Buffer of errors
	 *
	 * @return array|null Languages, if they found
	 */
	public static function list(&$errors = []): ?array
	{
		try {
			// Initializing of the buffer of languages
			$languages = [];

			foreach (new parser(static::LANGUAGES) as $file) {
				// Iterate through all files in the languages directory

				// Skipping system shortcuts
				if ($file->isDot()) continue;

				if ($file->isReadable() && $file->getSize() > 0) {
					// The file is readable and not empty

					// Write a language to the buffer registry of available languages
					$languages[] = $file->getBasename('.json');
				}
			}

			// Exit (success)
			return $languages;
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
