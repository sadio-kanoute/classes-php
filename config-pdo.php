<?php

/**
 * Configuration de la base de données avec PDO
 */

// Configuration de la base de données
define('DB_HOST', 'localhost');        
define('DB_NAME', 'classes');          
define('DB_USER', 'root');             
define('DB_PASS', '');                 
define('DB_CHARSET', 'utf8mb4');       

// DSN pour PDO
define('DB_DSN', 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET);

// Options PDO pour une meilleure sécurité et gestion d'erreurs
$pdo_options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,    
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,         
    PDO::ATTR_EMULATE_PREPARES   => false,                    
];

// Fonction pour obtenir la connexion PDO
function getPDOConnection() {
    global $pdo_options;
    
    try {
        $pdo = new PDO(DB_DSN, DB_USER, DB_PASS, $pdo_options);
        return $pdo;
    } catch (PDOException $e) {
        die('Erreur de connexion à la base de données : ' . $e->getMessage());
    }
}

?>