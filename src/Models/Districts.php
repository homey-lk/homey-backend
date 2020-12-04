<?php

namespace Models;


require_once('Core/BaseModel.php');

use Core\BaseModel as BaseModel;

class Districts extends BaseModel{

    protected static $table;

    public function __construct() {
        $table = (explode('\\',strtolower(basename(get_called_class()))));
        self::$table = isset($table[1]) ? $table[1] : $table[0];
    }

    public $schema = [
        '_id',
        'district',
        'province_id'
    ]; 

}