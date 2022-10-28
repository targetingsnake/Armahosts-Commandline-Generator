<?php

if (!defined('NICE_PROJECT')) {
    die('Permission denied.');
}

/**
 * Created by PhpStorm.
 * User: martin
 * Date: 27.12.17
 * Time: 19:03
 */
class config
{
    public static $SQL_SERVER = "x.x.x.x";
    public static $SQL_USER = "dbuser";
    public static $SQL_PASSWORD = "thisisAPassword";
    public static $SQL_SCHEMA = "dbName";
    public static $SQL_PREFIX = "prefix__";
    public static $SQL_Connector = "pdo";
    public static $DEBUG=true;
    public static $DEBUG_LEVEL=0;
    public static $PWD_LENGTH = 4;
    public static $PWD_ALGORITHM = PASSWORD_ARGON2ID;
    public static $RANDOM_STRING_LENGTH=170;
    public static $DOMAIN="your.url.com";

    public static $HMAC_SECRET = "";

    public static $BETA = false;
    public static $MAINTENANCE = false;


    public static $ROLE_EMPLOYEE = 10;
    public static $ROLE_ADMIN = 20;


    public static $SPECIAL_CHARS_CAPTCHA = false;

    public static $LOGO = "logo.sqf";
    public static $LOGO_BRAND = "brand.svg";

    public static $ADMINS = array("login1", "login2", "login3");

    public static $FTP_HOSTNAME = "your.arma.host.com";
    public static $FTP_PORT = 21;
    public static $FTP_PATH = "path/to/mod/directory";
    public static $FTP_USERNAME = "ftpUsername";
    public static $FTP_PASSWORD = "ftpPassword";
}
