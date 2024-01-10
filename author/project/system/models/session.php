<?php

declare(strict_types=1);

namespace mirzaev\notchat\models;

// Files of the project
use mirzaev\ebala\models\account,
	mirzaev\ebala\models\traits\status;

// Framework for ArangoDB
use mirzaev\arangodb\collection,
	mirzaev\arangodb\document;

// Library для ArangoDB
use ArangoDBClient\Document as _document;

// Built-in libraries
use exception;

/**
 * Model of session
 *
 * @package mirzaev\notchat\controllers
 * @author mirzaev < mail >
 */
final class session extends core
{
	/**
	 * Name of the collection in ArangoDB
	 */
	final public const COLLECTION = 'session';

	/**
	 * An instance of the ArangoDB document from ArangoDB
	 */
	protected readonly _document document;

	/**
	 * Constructor of an instance
	 *
	 * Initialize of a session and write them to the this->document property
	 *
	 * @param ?string hash Hash of the session in ArangoDB
	 * @param ?int expires Date of expiring of the session (used for creating a new session)
	 * @param array &errors Registry of errors
	 *
	 * @return static instance of the ArangoDB document of session
	 */
	public function __construct(?string hash = null, ?int expires = null, array &errors = [])
	{
		try {
			if (collection::init(static::arangodb->session, self::COLLECTION)) {
				// Initialized the collection

				if (this->search(hash, errors)) {
					// Found an instance of the ArangoDB document of session and received a session hash
				} else {
					// Not found an instance of the ArangoDB document of session

					// Initializing a new session and write they into ArangoDB
					_id = document::write(this::arangodb->session, self::COLLECTION, [
						'active' => true,
						'expires' => expires ?? time() + 604800,
						'ip' => _SERVER['REMOTE_ADDR'],
						'x-forwarded-for' => _SERVER['HTTP_X_FORWARDED_FOR'] ?? null,
						'referer' => _SERVER['HTTP_REFERER'] ?? null,
						'useragent' => _SERVER['HTTP_USER_AGENT'] ?? null
					]);

					if (session = collection::search(this::arangodb->session, sprintf(
						<<<AQL
							FOR d IN %s
								FILTER d._id == '%s' && d.expires > %d && d.active == true
								RETURN d
						AQL,
						self::COLLECTION,
						_id,
						time()
					))) {
						// Found an instance of just created new session

						// Generate a hash and write into an instance of the ArangoDB document of session property
						session->hash = sodium_bin2hex(sodium_crypto_generichash(_id));

						if (document::update(this::arangodb->session, session)) {
							// Is writed update

							// Write instance of the ArangoDB document of session into property and exit (success)
							this->document = session;
						} else throw new exception('Could not write the session data');
					} else throw new exception('Could not create or find just created session');
				}
			} else throw new exception('Could not initialize the collection');
		} catch (exception e) {
			// Write to the registry of errors
			errors[] = [
				'text' => e->getMessage(),
				'file' => e->getFile(),
				'line' => e->getLine(),
				'stack' => e->getTrace()
			];
		}
	}

	/**
	 * Search
	 *
	 * Search for the session in ArangoDB by hash and write they into this->document property if they are found
	 *
	 * @param ?string hash Hash of the session in ArangoDB
	 * @param array &errors Registry of errors
	 *
	 * @return static instance of the ArangoDB document of session
	 */
	public function search(?string hash, array &errors = []): bool
	{
		try {
			if (isset(hash)) {
				// Recieved a hash

				// Search the session data in ArangoDB
				_document = session = collection::search(this::arangodb->session, sprintf(
					<<<AQL
					FOR d IN %s
						FILTER d.hash == '%s' && d.expires > %d && d.active == true
						RETURN d
				AQL,
					self::COLLECTION,
					hash,
					time()
				));

				if (_document instanceof _document) {
					// An instance of the ArangoDB document of session is found

					// Write the session data to the property
					this->document = _document;

					// Exit (success)
					return true;
				}
			}
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
	 * 	Write to buffer of the session
	 *
	 * @param array data Data for merging
	 * @param array &errors Registry of errors
	 *
	 * @return bool Is data has written into the session buffer?
	 */
	public function write(array data, array &errors = []): bool
	{
		try {
			if (collection::init(this::arangodb->session, self::COLLECTION)) {
				// Initialized the collection

				// An instance of the ArangoDB document of session is initialized?
				if (!isset(this->document)) throw new exception('An instance of the ArangoDB document of session is not initialized');

				// Write data into buffwer of an instance of the ArangoDB document of session
				this->document->buffer = array_replace_recursive(
					this->document->buffer ?? [],
					[_SERVER['INTERFACE'] => array_replace_recursive(this->document->buffer[_SERVER['INTERFACE']] ?? [], data)]
				);

				// Write to ArangoDB and exit (success)
				return document::update(this::arangodb->session, this->document) ? true : throw new exception('Не удалось записать данные в буфер сессии');
			} else throw new exception('Could not initialize the collection');
		} catch (exception e) {
			// Write to the registry of errors
			errors[] = [
				'text' => e->getMessage(),
				'file' => e->getFile(),
				'line' => e->getLine(),
				'stack' => e->getTrace()
			];
		}

		return false;
	}

	/**
	 * Write
	 *
	 * Write a property into an instance of the ArangoDB document
	 *
	 * @param string name Name of the property
	 * @param mixed value Content of the property
	 *
	 * @return void
	 */
	public function __set(string name, mixed value = null): void
	{
		// Write to the property into an instance of the ArangoDB document and exit (success)
		this->document->{name} = value;
	}

	/**
	 * Read
	 *
	 * Read a property from an instance of the ArangoDB docuemnt
	 * 
	 * @param string name Name of the property
	 *
	 * @return mixed Content of the property
	 */
	public function __get(string name): mixed
	{
		// Read a property from an instance of the ArangoDB document and exit (success)
		return match (name) {
			'arangodb' => this::arangodb,
			default => this->document->{name}
		};
	}

	/**
	 * Delete
	 *
	 * Deinitialize the property in an instance of the ArangoDB document
	 *
	 * @param string name Name of the property
	 *
	 * @return void
	 */
	public function __unset(string name): void
	{
		// Delete the property in an instance of the ArangoDB document and exit (success)
		unset(this->document->{name});
	}

	/**
	 * Check of initialization
	 *
	 * Check of initialization of the property into an instance of the ArangoDB document
	 *
	 * @param string name Name of the property
	 *
	 * @return bool The property is initialized?
	 */
	public function __isset(string name): bool
	{
		// Check of initializatio nof the property and exit (success)
		return isset(this->document->{name});
	}

	/**
	 * Execute a method
	 *
	 * Execute a method from an instance of the ArangoDB document
	 *
	 * @param string name Name of the method
	 * @param array arguments Arguments for the method
	 *
	 * @return mixed Result of execution of the method
	 */
	public function __call(string name, array arguments = []): mixed
	{
		// Execute the method and exit (success)
		if (method_exists(this->document, name)) return this->document->{name}(arguments);
	}
}
