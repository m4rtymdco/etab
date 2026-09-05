<?php
/**
 * One-time installer: creates database, schema, and sample users.
 * Visit http://localhost/eTab/install.php then delete this file in production.
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');

$config = require __DIR__ . '/config/config.php';
$db = $config['db'];
$done = false;
$error = null;
$info = [];

$isCli = PHP_SAPI === 'cli';
if ($isCli || (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST')) {
    try {
        $dbName = str_replace('`', '', $db['name']);
        $opts = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
        $dsnDb = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=%s', $db['host'], $db['port'], $dbName, $db['charset']);
        try {
            $pdo = new PDO($dsnDb, $db['user'], $db['pass'], $opts);
        } catch (Throwable $connectErr) {
            $dsn = sprintf('mysql:host=%s;port=%s;charset=%s', $db['host'], $db['port'], $db['charset']);
            $pdo = new PDO($dsn, $db['user'], $db['pass'], $opts);
            try {
                $pdo->exec('CREATE DATABASE IF NOT EXISTS `' . $dbName . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
            } catch (Throwable $createErr) {
                // Hostinger databases already exist and CREATE DATABASE is denied.
            }
            $pdo->exec('USE `' . $dbName . '`');
        }

        $sql = file_get_contents(__DIR__ . '/database/schema.sql');
        $sql = preg_replace('/^CREATE DATABASE.*$/m', '', $sql);
        $sql = preg_replace('/^USE `[^`]+`;$/m', '', $sql);
        $sql = preg_replace('/^\s*--.*$/m', '', $sql);
        foreach (array_filter(array_map('trim', explode(';', $sql))) as $stmt) {
            if ($stmt === '') {
                continue;
            }
            $pdo->exec($stmt);
        }

        $adminHash = password_hash('Admin@123', PASSWORD_DEFAULT);
        $judgeHash = password_hash('Judge@123', PASSWORD_DEFAULT);
        $judge2Hash = password_hash('Judge@123', PASSWORD_DEFAULT);

        $ins = $pdo->prepare('INSERT INTO users (name, email, password_hash, role, is_active) VALUES (?, ?, ?, ?, 1)');
        $ins->execute(['System Admin', 'admin@etab.local', $adminHash, 'admin']);
        $adminId = (int) $pdo->lastInsertId();
        $ins->execute(['Jordan Blake', 'judge@etab.local', $judgeHash, 'judge']);
        $j1 = (int) $pdo->lastInsertId();
        $ins->execute(['Casey Rivera', 'judge2@etab.local', $judge2Hash, 'judge']);
        $j2 = (int) $pdo->lastInsertId();
        $ins->execute(['Morgan Lee', 'judge3@etab.local', password_hash('Judge@123', PASSWORD_DEFAULT), 'judge']);
        $j3 = (int) $pdo->lastInsertId();

        $pdo->prepare('INSERT INTO events (name, description, event_date, event_time, venue, status, drop_high_low, score_min, score_max, rounds, created_by, results_published)
            VALUES (?, ?, CURDATE(), ?, ?, ?, 1, 1, 100, 1, ?, 0)')
            ->execute([
                'Grand Talent Showcase 2026',
                'Annual multi-category talent competition. Scores are weighted and the highest and lowest judge totals are dropped.',
                '18:00:00',
                'Civic Center Auditorium',
                'ongoing',
                $adminId,
            ]);
        $eventId = (int) $pdo->lastInsertId();

        $crit = $pdo->prepare('INSERT INTO criteria (event_id, name, description, weight, sort_order) VALUES (?, ?, ?, ?, ?)');
        $crit->execute([$eventId, 'Technique', 'Skill, control, and execution', 30, 1]);
        $crit->execute([$eventId, 'Stage Presence', 'Charisma, confidence, audience connection', 25, 2]);
        $crit->execute([$eventId, 'Creativity', 'Originality and artistic interpretation', 25, 3]);
        $crit->execute([$eventId, 'Overall Impact', 'Memorable performance quality', 20, 4]);

        $pdo->prepare('INSERT INTO criteria_templates (name, description, created_by) VALUES (?, ?, ?)')
            ->execute(['Standard Performance', 'Technique, presence, creativity, impact', $adminId]);
        $tid = (int) $pdo->lastInsertId();
        $ti = $pdo->prepare('INSERT INTO criteria_template_items (template_id, name, description, weight, sort_order) VALUES (?, ?, ?, ?, ?)');
        $ti->execute([$tid, 'Technique', 'Skill and execution', 30, 1]);
        $ti->execute([$tid, 'Stage Presence', 'Charisma and connection', 25, 2]);
        $ti->execute([$tid, 'Creativity', 'Originality', 25, 3]);
        $ti->execute([$tid, 'Overall Impact', 'Memorable quality', 20, 4]);

        $cstmt = $pdo->prepare('INSERT INTO contestants (name, category, status) VALUES (?, ?, "active")');
        $people = [
            ['Ava Santos', 'Exclusive'],
            ['Noah Cruz', 'Exclusive'],
            ['Mia Reyes', 'Exclusive'],
            ['Liam Tan', 'Open'],
            ['Sofia Lim', 'Open'],
            ['Ethan Navarro', 'Open'],
        ];
        $ce = $pdo->prepare('INSERT INTO contestant_events (contestant_id, event_id, entry_number) VALUES (?, ?, ?)');
        $n = 1;
        foreach ($people as [$name, $cat]) {
            $cstmt->execute([$name, $cat]);
            $ce->execute([(int) $pdo->lastInsertId(), $eventId, str_pad((string) $n, 2, '0', STR_PAD_LEFT)]);
            $n++;
        }

        $ja = $pdo->prepare('INSERT INTO judge_assignments (judge_id, event_id) VALUES (?, ?)');
        $ja->execute([$j1, $eventId]);
        $ja->execute([$j2, $eventId]);
        $ja->execute([$j3, $eventId]);

        $done = true;
        $info = [
            'Admin: admin@etab.local / Admin@123',
            'Judge: judge@etab.local / Judge@123',
            'Judge: judge2@etab.local / Judge@123',
            'Judge: judge3@etab.local / Judge@123',
        ];
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

if ($isCli) {
    if ($error) {
        fwrite(STDERR, "Install failed: {$error}\n");
        exit(1);
    }
    echo "eTab database installed.\n";
    foreach ($info as $line) {
        echo $line . "\n";
    }
    echo "Open http://localhost:8080/eTab/login\n";
    exit($done ? 0 : 1);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Install eTab</title>
  <style>
    body { font-family: Segoe UI, system-ui, sans-serif; background:#0f172a; color:#e2e8f0; display:flex; min-height:100vh; align-items:center; justify-content:center; margin:0; }
    .card { background:#1e293b; padding:2rem; border-radius:16px; max-width:520px; width:90%; box-shadow:0 20px 50px rgba(0,0,0,.35); }
    h1 { margin-top:0; color:#818cf8; }
    button { background:#4f46e5; color:#fff; border:0; padding:.75rem 1.25rem; border-radius:10px; font-weight:600; cursor:pointer; }
    .ok { color:#34d399; }
    .err { color:#f87171; }
    code { background:#0f172a; padding:.15rem .4rem; border-radius:6px; }
  </style>
</head>
<body>
  <div class="card">
    <h1>eTab installer</h1>
    <?php if ($done): ?>
      <p class="ok">Database created and sample data loaded.</p>
      <ul><?php foreach ($info as $line): ?><li><?= htmlspecialchars($line) ?></li><?php endforeach; ?></ul>
      <p><a href="index.php?r=login" style="color:#a5b4fc">Go to login</a> (or <a href="login" style="color:#a5b4fc">/login</a>)</p>
      <p>Delete <code>install.php</code> after setup on a shared host.</p>
    <?php else: ?>
      <p>This will create the <code><?= htmlspecialchars($db['name']) ?></code> database on <code><?= htmlspecialchars($db['host']) ?></code> and seed demo users.</p>
      <?php if ($error): ?><p class="err"><?= htmlspecialchars($error) ?></p><?php endif; ?>
      <form method="post"><button type="submit">Install now</button></form>
    <?php endif; ?>
  </div>
</body>
</html>
