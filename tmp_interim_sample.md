

Statement and Confirmation of Own Work


Programme/Qualification
BSc(Hons) Business Computing and Information Systems

All NCC Education/UCLan assessed assignments submitted by students must have this statement as the cover page or it will not be accepted for marking. Please ensure that this statement is either firmly attached to the cover of the assignment or electronically inserted into the front of the assignment.


Student declaration

I have read and understood both UCLan’s Assessment Handbook and NCC Education’s Referencing and Bibliographies document (available on the main course page of the VLE). To the best of my knowledge my work has been accurately referenced and all sources cited correctly.

I can confirm the following details:

Student ID/Registration number: 202635 / G21407789

Name: Sai Thurein Khant

Centre Name: Strategy First International College

Module Name: Information Systems Project (Interim Report)

Module Leader: steve.wade@nccedu.com

Number of words: 1185 

 I confirm that this is my own work and that I have not colluded or plagiarised any part of it.

Due Date: Tuesday, 26 May

Student Signature: Sai 

Submitted Date: 26.5.2026

BSc (Hons) Business Computing andInformation SystemsUniversity of LancashireStatement and Confirmation of Own WorkBSc (Hons) Business Computing andInformation SystemsUniversity of LancashireStatement and Confirmation of Own Work
BSc (Hons) Business Computing and
Information Systems
University of Lancashire
Statement and Confirmation of Own Work
BSc (Hons) Business Computing and
Information Systems
University of Lancashire
Statement and Confirmation of Own Work




Student Declaration



A signed copy of this form must be submitted with every assignment.
If the statement is missing your work may not be marked.


I confirm the following details:

Candidate Name:
Sai Thurein Khant   
Candidate ID Number:
 202635    
Qualification:
 BSc (Hons) Business Computing & Information Systems   
Unit:
 Information Systems Project [Interim Report]  
Centre:
 Strategy First International College

I have read and understood both NCC Education’s Academic Misconduct Policy and the Referencing and Bibliographies document. To the best of my knowledge my work has been accurately referenced and all sources cited correctly.
I confirm that this is my own work and that I have not colluded or plagiarised any part of it.
Candidate Signature:
 Sai
Date:
 26.5.2026







Table of Contents
Title4
Background and Research4
Aims5
Objectives5
Research6
Development Methodology7
Use Case Diagram7
Details Use Case Diagram9
Class Diagram9
Initial Class Diagram10
Detailed Use Case Diagram10
Sequence Diagram11
Prototype12
GUI Designs12
Learning12
New Areas of Knowledge12
New Skills Achieved12
Bibliography14


























Title

Creation of an E-Commerce Website for SlayNa Sneaker Store

Background and Research

SlayNa Sneaker Store is a long-term licensed sneaker store that sells a range of authentic sneaker brands in Yangon, Myanmar. Currently, the company is primarily working at the brick-and-mortar shop and social media sites like Facebook and Instagram are used to promote products and communicate with customers. These channels have proved useful for initial market exposure but offer a number of constraints in terms of scalability of the business, the reach of customers and the streamlined order management.
With e-commerce booming in South-East Asia, SlayNa Sneaker Store is poised for an exciting opportunity to grow its online presence. Recent research has shown that adoption of ecommerce has picked up significantly over the past few years, largely due to better mobile Internet connectivity and the proliferation of smartphones in Myanmar. Considering this market trend, this project aims to design and develop an all-encompassing ecommerce site, allowing customers to view products, sign up, add orders online, and leave feedback, while also offering staff and administration the means to handle stock, orders, and reporting. The system aims to boost the company's overall revenue, improve brand recognition, and enhance its operational efficiency.
Since I made the Initial Proposal, more work has been done on platforms similar to this to inform a lot of architectural and design choices in the project, such as the choice of development tools, and additional refinement of the functional requirements. The back-end technologies for building the system will be PHP and MySQL, while the front-end interface will be responsive with HTML5, CSS3, and Bootstrap (Blum, 2018).
Aims
The main goal of this project is to create a secure, scalable, and user-friendly e-commerce website for the SlayNa Sneaker Store that will allow customers to browse, manage their accounts, and place orders online, while providing business administrators with extensive inventory management, employee management, and sales reporting capabilities.



Objectives
Rank 
Priority
Type
Objective
1
Functional 
Technical 
Create an adaptive and user-friendly product catalogue, so that customers can browse and search for sneakers by brand and category.
2
Functional 
Technical 
Establish a user registration and authentication system that is secure for customers, staff and administration.
3
Functional 
Technical 
Allow customers to place orders via a simple checkout process, and add products to their shopping cart.
4
Functional 
Technical 
Create an admin dashboard for product management, inventory, order processing and sales reporting.
5
Non-Functional 
Technical 
Test that it works with the most popular web browsers and is compatible with desktop and mobile devices.
6
Functional 
Informational 
Create a customer review and rating system for purchasing decisions and increased product visibility.
7
Non-functional 
Technical 
Implement the security measures that will help protect customer information (input validation, secure database interactions etc.).
8
Functional 
Development
Create detailed project documentation, such as requirements analysis, design diagrams, and test plans
9
Non-functional 
Skill Development
Develop PHP and MySQL skills, HTML/CSS, and Bootstrap skills by doing.
10 
Non-functional 
Academic
Develop research capabilities, analytical and project management abilities, working independently on projects.









Research
To understand the design and functionality of the SlayNa e-commerce website, three different websites of sneaker retailers were analyzed.
One of the widely known athletic footwear brands, Foot Locker (footlocker.com) sets a high standard for product presentation, search capabilities and secure payment collection on their website. It is a good loyalty rewards programme and an organized catalogue with powerful filtering features that makes its strength. But for a small business like SlayNa, implementing everything that had to be done would be too complex and impractical for the scope and budget of this project. (Barrabi 2019)
Nike site was analysed for its neat home page structure, great product presentation and easy navigation system. The category filtering, sneaker detail pages and modern promotional banners offered helpful design concepts for the SlayNa customer interface. Also, Nike's responsive design had highlighted the need to design a website that works well both on a desktop and on a mobile device. (Konigle, 2025)
Adidas was also explored to learn more about how a big boxed retailer like Adidas structures their product catalogue, shopping experience, and branding components. The visual structure and layout planning was inspired by the website's consistent color scheme, simple navigation bar, organized product grid, etc. In addition, the Adidas website emphasized the need for the inclusion of contemporary e-commerce elements, like customer-focused design considerations, secure checkout procedures, and filtering features. (EdrawMind, 2026)
This research provided valuable insights into responsive web design, user experience (UX), navigation flow and product management features. (Unger and Chandler, 2023) The results could have influenced the design and functionality of the SlayNa Sneaker Store website, and they ensured that the proposed system would meet the standards of modern e-commerce, appropriate for a university-level software development project.
Development Methodology
This project has adopted the Agile software development methodology, using iterative sprints to structure requirements gathering, design, development and review. Agile was chosen because it deals with changing requirements and allows frequent self-reviewing of progress during the development process. In Agile, the Scrum approach encourages a structured planning system with sprint cycles and a retrospective review of the work completed, which allows for a consistent process of review and improvement of the deliverables. (Bodén and Johansson, 2017)
Use Case Diagram
A Use Case Diagram is used to visually describe the interactions between various users and the system. It describes the essential functions of the site and the link between actors and the functioning of the system. (Sundaramoorthy, 2022) 

Fig 1 Overall Use Case Diagram
Details Use Case Diagram

Fig 2 Detail Use Case Diagram of Customer Login
Class Diagram 
A static structure diagram that describes an object-oriented system by representing the classes, attributes, operations (methods), and the relationships among objects in the system. It is the main architectural paradigm for software developers, which maps conceptual models of the domain to the actual program code. (Berenbach et al., 2009)
Initial Class Diagram

Fig 3 Initial Class Diagram
Detailed Use Case Diagram

Fig 4 Detailed Class Diagram

Sequence Diagram 
A Sequence Diagram is a dynamic Unified Modeling Language (UML) behavioral diagram which shows the order of the calls that are made between the objects and components in a particular sequence or scenario. (Shen, Robinson and Niu, 2011)

Fig 5 Customer Registration Sequence Diagram
Prototype 
GUI Designs
Initial wireframes and GUI prototypes of the SlayNa Sneaker Store website were created using Figma to develop a modern, responsive, user-friendly e-commerce interface. The prototypes are: Home Page, Product Listing Page, Product Detail Page, Shopping Cart, Checkout Page, Customer Dashboard and Administrator Dashboard. It's designed in a responsive web manner with a Bootstrap 5 grid layout for device compatibility. Further, the checkout was streamlined into a “two-step” checkout process to improve usability and user experience.


Fig 6 Prototype of SlayNa Sneaker Store’s Home Page

Fig 7 Prototype of SlayNa Sneaker Store’s Place Order Page

Fig 8 Prototype of SlayNa Sneaker Store’s Admin Dashboard 
Learning
New Areas of Knowledge

These are the new areas of knowledge that have been gained in the first semester of this project:

E-Commerce Architecture: Understanding of the architecture elements of an eCommerce system, including the front-end layers of presentation, back-end layer of business logic and database management, and how they collaborate to support online retail. (Razak et al., 2024)
Agile Software Development Methodology: Knowledge of principles and practices of Agile development, such as Sprint Planning, Iterative Delivery and Retrospective Evaluation and their application to individual projects.
UML Diagramming Conventions: Understanding the notation and conventions for Use Case diagrams, Class diagrams and Sequence diagrams and the different viewpoints that each one of these diagrams offers in system analysis and system design. (Saeki et al., 2024)
New Skills Achieved
These are the new skills which have been learnt in the first semester of this project:

UML Modelling Skill: Creating a Use Case, Class and Sequence Diagrams using UML standards and conventions and using these diagrams to communicate and validate the system requirements.
Database Design Skill: Design and document a relational database schema in MySQL including data types, primary and foreign keys, and tables.
Prototype Design Skill: producing a responsive wireframe and GUI mockups for websites based on the Bootstrap and usability principles.
PHP Development Skill: Ability to develop structured PHP server-side scripts for handling forms, sessions and communicating with databases, such as building parameterized queries to avoid SQL injection.
Skill in Requirements Analysis: Elicit, Document and Prioritize functional and non-functional requirements, using structured analysis and comparison with similar systems.








Bibliography
Blum, R. (2018) PHP, MySQL, & JavaScript All-in-One for dummies. John Wiley & Sons.
Barrabi, T 2019, “Foot Locker, Nike open tech-enhanced ‘Power Store’ in NYC,” FOXBusiness, https://n-p-r.org/retail/foot-locker-nike-power-store-nyc.
Konigle (2025) 'Nike Website Design Case Study,' Konigle, 16 June. https://konigle.com/blog/nike-website-design.
EdrawMind (2026) EDrawMind - AI mind mapping & Brainstorming Tool. https://edrawmind.wondershare.com/strategy-planning/adidas-marketing-mix-analysis.html.
Unger, R. and Chandler, C. (2023) A Project Guide to UX design: For User Experience Designers in the Field or in the Making. New Riders.
Bodén, J. and Johansson, C. (2017) Scaling agile development, Chalmers Publication Library (Chalmers University of Technology). http://studentarbeten.chalmers.se/publication/248971-scaling-agile-development.
Sundaramoorthy, S. (2022) UML diagramming: A Case Study Approach. CRC Press.
Berenbach, B. et al. (2009) Software & Systems Requirements Engineering: in practice. McGraw Hill Professional.
Saeki, M. et al. (2024) Advances in conceptual modeling: ER 2024 Workshops, AISA, CMLS, EmpER, QUAMES, JUSMOD, LLM4Modeling, Pittsburgh, PA, USA, October 28–31, 2024, Proceedings. Springer Nature.
Razak, N. et al. (2024) 'Exploring Digital Entrepreneurship: A qualitative study on new business models and digital marketing strategies,' Golden Ratio of Marketing and Applied Psychology of Business, 4(2), pp. 140–151. https://doi.org/10.52970/grmapb.v4i2.554.














Appendix 
(a) Functional Scope
Customer Scope 
Staff Scope 
Admin Scope 
Register and log in to a personal account
Browse sneaker products by category and brand
Search for specific products using a search bar
View detailed product information and imagery
Add items to a shopping cart
Place and confirm online orders
Submit product reviews and star ratings
Manage saved payment card information
Log in to a dedicated staff account
Manage product listings including adding, editing, and removing items
Update sneaker inventory levels and availability
View and process customer orders
Receive and respond to low+-stock alerts
Log in to an administrator account
Manage staff and customer accounts
Full product and category management
Monitor orders and payment records
Generate sales, inventory, and customer reports
Manage and update website content












(b) Detailed Use Case Diagram

Fig Detail Use Case Diagram for Customer Login

Fig Detail Use Case Diagram for Customer Review & Rating Management




Fig Detail Use Case Diagram for Customer Product Browsing Management


Fig Detail Use Case Diagram for Admin Manage Product 

Fig Detail Use Case Diagram for Admin Reports & Staff/Employee Management







Fig Detail Use Case Diagram for Admin Reports 

Fig Detail Use Case Diagram for Staff Inventory Management

Fig Detail Use Case Diagram for Staff Order Processing Management

Fig Detail Use Case Diagram for Staff Payment Confirmation Management









(c) Use Case Descriptions
Customer Login Management
Use Case ID
UC_01
Use Case Name
Customer Login Management 
Primary Actor 
Customer
Description
This use case enables customers to safely log in to the SlayNa Sneaker Store system.
Preconditions 
Customer is required to make a purchase before giving a review.
Main Flow
Step


1
Customer types in email address and password.

2
System validates credentials.

3
System verifies the customer's identity.

4
Customer logs in to dashboard.
Alternative Flow
An error message is displayed when login credentials are incorrect.
When the customer forgets password, the system lets reset it
Postconditions
Customer gains will be granted access to the system dashboard.

Review & Rating Management
Use Case ID
UC_02
Use Case Name
Review & Rating Management 
Primary Actor 
Customer
Description
This use case enables customers to write, edit and delete product reviews and ratings.
Preconditions 
Before posting a review, Customer is required to make a purchase of the product.
Main Flow
Step


1
Customer viewing of review section.

2
Customer writes review and rating.

3
System checks the purchase history.

4
Sneaker images are uploaded by customers.

5
Customer edits or deletes reviews if necessary.
Alternative Flow
The system will not accept the review if it is not available and there is no purchase history.
Postconditions
Customers' opinions and ratings have been successfully saved.



Product Management
Use Case ID
UC_03
Use Case Name
Product Management
Primary Actor 
Admin
Description
In this use case, the administrator can provide management of products, categories, sizes and brands in the system.
Preconditions 
Administrator will need to sign in to the system to be successful.
Main Flow
Step


1
This is the area of Product Management that is restricted for admin.

2
Admin adds new products.

3
Admin makes updates to product details.

4
Admin deletes products.

5
Admin manages categories

6
Admin manages sizes.

7
Admin manages brands.
Alternative Flow
If the product data is invalid, it will show up validation errors.
Postconditions
The success updating of product information.

Product Browsing Management
Use Case ID
UC_04
Use Case Name
Product Browsing Management
Primary Actor 
Customer
Description
This use case enables the customers to navigate through the sneaker products, search for products, and filter products based on various categories.
Preconditions 
Products have to be already present in the system.
Main Flow
Step


1
Customer visits Product Browse page.

2
Customers narrow down the options of products by size, color or brand.

3
Customers examine shoe information.Use a magnifying glass to look at the shoe information.

4
Users of the system look up products by brand

5
Type the first letters of what you're looking for, and the system will automatically show you possible options.

6
Visitors check out new footwear and recommended shoes.

7
 Customer visits sneaker products.
Alternative Flow
When there are no products in the database that match the search, the system will look for no result.
Postconditions
Customer is able to see information about product. Customer successfully reads product information.

Reports & Staff/Employee Management
Use Case ID
UC_05
Use Case Name
Reports & Staff Management
Primary Actor 
Admin
Description
This use case allows the administrator to manage employees and generate different system reports.
Preconditions 
There must be data in the system regarding employees and reports.
Main Flow
Step


1
Admin has access to management dashboard.

2
Admin controls the data of employees.

3
Admin creates Sales Reports.

4
Admin is responsible for issuing inventories.

5
Admin prepares customer reports.
Alternative Flow
If the report generation fails, then system will show an error notification.
Postconditions
The management of employee information and reports is effective.

Report Management 
Use Case ID
UC_06
Use Case Name
Reports Management
Primary Actor 
Admin
Description
In this use case, the administrator can see and produce various business reports from the system.
Preconditions 
The Sales, Inventory and Customer Data have to be stored in the Database.
Main Flow
Step


1
The reports management is available to admin.

2
Admin chooses the type of report to generate.

3
System produces sales, inventory or customer reports.

4
Reports were produced by admin views.
Alternative Flow
If the data in the report is not available, the system will give an error message.
Postconditions
Reports are successfully created and shown.

Inventory Management
Use Case ID
UC_07
Use Case Name
Inventory Management
Primary Actor 
Staff
Description
In this use case, staff can control the stock of products, availability, price information and other product information.
Preconditions 
The product records should be previously created in the inventory system.
Main Flow
Step


1
Staff has access to inventory management.

2
Staff views low stock products.

3
Staff is responsible for inventory data.

4
Staff update stock quantity.

5
Staff changes product price.

6
Staff checks product availability.
Alternative Flow
Low stock alerts are displayed when stock quantity gets low.
Postconditions
The updating of inventory information is successful.

Order Processing Management
Use Case ID
UC_08
Use Case Name
Order Processing Management
Primary Actor 
Staff
Description
The use case enables staff to handle and perform operations on customer orders, including viewing order details, approving orders, cancelling orders and updating order status.
Preconditions 
Customer Orders must be present in the system.
Main Flow
Step


1
Order management is accessed by staff.

2
Staff sees order information.

3
Employee checks on customer details.

4
Staff approves/ cancels orders.

5
Updates order status in system.

6
Order invoice is created in the system.
Alternative Flow
The order could be cancelled if they cannot be verified.
Postconditions
The updates of order status and invoice are successful.

Payment Confirmation Management
Use Case ID
UC_09
Use Case Name
Payment Confirmation Management
Primary Actor 
Staff
Description
This use case enables staff to check payment submission by viewing payment proof, validate payment or invalidate payment.
Preconditions 
Proof of customer payments needs to be loaded into the system.
Main Flow
Step


1
Staff is able to view management of payment confirmation.

2
Staff observes proof of payment.

3
Staff confirms payment details.

4
Staff verifies and accepts or denies payment.

5
Updates payment status for the system.
Alternative Flow
The staff does not accept the payment if the proof of payment is not correct.
Postconditions
The payment status gets updated successfully to the system.



















(d) Sequence Diagrams

Fig Sequence Diagram of Customer Registration Diagram

Fig Sequence diagram of Customer Login

Fig Sequence Diagram of Admin Login (and Feedback Pyint twar tL)

Fig Sequence Diagram of Admin Product Registration

Fig Sequence Diagram of Admin Report Generation 

Fig Sequence Diagram of Staff Order Management 

(e) Similar Website of SlayNa Sneaker Store  


Fig Nike Home Page 

Fig Nike Payment Plan

Adidas Website 

Fig Adidas Home page 

Fig Adidas Login Page  














Foot Locker Website

Fig Foot Locker Home Page

Fig Foot Locker New Arrival Page 







