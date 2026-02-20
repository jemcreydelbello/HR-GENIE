<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION['client_id'])) {
    header('Location: userlogin.php');
    exit();
}

include 'connect.php';

$user_name = isset($_SESSION['client_name']) ? htmlspecialchars($_SESSION['client_name']) : '';
$user_email = isset($_SESSION['client_email']) ? htmlspecialchars($_SESSION['client_email']) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Tickets - HR Genie</title>
    <link rel="icon" type="image/jpeg" href="../admin/assets/img/intellismart.jpg">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <?php include 'navbar.php'; ?>

    <div class="w-full max-w-5xl mx-auto py-4 sm:py-8 px-3 sm:px-4">
        <!-- Back Button -->
        <a href="index.php" class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-900 transition mb-4 sm:mb-6 text-sm sm:text-base">
            <i class="bi bi-arrow-return-left"></i> Back to Home
        </a>

        <!-- Page Header -->
         <div class="mb-6 sm:mb-8">
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 sm:gap-0 mb-4">
                <div>
                    <h1 class="text-2xl sm:text-3xl md:text-4xl font-bold text-gray-900 mb-1 sm:mb-2">My Support Tickets</h1>
                    <p class="text-sm sm:text-base text-gray-600">Track and manage your submitted support tickets</p>
                </div>
                <button onclick="openSubmitTicketModal()" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 sm:px-6 rounded-lg transition-colors flex items-center gap-2 w-full sm:w-auto justify-center sm:justify-start text-sm sm:text-base">
                    <i class="bi bi-plus-circle"></i> <span>Submit New Ticket</span>
                </button>
            </div>
            <hr class="border-gray-300 mt-4">
        </div>

        <!-- Tickets List -->
        <div class="space-y-4" id="ticketsList">
            <div class="text-center py-8 sm:py-12">
                <div class="text-4xl sm:text-5xl mb-4"><i class="bi bi-ticket-detailed text-gray-400"></i></div>
                <p class="text-gray-500 text-sm sm:text-base">Loading your tickets...</p>
            </div>
        </div>
    </div>

    <!-- Ticket Details Modal -->
    <div class="hidden fixed inset-0 z-50 flex items-start justify-center bg-black/50 p-2 sm:p-4 pt-20 sm:pt-24" id="ticketModal" onclick="if(event.target === this) closeTicketModal()">
        <div class="w-full max-w-5xl max-h-[90vh] bg-white rounded-lg shadow-2xl overflow-hidden flex flex-col" onclick="event.stopPropagation()">
            <!-- Modal Header -->
            <div class="flex items-center justify-between px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200 bg-white sticky top-0">
                <h3 class="text-lg sm:text-xl font-semibold text-gray-900 truncate" id="detailsTitle">Ticket Details</h3>
                <button onclick="closeTicketModal()" class="text-gray-400 hover:text-gray-600 text-2xl font-light flex-shrink-0 ml-2">&times;</button>
            </div>

            <!-- Modal Content - 2 Columns -->
            <div class="flex-1 overflow-y-auto">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 p-4 sm:p-6">
                    <!-- Left Column: Ticket Information -->
                    <div class="space-y-6">
                        <!-- Ticket Information Section -->
                        <div>
                            <h4 class="text-xs font-semibold text-gray-600 uppercase tracking-wide mb-4 pb-2 border-b border-gray-200">Ticket Information</h4>
                            
                            <div class="space-y-4">
                                <div>
                                    <span class="block text-xs font-semibold text-gray-500 uppercase mb-1">Ticket Number</span>
                                    <span id="detailsNumber" class="text-base sm:text-lg font-mono font-semibold text-blue-600"></span>
                                </div>
                                
                                <div>
                                    <span class="block text-xs font-semibold text-gray-500 uppercase mb-1">Subject</span>
                                    <span id="detailsSubject" class="text-gray-900 text-sm sm:text-base break-words font-medium"></span>
                                </div>
                                
                                <div>
                                    <span class="block text-xs font-semibold text-gray-500 uppercase mb-1">Status</span>
                                    <span id="detailsStatus" class="inline-block px-3 py-1 rounded-full text-xs font-semibold"></span>
                                </div>
                                
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <span class="block text-xs font-semibold text-gray-500 uppercase mb-1">Submitted</span>
                                        <span id="detailsDate" class="text-gray-700 text-xs sm:text-sm font-medium"></span>
                                    </div>
                                    
                                    <div id="resolvedDateSection" class="hidden">
                                        <span class="block text-xs font-semibold text-gray-500 uppercase mb-1">Resolved</span>
                                        <span id="detailsResolvedDate" class="text-gray-700 text-xs sm:text-sm font-medium"></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Description Section -->
                        <div>
                            <h4 class="text-xs font-semibold text-gray-600 uppercase tracking-wide mb-3 pb-2 border-b border-gray-200">Description</h4>
                            <div class="bg-gradient-to-br from-gray-50 to-gray-100 p-4 rounded-lg border border-gray-200 shadow-sm">
                                <span id="detailsDescription" class="whitespace-pre-wrap text-gray-700 text-xs sm:text-sm leading-relaxed block font-medium"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Attachment/Image -->
                    <div id="attachmentSection" class="hidden flex flex-col">
                        <h4 class="text-xs font-semibold text-gray-600 uppercase tracking-wide mb-3 pb-2 border-b border-gray-200">Attachment</h4>
                        <div id="attachmentBlock" class="flex-1 flex flex-col min-h-0"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentTicketId = null;

        // Load user tickets on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadUserTickets();
            
            // Check if URL parameter requests modal to be opened
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('openModal') === 'true') {
                setTimeout(() => {
                    openSubmitTicketModal();
                    window.history.replaceState({}, document.title, window.location.pathname);
                }, 100);
            }
        });

        // Load user tickets
        async function loadUserTickets() {
            try {
                const response = await fetch('get_user_tickets.php');
                const data = await response.json();

                const ticketsList = document.getElementById('ticketsList');

                if (!data.success) {
                    ticketsList.innerHTML = '<div class="text-center py-12"><p class="text-red-600">Error loading tickets</p></div>';
                    return;
                }

                if (data.tickets.length === 0) {
                    ticketsList.innerHTML = `
                        <div class="text-center py-8 sm:py-12">
                            <div class="text-4xl sm:text-5xl mb-4"><i class="bi bi-ticket-detailed text-gray-400"></i></div>
                            <p class="text-gray-500 mb-4 text-sm sm:text-base">No tickets submitted yet</p>
                            <a href="#" onclick="event.preventDefault(); openSubmitTicketModal();" class="text-blue-600 hover:text-blue-800 font-medium cursor-pointer text-sm sm:text-base">Submit your first ticket</a>
                        </div>
                    `;
                    return;
                }

                ticketsList.innerHTML = data.tickets.map(ticket => `
                    <div class="bg-white border border-gray-200 rounded-lg p-4 sm:p-6 cursor-pointer hover:shadow-md hover:-translate-y-1 transition-all">
                        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-3 sm:gap-4 mb-4">
                            <div class="min-w-0 flex-1">
                                <div class="text-blue-600 text-xs sm:text-sm font-semibold">${ticket.ticket_no}</div>
                                <div class="text-base sm:text-lg font-semibold text-gray-900 mt-1 break-words">${escapeHtml(decodeHtml(ticket.subject))}</div>
                            </div>
                            <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold whitespace-nowrap ${ticket.status.toLowerCase() === 'pending' ? 'bg-gray-100 text-gray-800' : ticket.status.toLowerCase() === 'in progress' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800'}">${ticket.status}</span>
                        </div>

                        <div class="flex gap-4 sm:gap-6 mb-4 text-xs sm:text-sm text-gray-600 flex-wrap">
                            <div>
                                <span class="font-semibold text-gray-800 text-xs uppercase block">Submitted</span>
                                <div class="mt-0.5">${ticket.created_at}</div>
                            </div>
                            ${ticket.date_resolved ? `
                                <div>
                                    <span class="font-semibold text-gray-800 text-xs uppercase block">Resolved</span>
                                    <div class="mt-0.5">${ticket.date_resolved}</div>
                                </div>
                            ` : ''}
                        </div>

                        <div class="text-gray-700 mb-3 line-clamp-2 text-sm" onclick="event.stopPropagation(); viewTicket('${ticket.ticket_id}')">${escapeHtml(decodeHtml(ticket.description))}</div>

                        ${ticket.attachment ? `
                            <div class="inline-flex items-center gap-2 px-3 py-2 bg-blue-50 border border-blue-200 rounded-lg text-blue-600 text-xs sm:text-sm font-medium">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                    <polyline points="7 10 12 15 17 10"></polyline>
                                    <line x1="12" y1="15" x2="12" y2="3"></line>
                                </svg>
                                Attachment included
                            </div>
                        ` : ''}
                    </div>
                `).join('');

                // Add click handlers to cards
                document.querySelectorAll('#ticketsList > div').forEach(card => {
                    card.addEventListener('click', function() {
                        const ticketNo = this.querySelector('.text-blue-600').textContent;
                        const tickets = data.tickets;
                        const ticket = tickets.find(t => t.ticket_no === ticketNo);
                        if (ticket) {
                            viewTicket(ticket.ticket_id);
                        }
                    });
                });

            } catch (error) {
                console.error('Error loading tickets:', error);
                document.getElementById('ticketsList').innerHTML = '<div class="text-center py-12"><p class="text-red-600">Error loading tickets</p></div>';
            }
        }

        // View ticket details
        function viewTicket(ticketId) {
            currentTicketId = ticketId;
            const modal = document.getElementById('ticketModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.style.overflow = 'hidden';

            fetch('get_user_tickets.php')
                .then(response => response.json())
                .then(data => {
                    const ticket = data.tickets.find(t => t.ticket_id == ticketId);
                    if (ticket) {
                        document.getElementById('detailsTitle').textContent = decodeHtml(ticket.subject);
                        document.getElementById('detailsNumber').textContent = ticket.ticket_no;
                        document.getElementById('detailsSubject').textContent = decodeHtml(ticket.subject);
                        
                        const statusElement = document.getElementById('detailsStatus');
                        statusElement.textContent = ticket.status;
                        const statusLower = ticket.status.toLowerCase();
                        let statusClass = 'bg-green-100 text-green-800';
                        if (statusLower === 'pending') {
                            statusClass = 'bg-gray-100 text-gray-800';
                        } else if (statusLower === 'in progress') {
                            statusClass = 'bg-yellow-100 text-yellow-800';
                        }
                        statusElement.className = `inline-block px-3 py-1 rounded-full text-xs font-semibold ${statusClass}`;
                        
                        document.getElementById('detailsDate').textContent = ticket.created_at;
                        document.getElementById('detailsDescription').textContent = decodeHtml(ticket.description);

                        const resolvedDateSection = document.getElementById('resolvedDateSection');
                        if (ticket.date_resolved) {
                            resolvedDateSection.classList.remove('hidden');
                            document.getElementById('detailsResolvedDate').textContent = ticket.date_resolved;
                        } else {
                            resolvedDateSection.classList.add('hidden');
                        }

                        const attachmentSection = document.getElementById('attachmentSection');
                        if (ticket.attachment) {
                            attachmentSection.classList.remove('hidden');
                            // Convert file path to web-accessible URL with correct admin folder path
                            const fileName = ticket.attachment.split(/[\\/]/).pop();
                            const attachmentUrl = `../admin/uploads/tickets/${fileName}`;
                            const fileExt = fileName.split('.').pop().toUpperCase();
                            const imageExtensions = ['PNG', 'JPG', 'JPEG', 'GIF', 'WEBP', 'BMP', 'SVG'];
                            const isImage = imageExtensions.includes(fileExt);
                            
                            if (isImage) {
                                // Display image directly
                                document.getElementById('attachmentBlock').innerHTML = `
                                    <div class="flex-1 flex flex-col bg-gradient-to-br from-gray-50 to-gray-100 rounded-lg border border-gray-200 overflow-hidden">
                                        <div class="flex-1 flex items-center justify-center p-2 sm:p-4 min-h-[300px]">
                                            <img src="${attachmentUrl}" alt="${escapeHtml(fileName)}" class="max-w-full max-h-full object-contain rounded shadow-sm" onerror="this.parentElement.innerHTML='<div class=\\'text-center text-gray-500 flex flex-col items-center gap-2\\'><svg width=\\'32\\'height=\\'32\\' class=\\'opacity-50\\' viewBox=\\'0 0 24 24\\' fill=\\'none\\' stroke=\\'currentColor\\' stroke-width=\\'2\\'><rect x=\\'3\\' y=\\'3\\' width=\\'18\\' height=\\'18\\' rx=\\'2\\'/><circle cx=\\'8.5\\' cy=\\'8.5\\' r=\\'1.5\\'/><path d=\\'M21 15l-5-5L5 21\\'/></svg><div>Failed to load image</div></div>'">
                                        </div>
                                        <div class="px-3 sm:px-4 py-2 sm:py-3 bg-white border-t border-gray-200">
                                            <p class="text-xs sm:text-sm font-semibold text-gray-900 truncate">${escapeHtml(fileName)}</p>
                                            <p class="text-xs text-gray-600 mt-0.5">${fileExt} Image</p>
                                        </div>
                                    </div>
                                `;
                            } else {
                                // Display file download for non-image files
                                document.getElementById('attachmentBlock').innerHTML = `
                                    <div class="flex flex-col gap-4 h-full justify-center p-4 bg-gradient-to-br from-gray-50 to-gray-100 rounded-lg border border-gray-200">
                                        <div class="text-center">
                                            <div class="text-blue-600 mb-3 flex justify-center text-5xl">
                                                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                                    <polyline points="7 10 12 15 17 10"></polyline>
                                                    <line x1="12" y1="15" x2="12" y2="3"></line>
                                                </svg>
                                            </div>
                                            <p class="font-semibold text-gray-900 text-sm sm:text-base mb-1">${escapeHtml(fileName)}</p>
                                            <p class="text-gray-600 text-xs sm:text-sm mb-4">${fileExt} File</p>
                                            <a href="${attachmentUrl}" download class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-blue-600 to-orange-500 text-white font-semibold text-sm rounded-lg hover:shadow-lg hover:-translate-y-0.5 transition">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                                    <polyline points="7 10 12 15 17 10"></polyline>
                                                    <line x1="12" y1="15" x2="12" y2="3"></line>
                                                </svg>
                                                Download File
                                            </a>
                                        </div>
                                    </div>
                                `;
                            }
                        } else {
                            attachmentSection.classList.add('hidden');
                        }
                    }
                });
        }

        // Close ticket modal
        function closeTicketModal() {
            const modal = document.getElementById('ticketModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.style.overflow = '';
            currentTicketId = null;
        }

        // Escape HTML
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

        // Decode HTML entities
        function decodeHtml(html) {
            const txt = document.createElement('textarea');
            txt.innerHTML = html;
            return txt.value;
        }

        // Close modal on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeTicketModal();
                closeSubmitTicketModal();
            }
        });

        // Close modal when clicking outside
        window.onclick = function(event) {
            const ticketModal = document.getElementById('ticketModal');
            const submitTicketModal = document.getElementById('submitTicketModal');
            if (event.target === ticketModal) {
                closeTicketModal();
            }
            if (event.target === submitTicketModal) {
                closeSubmitTicketModal();
            }
        };
    </script>

<?php include 'chatbot_widget.php'; ?>

</body>
</html>