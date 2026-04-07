<?php
header('Content-Type: application/json; charset=utf-8');
require_once 'db.php';

function sendJson($payload, $code = 200) {
    http_response_code($code);
    echo json_encode($payload);
    exit;
}

function getJsonInput() {
    $raw = file_get_contents('php://input');
    return json_decode($raw, true) ?? [];
}

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        $showDeleted = !empty($_GET['show_deleted']);

        if (!empty($_GET['id'])) {
            if ($showDeleted) {
                $stmt = $pdo->prepare('SELECT id, original_id, name, coordinates, created_at, deleted_at FROM deleted_geofences WHERE id = :id');
            } else {
                $stmt = $pdo->prepare('SELECT id, name, coordinates, created_at FROM geofences WHERE id = :id');
            }
            $stmt->execute([':id' => $_GET['id']]);
            $row = $stmt->fetch();
            if (!$row) {
                sendJson(['error' => 'Geofence not found'], 404);
            }
            $row['coordinates'] = json_decode($row['coordinates'], true);
            sendJson($row);
        }

        if ($showDeleted) {
            $stmt = $pdo->query('SELECT id, original_id, name, coordinates, created_at, deleted_at FROM deleted_geofences ORDER BY deleted_at DESC');
        } else {
            $stmt = $pdo->query('SELECT id, name, coordinates, created_at FROM geofences ORDER BY created_at DESC');
        }
        $rows = $stmt->fetchAll();
        foreach ($rows as &$row) {
            $row['coordinates'] = json_decode($row['coordinates'], true);
        }
        sendJson($rows);
    }

    if ($method === 'POST') {
        $data = getJsonInput();
        if (empty($data['name']) || empty($data['coordinates']) || !is_array($data['coordinates'])) {
            sendJson(['error' => 'Invalid geofence payload'], 400);
        }

        $stmt = $pdo->prepare('INSERT INTO geofences (name, coordinates) VALUES (:name, :coordinates)');
        $stmt->execute([
            ':name' => $data['name'],
            ':coordinates' => json_encode($data['coordinates']),
        ]);

        sendJson(['success' => true, 'id' => $pdo->lastInsertId()], 201);
    }

    if ($method === 'PUT') {
        $id = !empty($_GET['id']) ? (int)$_GET['id'] : null;
        if (!$id) {
            sendJson(['error' => 'Missing geofence ID'], 400);
        }

        $data = getJsonInput();
        if (empty($data['name']) || empty($data['coordinates']) || !is_array($data['coordinates'])) {
            sendJson(['error' => 'Invalid geofence payload'], 400);
        }

        $stmt = $pdo->prepare('UPDATE geofences SET name = :name, coordinates = :coordinates WHERE id = :id');
        $stmt->execute([
            ':id' => $id,
            ':name' => $data['name'],
            ':coordinates' => json_encode($data['coordinates']),
        ]);

        sendJson(['success' => true]);
    }

    if ($method === 'DELETE') {
        $id = !empty($_GET['id']) ? (int)$_GET['id'] : null;
        if (!$id) {
            sendJson(['error' => 'Missing geofence ID'], 400);
        }

        $stmt = $pdo->prepare('SELECT id, name, coordinates, created_at FROM geofences WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        if (!$row) {
            sendJson(['error' => 'Geofence not found'], 404);
        }

        $archive = $pdo->prepare('INSERT INTO deleted_geofences (original_id, name, coordinates, created_at) VALUES (:original_id, :name, :coordinates, :created_at)');
        $archive->execute([
            ':original_id' => $row['id'],
            ':name' => $row['name'],
            ':coordinates' => $row['coordinates'],
            ':created_at' => $row['created_at'],
        ]);

        $stmt = $pdo->prepare('DELETE FROM geofences WHERE id = :id');
        $stmt->execute([':id' => $id]);

        sendJson(['success' => $stmt->rowCount() > 0]);
    }

    sendJson(['error' => 'Unsupported HTTP method'], 405);
} catch (PDOException $e) {
    sendJson(['error' => 'Database error', 'details' => $e->getMessage()], 500);
}
