<?php
header('Content-Type: application/json');

$host = 'localhost';
$dbname = 'template_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $fileName = $_GET['fileName'] ?? '';

    if (empty($fileName)) {
        throw new Exception("Le nom du fichier est requis.");
    }

    $stmt = $pdo->prepare("SELECT contenu_html FROM template WHERE nom_fichier = :fileName");
    $stmt->execute([':fileName' => $fileName]);
    $content = $stmt->fetchColumn();

    if ($content === false) {
        throw new Exception("Template non trouvé.");
    }

    // Renvoie le contenu HTML brut sans l'échapper
    echo json_encode(['success' => true, 'content' => $content], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur de base de données : ' . $e->getMessage()]);
} catch(Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>