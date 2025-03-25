<?php
require_once '../../backend/database/db_connection.php';

header('Content-Type: application/json');

try {
    if (!$pdo) {
        throw new PDOException('Немає підключення до бази даних');
    }

    $stmt = $pdo->prepare("SELECT game_code, game_name, min_fps, max_fps, category FROM games");

    if (!$stmt->execute()) {
        throw new PDOException('Не вдалося виконати запит');
    }

    $games = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($games)) {
        http_response_code(404);
        echo json_encode([
            'error' => 'Список ігор порожній',
            'message' => 'Наразі немає доступних ігор'
        ]);
        exit;
    }

    echo json_encode($games);

} catch(PDOException $e) {
    http_response_code(500);
    error_log('Помилка бази даних: ' . $e->getMessage());
    echo json_encode([
        'error' => 'Технічна помилка',
        'message' => 'Не вдалося отримати список ігор. Спробуйте пізніше.'
    ]);
    exit;

} catch(Exception $e) {
    http_response_code(500);
    error_log('Невідома помилка: ' . $e->getMessage());
    echo json_encode([
        'error' => 'Критична помилка',
        'message' => 'Виникла неочікувана помилка. Спробуйте пізніше.'
    ]);
    exit;
}