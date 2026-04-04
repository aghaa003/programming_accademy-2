/**
 * api.js — shared CSRF/session-safe networking for all pages.
 *
 * What this file guarantees:
 * 1) Every mutating fetch() call automatically gets CSRF handling.
 * 2) CSRF is requested from the same origin as the target API URL.
 * 3) A single automatic retry is performed if a request returns 419.
 */
(function () {
    "use strict";

    var CSRF_READY_BY_ORIGIN = Object.create(null);
    var CSRF_PROMISE_BY_ORIGIN = Object.create(null);

    function toUrl(input) {
        if (input instanceof Request) {
            return new URL(input.url, window.location.href);
        }
        return new URL(String(input), window.location.href);
    }

    function getCookie(name) {
        var value = "; " + document.cookie;
        var parts = value.split("; " + name + "=");
        if (parts.length === 2) {
            return decodeURIComponent(parts.pop().split(";").shift());
        }
        return null;
    }

    function cloneHeaders(headersInit) {
        var headers = {};
        if (!headersInit) return headers;

        if (headersInit instanceof Headers) {
            headersInit.forEach(function (v, k) {
                headers[k] = v;
            });
            return headers;
        }

        if (Array.isArray(headersInit)) {
            headersInit.forEach(function (pair) {
                headers[pair[0]] = pair[1];
            });
            return headers;
        }

        Object.assign(headers, headersInit);
        return headers;
    }

    function markCsrfStale(origin) {
        CSRF_READY_BY_ORIGIN[origin] = false;
        CSRF_PROMISE_BY_ORIGIN[origin] = null;
    }

    function ensureCsrfForOrigin(origin) {
        if (CSRF_READY_BY_ORIGIN[origin]) {
            return Promise.resolve();
        }

        if (CSRF_PROMISE_BY_ORIGIN[origin]) {
            return CSRF_PROMISE_BY_ORIGIN[origin];
        }

        CSRF_PROMISE_BY_ORIGIN[origin] = window
            .fetch(origin + "/sanctum/csrf-cookie", {
                method: "GET",
                credentials: "include",
                headers: {
                    Accept: "application/json",
                },
            })
            .then(function () {
                CSRF_READY_BY_ORIGIN[origin] = true;
            })
            .catch(function (err) {
                markCsrfStale(origin);
                return Promise.reject(err);
            });

        return CSRF_PROMISE_BY_ORIGIN[origin];
    }

    var originalFetch = window.fetch.bind(window);

    function fetchWithCsrf(input, init, retrying) {
        var requestUrl = toUrl(input);
        var origin = requestUrl.origin;
        var options = Object.assign({}, init || {});
        var method = (options.method || "GET").toUpperCase();
        var isMutating =
            ["POST", "PUT", "PATCH", "DELETE"].indexOf(method) >= 0;

        if (!options.credentials) {
            options.credentials = "include";
        }

        if (!isMutating) {
            return originalFetch(input, options);
        }

        return ensureCsrfForOrigin(origin).then(function () {
            var token = getCookie("XSRF-TOKEN");
            var headers = cloneHeaders(options.headers);

            if (token) {
                headers["X-XSRF-TOKEN"] = token;
            }

            if (!headers.Accept) {
                headers.Accept = "application/json";
            }

            options.headers = headers;

            return originalFetch(input, options).then(function (response) {
                // Handle expired or rotated session token once automatically.
                if (response.status === 419 && !retrying) {
                    markCsrfStale(origin);
                    return fetchWithCsrf(input, init, true);
                }
                // Auto-redirect to login on 401 (session expired), except on auth pages.
                if (response.status === 401) {
                    var page = window.location.pathname.split("/").pop() || "";
                    var authPages = [
                        "login1.html",
                        "register.html",
                        "forgot-password.html",
                        "reset-password.html",
                    ];
                    if (
                        authPages.indexOf(page) === -1 &&
                        !window.__redirectingTo401
                    ) {
                        window.__redirectingTo401 = true;
                        window.location.href = "./login1.html";
                        return response;
                    }
                }
                return response;
            });
        });
    }

    // Global transparent patch: existing fetch() calls keep working.
    window.fetch = function (input, init) {
        return fetchWithCsrf(input, init, false);
    };

    // Pre-warm CSRF for the current page origin.
    ensureCsrfForOrigin(window.location.origin).catch(function () {
        // Ignore; first mutating request will retry automatically.
    });
})();
