<?php

declare(strict_types=1);

namespace mirzaev\notchat\views;

// Files of the project
use mirzaev\notchat\models\session,
	mirzaev\notchat\models\account;

// Framework for PHP
use mirzaev\minimal\controller;

// Templater of views
use Twig\Loader\FilesystemLoader,
	Twig\Environment as twig,
	Twig\Extra\Intl\IntlExtension as intl,
	Twig\TwigFilter;


// Built-in libraries
use ArrayAccess;

/**
 * Templater core
 *
 * @package mirzaev\notchat\views
 * @author mirzaev < mail >
 */
final class templater extends controller implements ArrayAccess
{
	/**
	 * Registry of global variables of view
	 */
	public array variables = [];

	/**
	 * Instance of twig templater 
	 */
	readonly public twig twig;

	/**
	 * Constructor of an instance
	 *
	 * @param ?session session Instance of the session of ArangoDB
	 *
	 * @return void
	 */
	public function __construct(?session &session = null): void
	{
		// Initializing of an instance of twig
		this->twig = new twig(new FilesystemLoader(VIEWS));

		// Initializing of global variables
		this->twig->addGlobal('theme', 'default');
		this->twig->addGlobal('server', _SERVER);
		this->twig->addGlobal('cookies', _COOKIE);
		if (!empty(session->status())) {
			this->twig->addGlobal('session', session);
		}

		// Initializing of twig extensions
		this->twig->addExtension(new intl());
	}

	/**
	 * Render a HTML-document
	 *
	 * @param string file Related path to a HTML-document
	 * @param ?array variables Registry of variables to push into registry of global variables
	 *
	 * @return ?string HTML-документ
	 */
	public function render(string file, ?array variables = null): ?string
	{
		// Generation and exit (success)
		return this->twig->render('themes' . DIRECTORY_SEPARATOR . this->twig->getGlobal('theme') . DIRECTORY_SEPARATOR . file, variables + this->variables);
	}

	/**
	 * Write
	 *
	 * Write a variable into registry of global variables
	 *
	 * @param string name Name of the variable
	 * @param mixed value Value of the variable
	 *
	 * @return void
	 */
	public function __set(string name, mixed value = null): void
	{
		// Write the variable and exit (success)
		this->variables[name] = value;
	}

	/**
	 * Read
	 *
	 * Read a variable from registry of global variables
	 *
	 * @param string name Name of the variable
	 *
	 * @return mixed Content of the variable, if they are found
	 */
	public function __get(string name): mixed
	{
    // Read the variable and exit (success)
		return this->variables[name];
	}

	/**
	 * Delete
	 *
	 * Delete a variable from the registry of global variables
	 *
	 * @param string name Name of the variable 
	 *
	 * @return void
	 */
	public function __unset(string name): void
	{
	 // Delete the variable and exit (success)
		unset(this->variables[name]);
	}

	/**
	 * Check of initialization
	 *
	 * Check of initialization in registry of global variables
	 *
	 * @param string name Name of the variable
	 *
	 * @return bool The variable is initialized?
	 */
	public function __isset(string name): bool
	{
		// Check of initialization of the variable and exit (success)
		return isset(this->variables[name]);
	}

	/**
	 * Write
	 *
	 * Write a variable into registry of global variables
	 *
	 * @param mixed name Name of an offset of the variable
	 * @param mixed value Value of the variable
	 *
	 * @return void
	 */
	public function offsetSet(mixed name, mixed value): void
	{
		// Write the variable and exit (success)
		this->variables[name] = value;
	}

	/**
	 * Read
	 *
	 * Read a variable from registry of global variables
	 *
	 * @param mixed name Name of the variable
	 *
	 * @return mixed Content of the variable, if they are found
	 */
	public function offsetGet(mixed name): mixed
	{
    // Read the variable and exit (success)
		return this->variables[name];
	}

	/**
	 * Delete
	 *
	 * Delete a variable from the registry of global variables
	 *
	 * @param mixed name Name of the variable 
	 *
	 * @return void
	 */
	public function offsetUnset(mixed name): void
	{
	 // Delete the variable and exit (success)
		unset(this->variables[name]);
	}

	/**
	 * Check of initialization
	 *
	 * Check of initialization in registry of global variables
	 *
	 * @param mixed name Name of the variable
	 *
	 * @return bool The variable is initialized?
	 */
	public function offsetExists(mixed name): bool
	{
		// Check of initialization of the variable and exit (success)
		return isset(this->variables[name]);
	}
}

