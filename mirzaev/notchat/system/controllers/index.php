<?php

declare(strict_types=1);

namespace mirzaev\notchat\controllers;

// Files of the project
use mirzaev\notchat\controllers\core,
	mirzaev\notchat\models\server;

/**
 * Index controller
 *
 * @package mirzaev\notchat\controllers
 * @author Arsen Mirzaev Tatyano-Muradovich <arsen@mirzaev.sexy>
 */
final class index extends core
{
	/**
	 * Render the main page
	 *
	 * @param array $parameters Parameters of the request (POST + GET)
	 */
	public function index(array $parameters = []): ?string
	{
		// Exit (success)
		if ($_SERVER['REQUEST_METHOD'] === 'GET') return $this->view->render('chats.html');
		else if ($_SERVER['REQUEST_METHOD'] === 'POST') return $this->view->render('chats.html');

		// Exit (fail)
		return null;
	}

	/**
	 * Render the servers section
	 *
	 * @param array $parameters Parameters of the request (POST + GET)
	 *
	 * @return void Generated JSON to the output buffer
	 */
	public function servers(array $parameters = []): void
	{
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			// POST

			// Initializing a response headers
			header('Content-Type: application/json');
			header('Content-Encoding: none');
			header('X-Accel-Buffering: no');

			// Initializing of the output buffer
			ob_start();

			// Generating the reponse
			echo json_encode(
				[
					'html' => $this->view->render('sections/servers.html', ['current' => server::read($parameters['server'], errors: $this->errors), 'servers' => server::all(100, errors: $this->errors) ?? []]),
					'errors' => null
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
