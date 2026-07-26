<?php
try {
    $pdo = new PDO('pgsql:host=127.0.0.1;dbname=reserva_litoral', 'postgres', 'admin');
    
    $stmt = $pdo->query("SELECT table_schema, table_name FROM information_schema.tables WHERE table_schema NOT IN ('pg_catalog', 'information_schema')");
    $tables = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($tables as $t) {
        $schema = $t['table_schema'];
        $table = $t['table_name'];
        $pdo->exec("ALTER TABLE \"{$schema}\".\"{$table}\" OWNER TO reserva_app;");
    }
    
    // Insert migration entry
    $migrationName = '2026_07_26_013838_alter_rooms_status_constraint_for_housekeeping';
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM public.migrations WHERE migration = ?");
    $stmt->execute([$migrationName]);
    if ($stmt->fetchColumn() == 0) {
        $stmtBatch = $pdo->query("SELECT MAX(batch) FROM public.migrations");
        $batch = ((int)$stmtBatch->fetchColumn()) + 1;
        $stmtInsert = $pdo->prepare("INSERT INTO public.migrations (migration, batch) VALUES (?, ?)");
        $stmtInsert->execute([$migrationName, $batch]);
        echo "Migration registrada com sucesso!\n";
    }

    echo "Propriedade de todas as tabelas alterada para reserva_app com sucesso!\n";
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}
