<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$docId = $_GET['id'] ?? null;

if (!$docId) {
    echo "Document ID is required.";
    exit;
}

$stmt = $conn->prepare("SELECT * FROM documents WHERE id = ?");
$stmt->bind_param("i", $docId);
$stmt->execute();
$document = $stmt->get_result()->fetch_assoc();

if (!$document) {
    echo "Document not found.";
    exit;
}

$username = $_SESSION['user'];
$stmt = $conn->prepare("SELECT id, role FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$userId = $user['id'];
$userRole = $user['role'];

$hasAccess = false;

if ($userRole === 'admin') {
    $hasAccess = true;
} elseif ($document['author_id'] == $userId) {
    $hasAccess = true;
} else {
    $stmt = $conn->prepare("SELECT 1 FROM shared_documents WHERE document_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $docId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $hasAccess = true;
    }
}

if (!$hasAccess) {
    echo "Access denied.";
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Document - <?= htmlspecialchars($document['title']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Roboto', 'sans-serif'],
                    }
                }
            }
        };
    </script>
    <style>
        #searchResults div {
            padding: 0.5rem;
            cursor: pointer;
        }
        #searchResults div:hover {
            background-color: #e5e7eb;
        }
    </style>
</head>
<body class="bg-gray-100 font-sans">

<div class="max-w-screen-lg mx-auto mt-8 bg-white px-8 py-6 rounded-lg shadow border border-gray-200">
    <div class="mb-4">
        <a href="dashboard.php" class="text-blue-600 hover:underline text-sm">&larr; Back to Dashboard</a>
    </div>

     <div class="flex items-center mb-4 gap-3">
        <img src="https://cdn-icons-png.flaticon.com/512/5968/5968517.png" alt="Google Docs Logo" class="w-8 h-8 flex-shrink-0" />
        <input type="text" value="<?= htmlspecialchars($document['title']) ?>" readonly
        class="text-2xl font-medium text-gray-800 bg-transparent border-none focus:outline-none flex-grow" />
    </div>

    <div class="flex items-center gap-3 text-gray-600 text-sm mb-4 border border-gray-300 rounded px-2 py-1">
        <button class="hover:bg-gray-200 px-2 py-1 rounded font-bold">B</button>
        <button class="hover:bg-gray-200 px-2 py-1 rounded italic">I</button>
        <button class="hover:bg-gray-200 px-2 py-1 rounded underline">U</button>
        <button class="hover:bg-gray-200 px-2 py-1 rounded">🔗</button>
    </div>

    <textarea id="editor"
              class="w-full min-h-[500px] border border-gray-300 rounded-lg p-4 shadow-inner focus:outline-none focus:ring focus:border-blue-300 resize-none mb-2 bg-white text-gray-800 text-base leading-relaxed">
<?= htmlspecialchars($document['content']) ?>
</textarea>

    <div id="autoSaveMsg" class="text-green-600 text-sm mb-4 hidden">All changes saved!</div>

    <div class="mb-6">
        <h2 class="text-lg font-semibold mb-2">Share Document</h2>
        <input type="text" id="userSearch" placeholder="Search users to add..."
               class="border border-gray-300 rounded px-3 py-2 w-full mb-2 focus:ring focus:border-blue-300" autocomplete="off" />

        <div id="searchResults" class="border border-gray-300 rounded max-h-48 overflow-y-auto mb-4 hidden bg-white text-sm"></div>

        <div id="sharedUsers" class="mb-4">
            <h3 class="font-semibold mb-2 text-sm">Users with Access:</h3>
            <ul id="sharedUsersList" class="list-disc list-inside text-sm"></ul>
        </div>
    </div>

    <div class="mt-4">
        <a href="activity_logs.php?id=<?= $docId ?>" class="text-blue-600 underline text-sm">View Activity Logs</a>
    </div>

    <h2 class="text-lg font-semibold mt-8 mb-2">Messages</h2>
    <div id="messageBox" class="h-48 overflow-y-auto border border-gray-300 rounded-lg p-4 bg-gray-50 mb-2 text-sm text-gray-800 shadow-inner"></div>

    <form id="messageForm" class="flex space-x-2">
        <input type="text" id="messageInput" placeholder="Type a message..."
               class="flex-grow border border-gray-300 rounded px-3 py-2 focus:ring focus:border-blue-300 text-sm" required />
        <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded text-sm shadow">
            Send
        </button>
    </form>

</div>

<script>
const editor = document.getElementById('editor');
let timeoutId = null;

function saveContent() {
    fetch('autosave.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `id=<?= $docId ?>&content=${encodeURIComponent(editor.value)}`
    }).then(res => res.text())
      .then(() => {
          const msg = document.getElementById('autoSaveMsg');
          msg.style.display = 'block';
          setTimeout(() => msg.style.display = 'none', 2000);
      });
}

editor.addEventListener('input', () => {
    clearTimeout(timeoutId);
    timeoutId = setTimeout(saveContent, 1000);
});

const messageBox = document.getElementById('messageBox');
const messageForm = document.getElementById('messageForm');
const messageInput = document.getElementById('messageInput');

function fetchMessages() {
    fetch('get_messages.php?doc_id=<?= $docId ?>')
        .then(res => res.json())
        .then(messages => {
            messageBox.innerHTML = messages.map(m =>
                `<div class="mb-2"><strong>${m.username}</strong>: ${m.message} 
                 <span class="text-xs text-gray-400">(${new Date(m.timestamp).toLocaleTimeString()})</span></div>`
            ).join('');
            messageBox.scrollTop = messageBox.scrollHeight;
        });
}

messageForm.addEventListener('submit', e => {
    e.preventDefault();
    const msg = messageInput.value.trim();
    if (msg === '') return;

    fetch('send_message.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `doc_id=<?= $docId ?>&message=${encodeURIComponent(msg)}`
    }).then(() => {
        messageInput.value = '';
        fetchMessages();
    });
});

setInterval(fetchMessages, 3000);
fetchMessages();

const userSearch = document.getElementById('userSearch');
const searchResults = document.getElementById('searchResults');
const sharedUsersList = document.getElementById('sharedUsersList');
const docId = <?= json_encode($docId) ?>;

function loadSharedUsers() {
    fetch(`get_shared_users.php?doc_id=${docId}`)
    .then(res => res.json())
    .then(users => {
        sharedUsersList.innerHTML = users.map(u =>
            `<li data-userid="${u.id}">${u.username} <button onclick="removeUser(${u.id})" class="text-red-500 ml-2 text-xs">Remove</button></li>`
        ).join('');
    });
}

userSearch.addEventListener('input', () => {
    const query = userSearch.value.trim();
    if (query.length < 2) {
        searchResults.classList.add('hidden');
        return;
    }
    fetch(`search_users.php?q=${encodeURIComponent(query)}&doc_id=${docId}`)
    .then(res => res.json())
    .then(users => {
        if (users.length === 0) {
            searchResults.innerHTML = "<div class='p-2 text-gray-500'>No users found</div>";
        } else {
            searchResults.innerHTML = users.map(u => 
                `<div class="p-2 cursor-pointer hover:bg-gray-200" onclick="addUser(${u.id}, '${u.username}')">${u.username} (${u.email})</div>`
            ).join('');
        }
        searchResults.classList.remove('hidden');
    });
});

function addUser(userId, username) {
    fetch('add_user_to_document.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `doc_id=${docId}&user_id=${userId}`
    }).then(res => res.text())
    .then(response => {
        if (response === 'success') {
            loadSharedUsers();
            userSearch.value = '';
            searchResults.classList.add('hidden');
        } else if (response === 'already_shared') {
            alert('User already has access.');
        } else if (response === 'missing_data') {
            alert('Missing data. Please try again.');
        } else {
            alert('Failed to add user: ' + response);
        }
    });
}

function removeUser(userId) {
    fetch('remove_user_from_document.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `doc_id=${docId}&user_id=${userId}`
    }).then(res => res.text())
    .then(response => {
        if (response === 'success') {
            loadSharedUsers();
        } else {
            alert('Failed to remove user.');
        }
    });
}

loadSharedUsers();
</script>

</body>
</html>