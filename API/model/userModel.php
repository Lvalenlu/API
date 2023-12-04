<?php
require_once "ConDB.php";
class UserModel{
    static public function createUser($data){
        $cantMail = self::getMail($data["use_mail"]);
        
           var_dump($data);
        if($cantMail == 0){
            $query = "INSERT INTO users (use_id, use_mail, use_pass, use_dataCreate, us_identifier, us_key, us_status) VALUES (NULL, :use_mail, :use_pass, :use_dataCreate, :us_identifier, :us_key, :us_status)";
            $status = "0";
            $hashedPassword = password_hash($data["use_pass"], PASSWORD_DEFAULT);
            $statement = Connection::connection()->prepare($query);
            $statement->bindParam(":use_mail", $data["use_mail"], PDO::PARAM_STR);
            $statement->bindParam(":use_pass", $hashedPassword, PDO::PARAM_STR);
            $statement->bindParam(":use_dataCreate", $data["use_dataCreate"], PDO::PARAM_STR);
            $statement->bindParam(":us_identifier", $data["us_identifier"], PDO::PARAM_STR);
            $statement->bindParam(":us_key", $data["us_key"], PDO::PARAM_STR);
            $statement->bindParam(":us_status", $status, PDO::PARAM_STR);

            $message = $statement->execute() ? "ok" : Connection::connection()->errorInfo();
            
            $statement->closeCursor();
            $statement = null;
            //var_dump($query); 
            $query ="";
        } else {
            $message = "El usuario ya existe";
            
        }
        return $message;
    }
    static private function getMail($mail){
        $query = "SELECT use_mail FROM users WHERE use_mail = '$mail'";
        $statement = Connection::connection()->prepare($query);
        $statement-> execute();
        return $statement->rowCount();
    }
    static public function getUsers($parametro) {
        $param = is_numeric($parametro) ? $parametro : 0;
        $query = "SELECT use_id, use_mail, use_dateCreate FROM users WHERE us_status = '1'";
        
        if ($param > 0) {
            $query .= " AND use_id = :param";
        }
    
        $statement = Connection::connection()->prepare($query);
        
        if ($param > 0) {
            $statement->bindParam(":param", $param, PDO::PARAM_INT);
        }
        
        $statement->execute();
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }        
    static public function login($data){
        //print_r($data)
        $user = $data['use_mail']; 
        $pass = ($data['use_pass']); 
        
        if(!empty($user) && !empty($pass)){
            $query="SELECT us_identifier,us_key, use_id FROM users WHERE use_mail = '$user' AND use_pass='$pass' AND us_status='1'";
            //var_dump($query);
            $statement = Connection::connection()->prepare($query);
            $statement-> execute();
            $result=$statement->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        }else{
            return "NO TIENE CREDENCIALES";
        }
    }
    static public function getUserAuth(){
        $query="";
        $query="SELECT us_identifier,us_key FROM users WHERE us_status = '1'";
        $statement = Connection::connection()->prepare($query);
        $statement->execute();
        $result=$statement->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }
    static private function getStatus($id) {
        $query = "SELECT us_status FROM users WHERE use_id = :id";
        $statement = Connection::connection()->prepare($query);
        $statement->bindParam(":id", $id, PDO::PARAM_INT);
        $statement->execute();
        $result = $statement->fetchColumn();
        
        return $result !== false ? $result : null;
    }
    static public function update($id, $data) {
        if (!isset($data['use_mail']) || !isset($data['use_pss'])) {
            return ["error" => "Datos incompletos"];
        }
    
        $hashedPassword = password_hash($data['use_pss'], PASSWORD_DEFAULT);
    
        $query = "UPDATE users SET use_mail = :use_mail, use_pss = :use_pss WHERE use_id = :use_id";
        $params = [
            'use_mail' => $data['use_mail'],
            'use_pss' => $hashedPassword,
            'use_id' => $id
        ];
    
        try {
            self::executeUpdateQuery($query, $params);
            $msg = ["msg" => "Usuario actualizado"];
            return $msg;
        } catch (PDOException $e) {
            return ["error" => "Error al actualizar el usuario: " . $e->getMessage()];
        }
    }
    
    static private function executeUpdateQuery($query, $params) {
        $statement = Connection::connection()->prepare($query);
        foreach ($params as $param => &$value) {
            $statement->bindParam(':' . $param, $value);
        }
        $statement->execute();
    }
    static public function updateStatus($id) {
        $status = self::getStatus($id);
        $newStatus = self::getNewStatus($status);
        $query = "UPDATE users SET us_status = :new_status WHERE use_id = :user_id";
    
        try {
            self::executeStatusUpdateQuery($query, $newStatus, $id);
            $msg = ["msg" => "Estado del usuario actualizado"];
            return $msg;
        } catch (PDOException $e) {
            return ["error" => "Error al actualizar el estado del usuario: " . $e->getMessage()];
        }
    }
    
    static private function getNewStatus($status) {
        return ($status == 0) ? 1 : 0;
    }
    
    static private function executeStatusUpdateQuery($query, $newStatus, $id) {
        $statement = Connection::connection()->prepare($query);
        $statement->bindParam(':new_status', $newStatus, PDO::PARAM_INT);
        $statement->bindParam(':user_id', $id, PDO::PARAM_INT);
        $statement->execute();
    }
    static public function activateUser($id) {
        $newStatus = self::getStatus($id) ? 0 : 1;
        $query = "UPDATE users SET us_status = :new_status WHERE use_id = :user_id";
    
        try {
            $statement = Connection::connection()->prepare($query);
            $statement->bindParam(':new_status', $newStatus, PDO::PARAM_INT);
            $statement->bindParam(':user_id', $id, PDO::PARAM_INT);
            $statement->execute();
    
            return ["msg" => "Estado del usuario actualizado"];
        } catch (PDOException $e) {
            return ["error" => "Error al actualizar el estado del usuario: " . $e->getMessage()];
        }
    }  
}
?>
