"""Generate docs/SlayNa_Customer_Flowchart.mdj (StarUML 6 Flowchart) for the customer journey in routes/web.php.

Usage: python docs/make_customer_flowchart.py docs/SlayNa_Customer_Flowchart.mdj
"""
import sys

from flowchart_mdj import build

# key: (shape, text, column, row)
NODES = {
    "start": ("Terminator", "Start", 0, 0),
    "home": ("Process", "Visit SlayNa home page", 0, 1),
    "browse": ("Process", "Browse / search products", 0, 2),
    "detail": ("Process", "View product details", 0, 3),
    "signed": ("Decision", "Signed in?", 0, 4),
    "hasacc": ("Decision", "Has an account?", 1, 4),
    "register": ("Data", "Register (name, email, password)", 2, 4),
    "login": ("Data", "Log in (email & password)", 1, 5),
    "valid": ("Decision", "Valid & account active?", 1, 6),
    "loginerr": ("Process", "Show login error", 1, 7),
    "size": ("Data", "Select size & quantity", 0, 6),
    "add": ("Process", "Add to cart", 0, 7),
    "stock": ("Decision", "Enough stock?", 0, 8),
    "stockerr": ("Process", "Show 'not enough stock'", -1, 8),
    "cart": ("Process", "View / update / remove cart items", 0, 9),
    "more": ("Decision", "Continue shopping?", 0, 10),
    "checkout": ("Process", "Proceed to checkout", 0, 11),
    "ship": ("Data", "Enter shipping address & payment method", 0, 12),
    "wallet": ("Decision", "Mobile wallet?", 0, 13),
    "upload": ("Data", "Upload transfer screenshot", -1, 13),
    "recheck": ("Decision", "Stock still available?", 0, 14),
    "recheckerr": ("Process", "Show insufficient stock error", 1, 14),
    "place": ("Process", "Create order & payment (pending), reduce stock, clear cart", 0, 15),
    "confirm": ("Process", "Show order confirmation", 0, 16),
    "track": ("Process", "Track order in My Orders", 0, 17),
    "cancelq": ("Decision", "Cancel order?", 0, 18),
    "pending": ("Decision", "Order still pending?", 1, 18),
    "cancel": ("Process", "Cancel order & restock items", 2, 18),
    "done": ("Decision", "Order completed?", 0, 19),
    "wait": ("Process", "Wait for staff to process", -1, 19),
    "review": ("Data", "Write review (rating & comment)", 0, 20),
    "logout": ("Process", "Log out", 0, 21),
    "end": ("Terminator", "End", 0, 22),
}
WIDE = {"place": (240, 60), "cart": (240, 50), "ship": (260, 50)}

# (source, source side, target, target side, label, bend points between them)
FLOWS = [
    ("start", "B", "home", "T", "", []),
    ("home", "B", "browse", "T", "", []),
    ("browse", "B", "detail", "T", "", []),
    ("detail", "B", "signed", "T", "", []),
    ("signed", "B", "size", "T", "Yes", []),
    ("signed", "R", "hasacc", "L", "No", []),
    ("hasacc", "R", "register", "L", "No", []),
    ("hasacc", "B", "login", "T", "Yes", []),
    ("register", "B", "login", "R", "", [("register", 5)]),
    ("login", "B", "valid", "T", "", []),
    ("valid", "L", "size", "R", "Yes", []),
    ("valid", "B", "loginerr", "T", "No", []),
    ("loginerr", "R", "login", "R", "", [(950, 7), (950, 5)]),
    ("size", "B", "add", "T", "", []),
    ("add", "B", "stock", "T", "", []),
    ("stock", "L", "stockerr", "R", "No", []),
    ("stockerr", "T", "size", "L", "", [("stockerr", 6)]),
    ("stock", "B", "cart", "T", "Yes", []),
    ("cart", "B", "more", "T", "", []),
    ("more", "L", "browse", "L", "Yes", [(40, 10), (40, 2)]),
    ("more", "B", "checkout", "T", "No", []),
    ("checkout", "B", "ship", "T", "", []),
    ("ship", "B", "wallet", "T", "", []),
    ("wallet", "L", "upload", "R", "Yes", []),
    ("upload", "B", "recheck", "L", "", [("upload", 14)]),
    ("wallet", "B", "recheck", "T", "No (COD / saved card)", []),
    ("recheck", "R", "recheckerr", "L", "No", []),
    ("recheckerr", "T", "cart", "R", "", [("recheckerr", 9)]),
    ("recheck", "B", "place", "T", "Yes", []),
    ("place", "B", "confirm", "T", "", []),
    ("confirm", "B", "track", "T", "", []),
    ("track", "B", "cancelq", "T", "", []),
    ("cancelq", "R", "pending", "L", "Yes", []),
    ("pending", "R", "cancel", "L", "Yes", []),
    ("pending", "B", "done", "R", "No", [("pending", 19)]),
    ("cancelq", "B", "done", "T", "No", []),
    ("cancel", "B", "logout", "R", "", [("cancel", 21)]),
    ("done", "L", "wait", "R", "No", []),
    ("wait", "T", "track", "L", "", [("wait", 17)]),
    ("done", "B", "review", "T", "Yes", []),
    ("review", "B", "logout", "T", "", []),
    ("logout", "B", "end", "T", "", []),
]


build(sys.argv[1], "Customer Flow", "Customer Flow Chart", NODES, FLOWS, WIDE)
