<?php
$pageTitle = 'Chatbot - Waves Support';
require_once '../includes/header.php';
require_once '../includes/navbar.php';
require_once '../includes/functions.php';

requireRole('customer');
?>

<div class="container my-4">
    <h2 class="mb-4"><i class="fas fa-robot me-2"></i>Chatbot Support</h2>

    <div class="chatbot-container">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-robot me-2"></i>Waves AI Assistant</h5>
            </div>
            <div class="card-body">
                <div class="chat-box" id="chatBox">
                    <div class="d-flex">
                        <div class="chat-message chat-bot">
                            Hi! I'm the Waves Support Bot. How can I help you today? You can ask about password issues, login problems, network issues, billing, and more.
                        </div>
                    </div>
                </div>
                <div class="input-group mt-3">
                    <input type="text" id="chatInput" class="form-control" placeholder="Type your message..." onkeypress="if(event.key==='Enter') sendMessage()">
                    <button class="btn btn-primary" onclick="sendMessage()"><i class="fas fa-paper-plane"></i></button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const responses = {
    'password': 'To reset your password, go to your Profile page and use the "Change Password" section. If you cannot login, contact admin at admin@waves.com.',
    'login': 'If you are having trouble logging in: 1) Check your email and password. 2) Make sure your account is active. 3) Clear browser cookies. 4) Contact support if issue persists.',
    'printer': 'For printer issues: 1) Check if the printer is turned on and connected. 2) Restart the printer. 3) Check for paper jams. 4) Reinstall printer drivers. 5) Submit a ticket if unresolved.',
    'network': 'For network issues: 1) Restart your router/modem. 2) Check cable connections. 3) Run network diagnostics. 4) Check if other devices can connect. 5) Contact IT support.',
    'internet': 'For internet problems: 1) Restart your router. 2) Check your ISP status. 3) Try a different browser. 4) Clear DNS cache. 5) Submit a ticket for further help.',
    'billing': 'For billing inquiries: 1) Check your invoice in your account. 2) Verify payment method. 3) Contact billing@waves-tech.com. 4) Submit a billing ticket for disputes.',
    'software': 'For software issues: 1) Restart the application. 2) Check for updates. 3) Reinstall if needed. 4) Check system requirements. 5) Submit a ticket with error details.',
    'hardware': 'For hardware problems: 1) Check all physical connections. 2) Restart the device. 3) Check warranty status. 4) Submit a ticket with device details and photos.',
    'ticket': 'To submit a ticket: Go to Dashboard > Submit New Ticket. Fill in the title, category, description, and attach files if needed. Our AI will classify and prioritize it automatically.',
    'status': 'To check your ticket status: Go to Dashboard > My Tickets. You can see all your tickets with their current status, priority, and AI analysis.',
    'hello': 'Hello! Welcome to Waves Support. How can I assist you today?',
    'hi': 'Hi there! How can I help you?',
    'thank': 'You are welcome! Is there anything else I can help with?',
    'bye': 'Goodbye! Feel free to come back anytime you need help.',
    'help': 'I can help with: password issues, login problems, printer setup, network/internet issues, billing questions, software problems, hardware issues, and ticket management. What do you need?'
};

function sendMessage() {
    const input = document.getElementById('chatInput');
    const message = input.value.trim();
    if (!message) return;

    const chatBox = document.getElementById('chatBox');

    // User message
    chatBox.innerHTML += `<div class="d-flex justify-content-end"><div class="chat-message chat-user">${escapeHtml(message)}</div></div>`;

    // Bot response
    const reply = getBotReply(message);
    chatBox.innerHTML += `<div class="d-flex"><div class="chat-message chat-bot">${reply}</div></div>`;

    // Log to database
    fetch('chatbot_log.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `question=${encodeURIComponent(message)}&response=${encodeURIComponent(reply)}`
    });

    input.value = '';
    chatBox.scrollTop = chatBox.scrollHeight;
}

function getBotReply(message) {
    const msg = message.toLowerCase();
    for (const [key, value] of Object.entries(responses)) {
        if (msg.includes(key)) {
            return value;
        }
    }
    return 'I\'m not sure about that. Please submit a support ticket at <a href="submit_ticket.php">Submit Ticket</a> for detailed assistance, or try asking about: password, login, network, billing, software, or hardware issues.';
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>

<?php require_once '../includes/footer.php'; ?>