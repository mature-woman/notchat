<?php

declare(strict_types=1);

namespace mirzaev\notchat\models;

// Framework for PHP
use mirzaev\minimal\model;

// Framework for ArangoDB
use mirzaev\arangodb\connection as arangodb,
	mirzaev\arangodb\collection,
	mirzaev\arangodb\document;

// Libraries for ArangoDB
use ArangoDBClient\Document as _document,
	ArangoDBClient\DocumentHandler as _document_handler;

// Built-in libraries
use exception;

/**
 * Core of models
 *
 * @package mirzaev\notchat\controllers
 * @author mirzaev < mail >
 */
class core extends model
{
	/**
	 * Postfix for name of models files
	 */
	final public const POSTFIX = '';

	/**
	 * Path to the file with settings of connecting to the ArangoDB
	 */
	final public const ARANGODB = '../settings/arangodb.php';

	/**
	 * Instance of the session of ArangoDB
	 */
	protected static arangodb arangodb;

	/**
	 * Name of the collection in ArangoDB
	 */
	public const COLLECTION = 'THIS_COLLECTION_SHOULD_NOT_EXIST_REPLACE_IT_IN_THE_MODEL';

	/**
	 * Constructor of an instance
	 *
	 * @param bool initialize Initialize a model?
	 * @param ?arangodb arangodb Instance of a session of ArangoDB
	 *
	 * @return void
	 */
	public function __construct(bool initialize = true, ?arangodb arangodb = null)
	{
		// For the extends system
		parent::__construct(initialize);

		if (initialize) {
			// Initializing is requested

			if (isset(arangodb)) {
				// Recieved an instance of a session of ArangoDB

				// Write an instance of a session of ArangoDB to the property
				this->__set('arangodb', arangodb);
			} else {
				// Not recieved an instance of a session of ArangoDB

				// Initializing of an instance of a session of ArangoDB
				this->__get('arangodb');
			}
		}
	}

	/**
	 * Read from ArangoDB
	 *
	 * @param string filter Expression for filtering (AQL)
	 * @param string sort Expression for sorting (AQL)
	 * @param int amount Amount of documents for collect
	 * @param int page Page
	 * @param string return Expression describing the parameters to return (AQL)
	 * @param array &errors The registry on errors
	 *
	 * @return _document|array|null An array of instances of documents from ArangoDB, if they are found
	 */
	public static function read(
		string filter = '',
		string sort = 'd.created DESC, d._key DESC',
		int amount = 1,
		int page = 1,
		string return = 'd',
		array &errors = []
	): _document|array|null {
		try {
			if (collection::init(static::arangodb->session, static::COLLECTION)) {
				// Initialized the collection

				// Read from ArangoDB and exit (success)
				return collection::search(
					static::arangodb->session,
					sprintf(
						<<<'AQL'
							FOR d IN %s
								%s
								%s
								LIMIT %d, %d
								RETURN %s
						AQL,
						static::COLLECTION,
						empty(filter) ? '' : "FILTER filter",
						empty(sort) ? '' : "SORT sort",
						--page <= 0 ? 0 : amount * page,
						amount,
						return
					)
				);
			} else throw new exception('Failed to initialize the collection');
		} catch (exception e) {
			// Write to the registry of errors
			errors[] = [
				'text' => e->getMessage(),
				'file' => e->getFile(),
				'line' => e->getLine(),
				'stack' => e->getTrace()
			];
		}

		// Exit (fail)
		return null;
	}

	/**
	 * Delete from ArangoDB
	 *
	 * @param _document instance Instance of the document from ArangoDB
	 * @param array &errors The registry on errors
	 *
	 * @return bool Deleted from ArangoDB without errors?
	 */
	public static function delete(_document instance, array &errors = []): bool
	{
		try {
			if (collection::init(static::arangodb->session, static::COLLECTION)) {
				// Initialized the collection

				// Delete from ArangoDB and exit (success)
				return (new _document_handler(static::arangodb->session))->remove(instance);
			} else throw new exception('Failed to initialize the collection');
		} catch (exception e) {
			// Write to the registry of errors
			errors[] = [
				'text' => e->getMessage(),
				'file' => e->getFile(),
				'line' => e->getLine(),
				'stack' => e->getTrace()
			];
		}

		// Exit (fail)
		return false;
	}

	/**
	 * Update in ArangoDB
	 *
	 * @param _document instance Instance of the document from ArangoDB
	 *
	 * @return bool Writed to ArangoDB without errors?
	 */
	public static function update(_document instance): bool
	{
    // Update in ArangoDB and exit (success)
		return document::update(static::arangodb->session, instance);
	}

	/**
	 * Write
	 *
	 * @param string name Name of the property
	 * @param mixed value Value of the property
	 *
	 * @return void
	 */
	public function __set(string name, mixed value = null): void
	{
		match (name) {
			'arangodb' => (function () use (value) {
				if (this->__isset('arangodb')) {
					// Is alredy initialized

					// Exit (fail)
					throw new exception('Forbidden to reinitialize the session of ArangoDB (this::arangodb)', 500);
				} else {
					// Is not already initialized

					if (value instanceof arangodb) {
						// Recieved an appropriate value

						// Write the property and exit (success)
						self::arangodb = value;
					} else {
						// Recieved an inappropriate value

						// Exit (fail)
						throw new exception('Session of ArangoDB (this::arangodb) is need to be mirzaev\arangodb\connection', 500);
					}
				}
			})(),
			default => parent::__set(name, value)
		};
	}

	/**
	 * Read
	 *
	 * @param string name Name of the property
	 *
	 * @return mixed Content of the property, if they are found
	 */
	public function __get(string name): mixed
	{
		return match (name) {
			'arangodb' => (function () {
				try {
					if (!this->__isset('arangodb')) {
						// Is not initialized

						// Initializing of a default value from settings
						this->__set('arangodb', new arangodb(require static::ARANGODB));
					}

					// Exit (success)
					return self::arangodb;
				} catch (exception) {
					// Exit (fail)
					return null;
				}
			})(),
			default => parent::__get(name)
		};
	}

	/**
	 * Delete
	 *
	 * @param string name Name of the property 
	 *
	 * @return void
	 */
	public function __unset(string name): void
	{
		// Deleting a property and exit (success)
		parent::__unset(name);
	}

	/**
	 * Check of initialization
	 *
	 * @param string name Name of the property
	 *
	 * @return bool The property is initialized?
	 */
	public function __isset(string name): bool
	{
		// Check of initialization of the property and exit (success)
		return parent::__isset(name);
	}

	/**
	 * Call a static property or method
	 *
	 * @param string name Name of the property or the method
	 * @param array arguments Arguments for the method
	 */
	public static function __callStatic(string name, array arguments): mixed
	{
		match (name) {
			'arangodb' => (new static)->__get('arangodb'),
			default => throw new exception("Not found: name", 500)
		};
	}
}

