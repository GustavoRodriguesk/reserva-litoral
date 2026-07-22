# MecDesk PMS — Reserva Litoral

MecDesk PMS é um sistema de gerenciamento de propriedades hoteleiras (Property Management System) moderno, seguro e multilocatário (Multi-tenant), projetado para lidar com múltiplos hotéis e inquilinos sob um único banco de dados PostgreSQL estruturado em múltiplos schemas (`core`, `iam`, `booking`, `crm`, `finance`, `ops`, `pos`).

---

## 🏗️ Arquitetura e Segurança (Multitenancy & RLS)

A segurança do sistema é baseada no conceito **Fail-Closed** utilizando **Row Level Security (RLS)** nativo do PostgreSQL.

### 1. Funcionamento do RLS
*   Cada transação ou conexão vinda do Laravel executa uma instrução de contexto no início da requisição:
    ```sql
    SET LOCAL app.current_tenant = '<UUID-DO-TENANT>';
    SET LOCAL app.current_user = '<UUID-DO-USUARIO>';
    ```
*   A função estável `iam.current_tenant_id()` resolve o tenant atual a partir desse contexto.
*   Políticas RLS nas tabelas filtram e bloqueiam automaticamente qualquer vazamento de dados entre locatários (ex: `tenant_id = iam.current_tenant_id()`).

### 2. Autenticação Segura (Bypass do RLS)
*   Como a autenticação do Laravel precisa validar credenciais e recuperar o usuário antes de saber a qual Tenant ele pertence, criamos um mecanismo de bypass seguro:
    *   **`IamUserProvider`**: Substitui o UserProvider nativo do Laravel.
    *   **Functions `SECURITY DEFINER`**: O arquivo `2026_07_13_213726_create_auth_functions.php` cria funções PostgreSQL de propriedade do superusuário que buscam usuários e senhas por e-mail, encapsulando e limitando o acesso de leitura a tabelas seguras sem comprometer o RLS do usuário da aplicação normal.

---

## 🛡️ Middlewares no Laravel

Configurados no pipeline global em `bootstrap/app.php`:
*   `SetTenantContext`: Configura as variáveis de sessão no PostgreSQL (`app.current_tenant` e `app.current_user`) para cada requisição ativa do usuário logado.
*   `EnsureTenantExists`: Garante que o locatário está ativo e que o usuário tem um tenant atribuído.
*   `EnsureActiveUser`: Impede o login de usuários desativados ou bloqueados no sistema.
*   `UpdateLastActivity`: Registra as atualizações de atividade de auditoria e segurança.

---

## 📦 Módulos Principais Implementados

### 1. Módulo de Hóspedes (CRM)
*   **Rotas:** `/dashboard/guests`
*   **Funcionalidades:**
    *   Cadastro e edição com FormRequests (`StoreGuestRequest`, `UpdateGuestRequest`).
    *   Validação exclusiva por tenant: impede e-mails e CPF/Documentos duplicados dentro do mesmo Tenant, mas permite a locatários diferentes terem cadastros com o mesmo e-mail.
    *   Soft Delete ativado utilizando a coluna `deleted_at`.
    *   Busca avançada e paginada por Nome, E-mail, Documento (CPF/Passaporte) ou Telefone.

### 2. Módulo de Reservas (Booking)
*   **Wizard Multi-Passo (`/reservations/create`):**
    Construído com **Alpine.js** de forma a não recarregar a página e simplificar a entrada de dados em 6 etapas:
    *   **Passo 1 (Datas):** Seleção de período e controles interativos com botões **+** e **−** para quantidade de Adultos e Crianças.
    *   **Passo 2 (Quarto):** Busca assíncrona de quartos livres via `apiAvailability`, estimando o valor total de diárias.
    *   **Passo 3 (Hóspede):** Busca de hóspedes existentes em tempo real via AJAX ou abertura de modal para criação rápida de novos hóspedes sem perder o fluxo do wizard.
    *   **Passo 4 (Extras):** Seleção de extras opcionais (Café da Manhã, Estacionamento, Cama Extra, Pet, Berço) com cálculo dinâmico de precificação (ex. diário, por hóspede ou taxa única).
    *   **Passo 5 (Pagamento):** Definição da forma de pagamento inicial (PIX, Cartão, Dinheiro ou registrar Pendente).
    *   **Passo 6 (Resumo):** Revisão completa dos totais e campo de observações internas antes da chamada do serviço de criação.

### 3. Detalhes da Reserva & Ações (`/reservations/{id}`)
*   Visualização rica e profissional da estadia do hóspede.
*   **Timeline de Eventos:** Histórico cronológico interativo (`reservation_events`) alimentado a cada ação do sistema (criação, check-in, cobranças extras, etc.).
*   **Controle de Estadia:**
    *   *Check-in:* Cria o registro em `booking.checkins` e altera o status do quarto no banco para `occupied`.
    *   *Check-out:* Cria o registro em `booking.checkouts` e altera o quarto para status de limpeza `cleaning`.
    *   *Cancelamento:* Cancela a reserva e libera o quarto imediatamente.
*   **Lançamentos Financeiros:** Formulários integrados em modais rápidos para adicionar novas cobranças/descontos (`reservation_charges`) e registrar pagamentos (`payments`), atualizando e recalculando o saldo devedor em tempo real.

### 4. Emissão de Faturas (`invoices`)
*   Fluxo de faturamento no menu de Ações da Reserva.
*   **Geração:** Cria uma nova fatura com status `issued`, consolidando todas as cobranças e descontos gerados.
*   **View Imprimível:** Tela limpa (sem navbar) ideal para impressão física ou exportação para PDF, contendo dados completos do hotel, do hóspede, detalhamento de itens e saldo final a pagar.

---

## 🛠️ Como Executar e Desenvolver

1.  **Configuração do Ambiente:**
    Verifique as credenciais no arquivo `.env` para o banco de dados PostgreSQL.
2.  **Preparar o Banco:**
    Caso esteja recomeçando o banco, execute as migrations:
    ```bash
    php artisan migrate:fresh --seed
    ```
3.  **Servidor de Desenvolvimento:**
    Inicie o servidor local:
    ```bash
    php artisan serve
    ```
    E inicie o Vite para compilar os assets CSS/JS em tempo real:
    ```bash
    npm run dev
    ```
