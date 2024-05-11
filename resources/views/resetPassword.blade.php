<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            width: 300px;
            text-align: center;
        }
        input[type="password"] {
            width: calc(100% - 20px);
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }
        input[type="submit"] {
            width: 100%;
            padding: 10px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        input[type="submit"]:hover {
            background-color: #0056b3;
        }
        .logo img {
            max-width: 100%; /* Membuat gambar tidak melebihi lebar container */
            height: auto; /* Mengikuti proporsi asli gambar */
            margin-bottom: 20px;
        }
        .error {
            color: red;
            margin-top: 5px;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="logo">
        @if(isset($user) && $user)
            <img src="https://gsjasungaikehidupan.com/storage/profile_pictures/gsja.png" alt="Profile Picture">
        @endif
    </div>
    <h2>Reset Password</h2>
    @if($errors->any())
        <div class="error">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <form method="POST" action="{{ route('reset-password') }}">
        @csrf
        @if(isset($user) && $user)
            <input type="hidden" name="id" value="{{ $user->id }}">
        @endif
        <input type="password" name="password" placeholder="New Password">
        <br><br>
        <input type="password" name="password_confirmation" placeholder="Confirm Password">
        <br><br>
        <input type="submit" value="Reset Password">
    </form>
</div>

</body>
</html>
