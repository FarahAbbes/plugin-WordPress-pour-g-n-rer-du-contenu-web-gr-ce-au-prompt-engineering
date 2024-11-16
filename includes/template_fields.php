<?php
// Connexion à la base de données (à adapter selon votre configuration)
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "template_db";

try {
    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Activer le mode d'exception pour mysqli
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    // Récupérer tous les templates de la table template
    $sql = "SELECT id, contenu_html FROM template";
    $result = $conn->query($sql);

    if ($result->num_rows === 0) {
        throw new Exception("Aucun contenu trouvé dans la table template.");
    }

    // Préparer les insertions dans template_fields
    $insertSql = "INSERT INTO template_fields (template_id, type, nom, label, required, identifiant, class_css, val_min, val_max, date_min, date_max, options, `condition`, limit_taille, format_fichier, valeur_par_defaut, button_value, attributs_supplementaires) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $insertStmt = $conn->prepare($insertSql);

    $totalInsertCount = 0;

    // Parcourir tous les templates
    while ($template = $result->fetch_assoc()) {
        $templateId = $template["id"];
        $htmlContent = $template["contenu_html"];

        $fields = parseTemplate($htmlContent, $templateId);

        foreach ($fields as $field) {
            insertField($insertStmt, $field);
            $totalInsertCount++;
        }
    }

    echo "Opération terminée. $totalInsertCount éléments insérés dans la table template_fields.";

} catch (Exception $e) {
    echo "Une erreur est survenue : " . $e->getMessage();
} finally {
    // Fermer les connexions et les statements
    if (isset($insertStmt)) $insertStmt->close();
    if (isset($conn)) $conn->close();
}

function insertField($stmt, $field) {
    $stmt->bind_param("isssissssssssisssss",
        $field['template_id'],
        $field['type'],
        $field['nom'],
        $field['label'],
        $field['required'],
        $field['identifiant'],
        $field['class_css'],
        $field['val_min'],
        $field['val_max'],
        $field['date_min'],
        $field['date_max'],
        $field['options'],
        $field['condition'],
        $field['limit_taille'],
        $field['format_fichier'],
        $field['valeur_par_defaut'],
        $field['button_value'],
        $field['attributs_supplementaires']
    );

    if (!$stmt->execute()) {
        error_log("Erreur lors de l'insertion : " . $stmt->error);
    }
}

function parseTemplate($templateContent, $templateId) {
    $dom = new DOMDocument();
    libxml_use_internal_errors(true);
    $dom->loadHTML(mb_convert_encoding($templateContent, 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_clear_errors();

    $xpath = new DOMXPath($dom);
    $formElements = $xpath->query("//input | //textarea | //select | //button");

    $fields = [];
    foreach ($formElements as $element) {
        $field = [
            'template_id' => $templateId,
            'type' => $element->nodeName,
            'nom' => $element->getAttribute('name'),
            'label' => getElementLabel($element, $xpath),
            'required' => $element->hasAttribute('required') ? 1 : 0,
            'identifiant' => $element->getAttribute('id'),
            'class_css' => $element->getAttribute('class'),
            'valeur_par_defaut' => $element->getAttribute('value'),
            'attributs_supplementaires' => json_encode(getElementAttributes($element)),
            'val_min' => null,
            'val_max' => null,
            'date_min' => null,
            'date_max' => null,
            'options' => null,
            'condition' => null,
            'limit_taille' => null,
            'format_fichier' => null,
            'button_value' => null
        ];

        // Traitement spécifique selon le type d'élément
        switch ($element->nodeName) {
            case 'input':
                $inputType = $element->getAttribute('type');
                $field['type'] = $inputType;
                switch ($inputType) {
                    case 'number':
                    case 'range':
                        $field['val_min'] = $element->getAttribute('min');
                        $field['val_max'] = $element->getAttribute('max');
                        break;
                    case 'date':
                        $field['date_min'] = $element->getAttribute('min');
                        $field['date_max'] = $element->getAttribute('max');
                        break;
                    case 'file':
                        $field['format_fichier'] = $element->getAttribute('accept');
                        break;
                        case 'checkbox':
                            if ($element->parentNode->getAttribute('class') === 'checkbox-wrapper') {
                                $field['type'] = 'checkbox_button';
                            } else {
                                $field['type'] = 'checkbox';
                            }
                            $field['button_value'] = $element->getAttribute('value');
                            break;
                }
                break;
            case 'select':
                $options = [];
                foreach ($element->getElementsByTagName('option') as $option) {
                    $options[] = $option->getAttribute('value') . ':' . $option->nodeValue;
                }
                $field['options'] = implode(';', $options);
                break;
            case 'textarea':
                $field['limit_taille'] = $element->getAttribute('maxlength');
                $field['valeur_par_defaut'] = $element->nodeValue;
                break;
            case 'button':
                $field['button_value'] = $element->textContent;
                break;
        }

        $fields[] = $field;
    }

    return $fields;
}

function getElementLabel($element, $xpath) {
    $id = $element->getAttribute('id');
    if ($id) {
        $label = $xpath->query("//label[@for='$id']")->item(0);
        if ($label) {
            return $label->nodeValue;
        }
    }
    return '';
}

function getElementAttributes($element) {
    $attrs = [];
    foreach ($element->attributes as $attr) {
        if (!in_array($attr->name, ['name', 'id', 'class', 'required', 'type', 'min', 'max', 'value', 'accept', 'maxlength'])) {
            $attrs[$attr->name] = $attr->value;
        }
    }
    return $attrs;
}


?>