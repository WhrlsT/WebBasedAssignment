<?php
require '../_base.php';
require_once '../lib/SimplePager.php';

// Check if user is admin
if (!$loggedIn || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'Admin') {
    header('Location: ../login.php');
    exit;
}

// Define user types at the beginning of the file
$userTypes = ['Member', 'Admin'];
$userStatuses = [1 => 'Active', 0 => 'Inactive']; // Define statuses

// Handle actions
$action = $_GET['action'] ?? '';
$userId = $_GET['id'] ?? 0;

switch ($action) {
    // In the add case, modify the POST handling:
    case 'add':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'] ?? '';
            $email = $_POST['email'] ?? '';
            $firstName = $_POST['first_name'] ?? '';
            $lastName = $_POST['last_name'] ?? '';
            $password = $_POST['password'] ?? '';
            $userType = $_POST['user_type'] ?? 'Member';

            // Check if email already exists
            $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE Email = ?");
            $checkStmt->execute([$email]);
            if ($checkStmt->fetchColumn() > 0) {
                echo json_encode(['error' => 'Email address already in use']);
                exit;
            }

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            try {
                $stmt = $pdo->prepare("INSERT INTO users (username, Email, FirstName, LastName, Password, UserType) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$username, $email, $firstName, $lastName, $hashedPassword, $userType]);
                $_SESSION['message'] = 'User added successfully';
                echo json_encode(['success' => true]);
                exit;
            } catch (PDOException $e) {
                echo json_encode(['error' => 'Error adding user: ' . $e->getMessage()]);
                exit;
            }
        }

        // Modify the add form to include first and last name fields
        ob_start();
        ?>
        <h2>Add New User</h2>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <form method="post" action="users.php?action=add">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username'] ?? ''); ?>" required>
            </div>

            <div class="form-group">
                <label for="first_name">First Name</label>
                <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($user['FirstName'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="last_name">Last Name</label>
                <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($user['LastName'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['Email'] ?? ''); ?>" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>

            <div class="form-group">
                <label for="user_type">User Type</label>
                <select id="user_type" name="user_type" required>
                    <?php foreach ($userTypes as $type): ?>
                        <option value="<?php echo htmlspecialchars($type); ?>">
                            <?php echo htmlspecialchars($type); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-actions">
                <button type="submit" class="admin-btn">Save</button>
                <button type="button" class="admin-btn secondary cancel-modal">Cancel</button>
            </div>
        </form>
        <?php
        echo ob_get_clean();
        exit;

    case 'edit':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'] ?? '';
            $email = $_POST['email'] ?? '';
            $firstName = $_POST['first_name'] ?? '';
            $lastName = $_POST['last_name'] ?? '';
            $userType = $_POST['user_type'] ?? 'Member';
            $password = $_POST['password'] ?? '';

            // Check if email already exists for another user
            $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE Email = ? AND UserID != ?");
            $checkStmt->execute([$email, $userId]);
            if ($checkStmt->fetchColumn() > 0) {
                echo json_encode(['error' => 'Email address already in use by another user']);
                exit;
            }

            try {
                if (!empty($password)) {
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE users SET username = ?, Email = ?, FirstName = ?, LastName = ?, UserType = ?, Password = ? WHERE UserID = ?");
                    $stmt->execute([$username, $email, $firstName, $lastName, $userType, $hashedPassword, $userId]);
                } else {
                    $stmt = $pdo->prepare("UPDATE users SET username = ?, Email = ?, FirstName = ?, LastName = ?, UserType = ? WHERE UserID = ?");
                    $stmt->execute([$username, $email, $firstName, $lastName, $userType, $userId]);
                }
                $_SESSION['message'] = 'User updated successfully';
                echo json_encode(['success' => true]);
                exit;
            } catch (PDOException $e) {
                echo json_encode(['error' => 'Error updating user: ' . $e->getMessage()]);
                exit;
            }
        }

        // Modify the edit form to include first and last name fields
        $user = [];
        if ($userId) {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE UserID = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
        }

        ob_start();
        ?>
        <h2>Edit User</h2>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        <form method="post" action="users.php?action=edit&id=<?php echo $userId; ?>">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username'] ?? ''); ?>" required>
            </div>

            <div class="form-group">
                <label for="first_name">First Name</label>
                <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($user['FirstName'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="last_name">Last Name</label>
                <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($user['LastName'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['Email'] ?? ''); ?>" required>
            </div>

            <div class="form-group">
                <label for="password">New Password (leave blank to keep current)</label>
                <input type="password" id="password" name="password">
            </div>

            <div class="form-group">
                <label for="user_type">User Type</label>
                <select id="user_type" name="user_type" required>
                    <?php foreach ($userTypes as $type): ?>
                        <option value="<?php echo htmlspecialchars($type); ?>" <?php echo ($user['UserType'] ?? '') === $type ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($type); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-actions">
                <button type="submit" class="admin-btn">Save</button>
                <button type="button" class="admin-btn secondary cancel-modal">Cancel</button>
            </div>
        </form>
        <?php
        echo ob_get_clean();
        exit;

    case 'deactivate': // Changed from 'delete'
        if ($userId && $userId != ($_SESSION['user_id'] ?? 0)) { // Prevent self-deactivation
            try {
                $stmt = $pdo->prepare("UPDATE users SET is_active = 0 WHERE UserID = ?");
                $stmt->execute([$userId]);
                $_SESSION['message'] = 'User deactivated successfully';
            } catch (PDOException $e) {
                $_SESSION['error'] = 'Error deactivating user: ' . $e->getMessage();
            }
        } else {
            $_SESSION['error'] = 'Invalid request or cannot deactivate self.';
        }
        header('Location: users.php'); // Redirect back to user list
        exit;

    case 'activate':
        if ($userId) {
            try {
                $stmt = $pdo->prepare("UPDATE users SET is_active = 1 WHERE UserID = ?");
                $stmt->execute([$userId]);
                $_SESSION['message'] = 'User activated successfully';
            } catch (PDOException $e) {
                $_SESSION['error'] = 'Error activating user: ' . $e->getMessage();
            }
        } else {
             $_SESSION['error'] = 'Invalid request.';
        }
        header('Location: users.php'); // Redirect back to user list
        exit;

    default:
        break;
}

// Get filter parameters
$userType = $_GET['user_type'] ?? '';
$search = $_GET['search'] ?? '';
$page = (int)($_GET['page'] ?? 1);
$sort = $_GET['sort'] ?? 'id_desc';
$statusFilter = isset($_GET['status']) && $_GET['status'] !== '' ? (int)$_GET['status'] : null; // Status filter

$_title = 'Admin - User Management';
include '../_head.php';
?>

<div class="admin-container">
    <div class="admin-sidebar">
        <?php include 'sidebar.php'; ?>
    </div>

    <div class="admin-content">
        <div class="admin-header">
            <h1>User Management</h1>
            <div class="admin-actions">
                <a href="#" id="addUserBtn" class="admin-btn">
                    <i class="fas fa-plus"></i> Add New User
                </a>
            </div>
        </div>

        <!-- Add Status Filter -->
        <div class="admin-filters">
            <form method="get" action="users.php" class="search-form">
                <div class="search-group">
                    <input type="text" name="search" placeholder="Search by name, email or username..." value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit" class="search-btn">
                        <i class="fas fa-search"></i>
                    </button>
                </div>

                <div class="filter-group">
                    <label for="user_type">User Type:</label>
                    <select name="user_type" id="user_type" onchange="this.form.submit()">
                        <option value="">All Types</option>
                        <?php foreach ($userTypes as $type): ?>
                            <option value="<?php echo htmlspecialchars($type); ?>" <?php echo $userType === $type ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($type); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Status Filter Dropdown -->
                <div class="filter-group">
                    <label for="status">Status:</label>
                    <select name="status" id="status" onchange="this.form.submit()">
                        <option value="">All Statuses</option>
                        <option value="1" <?php echo $statusFilter === 1 ? 'selected' : ''; ?>>Active</option>
                        <option value="0" <?php echo $statusFilter === 0 ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>

                <div class="sort-group">
                    <label for="sort">Sort by:</label>
                    <select name="sort" id="sort" onchange="this.form.submit()">
                        <option value="id_desc" <?php echo $sort === 'id_desc' ? 'selected' : ''; ?>>Newest First</option>
                        <option value="id_asc" <?php echo $sort === 'id_asc' ? 'selected' : ''; ?>>Oldest First</option>
                        <option value="name_asc" <?php echo $sort === 'name_asc' ? 'selected' : ''; ?>>Name (A-Z)</option>
                        <option value="name_desc" <?php echo $sort === 'name_desc' ? 'selected' : ''; ?>>Name (Z-A)</option>
                        <option value="status_asc" <?php echo $sort === 'status_asc' ? 'selected' : ''; ?>>Status (Active First)</option>
                        <option value="status_desc" <?php echo $sort === 'status_desc' ? 'selected' : ''; ?>>Status (Inactive First)</option>
                    </select>
                </div>
            </form>
        </div>

        <?php
        // Build query
        $query = "SELECT * FROM users";

        $conditions = [];
        $params = [];

        if (!empty($userType)) {
            $conditions[] = "UserType = ?";
            $params[] = $userType;
        }

        // Add status filter condition
        if ($statusFilter !== null) {
            $conditions[] = "is_active = ?";
            $params[] = $statusFilter;
        }

        if (!empty($search)) {
            $conditions[] = "(FirstName LIKE ? OR LastName LIKE ? OR Email LIKE ? OR Username LIKE ?)";
            $searchParam = "%$search%";
            $params = array_merge($params, [$searchParam, $searchParam, $searchParam, $searchParam]);
        }

        if (!empty($conditions)) {
            $query .= " WHERE " . implode(' AND ', $conditions);
        }

        // Add sorting
        switch ($sort) {
            case 'id_asc':
                $query .= " ORDER BY UserID ASC";
                break;
            case 'name_asc':
                $query .= " ORDER BY FirstName ASC, LastName ASC";
                break;
            case 'name_desc':
                $query .= " ORDER BY FirstName DESC, LastName DESC";
                break;
            case 'status_asc': // Sort by status
                 $query .= " ORDER BY is_active DESC, UserID DESC"; // Active first
                 break;
            case 'status_desc': // Sort by status
                 $query .= " ORDER BY is_active ASC, UserID DESC"; // Inactive first
                 break;
            default: // id_desc
                $query .= " ORDER BY UserID DESC";
        }

        // Use SimplePager for pagination
        $_db = $pdo; // SimplePager might expect $_db
        $pager = new SimplePager($query, $params, 10, $page);
        $users = $pager->result;
        $totalUsers = $pager->item_count;
        $totalPages = $pager->page_count;
        ?>

        <!-- Add Status Column and Update Actions Column -->
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>User Type</th>
                    <th>Status</th> <!-- New Column -->
                    <th>Registered</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="8" class="no-records">No users found</td> <!-- Updated colspan -->
                    </tr>
                <?php else: ?>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo $user['UserID'] ?? ''; ?></td>
                            <td><?php echo htmlspecialchars($user['username'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars(($user['FirstName'] ?? '') . ' ' . ($user['LastName'] ?? '')); ?></td>
                            <td><?php echo htmlspecialchars($user['Email'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($user['UserType'] ?? ''); ?></td>
                            <td> <!-- Status Display -->
                                <span class="status-badge <?php echo ($user['is_active'] ?? 0) ? 'status-active' : 'status-inactive'; ?>">
                                    <?php echo ($user['is_active'] ?? 0) ? 'Active' : 'Inactive'; ?>
                                </span>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($user['RegisterDate'] ?? 'now')); ?></td>
                            <td class="actions">
                                <a href="#" class="action-btn edit" data-userid="<?php echo $user['UserID'] ?? ''; ?>">Edit</a>
                                <?php if (($user['UserID'] ?? 0) != ($_SESSION['user_id'] ?? 0)): // Prevent actions on self ?>
                                    <?php if ($user['is_active'] ?? 0): ?>
                                        <a href="javascript:void(0);" onclick="confirmAction(<?php echo $user['UserID'] ?? ''; ?>, 'deactivate', 'Are you sure you want to deactivate this user? They will not be able to log in.')" class="action-btn deactivate">Deactivate</a>
                                    <?php else: ?>
                                        <a href="javascript:void(0);" onclick="confirmAction(<?php echo $user['UserID'] ?? ''; ?>, 'activate', 'Are you sure you want to activate this user? They will be able to log in again.')" class="action-btn activate">Activate</a>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php
                    // Build base URL for pagination links, preserving filters
                    $queryParams = http_build_query([
                        'user_type' => $userType,
                        'search' => $search,
                        'sort' => $sort,
                        'status' => $statusFilter
                    ]);
                ?>
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo ($page - 1); ?>&<?php echo $queryParams; ?>" class="page-link">&laquo; Previous</a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="?page=<?php echo $i; ?>&<?php echo $queryParams; ?>" class="page-link <?php echo $i === $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                    <a href="?page=<?php echo ($page + 1); ?>&<?php echo $queryParams; ?>" class="page-link">Next &raquo;</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Modal -->
        <div id="userModal" class="modal">
            <div class="modal-content">
                <span class="close-modal">&times;</span>
                <div id="modalFormContent"></div>
            </div>
        </div>

        <!-- Update JavaScript Confirmation Function -->
        <script>
            // Renamed from confirmDelete to confirmAction
            function confirmAction(userId, actionType, message) {
                if (confirm(message)) {
                    window.location.href = `users.php?action=${actionType}&id=${userId}`;
                }
            }

            document.addEventListener('DOMContentLoaded', function() {
                const modal = document.getElementById('userModal');
                const modalContent = document.getElementById('modalFormContent');
                const closeBtn = document.querySelector('.close-modal');

                // Add User button
                document.getElementById('addUserBtn').addEventListener('click', function(e) {
                    e.preventDefault();
                    fetch('users.php?action=add')
                        .then(response => response.text())
                        .then(html => {
                            modalContent.innerHTML = html;
                            // Initialize form handling for the new content if needed
                            initializeModalForm(modalContent.querySelector('form'));
                            modal.style.display = 'block';
                        })
                        .catch(error => console.error('Error fetching add form:', error));
                });

                // Edit buttons
                document.querySelectorAll('.action-btn.edit').forEach(btn => {
                    btn.addEventListener('click', function(e) {
                        e.preventDefault();
                        const userId = this.getAttribute('data-userid');
                        fetch(`users.php?action=edit&id=${userId}`)
                            .then(response => response.text())
                            .then(html => {
                                modalContent.innerHTML = html;
                                // Initialize form handling for the new content
                                initializeModalForm(modalContent.querySelector('form'));
                                modal.style.display = 'block';
                            })
                            .catch(error => console.error('Error fetching edit form:', error));
                    });
                });

                // Close modal
                closeBtn.addEventListener('click', function() {
                    modal.style.display = 'none';
                    modalContent.innerHTML = ''; // Clear content
                });

                // Close when clicking outside modal
                window.addEventListener('click', function(e) {
                    if (e.target === modal) {
                        modal.style.display = 'none';
                        modalContent.innerHTML = ''; // Clear content
                    }
                });

                // Handle cancel button inside modal
                // Use event delegation on modal content area
                modalContent.addEventListener('click', function(e) {
                    if (e.target.classList.contains('cancel-modal')) {
                        modal.style.display = 'none';
                        modalContent.innerHTML = ''; // Clear content
                    }
                });

                // Function to handle form submission via Fetch API
                function initializeModalForm(form) {
                    if (!form) return;
                    form.addEventListener('submit', function(e) {
                        e.preventDefault();
                        const formData = new FormData(form);

                        fetch(form.action, {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.error) {
                                // Display error more gracefully if possible
                                alert('Error: ' + data.error);
                            } else if (data.success) {
                                modal.style.display = 'none';
                                window.location.reload(); // Reload page to see changes
                            } else {
                                alert('An unknown error occurred.');
                            }
                        })
                        .catch(error => {
                            console.error('Form submission error:', error);
                            alert('An error occurred while submitting the form.');
                        });
                    });
                }

                // Note: The global submit listener is removed to avoid conflicts
                // with the dynamically added forms. Instead, we initialize
                // the listener when the form content is loaded into the modal.

            });
        </script>
        <!-- Add some basic CSS for status badges (you can customize this) -->
        <style>
            .status-badge {
                padding: 3px 8px;
                border-radius: 12px;
                font-size: 0.8em;
                font-weight: bold;
                color: white;
                text-transform: uppercase;
            }
            .status-active {
                background-color: #28a745; /* Green */
            }
            .status-inactive {
                background-color: #dc3545; /* Red */
            }
            .action-btn.deactivate {
                color: #dc3545;
            }
            .action-btn.activate {
                 color: #28a745;
            }
             .action-btn.deactivate:hover {
                text-decoration: underline;
            }
             .action-btn.activate:hover {
                 text-decoration: underline;
            }
        </style>
    </div>
</div>

<?php include '../_foot.php'; ?>