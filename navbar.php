<?php
$role = getUserRole();
$name = getUserName();
$isLogged = isLoggedIn();
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="/ticket_system/index.php">
            <i class="fas fa-headset me-2"></i>Waves Support
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <?php if (!$isLogged): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/ticket_system/index.php">Home</a>
                    </li>
                <?php elseif ($role === 'customer'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/ticket_system/customer/dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/ticket_system/customer/submit_ticket.php">Submit Ticket</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/ticket_system/customer/my_tickets.php">My Tickets</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/ticket_system/customer/chatbot.php">Chatbot</a>
                    </li>
                <?php elseif ($role === 'staff'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/ticket_system/staff/dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/ticket_system/staff/assigned_tickets.php">Assigned Tickets</a>
                    </li>
                <?php elseif ($role === 'admin'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/ticket_system/admin/dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/ticket_system/admin/manage_users.php">Users</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/ticket_system/admin/manage_categories.php">Categories</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/ticket_system/admin/manage_tickets.php">Tickets</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/ticket_system/admin/reports.php">Reports</a>
                    </li>
                <?php endif; ?>
            </ul>
            <ul class="navbar-nav">
                <?php if ($isLogged): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user me-1"></i><?php echo htmlspecialchars($name); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <?php if ($role === 'customer'): ?>
                                <li><a class="dropdown-item" href="/ticket_system/customer/profile.php">Profile</a></li>
                            <?php elseif ($role === 'staff'): ?>
                                <li><a class="dropdown-item" href="/ticket_system/staff/profile.php">Profile</a></li>
                            <?php elseif ($role === 'admin'): ?>
                                <li><a class="dropdown-item" href="/ticket_system/admin/settings.php">Settings</a></li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="/ticket_system/logout.php">
                                <i class="fas fa-sign-out-alt me-1"></i>Logout</a>
                            </li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/ticket_system/login.php">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/ticket_system/register.php">Register</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>