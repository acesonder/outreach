<?php
/**
 * API endpoint for toggling task completion
 */

require_once '../includes/config.php';

// Set JSON headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Start session and check authentication
startSecureSession();

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Authentication required']);
    exit;
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
$taskId = (int)($input['task_id'] ?? 0);

if ($taskId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid task ID']);
    exit;
}

try {
    $pdo = getDbConnection();
    $currentUser = getCurrentUser();
    
    // Check if user has permission to update this task
    $stmt = $pdo->prepare("
        SELECT task_id, status, assigned_to 
        FROM tasks 
        WHERE task_id = ? AND (assigned_to = ? OR ? IN ('admin', 'staff'))
    ");
    $stmt->execute([$taskId, $currentUser['user_id'], $currentUser['role']]);
    $task = $stmt->fetch();
    
    if (!$task) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Task not found or access denied']);
        exit;
    }
    
    // Toggle task status
    $newStatus = $task['status'] === 'completed' ? 'pending' : 'completed';
    $completedAt = $newStatus === 'completed' ? 'NOW()' : 'NULL';
    
    $stmt = $pdo->prepare("
        UPDATE tasks 
        SET status = ?, completed_at = $completedAt, updated_at = NOW() 
        WHERE task_id = ?
    ");
    $stmt->execute([$newStatus, $taskId]);
    
    // Log activity
    logActivity('task_status_updated', 'tasks', $taskId, 
               ['status' => $task['status']], 
               ['status' => $newStatus]);
    
    echo json_encode([
        'success' => true, 
        'message' => 'Task updated successfully',
        'new_status' => $newStatus
    ]);
    
} catch (PDOException $e) {
    error_log("API task toggle error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>