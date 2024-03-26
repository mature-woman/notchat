"use strict";

const VERSION = "0.1.0";
const EXPIRED = 86400000;

self.addEventListener("install", (event) => {
});

self.addEventListener("fetch", (event) => {
  event
    .respondWith(
      caches
        .match(event.request)
        .then((response) => {
          if (response) {
            // Found file in cache

            if (
              Date.now() - new Date(response.headers.get("last-modified")) >
                EXPIRED
            ) {
              // Expired period of storage response

              return fetch(event.request.clone())
                .then((response) => {
                  if (response.status === 200) {
                    // Downloaded new version

                    return caches
                      .open(VERSION)
                      .then((cache) => {
                        // Writing new version to cache
                        cache.put(event.request, response.clone());

                        // Exit (success)
                        return response;
                      });
                  } else throw "Failed to download new version";
                })
                .catch(() => {
                  // Exit (success) (return old version)
                  return response;
                });
            } else return response;
          } else {
            // Not found file in cache

            return fetch(event.request.clone())
              .then((response) => {
                if (response.status === 200) {
                  // Downloaded

                  return caches
                    .open(VERSION)
                    .then((cache) => {
                      // Writing to cache
                      cache.put(event.request, response.clone());

                      // Exit (success)
                      return response;
                    });
                } else throw "Failed to download";
              });
          }
        })
        .catch(() => {
          return caches.match("/offline");
        }),
    );
});

/* self.addEventListener("activate", (event) => {
  event.waitUntil(
    caches.keys().then((keyList) => {
      return Promise.all(
        keyList.map((key) => {
					// Deleting old versions of cache
          if (VERSION.indexOf(key) === -1) return caches.delete(key);
        }),
      );
    }),
  );
}); */
