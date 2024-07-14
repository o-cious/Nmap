<?php
session_start();

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "user_management";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// User registration
if (isset($_POST['register'])) {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $email = $_POST['email'];

    $sql = "INSERT INTO users (username, password, email) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $username, $password, $email);
    if ($stmt->execute()) {
        echo "Registration successful!";
    } else {
        echo "Error: " . $stmt->error;
    }
}

// User login
if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user'] = $user;
        echo "Login successful!";
    } else {
        echo "Invalid credentials!";
    }
}

// User logout
if (isset($_POST['logout'])) {
    session_destroy();
    echo "Logged out successfully!";
}

// CRUD Operations
if (isset($_SESSION['user'])) {
    // Create
    if (isset($_POST['create'])) {
        $username = $_POST['new_username'];
        $password = password_hash($_POST['new_password'], PASSWORD_BCRYPT);
        $email = $_POST['new_email'];

        $sql = "INSERT INTO users (username, password, email) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $username, $password, $email);
        if ($stmt->execute()) {
            echo "User created successfully!";
        } else {
            echo "Error: " . $stmt->error;
        }
    }

    // Read
    if (isset($_GET['read'])) {
        $sql = "SELECT * FROM users";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            echo "<table>";
            echo "<tr><th>ID</th><th>Username</th><th>Email</th></tr>";
            while ($row = $result->fetch_assoc()) {
                echo "<tr><td>" . $row['id'] . "</td><td>" . $row['username'] . "</td><td>" . $row['email'] . "</td></tr>";
            }
            echo "</table>";
        } else {
            echo "No users found.";
        }
    }

    // Update
    if (isset($_POST['update'])) {
        $id = $_POST['user_id'];
        $username = $_POST['update_username'];
        $email = $_POST['update_email'];

        $sql = "UPDATE users SET username = ?, email = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $username, $email, $id);
        if ($stmt->execute()) {
            echo "User updated successfully!";
        } else {
            echo "Error: " . $stmt->error;
        }
    }

    // Delete
    if (isset($_POST['delete'])) {
        $id = $_POST['user_id'];

        $sql = "DELETE FROM users WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            echo "User deleted successfully!";
        } else {
            echo "Error: " . $stmt->error;
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Management</title>
</head>
<body>
    <h1>User Management System</h1>

    <!-- Registration Form -->
    <form method="post" action="">
        <h2>Register</h2>
        Username: <input type="text" name="username" required><br>
        Password: <input type="password" name="password" required><br>
        Email: <input type="email" name="email" required><br>
        <input type="submit" name="register" value="Register">
    </form>

    <!-- Login Form -->
    <form method="post" action="">
        <h2>Login</h2>
        Username: <input type="text" name="username" required><br>
        Password: <input type="password" name="password" required><br>
        <input type="submit" name="login" value="Login">
    </form>

    <!-- Logout Form -->
    <form method="post" action="">
        <input type="submit" name="logout" value="Logout">
    </form>

    <!-- CRUD Forms -->
    <?php if (isset($_SESSION['user'])): ?>
        <h2>CRUD Operations</h2>

        <!-- Create Form -->
        <form method="post" action="">
            <h3>Create User</h3>
            Username: <input type="text" name="new_username" required><br>
            Password: <input type="password" name="new_password" required><br>
            Email: <input type="email" name="new_email" required><br>
            <input type="submit" name="create" value="Create">
        </form>

        <!-- Read Users -->
        <form method="get" action="">
            <h3>Read Users</h3>
            <input type="submit" name="read" value="Read">
        </form>

        <!-- Update User -->
        <form method="post" action="">
            <h3>Update User</h3>
            User ID: <input type="number" name="user_id" required><br>
            New Username: <input type="text" name="update_username" required><br>
            New Email: <input type="email" name="update_email" required><br>
            <input type="submit" name="update" value="Update">
        </form>

        <!-- Delete User -->
        <form method="post" action="">
            <h3>Delete User</h3>
            User ID: <input type="number" name="user_id" required><br>
            <input type="submit" name="delete" value="Delete">
        </form>
    <?php endif; ?>
</body>
</html>
