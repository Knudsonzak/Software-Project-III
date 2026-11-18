<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
</head>
<body>
    <div class="background-decoration">
        <div class="shape shape1">∿</div>
        <div class="shape shape2">∿</div>
        <div class="shape shape3">≡</div>
        <div class="shape shape4">≡</div>
    </div>

    <div class="container">
        <div class="logo-section">
            <div class="logo">Your logo</div>
            <h1>Login</h1>
        </div>

        @if ($errors->any())
            <div class="error-message">
                {{ $errors->first('email') }}
            </div>
        @endif

        <form method="POST" action="{{ route('signin') }}">
            @csrf

            <div class="form-group">
                <label for="email">Email</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    value="{{ old('email') }}"
                    required
                    autofocus
                    placeholder="Enter your email"
                >
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    required
                    placeholder="Enter your password"
                >
            </div>

            <div class="forgot-password">
                <a href="#">Forgot Password?</a>
            </div>

            <button type="submit" class="sign-in-btn">Sign In</button>
        </form>
    </div>
</body>
</html>
