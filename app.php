<?php
use Dotenv\Dotenv;

// Load environment variables first
require_once __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST)) {
    $connection = mysqli_connect($_ENV["DBHOST"], $_ENV["DBUSER"], $_ENV["DBPASS"], $_ENV["DBNAME"]);
    
    if (!$connection) {
        echo("Database connection failed: " . mysqli_connect_error());
        exit;
    }
    
    // Handle Edit Request
    if (isset($_POST["edit_participant"]) && isset($_POST["participant_id"], $_POST["name"], $_POST["email"], $_POST["role"])) {
        echo("Edit request received");
        
        $participant_id = intval($_POST["participant_id"]);
        $stmt = $connection->prepare("UPDATE participents SET name = ?, email = ?, role = ? WHERE id = ?");
        $stmt->bind_param("sssi", $_POST["name"], $_POST["email"], $_POST["role"], $participant_id);
        
        if ($stmt->execute()) {
            echo("Participant updated successfully");
        } else {
            echo("Error updating participant");
        }
        
        $stmt->close();
    }
    // Handle Add Request
    elseif (isset($_POST["name"], $_POST["email"], $_POST["role"])) {
        echo("POST request received");
        echo("POST data received");
        
        // Prepared statements automatically handle escaping
        $stmt = $connection->prepare("INSERT INTO participents (name, email, role) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $_POST["name"], $_POST["email"], $_POST["role"]);
        
        if ($stmt->execute()) {
            echo("Database connected successfully");
        } else {
            echo("Error adding participant");
        }
        
        $stmt->close();
    }
    
    mysqli_close($connection);
}


