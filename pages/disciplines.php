<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
        content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <?php require '../dbConnect.php' ?>
    <link href="../css/pagesStyles.css" rel="stylesheet">
</head>

<body>
    <?php
    session_start();
    global $pdo;

    #Проверка, вошел ли пользователь в аккаунт
    if (!isset($_SESSION['username'])) {
        header('Location: ../disciplines.php');
        exit;
    }

    if (isset($_POST['update_id'])) {
        $id = $_POST['update_id'];
        $name = $_POST['name'];
        $hours = $_POST['hours'];
        $cycle = $_POST['cycle'];

        $stmt = $pdo->prepare("UPDATE disciplines SET name = :name, hours = :hours, cycle = :cycle WHERE id = :id");
        $stmt->execute([
            'id' => $id,
            'name' => $name,
            'hours' => $hours,
            'cycle' => $cycle,
        ]);

        $_SESSION['message'] = "Запись с ID $id успешно обновлена.";
        header("Location: disciplines.php"); // Перезагрузка страницы
        exit();
    }

    if (isset($_POST['delete_id'])) {
        $deleteId = $_POST['delete_id'];

        $stmt = $pdo->prepare('SELECT * FROM workload WHERE discipline_id = :id');
        $stmt->execute(['id' => $deleteId]);

        if ($stmt->fetchAll() === array()) {
            $stmt = $pdo->prepare('DELETE FROM disciplines WHERE id = :id');
            $stmt->execute(['id' => $deleteId]);
            $_SESSION['message'] = "Строка с ID $deleteId удалена.";
        } else {
            $_SESSION['message'] = "У дисциплины осталась запись с рабочей нагрузкой, сначала удалите её.";
        }

        header("Location: disciplines.php"); // Перенаправляем на ту же страницу
        exit();
    }

    if (isset($_POST['new_recording'])) {
        $name = $_POST['new_name'];
        $hours = $_POST['new_hours'];
        $cycle = $_POST['new_cycle'];

        $stmt = $pdo->prepare("INSERT INTO disciplines (name, hours, cycle) VALUES (:new_name, :new_hours, :new_cycle)");
        $stmt->execute([
            'new_name' => $name,
            'new_hours' => $hours,
            'new_cycle' => $cycle,
        ]);

        $_SESSION['message'] = "Добавлена дисциплина $name.";
        header("Location: disciplines.php"); // Перезагрузка страницы
        exit();
    }

    #хз как это работает, но нужно оно для того, чтобы алерт не выводился каждый раз при обновлении страницы
    $message = '';
    if (isset($_SESSION['message'])) {
        $message = $_SESSION['message'];
        unset($_SESSION['message']); // Удаляем сообщение после вывода
    }

    #Если в строке поиска что-то есть, то таблица выводится согласно этому
    $searchQuery = '';
    if (isset($_GET['search'])) {
        $searchQuery = trim($_GET['search']);
        $stmt = $pdo->prepare('SELECT * FROM disciplines WHERE name LIKE :query OR hours LIKE :query OR cycle LIKE :query');
        $stmt->execute(['query' => '%' . $searchQuery . '%']);
    } else {
        $stmt = $pdo->query('SELECT * FROM disciplines');
    }
    ?>
    <header>
        <div class="top-menu">
            <form method="post" action="faculties.php">
                <button type="submit">Факультеты</button>
            </form>
            <form method="post" action="departments.php">
                <button type="submit">Кафедры</button>
            </form>
            <form method="post" action="teachers.php">
                <button type="submit">Преподаватели</button>
            </form>
            <form method="post" action="disciplines.php">
                <button type="submit">Дисциплины</button>
            </form>
            <form method="post" action="workload.php">
                <button type="submit">Рабочая нагрузка</button>
            </form>
        </div>
    </header>
    <div class="main-window">
        <div class="table-menu">
            <form method="get" action="disciplines.php">
                <input type="text" name="search" placeholder="Введите для поиска" value="<?= $searchQuery ?>">
                <button type="submit">Поиск</button>
            </form>
            <form method="post">
                <input type="hidden" name="delete_id" id="delete_id"> <!--Невидимое поле ввода для передачи информации об id записи-->
                <input type="hidden" name="new_recording" id="new_recording">
                <button type="submit" id="add_button">
                    <a href="#newRecording">Добавить новую запись</a>
                </button>
                <button type="submit" disabled id="delete_button">
                    Удалить выбранную строку
                </button>
            </form>
        </div>
        <table>
            <tr>
                <th>Название дисциплины</th>
                <th>Количество часов</th>
                <th>Цикл дисциплины</th>
            </tr>
            <?php while ($row = $stmt->fetch()): ?>
                <tr onclick="document.getElementById('delete_id').value='<?= $row['id'] ?>'; document.getElementById('delete_button').disabled=false;">
                    <td><?= $row['name'] ?></td>
                    <td><?= $row['hours'] ?></td>
                    <td><?= $row['cycle'] ?></td>
                    <td><a href="#editForm<?= $row['id'] ?>">Изменить</a></td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>
    <a href="../logout.php">Выйти</a>
    <?php if (!empty($message)): ?>
        <script>
            window.onload = function() {
                alert("<?= $message ?>") //выводит сообщение после загрузки всего остального
            }
        </script>
    <?php
    endif;

    #Создание модальных окон для редактирования
    $stmt->execute();
    while ($row = $stmt->fetch()):
    ?>
        <div id="editForm<?= $row['id'] ?>" class="modal">
            <div class="modal-content">
                <a href="#" class="close">&times;</a>
                <h2>Редактирование Дисциплины</h2>
                <form method="post">
                    <input type="hidden" name="update_id" value="<?= $row['id'] ?>">
                    <label>
                        Название:
                        <input type="text" name="name" value="<?= htmlspecialchars($row['name']) ?>">
                    </label>
                    <br>
                    <label>
                        Количество часов:
                        <input type="text" name="hours" value="<?= htmlspecialchars($row['hours']) ?>" required>
                    </label>
                    <br>
                    <label>
                        Цикл:
                        <input type="text" name="cycle" value="<?= htmlspecialchars($row['cycle']) ?>" required>
                    </label>
                    <br>
                    <button type="submit">Сохранить</button>
                </form>
            </div>
        </div>
    <?php endwhile; ?>
    <div id="newRecording" class="modal">
        <div class="modal-content">
            <a href="#" class="close">&times;</a>
            <h2>Добавление нового факультета</h2>
            <form method="post">
                <input type="hidden" name="new_recording" value="gcfgxfxjgdj">
                <label>
                    Название:
                    <input type="text" name="new_name" required>
                </label>
                <br>
                <label>
                    Количество часов:
                    <input type="text" name="new_hours" required>
                </label>
                <br>
                <label>
                    Цикл:
                    <input type="text" name="new_cycle" required>
                </label>
                <br>
                <button type="submit">Сохранить</button>
            </form>
        </div>
    </div>
</body>

</html>