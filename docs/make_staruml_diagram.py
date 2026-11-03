"""Generate docs/SlayNa_Class_Diagram.mdj (StarUML 6) from the classes below; keep them in step with docs/class-diagram.md.

Usage: python docs/make_staruml_diagram.py docs/SlayNa_Class_Diagram.mdj
"""
import base64, json, os, sys

_n = 0
def nid():
    global _n
    _n += 1
    return base64.b64encode(b"SLAYNA" + _n.to_bytes(6, "big")).decode()

def ref(o):
    return {"$ref": o["_id"]}

FONT = "Arial;13;0"
ROW = 17          # attribute / operation line height
CHAR = 7.2        # approx width per character at Arial 13

# name: (attributes, operations, x, y); attributes "vis name: type", operations "vis name(params): ret"
CLASSES = {
    "Admin": (["+id: int", "+name: string", "+email: string", "-password: string", "+phone: string",
               "+status: enum", "+created_at: datetime"],
              ["+isAdmin(): bool", "+isActive(): bool"], 40, 40),
    "SavedCard": (["+id: int", "+customer_id: int", "+card_holder: string", "+card_last4: char",
                   "+card_type: string", "+expiry_month: int", "+expiry_year: int"], [], 360, 40),
    "Customer": (["+id: int", "+name: string", "+email: string", "-password: string", "+phone: string",
                  "+address: string", "+status: enum", "+created_at: datetime"],
                 ["+isCustomer(): bool", "+isActive(): bool"], 680, 40),
    "Review": (["+id: int", "+product_id: int", "+customer_id: int", "+order_id: int", "+rating: int",
                "+comment: text", "+created_at: datetime"], [], 1000, 40),
    "Brand": (["+id: int", "+name: string", "+logo_path: string", "+show_in_marquee: bool"],
              ["+logoUrl(): string"], 1320, 40),
    "Category": (["+id: int", "+name: string"], [], 1640, 40),

    "Staff": (["+id: int", "+name: string", "+email: string", "-password: string", "+phone: string",
               "+managed_by: int", "+position: string", "+hired_on: date", "+status: enum", "+created_at: datetime"],
              ["+isStaff(): bool", "+isActive(): bool"], 40, 400),
    "Order": (["+id: int", "+customer_id: int", "+order_date: datetime", "+shipping_address: string",
               "+total_amount: decimal", "+order_status: enum", "+processed_by: int"],
              ["+hasPromotion(): bool", "+promotionSavings(): float", "+canBeCancelledByCustomer(): bool",
               "+stepIndex(): int", "+cancel(processedBy: int): void", "+statusColor(): string"], 680, 400),
    "Cart": (["+id: int", "+customer_id: int", "+product_size_id: int", "+quantity: int", "+created_at: datetime"],
             ["+subtotal(): float"], 1000, 400),
    "Product": (["+id: int", "+name: string", "+description: text", "+price: decimal", "+brand_id: int",
                 "+category_id: int", "+image_path: string", "+low_stock_threshold: int", "+status: enum"],
                ["+effectivePrice(): float", "+totalStock(): int", "+averageRating(): float",
                 "+reviewCount(): int", "+galleryUrls(): array"], 1320, 400),
    "Promotion": (["+id: int", "+title: string", "+description: string", "+product_id: int",
                   "+discount_percent: int", "+image_path: string", "+hover_image_path: string",
                   "+ends_on: date", "+is_active: bool"],
                  ["+isLive(): bool", "+discountedPrice(): float", "+savings(): float"], 1000, 1100),

    "PaymentMethod": (["+id: int", "+code: string", "+name: string"], ["+idFor(code: string): int"], 40, 800),
    "Payment": (["+id: int", "+order_id: int", "+payment_method_id: int", "+proof_image: string",
                 "+transaction_ref: string", "+payment_status: enum", "+confirmed_by: int",
                 "+confirmed_at: datetime", "+created_at: datetime"], ["+methodLabel(): string"], 360, 800),
    "OrderItem": (["+id: int", "+order_id: int", "+product_size_id: int", "+quantity: int",
                   "+unit_price: decimal", "+original_price: decimal", "+discount_percent: int",
                   "+promotion_id: int", "+promotion_title: string"],
                  ["+subtotal(): float", "+isPromotional(): bool", "+savings(): float"], 680, 800),
    "ProductImage": (["+id: int", "+product_id: int", "+image_path: string", "+sort_order: int"],
                     ["+imageUrl(): string"], 1640, 400),
    "ProductSize": (["+id: int", "+product_id: int", "+size_id: int", "+stock_quantity: int"],
                    ["+isLowStock(): bool"], 1000, 800),
    "Size": (["+id: int", "+label: string"], [], 1320, 800),
    "GalleryImage": (["+id: int", "+image_path: string", "+title: string", "+subtitle: string",
                      "+product_id: int", "+sort_order: int", "+is_active: bool"], [], 1640, 800),
}
STATIC_OPS = {"idFor"}

# (tail class, tail multiplicity, head class, head multiplicity, label, composite at tail?)
ASSOCIATIONS = [
    ("Admin", "1", "Staff", "0..*", "manages", False),
    ("Customer", "1", "SavedCard", "0..*", "saves", True),
    ("Customer", "1", "Cart", "0..*", "adds to", True),
    ("Customer", "1", "Order", "0..*", "places", False),
    ("Customer", "1", "Review", "0..*", "writes", False),
    ("Staff", "0..1", "Order", "0..*", "processes", False),
    ("Staff", "0..1", "Payment", "0..*", "confirms", False),
    ("Order", "1", "OrderItem", "1..*", "contains", True),
    ("Order", "1", "Payment", "0..1", "paid by", True),
    ("Order", "1", "Review", "0..*", "verifies", False),
    ("PaymentMethod", "1", "Payment", "0..*", "used for", False),
    ("Brand", "1", "Product", "0..*", "makes", False),
    ("Category", "1", "Product", "0..*", "groups", False),
    ("Product", "1", "ProductSize", "0..*", "available in", True),
    ("Size", "1", "ProductSize", "0..*", "used by", False),
    ("Product", "1", "ProductImage", "0..*", "photos", True),
    ("Product", "1", "Promotion", "0..*", "discounts", False),
    ("Product", "0..1", "GalleryImage", "0..*", "featured in", False),
    ("Product", "1", "Review", "0..*", "rates", False),
    ("ProductSize", "1", "Cart", "0..*", "holds", False),
    ("ProductSize", "1", "OrderItem", "0..*", "orders", False),
    ("Promotion", "0..1", "OrderItem", "0..*", "applied", False),
]


def parse_member(text):
    vis = {"+": "public", "-": "private", "#": "protected", "~": "package"}[text[0]]
    body = text[1:]
    if "(" in body:
        name, rest = body.split("(", 1)
        params, ret = rest.split("):", 1)
        params = [p.split(":") for p in params.split(",") if p.strip()]
        return vis, name, [(p[0].strip(), p[1].strip()) for p in params], ret.strip()
    name, typ = body.split(":", 1)
    return vis, name.strip(), None, typ.strip()


def label(parent, text="", visible=True, bold=False, left=0, top=0, width=0, align=None):
    o = {"_type": "LabelView", "_id": nid(), "_parent": ref(parent), "font": "Arial;13;1" if bold else FONT,
         "parentStyle": True, "left": left, "top": top, "width": width, "height": 13}
    if not visible:
        o["visible"] = False
    if text:
        o["text"] = text
    if align is not None:
        o["horizontalAlignment"] = align
    return o


def compartment(kind, view, model, left, top, width, height):
    return {"_type": kind, "_id": nid(), "_parent": ref(view), "model": ref(model), "font": FONT,
            "parentStyle": True, "left": left, "top": top, "width": width, "height": height}


project = {"_type": "Project", "_id": nid(), "name": "SlayNa Sneaker Store", "ownedElements": []}
model = {"_type": "UMLModel", "_id": nid(), "_parent": ref(project), "name": "Model", "ownedElements": []}
project["ownedElements"].append(model)
diagram = {"_type": "UMLClassDiagram", "_id": nid(), "_parent": ref(model), "name": "Class Diagram",
           "defaultDiagram": True, "ownedViews": []}
model["ownedElements"].append(diagram)

classes, views, boxes = {}, {}, {}

for cname, (attrs, ops, x, y) in CLASSES.items():
    cls = {"_type": "UMLClass", "_id": nid(), "_parent": ref(model), "name": cname,
           "ownedElements": [], "attributes": [], "operations": []}
    for a in attrs:
        vis, name, _, typ = parse_member(a)
        cls["attributes"].append({"_type": "UMLAttribute", "_id": nid(), "_parent": ref(cls),
                                  "name": name, "visibility": vis, "type": typ})
    for o in ops:
        vis, name, params, ret = parse_member(o)
        op = {"_type": "UMLOperation", "_id": nid(), "_parent": ref(cls), "name": name, "visibility": vis,
              "parameters": []}
        if name in STATIC_OPS:
            op["isStatic"] = True
        for pname, ptype in params:
            op["parameters"].append({"_type": "UMLParameter", "_id": nid(), "_parent": ref(op),
                                     "name": pname, "type": ptype})
        op["parameters"].append({"_type": "UMLParameter", "_id": nid(), "_parent": ref(op),
                                 "type": ret, "direction": "return"})
        cls["operations"].append(op)
    model["ownedElements"].append(cls)
    classes[cname] = cls

    longest = max([len(cname) + 4] + [len(t) for t in attrs + ops])
    width = max(200, int(longest * CHAR) + 30)
    name_h = 25
    attr_h = max(10, ROW * len(attrs) + 8)
    op_h = max(10, ROW * len(ops) + 8)
    height = name_h + attr_h + op_h

    view = {"_type": "UMLClassView", "_id": nid(), "_parent": ref(diagram), "model": ref(cls), "subViews": [],
            "font": FONT, "parentStyle": False, "containerChangeable": True,
            "left": x, "top": y, "width": width, "height": height}

    name_c = compartment("UMLNameCompartmentView", view, cls, x, y, width, name_h)
    stereo = label(name_c, visible=False, left=x, top=y)
    name_l = label(name_c, cname, bold=True, left=x + 5, top=y + 6, width=width - 10)
    ns = label(name_c, "(from Model)", visible=False, left=x, top=y)
    prop = label(name_c, visible=False, left=x, top=y, align=1)
    name_c["subViews"] = [stereo, name_l, ns, prop]
    name_c.update(stereotypeLabel=ref(stereo), nameLabel=ref(name_l), namespaceLabel=ref(ns), propertyLabel=ref(prop))

    attr_c = compartment("UMLAttributeCompartmentView", view, cls, x, y + name_h, width, attr_h)
    attr_c["subViews"] = [
        {"_type": "UMLAttributeView", "_id": nid(), "_parent": ref(attr_c), "model": ref(m), "font": FONT,
         "parentStyle": True, "left": x + 5, "top": y + name_h + 5 + i * ROW, "width": width - 10, "height": 13,
         "text": t, "horizontalAlignment": 0}
        for i, (t, m) in enumerate(zip(attrs, cls["attributes"]))]

    op_c = compartment("UMLOperationCompartmentView", view, cls, x, y + name_h + attr_h, width, op_h)
    op_c["subViews"] = [
        {"_type": "UMLOperationView", "_id": nid(), "_parent": ref(op_c), "model": ref(m), "font": FONT,
         "parentStyle": True, "left": x + 5, "top": y + name_h + attr_h + 5 + i * ROW, "width": width - 10,
         "height": 13, "text": t, "horizontalAlignment": 0}
        for i, (t, m) in enumerate(zip(ops, cls["operations"]))]

    rec_c = compartment("UMLReceptionCompartmentView", view, cls, x, y, 10, 10)
    rec_c["visible"] = False
    tpl_c = compartment("UMLTemplateParameterCompartmentView", view, cls, x + width, y, 10, 10)
    tpl_c["visible"] = False

    view["subViews"] = [name_c, attr_c, op_c, rec_c, tpl_c]
    view.update(nameCompartment=ref(name_c), attributeCompartment=ref(attr_c), operationCompartment=ref(op_c),
                receptionCompartment=ref(rec_c), templateParameterCompartment=ref(tpl_c))
    diagram["ownedViews"].append(view)
    views[cname] = view
    boxes[cname] = (x, y, width, height)


def edge_point(a, b):
    """Where the line from the centre of box a toward the centre of box b leaves box a."""
    ax, ay, aw, ah = a
    bx, by, bw, bh = b
    cx, cy = ax + aw / 2, ay + ah / 2
    dx, dy = bx + bw / 2 - cx, by + bh / 2 - cy
    if dx == 0 and dy == 0:
        return cx, cy
    sx = (aw / 2) / abs(dx) if dx else float("inf")
    sy = (ah / 2) / abs(dy) if dy else float("inf")
    s = min(sx, sy)
    return round(cx + dx * s), round(cy + dy * s)


def edge_label(view, model, visible, alpha, distance, position=None, text=None):
    o = {"_type": "EdgeLabelView", "_id": nid(), "_parent": ref(view), "model": ref(model), "font": FONT,
         "parentStyle": False, "left": 0, "top": 0, "height": 13, "alpha": alpha, "distance": distance,
         "hostEdge": ref(view)}
    if not visible:
        o["visible"] = False
    if position is not None:
        o["edgePosition"] = position
    if text:
        o["text"] = text
    return o


for tail, tmult, head, hmult, name, composite in ASSOCIATIONS:
    assoc = {"_type": "UMLAssociation", "_id": nid(), "_parent": ref(classes[tail]), "name": name}
    end1 = {"_type": "UMLAssociationEnd", "_id": nid(), "_parent": ref(assoc), "reference": ref(classes[tail]),
            "multiplicity": tmult}
    if composite:
        end1["aggregation"] = "composite"
    end2 = {"_type": "UMLAssociationEnd", "_id": nid(), "_parent": ref(assoc), "reference": ref(classes[head]),
            "multiplicity": hmult}
    assoc["end1"], assoc["end2"] = end1, end2
    classes[tail]["ownedElements"].append(assoc)

    p1 = edge_point(boxes[tail], boxes[head])
    p2 = edge_point(boxes[head], boxes[tail])
    v = {"_type": "UMLAssociationView", "_id": nid(), "_parent": ref(diagram), "model": ref(assoc),
         "subViews": [], "font": FONT, "parentStyle": False,
         "head": ref(views[head]), "tail": ref(views[tail]), "lineStyle": 1,
         "points": f"{p1[0]}:{p1[1]};{p2[0]}:{p2[1]}", "showVisibility": False, "showEndOrder": "hide"}
    pi = 1.5707963267948966
    name_l = edge_label(v, assoc, True, pi, 15, 1, name)
    stereo_l = edge_label(v, assoc, False, pi, 30, 1)
    prop_l = edge_label(v, assoc, False, -pi, 15, 1)
    t_role = edge_label(v, end1, False, 0.5235987755982988, 30, 2)
    t_prop = edge_label(v, end1, False, 0.7853981633974483, 40, 2)
    t_mult = edge_label(v, end1, True, -0.5235987755982988, 25, 2, tmult)
    h_role = edge_label(v, end2, False, -0.5235987755982988, 30)
    h_prop = edge_label(v, end2, False, -0.7853981633974483, 40)
    h_mult = edge_label(v, end2, True, 0.5235987755982988, 25, None, hmult)
    t_q = {"_type": "UMLQualifierCompartmentView", "_id": nid(), "_parent": ref(v), "model": ref(end1),
           "visible": False, "font": FONT, "parentStyle": True, "left": 0, "top": 0, "width": 10, "height": 10}
    h_q = {"_type": "UMLQualifierCompartmentView", "_id": nid(), "_parent": ref(v), "model": ref(end2),
           "visible": False, "font": FONT, "parentStyle": True, "left": 0, "top": 0, "width": 10, "height": 10}
    v["subViews"] = [name_l, stereo_l, prop_l, t_role, t_prop, t_mult, h_role, h_prop, h_mult, t_q, h_q]
    v.update(nameLabel=ref(name_l), stereotypeLabel=ref(stereo_l), propertyLabel=ref(prop_l),
             tailRoleNameLabel=ref(t_role), tailPropertyLabel=ref(t_prop), tailMultiplicityLabel=ref(t_mult),
             headRoleNameLabel=ref(h_role), headPropertyLabel=ref(h_prop), headMultiplicityLabel=ref(h_mult),
             tailQualifiersCompartment=ref(t_q), headQualifiersCompartment=ref(h_q))
    diagram["ownedViews"].append(v)

out = sys.argv[1]
with open(out, "w", encoding="utf-8") as f:
    json.dump(project, f, indent="\t")
print(f"wrote {out}: {len(CLASSES)} classes, {len(ASSOCIATIONS)} associations")
