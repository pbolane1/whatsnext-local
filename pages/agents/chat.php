<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Chatbase with Internal Link Modal</title>
<style>
#chat-modal {
display: none;
position: fixed;
top: 10%;
left: 10%;
width: 80%;
height: 80%;
background: white;
z-index: 1000;
border: 2px solid #ccc;
box-shadow: 0 0 10px rgba(0,0,0,0.3);
}

#chat-modal iframe {
width: 100%;
height: 90%;
border: none;
}

#chat-modal .modal-header {
background: #f1f1f1;
padding: 8px;
display: flex;
justify-content: space-between;
align-items: center;
}

#chat-modal .modal-header button {
background: transparent;
border: none;
font-size: 16px;
cursor: pointer;
}
</style>
</head>
<body>

<!-- Modal Container -->
<div id="chat-modal">
<div class="modal-header">
<span>Quick View</span>
<button onclick="closeModal()">?</button>
</div>
<iframe id="chat-iframe"></iframe>
</div>

<!-- Your App Content -->
<h1>Welcome to What's Next</h1>

<!-- Chatbase Embed -->
<script>
window.chatbaseConfig = {
chatbotId: "-ZZX0z_GM5ODnsS7KY_73",
};
</script>
<script src="https://www.chatbase.co/embed.min.js" id="chatbase-script" defer></script>

<!-- Link Interceptor Script -->
<script>
document.addEventListener("click", function (e) {
const link = e.target.closest("a");
if (!link) return;

const href = link.getAttribute("href");
if (!href || !href.startsWith("https://whatsnext.realestate")) return;

// Intercept internal links and open in modal
e.preventDefault();
openInAppModal(href);
});

function openInAppModal(url) {
document.getElementById("chat-iframe").src = url;
document.getElementById("chat-modal").style.display = "block";
}

function closeModal() {
document.getElementById("chat-modal").style.display = "none";
document.getElementById("chat-iframe").src = "";
}
</script>

</body>
</html>