#LWS OPTIMIZE - EXPIRE HEADER
# Règles ajoutées par LWS Optimize
# Rules added by LWS Optimize
<IfModule mod_expires.c>
ExpiresActive On
AddOutputFilterByType DEFLATE application/json
ExpiresByType image/jpg "access 1 year"
ExpiresByType image/jpeg "access 1 year"
ExpiresByType image/gif "access 1 year"
ExpiresByType image/png "access 1 year"
ExpiresByType image/svg "access 1 year"
ExpiresByType image/x-icon "access 1 year"
ExpiresByType text/css "access 1 year"
ExpiresByType application/pdf "access 1 year"
ExpiresByType application/javascript "access 1 year"
ExpiresByType application/x-javascript "access 1 year"
ExpiresByType application/x-shockwave-flash "access 1 year"
ExpiresByType text/html A0
ExpiresDefault "access 1 year"
</IfModule>

<FilesMatch "index_[0-2]\.(html|htm)$">
<IfModule mod_headers.c>
Header set Cache-Control "public, max-age=0, no-cache, must-revalidate"
Header set CDN-Cache-Control "public, maxage=31104000"
Header set Pragma "no-cache"
Header set Expires "Mon, 29 Oct 1923 20:30:00 GMT"
</IfModule>
</FilesMatch>
#END LWS OPTIMIZE - EXPIRE HEADER
#LWS OPTIMIZE - GZIP COMPRESSION
# Règles ajoutées par LWS Optimize
# Rules added by LWS Optimize
<IfModule mod_brotli.c>
AddOutputFilterByType BROTLI_COMPRESS application/javascript
AddOutputFilterByType BROTLI_COMPRESS application/json
AddOutputFilterByType BROTLI_COMPRESS application/rss+xml
AddOutputFilterByType BROTLI_COMPRESS application/xml
AddOutputFilterByType BROTLI_COMPRESS application/atom+xml
AddOutputFilterByType BROTLI_COMPRESS application/vnd.ms-fontobject
AddOutputFilterByType BROTLI_COMPRESS application/x-font-ttf
AddOutputFilterByType BROTLI_COMPRESS font/opentype
AddOutputFilterByType BROTLI_COMPRESS text/plain
AddOutputFilterByType BROTLI_COMPRESS text/pxml
AddOutputFilterByType BROTLI_COMPRESS text/html
AddOutputFilterByType BROTLI_COMPRESS text/css
AddOutputFilterByType BROTLI_COMPRESS text/x-component
AddOutputFilterByType BROTLI_COMPRESS image/svg+xml
AddOutputFilterByType BROTLI_COMPRESS image/x-icon
</IfModule>

<IfModule mod_deflate.c>
SetOutputFilter DEFLATE
<IfModule mod_filter.c>
AddOutputFilterByType DEFLATE application/javascript
AddOutputFilterByType DEFLATE application/json
AddOutputFilterByType DEFLATE application/rss+xml
AddOutputFilterByType DEFLATE application/xml
AddOutputFilterByType DEFLATE application/atom+xml
AddOutputFilterByType DEFLATE application/vnd.ms-fontobject
AddOutputFilterByType DEFLATE application/x-font-ttf
AddOutputFilterByType DEFLATE font/opentype
AddOutputFilterByType DEFLATE text/plain
AddOutputFilterByType DEFLATE text/pxml
AddOutputFilterByType DEFLATE text/html
AddOutputFilterByType DEFLATE text/css
AddOutputFilterByType DEFLATE text/x-component
AddOutputFilterByType DEFLATE image/svg+xml
AddOutputFilterByType DEFLATE image/x-icon
</IfModule>
</IfModule>
#END LWS OPTIMIZE - GZIP COMPRESSION
#LWS OPTIMIZE - CACHING
# Règles ajoutées par LWS Optimize
# Rules added by LWS Optimize
 #Last Modification: 16/09/2025 17:07:23
<IfModule mod_rewrite.c>
#---- STARTING DIRECTIVES ----#
RewriteEngine On
#### ####
RewriteBase /
## Connected desktop ##
RewriteCond %{QUERY_STRING} !^((gclid|fbclid|y(ad|s)?clid|utm_(source|medium|campaign|content|term)=[^&]+)+)$ [NC]
RewriteCond %{HTTP_HOST} ^mimshack230.com
RewriteCond %{HTTP:Cookie} !wordpress_logged_in_[^\=]+\=compte_mimshack
RewriteCond %{HTTP_USER_AGENT} '!(LWS_Optimize_Preload|LWS_Optimize_Preload_Mobile)' [NC]
RewriteCond %{REQUEST_METHOD} !POST
RewriteCond %{REQUEST_URI} !(\/){2,}
RewriteCond %{THE_REQUEST} !(\/){2,}
RewriteCond %{REQUEST_URI} \/$
RewriteCond %{QUERY_STRING} !.+
RewriteCond %{HTTP:Cookie} !comment_author_
RewriteCond %{HTTP:Profile} !^[a-z0-9\"]+ [NC]
RewriteCond %{HTTP_COOKIE} wordpress_logged_in_ [NC]
RewriteCond %{HTTP_USER_AGENT} !^.*\bCrMo\b|CriOS|Android.*Chrome\/[.0-9]*\s(Mobile)?|\bDolfin\b|Opera.*Mini|Opera.*Mobi|Android.*Opera|Mobile.*OPR\/[0-9.]+|Coast\/[0-9.]+|Skyfire|Mobile\sSafari\/[.0-9]*\sEdge|IEMobile|MSIEMobile|fennec|firefox.*maemo|(Mobile|Tablet).*Firefox|Firefox.*Mobile|FxiOS|bolt|teashark|Blazer|Version.*Mobile.*Safari|Safari.*Mobile|MobileSafari|Tizen|UC.*Browser|UCWEB|baiduboxapp|baidubrowser|DiigoBrowser|Puffin|\bMercury\b|Obigo|NF-Browser|NokiaBrowser|OviBrowser|OneBrowser|TwonkyBeamBrowser|SEMC.*Browser|FlyFlow|Minimo|NetFront|Novarra-Vision|MQQBrowser|MicroMessenger|Android.*PaleMoon|Mobile.*PaleMoon|Android|blackberry|\bBB10\b|rim\stablet\sos|PalmOS|avantgo|blazer|elaine|hiptop|palm|plucker|xiino|Symbian|SymbOS|Series60|Series40|SYB-[0-9]+|\bS60\b|Windows\sCE.*(PPC|Smartphone|Mobile|[0-9]{3}x[0-9]{3})|Window\sMobile|Windows\sPhone\s[0-9.]+|WCE;|Windows\sPhone\s10.0|Windows\sPhone\s8.1|Windows\sPhone\s8.0|Windows\sPhone\sOS|XBLWP7|ZuneWP7|Windows\sNT\s6\.[23]\;\sARM\;|\biPhone.*Mobile|\biPod|\biPad|Apple-iPhone7C2|MeeGo|Maemo|J2ME\/|\bMIDP\b|\bCLDC\b|webOS|hpwOS|\bBada\b|BREW.*$ [NC]
RewriteCond %{DOCUMENT_ROOT}//wp-content/cache/lwsoptimize/cache/$1index_2.html -f
RewriteRule ^(.*) wp-content/cache/lwsoptimize/cache/$1index_2.html [L]

## Connected mobile ##
RewriteCond %{QUERY_STRING} !^((gclid|fbclid|y(ad|s)?clid|utm_(source|medium|campaign|content|term)=[^&]+)+)$ [NC]
RewriteCond %{HTTP_HOST} ^mimshack230.com
RewriteCond %{HTTP:Cookie} !wordpress_logged_in_[^\=]+\=compte_mimshack
RewriteCond %{HTTP_USER_AGENT} '!(LWS_Optimize_Preload|LWS_Optimize_Preload_Mobile)' [NC]
RewriteCond %{REQUEST_METHOD} !POST
RewriteCond %{REQUEST_URI} !(\/){2,}
RewriteCond %{THE_REQUEST} !(\/){2,}
RewriteCond %{REQUEST_URI} \/$
RewriteCond %{QUERY_STRING} !.+
RewriteCond %{HTTP:Cookie} !comment_author_
RewriteCond %{HTTP:Profile} !^[a-z0-9\"]+ [NC]
RewriteCond %{HTTP_COOKIE} wordpress_logged_in_ [NC]
RewriteCond %{HTTP_USER_AGENT} .*\bCrMo\b|CriOS|Android.*Chrome\/[.0-9]*\s(Mobile)?|\bDolfin\b|Opera.*Mini|Opera.*Mobi|Android.*Opera|Mobile.*OPR\/[0-9.]+|Coast\/[0-9.]+|Skyfire|Mobile\sSafari\/[.0-9]*\sEdge|IEMobile|MSIEMobile|fennec|firefox.*maemo|(Mobile|Tablet).*Firefox|Firefox.*Mobile|FxiOS|bolt|teashark|Blazer|Version.*Mobile.*Safari|Safari.*Mobile|MobileSafari|Tizen|UC.*Browser|UCWEB|baiduboxapp|baidubrowser|DiigoBrowser|Puffin|\bMercury\b|Obigo|NF-Browser|NokiaBrowser|OviBrowser|OneBrowser|TwonkyBeamBrowser|SEMC.*Browser|FlyFlow|Minimo|NetFront|Novarra-Vision|MQQBrowser|MicroMessenger|Android.*PaleMoon|Mobile.*PaleMoon|Android|blackberry|\bBB10\b|rim\stablet\sos|PalmOS|avantgo|blazer|elaine|hiptop|palm|plucker|xiino|Symbian|SymbOS|Series60|Series40|SYB-[0-9]+|\bS60\b|Windows\sCE.*(PPC|Smartphone|Mobile|[0-9]{3}x[0-9]{3})|Window\sMobile|Windows\sPhone\s[0-9.]+|WCE;|Windows\sPhone\s10.0|Windows\sPhone\s8.1|Windows\sPhone\s8.0|Windows\sPhone\sOS|XBLWP7|ZuneWP7|Windows\sNT\s6\.[23]\;\sARM\;|\biPhone.*Mobile|\biPod|\biPad|Apple-iPhone7C2|MeeGo|Maemo|J2ME\/|\bMIDP\b|\bCLDC\b|webOS|hpwOS|\bBada\b|BREW.*$ [NC]
RewriteCond %{DOCUMENT_ROOT}//wp-content/cache/lwsoptimize/cache-mobile/$1index_2.html -f
RewriteRule ^(.*) wp-content/cache/lwsoptimize/cache-mobile/$1index_2.html [L]

## Anonymous mobile ##
RewriteCond %{QUERY_STRING} !^((gclid|fbclid|y(ad|s)?clid|utm_(source|medium|campaign|content|term)=[^&]+)+)$ [NC]
RewriteCond %{HTTP_HOST} ^mimshack230.com
RewriteCond %{HTTP:Cookie} !wordpress_logged_in_[^\=]+\=compte_mimshack
RewriteCond %{HTTP_USER_AGENT} '!(LWS_Optimize_Preload|LWS_Optimize_Preload_Mobile)' [NC]
RewriteCond %{REQUEST_METHOD} !POST
RewriteCond %{REQUEST_URI} !(\/){2,}
RewriteCond %{THE_REQUEST} !(\/){2,}
RewriteCond %{REQUEST_URI} \/$
RewriteCond %{QUERY_STRING} !.+
RewriteCond %{HTTP:Cookie} !comment_author_
RewriteCond %{HTTP:Profile} !^[a-z0-9\"]+ [NC]
RewriteCond %{HTTP_COOKIE} !wordpress_logged_in_ [NC]
RewriteCond %{HTTP_USER_AGENT} .*\bCrMo\b|CriOS|Android.*Chrome\/[.0-9]*\s(Mobile)?|\bDolfin\b|Opera.*Mini|Opera.*Mobi|Android.*Opera|Mobile.*OPR\/[0-9.]+|Coast\/[0-9.]+|Skyfire|Mobile\sSafari\/[.0-9]*\sEdge|IEMobile|MSIEMobile|fennec|firefox.*maemo|(Mobile|Tablet).*Firefox|Firefox.*Mobile|FxiOS|bolt|teashark|Blazer|Version.*Mobile.*Safari|Safari.*Mobile|MobileSafari|Tizen|UC.*Browser|UCWEB|baiduboxapp|baidubrowser|DiigoBrowser|Puffin|\bMercury\b|Obigo|NF-Browser|NokiaBrowser|OviBrowser|OneBrowser|TwonkyBeamBrowser|SEMC.*Browser|FlyFlow|Minimo|NetFront|Novarra-Vision|MQQBrowser|MicroMessenger|Android.*PaleMoon|Mobile.*PaleMoon|Android|blackberry|\bBB10\b|rim\stablet\sos|PalmOS|avantgo|blazer|elaine|hiptop|palm|plucker|xiino|Symbian|SymbOS|Series60|Series40|SYB-[0-9]+|\bS60\b|Windows\sCE.*(PPC|Smartphone|Mobile|[0-9]{3}x[0-9]{3})|Window\sMobile|Windows\sPhone\s[0-9.]+|WCE;|Windows\sPhone\s10.0|Windows\sPhone\s8.1|Windows\sPhone\s8.0|Windows\sPhone\sOS|XBLWP7|ZuneWP7|Windows\sNT\s6\.[23]\;\sARM\;|\biPhone.*Mobile|\biPod|\biPad|Apple-iPhone7C2|MeeGo|Maemo|J2ME\/|\bMIDP\b|\bCLDC\b|webOS|hpwOS|\bBada\b|BREW.*$ [NC]
RewriteCond %{DOCUMENT_ROOT}//wp-content/cache/lwsoptimize/cache-mobile/$1index_0.html -f
RewriteRule ^(.*) wp-content/cache/lwsoptimize/cache-mobile/$1index_0.html [L]

## Anonymous desktop ##
RewriteCond %{QUERY_STRING} !^((gclid|fbclid|y(ad|s)?clid|utm_(source|medium|campaign|content|term)=[^&]+)+)$ [NC]
RewriteCond %{HTTP_HOST} ^mimshack230.com
RewriteCond %{HTTP:Cookie} !wordpress_logged_in_[^\=]+\=compte_mimshack
RewriteCond %{HTTP_USER_AGENT} '!(LWS_Optimize_Preload|LWS_Optimize_Preload_Mobile)' [NC]
RewriteCond %{REQUEST_METHOD} !POST
RewriteCond %{REQUEST_URI} !(\/){2,}
RewriteCond %{THE_REQUEST} !(\/){2,}
RewriteCond %{REQUEST_URI} \/$
RewriteCond %{QUERY_STRING} !.+
RewriteCond %{HTTP:Cookie} !comment_author_
RewriteCond %{HTTP:Profile} !^[a-z0-9\"]+ [NC]
RewriteCond %{HTTP:Cookie} !wordpress_logged_in [NC]
RewriteCond %{HTTP_USER_AGENT} !^.*\bCrMo\b|CriOS|Android.*Chrome\/[.0-9]*\s(Mobile)?|\bDolfin\b|Opera.*Mini|Opera.*Mobi|Android.*Opera|Mobile.*OPR\/[0-9.]+|Coast\/[0-9.]+|Skyfire|Mobile\sSafari\/[.0-9]*\sEdge|IEMobile|MSIEMobile|fennec|firefox.*maemo|(Mobile|Tablet).*Firefox|Firefox.*Mobile|FxiOS|bolt|teashark|Blazer|Version.*Mobile.*Safari|Safari.*Mobile|MobileSafari|Tizen|UC.*Browser|UCWEB|baiduboxapp|baidubrowser|DiigoBrowser|Puffin|\bMercury\b|Obigo|NF-Browser|NokiaBrowser|OviBrowser|OneBrowser|TwonkyBeamBrowser|SEMC.*Browser|FlyFlow|Minimo|NetFront|Novarra-Vision|MQQBrowser|MicroMessenger|Android.*PaleMoon|Mobile.*PaleMoon|Android|blackberry|\bBB10\b|rim\stablet\sos|PalmOS|avantgo|blazer|elaine|hiptop|palm|plucker|xiino|Symbian|SymbOS|Series60|Series40|SYB-[0-9]+|\bS60\b|Windows\sCE.*(PPC|Smartphone|Mobile|[0-9]{3}x[0-9]{3})|Window\sMobile|Windows\sPhone\s[0-9.]+|WCE;|Windows\sPhone\s10.0|Windows\sPhone\s8.1|Windows\sPhone\s8.0|Windows\sPhone\sOS|XBLWP7|ZuneWP7|Windows\sNT\s6\.[23]\;\sARM\;|\biPhone.*Mobile|\biPod|\biPad|Apple-iPhone7C2|MeeGo|Maemo|J2ME\/|\bMIDP\b|\bCLDC\b|webOS|hpwOS|\bBada\b|BREW.*$ [NC]
RewriteCond %{DOCUMENT_ROOT}//wp-content/cache/lwsoptimize/cache/$1index_0.html -f
RewriteRule ^(.*) wp-content/cache/lwsoptimize/cache/$1index_0.html [L]

FileETag None
Header unset ETag
</IfModule>

<FilesMatch "index_.*\.html$">
<If "%{REQUEST_URI} =~ m#wp\-content/cache/lwsoptimize/cache#">
Header set Edge-Cache-Platform 'lwsoptimize'
</If>
</FilesMatch>
#END LWS OPTIMIZE - CACHING
	# BEGIN WP MANAGER
	# Règles ajoutées par LWS Wordpress Manager, ne pas éditer à la main
	# Rules added by LWS Wordpress Manager, do not edit by hand
	Options +Indexes
RewriteRule ^author/(.+) "-" [F]
RewriteRule (.+)\.sql$ "-" [F]
RewriteRule (license\.txt|readme\.html)$ "-" [F]
RewriteRule xmlrpc\.php$ "-" [F]
<If "%{REQUEST_URI} =~ m#wp-content/uploads/.+\.php#">
    SetHandler !
</If>
RewriteCond %{HTTP:X-Forwarded-Proto} !https
RewriteCond %{HTTPS} !on
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [redirect=301,L]
<Files ~ "^.*\.([Hh][Tt][AaPp])">
order allow,deny
 deny from all
satisfy all
</Files>
IndexIgnore *
RewriteEngine On
RewriteCond %{HTTP_USER_AGENT} (?:virusbot|spambot|evilbot|acunetix|BLEXBot|domaincrawler\.com|LinkpadBot|MJ12bot/v|majestic12\.co\.uk|AhrefsBot|TwengaBot|SemrushBot|nikto|winhttp|Xenu\s+Link\s+Sleuth|Baiduspider|HTTrack|clshttp|harvest|extract|grab|miner|python-requests) [NC]
RewriteRule ^(.*)$ http://no.access/

	# END WP MANAGER

# BEGIN WordPress
# Les directives (lignes) entre « BEGIN WordPress » et « END WordPress » sont générées
# dynamiquement, et doivent être modifiées uniquement via les filtres WordPress.
# Toute modification des directives situées entre ces marqueurs sera surchargée.
<IfModule mod_rewrite.c>
RewriteEngine On
RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]
RewriteBase /
RewriteRule ^index\.php$ - [L]
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule . /index.php [L]
</IfModule>

# END WordPress

<IfFile /var/www/mimshack230.com/php8.3.socket>
AddHandler "proxy:unix:/var/www/mimshack230.com/php8.3.socket|fcgi://127.0.0.1:9000/" .php
</IfFile>