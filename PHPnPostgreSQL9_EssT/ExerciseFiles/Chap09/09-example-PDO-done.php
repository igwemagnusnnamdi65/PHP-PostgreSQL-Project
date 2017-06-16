<?php
/*******************************************************************************
    start.php by  Igwe Magnus Nnamdi
    based upon BW's test sandbox for PHP
    Copyright (c) 2004-2017 Igwe Magnus Nnamdi
*******************************************************************************/

define('TITLE', 'PostgreSQL Quick Start');
define('VERSION', '1.2.1');
define('HEADER', '../assets/header.php');
define('BODY', '../assets/body.php');
define('FOOTER', '../assets/footer.php');
define('PGUSER', 'sid');
define('PGPASS', 'foo.bar');
define('DATABASE', 'test');
define('TABLE_NAME', 'test');

_init();
main();
page();

function main()
{
    global $G;
    // database operations go here
    try {
        $tn = TABLE_NAME;
        $db = new PDO('pgsql:host=localhost;port=5432;dbname=' . DATABASE, PGUSER, PGPASS,
            array( PDO::ATTR_PERSISTENT => true ));
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
        $db->exec("DROP TABLE IF EXISTS $tn");
        $db->exec("CREATE TABLE $tn (a TEXT, b TEXT, c TEXT)");
        message('Table %s created successfully', $tn);

        $sth = $db->prepare("INSERT INTO $tn VALUES (?, ?, ?)");
        $sth->execute(array('a', 'b', 'c'));
        $sth->execute(array('1', '2', '3'));
        $sth->execute(array('one', 'two', 'three'));

        $sth = $db->prepare("SELECT * FROM $tn");
        $sth->setFetchMode(PDO::FETCH_ASSOC);
        $sth->execute();

        foreach ( $sth as $row ) {
            message('%s, %s, %s', $row['a'], $row['b'], $row['c']);
        }

        $db->exec("DROP TABLE $tn");
        message('Table <i>%s</i> dropped.', $tn);
    } catch(PDOException $e) {
        error($e->getMessage());
    }
}

function _init( )
{
    global $G;
    $G['TITLE'] = TITLE;
    $G['ME'] = basename($_SERVER['SCRIPT_FILENAME']);

    // initialize display vars
    foreach ( array( 'MESSAGES', 'ERRORS', 'CONTENT' ) as $v )
        $G[$v] = "";
}

function page( )
{
    global $G;
    set_vars();

    require_once(HEADER);
    require_once(BODY);
    require_once(FOOTER);
    exit();
}

//

function set_vars( )
{
    global $G;
    if(isset($G["_MSG_ARRAY"])) foreach ( $G["_MSG_ARRAY"] as $m ) $G["MESSAGES"] .= $m;
    if(isset($G["_ERR_ARRAY"])) foreach ( $G["_ERR_ARRAY"] as $m ) $G["ERRORS"] .= $m;
    if(isset($G["_CON_ARRAY"])) foreach ( $G["_CON_ARRAY"] as $m ) $G["CONTENT"] .= $m;
}

function content( $s )
{
    global $G;
    $G["_CON_ARRAY"][] = "\n<div class=\"content\">$s</div>\n";
}

function message()
{
    global $G;
    $args = func_get_args();
    if(count($args) < 1) return;
    $s = vsprintf(array_shift($args), $args);
    $G["_MSG_ARRAY"][] = "<p class=\"message\">$s</p>\n";
}

function error_message()
{
    global $G;
    $args = func_get_args();
    if(count($args) < 1) return;
    $s = vsprintf(array_shift($args), $args);
    $G["_ERR_ARRAY"][] = "<p class=\"error_message\">$s</p>\n";
}

function error( $s )
{
    error_message($s);
    page();
}

?>
