-- =====================================================================
-- MecDesk PMS — Migração v3 -> v4
-- =====================================================================
-- Pré-requisito: rodar depois de mecdesk_pms_schema_v3.sql (não recria
-- nada que já existe lá, só ALTERs/CREATEs incrementais).
--
-- Escopo desta migração (o que foi decidido fazer agora):
--   1. Exclusion constraint contra overbooking (booking.reservation_rooms)
--   2. CHECKs financeiros
--   3. Auditoria automática via trigger (recortada, não em toda tabela)
--   4. Row Level Security (tenant/hotel)
--   5. Materialized views para dashboard
--
-- Ficou de fora de propósito (ver justificativa na conversa):
--   - Particionamento agora (preparar, não executar, até volume justificar)
--   - Tabelas de "cache" no Postgres (isso é papel de Redis, não de tabela)
--   - Migração de UUID v4 -> v7 em PKs existentes (troca só em tabelas novas)
--   - Event bus / domain_events com broker externo (manter como outbox
--     interno só quando houver um segundo processo real para consumir)
-- =====================================================================


-- =====================================================================
-- 1) EXCLUSION CONSTRAINT — impede dois quartos ocupando o mesmo período
-- =====================================================================
-- A constraint vive em reservation_rooms (não em reservations), porque é
-- ali que mora o par quarto+período. Ela não enxerga o status de outra
-- tabela, então precisamos de uma coluna local "is_active" mantida em
-- sincronia com booking.reservations.reservation_status via trigger.
-- Sem isso, cancelar uma reserva deixaria o quarto "preso" para sempre.

ALTER TABLE booking.reservation_rooms
    ADD COLUMN is_active BOOLEAN NOT NULL DEFAULT TRUE;

CREATE EXTENSION IF NOT EXISTS btree_gist;

ALTER TABLE booking.reservation_rooms
    ADD CONSTRAINT no_overlap_room_period
    EXCLUDE USING gist (
        room_id WITH =,
        daterange(check_in_date, check_out_date, '[)') WITH &&
    )
    WHERE (is_active);

COMMENT ON CONSTRAINT no_overlap_room_period ON booking.reservation_rooms IS
    'Impede duas reservas ativas ocuparem o mesmo quarto no mesmo período, mesmo sob concorrência. Intervalo meia-aberta [check_in, check_out) para permitir check-out e check-in no mesmo dia.';

-- Sincroniza is_active com o status da reserva-mãe. Statuses que liberam
-- o quarto: canceled, no_show, refunded. Os demais mantêm is_active = true.
CREATE OR REPLACE FUNCTION booking.sync_reservation_rooms_active()
RETURNS TRIGGER AS $$
BEGIN
    IF NEW.reservation_status IN ('canceled', 'no_show', 'refunded') THEN
        UPDATE booking.reservation_rooms
           SET is_active = FALSE
         WHERE reservation_id = NEW.id
           AND is_active = TRUE;
    ELSIF NEW.reservation_status IS DISTINCT FROM OLD.reservation_status THEN
        -- reativação (ex.: reversão manual de cancelamento) volta a valer
        -- a checagem de overlap
        UPDATE booking.reservation_rooms
           SET is_active = TRUE
         WHERE reservation_id = NEW.id
           AND is_active = FALSE
           AND OLD.reservation_status IN ('canceled', 'no_show', 'refunded');
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_sync_reservation_rooms_active
    AFTER UPDATE OF reservation_status ON booking.reservations
    FOR EACH ROW
    WHEN (OLD.reservation_status IS DISTINCT FROM NEW.reservation_status)
    EXECUTE FUNCTION booking.sync_reservation_rooms_active();

-- Também precisa rodar no INSERT, caso a reserva já nasça cancelada
-- (edge case raro, mas o trigger acima só dispara em UPDATE).
CREATE OR REPLACE FUNCTION booking.set_initial_reservation_rooms_active()
RETURNS TRIGGER AS $$
BEGIN
    IF NEW.reservation_status IN ('canceled', 'no_show', 'refunded') THEN
        UPDATE booking.reservation_rooms
           SET is_active = FALSE
         WHERE reservation_id = NEW.id;
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_initial_reservation_rooms_active
    AFTER INSERT ON booking.reservations
    FOR EACH ROW
    EXECUTE FUNCTION booking.set_initial_reservation_rooms_active();


-- =====================================================================
-- 2) CHECKs FINANCEIROS
-- =====================================================================
-- Convenção adotada: todo valor monetário em linha ativa é armazenado
-- como não-negativo; sinal/direção (desconto, estorno, saída de estoque)
-- é sempre expresso por uma coluna de tipo/flag, nunca pelo sinal do
-- número. reservation_charges.is_discount já segue essa convenção — os
-- CHECKs abaixo só formalizam a regra que já estava implícita no design.

ALTER TABLE booking.reservation_charges
    ADD CONSTRAINT chk_reservation_charges_quantity CHECK (quantity > 0),
    ADD CONSTRAINT chk_reservation_charges_unit_amount CHECK (unit_amount >= 0),
    ADD CONSTRAINT chk_reservation_charges_total_amount CHECK (total_amount >= 0);

ALTER TABLE booking.reservations
    ADD CONSTRAINT chk_reservations_total_amount CHECK (total_amount >= 0),
    ADD CONSTRAINT chk_reservations_adults CHECK (adults > 0),
    ADD CONSTRAINT chk_reservations_children CHECK (children >= 0);

ALTER TABLE booking.reservation_rooms
    ADD CONSTRAINT chk_reservation_rooms_rate CHECK (rate_per_night >= 0);

ALTER TABLE finance.payments
    ADD CONSTRAINT chk_payments_amount CHECK (amount >= 0);

ALTER TABLE finance.refunds
    ADD CONSTRAINT chk_refunds_amount CHECK (amount >= 0);

ALTER TABLE finance.invoices
    ADD CONSTRAINT chk_invoices_total_amount CHECK (total_amount >= 0),
    ADD CONSTRAINT chk_invoices_tax_amount CHECK (tax_amount >= 0);

ALTER TABLE finance.invoice_items
    ADD CONSTRAINT chk_invoice_items_quantity CHECK (quantity > 0),
    ADD CONSTRAINT chk_invoice_items_unit_amount CHECK (unit_amount >= 0),
    ADD CONSTRAINT chk_invoice_items_total_amount CHECK (total_amount >= 0);

ALTER TABLE finance.coupons
    ADD CONSTRAINT chk_coupons_discount_value CHECK (discount_value > 0),
    ADD CONSTRAINT chk_coupons_used_count CHECK (used_count >= 0),
    -- desconto percentual não pode passar de 100%
    ADD CONSTRAINT chk_coupons_percentage_range CHECK (
        discount_type <> 'percentage' OR discount_value <= 100
    );

ALTER TABLE finance.promotions
    ADD CONSTRAINT chk_promotions_discount_percent CHECK (
        discount_percent IS NULL OR (discount_percent > 0 AND discount_percent <= 100)
    );

ALTER TABLE ops.products
    ADD CONSTRAINT chk_products_sale_price CHECK (sale_price >= 0),
    ADD CONSTRAINT chk_products_cost_price CHECK (cost_price >= 0),
    ADD CONSTRAINT chk_products_stock_quantity CHECK (stock_quantity >= 0),
    ADD CONSTRAINT chk_products_minimum_stock CHECK (minimum_stock >= 0);

-- stock_movements.quantity fica > 0 sempre; a direção (entrada/saída) é
-- dada por movement_type, nunca pelo sinal.
ALTER TABLE ops.stock_movements
    ADD CONSTRAINT chk_stock_movements_quantity CHECK (quantity > 0);

ALTER TABLE ops.service_orders
    ADD CONSTRAINT chk_service_orders_quantity CHECK (quantity > 0),
    ADD CONSTRAINT chk_service_orders_unit_price CHECK (unit_price >= 0),
    ADD CONSTRAINT chk_service_orders_total_price CHECK (total_price >= 0);

ALTER TABLE ops.services
    ADD CONSTRAINT chk_services_price CHECK (price >= 0);

ALTER TABLE pos.orders
    ADD CONSTRAINT chk_pos_orders_total_amount CHECK (total_amount >= 0);

ALTER TABLE pos.order_items
    ADD CONSTRAINT chk_pos_order_items_quantity CHECK (quantity > 0),
    ADD CONSTRAINT chk_pos_order_items_unit_price CHECK (unit_price >= 0),
    ADD CONSTRAINT chk_pos_order_items_total_price CHECK (total_price >= 0);

ALTER TABLE pos.payments
    ADD CONSTRAINT chk_pos_payments_amount CHECK (amount >= 0);

ALTER TABLE booking.checkouts
    ADD CONSTRAINT chk_checkouts_extra_amount CHECK (extra_amount >= 0);

ALTER TABLE booking.room_rates
    ADD CONSTRAINT chk_room_rates_weekday_price CHECK (weekday_price IS NULL OR weekday_price >= 0),
    ADD CONSTRAINT chk_room_rates_weekend_price CHECK (weekend_price IS NULL OR weekend_price >= 0),
    ADD CONSTRAINT chk_room_rates_holiday_price CHECK (holiday_price IS NULL OR holiday_price >= 0),
    ADD CONSTRAINT chk_room_rates_minimum_nights CHECK (minimum_nights > 0);

ALTER TABLE booking.room_types
    ADD CONSTRAINT chk_room_types_base_price CHECK (base_price >= 0),
    ADD CONSTRAINT chk_room_types_capacity CHECK (max_capacity >= base_capacity AND base_capacity > 0);

ALTER TABLE finance.plans
    ADD CONSTRAINT chk_plans_price_monthly CHECK (price_monthly >= 0),
    ADD CONSTRAINT chk_plans_price_yearly CHECK (price_yearly IS NULL OR price_yearly >= 0);


-- =====================================================================
-- 3) AUDITORIA AUTOMÁTICA VIA TRIGGER
-- =====================================================================
-- Recorte deliberado: só nas tabelas com relevância de compliance/
-- financeiro/segurança. Tabelas de altíssima frequência de escrita
-- (booking.room_availability, ops.stock_movements linha-a-linha, etc.)
-- ficam de fora do trigger genérico para não inflar audit_logs sem
-- necessidade — o histórico de negócio delas já existe em
-- reservation_events / stock_movements / reservation_charges.

CREATE OR REPLACE FUNCTION iam.fn_audit_trigger()
RETURNS TRIGGER AS $$
DECLARE
    v_tenant_id UUID;
    v_user_id   UUID;
    v_entity_id UUID;
BEGIN
    -- current_setting fica em branco se a aplicação não tiver setado a
    -- sessão (ex.: script administrativo) — nesse caso gravamos NULL em
    -- vez de falhar a transação toda.
    BEGIN
        v_tenant_id := current_setting('app.current_tenant', TRUE)::UUID;
    EXCEPTION WHEN OTHERS THEN
        v_tenant_id := NULL;
    END;

    BEGIN
        v_user_id := current_setting('app.current_user', TRUE)::UUID;
    EXCEPTION WHEN OTHERS THEN
        v_user_id := NULL;
    END;

    v_entity_id := COALESCE(NEW.id, OLD.id);

    INSERT INTO iam.audit_logs (tenant_id, user_id, action, entity_type, entity_id, metadata)
    VALUES (
        v_tenant_id,
        v_user_id,
        lower(TG_OP),
        TG_TABLE_SCHEMA || '.' || TG_TABLE_NAME,
        v_entity_id,
        jsonb_build_object(
            'old', to_jsonb(OLD),
            'new', to_jsonb(NEW)
        )
    );

    RETURN COALESCE(NEW, OLD);
END;
$$ LANGUAGE plpgsql;

COMMENT ON FUNCTION iam.fn_audit_trigger() IS
    'Auditoria genérica. Espera app.current_tenant/app.current_user setados via SET LOCAL no início de cada transação da aplicação; na ausência, grava NULL em vez de falhar.';

-- Aplicada tabela por tabela (explícito, não em loop automático via
-- information_schema) para deixar claro e revisável o que é auditado.
CREATE TRIGGER trg_audit_reservations
    AFTER INSERT OR UPDATE OR DELETE ON booking.reservations
    FOR EACH ROW EXECUTE FUNCTION iam.fn_audit_trigger();

CREATE TRIGGER trg_audit_reservation_charges
    AFTER INSERT OR UPDATE OR DELETE ON booking.reservation_charges
    FOR EACH ROW EXECUTE FUNCTION iam.fn_audit_trigger();

CREATE TRIGGER trg_audit_payments
    AFTER INSERT OR UPDATE OR DELETE ON finance.payments
    FOR EACH ROW EXECUTE FUNCTION iam.fn_audit_trigger();

CREATE TRIGGER trg_audit_refunds
    AFTER INSERT OR UPDATE OR DELETE ON finance.refunds
    FOR EACH ROW EXECUTE FUNCTION iam.fn_audit_trigger();

CREATE TRIGGER trg_audit_invoices
    AFTER INSERT OR UPDATE OR DELETE ON finance.invoices
    FOR EACH ROW EXECUTE FUNCTION iam.fn_audit_trigger();

CREATE TRIGGER trg_audit_guests
    AFTER INSERT OR UPDATE OR DELETE ON crm.guests
    FOR EACH ROW EXECUTE FUNCTION iam.fn_audit_trigger();

CREATE TRIGGER trg_audit_room_rates
    AFTER INSERT OR UPDATE OR DELETE ON booking.room_rates
    FOR EACH ROW EXECUTE FUNCTION iam.fn_audit_trigger();

CREATE TRIGGER trg_audit_users
    AFTER INSERT OR UPDATE OR DELETE ON iam.users
    FOR EACH ROW EXECUTE FUNCTION iam.fn_audit_trigger();

CREATE TRIGGER trg_audit_user_roles
    AFTER INSERT OR UPDATE OR DELETE ON iam.user_roles
    FOR EACH ROW EXECUTE FUNCTION iam.fn_audit_trigger();


-- =====================================================================
-- 4) ROW LEVEL SECURITY
-- =====================================================================
-- Contrato com a aplicação: toda conexão/transação que atende requisição
-- de tenant deve rodar, logo no início:
--   SET LOCAL app.current_tenant = '<uuid-do-tenant>';
--   SET LOCAL app.current_hotel_scope = '<uuid-do-hotel>' | NULL  -- opcional
-- Sem isso setado, current_setting(...) sem o segundo argumento TRUE
-- lançaria erro — por segurança (fail-closed), preferimos que falte
-- configuração derrube a query em vez de vazar dado por engano.
--
-- Duas funções de apoio, marcadas STABLE para o planner poder cachear
-- o resultado dentro da mesma query:

CREATE OR REPLACE FUNCTION iam.current_tenant_id()
RETURNS UUID
LANGUAGE sql STABLE AS $$
    SELECT current_setting('app.current_tenant', TRUE)::UUID;
$$;

-- iam.user_roles.hotel_id é nulável (NULL = papel vale para todos os
-- hotéis do tenant). Por isso a policy de hotel não pode ser um simples
-- "hotel_id = current_setting(...)"; ela precisa aceitar qualquer hotel
-- do tenant corrente quando não há escopo de hotel mais restritivo sendo
-- aplicado pela aplicação. A função abaixo só resolve o tenant; o
-- eventual recorte por hotel específico continua sendo responsabilidade
-- da query (WHERE hotel_id = ...), não da RLS — RLS aqui é a rede de
-- segurança contra vazamento entre tenants, não o controle de permissão
-- fina por papel.
COMMENT ON FUNCTION iam.current_tenant_id() IS
    'Tenant da sessão corrente, setado pela aplicação via SET LOCAL app.current_tenant no início da transação.';

-- core.hotels: base de tudo, tem tenant_id direto.
ALTER TABLE core.hotels ENABLE ROW LEVEL SECURITY;
CREATE POLICY tenant_isolation ON core.hotels
    USING (tenant_id = iam.current_tenant_id());

-- Tabelas com hotel_id direto: policy via EXISTS em core.hotels (que já
-- está protegida acima, então não há bypass). Padronizamos o nome da
-- policy como "tenant_isolation" em todas para facilitar auditoria de
-- quais tabelas têm RLS ativo (pg_policies).

ALTER TABLE booking.room_types ENABLE ROW LEVEL SECURITY;
CREATE POLICY tenant_isolation ON booking.room_types
    USING (hotel_id IN (SELECT id FROM core.hotels WHERE tenant_id = iam.current_tenant_id()));

ALTER TABLE booking.rooms ENABLE ROW LEVEL SECURITY;
CREATE POLICY tenant_isolation ON booking.rooms
    USING (hotel_id IN (SELECT id FROM core.hotels WHERE tenant_id = iam.current_tenant_id()));

ALTER TABLE booking.pricing_rules ENABLE ROW LEVEL SECURITY;
CREATE POLICY tenant_isolation ON booking.pricing_rules
    USING (hotel_id IN (SELECT id FROM core.hotels WHERE tenant_id = iam.current_tenant_id()));

ALTER TABLE booking.reservations ENABLE ROW LEVEL SECURITY;
CREATE POLICY tenant_isolation ON booking.reservations
    USING (hotel_id IN (SELECT id FROM core.hotels WHERE tenant_id = iam.current_tenant_id()));

ALTER TABLE booking.booking_sessions ENABLE ROW LEVEL SECURITY;
CREATE POLICY tenant_isolation ON booking.booking_sessions
    USING (hotel_id IN (SELECT id FROM core.hotels WHERE tenant_id = iam.current_tenant_id()));

ALTER TABLE finance.invoices ENABLE ROW LEVEL SECURITY;
CREATE POLICY tenant_isolation ON finance.invoices
    USING (hotel_id IN (SELECT id FROM core.hotels WHERE tenant_id = iam.current_tenant_id()));

ALTER TABLE finance.payment_methods ENABLE ROW LEVEL SECURITY;
CREATE POLICY tenant_isolation ON finance.payment_methods
    USING (hotel_id IN (SELECT id FROM core.hotels WHERE tenant_id = iam.current_tenant_id()));

ALTER TABLE finance.coupons ENABLE ROW LEVEL SECURITY;
CREATE POLICY tenant_isolation ON finance.coupons
    USING (hotel_id IN (SELECT id FROM core.hotels WHERE tenant_id = iam.current_tenant_id()));

ALTER TABLE finance.promotions ENABLE ROW LEVEL SECURITY;
CREATE POLICY tenant_isolation ON finance.promotions
    USING (hotel_id IN (SELECT id FROM core.hotels WHERE tenant_id = iam.current_tenant_id()));

ALTER TABLE ops.products ENABLE ROW LEVEL SECURITY;
CREATE POLICY tenant_isolation ON ops.products
    USING (hotel_id IN (SELECT id FROM core.hotels WHERE tenant_id = iam.current_tenant_id()));

ALTER TABLE ops.tasks ENABLE ROW LEVEL SECURITY;
CREATE POLICY tenant_isolation ON ops.tasks
    USING (hotel_id IN (SELECT id FROM core.hotels WHERE tenant_id = iam.current_tenant_id()));

ALTER TABLE ops.maintenance_tickets ENABLE ROW LEVEL SECURITY;
CREATE POLICY tenant_isolation ON ops.maintenance_tickets
    USING (hotel_id IN (SELECT id FROM core.hotels WHERE tenant_id = iam.current_tenant_id()));

ALTER TABLE pos.orders ENABLE ROW LEVEL SECURITY;
CREATE POLICY tenant_isolation ON pos.orders
    USING (hotel_id IN (SELECT id FROM core.hotels WHERE tenant_id = iam.current_tenant_id()));

-- Tabelas-filhas (sem hotel_id direto): a policy sobe até reservations/
-- orders/products que já filtram. Aqui vale a pena, no futuro, avaliar
-- denormalizar hotel_id para essas tabelas quentes se o custo do JOIN em
-- RLS pesar no plano de execução (EXPLAIN ANALYZE antes de decidir).

ALTER TABLE booking.reservation_rooms ENABLE ROW LEVEL SECURITY;
CREATE POLICY tenant_isolation ON booking.reservation_rooms
    USING (reservation_id IN (
        SELECT id FROM booking.reservations
         WHERE hotel_id IN (SELECT id FROM core.hotels WHERE tenant_id = iam.current_tenant_id())
    ));

ALTER TABLE booking.reservation_charges ENABLE ROW LEVEL SECURITY;
CREATE POLICY tenant_isolation ON booking.reservation_charges
    USING (reservation_id IN (
        SELECT id FROM booking.reservations
         WHERE hotel_id IN (SELECT id FROM core.hotels WHERE tenant_id = iam.current_tenant_id())
    ));

ALTER TABLE finance.payments ENABLE ROW LEVEL SECURITY;
CREATE POLICY tenant_isolation ON finance.payments
    USING (reservation_id IN (
        SELECT id FROM booking.reservations
         WHERE hotel_id IN (SELECT id FROM core.hotels WHERE tenant_id = iam.current_tenant_id())
    ));

ALTER TABLE pos.order_items ENABLE ROW LEVEL SECURITY;
CREATE POLICY tenant_isolation ON pos.order_items
    USING (order_id IN (
        SELECT id FROM pos.orders
         WHERE hotel_id IN (SELECT id FROM core.hotels WHERE tenant_id = iam.current_tenant_id())
    ));

-- iam.users/roles/audit_logs já têm tenant_id direto (ou NULL para papéis
-- de sistema, que devem ficar visíveis para todos — por isso o OR IS NULL).

ALTER TABLE iam.users ENABLE ROW LEVEL SECURITY;
CREATE POLICY tenant_isolation ON iam.users
    USING (tenant_id = iam.current_tenant_id());

ALTER TABLE iam.roles ENABLE ROW LEVEL SECURITY;
CREATE POLICY tenant_isolation ON iam.roles
    USING (tenant_id = iam.current_tenant_id() OR tenant_id IS NULL);

ALTER TABLE iam.audit_logs ENABLE ROW LEVEL SECURITY;
CREATE POLICY tenant_isolation ON iam.audit_logs
    USING (tenant_id = iam.current_tenant_id() OR tenant_id IS NULL);

-- IMPORTANTE: roles de aplicação que rodam jobs administrativos/batch
-- (ex.: fechamento noturno, integrações) devem usar um role de banco com
-- BYPASSRLS, nunca desligar RLS na tabela. Ex.:
--   CREATE ROLE mecdesk_batch_worker WITH LOGIN BYPASSRLS;
-- Isso mantém a policy como rede de segurança padrão para o role da
-- aplicação web (que é o que efetivamente atende requisição de tenant).


-- =====================================================================
-- 5) MATERIALIZED VIEWS PARA DASHBOARD
-- =====================================================================
-- Índices exigidos para permitir REFRESH MATERIALIZED VIEW CONCURRENTLY
-- (senão o refresh trava leitura durante a atualização).

CREATE MATERIALIZED VIEW ops.mv_daily_revenue AS
SELECT
    h.id AS hotel_id,
    r.check_in_date AS revenue_date,
    r.currency,
    SUM(rc.total_amount) FILTER (WHERE NOT rc.is_discount) AS gross_amount,
    SUM(rc.total_amount) FILTER (WHERE rc.is_discount)     AS discount_amount,
    COUNT(DISTINCT r.id) AS reservations_count
FROM booking.reservations r
JOIN core.hotels h ON h.id = r.hotel_id
JOIN booking.reservation_charges rc ON rc.reservation_id = r.id
WHERE r.reservation_status NOT IN ('canceled', 'no_show')
GROUP BY h.id, r.check_in_date, r.currency;

CREATE UNIQUE INDEX uq_mv_daily_revenue
    ON ops.mv_daily_revenue (hotel_id, revenue_date, currency);

CREATE MATERIALIZED VIEW ops.mv_occupancy AS
SELECT
    rt.hotel_id,
    a.date AS occupancy_date,
    COUNT(*) FILTER (WHERE a.status = 'occupied')  AS rooms_occupied,
    COUNT(*) FILTER (WHERE a.status = 'available') AS rooms_available,
    COUNT(*) AS rooms_total
FROM booking.room_availability a
JOIN booking.rooms r ON r.id = a.room_id
JOIN booking.room_types rt ON rt.id = r.room_type_id
GROUP BY rt.hotel_id, a.date;

CREATE UNIQUE INDEX uq_mv_occupancy
    ON ops.mv_occupancy (hotel_id, occupancy_date);

CREATE MATERIALIZED VIEW ops.mv_room_status AS
SELECT
    r.hotel_id,
    r.id AS room_id,
    r.number,
    r.status,
    hk.status AS pending_housekeeping_status,
    hk.scheduled_date AS housekeeping_scheduled_date
FROM booking.rooms r
LEFT JOIN LATERAL (
    SELECT status, scheduled_date
      FROM ops.housekeeping_tasks
     WHERE room_id = r.id
       AND status <> 'verified'
     ORDER BY scheduled_date DESC
     LIMIT 1
) hk ON TRUE;

CREATE UNIQUE INDEX uq_mv_room_status
    ON ops.mv_room_status (room_id);

CREATE MATERIALIZED VIEW ops.mv_monthly_sales AS
SELECT
    hotel_id,
    date_trunc('month', revenue_date)::DATE AS month,
    currency,
    SUM(gross_amount)    AS gross_amount,
    SUM(discount_amount) AS discount_amount,
    SUM(reservations_count) AS reservations_count
FROM ops.mv_daily_revenue
GROUP BY hotel_id, date_trunc('month', revenue_date), currency;

CREATE UNIQUE INDEX uq_mv_monthly_sales
    ON ops.mv_monthly_sales (hotel_id, month, currency);

COMMENT ON MATERIALIZED VIEW ops.mv_daily_revenue IS
    'Refresh recomendado: a cada 15-30 min via job (REFRESH MATERIALIZED VIEW CONCURRENTLY), não em tempo real.';
COMMENT ON MATERIALIZED VIEW ops.mv_occupancy IS
    'Refresh recomendado: a cada 15-30 min. Fonte é room_availability, já pré-calculada.';
COMMENT ON MATERIALIZED VIEW ops.mv_room_status IS
    'Refresh recomendado: a cada 2-5 min (estado operacional, muda mais rápido que receita/ocupação).';
COMMENT ON MATERIALIZED VIEW ops.mv_monthly_sales IS
    'Agregação sobre mv_daily_revenue — refresh depois dela, não em paralelo.';

-- Nota: essas quatro MVs cobrem o pedido original. Se o volume de
-- reservation_charges crescer muito, considerar recalcular mv_daily_revenue
-- de forma incremental (trigger que atualiza uma tabela normal em vez de
-- REFRESH completo) — decisão para revisitar quando houver métricas reais
-- de tempo de refresh.
