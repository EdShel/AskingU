<?php

require_once "DbAccess.php";

interface iModelMap
{
    public static function InitSQLTable(DbAccess $db) : void;
}