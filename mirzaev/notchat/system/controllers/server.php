<?php

declare(strict_types=1);

namespace mirzaev\notchat\controllers;

// Files of the project
use mirzaev\notchat\controllers\core,
	mirzaev\notchat\controllers\traits\errors,
	mirzaev\notchat\models\server as model;

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
			model::write($parameters['domain'], file_get_contents('php://input'), $this->errors);

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

			// Read a file with server data
			$server = json_decode(model::read(model::domain($parameters['server']), $this->errors), true, 8);

			// Remove protected parameters
			unset($server['key']);

			// Initializing a response headers
			header('Content-Type: application/json');
			header('Content-Encoding: none');
			header('X-Accel-Buffering: no');

			// Initializing of the output buffer
			ob_start();

			// Generating the reponse
			echo json_encode(
				[
					'server' => $server,
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
