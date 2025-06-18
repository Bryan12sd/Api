<?php
// Incluye el archivo de conexión a la base de datos y verificación de API Key
require_once '../config/database.php';
require_once '../auth/apikey.php';

// Establece el tipo de contenido como JSON
header("Content-Type: application/json");

// Verifica si la API Key es válida, si no, devuelve error 401
if (!isAuthorized()) {
    http_response_code(401);
    echo json_encode(["message" => "Acceso no autorizado"]);
    exit();
}

// Obtiene la conexión a la base de datos
$db = (new Database())->getConnection();

// Obtiene el método HTTP y la URI solicitada
$method = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];
$scriptName = $_SERVER['SCRIPT_NAME'];

// Elimina la ruta del script del URI para extraer solo la parte de los parámetros
$path = str_replace(dirname($scriptName), '', $uri);
$segments = explode('/', trim($path, '/'));

// Extrae el ID del pedido si existe en la URI (ej. /pedidos/1)
$id = $segments[1] ?? null;

// Maneja la lógica según el método HTTP
switch ($method) {
    case 'POST':
        // Crea un nuevo pedido
        $data = json_decode(file_get_contents("php://input"), true);

        // Verifica que se hayan enviado los datos requeridos
        if (!isset($data['cliente'], $data['producto'], $data['cantidad'])) {
            http_response_code(400);
            echo json_encode(["message" => "Datos incompletos"]);
            exit();
        }

        // Inserta el pedido con estado "pendiente"
        $stmt = $db->prepare("INSERT INTO pedidos (cliente, producto, cantidad, estado) VALUES (?, ?, ?, 'pendiente')");
        $stmt->execute([$data['cliente'], $data['producto'], $data['cantidad']]);

        echo json_encode(["message" => "Pedido creado"]);
        break;

    case 'GET':
        // Obtiene uno o todos los pedidos
        if ($id) {
            // Si se especifica un ID, busca ese pedido
            $stmt = $db->prepare("SELECT * FROM pedidos WHERE id = ?");
            $stmt->execute([$id]);
            $pedido = $stmt->fetch(PDO::FETCH_ASSOC);

            // Devuelve el pedido si existe o un mensaje si no
            echo json_encode($pedido ?: ["message" => "Pedido no encontrado"]);
        } else {
            // Si no hay ID, devuelve todos los pedidos
            $stmt = $db->query("SELECT * FROM pedidos");
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        }
        break;

    case 'PUT':
        // Actualiza el estado de un pedido existente
        parse_str(file_get_contents("php://input"), $data);

        // Verifica que se haya proporcionado el nuevo estado
        if (!isset($data['estado'])) {
            http_response_code(400);
            echo json_encode(["message" => "Estado no proporcionado"]);
            exit();
        }

        // Actualiza el estado del pedido según el ID
        $stmt = $db->prepare("UPDATE pedidos SET estado = ? WHERE id = ?");
        $stmt->execute([$data['estado'], $id]);

        echo json_encode(["message" => "Pedido actualizado"]);
        break;

    case 'DELETE':
        // Elimina un pedido solo si está en estado "pendiente"

        // Verifica si el pedido existe
        $stmt = $db->prepare("SELECT estado FROM pedidos WHERE id = ?");
        $stmt->execute([$id]);
        $pedido = $stmt->fetch(PDO::FETCH_ASSOC);

        // Si no se encuentra el pedido, devuelve error 404
        if (!$pedido) {
            http_response_code(404);
            echo json_encode(["message" => "Pedido no encontrado"]);
            exit();
        }

        // Si el estado no es "pendiente", no permite eliminarlo
        if ($pedido['estado'] !== 'pendiente') {
            http_response_code(400);
            echo json_encode(["message" => "No se puede eliminar el pedido porque ya está '{$pedido['estado']}'"]);
            exit();
        }

        // Elimina el pedido si está pendiente
        $stmt = $db->prepare("DELETE FROM pedidos WHERE id = ?");
        $stmt->execute([$id]);

        echo json_encode(["message" => "Pedido eliminado"]);
        break;

    default:
        // Si el método HTTP no está permitido, devuelve error 405
        http_response_code(405);
        echo json_encode(["message" => "Método no permitido"]);
        break;
}
