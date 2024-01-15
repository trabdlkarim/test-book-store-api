<?php

namespace App\OAuth2;

use OAuth2\Storage\Pdo;
use Illuminate\Support\Facades\DB;

class MySQLStorage extends Pdo
{
    protected $storage;

    public function __construct($dbName = null, $dbUsername=null, $dbPass=null, $dbHost='localhost')
    {
        $connection = array(
            'dsn' => "mysql:host={$dbHost};dbname=" . ($dbName ?: DB::getDatabaseName()),
            'username' => $dbUsername ?: env('DB_USERNAME'),
            'password' => $dbPass ?: env('DB_PASSWORD'),
        );
        $config = array(
            'user_table' => 'users',
        );

        parent::__construct($connection, $config);
    }

    /**
     * @param string $username
     * @return array|bool
     */
    public function getUser($username)
    {
        $stmt = $this->db->prepare($sql = sprintf('SELECT * from %s where email=:username', $this->config['user_table']));
        $stmt->execute(array('username' => $username));

        if (!$userInfo = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            return false;
        }

        // the default behavior is to use "username" as the user_id
        return array_merge(array(
            'user_id' => $username
        ), $userInfo);
    }


    /**
     * plaintext passwords are bad!  Override this for your application
     *
     * @param string $username
     * @param string $password
     * @param string $firstName
     * @param string $lastName
     * @return bool
     */
    public function setUser($username, $password, $firstName = null, $lastName = null)
    {
        // do not store in plaintext
        $password = $this->hashPassword($password);

        // if it exists, update it.
        if ($this->getUser($username)) {
            $stmt = $this->db->prepare($sql = sprintf('UPDATE %s SET password=:password, first_name=:firstName, last_name=:lastName where email=:username', $this->config['user_table']));
        } else {
            $stmt = $this->db->prepare(sprintf('INSERT INTO %s (email, password, first_name, last_name) VALUES (:username, :password, :firstName, :lastName)', $this->config['user_table']));
        }

        return $stmt->execute(compact('username', 'password', 'firstName', 'lastName'));
    }


    /**
     * plaintext passwords are bad!  Override this for your application
     *
     * @param array $user
     * @param string $password
     * @return bool
     */
    protected function checkPassword($user, $password)
    {
        return password_verify($password, $user['password']);
    }


    protected function hashPassword($password)
    {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    public function getDb()
    {
        return $this->db;
    }
}
