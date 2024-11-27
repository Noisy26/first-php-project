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
        header('Location: ../index.php');
        exit;
    }

    if (isset($_POST['update_id'])) {
        $faculty_name = $_POST['faculty_name'];

        $stmt = $pdo->prepare('SELECT id FROM faculties WHERE name = :name');
        $stmt->execute([
            'name' => $faculty_name
        ]);
        $res = $stmt->fetch();

        $id = $_POST['update_id'];
        $name = $_POST['name'];
        $head_full_name = $_POST['head_full_name'];
        $room_number = $_POST['room_number'];
        $building_number = $_POST['building_number'];
        $phone = $_POST['phone'];
        $teacher_count = $_POST['teacher_count'];
        $faculty_id = $res['id'];

        $stmt = $pdo->prepare("UPDATE departments SET 
       name = :name, 
       head_full_name = :head_full_name, 
       room_number = :room_number, 
       building_number = :building_number, 
       phone = :phone, 
       teacher_count = :teacher_count,
       faculty_id = :faculty_id
       WHERE id = :id");
        $stmt->execute([
            'id' => $id,
            'name' => $name,
            'head_full_name' => $head_full_name,
            'room_number' => $room_number,
            'building_number' => $building_number,
            'phone' => $phone,
            'teacher_count' => $teacher_count,
            'faculty_id' => $faculty_id
        ]);

        $_SESSION['message'] = "Запись с ID $id успешно обновлена.";
        header("Location: departments.php"); // Перезагрузка страницы
        exit();
    }

    if (isset($_POST['delete_id'])) {
        $deleteId = $_POST['delete_id'];

        $stmt = $pdo->prepare('SELECT first_name FROM teachers WHERE department_id = :id');
        $stmt->execute(['id' => $deleteId]);

        if ($stmt->fetchAll() === array()) {
            $stmt = $pdo->prepare('DELETE FROM departments WHERE id = :id');
            $stmt->execute(['id' => $deleteId]);
            $_SESSION['message'] = "Строка с ID $deleteId удалена.";
        } else {
            $_SESSION['message'] = "К этой кафедре привязан преподаватель, измените кафедру преподавателя или удалите запись преподавателя!";
        }

        header("Location: departments.php"); // Перенаправляем на ту же страницу
        exit();
    }

    if (isset($_POST['new_recording'])) {
        $faculty_name = $_POST['new_faculty_name'];

        $stmt = $pdo->prepare('SELECT id FROM faculties WHERE name = :name');
        $stmt->execute([
            'name' => $faculty_name
        ]);
        $res = $stmt->fetch();

        $name = $_POST['new_name'];
        $head_full_name = $_POST['new_head_full_name'];
        $room_number = $_POST['new_room_number'];
        $building_number = $_POST['new_building_number'];
        $phone = $_POST['new_phone'];
        $teacher_count = $_POST['new_teacher_count'];
        $faculty_id = $res['id'];

        $stmt = $pdo->prepare("INSERT INTO departments 
        (name, head_full_name, room_number, building_number, phone, teacher_count, faculty_id) VALUES 
        (:new_name, :new_head_full_name, :new_room_number, :new_building_number, :new_phone, :new_teacher_count, :new_faculty_id)");

        $stmt->execute([
            'new_name' => $name,
            'new_head_full_name' => $head_full_name,
            'new_room_number' => $room_number,
            'new_building_number' => $building_number,
            'new_phone' => $phone,
            'new_teacher_count' => $teacher_count,
            'new_faculty_id' => $faculty_id
        ]);

        $_SESSION['message'] = "Добавлена кафедра $name.";
        header("Location: departments.php"); // Перезагрузка страницы
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
        $stmt = $pdo->prepare('SELECT * FROM departments WHERE name LIKE :query OR head_full_name LIKE :query OR phone LIKE :query OR room_number LIKE :query');
        $stmt->execute(['query' => '%' . $searchQuery . '%']);
    } else {
        $stmt = $pdo->query('SELECT * FROM departments');
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
            <form method="get" action="departments.php">
                <input type="text" name="search" placeholder="Введите для поиска" value="<?= $searchQuery ?>">
                <button type="submit">Поиск</button>
            </form>
            <form method="post">
                <input type="hidden" name="delete_id" id="delete_id"> <!--Невидимое поле ввода для передачи информации об id записи-->
                <input type="hidden" name="new_recording" id="new_recording">
                <button disabled>
                    <a href="#newRecording">Добавить новую запись</a>
                </button>
                <button type="submit" disabled id="delete_button">
                    Удалить выбранную строку
                </button>
            </form>
        </div>
        <table>
            <tr>
                <th>Название кафедры</th>
                <th>ФИО заведующего</th>
                <th>Номер кабинета</th>
                <th>Номер корпуса</th>
                <th>Телефон</th>
                <th>Количество преподавателей</th>
                <th>Факультет</th>
            </tr>
            <?php while ($row = $stmt->fetch()): ?>
                <tr onclick="document.getElementById('delete_id').value='<?= $row['id'] ?>'; document.getElementById('delete_button').disabled=false;">
                    <td><?= $row['name'] ?></td>
                    <td><?= $row['head_full_name'] ?></td>
                    <td><?= $row['room_number'] ?></td>
                    <td><?= $row['building_number'] ?></td>
                    <td><?= $row['phone'] ?></td>
                    <td><?= $row['teacher_count'] ?></td>
                    <td>
                        <?php
                        $stmt1 = $pdo->prepare('SELECT name FROM faculties WHERE id = :id');
                        $stmt1->execute([
                            'id' => $row['faculty_id']
                        ]);
                        $res = $stmt1->fetch();

                        echo $res['name'];
                        ?>
                    </td>
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
                <br>
                <h2>Редактирование кафедры</h2>
                <form method="post">
                    <input type="hidden" name="update_id" value="<?= $row['id'] ?>">
                    <label>
                        Название кафедры:
                        <input type="text" name="name" value="<?= htmlspecialchars($row['name']) ?>">
                    </label>
                    <br>
                    <br>
                    <label>
                        ФИО заведующего:
                        <input type="text" name="head_full_name" value="<?= htmlspecialchars($row['head_full_name']) ?>" required>
                    </label>
                    <br>
                    <br>
                    <label>
                        Номер кабинета:
                        <input type="text" name="room_number" value="<?= htmlspecialchars($row['room_number']) ?>" required>
                    </label>
                    <br>
                    <br>
                    <label>
                        Номер корпуса:
                        <input type="text" name="building_number" value="<?= htmlspecialchars($row['building_number']) ?>" required>
                    </label>
                    <br>
                    <br>
                    <label>
                        Телефон:
                        <input type="text" name="phone" value="<?= htmlspecialchars($row['phone']) ?>" pattern="[0-9\-]+" required>
                    </label>
                    <br>
                    <br>
                    <label>
                        Количество преподавателей:
                        <input type="text" name="teacher_count" value="<?= htmlspecialchars($row['teacher_count']) ?>" required>
                    </label>
                    <br>
                    <br>
                    <label>
                        Факультет:
                        <select name="faculty_name">
                            <?php
                            $stmt1 = $pdo->query('SELECT * FROM faculties');
                            while ($name = $stmt1->fetch()):
                            ?>
                                <option <?php if ($row['faculty_id'] === $name['id']) echo 'selected' ?>>
                                    <?= $name['name'] ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </label>
                    <br>
                    <br>
                    <button class="submit" type="submit">Сохранить</button>
                </form>
            </div>
        </div>
    <?php endwhile; ?>
    <div id="newRecording" class="modal">
        <div class="modal-content">
            <a href="#" class="close">&times;</a>
            <h2>Добавление новой кафедры</h2>
            <form method="post">
                <input type="hidden" name="new_recording" value="gcfgxfxjgdj">
                <label>
                    Название кафедры:
                    <input type="text" name="new_name" required>
                </label>
                <br>
                <label>
                    ФИО заведующего:
                    <input type="text" name="new_head_full_name" required>
                </label>
                <br>
                <label>
                    Номер кабинета:
                    <input type="text" name="new_room_number" required>
                </label>
                <br>
                <label>
                    Номер корпуса:
                    <input type="text" name="new_building_number" required>
                </label>
                <br>
                <label>
                    Телефон:
                    <input type="text" name="new_phone" pattern="[0-9\-]+" required>
                </label>
                <br>
                <label>
                    Количество преподавателей:
                    <input type="text" name="new_teacher_count" required>
                </label>
                <br>
                <label>
                    Факультет:
                    <select name="new_faculty_name">
                        <?php
                        $stmt1 = $pdo->query('SELECT name FROM faculties');
                        while ($name = $stmt1->fetch()):
                        ?>
                            <option>
                                <?= $name['name'] ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </label>
                <br>
                <div class="submit-cont">
                    <button type="submit">Сохранить</button>
                </div>
            </form>
        </div>
    </div>
</body>

</html>