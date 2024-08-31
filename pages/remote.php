<?php
require_once '../config/config.php';
require_once '../class/Usuario.php';
require_once '../class/Autenticacao.php';

date_default_timezone_set('America/Sao_Paulo');
setlocale(LC_TIME, 'pt_BR.UTF-8');

$usuario = new Usuario();
$autenticacao = new Autenticacao($usuario);

$dados = array();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $usuario->sanitize($_POST['acao']);
    if ($acao === 'fazer_login'){
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
                $linguagem = $usuario->sanitize($_POST['linguagem']);
                $resultado = $diretorios->clonarRepositorio($user, $id_usuario, $nome_diretorio, $linguagem);
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
