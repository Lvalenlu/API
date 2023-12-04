<?php
Class UserController{
    private $_method; //get, post, put
    private $_complement; //get user 1 o 2
    private $_data; //datos a insertar o actualizar

    function __construct($method,$complement,$data){
        $this->_method = $method;
        $this->_complement = $complement;
        $this->_data = $data !=0 ? $data : "";
    }
    public function index(){
        switch ($this->_requestType) {
            case "GET":
                $this->getRequest();
                break;
            case "POST":
                $this->postRequest();
                break;
            case "PUT":
                $this->putRequest();
                break;
            case "DELETE":
                $this->deleteRequest();
                break;
        }
    }

    private function generateSalting(){
        if (!empty($data)) {
            $trimmed_data = array_map('trim', $data);
            $hashedPassword = password_hash($trimmed_data['use_pass'], PASSWORD_DEFAULT);

            // Salting
            $identifier = str_replace("$", "y78", crypt($trimmed_data['use_mail'], 'ser3478'));
            $key = str_replace("$", "ERT", crypt($hashedPassword, '$uniempresarial2024'));

            $trimmed_data['us_identifier'] = $identifier;
            $trimmed_data['us_key'] = $key;

            return $trimmed_data;
        }

    }

    private function getRequest() {
        switch ($this->_complement) {
            case 0:
                $user = UserModel::getUsers(0);
                $json = $user;
                break;
            default:
                $user = UserModel::getUsers($this->_complement);
                $json = $user == null ? ["mensaje" => "No existe el usuario"] : $user;
                break;
        }
        echo json_encode($json);
    }
    private function postRequest() {
        $createUser = UserModel::createUser($this->generateSalting());
        $json = ["result" => $createUser];
        echo json_encode($json, true);
    }

    private function putRequest() {
        $PartsComp = explode("/", $this->_complement);
        $BaseComp = $PartsComp[0];
        $id = $PartsComp[1];
      
        if ($BaseComp === "activate") {
            $activateUser = UserModel::activateUser($id);
            $json = ["response" => $activateUser];
            echo json_encode($json, true);
        } else {
            $createUser = UserModel::update($this->_complement, $this->_data);
            $json = ["response" => $createUser];
            echo json_encode($json, true);
        }
    }

    private function deleteRequest() {
        $createUser = UserModel::updateStatus($this->_complement);
        $json = ["response" => $createUser];
        echo json_encode($json, true);
    }
}
?>
