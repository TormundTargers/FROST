<?php
    function dbconnect() {
        try {
            /* Username & password will need to be changed to
                account that FROST can use in the database */
            return new PDO('mysql:host=192.168.0.10; dbname=FROST', 'jamie', 'password');
        }
        catch(PDOException $e) {
            die('Cannot connect to database: ' . $e -> getMessage());
        }
    }