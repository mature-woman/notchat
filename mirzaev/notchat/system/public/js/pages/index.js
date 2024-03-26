try {
  navigator.serviceWorker
    .register("cache.js")
    .then((cache) => {
      // Registered
      journal.write("ServiceWorker registered: " + cache.scope);
    })
    .catch((error) => {
      // Not registered
      journal.write("ServiceWorker not registered: " + error);
    });
} catch (error) {
  console.error(`pizda ${error}`);
}
