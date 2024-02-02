<?php

declare(strict_types=1);

namespace mirzaev\notchat\controllers;

// Files of the project
use mirzaev\notchat\controllers\core;

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
}
