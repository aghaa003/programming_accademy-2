// site-notification.js
// Lightweight shared notification for the site. Use showNotification(message, type='info', timeout=4000)
(function () {
  function ensureEl() {
    let el = document.getElementById("siteNotification");
    if (!el) {
      el = document.createElement("div");
      el.id = "siteNotification";
      el.style.position = "fixed";
      el.style.top = "20px";
      el.style.right = "20px";
      el.style.zIndex = 2000;
      el.style.display = "none";
      el.style.padding = "12px 18px";
      el.style.borderRadius = "8px";
      el.style.color = "#fff";
      el.style.fontWeight = "600";
      el.style.boxShadow = "0 8px 30px rgba(0,0,0,0.2)";
      document.body.appendChild(el);
    }
    return el;
  }

  window.showNotification = function (message, type = "info", timeout = 4000) {
    const el = ensureEl();
    el.style.display = "block";
    el.textContent = message;
    if (type === "success") el.style.background = "#10b981";
    else if (type === "error") el.style.background = "#ef4444";
    else el.style.background = "#2563eb";
    clearTimeout(el._hideTimer);
    el._hideTimer = setTimeout(() => {
      el.style.display = "none";
    }, timeout);
  };
})();
