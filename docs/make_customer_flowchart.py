"""Generate docs/SlayNa_Customer_Flowchart.mdj (StarUML 6 Flowchart) for the customer journey in routes/web.php.

Usage: python docs/make_customer_flowchart.py docs/SlayNa_Customer_Flowchart.mdj
"""
import base64, json, sys

_n = 0
def nid():
    global _n
    _n += 1
    return base64.b64encode(b"SLAYFC" + _n.to_bytes(6, "big")).decode()

def ref(o):
    return {"$ref": o["_id"]}

FONT = "Arial;13;0"
COL_X = {-1: 200, 0: 500, 1: 800, 2: 1100}   # column centre x
ROW_H = 100                                   # row centre y = 60 + row * ROW_H
SIZES = {"Terminator": (140, 40), "Process": (220, 50), "Data": (230, 50), "Decision": (200, 70)}

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


def box(key):
    shape, _, col, row = NODES[key]
    w, h = WIDE.get(key, SIZES[shape])
    cx, cy = COL_X[col], 60 + row * ROW_H
    return cx - w // 2, cy - h // 2, w, h


def anchor(key, side):
    x, y, w, h = box(key)
    return {"T": (x + w // 2, y), "B": (x + w // 2, y + h), "L": (x, y + h // 2), "R": (x + w, y + h // 2)}[side]


def bend(point):
    """A bend is (x, row) or (node key, row): x taken from that node's centre, y from the row centre."""
    x, row = point
    if isinstance(x, str):
        bx, _, bw, _ = box(x)
        x = bx + bw // 2
    return x, 60 + row * ROW_H


project = {"_type": "Project", "_id": nid(), "name": "SlayNa Sneaker Store", "ownedElements": []}
chart = {"_type": "FCFlowchart", "_id": nid(), "_parent": ref(project), "name": "Customer Flow", "ownedElements": []}
project["ownedElements"].append(chart)
diagram = {"_type": "FCFlowchartDiagram", "_id": nid(), "_parent": ref(chart), "name": "Customer Flow Chart",
           "defaultDiagram": True, "ownedViews": []}
chart["ownedElements"].append(diagram)

models, views = {}, {}
for key, (shape, text, _, _) in NODES.items():
    m = {"_type": "FC" + shape, "_id": nid(), "_parent": ref(chart), "name": text, "ownedElements": []}
    chart["ownedElements"].append(m)
    models[key] = m

    x, y, w, h = box(key)
    v = {"_type": f"FC{shape}View", "_id": nid(), "_parent": ref(diagram), "model": ref(m), "subViews": [],
         "font": FONT, "parentStyle": False, "left": x, "top": y, "width": w, "height": h, "wordWrap": True}
    name_l = {"_type": "LabelView", "_id": nid(), "_parent": ref(v), "font": FONT, "parentStyle": True,
              "left": x + 5, "top": y + h // 2 - 7, "width": w - 10, "height": 13, "text": text}
    v["subViews"].append(name_l)
    v["nameLabel"] = ref(name_l)
    diagram["ownedViews"].append(v)
    views[key] = v

for src, sside, dst, dside, name, bends in FLOWS:
    flow = {"_type": "FCFlow", "_id": nid(), "_parent": ref(models[src]),
            "source": ref(models[src]), "target": ref(models[dst])}
    if name:
        flow["name"] = name
    models[src]["ownedElements"].append(flow)

    pts = [anchor(src, sside)] + [bend(b) for b in bends] + [anchor(dst, dside)]
    v = {"_type": "FCFlowView", "_id": nid(), "_parent": ref(diagram), "model": ref(flow), "subViews": [],
         "font": FONT, "parentStyle": False, "head": ref(views[dst]), "tail": ref(views[src]), "lineStyle": 1,
         "points": ";".join(f"{px}:{py}" for px, py in pts)}
    name_l = {"_type": "EdgeLabelView", "_id": nid(), "_parent": ref(v), "model": ref(flow), "font": FONT,
              "parentStyle": False, "left": 0, "top": 0, "height": 13, "alpha": 0.5235987755982988,
              "distance": 15, "hostEdge": ref(v), "edgePosition": 2}
    if name:
        name_l["text"] = name
    else:
        name_l["visible"] = False
    v["subViews"].append(name_l)
    v["nameLabel"] = ref(name_l)
    diagram["ownedViews"].append(v)

out = sys.argv[1]
with open(out, "w", encoding="utf-8") as f:
    json.dump(project, f, indent="\t")
print(f"wrote {out}: {len(NODES)} nodes, {len(FLOWS)} flows")
