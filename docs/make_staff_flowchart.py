"""Generate docs/SlayNa_Staff_Flowchart.mdj (StarUML 6 Flowchart) for the staff routes in routes/web.php.

Usage: python docs/make_staff_flowchart.py docs/SlayNa_Staff_Flowchart.mdj
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
    "dash": ("Process", "View staff dashboard (pending orders, payments, low stock)", 0, 4),

    "payments": ("Decision", "Verify payments?", 0, 5),
    "proof": ("Process", "Check transfer screenshot & transaction ref", 1, 5),
    "payok": ("Decision", "Payment received?", 2, 5),
    "confirm": ("Process", "Confirm payment (pending order becomes confirmed)", 3, 5),
    "reject": ("Process", "Reject payment", 2, 6),

    "orders": ("Decision", "Process orders?", 0, 7),
    "openorder": ("Process", "Open order & check items", 1, 7),
    "cancelq": ("Decision", "Cancel order?", 2, 7),
    "advance": ("Process", "Set next status: processing, shipped, completed", 3, 7),
    "cancel": ("Process", "Cancel order & restock items", 2, 8),

    "inventory": ("Decision", "Update inventory?", 0, 9),
    "lowstock": ("Process", "Filter low-stock sizes", 1, 9),
    "qty": ("Data", "Enter new stock quantity", 2, 9),
    "savestock": ("Process", "Save stock", 3, 9),

    "logout": ("Process", "Log out", 0, 11),
    "end": ("Terminator", "End", 0, 12),
}
WIDE = {"dash": (260, 60), "proof": (250, 50), "confirm": (260, 60), "advance": (260, 50)}


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
    ("dash", "B", "payments", "T", "", []),

    ("payments", "R", "proof", "L", "Yes", []),
    ("proof", "R", "payok", "L", "", []),
    ("payok", "R", "confirm", "L", "Yes", []),
    ("payok", "B", "reject", "T", "No", []),
    back("confirm", "R", 5),
    back("reject", "R", 6),
    ("payments", "B", "orders", "T", "No", []),

    ("orders", "R", "openorder", "L", "Yes", []),
    ("openorder", "R", "cancelq", "L", "", []),
    ("cancelq", "R", "advance", "L", "No", []),
    ("cancelq", "B", "cancel", "T", "Yes", []),
    back("advance", "R", 7),
    back("cancel", "R", 8),
    ("orders", "B", "inventory", "T", "No", []),

    ("inventory", "R", "lowstock", "L", "Yes", []),
    ("lowstock", "R", "qty", "L", "", []),
    ("qty", "R", "savestock", "L", "", []),
    back("savestock", "R", 9),
    ("inventory", "B", "logout", "T", "No", []),
    ("logout", "B", "end", "T", "", []),
]


build(sys.argv[1], "Staff Flow", "Staff Flow Chart", NODES, FLOWS, WIDE)
