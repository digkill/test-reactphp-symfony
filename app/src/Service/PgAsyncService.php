<?php

namespace App\Service;

use PgAsync\Message\NotificationResponse;

class PgAsyncService
{
    const CHANNEL_NAME = 'MESSAGE_CHANNEL';

    private $client;

    public function __construct()
    {
        $this->client = new \PgAsync\Client([
            "host" => 'localhost',
            "port" => '5433',
            "user" =>  'postgres',
            "database" => 'postgres',
            "password" => 'password',
            "auto_disconnect" => true
        ]);



        echo $this->client->getConnectionCount();

    }

    public function getClient()
    {
        return $this->client;
    }

    public function put($table, $data)
    {
        $keys = array_keys($data);
        $keysString = implode(',', $keys);

        $values = array_values($data);
        $values = array_map(function ($item) {
            return "'" . $item . "'";
        }, $values);
        $valuesString = implode(',', $values);

        $this->client->query("INSERT INTO $table ($keysString) VALUES($valuesString)")->subscribe(function ($row) {
            var_dump($row);
            echo 'ddd';
        },
            function ($e) {
                echo "Failed.\n";
            },
            function () {
                echo "Complete.\n";
            });
        return $this;
    }
}