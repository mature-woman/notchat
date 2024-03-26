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
       * Select a server (dampered)
       *
       * @param {string} server Domain or IP-address:port of the server (from cache by default)
       * @param {bool} force Force execution
       *
       * @return {void} Into the document will be generated and injected an HTML-element
       */
      select(
        server = localStorage.server_ip && localStorage.server_port
          ? localStorage.server_ip + ":" + localStorage.server_port
          : localStorage.server_domain,
        force = false,
      ) {
        // Writing status: "connecting"
        core.menu.setAttribute("data-menu-status", "connecting");

        // Deinitializing animation of opening
        core.menu.getElementsByTagName("output")[0].classList.remove(
          "slide-down",
        );

        // Initializing animation of closing
        core.menu.getElementsByTagName("output")[0].classList.add(
          "slide-down-revert",
        );

        // Disabled for animation
        /* core.menu.querySelector('figcaption[data-server="domain"]')
          .innerText =
          core.menu.querySelector('pre[data-server="description"]')
            .innerText =
            ""; */

        this._select(server, force);
      },

      /**
       * Select a server
       *
       * @param {string} server Domain or IP-address:port of the server (from cache by default)
       * @param {bool} force Force execution
       *
       * @return {void} Into the document will be generated and injected an HTML-element
       */
      _select: damper(
        (
          server = localStorage.server_ip && localStorage.server_port
            ? localStorage.server_ip + ":" + localStorage.server_port
            : localStorage.server_domain,
          force = false,
        ) => {
          if (server.length > 512) {
            notifications.write(text.read("CHATS_SERVER_ERROR_LENGTH_MAX"));
          } else if (typeof server === "string" && server.length > 0) {
            if (
              core.menu instanceof HTMLElement &&
              core.menu.getAttribute("data-menu") === "chats"
            ) {
              //

              // Initializing the unlock function
              function unblock() {
                // Writing status: "empty"
                if (
                  core.menu.querySelector('figcaption[data-server="domain"]')
                      .innerText.length === 0 &&
                  core.menu.querySelector('pre[data-server="description"]')
                      .innerText.length === 0
                ) {
                  core.menu.getElementsByTagName("search")[0]
                    .getElementsByTagName("label")[0].classList.add(
                      "empty",
                    );
                }

                // Writing status: "disconnected"
                core.menu.setAttribute("data-menu-status", "disconnected");
              }

              // Initiating a unlock delay in case the server does not respond
              const timeout = setTimeout(() => {
                // this.errors(["Server does not respond"]);
                unblock();
              }, 5000);

              core.request(
                `/server/read/${encodeURIComponent(server)}`,
                `language=${localStorage.language ?? "english"}`,
              ).then((json) => {
                // Deinitializing of unlock delay
                clearTimeout(timeout);

                if (
                  json.errors !== null && typeof json.errors === "object" &&
                  json.errors.length > 0
                ) {
                  // Generating notifications with errors
                  // for (const error of json.errors) notifications.write(error);

                  // Writing status: "disconnected"
                  core.menu.setAttribute("data-menu-status", "disconnected");

                  // Writind domain of the server
                  core.menu.querySelector('figcaption[data-server="domain"]')
                    .innerText = "";

                  // Writing description of the server
                  core.menu.querySelector('pre[data-server="description"]')
                    .innerText = "";

                  // Writing status: "empty" (for opening the description window)
                  core.menu.getElementsByTagName("search")[0]
                    .getElementsByTagName("label")[0].classList.add(
                      "empty",
                    );
                } else {
                  // Writing status: "connected"
                  core.menu.setAttribute("data-menu-status", "connected");

                  // Writind domain of the server
                  core.menu.querySelector('figcaption[data-server="domain"]')
                    .innerText = `${json.server.domain}`;

                  // Writing description of the server
                  core.menu.querySelector('pre[data-server="description"]')
                    .innerText = `${json.server.description}`;

                  // Deleting status: "empty" (for opening the description window) (it is implied that the response from the server cannot be empty)
                  core.menu.getElementsByTagName("search")[0]
                    .getElementsByTagName("label")[0].classList.remove(
                      "empty",
                    );

                  // Deinitializing animation of closing
                  core.menu.getElementsByTagName("output")[0].classList.remove(
                    "slide-down-revert",
                  );

                  // Initializing animation of opening
                  core.menu.getElementsByTagName("output")[0].classList.add(
                    "slide-down",
                  );

                  // Writing data of the server to local storage (browser)
                  localStorage.server_domain = json.server.domain;
                  localStorage.server_ip = json.server.ip;
                  localStorage.server_port = json.server.port;
                }
              });
            }
          }
        },
        800,
        1,
      ),
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
      servers(
        server = `${localStorage.server_ip}:${localStorage.server_port}`,
      ) {
        core.request(
          "/servers",
          typeof server === "string" && server.length > 0
            ? `server=${server}`
            : "",
        ).then((json) => {
          if (core.servers instanceof HTMLElement) core.servers.remove();
          if (
            json.errors !== null && typeof json.errors === "object" &&
            json.errors.length > 0
          ) {
            // Generating notifications with errors
            for (const error of json.errors) notifications.write(error);
          } else {
            if (core.menu instanceof HTMLElement) {
							// Writing status of connection (hack with replaying animations)
							core.menu.setAttribute('data-menu-status', 'disconnected');
							setTimeout(() => core.menu.setAttribute('data-menu-status', json.status ?? 'disconnected'), 100);

              const element = document.createElement("search");

              const search = core.menu.getElementsByTagName("search")[0];
              if (search instanceof HTMLElement) search.remove();

              core.menu.prepend(element);
              element.outerHTML = json.html;

              core.menu = document.body.querySelector(
                "section[data-section='menu']",
              );
            }
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
          ) {
            // Generating notifications with errors
            for (const error of json.errors) notifications.write(error);
          } else {
            // сосать бебру
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
