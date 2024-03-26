"use strict";

if (typeof window.journal !== "function") {
  // Not initialized

  // Initialize of the class in global namespace
  window.journal = class cache {
    static write(text) {
      console.log(`[${core.domain ?? "notchat"}] ${text}`);
    }
  };
}

// Dispatch event: "initialized"
document.dispatchEvent(
  new CustomEvent("journal.initialized", {
    detail: { journal: window.journal },
  }),
);
