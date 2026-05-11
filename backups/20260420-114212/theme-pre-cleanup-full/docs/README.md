# PowerUp Industrial WordPress Theme

![PowerUp Industrial Theme](screenshot.png)

**Version:** 2.0.0  
**Author:** PowerUp Team  
**Requires WordPress:** 5.8+  
**Requires PHP:** 7.4+  
**Tested up to:** WordPress 6.6  
**License:** GPL v2 or later  
**License URI:** https://www.gnu.org/licenses/gpl-2.0.html  

## Overview

PowerUp Industrial is a modern, responsive WordPress theme designed for industrial and e-commerce websites. Built with performance, security, and accessibility in mind, it provides a solid foundation for building professional online stores and business websites.

## Features

### 🚀 Performance Optimized
- Modular code architecture for fast loading
- Lazy loading for images and resources
- Critical CSS inlining
- Minified and concatenated assets
- Cache-friendly structure

### 🔒 Security First
- Input validation and output escaping
- Nonce verification for all forms
- Security headers implementation
- File upload restrictions
- SQL injection prevention

### ♿ Accessibility Ready
- WCAG 2.1 AA compliant
- Keyboard navigation support
- Screen reader optimized
- ARIA labels and landmarks
- Color contrast compliance

### 📱 Fully Responsive
- Mobile-first design approach
- Responsive breakpoints
- Touch-friendly interfaces
- Adaptive images with srcset
- Flexible grid system

### 🛒 WooCommerce Integrated
- Custom product templates
- Tier pricing functionality
- Product video support
- Marketplace integration
- Enhanced cart functionality

### 🎨 Customization Options
- WordPress Customizer integration
- Color and typography controls
- Layout options
- Header and footer settings
- Social media integration

## Quick Start

### Installation

1. **Upload via WordPress Admin**
   - Download the theme zip file
   - Go to Appearance → Themes → Add New → Upload Theme
   - Select the zip file and click Install Now
   - Activate the theme

2. **Manual Installation**
   - Extract the theme zip file
   - Upload the `powerup-industrial` folder to `/wp-content/themes/`
   - Go to Appearance → Themes and activate PowerUp Industrial

3. **Using Git**
   ```bash
   cd wp-content/themes/
   git clone https://github.com/your-repo/powerup-industrial.git
   ```

### Basic Configuration

1. **Set up Navigation Menus**
   - Go to Appearance → Menus
   - Create and assign Primary, Footer, and Mobile menus

2. **Configure Theme Options**
   - Go to Appearance → Customize
   - Configure colors, typography, and layout settings

3. **Set up WooCommerce** (if needed)
   - Install and activate WooCommerce plugin
   - Configure shop settings
   - Import demo products (optional)

## File Structure

```
powerup-industrial/
├── assets/              # Theme assets
│   ├── css/            # Stylesheets
│   ├── js/             # JavaScript files
│   └── images/         # Theme images
├── config/             # Configuration files
│   ├── constants.php   # Theme constants
│   ├── settings.php    # Theme settings
│   ├── hooks.php       # Action/filter hooks
│   └── assets.php      # Assets configuration
├── docs/               # Documentation
│   ├── README.md       # This file
│   ├── INSTALLATION.md # Installation guide
│   ├── DEVELOPMENT.md  # Development guide
│   ├── HOOKS.md        # Available hooks
│   └── CHANGELOG.md    # Version history
├── inc/                # Theme modules
│   ├── setup.php       # Theme setup
│   ├── assets.php      # Assets management
│   ├── navigation.php  # Navigation functions
│   ├── woocommerce.php # WooCommerce integration
│   ├── security.php    # Security functions
│   ├── accessibility.php # Accessibility features
│   ├── utilities.php   # Utility functions
│   ├── customizer.php  # Customizer settings
│   └── template-tags.php # Template tags
├── languages/          # Translation files
├── template-parts/     # Template partials
├── woocommerce/        # WooCommerce templates
├── index.php          # Main template
├── style.css          # Main stylesheet
├── functions.php      # Theme functions (entry point)
└── screenshot.png     # Theme screenshot
```

## Theme Modules

The theme follows a modular architecture with the following modules:

1. **Setup** (`inc/setup.php`) - Theme initialization and configuration
2. **Assets** (`inc/assets.php`) - Stylesheet and script management
3. **Navigation** (`inc/navigation.php`) - Menu and navigation functions
4. **WooCommerce** (`inc/woocommerce.php`) - E-commerce integration
5. **Security** (`inc/security.php`) - Security features and functions
6. **Accessibility** (`inc/accessibility.php`) - Accessibility enhancements
7. **Utilities** (`inc/utilities.php`) - Helper functions and utilities
8. **Customizer** (`inc/customizer.php`) - Theme customization options
9. **Template Tags** (`inc/template-tags.php`) - Template helper functions

## Configuration Files

The theme uses a centralized configuration system:

- **Constants** (`config/constants.php`) - Define theme constants and settings
- **Settings** (`config/settings.php`) - Configure theme behavior and options
- **Hooks** (`config/hooks.php`) - Register all action and filter hooks
- **Assets** (`config/assets.php`) - Manage asset loading and optimization

## Development

### Prerequisites

- WordPress 5.8+
- PHP 7.4+
- Composer (for development)
- Node.js (for asset building)
- Git

### Development Setup

1. Clone the repository:
   ```bash
   git clone https://github.com/your-repo/powerup-industrial.git
   cd powerup-industrial
   ```

2. Install PHP dependencies:
   ```bash
   composer install
   ```

3. Install Node.js dependencies:
   ```bash
   npm install
   ```

4. Build assets:
   ```bash
   npm run build
   ```

### Coding Standards

- Follow WordPress Coding Standards
- Use PHPDoc comments for all functions
- Prefix all functions with `powerup_`
- Use descriptive variable names
- Include error handling and validation

### Testing

Run the following tests before deployment:

1. **PHP Syntax Check:**
   ```bash
   php -l *.php
   find . -name "*.php" -exec php -l {} \;
   ```

2. **Code Standards:**
   ```bash
   composer check-cs
   ```

3. **Security Scan:**
   ```bash
   wp plugin install wpscan --activate
   wp wpscan --url=your-site.com
   ```

4. **Performance Test:**
   - Use Google PageSpeed Insights
   - Run Lighthouse audit
   - Test on WebPageTest

## Customization

### Child Theme

We recommend using a child theme for customizations:

```php
// functions.php in child theme
add_action('wp_enqueue_scripts', 'powerup_child_enqueue_styles');
function powerup_child_enqueue_styles() {
    wp_enqueue_style('parent-style', get_template_directory_uri() . '/style.css');
    wp_enqueue_style('child-style', get_stylesheet_directory_uri() . '/style.css');
}
```

### Hooks and Filters

The theme provides numerous hooks for customization:

- **Action Hooks:** Modify theme behavior
- **Filter Hooks:** Customize output and data
- **Template Hooks:** Extend template functionality

See [HOOKS.md](HOOKS.md) for complete hook documentation.

### CSS Customization

Use the WordPress Customizer or add custom CSS:

```css
/* Custom CSS in Customizer */
:root {
    --color-primary: #ff6200;
    --color-secondary: #333333;
}
```

## Performance Optimization

### Enabled by Default

- Asset minification and concatenation
- Lazy loading for images
- Critical CSS inlining
- Cache busting
- Preloaded resources

### Additional Optimization

1. **Enable Caching:**
   ```php
   // In wp-config.php
   define('WP_CACHE', true);
   ```

2. **Use CDN:**
   ```php
   // In config/assets.php
   'cdn' => array(
       'enable' => true,
       'url' => 'https://cdn.your-site.com',
   ),
   ```

3. **Optimize Images:**
   - Use WebP format
   - Implement responsive images
   - Compress images before upload

## Security Features

### Built-in Security

- Input validation and sanitization
- Output escaping
- Nonce verification
- Security headers
- File upload restrictions
- Login attempt limiting

### Additional Security Measures

1. **Update Regularly:**
   - Keep WordPress, themes, and plugins updated
   - Monitor security advisories

2. **Use SSL:**
   ```bash
   # Force SSL in .htaccess
   RewriteEngine On
   RewriteCond %{HTTPS} off
   RewriteRule ^(.*)$ https://%{HTTP_HOST}/$1 [R=301,L]
   ```

3. **Security Plugins:**
   - Wordfence Security
   - Sucuri Security
   - iThemes Security

## Accessibility Compliance

### WCAG 2.1 AA Compliance

- **Perceivable:** Text alternatives, captions, adaptable content
- **Operable:** Keyboard accessible, enough time, navigable
- **Understandable:** Readable, predictable, input assistance
- **Robust:** Compatible with assistive technologies

### Testing Accessibility

1. **Automated Testing:**
   - WAVE Evaluation Tool
   - axe DevTools
   - Lighthouse Accessibility Audit

2. **Manual Testing:**
   - Keyboard navigation
   - Screen reader testing
   - Color contrast verification
   - Focus management

## Support

### Documentation
- [Installation Guide](INSTALLATION.md)
- [Development Guide](DEVELOPMENT.md)
- [Hooks Reference](HOOKS.md)
- [Changelog](CHANGELOG.md)

### Resources
- [WordPress Theme Handbook](https://developer.wordpress.org/themes/)
- [WooCommerce Documentation](https://docs.woocommerce.com/)
- [WCAG 2.1 Guidelines](https://www.w3.org/TR/WCAG21/)
- [OWASP Security Guidelines](https://owasp.org/www-project-top-ten/)

### Support Channels
- GitHub Issues: Bug reports and feature requests
- Email Support: support@powerup.com
- Community Forum: forum.powerup.com

## Contributing

We welcome contributions! Please see our [Contributing Guidelines](CONTRIBUTING.md) for details.

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Submit a pull request

## License

This theme is licensed under the GPL v2 or later.

```text
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
```

## Credits

- Built by the PowerUp Team
- Inspired by modern industrial design principles
- Tested by the WordPress community
- Special thanks to all contributors

---

**PowerUp Industrial** - Powering your industrial e-commerce success since 2023.