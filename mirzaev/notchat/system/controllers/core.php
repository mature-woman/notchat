<?php

declare(strict_types=1);

namespace mirzaev\notchat\controllers;

// Files of the project
use mirzaev\notchat\views\templater,
	mirzaev\notchat\models\core as models;

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
	 * Constructor of an instance
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

			// Initializing of models core
			new models();
	
		// Initializing of preprocessor of views
			$this->view = new templater();
		}
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
