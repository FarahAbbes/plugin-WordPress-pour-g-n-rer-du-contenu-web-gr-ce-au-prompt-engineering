<?php
header('Content-Type: application/json');

$host = 'localhost';
$dbname = 'template_db';
$username = 'root';
$password = '';

try {
    // Connexion à la base de données
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Récupération et validation de l'ID du template
    $templateId = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);

    if (!$templateId) {
        throw new Exception("ID de template invalide.");
    }

    // Vérification de l'existence du template
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM template WHERE id = :template_id");
    $stmt->execute([':template_id' => $templateId]);

    if ($stmt->fetchColumn() == 0) {
        throw new Exception("Le template avec l'ID spécifié n'existe pas.");
    }

    // Début de la transaction
    $pdo->beginTransaction();

    // Suppression des données de soumission associées
    $stmt = $pdo->prepare("DELETE FROM submission_data 
                           WHERE submission_id IN (SELECT id FROM submissions WHERE template_id = :template_id)");
    $stmt->execute([':template_id' => $templateId]);

    // Suppression des soumissions associées
    $stmt = $pdo->prepare("DELETE FROM submissions WHERE template_id = :template_id");
    $stmt->execute([':template_id' => $templateId]);

    // Suppression des champs du template
    $stmt = $pdo->prepare("DELETE FROM template_fields WHERE template_id = :template_id");
    $stmt->execute([':template_id' => $templateId]);

    // Suppression du template lui-même
    $stmt = $pdo->prepare("DELETE FROM template WHERE id = :template_id");
    $stmt->execute([':template_id' => $templateId]);

    // Validation de la transaction
    $pdo->commit();

    // Réponse JSON en cas de succès
    echo json_encode(['success' => true, 'message' => 'Template supprimé avec succès']);

} catch(Exception $e) {
    // Annulation de la transaction en cas d'erreur
    if (isset($pdo)) {
        $pdo->rollBack();
    }
    // Réponse JSON en cas d'erreur
    echo json_encode(['success' => false, 'message' => 'Erreur : ' . $e->getMessage()]);
}

?>
