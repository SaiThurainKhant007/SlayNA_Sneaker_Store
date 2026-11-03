"""Generate docs/SlayNa_Customer_Sitemap.mdj (StarUML 6 Flowchart) mapping the customer-facing pages in routes/web.php.

Usage: python docs/make_customer_sitemap.py docs/SlayNa_Customer_Sitemap.mdj
"""
import sys

from flowchart_mdj import build

# key: (shape, text, column, row); sections are rounded boxes, pages are plain boxes indented under their parent
NODES = {
    "home": ("Process", "Home ( / )", 1.5, 0),

    "public": ("AlternateProcess", "Public pages", 0, 1.2),
    "shop": ("Process", "Shop / search ( /products )", 0.6, 2.2),
    "product": ("Process", "Product details & reviews ( /products/{id} )", 1.05, 3.2),
    "new": ("Process", "New Arrivals ( /new-arrivals )", 0.6, 4.2),
    "brands": ("Process", "Brands ( /brands )", 0.6, 5.2),
    "about": ("Process", "About Us ( /about )", 0.6, 6.2),
    "contact": ("Process", "Contact ( /contact )", 0.6, 7.2),

    "guest": ("AlternateProcess", "Guest only", 1.5, 1.2),
    "login": ("Process", "Login ( /login )", 2.1, 2.2),
    "register": ("Process", "Register ( /register )", 2.1, 3.2),

    "customer": ("AlternateProcess", "Customer only (signed in)", 3, 1.2),
    "cart": ("Process", "Cart ( /cart )", 3.6, 2.2),
    "checkout": ("Process", "Checkout ( /checkout )", 4.05, 3.2),
    "confirm": ("Process", "Order confirmation ( /orders/{id}/confirmation )", 4.5, 4.2),
    "account": ("Process", "My Account ( /account )", 3.6, 5.2),
    "orders": ("Process", "My Orders ( /account/orders )", 3.6, 6.2),
    "order": ("Process", "Order details, track & cancel ( /account/orders/{id} )", 4.1, 7.2),
    "cards": ("Process", "Saved Cards ( /account/saved-cards )", 3.6, 8.2),
    "profile": ("Process", "My Profile & password ( /profile )", 3.6, 9.2),
}
WIDE = {"product": (220, 60), "confirm": (240, 60), "order": (240, 60)}


def section(key):
    """Home to a section header, along a bus line just above the sections."""
    if NODES[key][2] == NODES["home"][2]:
        return ("home", "B", key, "T", "", [])
    return ("home", "B", key, "T", "", [("home", 0.6), (key, 0.6)])


def child(parent, key):
    """Parent down its centre line, then right into the indented child page."""
    return (parent, "B", key, "L", "", [(parent, NODES[key][3])])


# (source, source side, target, target side, label, bend points between them)
FLOWS = [
    section("public"), section("guest"), section("customer"),

    child("public", "shop"), child("shop", "product"), child("public", "new"),
    ("new", "R", "product", "B", "", [("product", 4.2)]),
    child("public", "brands"), child("public", "about"), child("public", "contact"),

    child("guest", "login"), child("guest", "register"),

    child("customer", "cart"), child("cart", "checkout"), child("checkout", "confirm"),
    child("customer", "account"), child("customer", "orders"), child("orders", "order"),
    child("customer", "cards"), child("customer", "profile"),
]


build(sys.argv[1], "Customer Site Map", "Customer Site Map", NODES, FLOWS, WIDE)
