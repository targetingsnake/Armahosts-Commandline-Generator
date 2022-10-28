<?php
/**
 * This File includes all needed files for database interaction in correct order
 *
 * @package default
 */

if (!defined('NICE_PROJECT')) {
    die('Permission denied.');
}

require_once 'bin/database/basic-db.php';
require_once 'bin/database/user-db.php';
require_once 'bin/database/role-db.php';
require_once 'bin/database/session-db.php';
require_once 'bin/database/user_browserinfo-db.php';
require_once 'bin/database/mod-db.php';
require_once 'bin/database/log-db.php';
