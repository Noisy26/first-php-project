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
        $id = $_POST['update_id'];
        $first_name = $_POST['first_name'];
        $last_name = $_POST['last_name'];
        $middle_name = $_POST['middle_name'];
        $department_id = $_POST['department_id'];
        $birth_year = $_POST['birth_year'];
        $hire_year = $_POST['hire_year'];
        $experience = $_POST['experience'];
        $position = $_POST['position'];
        $gender = $_POST['gender'];
        $city = $_POST['city'];

        $stmt = $pdo->prepare("UPDATE teachers SET 
         first_name = :first_name,
         last_name = :last_name,
         middle_name = :middle_name,
         department_id = :department_id,
         birth_year = :birth_year,
         hire_year = :hire_year,
         experience = :experience,
         position = :position,
         gender = :gender,
         city = :city
         WHERE id = :id");
        $stmt->execute([
            'id' => $id,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'middle_name' => $middle_name,
            'department_id' => $department_id,
            'birth_year' => $birth_year,
            'hire_year' => $hire_year,
            'experience' => $experience,
            'position' => $position,
            'gender' => $gender,
            'city' => $city,
        ]);

        $_SESSION['message'] = "Запись с ID $id успешно обновлена.";
        header("Location: teachers.php"); // Перезагрузка страницы
        exit();
    }

    if (isset($_POST['delete_id'])) {
        $deleteId = $_POST['delete_id'];

        $stmt = $pdo->prepare('DELETE FROM teachers WHERE id = :id');
        $stmt->execute(['id' => $deleteId]);
        $_SESSION['message'] = "Строка с ID $deleteId удалена.";

        header("Location: teachers.php"); // Перенаправляем на ту же страницу
        exit();
    }

    if (isset($_POST['new_recording'])) {
        $first_name = $_POST['new_first_name'];
        $last_name = $_POST['new_last_name'];
        $middle_name = $_POST['new_middle_name'];
        $department_id = $_POST['new_department_id'];
        $birth_year = $_POST['new_birth_year'];
        $hire_year = $_POST['new_hire_year'];
        $experience = $_POST['new_experience'];
        $position = $_POST['new_position'];
        $gender = $_POST['new_gender'];
        $city = $_POST['new_city'];
        $login = $_POST['new_login'];
        $password = $_POST['new_password'];

        $stmt = $pdo->prepare("INSERT INTO teachers 
        (first_name, last_name, middle_name, department_id, birth_year, hire_year, experience, position, gender, city, login, password) VALUES 
        (:first_name, :last_name, :middle_name, :department_id, :birth_year, :hire_year, :experience, :position, :gender, :city, :login, :password)");
        $stmt->execute([
            'first_name' => $first_name,
            'last_name' => $last_name,
            'middle_name' => $middle_name,
            'department_id' => $department_id,
            'birth_year' => $birth_year,
            'hire_year' => $hire_year,
            'experience' => $experience,
            'position' => $position,
            'gender' => $gender,
            'city' => $city,
            'login' => $login,
            'password' => $password
        ]);

        $_SESSION['message'] = "Добавлен преподаватель $first_name.";
        header("Location: teachers.php"); // Перезагрузка страницы
        exit();
    }

    $message = '';
    if (isset($_SESSION['message'])) {
        $message = $_SESSION['message'];
        unset($_SESSION['message']); // Удаляем сообщение после вывода
    }

    #Если в строке поиска что-то есть, то таблица выводится согласно этому
    $searchQuery = '';
    if (isset($_GET['search'])) {
        $searchQuery = trim($_GET['search']);
        $stmt = $pdo->prepare('SELECT * FROM teachers WHERE 
        first_name LIKE :query OR 
        last_name LIKE :query OR 
        middle_name LIKE :query OR 
        position LIKE :query OR
        city LIKE :query');
        $stmt->execute(['query' => '%' . $searchQuery . '%']);
    } else {
        $stmt = $pdo->query('SELECT * FROM teachers');
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
            <form method="get" action="teachers.php">
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
                <th>Имя</th>
                <th>Фамилия</th>
                <th>Отчество</th>
                <th>Кафедра</th>
                <th>Год рождения</th>
                <th>Год найма</th>
                <th>Стаж</th>
                <th>Должность</th>
                <th>Пол</th>
                <th>Город</th>
            </tr>
            <?php while ($row = $stmt->fetch()): ?>
                <tr onclick="document.getElementById('delete_id').value='<?= $row['id'] ?>'; document.getElementById('delete_button').disabled=false;">
                    <td><?= $row['first_name'] ?></td>
                    <td><?= $row['last_name'] ?></td>
                    <td><?= $row['middle_name'] ?></td>
                    <td><?= $row['department_id'] ?></td>
                    <td><?= $row['birth_year'] ?></td>
                    <td><?= $row['hire_year'] ?></td>
                    <td><?= $row['experience'] ?></td>
                    <td><?= $row['position'] ?></td>
                    <td><?= $row['gender'] ?></td>
                    <td><?= $row['city'] ?></td>
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
                <h2>Редактирование информации о преподавателе</h2>
                <form method="post">
                    <input type="hidden" name="update_id" value="<?= $row['id'] ?>">
                    <label>
                        Имя:
                        <input type="text" name="first_name" value="<?= htmlspecialchars($row['first_name']) ?>" required>
                    </label>
                    <br>
                    <label>
                        Фамилия:
                        <input type="text" name="last_name" value="<?= htmlspecialchars($row['last_name']) ?>" required>
                    </label>
                    <br>
                    <label>
                        Отчество:
                        <input type="text" name="middle_name" value="<?= htmlspecialchars($row['middle_name']) ?>" required>
                    </label>
                    <br>
                    <label>
                        Кафедра:
                        <input type="text" name="department_id" value="<?= htmlspecialchars($row['department_id']) ?>" required>
                    </label>
                    <br>
                    <label>
                        Год рождения:
                        <input type="text" name="birth_year" value="<?= htmlspecialchars($row['birth_year']) ?>" required>
                    </label>
                    <br>
                    <label>
                        Год найма:
                        <input type="text" name="hire_year" value="<?= htmlspecialchars($row['hire_year']) ?>" required>
                    </label>
                    <br>
                    <label>
                        Стаж:
                        <input type="text" name="experience" value="<?= htmlspecialchars($row['experience']) ?>" required>
                    </label>
                    <br>
                    <label>
                        Должность:
                        <input type="text" name="position" value="<?= htmlspecialchars($row['position']) ?>" required>
                    </label>
                    <br>
                    <label>
                        Пол:
                        <input type="text" name="gender" value="<?= htmlspecialchars($row['gender']) ?>" required>
                    </label>
                    <br>
                    <label>
                        Город:
                        <input type="text" name="city" value="<?= htmlspecialchars($row['city']) ?>" required>
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
            <h2>Добавление нового преподавателя</h2>
            <form method="post">
                <input type="hidden" name="new_recording" value="gcfgxfxjgdj">
                <label>
                    Имя:
                    <input type="text" name="new_first_name" required>
                </label>
                <br>
                <label>
                    Фамилия:
                    <input type="text" name="new_last_name" required>
                </label>
                <br>
                <label>
                    Отчество:
                    <input type="text" name="new_middle_name" required>
                </label>
                <br>
                <label>
                    Кафедра:
                    <input type="text" name="new_department_id" required>
                </label>
                <br>
                <label>
                    Год рождения:
                    <input type="text" name="new_birth_year" required>
                </label>
                <br>
                <label>
                    Год найма:
                    <input type="text" name="new_hire_year" required>
                </label>
                <br>
                <label>
                    Стаж:
                    <input type="text" name="new_experience" required>
                </label>
                <br>
                <label>
                    Должность:
                    <input type="text" name="new_position" required>
                </label>
                <br>
                <label>
                    Пол:
                    <input type="text" name="new_gender"" required>
            </label>
            <br>
            <label>
                Город:
                <input type=" text" name="new_city" required>
                </label>
                <br>
                <label>
                    Логин:
                    <input type="text" name="new_login" required>
                </label>
                <br>
                <label>
                    Пароль:
                    <input type="text" name="new_password" required>
                </label>
                <br>
                <button type="submit">Сохранить</button>
            </form>
        </div>
    </div>
</body>

</html>