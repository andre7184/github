<?php
require_once '../config/config.php';
require_once '../class/Usuario.php';
require_once '../class/Autenticacao.php';

$usuario = new Usuario();
$autenticacao = new Autenticacao($usuario);

$dados = array();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $usuario->sanitize($_POST['acao']);
    if ($acao === 'recuperar_senha') {
        $email = $usuario->sanitize($_POST['email']);
        // Chama o método de recuperação de senha
        $novaSenha = $usuario->recuperarSenha($email);
        if ($novaSenha) {
            $nomeCliente = $usuario->getNome($email);
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
        $name = $usuario->sanitize($_POST['nome']);
        $email = $usuario->sanitize($_POST['email']);
        $senha = $usuario->sanitize($_POST['senha']);
        // verificar se o usuario logado é admin
        if ($autenticacao->estaLogado() && $autenticacao->eAdmin()) {
            $tipo_admin = true;
        } else {
            $tipo_admin = false;
        }
        if ($usuario->emailCadastrado($email)) {
            $dados['status'] = 'error';
            $dados['message'] = 'Email já está registrado.';
        } else {
            // Registre o novo usuário
            if ($usuario->cadastraUsuario($name, $email, $senha, $tipo_admin)) {
                $dados['status'] = 'success';
                $dados['message'] = 'Registro bem-sucedido.';
            } else {
                $dados['status'] = 'error';
                $dados['message'] = 'Falha no registro.';
            }
        }
    } else if ($acao === 'verifica_nomeusuario') {
        $name = $usuario->sanitize($_POST['nome']);
        $email = $usuario->sanitize($_POST['email']);
        $senha = $usuario->sanitize($_POST['senha']);
        // verificar se o usuario logado é admin
        if ($autenticacao->estaLogado() && $autenticacao->eAdmin()) {
            $tipo_admin = true;
        } else {
            $tipo_admin = false;
        }
        if ($usuario->emailCadastrado($email)) {
            $dados['status'] = 'error';
            $dados['message'] = 'Email já está registrado.';
        } else {
            // Registre o novo usuário
            if ($usuario->cadastraUsuario($name, $email, $senha, $tipo_admin)) {
                $dados['status'] = 'success';
                $dados['message'] = 'Registro bem-sucedido.';
            } else {
                $dados['status'] = 'error';
                $dados['message'] = 'Falha no registro.';
            }
        }
    } else { 
        if ($autenticacao->estaLogado()){
            if ($acao === 'dados_do_usuario') {
                // Busca os dados do usuário
                if (isset($_POST['id'])){
                    $conditions = ['id' => $usuario->sanitize($_POST['id'])];
                } else {
                    $conditions = ['id' => $_SESSION['id']];
                }
                $linha_usuario = $usuario->listarUsuario($conditions)[0];
                unset($linha_usuario['senha']);
                if ($linha_usuario['admin'] == 1) {
                    $linha_usuario['admin'] = 'Admin';
                    $linha_usuario['qtd_historico'] = $usuario->qtdTransacoes();  
                } else {
                    $linha_usuario['admin'] = 'Normal';
                    $linha_usuario['qtd_historico'] = $usuario->qtdTransacoes(['id_usuario' => $_SESSION['id']]);  
                }
                $dados=$linha_usuario;
            } else if ($acao === 'alterar_usuario'){
                $nome = $usuario->sanitize($_POST['nome']);
                $email = $usuario->sanitize($_POST['email']);
                $id = $usuario->sanitize($_POST['id']);
                $data= ['nome'=>$nome,'email'=>$email];
                $conditions = ['id' => $id];
                $linha_usuario = $usuario->AlterarUsuario($data, $conditions);      
                if ($linha_usuario){
                    $dados['status'] = 'success';
                    $dados['message'] = 'Usuário alterado com sucesso.';
                }else{
                    $dados['status'] = 'error';
                    $dados['message'] = 'Falha na alteração.';
                }

            } else if ($acao === 'alterar_senha'){
                $senha = $usuario->sanitize($_POST['senha']);
                $id = $usuario->sanitize($_POST['id']); 
                $senha = password_hash($senha, PASSWORD_DEFAULT); 
                $data = ['senha'=>$senha];
                $conditions = ['id' => $id];
                $usuario = $usuario->AlterarUsuario($data, $conditions);      
                if ($usuario){
                    $dados['status'] = 'success';
                    $dados['message'] = 'Senha alterada com sucesso.';
                }else{
                    $dados['status'] = 'error';
                    $dados['message'] = 'Falha na alteração.';
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
