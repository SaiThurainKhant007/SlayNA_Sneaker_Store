"""Generate docs/SlayNa_Admin_Sitemap.mdj (StarUML 6 Flowchart) mapping the admin panel pages in routes/web.php.

Usage: python docs/make_admin_sitemap.py docs/SlayNa_Admin_Sitemap.mdj
"""
import sys

from flowchart_mdj import build

# key: (shape, text, column, row); sections follow the sidebar groups in layouts/admin.blade.php
NODES = {
    "login": ("Process", "Login ( /login )", 3, 0),
    "dash": ("Process", "Admin Dashboard ( /admin/dashboard )", 3, 1),

    "main": ("AlternateProcess", "Main", 0, 2.2),
    "products": ("Process", "Products ( /admin/products )", 0.6, 3.2),
    "create": ("Process", "Add product ( /admin/products/create )", 1.05, 4.2),
    "edit": ("Process", "Edit product ( /admin/products/{id}/edit )", 1.05, 5.2),
    "categories": ("Process", "Categories ( /admin/categories )", 0.6, 6.2),
    "brands": ("Process", "Brands & Logos ( /admin/brands )", 0.6, 7.2),

    "marketing": ("AlternateProcess", "Marketing", 1.5, 2.2),
    "promotions": ("Process", "Promotions ( /admin/promotions )", 2.1, 3.2),
    "gallery": ("Process", "Model Gallery ( /admin/gallery )", 2.1, 4.2),

    "people": ("AlternateProcess", "People", 3, 2.2),
    "staff": ("Process", "Staff & Customers ( /admin/staff )", 3.6, 3.2),

    "insights": ("AlternateProcess", "Insights", 4.5, 2.2),
    "reports": ("Process", "Reports ( /admin/reports )", 5.1, 3.2),
    "sales": ("Process", "Sales tab ( ?type=sales )", 5.55, 4.2),
    "inventory": ("Process", "Inventory tab ( ?type=inventory )", 5.55, 5.2),
    "customers": ("Process", "Customers tab ( ?type=customers )", 5.55, 6.2),
    "csv": ("Process", "CSV export ( /admin/reports/{report}.csv )", 5.55, 7.2),

    "system": ("AlternateProcess", "System", 6, 2.2),
    "profile": ("Process", "My Profile ( /profile )", 6.6, 3.2),
    "store": ("Process", "View Store ( / )", 6.6, 4.2),
    "logout": ("Process", "Log Out", 6.6, 5.2),
}
WIDE = {"dash": (260, 50), "create": (220, 60), "edit": (220, 60), "csv": (220, 60)}


def section(key):
    """Dashboard to a section header, along a bus line just above the sections."""
    if NODES[key][2] == NODES["dash"][2]:
        return ("dash", "B", key, "T", "", [])
    return ("dash", "B", key, "T", "", [("dash", 1.6), (key, 1.6)])


def child(parent, key):
    """Parent down its centre line, then right into the indented child page."""
    return (parent, "B", key, "L", "", [(parent, NODES[key][3])])


# (source, source side, target, target side, label, bend points between them)
FLOWS = [
    ("login", "B", "dash", "T", "", []),
    section("main"), section("marketing"), section("people"), section("insights"), section("system"),

    child("main", "products"), child("products", "create"), child("products", "edit"),
    child("main", "categories"), child("main", "brands"),

    child("marketing", "promotions"), child("marketing", "gallery"),

    child("people", "staff"),

    child("insights", "reports"), child("reports", "sales"), child("reports", "inventory"),
    child("reports", "customers"), child("reports", "csv"),

    child("system", "profile"), child("system", "store"), child("system", "logout"),
]


build(sys.argv[1], "Admin Site Map", "Admin Site Map", NODES, FLOWS, WIDE)
