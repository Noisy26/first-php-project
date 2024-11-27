<?php
$host = 'localhost';
$user = 'root';
$password = '123';
$dbname = 'UniversityDB';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;", $user, $password);
} catch (PDOException $e) {
    print "Ошибка!: " . $e->getMessage() . '<br>';
}
