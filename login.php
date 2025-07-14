<?php
require_once 'config.php';
require_once 'db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields';
    } else {
        $db = Database::getInstance();
        $user = $db->getUserByEmail($email);
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            
            // Update user online status
            $db->updateUserStatus($user['id'], true);
            
            echo "<script>window.location.href = 'home.php';</script>";
            exit();
        } else {
            $error = 'Invalid email or password';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo APP_NAME; ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #25D366 0%, #128C7E 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
            padding: 50px 40px;
            width: 100%;
            max-width: 450px;
            animation: slideIn 0.6s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .logo {
            text-align: center;
            margin-bottom: 40px;
        }

        .logo-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #25D366, #128C7E);
            border-radius: 50%;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 35px;
            color: white;
            box-shadow: 0 10px 30px rgba(37, 211, 102, 0.3);
        }

        h2 {
            color: #333;
            font-size: 2rem;
            text-align: center;
            margin-bottom: 10px;
            font-weight: 700;
        }

        .subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 40px;
            font-size: 1rem;
        }

        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
            font-size: 0.95rem;
        }

        .form-group input {
            width: 100%;
            padding: 15px 20px;
            border: 2px solid #e1e5e9;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .form-group input:focus {
            outline: none;
            border-color: #25D366;
            background: white;
            box-shadow: 0 0 0 3px rgba(37, 211, 102, 0.1);
        }

        .error-message {
            background: #fee;
            color: #c33;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 25px;
            border-left: 4px solid #c33;
            font-size: 0.95rem;
        }

        .btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #25D366, #128C7E);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-bottom: 25px;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(37, 211, 102, 0.4);
        }

        .btn:active {
            transform: translateY(0);
        }

        .divider {
            text-align: center;
            margin: 30px 0;
            position: relative;
            color: #666;
        }

        .divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: #e1e5e9;
        }

        .divider span {
            background: white;
            padding: 0 20px;
            font-size: 0.9rem;
        }

        .signup-link {
            text-align: center;
        }

        .signup-link a {
            color: #25D366;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .signup-link a:hover {
            color: #128C7E;
            text-decoration: underline;
        }

        .back-link {
            position: absolute;
            top: 20px;
            left: 20px;
            color: white;
            text-decoration: none;
            font-size: 1.5rem;
            transition: transform 0.3s ease;
        }

        .back-link:hover {
            transform: translateX(-5px);
        }

        @media (max-width: 768px) {
            .login-container {
                padding: 30px 20px;
                margin: 10px;
            }
            
            h2 {
                font-size: 1.7rem;
            }
        }
    </style>
</head>
<body>
    <a href="index.php" class="back-link">←</a>
    
    <div class="login-container">
        <div class="logo">
            <div class="logo-icon">💬</div>
            <h2>Welcome Back</h2>
            <p class="subtitle">Sign in to continue chatting</p>
        </div>

        <?php if ($error): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" required 
                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                       placeholder="Enter your email">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required 
                       placeholder="Enter your password">
            </div>

            <button type="submit" class="btn">Sign In</button>
        </form>

        <div class="divider">
            <span>Don't have an account?</span>
        </div>

        <div class="signup-link">
            <a href="signup.php">Create New Account</a>
        </div>
    </div>

    <script>
        // Add form validation and effects
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            const inputs = document.querySelectorAll('input');
            
            // Add floating label effect
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.parentElement.classList.add('focused');
                });
                
                input.addEventListener('blur', function() {
                    if (this.value === '') {
                        this.parentElement.classList.remove('focused');
                    }
                });
                
                // Check if input has value on load
                if (input.value !== '') {
                    input.parentElement.classList.add('focused');
                }
            });
            
            // Form submission with loading state
            form.addEventListener('submit', function() {
                const submitBtn = this.querySelector('.btn');
                submitBtn.innerHTML = 'Signing In...';
                submitBtn.disabled = true;
            });
        });
    </script>
</body>
</html>
