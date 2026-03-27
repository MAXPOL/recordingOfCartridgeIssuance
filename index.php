<?php
session_start();

// Простая авторизация
$password = '123';
$error = '';

if (isset($_POST['login'])) {
    if ($_POST['password'] == $password) {
        $_SESSION['auth'] = true;
    } else {
        $error = 'Неверный пароль';
    }
}

if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}

if (!isset($_SESSION['auth']) || !$_SESSION['auth']) {
    ?>
    <!DOCTYPE html>
    <html lang="ru">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Авторизация - Учет картриджей</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body class="bg-light">
        <div class="container mt-5">
            <div class="row justify-content-center">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h4 class="mb-0">Вход в систему</h4>
                        </div>
                        <div class="card-body">
                            <?php if ($error): ?>
                                <div class="alert alert-danger"><?= $error ?></div>
                            <?php endif; ?>
                            <form method="POST">
                                <div class="mb-3">
                                    <label for="password" class="form-label">Пароль:</label>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </div>
                                <button type="submit" name="login" class="btn btn-primary w-100">Войти</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Инициализация файлов данных
$employees_file = 'employees.json';
$cartridges_file = 'cartridges.json';
$records_file = 'records.json';
$years_file = 'years.json';
$settings_file = 'settings.json';

// Создание файлов если не существуют
if (!file_exists($employees_file)) file_put_contents($employees_file, json_encode([]));
if (!file_exists($cartridges_file)) file_put_contents($cartridges_file, json_encode([]));
if (!file_exists($records_file)) file_put_contents($records_file, json_encode([]));
if (!file_exists($years_file)) {
    $current_year = date('Y');
    file_put_contents($years_file, json_encode([$current_year, $current_year + 1]));
}
if (!file_exists($settings_file)) {
    $settings = ['default_year' => date('Y')];
    file_put_contents($settings_file, json_encode($settings));
}

// Загрузка данных
$employees = json_decode(file_get_contents($employees_file), true);
if (!is_array($employees)) $employees = [];

$cartridges = json_decode(file_get_contents($cartridges_file), true);
if (!is_array($cartridges)) $cartridges = [];

$records = json_decode(file_get_contents($records_file), true);
if (!is_array($records)) $records = [];

$years = json_decode(file_get_contents($years_file), true);
if (!is_array($years)) $years = [date('Y'), date('Y') + 1];

$settings = json_decode(file_get_contents($settings_file), true);
if (!is_array($settings)) $settings = ['default_year' => date('Y')];

// Обработка сохранения настроек
if (isset($_POST['save_settings'])) {
    $settings['default_year'] = (int)$_POST['default_year'];
    file_put_contents($settings_file, json_encode($settings));
    header('Location: index.php?tab=settings');
    exit;
}

// Обработка добавления года
if (isset($_POST['add_year'])) {
    $new_year = (int)$_POST['year'];
    if (!in_array($new_year, $years)) {
        $years[] = $new_year;
        sort($years);
        file_put_contents($years_file, json_encode($years));
    }
    header('Location: index.php?tab=years');
    exit;
}

// Обработка удаления года
if (isset($_GET['delete_year'])) {
    $year_to_delete = (int)$_GET['delete_year'];
    $has_records = false;
    foreach ($records as $record) {
        if ($record['year'] == $year_to_delete) {
            $has_records = true;
            break;
        }
    }
    
    if (!$has_records) {
        $new_years = array();
        foreach ($years as $y) {
            if ($y != $year_to_delete) {
                $new_years[] = $y;
            }
        }
        $years = $new_years;
        sort($years);
        file_put_contents($years_file, json_encode($years));
    }
    header('Location: index.php?tab=years' . ($has_records ? '&error=has_records' : ''));
    exit;
}

// Обработка добавления сотрудника
if (isset($_POST['add_employee'])) {
    $new_employee = array(
        'id' => uniqid(),
        'fio' => $_POST['fio']
    );
    $employees[] = $new_employee;
    file_put_contents($employees_file, json_encode($employees, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    header('Location: index.php?tab=employees');
    exit;
}

// Обработка удаления сотрудника
if (isset($_GET['delete_employee'])) {
    $new_employees = array();
    foreach ($employees as $e) {
        if ($e['id'] != $_GET['delete_employee']) {
            $new_employees[] = $e;
        }
    }
    $employees = $new_employees;
    file_put_contents($employees_file, json_encode($employees, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    header('Location: index.php?tab=employees');
    exit;
}

// Обработка добавления картриджа
if (isset($_POST['add_cartridge'])) {
    $new_cartridge = array(
        'id' => uniqid(),
        'name' => $_POST['name']
    );
    $cartridges[] = $new_cartridge;
    file_put_contents($cartridges_file, json_encode($cartridges, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    header('Location: index.php?tab=cartridges');
    exit;
}

// Обработка удаления картриджа
if (isset($_GET['delete_cartridge'])) {
    $new_cartridges = array();
    foreach ($cartridges as $c) {
        if ($c['id'] != $_GET['delete_cartridge']) {
            $new_cartridges[] = $c;
        }
    }
    $cartridges = $new_cartridges;
    file_put_contents($cartridges_file, json_encode($cartridges, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    header('Location: index.php?tab=cartridges');
    exit;
}

// Обработка выдачи картриджа
if (isset($_POST['add_record'])) {
    $new_record = array(
        'id' => uniqid(),
        'employee_id' => $_POST['employee_id'],
        'cartridge_id' => $_POST['cartridge_id'],
        'quantity' => (int)$_POST['quantity'],
        'date' => $_POST['date']
    );
    
    $date_parts = explode('-', $_POST['date']);
    $new_record['year'] = $date_parts[0];
    $new_record['month'] = $date_parts[1];
    
    $records[] = $new_record;
    file_put_contents($records_file, json_encode($records, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    header('Location: index.php?tab=records');
    exit;
}

// Обработка удаления записи
if (isset($_GET['delete_record'])) {
    $new_records = array();
    foreach ($records as $r) {
        if ($r['id'] != $_GET['delete_record']) {
            $new_records[] = $r;
        }
    }
    $records = $new_records;
    file_put_contents($records_file, json_encode($records, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    header('Location: index.php?tab=records');
    exit;
}

// Текущая вкладка
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'employees';

// Получаем доступные года
$years_from_records = array();
foreach ($records as $record) {
    $years_from_records[$record['year']] = true;
}
$all_years = array_unique(array_merge($years, array_keys($years_from_records)));
sort($all_years);

// Выбор года по умолчанию из настроек
$selected_year = isset($_GET['year']) ? $_GET['year'] : $settings['default_year'];
$selected_month = isset($_GET['month']) ? $_GET['month'] : date('m');

// Функция для получения имени сотрудника по ID
function getEmployeeName($employees, $id) {
    foreach ($employees as $e) {
        if ($e['id'] == $id) return $e['fio'];
    }
    return 'Неизвестно';
}

// Функция для получения названия картриджа по ID
function getCartridgeName($cartridges, $id) {
    foreach ($cartridges as $c) {
        if ($c['id'] == $id) return $c['name'];
    }
    return 'Неизвестно';
}

// Отчеты
$report_by_cartridge_month = array();
$report_by_employee_month = array();
$report_by_cartridge_year = array();
$report_by_employee_year = array();
$report_detailed_month = array();
$report_detailed_year = array();

foreach ($records as $record) {
    $cartridge_name = getCartridgeName($cartridges, $record['cartridge_id']);
    $employee_name = getEmployeeName($employees, $record['employee_id']);
    $key = $employee_name . '||' . $cartridge_name;
    
    if ($record['year'] == $selected_year) {
        if ($record['month'] == $selected_month) {
            if (!isset($report_by_cartridge_month[$cartridge_name])) {
                $report_by_cartridge_month[$cartridge_name] = 0;
            }
            $report_by_cartridge_month[$cartridge_name] += $record['quantity'];
            
            if (!isset($report_by_employee_month[$employee_name])) {
                $report_by_employee_month[$employee_name] = 0;
            }
            $report_by_employee_month[$employee_name] += $record['quantity'];
            
            if (!isset($report_detailed_month[$key])) {
                $report_detailed_month[$key] = array(
                    'employee' => $employee_name,
                    'cartridge' => $cartridge_name,
                    'quantity' => 0
                );
            }
            $report_detailed_month[$key]['quantity'] += $record['quantity'];
        }
        
        if (!isset($report_by_cartridge_year[$cartridge_name])) {
            $report_by_cartridge_year[$cartridge_name] = 0;
        }
        $report_by_cartridge_year[$cartridge_name] += $record['quantity'];
        
        if (!isset($report_by_employee_year[$employee_name])) {
            $report_by_employee_year[$employee_name] = 0;
        }
        $report_by_employee_year[$employee_name] += $record['quantity'];
        
        if (!isset($report_detailed_year[$key])) {
            $report_detailed_year[$key] = array(
                'employee' => $employee_name,
                'cartridge' => $cartridge_name,
                'quantity' => 0
            );
        }
        $report_detailed_year[$key]['quantity'] += $record['quantity'];
    }
}

// Сортировка отчетов
arsort($report_by_cartridge_month);
arsort($report_by_employee_month);
arsort($report_by_cartridge_year);
arsort($report_by_employee_year);

// Сортировка детальных отчетов
uasort($report_detailed_month, function($a, $b) {
    return $b['quantity'] - $a['quantity'];
});
uasort($report_detailed_year, function($a, $b) {
    return $b['quantity'] - $a['quantity'];
});

// Названия месяцев
$months = array(
    '01' => 'Январь', '02' => 'Февраль', '03' => 'Март',
    '04' => 'Апрель', '05' => 'Май', '06' => 'Июнь',
    '07' => 'Июль', '08' => 'Август', '09' => 'Сентябрь',
    '10' => 'Октябрь', '11' => 'Ноябрь', '12' => 'Декабрь'
);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Учет картриджей</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <style>
        body { background-color: #f8f9fa; }
        .navbar { background-color: #212529 !important; }
        .nav-tabs .nav-link { color: #495057; font-weight: 500; }
        .nav-tabs .nav-link.active { background-color: #fff; border-bottom-color: transparent; }
        .card { border-radius: 0.5rem; box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075); }
        .table th { background-color: #f1f3f5; }
        .badge { font-size: 0.9rem; }
        .btn-sm { margin: 0 2px; }
        .print-only { display: none; }
        .year-badge {
            font-size: 1.2rem;
            padding: 0.5rem 1rem;
            margin: 0.25rem;
            display: inline-block;
        }
        .settings-card {
            border-left: 4px solid #ffc107;
        }
        @media print {
            .no-print { display: none !important; }
            .print-only { display: block !important; }
            .card { box-shadow: none; border: 1px solid #ddd; }
            .table th { background-color: #eee !important; }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark mb-4 no-print">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1">
                <i class="bi bi-printer-fill me-2"></i>
                Система учета картриджей
            </span>
            <a href="?logout=1" class="btn btn-outline-light btn-sm">
                <i class="bi bi-box-arrow-right me-1"></i>Выйти
            </a>
        </div>
    </nav>

    <div class="container-fluid px-4">
        <!-- Навигация по вкладкам -->
        <ul class="nav nav-tabs mb-4 no-print">
            <li class="nav-item">
                <a class="nav-link <?= $tab == 'employees' ? 'active' : '' ?>" href="?tab=employees">
                    <i class="bi bi-people-fill me-1"></i>Сотрудники
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $tab == 'cartridges' ? 'active' : '' ?>" href="?tab=cartridges">
                    <i class="bi bi-box-seam me-1"></i>Картриджи
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $tab == 'records' ? 'active' : '' ?>" href="?tab=records">
                    <i class="bi bi-journal-plus me-1"></i>Выдача
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $tab == 'reports' ? 'active' : '' ?>" href="?tab=reports">
                    <i class="bi bi-bar-chart-fill me-1"></i>Отчеты
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $tab == 'years' ? 'active' : '' ?>" href="?tab=years">
                    <i class="bi bi-calendar-fill me-1"></i>Года
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $tab == 'settings' ? 'active' : '' ?>" href="?tab=settings">
                    <i class="bi bi-gear-fill me-1"></i>Настройки
                </a>
            </li>
        </ul>

        <!-- Вкладка: Сотрудники -->
        <?php if ($tab == 'employees'): ?>
            <div class="row no-print">
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <i class="bi bi-person-plus-fill me-1"></i>Добавить сотрудника
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="mb-3">
                                    <label for="fio" class="form-label">ФИО сотрудника:</label>
                                    <input type="text" class="form-control" id="fio" name="fio" required>
                                </div>
                                <button type="submit" name="add_employee" class="btn btn-primary w-100">
                                    <i class="bi bi-plus-circle me-1"></i>Добавить
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header bg-dark text-white">
                            <i class="bi bi-list-ul me-1"></i>Список сотрудников
                        </div>
                        <div class="card-body">
                            <?php if (empty($employees)): ?>
                                <p class="text-muted text-center">Нет добавленных сотрудников</p>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>ФИО</th>
                                                <th class="no-print">Действия</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($employees as $index => $employee): ?>
                                                <tr>
                                                    <td><?= $index + 1 ?></td>
                                                    <td><?= htmlspecialchars($employee['fio']) ?></td>
                                                    <td class="no-print">
                                                        <a href="?delete_employee=<?= $employee['id'] ?>" 
                                                           class="btn btn-sm btn-outline-danger" 
                                                           onclick="return confirm('Удалить сотрудника?')">
                                                            <i class="bi bi-trash"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Вкладка: Картриджи -->
        <?php if ($tab == 'cartridges'): ?>
            <div class="row no-print">
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <div class="card-header bg-success text-white">
                            <i class="bi bi-box-seam me-1"></i>Добавить картридж
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Наименование картриджа:</label>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                </div>
                                <button type="submit" name="add_cartridge" class="btn btn-success w-100">
                                    <i class="bi bi-plus-circle me-1"></i>Добавить
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header bg-dark text-white">
                            <i class="bi bi-list-ul me-1"></i>Список картриджей
                        </div>
                        <div class="card-body">
                            <?php if (empty($cartridges)): ?>
                                <p class="text-muted text-center">Нет добавленных картриджей</p>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Наименование</th>
                                                <th class="no-print">Действия</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($cartridges as $index => $cartridge): ?>
                                                <tr>
                                                    <td><?= $index + 1 ?></td>
                                                    <td><?= htmlspecialchars($cartridge['name']) ?></td>
                                                    <td class="no-print">
                                                        <a href="?delete_cartridge=<?= $cartridge['id'] ?>" 
                                                           class="btn btn-sm btn-outline-danger"
                                                           onclick="return confirm('Удалить картридж?')">
                                                            <i class="bi bi-trash"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Вкладка: Выдача картриджей -->
        <?php if ($tab == 'records'): ?>
            <div class="row no-print">
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <div class="card-header bg-info text-white">
                            <i class="bi bi-journal-plus me-1"></i>Выдать картридж
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="mb-3">
                                    <label for="employee_id" class="form-label">Сотрудник:</label>
                                    <select class="form-select" id="employee_id" name="employee_id" required>
                                        <option value="">Выберите сотрудника</option>
                                        <?php foreach ($employees as $employee): ?>
                                            <option value="<?= $employee['id'] ?>"><?= htmlspecialchars($employee['fio']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="cartridge_id" class="form-label">Картридж:</label>
                                    <select class="form-select" id="cartridge_id" name="cartridge_id" required>
                                        <option value="">Выберите картридж</option>
                                        <?php foreach ($cartridges as $cartridge): ?>
                                            <option value="<?= $cartridge['id'] ?>"><?= htmlspecialchars($cartridge['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="quantity" class="form-label">Количество:</label>
                                    <input type="number" class="form-control" id="quantity" name="quantity" min="1" value="1" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="date" class="form-label">Дата выдачи:</label>
                                    <input type="date" class="form-control" id="date" name="date" value="<?= date('Y-m-d') ?>" required>
                                </div>
                                
                                <button type="submit" name="add_record" class="btn btn-info w-100" 
                                        <?= (empty($employees) || empty($cartridges)) ? 'disabled' : '' ?>>
                                    <i class="bi bi-check-circle me-1"></i>Записать выдачу
                                </button>
                                
                                <?php if (empty($employees) || empty($cartridges)): ?>
                                    <div class="mt-2 text-warning small">
                                        <i class="bi bi-exclamation-triangle-fill me-1"></i>
                                        Необходимо добавить сотрудников и картриджи
                                    </div>
                                <?php endif; ?>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header bg-dark text-white">
                            <i class="bi bi-clock-history me-1"></i>История выдач
                        </div>
                        <div class="card-body">
                            <?php if (empty($records)): ?>
                                <p class="text-muted text-center">Нет записей о выдаче</p>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Дата</th>
                                                <th>Сотрудник</th>
                                                <th>Картридж</th>
                                                <th>Кол-во</th>
                                                <th class="no-print">Действия</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            $sorted_records = array_reverse($records);
                                            foreach ($sorted_records as $record): 
                                            ?>
                                                <tr>
                                                    <td><?= date('d.m.Y', strtotime($record['date'])) ?></td>
                                                    <td><?= htmlspecialchars(getEmployeeName($employees, $record['employee_id'])) ?></td>
                                                    <td><?= htmlspecialchars(getCartridgeName($cartridges, $record['cartridge_id'])) ?></td>
                                                    <td><span class="badge bg-primary"><?= $record['quantity'] ?></span></td>
                                                    <td class="no-print">
                                                        <a href="?delete_record=<?= $record['id'] ?>" 
                                                           class="btn btn-sm btn-outline-danger"
                                                           onclick="return confirm('Удалить запись?')">
                                                            <i class="bi bi-trash"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Вкладка: Настройки -->
        <?php if ($tab == 'settings'): ?>
            <div class="row">
                <div class="col-md-6 mx-auto">
                    <div class="card settings-card">
                        <div class="card-header bg-warning">
                            <i class="bi bi-gear-fill me-1"></i>Настройки системы
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="mb-4">
                                    <label for="default_year" class="form-label fw-bold">Год по умолчанию для отчетов:</label>
                                    <select class="form-select" id="default_year" name="default_year" required>
                                        <?php foreach ($all_years as $year): ?>
                                            <option value="<?= $year ?>" <?= $settings['default_year'] == $year ? 'selected' : '' ?>>
                                                <?= $year ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="form-text text-muted">
                                        <i class="bi bi-info-circle me-1"></i>
                                        Этот год будет автоматически выбран при открытии вкладки "Отчеты"
                                    </div>
                                </div>
                                
                                <div class="mb-4">
                                    <label class="form-label fw-bold">Текущие настройки:</label>
                                    <ul class="list-group">
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            Всего сотрудников
                                            <span class="badge bg-primary rounded-pill"><?= count($employees) ?></span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            Всего картриджей
                                            <span class="badge bg-success rounded-pill"><?= count($cartridges) ?></span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            Всего записей
                                            <span class="badge bg-info rounded-pill"><?= count($records) ?></span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            Доступно годов
                                            <span class="badge bg-secondary rounded-pill"><?= count($all_years) ?></span>
                                        </li>
                                    </ul>
                                </div>
                                
                                <button type="submit" name="save_settings" class="btn btn-warning w-100">
                                    <i class="bi bi-check-circle me-1"></i>Сохранить настройки
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Вкладка: Года -->
        <?php if ($tab == 'years'): ?>
            <div class="row no-print">
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <div class="card-header bg-secondary text-white">
                            <i class="bi bi-calendar-plus me-1"></i>Добавить год
                        </div>
                        <div class="card-body">
                            <?php if (isset($_GET['error']) && $_GET['error'] == 'has_records'): ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <i class="bi bi-exclamation-triangle-fill me-1"></i>
                                    Нельзя удалить год, в котором есть записи о выдаче!
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>
                            
                            <form method="POST">
                                <div class="mb-3">
                                    <label for="year" class="form-label">Год:</label>
                                    <input type="number" class="form-control" id="year" name="year" 
                                           min="2000" max="2100" value="<?= date('Y') + 1 ?>" required>
                                </div>
                                <button type="submit" name="add_year" class="btn btn-secondary w-100">
                                    <i class="bi bi-plus-circle me-1"></i>Добавить год
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header bg-dark text-white">
                            <i class="bi bi-calendar-range me-1"></i>Доступные года
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <span class="badge bg-info year-badge">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Всего: <?= count($years) ?> лет
                                </span>
                            </div>
                            
                            <?php if (empty($years)): ?>
                                <p class="text-muted text-center">Нет добавленных годов</p>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Год</th>
                                                <th>Статус</th>
                                                <th class="no-print">Действия</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($years as $index => $year): 
                                                $has_records = false;
                                                foreach ($records as $record) {
                                                    if ($record['year'] == $year) {
                                                        $has_records = true;
                                                        break;
                                                    }
                                                }
                                            ?>
                                                <tr>
                                                    <td><?= $index + 1 ?></td>
                                                    <td><strong><?= $year ?></strong></td>
                                                    <td>
                                                        <?php if ($has_records): ?>
                                                            <span class="badge bg-success">Есть записи</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-secondary">Нет записей</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="no-print">
                                                        <?php if (!$has_records): ?>
                                                            <a href="?delete_year=<?= $year ?>" 
                                                               class="btn btn-sm btn-outline-danger"
                                                               onclick="return confirm('Удалить год <?= $year ?>?')">
                                                                <i class="bi bi-trash"></i>
                                                            </a>
                                                        <?php else: ?>
                                                            <button class="btn btn-sm btn-outline-secondary" disabled 
                                                                    title="Нельзя удалить год с записями">
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Статистика по годам -->
            <div class="row mt-4 no-print">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header bg-info text-white">
                            <i class="bi bi-pie-chart me-1"></i>Статистика записей по годам
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <?php 
                                $yearly_stats = array();
                                foreach ($records as $record) {
                                    if (!isset($yearly_stats[$record['year']])) {
                                        $yearly_stats[$record['year']] = 0;
                                    }
                                    $yearly_stats[$record['year']] += $record['quantity'];
                                }
                                krsort($yearly_stats);
                                ?>
                                
                                <?php if (empty($yearly_stats)): ?>
                                    <p class="text-muted text-center">Нет записей о выдаче</p>
                                <?php else: ?>
                                    <?php foreach ($yearly_stats as $year => $total): ?>
                                        <div class="col-md-3 mb-3">
                                            <div class="card bg-light">
                                                <div class="card-body text-center">
                                                    <h3><?= $year ?></h3>
                                                    <span class="badge bg-primary" style="font-size: 1.2rem;">
                                                        <?= $total ?> шт.
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Вкладка: Отчеты -->
        <?php if ($tab == 'reports'): ?>
            <!-- Заголовок для печати -->
            <div class="print-only text-center mb-4">
                <h2>Отчет по движению картриджей</h2>
                <h4>Период: <?= $months[$selected_month] ?> <?= $selected_year ?> года</h4>
                <hr>
            </div>

            <!-- Фильтр и кнопки печати -->
            <div class="row mb-4 no-print">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header bg-warning">
                            <i class="bi bi-funnel-fill me-1"></i>Фильтр отчетов
                        </div>
                        <div class="card-body">
                            <form method="GET" class="row g-3">
                                <input type="hidden" name="tab" value="reports">
                                <div class="col-md-4">
                                    <label for="year" class="form-label">Год:</label>
                                    <select class="form-select" id="year" name="year">
                                        <?php foreach ($all_years as $year): ?>
                                            <option value="<?= $year ?>" <?= $selected_year == $year ? 'selected' : '' ?>><?= $year ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="month" class="form-label">Месяц:</label>
                                    <select class="form-select" id="month" name="month">
                                        <?php foreach ($months as $num => $name): ?>
                                            <option value="<?= $num ?>" <?= $selected_month == $num ? 'selected' : '' ?>><?= $name ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4 d-flex align-items-end">
                                    <button type="submit" class="btn btn-warning w-100">
                                        <i class="bi bi-search me-1"></i>Применить фильтр
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button onclick="window.print()" class="btn btn-secondary w-100">
                        <i class="bi bi-printer me-1"></i>Распечатать все отчеты
                    </button>
                </div>
            </div>

            <!-- Отчет за месяц по моделям -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                            <span><i class="bi bi-pie-chart-fill me-1"></i>За <?= $months[$selected_month] ?> <?= $selected_year ?> по моделям</span>
                            <button onclick="printReport('report1')" class="btn btn-sm btn-light no-print">
                                <i class="bi bi-printer"></i>
                            </button>
                        </div>
                        <div class="card-body" id="report1">
                            <?php if (empty($report_by_cartridge_month)): ?>
                                <p class="text-muted text-center">Нет данных за выбранный период</p>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Модель картриджа</th>
                                                <th class="text-end">Количество</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($report_by_cartridge_month as $name => $quantity): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($name) ?></td>
                                                    <td class="text-end"><span class="badge bg-primary"><?= $quantity ?></span></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Отчет за месяц по людям -->
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                            <span><i class="bi bi-person-badge-fill me-1"></i>За <?= $months[$selected_month] ?> <?= $selected_year ?> по сотрудникам</span>
                            <button onclick="printReport('report2')" class="btn btn-sm btn-light no-print">
                                <i class="bi bi-printer"></i>
                            </button>
                        </div>
                        <div class="card-body" id="report2">
                            <?php if (empty($report_by_employee_month)): ?>
                                <p class="text-muted text-center">Нет данных за выбранный период</p>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Сотрудник</th>
                                                <th class="text-end">Количество</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($report_by_employee_month as $name => $quantity): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($name) ?></td>
                                                    <td class="text-end"><span class="badge bg-success"><?= $quantity ?></span></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Детальный отчет за месяц (Человек + Картридж) -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                            <span><i class="bi bi-table me-1"></i>Детальный отчет: Сотрудник - Картридж (<?= $months[$selected_month] ?> <?= $selected_year ?>)</span>
                            <button onclick="printReport('report3')" class="btn btn-sm btn-light no-print">
                                <i class="bi bi-printer"></i>
                            </button>
                        </div>
                        <div class="card-body" id="report3">
                            <?php if (empty($report_detailed_month)): ?>
                                <p class="text-muted text-center">Нет данных за выбранный период</p>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-sm table-striped">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Сотрудник</th>
                                                <th>Картридж</th>
                                                <th class="text-end">Количество</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            $index = 1;
                                            foreach ($report_detailed_month as $item): 
                                            ?>
                                                <tr>
                                                    <td><?= $index++ ?></td>
                                                    <td><?= htmlspecialchars($item['employee']) ?></td>
                                                    <td><?= htmlspecialchars($item['cartridge']) ?></td>
                                                    <td class="text-end"><span class="badge bg-info"><?= $item['quantity'] ?></span></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Отчет за год по моделям -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                            <span><i class="bi bi-bar-chart-steps me-1"></i>За <?= $selected_year ?> год по моделям</span>
                            <button onclick="printReport('report4')" class="btn btn-sm btn-light no-print">
                                <i class="bi bi-printer"></i>
                            </button>
                        </div>
                        <div class="card-body" id="report4">
                            <?php if (empty($report_by_cartridge_year)): ?>
                                <p class="text-muted text-center">Нет данных за выбранный год</p>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Модель картриджа</th>
                                                <th class="text-end">Количество</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($report_by_cartridge_year as $name => $quantity): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($name) ?></td>
                                                    <td class="text-end"><span class="badge bg-primary"><?= $quantity ?></span></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Отчет за год по людям -->
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                            <span><i class="bi bi-people-fill me-1"></i>За <?= $selected_year ?> год по сотрудникам</span>
                            <button onclick="printReport('report5')" class="btn btn-sm btn-light no-print">
                                <i class="bi bi-printer"></i>
                            </button>
                        </div>
                        <div class="card-body" id="report5">
                            <?php if (empty($report_by_employee_year)): ?>
                                <p class="text-muted text-center">Нет данных за выбранный год</p>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Сотрудник</th>
                                                <th class="text-end">Количество</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($report_by_employee_year as $name => $quantity): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($name) ?></td>
                                                    <td class="text-end"><span class="badge bg-success"><?= $quantity ?></span></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Детальный отчет за год (Человек + Картридж) -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header bg-warning d-flex justify-content-between align-items-center">
                            <span><i class="bi bi-table me-1"></i>Детальный отчет: Сотрудник - Картридж (<?= $selected_year ?> год)</span>
                            <button onclick="printReport('report6')" class="btn btn-sm btn-dark no-print">
                                <i class="bi bi-printer"></i>
                            </button>
                        </div>
                        <div class="card-body" id="report6">
                            <?php if (empty($report_detailed_year)): ?>
                                <p class="text-muted text-center">Нет данных за выбранный год</p>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-sm table-striped">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Сотрудник</th>
                                                <th>Картридж</th>
                                                <th class="text-end">Количество</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            $index = 1;
                                            foreach ($report_detailed_year as $item): 
                                            ?>
                                                <tr>
                                                    <td><?= $index++ ?></td>
                                                    <td><?= htmlspecialchars($item['employee']) ?></td>
                                                    <td><?= htmlspecialchars($item['cartridge']) ?></td>
                                                    <td class="text-end"><span class="badge bg-warning"><?= $item['quantity'] ?></span></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Скрипт для печати отдельных отчетов -->
    <script>
        function printReport(reportId) {
            const reportContent = document.getElementById(reportId).innerHTML;
            const card = document.getElementById(reportId).closest('.card');
            const reportTitle = card.querySelector('.card-header span').innerText;
            
            const printWindow = window.open('', '_blank');
            printWindow.document.write(`
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Печать отчета</title>
                    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
                    <style>
                        body { padding: 20px; }
                        .badge { font-size: 0.9rem; }
                        @media print {
                            .no-print { display: none; }
                            button { display: none; }
                        }
                    </style>
                </head>
                <body>
                    <div class="container">
                        <h2 class="text-center mb-4">${reportTitle}</h2>
                        ${reportContent}
                        <div class="text-center mt-4 no-print">
                            <button onclick="window.print()" class="btn btn-primary">Печать</button>
                            <button onclick="window.close()" class="btn btn-secondary">Закрыть</button>
                        </div>
                    </div>
                </body>
                </html>
            `);
            printWindow.document.close();
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
