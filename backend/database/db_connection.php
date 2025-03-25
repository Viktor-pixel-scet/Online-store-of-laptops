<?php
$host = 'localhost';
$dbname = 'laptop_market';
$username = 'root';
$password = '';

try {
    if (empty($host) || empty($dbname) || empty($username)) {
        throw new InvalidArgumentException('Відсутні параметри підключення до бази даних');
    }

    if (strlen($password) > 255) {
        throw new InvalidArgumentException('Некоректна довжина паролю');
    }

    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
            PDO::ATTR_TIMEOUT => 5
        ]
    );

    $pdo->query("SELECT 1");

} catch (InvalidArgumentException $e) {
    error_log('Помилка параметрів підключення: ' . $e->getMessage());
    die('Помилка налаштувань підключення до бази даних');

} catch (PDOException $e) {
    $errorCode = $e->getCode();

    switch ($errorCode) {
        case 1045:
            error_log('Помилка аутентифікації в базі даних: ' . $e->getMessage());
            die('Невірний логін або пароль');

        case 1049:
            error_log('Неіснуюча база даних: ' . $e->getMessage());
            die('Вказана база даних не існує');

        case 2002:
            error_log('Неможливо підключитися до хоста: ' . $e->getMessage());
            die('Проблема з підключенням до сервера бази даних');

        default:
            error_log('Критична помилка бази даних: ' . $e->getMessage());
            die('Технічні проблеми. Спробуйте пізніше.');
    }

} catch (Exception $e) {
    error_log('Невідома помилка підключення: ' . $e->getMessage());
    die('Системна помилка підключення');
}