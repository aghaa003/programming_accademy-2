/**
 * AI Chat Modal Loader
 * Fetches ai_chat_modal.html and injects the modal HTML, styles, and script into the page.
 */
(function () {
    fetch("./ai_chat_modal.html")
        .then(function (r) {
            return r.text();
        })
        .then(function (html) {
            var temp = document.createElement("div");
            temp.innerHTML = html;

            // Inject modal HTML (the <div id="aiChatModal">)
            var modalEl = temp.querySelector("#aiChatModal");
            if (modalEl) document.body.appendChild(modalEl);

            // Inject styles
            temp.querySelectorAll("style").forEach(function (s) {
                document.head.appendChild(s.cloneNode(true));
            });

            // Execute scripts (innerHTML doesn't execute scripts, so we recreate them)
            temp.querySelectorAll("script").forEach(function (oldScript) {
                var newScript = document.createElement("script");
                newScript.textContent = oldScript.textContent;
                document.body.appendChild(newScript);
            });
        })
        .catch(function (e) {
            console.warn("AI modal load failed:", e);
        });
})();
