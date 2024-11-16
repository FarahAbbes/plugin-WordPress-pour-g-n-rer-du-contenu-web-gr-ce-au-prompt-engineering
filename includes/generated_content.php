<?php
// Connexion à la base de données
$host = 'localhost';
$dbname = 'template_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $submissionId = filter_var($_GET['submission_id'] ?? null, FILTER_VALIDATE_INT);
    if (!$submissionId) {
        throw new Exception("ID de soumission invalide.");
    }

    // Récupérer le contenu généré à partir de la base de données ou des fichiers
    // Par exemple :
    $stmt = $pdo->prepare("SELECT * FROM generated_content WHERE submission_id = :submission_id");
    $stmt->execute([':submission_id' => $submissionId]);
    $generatedContent = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$generatedContent) {
        throw new Exception("Contenu généré introuvable.");
    }

    echo "<h1>Contenu généré</h1>";
    echo "<div>{$generatedContent['content']}</div>";

} catch(PDOException $e) {
    error_log('PDO Error: ' . $e->getMessage());
    echo "Erreur de base de données. Veuillez réessayer plus tard.";
} catch(Exception $e) {
    error_log('Error: ' . $e->getMessage());
    echo $e->getMessage();
}
?>
