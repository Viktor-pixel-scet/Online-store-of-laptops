<?php
// Виправлений шлях до файлу бази даних
require_once __DIR__ . '/../database/Database.php';

function getDatabaseConnection() {
    $db = new Database();
    $pdo = $db->getConnection();
    if (!$pdo) {
        throw new PDOException('Немає підключення до бази даних');
    }
    return $pdo;
}

function getGames($pdo) {
    try {
        $stmt = $pdo->prepare("SELECT game_code, game_name, min_fps, max_fps, category FROM games");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('Помилка запиту до таблиці games: ' . $e->getMessage());
        throw $e;
    }
}

function handleRequest() {
    setJsonHeaders();

    try {
        $games = fetchGames();
        validateGamesData($games);
        sendSuccessResponse($games);

    } catch(PDOException $e) {
        handleDatabaseError($e);
    } catch(Exception $e) {
        handleGenericError($e);
    }
}

function setJsonHeaders() {
    header('Content-Type: application/json');
}

function fetchGames() {
    $pdo = getDatabaseConnection();
    return getGames($pdo);
}

function validateGamesData($games) {
    if (empty($games)) {
        sendEmptyGamesResponse();
    }
}

function sendSuccessResponse($games) {
    echo json_encode($games);
}

function sendEmptyGamesResponse() {
    http_response_code(404);
    echo json_encode([
        'error' => 'Список ігор порожній',
        'message' => 'Наразі немає доступних ігор'
    ]);
    exit;
}

function handleDatabaseError($e) {
    http_response_code(500);
    error_log('Помилка бази даних: ' . $e->getMessage());
    echo json_encode([
        'error' => 'Технічна помилка',
        'message' => 'Не вдалося отримати список ігор. Спробуйте пізніше.'
    ]);
    exit;
}

function handleGenericError($e) {
    http_response_code(500);
    error_log('Невідома помилка: ' . $e->getMessage());
    echo json_encode([
        'error' => 'Критична помилка',
        'message' => 'Виникла неочікувана помилка. Спробуйте пізніше.'
    ]);
    exit;
}

handleRequest();
?>