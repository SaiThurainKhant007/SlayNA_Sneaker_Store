"""Generate docs/SlayNa_Staff_Sitemap.mdj (StarUML 6 Flowchart) mapping the staff panel pages in routes/web.php.

Usage: python docs/make_staff_sitemap.py docs/SlayNa_Staff_Sitemap.mdj
"""
import sys

from flowchart_mdj import build

# key: (shape, text, column, row); "Work" is the sidebar menu in layouts/staff.blade.php, "Account" its user menu
NODES = {
    "login": ("Process", "Login ( /login )", 1, 0),
    "dash": ("Process", "Staff Dashboard ( /staff/dashboard )", 1, 1),

    "work": ("AlternateProcess", "Work", 0, 2.2),
    "orders": ("Process", "Orders ( /staff/orders )", 0.6, 3.2),
    "status": ("Process", "Filter by status ( ?status=pending … )", 1.05, 4.2),
    "payments": ("Process", "Payments ( /staff/payments )", 0.6, 5.2),
    "inventory": ("Process", "Inventory ( /staff/inventory )", 0.6, 6.2),
    "low": ("Process", "Low stock only ( ?low=1 )", 1.05, 7.2),

    "account": ("AlternateProcess", "Account", 2, 2.2),
    "profile": ("Process", "My Profile ( /profile )", 2.6, 3.2),
    "store": ("Process", "View Store ( / )", 2.6, 4.2),
    "logout": ("Process", "Log Out", 2.6, 5.2),
}
WIDE = {"dash": (260, 50), "status": (220, 60)}


def section(key):
    """Dashboard to a section header, along a bus line just above the sections."""
    return ("dash", "B", key, "T", "", [("dash", 1.6), (key, 1.6)])


def child(parent, key):
    """Parent down its centre line, then right into the indented child page."""
    return (parent, "B", key, "L", "", [(parent, NODES[key][3])])


# (source, source side, target, target side, label, bend points between them)
FLOWS = [
    ("login", "B", "dash", "T", "", []),
    section("work"), section("account"),

    child("work", "orders"), child("orders", "status"), child("work", "payments"),
    child("work", "inventory"), child("inventory", "low"),

    child("account", "profile"), child("account", "store"), child("account", "logout"),
]


build(sys.argv[1], "Staff Site Map", "Staff Site Map", NODES, FLOWS, WIDE)
