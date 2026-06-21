<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$databasePath = __DIR__ . '/database.sqlite';
$pdo = new PDO('sqlite:' . $databasePath);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->exec('PRAGMA foreign_keys = ON');

initializeDatabase($pdo);
seedDatabase($pdo);

$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = rtrim($path, '/');

try {
    route($method, $path, $pdo);
} catch (Exception $e) {
    sendJson(['error' => $e->getMessage()], 500);
}

function route(string $method, string $path, PDO $pdo): void
{
    if ($path === '/api/boards' && $method === 'GET') {
        listBoards($pdo);
        return;
    }

    if ($path === '/api/boards' && $method === 'POST') {
        createBoard($pdo);
        return;
    }

    if (preg_match('#^/api/boards/(\d+)$#', $path, $matches)) {
        $boardId = (int) $matches[1];
        if ($method === 'GET') {
            getBoard($pdo, $boardId);
            return;
        }
        if ($method === 'PUT') {
            updateBoard($pdo, $boardId);
            return;
        }
        if ($method === 'DELETE') {
            deleteBoard($pdo, $boardId);
            return;
        }
    }

    if ($path === '/api/lists' && $method === 'POST') {
        createList($pdo);
        return;
    }

    if (preg_match('#^/api/lists/(\d+)$#', $path, $matches)) {
        $listId = (int) $matches[1];
        if ($method === 'PUT') {
            updateList($pdo, $listId);
            return;
        }
        if ($method === 'DELETE') {
            deleteList($pdo, $listId);
            return;
        }
    }

    if ($path === '/api/cards' && $method === 'POST') {
        createCard($pdo);
        return;
    }

    if (preg_match('#^/api/cards/(\d+)$#', $path, $matches)) {
        $cardId = (int) $matches[1];
        if ($method === 'PUT') {
            updateCard($pdo, $cardId);
            return;
        }
        if ($method === 'DELETE') {
            deleteCard($pdo, $cardId);
            return;
        }
    }

    if ($path === '/api/members' && $method === 'POST') {
        createMember($pdo);
        return;
    }

    if (preg_match('#^/api/members/(\d+)$#', $path, $matches)) {
        $memberId = (int) $matches[1];
        if ($method === 'DELETE') {
            deleteMember($pdo, $memberId);
            return;
        }
    }

    sendJson(['error' => 'Route not found'], 404);
}

function initializeDatabase(PDO $pdo): void
{
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS boards (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
        )'
    );

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS lists (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            board_id INTEGER NOT NULL,
            title TEXT NOT NULL,
            created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY(board_id) REFERENCES boards(id) ON DELETE CASCADE
        )'
    );

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS members (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            board_id INTEGER NOT NULL,
            name TEXT NOT NULL,
            initials TEXT NOT NULL,
            created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY(board_id) REFERENCES boards(id) ON DELETE CASCADE
        )'
    );

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS cards (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            list_id INTEGER NOT NULL,
            title TEXT NOT NULL,
            description TEXT DEFAULT "",
            tags TEXT DEFAULT "[]",
            member_id INTEGER,
            due_date TEXT,
            created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY(list_id) REFERENCES lists(id) ON DELETE CASCADE,
            FOREIGN KEY(member_id) REFERENCES members(id) ON DELETE SET NULL
        )'
    );
}

function seedDatabase(PDO $pdo): void
{
    $boardCount = (int) $pdo->query('SELECT COUNT(*) FROM boards')->fetchColumn();
    if ($boardCount > 0) {
        return;
    }

    $pdo->beginTransaction();
    $stmt = $pdo->prepare('INSERT INTO boards (name) VALUES (:name)');
    $stmt->execute([':name' => 'Sprint Board']);
    $boardId = (int) $pdo->lastInsertId();

    $lists = ['To Do', 'Doing', 'Done'];
    $listIds = [];
    $listStmt = $pdo->prepare('INSERT INTO lists (board_id, title) VALUES (:board_id, :title)');
    foreach ($lists as $title) {
        $listStmt->execute([':board_id' => $boardId, ':title' => $title]);
        $listIds[$title] = (int) $pdo->lastInsertId();
    }

    $members = [
        ['name' => 'Aryan', 'initials' => 'AR'],
        ['name' => 'Priya', 'initials' => 'PR'],
        ['name' => 'Kabir', 'initials' => 'KA'],
        ['name' => 'Sneha', 'initials' => 'SN'],
    ];
    $memberIds = [];
    $memberStmt = $pdo->prepare('INSERT INTO members (board_id, name, initials) VALUES (:board_id, :name, :initials)');
    foreach ($members as $member) {
        $memberStmt->execute([':board_id' => $boardId, ':name' => $member['name'], ':initials' => $member['initials']]);
        $memberIds[$member['initials']] = (int) $pdo->lastInsertId();
    }

    $cards = [
        ['list' => 'To Do', 'title' => 'Design login screen', 'description' => 'Wireframe the new authentication flow.', 'tags' => ['UI', 'Design'], 'member' => 'AR', 'due_date' => '2026-07-01'],
        ['list' => 'To Do', 'title' => 'Add team labels', 'description' => 'Create label component for team assignment.', 'tags' => ['Frontend'], 'member' => 'PR', 'due_date' => '2026-07-03'],
        ['list' => 'Doing', 'title' => 'Implement API endpoints', 'description' => 'Build board, list, card, and member endpoints.', 'tags' => ['Backend', 'API'], 'member' => 'KA', 'due_date' => '2026-06-25'],
        ['list' => 'Doing', 'title' => 'Fix responsive layout', 'description' => 'Adjust board view for mobile screens.', 'tags' => ['Bug'], 'member' => 'SN', 'due_date' => '2026-06-26'],
        ['list' => 'Done', 'title' => 'Setup SQLite database', 'description' => 'Database initialized and seeded with sample data.', 'tags' => ['Database'], 'member' => 'AR', 'due_date' => '2026-06-20'],
    ];
    $cardStmt = $pdo->prepare(
        'INSERT INTO cards (list_id, title, description, tags, member_id, due_date) VALUES (:list_id, :title, :description, :tags, :member_id, :due_date)'
    );
    foreach ($cards as $card) {
        $cardStmt->execute([
            ':list_id' => $listIds[$card['list']],
            ':title' => $card['title'],
            ':description' => $card['description'],
            ':tags' => json_encode($card['tags'], JSON_UNESCAPED_UNICODE),
            ':member_id' => $memberIds[$card['member']],
            ':due_date' => $card['due_date'],
        ]);
    }

    $pdo->commit();
}

function parseJsonBody(): array
{
    $body = file_get_contents('php://input');
    if ($body === false || $body === '') {
        return [];
    }
    $data = json_decode($body, true);
    if (!is_array($data)) {
        sendJson(['error' => 'Invalid JSON request body'], 400);
        exit;
    }
    return $data;
}

function listBoards(PDO $pdo): void
{
    $boards = $pdo->query('SELECT id, name FROM boards ORDER BY id')->fetchAll(PDO::FETCH_ASSOC);
    foreach ($boards as &$board) {
        $board['lists'] = getListsForBoard($pdo, (int) $board['id']);
    }
    sendJson($boards);
}

function getBoard(PDO $pdo, int $boardId): void
{
    $stmt = $pdo->prepare('SELECT id, name FROM boards WHERE id = :id');
    $stmt->execute([':id' => $boardId]);
    $board = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$board) {
        sendJson(['error' => 'Board not found'], 404);
        return;
    }
    $board['lists'] = getListsForBoard($pdo, $boardId);
    $board['members'] = getMembersForBoard($pdo, $boardId);
    sendJson($board);
}

function getListsForBoard(PDO $pdo, int $boardId): array
{
    $stmt = $pdo->prepare('SELECT id, title FROM lists WHERE board_id = :board_id ORDER BY id');
    $stmt->execute([':board_id' => $boardId]);
    $lists = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($lists as &$list) {
        $list['cards'] = getCardsForList($pdo, (int) $list['id']);
    }
    return $lists;
}

function getCardsForList(PDO $pdo, int $listId): array
{
    $stmt = $pdo->prepare(
        'SELECT c.id, c.title, c.description, c.tags, c.member_id, c.due_date, m.name AS member_name, m.initials AS member_initials
            FROM cards c
            LEFT JOIN members m ON c.member_id = m.id
            WHERE c.list_id = :list_id
            ORDER BY c.id'
    );
    $stmt->execute([':list_id' => $listId]);
    $cards = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($cards as &$card) {
        $card['tags'] = json_decode($card['tags'] ?? '[]', true) ?: [];
        if ($card['member_id'] === null) {
            $card['member_name'] = null;
            $card['member_initials'] = null;
        }
    }
    return $cards;
}

function getMembersForBoard(PDO $pdo, int $boardId): array
{
    $stmt = $pdo->prepare('SELECT id, name, initials FROM members WHERE board_id = :board_id ORDER BY id');
    $stmt->execute([':board_id' => $boardId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function createBoard(PDO $pdo): void
{
    $data = parseJsonBody();
    $name = trim($data['name'] ?? '');
    if ($name === '') {
        sendJson(['error' => 'Board name is required'], 400);
        return;
    }
    $stmt = $pdo->prepare('INSERT INTO boards (name) VALUES (:name)');
    $stmt->execute([':name' => $name]);
    $boardId = (int) $pdo->lastInsertId();
    getBoard($pdo, $boardId);
}

function updateBoard(PDO $pdo, int $boardId): void
{
    $data = parseJsonBody();
    $name = trim($data['name'] ?? '');
    if ($name === '') {
        sendJson(['error' => 'Board name is required'], 400);
        return;
    }
    $stmt = $pdo->prepare('UPDATE boards SET name = :name, updated_at = CURRENT_TIMESTAMP WHERE id = :id');
    $stmt->execute([':name' => $name, ':id' => $boardId]);
    getBoard($pdo, $boardId);
}

function deleteBoard(PDO $pdo, int $boardId): void
{
    $stmt = $pdo->prepare('DELETE FROM boards WHERE id = :id');
    $stmt->execute([':id' => $boardId]);
    sendJson(['success' => true]);
}

function createList(PDO $pdo): void
{
    $data = parseJsonBody();
    $boardId = (int) ($data['board_id'] ?? 0);
    $title = trim($data['title'] ?? '');
    if ($boardId <= 0 || $title === '') {
        sendJson(['error' => 'board_id and title are required'], 400);
        return;
    }
    $stmt = $pdo->prepare('INSERT INTO lists (board_id, title) VALUES (:board_id, :title)');
    $stmt->execute([':board_id' => $boardId, ':title' => $title]);
    $listId = (int) $pdo->lastInsertId();
    $stmt = $pdo->prepare('SELECT id, board_id, title FROM lists WHERE id = :id');
    $stmt->execute([':id' => $listId]);
    sendJson($stmt->fetch(PDO::FETCH_ASSOC), 201);
}

function updateList(PDO $pdo, int $listId): void
{
    $data = parseJsonBody();
    $title = trim($data['title'] ?? '');
    if ($title === '') {
        sendJson(['error' => 'title is required'], 400);
        return;
    }
    $stmt = $pdo->prepare('UPDATE lists SET title = :title, updated_at = CURRENT_TIMESTAMP WHERE id = :id');
    $stmt->execute([':title' => $title, ':id' => $listId]);
    $stmt = $pdo->prepare('SELECT id, board_id, title FROM lists WHERE id = :id');
    $stmt->execute([':id' => $listId]);
    sendJson($stmt->fetch(PDO::FETCH_ASSOC));
}

function deleteList(PDO $pdo, int $listId): void
{
    $stmt = $pdo->prepare('DELETE FROM lists WHERE id = :id');
    $stmt->execute([':id' => $listId]);
    sendJson(['success' => true]);
}

function createCard(PDO $pdo): void
{
    $data = parseJsonBody();
    $listId = (int) ($data['list_id'] ?? 0);
    $title = trim($data['title'] ?? '');
    $description = trim($data['description'] ?? '');
    $tags = isset($data['tags']) && is_array($data['tags']) ? $data['tags'] : [];
    $memberId = isset($data['member_id']) ? (int) $data['member_id'] : null;
    $dueDate = isset($data['due_date']) ? trim($data['due_date']) : null;

    if ($listId <= 0 || $title === '') {
        sendJson(['error' => 'list_id and title are required'], 400);
        return;
    }
    $stmt = $pdo->prepare(
        'INSERT INTO cards (list_id, title, description, tags, member_id, due_date)
            VALUES (:list_id, :title, :description, :tags, :member_id, :due_date)'
    );
    $stmt->execute([
        ':list_id' => $listId,
        ':title' => $title,
        ':description' => $description,
        ':tags' => json_encode(array_values($tags), JSON_UNESCAPED_UNICODE),
        ':member_id' => $memberId ?: null,
        ':due_date' => $dueDate ?: null,
    ]);
    $cardId = (int) $pdo->lastInsertId();
    sendJson(loadCard($pdo, $cardId), 201);
}

function updateCard(PDO $pdo, int $cardId): void
{
    $data = parseJsonBody();
    $fields = [];
    $params = [':id' => $cardId];

    if (isset($data['list_id'])) {
        $fields[] = 'list_id = :list_id';
        $params[':list_id'] = (int) $data['list_id'];
    }
    if (isset($data['title'])) {
        $fields[] = 'title = :title';
        $params[':title'] = trim($data['title']);
    }
    if (isset($data['description'])) {
        $fields[] = 'description = :description';
        $params[':description'] = trim($data['description']);
    }
    if (array_key_exists('tags', $data)) {
        $fields[] = 'tags = :tags';
        $tags = is_array($data['tags']) ? $data['tags'] : [];
        $params[':tags'] = json_encode(array_values($tags), JSON_UNESCAPED_UNICODE);
    }
    if (array_key_exists('member_id', $data)) {
        $fields[] = 'member_id = :member_id';
        $params[':member_id'] = $data['member_id'] !== null ? (int) $data['member_id'] : null;
    }
    if (array_key_exists('due_date', $data)) {
        $fields[] = 'due_date = :due_date';
        $params[':due_date'] = trim($data['due_date'] ?? '');
    }

    if (empty($fields)) {
        sendJson(['error' => 'No data provided to update'], 400);
        return;
    }

    $fields[] = 'updated_at = CURRENT_TIMESTAMP';
    $stmt = $pdo->prepare('UPDATE cards SET ' . implode(', ', $fields) . ' WHERE id = :id');
    $stmt->execute($params);
    sendJson(loadCard($pdo, $cardId));
}

function deleteCard(PDO $pdo, int $cardId): void
{
    $stmt = $pdo->prepare('DELETE FROM cards WHERE id = :id');
    $stmt->execute([':id' => $cardId]);
    sendJson(['success' => true]);
}

function createMember(PDO $pdo): void
{
    $data = parseJsonBody();
    $boardId = (int) ($data['board_id'] ?? 0);
    $name = trim($data['name'] ?? '');
    $initials = trim($data['initials'] ?? '');
    if ($boardId <= 0 || $name === '' || $initials === '') {
        sendJson(['error' => 'board_id, name, and initials are required'], 400);
        return;
    }
    $stmt = $pdo->prepare('INSERT INTO members (board_id, name, initials) VALUES (:board_id, :name, :initials)');
    $stmt->execute([':board_id' => $boardId, ':name' => $name, ':initials' => $initials]);
    $memberId = (int) $pdo->lastInsertId();
    $stmt = $pdo->prepare('SELECT id, board_id, name, initials FROM members WHERE id = :id');
    $stmt->execute([':id' => $memberId]);
    sendJson($stmt->fetch(PDO::FETCH_ASSOC), 201);
}

function deleteMember(PDO $pdo, int $memberId): void
{
    $stmt = $pdo->prepare('DELETE FROM members WHERE id = :id');
    $stmt->execute([':id' => $memberId]);
    sendJson(['success' => true]);
}

function loadCard(PDO $pdo, int $cardId): array
{
    $stmt = $pdo->prepare(
        'SELECT c.id, c.list_id, c.title, c.description, c.tags, c.member_id, c.due_date,
                m.name AS member_name, m.initials AS member_initials
            FROM cards c
            LEFT JOIN members m ON c.member_id = m.id
            WHERE c.id = :id'
    );
    $stmt->execute([':id' => $cardId]);
    $card = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$card) {
        sendJson(['error' => 'Card not found'], 404);
        exit;
    }
    $card['tags'] = json_decode($card['tags'] ?? '[]', true) ?: [];
    return $card;
}

function sendJson($payload, int $status = 200): void
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}
