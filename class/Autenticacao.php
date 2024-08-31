<?php
class Autenticacao {
    private $usuario;

    public function __construct($usuario = null){
        $this->usuario = $usuario;
        session_start();
    }

    public function login($email, $senha){
        $userId = $this->usuario->verificaLogin($email, $senha);
        if ($userId) {
            $_SESSION['id'] = $userId;
            $_SESSION['email'] = $email;
            $_SESSION['user'] = $this->usuario->getUser($email);
            return true;
        } else {
            return false;
        }
    }
    
    public function getUser(){
        return isset($_SESSION['user']) && $_SESSION['user'];
    }

    public function logout(){
        session_unset();
        session_destroy();
    }

    public function estaLogado(){
        return isset($_SESSION['email']);
    }

    public function getSession() {
        if ($this->estaLogado()) {
            return array(
                'id' => $_SESSION['id'],
                'email' => $_SESSION['email'],
                'user' => $_SESSION['user']
            );
        } else {
            return null;
        }
    }
}
?>
