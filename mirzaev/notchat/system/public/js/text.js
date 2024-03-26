"use strict";

if (typeof window.text !== "function") {
  // Not initialized

  // Initialize of the class in global namespace
  window.text = class text {
    /**
     * Language
     */
    static language = {
      /**
       * Select a language
       *
       * @param {string} language Name of language
       *
       * @return {void}
       */
      select(language = 'english') {
				// Write to the local storage in browser
				localStorage.language = language;
      },
    };
  };
}

// Dispatch event: "initialized"
document.dispatchEvent(
  new CustomEvent("text.initialized", {
    detail: { text: window.text },
  }),
);
