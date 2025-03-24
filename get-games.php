<?php
require_once 'db_connection.php';

header('Content-Type: application/json');

try {
    $stmt = $pdo->query("SELECT game_code, game_name, min_fps, max_fps, category FROM games");
    $games = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($games);

} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Database error',
        'message' => $e->getMessage()
    ]);
}