<?php
/** Returns the live overrides for the JS i18n dictionary (translations table). */

require_once __DIR__ . '/../includes/api.php';

json_ok([
    'list' => DB::all('SELECT dict_key, en, bn FROM translations ORDER BY dict_key ASC'),
]);