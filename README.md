# API de Gerenciamento de Cartões e Despesas


## 💳 Sobre o projeto

Esta é uma API robusta desenvolvida em Laravel 12 para gerenciar o ciclo de vida de cartões de crédito e o registro de despesas associadas. O sistema conta com controle de acesso baseado em perfis (Admin e Comum) e validações rigorosas de regras de negócio.

O desenvolvimento é guiado por princípios de qualidade de software, como modularidade, Programação Orientada a Objetos (POO), padrões SOLID e uma cobertura de testes de integração.

## ⚡ Funcionalidades Principais

### Usuários

**Registro e Autenticação:** Cadastro de novos usuários e autenticação via tokens Laravel Sanctum.

**Perfis de Acesso:** Distinção entre usuários admin e comum para controle de permissões.

### Cartões

**Gestão Completa:** Criação, atualização, visualização e exclusão de cartões.

**Validação de Número:** Algoritmo integrado para validar números de cartão de crédito via teste de Luhn.

**Controle de Status:** Gerenciamento de estados do cartão: ativo, bloqueado ou cancelado.

**Depósitos:** Adição de saldo em cartões ativos.

### Despesas

**Registro de Gastos:** Criação de despesas vinculadas a cartões específicos.

**Validação de Saldo:** Verificação automática de saldo insuficiente antes de processar compras.

**Notificações por E-mail:** Envio automático de alertas de novas despesas para o dono do cartão e cópia para administradores.

## 🛠️ Tecnologias Utilizadas

- **PHP 8.3**
- **Framework:** Laravel 12
- **Autenticação:** Sanctum
- **Banco de Dados:** SQLite
- **Infraestrutura:** Docker e Docker Compose (PHP 8.3-FPM + Nginx)
- **Testes:** PHPUnit 11.5

## 💻 Execução do Projeto

**Pré-requisitos**
- Docker instalado

### 🔑 Variáveis de Ambiente

Configuração de E-mail (MailerSend): Para o funcionamento dos alertas de despesas, crie um arquivo `.env` com base no `.env.example` e configure as seguintes chaves:
- `MAILERSEND_API_KEY`: Sua chave de API.
- `MAIL_FROM_ADDRESS`: E-mail remetente autorizado.
- `MAIL_MAILER=mailersend`

### 🚀 Instalação e execução

1. Clonar o repositório
```bash
git clone https://github.com/lucasfrgabriel/laravel-card-expense-api.git
cd laravel-card-expense-api
```

2. Subir os containers:
```bash
docker compose up -d --build
```

3. Acessear o container da aplicação:
```bash
 docker exec -it laravel_app bash
```

4. Gerar chave para aplicação e configurar o ambiente:
```bash
php artisan key:generate
php artisan optimize:clear
```

Adicione a chave gerada no `.env` em `APP_KEY`.

5. Executar as migrações (dentro do container):
```bash
php artisan migrate
```

## 🧪 Suíte de Testes
O projeto possui cobertura de testes unitários e de integração para garantir a estabilidade das funções críticas.

**Testes Unitários:** Validação isolada de Services e Utils.

**Testes de Feature:** Validação de endpoints, fluxos de autenticação e permissões de acesso.

**Para rodar os testes:**
```bash
php artisan test
```

## 📡 API

### 📄 Documentação Interativa (Swagger/OpenAPI)
A documentação completa dos endpoints, parâmetros e tipos de retorno está disponível em:
[http://localhost:8080/docs/api](http://localhost:8080/docs/api)

### Endpoints

A paginação das listagens pode ser controlada através do parâmetro opcional ?paginate, permitindo definir o número de itens desejados por requisição. Os resultados são paginados por padrão (10 itens).

#### Usuários
| Verbo    | Endpoint              | Protegido? | Descrição                                      |
|:---------|:----------------------|:-----------|:-----------------------------------------------|
| `POST`   | `/api/login`          | ❌ Não      | Autenticação e obtenção de token de acesso     |
| `POST`   | `/api/users/register` | ❌ Não      | Cadastra um novo usuário.                      |
| `GET`    | `/api/users`          | ✅ Sim      | Lista todos os usuários.                       |
| `GET`    | `/api/users/{user}`   | ✅ Sim      | Busca um usuário específico.                   |
| `PATCH`  | `/api/users/{user}`   | ✅ Sim      | Atualiza informações de um usuário específico. |
| `DELETE` | `/api/users/{user}`   | ✅ Sim      | Deleta um usuário específico.                  |

#### Cartões
| Verbo    | Endpoint                              | Protegido? | Descrição                                     |
|:---------|:--------------------------------------|:-----------|:----------------------------------------------|
| `POST`   | `/api/cards`                          | ✅ Sim      | Cadastra um novo cartão.                      |
| `POST`   | `/api/cards/{card}/deposit`           | ✅ Sim      | Realiza um novo depósito no cartão.           |
| `GET`    | `/api/cards`                          | ✅ Sim      | Lista todos os cartões.                       |
| `GET`    | `/api/cards/{card}`                   | ✅ Sim      | Lista um cartão específico.                   |
| `PATCH`  | `/api/cards/{card}`                   | ✅ Sim      | Atualiza informações de um cartão específico. |
| `PATCH`  | `/api/cards/{card}/status`            | ✅ Sim      | Atualiza o status de um cartão específico.    |
| `DELETE` | `/api/cards/{card}`                   | ✅ Sim      | Deleta um cartão específico.                  |

#### Despesas
| Verbo    | Endpoint                              | Protegido? | Descrição                                     |
|:---------|:--------------------------------------|:-----------|:----------------------------------------------|
| `POST`   | `/api/expenses`                       | ✅ Sim      | Cadastra uma nova despesa.                    |
| `GET`    | `/api/expenses`                       | ✅ Sim      | Lista todas as despesas.                      |
| `GET`    | `/api/expenses/{expense}`             | ✅ Sim      | Lista uma despesa específica.                 |
| `DELETE` | `/api/expenses/{expense}`             | ✅ Sim      | Deleta uma despesa específica.                |

## 🏗️ Estrutura de Pastas

O projeto foi desenvolvido seguindo padrões que visam facilidade de manutenção e escalabilidade.

```
app
├── Enums
├── Events
├── Exceptions
│   ├── Cards
│   ├── Expenses
│   └── Users
├── Http
│   ├── Controllers
│   ├── Requests
│   │   ├── Cards
│   │   ├── Expenses
│   │   └── Users
│   └── Resources
├── Listeners
├── Mail
├── Models
├── Policies
├── Providers
├── Repositories
└── Services
```
