<?php

session_start();
if (!isset($_SESSION['tasks'])) {
    $_SESSION['tasks'] = [];
}

// Maneja las acciones (Agregar, Completar, Cambiar Prioridad, Modificar, Eliminar, Filtrar)
$filter = 'all'; // Filtro predeterminado (mostrar todo)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];

    if ($action === 'add') {
        $name = $_POST['taskName'];
        if (!empty($name)) {
            $_SESSION['tasks'][] = [
                'name' => $name,
                'completed' => false,
                'priority' => 'media' // Prioridad predeterminada
            ];
        }
    } elseif ($action === 'delete') {
        $index = $_POST['index'];
        unset($_SESSION['tasks'][$index]);
        $_SESSION['tasks'] = array_values($_SESSION['tasks']); // Reindexa el array
    } elseif ($action === 'toggleComplete') {
        $index = $_POST['index'];
        $_SESSION['tasks'][$index]['completed'] = !$_SESSION['tasks'][$index]['completed'];
    } elseif ($action === 'edit') {
        $index = $_POST['index'];
        $name = $_POST['taskName'];
        $_SESSION['tasks'][$index]['name'] = $name;
    } elseif ($action === 'changePriority') {
        $index = $_POST['index'];
        $priority = $_POST['priority'];
        $_SESSION['tasks'][$index]['priority'] = $priority;
    } elseif ($action === 'filter') {
        $filter = $_POST['filter'];
    }
}

// Función para filtrar las tareas
function filterTasks($tasks, $filter) {
    if ($filter === 'all') {
        return $tasks;
    } elseif ($filter === 'completada') {
        return array_filter($tasks, fn($task) => $task['completed']);
    } elseif ($filter === 'pendiente') {
        return array_filter($tasks, fn($task) => !$task['completed']);
    } elseif (in_array($filter, ['alta', 'media', 'baja'])) {
        return array_filter($tasks, fn($task) => $task['priority'] === $filter);
    }
    return $tasks;
}

// Filtra las tareas segunopción seleccionada
$filteredTasks = filterTasks($_SESSION['tasks'], $filter);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Tareas</title>
    <link rel="stylesheet" href="PF.css">
</head>
<style>
    body {
        font-family: Arial, sans-serif;
        margin: 20px;
    }
    .container {
        max-width: 600px;
        margin: 0 auto;
    }
    h1 {
        text-align: center;
    }
    .task-input, .filter-input {
        display: flex;
        gap: 10px;
        margin-bottom: 20px;
    }
    .task-input input[type="text"] {
        flex: 1;
        padding: 10px;
    }
    .task-list {
        list-style: none;
        padding: 0;
    }
    .task-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px;
        margin-bottom: 10px;
        border: 1px solid #ddd;
        border-radius: 5px;
    }
    .completed {
        background-color: #d4edda;
    }
    button {
        padding: 5px 10px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
    }
    .add-btn {
        background-color: #28a745;
        color: white;
    }
    .complete-btn {
        background-color: #28a745;
        color: white;
    }
    .incomplete-btn {
        background-color: #ffc107;
        color: black;
    }
    .edit-btn {
        background-color: #007bff;
        color: white;
    }
    .delete-btn {
        background-color: #dc3545;
        color: white;
    }
    select {
        padding: 5px;
        border: 1px solid #ddd;
        border-radius: 5px;
    }
</style>
<body>
    <div class="container">
        <h1>Lista de tareas</h1>

        <!-- Formulario para agregar tareas -->
        <form method="POST" class="task-input">
            <input type="text" name="taskName" placeholder="Nueva tarea" required>
            <button type="submit" name="action" value="add" class="add-btn">Agregar tarea</button>
        </form>

        <!-- Formulario para filtrar tareas -->
        <form method="POST" class="filter-input" onchange="this.submit()">
            <select name="filter">
                <option value="all" <?= $filter === 'all' ? 'selected' : '' ?>>Todas</option>
                <option value="completada" <?= $filter === 'completada' ? 'selected' : '' ?>>Completadas</option>
                <option value="pendiente" <?= $filter === 'pendiente' ? 'selected' : '' ?>>Pendientes</option>
                <option value="alta" <?= $filter === 'alta' ? 'selected' : '' ?>>Prioridad Alta</option>
                <option value="media" <?= $filter === 'media' ? 'selected' : '' ?>>Prioridad Media</option>
                <option value="baja" <?= $filter === 'baja' ? 'selected' : '' ?>>Prioridad Baja</option>
            </select>
            <input type="hidden" name="action" value="filter">
        </form>

        <!-- Lista de tareas filtradas -->
        <ul class="task-list">
            <?php foreach ($filteredTasks as $index => $task): ?>
                <li class="task-item <?= $task['completed'] ? 'completed' : '' ?>">
                    <span>
                        <?= htmlspecialchars($task['name']) ?>
                        <small class="priority-<?= $task['priority'] ?>">
                            (<?= ucfirst($task['priority']) ?> prioridad)
                        </small>
                    </span>
                    <div>
                        <!-- Completar/Incompletar -->
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="index" value="<?= $index ?>">
                            <button type="submit" name="action" value="toggleComplete" 
                                class="<?= $task['completed'] ? 'incomplete-btn' : 'complete-btn' ?>">
                                <?= $task['completed'] ? 'Completa' : 'Pendiente' ?>
                            </button>
                        </form>

                        <!-- Cambiar Prioridad -->
                        <form method="POST" style="display: inline;" onchange="this.submit()">
                            <input type="hidden" name="index" value="<?= $index ?>">
                            <select name="priority" required>
                                <option value="alta" <?= $task['priority'] === 'alta' ? 'selected' : '' ?>>Alta</option>
                                <option value="media" <?= $task['priority'] === 'media' ? 'selected' : '' ?>>Media</option>
                                <option value="baja" <?= $task['priority'] === 'baja' ? 'selected' : '' ?>>Baja</option>
                            </select>
                            <input type="hidden" name="action" value="changePriority">
                        </form>

                        <!-- Editar -->
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="index" value="<?= $index ?>">
                            <input type="text" name="taskName" placeholder="Nuevo nombre" required>
                            <button type="submit" name="action" value="edit" class="edit-btn">Modificar</button>
                        </form>

                        <!-- Eliminar -->
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="index" value="<?= $index ?>">
                            <button type="submit" name="action" value="delete" class="delete-btn">Eliminar</button>
                        </form>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>


</body>
</html>
