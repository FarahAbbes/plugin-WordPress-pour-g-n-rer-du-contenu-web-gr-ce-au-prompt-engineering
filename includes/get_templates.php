<?php
header('Content-Type: application/json');

// Database connection parameters
$host = 'localhost';
$dbname = 'template_db';
$username = 'root';
$password = '';

try {
    // Connect to the database
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch all templates from the database
    $stmt = $pdo->query("SELECT * FROM template");
    $templates = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Return templates as JSON
    echo json_encode(['success' => true, 'templates' => $templates]);

} catch(PDOException $e) {
    // Database error
    echo json_encode(['success' => false, 'message' => 'Erreur de base de données : ' . $e->getMessage()]);
} catch(Exception $e) {
    // Other errors
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
