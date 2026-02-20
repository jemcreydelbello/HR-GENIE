<!-- Chatbot Widget -->
<div id="chatbotWidget" class="fixed bottom-5 right-5 w-96 bg-white rounded-2xl shadow-2xl flex flex-col z-[9999] border border-gray-200 chatbot-hidden" style="height: 600px;">
    <!-- Header with Background Image -->
    <div class="relative h-32 bg-cover bg-center rounded-t-2xl overflow-hidden" style="background-image: url('chatbot-bg.png');">
        
        <!-- Header Content -->
        <div class="relative h-full flex flex-col justify-between p-4">
            <!-- Top Buttons -->
            <div class="flex items-center justify-between">
                <!-- Back Button -->
                <button id="backBtn" onclick="goBackTab()" class="text-white hover:bg-white/20 p-2 rounded-lg transition hidden">
                    <i class="bi bi-chevron-left text-lg"></i>
                </button>
                <div class="flex-1"></div>
                
                <!-- Window Control Buttons (Expand, Close) -->
                <button id="expandBtn" onclick="toggleWindowSize()" class="text-white hover:bg-white/20 p-2 rounded-lg transition" title="Expand">
                    <i class="bi bi-arrows-expand text-lg"></i>
                </button>
                
                <!-- Close Button -->
                <button onclick="toggleChatbot()" class="text-white hover:bg-white/20 p-2 rounded-lg transition">
                    <i class="bi bi-x text-lg"></i>
                </button>
            </div>
            
            <!-- Title and Subtitle -->
            <div id="headerContent" class="text-white">
                <h2 class="text-lg font-bold">HRDOTNET Genie AI</h2>
                <p class="text-sm mt-1">Get help with HR questions</p>
            </div>
        </div>
    </div>

    <!-- Main Content Area (Dynamic) -->
    <div id="chatbotMainContent" class="flex-1 overflow-y-auto">
        <!-- Home Tab Content (default) -->
        <div id="homeTab" class="p-4 space-y-4">
            <div>
                <h4 class="font-bold text-gray-800 mb-3">Most Helpful Articles</h4>
                <div id="faqList" class="space-y-2">
                    <!-- Will be populated by JavaScript -->
                </div>
            </div>
        </div>

        <!-- Messages Tab Content -->
        <div id="messagesTab" class="p-0 hidden flex flex-col h-full">
            <!-- Messages Tab Header with Background Image -->
            <div id="messagesHeader" class="relative h-32 bg-cover bg-center rounded-t-2xl overflow-hidden hidden" style="background-image: url('chatbot-bg.png');">
                
                <!-- Header Content -->
                <div class="relative h-full flex flex-col justify-between p-4">
                    <!-- Top Buttons -->
                    <div class="flex items-center justify-between">
                        <div class="flex-1"></div>
                        
                        <!-- Expand/Collapse Button -->
                        <button id="expandBtnMessages" onclick="toggleWindowSize()" class="text-white hover:bg-white/20 p-2 rounded-lg transition" title="Expand">
                            <i class="bi bi-arrows-expand text-lg"></i>
                        </button>
                        
                        <!-- Close Button -->
                        <button onclick="toggleChatbot()" class="text-white hover:bg-white/20 p-2 rounded-lg transition">
                            <i class="bi bi-x text-lg"></i>
                        </button>
                    </div>
                    
                    <!-- Title and Subtitle -->
                    <div class="text-white">
                        <h2 class="text-lg font-bold">HRDOTNET Genie AI</h2>
                        <p class="text-sm mt-1">Check out our FAQ or chat with us.</p>
                    </div>
                </div>
            </div>
            
            <!-- Chat Messages Area -->
            <div id="chatMessages" class="flex-1 overflow-y-auto space-y-3 p-4">
                <!-- Messages will be populated here -->
            </div>
            
            <!-- Message Input Area (Fixed at bottom) -->
            <div class="border-t border-gray-200 p-4 space-y-3 bg-white">
                <div class="relative">
                    <textarea 
                        id="userMessage" 
                        placeholder="Message..."
                        class="w-full px-4 py-3 pr-14 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"
                        rows="1"
                        oninput="autoExpandTextarea(); updateSendButtonState()"
                    ></textarea>
                    <button 
                        id="sendBtn"
                        onclick="sendUserMessage()" 
                        class="absolute right-3 bottom-3 w-8 h-8 bg-gray-300 text-white rounded-full transition flex items-center justify-center flex-shrink-0 disabled-btn"
                        title="Send message"
                        disabled
                    >
                        <i class="bi bi-arrow-up text-sm"></i>
                    </button>
                </div>
            </div>
            
            <!-- Footer -->
            <div class="border-t border-gray-200 px-4 py-3 bg-gray-50 text-center text-xs text-gray-500 rounded-b-xl">
                <p>Powered by <span class="font-semibold text-gray-700">HRDOTNET Genie AI</span></p>
            </div>
        </div>

        <!-- Help Tab Content -->
        <div id="helpTab" class="p-3 hidden flex flex-col h-full bg-gradient-to-br from-gray-50 to-gray-100">
            <div class="space-y-3 flex-1">
                <!-- Need Help Section -->
                <div class="bg-white p-3 rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition">
                    <div class="flex items-start gap-2">
                        <div class="flex-shrink-0 w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                            <i class="bi bi-question-circle text-blue-600 text-sm"></i>
                        </div>
                        <div class="flex-1">
                            <h4 class="font-semibold text-gray-900 text-sm mb-0.5">Need Help?</h4>
                            <p class="text-xs text-gray-600">Couldn't find your answer? Our HR team is here to assist.</p>
                        </div>
                    </div>
                </div>

                <!-- How It Works Section -->
                <div class="bg-white p-3 rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition">
                    <div class="flex items-start gap-2 mb-2">
                        <div class="flex-shrink-0 w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                            <i class="bi bi-check-circle text-green-600 text-sm"></i>
                        </div>
                        <h4 class="font-semibold text-gray-900 text-sm">How It Works</h4>
                    </div>
                    <div class="space-y-1 ml-1">
                        <div class="flex items-start gap-1.5">
                            <span class="inline-flex items-center justify-center w-4 h-4 rounded-full bg-blue-500 text-white text-xs font-semibold flex-shrink-0 mt-0.5">1</span>
                            <p class="text-xs text-gray-700">Submit a ticket with your question</p>
                        </div>
                        <div class="flex items-start gap-1.5">
                            <span class="inline-flex items-center justify-center w-4 h-4 rounded-full bg-blue-500 text-white text-xs font-semibold flex-shrink-0 mt-0.5">2</span>
                            <p class="text-xs text-gray-700">HR team reviews your request</p>
                        </div>
                        <div class="flex items-start gap-1.5">
                            <span class="inline-flex items-center justify-center w-4 h-4 rounded-full bg-blue-500 text-white text-xs font-semibold flex-shrink-0 mt-0.5">3</span>
                            <p class="text-xs text-gray-700">Receive a response soon</p>
                        </div>
                    </div>
                </div>

                <!-- Submit Ticket Section -->
                <div class="bg-gradient-to-r from-blue-600 to-blue-700 p-3 rounded-lg shadow-sm text-white mt-auto">
                    <div class="flex items-start gap-2 mb-2">
                        <div class="flex-shrink-0 w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center">
                            <i class="bi bi-ticket text-white text-sm"></i>
                        </div>
                        <div>
                            <h4 class="font-semibold text-white text-sm">Submit a Ticket</h4>
                            <p class="text-xs text-blue-50">Get help from our HR team</p>
                        </div>
                    </div>
                    <button onclick="openSubmitTicketModal()" class="w-full bg-white text-blue-600 hover:bg-blue-50 font-semibold py-1.5 px-3 rounded text-sm transition flex items-center justify-center gap-1.5">
                        <i class="bi bi-send text-xs"></i>
                        Submit Ticket
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Navigation -->
    <div class="border-t border-gray-200 bg-white rounded-b-xl grid grid-cols-3 divide-x divide-gray-200">
        <button onclick="switchChatTab('home')" id="homeBtn" class="py-2 px-3 text-center hover:bg-gray-50 transition flex flex-col items-center gap-0.5 tab-btn border-b-2 border-transparent">
            <i class="bi bi-house-fill text-gray-600 text-sm"></i>
            <span class="text-xs font-medium text-gray-600">Home</span>
        </button>
        <button onclick="switchChatTab('messages')" id="messagesBtn" class="py-2 px-3 text-center hover:bg-gray-50 transition flex flex-col items-center gap-0.5 tab-btn border-b-2 border-transparent">
            <i class="bi bi-chat-left-dots-fill text-gray-600 text-sm"></i>
            <span class="text-xs font-medium text-gray-600">Messages</span>
        </button>
        <button onclick="switchChatTab('help')" id="helpBtn" class="py-2 px-3 text-center hover:bg-gray-50 transition flex flex-col items-center gap-0.5 tab-btn border-b-2 border-transparent">
            <i class="bi bi-question-circle-fill text-gray-600 text-sm"></i>
            <span class="text-xs font-medium text-gray-600">Help</span>
        </button>
    </div>

    <!-- Minimize Button -->
    <button 
        onclick="toggleChatbot()" 
        id="minimizeBtn"
        class="absolute -bottom-14 left-0 right-0 mx-auto bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition hidden"
    >
        ⬆ Show Chat
    </button>
</div>

<!-- Include Shared Submit Ticket Modal -->
<?php include 'submit_ticket_modal.php'; ?>

<!-- Floating Chatbot Button (when minimized) -->
<button 
    id="chatbotToggle"
    onclick="toggleChatbot()" 
    class="fixed bottom-5 right-5 w-14 h-14 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-full shadow-lg hover:shadow-xl transition flex items-center justify-center text-2xl z-[9999] chatbot-toggle-btn cursor-pointer"
    title="Open Chat"
    style="pointer-events: auto;"
>
    <i class="bi bi-chat-dots"></i>
    <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-6 h-6 flex items-center justify-center" id="notificationBadge" style="display: none;">1</span>
</button>

<!-- Toast Notification -->
    <div id="toastNotification" class="hidden fixed top-5 right-5 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-[10001] flex items-center gap-3">
        <i class="bi bi-check-circle text-xl"></i>
        <span id="toastMessage">Ticket submitted successfully!</span>
    </div>

<style>
.chatbot-hidden {
    display: none !important;
}

.chatbot-toggle-btn {
    display: flex !important;
}

#chatbotWidget {
    display: flex !important;
    flex-direction: column !important;
}

/* Toast fade out animation */
@keyframes fadeOut {
    0% {
        opacity: 1;
    }
    100% {
        opacity: 0;
    }
}

.toast-fade-out {
    animation: fadeOut 0.5s ease-in-out forwards;
}

/* Mobile responsiveness */
@media (max-width: 640px) {
    #chatbotWidget {
        width: calc(100vw - 32px) !important;
        height: calc(100vh - 100px) !important;
        max-height: 600px !important;
        right: 16px !important;
        bottom: 16px !important;
        border-radius: 1rem !important;
        max-width: none !important;
    }

    #chatbotWidget .rounded-t-2xl {
        border-radius: 1rem 1rem 0 0 !important;
    }
    
    #chatbotWidget .rounded-b-xl {
        border-radius: 0 0 1rem 1rem !important;
    }

    #minimizeBtn {
        bottom: -50px !important;
    }

    #chatbotToggle {
        width: 52px !important;
        height: 52px !important;
        right: 16px !important;
        bottom: 16px !important;
    }

    /* Hide resize/expand buttons on mobile */
    #expandBtn,
    #expandBtnMessages {
        display: none !important;
    }
}

#chatbotWidget.chatbot-hidden {
    display: none !important;
}

.chatbot-toggle-btn.hidden {
    display: none !important;
}

#chatMessages::-webkit-scrollbar {
    display: none;
}

#chatMessages {
    -ms-overflow-style: none;
    scrollbar-width: none;
}

.chat-loading {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
}

.chat-loading span {
    width: 8px;
    height: 8px;
    background: #666;
    border-radius: 50%;
    animation: bounce 1.4s infinite;
}

.chat-loading span:nth-child(2) {
    animation-delay: 0.2s;
}

.chat-loading span:nth-child(3) {
    animation-delay: 0.4s;
}

@keyframes bounce {
    0%, 80%, 100% {
        opacity: 0.3;
        transform: scale(0.8);
    }
    40% {
        opacity: 1;
        transform: scale(1);
    }
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fadeIn {
    animation: slideUp 0.3s ease-out;
}

/* Active tab styling */
.tab-btn {
    border-bottom: 2px solid transparent;
}

.tab-btn.active {
    background-color: #f0f9ff;
    border-bottom-color: #2563eb !important;
}

.tab-btn.active i {
    color: #2563eb !important;
}

.tab-btn.active span {
    color: #2563eb !important;
}

/* Send button disabled state */
.disabled-btn {
    cursor: not-allowed !important;
    opacity: 0.6;
}

.disabled-btn:hover {
    background-color: #d1d5db !important;
}

.enabled-btn {
    background-color: #2563eb !important;
    cursor: pointer !important;
    opacity: 1;
}

.enabled-btn:hover {
    background-color: #1d4ed8 !important;
}
</style>

<script>
// Global function stub - will be properly defined below
if (typeof toggleChatbot === 'undefined') {
    window.toggleChatbot = function() {
        console.error('toggleChatbot not yet initialized');
    };
}

let chatSessionId = null;
let isChatMinimized = true;
let currentTab = 'home';

// Utility function to escape HTML
function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
}

// Load most liked FAQ articles on page load
async function loadFAQArticles() {
    try {
        const response = await fetch('get_most_liked_articles.php?limit=5');
        const data = await response.json();
        
        console.log('FAQ Articles Response:', data); // Debug log
        
        if (data.success && data.articles && data.articles.length > 0) {
            const faqList = document.getElementById('faqList');
            faqList.innerHTML = '';
            
            data.articles.forEach(article => {
                const articleEl = document.createElement('div');
                articleEl.className = 'p-3 bg-white rounded-lg hover:bg-blue-50 cursor-pointer transition border border-gray-200 hover:border-blue-400';
                articleEl.onclick = function() {
                    window.location.href = 'view_article.php?id=' + article.article_id;
                };
                articleEl.innerHTML = `
                    <div class="text-sm font-medium text-gray-800 hover:text-blue-600 line-clamp-2">
                        ${article.title}
                    </div>
                    <p class="text-xs text-gray-500 mt-1">${article.category}</p>
                    <div class="text-xs text-green-600 mt-1 flex items-center gap-1">
                        <i class="bi bi-hand-thumbs-up text-xs"></i>
                        ${article.helpful_count || 0} found helpful
                    </div>
                `;
                faqList.appendChild(articleEl);
            });
            
            console.log('Loaded ' + data.articles.length + ' articles'); // Debug log
        } else {
            console.log('No articles returned or success=false:', data); // Debug log
            const faqList = document.getElementById('faqList');
            faqList.innerHTML = '<p class="text-sm text-gray-500">No articles available</p>';
        }
    } catch (error) {
        console.error('Error loading FAQ articles:', error);
        const faqList = document.getElementById('faqList');
        faqList.innerHTML = '<p class="text-sm text-red-500">Error loading articles: ' + error.message + '</p>';
    }
}

// Switch between tabs
function switchChatTab(tab) {
    currentTab = tab;
    
    // Get header, search, quick action, and bottom navigation elements
    const header = document.querySelector('.relative.h-32.bg-cover');
    const quickAction = document.querySelector('[id="quickActionBtn"]')?.parentElement;
    const searchBar = document.querySelector('#chatSearchInput')?.parentElement;
    const bottomNav = document.querySelector('.flex.gap-2.border-b.border-t');
    const messagesHeader = document.getElementById('messagesHeader');
    
    // Hide all tabs
    document.getElementById('homeTab').classList.add('hidden');
    document.getElementById('messagesTab').classList.add('hidden');
    document.getElementById('helpTab').classList.add('hidden');
    
    // Remove active state from all buttons
    document.getElementById('homeBtn').classList.remove('active');
    document.getElementById('messagesBtn').classList.remove('active');
    document.getElementById('helpBtn').classList.remove('active');
    
    // Show selected tab
    if (tab === 'home') {
        // Show header, search, quick action, and bottom nav for home tab
        if (header) header.classList.remove('hidden');
        if (quickAction) quickAction.classList.remove('hidden');
        if (searchBar) searchBar.classList.remove('hidden');
        if (bottomNav) bottomNav.classList.remove('hidden');
        if (messagesHeader) messagesHeader.classList.add('hidden');
        
        document.getElementById('homeTab').classList.remove('hidden');
        document.getElementById('homeBtn').classList.add('active');
    } else if (tab === 'messages') {
        // Hide header, search, quick action, and bottom nav for messages tab - full chatbox experience
        if (header) header.classList.add('hidden');
        if (quickAction) quickAction.classList.add('hidden');
        if (searchBar) searchBar.classList.add('hidden');
        if (bottomNav) bottomNav.classList.add('hidden');
        if (messagesHeader) messagesHeader.classList.remove('hidden');
        
        document.getElementById('messagesTab').classList.remove('hidden');
        document.getElementById('messagesBtn').classList.add('active');
        
        // Show welcome message if it's the first time
        initializeMessagesTab();
    } else if (tab === 'help') {
        // Show header, search, quick action, and bottom nav for help tab
        if (header) header.classList.remove('hidden');
        if (quickAction) quickAction.classList.remove('hidden');
        if (searchBar) searchBar.classList.remove('hidden');
        if (bottomNav) bottomNav.classList.remove('hidden');
        if (messagesHeader) messagesHeader.classList.add('hidden');
        
        document.getElementById('helpTab').classList.remove('hidden');
        document.getElementById('helpBtn').classList.add('active');
    }
}

// Search article from FAQ
function searchArticle(query) {
    switchChatTab('messages');
    document.getElementById('chatSearchInput').value = query;
    
    // Send message
    const chatMessages = document.getElementById('chatMessages');
    const messageDiv = document.createElement('div');
    messageDiv.classList.add('flex', 'gap-2');
    messageDiv.innerHTML = `
        <div class="flex-1"></div>
        <div class="bg-blue-600 text-white px-4 py-2 rounded-lg rounded-br-none shadow-sm max-w-xs">
            <p class="text-sm break-words">${escapeHtml(query)}</p>
        </div>
    `;
    chatMessages.appendChild(messageDiv);
    
    // Show loading indicator
    showLoadingIndicator();
    
    sendChatMessageWithText(query);
}

function handleSearchKeypress(event) {
    if (event.key === 'Enter') {
        event.preventDefault();
        const query = document.getElementById('chatSearchInput').value.trim();
        if (query) {
            searchArticle(query);
        }
    }
}

// Chat persistence functions
function saveChatHistory() {
    const messagesContainer = document.getElementById('chatMessages');
    const messages = [];
    
    const messageElements = messagesContainer.querySelectorAll('[data-message-type]');
    messageElements.forEach(el => {
        const type = el.getAttribute('data-message-type');
        const content = el.getAttribute('data-message-content');
        if (type && content) {
            messages.push({ type, content });
        }
    });
    
    localStorage.setItem('chatbot_history', JSON.stringify(messages));
}

function loadChatHistory() {
    const saved = localStorage.getItem('chatbot_history');
    if (saved) {
        try {
            const messages = JSON.parse(saved);
            const messagesContainer = document.getElementById('chatMessages');
            
            messagesContainer.innerHTML = '';
            
            messages.forEach(msg => {
                if (msg.type === 'user') {
                    addChatMessage(msg.content, 'user');
                } else if (msg.type === 'bot') {
                    addChatMessageWithHtml(msg.content, 'bot');
                }
            });
        } catch (e) {
            console.error('Error loading chat history:', e);
        }
    }
}

function clearChatHistory() {
    localStorage.removeItem('chatbot_history');
    const messagesContainer = document.getElementById('chatMessages');
    messagesContainer.innerHTML = `
        <div class="flex gap-2">
            <div class="bg-gray-100 px-4 py-2 rounded-lg rounded-tl-none shadow-sm max-w-xs">
                <p class="text-sm text-gray-800">Hi! 👋 I'm HRDOTNET Genie Bot. Ask me anything about our HR FAQs, policies, or any other information you need!</p>
            </div>
        </div>
    `;
}

function toggleChatbot() {
    const widget = document.getElementById('chatbotWidget');
    const toggleBtn = document.getElementById('chatbotToggle');
    
    isChatMinimized = !isChatMinimized;
    
    if (isChatMinimized) {
        widget.classList.add('chatbot-hidden');
        toggleBtn.classList.remove('hidden');
    } else {
        widget.classList.remove('chatbot-hidden');
        toggleBtn.classList.add('hidden');
    }
}

async function sendChatMessageWithText(message) {
    // Show loading indicator
    showLoadingIndicator();
    
    // Check for category request
    if (message.toLowerCase().includes('categor')) {
        removeLoadingIndicator();
        await displayCategories();
        return;
    }
    
    // Check for article search
    if (message.toLowerCase().includes('search') || message.toLowerCase().includes('find')) {
        removeLoadingIndicator();
        // Extract search term
        const searchTerm = message.replace(/search|find|for|article/gi, '').trim();
        if (searchTerm) {
            await searchArticlesByKeyword(searchTerm);
        }
        return;
    }
    
    try {
        // Call PHP endpoint which routes to Hugging Face API (localhost:5000)
        const response = await fetch('chatbot.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'message=' + encodeURIComponent(message),
            signal: AbortSignal.timeout(60000) // 60 second timeout for slow LLM responses
        });
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        
        const data = await response.json();
        
        if (data.success) {
            removeLoadingIndicator();
            let formattedResponse = data.bot_response;
            
            // Add source links if available
            if (data.data && data.data.sources && Array.isArray(data.data.sources) && data.data.sources.length > 0) {
                formattedResponse += '\n\n---\n\n';
                // Create HTML for sources with clickable links
                let sourceHtml = '📚 <strong>Source:</strong><br>';
                let source = data.data.sources[0];
                sourceHtml += `• <a href="view_article.php?id=${source.article_id}" target="_blank" style="color: #2563eb; text-decoration: underline; cursor: pointer;">${escapeHtml(source.title)}</a> <span style="color: #666; font-size: 0.9em;">(${escapeHtml(source.category)})</span>`;
                if (data.data.sources.length > 1) {
                    sourceHtml += `<br><span style="color: #666; font-size: 0.85em;">Based on ${data.data.sources.length} relevant article(s)</span>`;
                }
                sourceHtml += `<br><span style="color: #999; font-size: 0.85em;">👆 Click to see full details</span>`;
                formattedResponse += sourceHtml;
            }
            
            addChatMessageWithHtml(formattedResponse, 'bot');
        } else {
            removeLoadingIndicator();
            addChatMessage((data.error || data.message || 'Unknown error occurred.'), 'bot');
        }
    } catch (error) {
        console.error('Chat error:', error);
        removeLoadingIndicator();
        
        if (error.message.includes('Failed to fetch')) {
            addChatMessage('⚠️ Sorry, the chatbot service is not available right now. Please try again later.', 'bot');
        } else {
            addChatMessage('❌ ' + error.message || 'Sorry, I couldn\'t connect to the server. Please try again.', 'bot');
        }
    }
}

async function sendChatMessage() {
    const input = document.getElementById('chatSearchInput');
    const message = input.value.trim();
    
    if (!message || message.length === 0) return;
    
    // Don't display the user's question - only show the bot's answer
    // addChatMessage(message, 'user');
    input.value = '';
    
    sendChatMessageWithText(message);
}

function addChatMessage(message, sender) {
    const messagesContainer = document.getElementById('chatMessages');
    const messageDiv = document.createElement('div');
    messageDiv.classList.add('flex', 'gap-2', 'animate-fadeIn');
    messageDiv.setAttribute('data-message-type', sender);
    messageDiv.setAttribute('data-message-content', message);
    
    if (sender === 'user') {
        messageDiv.innerHTML = `
            <div class="flex-1"></div>
            <div class="bg-blue-600 text-white px-4 py-2 rounded-lg rounded-br-none shadow-sm max-w-xs">
                <p class="text-sm break-words">${escapeHtml(message)}</p>
            </div>
        `;
    } else {
        messageDiv.innerHTML = `
            <div class="bg-gray-100 px-4 py-2 rounded-lg rounded-tl-none shadow-sm max-w-xs">
                <div class="text-sm text-gray-800 whitespace-pre-wrap break-words">${formatBotMessage(message)}</div>
            </div>
        `;
    }
    
    messagesContainer.appendChild(messageDiv);
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
    saveChatHistory();
}

function formatBotMessage(message) {
    // Check if this is step-by-step content
    if (/Step\s+\d+:/i.test(message)) {
        // Format step-by-step responses
        // Split by steps and wrap each step with bold label and proper newline
        return message.replace(/Step\s+(\d+):\s*([^\n]*)/gi, function(match, stepNum, content) {
            return '<strong>Step ' + stepNum + ':</strong> ' + escapeHtml(content.trim());
        });
    }
    
    // Regular message formatting (markdown-style links)
    return message.replace(/\[([^\]]+)\]\(([^\)]+)\)/g, '<a href="$2" class="text-blue-600 hover:text-blue-800 underline font-semibold" target="_blank">$1</a>');
}

function addChatMessageWithHtml(htmlContent, sender) {
    const messagesContainer = document.getElementById('chatMessages');
    const messageDiv = document.createElement('div');
    messageDiv.classList.add('flex', 'gap-2', 'animate-fadeIn');
    messageDiv.setAttribute('data-message-type', sender);
    messageDiv.setAttribute('data-message-content', htmlContent);
    
    if (sender === 'user') {
        messageDiv.innerHTML = `
            <div class="flex-1"></div>
            <div class="bg-blue-600 text-white px-4 py-2 rounded-lg rounded-br-none shadow-sm max-w-xs">
                <p class="text-sm break-words">${escapeHtml(htmlContent)}</p>
            </div>
        `;
    } else {
        let formattedContent = htmlContent
            .replace(/\*\*([^\*]+)\*\*/g, '<strong>$1</strong>')
            .replace(/\[([^\]]+)\]\(([^\)]+)\)/g, '<a href="$2" class="text-blue-600 hover:text-blue-800 underline font-semibold" target="_blank">$1</a>')
            .replace(/\n/g, '<br>')
            .replace(/([😊👋🤖📚📂✨🔗💡❌⚠️🟢🟡🔴])/g, '$1');
        
        messageDiv.innerHTML = `
            <div class="bg-gray-100 px-4 py-2 rounded-lg rounded-tl-none shadow-sm max-w-xs">
                <div class="text-sm text-gray-800">${formattedContent}</div>
            </div>
        `;
    }
    
    messagesContainer.appendChild(messageDiv);
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
    saveChatHistory();
}

function showLoadingIndicator() {
    const messagesContainer = document.getElementById('chatMessages');
    const loadingDiv = document.createElement('div');
    loadingDiv.id = 'loadingIndicator';
    loadingDiv.classList.add('flex', 'gap-2');
    loadingDiv.innerHTML = `
        <div class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center flex-shrink-0 text-xs font-bold">
            C
        </div>
        <div class="bg-gray-100 px-4 py-2 rounded-lg rounded-tl-none shadow-sm chat-loading">
            <span></span>
            <span></span>
            <span></span>
        </div>
    `;
    messagesContainer.appendChild(loadingDiv);
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
}

function removeLoadingIndicator() {
    const loadingDiv = document.getElementById('loadingIndicator');
    if (loadingDiv) {
        loadingDiv.remove();
    }
}

// Update send button state based on message input
function updateSendButtonState() {
    const userMessage = document.getElementById('userMessage');
    const sendBtn = document.getElementById('sendBtn');
    const hasMessage = userMessage.value.trim().length > 0;
    
    if (hasMessage) {
        sendBtn.disabled = false;
        sendBtn.classList.remove('disabled-btn');
        sendBtn.classList.add('enabled-btn');
    } else {
        sendBtn.disabled = true;
        sendBtn.classList.remove('enabled-btn');
        sendBtn.classList.add('disabled-btn');
    }
}

// Auto-expand textarea based on content
function autoExpandTextarea() {
    const textarea = document.getElementById('userMessage');
    if (textarea) {
        // Reset height to auto to get the correct scrollHeight
        textarea.style.height = 'auto';
        // Set height to scrollHeight (content height)
        textarea.style.height = textarea.scrollHeight + 'px';
    }
}

// Send user message from Messages tab
function sendUserMessage() {
    const message = document.getElementById('userMessage').value.trim();
    const sendBtn = document.getElementById('sendBtn');
    
    console.log('sendUserMessage called, message:', message);
    
    // Validate input
    if (!message || message.length === 0) {
        console.log('Message is empty');
        return;
    }
    
    // Disable button during send
    sendBtn.disabled = true;
    sendBtn.classList.add('disabled-btn');
    sendBtn.classList.remove('enabled-btn');
    
    console.log('Adding user message to chat');
    // Add user message to chat
    addChatMessage(message, 'user');
    
    // Clear input
    document.getElementById('userMessage').value = '';
    
    // Save message to localStorage
    saveUserMessage(message);
    
    // Send to chatbot API
    sendChatMessageWithText(message);
    
    // Auto-scroll to bottom
    const messagesContainer = document.getElementById('chatMessages');
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
    
    // Reset button state after a short delay
    setTimeout(function() {
        updateSendButtonState();
    }, 500);
}

// Save user message to localStorage
function saveUserMessage(message) {
    const messages = JSON.parse(localStorage.getItem('user_messages') || '[]');
    messages.push({
        message: message,
        timestamp: new Date().toISOString()
    });
    localStorage.setItem('user_messages', JSON.stringify(messages));
}

// Update quick action button based on message history
function updateQuickActionButton() {
    const messages = JSON.parse(localStorage.getItem('user_messages') || '[]');
    const quickActionText = document.getElementById('quickActionText');
    
    // Only update if element exists
    if (!quickActionText) return;
    
    if (messages.length > 0) {
        const lastMessage = messages[messages.length - 1];
        const preview = lastMessage.message.substring(0, 30) + (lastMessage.message.length > 30 ? '...' : '');
        quickActionText.textContent = `📬 Recent: ${preview}`;
    } else {
        quickActionText.textContent = 'Send us a message';
    }
}

// Window Control Functions (Minimize, Maximize/Restore)
function updateMaximizeButton() {
    const widget = document.getElementById('chatbotWidget');
    const expandBtn = document.getElementById('expandBtn');
    const expandBtnMessages = document.getElementById('expandBtnMessages');
    const isExpanded = widget.style.width === '600px' || widget.offsetWidth > 450;
    
    if (isExpanded) {
        if (expandBtn) {
            expandBtn.innerHTML = '<i class="bi bi-arrows-collapse text-lg"></i>';
            expandBtn.title = 'Collapse';
        }
        if (expandBtnMessages) {
            expandBtnMessages.innerHTML = '<i class="bi bi-arrows-collapse text-lg"></i>';
            expandBtnMessages.title = 'Collapse';
        }
    } else {
        if (expandBtn) {
            expandBtn.innerHTML = '<i class="bi bi-arrows-expand text-lg"></i>';
            expandBtn.title = 'Expand';
        }
        if (expandBtnMessages) {
            expandBtnMessages.innerHTML = '<i class="bi bi-arrows-expand text-lg"></i>';
            expandBtnMessages.title = 'Expand';
        }
    }
}

function toggleWindowSize() {
    const widget = document.getElementById('chatbotWidget');
    const isExpanded = widget.style.width === '600px' || widget.offsetWidth > 450;
    
    if (isExpanded) {
        // Collapse to normal size
        widget.style.width = '384px';
        widget.style.height = '600px';
    } else {
        // Expand to larger size
        widget.style.width = '600px';
        widget.style.height = '650px';
    }
    
    updateMaximizeButton();
}


function goBackTab() {
    switchChatTab('home');
    document.getElementById('backBtn').classList.add('hidden');
}

// Display all categories
async function displayCategories() {
    try {
        const response = await fetch('get_categories.php');
        const data = await response.json();
        
        console.log('Categories response:', data);
        
        if (data.success && data.categories && data.categories.length > 0) {
            let categoryList = '📚 **Here are all available categories:**\n\n';
            data.categories.forEach(cat => {
                const desc = cat.description_ && cat.description_.trim() ? cat.description_ : 'HR Information';
                categoryList += `• **${cat.category_name}** - ${desc}\n`;
            });
            addChatMessageWithHtml(categoryList, 'bot');
        } else {
            addChatMessage('Sorry, I couldn\'t load the categories.', 'bot');
        }
    } catch (error) {
        console.error('Error loading categories:', error);
        addChatMessage('❌ Error loading categories. Please try again.', 'bot');
    }
}

// Search articles by keyword
async function searchArticlesByKeyword(keyword) {
    try {
        const response = await fetch(`search_article.php?q=${encodeURIComponent(keyword)}`);
        const data = await response.json();
        
        console.log('Search results:', data);
        
        if (data && data.length > 0) {
            // Filter to only show articles
            const articles = data.filter(item => item.type === 'article');
            
            if (articles.length > 0) {
                let searchResults = `📄 **Found ${articles.length} article(s) related to "${keyword}":**\n\n`;
                articles.slice(0, 5).forEach(article => {
                    searchResults += `• **${article.title}** (${article.category})\n`;
                });
                if (articles.length > 5) {
                    searchResults += `\n... and ${articles.length - 5} more articles.`;
                }
                addChatMessageWithHtml(searchResults, 'bot');
            } else {
                addChatMessage(`Sorry, I couldn't find articles about "${keyword}". Try searching for another topic!`, 'bot');
            }
        } else {
            addChatMessage(`Sorry, I couldn't find articles about "${keyword}". Try searching for another topic!`, 'bot');
        }
    } catch (error) {
        console.error('Error searching articles:', error);
        addChatMessage('❌ Error searching articles. Please try again.', 'bot');
    }
}

// Initialize messages tab with welcome message
function initializeMessagesTab() {
    const messagesContainer = document.getElementById('chatMessages');
    
    // Check if welcome message already exists
    if (messagesContainer.children.length === 0) {
        // Add welcome message
        const welcomeMsg = "👋 Hi! I'm HRDOTNET Genie. I'm here to help you find information about HR policies, articles, and categories.";
        addChatMessage(welcomeMsg, 'bot');
    }
    
    // Auto-scroll to bottom
    setTimeout(function() {
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }, 100);
}

// Handle Enter key in message textarea
document.addEventListener('DOMContentLoaded', function() {
    const userMessageInput = document.getElementById('userMessage');
    if (userMessageInput) {
        userMessageInput.addEventListener('keydown', function(event) {
            if (event.key === 'Enter' && !event.shiftKey) {
                event.preventDefault();
                sendUserMessage();
            }
        });
    }
});

// Initialize chatbot
window.addEventListener('load', function() {
    // Ensure widget and button are properly initialized
    const widget = document.getElementById('chatbotWidget');
    const toggleBtn = document.getElementById('chatbotToggle');
    
    if (widget) {
        // Make sure widget starts hidden
        widget.classList.add('chatbot-hidden');
    }
    
    if (toggleBtn) {
        // Make sure button is visible
        toggleBtn.classList.remove('hidden');
        toggleBtn.style.display = 'flex';
    }
    
    // Set initial state
    isChatMinimized = true;
    
    loadFAQArticles();
    loadChatHistory();
    updateQuickActionButton();
    updateMaximizeButton();
    // Initialize button state
    updateSendButtonState();
});

// Ensure toggle function is robust
const originalToggleChatbot = toggleChatbot;
window._realToggleChatbot = function() {
    const widget = document.getElementById('chatbotWidget');
    const toggleBtn = document.getElementById('chatbotToggle');
    
    if (!widget || !toggleBtn) {
        console.error('Chatbot widget or toggle button not found');
        return;
    }
    
    isChatMinimized = !isChatMinimized;
    
    if (isChatMinimized) {
        widget.classList.add('chatbot-hidden');
        toggleBtn.classList.remove('hidden');
        toggleBtn.style.display = 'flex';
    } else {
        widget.classList.remove('chatbot-hidden');
        widget.style.display = 'flex';
        toggleBtn.classList.add('hidden');
        toggleBtn.style.display = 'none';
        const searchInput = document.getElementById('chatSearchInput');
        if (searchInput) {
            setTimeout(() => searchInput.focus(), 100);
        }
    }
};

// Override the global toggleChatbot with the real implementation
window.toggleChatbot = window._realToggleChatbot;

// Show toast notification
function showToast(message = 'Ticket submitted successfully!', duration = 3000) {
    const toast = document.getElementById('toastNotification');
    const toastMessage = document.getElementById('toastMessage');
    
    if (toast && toastMessage) {
        toastMessage.textContent = message;
        toast.classList.remove('hidden');
        toast.classList.add('flex');
        toast.classList.remove('toast-fade-out');
        
        setTimeout(() => {
            toast.classList.add('toast-fade-out');
            
            setTimeout(() => {
                toast.classList.add('hidden');
                toast.classList.remove('flex');
                toast.classList.remove('toast-fade-out');
            }, 500); // Wait for animation to complete
        }, duration);
    }
}

// Alias for global access
window.showToast = showToast;

// Handle file name display in chatbot context
document.addEventListener('DOMContentLoaded', function() {
    const attachmentInput = document.getElementById('attachmentInput');
    if (attachmentInput) {
        attachmentInput.addEventListener('change', function() {
            const fileName = this.files.length > 0 ? this.files[0].name : 'No file chosen';
            const attachmentName = document.getElementById('attachmentName');
            if (attachmentName) {
                attachmentName.textContent = fileName;
            }
        });
    }
});
</script>
