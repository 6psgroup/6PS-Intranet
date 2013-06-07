<?php
// DB connection information
define('__DA_HOST','localhost',TRUE);
define('__DA_DATABASE','',TRUE);
define('__DA_USER','',TRUE);
define('__DA_PASS','',TRUE);

// Brazil WHMCS Connection information
define('__DA_WHMCS_HOST','',TRUE);
define('__DA_WHMCS_DATABASE','',TRUE);
define('__DA_WHMCS_USER','',TRUE);
define('__DA_WHMCS_PASS','',TRUE);
define('__WWW_WHMCS_USER',''); // prefix for user account page

// US AWBS connection information
define('__DA_AWBS_HOST','localhost',TRUE);
define('__DA_AWBS_DATABASE','',TRUE);
define('__DA_AWBS_USER','',TRUE);
define('__DA_AWBS_PASS','',TRUE);
define('__WWW_AWBS_USER',''); // prefix for user account page

// File system path for root of site (/path/to/site/)
define('__SITE_FS_ROOT','/path/to/intranet/'); // include trailing slash
define('__SITE_COOKIE_NAME','6PS-Intranet');
define('__SITE_COOKIE_DOMAIN','intranet.6ps.com');
define('__SITE_COOKIE_EXPIRE',time()+604800); // 7 days

// HTTP root (http://www.example.com)
define('__SITE_WWW_ROOT','http://intranet.6ps.com/'); // DO NOT include trailing slash

// Path to photos (/path/to/photos/)
define('__SITE_PHOTO_ROOT',__SITE_FS_ROOT.'images_upload/'); // include trailing slash
define('__WWW_PHOTO_ROOT','/intranet/images_upload/'); // include trailing slash

// ID of home page
define('__HOME_PAGE',1);

?>
