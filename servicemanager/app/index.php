<?php
$host = getenv('DB_HOST') ?: 'db';
$db   = getenv('DB_NAME') ?: 'gswstore';
$user = getenv('DB_USER') ?: 'gswuser';
$pass = getenv('DB_PASSWORD') ?: '';

$mysqli = @new mysqli($host, $user, $pass, $db);

if ($mysqli->connect_errno) {
    http_response_code(503);
    echo '<h1>ServiceManager</h1>';
    echo '<p>Base de datos no disponible.</p>';
    exit;
}

$result = $mysqli->query('SELECT id, nombre, estado FROM servicios ORDER BY id');
$rows = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
$total = count($rows);
?>
<h1>ServiceManager Web Platform</h1>
<p>Contenedor web operativo.</p>
<p>MySQL conectado: <?php echo htmlspecialchars($mysqli->server_info); ?></p>
<p>Servicios registrados: <?php echo (int) $total; ?></p>
<table border="1" cellpadding="6" cellspacing="0">
    <thead>
        <tr><th>id</th><th>nombre</th><th>estado</th></tr>
    </thead>
    <tbody>
        <?php foreach ($rows as $row): ?>
        <tr>
            <td><?php echo (int) $row['id']; ?></td>
            <td><?php echo htmlspecialchars($row['nombre']); ?></td>
            <td><?php echo htmlspecialchars($row['estado']); ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<p>Versión 1.0</p>
