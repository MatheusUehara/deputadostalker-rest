<<<<<<< HEAD
<?php

/**
 * Created by PhpStorm.
 * User: Matheus Uehara
 * Date: 07/05/2016
 * Time: 00:23
 */
class Usuario{

    private $conn;

    function __construct() {
        require_once '../include/DbConnect.php';
        $db = new DbConnect();
        $this->conn = $db->connect();
    }

    /**
     * Creating new user
     * @param String $id User id
     * @param String $name User name
     * @param String $email User login email
     * @param String $redeSocial User redeSocial
     */
    public function criaUsuario($id,$name, $email, $redeSocial) {
        if (!$this->checaUsuario($id)) {
            $stmt = $this->conn->prepare("INSERT INTO usuario (id, name, email, redeSocial) values(?, ?, ?, ?)");
            $stmt->bind_param("ssss",$id, $name, $email, $redeSocial);
            $result = $stmt->execute();
            $stmt->close();
            // Check for successful insertion
            if ($result) {
                // User successfully inserted
                return USER_CREATED_SUCCESSFULLY;
            } else {
                // Failed to create user
                return USER_CREATE_FAILED;
            }
        } else {
            // User with same email already existed in the db
            return USER_ALREADY_EXISTED;
        }
        return $response;
    }

    /**
     * Checking for duplicate user by id address
     * @param String $id id to check in db
     * @return boolean
     */
    private function checaUsuario($id) {
        $stmt = $this->conn->prepare("SELECT * from usuario WHERE id = ?");
        $stmt->bind_param("s", $id);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }
=======
<?php

/**
 * Created by PhpStorm.
 * User: Matheus Uehara
 * Date: 07/05/2016
 * Time: 00:23
 */
class Usuario{

    private $conn;

    function __construct() {
        require_once '../include/DbConnect.php';
        $db = new DbConnect();
        $this->conn = $db->connect();
    }

    /**
     * Creating new user
     * @param String $id User id
     * @param String $name User name
     * @param String $email User login email
     * @param String $redeSocial User redeSocial
     */
    public function criaUsuario($id,$name, $email, $redeSocial) {
        if (!$this->checaUsuario($id)) {
            $stmt = $this->conn->prepare("INSERT INTO usuario (id, name, email, redeSocial) values(?, ?, ?, ?)");
            $stmt->bind_param("ssss",$id, $name, $email, $redeSocial);
            $result = $stmt->execute();
            $stmt->close();
            // Check for successful insertion
            if ($result) {
                // User successfully inserted
                return USER_CREATED_SUCCESSFULLY;
            } else {
                // Failed to create user
                return USER_CREATE_FAILED;
            }
        } else {
            // User with same email already existed in the db
            return USER_ALREADY_EXISTED;
        }
        return $response;
    }

    /**
     * Checking for duplicate user by id address
     * @param String $id id to check in db
     * @return boolean
     */
    private function checaUsuario($id) {
        $stmt = $this->conn->prepare("SELECT * from usuario WHERE id = ?");
        $stmt->bind_param("s", $id);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }
>>>>>>> remotes/origin/master
}