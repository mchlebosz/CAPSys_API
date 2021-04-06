<?php
class Database
{
    private $db_host = "database-capsys.css23hlmscax.eu-central-1.rds.amazonaws.com";
    private $db_name = "capsys";
    private $db_user = "admin";
    private $db_password = "NT2QxbAYT%mLWX";

    public function createConnection()
    {
        $connection = new mysqli($this->db_host, $this->db_user, $this->db_password, $this->db_name);

        if ($connection === false) {
            die("ERROR: Could not connect. " . $connection->connect_error);
        }

        return $connection;
    }
}
