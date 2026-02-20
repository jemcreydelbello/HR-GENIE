<!-- Shared Submit Ticket Modal - Can be included in any page -->
<div id="submitTicketModal" class="hidden fixed inset-0 z-[10000] flex items-center justify-center bg-black/50 p-4" onclick="if(event.target === this) closeSubmitTicketModal()">
    <div class="w-full max-w-2xl bg-white rounded-lg shadow-xl p-8 space-y-4 overflow-y-auto max-h-[90vh]" onclick="event.stopPropagation()">
        <!-- Modal Header -->
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-2xl font-bold text-gray-900">Submit a Ticket</h3>
            <button onclick="closeSubmitTicketModal()" class="text-gray-500 hover:text-gray-700">
                <i class="bi bi-x text-2xl"></i>
            </button>
        </div>
        
        <form id="submitTicketForm" class="space-y-4">
            <!-- Full Name and Email Row -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-900 mb-2">Full Name <span class="text-red-500">*</span></label>
                    <input type="text" name="full_name" required class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Enter your full name" value="<?php echo isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : ''; ?>">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-900 mb-2">Email <span class="text-red-500">*</span></label>
                    <input type="email" name="email" required class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="your.email@example.com" value="<?php echo isset($_SESSION['user_email']) ? htmlspecialchars($_SESSION['user_email']) : ''; ?>">
                </div>
            </div>
            
            <!-- Category -->
            <div>
                <label class="block text-sm font-medium text-gray-900 mb-2">Category <span class="text-red-500">*</span></label>
                <select name="category" required class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Select a category</option>
                </select>
            </div>
            
            <!-- Subject -->
            <div>
                <label class="block text-sm font-medium text-gray-900 mb-2">Subject <span class="text-red-500">*</span></label>
                <input type="text" name="subject" required class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Brief description of your issue">
            </div>
            
            <!-- Message -->
            <div>
                <label class="block text-sm font-medium text-gray-900 mb-2">Message <span class="text-red-500">*</span></label>
                <textarea name="message" rows="5" required class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none" placeholder="Please provide detailed information about your issue"></textarea>
            </div>
            
            <!-- Attachment -->
            <div>
                <label class="block text-sm font-medium text-gray-900 mb-2">Attachment (Optional)</label>
                <div class="flex items-center gap-2">
                    <input type="file" name="attachment" id="attachmentInput" class="hidden" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.gif">
                    <button type="button" onclick="document.getElementById('attachmentInput').click()" class="px-6 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Choose File
                    </button>
                    <span id="attachmentName" class="text-sm text-gray-600">No file chosen</span>
                </div>
            </div>
            
            <!-- Buttons -->
            <div class="flex gap-3 pt-4">
                <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-lg text-sm transition">
                    Submit Ticket
                </button>
                <button type="reset" class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-3 px-4 rounded-lg text-sm transition">
                    Clear
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Open submit ticket modal
    function openSubmitTicketModal() {
        const modal = document.getElementById('submitTicketModal');
        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.style.overflow = 'hidden';
            
            // Reset form and load categories
            const form = document.getElementById('submitTicketForm');
            if (form) {
                form.reset();
                // Reset attachment name
                const attachmentName = document.getElementById('attachmentName');
                if (attachmentName) {
                    attachmentName.textContent = 'No file chosen';
                }
            }
            
            // Load categories
            loadSubmitTicketCategories();
        }
    }
    
    // Close submit ticket modal
    function closeSubmitTicketModal() {
        const modal = document.getElementById('submitTicketModal');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.style.overflow = '';
        }
    }
    
    // Load categories for the ticket form
    function loadSubmitTicketCategories() {
        const categorySelect = document.querySelector('#submitTicketForm select[name="category"]');
        if (!categorySelect) return;
        
        fetch('get_ticket_categories.php')
            .then(response => response.json())
            .then(data => {
                if (data.success && data.categories) {
                    categorySelect.innerHTML = '<option value="">Select a category</option>';
                    data.categories.forEach(cat => {
                        const option = document.createElement('option');
                        option.value = cat.category_id;
                        option.textContent = cat.category_name;
                        categorySelect.appendChild(option);
                    });
                }
            })
            .catch(error => console.log('Could not load categories:', error));
    }
    
    // Handle file name display
    document.addEventListener('DOMContentLoaded', function() {
        const attachmentInput = document.getElementById('attachmentInput');
        if (attachmentInput) {
            attachmentInput.addEventListener('change', function() {
                const fileName = this.files.length > 0 ? this.files[0].name : 'No file chosen';
                document.getElementById('attachmentName').textContent = fileName;
            });
        }
    });
    
    // Handle form submission using event delegation
    document.addEventListener('submit', function(e) {
        if (e.target && e.target.id === 'submitTicketForm') {
            e.preventDefault();
            
            const form = e.target;
            const formData = new FormData(form);
            
            // Send to server with FormData (handles file uploads)
            fetch('submit_ticket.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    // Show toast if available, otherwise show alert
                    if (window.showToast) {
                        window.showToast('Ticket submitted successfully!', 3000);
                    } else {
                        alert('Ticket submitted successfully!');
                    }
                    
                    form.reset();
                    const attachmentName = document.getElementById('attachmentName');
                    if (attachmentName) {
                        attachmentName.textContent = 'No file chosen';
                    }
                    
                    setTimeout(() => {
                        closeSubmitTicketModal();
                        
                        // Reload tickets if function exists (my_tickets.php)
                        if (window.loadUserTickets) {
                            window.loadUserTickets();
                        }
                    }, 500);
                } else {
                    alert('Error: ' + (result.error || 'Failed to submit ticket'));
                }
            })
            .catch(error => {
                console.error('Error submitting ticket:', error);
                alert('An error occurred. Please try again.');
            });
        }
    });
    
    // Make functions globally accessible
    window.openSubmitTicketModal = openSubmitTicketModal;
    window.closeSubmitTicketModal = closeSubmitTicketModal;
</script>
