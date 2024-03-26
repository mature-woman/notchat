"use strict";

/**
 * Damper
 *
 * @param {function} function Function to execute after damping
 * @param {number} timeout Timer in milliseconds (ms)
 * @param {number} force Argument number storing the enforcement status of execution (see @example)
 *
 * @example
 * 	$a = damper(
 * 		async (
 * 			a,							// 0
 * 			b,							// 1
 * 			c,							// 2
 * 			force = false,	// 3
 * 			d								// 4
 * 		) => {
 * 			// Body of function
 * 		},
 * 			500,
 * 			3,							// 3 -> "force" argument
 * 		);
 *
 * 	$a('for a', 'for b', 'for c', true, 'for d'); // Force execute is enabled
 *
 * @return {void}
 */
function damper(func, timeout = 300, force) {
  // Initializing of the timer
  let timer;

  return (...args) => {
    // Deinitializing of the timer
    clearTimeout(timer);

    if (typeof force === "number" && args[force]) {
      // Force execution (ignoring the timer)

      func.apply(this, args);
    } else {
      // Normal execution

      // Execute the handled function (entry into recursion)
      timer = setTimeout(() => {
        func.apply(this, args);
      }, timeout);
    }
  };
}

// Dispatch event: "initialized"
document.dispatchEvent(
  new CustomEvent("damper.initialized", {
    detail: { damper },
  }),
);
