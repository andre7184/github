<?php
$senha_admin='142536';
$empresa='Mercado online';

function enviarEmail($emailOrigem, $para, $assunto, $mensagem) {
    // echo '$emailOrigem.', '.$para.', '.$assunto.', '.$mensagem;'
    require '../../vendor/autoload.php';
    $mail = new PHPMailer\PHPMailer\PHPMailer();
    $mail->isSMTP();
    $mail->Host = 'sandbox.smtp.mailtrap.io'; // Substitua pelo seu servidor SMTP
    $mail->SMTPAuth = true;
    $mail->Username = '8fbfb0134d8a1a'; // Substitua pelo seu nome de usuário
    $mail->Password = '3e6b4df11f2ec3'; // Substitua pela sua senha
    $mail->SMTPSecure = 'tls';
    $mail->Port = 2525;

    $mail->setFrom($emailOrigem, 'Mailer'); // Substitua pelo seu endereço de e-mail
    $mail->addAddress($para);
    $mail->isHTML(true);
    $mail->CharSet = 'UTF-8';
    $mail->Subject = $assunto;
    $mail->Body    = $mensagem;

    if(!$mail->send()) {
        return 'A mensagem não pôde ser enviada.';
        return 'Erro do Mailer: ' . $mail->ErrorInfo;
    } else {
        return 'sucesso';
    }
}
?>