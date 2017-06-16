<?php
/*******************************************************************************
    start.php by Igwe Magnus Nnamdi
    based upon BW's test sandbox for PHP
    Copyright (c) 2004-2017 Igwe Magnus Nnamdi
*******************************************************************************/

define('TITLE', 'PostgreSQL Quick Start');
define('VERSION', '1.2.0');
define('HEADER', '../assets/header.php');
define('BODY', '../assets/body.php');
define('FOOTER', '../assets/footer.php');
define('PGUSER', 'sid');
define('PGPASS', 'foo.bar');
define('DATABASE', 'test');
define('TABLE_NAME', 'test');

require_once('../lib/bwPgSQL.php');

_init();
main();
page();

function main()
{
    global $G;
    $db = $G['db'];
    $tn = TABLE_NAME;

    message('%s version %s', TITLE, VERSION);
    message('bwPgSQL version %s, PostgreSQL server version %s', $db->version(), $db->version('pgsql'));
    message('database: %s, table name: %s', DATABASE, TABLE_NAME);

    $query_start_time = microtime(TRUE);
    message('There are %d rows in the table.', $db->count_recs());

    // database operations go here
    try {
        $id = 3;
        message('deleting id %d', $id);
        $db->sql_do("DELETE FROM $tn WHERE id = ?", $id);
        message('after delete: There are %d records in the table', $db->count_recs());
        foreach($db->get_recs() as $row) {
            message('id: %d: The %s says %s', $row['id'], $row['animal'], $row['sound']);
        }
        $id = 2;
        message('deleting id %d', $id);
        $db->delete($id);
        message('after delete: There are %d records in the table', $db->count_recs());
        foreach($db->get_recs() as $row) {
            message('id: %d: The %s says %s', $row['id'], $row['animal'], $row['sound']);
        }

        // done -- drop the table
        $db->sql_do("DROP TABLE $tn");
        message('Table <i>%s</i> dropped.', $tn);
    } catch (PDOException $e) {
        error($e->getMessage());
    }

    $elapsed_time = microtime(TRUE) - $query_start_time;
    message('elapsed time: %s ms', number_format($elapsed_time * 1000, 2));
}

function _init( )
{
    global $G;
    $G['TITLE'] = TITLE;
    $G['ME'] = basename($_SERVER['SCRIPT_FILENAME']);

    // initialize display vars
    foreach ( array( 'MESSAGES', 'ERRORS', 'CONTENT' ) as $v )
        $G[$v] = "";

    // create the table
    try {
        $db = new bwPgSQL( PGUSER, PGPASS, DATABASE, TABLE_NAME );
        $tn = TABLE_NAME;
        $db->sql_do("DROP TABLE IF EXISTS $tn");
        $db->sql_do("CREATE TABLE $tn ( id SERIAL PRIMARY KEY, animal TEXT, sound TEXT )");
        // insert some rows
        $db->sql_do("INSERT INTO $tn (animal, sound) VALUES (?, ?)", 'cat', 'purr');
        $db->sql_do("INSERT INTO $tn (animal, sound) VALUES (?, ?)", 'dog', 'woof');
        $db->sql_do("INSERT INTO $tn (animal, sound) VALUES (?, ?)", 'duck', 'quack');
        $db->sql_do("INSERT INTO $tn (animal, sound) VALUES (?, ?)", 'bear', 'grrr');
    } catch (PDOException $e) {
        error($e->getMessage());
    }
    $G['db'] = $db;
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
