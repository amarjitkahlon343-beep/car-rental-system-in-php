<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'Root@123');
define('DB_NAME', 'phpproject');

function getDbConnection()
{
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        error_log("Connection failed: " . $conn->connect_error);
        die("Database connection error. Please try again later.");
    }
    return $conn;
}
function handleInventory($data)
{
    $conn = getDbConnection();

    // Sanitize inputs
    $op = $data['operation'] ?? 'select_all';
    $id = (int) ($data['id'] ?? 0);
    $make = $conn->real_escape_string($data['make'] ?? '');
    $model = $conn->real_escape_string($data['model'] ?? '');
    $year = (int) ($data['year'] ?? 0);
    $color = $conn->real_escape_string($data['color'] ?? '');
    $price = (float) ($data['price'] ?? 0);
    $stock = (int) ($data['stock'] ?? 0);

    // Call the stored procedure
    $stmt = $conn->prepare("CALL car_crud(?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sissiddi", $op, $id, $make, $model, $year, $color, $price, $stock);
    $stmt->execute();

    $result = $stmt->get_result();

    // Return list for select_all, or message for others
    $output = ($op === 'select_all') ? $result->fetch_all(MYSQLI_ASSOC) : $result->fetch_assoc();

    $stmt->close();
    $conn->close();
    return $output;
}

function getCarImageUrl($model)
{
    $model = strtolower($model);
    if (strpos($model, 'tesla') !== false)
        return "https://images.unsplash.com/photo-1560958089-b8a1929cea89?w=400&h=250&fit=crop";
    if (strpos($model, 'bmw') !== false)
        return "https://images.unsplash.com/photo-1555215695-3004980ad54e?w=400&h=250&fit=crop";
    if (strpos($model, 'mercedes') !== false)
        return "https://images.unsplash.com/photo-1618843479313-40f8afb4b4d8?w=400&h=250&fit=crop";
    if (strpos($model, 'toyota') !== false)
        return "https://images.unsplash.com/photo-1621007947382-bb3c3994e3fb?w=400&h=250&fit=crop";
    if (strpos($model, 'honda') !== false)
        return "https://images.unsplash.com/photo-1599912027806-cfec9f5944b6?w=400&h=250&fit=crop";
    if (strpos($model, 'ford') !== false)
        return "https://images.unsplash.com/photo-1551816230-ef5deaed4a26?w=400&h=250&fit=crop";
    if (strpos($model, 'thar') !== false)
        return "https://images.unsplash.com/photo-1603386329225-868f9b1ee6c9?w=400&h=250&fit=crop";

    return "https://images.unsplash.com/photo-1494976388531-d1058494cdd8?w=400&h=250&fit=crop";
}

function executeCarProcedure($data)
{
    $conn = getDbConnection();

    $params = array_merge([
        'operation' => 'select_all',
        'id' => 0,
        'make' => '',
        'model' => '',
        'year' => 0,
        'color' => '',
        'price' => 0.0,
        'stock' => 0
    ], $data);

    $stmt = $conn->prepare("CALL car_crud(?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param(
        "sissiddi",
        $params['operation'],
        $params['id'],
        $params['make'],
        $params['model'],
        $params['year'],
        $params['color'],
        $params['price'],
        $params['stock']
    );

    $stmt->execute();
    $result = $stmt->get_result();

    if ($params['operation'] === 'select_all') {
        $output = ($result) ? $result->fetch_all(MYSQLI_ASSOC) : [];
    } else {
        $output = ($result) ? $result->fetch_assoc() : ['message' => 'Operation successful'];
    }

    $stmt->close();
    $conn->close();
    return $output;
}
?>