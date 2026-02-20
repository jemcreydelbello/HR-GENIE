<?php
header('Content-Type: application/json');
require_once 'connect.php';

$q = isset($_GET['q']) ? trim($_GET['q']) : '';

if ($q === '') {
    echo json_encode(['success' => true, 'results' => []]);
    exit;
}

$qParam = '%' . $q . '%';

$sql = "SELECT a.article_id, a.title
        FROM ARTICLES a
        LEFT JOIN article_standard s ON a.article_id = s.article_id
        LEFT JOIN article_steps st ON a.article_id = st.article_id
        LEFT JOIN article_qa q ON a.article_id = q.article_id
        WHERE a.title LIKE ? 
           OR s.description LIKE ? 
           OR st.step_title LIKE ?
           OR st.step_description LIKE ?
           OR q.question LIKE ?
           OR q.answer LIKE ?
        LIMIT 20";

if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param('ssssss', $qParam, $qParam, $qParam, $qParam, $qParam, $qParam);
    $stmt->execute();
    $res = $stmt->get_result();
    $results = [];
    while ($row = $res->fetch_assoc()) {
        // Get snippet based on article type
        $type_stmt = $conn->prepare("SELECT article_type FROM ARTICLES WHERE article_id = ?");
        $type_stmt->bind_param('i', $row['article_id']);
        $type_stmt->execute();
        $type_result = $type_stmt->get_result();
        $type_row = $type_result->fetch_assoc();
        $article_type = $type_row['article_type'] ?? 'standard';
        $type_stmt->close();
        
        $snippet = '';
        if ($article_type === 'standard') {
            $snippet_stmt = $conn->prepare("SELECT description FROM article_standard WHERE article_id = ? LIMIT 1");
            $snippet_stmt->bind_param('i', $row['article_id']);
            $snippet_stmt->execute();
            $snippet_result = $snippet_stmt->get_result();
            if ($snippet_result->num_rows > 0) {
                $snippet_row = $snippet_result->fetch_assoc();
                $snippet = substr($snippet_row['description'], 0, 150);
            }
            $snippet_stmt->close();
        } elseif ($article_type === 'step_by_step') {
            $snippet_stmt = $conn->prepare("SELECT step_title FROM article_steps WHERE article_id = ? ORDER BY step_number LIMIT 1");
            $snippet_stmt->bind_param('i', $row['article_id']);
            $snippet_stmt->execute();
            $snippet_result = $snippet_stmt->get_result();
            if ($snippet_result->num_rows > 0) {
                $snippet_row = $snippet_result->fetch_assoc();
                $snippet = $snippet_row['step_title'];
            }
            $snippet_stmt->close();
        } elseif ($article_type === 'qa') {
            $snippet_stmt = $conn->prepare("SELECT question FROM article_qa WHERE article_id = ? LIMIT 1");
            $snippet_stmt->bind_param('i', $row['article_id']);
            $snippet_stmt->execute();
            $snippet_result = $snippet_stmt->get_result();
            if ($snippet_result->num_rows > 0) {
                $snippet_row = $snippet_result->fetch_assoc();
                $snippet = $snippet_row['question'];
            }
            $snippet_stmt->close();
        }
        
        $results[] = [
            'article_id' => $row['article_id'],
            'title' => $row['title'],
            'snippet' => $snippet
        ];
    }
    $stmt->close();
    echo json_encode(['success' => true, 'results' => $results]);
} else {
    echo json_encode(['success' => false, 'results' => []]);
}

$conn->close();
?>
<div class="flex justify-center mt-10 relative max-w-2xl mx-auto">
  <div class="w-full relative">
    <!-- Search Input -->
    <input id="faqSearch" type="text" placeholder="Search FAQs..."
           class="w-full pl-4 pr-12 py-2 rounded-lg border border-gray-300 shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-400">

    <!-- Search Button Icon -->
    <button id="searchBtn"
            class="absolute right-2 top-1/2 transform -translate-y-1/2 bg-blue-500 hover:bg-blue-600 text-white rounded px-3 py-1">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z" />
      </svg>
    </button>

    <!-- Search Results Overlay -->
    <div id="searchResults" class="absolute left-0 right-0 mt-1 bg-white shadow-lg rounded max-h-96 overflow-y-auto z-50"></div>
  </div>
</div>


<script>
const searchInput = document.getElementById('faqSearch');
const resultsDiv = document.getElementById('searchResults');
let timeout = null;

searchInput.addEventListener('input', () => {
    clearTimeout(timeout);
    const query = searchInput.value.trim();
    if (query.length === 0) {
        resultsDiv.innerHTML = '';
        return;
    }

    // debounce for 300ms
    timeout = setTimeout(() => {
        fetch(`search.php?q=${encodeURIComponent(query)}`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    resultsDiv.innerHTML = data.results.length === 0 
                        ? `<p class="text-gray-500">No results found.</p>` 
                        : data.results.map(r => `
                            <a href="article.php?id=${r.article_id}" class="block p-3 border rounded hover:bg-blue-50 transition">
                                <h3 class="font-semibold text-blue-700">${r.title}</h3>
                                <p class="text-gray-700 text-sm">${r.snippet.substring(0, 120)}...</p>
                                <span class="text-xs text-gray-400 uppercase">${r.type.replace('_',' ')}</span>
                            </a>
                        `).join('');
                }
            });
    }, 300);
});
</script>
