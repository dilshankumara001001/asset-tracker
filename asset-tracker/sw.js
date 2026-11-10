const CACHE_NAME = 'asset-tracker-v2';

self.addEventListener('install', (e) => {
  self.skipWaiting(); // අලුත් වර්ෂන් එක වහාම දාන්න
});

self.addEventListener('activate', (e) => {
  e.waitUntil(
    caches.keys().then((keyList) => {
      return Promise.all(keyList.map((key) => {
        if (key !== CACHE_NAME) {
          return caches.delete(key); // පරණ cache අයින් කරන්න
        }
      }));
    })
  );
});

self.addEventListener('fetch', (e) => {
  // PHP ෆයිල්ස් කිසිසේත්ම Cache කරන්නේ නෑ. හැමවෙලේම සර්වර් එකෙන් අලුත්ම එක ගන්නවා.
  if (e.request.url.includes('.php')) {
    e.respondWith(fetch(e.request));
    return;
  }
  
  // අනිත් ෆයිල්ස් (css, images) විතරක් cache කරනවා
  e.respondWith(
    caches.match(e.request).then(res => res || fetch(e.request))
  );
});