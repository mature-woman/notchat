<?php

declare(strict_types=1);

namespace mirzaev\notchat\controllers;

// Files of the project
use mirzaev\notchat\controllers\core,
	mirzaev\notchat\controllers\traits\errors,
	mirzaev\notchat\models\dns,
	mirzaev\notchat\models\server as model,
	mirzaev\notchat\models\log,
	mirzaev\notchat\models\enumerations\log as type;

// Built-in libraries
use exception;

/**
 * Server controller
 *
 * @package mirzaev\notchat\controllers
 * @author Arsen Mirzaev Tatyano-Muradovich <arsen@mirzaev.sexy>
 */
final class server extends core
{
	use errors;

	/**
	 * Write the server
	 *
	 * API for server registration
	 *
	 * @param array $parameters Parameters of the request (POST + GET)
	 *
	 * @return void Generated JSON to the output buffer
	 */
	public function write(array $parameters = []): void
	{
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			// POST

			// Create a file with server data
			model::write(dns::domain($parameters['server'], errors: $this->errors), file_get_contents('php://input'), $this->errors);

			// Initializing a response headers
			header('Content-Type: application/json');
			header('Content-Encoding: none');
			header('X-Accel-Buffering: no');

			// Initializing of the output buffer
			ob_start();

			// Generating the reponse
			echo json_encode(
				[
					'errors' => static::text($this->errors)
				]
			);

			// Initializing a response headers
			header('Content-Length: ' . ob_get_length());

			// Sending and deinitializing of the output buffer
			ob_end_flush();
			flush();
		}
	}

	/**
	 * Read the server
	 *
	 * API for server reading
	 *
	 * @param array $parameters Parameters of the request (POST + GET)
	 *
	 * @return void Generated JSON to the output buffer
	 */
	public function read(array $parameters = []): void
	{
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			// POST

			// Initializing of buffer of response
			$return = [];

			try {
				// Decode of user input
				$parameters['server'] = urldecode($parameters['server']);

				// Validation of user input
				if (mb_strlen($parameters['server']) > 512) throw new exception('Server address longer than 512 characters');

				if ($domain = dns::domain($parameters['server'], errors: $this->errors)) {
					if ($raw = model::read(domain: $domain)) {
						// File found and read

						// Decoding server data to remove protected parameters
						$return['server'] = json_decode($raw, true, 8);

						// Remove protected parameters
						unset($return['server']['key']);
					} else throw new exception('Server offline');
				} else throw new exception('Server not found');
			} catch (exception $e) {
				// Write to the buffer of errors
				$this->errors[] = [
					'text' => $e->getMessage(),
					'file' => $e->getFile(),
					'line' => $e->getLine(),
					'stack' => $e->getTrace()
				];

				// Write to the log of errors
				log::write(type::ERRORS, "[{$_SERVER['REMOTE_ADDR']}] " . (empty($_SERVER['HTTP_X_FORWARDED_FOR']) ? '' : "[{$_SERVER['HTTP_X_FORWARDED_FOR']}] ") . $e->getMessage());
			}

			// Initializing a response headers
			header('Content-Type: application/json');
			header('Content-Encoding: none');
			header('X-Accel-Buffering: no');

			// Initializing of the output buffer
			ob_start();

			// Generating the reponse
			echo json_encode(
				$return + [
					'errors' => static::text($this->errors)
				]
			);

			// Initializing a response headers
			header('Content-Length: ' . ob_get_length());

			// Sending and deinitializing of the output buffer
			ob_end_flush();
			flush();
		}
	}
}
