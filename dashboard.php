<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Таблица 3_dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Подключение Bootstrap CSS через CDN -->
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
  <style>
    /* Стили для прокручиваемой таблицы */
    table {
      width: 100%;
      overflow-x: auto;
      display: block;
    }
    th, td {
      min-width: 120px;
      white-space: nowrap;
    }
  </style>
</head>
<body>
  <div class="container">
    <?php
    // Установить параметры подключения к базе данных
    $servername = "165.22.196.102";
    $username = "stata";
    $password = "stata";
    $dbname = "stata";

    // Создать соединение с базой данных
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Проверить соединение
    if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
    }

    // Получить названия столбцов таблицы
    $sql = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '3_dashboard' AND TABLE_SCHEMA = 'stata'";
    $result = $conn->query($sql);

    // Создать заголовок таблицы
    echo "<table class='table table-striped table-bordered table-hover'><thead class='thead-dark'><tr>";
    while($row = $result->fetch_assoc()) {
      echo "<th>" . $row["COLUMN_NAME"] . "</th>";
    }
    echo "</tr></thead><tbody>";

    // Получить данные из таблицы
    $sql = "SELECT * FROM 3_dashboard";
    $result = $conn->query($sql);

    // Вывести данные в таблицу
    while($row = $result->fetch_assoc()) {
      echo "<tr>";
      foreach($row as $value) {
        echo "<td>" . $value . "</td>";
      }
      echo "</tr>";
    }
    echo "</tbody></table>";

    // Закрыть соединение с базой данных
    $conn->close();
    ?>
  </div>
  <!-- Подключение Bootstrap JS через CDN -->
  <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>
</html>
