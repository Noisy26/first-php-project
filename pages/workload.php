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
        list($last_name, $first_name, $middle_name) = explode(" ", $_POST['teacher']);

        $stmt = $pdo->prepare('SELECT id FROM teachers WHERE 
        first_name = :first_name AND
        last_name = :last_name AND
        middle_name = :middle_name');
        $stmt->execute([
            'first_name' => $first_name,
            'last_name' => $last_name,
            'middle_name' => $middle_name
        ]);
        $res = $stmt->fetch();

        $stmt = $pdo->prepare('SELECT id FROM disciplines WHERE name = :name');
        $stmt->execute([
            'name' => $_POST['discipline']
        ]);
        $res2 = $stmt->fetch();

        $id = $_POST['update_id'];
        $teacher_id = $res['id'];
        $discipline_id = $res2['id'];
        $academic_year = $_POST['academic_year'];
        $semester = $_POST['semester'];
        $group_name = $_POST['group_name'];
        $student_count = $_POST['student_count'];
        $final_control_type = $_POST['final_control_type'];

        $stmt = $pdo->prepare("UPDATE workload SET 
        teacher_id = :teacher_id, 
        discipline_id = :discipline_id, 
        academic_year = :academic_year, 
        semester = :semester, 
        group_name = :group_name,
        student_count = :student_count,
        final_control_type = :final_control_type
        WHERE id = :id");
        $stmt->execute([
            'id' => $id,
            'teacher_id' => $teacher_id,
            'discipline_id' => $discipline_id,
            'academic_year' => $academic_year,
            'semester' => $semester,
            'group_name' => $group_name,
            'student_count' => $student_count,
            'final_control_type' => $final_control_type
        ]);

        $_SESSION['message'] = "Запись с ID $id успешно обновлена.";
        header("Location: workload.php"); // Перезагрузка страницы
        exit();
    }

    if (isset($_POST['delete_id'])) {
        $deleteId = $_POST['delete_id'];

        $stmt = $pdo->prepare('DELETE FROM workload WHERE id = :id');
        $stmt->execute(['id' => $deleteId]);
        $_SESSION['message'] = "Строка с ID $deleteId удалена.";

        header("Location: workload.php"); // Перенаправляем на ту же страницу
        exit();
    }

    if (isset($_POST['new_recording'])) {

        list($last_name, $first_name, $middle_name) = explode(" ", $_POST['new_teacher']);
        $disciplineName = $_POST['new_discipline'];

        $stmt = $pdo->prepare('SELECT id FROM teachers WHERE 
        first_name = :first_name AND
        last_name = :last_name AND
        middle_name = :middle_name');
        $stmt->execute([
            'first_name' => $first_name,
            'last_name' => $last_name,
            'middle_name' => $middle_name
        ]);
        $res = $stmt->fetch();

        $stmt = $pdo->prepare('SELECT id FROM disciplines WHERE name = :name');
        $stmt->execute([
            'name' => $disciplineName
        ]);
        $res2 = $stmt->fetch();

        $teacher_id = $res['id'];
        $discipline_id = $res2['id'];
        $academic_year = $_POST['new_academic_year'];
        $semester = $_POST['new_semester'];
        $group_name = $_POST['new_group_name'];
        $student_count = $_POST['new_student_count'];
        $final_control_type = $_POST['new_final_control_type'];

        $stmt = $pdo->prepare("INSERT INTO workload (teacher_id, discipline_id, academic_year, semester, group_name, student_count, final_control_type) VALUES 
        (:new_teacher_id, :new_discipline_id, :new_academic_year, :new_semester, :new_group_name, :new_student_count, :new_final_control_type)");
        $stmt->execute([
            'new_teacher_id' => $teacher_id,
            'new_discipline_id' => $discipline_id,
            'new_academic_year' => $academic_year,
            'new_semester' => $semester,
            'new_group_name' => $group_name,
            'new_student_count' => $student_count,
            'new_final_control_type' => $final_control_type
        ]);

        $_SESSION['message'] = 'Добавлена рабочая нагрузка.';
        header("Location: workload.php"); // Перезагрузка страницы
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
        $stmt = $pdo->prepare('SELECT * FROM workload WHERE group_name LIKE :query OR final_control_type LIKE :query OR academic_year LIKE :query');
        $stmt->execute(['query' => '%' . $searchQuery . '%']);
    } else {
        $stmt = $pdo->query('SELECT * FROM workload');
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
            <form method="get" action="workload.php">
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
                <th>Преподаватель</th>
                <th>Дисциплина</th>
                <th>Учебный год</th>
                <th>Семестр</th>
                <th>Группа</th>
                <th>Кол-во студентов</th>
                <th>Вид итогового контроля</th>
            </tr>
            <?php while ($row = $stmt->fetch()): ?>
                <tr onclick="document.getElementById('delete_id').value='<?= $row['id'] ?>'; document.getElementById('delete_button').disabled=false;">
                    <td>
                        <?php
                        $stmt1 = $pdo->prepare('SELECT * FROM teachers WHERE id = :id');
                        $stmt1->execute([
                            'id' => $row['teacher_id']
                        ]);
                        $res = $stmt1->fetch();

                        echo $res['last_name'] . ' ' . $res['first_name'] . ' ' . $res['middle_name'];
                        ?>
                    </td>
                    <td>
                        <?php
                        $stmt1 = $pdo->prepare('SELECT * FROM disciplines WHERE id = :id');
                        $stmt1->execute([
                            'id' => $row['discipline_id']
                        ]);
                        $res = $stmt1->fetch();

                        echo $res['name'];
                        ?>
                    </td>
                    <td><?= $row['academic_year'] ?></td>
                    <td><?= $row['semester'] ?></td>
                    <td><?= $row['group_name'] ?></td>
                    <td><?= $row['student_count'] ?></td>
                    <td><?= $row['final_control_type'] ?></td>
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
                <h2>Редактирование рабочей нагрузки</h2>
                <form method="post">
                    <input type="hidden" name="update_id" value="<?= $row['id'] ?>">
                    <label>
                        Преподаватель:
                        <select name="teacher">
                            <?php
                            $teachers = $pdo->query('SELECT * FROM teachers');
                            while ($name = $teachers->fetch()):
                            ?>
                                <option>
                                    <?= $name['last_name'] . ' ' . $name['first_name'] . ' ' . $name['middle_name'] ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </label>
                    <br>
                    <label>
                        Дисциплина:
                        <select name="discipline">
                            <?php
                            $disciplines = $pdo->query('SELECT * FROM disciplines');
                            while ($name = $disciplines->fetch()):
                            ?>
                                <option>
                                    <?= $name['name'] ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </label>
                    <br>
                    <label>
                        Учебный год:
                        <input type="text" name="academic_year" value="<?= htmlspecialchars($row['academic_year']) ?>" required>
                    </label>
                    <br>
                    <label>
                        Семестр:
                        <input type="text" name="semester" value="<?= htmlspecialchars($row['semester']) ?>" required>
                    </label>
                    <br>
                    <label>
                        Группа:
                        <input type="text" name="group_name" value="<?= htmlspecialchars($row['group_name']) ?>" required>
                    </label>
                    <br>
                    <label>
                        Количество студентов:
                        <input type="text" name="student_count" value="<?= htmlspecialchars($row['student_count']) ?>" required>
                    </label>
                    <br>
                    <label>
                        Вид итогового контроля:
                        <input type="text" name="final_control_type" value="<?= htmlspecialchars($row['final_control_type']) ?>" required>
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
                    Преподаватель:
                    <select name="new_teacher">
                        <?php
                        $teachers = $pdo->query('SELECT * FROM teachers');
                        while ($name = $teachers->fetch()):
                        ?>
                            <option>
                                <?= $name['last_name'] . ' ' . $name['first_name'] . ' ' . $name['middle_name'] ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </label>
                <br>
                <label>
                    Дисциплина:
                    <select name="new_discipline">
                        <?php
                        $disciplines = $pdo->query('SELECT * FROM disciplines');
                        while ($name = $disciplines->fetch()):
                        ?>
                            <option>
                                <?= $name['name'] ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </label>
                <br>
                <label>
                    Учебный год:
                    <input type="text" name="new_academic_year" required>
                </label>
                <br>
                <label>
                    Семестр:
                    <input type="text" name="new_semester" required>
                </label>
                <br>
                <label>
                    Группа:
                    <input type="text" name="new_group_name" required>
                </label>
                <br>
                <label>
                    Количество студентов:
                    <input type="text" name="new_student_count" required>
                </label>
                <br>
                <label>
                    Вид итогового контроля:
                    <input type="text" name="new_final_control_type" required>
                </label>
                <br>
                <button type="submit">Сохранить</button>
            </form>
        </div>
    </div>
</body>

</html>