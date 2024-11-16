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

    // Get POST data
    $fileName = $_POST['fileName'] ?? '';
    $htmlCode = $_POST['formCode'] ?? '';

    // Verify data
    if (empty($fileName) || empty($htmlCode)) {
        throw new Exception("Le nom du fichier et le contenu HTML sont requis.");
    }

    // Check if the file name already exists
    $stmt = $pdo->prepare("SELECT id, COUNT(*) as count FROM template WHERE nom_fichier = :fileName");
    $stmt->execute([':fileName' => $fileName]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $count = $result['count'];
    $templateId = $result['id'];

    if ($count > 0) {
        // File exists, update it
        $stmt = $pdo->prepare("UPDATE template SET contenu_html = :formCode WHERE nom_fichier = :fileName");
        $message = "Template mis à jour avec succès";
    } else {
        // New file, insert it
        $stmt = $pdo->prepare("INSERT INTO template (nom_fichier, contenu_html) VALUES (:fileName, :formCode)");
        $message = "Nouveau template créé avec succès";
    }

    // Execute the query
    $stmt->execute([
        ':fileName' => $fileName,
        ':formCode' => $htmlCode
    ]);

    if (!$templateId) {
        $templateId = $pdo->lastInsertId();
    }

    // Clear existing fields for this template
    $stmt = $pdo->prepare("DELETE FROM template_fields WHERE template_id = :templateId");
    $stmt->execute([':templateId' => $templateId]);

    // Process HTML
    $dom = new DOMDocument();
    libxml_use_internal_errors(true);
    $dom->loadHTML(mb_convert_encoding($htmlCode, 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_clear_errors();

    $xpath = new DOMXPath($dom);
    $elements = $xpath->query("//input | //select | //textarea");

    $optionsMap = [];

    foreach ($elements as $element) {
        $type = strtolower($element->tagName);
        $name = $element->getAttribute("name") ?: null;
        $id = $element->getAttribute("id") ?: null;
        $required = $element->hasAttribute("required") ? 1 : 0;
        $class = $element->getAttribute("class") ?: null;

        $label = null;
        $labelElement = $xpath->query("//label[@for='" . $id . "']")->item(0);
        if ($labelElement) {
            $label = $labelElement->textContent;
        }

        $val_min = $val_max = $date_min = $date_max = $options = $condition = $limit_taille = $format_fichier = $valeur_par_defaut = null;
        $attributs_supplementaires = null;

        switch ($type) {
            case 'input':
                $inputType = $element->getAttribute("type");
                switch ($inputType) {
                    case 'number':
                        $val_min = $element->getAttribute("min") ?: null;
                        $val_max = $element->getAttribute("max") ?: null;
                        $valeur_par_defaut = $element->getAttribute("value") ?: null;
                        break;
                    case 'date':
                        $date_min = $element->getAttribute("min") ?: null;
                        $date_max = $element->getAttribute("max") ?: null;
                        $valeur_par_defaut = $element->getAttribute("value") ?: null;
                        break;
                    case 'file':
                        $format_fichier = $element->getAttribute("accept") ?: null;
                        break;
                    case 'checkbox':
                        $value = $element->getAttribute("value");
                        $checked = $element->hasAttribute("checked");
                        
                        if (!isset($optionsMap[$name])) {
                            $optionsMap[$name] = ['type' => 'checkbox', 'options' => [], 'default' => []];
                        }
                        $optionsMap[$name]['options'][] = $value . ':' . ($label ?: $value);
                        if ($checked) {
                            $optionsMap[$name]['default'][] = $value;
                        }
                        continue 3; // Skip to the next element
                    case 'radio':
                        $value = $element->getAttribute("value");
                        $checked = $element->hasAttribute("checked");
                        
                        if (!isset($optionsMap[$name])) {
                            $optionsMap[$name] = ['type' => 'radio', 'options' => [], 'default' => null];
                        }
                        $optionsMap[$name]['options'][] = $value . ':' . ($label ?: $value);
                        if ($checked) {
                            $optionsMap[$name]['default'] = $value;
                        }
                        continue 3; // Skip to the next element
                    default:
                        $valeur_par_defaut = $element->getAttribute("value") ?: null;
                }
                break;
            case 'select':
                $optionElements = $element->getElementsByTagName("option");
                $optionsArray = [];
                $defaultValue = null;
                foreach ($optionElements as $option) {
                    $optionsArray[] = $option->getAttribute("value") . ":" . $option->textContent;
                    if ($option->hasAttribute("selected")) {
                        $defaultValue = $option->getAttribute("value");
                    }
                }
                $options = implode(";", $optionsArray);
                $valeur_par_defaut = $defaultValue;
                $attributs_supplementaires = $element->hasAttribute("multiple") ? json_encode(['multiple' => true]) : null;
                break;
            case 'textarea':
                $limit_taille = $element->getAttribute("maxlength") ?: null;
                $valeur_par_defaut = $element->textContent ?: null;
                break;
        }

        if (!in_array($type, ['select']) && !in_array($inputType, ['checkbox', 'radio'])) {
            insertField($pdo, $templateId, $type, $name, $label, $required, $id, $class,
                        $val_min, $val_max, $date_min, $date_max, $options, $condition,
                        $limit_taille, $format_fichier, $valeur_par_defaut, $attributs_supplementaires);
        }
    }

    // Process collected options for checkbox, radio, and select
    foreach ($optionsMap as $fieldName => $fieldData) {
        $type = $fieldData['type'];
        $options = implode(';', $fieldData['options']);
        $valeur_par_defaut = is_array($fieldData['default']) ? implode(',', $fieldData['default']) : $fieldData['default'];

        insertField($pdo, $templateId, $type, $fieldName, ucfirst(str_replace('_', ' ', $fieldName)), $required, $id, $class,
                    $val_min, $val_max, $date_min, $date_max, $options, $condition,
                    $limit_taille, $format_fichier, $valeur_par_defaut, $attributs_supplementaires);
    }

    // Success response
    echo json_encode(['success' => true, 'message' => $message, 'isAlert' => true]);

} catch(PDOException $e) {
    // Database error
    echo json_encode(['success' => false, 'message' => 'Erreur de base de données : ' . $e->getMessage(), 'isAlert' => true]);
} catch(Exception $e) {
    // Other errors
    echo json_encode(['success' => false, 'message' => $e->getMessage(), 'isAlert' => true]);
}

function insertField($pdo, $templateId, $type, $name, $label, $required, $id, $class,
                     $val_min, $val_max, $date_min, $date_max, $options, $condition,
                     $limit_taille, $format_fichier, $valeur_par_defaut, $attributs_supplementaires) {
    $stmt = $pdo->prepare("INSERT INTO template_fields (template_id, type, nom, label, required, identifiant, class_css, val_min, val_max, date_min, date_max, options, `condition`, limit_taille, format_fichier, valeur_par_defaut, attributs_supplementaires) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $stmt->execute([
        $templateId, $type, $name, $label, $required, $id, $class,
        $val_min, $val_max, $date_min, $date_max, $options, $condition,
        $limit_taille, $format_fichier, $valeur_par_defaut, $attributs_supplementaires
    ]);
}