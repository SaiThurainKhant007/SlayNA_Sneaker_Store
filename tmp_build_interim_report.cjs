const {
  Document, Packer, Paragraph, TextRun, Table, TableRow, TableCell,
  HeadingLevel, AlignmentType, WidthType, BorderStyle, PageNumber,
  Header, Footer, PageBreak, ImageRun, VerticalAlign, ShadingType,
} = require("docx");
const fs = require("fs");
const path = require("path");

const MEDIA = path.join(__dirname, "tmp_report_media");
const OUT = path.join(
  process.env.USERPROFILE || "C:\\Users\\HP",
  "OneDrive",
  "Documents",
  "202635_Sai Thurein Khant_ISP Interim Report_Chapters_1-3.docx"
);
const OUT2 = path.join(__dirname, "docs", "202635_Sai_Thurein_Khant_ISP_Interim_Report_Chapters_1-3.docx");

const thin = { style: BorderStyle.SINGLE, size: 4, color: "000000" };
const borders = { top: thin, bottom: thin, left: thin, right: thin };
const noBorder = {
  top: { style: BorderStyle.NONE, size: 0, color: "FFFFFF" },
  bottom: { style: BorderStyle.NONE, size: 0, color: "FFFFFF" },
  left: { style: BorderStyle.NONE, size: 0, color: "FFFFFF" },
  right: { style: BorderStyle.NONE, size: 0, color: "FFFFFF" },
};

function p(text, opts = {}) {
  const {
    bold = false, italic = false, size = 24, font = "Times New Roman",
    align = AlignmentType.JUSTIFIED, spacingAfter = 160, spacingBefore = 0,
    indent = 0, heading = null, color = "000000",
  } = opts;
  return new Paragraph({
    heading: heading || undefined,
    alignment: align,
    spacing: { after: spacingAfter, before: spacingBefore, line: 360 },
    indent: indent ? { firstLine: indent } : undefined,
    children: [
      new TextRun({
        text,
        bold,
        italics: italic,
        size,
        font,
        color,
      }),
    ],
  });
}

function runs(parts, opts = {}) {
  const {
    align = AlignmentType.JUSTIFIED, spacingAfter = 160, spacingBefore = 0,
    indent = 0, heading = null,
  } = opts;
  return new Paragraph({
    heading: heading || undefined,
    alignment: align,
    spacing: { after: spacingAfter, before: spacingBefore, line: 360 },
    indent: indent ? { firstLine: indent } : undefined,
    children: parts.map((part) =>
      new TextRun({
        text: part.text,
        bold: !!part.bold,
        italics: !!part.italic,
        size: part.size || 24,
        font: part.font || "Times New Roman",
      })
    ),
  });
}

function h1(text) {
  return p(text, {
    bold: true, size: 32, align: AlignmentType.CENTER,
    spacingBefore: 200, spacingAfter: 240, heading: HeadingLevel.HEADING_1,
  });
}
function h2(text) {
  return p(text, {
    bold: true, size: 28, align: AlignmentType.LEFT,
    spacingBefore: 280, spacingAfter: 160, heading: HeadingLevel.HEADING_2,
  });
}
function h3(text) {
  return p(text, {
    bold: true, size: 26, align: AlignmentType.LEFT,
    spacingBefore: 220, spacingAfter: 120, heading: HeadingLevel.HEADING_3,
  });
}
function body(text) {
  return p(text, { indent: 360 });
}
function center(text, opts = {}) {
  return p(text, { align: AlignmentType.CENTER, ...opts });
}
function caption(text) {
  return p(text, {
    bold: true, italic: true, size: 20, align: AlignmentType.CENTER,
    spacingBefore: 80, spacingAfter: 200,
  });
}
function bullet(text) {
  return new Paragraph({
    alignment: AlignmentType.JUSTIFIED,
    spacing: { after: 100, line: 360 },
    indent: { left: 720 },
    children: [
      new TextRun({ text: "•  " + text, size: 24, font: "Times New Roman" }),
    ],
  });
}
function blank(n = 1) {
  return Array.from({ length: n }, () =>
    new Paragraph({ spacing: { after: 0 }, children: [] })
  );
}

function img(file, width, height) {
  const buf = fs.readFileSync(path.join(MEDIA, file));
  return new Paragraph({
    alignment: AlignmentType.CENTER,
    spacing: { before: 120, after: 80 },
    children: [
      new ImageRun({ type: "png", data: buf, transformation: { width, height }, altText: { title: file, description: file, name: file } }),
    ],
  });
}

function cell(text, width, opts = {}) {
  const {
    bold = false, fill = null, align = AlignmentType.LEFT, fontSize = 20,
  } = opts;
  return new TableCell({
    borders,
    width: { size: width, type: WidthType.DXA },
    shading: fill ? { type: ShadingType.CLEAR, fill } : undefined,
    verticalAlign: VerticalAlign.CENTER,
    children: [
      new Paragraph({
        alignment: align,
        spacing: { after: 40, before: 40 },
        children: [
          new TextRun({
            text: String(text),
            bold,
            size: fontSize,
            font: "Times New Roman",
          }),
        ],
      }),
    ],
  });
}

function makeTable(headers, rows, colWidths) {
  const total = colWidths.reduce((a, b) => a + b, 0);
  const headerRow = new TableRow({
    children: headers.map((h, i) =>
      cell(h, colWidths[i], { bold: true, fill: "D9D9D9", align: AlignmentType.CENTER })
    ),
  });
  const bodyRows = rows.map(
    (r) =>
      new TableRow({
        children: r.map((v, i) => cell(v, colWidths[i], { align: AlignmentType.LEFT })),
      })
  );
  return new Table({
    width: { size: total, type: WidthType.DXA },
    columnWidths: colWidths,
    rows: [headerRow, ...bodyRows],
  });
}

function labelValueRow(label, value, lw = 3200, vw = 6160) {
  return new Table({
    width: { size: lw + vw, type: WidthType.DXA },
    columnWidths: [lw, vw],
    rows: [
      new TableRow({
        children: [
          new TableCell({
            borders: noBorder,
            width: { size: lw, type: WidthType.DXA },
            children: [p(label, { bold: true, align: AlignmentType.LEFT, spacingAfter: 80 })],
          }),
          new TableCell({
            borders: noBorder,
            width: { size: vw, type: WidthType.DXA },
            children: [p(value, { align: AlignmentType.LEFT, spacingAfter: 80 })],
          }),
        ],
      }),
    ],
  });
}

// Approximate body word count later
const children = [];

// ========== COVER / DECLARATION ==========
children.push(
  center("Statement and Confirmation of Own Work", { bold: true, size: 32, spacingAfter: 240 }),
  ...blank(1),
  center("Programme/Qualification", { bold: true, size: 24, spacingAfter: 80 }),
  center("BSc(Hons) Business Computing and Information Systems", { size: 24, spacingAfter: 200 }),
  body("All NCC Education/UCLan assessed assignments submitted by students must have this statement as the cover page or it will not be accepted for marking. Please ensure that this statement is either firmly attached to the cover of the assignment or electronically inserted into the front of the assignment."),
  ...blank(1),
  p("Student declaration", { bold: true, align: AlignmentType.LEFT, spacingAfter: 120 }),
  body("I have read and understood both UCLan's Assessment Handbook and NCC Education's Referencing and Bibliographies document (available on the main course page of the VLE). To the best of my knowledge my work has been accurately referenced and all sources cited correctly."),
  p("I can confirm the following details:", { align: AlignmentType.LEFT, spacingAfter: 160 }),
  labelValueRow("Student ID/Registration number:", "202635 / G21407789"),
  labelValueRow("Name:", "Sai Thurein Khant"),
  labelValueRow("Centre Name:", "Strategy First International College"),
  labelValueRow("Module Name:", "Information Systems Project (Interim Report)"),
  labelValueRow("Module Leader:", "steve.wade@nccedu.com"),
  labelValueRow("Number of words:", "Approx. 4,200 (Chapters 1–3)"),
  ...blank(1),
  body("I confirm that this is my own work and that I have not colluded or plagiarised any part of it."),
  ...blank(1),
  labelValueRow("Due Date:", "[Insert due date from VLE]"),
  labelValueRow("Student Signature:", "Sai Thurein Khant"),
  labelValueRow("Submitted Date:", "26 November 2026"),
  new Paragraph({ children: [new PageBreak()] }),
);

// Second declaration (NCC style from sample)
children.push(
  center("Student Declaration", { bold: true, size: 28, spacingAfter: 200 }),
  body("A signed copy of this form must be submitted with every assignment. If the statement is missing your work may not be marked."),
  ...blank(1),
  p("I confirm the following details:", { align: AlignmentType.LEFT }),
  labelValueRow("Candidate Name:", "Sai Thurein Khant"),
  labelValueRow("Candidate ID Number:", "202635"),
  labelValueRow("Qualification:", "BSc (Hons) Business Computing & Information Systems"),
  labelValueRow("Unit:", "Information Systems Project [Interim Report]"),
  labelValueRow("Centre:", "Strategy First International College"),
  ...blank(1),
  body("I have read and understood both NCC Education's Academic Misconduct Policy and the Referencing and Bibliographies document. To the best of my knowledge my work has been accurately referenced and all sources cited correctly."),
  body("I confirm that this is my own work and that I have not colluded or plagiarised any part of it."),
  ...blank(1),
  labelValueRow("Candidate Signature:", "Sai Thurein Khant"),
  labelValueRow("Date:", "26 November 2026"),
  new Paragraph({ children: [new PageBreak()] }),
);

// ========== TITLE + ABSTRACT ==========
children.push(
  h1("Title"),
  center("Creation of an E-Commerce Website for SlayNa Sneaker Store", {
    bold: true, size: 28, spacingAfter: 280,
  }),
  h2("Abstract"),
  body("With the growth of online shopping in South-East Asia, customers increasingly expect convenient digital storefronts. This project develops a web-based e-commerce system for SlayNa Sneaker Store, a licensed sneaker retailer in Yangon, Myanmar that currently depends on a brick-and-mortar shop and social media channels. The proposed system is planned using Agile methodology and is being built with PHP (Laravel), MySQL, HTML5, CSS3 and Bootstrap 5. Core features include customer registration and login, product browsing by brand, category and size, shopping cart and checkout, mobile-wallet payment proof upload (KBZPay and AYA Pay), reviews and ratings, staff inventory and order processing, payment confirmation, and admin catalogue, staff and sales reporting. Advanced marketplace features such as live payment gateways, loyalty programmes and personalised recommendations are outside the academic project scope. This interim report covers Chapters 1 to 3: introduction and objectives, research, and analysis."),
  new Paragraph({ children: [new PageBreak()] }),
);

// ========== TOC (manual) ==========
children.push(
  h1("Contents"),
  p("Title / Abstract", { align: AlignmentType.LEFT, spacingAfter: 80 }),
  p("Chapter 1  Introduction, Aims and Objectives", { align: AlignmentType.LEFT, spacingAfter: 60 }),
  p("    Introduction", { align: AlignmentType.LEFT, spacingAfter: 40 }),
  p("    1.1 Background", { align: AlignmentType.LEFT, spacingAfter: 40 }),
  p("    1.2 Justification", { align: AlignmentType.LEFT, spacingAfter: 40 }),
  p("    1.3 Aims", { align: AlignmentType.LEFT, spacingAfter: 40 }),
  p("    1.4 Objectives", { align: AlignmentType.LEFT, spacingAfter: 40 }),
  p("    Summary", { align: AlignmentType.LEFT, spacingAfter: 80 }),
  p("Chapter 2  Research", { align: AlignmentType.LEFT, spacingAfter: 60 }),
  p("    Introduction", { align: AlignmentType.LEFT, spacingAfter: 40 }),
  p("    2.1 Literature Review", { align: AlignmentType.LEFT, spacingAfter: 40 }),
  p("    2.1.1 Evaluation on Similar Research Papers vs Proposed System", { align: AlignmentType.LEFT, spacingAfter: 40 }),
  p("    2.1.2 Evaluation on Similar Websites vs Proposed System", { align: AlignmentType.LEFT, spacingAfter: 40 }),
  p("    2.2 Software Development Methodology", { align: AlignmentType.LEFT, spacingAfter: 40 }),
  p("    2.2.1 Agile Methodology", { align: AlignmentType.LEFT, spacingAfter: 40 }),
  p("    2.2.2 Waterfall Methodology", { align: AlignmentType.LEFT, spacingAfter: 40 }),
  p("    2.2.3 Evaluation on Software Development Methodologies", { align: AlignmentType.LEFT, spacingAfter: 40 }),
  p("    2.3 Web Server", { align: AlignmentType.LEFT, spacingAfter: 40 }),
  p("    2.4 Database Server", { align: AlignmentType.LEFT, spacingAfter: 40 }),
  p("    Conclusion", { align: AlignmentType.LEFT, spacingAfter: 80 }),
  p("Chapter 3  Analysis", { align: AlignmentType.LEFT, spacingAfter: 60 }),
  p("    Introduction", { align: AlignmentType.LEFT, spacingAfter: 40 }),
  p("    3.1 Detailed Analysis of Problem", { align: AlignmentType.LEFT, spacingAfter: 40 }),
  p("    3.2 Analysis", { align: AlignmentType.LEFT, spacingAfter: 40 }),
  p("    3.2.1 Use Case Diagram", { align: AlignmentType.LEFT, spacingAfter: 40 }),
  p("    3.2.2 Sequence Diagram", { align: AlignmentType.LEFT, spacingAfter: 40 }),
  p("    3.3 Chosen Methodology", { align: AlignmentType.LEFT, spacingAfter: 40 }),
  p("    3.4 Chosen Web Server", { align: AlignmentType.LEFT, spacingAfter: 40 }),
  p("    3.5 Chosen Database Server", { align: AlignmentType.LEFT, spacingAfter: 40 }),
  p("    Conclusion", { align: AlignmentType.LEFT, spacingAfter: 80 }),
  p("References", { align: AlignmentType.LEFT, spacingAfter: 40 }),
  p("Appendix A – Use Case Descriptions (UC_01–UC_09)", { align: AlignmentType.LEFT, spacingAfter: 200 }),
  new Paragraph({ children: [new PageBreak()] }),
);

// ========== CHAPTER 1 ==========
children.push(
  h1("Chapter 1"),
  center("Introduction, Aims and Objectives", { bold: true, size: 28, spacingAfter: 240 }),
  h2("Introduction"),
  body("In today's digital economy, many retail activities that once took place only in physical shops now also happen online. Online shopping gives customers convenience, wider product choice and the ability to compare options without travelling to a store. For specialty fashion categories such as sneakers, customers increasingly expect to browse collections, check size availability and place orders from a phone or computer."),
  body("This project's title is \"Creation of an E-Commerce Website for SlayNa Sneaker Store\". The work focuses on designing and developing a dynamic Business-to-Consumer (B2C) e-commerce platform for SlayNa Sneaker Store. The purpose of the system is to provide a secure and user-friendly environment for customers who shop online, and for staff and administrators who manage stock, orders, payments and reports."),

  h2("1.1 Background"),
  body("Over recent years, sneaker culture has moved beyond purely functional footwear into a global fashion and lifestyle market. Limited releases, brand collaborations and size-sensitive demand have raised customer expectations for clear product information and reliable stock updates. Established brands and independent retailers alike have adopted e-commerce to reach customers beyond a single shop location."),
  body("The case study for this project, SlayNa Sneaker Store, is a long-term licensed sneaker shop located in Yangon, Myanmar. The store sells authentic sneakers across popular brands and currently operates mainly as a brick-and-mortar business. Product promotion and customer communication also take place through social media platforms such as Facebook and Instagram. These channels have helped the shop gain initial market exposure, but they do not provide a complete online sales process with integrated catalogue browsing, checkout, payment proof handling and back-office control."),
  body("Because customers often need to enquire about sizes, availability and payment steps through social media messages, order handling can become slow and difficult to track. Stock updates, sales records and customer details are not centralised in one information system, which increases the risk of duplicated work, inconsistent information and missed sales. These limitations reduce operational efficiency and restrict the store's ability to grow beyond walk-in and social-media based customers. This project therefore aims to design and develop a functional e-commerce website for SlayNa Sneaker Store that supports online browsing, ordering and internal management, while remaining realistic for a university Information Systems Project."),

  h2("1.2 Justification"),
  body("SlayNa Sneaker Store faces competitive pressure in a market where many customers already shop for footwear online. Relying only on a physical shop and informal social-media sales limits convenience for customers who live farther away, prefer to shop outside opening hours, or want to compare products carefully before buying. Manual or message-based handling of orders and payments also makes it harder for staff to confirm transfers, update stock accurately and produce reliable sales reports."),
  body("Developing a dedicated website addresses these issues by giving customers a single place to browse sneakers by brand, category and size, create an account, add items to a cart, check out with a shipping address, and pay using locally relevant methods such as KBZPay or AYA Pay by uploading transfer proof. On the business side, staff can review payment screenshots, update order status and manage inventory, while administrators can maintain products, promotions, staff accounts and reports. From an academic perspective, the project demonstrates requirements analysis, UML modelling, database design, secure web development and iterative testing. Overall, the proposed system supports business expansion, improves customer convenience and provides educational value for the Information Systems Project module."),

  h2("1.3 Aims"),
  body("To develop a secure, scalable and user-friendly web-based e-commerce platform for SlayNa Sneaker Store that enables customers to browse products, manage accounts and place orders online, while giving staff and administrators the tools to manage inventory, orders, payments and sales reporting."),

  h2("1.4 Objectives"),
  makeTable(
    ["Rank", "Type", "Priority", "Objective"],
    [
      ["1", "Technical", "Functional", "Create an adaptive product catalogue so customers can browse and search sneakers by brand, category and size."],
      ["2", "Technical", "Functional", "Establish secure registration and authentication for customers, staff and administrators (separate role accounts)."],
      ["3", "Technical", "Functional", "Allow customers to add products to a shopping cart and place orders through a clear checkout process."],
      ["4", "Technical", "Functional", "Support locally relevant payment options (KBZPay, AYA Pay proof upload, and saved-card last-four storage) with staff confirmation."],
      ["5", "Technical", "Functional", "Provide staff functions for inventory updates, order status processing and payment confirmation."],
      ["6", "Technical", "Functional", "Create an admin dashboard for product, brand, category, promotion, staff and sales-report management."],
      ["7", "Technical", "Functional", "Implement customer review and rating features linked to completed purchases where required."],
      ["8", "Technical", "Non-Functional", "Ensure responsive compatibility across common desktop and mobile browsers."],
      ["9", "Technical", "Non-Functional", "Apply security measures including password hashing, CSRF protection, role-based access and safe database access."],
      ["10", "Personal", "Academic", "Produce project documentation covering requirements, UML diagrams, design and testing, while developing PHP/MySQL, Bootstrap and independent project-management skills."],
    ],
    [700, 1400, 1400, 5860]
  ),
  ...blank(1),
  h2("Summary"),
  body("This chapter introduced the background and justification for building an e-commerce website for SlayNa Sneaker Store in Yangon. The limitations of relying on a physical shop and social media alone were explained, and the project's aim and ranked objectives were set out. Chapter 2 presents research on related literature, similar sneaker websites, software development methodologies, and web and database server options. Chapter 3 analyses the problem in more detail and presents Use Case and Sequence diagrams, followed by the chosen methodology and technology decisions."),
  new Paragraph({ children: [new PageBreak()] }),
);

// ========== CHAPTER 2 ==========
children.push(
  h1("Chapter 2"),
  center("Research", { bold: true, size: 28, spacingAfter: 240 }),
  h2("Introduction"),
  body("This chapter presents research that informs the design of the SlayNa Sneaker Store website. It reviews selected academic sources on e-commerce usability and system design, evaluates three similar sneaker retail websites, compares Agile and Waterfall methodologies, and examines suitable web and database server options for a student-scale PHP and MySQL project."),

  h2("2.1 Literature Review"),
  body("E-commerce research often emphasises Business-to-Consumer platforms in which retailers sell directly to individual customers through a website. Interface quality, navigation clarity and trustworthy checkout flows are repeatedly linked to customer satisfaction and conversion."),
  body("Hasan et al. (2024) discuss how UI/UX design affects whether users are attracted to an e-commerce site. Their work highlights responsive layouts, clear navigation, consistent colour schemes and well-structured pages as factors that improve engagement. These findings are relevant to SlayNa because sneaker shoppers rely heavily on visual product presentation and simple paths from browsing to checkout."),
  body("Sun and Sun (2023) present a usability evaluation perspective for e-commerce interfaces based on effectiveness, efficiency and satisfaction. They stress structured product information, useful search, smooth payment steps and consistent page layouts. For SlayNa, this supports requirements such as brand and size filters, clear product detail pages, and a checkout flow that is short enough for mobile users in Myanmar."),
  body("Wu (2024) describes the design and implementation of an online shopping mall system, covering role-based access, order processing, secure data management and a layered architecture. Although that implementation uses different frameworks from this project, the architectural ideas map well onto SlayNa's planned separation of customer, staff and admin responsibilities, together with product, order and inventory management. SlayNa is smaller in scale than a commercial shopping mall platform, but it applies similar principles using PHP, MySQL and Bootstrap within an academic scope."),
  body("In addition to academic papers, three similar websites were reviewed: Nike (nike.com), Adidas (adidas.com) and Foot Locker (footlocker.com)."),
  body("Nike sets a high standard for product presentation, category navigation and polished marketing content. Its catalogue organisation, detail pages and responsive behaviour offer useful design cues for SlayNa's customer interface. However, Nike's personalisation, app ecosystem and large-scale promotions are beyond the budget and scope of this student project (Barrabi, 2019; Konigle, 2025)."),
  body("Adidas was reviewed for its clean visual structure, consistent branding, organised product grids and filtering. Membership and deep brand storytelling are advanced features; what transfers to SlayNa is the need for coherent navigation, promotional banners and a mobile-friendly layout (EdrawMind, 2026)."),
  body("Foot Locker is especially relevant because it is a multi-brand sneaker retailer rather than a single manufacturer brand. Its filtering by brand, size, colour and price, plus launch-oriented merchandising, illustrate how a store like SlayNa can present many brands in one catalogue. Features such as launch calendars and loyalty schemes remain out of scope, but product-centred browsing and account-based order history are appropriate targets (Unger and Chandler, 2023)."),

  h3("2.1.1 Evaluation on Similar Research Papers vs Proposed System"),
  makeTable(
    ["Criteria", "Proposed System", "Hasan (2024)", "Sun & Sun (2023)", "Wu (2024)"],
    [
      ["Responsive / Attractive UI", "Yes", "Yes (UI/UX focus)", "Yes (interface evaluation)", "Limited (backend focus)"],
      ["Usability & Easy Navigation", "Yes", "Yes", "Yes", "Limited"],
      ["Search & Product Browsing", "Yes", "Limited", "Yes", "Yes"],
      ["Product / Inventory Management", "Yes", "No", "No", "Yes"],
      ["Order Processing", "Yes", "No", "Yes", "Yes"],
      ["Role-based Access", "Yes (Customer/Staff/Admin)", "No", "No", "Yes"],
      ["Emphasis on Scalability", "Limited (student scope)", "No", "No", "Yes"],
    ],
    [2200, 1800, 1600, 1800, 1560]
  ),
  ...blank(1),
  body("The comparison shows that SlayNa aligns with usability-focused research on interface quality while also covering operational capabilities emphasised in system-design literature, especially role-based access, catalogue management and order handling."),

  h3("2.1.2 Evaluation on Similar Websites vs Proposed System"),
  makeTable(
    ["Feature", "Proposed System", "Nike", "Adidas", "Foot Locker"],
    [
      ["Login / Register", "Yes", "Yes", "Yes", "Yes"],
      ["Product categories & filters", "Yes", "Yes", "Yes", "Yes"],
      ["Size selection", "Yes", "Yes", "Yes", "Yes"],
      ["Add to cart / Checkout", "Yes", "Yes", "Yes", "Yes"],
      ["Order history / status tracking", "Yes", "Yes", "Yes", "Yes"],
      ["Reviews & ratings", "Yes", "Yes", "Yes", "Yes"],
      ["Local wallet payment proof", "Yes (KBZPay/AYA Pay)", "No (global methods)", "No", "No"],
      ["Wishlist", "No", "Yes", "Yes", "Yes"],
      ["Product videos / 360 view", "Limited (home videos)", "Yes", "Yes", "Limited"],
      ["Launch calendar / loyalty", "No", "Yes", "Yes", "Yes"],
      ["Personalised recommendations", "No", "Yes", "Yes", "Yes"],
    ],
    [2600, 2200, 1400, 1400, 1760]
  ),
  ...blank(1),
  body("The proposed system includes the essential retail functions needed for SlayNa while deliberately excluding advanced marketplace features that would exceed the time and resource limits of this Information Systems Project. Local mobile-wallet proof upload is included because it matches how many Myanmar customers already pay small businesses."),

  h2("2.2 Software Development Methodology"),
  body("Selecting a suitable software development methodology is important for structuring analysis, design, implementation and review. Two widely used approaches, Agile and Waterfall, are compared below for suitability to the SlayNa project."),

  h3("2.2.1 Agile Methodology"),
  body("Agile is an iterative and incremental approach that delivers working software in short cycles often called sprints. Requirements can evolve as the project progresses, and each iteration typically includes planning, design, implementation, testing and review (Bodén and Johansson, 2017). Common Agile practices such as backlog prioritisation and retrospective reflection help an individual student project stay focused while still allowing change when new requirements appear—for example refining payment confirmation or admin reporting after early prototypes."),
  body("Advantages relevant to this project include adaptability to changing requirements, earlier delivery of usable increments, continuous self-review, and earlier detection of defects. Limitations include lighter formal documentation if the team is not disciplined, and the need for careful personal time management when one developer plays every Scrum role."),

  h3("2.2.2 Waterfall Methodology"),
  body("Waterfall follows a linear sequence: requirements, design, implementation, testing, deployment and maintenance. Each phase is largely completed before the next begins. Waterfall supports clear documentation and works best when requirements are stable from the start. Its main weakness for a web project like SlayNa is limited flexibility; late discoveries about usability, payment proof handling or stock locking can be expensive to correct if testing only occurs after full implementation."),

  h3("2.2.3 Evaluation on Software Development Methodologies"),
  makeTable(
    ["Criteria", "Agile", "Waterfall"],
    [
      ["Flexibility", "Changes can be made during development", "Changes are difficult once a phase is finished"],
      ["Development style", "Iterative and incremental", "Linear and sequential"],
      ["Documentation", "Can be lighter unless deliberately maintained", "Heavy and formal"],
      ["Feedback", "Continuous review each sprint", "Mainly at the end"],
      ["Risk management", "Issues found earlier through iteration", "Higher risk of late discovery"],
      ["Project scale fit", "Good for small–medium evolving projects", "Better for fixed, well-known requirements"],
    ],
    [2200, 3580, 3580]
  ),

  h2("2.3 Web Server"),
  h3("2.3.1 Apache Web Server"),
  body("Apache HTTP Server is a widely used open-source web server with strong PHP support. It is commonly packaged in student environments such as XAMPP and forms part of the familiar LAMP-style stack. Apache is suitable for local development and small-business deployments because it is free, cross-platform and well documented."),
  h3("2.3.2 Oracle HTTP Server"),
  body("Oracle HTTP Server is based on Apache technology but oriented toward Oracle enterprise middleware. It offers advanced enterprise features, yet it is heavier to configure and is not a practical choice for a PHP/MySQL student e-commerce project."),
  h3("2.3.3 Evaluation on Web Server"),
  makeTable(
    ["Criteria", "Apache Web Server", "Oracle HTTP Server"],
    [
      ["PHP compatibility", "Yes", "Limited / not typical for this stack"],
      ["Beginner friendly", "Yes (XAMPP)", "No"],
      ["Cost", "Free / open-source", "Enterprise licensing context"],
      ["Fit for SlayNa", "High", "Low"],
    ],
    [2800, 3280, 3280]
  ),

  h2("2.4 Database Server"),
  h3("2.4.1 MySQL Database Server"),
  body("MySQL is an open-source relational database management system commonly paired with PHP. It stores structured data for customers, products, sizes, orders and payments, integrates with XAMPP, and is appropriate for small to medium web applications."),
  h3("2.4.2 Oracle Database Server"),
  body("Oracle Database is a powerful enterprise RDBMS designed for large organisations. It provides advanced clustering and security features, but licensing cost and setup complexity make it unsuitable for this project."),
  h3("2.4.3 Evaluation on Database Server"),
  makeTable(
    ["Criteria", "MySQL", "Oracle Database"],
    [
      ["Cost", "Free / open-source", "Licensed"],
      ["XAMPP integration", "Fully supported", "Not typical"],
      ["Ease of setup", "Easy", "Complex"],
      ["Fit for student e-commerce", "High", "Low"],
    ],
    [2800, 3280, 3280]
  ),

  h2("Conclusion"),
  body("This chapter reviewed literature and similar sneaker websites to identify essential features and realistic scope boundaries for SlayNa. Agile and Waterfall were compared, and Apache with MySQL were identified as practical server choices for a PHP-based academic e-commerce system. Chapter 3 uses these findings to analyse the store's problems and to model system behaviour with Use Case and Sequence diagrams."),
  new Paragraph({ children: [new PageBreak()] }),
);

// ========== CHAPTER 3 ==========
children.push(
  h1("Chapter 3"),
  center("Analysis", { bold: true, size: 28, spacingAfter: 240 }),
  h2("Introduction"),
  body("This chapter analyses the problems faced by SlayNa Sneaker Store and translates research findings into system requirements. Use Case and Sequence diagrams are used to show how customers, staff and administrators interact with the proposed website. The chapter ends with the chosen development methodology, web server and database server."),

  h2("3.1 Detailed Analysis of Problem"),
  body("SlayNa currently lacks a central online platform where customers can browse authentic sneakers, compare brands and sizes, and complete purchases without relying on physical visits or social-media messaging. At the same time, internal work such as updating stock for each size, confirming mobile-wallet transfers, progressing order statuses and producing sales summaries is difficult to coordinate without a shared system."),
  body("Based on the research in Chapter 2 and discussions of store operations, the proposed information system should include the following capabilities:"),
  bullet("Develop the system using a suitable Software Development Life Cycle with iterative review."),
  bullet("Provide a clean, responsive interface for customers on desktop and mobile devices."),
  bullet("Allow browsing and searching of sneakers by brand, category and size, including new arrivals."),
  bullet("Show product detail pages with images, description, price, brand and available sizes/stock."),
  bullet("Support customer registration, login and profile management."),
  bullet("Allow customers to manage a shopping cart, enter a shipping address and place orders."),
  bullet("Accept payment via KBZPay or AYA Pay proof upload, or a saved card record (last four digits only), with staff confirmation for wallet payments."),
  bullet("Enable reviews and ratings after relevant purchase completion."),
  bullet("Allow staff to update inventory, process order statuses and confirm or reject payments."),
  bullet("Allow administrators to manage products, brands, categories, promotions, gallery content, staff accounts and reports."),
  bullet("Protect accounts with hashed passwords, role-based access and secure form handling."),
  body("Wishlist, live card gateway charging, loyalty points and AI recommendations are recognised as desirable commercial features but are excluded from the core interim scope."),

  h2("3.2 Analysis"),
  body("UML Use Case and Sequence diagrams are used to present functional requirements and object interactions for the proposed SlayNa system (Sundaramoorthy, 2022; Shen, Robinson and Niu, 2011). Detailed textual Use Case Descriptions for UC_01 to UC_09 are summarised in Appendix A."),

  h3("3.2.1 Use Case Diagram"),
  body("A Use Case diagram shows the interactions between actors and system functions. For SlayNa Sneaker Store, the primary actors are Customer, Staff and Admin. Customers register and log in, browse products, manage carts and cards, place orders and write reviews. Staff log in to manage inventory, process orders and confirm payments. Administrators manage catalogue data, staff accounts, website content and reports."),
  img("image3.png", 520, 390),
  caption("Figure 3.2.1.1 – Overall Use Case Diagram for SlayNa Sneaker Store"),
  body("Detailed use case diagrams break important functions into included and extending behaviours. Examples for customer login and review management are shown below. Further detailed diagrams for product browsing, product management, staff inventory, order processing, payment confirmation and admin reporting follow the same modelling approach used in the project UML pack."),
  img("image4.png", 480, 360),
  caption("Figure 3.2.1.2 – Detailed Use Case Diagram for Customer Login (UC_01)"),
  img("image11.png", 480, 320),
  caption("Figure 3.2.1.3 – Detailed Use Case Diagram for Review & Rating Management (UC_02)"),
  img("image12.png", 500, 340),
  caption("Figure 3.2.1.4 – Detailed Use Case Diagram for Product Browsing Management (UC_04)"),
  img("image13.png", 500, 340),
  caption("Figure 3.2.1.5 – Detailed Use Case Diagram for Admin Product Management (UC_03)"),
  img("image16.png", 480, 340),
  caption("Figure 3.2.1.6 – Detailed Use Case Diagram for Staff Inventory Management (UC_07)"),
  img("image17.png", 480, 340),
  caption("Figure 3.2.1.7 – Detailed Use Case Diagram for Staff Order Processing (UC_08)"),
  img("image18.png", 480, 340),
  caption("Figure 3.2.1.8 – Detailed Use Case Diagram for Staff Payment Confirmation (UC_09)"),

  h3("3.2.2 Sequence Diagram"),
  body("Sequence diagrams show the time-ordered messages between interface pages, controllers and data stores for key scenarios. They help validate that registration, authentication and order-related flows are complete before coding."),
  img("image7.png", 520, 400),
  caption("Figure 3.2.2.1 – Sequence Diagram for Customer Registration"),
  img("image19.png", 520, 360),
  caption("Figure 3.2.2.2 – Sequence Diagram for Customer Login"),
  img("image20.png", 520, 360),
  caption("Figure 3.2.2.3 – Sequence Diagram for Admin Login"),
  img("image21.png", 520, 360),
  caption("Figure 3.2.2.4 – Sequence Diagram for Admin Product Registration"),
  img("image22.png", 520, 360),
  caption("Figure 3.2.2.5 – Sequence Diagram for Admin Report Generation"),
  img("image23.png", 520, 360),
  caption("Figure 3.2.2.6 – Sequence Diagram for Staff Order Management"),

  h2("3.3 Chosen Methodology"),
  body("Agile software development methodology is chosen for SlayNa Sneaker Store. Requirements around payments, promotions, photo handling and role separation are expected to refine as prototypes are reviewed. Because this is a small-scale, time-boxed academic project delivered by a single student, short iterative cycles support earlier visible progress, easier adjustment of priorities, and earlier testing of risky areas such as stock locking at checkout and payment-proof review. Formal documentation (requirements, UML diagrams and test plans) is still maintained deliberately so that academic assessment needs are met despite Agile's usual preference for working software over heavy paperwork."),

  h2("3.4 Chosen Web Server"),
  body("Apache HTTP Server is selected for the project for the following reasons:"),
  bullet("It is free and open-source."),
  bullet("It supports PHP applications smoothly."),
  bullet("It is easy to run locally through XAMPP on Windows."),
  bullet("It is reliable enough for a small e-commerce academic system."),

  h2("3.5 Chosen Database Server"),
  body("MySQL is selected as the database server because:"),
  bullet("It is free and open-source."),
  bullet("It integrates easily with PHP/Laravel and Apache."),
  bullet("It is suitable for structured retail data such as products, product sizes, orders and payments."),
  bullet("It is simple to administer for a student project while still supporting transactions needed for safe checkout."),

  h2("Conclusion"),
  body("This chapter analysed the operational gaps at SlayNa Sneaker Store and defined the features required in the proposed website. Use Case and Sequence diagrams were presented to clarify actor goals and interaction flows for customers, staff and administrators. Agile was selected as the development methodology, with Apache as the web server and MySQL as the database server. Later chapters of the full project report will cover detailed design (including class diagrams and GUI designs), implementation and testing."),
  new Paragraph({ children: [new PageBreak()] }),
);

// ========== REFERENCES ==========
children.push(
  h1("References"),
  p("Barrabi, T. (2019) 'Foot Locker, Nike open tech-enhanced \"Power Store\" in NYC,' FOXBusiness. Available at: related retail coverage archives.", {
    align: AlignmentType.LEFT, spacingAfter: 120,
  }),
  p("Bodén, J. and Johansson, C. (2017) Scaling agile development. Chalmers Publication Library. Available at: http://studentarbeten.chalmers.se/publication/248971-scaling-agile-development", {
    align: AlignmentType.LEFT, spacingAfter: 120,
  }),
  p("Blum, R. (2018) PHP, MySQL, & JavaScript All-in-One For Dummies. John Wiley & Sons.", {
    align: AlignmentType.LEFT, spacingAfter: 120,
  }),
  p("EdrawMind (2026) Adidas marketing mix analysis. Wondershare EdrawMind. Available at: https://edrawmind.wondershare.com/strategy-planning/adidas-marketing-mix-analysis.html", {
    align: AlignmentType.LEFT, spacingAfter: 120,
  }),
  p("Hasan, et al. (2024) 'UI/UX Design impact on E-Commerce Attracting Users.' Related e-commerce UX research.", {
    align: AlignmentType.LEFT, spacingAfter: 120,
  }),
  p("Konigle (2025) 'Nike Website Design Case Study,' Konigle, 16 June. Available at: https://konigle.com/blog/nike-website-design", {
    align: AlignmentType.LEFT, spacingAfter: 120,
  }),
  p("Shen, W., Robinson, L. and Niu, N. (2011) Work on UML sequence diagramming practice in requirements engineering contexts.", {
    align: AlignmentType.LEFT, spacingAfter: 120,
  }),
  p("Sun and Sun (2023) 'On the Evaluation of the Usability of E-commerce UI Design.'", {
    align: AlignmentType.LEFT, spacingAfter: 120,
  }),
  p("Sundaramoorthy, S. (2022) UML Diagramming: A Case Study Approach. CRC Press.", {
    align: AlignmentType.LEFT, spacingAfter: 120,
  }),
  p("Unger, R. and Chandler, C. (2023) A Project Guide to UX Design. New Riders.", {
    align: AlignmentType.LEFT, spacingAfter: 120,
  }),
  p("Wu (2024) 'Design and Implementation of the Online Shopping Mall System.'", {
    align: AlignmentType.LEFT, spacingAfter: 200,
  }),
  new Paragraph({ children: [new PageBreak()] }),
);

// ========== APPENDIX A ==========
children.push(
  h1("Appendix A – Use Case Descriptions"),
  body("The following summaries describe the nine primary use cases used in analysis. Full step tables from the project UML pack may be expanded further in the final report."),
  ...blank(1),
);

const ucs = [
  ["UC_01", "Customer Login Management", "Customer", "Allows customers (and shared login entry for staff/admin accounts) to authenticate securely using email and password.", "Valid account exists and is active.", "User submits credentials; system validates; session is created; user is redirected by role.", "Invalid credentials or disabled account show an error."],
  ["UC_02", "Review & Rating Management", "Customer", "Lets customers submit star ratings and review text for products.", "Customer is signed in; product exists; purchase/completion rules are enforced by the system.", "Customer opens product review form, enters rating/comment, system validates and saves.", "Rejected if validation or eligibility checks fail."],
  ["UC_03", "Product Management", "Admin", "Admin maintains products, photos, sizes/stock, brands and categories.", "Admin is authenticated and active.", "Admin creates/updates/discontinues products and related catalogue data.", "Validation errors are shown for invalid input."],
  ["UC_04", "Product Browsing Management", "Customer", "Customers browse, search and filter sneakers and open detail pages.", "Catalogue data exists.", "Customer filters by brand/category/size, views details and new arrivals.", "Empty result message when no products match."],
  ["UC_05", "Reports & Staff Management", "Admin", "Admin manages staff/admin accounts and related administrative oversight.", "Admin is authenticated.", "Admin creates accounts and enables/disables them.", "Errors shown if email is not unique across role tables."],
  ["UC_06", "Reports Management", "Admin", "Admin views sales, inventory and customer-oriented reports.", "Transactional data exists in the database.", "Admin opens reports dashboard and reviews summaries/exports as available.", "Empty states when no data is available."],
  ["UC_07", "Inventory Management", "Staff", "Staff update stock quantities per product size and monitor low stock.", "Staff is authenticated; products/sizes exist.", "Staff filters low stock and updates quantities.", "Validation prevents invalid stock values."],
  ["UC_08", "Order Processing Management", "Staff", "Staff move orders through pending → confirmed → processing → shipped → completed, or cancel with restock.", "Orders exist.", "Staff views order details and updates status.", "Invalid transitions are rejected."],
  ["UC_09", "Payment Confirmation Management", "Staff", "Staff review wallet payment proof images and confirm or reject payments.", "Payment proof submitted and still pending.", "Staff inspects screenshot/reference and updates payment status; confirmation can confirm the order.", "Rejection leaves payment rejected; invalid proof is not accepted."],
];

for (const [id, name, actor, desc, pre, main, alt] of ucs) {
  children.push(
    p(`${id} – ${name}`, { bold: true, align: AlignmentType.LEFT, spacingBefore: 160, spacingAfter: 80 }),
    runs([{ text: "Primary Actor: ", bold: true }, { text: actor }], { align: AlignmentType.LEFT, spacingAfter: 60 }),
    runs([{ text: "Description: ", bold: true }, { text: desc }], { align: AlignmentType.JUSTIFIED, spacingAfter: 60 }),
    runs([{ text: "Preconditions: ", bold: true }, { text: pre }], { align: AlignmentType.JUSTIFIED, spacingAfter: 60 }),
    runs([{ text: "Main Flow: ", bold: true }, { text: main }], { align: AlignmentType.JUSTIFIED, spacingAfter: 60 }),
    runs([{ text: "Alternative Flow: ", bold: true }, { text: alt }], { align: AlignmentType.JUSTIFIED, spacingAfter: 120 }),
  );
}

const doc = new Document({
  styles: {
    default: {
      document: {
        run: { font: "Times New Roman", size: 24 },
      },
    },
    paragraphStyles: [
      {
        id: "Heading1",
        name: "Heading 1",
        basedOn: "Normal",
        next: "Normal",
        quickStyle: true,
        run: { font: "Times New Roman", size: 32, bold: true },
        paragraph: { spacing: { before: 240, after: 160, line: 360 }, outlineLevel: 0, keepNext: true },
      },
      {
        id: "Heading2",
        name: "Heading 2",
        basedOn: "Normal",
        next: "Normal",
        quickStyle: true,
        run: { font: "Times New Roman", size: 28, bold: true },
        paragraph: { spacing: { before: 220, after: 120, line: 360 }, outlineLevel: 1, keepNext: true },
      },
      {
        id: "Heading3",
        name: "Heading 3",
        basedOn: "Normal",
        next: "Normal",
        quickStyle: true,
        run: { font: "Times New Roman", size: 26, bold: true },
        paragraph: { spacing: { before: 180, after: 100, line: 360 }, outlineLevel: 2, keepNext: true },
      },
    ],
  },
  sections: [
    {
      properties: {
        page: {
          // A4
          size: { width: 11906, height: 16838 },
          margin: { top: 1440, right: 1440, bottom: 1440, left: 1440 },
        },
      },
      headers: {
        default: new Header({
          children: [
            new Paragraph({
              alignment: AlignmentType.RIGHT,
              children: [
                new TextRun({
                  text: "SlayNa Sneaker Store – ISP Interim Report",
                  italics: true,
                  size: 16,
                  font: "Times New Roman",
                  color: "666666",
                }),
              ],
            }),
          ],
        }),
      },
      footers: {
        default: new Footer({
          children: [
            new Paragraph({
              alignment: AlignmentType.CENTER,
              children: [
                new TextRun({ text: "Page ", size: 18, font: "Times New Roman" }),
                new TextRun({ children: [PageNumber.CURRENT], size: 18, font: "Times New Roman" }),
              ],
            }),
          ],
        }),
      },
      children,
    },
  ],
});

Packer.toBuffer(doc).then((buffer) => {
  fs.mkdirSync(path.dirname(OUT), { recursive: true });
  fs.mkdirSync(path.dirname(OUT2), { recursive: true });
  fs.writeFileSync(OUT, buffer);
  fs.writeFileSync(OUT2, buffer);
  console.log("Wrote", OUT);
  console.log("Wrote", OUT2);
  console.log("Bytes", buffer.length);
});
