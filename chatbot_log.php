<?php
require_once '../includes/session.php';
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isLoggedIn()) {
    $question = trim($_POST['question'] ?? '');
    $response = trim($_POST['response'] ?? '');

    if (!empty($question) && !empty($response)) {
        $stmt = $conn->prepare("INSERT INTO chatbot_logs (user_id, question, response) VALUES (?, ?, ?)");
        $stmt->execute([getUserId(), $question, $response]);
    }
}
?>