/**
 * Shared HTML-escaping utility to prevent XSS when inserting
 * API data into innerHTML via template literals.
 */
function escapeHtml(text) {
    if (text === null || text === undefined) return "";
    const map = {
        "&": "&amp;",
        "<": "&lt;",
        ">": "&gt;",
        '"': "&quot;",
        "'": "&#039;",
    };
    return String(text).replace(/[&<>"']/g, (m) => map[m]);
}
