import zipfile, re

path = r"c:\Users\HP\OneDrive\Documents\202635_Sai Thurein Khant_ISP Interim Report_Spring 2026..docx"
with zipfile.ZipFile(path) as z:
    rels = z.read("word/_rels/document.xml.rels").decode("utf-8")
    doc = z.read("word/document.xml").decode("utf-8")

rid_map = {}
for m in re.finditer(r'Id="(rId\d+)"[^>]*Target="media/([^"]+)"', rels):
    rid_map[m.group(1)] = m.group(2)

parts = re.split(r"<w:p[ >]", doc)
for part in parts:
    texts = re.findall(r"<w:t[^>]*>([^<]*)</w:t>", part)
    t = "".join(texts).strip()
    imgs = re.findall(r'r:embed="(rId\d+)"', part)
    if imgs:
        print(t[:100], "->", [rid_map.get(i, i) for i in imgs])
