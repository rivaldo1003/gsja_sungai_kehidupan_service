<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GSJA Sungai Kehidupan - Selamat Datang</title>
    <style>
        header {
    background-color: #111;
    color: #fff;
    text-align: center;
    padding: 2em;
    position: relative;
}

header img {
    max-width: 30%;
    height: auto;
    display: block;
    margin: auto;
    background: transparent; /* Atur latar belakang gambar menjadi transparan. */
}

body {
    font-family: 'Helvetica Neue', sans-serif;
    margin: 0;
    padding: 0;
    background-color: transparent; /* Hilangkan latar belakang putih pada body. */
}
        nav {
            background-color: #333;
            padding: 1em;
            text-align: center;
        }
        nav a {
            color: #fff;
            text-decoration: none;
            padding: 0.5em 1em;
            margin: 0 1em;
            font-size: 18px;
        }
        section {
            text-align: center;
            padding: 2em;
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin: 20px;
            border-radius: 10px;
            opacity: 0;
            animation: fadeIn 1s ease-out forwards;
        }
        .cta-button {
            display: inline-block;
            background-color: #000;
            color: #fff;
            padding: 15px 30px;
            text-decoration: none;
            font-size: 20px;
            border-radius: 8px;
            transition: background-color 0.3s ease;
        }
        .cta-button:hover {
            background-color: #333;
        }
        footer {
            background-color: #111;
            color: #fff;
            text-align: center;
            padding: 2em;
            position: fixed;
            bottom: 0;
            width: 100%;
        }
        form {
            max-width: 600px;
            margin: auto;
        }
        label {
            display: block;
            margin-bottom: 8px;
        }
        input,
        textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 16px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        input[type="submit"] {
            background-color: #000;
            color: #fff;
            border: none;
            border-radius: 4px;
            padding: 15px 30px;
            font-size: 18px;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #333;
        }

        @keyframes fadeIn {
            to {
                opacity: 1;
            }
        }

        @media (max-width: 768px) {
            nav {
                display: flex;
                flex-direction: column;
                align-items: center;
            }
            nav a {
                margin: 0.5em 0;
            }
        }
    </style>
</head>
<body>
   <header>
    <img src="https://gsjasungaikehidupan.com/storage/profile_pictures/profile_pictures/gsja.png" alt="Logo GSJA Sungai Kehidupan">
    <h1>GSJA Sungai Kehidupan</h1>
    <p>Selamat Datang di Website Kami</p>
</header>
    

    <nav>
        <a href="#">Beranda</a>
        <a href="#">Tentang Kami</a>
        <a href="#">Khotbah</a>
        <a href="#kontak">Hubungi Kami</a>
    </nav>

    <section>
        <h2>Menjadi Bangsa yang Memuridkan</h2>
        <p>Selamat datang di GSJA Sungai Kehidupan. Ayo jadikan semua bangsa Murid Yesus.</p>
        <a href="#kontak" class="cta-button">Hubungi Kami</a>
    </section>

    <section id="kontak">
        <h2>Hubungi Kami</h2>
        <form method="post" action="">
            <label for="nama">Nama:</label>
            <input type="text" id="nama" name="nama" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>

            <label for="pesan">Pesan:</label>
            <textarea id="pesan" name="pesan" rows="4" required></textarea>

            <input type="submit" value="Kirim Pesan" class="cta-button">
        </form>

        <?php
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $nama = $_POST["nama"];
            $email = $_POST["email"];
            $pesan = $_POST["pesan"];

            $to = "kontak@gerejaanda.com";
            $subject = "Pesan dari $nama";
            $message = "Nama: $nama\nEmail: $email\nPesan: $pesan";

            mail($to, $subject, $message);
            echo "<p>Pesan Anda telah berhasil dikirim. Terima kasih!</p>";
        }
        ?>
    </section>

    <!--<footer>-->
    <!--    <p>&copy; 2024 Gereja XYZ. All rights reserved.</p>-->
    <!--</footer>-->

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var sections = document.querySelectorAll(".section");
            sections.forEach(function(section) {
                section.style.opacity = 1;
            });
        });
    </script>
</body>
</html>
