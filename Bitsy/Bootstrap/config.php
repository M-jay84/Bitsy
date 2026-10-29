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
    // category,name,dl,magnet,uploader,tube,tmdb,comments,nfo,size,completed,seeders,leechers,health,external,added,speed,wait,rating
    'TORRENTTABLE_COLUMNS' => 'category,name,dl,magnet,uploader,tube,tmdb,comments,nfo,size,completed,seeders,leechers,health,external,added,speed,wait,rating,admin',
    // Set User Group
    '_VIP' => 3,
    '_UPLOADER' => 4,
    '_MODERATOR' => 5,
    '_ADMINISTRATOR' => 7,
    // Cat arrays
    'PopularCats' => array(1), // popular cat id for popular block
    'MovieCats' => array('Film'), // movie cats eg array('Movies','MoviesHD')
    'SerieCats' => array('TV'), // serie cat eg array('Anime','tv')
    // TMDB
    '_TMDBAPIKEY' => 'Place_Key', // Place Key Here ////////
    '_TMDBLANG' => 'en', // TMDB Language
    '_TMDBDEBUG' => false, // TMDB Debug
    '_TMDBswitch' => true, // TMDB On - Off 
    '_OMDBAPIKEY' => 'Place_Key', // Place Key Here ////////
    '_OMDBswitch' => true, // TMDB On - Off
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
    'MEMBERSONLY_WAIT' => true,
    'ALLOWEXTERNAL' => true,
    'UPLOADERSONLY' => false,
    'INVITEONLY' => false,
    'ENABLEINVITES' => true,
    'CONFIRMEMAIL' => false,
    'ACONFIRM' => false,
    'ANONYMOUSUPLOAD' => false,
    'FORUMS' => true,
    'FORUMS_GUESTREAD' => true,
    'FORCETHANKS' => false,
    'ALLOWLIKES' => true,
    'SHOUTBOX' => true,
    'NEWSON' => true,
    'DISCLAIMERON' => true,
    'FORUMONINDEX' => true,
    'LATESTFORUMPOSTONINDEX' => true,
    'IPCHECK' => false,
    'YOU_TUBE' => true,
    'FREELEECHGBON' => false,
    'FREELEECHGB' => 8589934592,
    'ACCOUNTMAX' => 1,
    'MAXUSERS' => 20000,
    'MAXUSERSINVITE' => 50000,
    'CURRENCYSYMBOL' => '£',
    'BONUSPERTIME' => 0.1,
    'ADDBONUS' => 3600,
    'CACHE_TYPE' => 'disk',
    'MEMCACHE_HOST' => 'localhost',
    'MEMCACHE_PORT' => 11211,
    'PEERLIMIT' => 10000,
    'AUTOCLEANINTERVAL' => 600,
    'ANNOUNCEINTERVAL' => 900,
    'SIGNUPTIMEOUT' => 259200,
    'MAXDEADTORRENTTIMEOUT' => 21600,
    'LOGCLEAN' => 28,
    'mail_type' => 'php',
    'mail_smtp_host' => '25',
    'mail_smtp_port' => 25,
    'mail_smtp_ssl' => false,
    'mail_smtp_auth' => false,
    'mail_smtp_user' => '',
    'mail_smtp_pass' => '',
    'RATIOWARNENABLE' => true,
    'RATIOWARNMINRATIO' => 0.4,
    'RATIOWARN_MINGIGS' => 4,
    'RATIOWARN_DAYSTOWARN' => 14,
    'CLASS_WAIT' => 1,
    'GIGSA' => 1,
    'RATIOA' => 0.5,
    'A_WAIT' => 24,
    'GIGSB' => 3,
    'RATIOB' => 0.65,
    'B_WAIT' => 12,
    'GIGSC' => 5,
    'RATIOC' => 0.8,
    'C_WAIT' => 6,
    'GIGSD' => 7,
    'RATIOD' => 0.95,
    'D_WAIT' => 2,
    'HNR_ON' => false,
    'HNR_DEADLINE' => 7,
    'HNR_SEEDTIME' => 48,
    'HNR_WARN' => 5,
    'HNR_STOP_DL' => 15,
    'HNR_BAN' => 50,
));