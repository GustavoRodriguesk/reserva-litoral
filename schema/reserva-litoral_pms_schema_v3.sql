-- =====================================================================
-- MecDesk PMS/SaaS — Schema completo (PostgreSQL 15+)
-- Refatoração v2 a partir do schema inicial (900+ linhas -> multi-schema)
--
-- CONVENÇÕES GERAIS
-- - Chaves primárias em UUID (gen_random_uuid()), exceto tabelas de
--   lookup puro (countries, currencies, languages, reservation_statuses,
--   stay_statuses), que usam código natural como PK.
-- - Organização por schema (namespaces) em vez de um único "public":
--     iam          -> tenants, usuários, papéis/permissões, auditoria
--     core         -> hotéis, lookups globais (país/moeda/idioma), arquivos
--     crm          -> hóspedes, documentos, consentimentos LGPD, fidelidade
--     booking      -> quartos, tarifário (rate plans), disponibilidade, reservas
--     finance      -> planos/assinaturas do SaaS, pagamentos, faturas
--     ops          -> limpeza, manutenção, produtos/estoque, tarefas
--     pos          -> ponto de venda (comandas/pedidos), desacoplado de
--                     reserva — aceita hóspede, reserva ou cliente avulso
--     cms          -> conteúdo do site público (separado da operação)
--     comms        -> e-mails, notificações, mensageria (WhatsApp etc.)
--     integration  -> integrações externas (Booking, Airbnb, fechaduras...)
-- - Toda tabela "de negócio" carrega tenant_id e/ou hotel_id para
--   isolamento multi-tenant (um tenant pode ter vários hotéis).
-- - created_at/updated_at em TIMESTAMPTZ; updated_at mantido
--   automaticamente por trigger genérica (core.set_updated_at).
-- - Entidades centrais têm deleted_at (soft delete) para nunca perder
--   histórico por exclusão física.
-- - Nada é sobrescrito em tabelas de histórico/eventos
--   (reservation_events, hotel_settings_history, loyalty_transactions).
-- - Status de negócio com significado dinâmico por tenant (reservation
--   e stay) viraram tabelas de domínio; status puramente operacionais e
--   estáveis (quarto, pagamento, limpeza, manutenção) permanecem como
--   VARCHAR + CHECK, mais simples e igualmente seguros.
-- =====================================================================

CREATE EXTENSION IF NOT EXISTS pgcrypto;

CREATE SCHEMA IF NOT EXISTS iam;
CREATE SCHEMA IF NOT EXISTS core;
CREATE SCHEMA IF NOT EXISTS crm;
CREATE SCHEMA IF NOT EXISTS booking;
CREATE SCHEMA IF NOT EXISTS finance;
CREATE SCHEMA IF NOT EXISTS ops;
CREATE SCHEMA IF NOT EXISTS pos;
CREATE SCHEMA IF NOT EXISTS cms;
CREATE SCHEMA IF NOT EXISTS comms;
CREATE SCHEMA IF NOT EXISTS integration;

-- Função genérica para manter updated_at (usada por trigger em todas
-- as tabelas que possuem essa coluna — aplicada no fim do arquivo).
CREATE OR REPLACE FUNCTION core.set_updated_at()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = now();
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- =====================================================================
-- SCHEMA: iam — Tenants, usuários, RBAC dinâmico, tokens, auditoria
-- =====================================================================

CREATE TABLE iam.tenants (
    id              UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    name            VARCHAR(255) NOT NULL,
    slug            VARCHAR(100) NOT NULL UNIQUE,
    status          VARCHAR(20) NOT NULL DEFAULT 'trial'
                        CHECK (status IN ('trial','active','suspended','canceled')),
    trial_ends_at   TIMESTAMPTZ,
    created_at      TIMESTAMPTZ NOT NULL DEFAULT now(),
    updated_at      TIMESTAMPTZ NOT NULL DEFAULT now(),
    deleted_at      TIMESTAMPTZ
);
COMMENT ON TABLE iam.tenants IS 'Conta SaaS. Um tenant pode ter 1..N hotéis (ex.: rede/grupo). Plano/assinatura vivem em finance.subscriptions, não aqui.';

CREATE TABLE iam.users (
    id              UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    tenant_id       UUID NOT NULL REFERENCES iam.tenants(id) ON DELETE CASCADE,
    name            VARCHAR(255) NOT NULL,
    email           VARCHAR(255) NOT NULL,
    password_hash   VARCHAR(255) NOT NULL,
    phone           VARCHAR(30),
    avatar_file_id  UUID, -- FK adicionada após core.files existir
    locale          VARCHAR(10) NOT NULL DEFAULT 'pt-BR',
    is_active       BOOLEAN NOT NULL DEFAULT TRUE,
    last_login_at   TIMESTAMPTZ,
    created_at      TIMESTAMPTZ NOT NULL DEFAULT now(),
    updated_at      TIMESTAMPTZ NOT NULL DEFAULT now(),
    deleted_at      TIMESTAMPTZ,
    UNIQUE (tenant_id, email)
);

CREATE TABLE iam.roles (
    id              UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    tenant_id       UUID REFERENCES iam.tenants(id) ON DELETE CASCADE, -- NULL = papel padrão do sistema
    name            VARCHAR(100) NOT NULL, -- Owner, Gerente, Recepção, Financeiro, Limpeza, Manutenção...
    description     TEXT,
    is_system_role  BOOLEAN NOT NULL DEFAULT FALSE,
    created_at      TIMESTAMPTZ NOT NULL DEFAULT now(),
    updated_at      TIMESTAMPTZ NOT NULL DEFAULT now()
);

CREATE TABLE iam.permissions (
    id          UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    code        VARCHAR(100) NOT NULL UNIQUE, -- ex.: reservations.create, payments.refund
    module      VARCHAR(50) NOT NULL,
    description TEXT,
    created_at  TIMESTAMPTZ NOT NULL DEFAULT now()
);

CREATE TABLE iam.role_permissions (
    role_id       UUID NOT NULL REFERENCES iam.roles(id) ON DELETE CASCADE,
    permission_id UUID NOT NULL REFERENCES iam.permissions(id) ON DELETE CASCADE,
    PRIMARY KEY (role_id, permission_id)
);

-- user_roles: hotel_id nulo = papel vale para todos os hotéis do tenant.
-- (PRIMARY KEY não aceita coluna nula, por isso usamos id substituto +
-- unique constraint com NULLS NOT DISTINCT, recurso do PostgreSQL 15+.)
CREATE TABLE iam.user_roles (
    id          UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    user_id     UUID NOT NULL REFERENCES iam.users(id) ON DELETE CASCADE,
    role_id     UUID NOT NULL REFERENCES iam.roles(id) ON DELETE CASCADE,
    hotel_id    UUID, -- FK adicionada após core.hotels existir
    created_at  TIMESTAMPTZ NOT NULL DEFAULT now(),
    UNIQUE NULLS NOT DISTINCT (user_id, role_id, hotel_id)
);

CREATE TABLE iam.api_tokens (
    id              UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    tenant_id       UUID NOT NULL REFERENCES iam.tenants(id) ON DELETE CASCADE,
    name            VARCHAR(150) NOT NULL,
    token_hash      VARCHAR(255) NOT NULL,
    scopes          JSONB NOT NULL DEFAULT '[]',
    last_used_at    TIMESTAMPTZ,
    expires_at      TIMESTAMPTZ,
    revoked_at      TIMESTAMPTZ,
    created_at      TIMESTAMPTZ NOT NULL DEFAULT now()
);

CREATE TABLE iam.webhooks (
    id           UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    tenant_id    UUID NOT NULL REFERENCES iam.tenants(id) ON DELETE CASCADE,
    url          TEXT NOT NULL,
    event_types  JSONB NOT NULL DEFAULT '[]',
    secret       VARCHAR(120),
    is_active    BOOLEAN NOT NULL DEFAULT TRUE,
    created_at   TIMESTAMPTZ NOT NULL DEFAULT now()
);

-- Quem cancelou, alterou, entrou, mudou preço, IP, data, navegador...
CREATE TABLE iam.audit_logs (
    id           UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    tenant_id    UUID REFERENCES iam.tenants(id) ON DELETE CASCADE,
    user_id      UUID REFERENCES iam.users(id) ON DELETE SET NULL,
    action       VARCHAR(100) NOT NULL, -- login, cancel_reservation, update_price...
    entity_type  VARCHAR(80),
    entity_id    UUID,
    ip_address   INET,
    user_agent   TEXT,
    metadata     JSONB NOT NULL DEFAULT '{}',
    created_at   TIMESTAMPTZ NOT NULL DEFAULT now()
);
COMMENT ON TABLE iam.audit_logs IS 'Em produção com muitos hotéis, considerar particionamento por created_at (RANGE mensal) ou pg_partman para manter performance de escrita/retenção.';

-- =====================================================================
-- SCHEMA: core — Lookups globais, hotéis, arquivos, domínios
-- =====================================================================

CREATE TABLE core.countries (
    code          CHAR(2) PRIMARY KEY, -- ISO 3166-1 alpha-2
    name          VARCHAR(100) NOT NULL,
    phone_prefix  VARCHAR(6)
);

CREATE TABLE core.currencies (
    code            CHAR(3) PRIMARY KEY, -- ISO 4217
    name            VARCHAR(60) NOT NULL,
    symbol          VARCHAR(10),
    decimal_places  SMALLINT NOT NULL DEFAULT 2
);

CREATE TABLE core.languages (
    code  VARCHAR(10) PRIMARY KEY, -- IETF BCP 47, ex.: pt-BR, en-US
    name  VARCHAR(60) NOT NULL
);

CREATE TABLE core.hotels (
    id                UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    tenant_id         UUID NOT NULL REFERENCES iam.tenants(id) ON DELETE CASCADE,
    name              VARCHAR(255) NOT NULL,
    legal_name        VARCHAR(255),
    document_number   VARCHAR(30), -- CNPJ/CPF
    email             VARCHAR(255),
    phone             VARCHAR(30),
    address_line      VARCHAR(255),
    city              VARCHAR(120),
    state             VARCHAR(120),
    country_code      CHAR(2) REFERENCES core.countries(code),
    postal_code       VARCHAR(20),
    latitude          NUMERIC(10,7),
    longitude         NUMERIC(10,7),
    timezone          VARCHAR(60) NOT NULL DEFAULT 'America/Sao_Paulo',
    default_currency  CHAR(3) NOT NULL DEFAULT 'BRL' REFERENCES core.currencies(code),
    default_language  VARCHAR(10) NOT NULL DEFAULT 'pt-BR' REFERENCES core.languages(code),
    is_active         BOOLEAN NOT NULL DEFAULT TRUE,
    created_at        TIMESTAMPTZ NOT NULL DEFAULT now(),
    updated_at        TIMESTAMPTZ NOT NULL DEFAULT now(),
    deleted_at        TIMESTAMPTZ
);
COMMENT ON TABLE core.hotels IS 'Um tenant pode ter vários hotéis (franquias/redes) — não assumir 1:1 tenant/hotel.';

CREATE TABLE core.hotel_settings (
    hotel_id                   UUID PRIMARY KEY REFERENCES core.hotels(id) ON DELETE CASCADE,
    checkin_time               TIME NOT NULL DEFAULT '14:00',
    checkout_time              TIME NOT NULL DEFAULT '12:00',
    cancellation_policy        TEXT,
    overbooking_allowed        BOOLEAN NOT NULL DEFAULT FALSE,
    auto_confirm_reservations  BOOLEAN NOT NULL DEFAULT FALSE,
    settings_json              JSONB NOT NULL DEFAULT '{}',
    version                    INTEGER NOT NULL DEFAULT 1,
    updated_at                 TIMESTAMPTZ NOT NULL DEFAULT now()
);

-- Versionamento de configurações: cada UPDATE em settings_json arquiva
-- a versão anterior aqui antes de aplicar a nova.
CREATE TABLE core.hotel_settings_history (
    id             UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    hotel_id       UUID NOT NULL REFERENCES core.hotels(id) ON DELETE CASCADE,
    version        INTEGER NOT NULL,
    settings_json  JSONB NOT NULL,
    changed_by     UUID REFERENCES iam.users(id),
    changed_at     TIMESTAMPTZ NOT NULL DEFAULT now()
);

CREATE OR REPLACE FUNCTION core.log_hotel_settings_change()
RETURNS TRIGGER AS $$
BEGIN
    INSERT INTO core.hotel_settings_history (hotel_id, version, settings_json, changed_at)
    VALUES (OLD.hotel_id, OLD.version, OLD.settings_json, now());
    NEW.version := OLD.version + 1;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_hotel_settings_history
    BEFORE UPDATE ON core.hotel_settings
    FOR EACH ROW
    WHEN (OLD.settings_json IS DISTINCT FROM NEW.settings_json)
    EXECUTE FUNCTION core.log_hotel_settings_change();

CREATE TABLE core.hotel_users (
    hotel_id    UUID NOT NULL REFERENCES core.hotels(id) ON DELETE CASCADE,
    user_id     UUID NOT NULL REFERENCES iam.users(id) ON DELETE CASCADE,
    is_primary  BOOLEAN NOT NULL DEFAULT FALSE,
    created_at  TIMESTAMPTZ NOT NULL DEFAULT now(),
    PRIMARY KEY (hotel_id, user_id)
);

ALTER TABLE iam.user_roles
    ADD CONSTRAINT fk_user_roles_hotel FOREIGN KEY (hotel_id) REFERENCES core.hotels(id) ON DELETE CASCADE;

CREATE TABLE core.domains (
    id           UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    hotel_id     UUID NOT NULL REFERENCES core.hotels(id) ON DELETE CASCADE,
    domain_name  VARCHAR(255) NOT NULL UNIQUE,
    is_primary   BOOLEAN NOT NULL DEFAULT FALSE,
    is_verified  BOOLEAN NOT NULL DEFAULT FALSE,
    ssl_status   VARCHAR(30) NOT NULL DEFAULT 'pending'
                     CHECK (ssl_status IN ('pending','provisioning','active','failed')),
    created_at   TIMESTAMPTZ NOT NULL DEFAULT now()
);

CREATE TABLE core.files (
    id             UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    tenant_id      UUID REFERENCES iam.tenants(id) ON DELETE CASCADE,
    original_name  VARCHAR(255) NOT NULL,
    storage_path   TEXT NOT NULL,
    mime_type      VARCHAR(120),
    size_bytes     BIGINT,
    uploaded_by    UUID REFERENCES iam.users(id),
    created_at     TIMESTAMPTZ NOT NULL DEFAULT now()
);

ALTER TABLE iam.users
    ADD CONSTRAINT fk_users_avatar FOREIGN KEY (avatar_file_id) REFERENCES core.files(id) ON DELETE SET NULL;

CREATE TABLE core.amenities (
    id    UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    name  VARCHAR(100) NOT NULL UNIQUE, -- Wi-Fi, Ar-condicionado, Frigobar...
    icon  VARCHAR(60)
);

-- =====================================================================
-- SCHEMA: crm — Hóspedes, documentos, LGPD, preferências, fidelidade
-- =====================================================================

CREATE TABLE crm.guests (
    id                  UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    tenant_id           UUID NOT NULL REFERENCES iam.tenants(id) ON DELETE CASCADE,
    full_name           VARCHAR(255) NOT NULL,
    email               VARCHAR(255),
    phone               VARCHAR(30),
    document_type       VARCHAR(20), -- CPF, RG, Passaporte...
    document_number     VARCHAR(40),
    birth_date          DATE,
    nationality         CHAR(2) REFERENCES core.countries(code),
    preferred_language  VARCHAR(10) REFERENCES core.languages(code),
    notes               TEXT,
    created_at          TIMESTAMPTZ NOT NULL DEFAULT now(),
    updated_at          TIMESTAMPTZ NOT NULL DEFAULT now(),
    deleted_at          TIMESTAMPTZ
);

CREATE TABLE crm.guest_documents (
    id             UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    guest_id       UUID NOT NULL REFERENCES crm.guests(id) ON DELETE CASCADE,
    file_id        UUID REFERENCES core.files(id) ON DELETE SET NULL,
    document_type  VARCHAR(40) NOT NULL, -- RG, CPF, Passaporte, Ficha assinada...
    uploaded_at    TIMESTAMPTZ NOT NULL DEFAULT now()
);

CREATE TABLE crm.guest_addresses (
    id            UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    guest_id      UUID NOT NULL REFERENCES crm.guests(id) ON DELETE CASCADE,
    address_line  VARCHAR(255),
    city          VARCHAR(120),
    state         VARCHAR(120),
    country_code  CHAR(2) REFERENCES core.countries(code),
    postal_code   VARCHAR(20),
    is_primary    BOOLEAN NOT NULL DEFAULT TRUE
);

-- LGPD: registro de consentimentos por finalidade e versão de termo.
CREATE TABLE crm.guest_consents (
    id            UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    guest_id      UUID NOT NULL REFERENCES crm.guests(id) ON DELETE CASCADE,
    consent_type  VARCHAR(80) NOT NULL, -- marketing, dados_pessoais, terceiros...
    accepted      BOOLEAN NOT NULL,
    accepted_at   TIMESTAMPTZ NOT NULL DEFAULT now(),
    ip_address    INET,
    version       VARCHAR(20)
);

CREATE TABLE crm.guest_preferences (
    id                 UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    guest_id           UUID NOT NULL REFERENCES crm.guests(id) ON DELETE CASCADE,
    preference_type    VARCHAR(60) NOT NULL, -- travesseiro, andar, dieta, vista...
    preference_value   VARCHAR(255) NOT NULL,
    created_at         TIMESTAMPTZ NOT NULL DEFAULT now()
);

CREATE TABLE crm.loyalty_programs (
    id                        UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    hotel_id                  UUID NOT NULL REFERENCES core.hotels(id) ON DELETE CASCADE,
    name                      VARCHAR(150) NOT NULL,
    points_per_currency_unit  NUMERIC(8,2) NOT NULL DEFAULT 1,
    is_active                 BOOLEAN NOT NULL DEFAULT TRUE,
    created_at                TIMESTAMPTZ NOT NULL DEFAULT now()
);

CREATE TABLE crm.loyalty_accounts (
    id                  UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    loyalty_program_id  UUID NOT NULL REFERENCES crm.loyalty_programs(id) ON DELETE CASCADE,
    guest_id            UUID NOT NULL REFERENCES crm.guests(id) ON DELETE CASCADE,
    points_balance      INTEGER NOT NULL DEFAULT 0,
    tier                VARCHAR(40) NOT NULL DEFAULT 'standard',
    created_at          TIMESTAMPTZ NOT NULL DEFAULT now(),
    UNIQUE (loyalty_program_id, guest_id)
);

-- Histórico imutável de pontos (nunca sobrescrever saldo diretamente).
CREATE TABLE crm.loyalty_transactions (
    id                  UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    loyalty_account_id  UUID NOT NULL REFERENCES crm.loyalty_accounts(id) ON DELETE CASCADE,
    reservation_id      UUID, -- FK adicionada após booking.reservations existir
    points              INTEGER NOT NULL, -- positivo = crédito, negativo = resgate
    reason              VARCHAR(150),
    created_at          TIMESTAMPTZ NOT NULL DEFAULT now()
);

-- =====================================================================
-- SCHEMA: booking — Quartos, tarifário, disponibilidade
-- =====================================================================

CREATE TABLE booking.room_types (
    id             UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    hotel_id       UUID NOT NULL REFERENCES core.hotels(id) ON DELETE CASCADE,
    name           VARCHAR(150) NOT NULL, -- Standard, Luxo, Suíte...
    description    TEXT,
    base_capacity  SMALLINT NOT NULL DEFAULT 2,
    max_capacity   SMALLINT NOT NULL DEFAULT 2,
    base_price     NUMERIC(12,2) NOT NULL DEFAULT 0, -- "a partir de" para listagem/SEO; não é mais a fonte de preço (ver booking.rate_plans/room_rates)
    is_active      BOOLEAN NOT NULL DEFAULT TRUE,
    created_at     TIMESTAMPTZ NOT NULL DEFAULT now(),
    updated_at     TIMESTAMPTZ NOT NULL DEFAULT now(),
    deleted_at     TIMESTAMPTZ
);

CREATE TABLE booking.rooms (
    id            UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    hotel_id      UUID NOT NULL REFERENCES core.hotels(id) ON DELETE CASCADE,
    room_type_id  UUID NOT NULL REFERENCES booking.room_types(id) ON DELETE RESTRICT,
    number        VARCHAR(20) NOT NULL,
    floor         VARCHAR(20),
    status        VARCHAR(20) NOT NULL DEFAULT 'available'
                      CHECK (status IN ('available','occupied','maintenance','blocked','cleaning')),
    is_active     BOOLEAN NOT NULL DEFAULT TRUE,
    created_at    TIMESTAMPTZ NOT NULL DEFAULT now(),
    updated_at    TIMESTAMPTZ NOT NULL DEFAULT now(),
    deleted_at    TIMESTAMPTZ,
    UNIQUE (hotel_id, number)
);

CREATE TABLE booking.room_images (
    id             UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    room_type_id   UUID NOT NULL REFERENCES booking.room_types(id) ON DELETE CASCADE,
    file_id        UUID NOT NULL REFERENCES core.files(id) ON DELETE CASCADE,
    position       SMALLINT NOT NULL DEFAULT 0,
    created_at     TIMESTAMPTZ NOT NULL DEFAULT now()
);

CREATE TABLE booking.room_amenities (
    room_type_id  UUID NOT NULL REFERENCES booking.room_types(id) ON DELETE CASCADE,
    amenity_id    UUID NOT NULL REFERENCES core.amenities(id) ON DELETE CASCADE,
    PRIMARY KEY (room_type_id, amenity_id)
);

-- Rate plans: camada entre room_types e room_rates. Um room_type passa a
-- ter N planos comerciais (flexível, não-reembolsável, corporativo, café
-- da manhã incluso...), cada um com seu próprio tarifário em room_rates.
-- Isso é o que permite vender o mesmo quarto físico por preços/condições
-- diferentes (e é também a unidade que o channel manager mapeia 1:1 com
-- o "rate plan" da OTA — ver integration.mapped_rates).
CREATE TABLE booking.rate_plans (
    id                  UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    room_type_id        UUID NOT NULL REFERENCES booking.room_types(id) ON DELETE CASCADE,
    name                VARCHAR(150) NOT NULL, -- Flexível, Não-reembolsável, Corporativo, Café da manhã incluso...
    code                VARCHAR(40), -- código curto interno, útil para mapear com a OTA
    description         TEXT,
    is_refundable       BOOLEAN NOT NULL DEFAULT TRUE,
    cancellation_policy TEXT,
    meal_plan           VARCHAR(30) NOT NULL DEFAULT 'room_only'
                            CHECK (meal_plan IN ('room_only','breakfast','half_board','full_board','all_inclusive')),
    min_advance_days    INTEGER NOT NULL DEFAULT 0, -- antecedência mínima de reserva
    is_default          BOOLEAN NOT NULL DEFAULT FALSE, -- plano padrão exibido para o room_type
    is_active           BOOLEAN NOT NULL DEFAULT TRUE,
    created_at          TIMESTAMPTZ NOT NULL DEFAULT now(),
    updated_at          TIMESTAMPTZ NOT NULL DEFAULT now(),
    deleted_at          TIMESTAMPTZ,
    UNIQUE (room_type_id, code)
);
COMMENT ON TABLE booking.rate_plans IS 'Cada room_type tem N rate_plans. O preço em si (por data/temporada) vive em booking.room_rates, que agora referencia o rate_plan em vez do room_type diretamente.';

-- Garante no máximo um plano padrão ativo por room_type (índice parcial,
-- não um CHECK, porque a regra depende de outras linhas da tabela).
CREATE UNIQUE INDEX uq_rate_plans_one_default_per_room_type
    ON booking.rate_plans (room_type_id)
    WHERE is_default AND deleted_at IS NULL;

-- Tarifário: substitui o "preço fixo" por faixas com temporada, fim de
-- semana, feriado e restrições de venda (stop-sell, min. de noites...).
-- Agora vinculado ao rate_plan (não mais direto ao room_type): o mesmo
-- room_type pode ter tarifas diferentes por plano no mesmo período.
CREATE TABLE booking.room_rates (
    id                UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    rate_plan_id      UUID NOT NULL REFERENCES booking.rate_plans(id) ON DELETE CASCADE,
    name              VARCHAR(120),
    start_date        DATE NOT NULL,
    end_date          DATE NOT NULL,
    weekday_price     NUMERIC(12,2),
    weekend_price     NUMERIC(12,2),
    holiday_price     NUMERIC(12,2),
    minimum_nights    INTEGER NOT NULL DEFAULT 1,
    stop_sell         BOOLEAN NOT NULL DEFAULT FALSE,
    closed_arrival    BOOLEAN NOT NULL DEFAULT FALSE,
    closed_departure  BOOLEAN NOT NULL DEFAULT FALSE,
    created_at        TIMESTAMPTZ NOT NULL DEFAULT now(),
    updated_at        TIMESTAMPTZ NOT NULL DEFAULT now(),
    CHECK (end_date >= start_date)
);

-- Motor de preços: regras/exceções (feriado +20%, 7+ noites -10%,
-- último quarto +15%, baixa temporada -25%...), avaliadas por prioridade.
CREATE TABLE booking.pricing_rules (
    id          UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    hotel_id    UUID NOT NULL REFERENCES core.hotels(id) ON DELETE CASCADE,
    name        VARCHAR(150) NOT NULL,
    priority    INTEGER NOT NULL DEFAULT 1,
    conditions  JSONB NOT NULL,
    actions     JSONB NOT NULL,
    is_active   BOOLEAN NOT NULL DEFAULT TRUE,
    created_at  TIMESTAMPTZ NOT NULL DEFAULT now()
);

-- Bloqueios de quarto que não são reserva nem manutenção (reforma,
-- uso do proprietário, VIP hold etc.) sem precisar "mentir" o status.
CREATE TABLE booking.room_blocks (
    id          UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    room_id     UUID NOT NULL REFERENCES booking.rooms(id) ON DELETE CASCADE,
    start_date  DATE NOT NULL,
    end_date    DATE NOT NULL,
    reason      VARCHAR(150),
    notes       TEXT,
    created_by  UUID REFERENCES iam.users(id),
    created_at  TIMESTAMPTZ NOT NULL DEFAULT now(),
    CHECK (end_date >= start_date)
);

-- Disponibilidade pré-calculada por dia (evita varrer reservas em tempo
-- real; essencial para performance de busca e integrações de canal).
CREATE TABLE booking.room_availability (
    room_id          UUID NOT NULL REFERENCES booking.rooms(id) ON DELETE CASCADE,
    date             DATE NOT NULL,
    status           VARCHAR(20) NOT NULL DEFAULT 'available'
                         CHECK (status IN ('available','occupied','blocked','maintenance')),
    reservation_id   UUID, -- FK adicionada após booking.reservations existir
    price_override   NUMERIC(12,2),
    PRIMARY KEY (room_id, date)
);

-- =====================================================================
-- SCHEMA: booking — Reservas (status de domínio, histórico, composição
-- de preço, check-in/out, sessões do motor de reservas, OTA)
-- =====================================================================

-- Status como tabela de domínio: cada tenant pode customizar rótulos
-- e ordenação sem alterar o schema. reservation_status e stay_status
-- ficam separados de propósito (uma reserva "confirmada" e um hóspede
-- "hospedado" são conceitos diferentes e não devem se misturar).
CREATE TABLE booking.reservation_statuses (
    code        VARCHAR(30) PRIMARY KEY,
    label       VARCHAR(60) NOT NULL,
    sort_order  SMALLINT NOT NULL DEFAULT 0
);

CREATE TABLE booking.stay_statuses (
    code        VARCHAR(30) PRIMARY KEY,
    label       VARCHAR(60) NOT NULL,
    sort_order  SMALLINT NOT NULL DEFAULT 0
);

CREATE TABLE booking.reservations (
    id                 UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    hotel_id           UUID NOT NULL REFERENCES core.hotels(id) ON DELETE CASCADE,
    locator_code       VARCHAR(20) NOT NULL, -- código curto para o hóspede acompanhar
    main_guest_id      UUID NOT NULL REFERENCES crm.guests(id),
    reservation_status VARCHAR(30) NOT NULL DEFAULT 'pending'
                           REFERENCES booking.reservation_statuses(code),
    stay_status        VARCHAR(30) NOT NULL DEFAULT 'awaiting_checkin'
                           REFERENCES booking.stay_statuses(code),
    channel            VARCHAR(40) NOT NULL DEFAULT 'direct', -- direct, booking, airbnb, expedia...
    check_in_date      DATE NOT NULL,
    check_out_date     DATE NOT NULL,
    adults             SMALLINT NOT NULL DEFAULT 1,
    children           SMALLINT NOT NULL DEFAULT 0,
    currency           CHAR(3) NOT NULL DEFAULT 'BRL' REFERENCES core.currencies(code),
    total_amount       NUMERIC(12,2) NOT NULL DEFAULT 0,
    notes              TEXT,
    created_by         UUID REFERENCES iam.users(id),
    created_at         TIMESTAMPTZ NOT NULL DEFAULT now(),
    updated_at         TIMESTAMPTZ NOT NULL DEFAULT now(),
    deleted_at         TIMESTAMPTZ,
    UNIQUE (hotel_id, locator_code),
    CHECK (check_out_date > check_in_date)
);

ALTER TABLE booking.room_availability
    ADD CONSTRAINT fk_room_availability_reservation
    FOREIGN KEY (reservation_id) REFERENCES booking.reservations(id) ON DELETE SET NULL;

ALTER TABLE crm.loyalty_transactions
    ADD CONSTRAINT fk_loyalty_transactions_reservation
    FOREIGN KEY (reservation_id) REFERENCES booking.reservations(id) ON DELETE SET NULL;

CREATE TABLE booking.reservation_rooms (
    id              UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    reservation_id  UUID NOT NULL REFERENCES booking.reservations(id) ON DELETE CASCADE,
    room_id         UUID NOT NULL REFERENCES booking.rooms(id),
    rate_plan_id    UUID REFERENCES booking.rate_plans(id), -- qual plano (flexível/não-reembolsável/...) foi vendido
    check_in_date   DATE NOT NULL,
    check_out_date  DATE NOT NULL,
    rate_per_night  NUMERIC(12,2) NOT NULL,
    created_at      TIMESTAMPTZ NOT NULL DEFAULT now()
);

CREATE TABLE booking.reservation_guests (
    reservation_id  UUID NOT NULL REFERENCES booking.reservations(id) ON DELETE CASCADE,
    guest_id        UUID NOT NULL REFERENCES crm.guests(id) ON DELETE CASCADE,
    is_main         BOOLEAN NOT NULL DEFAULT FALSE,
    PRIMARY KEY (reservation_id, guest_id)
);

-- Histórico imutável: nunca sobrescrever, sempre registrar o evento.
-- Ex.: "Mudou do 102 para 203, por João, às 15:32".
CREATE TABLE booking.reservation_events (
    id              UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    reservation_id  UUID NOT NULL REFERENCES booking.reservations(id) ON DELETE CASCADE,
    event_type      VARCHAR(60) NOT NULL, -- room_change, status_change, price_change, cancel...
    description     TEXT NOT NULL,
    metadata        JSONB NOT NULL DEFAULT '{}',
    performed_by    UUID REFERENCES iam.users(id),
    performed_at    TIMESTAMPTZ NOT NULL DEFAULT now()
);

-- Composição do preço: diária, taxa de limpeza, desconto, cupom,
-- imposto, serviço extra — nunca só o valor final.
CREATE TABLE booking.reservation_charges (
    id              UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    reservation_id  UUID NOT NULL REFERENCES booking.reservations(id) ON DELETE CASCADE,
    charge_type     VARCHAR(40) NOT NULL, -- diaria, taxa_limpeza, desconto, cupom, imposto, servico_extra
    description     VARCHAR(255),
    quantity        NUMERIC(10,2) NOT NULL DEFAULT 1,
    unit_amount     NUMERIC(12,2) NOT NULL,
    total_amount    NUMERIC(12,2) NOT NULL,
    is_discount     BOOLEAN NOT NULL DEFAULT FALSE,
    created_at      TIMESTAMPTZ NOT NULL DEFAULT now()
);

-- Vínculo de uma reserva com o ID externo no canal (Booking, Airbnb...).
-- Uma reserva pode existir em vários canais simultaneamente.
CREATE TABLE booking.reservation_external_refs (
    id                UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    reservation_id    UUID NOT NULL REFERENCES booking.reservations(id) ON DELETE CASCADE,
    provider          VARCHAR(50) NOT NULL,
    external_id       VARCHAR(200) NOT NULL,
    external_status   VARCHAR(60),
    payload           JSONB NOT NULL DEFAULT '{}',
    synchronized_at   TIMESTAMPTZ,
    UNIQUE (provider, external_id)
);

-- Sessão do motor de reservas no site (mede abandono de reserva, UTMs).
CREATE TABLE booking.booking_sessions (
    id           UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    hotel_id     UUID NOT NULL REFERENCES core.hotels(id) ON DELETE CASCADE,
    token        UUID NOT NULL DEFAULT gen_random_uuid(),
    check_in     DATE,
    check_out    DATE,
    adults       INTEGER,
    children     INTEGER,
    utm_source   VARCHAR(80),
    utm_medium   VARCHAR(80),
    utm_campaign VARCHAR(80),
    ip_address   INET,
    expires_at   TIMESTAMPTZ,
    created_at   TIMESTAMPTZ NOT NULL DEFAULT now()
);

CREATE TABLE booking.checkins (
    id                  UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    reservation_id      UUID UNIQUE NOT NULL REFERENCES booking.reservations(id) ON DELETE CASCADE,
    checked_in_by       UUID REFERENCES iam.users(id),
    checked_in_at       TIMESTAMPTZ,
    signature_file_id   UUID REFERENCES core.files(id),
    document_verified   BOOLEAN NOT NULL DEFAULT FALSE,
    notes               TEXT
);

CREATE TABLE booking.checkouts (
    id               UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    reservation_id   UUID UNIQUE NOT NULL REFERENCES booking.reservations(id) ON DELETE CASCADE,
    checked_out_by   UUID REFERENCES iam.users(id),
    checked_out_at   TIMESTAMPTZ,
    damage_report    TEXT,
    extra_amount     NUMERIC(12,2) NOT NULL DEFAULT 0,
    notes            TEXT
);

-- =====================================================================
-- SCHEMA: finance — Planos/assinaturas do SaaS, pagamentos, faturas
-- =====================================================================

CREATE TABLE finance.plans (
    id             UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    name           VARCHAR(100) NOT NULL,
    slug           VARCHAR(100) UNIQUE NOT NULL,
    price_monthly  NUMERIC(12,2) NOT NULL,
    price_yearly   NUMERIC(12,2),
    max_hotels     INTEGER,
    max_users      INTEGER,
    max_rooms      INTEGER,
    features       JSONB NOT NULL DEFAULT '{}',
    is_active      BOOLEAN NOT NULL DEFAULT TRUE,
    created_at     TIMESTAMPTZ NOT NULL DEFAULT now()
);

CREATE TABLE finance.subscriptions (
    id                       UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    tenant_id                UUID NOT NULL REFERENCES iam.tenants(id) ON DELETE CASCADE,
    plan_id                  UUID NOT NULL REFERENCES finance.plans(id),
    status                   VARCHAR(20) NOT NULL DEFAULT 'trial'
                                 CHECK (status IN ('trial','active','past_due','canceled','expired')),
    gateway                  VARCHAR(50),
    gateway_subscription_id  VARCHAR(150),
    started_at               TIMESTAMPTZ,
    trial_ends_at             TIMESTAMPTZ,
    renew_at                  TIMESTAMPTZ,
    canceled_at                TIMESTAMPTZ,
    created_at                 TIMESTAMPTZ NOT NULL DEFAULT now(),
    updated_at                  TIMESTAMPTZ NOT NULL DEFAULT now()
);

-- Regra de negócio garantida no banco: só uma assinatura "em vigor"
-- (trial/active/past_due) por tenant.
CREATE UNIQUE INDEX uq_subscriptions_one_active_per_tenant
    ON finance.subscriptions (tenant_id)
    WHERE status IN ('trial','active','past_due');

CREATE TABLE finance.payment_methods (
    id         UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    hotel_id   UUID NOT NULL REFERENCES core.hotels(id) ON DELETE CASCADE,
    type       VARCHAR(20) NOT NULL
                   CHECK (type IN ('credit_card','debit_card','pix','boleto','cash','bank_transfer')),
    provider   VARCHAR(60), -- stripe, mercadopago, asaas...
    is_active  BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMPTZ NOT NULL DEFAULT now()
);

CREATE TABLE finance.payments (
    id                       UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    reservation_id           UUID NOT NULL REFERENCES booking.reservations(id) ON DELETE CASCADE,
    payment_method_id        UUID REFERENCES finance.payment_methods(id),
    amount                   NUMERIC(12,2) NOT NULL,
    currency                 CHAR(3) NOT NULL DEFAULT 'BRL' REFERENCES core.currencies(code),
    status                   VARCHAR(20) NOT NULL DEFAULT 'pending'
                                 CHECK (status IN ('pending','processing','paid','failed','refunded','partially_paid')),
    external_transaction_id  VARCHAR(120),
    paid_at                  TIMESTAMPTZ,
    created_at               TIMESTAMPTZ NOT NULL DEFAULT now(),
    updated_at               TIMESTAMPTZ NOT NULL DEFAULT now()
);

CREATE TABLE finance.refunds (
    id            UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    payment_id    UUID NOT NULL REFERENCES finance.payments(id) ON DELETE CASCADE,
    amount        NUMERIC(12,2) NOT NULL,
    reason        TEXT,
    status        VARCHAR(30) NOT NULL DEFAULT 'pending',
    requested_by  UUID REFERENCES iam.users(id),
    processed_at  TIMESTAMPTZ,
    created_at    TIMESTAMPTZ NOT NULL DEFAULT now()
);

CREATE TABLE finance.invoices (
    id                   UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    hotel_id             UUID NOT NULL REFERENCES core.hotels(id) ON DELETE CASCADE,
    reservation_id       UUID REFERENCES booking.reservations(id),
    invoice_number       VARCHAR(40) NOT NULL,
    status               VARCHAR(20) NOT NULL DEFAULT 'draft'
                             CHECK (status IN ('draft','issued','canceled','paid')),
    total_amount         NUMERIC(12,2) NOT NULL DEFAULT 0,
    tax_amount           NUMERIC(12,2) NOT NULL DEFAULT 0,
    issued_at            TIMESTAMPTZ,
    fiscal_document_id   VARCHAR(120), -- NFS-e / NF-e, conforme legislação local
    created_at           TIMESTAMPTZ NOT NULL DEFAULT now(),
    UNIQUE (hotel_id, invoice_number)
);

CREATE TABLE finance.invoice_items (
    id            UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    invoice_id    UUID NOT NULL REFERENCES finance.invoices(id) ON DELETE CASCADE,
    description   VARCHAR(255) NOT NULL,
    quantity      NUMERIC(10,2) NOT NULL DEFAULT 1,
    unit_amount   NUMERIC(12,2) NOT NULL,
    total_amount  NUMERIC(12,2) NOT NULL
);

CREATE TABLE finance.coupons (
    id              UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    hotel_id        UUID NOT NULL REFERENCES core.hotels(id) ON DELETE CASCADE,
    code            VARCHAR(40) NOT NULL,
    discount_type   VARCHAR(20) NOT NULL DEFAULT 'percentage'
                        CHECK (discount_type IN ('percentage','fixed_amount')),
    discount_value  NUMERIC(12,2) NOT NULL,
    valid_from      DATE,
    valid_until     DATE,
    max_uses        INTEGER,
    used_count      INTEGER NOT NULL DEFAULT 0,
    is_active       BOOLEAN NOT NULL DEFAULT TRUE,
    UNIQUE (hotel_id, code)
);

CREATE TABLE finance.promotions (
    id                UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    hotel_id          UUID NOT NULL REFERENCES core.hotels(id) ON DELETE CASCADE,
    name              VARCHAR(150) NOT NULL,
    description       TEXT,
    discount_percent  NUMERIC(5,2),
    valid_from        DATE,
    valid_until       DATE,
    is_active         BOOLEAN NOT NULL DEFAULT TRUE
);

-- =====================================================================
-- SCHEMA: ops — Serviços extras, limpeza, manutenção, produtos/estoque,
-- tarefas gerais da equipe
-- =====================================================================

CREATE TABLE ops.services (
    id         UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    hotel_id   UUID NOT NULL REFERENCES core.hotels(id) ON DELETE CASCADE,
    name       VARCHAR(150) NOT NULL, -- lavanderia, frigobar, spa, transfer...
    price      NUMERIC(12,2) NOT NULL DEFAULT 0,
    is_active  BOOLEAN NOT NULL DEFAULT TRUE
);

CREATE TABLE ops.service_orders (
    id              UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    reservation_id  UUID NOT NULL REFERENCES booking.reservations(id) ON DELETE CASCADE,
    service_id      UUID NOT NULL REFERENCES ops.services(id),
    quantity        NUMERIC(10,2) NOT NULL DEFAULT 1,
    unit_price      NUMERIC(12,2) NOT NULL,
    total_price     NUMERIC(12,2) NOT NULL,
    requested_at    TIMESTAMPTZ NOT NULL DEFAULT now(),
    delivered_at    TIMESTAMPTZ
);

CREATE TABLE ops.housekeeping_tasks (
    id              UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    room_id         UUID NOT NULL REFERENCES booking.rooms(id) ON DELETE CASCADE,
    assigned_to     UUID REFERENCES iam.users(id),
    status          VARCHAR(20) NOT NULL DEFAULT 'pending'
                        CHECK (status IN ('pending','in_progress','done','verified')),
    scheduled_date  DATE NOT NULL,
    started_at      TIMESTAMPTZ,
    completed_at    TIMESTAMPTZ,
    notes           TEXT,
    created_at      TIMESTAMPTZ NOT NULL DEFAULT now()
);

CREATE TABLE ops.maintenance_tickets (
    id           UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    room_id      UUID REFERENCES booking.rooms(id) ON DELETE CASCADE,
    hotel_id     UUID NOT NULL REFERENCES core.hotels(id) ON DELETE CASCADE,
    title        VARCHAR(255) NOT NULL,
    description  TEXT,
    priority     VARCHAR(10) NOT NULL DEFAULT 'medium'
                     CHECK (priority IN ('low','medium','high','urgent')),
    status       VARCHAR(20) NOT NULL DEFAULT 'open'
                     CHECK (status IN ('open','in_progress','done','canceled')),
    assigned_to  UUID REFERENCES iam.users(id),
    opened_by    UUID REFERENCES iam.users(id),
    opened_at    TIMESTAMPTZ NOT NULL DEFAULT now(),
    resolved_at  TIMESTAMPTZ
);

-- Produtos vendáveis/consumíveis (frigobar, restaurante, bar, amenities).
CREATE TABLE ops.products (
    id               UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    hotel_id         UUID NOT NULL REFERENCES core.hotels(id) ON DELETE CASCADE,
    name             VARCHAR(150) NOT NULL,
    barcode          VARCHAR(80),
    sku              VARCHAR(80),
    sale_price       NUMERIC(12,2) NOT NULL DEFAULT 0,
    cost_price       NUMERIC(12,2) NOT NULL DEFAULT 0,
    stock_quantity   NUMERIC(12,2) NOT NULL DEFAULT 0,
    minimum_stock    NUMERIC(12,2) NOT NULL DEFAULT 0,
    is_active        BOOLEAN NOT NULL DEFAULT TRUE,
    created_at       TIMESTAMPTZ NOT NULL DEFAULT now(),
    updated_at       TIMESTAMPTZ NOT NULL DEFAULT now()
);

-- Nota: a antiga ops.consumptions (consumo de produto por reserva) foi
-- removida — essa responsabilidade passou para pos.orders/order_items,
-- que cobre o mesmo caso (produto lançado numa reserva) sem acoplar o
-- POS a uma reserva obrigatória (ver schema pos, abaixo de ops).

-- Movimentações de estoque (entrada, saída, ajuste, venda por
-- reservation_id ou compra por purchase_order_id, via reference_*).
CREATE TABLE ops.stock_movements (
    id              UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    product_id      UUID NOT NULL REFERENCES ops.products(id),
    movement_type   VARCHAR(20) NOT NULL
                        CHECK (movement_type IN ('in','out','adjustment','loss')),
    quantity        NUMERIC(12,2) NOT NULL,
    reference_type  VARCHAR(40), -- reservation, purchase, manual_adjustment...
    reference_id    UUID,
    created_by      UUID REFERENCES iam.users(id),
    created_at      TIMESTAMPTZ NOT NULL DEFAULT now()
);

-- Tarefas genéricas da equipe, fora do escopo de limpeza/manutenção
-- (ex.: "ligar para fornecedor", "revisar contrato").
CREATE TABLE ops.tasks (
    id            UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    hotel_id      UUID NOT NULL REFERENCES core.hotels(id) ON DELETE CASCADE,
    title         VARCHAR(255) NOT NULL,
    description   TEXT,
    assigned_to   UUID REFERENCES iam.users(id),
    status        VARCHAR(20) NOT NULL DEFAULT 'open'
                      CHECK (status IN ('open','in_progress','done','canceled')),
    due_at        TIMESTAMPTZ,
    completed_at  TIMESTAMPTZ,
    created_by    UUID REFERENCES iam.users(id),
    created_at    TIMESTAMPTZ NOT NULL DEFAULT now()
);

-- =====================================================================
-- SCHEMA: pos — Ponto de venda (comandas/pedidos), desacoplado de
-- reserva. Cobre frigobar, restaurante, bar, spa avulso etc. Um pedido
-- pode ser aberto para um hóspede, para uma reserva, ou para um cliente
-- avulso (não-hóspede, ex.: visitante do restaurante) — nunca exige
-- reserva como pré-requisito para vender algo.
-- =====================================================================

CREATE TABLE pos.orders (
    id              UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    hotel_id        UUID NOT NULL REFERENCES core.hotels(id) ON DELETE CASCADE,
    order_number    VARCHAR(30) NOT NULL, -- número/comanda visível para operação
    guest_id        UUID REFERENCES crm.guests(id) ON DELETE SET NULL,
    reservation_id  UUID REFERENCES booking.reservations(id) ON DELETE SET NULL,
    customer_name   VARCHAR(255), -- cliente avulso, sem cadastro de hóspede/reserva
    status          VARCHAR(20) NOT NULL DEFAULT 'open'
                        CHECK (status IN ('open','closed','canceled')),
    total_amount    NUMERIC(12,2) NOT NULL DEFAULT 0,
    notes           TEXT,
    opened_by       UUID REFERENCES iam.users(id),
    opened_at       TIMESTAMPTZ NOT NULL DEFAULT now(),
    closed_at       TIMESTAMPTZ,
    created_at      TIMESTAMPTZ NOT NULL DEFAULT now(),
    updated_at      TIMESTAMPTZ NOT NULL DEFAULT now(),
    UNIQUE (hotel_id, order_number),
    -- Precisa de pelo menos uma forma de identificar para quem é o pedido:
    -- hóspede cadastrado, reserva, ou nome avulso. Os três são independentes
    -- entre si (uma comanda de reserva pode não ter guest_id preenchido).
    CHECK (guest_id IS NOT NULL OR reservation_id IS NOT NULL OR customer_name IS NOT NULL)
);
COMMENT ON TABLE pos.orders IS 'Pedido/comanda do POS. Decisão de arquitetura: NUNCA exigir reservation_id — o restaurante/bar do hotel também atende quem não está hospedado.';

CREATE TABLE pos.order_items (
    id           UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    order_id     UUID NOT NULL REFERENCES pos.orders(id) ON DELETE CASCADE,
    product_id   UUID REFERENCES ops.products(id), -- nulo = item avulso/fora do catálogo
    description  VARCHAR(255), -- nome do item; obrigatório quando product_id é nulo
    quantity     NUMERIC(10,2) NOT NULL DEFAULT 1,
    unit_price   NUMERIC(12,2) NOT NULL,
    total_price  NUMERIC(12,2) NOT NULL,
    added_by     UUID REFERENCES iam.users(id),
    added_at     TIMESTAMPTZ NOT NULL DEFAULT now(),
    CHECK (product_id IS NOT NULL OR description IS NOT NULL)
);

CREATE TABLE pos.payments (
    id                       UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    order_id                 UUID NOT NULL REFERENCES pos.orders(id) ON DELETE CASCADE,
    payment_method_id        UUID REFERENCES finance.payment_methods(id),
    amount                   NUMERIC(12,2) NOT NULL,
    currency                 CHAR(3) NOT NULL DEFAULT 'BRL' REFERENCES core.currencies(code),
    status                   VARCHAR(20) NOT NULL DEFAULT 'pending'
                                 CHECK (status IN ('pending','processing','paid','failed','refunded','partially_paid')),
    external_transaction_id  VARCHAR(120),
    paid_at                  TIMESTAMPTZ,
    created_at               TIMESTAMPTZ NOT NULL DEFAULT now(),
    updated_at               TIMESTAMPTZ NOT NULL DEFAULT now()
);
COMMENT ON TABLE pos.payments IS 'Pagamento do POS, independente de finance.payments (que é sempre atrelado a reservation_id). Um pedido de cliente avulso é pago aqui sem nunca tocar em booking.reservations.';

-- =====================================================================
-- SCHEMA: cms — Conteúdo do site público (separado da operação)
-- =====================================================================

CREATE TABLE cms.hotel_pages (
    id                  UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    hotel_id            UUID NOT NULL REFERENCES core.hotels(id) ON DELETE CASCADE,
    hero_title          VARCHAR(255),
    hero_subtitle       VARCHAR(255),
    hero_image_file_id  UUID REFERENCES core.files(id) ON DELETE SET NULL,
    gallery             JSONB NOT NULL DEFAULT '[]',
    policies            TEXT,
    contact_info        JSONB NOT NULL DEFAULT '{}',
    seo_title           VARCHAR(255),
    seo_description     VARCHAR(500),
    updated_at          TIMESTAMPTZ NOT NULL DEFAULT now()
);

-- =====================================================================
-- SCHEMA: comms — E-mails, notificações, mensageria (WhatsApp etc.)
-- =====================================================================

CREATE TABLE comms.emails (
    id              UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    hotel_id        UUID REFERENCES core.hotels(id) ON DELETE CASCADE,
    reservation_id  UUID REFERENCES booking.reservations(id),
    to_address      VARCHAR(255) NOT NULL,
    subject         VARCHAR(255) NOT NULL,
    template        VARCHAR(80),
    status          VARCHAR(20) NOT NULL DEFAULT 'pending'
                        CHECK (status IN ('pending','sent','failed','bounced')),
    sent_at         TIMESTAMPTZ,
    created_at      TIMESTAMPTZ NOT NULL DEFAULT now()
);

CREATE TABLE comms.notifications (
    id          UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    user_id     UUID REFERENCES iam.users(id) ON DELETE CASCADE,
    guest_id    UUID REFERENCES crm.guests(id) ON DELETE CASCADE,
    channel     VARCHAR(20) NOT NULL CHECK (channel IN ('email','sms','push','whatsapp')),
    title       VARCHAR(255),
    message     TEXT NOT NULL,
    is_read     BOOLEAN NOT NULL DEFAULT FALSE,
    sent_at     TIMESTAMPTZ,
    created_at  TIMESTAMPTZ NOT NULL DEFAULT now()
);

-- Conversas com hóspedes via WhatsApp/webchat, vinculadas a uma reserva
-- quando possível — base para automações e histórico de atendimento.
CREATE TABLE comms.message_threads (
    id                        UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    hotel_id                  UUID NOT NULL REFERENCES core.hotels(id) ON DELETE CASCADE,
    guest_id                  UUID REFERENCES crm.guests(id) ON DELETE SET NULL,
    reservation_id            UUID REFERENCES booking.reservations(id) ON DELETE SET NULL,
    channel                   VARCHAR(20) NOT NULL DEFAULT 'whatsapp'
                                  CHECK (channel IN ('whatsapp','webchat','email','sms')),
    external_conversation_id  VARCHAR(150),
    status                    VARCHAR(20) NOT NULL DEFAULT 'open'
                                  CHECK (status IN ('open','closed','archived')),
    created_at                TIMESTAMPTZ NOT NULL DEFAULT now(),
    updated_at                TIMESTAMPTZ NOT NULL DEFAULT now()
);

CREATE TABLE comms.messages (
    id                    UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    thread_id             UUID NOT NULL REFERENCES comms.message_threads(id) ON DELETE CASCADE,
    sender_type           VARCHAR(20) NOT NULL CHECK (sender_type IN ('guest','staff','system')),
    sender_user_id        UUID REFERENCES iam.users(id),
    body                  TEXT NOT NULL,
    external_message_id   VARCHAR(150),
    delivered_at          TIMESTAMPTZ,
    read_at               TIMESTAMPTZ,
    created_at            TIMESTAMPTZ NOT NULL DEFAULT now()
);

-- =====================================================================
-- SCHEMA: integration — Integrações externas (OTAs, fechaduras, etc.)
-- =====================================================================

CREATE TABLE integration.integrations (
    id            UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    hotel_id      UUID NOT NULL REFERENCES core.hotels(id) ON DELETE CASCADE,
    provider      VARCHAR(60) NOT NULL, -- booking.com, airbnb, expedia, fechadura inteligente...
    config        JSONB NOT NULL DEFAULT '{}',
    is_active     BOOLEAN NOT NULL DEFAULT TRUE,
    connected_at  TIMESTAMPTZ,
    created_at    TIMESTAMPTZ NOT NULL DEFAULT now()
);

-- ---------------------------------------------------------------------
-- Channel manager: sync real (push de tarifa/disponibilidade para a OTA
-- e pull de reservas). As quatro peças abaixo cobrem o ciclo completo:
-- mapeamento (o que é o quê no lado de fora), fila de trabalho (o que
-- falta enviar), log (o que foi enviado e o resultado) e import (o que
-- chegou de novo da OTA e ainda precisa virar reserva no PMS).
-- ---------------------------------------------------------------------

-- De-para entre room_type interno e o "room"/quarto cadastrado na OTA.
CREATE TABLE integration.mapped_rooms (
    id                UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    integration_id    UUID NOT NULL REFERENCES integration.integrations(id) ON DELETE CASCADE,
    room_type_id      UUID NOT NULL REFERENCES booking.room_types(id) ON DELETE CASCADE,
    external_room_id  VARCHAR(150) NOT NULL, -- ID do room type/produto no lado da OTA
    external_room_name VARCHAR(255),
    is_active         BOOLEAN NOT NULL DEFAULT TRUE,
    created_at        TIMESTAMPTZ NOT NULL DEFAULT now(),
    UNIQUE (integration_id, room_type_id),
    UNIQUE (integration_id, external_room_id)
);

-- De-para entre rate_plan interno e o rate plan cadastrado na OTA —
-- é essa granularidade (não o room_type) que o push de tarifa usa.
CREATE TABLE integration.mapped_rates (
    id                     UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    integration_id         UUID NOT NULL REFERENCES integration.integrations(id) ON DELETE CASCADE,
    rate_plan_id           UUID NOT NULL REFERENCES booking.rate_plans(id) ON DELETE CASCADE,
    external_rate_plan_id  VARCHAR(150) NOT NULL,
    external_rate_plan_name VARCHAR(255),
    is_active              BOOLEAN NOT NULL DEFAULT TRUE,
    created_at             TIMESTAMPTZ NOT NULL DEFAULT now(),
    UNIQUE (integration_id, rate_plan_id),
    UNIQUE (integration_id, external_rate_plan_id)
);

-- Fila de trabalho: o que ainda precisa ser empurrado para a OTA
-- (tarifa, disponibilidade ou restrição), por data. Um worker externo
-- consome esta fila e grava o resultado em sync_logs.
CREATE TABLE integration.sync_queue (
    id              UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    integration_id  UUID NOT NULL REFERENCES integration.integrations(id) ON DELETE CASCADE,
    sync_type       VARCHAR(20) NOT NULL
                        CHECK (sync_type IN ('rate','availability','restriction')),
    room_type_id    UUID REFERENCES booking.room_types(id) ON DELETE CASCADE,
    rate_plan_id    UUID REFERENCES booking.rate_plans(id) ON DELETE CASCADE,
    start_date      DATE NOT NULL,
    end_date        DATE NOT NULL,
    payload         JSONB NOT NULL DEFAULT '{}', -- snapshot do que deve ser enviado
    status          VARCHAR(20) NOT NULL DEFAULT 'pending'
                        CHECK (status IN ('pending','processing','done','failed')),
    attempts        INTEGER NOT NULL DEFAULT 0,
    scheduled_at    TIMESTAMPTZ NOT NULL DEFAULT now(),
    processed_at    TIMESTAMPTZ,
    created_at      TIMESTAMPTZ NOT NULL DEFAULT now(),
    CHECK (end_date >= start_date)
);
COMMENT ON TABLE integration.sync_queue IS 'Fila de push (tarifa/disponibilidade/restrição) para a OTA. Cada item processado gera uma linha em sync_logs, independente do resultado.';

-- Log imutável de toda tentativa de sincronização, push ou pull.
CREATE TABLE integration.sync_logs (
    id               UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    integration_id   UUID NOT NULL REFERENCES integration.integrations(id) ON DELETE CASCADE,
    sync_queue_id    UUID REFERENCES integration.sync_queue(id) ON DELETE SET NULL,
    direction        VARCHAR(10) NOT NULL CHECK (direction IN ('push','pull')),
    sync_type        VARCHAR(20) NOT NULL
                         CHECK (sync_type IN ('rate','availability','restriction','reservation')),
    status           VARCHAR(20) NOT NULL CHECK (status IN ('success','failed')),
    request_payload  JSONB,
    response_payload JSONB,
    error_message    TEXT,
    executed_at      TIMESTAMPTZ NOT NULL DEFAULT now()
);

-- Pull de reservas: toda reserva recebida da OTA entra aqui primeiro
-- (com o payload bruto), antes de virar (ou ser casada com) uma linha
-- em booking.reservations. Preserva o payload original para auditoria
-- e permite reprocessar em caso de falha de parsing/conflito.
CREATE TABLE integration.reservation_imports (
    id                     UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    integration_id         UUID NOT NULL REFERENCES integration.integrations(id) ON DELETE CASCADE,
    external_reservation_id VARCHAR(150) NOT NULL,
    raw_payload            JSONB NOT NULL,
    reservation_id         UUID REFERENCES booking.reservations(id) ON DELETE SET NULL,
    status                 VARCHAR(20) NOT NULL DEFAULT 'received'
                               CHECK (status IN ('received','processed','failed','duplicate')),
    error_message          TEXT,
    received_at            TIMESTAMPTZ NOT NULL DEFAULT now(),
    processed_at           TIMESTAMPTZ,
    UNIQUE (integration_id, external_reservation_id)
);

-- =====================================================================
-- ÍNDICES
-- =====================================================================

CREATE INDEX idx_users_tenant ON iam.users(tenant_id);
CREATE INDEX idx_hotel_users_user ON core.hotel_users(user_id);
CREATE INDEX idx_hotels_tenant ON core.hotels(tenant_id);
CREATE INDEX idx_room_types_hotel ON booking.room_types(hotel_id);
CREATE INDEX idx_rooms_hotel ON booking.rooms(hotel_id);
CREATE INDEX idx_rooms_room_type ON booking.rooms(room_type_id);
CREATE INDEX idx_rate_plans_room_type ON booking.rate_plans(room_type_id) WHERE deleted_at IS NULL;
CREATE INDEX idx_room_rates_rate_plan_dates ON booking.room_rates(rate_plan_id, start_date, end_date);
CREATE INDEX idx_reservation_rooms_rate_plan ON booking.reservation_rooms(rate_plan_id);
CREATE INDEX idx_pricing_rules_hotel ON booking.pricing_rules(hotel_id) WHERE is_active;
CREATE INDEX idx_room_blocks_room_dates ON booking.room_blocks(room_id, start_date, end_date);
CREATE INDEX idx_guests_tenant ON crm.guests(tenant_id);
CREATE INDEX idx_guests_document ON crm.guests(document_number);
CREATE INDEX idx_reservations_hotel ON booking.reservations(hotel_id);
CREATE INDEX idx_reservations_dates ON booking.reservations(hotel_id, check_in_date, check_out_date);
CREATE INDEX idx_reservations_guest ON booking.reservations(main_guest_id);
CREATE INDEX idx_reservations_status ON booking.reservations(hotel_id, reservation_status);
CREATE INDEX idx_reservation_rooms_room ON booking.reservation_rooms(room_id);
CREATE INDEX idx_reservation_rooms_resv ON booking.reservation_rooms(reservation_id);
CREATE INDEX idx_reservation_events_resv ON booking.reservation_events(reservation_id);
CREATE INDEX idx_reservation_charges_resv ON booking.reservation_charges(reservation_id);
CREATE INDEX idx_reservation_ext_refs_resv ON booking.reservation_external_refs(reservation_id);
CREATE INDEX idx_room_availability_date ON booking.room_availability(date);
CREATE INDEX idx_booking_sessions_hotel ON booking.booking_sessions(hotel_id, expires_at);
CREATE INDEX idx_payments_reservation ON finance.payments(reservation_id);
CREATE INDEX idx_invoices_hotel ON finance.invoices(hotel_id);
CREATE INDEX idx_subscriptions_tenant ON finance.subscriptions(tenant_id);
CREATE INDEX idx_service_orders_resv ON ops.service_orders(reservation_id);
CREATE INDEX idx_stock_movements_product ON ops.stock_movements(product_id);
CREATE INDEX idx_products_hotel ON ops.products(hotel_id);
CREATE INDEX idx_housekeeping_room ON ops.housekeeping_tasks(room_id, scheduled_date);
CREATE INDEX idx_maintenance_hotel ON ops.maintenance_tickets(hotel_id, status);
CREATE INDEX idx_ops_tasks_hotel ON ops.tasks(hotel_id, status);
CREATE INDEX idx_pos_orders_hotel ON pos.orders(hotel_id, status);
CREATE INDEX idx_pos_orders_guest ON pos.orders(guest_id);
CREATE INDEX idx_pos_orders_reservation ON pos.orders(reservation_id);
CREATE INDEX idx_pos_order_items_order ON pos.order_items(order_id);
CREATE INDEX idx_pos_order_items_product ON pos.order_items(product_id);
CREATE INDEX idx_pos_payments_order ON pos.payments(order_id);
CREATE INDEX idx_notifications_user ON comms.notifications(user_id);
CREATE INDEX idx_message_threads_hotel ON comms.message_threads(hotel_id, status);
CREATE INDEX idx_messages_thread ON comms.messages(thread_id);
CREATE INDEX idx_loyalty_accounts_guest ON crm.loyalty_accounts(guest_id);
CREATE INDEX idx_loyalty_transactions_account ON crm.loyalty_transactions(loyalty_account_id);
CREATE INDEX idx_audit_logs_tenant ON iam.audit_logs(tenant_id);
CREATE INDEX idx_audit_logs_entity ON iam.audit_logs(entity_type, entity_id);
CREATE INDEX idx_integrations_hotel ON integration.integrations(hotel_id);
CREATE INDEX idx_mapped_rooms_integration ON integration.mapped_rooms(integration_id);
CREATE INDEX idx_mapped_rates_integration ON integration.mapped_rates(integration_id);
CREATE INDEX idx_sync_queue_pending ON integration.sync_queue(integration_id, status, scheduled_at) WHERE status IN ('pending','failed');
CREATE INDEX idx_sync_logs_integration ON integration.sync_logs(integration_id, executed_at);
CREATE INDEX idx_reservation_imports_integration ON integration.reservation_imports(integration_id, status);

-- =====================================================================
-- TRIGGERS updated_at — aplicadas automaticamente a toda tabela, em
-- qualquer schema do produto, que possua a coluna updated_at.
-- =====================================================================

DO $$
DECLARE
    r RECORD;
BEGIN
    FOR r IN
        SELECT table_schema, table_name
        FROM information_schema.columns
        WHERE column_name = 'updated_at'
          AND table_schema IN ('iam','core','crm','booking','finance','ops','pos','cms','comms','integration')
    LOOP
        EXECUTE format(
            'CREATE TRIGGER trg_set_updated_at BEFORE UPDATE ON %I.%I FOR EACH ROW EXECUTE FUNCTION core.set_updated_at();',
            r.table_schema, r.table_name
        );
    END LOOP;
END $$;

-- =====================================================================
-- SEEDS — dados de referência mínimos para o sistema funcionar
-- =====================================================================

INSERT INTO core.currencies (code, name, symbol, decimal_places) VALUES
    ('BRL', 'Real brasileiro', 'R$', 2),
    ('USD', 'Dólar americano', 'US$', 2),
    ('EUR', 'Euro', '€', 2);

INSERT INTO core.languages (code, name) VALUES
    ('pt-BR', 'Português (Brasil)'),
    ('en-US', 'English (US)'),
    ('es-ES', 'Español');

INSERT INTO core.countries (code, name, phone_prefix) VALUES
    ('BR', 'Brasil', '+55'),
    ('US', 'Estados Unidos', '+1'),
    ('PT', 'Portugal', '+351'),
    ('AR', 'Argentina', '+54');

INSERT INTO booking.reservation_statuses (code, label, sort_order) VALUES
    ('pending', 'Pendente', 10),
    ('pre_reservation', 'Pré-reserva', 20),
    ('awaiting_payment', 'Aguardando pagamento', 30),
    ('confirmed', 'Confirmada', 40),
    ('canceled', 'Cancelada', 50),
    ('no_show', 'No-show', 60),
    ('refunded', 'Reembolsada', 70);

INSERT INTO booking.stay_statuses (code, label, sort_order) VALUES
    ('awaiting_checkin', 'Aguardando check-in', 10),
    ('checked_in', 'Hospedado', 20),
    ('checked_out', 'Check-out realizado', 30);

-- Papéis padrão do sistema (tenant_id NULL = disponível para todos os
-- tenants; permissões específicas são vinculadas via role_permissions).
INSERT INTO iam.roles (tenant_id, name, description, is_system_role) VALUES
    (NULL, 'Owner', 'Acesso total ao sistema', TRUE),
    (NULL, 'Gerente', 'Gestão operacional do hotel', TRUE),
    (NULL, 'Recepção', 'Check-in, check-out e atendimento ao hóspede', TRUE),
    (NULL, 'Financeiro', 'Pagamentos, faturas e relatórios financeiros', TRUE),
    (NULL, 'Limpeza', 'Gestão de limpeza dos quartos', TRUE),
    (NULL, 'Manutenção', 'Gestão de chamados de manutenção', TRUE);

-- Planos comerciais discutidos (Básico/Profissional/Premium do MVP).
INSERT INTO finance.plans (name, slug, price_monthly, price_yearly, max_hotels, max_users, max_rooms, features) VALUES
    ('Básico', 'basico', 149.00, 1490.00, 1, 5, 20,
        '{"site": true, "reservas": true, "dashboard": true}'),
    ('Profissional', 'profissional', 349.00, 3490.00, 1, 15, 60,
        '{"site": true, "reservas": true, "dashboard": true, "relatorios": true, "pagamentos_online": true, "emails_automaticos": true}'),
    ('Premium', 'premium', 599.00, 5990.00, NULL, NULL, NULL,
        '{"site": true, "reservas": true, "dashboard": true, "relatorios": true, "pagamentos_online": true, "emails_automaticos": true, "dominio_personalizado": true, "integracoes": true, "suporte_prioritario": true}');
