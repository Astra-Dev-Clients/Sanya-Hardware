<?php 
session_start(); 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Store</title>
    <style>
        :root {
            --background-color: #ffffff;
            --default-color: #314862;
            --heading-color: #A0C878;
            --accent-color: #143D60;
            --surface-color: #ffffff;
            --contrast-color: #ffffff;
        }
        body {
            font-family: Arial, sans-serif;
            min-height: 100vh;
            margin: 0;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            position: relative;
            z-index: 1;
            background: var(--background-color);
            color: var(--default-color);
        }
        .background-image {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            width: 100vw;
            height: 100vh;
            background: url('../assets/img/bg/technology.jpg') center center/cover no-repeat;
            z-index: 0;
        }
        .overlay {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(0,0,0,0.45);
            z-index: 1;
        }
        .login-container {
            background: var(--surface-color);
            padding: 2rem 2.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            width: 320px;
            position: relative;
            z-index: 2;
        }
        .login-container h2 {
            margin-bottom: 1.5rem;
            text-align: center;
            color: var(--heading-color);
        }
        .form-group {
            margin-bottom: 1rem;
        }
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--default-color);
        }
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #ccc;
            border-radius: 4px;
            color: var(--default-color);
            background: var(--contrast-color);
        }
        button {
            width: 100%;
            padding: 0.7rem;
            background: var(--accent-color);
            color: var(--contrast-color);
            border: none;
            border-radius: 4px;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.2s;
        }
        button:hover {
            background: #082037; /* Darker shade for hover effect */
            /* color: var(--accent-color); */
        }
        .forgot-password {
            display: block;
            text-align: right;
            margin-top: 0.5rem;
            font-size: 0.95rem;
        }
        .forgot-password a {
            color: var(--accent-color);
            text-decoration: none;
        }
        .forgot-password a:hover {
            text-decoration: underline;
        }
        .footer {
            width: 100vw;
            text-align: center;
            padding: 1rem 0 0.5rem 0;
            color: var(--default-color);
            font-size: 0.95rem;
            position: relative;
            z-index: 2;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            padding: 0.75rem 1.25rem;
            border-radius: 4px;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="background-image"></div>
    <div class="overlay"></div>
<div class="login-container">
    <h2>Store Login</h2>

      <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger">
                        <?= $_SESSION['error']; unset($_SESSION['error']); ?>
                    </div>
        <?php endif; ?>

    <form action="../backend/process_login.php" method="POST">
        <div class="form-group">
            <label for="store_id">St ore ID</label>
            <input type="text" id="store_id" name="store_id" required>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
        </div>
        <button type="submit">Login</button>

        <div class="links" style="display: flex; justify-content: space-between; margin-top: 1rem;">
            <div class="forgot-password">
                <a href="/forgot-password">Reset Password</a>
            </div>

            <div class="forgot-password">
                <a href="register.php"> Register</a>
            </div>
        </div>

        
    </form>
</div>

    <div class="footer" style="position:fixed; bottom:0; left:0; right:0; background:rgba(255,255,255,0.85); z-index:3;">
        Powered by Astra Softwares
    </div>
</body>
</html>