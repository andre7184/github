<?php
require_once '../config/config.php';
require_once '../class/Usuario.php';
require_once '../class/Autenticacao.php';

date_default_timezone_set('America/Sao_Paulo');
setlocale(LC_TIME, 'pt_BR.UTF-8');

$usuario = new Usuario();
$autenticacao = new Autenticacao($usuario);

// Verifica se a solicitação é GET e se a ação é authgithub
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['acao']) && $_GET['acao'] === 'authgithub') {
    $code = $_GET['code'];
    $state = $_GET['state'];
    $storedState = $_SESSION['oauth2state'];

    if ($state !== $storedState) {
        die('Estado inválido');
    }

    $clientId = 'Ov23litmC5N7kJpBy26i'; // Substitua pelo seu Client ID do GitHub
    $clientSecret = 'a3df6db1ddac45dd8b92db8d331584d1efc52880'; // Substitua pelo seu Client Secret do GitHub
    $redirectUri = 'https://vps52814.publiccloud.com.br/github/pages/remote.php?acao=authgithub';

    // Trocar o código de autorização por um token de acesso
    $tokenUrl = 'https://github.com/login/oauth/access_token';
    $postData = [
        'client_id' => $clientId,
        'client_secret' => $clientSecret,
        'code' => $code,
        'redirect_uri' => $redirectUri,
    ];

    $ch = curl_init($tokenUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);
    $response = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($response, true);
    $accessToken = $data['access_token'];

    // Obter informações do usuário
    $userUrl = 'https://api.github.com/user';
    $ch = curl_init($userUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $accessToken, 'User-Agent: YourAppName']);
    $userResponse = curl_exec($ch);
    curl_close($ch);

    $githubUser = json_decode($userResponse, true);

    // Verificar se o email está vazio
    if (empty($githubUser['email'])) {
        // Fazer uma solicitação adicional para obter os emails do usuário
        $emailsUrl = 'https://api.github.com/user/emails';
        $ch = curl_init($emailsUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $accessToken, 'User-Agent: YourAppName']);
        $emailsResponse = curl_exec($ch);
        curl_close($ch);

        $emails = json_decode($emailsResponse, true);
        print_r($emails);
        // Verificar se $emails é um array
        if (is_array($emails)) {
            // Procurar pelo email principal
            foreach ($emails as $email) {
                // Verificar se $email é um array e se contém as chaves 'primary' e 'verified'
                if (is_array($email) && isset($email['primary']) && isset($email['verified']) && $email['primary'] && $email['verified']) {
                    $githubUser['email'] = $email['email'];
                    break;
                }
            }
        }
    }

    // Autenticar o usuário
    $retorno = $autenticacao->loginWithGithub($githubUser);

    if ($retorno) {
        // Redirecionar para a página home.html após o login bem-sucedido
        header('Location: home.html');
        exit();
    }else{
        ?>
        <!DOCTYPE html>
        <html lang="pt-BR">
        <head>
            <meta charset="UTF-8">
            <title>Redirecionamento</title>
            <script>
                // Função para redirecionar após o tempo de espera
                function redirecionar() {
                    setTimeout(function() {
                        window.location.href = "index.html";
                    }, 5000); // Converte segundos para milissegundos
                }
            </script>
        </head>
        <body onload="redirecionar()">
            <p><?php echo 'Falha no login do Github. Redirecionando ...'; ?></p>
        </body>
        </html>
    <?php
    }
    exit();
}
$dados = array();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $usuario->sanitize($_POST['acao']);
    if ($acao === 'salvar_stategithub') {
        $state = $usuario->sanitize($_POST['state']);
        $_SESSION['oauth2state'] = $state;
        $dados['success'] = true;
        $dados['message'] = 'State salvo com sucesso!';
    }else if ($acao === 'fazer_login'){
        $email = $usuario->sanitize($_POST['email']);
        $senha = $usuario->sanitize($_POST['senha']);
    
        // Tentando fazer login
        $login = $autenticacao->login($email, $senha);
        if ($login) {
            $dados['success'] = true;
            $dados['message'] = 'Login bem-sucedido!';
        }else{
            $dados['success'] = false;
            $dados['message'] = 'Falha no login. Verifique seu email e senha.';
        }
    } else if ($acao === 'recuperar_senha') {
        $email = $usuario->sanitize($_POST['email']);
        // Chama o método de recuperação de senha
        $novaSenha = $usuario->recuperarSenha($email);
        if ($novaSenha) {
            $nomeUser = $usuario->getUser($email);
            $url_login = "http://localhost/mercado_online/index.html?pg=login.html"; 
            $assunto = "Recuperação de Senha";
            $corpoEmail = "Olá $nomeCliente,<br><br>Recebemos uma solicitação para redefinir sua senha.<br><br>Sua nova senha é: <b>$novaSenha</b><br><a href='$url_login'>$url_login</a><br>Acesse para entrar com sua nova senha.<br><br><br>Se você não solicitou uma redefinição de senha, por favor, ignore este e-mail.<br><br>Atenciosamente,<br><b>Equipe $empresa</b>";
            $emailOrigem = "tubarao84@gmail.com>"; 
            $enviado=enviarEmail($emailOrigem, $email, $assunto, $corpoEmail);
            if ($enviado=='sucesso') {
                $dados['status'] = 'success';
                $dados['message'] = 'Uma nova senha foi enviada para o seu e-mail.';
            } else {
                $dados['status'] = 'error';
                $dados['message'] = $enviado;
            }
        } else {
            // O e-mail não está registrado
            $dados['status'] = 'error';
            $dados['message'] = 'E-mail não cadastrado.';
        }
    } else if ($acao === 'cadastrar_usuario') {
        $user = $usuario->sanitize($_POST['user']);
        $email = $usuario->sanitize($_POST['email']);
        $senha = $usuario->sanitize($_POST['senha']);
        if ($usuario->emailCadastrado($email)) {
            $dados['status'] = 'error';
            $dados['message'] = 'Email já está registrado.';
        } else {
            // Registre o novo usuário
            if ($usuario->cadastraUsuario($user, $email, $senha)) {
                $dados['status'] = 'success';
                $dados['message'] = 'Registro bem-sucedido.';
            } else {
                $dados['status'] = 'error';
                $dados['message'] = 'Falha no registro.';
            }
        }
    } else if ($acao === 'verifica_user') {
        $user = $usuario->sanitize($_POST['user']);
        // verificar se o usuario logado é admin
        if ($usuario->userCadastrado($user)) {
            $dados['status'] = 'error';
            $dados['message'] = 'User já está registrado.';
        } else {
            $dados['status'] = 'success';
            $dados['message'] = 'User disponível.';
        }
    } else { 
        if ($autenticacao->estaLogado()){
            $arrayDados = $autenticacao->getSession();
            $user = $arrayDados['user'];
            $id_usuario = $arrayDados['id'];
            $email = $arrayDados['email'];

            require_once '../class/Diretorios.php';
            $diretorios = new Diretorios();

            if ($acao === 'dados_do_usuario') {
                $conditions = ['id' => $id_usuario];
                $linha_usuario = $usuario->listarUsuario($conditions)[0];
                unset($linha_usuario['senha']);
                $dados['user']=$linha_usuario;
                $dados['diretorios'] = $diretorios->listarRepositorios($id_usuario);

            } else if ($acao === 'alterar_usuario'){
                $novo_email = $usuario->sanitize($_POST['email']);
                $data= ['email'=>$novo_email];
                $conditions = ['id' => $id_usuario];
                $linha_usuario = $usuario->AlterarUsuario($data, $conditions);      
                if ($linha_usuario['success']){
                    $dados['status'] = 'success';
                    $dados['message'] = 'Usuário alterado com sucesso.';
                }else{
                    $dados['status'] = 'error';
                    $dados['message'] = $linha_usuario['msg'];
                }

            } else if ($acao === 'alterar_senha'){
                $nova_senha = $usuario->sanitize($_POST['senha']);
                $senha = password_hash($nova_senha, PASSWORD_DEFAULT); 
                $data = ['senha'=>$senha];
                $conditions = ['id' => $id_usuario];
                $usuario = $usuario->AlterarUsuario($data, $conditions);      
                if ($usuario){
                    $dados['status'] = 'success';
                    $dados['message'] = 'Senha alterada com sucesso.';
                }else{
                    $dados['status'] = 'error';
                    $dados['message'] = 'Falha na alteração.';
                }
            } else if ($acao === 'clonar_repositorio') {
                $nome_diretorio = $usuario->sanitize($_POST['repositorio']);
                $resultado = $diretorios->clonarRepositorio($user, $id_usuario, $nome_diretorio);
                if ($resultado['success']) {
                    $dados['status'] = 'success';
                    $dados['message'] = 'Repositório clonado com sucesso!';
                } else {
                    $dados['status'] = 'error';
                    $dados['message'] = $resultado['msg'];
                }
            } else if ($acao === 'atualizar_repositorio') {
                $id_diretorio = $usuario->sanitize($_POST['id_diretorio']);
                if ($id_diretorio) {
                    $resultado = $diretorios->atualizarRepositorio($user,$id_diretorio);
                    if ($resultado['success']) {
                        $dados['status'] = 'success';
                        $dados['message'] = 'Repositório atualizado com sucesso!';
                    } else {
                        $dados['status'] = 'error';
                        $dados['message'] = $resultado['msg'];
                    }
                } else {
                    $dados['status'] = 'error';
                    $dados['message'] = 'Id do diretorio inexistente!';
                }
            } else if ($acao === 'listar_repositorios') {
                $host = "https://".$_SERVER['HTTP_HOST'];
                $repositorios = $diretorios->listarRepositorios($id_usuario);
                foreach ($repositorios as $i => $valor) {
                    if ($valor['data_atualizado'] !== '0000-00-00 00:00:00') {
                        $data_atualizado = new DateTime($valor['data_atualizado']);
                        $data_atualizado = $data_atualizado->format('d/m/Y H:i:s');
                    }
                    unset($repositorios[$i]['existe']);
                    $repositorios[$i]['url'] = $host.'/gh/'.$user.'/'.$valor['nome'];
                    $repositorios[$i]['data_atualizado'] = $data_atualizado;
                }
                $dados['status'] = 'success';
                $dados['repositorios'] = $repositorios;
            } else if ($acao === 'remover_repositorio') {
                $id_diretorio = $usuario->sanitize($_POST['id_diretorio']);
                if ($id_diretorio) {
                    $resultado = $diretorios->removerRepositorio($user,$id_diretorio);
                    if ($resultado['success']) {
                        $dados['status'] = 'success';
                        $dados['message'] = 'Repositório removido com sucesso!';
                    } else {
                        $dados['status'] = 'error';
                        $dados['message'] = $resultado['msg'];
                    }
                } else {
                    $dados['status'] = 'error';
                    $dados['message'] = 'ID do diretorio não fornecido.';
                }
            }else if ($acao === 'logout'){
                $autenticacao->logout();
                $dados['status'] = 'logged_out';
            }
        }else{
            $dados=['naoautenticado' => true];
        }
    }
} else {
    $dados['status'] = 'error';
    $dados['message'] = 'Método de solicitação inválido.';
}

header('Content-Type: application/json');
echo json_encode($dados);
?>
