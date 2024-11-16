<?php
// promptgenere.php

// Inclure la connexion à la base de données
include 'db_connection.php';

// Récupérer l'ID de la soumission
$submission_id = $_GET['submission_id'];

// Récupérer le contenu généré depuis la base de données
$query = "SELECT prompt_specifique FROM submissions WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $submission_id);
$stmt->execute();
$stmt->bind_result($content);
$stmt->fetch();
$stmt->close();

// Générer le résumé et les mots-clés en appelant l'API OpenAI ou autre service
function generateSummaryAndKeywords($content) {
    $apiKey = 'YOUR_OPENAI_API_KEY';
    $url = 'https://api.openai.com/v1/engines/text-davinci-003/completions';
    
    $postData = array(
        'prompt' => "Générez un résumé et des mots-clés pour le contenu suivant : $content",
        'max_tokens' => 300
    );
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey
    ));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    // Déboguer la réponse
    echo '<pre>';
    print_r($response);
    echo '</pre>';
    
    $responseData = json_decode($response, true);
    $text = $responseData['choices'][0]['text'];
    
    // Suppose que le résumé et les mots-clés sont séparés par des sauts de ligne
    $parts = explode("\n", $text);
    $summary = isset($parts[0]) ? trim($parts[0]) : '';
    $keywords = isset($parts[1]) ? trim($parts[1]) : '';
    
    return array($summary, $keywords);
}

// Générer le résumé et les mots-clés
list($summary, $keywords) = generateSummaryAndKeywords($content);

// Mettre à jour la base de données avec le résumé et les mots-clés
$query = "UPDATE submissions SET resume = ?, mots_cles = ? WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ssi", $summary, $keywords, $submission_id);
$stmt->execute();
$stmt->close();

// Afficher les résultats
header("Location: show_results.php?submission_id=" . $submission_id);
exit();
?>
