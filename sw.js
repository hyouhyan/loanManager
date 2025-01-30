// キャッシュ名とキャッシュファイルの指定
var CACHE_NAME = 'pwa-sample-caches';
var urlsToCache = [
    "/",
    "index.php",
    "header.php",
    "footer.php",
    "share.php",
    "css/style.css",
    "contact/add_contact.php",
    "contact/delete_contact.php",
    "contact/edit_contact.php",
    "db/database.php",
    "share/index.php",
    "transaction/add_transaction.php",
    "transaction/contact_transaction.php",
    "transaction/share_transaction.php",
    "user/edit_userinfo.php",
    "user/login.php",
    "user/logout.php",
    "user/register.php",
];

// インストール処理
self.addEventListener('install', function(event) {
	event.waitUntil(
		caches
			.open(CACHE_NAME)
			.then(function(cache) {
				return cache.addAll(urlsToCache);
			})
	);
});

// リソースフェッチ時のキャッシュロード処理
self.addEventListener('fetch', function(event) {
	event.respondWith(
		caches
			.match(event.request)
			.then(function(response) {
				return response ? response : fetch(event.request);
			})
	);
});