# WP Testimonial Walls

Ein professionelles WordPress-Plugin zum Erstellen und Anzeigen mehrerer Testimonial-Wände. Jede Wall zeigt Aussagen von Personen oder Unternehmen, wobei der Name im Vordergrund steht und bei Firmen optional ein Logo erscheint.

## 🚀 Features

### Kernfunktionen
- **Mehrere Testimonial-Wände**: Erstellen Sie unbegrenzt viele Wände für verschiedene Zwecke
- **Wiederverwendbare Testimonials**: Ein Testimonial kann mehreren Wänden zugeordnet werden
- **Flexible Layouts**: Grid, Slider und Masonry-Layouts verfügbar
- **Responsive Design**: Funktioniert perfekt auf allen Geräten
- **Drag & Drop**: Einfache Verwaltung der Testimonial-Reihenfolge

### Darstellungsoptionen
- **Shortcode-Integration**: `[wp_testimonial_wall id="123"]`
- **Gutenberg-Block**: Mit Live-Vorschau im Editor
- **Layout-Optionen**: Grid (1-4 Spalten), Slider, Masonry
- **Logo-Unterstützung**: Optionale Firmenlogos mit Lazy Loading

### Performance & SEO
- **Caching-System**: Transients für optimale Performance (≤50KB Assets)
- **Lazy Loading**: Für Bilder und Logos
- **Strukturierte Daten**: Schema.org für bessere SEO
- **DSGVO-konform**: Keine externen CDNs oder Tracker

### Barrierefreiheit
- **WCAG 2.1 AA konform**: Vollständige Barrierefreiheit
- **ARIA-Labels**: Für Screenreader optimiert
- **Tastatursteuerung**: Vollständige Navigation per Tastatur
- **RTL-Unterstützung**: Für Rechts-nach-Links-Sprachen

## 📋 Systemanforderungen

- **WordPress**: 6.0 oder höher
- **PHP**: 8.1 oder höher
- **MySQL**: 5.7 oder höher

## 🛠 Installation

### Automatische Installation
1. WordPress Admin → Plugins → Neu hinzufügen
2. Nach "WP Testimonial Walls" suchen
3. Installieren und aktivieren

### Manuelle Installation
1. Plugin-Dateien in `/wp-content/plugins/wp-testimonial-walls/` hochladen
2. Plugin in WordPress Admin aktivieren
3. Zu "Testimonial Walls" im Admin-Menü navigieren

## 🎯 Schnellstart

### 1. Erstes Testimonial erstellen
```
Admin → Testimonial Walls → Add New Testimonial
- Titel: Aussage/Zitat eingeben
- Name der Person: [Erforderlich]
- Unternehmen: [Optional]
- Logo: [Optional, empfohlen 200x100px]
```

### 2. Testimonial-Wand erstellen
```
Admin → Testimonial Walls → Add New Wall
- Titel der Wand eingeben
- Layout wählen (Grid/Slider/Masonry)
- Testimonials zuordnen (Drag & Drop)
- Einstellungen konfigurieren
```

### 3. Wand anzeigen
```html
<!-- Shortcode -->
[wp_testimonial_wall id="123"]

<!-- Mit Optionen -->
[wp_testimonial_wall id="123" layout="slider" columns="3" show_logos="true"]
```

## 📖 Verwendung

### Shortcode-Parameter

| Parameter | Typ | Standard | Beschreibung |
|-----------|-----|----------|--------------|
| `id` | Integer | - | **Erforderlich**: ID der Testimonial-Wand |
| `layout` | String | Wall-Standard | Layout überschreiben: `grid`, `slider`, `masonry` |
| `columns` | Integer | Wall-Standard | Spaltenanzahl (1-4, nur Grid/Masonry) |
| `show_logos` | Boolean | Wall-Standard | Logos anzeigen: `true`, `false` |
| `class` | String | - | Zusätzliche CSS-Klasse |

### Beispiele

```html
<!-- Basis-Verwendung -->
[wp_testimonial_wall id="123"]

<!-- Grid mit 2 Spalten -->
[wp_testimonial_wall id="123" layout="grid" columns="2"]

<!-- Slider ohne Logos -->
[wp_testimonial_wall id="123" layout="slider" show_logos="false"]

<!-- Mit eigener CSS-Klasse -->
[wp_testimonial_wall id="123" class="my-custom-testimonials"]
```

### Gutenberg-Block

1. **Block hinzufügen**: "Testimonial Wall" im Block-Inserter suchen
2. **Wand auswählen**: Aus Dropdown-Liste wählen
3. **Optionen anpassen**: Layout, Spalten, Logos in der Seitenleiste
4. **Live-Vorschau**: Sofortige Vorschau im Editor

## 🎨 Anpassung

### CSS-Variablen
```css
:root {
  --testimonial-primary-color: #0073aa;
  --testimonial-text-color: #333;
  --testimonial-background: #fff;
  --testimonial-border-radius: 8px;
  --testimonial-spacing: 1.5rem;
}
```

### BEM-CSS-Klassen
```css
.wp-testimonial-wall { /* Haupt-Container */ }
.wp-testimonial-wall__container { /* Testimonial-Container */ }
.wp-testimonial-wall__item { /* Einzelnes Testimonial */ }
.wp-testimonial-wall__content { /* Testimonial-Text */ }
.wp-testimonial-wall__author { /* Autor-Bereich */ }
.wp-testimonial-wall__name { /* Name der Person */ }
.wp-testimonial-wall__company { /* Firmenname */ }
.wp-testimonial-wall__logo { /* Firmenlogo */ }
```

### Layout-spezifische Klassen
```css
.wp-testimonial-wall--grid { /* Grid-Layout */ }
.wp-testimonial-wall--slider { /* Slider-Layout */ }
.wp-testimonial-wall--masonry { /* Masonry-Layout */ }
.wp-testimonial-wall--columns-3 { /* 3-Spalten-Layout */ }
```

## ⚙️ Konfiguration

### Plugin-Einstellungen
```
Admin → Testimonial Walls → Settings

Cache-Dauer: 3600 Sekunden (Standard)
Lazy Loading: Aktiviert (empfohlen)
RTL-Unterstützung: Aktiviert
Strukturierte Daten: Aktiviert (SEO)
```

### Performance-Optimierung
- **Cache-Dauer**: Anpassen je nach Aktualisierungshäufigkeit
- **Lazy Loading**: Für bessere Ladezeiten aktiviert lassen
- **Bildgrößen**: Logos optimal bei 200x100px
- **Testimonial-Länge**: Kurze, prägnante Texte bevorzugen

## 🔧 Entwicklung

### Hooks & Filter

#### Actions
```php
// Nach Plugin-Initialisierung
do_action('wp_testimonial_walls_init');

// Nach Testimonial-Speicherung
do_action('wp_testimonial_walls_testimonial_saved', $testimonial_id);

// Nach Wall-Speicherung
do_action('wp_testimonial_walls_wall_saved', $wall_id);
```

#### Filter
```php
// Testimonial-Ausgabe anpassen
apply_filters('wp_testimonial_walls_testimonial_content', $content, $testimonial);

// Wall-Einstellungen anpassen
apply_filters('wp_testimonial_walls_wall_settings', $settings, $wall_id);

// CSS-Klassen anpassen
apply_filters('wp_testimonial_walls_css_classes', $classes, $wall_id);
```

### Eigene Layouts erstellen
```php
// Layout registrieren
add_filter('wp_testimonial_walls_layouts', function($layouts) {
    $layouts['custom'] = __('Custom Layout', 'textdomain');
    return $layouts;
});

// Layout-Template
add_action('wp_testimonial_walls_render_layout_custom', function($testimonials, $settings) {
    // Eigene Layout-Logik hier
});
```

## 🌍 Mehrsprachigkeit

### Unterstützte Sprachen
- **Deutsch** (de_DE) - Vollständig übersetzt
- **Englisch** (en_US) - Standard

### Eigene Übersetzungen
1. `.pot`-Datei aus `/languages/` verwenden
2. Mit Poedit oder ähnlichem Tool übersetzen
3. `.po` und `.mo` Dateien in `/languages/` speichern
4. Format: `wp-testimonial-walls-{locale}.po`

## 🚨 Fehlerbehebung

### Häufige Probleme

#### Testimonials werden nicht angezeigt
```
✓ Wall-ID korrekt?
✓ Testimonials der Wall zugeordnet?
✓ Wall veröffentlicht?
✓ Cache geleert?
```

#### Styling-Probleme
```
✓ Theme-Konflikte prüfen
✓ CSS-Spezifität erhöhen
✓ Browser-Cache leeren
✓ Plugin-CSS aktiviert?
```

#### Performance-Probleme
```
✓ Cache-Einstellungen prüfen
✓ Lazy Loading aktiviert?
✓ Bildgrößen optimiert?
✓ Testimonial-Anzahl reduzieren?
```

### Debug-Modus
```php
// In wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);

// Plugin-spezifisches Debugging
define('WP_TESTIMONIAL_WALLS_DEBUG', true);
```

## 📊 Wartung

### Cache verwalten
```
Admin → Testimonial Walls → Settings → Maintenance
- Cache leeren: Alle zwischengespeicherten Daten entfernen
- Datenbank bereinigen: Verwaiste Beziehungen entfernen
```

### Backup-Empfehlungen
- **Datenbank**: Regelmäßige Backups der WordPress-Datenbank
- **Uploads**: Backup des `/wp-content/uploads/` Ordners für Logos
- **Plugin-Einstellungen**: Export über WordPress-Tools

## 🔒 Sicherheit

### Best Practices
- **Berechtigungen**: Nur vertrauenswürdige Benutzer können Testimonials verwalten
- **Eingabe-Validierung**: Alle Eingaben werden sanitized und validiert
- **Nonces**: Schutz vor CSRF-Angriffen
- **Capability-Checks**: Berechtigungsprüfung bei allen Aktionen

### DSGVO-Konformität
- **Keine externen Requests**: Alle Assets werden lokal geladen
- **Keine Tracking-Cookies**: Plugin setzt keine Cookies
- **Datenminimierung**: Nur notwendige Daten werden gespeichert
- **Löschung**: Vollständige Entfernung bei Plugin-Deinstallation

## 📈 Updates

### Automatische Updates
- Plugin unterstützt WordPress Auto-Updates
- Datenbank-Migrationen werden automatisch durchgeführt
- Einstellungen bleiben bei Updates erhalten

### Changelog
Siehe [CHANGELOG.md](CHANGELOG.md) für detaillierte Versionshistorie.

## 🤝 Support

### Community-Support
- **GitHub Issues**: [Repository Issues](https://github.com/psart-scs/WP-Testimonial-Walls/issues)
- **WordPress Forum**: Plugin-Support-Forum
- **Dokumentation**: Vollständige Docs auf GitHub

### Beitragen
1. Repository forken
2. Feature-Branch erstellen (`git checkout -b feature/AmazingFeature`)
3. Änderungen committen (`git commit -m 'Add AmazingFeature'`)
4. Branch pushen (`git push origin feature/AmazingFeature`)
5. Pull Request erstellen

## 📄 Lizenz

Dieses Plugin ist unter der GPL v2 oder höher lizenziert. Siehe [LICENSE](LICENSE) für Details.

## 👨‍💻 Autor

**psart-scs**
- GitHub: [@psart-scs](https://github.com/psart-scs)
- Website: [psart-scs.com](https://psart-scs.com)

## 🙏 Danksagungen

- WordPress-Community für die ausgezeichnete Dokumentation
- Alle Beta-Tester und Feedback-Geber
- Open-Source-Bibliotheken und Tools

---

**Made with ❤️ for the WordPress Community**
