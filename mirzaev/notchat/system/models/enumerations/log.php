<?php

declare(strict_types=1);

namespace mirzaev\notchat\models\enumerations;

/**
 * Types of logs
 *
 * @package mirzaev\notchat\models\enumerations
 * @author Arsen Mirzaev Tatyano-Muradovich <arsen@mirzaev.sexy>
 */
enum log: string
{
	case ERRORS = 'errors.log';
	case CONNECTIONS = 'connections.log';
	case BANS = 'bans.log';
	case FIREWALL = 'firewall.log';
	case SESSIONS = 'sessions.log';
	case SERVERS = 'servers.log';
	case DNS = 'dns.log';
}
