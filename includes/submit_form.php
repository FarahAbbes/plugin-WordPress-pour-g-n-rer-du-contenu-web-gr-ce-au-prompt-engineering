<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);
set_time_limit(120); // 2 minutes

header('Content-Type: application/json');

error_log('Received POST data: ' . print_r($_POST, true));

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

$host = 'localhost';
$dbname = 'template_db';
$username = 'root';
$password = '';

// Vérification reCAPTCHA
$recaptchaSecret = '6Ld3KxYqAAAAAOq1QbqB00J97ZDWoSr0vOf8ZfMe';
$recaptchaResponse = $_POST['g-recaptcha-response'] ?? '';

error_log('reCAPTCHA response: ' . $recaptchaResponse);

$verifyResponse = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret='.$recaptchaSecret.'&response='.$recaptchaResponse);
error_log('Verify response: ' . $verifyResponse);

$responseData = json_decode($verifyResponse);

if(!$responseData->success) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Échec de la vérification reCAPTCHA']);
    exit;
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

    $formData = $_POST;
    $templateId = filter_var($formData['template_id'] ?? null, FILTER_VALIDATE_INT);

    if (!$templateId) {
        throw new Exception("L'ID du template est manquant ou invalide.");
    }

    $pdo->beginTransaction();

    // Insérer la soumission
    $stmt = $pdo->prepare("INSERT INTO submissions (template_id) VALUES (:template_id)");
    $stmt->execute([':template_id' => $templateId]);
    $submissionId = $pdo->lastInsertId();

    // Récupérer les champs du template
    $stmt = $pdo->prepare("SELECT id, nom, type, label, options, attributs_supplementaires FROM template_fields WHERE template_id = :template_id");
    $stmt->execute([':template_id' => $templateId]);
    $templateFields = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Insérer les données soumises
    $insertDataStmt = $pdo->prepare("INSERT INTO submission_data (submission_id, field_id, valeur) VALUES (:submission_id, :field_id, :valeur)");
    $prompt = "Agissez en tant qu'expert en rédaction web et en référencement naturel pour créer le contenu d'une page '..' pour un site\n\n";
    $prompt .= "Paramètres :\n";

    foreach ($templateFields as $field) {
        $fieldName = $field['nom'];
        $fieldId = $field['id'];
        $fieldType = $field['type'];
        $value = $formData[$fieldName] ?? null;
switch ($fieldType) {
            case 'number':
                $value = filter_var($value, FILTER_VALIDATE_FLOAT);
                if ($value === false) {
                    throw new Exception("Valeur invalide pour le champ numérique: " . $fieldName);
                }
                break;
            case 'date':
                if (!empty($value) && !preg_match("/^\d{4}-\d{2}-\d{2}$/", $value)) {
                    throw new Exception("Format de date invalide pour le champ: " . $fieldName);
                }
                break;
            case 'textarea':
                $value = htmlspecialchars($value);
                break;
            case 'url':
                $value = filter_var($value, FILTER_VALIDATE_URL);
                if ($value === false) {
                    throw new Exception("URL invalide pour le champ: " . $fieldName);
                }
                break;
            case 'file':
                if (isset($_FILES[$fieldName])) {
                    $uploadedFile = $_FILES[$fieldName];
                    $uploadDir = 'uploads/';
                    $filePath = $uploadDir . basename($uploadedFile['name']);
                    if (move_uploaded_file($uploadedFile['tmp_name'], $filePath)) {
                        $value = $filePath;
                    } else {
                        throw new Exception("Erreur lors de l'upload du fichier: " . $fieldName);
                    }
                }
                break;
            case 'checkbox':
                $value = [];
                $allOptions = explode(';', $field['options']);
                $allowedOptions = [];
                foreach ($allOptions as $option) {
                    list($optionValue, $optionLabel) = explode(':', $option);
                    $allowedOptions[$optionValue] = $optionLabel;
                }
                foreach ($allowedOptions as $optionValue => $optionLabel) {
                    $checkboxName = $fieldName . '[' . $optionValue . ']';
                    if (isset($formData[$checkboxName]) && $formData[$checkboxName] === 'on') {
                        $value[] = $optionValue;
                    }
                }
                $value = json_encode($value);
                break;
            case 'select':
                $isMultiple = isset($field['attributs_supplementaires']) && 
                              json_decode($field['attributs_supplementaires'], true)['multiple'] ?? false;
                if ($isMultiple && !is_array($value)) {
                    $value = [$value];
                } elseif (!$isMultiple && is_array($value)) {
                    $value = reset($value);
                }
                $allowedOptions = json_decode($field['options'], true);
                if (is_array($value)) {
                    $value = array_intersect($value, $allowedOptions);
                    $value = json_encode($value);
                } else {
                    $value = in_array($value, $allowedOptions) ? $value : null;
                }
                break;
            case 'radio':
                $allowedOptions = json_decode($field['options'], true);
                $value = in_array($value, $allowedOptions) ? $value : null;
                break;
        }


        if (isset($value)) {
            $insertDataStmt->execute([
                ':submission_id' => $submissionId,
                ':field_id' => $fieldId,
                ':valeur' => is_array($value) ? json_encode($value) : $value
            ]);
            $prompt .= "{$field['label']} : " . (is_array($value) ? json_encode($value) : $value) . "\n";
        }
    }

    $prompt .= "\nGénérez un contenu riche, pertinent et optimisé pour le SEO tout en restant engageant et authentique. ";
    $prompt .= "Après avoir généré le contenu principal, fournissez également :
    1. Une liste de 5 à 10 mots-clés pertinents, séparés par des virgules.
    2. Un résumé de 100 à 150 mots qui capture l'essence du contenu.

    Formatez votre réponse comme suit :
    
    # Contenu Principal
    [Insérez ici le contenu principal]

    # Mots-clés
    [Liste de mots-clés]

    # Résumé
    [Résumé du contenu]";

    // Mettre à jour le prompt spécifique dans la table submissions
    $updateStmt = $pdo->prepare("UPDATE submissions SET prompt_specifique = :prompt WHERE id = :id");
    $updateStmt->execute([
        ':prompt' => $prompt,
        ':id' => $submissionId
    ]);

    $pdo->commit();

    // Appel à l'API Gemini pour générer tout le contenu en une seule fois
    $apiKey = "AIzaSyAh_GVMvdz9jmwfmkTvIH1giywYDr3jelI";
    $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.0-pro-latest:generateContent?key=" . $apiKey;

    $data = [
        "contents" => [
            ["parts" => [["text" => $prompt]]]
        ]
    ];

    $options = [
        'http' => [
            'method' => 'POST',
            'header' => 'Content-Type: application/json',
            'content' => json_encode($data)
        ]
    ];

    $context = stream_context_create($options);
    $result = @file_get_contents($url, false, $context);

    if ($result === FALSE) {
        throw new Exception("Erreur lors de l'appel à l'API Gemini: " . error_get_last()['message']);
    }

    $response = json_decode($result, true);
    $finalContent = $response['candidates'][0]['content']['parts'][0]['text'];

    echo json_encode(['success' => true, 'message' => 'Contenu généré avec succès', 'content' => $finalContent]);

} catch(PDOException $e) {
    $pdo->rollBack();
    error_log('PDO Error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erreur de base de données: ' . $e->getMessage()]);
} catch(Exception $e) {
    if (isset($pdo)) {
        $pdo->rollBack();
    }
    error_log('Error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} catch(Throwable $e) {
    if (isset($pdo)) {
        $pdo->rollBack();
    }
    error_log('Critical Error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Une erreur inattendue s\'est produite. Veuillez réessayer plus tard.']);
}
?>
