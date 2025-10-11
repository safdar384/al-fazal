# Local Vendor Assets

All CDN dependencies have been downloaded locally to improve performance and enable offline development.

## Downloaded Assets

### Location: `public/vendor/`

```
public/vendor/
├── bootstrap-icons/         (400KB)
│   ├── bootstrap-icons.min.css
│   └── fonts/
│       ├── bootstrap-icons.woff2
│       └── bootstrap-icons.woff
├── datatables/              (104KB)
│   ├── dataTables.bootstrap5.min.css
│   ├── jquery.dataTables.min.js
│   └── dataTables.bootstrap5.min.js
├── fonts/                   (4KB)
│   └── source-sans-3.css
├── jquery/                  (88KB)
│   └── jquery-3.6.0.min.js
├── overlayscrollbars/       (16KB)
│   └── overlayscrollbars.min.css
├── select2/                 (88KB)
│   ├── select2.min.css
│   └── select2.min.js
└── sweetalert2/             (160KB)
    └── sweetalert2.all.min.js
```

**Total Size: ~860KB**

## Benefits

✅ **Faster Load Times** - Assets served from your server  
✅ **Offline Development** - No internet required  
✅ **No CDN Dependency** - Avoid CDN outages  
✅ **Better Control** - Version locked in your codebase  
✅ **Security** - No external dependencies  

## Previously Used CDNs (Now Local)

| Library | Previous CDN | Now Local Path |
|---------|-------------|----------------|
| jQuery 3.6.0 | code.jquery.com | `vendor/jquery/` |
| Bootstrap Icons 1.13.1 | cdn.jsdelivr.net | `vendor/bootstrap-icons/` |
| Select2 4.1.0-rc.0 | cdn.jsdelivr.net | `vendor/select2/` |
| DataTables 1.13.6 | cdn.datatables.net | `vendor/datatables/` |
| SweetAlert2 v11 | cdn.jsdelivr.net | `vendor/sweetalert2/` |
| OverlayScrollbars 2.11.0 | cdn.jsdelivr.net | `vendor/overlayscrollbars/` |
| Source Sans 3 | cdn.jsdelivr.net | `vendor/fonts/` |

## Updated Files

- ✅ `resources/views/layout/header.blade.php` - All asset paths updated to use local files

## Maintenance

To update any library:
1. Download the new version to the appropriate `public/vendor/` directory
2. Update the filename in `header.blade.php` if version number changed
3. Clear cache: `php artisan view:clear && php artisan cache:clear`

## Version Control

All vendor assets are stored in `public/vendor/` and should be committed to your repository.

---

**Last Updated:** October 12, 2025  
**Migrated From:** CDN (jsdelivr, datatables.net, jquery.com)  
**Migrated To:** Local assets in `public/vendor/`

