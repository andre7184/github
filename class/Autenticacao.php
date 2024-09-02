<?php
class Autenticacao {
    private $usuario;

    public function __construct($usuario = null){
        $this->usuario = $usuario;
        session_name('git_home'); // Defina um nome único para a sessão
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
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
        // Limpar todas as variáveis de sessão
        session_unset();
            
        // Destruir a sessão
        session_destroy();

        // Opcional: Limpar cookies de sessão
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
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

    public function loginWithGithub($githubUser) {
        $email = $githubUser['email'];
        $user = $githubUser['login'];
        $githubId = $githubUser['id']; // ID único do GitHub
        $nome = $githubUser['name'];
        $avatarUrl = $githubUser['avatar_url'];

        echo 'loginWithGithub: ' . $user . ' ' . $email . ' ' . $githubId . ' ' . $nome . ' ' . $avatarUrl;
        // Verifica se o usuário já está cadastrado pelo nome de usuário
        if (!$this->usuario->userCadastrado($user)) {
            // Cadastra o usuário com os dados do GitHub
            $this->usuario->cadastraUsuarioGitHub($user, $email, $githubId, $nome, $avatarUrl);
        } else {
            // Atualiza os dados do usuário com os dados do GitHub
            $this->usuario->atualizarUsuarioGitHub($user, $email, $githubId, $nome, $avatarUrl);
        }

        // Obtém o ID do usuário
        $userId = $this->usuario->verificaLogin($user, $githubId);

        // Inicia a sessão do usuário
        if ($userId) {
            $_SESSION['id'] = $userId;
            $_SESSION['email'] = $email;
            $_SESSION['user'] = $user;
            return true;
        }else{
            return false;
        }
    }
}

?>
