<?php

require_once "classes/DbAccess.php";

interface iModelMap
{
    public static function InitSQLTable(DbAccess $db) : void;
}