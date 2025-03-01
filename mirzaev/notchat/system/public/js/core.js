"use strict";

if (typeof window.core !== "function") {
  // Not initialized

  // Initialize of the class in global namespace
  window.core = class core {
		// Domain
		static domain = window.location.hostname;

		// Animations are enabled?
		static animations = getComputedStyle(document.body).getPropertyValue('--animations') === '1';

		// Label for the <header> element
		static header = document.body.getElementsByTagName('header')[0];

		// Label for the <aside> element
		static aside = document.body.getElementsByTagName('aside')[0];

		// Label for the "menu" element
		static menu = document.body.querySelector("section[data-section='menu']");

		// Label for the <main> element
		static main = document.body.getElementsByTagName('main')[0];

		// Label for the <footer> element
		static footer = document.body.getElementsByTagName('footer')[0];

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
    static async request(
      address = '/',
      body,
      method = "POST",
      headers = {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      type = "json",
    ) {
      return await fetch(address, { method, headers, body })
        .then((response) => response[type]());
    }
  };
}

// Dispatch event: "initialized"
document.dispatchEvent(
  new CustomEvent("core.initialized", {
    detail: { core: window.core },
  }),
);
