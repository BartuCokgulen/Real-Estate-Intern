<?php
require_once '../config.php';
require_once '../includes/functions.php';

// Require admin access
require_admin();

// Get filter parameters
$status = isset($_GET['status']) ? $_GET['status'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';

// Sayfalama parametreleri
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Sıralama parametreleri
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'created_at';
$order = isset($_GET['order']) ? $_GET['order'] : 'DESC';

try {
    // Build the query with filters
    $query = "SELECT pi.*, p.title as property_title, p.id as property_id,
                     u.username as owner_name, u.email as owner_email
              FROM property_inquiries pi 
              INNER JOIN properties p ON pi.property_id = p.id 
              INNER JOIN users u ON p.user_id = u.id 
              WHERE 1=1";
    
    $params = [];
    
    // Add status filter
    if (!empty($status)) {
        $query .= " AND pi.status = :status";
        $params[':status'] = $status;
    }
    
    // Add search filter
    if (!empty($search)) {
        $query .= " AND (pi.name LIKE :search OR pi.email LIKE :search OR pi.message LIKE :search 
                        OR p.title LIKE :search OR u.username LIKE :search)";
        $params[':search'] = "%$search%";
    }
    
    // Add date range filter
    if (!empty($start_date)) {
        $query .= " AND DATE(pi.created_at) >= :start_date";
        $params[':start_date'] = $start_date;
    }
    if (!empty($end_date)) {
        $query .= " AND DATE(pi.created_at) <= :end_date";
        $params[':end_date'] = $end_date;
    }
    
    $query .= " ORDER BY " . ($sort === 'property_title' ? 'p.title' : 'pi.' . $sort) . " $order";
    $query .= " LIMIT $offset, $per_page";
    
    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $inquiries = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Toplam kayıt sayısını al
    $count_query = "SELECT COUNT(*) FROM property_inquiries pi 
                    INNER JOIN properties p ON pi.property_id = p.id 
                    INNER JOIN users u ON p.user_id = u.id 
                    WHERE 1=1";
    
    if (!empty($status)) {
        $count_query .= " AND pi.status = :status";
    }
    if (!empty($search)) {
        $count_query .= " AND (pi.name LIKE :search OR pi.email LIKE :search OR pi.message LIKE :search 
                        OR p.title LIKE :search OR u.username LIKE :search)";
    }
    if (!empty($start_date)) {
        $count_query .= " AND DATE(pi.created_at) >= :start_date";
    }
    if (!empty($end_date)) {
        $count_query .= " AND DATE(pi.created_at) <= :end_date";
    }
    
    $stmt = $conn->prepare($count_query);
    if (!empty($status)) $stmt->bindValue(':status', $status);
    if (!empty($search)) $stmt->bindValue(':search', "%$search%");
    if (!empty($start_date)) $stmt->bindValue(':start_date', $start_date);
    if (!empty($end_date)) $stmt->bindValue(':end_date', $end_date);
    
    $stmt->execute();
    $total_records = $stmt->fetchColumn();
    $total_pages = ceil($total_records / $per_page);

    // İstatistikleri hesapla
    $stats_query = "SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'new' THEN 1 ELSE 0 END) as new_count,
        SUM(CASE WHEN status = 'read' THEN 1 ELSE 0 END) as read_count,
        SUM(CASE WHEN status = 'replied' THEN 1 ELSE 0 END) as replied_count,
        DATE(MIN(created_at)) as oldest_date,
        DATE(MAX(created_at)) as newest_date
    FROM property_inquiries";
    
    $stmt = $conn->query($stats_query);
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);

    // Son 7 günlük istatistikler
    $daily_stats_query = "SELECT 
        DATE(created_at) as date,
        COUNT(*) as total,
        SUM(CASE WHEN status = 'new' THEN 1 ELSE 0 END) as new_count,
        SUM(CASE WHEN status = 'read' THEN 1 ELSE 0 END) as read_count,
        SUM(CASE WHEN status = 'replied' THEN 1 ELSE 0 END) as replied_count
    FROM property_inquiries 
    WHERE created_at >= DATE_SUB(CURRENT_DATE, INTERVAL 7 DAY)
    GROUP BY DATE(created_at)
    ORDER BY date DESC";
    
    $stmt = $conn->query($daily_stats_query);
    $daily_stats = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // En çok sorgulanan mülkler
    $popular_properties_query = "SELECT 
        p.title, COUNT(*) as inquiry_count
    FROM property_inquiries pi
    INNER JOIN properties p ON pi.property_id = p.id
    GROUP BY p.id, p.title
    ORDER BY inquiry_count DESC
    LIMIT 5";
    
    $stmt = $conn->query($popular_properties_query);
    $popular_properties = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    error_log("Error in admin/inquiries.php: " . $e->getMessage());
    $inquiries = [];
    $_SESSION['error'] = "An error occurred while fetching inquiries.";
}

$page_title = 'Manage Inquiries';

// Page specific CSS
$extra_css = '
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
<style>
    .status-badge {
        min-width: 80px;
        text-align: center;
    }
    .property-link {
        color: #0d6efd;
        text-decoration: none;
    }
    .property-link:hover {
        text-decoration: underline;
    }
    .search-filters {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 0.25rem;
        padding: 1rem;
        margin-bottom: 1.5rem;
    }
    .search-filters .form-label {
        font-weight: 500;
    }
    .stats-card {
        transition: transform 0.2s;
    }
    .stats-card:hover {
        transform: translateY(-5px);
    }
    .chart-container {
        position: relative;
        height: 300px;
        margin-bottom: 2rem;
    }
    .stats-table th {
        font-weight: 500;
    }
</style>';

require_once 'header.php';
?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Property Inquiries</h1>
        <div class="btn-group">
            <button type="button" class="btn btn-outline-primary" id="markAllRead">
                <i class="bi bi-check-all"></i> Mark All as Read
            </button>
            <button type="button" class="btn btn-outline-danger" id="deleteRead">
                <i class="bi bi-trash"></i> Delete Read Messages
            </button>
        </div>
        <div class="dropdown d-inline-block">
            <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                <i class="bi bi-download"></i> Export
            </button>
            <ul class="dropdown-menu">
                <li>
                    <a class="dropdown-item" href="export/inquiries-csv.php<?php 
                        echo !empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : ''; 
                    ?>">
                        <i class="bi bi-filetype-csv"></i> Export as CSV
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="export/inquiries-excel.php<?php 
                        echo !empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : ''; 
                    ?>">
                        <i class="bi bi-filetype-xlsx"></i> Export as Excel
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="export/inquiries-pdf.php<?php 
                        echo !empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : ''; 
                    ?>">
                        <i class="bi bi-filetype-pdf"></i> Export as PDF
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <!-- Search Filters -->
    <div class="search-filters">
        <form method="GET" action="" class="row g-3">
            <div class="col-md-4">
                <label for="search" class="form-label">Search</label>
                <input type="text" class="form-control" id="search" name="search" 
                       placeholder="Search in name, email, message..." 
                       value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <div class="col-md-2">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="">All</option>
                    <option value="new" <?php echo $status === 'new' ? 'selected' : ''; ?>>New</option>
                    <option value="read" <?php echo $status === 'read' ? 'selected' : ''; ?>>Read</option>
                    <option value="replied" <?php echo $status === 'replied' ? 'selected' : ''; ?>>Replied</option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="start_date" class="form-label">Start Date</label>
                <input type="date" class="form-control" id="start_date" name="start_date" 
                       value="<?php echo htmlspecialchars($start_date); ?>">
            </div>
            <div class="col-md-2">
                <label for="end_date" class="form-label">End Date</label>
                <input type="date" class="form-control" id="end_date" name="end_date" 
                       value="<?php echo htmlspecialchars($end_date); ?>">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <div class="btn-group w-100">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search"></i> Search
                    </button>
                    <a href="inquiries.php" class="btn btn-secondary">
                        <i class="bi bi-x-circle"></i> Clear
                    </a>
                </div>
            </div>
        </form>
    </div>

    <div class="bulk-actions" style="display: none;">
        <div class="btn-group">
            <button type="button" class="btn btn-success bulk-mark-read">
                <i class="bi bi-check-all"></i> Mark Selected as Read
            </button>
            <button type="button" class="btn btn-danger bulk-delete">
                <i class="bi bi-trash"></i> Delete Selected
            </button>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white stats-card">
                <div class="card-body">
                    <h6 class="card-title">Total Inquiries</h6>
                    <h2 class="card-text"><?php echo number_format($stats['total']); ?></h2>
                    <small>Since <?php echo format_date($stats['oldest_date']); ?></small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white stats-card">
                <div class="card-body">
                    <h6 class="card-title">New Inquiries</h6>
                    <h2 class="card-text"><?php echo number_format($stats['new_count']); ?></h2>
                    <small><?php echo round(($stats['new_count'] / $stats['total']) * 100); ?>% of total</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white stats-card">
                <div class="card-body">
                    <h6 class="card-title">Read Inquiries</h6>
                    <h2 class="card-text"><?php echo number_format($stats['read_count']); ?></h2>
                    <small><?php echo round(($stats['read_count'] / $stats['total']) * 100); ?>% of total</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-secondary text-white stats-card">
                <div class="card-body">
                    <h6 class="card-title">Replied Inquiries</h6>
                    <h2 class="card-text"><?php echo number_format($stats['replied_count']); ?></h2>
                    <small><?php echo round(($stats['replied_count'] / $stats['total']) * 100); ?>% of total</small>
                </div>
            </div>
        </div>
    </div>

    <div class="alert alert-info mb-4">
        <i class="bi bi-info-circle"></i> 
        Showing inquiries from <?php echo format_date($stats['oldest_date']); ?> 
        to <?php echo format_date($stats['newest_date']); ?>
    </div>

    <?php if (empty($inquiries)): ?>
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i> No inquiries found.
        </div>
    <?php else: ?>
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th width="40">
                                    <div class="form-check">
                                        <input class="form-check-input select-all" type="checkbox" id="selectAll">
                                    </div>
                                </th>
                                <th>Status</th>
                                <th>Property</th>
                                <th>From</th>
                                <th>Owner</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($inquiries as $inquiry): ?>
                                <tr class="<?php echo $inquiry['status'] === 'new' ? 'table-warning' : ''; ?>">
                                    <td>
                                        <div class="form-check">
                                            <input class="form-check-input inquiry-checkbox" type="checkbox" value="<?php echo $inquiry['id']; ?>">
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge status-badge bg-<?php 
                                            echo match($inquiry['status']) {
                                                'new' => 'success',
                                                'read' => 'secondary',
                                                'replied' => 'primary',
                                                default => 'secondary'
                                            };
                                        ?>">
                                            <?php echo ucfirst($inquiry['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="../property-details.php?id=<?php echo $inquiry['property_id']; ?>" 
                                           class="property-link" target="_blank">
                                            <?php echo htmlspecialchars($inquiry['property_title']); ?>
                                        </a>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($inquiry['name']); ?>
                                        <br>
                                        <small class="text-muted">
                                            <?php echo htmlspecialchars($inquiry['email']); ?>
                                            <?php if (!empty($inquiry['phone'])): ?>
                                                <br><?php echo htmlspecialchars($inquiry['phone']); ?>
                                            <?php endif; ?>
                                        </small>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($inquiry['owner_name']); ?>
                                        <br>
                                        <small class="text-muted">
                                            <?php echo htmlspecialchars($inquiry['owner_email']); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <?php echo format_date($inquiry['created_at']); ?>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-info view-message" 
                                                data-bs-toggle="modal" data-bs-target="#messageModal"
                                                data-message="<?php echo htmlspecialchars($inquiry['message']); ?>"
                                                data-title="<?php echo htmlspecialchars($inquiry['property_title']); ?>"
                                                data-from="<?php echo htmlspecialchars($inquiry['name']); ?>"
                                                data-email="<?php echo htmlspecialchars($inquiry['email']); ?>"
                                                data-date="<?php echo format_date($inquiry['created_at']); ?>">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <a href="mailto:<?php echo htmlspecialchars($inquiry['email']); ?>" 
                                           class="btn btn-sm btn-primary">
                                            <i class="bi bi-reply"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-danger delete-inquiry" 
                                                data-id="<?php echo $inquiry['id']; ?>">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Message Modal -->
    <div class="modal fade" id="messageModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Inquiry Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <h6 class="property-title mb-3"></h6>
                    <div class="inquiry-meta mb-3">
                        <strong>From:</strong> <span class="inquiry-from"></span><br>
                        <strong>Email:</strong> <span class="inquiry-email"></span><br>
                        <strong>Date:</strong> <span class="inquiry-date"></span>
                    </div>
                    <div class="inquiry-message"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <a href="#" class="btn btn-primary reply-email">
                        <i class="bi bi-reply"></i> Reply via Email
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Sayfalama linklerini ekle -->
    <?php if ($total_pages > 1): ?>
        <nav aria-label="Page navigation" class="mt-4">
            <ul class="pagination justify-content-center">
                <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?<?php 
                            $_GET['page'] = $page - 1;
                            echo http_build_query($_GET);
                        ?>">Previous</a>
                    </li>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                        <a class="page-link" href="?<?php 
                            $_GET['page'] = $i;
                            echo http_build_query($_GET);
                        ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>
                
                <?php if ($page < $total_pages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?<?php 
                            $_GET['page'] = $page + 1;
                            echo http_build_query($_GET);
                        ?>">Next</a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    <?php endif; ?>

    <!-- Grafikler -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="dailyStats"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="statusPie"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detaylı İstatistikler -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">Daily Statistics</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="statsTable" class="table table-striped stats-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Total</th>
                            <th>New</th>
                            <th>Read</th>
                            <th>Replied</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($daily_stats as $day): ?>
                            <tr>
                                <td><?php echo format_date($day['date']); ?></td>
                                <td><?php echo $day['total']; ?></td>
                                <td>
                                    <span class="badge bg-success">
                                        <?php echo $day['new_count']; ?>
                                        (<?php echo $day['total'] > 0 ? round(($day['new_count'] / $day['total']) * 100) : 0; ?>%)
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-info">
                                        <?php echo $day['read_count']; ?>
                                        (<?php echo $day['total'] > 0 ? round(($day['read_count'] / $day['total']) * 100) : 0; ?>%)
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">
                                        <?php echo $day['replied_count']; ?>
                                        (<?php echo $day['total'] > 0 ? round(($day['replied_count'] / $day['total']) * 100) : 0; ?>%)
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- En Çok Sorgulanan Mülkler -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">Most Popular Properties</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Property</th>
                            <th>Inquiries</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($popular_properties as $property): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($property['title']); ?></td>
                                <td>
                                    <span class="badge bg-primary">
                                        <?php echo $property['inquiry_count']; ?> inquiries
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
// Prepare chart data
$chart_data = [
    'dates' => array_column(array_reverse($daily_stats), 'date'),
    'new' => array_column(array_reverse($daily_stats), 'new_count'),
    'read' => array_column(array_reverse($daily_stats), 'read_count'),
    'replied' => array_column(array_reverse($daily_stats), 'replied_count')
];
?>

<script>
// View Message Modal
document.querySelectorAll(".view-message").forEach(button => {
    button.addEventListener("click", function() {
        const modal = document.getElementById("messageModal");
        modal.querySelector(".property-title").textContent = this.dataset.title;
        modal.querySelector(".inquiry-from").textContent = this.dataset.from;
        modal.querySelector(".inquiry-email").textContent = this.dataset.email;
        modal.querySelector(".inquiry-date").textContent = this.dataset.date;
        modal.querySelector(".inquiry-message").textContent = this.dataset.message;
        modal.querySelector(".reply-email").href = "mailto:" + this.dataset.email;
    });
});

// Mark All as Read
document.getElementById("markAllRead").addEventListener("click", function() {
    if (confirm("Are you sure you want to mark all messages as read?")) {
        fetch("ajax/mark-inquiries-read.php", {
            method: "POST"
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert("Error: " + data.message);
            }
        })
        .catch(error => {
            console.error("Error:", error);
            alert("An error occurred. Please try again.");
        });
    }
});

// Delete Read Messages
document.getElementById("deleteRead").addEventListener("click", function() {
    if (confirm("Are you sure you want to delete all read messages? This action cannot be undone.")) {
        fetch("ajax/delete-read-inquiries.php", {
            method: "POST"
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert("Error: " + data.message);
            }
        })
        .catch(error => {
            console.error("Error:", error);
            alert("An error occurred. Please try again.");
        });
    }
});

// Delete Single Inquiry
document.querySelectorAll(".delete-inquiry").forEach(button => {
    button.addEventListener("click", function() {
        if (confirm("Are you sure you want to delete this inquiry? This action cannot be undone.")) {
            const inquiryId = this.dataset.id;
            fetch("ajax/delete-inquiry.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded",
                },
                body: `inquiry_id=${inquiryId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.closest("tr").remove();
                    if (document.querySelectorAll("tbody tr").length === 0) {
                        location.reload();
                    }
                } else {
                    alert("Error: " + data.message);
                }
            })
            .catch(error => {
                console.error("Error:", error);
                alert("An error occurred. Please try again.");
            });
        }
    });
});

// Date range validation
document.getElementById("end_date").addEventListener("change", function() {
    const startDate = document.getElementById("start_date").value;
    const endDate = this.value;
    
    if (startDate && endDate && startDate > endDate) {
        alert("End date cannot be earlier than start date");
        this.value = "";
    }
});

document.getElementById("start_date").addEventListener("change", function() {
    const startDate = this.value;
    const endDate = document.getElementById("end_date").value;
    
    if (startDate && endDate && startDate > endDate) {
        alert("Start date cannot be later than end date");
        this.value = "";
    }
});

// Bulk Actions
const bulkActions = document.querySelector('.bulk-actions');
const selectAll = document.getElementById('selectAll');
const inquiryCheckboxes = document.querySelectorAll('.inquiry-checkbox');

// Show/Hide bulk actions
function toggleBulkActions() {
    const checkedBoxes = document.querySelectorAll('.inquiry-checkbox:checked');
    bulkActions.style.display = checkedBoxes.length > 0 ? 'block' : 'none';
}

// Select all functionality
selectAll.addEventListener('change', function() {
    inquiryCheckboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
    toggleBulkActions();
});

// Individual checkbox change
inquiryCheckboxes.forEach(checkbox => {
    checkbox.addEventListener('change', function() {
        const allChecked = Array.from(inquiryCheckboxes).every(cb => cb.checked);
        selectAll.checked = allChecked;
        toggleBulkActions();
    });
});

// Bulk mark as read
document.querySelector('.bulk-mark-read').addEventListener('click', function() {
    const selectedIds = Array.from(document.querySelectorAll('.inquiry-checkbox:checked'))
        .map(cb => cb.value);
    
    if (selectedIds.length === 0) return;
    
    if (confirm('Are you sure you want to mark selected messages as read?')) {
        fetch('ajax/bulk-mark-read.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ ids: selectedIds })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
        });
    }
});

// Bulk delete
document.querySelector('.bulk-delete').addEventListener('click', function() {
    const selectedIds = Array.from(document.querySelectorAll('.inquiry-checkbox:checked'))
        .map(cb => cb.value);
    
    if (selectedIds.length === 0) return;
    
    if (confirm('Are you sure you want to delete selected messages? This action cannot be undone.')) {
        fetch('ajax/bulk-delete.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ ids: selectedIds })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
        });
    }
});

// Günlük istatistik grafiği
const ctx = document.getElementById("dailyStats").getContext("2d");
const chartData = <?php echo json_encode($chart_data); ?>;

new Chart(ctx, {
    type: "line",
    data: {
        labels: chartData.dates,
        datasets: [{
            label: "New",
            data: chartData.new,
            borderColor: "#198754",
            tension: 0.1
        }, {
            label: "Read",
            data: chartData.read,
            borderColor: "#0dcaf0",
            tension: 0.1
        }, {
            label: "Replied",
            data: chartData.replied,
            borderColor: "#6c757d",
            tension: 0.1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            title: {
                display: true,
                text: "Last 7 Days Statistics"
            }
        }
    }
});

// Durum dağılımı pasta grafiği
const pieCtx = document.getElementById("statusPie").getContext("2d");
const statsData = {
    new: <?php echo (int)$stats['new_count']; ?>,
    read: <?php echo (int)$stats['read_count']; ?>,
    replied: <?php echo (int)$stats['replied_count']; ?>
};

new Chart(pieCtx, {
    type: "pie",
    data: {
        labels: ["New", "Read", "Replied"],
        datasets: [{
            data: [statsData.new, statsData.read, statsData.replied],
            backgroundColor: ["#198754", "#0dcaf0", "#6c757d"]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            title: {
                display: true,
                text: "Inquiry Status Distribution"
            }
        }
    }
});

// DataTables başlat
$(document).ready(function() {
    $("#statsTable").DataTable({
        pageLength: 7,
        order: [[0, "desc"]],
        dom: "rtip"
    });
});
</script>

<?php
require_once 'footer.php';
?> 