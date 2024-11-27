<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
        content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link href="../css/pagesStyles.css" rel="stylesheet">
    <title>Document</title>
</head>

<body>
    <?php
    require "../dbConnect.php";
    global $pdo;

    $selectedOption = isset($_POST['student_group']) ? $_POST['student_group'] : null;

    if (isset($_POST['student_group']) && $_POST['student_group'] !== '--Выберите группу--') {
        $group_name = $_POST['student_group'];

        $stmt = $pdo->prepare('SELECT * FROM workload WHERE group_name = :group_name');
        $stmt->execute([
            'group_name' => $group_name
        ]);
        $workload = $stmt->fetch();

        $stmt = $pdo->prepare('SELECT * FROM disciplines WHERE id = :id');
        $stmt->execute([
            'id' => $workload['discipline_id']
        ]);
        $disciplines = $stmt->fetch();

        $stmt = $pdo->prepare('SELECT * FROM teachers WHERE id = :id');
        $stmt->execute([
            'id' => $workload['teacher_id']
        ]);
        $teachers = $stmt->fetch();

        $stmt = $pdo->prepare('SELECT * FROM departments WHERE id = :id');
        $stmt->execute([
            'id' => $teachers['department_id']
        ]);
        $departments = $stmt->fetch();

        $stmt = $pdo->prepare('SELECT * FROM faculties WHERE id = :id');
        $stmt->execute([
            'id' => $departments['faculty_id']
        ]);
        $faculties = $stmt->fetch();

        //    echo '<pre>';
        //    print_r($faculties);
        //    print_r($departments);
        //    print_r($teachers);
        //    print_r($disciplines);
        //    print_r($workload);
        //    echo '</pre>';
    }

    $stmt = $pdo->query('SELECT * FROM workload');
    ?>
    <div class="main-window">
        <form method="post">
            <select name="student_group" onchange="this.form.submit()">
                <option>--Выберите группу--</option>
                <?php while ($row = $stmt->fetch()): ?>
                    <option <?php if ($selectedOption === $row['group_name']) echo 'selected' ?>>
                        <?= $row['group_name'] ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </form>
        <?php if (isset($_POST['student_group']) && $_POST['student_group'] !== '--Выберите группу--'): ?>
            <?= $faculties['name'] ?>
            <ul>
                <li>Декан: <?= $faculties['dean_full_name'] ?></li>
                <li>Телефон: <?= $faculties['phone'] ?></li>
                <li>Кабинет: <?= $faculties['room_number'] ?></li>
                <li>Здание: <?= $faculties['building_number'] ?> </li>
            </ul>
            <?= $departments['name'] ?>
            <ul>
                <li>Заведующий: <?= $departments['head_full_name'] ?></li>
                <li>Телефон: <?= $departments['phone'] ?> </li>
                <li>Кабинет: <?= $departments['room_number'] ?></li>
                <li>Здание: <?= $departments['building_number'] ?></li>
            </ul>
            Основной предмет: <?= $disciplines['name'] ?>
            <ul>
                <li>
                    Преподаватель:
                    <?= $teachers['last_name'] ?>
                    <?= $teachers['first_name'] ?>
                    <?= $teachers['middle_name'] ?>
                </li>
                <li>Количество часов: <?= $disciplines['hours'] ?></li>
                <li>Цикл дисциплины: <?= $disciplines['cycle'] ?></li>
                <li>Учебный год: <?= $workload['academic_year'] ?></li>
                <li>Семестр: <?= $workload['semester'] ?>-й</li>
                <li>Вид итогового контроля: <?= $workload['final_control_type'] ?></li>
            </ul>


        <?php else: ?>
            <p>Выберите группу для того чтобы увидеть подробную информацию</p>
        <?php endif; ?>
    </div>
    <a href="../logout.php">Выйти</a>
</body>

</html>