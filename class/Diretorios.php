<?php
require_once 'Crud.php';

class Diretorios {
    private $crud;

    public function __construct() {
        $this->crud = new Crud();
    }

    public function clonarRepositorio($user, $id_usuario, $repositorio) {
        $baseDir = '../../';
        $userDir = $baseDir . $user;

        // Verifica se estamos no diretório correto
        exec('ls ' . escapeshellarg($baseDir), $output, $returnVar);

        // Exibe o resultado do comando
        if ($returnVar === 0) {
            echo 'Conteúdo do diretório base (' . $baseDir . '):<br>';
            echo '<pre>' . implode("\n", $output) . '</pre>';
        } else {
            echo 'Erro ao listar o diretório base.';
        }
        exit;
        
        $baseDir = '../../gh/';
        $userDir = $baseDir . $user;

        // Cria o diretório do usuário se não existir
        if (!file_exists($userDir)) {
            mkdir($userDir, 0777, true);
        }

        $repoUrl = "https://github.com/$user/$repositorio.git";
        $cloneDir = "$userDir/$repositorio";

        // Clona o repositório
        $cloneCommand = "git clone $repoUrl $cloneDir";
        exec($cloneCommand, $output, $returnVar);

        if ($returnVar === 0) {
            // Salva no banco de dados
            $data = [
                'nome' => $repositorio,
                'id_usuario' => $id_usuario,
                'data_atualizado' => date('Y-m-d H:i:s')
            ];
            return $this->crud->create('diretorio', $data);
        } else {
            return false;
        }
    }

    public function listarRepositorios($id_usuario) {
        $repositorios = $this->crud->read('diretorio', ['id_usuario' => $id_usuario]);
        $this->verificarRepositorios($id_usuario, $repositorios);
        return $repositorios;
    }

    public function verificarRepositorios($id_usuario, &$repositorios) {
        $baseDir = '../../gh/';
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
            $repoDir = "../../gh/$user/$nome";

            // Remove o diretório do repositório
            $this->deleteDirectory($repoDir);

            // Remove do banco de dados
            return $this->crud->delete('diretorio', ['id' => $id_repositorio]);
        } else {
            return false;
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
