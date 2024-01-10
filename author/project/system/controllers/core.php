<?php

declare(strict_types=1);

namespace mirzaev\notchat\controllers;

// Files of the project
use mirzaev\notchat\views\manager,
	mirzaev\notchat\models\core as models,
	mirzaev\notchat\models\account_model as account,
	mirzaev\notchat\models\session_model as session;

// Library for ArangoDB
use ArangoDBClient\Document as _document;

// Framework for PHP
use mirzaev\minimal\controller;

/**
 * Core of controllers
 *
 * @package mirzaev\notchat\controllers
 * @author mirzaev < mail >
 */
class core extends controller
{
	/**
	 * Postfix for name of controllers files
	 */
	final public const POSTFIX = '';

	/**
	 * Instance of a session
	 */
	protected readonly session session;

	/**
	 * Instance of an account
	 */
	protected readonly ?account account;

	/**
	 * Registry of errors
	 */
	protected array errors = [
		'session' => [],
		'account' => []
	];

	/**
	 * Constructor of an instance
	 *
	 * @param bool initialize Initialize a controller?
	 *
	 * @return void
	 */
	public function __construct(bool initialize = true)
	{
		// Blocking requests from CloudFlare (better to write this blocking into nginx config file)
		if (_SERVER['HTTP_USER_AGENT'] === 'nginx-ssl early hints') return;

		// For the extends system
		parent::__construct(initialize);

		if (initialize) {
			// Initializing is requested

			// Initializing of models core (connect to ArangoDB...)
			new models();

			// Initializing of the date until which the session will be active
			expires = strtotime('+1 week');

			// Initializing of default value of hash of the session
			_COOKIE["session"] ??= null;

			// Initializing of session
			this->session = new session(_COOKIE["session"], expires, this->errors['session']);

			// Handle a problems with initializing a session
			if (!empty(this->errors['session'])) die;
			else if (_COOKIE["session"] !== this->session->hash) {
				// Hash of the session is changed (implies that the session has expired and recreated)

				// Write a new hash of the session to cookies
				setcookie(
					'session',
					this->session->hash,
					[
						'expires' => expires,
						'path' => '/',
						'secure' => true,
						'httponly' => true,
						'samesite' => 'strict'
					]
				);
			}

			// Initializing of preprocessor of views
			this->view = new templater(this->session);
		}
	}

	/**
	 * Check of initialization
	 *
	 * Checks whether a property is initialized in a document instance from ArangoDB
	 *
	 * @param string name Name of the property from ArangoDB
	 *
	 * @return bool The property is initialized?
	 */
	public function __isset(string name): bool
	{
		// Check of initialization of the property and exit (success)
		return match (name) {
			default => isset(this->{name})
		};
	}

}
