# **WP Testimonial Walls**

A professional WordPress plugin for creating and displaying multiple testimonial walls. Each wall shows statements from people or companies, with the name in the foreground and an optional logo for companies.

## **🚀 Features**

### **Core Functions**

* **Multiple Testimonial Walls**: Create unlimited walls for different purposes  
* **Reusable Testimonials**: A testimonial can be assigned to multiple walls  
* **Flexible Layouts**: Grid, Slider, and Masonry layouts available  
* **Responsive Design**: Works perfectly on all devices  
* **Drag & Drop**: Easy management of testimonial order

### **Display Options**

* **Shortcode Integration**: \[wp\_testimonial\_wall id="123"\]  
* **Gutenberg Block**: With live preview in the editor  
* **Layout Options**: Grid (1-4 columns), Slider, Masonry  
* **Logo Support**: Optional company logos with lazy loading

### **Performance & SEO**

* **Caching System**: Transients for optimal performance (≤50KB assets)  
* **Lazy Loading**: For images and logos  
* **Structured Data**: Schema.org for better SEO  
* **GDPR Compliant**: No external CDNs or trackers

### **Accessibility**

* **WCAG 2.1 AA Compliant**: Full accessibility  
* **ARIA Labels**: Optimized for screen readers  
* **Keyboard Control**: Full keyboard navigation  
* **RTL Support**: For right-to-left languages

## **📋 System Requirements**

* **WordPress**: 6.0 or higher  
* **PHP**: 8.1 or higher  
* **MySQL**: 5.7 or higher

## **🛠 Installation**

### **Automatic Installation**

1. WordPress Admin → Plugins → Add New  
2. Search for "WP Testimonial Walls"  
3. Install and activate

### **Manual Installation**

1. Upload plugin files to /wp-content/plugins/wp-testimonial-walls/  
2. Activate the plugin in WordPress Admin  
3. Navigate to "Testimonial Walls" in the admin menu

## **🎯 Quick Start**

### **1\. Create Your First Testimonial**

Admin → Testimonial Walls → Add New Testimonial  
\- Title: Enter statement/quote  
\- Person's Name: \[Required\]  
\- Company: \[Optional\]  
\- Logo: \[Optional, recommended 200x100px\]

### **2\. Create a Testimonial Wall**

Admin → Testimonial Walls → Add New Wall  
\- Enter wall title  
\- Choose a layout (Grid/Slider/Masonry)  
\- Assign testimonials (Drag & Drop)  
\- Configure settings

### **3\. Display the Wall**

\<\!-- Shortcode \--\>  
\[wp\_testimonial\_wall id="123"\]

\<\!-- With options \--\>  
\[wp\_testimonial\_wall id="123" layout="slider" columns="3" show\_logos="true"\]

## **📖 Usage**

### **Shortcode Parameters**

| Parameter | Type | Default | Description |
| :---- | :---- | :---- | :---- |
| id | Integer | \- | **Required**: ID of the testimonial wall |
| layout | String | Wall default | Override layout: grid, slider, masonry |
| columns | Integer | Wall default | Number of columns (1-4, Grid/Masonry only) |
| show\_logos | Boolean | Wall default | Show logos: true, false |
| class | String | \- | Additional CSS class |

### **Examples**

\<\!-- Basic usage \--\>  
\[wp\_testimonial\_wall id="123"\]

\<\!-- Grid with 2 columns \--\>  
\[wp\_testimonial\_wall id="123" layout="grid" columns="2"\]

\<\!-- Slider without logos \--\>  
\[wp\_testimonial\_wall id="123" layout="slider" show\_logos="false"\]

\<\!-- With custom CSS class \--\>  
\[wp\_testimonial\_wall id="123" class="my-custom-testimonials"\]

### **Gutenberg Block**

1. **Add Block**: Search for "Testimonial Wall" in the block inserter  
2. **Select Wall**: Choose from the dropdown list  
3. **Adjust Options**: Configure layout, columns, and logos in the sidebar  
4. **Live Preview**: Instant preview in the editor

## **🎨 Customization**

### **CSS Variables**

:root {  
  \--testimonial-primary-color: \#0073aa;  
  \--testimonial-text-color: \#333;  
  \--testimonial-background: \#fff;  
  \--testimonial-border-radius: 8px;  
  \--testimonial-spacing: 1.5rem;  
}

### **BEM CSS Classes**

.wp-testimonial-wall { /\* Main container \*/ }  
.wp-testimonial-wall\_\_container { /\* Testimonial container \*/ }  
.wp-testimonial-wall\_\_item { /\* Single testimonial \*/ }  
.wp-testimonial-wall\_\_content { /\* Testimonial text \*/ }  
.wp-testimonial-wall\_\_author { /\* Author area \*/ }  
.wp-testimonial-wall\_\_name { /\* Person's name \*/ }  
.wp-testimonial-wall\_\_company { /\* Company name \*/ }  
.wp-testimonial-wall\_\_logo { /\* Company logo \*/ }

### **Layout-Specific Classes**

.wp-testimonial-wall--grid { /\* Grid layout \*/ }  
.wp-testimonial-wall--slider { /\* Slider layout \*/ }  
.wp-testimonial-wall--masonry { /\* Masonry layout \*/ }  
.wp-testimonial-wall--columns-3 { /\* 3-column layout \*/ }

## **⚙️ Configuration**

### **Plugin Settings**

Admin → Testimonial Walls → Settings

Cache Duration: 3600 seconds (default)  
Lazy Loading: Enabled (recommended)  
RTL Support: Enabled  
Structured Data: Enabled (SEO)

### **Performance Optimization**

* **Cache Duration**: Adjust based on update frequency  
* **Lazy Loading**: Keep enabled for better loading times  
* **Image Sizes**: Logos are optimal at 200x100px  
* **Testimonial Length**: Prefer short, concise texts

## **🔧 Development**

### **Hooks & Filters**

#### **Actions**

// After plugin initialization  
do\_action('wp\_testimonial\_walls\_init');

// After testimonial is saved  
do\_action('wp\_testimonial\_walls\_testimonial\_saved', $testimonial\_id);

// After wall is saved  
do\_action('wp\_testimonial\_walls\_wall\_saved', $wall\_id);

#### **Filters**

// Adjust testimonial output  
apply\_filters('wp\_testimonial\_walls\_testimonial\_content', $content, $testimonial);

// Adjust wall settings  
apply\_filters('wp\_testimonial\_walls\_wall\_settings', $settings, $wall\_id);

// Adjust CSS classes  
apply\_filters('wp\_testimonial\_walls\_css\_classes', $classes, $wall\_id);

### **Create Custom Layouts**

// Register layout  
add\_filter('wp\_testimonial\_walls\_layouts', function($layouts) {  
    $layouts\['custom'\] \= \_\_('Custom Layout', 'textdomain');  
    return $layouts;  
});

// Layout template  
add\_action('wp\_testimonial\_walls\_render\_layout\_custom', function($testimonials, $settings) {  
    // Custom layout logic here  
});

## **🌍 Multilingual**

### **Supported Languages**

* **German** (de\_DE) \- Fully translated  
* **English** (en\_US) \- Default

### **Custom Translations**

1. Use the .pot file from /languages/  
2. Translate with Poedit or a similar tool  
3. Save .po and .mo files in /languages/  
4. Format: wp-testimonial-walls-{locale}.po

## **🚨 Troubleshooting**

### **Common Problems**

#### **Testimonials are not displayed**

✓ Is the wall ID correct?  
✓ Are testimonials assigned to the wall?  
✓ Is the wall published?  
✓ Have you cleared the cache?

#### **Styling Issues**

✓ Check for theme conflicts  
✓ Increase CSS specificity  
✓ Clear browser cache  
✓ Is plugin CSS enabled?

#### **Performance Issues**

✓ Check cache settings  
✓ Is lazy loading enabled?  
✓ Are image sizes optimized?  
✓ Reduce the number of testimonials?

### **Debug Mode**

// In wp-config.php  
define('WP\_DEBUG', true);  
define('WP\_DEBUG\_LOG', true);

// Plugin-specific debugging  
define('WP\_TESTIMONIAL\_WALLS\_DEBUG', true);

## **📊 Maintenance**

### **Manage Cache**

Admin → Testimonial Walls → Settings → Maintenance  
\- Clear Cache: Remove all cached data  
\- Clean Database: Remove orphaned relationships

### **Backup Recommendations**

* **Database**: Regular backups of the WordPress database  
* **Uploads**: Backup the /wp-content/uploads/ folder for logos  
* **Plugin Settings**: Export via WordPress tools

## **🔒 Security**

### **Best Practices**

* **Permissions**: Only trusted users can manage testimonials  
* **Input Validation**: All inputs are sanitized and validated  
* **Nonces**: Protection against CSRF attacks  
* **Capability Checks**: Permission checks for all actions

### **GDPR Compliance**

* **No External Requests**: All assets are loaded locally  
* **No Tracking Cookies**: The plugin does not set any cookies  
* **Data Minimization**: Only necessary data is stored  
* **Deletion**: Full removal upon plugin uninstallation

## **📈 Updates**

### **Automatic Updates**

* The plugin supports WordPress auto-updates  
* Database migrations are performed automatically  
* Settings are retained during updates

### **Changelog**

See [CHANGELOG.md](http://docs.google.com/CHANGELOG.md) for a detailed version history.

## **🤝 Support**

### **Community Support**

* **GitHub Issues**: [Repository Issues](https://github.com/psart-scs/WP-Testimonial-Walls/issues)  
* **WordPress Forum**: Plugin support forum  
* **Documentation**: Full docs on GitHub

### **Contributing**

1. Fork the repository  
2. Create a feature branch (git checkout \-b feature/AmazingFeature)  
3. Commit your changes (git commit \-m 'Add AmazingFeature')  
4. Push the branch (git push origin feature/AmazingFeature)  
5. Open a Pull Request

## **📄 License**

This plugin is licensed under the GPL v2 or later. See [LICENSE](https://www.google.com/search?q=LICENSE) for details.

## **👨‍💻 Author**

**psart-scs**

* GitHub: [@psart-scs](https://github.com/psart-scs)  
* Website: [psart-scs.com](https://psart-scs.com)

## **🙏 Acknowledgments**

* WordPress community for the excellent documentation  
* All beta testers and feedback providers  
* Open-source libraries and tools

**Made with ❤️ for the WordPress Community**
