<?php

declare(strict_types=1);

namespace mirzaev\notchat\controllers;

// Files of the project
use mirzaev\notchat\controllers\core,
	mirzaev\notchat\models\dns,
	mirzaev\notchat\models\server,
	mirzaev\notchat\models\text;

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
		// Initializing a list with languages
		$this->view->languages = text::list($this->errors);

		// Инициализация бегущей строки
		$this->view->hotline = [
			'id' => 'hotline'
		];

		// Инициализация параметров бегущей строки
		$this->view->hotline = [
			'parameters' => [
				'step' => '0.3'
			]
		] + $this->view->hotline;

		// Инициализация аттрибутов бегущей строки
		$this->view->hotline = [
			'attributes' => []
		] + $this->view->hotline;

		// Инициализация элементов бегущей строки
		$this->view->hotline = [
			'elements' => [
				['html' => ''],
				[
					'tag' => 'article',
					'attributes' => [
						'class' => 'trash'
					],
					'html' => $this->view->render(DIRECTORY_SEPARATOR . 'hotline' . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'trash.html', [
						'id' => 'trash_1',
						'title' => 'Linoleum',
						'main' => '<p>Do you really like the rotting smell, dull sound and disgusting greasy shine of parquet-like fake pattern on a polymer toxic film? <b>Are you fucking insane?</b></p>',
						'image' => [
							'src' => 'https://virus.mirzaev.sexy/images/trash/linoleum.png',
							'alt' => 'Linoleum'
						]
					])
				],
				['html' => ''],
				['html' => ''],
				['html' => ''],
				['html' => ''],
				['html' => ''],
				['html' => ''],
				['html' => ''],
				['html' => ''],
				['html' => ''],
				['html' => ''],
				['html' => ''],
				['html' => ''],
				['html' => '']
			]
		] + $this->view->hotline;


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
	/* public function cache(array $parameters = []): void
	{
		if ($_SERVER['REQUEST_METHOD'] === 'GET') {
			// GET

			if (file_exists($path = INDEX . DIRECTORY_SEPARATOR . 'manifest')) {
				// File found

				// Clearing the output buffer
				if (ob_get_level()) ob_end_clean();

				// Initializing of the output buffer
				ob_start();

				// Initializing a response headers
				header('Content-Type: text/cache-manifest');

				// Generating the reponse
				if ($file = fopen($path, 'r')) {
					// File open

					// Reading file
					while (!feof($file)) echo fread($path, 1024);

					// Closing file
					fclose($file);
				}

				// Initializing a response headers
				header('Content-Length: ' . ob_get_length());

				// Sending and deinitializing of the output buffer
				ob_end_flush();
				flush();
			}
		}
	} */

	/**
	 * 
	 *
	 * @return void Generated JSON to the output buffer
	 */
	/* public function cache(array $parameters = []): void
	{
		if ($_SERVER['REQUEST_METHOD'] === 'GET') {
			// GET

			if (file_exists($path = INDEX . DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR . 'cache.js')) {
				// File found

				// Clearing the output buffer
				if (ob_get_level()) ob_end_clean();

				// Initializing of the output buffer
				ob_start();

				// Initializing a response headers
				header('Content-Type: application/javascript charset=utf-8');

				// Generating the reponse
				if ($file = fopen($path, 'r')) {
					// File open

					// Reading file
					while (!feof($file)) echo fread($file, 1024);

					// Closing file
					fclose($file);
				}

				// Initializing a response headers
				header('Content-Length: ' . ob_get_length());

				// Sending and deinitializing of the output buffer
				ob_end_flush();
				flush();
			}
		}
	} */

	/**
	 * Render the offline page
	 */
	public function offline(): ?string
	{
		// Initializing of the title
		$this->view->title = 'bye';

		// Exit (success)
		return $this->view->render('pages/offline.html');
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
					'html' => $this->view->render(
						'sections/servers.html',
						[
							'current' => isset($parameters['server'])
								&& ($server = server::read(domain: dns::domain($parameters['server'], errors: $this->errors), errors: $this->errors))
								? json_decode($server, true, 8)
								: null,
							'servers' => server::all(100, errors: $this->errors) ?? []
						]
					),
					'status' => isset($server) ? 'connected' : 'disconnected',
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
