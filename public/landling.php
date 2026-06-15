<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>GoCloud Loading Screen</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, Helvetica, sans-serif;
        }

        body {
            width: 100%;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: #ffffff;
            overflow: hidden;
        }

        .loader-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            animation: fadeIn 1s ease;
        }

        .logo-circle {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            background: linear-gradient(135deg, #2f80ff, #0057ff);
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
            animation: popIn 1s ease;
            box-shadow: 0 15px 40px rgba(0, 87, 255, 0.25);
        }

        .cloud {
            width: 75px;
            height: 45px;
            border: 7px solid white;
            border-radius: 40px;
            position: relative;
            border-top-left-radius: 50px;
            border-top-right-radius: 50px;
            background: transparent;
        }

        .cloud::before {
            content: '';
            position: absolute;
            width: 35px;
            height: 35px;
            border: 7px solid white;
            border-radius: 50%;
            top: -22px;
            left: 5px;
            background: transparent;
        }

        .cloud::after {
            content: '';
            position: absolute;
            width: 40px;
            height: 40px;
            border: 7px solid white;
            border-radius: 50%;
            top: -25px;
            right: -5px;
            background: transparent;
        }

        .check {
            position: absolute;
            top: -5px;
            right: -5px;
            width: 45px;
            height: 45px;
            background: white;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            color: #0057ff;
            font-size: 24px;
            font-weight: bold;
            animation: checkAppear 1s ease forwards;
            animation-delay: 1.5s;
            opacity: 0;
        }

        .brand {
            margin-top: 25px;
            font-size: 42px;
            font-weight: 700;
            color: #111;
            letter-spacing: 1px;
            opacity: 0;
            animation: textFade 1s ease forwards;
            animation-delay: 2s;
        }

        .tagline {
            margin-top: 10px;
            font-size: 15px;
            letter-spacing: 5px;
            color: #888;
            text-transform: uppercase;
            opacity: 0;
            animation: textFade 1s ease forwards;
            animation-delay: 2.4s;
        }

        .pulse {
            position: absolute;
            width: 180px;
            height: 180px;
            border-radius: 50%;
            border: 2px solid rgba(0, 87, 255, 0.2);
            animation: pulse 2s infinite;
        }

        @keyframes popIn {
            0% {
                transform: scale(0);
                opacity: 0;
            }

            100% {
                transform: scale(1);
                opacity: 1;
            }
        }

        @keyframes checkAppear {
            0% {
                transform: scale(0);
                opacity: 0;
            }

            100% {
                transform: scale(1);
                opacity: 1;
            }
        }

        @keyframes textFade {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes pulse {
            0% {
                transform: scale(0.9);
                opacity: 1;
            }

            100% {
                transform: scale(1.3);
                opacity: 0;
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        @media(max-width:768px) {

            .logo-circle {
                width: 120px;
                height: 120px;
            }

            .brand {
                font-size: 32px;
            }

            .tagline {
                font-size: 12px;
                letter-spacing: 3px;
            }
        }
    </style>
</head>

<body>

    <div class="loader-container">

        <div class="pulse"></div>

        <div class="logo-circle">

            <div class="cloud"></div>

            <div class="check">✓</div>

        </div>

        <div class="brand">GoCloud</div>

        <div class="tagline">Secure Your Data</div>

    </div>

    <script>
        // redirect to login page after animation

        setTimeout(() => {

            window.location.href = "dashboard_new.php";

        }, 5000);
    </script>

</body>

</html>