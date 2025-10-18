<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Bienvenido a GESCON</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    html, body {
      height: 100%;
      width: 100%;
      font-family: Arial, sans-serif;
    }

    body {
      position: relative;
    }

    .background-container {
      position: fixed;
      top: 0;
      left: 0;
      height: 100%;
      width: 100%;
      background-image: url("../img/inicio.jpg"); 
      background-size: cover;
      background-position: center;
      background-repeat: no-repeat;
      z-index: -1;
    }

    header {
      background-color: #003366;
      color: white;
      padding: 15px 30px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .login-btn {
      background-color: white;
      color: #003366;
      border: none;
      padding: 8px 16px;
      border-radius: 5px;
      font-weight: bold;
      cursor: pointer;
    }

    .info-overlay {
      position: absolute;
      right: 50px;
      top: 50%;
      transform: translateY(-50%);
      width: 400px;
      background-color: rgba(255, 255, 255, 0.95);
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 0 15px rgba(0,0,0,0.2);
    }

    .info-overlay h2 {
      color: #003366;
      margin-bottom: 20px;
    }

    .info-overlay p {
      font-size: 16px;
      margin-bottom: 10px;
    }

    @media (max-width: 768px) {
      .info-overlay {
        width: 90%;
        left: 5%;
        right: 5%;
        top: auto;
        bottom: 30px;
        transform: none;
      }
    }
  </style>
</head>
<body>

  <div class="background-container"></div>

  <header>
    <h1>Bienvenido a GESCON</h1>
    <a href="login.php"><button class="login-btn">Login</button></a>
  </header>

  <div class="info-overlay">
    <h2>¿Quiénes somos?</h2>
    <p>Somos una plataforma para gestionar artículos académicos.</p>
    <p>Facilitamos la revisión, evaluación y publicación colaborativa.</p>
    <p>GESCON apoya a autores, revisores y administradores.</p>
  </div>

</body>
</html>
