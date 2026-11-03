"""Generate docs/SlayNa_Admin_Flowchart.mdj (StarUML 6 Flowchart) for the admin routes in routes/web.php.

Usage: python docs/make_admin_flowchart.py docs/SlayNa_Admin_Flowchart.mdj
"""
import sys

from flowchart_mdj import build

RX = 1580   # x of the shared "back to dashboard" line

# key: (shape, text, column, row)
NODES = {
    "start": ("Terminator", "Start", 0, 0),
    "loginpage": ("Process", "Open login page", 0, 1),
    "login": ("Data", "Enter email & password", 0, 2),
    "valid": ("Decision", "Valid & account active?", 0, 3),
    "loginerr": ("Process", "Show login error", -1, 3),
    "dash": ("Process", "View admin dashboard (sales, orders, low stock)", 0, 4),

    "products": ("Decision", "Manage products?", 0, 5),
    "prodform": ("Data", "Add / edit product: details, sizes & stock, photos", 1, 5),
    "prodvalid": ("Decision", "Valid input?", 2, 5),
    "prodsave": ("Process", "Save or discontinue product", 3, 5),
    "proderr": ("Process", "Show validation errors", 2, 6),

    "catalog": ("Decision", "Manage categories / brands?", 0, 7),
    "catform": ("Data", "Add / rename / delete category or brand", 1, 7),
    "inuse": ("Decision", "Deleting one that has products?", 2, 7),
    "catsave": ("Process", "Save changes", 3, 7),
    "caterr": ("Process", "Show 'cannot delete' error", 2, 8),

    "promos": ("Decision", "Manage promotions?", 0, 9),
    "promoform": ("Data", "Pick product, discount %, end date, images", 1, 9),
    "promosave": ("Process", "Save / activate / remove promotion", 2, 9),

    "gallery": ("Decision", "Manage gallery?", 0, 10),
    "galform": ("Data", "Upload photos, link product, set order", 1, 10),
    "galsave": ("Process", "Save gallery photos", 2, 10),

    "accounts": ("Decision", "Manage staff & admins?", 0, 11),
    "newacc": ("Decision", "Create new account?", 1, 11),
    "accform": ("Data", "Enter name, email, password, role", 2, 11),
    "accsave": ("Process", "Create staff / admin account", 3, 11),
    "ownacc": ("Decision", "Own admin account?", 1, 12),
    "keep": ("Process", "Leave unchanged (no self lock-out)", 2, 12),
    "toggle": ("Process", "Enable / disable account", 1, 13),

    "reports": ("Decision", "View reports?", 0, 14),
    "pick": ("Data", "Pick report: sales, top products, inventory, customers", 1, 14),
    "export": ("Decision", "Export CSV?", 2, 14),
    "csv": ("Process", "Download CSV", 3, 14),

    "logout": ("Process", "Log out", 0, 16),
    "end": ("Terminator", "End", 0, 17),
}
WIDE = {"dash": (260, 50), "prodform": (250, 60), "catform": (250, 50), "promoform": (250, 50),
        "pick": (250, 60), "keep": (240, 50)}


def back(src, side, row):
    """Return flow from a finished task to the dashboard along the shared line at RX."""
    return (src, side, "dash", "R", "", [(RX, row), (RX, 4)])


# (source, source side, target, target side, label, bend points between them)
FLOWS = [
    ("start", "B", "loginpage", "T", "", []),
    ("loginpage", "B", "login", "T", "", []),
    ("login", "B", "valid", "T", "", []),
    ("valid", "L", "loginerr", "R", "No", []),
    ("loginerr", "T", "login", "L", "", [("loginerr", 2)]),
    ("valid", "B", "dash", "T", "Yes", []),
    ("dash", "B", "products", "T", "", []),

    ("products", "R", "prodform", "L", "Yes", []),
    ("prodform", "R", "prodvalid", "L", "", []),
    ("prodvalid", "R", "prodsave", "L", "Yes", []),
    ("prodvalid", "B", "proderr", "T", "No", []),
    ("proderr", "L", "prodform", "B", "", [("prodform", 6)]),
    back("prodsave", "R", 5),
    ("products", "B", "catalog", "T", "No", []),

    ("catalog", "R", "catform", "L", "Yes", []),
    ("catform", "R", "inuse", "L", "", []),
    ("inuse", "R", "catsave", "L", "No", []),
    ("inuse", "B", "caterr", "T", "Yes", []),
    back("catsave", "R", 7),
    back("caterr", "R", 8),
    ("catalog", "B", "promos", "T", "No", []),

    ("promos", "R", "promoform", "L", "Yes", []),
    ("promoform", "R", "promosave", "L", "", []),
    back("promosave", "R", 9),
    ("promos", "B", "gallery", "T", "No", []),

    ("gallery", "R", "galform", "L", "Yes", []),
    ("galform", "R", "galsave", "L", "", []),
    back("galsave", "R", 10),
    ("gallery", "B", "accounts", "T", "No", []),

    ("accounts", "R", "newacc", "L", "Yes", []),
    ("newacc", "R", "accform", "L", "Yes", []),
    ("accform", "R", "accsave", "L", "", []),
    back("accsave", "R", 11),
    ("newacc", "B", "ownacc", "T", "No (toggle status)", []),
    ("ownacc", "R", "keep", "L", "Yes", []),
    back("keep", "R", 12),
    ("ownacc", "B", "toggle", "T", "No", []),
    back("toggle", "R", 13),
    ("accounts", "B", "reports", "T", "No", []),

    ("reports", "R", "pick", "L", "Yes", []),
    ("pick", "R", "export", "L", "", []),
    ("export", "R", "csv", "L", "Yes", []),
    back("csv", "R", 14),
    ("export", "B", "dash", "R", "No", [("export", 15), (RX, 15), (RX, 4)]),
    ("reports", "B", "logout", "T", "No", []),
    ("logout", "B", "end", "T", "", []),
]


build(sys.argv[1], "Admin Flow", "Admin Flow Chart", NODES, FLOWS, WIDE)
