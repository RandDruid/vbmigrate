# vbmigrate

This is a simple WordPress authentication filter. Its main purpose to simplify migration from vBulletin based forum to WordPress based forum, without asking users to re-create passwords.  
Users accounts must be already created in WordPress, but passwords may be set randomly - they will be overwritten after successful check with vBulletin DB.

Filter logic:

1) Checks authentication of user in WP database. If successful - exit filter normally.
2) If step 1 fails - checks authentication against vBulletin database. If this fails - exit filter with error.
3) If step 2 succeeds, save password to WordPress database for this user. Exit filter normally.

Filter requires credentials to connect to vBulletin database. Please add these statements to `wp-config.php` and provide valid credentials.

```php
define('VB_DB_HOST', '127.0.0.1');
define('VB_DB_USER', 'vbuser');
define('VB_DB_PASSWORD', 'vbpass');
define('VB_DB_DATABASE', 'forum');
```
