"use strict";

if (typeof window.chats !== "function") {
  // Not initialized

  // Initialize of the class in global namespace
  window.chats = class chats {
    /**
     * Server
     */
    static server = {
      /**
       * Select server
       *
       * @param {string} server Domain or IP-address of the server (from cache by default)
       *
       * @return {void} Into the document will be generated and injected an HTML-element
       */
      select(server = localStorage.server_ip ?? localStorage.server_domain) {
        if (typeof server === "string" && server.length > 0) {
          if (core.servers instanceof HTMLElement) {
            core.request(`/server/read/${server}`).then((json) => {
              if (
                json.errors !== null && typeof json.errors === "object" &&
                json.errors.length > 0
              ) {} else {
                document.querySelector('figcaption[data-server="ip"]').innerText = `${json.ip}:${json.port}`;
                document.querySelector('figcaption[data-server="description"]').innerText = `${json.description}`;
              }
            });
          }
        }
      },
    };

    /**
     * Generators
     */
    static generate = {
      /**
       * HTML-element with a server selection form
       *
       * @param {string} server Domain or IP-address of the server (from cache by default)
       *
       * @return {void} Into the document will be generated and injected an HTML-element
       */
      servers(server = localStorage.server_ip ?? localStorage.server_domain) {
        core.request(
          "/servers",
          typeof server === "string" && server.length > 0
            ? `server=${server}}`
            : "",
        ).then((json) => {
          if (core.servers instanceof HTMLElement) core.servers.remove();
          if (
            json.errors !== null && typeof json.errors === "object" &&
            json.errors.length > 0
          ) {} else {
            const element = document.createElement("div");
            core.header.after(element);
            element.outerHTML = json.html;

            core.servers = document.body.querySelector(
              "section[data-section='servers']",
            );
          }
        });
      },

      /**
       * @param {string}
       *
       * @return {void} Into the document will be generated and injected an HTML-element
       */
      chat() {
        core.request("/chat").then((json) => {
          if (
            json.errors !== null && typeof json.errors === "object" &&
            json.errors.length > 0
          ) {} else {
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
