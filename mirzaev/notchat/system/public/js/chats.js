"use strict";

if (typeof window.chats !== "function") {
  // Not initialized

  // Initialize of the class in global namespace
  window.chats = class chats {
    /**
     * Request
     *
     * @param {string} address
     * @param {string} body
     * @param {string} method POST, GET...
     * @param {object} headers
     * @param {string} type Format of response (json, text...)
     *
     * @return {Promise}
     */
    static generate = {
      servers() {
        core.request("/servers").then((json) => {
          if (core.servers instanceof HTMLElement) core.servers.remove();
          if (json.errors !== null && typeof json.errors === 'object' && json.errors.length > 0) {}
          else {
            const element = document.createElement("div");
            core.header.after(element);
            element.outerHTML = json.html;

            core.servers = document.body.querySelector("section[data-section='servers']");
          }
        });
      },
      chat() {
        core.request("/chat").then((json) => {
          if (json.errors !== null && typeof json.errors === 'object' && json.errors.length > 0) {}
          else {
            const element = document.createElement("div");
            const position = core.main.children.length;
            element.style.setProperty("--position", position);
            core.main.append(element);
            core.main.style.setProperty("--elements", position + 1);
            element.innerHTML = json.html;
          }
        });
      },
    };
  };
}

// Dispatch event: "initialized"
document.dispatchEvent(
  new CustomEvent("chats.initialized", {
    detail: { chats: window.chats },
  }),
);
