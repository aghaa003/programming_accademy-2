// Reusable Login Modal for Programming Academy
// This script provides a consistent login modal across all pages

(function () {
  "use strict";

  // Create modal HTML and inject into page
  function createLoginModal() {
    const modalHTML = `
      <div id="loginModal" class="login-modal-overlay" style="display: none;">
        <div class="login-modal-content">
          <div class="login-modal-header">
            <h3>تنبيه</h3>
            <span class="login-modal-close" id="closeLoginModal">&times;</span>
          </div>
          <div class="login-modal-body">
            <div class="login-modal-icon">
              <i class="fas fa-lock"></i>
            </div>
            <p class="login-modal-message">يجب عليك تسجيل الدخول للوصول إلى هذا المحتوى.</p>
            <div class="login-modal-actions">
              <a href="login1.html" class="login-modal-btn-primary" id="modalLoginBtn">تسجيل الدخول</a>
              <button class="login-modal-btn-secondary" id="cancelLoginBtn">إلغاء</button>
            </div>
          </div>
        </div>
      </div>
    `;

    const modalStyles = `
      <style id="loginModalStyles">
        .login-modal-overlay {
          display: none;
          position: fixed;
          z-index: 10000;
          left: 0;
          top: 0;
          width: 100%;
          height: 100%;
          background-color: rgba(0, 0, 0, 0.5);
          animation: fadeIn 0.3s;
        }

        .login-modal-content {
          background-color: #fff;
          margin: 15% auto;
          padding: 0;
          border-radius: 10px;
          width: 90%;
          max-width: 500px;
          box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
          animation: slideIn 0.3s;
          overflow: hidden;
        }

        .login-modal-header {
          padding: 20px;
          background: linear-gradient(135deg, #4361ee, #3a0ca3);
          color: white;
          display: flex;
          justify-content: space-between;
          align-items: center;
        }

        .login-modal-header h3 {
          margin: 0;
          font-size: 1.2rem;
        }

        .login-modal-close {
          color: white;
          font-size: 28px;
          font-weight: bold;
          cursor: pointer;
          transition: all 0.3s ease;
          line-height: 1;
        }

        .login-modal-close:hover {
          color: #f0f0f0;
          transform: scale(1.1);
        }

        .login-modal-body {
          padding: 30px 20px;
          text-align: center;
        }

        .login-modal-icon {
          font-size: 3rem;
          color: #f59e0b;
          margin-bottom: 20px;
        }

        .login-modal-message {
          font-size: 1.1rem;
          color: #1e293b;
          margin-bottom: 30px;
        }

        .login-modal-actions {
          display: flex;
          justify-content: center;
          gap: 15px;
          flex-wrap: wrap;
        }

        .login-modal-btn-primary {
          background: #4361ee;
          color: white;
          padding: 10px 25px;
          border-radius: 5px;
          text-decoration: none;
          font-weight: 600;
          transition: all 0.3s ease;
          border: none;
          cursor: pointer;
        }

        .login-modal-btn-primary:hover {
          background: #3a0ca3;
          transform: translateY(-2px);
        }

        .login-modal-btn-secondary {
          background: #e2e8f0;
          color: #1e293b;
          padding: 10px 25px;
          border-radius: 5px;
          border: none;
          font-weight: 600;
          cursor: pointer;
          transition: all 0.3s ease;
        }

        .login-modal-btn-secondary:hover {
          background: #cbd5e1;
        }

        @keyframes fadeIn {
          from { opacity: 0; }
          to { opacity: 1; }
        }

        @keyframes slideIn {
          from { transform: translateY(-50px); opacity: 0; }
          to { transform: translateY(0); opacity: 1; }
        }

        @media (max-width: 576px) {
          .login-modal-actions {
            flex-direction: column;
            width: 100%;
          }
          
          .login-modal-btn-primary,
          .login-modal-btn-secondary {
            width: 100%;
          }
        }
      </style>
    `;

    // Inject styles
    if (!document.getElementById("loginModalStyles")) {
      document.head.insertAdjacentHTML("beforeend", modalStyles);
    }

    // Inject modal HTML
    if (!document.getElementById("loginModal")) {
      document.body.insertAdjacentHTML("beforeend", modalHTML);
    }
  }

  // Show login modal
  function showLoginModal(options = {}) {
    const modal = document.getElementById("loginModal");
    const modalLoginBtn = document.getElementById("modalLoginBtn");

    if (!modal) {
      createLoginModal();
      return showLoginModal(options);
    }

    // Set custom message if provided
    if (options.message) {
      const messageEl = modal.querySelector(".login-modal-message");
      if (messageEl) messageEl.textContent = options.message;
    }

    // Set redirect URL
    const redirectUrl =
      options.redirect || window.location.pathname + window.location.search;
    if (modalLoginBtn) {
      modalLoginBtn.href = `login1.html?redirect=${encodeURIComponent(
        redirectUrl
      )}`;
    }

    modal.style.display = "block";
    document.body.style.overflow = "hidden";
  }

  // Hide login modal
  function hideLoginModal() {
    const modal = document.getElementById("loginModal");
    if (modal) {
      modal.style.display = "none";
      document.body.style.overflow = "auto";
    }
  }

  // Initialize modal on DOM ready
  function initLoginModal() {
    createLoginModal();

    // Event listeners
    const closeBtn = document.getElementById("closeLoginModal");
    const cancelBtn = document.getElementById("cancelLoginBtn");
    const modal = document.getElementById("loginModal");

    if (closeBtn) {
      closeBtn.addEventListener("click", hideLoginModal);
    }

    if (cancelBtn) {
      cancelBtn.addEventListener("click", hideLoginModal);
    }

    // Close when clicking outside
    if (modal) {
      modal.addEventListener("click", function (e) {
        if (e.target === modal) {
          hideLoginModal();
        }
      });
    }

    // Close on Escape key
    document.addEventListener("keydown", function (e) {
      if (e.key === "Escape" && modal && modal.style.display === "block") {
        hideLoginModal();
      }
    });
  }

  // Auto-initialize when DOM is ready
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initLoginModal);
  } else {
    initLoginModal();
  }

  // Export functions to window for global access
  window.showLoginModal = showLoginModal;
  window.hideLoginModal = hideLoginModal;
})();
