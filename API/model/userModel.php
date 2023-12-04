<?php
require_once "ConDB.php";
class UserModel{
    static public function createUser($data){
        $cantMail = self::getMail($data["use_mail"]);
        
           var_dump($data);
        if($cantMail == 0){
            $query = "INSERT INTO users (use_id,use_mail,use_pass,use_dataCreate,us_identifier,us_key,us_status) 
                      VALUES (NULL, :use_mail, :use_pass, :use_dataCreate, :us_identifier, :us_key, :us_status)";
            
            $status = "0";
            $statement = Connection::connection()->prepare($query);
            $statement->bindParam(":use_mail", $data["use_mail"], PDO::PARAM_STR);
            $statement->bindParam(":use_pass", $data["use_pass"], PDO::PARAM_STR);
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
    static public function getUsers($parameter){
        $parameter = is_numeric($parameter) ? $parameter :0;
        $query = "SELECT use_id, use_mail, use_dataCreate FROM users ";
        $query .= ($parameter > 0) ? " WHERE users.use_id = '$parameter' AND " : "";
        $query .= ($parameter > 0) ? " us_status ='1';" : "WHERE us_status ='1';";
        //echo $query;
        $statement = Connection::connection()->prepare($query);
        $statement->execute();
        $result=$statement->fetchAll();
        return $result;
    }
    static public function login($data){
        $user = $data['use_mail']; 
        $pass = md5($data['use_pass']); 
        
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
        $query="SELECT us_identifier,us_key FROM users WHERE us_status = 1";
        $statement = Connection::connection()->prepare($query);
        $statement->execute();
        $result=$statement->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }
}
?>