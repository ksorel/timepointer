<?php
require_once __DIR__ . '/config/headers.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/utils/Response.php';

// Routeur simple
$request = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];

switch (true) {
    // Authentification
    case $method == 'POST' && preg_match('/\/api\/login$/', $request):
        require_once __DIR__ . '/controllers/AuthController.php';
        (new AuthController())->login();
        break;
        
    case $method == 'GET' && preg_match('/\/api\/profile$/', $request):
        require_once __DIR__ . '/controllers/AuthController.php';
        (new AuthController())->profile();
        break;
        
    // Pointage
    case $method == 'POST' && preg_match('/\/api\/pointage$/', $request):
        require_once __DIR__ . '/controllers/PointageController.php';
        (new PointageController())->enregistrerPointage();
        break;
        
    case $method == 'GET' && preg_match('/\/api\/pointage\/today$/', $request):
        require_once __DIR__ . '/controllers/PointageController.php';
        (new PointageController())->getPointageDuJour();
        break;
        
    case $method == 'GET' && preg_match('/\/api\/pointage\/history$/', $request):
        require_once __DIR__ . '/controllers/PointageController.php';
        (new PointageController())->getHistorique();
        break;
        
    // Paramètres
    case $method == 'GET' && preg_match('/\/api\/settings\/horaires$/', $request):
        require_once __DIR__ . '/controllers/SettingsController.php';
        (new SettingsController())->getHoraires();
        break;
        
    case $method == 'GET' && preg_match('/\/api\/settings\/horaire-du-jour$/', $request):
        require_once __DIR__ . '/controllers/SettingsController.php';
        (new SettingsController())->getHoraireDuJour();
        break;
        
    case $method == 'PUT' && preg_match('/\/api\/settings\/horaires$/', $request):
        require_once __DIR__ . '/controllers/SettingsController.php';
        (new SettingsController())->updateHoraires();
        break;
        
    default:
        Response::error('Endpoint non trouvé', 404);
        break;
}
?>