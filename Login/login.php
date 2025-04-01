<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Food Safety Checker</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2980b9;
        }
        
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .login-container {
            width: 100%;
            max-width: 400px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .login-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: transform 0.3s ease;
        }
        
        .login-card:hover {
            transform: translateY(-5px);
        }
        
        .card-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 2rem;
            text-align: center;
        }
        
        .card-header h3 {
            margin: 0;
            font-size: 1.8rem;
            font-weight: 600;
        }
        
        .card-header p {
            margin: 0.5rem 0 0;
            opacity: 0.9;
        }
        
        .card-body {
            padding: 2rem;
        }
        
        .form-control {
            border-radius: 10px;
            padding: 12px 15px;
            border: 1px solid #e0e0e0;
            transition: all 0.3s ease;
            margin-bottom: 1rem;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            width: 100%;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.4);
        }
        
        .social-login {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .social-btn {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: white;
            transition: all 0.3s ease;
        }
        
        .social-btn:hover {
            transform: translateY(-3px);
        }
        
        .facebook { background-color: #3b5998; }
        .google { background-color: #db4437; }
        .twitter { background-color: #1da1f2; }
        
        .login-divider {
            display: flex;
            align-items: center;
            margin: 20px 0;
        }
        
        .login-divider::before,
        .login-divider::after {
            content: "";
            flex: 1;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .login-divider span {
            padding: 0 10px;
            color: #777;
            font-size: 0.9rem;
        }
        
        .alert {
            border-radius: 10px;
            margin-bottom: 1rem;
        }
        
        .form-floating label {
            padding-left: 15px;
        }
        
        .form-floating .form-control {
            height: 60px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="card-header">
                <h3>Food Safety Checker</h3>
                <p>Sign in to continue</p>
            </div>
            <div class="card-body">
                <div class="social-login">
                    <a href="#" class="social-btn facebook"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="social-btn google"><i class="fab fa-google"></i></a>
                    <a href="#" class="social-btn twitter"><i class="fab fa-twitter"></i></a>
                </div>
                
                <div class="login-divider">
                    <span>or login with email</span>
                </div>
                
                <form action="process_login.php" method="post">
                    <?php
                    $error = $_GET['error'] ?? '';
                    if ($error) {
                        $error_message = '';
                        switch ($error) {
                            case 'username_required':
                                $error_message = 'Username or email is required';
                                break;
                            case 'password_required':
                                $error_message = 'Password is required';
                                break;
                            case 'invalid_credentials':
                                $error_message = 'Invalid username or password';
                                break;
                            case 'db_error':
                                $error_message = 'Database error. Please try again.';
                                break;
                            case 'server_error':
                                $error_message = 'Server error. Please try again.';
                                break;
                            default:
                                $error_message = 'An error occurred. Please try again.';
                        }
                        echo '<div class="alert alert-danger">' . htmlspecialchars($error_message) . '</div>';
                    }
                    ?>
                    
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="username" name="username" placeholder="Username or Email" required>
                        <label for="username"><i class="fas fa-user me-2"></i>Username or Email</label>
                    </div>
                    
                    <div class="form-floating mb-4">
                        <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                        <label for="password"><i class="fas fa-lock me-2"></i>Password</label>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-sign-in-alt me-2"></i>Login
                        </button>
                    </div>
                </form>
                
                <div class="mt-4 text-center">
                    <p>Don't have an account? <a href="signup.html" class="text-decoration-none fw-bold">Sign up</a></p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
