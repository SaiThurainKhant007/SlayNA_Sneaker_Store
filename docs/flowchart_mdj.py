"""Shared builder for the StarUML 6 Flowchart files (make_customer_flowchart.py, make_admin_flowchart.py).

Nodes are {key: (shape, text, column, row)}; flows are (source, side, target, side, label, bends),
where a side is T/B/L/R and a bend is (x or node key, row).
"""
import base64, json

FONT = "Arial;13;0"
COL_X = {-1: 200, 0: 500, 1: 800, 2: 1100, 3: 1400}   # column centre x
ROW_H = 100                                            # row centre y = 60 + row * ROW_H
SIZES = {"Terminator": (140, 40), "Process": (220, 50), "Data": (230, 50), "Decision": (200, 70)}


def build(out, chart_name, diagram_name, nodes, flows, wide=None):
    wide = wide or {}
    n = 0

    def nid():
        nonlocal n
        n += 1
        return base64.b64encode(b"SLAYFC" + n.to_bytes(6, "big")).decode()

    def ref(o):
        return {"$ref": o["_id"]}

    def box(key):
        shape, _, col, row = nodes[key]
        w, h = wide.get(key, SIZES[shape])
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
    chart = {"_type": "FCFlowchart", "_id": nid(), "_parent": ref(project), "name": chart_name, "ownedElements": []}
    project["ownedElements"].append(chart)
    diagram = {"_type": "FCFlowchartDiagram", "_id": nid(), "_parent": ref(chart), "name": diagram_name,
               "defaultDiagram": True, "ownedViews": []}
    chart["ownedElements"].append(diagram)

    models, views = {}, {}
    for key, (shape, text, _, _) in nodes.items():
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

    for src, sside, dst, dside, name, bends in flows:
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

    with open(out, "w", encoding="utf-8") as f:
        json.dump(project, f, indent="\t")
    print(f"wrote {out}: {len(nodes)} nodes, {len(flows)} flows")
