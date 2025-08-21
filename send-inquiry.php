<?php
require_once 'config.php';
require_once 'includes/functions.php';

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: properties.php');
    exit();
}

// Get form data
$property_id = isset($_POST['property_id']) ? (int)$_POST['property_id'] : 0;
$name = isset($_POST['name']) ? clean_input($_POST['name']) : '';
$email = isset($_POST['email']) ? clean_input($_POST['email']) : '';
$phone = isset($_POST['phone']) ? clean_input($_POST['phone']) : '';
$message = isset($_POST['message']) ? clean_input($_POST['message']) : '';

// Validate inputs
if (empty($property_id) || empty($name) || empty($email) || empty($message)) {
    $_SESSION['error'] = "Please fill in all required fields.";
    header("Location: property-details.php?id=$property_id");
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error'] = "Please enter a valid email address.";
    header("Location: property-details.php?id=$property_id");
    exit();
}

try {
    // First, check if the property exists and get owner info
    $property_query = "SELECT p.*, u.email as owner_email 
                      FROM properties p 
                      LEFT JOIN users u ON p.user_id = u.id 
                      WHERE p.id = ? AND p.status = 'approved'";
    $stmt = $conn->prepare($property_query);
    $stmt->execute([$property_id]);
    $property = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$property) {
        $_SESSION['error'] = "Property not found.";
        header('Location: properties.php');
        exit();
    }

    // Insert the inquiry into database
    $query = "INSERT INTO property_inquiries (property_id, name, email, phone, message, status, created_at) 
              VALUES (?, ?, ?, ?, ?, 'new', NOW())";
    $stmt = $conn->prepare($query);
    $stmt->execute([$property_id, $name, $email, $phone, $message]);

    // Send email notification to property owner (in a production environment)
    // Note: This is just a placeholder. You should implement proper email sending in production
    /*
    $to = $property['owner_email'];
    $subject = "New Inquiry for " . $property['title'];
    $email_message = "You have received a new inquiry for your property:\n\n";
    $email_message .= "From: $name\n";
    $email_message .= "Email: $email\n";
    $email_message .= "Phone: $phone\n\n";
    $email_message .= "Message:\n$message";
    
    mail($to, $subject, $email_message);
    */

    $_SESSION['success'] = "Your inquiry has been sent successfully. The property owner will contact you soon.";
    header("Location: property-details.php?id=$property_id");
    exit();

} catch(PDOException $e) {
    error_log("Error in send-inquiry.php: " . $e->getMessage());
    $_SESSION['error'] = "Sorry, there was an error sending your inquiry. Please try again later.";
    header("Location: property-details.php?id=$property_id");
    exit();
}
?> 