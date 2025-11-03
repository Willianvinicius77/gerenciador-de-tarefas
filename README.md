# API Gerenciador de Tarefas (PHP + SQLite)

Estrutura do projeto:
```
tarefas-php-api/
├─ public/
│  └─ index.php        # ponto de entrada (servidor embutido PHP)
├─ src/
│  ├─ Controller.php   # lógica das rotas
│  └─ Database.php     # conexão e inicialização SQLite
├─ data/
│  └─ (database será criado automaticamente) 
├─ openapi.json        # documentação OpenAPI (básica)
└─ README.md
```

## Requisitos
- PHP 7.4+ (recomendo PHP 8.x)
- PDO SQLite habilitado

## Como rodar
1. Entre na pasta `public`:
   ```
   cd public
   ```
2. Rode o servidor embutido do PHP na porta 3001:
   ```
   php -S localhost:3001
   ```
   (ou `php -S 0.0.0.0:3001` para aceitar conexões externas)

A API estará disponível em `http://localhost:3001`.

## Rotas
- `GET /` → rota de teste
- `GET /tarefas` → listar tarefas
- `POST /tarefas` → criar tarefa (JSON)
- `PUT /tarefas/{id}` → atualizar tarefa (JSON)
- `DELETE /tarefas/{id}` → deletar tarefa

## Validação
- Validação simples feita com PHP (campos obrigatórios: título)

## CORS
- CORS configurado para permitir origens (`*`) e métodos necessários.

## OpenAPI / Swagger
- O arquivo `openapi.json` oferece uma especificação mínima. Você pode servir essa especificação no Swagger UI (https://swagger.io/tools/swagger-ui/) apontando para `http://localhost:3001/openapi.json`.