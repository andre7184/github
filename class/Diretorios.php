<?php
require_once 'Crud.php';

class Diretorios {
    private $crud;

    public function __construct() {
        $this->crud = new Crud();
    }

    public function clonarRepositorio($user, $id_usuario, $repositorio) {
        $baseDir = realpath(__DIR__ . '/../../gh/') . '/';
        $userDir = $baseDir . $user;

        if (!is_dir($baseDir)) {
            return array(
                'status' => false,
                'msg' => 'Diretório base não encontrado.'
            );
        }

        // Cria o diretório do usuário se não existir
        if (!file_exists($userDir)) {
            if (!mkdir($userDir, 0777, true)) {
                return array(
                    'status' => false,
                    'msg' => 'Não foi possível criar o diretório do usuário.'
                );
            }
        }

        $repoUrl = "https://github.com/$user/$repositorio.git";

        // Clona o repositório usando shell_exec
        $command = "git clone $repoUrl " . escapeshellarg($userDir);
        $output = shell_exec($command);

        if ($output === null) {
            return array(
                'status' => false,
                'msg' => 'Erro ao clonar o repositório.'
            );
        }

        // Salva no banco de dados
        $data = [
            'nome' => $repositorio,
            'id_usuario' => $id_usuario,
            'data_atualizado' => date('Y-m-d H:i:s')
        ];
        return $this->crud->create('diretorio', $data);
    }

    public function listarRepositorios($id_usuario) {
        $repositorios = $this->crud->read('diretorio', ['id_usuario' => $id_usuario]);
        $this->verificarRepositorios($id_usuario, $repositorios);
        return $repositorios;
    }

    public function verificarRepositorios($id_usuario, &$repositorios) {
        $baseDir = realpath(__DIR__ . '/../../gh/') . '/';
        $userDir = $baseDir . $id_usuario;

        foreach ($repositorios as &$repositorio) {
            $repoDir = "$userDir/{$repositorio['nome']}";
            $repositorio['existe'] = file_exists($repoDir);
        }
    }

    public function removerRepositorio($id_repositorio) {
        $repositorio = $this->crud->read('diretorio', ['id' => $id_repositorio]);
        if ($repositorio) {
            $user = $repositorio[0]['id_usuario'];
            $nome = $repositorio[0]['nome'];
            $repoDir = realpath(__DIR__ . '/../../gh/') . "/".$user.'/'.$nome;

            // Remove o diretório do repositório
            $this->deleteDirectory($repoDir);

            // Remove do banco de dados
            return $this->crud->delete('diretorio', ['id' => $id_repositorio]);
        } else {
            return false;
        }
    }

    public function atualizarRepositorio($user, $repositorio) {
        $baseDir = realpath(__DIR__ . '/../../gh/') . '/';
        $repoDir = $baseDir . $user .'/'. $repositorio;

        if (is_dir($repoDir)) {
            $command = "cd " . escapeshellarg($repoDir) . " && git pull";
            $output = shell_exec($command);

            if ($output === null) {
                return ['status' => 'error', 'message' => 'Erro ao atualizar repositório.'];
            }

            return ['status' => 'success', 'message' => 'Repositório atualizado com sucesso.'];
        } else {
            return ['status' => 'error', 'message' => 'Repositório não encontrado.'];
        }
    }

    private function deleteDirectory($dir) {
        if (!file_exists($dir)) {
            return true;
        }

        if (!is_dir($dir)) {
            return unlink($dir);
        }

        foreach (scandir($dir) as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            }

            if (!$this->deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
                return false;
            }
        }

        return rmdir($dir);
    }
}
?>
