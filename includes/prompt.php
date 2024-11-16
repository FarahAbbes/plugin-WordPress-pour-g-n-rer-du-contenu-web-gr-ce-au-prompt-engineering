<?php
try {
    $pdo = new PDO("mysql:host=localhost;dbname=template_db", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Récupérer la dernière soumission
    $stmt = $pdo->query("SELECT id, template_id FROM submissions ORDER BY date_soumission DESC LIMIT 1");
    $submission = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$submission) {
        throw new Exception("Aucune soumission trouvée.");
    }

    $submissionId = $submission['id'];
    $templateId = $submission['template_id'];

    // Récupérer les champs du template et leurs valeurs soumises
    $stmt = $pdo->prepare("
        SELECT tf.label, sd.valeur
        FROM template_fields tf
        JOIN submission_data sd ON tf.id = sd.field_id
        WHERE sd.submission_id = :submission_id
    ");
    $stmt->execute([':submission_id' => $submissionId]);
    $fields = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Construire le prompt initial
    $prompt = "Agissez en tant qu'expert en rédaction web et en référencement naturel pour créer le contenu d'une page '..' pour un site\n\n";
    $prompt .= "Paramètres :\n";

    foreach ($fields as $field) {
        $prompt .= "{$field['label']} : {$field['valeur']}\n";
    }

    $prompt .= "\nGénérez un contenu riche, pertinent et optimisé pour le SEO tout en restant engageant et authentique.";

    // Mettre à jour le prompt spécifique dans la table submissions
    $updateStmt = $pdo->prepare("UPDATE submissions SET prompt_specifique = :prompt WHERE id = :id");
    $updateStmt->execute([
        ':prompt' => $prompt,
        ':id' => $submissionId
    ]);

    echo "Prompt généré et enregistré avec succès.";
} catch(PDOException $e) {
    echo "Erreur de base de données : " . $e->getMessage();
} catch(Exception $e) {
    echo $e->getMessage();
}
?>