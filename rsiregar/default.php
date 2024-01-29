<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: 'Helvetica Neue', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            color: #333;
        }

        header {
            background-color: #000;
            color: #fff;
            padding: 1em;
            text-align: center;
        }

        nav {
            display: flex;
            justify-content: center;
            background-color: #000;
            padding: 0.5em;
        }

        nav a {
            text-decoration: none;
            color: #fff;
            margin: 0 15px;
            font-weight: bold;
            transition: color 0.3s ease-in-out;
        }

        nav a:hover {
            color: #ccc;
        }

        main {
            padding: 20px;
            text-align: center;
        }

        section {
            margin-bottom: 40px;
        }

        section h2 {
            color: #000;
        }

        section p {
            color: #666;
            line-height: 1.6;
        }

        .slider-container {
            overflow: hidden;
            max-width: 100%;
            margin-bottom: 20px;
        }

        .slider {
            display: flex;
            transition: transform 0.8s ease-in-out;
        }

        .slider img {
            width: 100%;
            height: auto;
        }

        footer {
            background-color: #000;
            color: #fff;
            text-align: center;
            padding: 1em;
            position: absolute;
            bottom: 0;
            width: 100%;
        }

        form {
            max-width: 600px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
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

        button {
            background-color: #000;
            color: #fff;
            padding: 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        button:hover {
            background-color: #333;
        }

        @media (max-width: 600px) {
            nav {
                flex-direction: column;
                align-items: center;
            }

            nav a {
                margin: 5px 0;
            }

            .slider {
                display: block;
                overflow: hidden;
            }

            .slider img {
                width: 100%;
                height: auto;
                display: block;
            }

            form {
                max-width: 100%;
            }
        }
    </style>
    <title>Your Company</title>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const slider = document.querySelector('.slider');
            const images = document.querySelectorAll('.slider img');

            let counter = 1;
            const size = images[0].clientWidth;

            setInterval(() => {
                slider.style.transition = "transform 0.8s ease-in-out";
                slider.style.transform = 'translateX(' + (-size * counter) + 'px)';
                counter++;

                if (counter === images.length) {
                    counter = 0;
                }
            }, 3000);

            slider.addEventListener('transitionend', () => {
                if (images[counter].id === 'lastClone') {
                    slider.style.transition = "none";
                    counter = images.length - 2;
                    slider.style.transform = 'translateX(' + (-size * counter) + 'px)';
                }
            });
        });
    </script>
</head>

<body>
    <header>
        <h1>GSJA Sungai Kehidupan Surabaya</h1>
    </header>
    <nav>
        <a href="#home">Home</a>
        <a href="#program">Programs</a>
        <a href="#services">Services</a>
        <a href="#about">About Us</a>
        <a href="#contact">Contact</a>
    </nav>
    <main>
        <section id="products" class="slider-container">
            <h2>Diamond Generation</h2>
            <div class="slider">
                <!-- Tambahkan beberapa foto worship dari internet di sini -->
                <img src="https://t3.ftcdn.net/jpg/02/98/36/04/360_F_298360495_TuPKH2prHdDHAOpzd6zGbTfnIBoNpZB9.jpg" alt="Worship 1">
                <img src="https://t3.ftcdn.net/jpg/02/88/63/30/360_F_288633014_12Xcx6nyozXBxe2qn8VnsrFyepVvaGuR.jpg" alt="Worship 2">
                <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRW8IpKof5SlYC_gMF4qMlC6YoU3Fz8Pt6BNF5bO_-C_IWUbf7BRdk2Ke713XqHTSWX2rk&usqp=CAU" alt="Worship 3">
                <!-- Duplicate images for smooth sliding -->
                <img src="https://t3.ftcdn.net/jpg/02/98/36/04/360_F_298360495_TuPKH2prHdDHAOpzd6zGbTfnIBoNpZB9.jpg" alt="Worship 1" id="lastClone">
                <img src="https://t3.ftcdn.net/jpg/02/88/63/30/360_F_288633014_12Xcx6nyozXBxe2qn8VnsrFyepVvaGuR.jpg" alt="Worship 2">
            </div>
        </section>

        <section id="contact">
            <h2>Contact Us</h2>
            <form action="#" method="post">
                <label for="name">Your Name:</label>
                <input type="text" id="name" name="name" required>

                <label for="email">Your Email:</label>
                <input type="email" id="email" name="email" required>

                <label for="message">Your Message:</label>
                <textarea id="message" name="message" rows="4" required></textarea>

                <button type="submit">Submit</button>
            </form>
        </section>
    </main>
    <footer>
        <p>&copy; <?php echo date("Y"); ?> GSJA Sungai Kehidupan. All rights reserved.</p>
    </footer>
</body>

</html>
