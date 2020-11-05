<?php

namespace Root\api;

use Illuminate\Database\Capsule\Manager as Capsule;

class DatabaseIlluminate
{
    public function __construct()
    {
        $capsule = new Capsule;

        $capsule->addConnection([
            'driver'    => 'mysql',
            'host'      => config()->db_server,
            'database'  => config()->db_name,
            'username'  => config()->db_user,
            'password'  => config()->db_password,
            'charset'   => config()->db_charset,
            'collation' => config()->db_collation,
            'prefix'    => '',
        ]);

        $capsule->bootEloquent();
    }
}