<?php
    declare(strict_types=1);

    class Controller {
        private static function jsonInput() {
            $data = file_get_contents('php://input');
            $decoded = json_decode($data, true);
            return is_array($decoded) ? $decoded : [];
        }

        public static function test() {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'ok', 'message' => 'API de tarefas (PHP + SQLite) funcionando']);
        }

        public static function list() {
            $pdo = Database::getInstance();
            $stmt = $pdo->query('SELECT * FROM tarefas ORDER BY prioridade ASC, criado_em DESC');
            $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
            // Convertendo campo concluida para booleano
            foreach ($tasks as &$t) {
                $t['concluida'] = (bool)$t['concluida'];
            }
            header('Content-Type: application/json');
            echo json_encode($tasks);
        }

        public static function create() {
            $input = self::jsonInput();
            $errors = [];
            if (empty($input['titulo']) || !is_string($input['titulo'])) {
                $errors[] = 'Campo "titulo" é obrigatório e deve ser string.';
            }
            // prioridade opcional -> inteiro entre 1 e 5
            $prioridade = isset($input['prioridade']) ? (int)$input['prioridade'] : 3;
            if ($prioridade < 1 || $prioridade > 5) {
                $errors[] = 'Campo "prioridade" deve ser inteiro entre 1 e 5.';
            }
            if (!empty($errors)) {
                http_response_code(422);
                header('Content-Type: application/json');
                echo json_encode(['errors' => $errors]);
                return;
            }

            $descricao = isset($input['descricao']) ? $input['descricao'] : null;
            $concluida = !empty($input['concluida']) ? 1 : 0;

            $pdo = Database::getInstance();
            $stmt = $pdo->prepare('INSERT INTO tarefas (titulo, descricao, concluida, prioridade, criado_em) VALUES (:titulo, :descricao, :concluida, :prioridade, :criado_em)');
            $stmt->execute([
                ':titulo' => $input['titulo'],
                ':descricao' => $descricao,
                ':concluida' => $concluida,
                ':prioridade' => $prioridade,
                ':criado_em' => (new DateTime())->format(DateTime::ATOM),
            ]);

            $id = (int)$pdo->lastInsertId();
            http_response_code(201);
            header('Content-Type: application/json');
            echo json_encode(['id' => $id, 'message' => 'Tarefa criada com sucesso']);
        }

        public static function update(int $id) {
            $input = self::jsonInput();
            $pdo = Database::getInstance();
            $stmt = $pdo->prepare('SELECT * FROM tarefas WHERE id = :id');
            $stmt->execute([':id' => $id]);
            $task = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$task) {
                http_response_code(404);
                echo json_encode(['error' => 'Tarefa não encontrada']);
                return;
            }

            $fields = [];
            $params = [':id' => $id];

            if (isset($input['titulo'])) {
                if (!is_string($input['titulo']) || trim($input['titulo']) === '') {
                    http_response_code(422);
                    echo json_encode(['error' => 'Campo "titulo" inválido.']);
                    return;
                }
                $fields[] = 'titulo = :titulo';
                $params[':titulo'] = $input['titulo'];
            }
            if (array_key_exists('descricao', $input)) {
                $fields[] = 'descricao = :descricao';
                $params[':descricao'] = $input['descricao'];
            }
            if (array_key_exists('concluida', $input)) {
                $fields[] = 'concluida = :concluida';
                $params[':concluida'] = !empty($input['concluida']) ? 1 : 0;
            }
            if (array_key_exists('prioridade', $input)) {
                $p = (int)$input['prioridade'];
                if ($p < 1 || $p > 5) {
                    http_response_code(422);
                    echo json_encode(['error' => 'Campo "prioridade" deve ser entre 1 e 5.']);
                    return;
                }
                $fields[] = 'prioridade = :prioridade';
                $params[':prioridade'] = $p;
            }

            if (empty($fields)) {
                http_response_code(400);
                echo json_encode(['error' => 'Nenhum campo para atualizar.']);
                return;
            }

            $sql = 'UPDATE tarefas SET ' . implode(', ', $fields) . ' WHERE id = :id';
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            header('Content-Type: application/json');
            echo json_encode(['message' => 'Tarefa atualizada com sucesso']);
        }

        public static function delete(int $id) {
            $pdo = Database::getInstance();
            $stmt = $pdo->prepare('DELETE FROM tarefas WHERE id = :id');
            $stmt->execute([':id' => $id]);
            if ($stmt->rowCount() === 0) {
                http_response_code(404);
                echo json_encode(['error' => 'Tarefa não encontrada']);
                return;
            }
            echo json_encode(['message' => 'Tarefa deletada com sucesso']);
        }
    }