��    �      L  �   |      H  s   I  l   �  {   *  j   �  ;    !  M     o  �   �          ,     K  ?   e     �  0   �  8   �     +    2     I      Y  8   z  B   �  C   �  I   :  J   �  :   �     
  *  "  E   M  E   �     �  f   �  �   ]  �      �  �   �      �  &   �  N     �   m     �       �  3     �     �  s   �     a  �   t     [     l     �  $   �  y   �  �   /   O   �   I   !  M   N!  G   �!  �   �!    �"     �#     �#  �   �#     �$     �$     �$  d   �$     `%  #   q%  -   �%     �%  )   �%  /   &  *   5&  0   `&     �&     �&     �&      �&     �&     �&     '  $   0'  1   U'     �'  J   �'  $   �'     	(     "(     8(  %   @(     f(     o(     |(  >   �(  ?   �(  F   )  )   \)  M   �)  9   �)     *  `   *     �*  >  �*  �   �+  T   u,  �   �,  S   m-  �   �-  P   �.  e  �.  ,   N2  �   {2  >   ]3  R  �3  l  �4  �   \6  0   Y7     �7  !   �7  #   �7  !   �7     8     8     &8  .   88     g8     s8     �8  �   �8     >9     W9  �   Z9  �   3:  �  ;  x   =  r   |=  �   �=  {   p>  B  �>  7  /@     gA  �   }A     +B     @B     `B  E   zB     �B  7   �B  B   C     YC    _C     rD  &   �D  ;   �D  J   �D  L   0E  Q   }E  R   �E  F   "F  %   iF  ,  �F  _   �G  J   H  #   gH  �   �H  �   I    �I  2  �K  	  &M  %   0N  %   VN  S   |N  �   �N     kO     �O  �  �O     �Q  
   �Q  �   �Q     NR  �   aR     OS     _S     S     �S  �   �S  �   /T  G   �T  G   U  G   _U  G   �U  �   �U  H  �V  !   X     1X  �   GX     ,Y     EY      MY  �   nY     �Y  3   
Z  2   >Z  $   qZ  0   �Z  )   �Z  0   �Z  )   "[     L[     ^[     k[  !   �[     �[     �[     �[  &    \  5   '\     ]\  U   w\  ,   �\     �\     ]     /]  )   7]     a]     n]     �]  F   �]  H   �]  N   1^  )   �^  P   �^  8   �^     4_  f   G_     �_  l  �_  �   3a  [    b  �   \b  [   c  �   kc  [   Sd  �  �d  /   Ch     sh  9   ti  �  �i  �  >k  �   �l  5   �m     #n  +   ;n  )   gn  "   �n     �n     �n     �n  ,   �n     o     )o     Eo  �   ^o  $   p     -p  �   2p  �   q     U                  0   R           >   :       �              �   -          =      7              ;   f   g       	   y   3   1            .         D   !   X   v   
   P   I   C   n   K   Z       b       {   J   j      S          W       T   /   V              l   N       �   \           @          |   s   Y      #              u   Q   ?       q      O   A       �       9   �       ]   <               4   '      d       }       5               $       m      )   o   *       ,       r   H      i   "   c          e   ^   `      B   +   G   x   k   (   2   p   %   �   _   &   �      M             E      [      ~   z   w   L             t   8   h           6   F   a            # Example: Category separator (/) and article-word separator (-)
category_one/category_two/article-description.html # Example: Category separator (/) and category-word separator (_)
category_one/category_two/articlename.html # Example: Category-article separator (/) and article-word separator (-)
category_one/category_two/article-description.html # enable apache mod rewrite module
RewriteEngine on

# disable apache mod rewrite module
RewriteEngine off # structure of a normal url
$url = 'front_content.php?idart=123&amp;lang=2&amp;client=1';

# creation of a url by using the CONTENIDOs Url-Builder (since 4.8.9),
# wich expects the parameter as a assoziative array
$params = array('idart'=>123, 'lang'=>2, 'client'=>1);
$newUrl = cUri::getInstance()->build($params); # {incoming_url}>>>{new_url}
/incoming_url/name.html>>>new_url/new_name.html

# route a specific incoming url to a new page
/campaign/20_percent_on_everything_except_animal_food.html>>>front_content.php?idcat=23

# route request to wwwroot to a specific page
/>>>front_content.php?idart=16 (possible values: %s) 100 = exact match with no tolerance
85  = paths with little errors will match to similar ones
0   = matching will work even for total wrong paths Advanced Mod Rewrite Advanced Mod Rewrite functions Advanced Mod Rewrite test Append article name always to URLs (even at URLs to categories) Append article name to URLs Are several clients maintained in one directory? Article-word separator (delemiter between article words) Author By using this option, all Clean-URLs will be generated directly in module or plugins.<br>This means, all areas in modules/plugins, who generate internal URLs to categories/articles, have to be adapted manually.<br>All Clean-URLs have to be generated by using following function: CONTENIDO forum CONTENIDO installation directory Category separator (delemiter between single categories) Category separator has to be different from article-word separator Category separator has to be different from category-word separator Category-article separator (delemiter between category-block and article) Category-article separator has to be different from article-word separator Category-word separator (delemiter between category words) Check path to .htaccess Clean-URLs will be generated during page output. Modules/Plugins are able to generate URLs to frontend<br>as usual as in previous CONTENIDO versions using a format like "front_content.php?idcat=1&amp;idart=2".<br>The URLs will be replaced by the plugin to Clean-URLs before sending the HTML output. Configuration could not saved. Please check write permissions for %s  Configuration has <b>not</b> been saved, because of enabled debugging Configuration has been saved Configure your own separators with following 4 settings<br>to control generated URLs to your own taste Contains a simple collection of rules. Each requests pointing to valid symlinks, folders or<br>files, will be excluded from rewriting. Remaining requests will be rewritten to front_content.php Contains rules with restrictive settings.<br>All requests pointing to extension avi, css, doc, flv, gif, gzip, ico, jpeg, jpg, js, mov, <br>mp3, pdf, png, ppt, rar, txt, wav, wmv, xml, zip, will be excluded vom rewriting.<br>Remaining requests will be rewritten to front_content.php,<br>except requests to 'contenido/', 'setup/', 'cms/upload', 'cms/front_content.php', etc.<br>Each resource, which has to be excluded from rewriting must be specified explicitly. Copy the selected .htaccess template into CONTENIDO installation directory<br><br>&nbsp;&nbsp;&nbsp;&nbsp;{CONTENIDO_FULL_PATH}.<br><br>This is the recommended option for a CONTENIDO installation with one or more clients<br>who are running on the same domain. Copy the selected .htaccess template into client's directory<br><br>&nbsp;&nbsp;&nbsp;&nbsp;{CLIENT_FULL_PATH}.<br><br>This is the recommended option for a multiple client system<br>where each client has it's own domain/subdomain Copy/Download .htaccess template Default article name without extension Define options to genereate the URLs by using the form below and run the test. Depending on configuration, pages could be found thru different URLs.<br>Enabling of this option prevents this. Examples for duplicated content Differences to variant a.) Differences to variant b.) Disabling of plugin does not result in disabling mod rewrite module of the web server - This means,<br> all defined rules in the .htaccess are still active and could create unwanted side effects.<br><br>Apache mod rewrite could be enabled/disabled by setting the RewriteEngine directive.<br>Any defined rewrite rules could remain in the .htaccess and they will not processed,<br>if the mod rewrite module is disabled Discard changes Download Download selected .htaccess template to copy it to the destination folder<br>or to take over the settings manually. Duplicated content Duration of test run: {time} seconds.<br>Number of processed URLs: {num_urls}<br><span class="settingFine">Successful resolved: {num_success}</span><br><span class="settingWrong">Errors during resolving: {num_fail}</span></strong> E-Mail to author Enable Advanced Mod Rewrite Example File extension at the end of the URL Found some category and/or article aliases. It is recommended to run the reset function in %sFunctions%s area, if needed. If enabled, the name of the root category (e. g. "Mainnavigation" in a CONTENIDO default installation), will be preceded to the URL. Invalid separator for article words, allowed is one of following characters: %s Invalid separator for article, allowed is one of following characters: %s Invalid separator for category words, allowed one of following characters: %s Invalid separator for category, allowed one of following characters: %s It's necessary to specify a file extension at the moment, due do existing issues, which are not solved until yet. An not defined extension may result in invalid article detection in some cases. It's strongly recommended to specify a extension here,<br>if the option "Append article name always to URLs" was not selected.<br><br>Otherwise URLs to categories and articles would have the same format<br>which may result in unresolvable categories/articles in some cases. Moment of URL generation More informations Name of the root category in the URL: Feasible is /maincategory/subcategory/ and /subcategory/
Language in the URL: Feasible is /german/category/ and /1/category/
Client in the URL: Feasible is /client/category/ und /1/category/ No Client selected Note Number of URLs to generate Only empty aliases will be reset, existing aliases, e. g. manually set aliases, will not be changed. Parameter to use Path to .htaccess from DocumentRoot Percentage for similar category paths in URLs Please check your input Please specify separator (%s) for article Please specify separator (%s) for article words Please specify separator (%s) for category Please specify separator (%s) for category words Plugin functions Plugin page Plugin settings Plugin thread in CONTENIDO forum Prepend client to the URL Prepend language to the URL Prevent duplicated content Redirect in case of invalid articles Redirect to error page in case of invaid articles Reset all aliases Reset all category-/article aliases. Existing aliases will be overwritten. Reset category-/ and article aliases Reset only empty aliases Restrictive .htaccess Routing Routing definitions for incoming URLs Run test Save changes Select .htaccess template Separator for category and article words must not be identical Separator for category and category words must not be identical Separator for category-article and article words must not be identical Should the URLs be written in lower case? Should the language appear in the URL (required for multi language websites)? Should the name of root category be displayed in the URL? Simple .htaccess Specification of file extension with a preceded dot<br>e.g. ".html" for http://host/foo/bar.html Start from root category Still compatible to old modules/plugins, since no changes in codes are required
All occurring URLs in HTML code, even those set by wysiwyg, will be switched to Clean-URLs
All URLs will usually be collected and converted to Clean-URLs at once.<br>Doing it this way reduces the amount of executed database significantly. The .htaccess file could not found either in CONTENIDO installation directory nor in client directory.<br>It should set up in %sFunctions%s area, if needed. The article name has a invalid format, allowed are the chars /^[a-zA-Z0-9\-_\/\.]*$/ The default way to generate URLs to fronend pages
Each URL in modules/plugins has to be generated by UriBuilder
Each generated Clean-Url requires a database query The file extension has a invalid format, allowed are the chars \.([a-zA-Z0-9\-_\/]) The path will be checked, if this option is enabled.<br>But this could result in an error in some cases, even if the specified path is valid and<br>clients DocumentRoot differs from CONTENIDO backend DocumentRoot. The root directory has a invalid format, alowed are the chars [a-zA-Z0-9\-_\/\.] The routing does not sends a HTTP header redirection to the destination URL, the redirection will happen internally by<br>replacing the detected incoming URL against the new destination URL (overwriting of article- categoryid)
Incoming URLs can point to non existing resources (category/article), but the desttination URLs should point<br>to valid CONTENIDO articles/categories
Destination URLs should point to real URLs to categories/articles,<br>e. g.front_content.php?idcat=23 or front_content.php?idart=34
The language id should attached to the URL in multi language sites<br>e. g. front_content.php?idcat=23&amp;lang=1
The client id should attached to the URL in multi client sites sharing the same folder<br>e. g. front_content.php?idcat=23&amp;client=2
The destination URL should not start with '/' or './' (wrong: /front_content.php, correct: front_content.php) The specified directory "%s" does not exists The specified directory "%s" does not exists in DOCUMENT_ROOT "%s". this could happen, if clients DOCUMENT_ROOT differs from CONTENIDO backends DOCUMENT_ROOT. However, the setting will be taken over because of disabled check. The start page will be displayed if this option is not enabled This process could require some time depending on amount of categories/articles.<br>The aliases will not contain the configured plugin separators, but the CONTENIDO default separators '/' und '-', e. g. '/category-word/article-word'.<br>Execution of this function ma be helpful to prepare all or empty aliases for the usage by the plugin. This setting refers only to the category path of a URL. If AMR is configured<br>to prepend e. g. the root category, language and/or client to the URL,<br>the specified percentage will not applied to those parts of the URL.<br>A incoming URL will be cleaned from those values and the remaining path (urlpath of the category)<br>will be checked against similarities. Type '/' if the .htaccess file lies inside the wwwroot (DocumentRoot) folder.<br>Type the path to the subfolder fromm wwwroot, if CONTENIDO is installed in a subfolder within the wwwroot<br>(e. g. http://domain/mycontenido -&gt; path = '/mycontenido/') Type one routing definition per line as follows: URLs in lower case Use client name instead of the id Use language name instead of the id Value has to be between 0 an 100. Value has to be numeric. Version Visit plugin page a.) During the output of HTML code of the page and copy to b.) In modules or plugins client directory e. g. "index" for index.ext<br>In case of selected "Append article name always to URLs" option and a empty field,<br>the name of the start article will be used opens page in new window or www.domain.com/category1-category2.articlename.html
www.domain.com/category1/category2-articlename.html
www.domain.com/category.name1~category2~articlename.html
www.domain.com/category_name1-category2-articlename.foo {pref}<strong>{name}</strong><br>{pref}Builder in:    {url_in}<br>{pref}Builder out:   {url_out}<br>{pref}<span style="color:{color}">Resolved URL:  {url_res}</span><br>{pref}Resolver err:  {err}<br>{pref}Resolved data: {data} Project-Id-Version: CONTENIDO Plugin Advanced Mod Rewrite
Report-Msgid-Bugs-To: 
POT-Creation-Date: 2013-06-01 10:27+0200
PO-Revision-Date: 2014-08-21 08:48+0100
Last-Translator: Frederic Schneider <frederic.schneider@4fb.de>
Language-Team: Murat Purc <murat@purc.de>
Language: de_DE
MIME-Version: 1.0
Content-Type: text/plain; charset=UTF-8
Content-Transfer-Encoding: 8bit
X-Poedit-Basepath: ../../../
X-Poedit-KeywordsList: i18n
X-Generator: Poedit 1.6.7
X-Poedit-SearchPath-0: mod_rewrite
 # Beispiel: Kategorie-Separator (/) und Artikelwort-Separator (-)
kategorie_eins/kategorie_zwei/artikel-bezeichnung.html # Beispiel: Kategorie-Separator (/) und Kategoriewort-Separator (_)
kategorie_eins/kategorie_zwei/artikelname.html # Beispiel: Kategorie-Artikel-Separator (/) und Artikelwort-Separator (-)
kategorie_eins/kategorie_zwei/artikel-bezeichnung.html # aktivieren des apache mod rewrite moduls
RewriteEngine on

# deaktivieren des apache mod rewrite moduls
RewriteEngine off # aufbau einer normalen url
$url = 'front_content.php?idart=123&amp;lang=2&amp;client=1';

# erstellen der neuen Url über CONTENIDOs Url-Builder (seit 4.8.9),
# der die Parameter als assoziatives array erwartet
$params = array('idart'=>123, 'lang'=>2, 'client'=>1);
$newUrl = Contenido_Url::getInstance()->build($params); # {eingehende_url}>>>{neue_url}
/eingehende_url/name.html>>>neue_url/neuer_name.html

# bestimmte eingehende url zur einer seite weiterleiten
/aktionen/20_prozent_auf_alles_ausser_tiernahrung.html>>>front_content.php?idcat=23

# request zum wwwroot auf eine bestimmte seite routen
/>>>front_content.php?idart=16 (mögliche Werte: %s) 100 = exakte überinstimmung, keine fehlertoleranz
85  = pfade mit kleineren fehlern ergeben auch treffern
0   = vollständig fehlerhafte pfade ergeben dennoch einen treffer Advanced Mod Rewrite Advanced Mod Rewrite-Funktionen Advanced Mod Rewrite-Test Artikelname immer an die URLs anhängen (auch bei URLs zu Kategorien) Artikelname an URLs anhängen Werden mehrere Mandanten in einem Verzeichnis gepflegt? Artikelwort-Separator (Trenner zwischen einzelnen Artikelwörtern) Autor Bei dieser Option werden die Clean-URLs direkt in Modulen/Plugins generiert. Das bedeutet,<br>dass alle internen URLs auf Kategorien/Artikel in den Modulausgaben ggf. manuell angepasst werden müssen. <br>Clean-URLs müssen dann stets mit folgender Funktion erstellt werden: CONTENIDO-Forum das CONTENIDO Installationsverzeichnis Kategorie-Separator (Trenner zwischen einzelnen Kategorien) Kategorie-Separator und Artikelwort-Separator müssen unterschiedlich sein Kategorie-Separator und Kategoriewort-Separator müssen unterschiedlich sein Kategorie-Artikel-Separator (Trenner zwischen Kategorieabschnitt und Artikelname) Kategorie-Artikel-Separator und Artikelwort-Separator müssen unterschiedlich sein Kategoriewort-Separator (Trenner zwischen einzelnen Kategoriewörtern) Pfad zur .htaccess-Datei überprüfen Clean-URLs werden bei der Ausgabe der Seite generiert. Module/Plugins können URLs zum Frontend, <br>wie in früheren Versionen von CONTENIDO üblich, nach dem Muster "front_content.php?idcat=1&amp;idart=2" <br>ausgeben. Die URLs werden vom Plugin vor der Ausgabe des HTML-Outputs Clean-URLs ersetzt. Konfiguration konnte nicht gespeichert werden. Überprüfen Sie bitte die Schreibrechte für %s Konfiguration wurde <b>nicht</b> gespeichert, weil das Debugging aktiv ist Die Konfiguration wurde gespeichert Mit den nächsten 4 Einstellungen können die Trennzeichen in den<br>generierten URLs nach den eigenen Wünschen gesetzt werden. Enthält eine einfachere Sammlung an Regeln. Alle Anfragen, die auf gültige Symlinks, Verzeichnisse oder<br>Dateien gehen, werden vom Umschreiben ausgeschlossen. Restliche Anfragen werden an front_content.php<br>umschrieben. Enthält Regeln mit restriktiveren Einstellungen.<br>Alle Anfragen, die auf die Dateienendung avi, css, doc, flv, gif, gzip, ico, jpeg, jpg, js, mov, <br>mp3, pdf, png, ppt, rar, txt, wav, wmv, xml, zip gehen, werden vom Umschreiben ausgeschlossen.<br>Alle anderen Anfragen, werden an front_content.php umschrieben.<br>Ausgeschlossen davon sind 'contenido/', 'setup/', 'cms/upload', 'cms/front_content.php', usw.<br>Jede neue Ressource, die vom Umschreiben ausgeschlossen werden soll, muss explizit definiert werden. Die gewählte .htaccess Vorlage in das CONTENIDO Installationsverzeichnis<br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;{CONTENIDO_FULL_PATH}<br>
<br>
kopieren.<br>
Das ist die empfohlene Option für eine CONTENIDO-Installation mit einem Mandanten oder<br>
mehreren Mandanten, die alle unter der gleichen Domain laufen. Die gewählte .htaccess Vorlage in das Mandantenerzeichnis<br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;{CLIENT_FULL_PATH}<br>
<br>
kopieren.<br>
Diese Option ist z. B. bei einem Mehrmandantensystem empfohlen,<br>
wenn jeder Mandant unter einer eigenen Domain/Subdomain läuft. .htaccess Vorlage kopieren/downloaden Standard-Artikelname ohne Dateiendung Optionen zum Generieren der URLs im folgenden Formular setzen und den Test starten. Seiten werden je nach Konfiguration unter verschiedenen URLs gefunden.<br>Das Aktivieren dieser Option unterbindet dies. Beispiele für duplicated Content Unterschiede zur Variante a.) Unterschiede zur Variante b.) Beim Deaktivieren des Plugins wird das mod rewrite Modul des Webservers nicht mit deaktiviert - Das bedeutet, <br>dass alle in der .htaccess definerten Regeln weiterhin aktiv sind und einen unerwünschten Nebeneffekt haben können.<br><br>Apache mod rewrite lässt sich in der .htaccess durch das Setzen der RewriteEngine-Direktive aktivieren/deaktivieren.<br>Wird das mod rewrite Modul deaktiviert, können die in der .htaccess definierten Regeln weiterhin bleiben, sie werden <br>dann nicht verarbeitet. Änderungen verwerfen Downloaden Die gewählte .htaccess Vorlage downloaden um z. B. die Datei manuell<br>
in das Verzeichnis zu kopieren oder Einstellungen zu übernehmen. Duplicated Content Dauer des Testdurchlaufs: {time} Sekunden.<br>
Anzahl verarbeiteter URLs: {num_urls}<br>
<span style="color:green">Erfolgreich aufgelöst: {num_success}</span><br>
<span style="color:red">Fehler beim Auflösen: {num_fail}</span></strong> E-Mail an Autor Advanced Mod Rewrite aktivieren Beispiel Dateiendung am Ende der URL Einige leere Kategorie- und/oder Artikelaliase wurden gefunden. Es wird empfohlen, diese über den Bereich %sFunktionen%s zurückzusetzen. Ist diese Option gewählt, wird der Name des Hauptbaumes (Kategoriebaum, <br>z. B. "Hauptnavigation" bei CONTENIDO Standardinstallation) der URL vorangestellt. Trenner für Kategorie ist ungültig, erlaubt ist eines der Zeichen: %s Trenner für Kategorie ist ungültig, erlaubt ist eines der Zeichen: %s Trenner für Kategorie ist ungültig, erlaubt ist eines der Zeichen: %s Trenner für Kategorie ist ungültig, erlaubt ist eines der Zeichen: %s Aufgrund diverser Probleme, die noch nicht in dieser Version gelöst wurden konnten, sollte unbedingt eine Endung angegeben werden. Ohne eine angegebene Endung kann es zur fehlerhaften Erkennung der Artikel kommen. Falls die Option "Artikelname immer an die URLs anhängen" nicht gewählt wurde, <br>ist es zwingend notwending, dass hier eine Dateiendung angegeben wird.<br><br>Sonst haben URLs zu Kategorien und zu Seiten das gleiche Format, und die korrekte<br>Auflösung der Kategorie und/oder des Artikels kann nicht gewährleistet werden. Zeitpunkt zum Generieren der URLs Weitere Informationen Name des Hauptbaumes in der URL: Möglich /hauptkategorie/unterkategorie/ und /unterkategorie/
Sprache in der URL: Möglich /deutsch/kategorie/ und /1/kategorie/
Mandant in der URL: Möglich /mandant/kategorie/ und /1/kategorie/ Kein Mandant ausgewählt Hinweis Anzahl der zu generierenden URLs Nur leere Kategorie-/Artikelaliase initial setzen<br>Vorhandene Aliase, z.B. vorher manuell gesetze Aliase werden nicht geändert. Zu verwendende Parameter Pfad zur .htaccess-Datei vom DocumentRoot ausgehend Prozentsatz für ähnliche Kategorie-Pfade in URLs Bitte überprüfen Sie Ihre Eingaben Bitte Trenner (%s) für Kategoriewörter angeben Bitte Trenner (%s) für Kategorie angeben Bitte Trenner (%s) für Kategoriewörter angeben Bitte Trenner (%s) für Kategorie angeben Plugin Funktionen Plugin-Seite Plugin-Einstellungen Plugin-Beitrag im CONTENIDO-Forum Mandant an die URL voranstellen Sprache an die URL voranstellen Duplicated Content verhindern Weiterleitung bei ungültigen Artikeln Bei ungültigen Artikeln zur Fehlerseite weiterleiten Alle Aliase zurücksetzen Alle Kategorie-/Artikelaliase neu setzen.<br>Vorhandene Aliase werden überschrieben. Kategorie-/ und Artikel-Aliase zurücksetzen Nur leere Aliase zurücksetzen Restriktive .htaccess Routing Routing Definitionen für eingehende URLs Test starten Änderungen speichern .htaccess Vorlage auswählen Der Trenner für Kategorien und Artikelwörter darf nicht gleichlauten Trenner für Kategorie und Kategoriewörter dürfen nicht identisch sein Trenner für Kategorie-Artikel und Artikelwörter dürfen nicht identisch sein Sollen die URLs klein geschrieben werden? Soll die Sprache mit in der URL erscheinen (für Mehrsprachsysteme unabdingbar)? Soll der Name des Hauptbaumes mit in der URL erscheinen? Einfache .htaccess Angabe der Dateiendung mit einem vorangestellten Punkt,<br>z. B. ".html" für http://host/foo/bar.html Start vom Hauptbaum aus Weiterhin kompatibel zu älteren Modulen/Plugins, da keine Änderungen am Code nötig sind
Sämtliche im HTML-Code vorkommende URLs, auch über wysiwyg gesetzte URLs, <br>werden auf Clean-URLs umgestellt
Alle umzuschreibenden URLs werden in der Regel "gesammelt" und auf eimmal umgeschreiben, <br>dadurch wird die Anzahl der Datenbank-Abfragen sehr stark minimiert Es wurde weder im CONTENIDO-Installationsverzeichnis noch im Mandantenverzeichnis eine .htaccess-Datei gefunden.<br>Die .htaccess-Datei sollte gegebenenfalls im Bereich %sFunktionen%s eingerichtet werden. Das Rootverzeichnis hat ein ungültiges Format, erlaubt sind die Zeichen [a-zA-Z0-9\-_\/\.] Der Standardweg um URLs zu Frontendseiten zu generieren
Jede URL in Modulen/Plugins ist vom UrlBuilder zu erstellen
Für jede umzuschreibende URL ist eine Datenbankabfrage nötig Das Rootverzeichnis hat ein ungültiges Format, erlaubt sind die Zeichen [a-zA-Z0-9\-_\/\.] Ist diese Option aktiv, wird der eingegebene Pfad überprüft. Das kann unter <br>Umständen einen Fehler verursachen, obwohl der Pfad zwar richtig ist, aber der Mandant <br>einen anderen DocumentRoot als das CONTENIDO Backend hat. Das Rootverzeichnis hat ein ungültiges Format, erlaubt sind die Zeichen [a-zA-Z0-9\-_\/\.] Das Routing schickt keinen HTTP Header mit einer Weiterleitung zur Ziel-URL, die Umleitung findet intern<br>durch das Ersetzen der erkannten Eingangsseite gegen die neue Zielseite statt (Überschreiben der Artikel-/Kategorieid)
Eingehende URLs können auch nicht vorhandene Ressourcen (Kategorie, Artikel) sein, hinter der Ziel URL muss eine <br>gültige CONTENIDO-Seite (Kategorie/Artikel) liegen
Als Ziel URL sollte eine reale URL zur Kategorie/Seite angegeben werden, <br>z. B. front_content.php?idcat=23 oder front_content.php?idart=34.
Bei mehrsprachigen Auftritten sollte die Id der Sprache angehängt werden, <br>z. B. front_content.php?idcat=23&amp;lang=1
Bei mehreren Mandanten im gleichen Verzeichnis sollte die Id des Mandanten angehängt werden,<br>z. B. front_content.php?idcat=23&amp;client=2
Die Zielurl sollte nicht mit '/' oder './' beginnen (falsch: /front_content.php, richtig: front_content.php) Das angegebene Verzeichnis "%s" existiert nicht Das angegebene Verzeichnis "%s" existiert nicht im DOCUMENT_ROOT "%s". Das kann vorkommen, wenn das DOCUMENT_ROOT des Mandanten vom CONTENIDO Backend DOCUMENT_ROOT abweicht. Die Einstellung wird dennoch übernommen, da die Überprüfung abgeschaltet wurde. Ist die Option nicht aktiv, wird die Startseite angezeigt Dieser Prozess kann je nach Anzahl der Kategorien/Artikel etwas Zeit in Anspruch nehmen.<br>
Die Aliase erhalten nicht die oben konfigurierten Separatoren, sondern die CONTENIDO-Standardseparatoren '/' und '-', z. B. '/category-word/article-word'.<br>
Das Ausführen dieser Funktion kann Hilfreich sein, um sämtliche oder nur leere Aliase nachträglich auf die Verwendung mit dem Plugin anzupassen. Diese Einstellung bezieht sich nur auf den Kategorieteil einer URL. Ist AMR so konfiguriert, dass z. B. die<br>Hauptnavigation und/oder Sprache sowie der Name des Mandanten an die URL vorangestellt wird, so wird der hier <br>definierte Prozentsatz nicht auf diese Werte der URL angewendet.<br>Eine ankommende URL wird von solchen Präfixen entfernt, der übrig gebliebene Pfad (Urlpfad der Kategorie)<br>wird auf die Ähnlichkleit hin geprüft. Liegt die .htaccess im wwwroot (DocumentRoot), ist '/' anzugeben, ist CONTENIDO in einem <br>Unterverzeichnis von wwwroot installiert, ist der Pfad vom wwwroot aus anzugeben <br>(z. B. http://domain/mycontenido -&gt; Pfad = '/mycontenido/'). Pro Zeile eine Routing Definition wie folgt eingeben: URLs in Kleinbuchstaben Name des Mandanten anstatt die Id verwenden Name der Sprache anstatt die Id verwenden Wert muss zwischen 0 und 100 sein. Wert muss numerisch sein. Version Plugin-Seite besuchen a.) Bei der Ausgabe des HTML Codes der Seite und kopieren in b.) In Modulen oder Plugins das Mandantenverzeichnis z. B. "index" für index.ext.<br>Wenn die Option "Artikelname immer an die URLs anhängen" aktiviert und das Feld leer ist,<br>wird der Name des Startartikels verwendet. öffnet Seite in einem neuen Fenster oder www.domain.de/kategorie1-kategorie2.artikelname.html
www.domain.de/kategorie1/kategorie2-artikelname.html
www.domain.de/kategorie.name1~kategorie2~artikelname.html
www.domain.de/kategorie_name1-kategorie2-artikelname.foo {pref}<strong>{name}</strong><br>{pref}Builder Eingang:   {url_in}<br>{pref}Builder Ausgang:   {url_out}<br>{pref}<span style="color:{color}">Aufgelöste URL:    {url_res}</span><br>{pref}Aufgelöse-Fehler:  {err}<br>{pref}Aufgelöste Daten:  {data} 