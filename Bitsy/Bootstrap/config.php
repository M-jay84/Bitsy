<?php
define('CONFIG', array (
    // Quick Time
    'TT_TIME' => time(),
    'TT_DATE' => date('Y-m-d H:i:s'),
    // File Charset
    'CHARSET' => 'utf-8',
    // Announcelist //seperate via comma
    'ANNOUNCELIST' => '/announce.php',
    // Passkey Url
    'PASSKEYURL' => '/announce.php?passkey=%s',
    // Image upload settings
    'USE_WEBP' => true, // Use Webp 
    'IMAGEMAXFILESIZE' => 524288, // Max uploaded image size in bytes (Default: 512 kB)
    'ALLOWEDIMAGETYPES' => array(
        'image/gif' => '.gif',
        'image/jpeg' => '.jpg',
        'image/jpg' => '.jpg',
        'image/png' => '.png',
    ),
    // Hide Blocks On Pages
    'ISURL' => array('login', 'logout', 'signup', 'contact', 'recover', 'recover/confirm'),
    // Set User Group
    '_VIP' => 3,
    '_UPLOADER' => 4,
    '_MODERATOR' => 5,
    '_ADMINISTRATOR' => 7,
    'SITENAME' => 'M-BiTsy',
    'SITEEMAIL' => 'something@email.com',
    'SITE_ONLINE' => true,
    'OFFLINEMSG' => 'Site is down for a little while',
    'WELCOMEPM_MSG' => 'Thank you for registering at our tracker! Please remember to keep your ratio at 1.00 or greater',
    'UPLOADRULES' => 'You should also include a .nfo file wherever possible<br />Try to make sure your torrent is well-seeded for at least 24 hours<br />Do not re-release material that is still active',
    'CAPTCHA_ON' => false,
    'CAPTCHA_KEY' => 'googlecaptchakey',
    'CAPTCHA_SECRET' => 'googlecaptchasecret',
    'CSRF_Token' => false,
    'DEFAULTLANG' => 'english',
    'DEFAULTTHEME' => 'inc',
    'MEMBERSONLY' => false,
    'INVITEONLY' => false,
    'ENABLEINVITES' => true,
    'CONFIRMEMAIL' => false,
    'ACONFIRM' => false,
    'IPCHECK' => false,
    'CACHE_TYPE' => 'disk',
    'MEMCACHE_HOST' => 'localhost',
    'MEMCACHE_PORT' => 11211,
    'mail_type' => 'php',
    'mail_smtp_host' => '25',
    'mail_smtp_port' => 25,
    'mail_smtp_ssl' => false,
    'mail_smtp_auth' => false,
    'mail_smtp_user' => '',
    'mail_smtp_pass' => '',
));