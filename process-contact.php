<?php
session_start();
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    // Debug log
    error_log("Contact form submission - Name: $name, Email: $email, Subject: $subject");
    
    // Validate inputs
    $errors = [];
    
    if (empty($name)) {
        $errors[] = "Name is required";
    }
    
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    if (empty($subject)) {
        $errors[] = "Subject is required";
    }
    
    if (empty($message)) {
        $errors[] = "Message is required";
    }
    
    if (empty($errors)) {
        try {
            $stmt = $conn->prepare("
                INSERT INTO contact_messages (name, email, subject, message)
                VALUES (?, ?, ?, ?)
            ");
            
            $result = $stmt->execute([$name, $email, $subject, $message]);
            
            if ($result) {
                error_log("Contact message saved successfully for $email");
                $_SESSION['success'] = "Your message has been sent successfully. We'll get back to you soon!";
            } else {
                error_log("Failed to save contact message for $email");
                $_SESSION['error'] = "Sorry, there was an error sending your message. Please try again later.";
            }
            
        } catch (PDOException $e) {
            error_log("Database error in contact form: " . $e->getMessage());
            $_SESSION['error'] = "Sorry, there was an error sending your message. Please try again later.";
        }
    } else {
        error_log("Contact form validation errors: " . implode(", ", $errors));
        $_SESSION['error'] = implode("<br>", $errors);
    }
    
    header('Location: contact.php');
    exit;
}

header('Location: contact.php');
exit; 