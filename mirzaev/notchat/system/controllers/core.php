<?php

declare(strict_types=1);

namespace mirzaev\notchat\controllers;

// Files of the project
use mirzaev\notchat\views\templater,
	mirzaev\notchat\models\core as models,
	mirzaev\notchat\models\log,
	mirzaev\notchat\models\enumerations\log as type,
	mirzaev\notchat\models\firewall;

// Framework for PHP
use mirzaev\minimal\controller;

/**
 * Core of controllers
 *
 * @package mirzaev\notchat\controllers
 * @author Arsen Mirzaev Tatyano-Muradovich <arsen@mirzaev.sexy>
 */
class core extends controller
{
	/**
	 * Postfix for name of controllers files
	 */
	final public const POSTFIX = '';

	/**
	 * Registry of errors
	 */
	protected array $errors = [];

	/**
	 * Constructor
	 *
	 * @param bool $initialize Initialize a controller?
	 *
	 * @return void
	 */
	public function __construct(bool $initialize = true)
	{
		// For the extends system
		parent::__construct($initialize);

		if ($initialize) {
			// Initializing is requested

			// Write to the log of connections
			log::write(
				type::CONNECTIONS,
				trim("[{$_SERVER['REMOTE_ADDR']}] "
					. (empty($_SERVER['HTTP_X_FORWARDED_FOR']) ? '' : "[{$_SERVER['HTTP_X_FORWARDED_FOR']}] ")
					. (empty($_SERVER['HTTP_REFERER']) ? '' : "[{$_SERVER['HTTP_REFERER']}] ")
					. (empty($_SERVER['HTTP_USER_AGENT']) ? '' : "[{$_SERVER['HTTP_USER_AGENT']}]"), ' ')
			);

			// Initializing of preprocessor of views
			$this->view = new templater();

			// Checking for ban
			if (
				(isset($_SERVER['HTTP_X_FORWARDED_FOR']) && firewall::banned($_SERVER['HTTP_X_FORWARDED_FOR']))
				|| (isset($_SERVER['REMOTE_ADDR']) && firewall::banned($_SERVER['REMOTE_ADDR']))
			) {
				// IP-address is banned

				// Sending a reply
				echo $this->view->render('pages/ban.html');

				// Exit (success)
				die;
			}

			// Initializing of models core
			new models();

			// Initializing a response headers
			header('Service-Worker-Allowed: /');
		}
	}

	/**
	 * Destructor
	 *
	 * @return void
	 */
	public function __destruct()
	{
		// Analyze recent requests
		firewall::analyze();
	}

	/**
	 * Check of initialization
	 *
	 * Checks whether a property is initialized in a document instance from ArangoDB
	 *
	 * @param string $name Name of the property from ArangoDB
	 *
	 * @return bool The property is initialized?
	 */
	public function __isset(string $name): bool
	{
		// Check of initialization of the property and exit (success)
		return match ($name) {
			default => isset($this->{$name})
		};
	}
}
