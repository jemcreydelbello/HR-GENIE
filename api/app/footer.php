<!-- Footer -->
<footer class="bg-[#559CDA] text-white py-6 mt-8 not-italic">
    <div class="w-full px-12">
        <!-- Top Row - Logo Only -->
        <div>
            <img src="assets/img/intelli.png" alt="Intellismart" class="h-10 mb-2">
        </div>

        <!-- Top Section with Logo, Nav, and Socials -->
        <div class="grid grid-cols-1 md:grid-cols-8 gap-6 mb-4 pb-4 border-b border-blue-400 border-opacity-30">
            
            <!-- Left Side - Logo and Company Info (Column 1) -->
            <div class="md:col-span-2">
                <p class="text-xs text-white leading-relaxed">
                    <span class="font-semibold">Head Office</span><br>
                    12 Catanduanes Street, Brgy Paltok West Ave., Quezon City, Philippines 1105<br>
                    +632 8350 5986 (loc. 348) | +632 8352 0377<br>
                    sales@intellismartinc.com
                </p>
            </div>

            <!-- Center - Navigation Links (Column 2) -->
            <div class="md:col-span-4 flex flex-col items-start justify-start">
                <p class="text-sm font-bold text-white mb-2">Company</p>
                <div class="flex flex-col items-start gap-1">
                    <?php
                        $current_page = basename($_SERVER['SCRIPT_NAME']);
                        $nav_items = [
                            'index.php' => 'Home',
                            'intellismart.php' => 'Intellismart',
                            'guide.php' => 'Guide',
                            'system_description.php' => 'System Description'
                        ];
                    ?>
                    <?php foreach ($nav_items as $page => $label): ?>
                        <a href="<?php echo $page; ?>" 
                           class="text-sm text-white hover:underline transition">
                            <?php echo $label; ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Right Side - Submit Ticket Card and Social Media (Column 3) -->
            <div class="md:col-span-2 flex flex-col items-center gap-4">
                <!-- Submit a Ticket Card -->
                <div class="bg-gradient-to-r from-blue-600 to-blue-700 p-3 rounded-lg shadow-sm text-white w-64">
                    <div class="flex items-start gap-2 mb-2">
                        <div class="flex-shrink-0 w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center">
                            <i class="bi bi-ticket text-white text-sm"></i>
                        </div>
                        <div>
                            <h4 class="font-semibold text-white text-sm">Submit a Ticket</h4>
                            <p class="text-xs text-blue-50">Get help from our HR team</p>
                        </div>
                    </div>
                    <a href="submit_support.php" class="w-full bg-white text-blue-600 hover:bg-blue-50 font-semibold py-1.5 px-3 rounded text-sm transition flex items-center justify-center gap-1.5">
                        <i class="bi bi-send text-xs"></i>
                        Submit Ticket
                    </a>
                </div>
                
                <!-- Hrdotnet Genie and Social Media Icons -->
                <div class="flex flex-col items-center gap-2">
                    <p class="text-xs font-semibold text-white">Hrdotnet Genie</p>
                    <div class="flex flex-row items-center gap-2">
                        <a href="https://facebook.com" target="_blank" class="flex items-center justify-center w-8 h-8 bg-blue-600 rounded-full hover:bg-blue-700 transition">
                            <i class="bi bi-facebook text-white text-sm"></i>
                        </a>
                        <a href="https://instagram.com" target="_blank" class="flex items-center justify-center w-8 h-8 bg-pink-600 rounded-full hover:bg-pink-700 transition">
                            <i class="bi bi-instagram text-white text-sm"></i>
                        </a>
                        <a href="https://linkedin.com" target="_blank" class="flex items-center justify-center w-8 h-8 bg-blue-500 rounded-full hover:bg-blue-600 transition">
                            <i class="bi bi-linkedin text-white text-sm"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bottom Section - Copyright -->
        <div class="text-center">
            <p class="text-xs text-white text-[14px]">&copy; 2026 Hrdotnet Genie. All Rights Reserved.</p>
        </div>
    </div>
</footer>