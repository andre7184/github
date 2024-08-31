<?php
require_once 'Crud.php';

class Diretorios {
    private $crud;

    public function __construct() {
        $this->crud = new Crud();
    }

    private function runCommand($command) {
        $descriptorspec = array(
            0 => array("pipe", "r"),  // stdin
            1 => array("pipe", "w"),  // stdout
            2 => array("pipe", "w")   // stderr
        );

        $process = proc_open($command, $descriptorspec, $pipes);

        if (is_resource($process)) {
            $output = stream_get_contents($pipes[1]);
            $errorOutput = stream_get_contents($pipes[2]);
            fclose($pipes[1]);
            fclose($pipes[2]);
            $returnCode = proc_close($process);

            return array('output' => $output, 'error' => $errorOutput, 'return_code' => $returnCode);
        }

        return null;
    }

    public function clonarRepositorio($user, $id_usuario, $repositorio, $linguagem) {
        $baseDir = realpath(__DIR__ . '/../../gh/') . '/';
        $userDir = $baseDir . $user;
    
        if (!is_dir($baseDir)) {
            return ['success' => false, 'msg' => 'Diretório base não encontrado.'];
        }
    
        if (!file_exists($userDir)) {
            if (!mkdir($userDir, 0777, true)) {
                return ['success' => false, 'msg' => 'Não foi possível criar o diretório do usuário.'];
            }
        }
    
        $repoUrl = "https://github.com/$user/$repositorio.git";
        
        if (is_dir($userDir .'/'. $repositorio . '/.git')) {
            // Diretório já contém um repositório Git, fazer pull
            $command = "cd " . escapeshellarg($userDir) . " && git pull";
        } else {
            // Diretório não contém um repositório Git, clonar
            $command = "git clone $repoUrl " . escapeshellarg($userDir);
        }
        $result = $this->runCommand($command);
    
        if ($result === null || $result['return_code'] !== 0) {
            return ['success' => false, 'msg' => 'Erro ao clonar/atualizar o repositório: ' . $result['error']];
        }
    
        $data = [
            'nome' => $repositorio,
            'id_usuario' => $id_usuario,
            'linguagem' => $linguagem
        ];
        $status =$this->crud->create('diretorio', $data);
        if($status){
            return ['success' => true, 'msg' => 'tabela diretorio criada com sucesso.'];
        }else{
            return ['success' => false, 'msg' => 'Erro ao criar tabela diretório.'];
        }
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

    public function removerRepositorio($user,$id_diretorio) {
        $diretorio = $this->crud->read('diretorio', ['id' => $id_diretorio]);
        if ($diretorio) {
            $nome = $diretorio[0]['nome'];
            $repoDir = realpath(__DIR__ . '/../../gh/') . "/".$user.'/'.$nome;

            // Remove o diretório do repositório
            $this->deleteDirectory($repoDir);

            // Remove do banco de dados
            $status = $this->crud->delete('diretorio', ['id' => $id_diretorio]);
            if($status){
                return ['success' => true, 'msg' => 'tabela diretorio removida com sucesso.'];
            }else{
                return ['success' => false, 'msg' => 'Erro ao remover a tabela diretorio.'];
            }
        } else {
            return ['success' => false, 'msg' => 'Diretório não encontrado.'];
        }
    }

    public function atualizarRepositorio($user,$id_diretorio) {
        $diretorio = $this->crud->read('diretorio', ['id' => $id_diretorio]);
        if ($diretorio) {
            $nome_diretorio = $diretorio[0]['nome'];
            $linguagem = $diretorio[0]['linguagem'];
            $baseDir = realpath(__DIR__ . '/../../gh/') . '/';
            $repoDir = $baseDir . $user .'/'. $nome_diretorio;

            if (is_dir($repoDir)) {
                $command = "cd " . escapeshellarg($repoDir) . " && git pull";
                $result = $this->runCommand($command);

                if ($result === null || $result['return_code'] !== 0) {
                    return ['success' => false, 'msg' => 'Erro ao atualizar repositório: ' . $result['error']];
                }
                // Atualiza os dados do repositório no banco de dados
                $data = [
                    'linguagem' => $linguagem
                ];
                $status = $this->crud->update('diretorio', $data, ['id' => $id_diretorio]);
                if($status){
                    return ['success' => true, 'msg' => 'Repositório atualizado com sucesso.'];
                }else{
                    return ['success' => false, 'msg' => 'Erro ao atualizar repositório.'];
                }
            } else {
                return ['success' => false, 'msg' => 'Repositório não encontrado.'];
            }
        } else {
            return ['success' => false, 'msg' => 'Repositório não encontrado.'];
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
