<?php
// Paramètres de connexion à la base de données
$host = 'localhost';
$dbname = 'template_db';
$username = 'root';
$password = '';

// Vérifier si un ID a été passé en paramètre
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID de template invalide.");
}

$templateId = $_GET['id'];

try {
    // Connexion à la base de données
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Récupérer les détails du template
    $stmt = $pdo->prepare("SELECT * FROM template WHERE id = :id");
    $stmt->execute([':id' => $templateId]);
    $template = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$template) {
        die("Template non trouvé.");
    }

    // Traiter la soumission du formulaire
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $newContent = $_POST['content'] ?? '';
        $newName = $_POST['name'] ?? '';

        if (!empty($newContent) && !empty($newName)) {
            $pdo->beginTransaction();

            try {
                // Mettre à jour le template existant
                $updateStmt = $pdo->prepare("UPDATE template SET nom_fichier = :name, contenu_html = :content WHERE id = :id");
                $updateStmt->execute([
                    ':name' => $newName,
                    ':content' => $newContent,
                    ':id' => $templateId
                ]);

                $pdo->commit();
                $message = "Template existant mis à jour avec succès.";
            } catch (Exception $e) {
                $pdo->rollBack();
                $message = "Erreur lors de la mise à jour : " . $e->getMessage();
            }
        } else {
            $message = "Erreur : Le nom et le contenu du template sont requis.";
        }
    }
} catch(PDOException $e) {
    die("Erreur de base de données : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Éditer le Template</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>Éditer le Template</h1>
        
        <?php if (isset($message)): ?>
            <div class="alert alert-info"><?php echo $message; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="name">Nom du Template:</label>
                <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($template['nom_fichier']); ?>" required>
            </div>
            <div class="form-group">
                <label for="content">Contenu HTML:</label>
                <textarea class="form-control" id="content" name="content" rows="10" required><?php echo htmlspecialchars($template['contenu_html']); ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>