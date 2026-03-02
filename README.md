# OpenTT Smart Redirect

OpenTT Smart Redirect is a lightweight WordPress plugin that redirects all non-admin users to a selected page.  
It is designed for simple maintenance mode, temporary site lockdowns, or controlled access scenarios.

The plugin is intentionally minimal and does one thing only: redirect visitors while allowing administrators to keep working normally.

---

## Features

- Redirect all non-admin users to a selected page
- Administrators are never redirected
- Optional support for allowing subpages of the target page
- Automatically creates a default `/maintenance` page on activation
- No external dependencies
- No front-end UI or styling assumptions

---

## Use Cases

- Website maintenance mode
- Temporary site closure
- Event-based access control
- Content migration or restructuring phases

---

## Installation

1. Download the latest release from GitHub  
2. Upload the plugin folder to `wp-content/plugins/`
3. Activate **OpenTT Smart Redirect** from the WordPress admin panel
4. Go to **OpenTT Smart Redirect** in the admin menu
5. Enable redirect mode and select a target page

---

## How It Works

When redirect mode is enabled:

- Logged-in administrators (`manage_options`) can access the entire site normally
- All other users are redirected to the selected page
- If enabled, subpages of the selected page are allowed
- All logic runs through `template_redirect` with minimal overhead

---

## Philosophy

This plugin is intentionally simple.

It does not try to replace full maintenance-mode solutions, introduce visual overlays, or add unnecessary complexity.  
It exists to solve a very specific problem in a predictable and transparent way.

---

## License

AGPL-3.0  
This ensures that improvements and hosted modifications remain free and accessible to the community.

---

## Changelog

### v1.4
- Added role-based bypass control for redirect mode
- Administrators are always allowed to access the site
- Other roles can optionally be allowed via admin settings

### v1.3
- Initial public release
- Basic redirect mode with admin bypass
- Automatic maintenance page creation

---
## Author

Developed by **Aleksa Dimitrijević**  
Part of the OpenTT project ecosystem.