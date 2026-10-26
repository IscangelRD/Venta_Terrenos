<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso Administrativo - Terrenos</title>
    <style>
        * { box-sizing: border-box; font-family: 'Segoe UI', system-ui, -apple-system, sans-serif; margin: 0; padding: 0; }
        body {
            min-height: 100vh; display: flex; align-items: center; justify-content: center;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            padding: 20px;
        }
        .login-card {
            background: #ffffff; width: 100%; max-width: 380px; padding: 32px 26px;
            border-radius: 18px; box-shadow: 0 16px 36px rgba(0,0,0,0.35);
        }
        h2 { color: #0f172a; text-align: center; font-size: 1.4rem; margin-bottom: 6px; }
        p { color: #64748b; font-size: 0.88rem; text-align: center; margin-bottom: 22px; }
        .form-group { margin-bottom: 16px; }
        label { display: block; font-size: 0.84rem; font-weight: 600; color: #334155; margin-bottom: 6px; }
        input {
            width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.95rem;
            outline: none; transition: border 0.2s;
        }
        input:focus { border-color: #16a34a; box-shadow: 0 0 0 3px rgba(22,163,74,0.15); }
        .btn-login {
            width: 100%; background: #16a34a; color: #ffffff; border: none; padding: 13px;
            border-radius: 9px; font-weight: 700; font-size: 1rem; cursor: pointer; margin-top: 6px;
            transition: background 0.2s;
        }
        .btn-login:hover { background: #15803d; }
        .error-msg {
            background: #fee2e2; color: #b91c1c; padding: 10px; border-radius: 8px;
            font-size: 0.84rem; margin-bottom: 16px; text-align: center; font-weight: 600;
        }
        .back-link {
            display: block; text-align: center; margin-top: 18px; color: #64748b;
            text-decoration: none; font-size: 0.85rem; font-weight: 500;
        }
        .back-link:hover { color: #0f172a; }
    </style>
</head>
<body>

<div class="login-card">
    <h2>🔐 Panel Administrativo</h2>
    <p>Ingresa tus datos para gestionar predios</p>

    <?php if (isset($_GET['error'])): ?>
        <div class="error-msg">Usuario o contraseña incorrectos.</div>
    <?php endif; ?>

    <form action="index.php?action=do_login" method="POST">
        <div class="form-group">
            <label>Usuario</label>
            <input type="text" name="usuario" required placeholder="admin" autofocus>
        </div>
        <div class="form-group">
            <label>Contraseña</label>
            <input type="password" name="password" required placeholder="••••••••">
        </div>
        <button type="submit" class="btn-login">Ingresar al Panel</button>
    </form>
    <a href="index.php" class="back-link">← Volver al Mapa de Clientes</a>
</div>

</body>
</html>