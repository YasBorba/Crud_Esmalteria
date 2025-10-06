<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Esmalte - Studio D.I.Y</title>
    <style>
/* ======== RESET E BASE ======== */
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
  font-family: 'Poppins', sans-serif;
}

body {
  background: linear-gradient(135deg, #f9d5e5, #fcd5ce, #f8c8dc);
  min-height: 100vh;
  display: flex;
  align-items: flex-start;
  justify-content: center;
  padding: 30px 15px;
  color: #444;
}

/* ======== CONTAINER ======== */
.container {
  background: #fff;
  padding: 40px 30px;
  border-radius: 20px;
  width: 100%;
  max-width: 700px;
  box-shadow: 0 8px 25px rgba(214, 51, 108, 0.25);
  animation: fadeIn 0.8s ease-in-out;
}

.container h1 {
  text-align: center;
  margin-bottom: 30px;
  color: #d6336c;
  font-weight: 700;
  letter-spacing: 1px;
  font-size: 2rem;
}

/* ======== FORM ======== */
form {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.form-row {
  display: flex;
  flex-wrap: wrap;
  gap: 15px;
  justify-content: space-between;
}

.form-group {
  flex: 1 1 250px;
  display: flex;
  flex-direction: column;
}

.form-group label {
  margin-bottom: 6px;
  font-weight: 600;
  color: #555;
}

input,
select,
textarea {
  padding: 10px;
  border-radius: 12px;
  border: 2px solid #f8c8dc;
  font-size: 14px;
  transition: 0.3s ease;
}

input:focus,
select:focus,
textarea:focus {
  border-color: #d6336c;
  box-shadow: 0 0 8px rgba(214, 51, 108, 0.4);
  outline: none;
}

textarea {
  resize: vertical;
  min-height: 70px;
}

/* ======== BOTÕES ======== */
.btn,
button {
  padding: 12px 24px;
  background: linear-gradient(135deg, #d6336c, #f0569b);
  color: #fff;
  border: none;
  border-radius: 12px;
  font-weight: bold;
  cursor: pointer;
  transition: all 0.3s ease;
  text-decoration: none;
  text-align: center;
  display: inline-block;
  margin-top: 10px;
}

.btn:hover,
button:hover {
  background: linear-gradient(135deg, #b81e53, #fc4999);
  transform: scale(1.05);
  box-shadow: 0 4px 10px rgba(214, 51, 108, 0.4);
}

/* ======== ANIMAÇÃO ======== */
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(-15px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* ======== ALERTAS ======== */
.alert {
  padding: 12px 15px;
  border-radius: 10px;
  margin-bottom: 20px;
  font-weight: 500;
}

.alert.success {
  background: #e6f4ea;
  border-left: 5px solid #4caf50;
  color: #2e7d32;
}

.alert.error {
  background: #fdecea;
  border-left: 5px solid #e53935;
  color: #c62828;
}

/* ======== RESPONSIVIDADE ======== */
@media (max-width: 700px) {
  .container {
    padding: 25px 20px;
  }
  .form-row {
    flex-direction: column;
  }
  .btn, button {
    width: 100%;
  }
  .container h1 {
    font-size: 1.6rem;
  }
  input, select, textarea {
    font-size: 13px;
  }
}
    </style>
</head>
<body>
    <div class="container">
        <h1>Editar Esmalte</h1>

        <form method="post">
            <div class="form-row">
                <div class="form-group">
                    <label>Nome:</label>
                    <input type="text" name="nome" required value="<?= htmlspecialchars($esmalte['nome']) ?>">
                </div>
                <div class="form-group">
                    <label>Preço (R$):</label>
                    <input type="number" name="preco" step="0.01" required value="<?= $esmalte['preco'] ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Categoria:</label>
                    <select name="categorias" required>
                        <option value="Cremoso" <?= $esmalte['categoria'] == 'Cremoso' ? 'selected' : '' ?>>Cremoso</option>
                        <option value="Metalico" <?= $esmalte['categoria'] == 'Metalico' ? 'selected' : '' ?>>Metálico</option>
                        <option value="Glitter" <?= $esmalte['categoria'] == 'Glitter' ? 'selected' : '' ?>>Glitter</option>
                        <option value="Perolado" <?= $esmalte['categoria'] == 'Perolado' ? 'selected' : '' ?>>Perolado</option>
                        <option value="Fosco" <?= $esmalte['categoria'] == 'Fosco' ? 'selected' : '' ?>>Fosco</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Marca:</label>
                    <input type="text" name="marcas" required value="<?= htmlspecialchars($esmalte['marca']) ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Estoque Mínimo:</label>
                    <input type="number" name="estoque_minimo" min="0" required value="<?= $esmalte['estoque_minimo'] ?>">
                </div>
                <div class="form-group">
                    <label>Cores:</label>
                    <textarea name="cores" required><?= htmlspecialchars($esmalte['cores']) ?></textarea>
                </div>
            </div>

            <button type="submit" name="update" class="btn">Atualizar</button>
            <a href="esmaltes.php" class="btn">Cancelar</a>
        </form>
    </div>
</body>
</html>
