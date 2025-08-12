<?php
function is_logged_in() {
    return isset($_SESSION['user_id']);
}
function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}
function generate_random_string($length = 10) {
    return bin2hex(random_bytes($length));
}
function format_price($price) {
    return '$' . number_format($price, 2);
}
function format_date($date, $format = 'M d, Y') {
    return date($format, strtotime($date));
}
function get_status_badge_class($status) {
    switch ($status) {
        case 'active':
            return 'success';
        case 'pending':
            return 'warning';
        case 'sold':
            return 'info';
        default:
            return 'secondary';
    }
}
function get_property_type_icon($type) {
    switch ($type) {
        case 'house':
            return 'bi-house-door';
        case 'apartment':
            return 'bi-building';
        case 'condo':
            return 'bi-building-add';
        case 'townhouse':
            return 'bi-house';
        default:
            return 'bi-house';
    }
}
function validate_image_upload($file, $max_size = 5242880) {
    $errors = [];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = "Error uploading file.";
        return $errors;
    }
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    if (!in_array($file['type'], $allowed_types)) {
        $errors[] = "Invalid file type. Only JPG, PNG, and GIF are allowed.";
    }
    if ($file['size'] > $max_size) {
        $errors[] = "File size must be less than " . ($max_size / 1024 / 1024) . "MB.";
    }
    return $errors;
}
function upload_image($file, $target_dir) {
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $file_name = uniqid() . '.' . $file_extension;
    $target_path = $target_dir . $file_name;
    if (move_uploaded_file($file['tmp_name'], $target_path)) {
        return $file_name;
    }
    return false;
}
function delete_image($file_path) {
    if (file_exists($file_path)) {
        return unlink($file_path);
    }
    return false;
}
function get_pagination_data($total_items, $items_per_page, $current_page) {
    $total_pages = ceil($total_items / $items_per_page);
    $offset = ($current_page - 1) * $items_per_page;
    return [
        'total_pages' => $total_pages,
        'offset' => $offset,
        'current_page' => $current_page,
        'items_per_page' => $items_per_page
    ];
}
function generate_pagination_links($current_page, $total_pages, $url) {
    $links = [];
    if ($current_page > 1) {
        $links[] = [
            'url' => $url . '?page=' . ($current_page - 1),
            'text' => 'Previous',
            'class' => ''
        ];
    }
    for ($i = 1; $i <= $total_pages; $i++) {
        $links[] = [
            'url' => $url . '?page=' . $i,
            'text' => $i,
            'class' => $i === $current_page ? 'active' : ''
        ];
    }
    if ($current_page < $total_pages) {
        $links[] = [
            'url' => $url . '?page=' . ($current_page + 1),
            'text' => 'Next',
            'class' => ''
        ];
    }
    return $links;
}
function init_session() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}
function is_admin() {
    init_session();
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin' && isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
}
function require_login() {
    init_session();
    if (!is_logged_in()) {
        $_SESSION['error'] = "Please log in to access this page.";
        header('Location: login.php');
        exit;
    }
}
function require_admin() {
    require_login();
    if (!is_admin()) {
        $_SESSION['error'] = "You don't have permission to access this page.";
        header('Location: index.php');
        exit;
    }
}
function get_user_name() {
    return isset($_SESSION['username']) ? $_SESSION['username'] : 'Guest';
}
function count_new_messages($conn) {
    try {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM contact_messages WHERE status = 'new'");
        $stmt->execute();
        return $stmt->fetchColumn();
    } catch(PDOException $e) {
        return 0;
    }
}
function count_new_inquiries($conn) {
    try {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM property_inquiries WHERE status = 'new'");
        $stmt->execute();
        return $stmt->fetchColumn();
    } catch(PDOException $e) {
        return 0;
    }
}
function count_pending_properties($conn) {
    try {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM properties WHERE status = 'pending'");
        $stmt->execute();
        return $stmt->fetchColumn();
    } catch(PDOException $e) {
        return 0;
    }
}
function clean_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
function show_success($message) {
    $_SESSION['success'] = $message;
}
function show_error($message) {
    $_SESSION['error'] = $message;
}
function get_property_status_badge($status) {
    $badge_class = match($status) {
        'pending' => 'bg-warning',
        'approved' => 'bg-success',
        'rejected' => 'bg-danger',
        default => 'bg-secondary'
    };
    return sprintf('<span class="badge %s">%s</span>', $badge_class, ucfirst($status));
}
function get_property_type_badge($type) {
    $colors = [
        'house' => 'primary',
        'apartment' => 'success',
        'villa' => 'info',
        'land' => 'warning'
    ];
    $color = isset($colors[$type]) ? $colors[$type] : 'secondary';
    return '<span class="badge bg-' . $color . '">' . ucfirst($type) . '</span>';
}
function is_property_favorited($conn, $user_id, $property_id) {
    try {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM favorites WHERE user_id = ? AND property_id = ?");
        $stmt->execute([$user_id, $property_id]);
        return $stmt->fetchColumn() > 0;
    } catch(PDOException $e) {
        return false;
    }
}
function toggle_favorite($conn, $user_id, $property_id) {
    try {
        if (is_property_favorited($conn, $user_id, $property_id)) {
            $stmt = $conn->prepare("DELETE FROM favorites WHERE user_id = ? AND property_id = ?");
            $stmt->execute([$user_id, $property_id]);
            return false;
        } else {
            $stmt = $conn->prepare("INSERT INTO favorites (user_id, property_id) VALUES (?, ?)");
            $stmt->execute([$user_id, $property_id]);
            return true;
        }
    } catch(PDOException $e) {
        return null;
    }
}
function get_property_image($property_id) {
    global $conn;
    try {
        $query = "SELECT image_url FROM property_images WHERE property_id = ? AND is_primary = 1 LIMIT 1";
        $stmt = $conn->prepare($query);
        $stmt->execute([$property_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['image_url'] : 'assets/img/property-placeholder.jpg';
    } catch(PDOException $e) {
        return 'assets/img/property-placeholder.jpg';
    }
}
function get_user_avatar($user_id) {
    global $conn;
    try {
        $query = "SELECT avatar FROM users WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->execute([$user_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result && $result['avatar'] ? $result['avatar'] : 'assets/img/user-placeholder.jpg';
    } catch(PDOException $e) {
        return 'assets/img/user-placeholder.jpg';
    }
}
function get_status_color($status) {
    switch ($status) {
        case 'active':
            return 'success';
        case 'pending':
            return 'warning';
        case 'sold':
            return 'info';
        case 'inactive':
            return 'secondary';
        default:
            return 'light';
    }
}
function get_message_status_color($status) {
    switch ($status) {
        case 'new':
            return 'danger';
        case 'read':
            return 'info';
        case 'replied':
            return 'success';
        default:
            return 'secondary';
    }
} 