<?php

// api/check_session.php

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");

// INSERCIÓN DE POLÍTICA CORS SEGURA (Solo Dominio de Producción)
// ----------------------------------------------------------------------
$production_origin = 'https://citasalud.infinityfreeapp.com'; 

$origin = $production_origin;
if (isset($_SERVER['HTTP_ORIGIN']) && $_SERVER['HTTP_ORIGIN'] === $production_origin) {
    $origin = $production_origin;
} else {
    $origin = $production_origin; 
}

header("Access-Control-Allow-Origin: " . $origin); 
// ----------------------------------------------------------------------

// REEMPLAZO: Usando 'use' para los namespaces (asumiendo autoloader configurado)
use Config\Database; 
use Models\Paciente; 


session_start();

$response = ["is_valid" => false];

if (isset($_SESSION['paciente_documento'])) {
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        $paciente_model = new Paciente($db);
        $paciente = $paciente_model->findByDocumento($_SESSION['paciente_documento']);
        
        // Verifica si el ID de sesión del navegador coincide con el ID activo en la DB.
        if ($paciente && $paciente->session_id_activa === session_id()) {
            $response["is_valid"] = true;
        } else {
            // Si el ID es diferente, limpiar la sesión de PHP (por si acaso).
            session_unset();
            session_destroy();
        }
    }
}

http_response_code(200);
echo json_encode($response);