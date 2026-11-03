# SlayNa Sneaker Store — Class Diagram

UML class diagram of the Eloquent models in `app/Models`. Each class is one
database table; attributes are the table's columns (from `database/migrations`)
and methods are the public model methods (relationships are drawn as
associations). A filled diamond (`*--`) marks a cascade-delete composition.

Admin, Staff and Customer are separate tables, each with its own login guard
(`admin`, `staff`, `web` in `config/auth.php`). An email can only belong to one
of them.

**Keep this file and the database in sync:** any table or column change here
needs a migration, and any migration that changes the schema needs this
diagram updated.

GitHub and VS Code (with a Mermaid extension) render this diagram directly.

| Class | Table |
|---|---|
| Admin | `admins` |
| Staff | `staff` |
| Customer | `customers` |
| SavedCard | `saved_cards` |
| Cart | `carts` |
| Order | `orders` |
| OrderItem | `order_items` |
| Payment | `payments` |
| PaymentMethod | `payment_methods` |
| Review | `reviews` |
| Product | `products` |
| ProductSize | `product_sizes` |
| ProductImage | `product_images` |
| Promotion | `promotions` |
| GalleryImage | `gallery_images` |
| Brand | `brands` |
| Category | `categories` |
| Size | `sizes` |

```mermaid
classDiagram
direction TB
class Admin {
  +int id
  +string name
  +string email
  -string password
  +string phone
  +enum status
  +datetime created_at
  +isAdmin() bool
  +isActive() bool
}
class Staff {
  +int id
  +string name
  +string email
  -string password
  +string phone
  +int managed_by
  +string position
  +date hired_on
  +enum status
  +datetime created_at
  +isStaff() bool
  +isActive() bool
}
class Customer {
  +int id
  +string name
  +string email
  -string password
  +string phone
  +string address
  +enum status
  +datetime created_at
  +isCustomer() bool
  +isActive() bool
}
class SavedCard {
  +int id
  +int customer_id
  +string card_holder
  +char card_last4
  +string card_type
  +int expiry_month
  +int expiry_year
}
class Cart {
  +int id
  +int customer_id
  +int product_size_id
  +int quantity
  +datetime created_at
  +subtotal() float
}
class Order {
  +int id
  +int customer_id
  +datetime order_date
  +string shipping_address
  +decimal total_amount
  +enum order_status
  +int processed_by
  +STATUSES$
  +hasPromotion() bool
  +promotionSavings() float
  +canBeCancelledByCustomer() bool
  +stepIndex() int
  +cancel(processedBy) void
  +statusColor() string
}
class OrderItem {
  +int id
  +int order_id
  +int product_size_id
  +int quantity
  +decimal unit_price
  +decimal original_price
  +int discount_percent
  +int promotion_id
  +string promotion_title
  +subtotal() float
  +isPromotional() bool
  +savings() float
}
class Payment {
  +int id
  +int order_id
  +int payment_method_id
  +string proof_image
  +string transaction_ref
  +enum payment_status
  +int confirmed_by
  +datetime confirmed_at
  +datetime created_at
  +methodLabel() string
}
class PaymentMethod {
  +int id
  +string code
  +string name
  +idFor(code)$ int
}
class Review {
  +int id
  +int product_id
  +int customer_id
  +int order_id
  +int rating
  +text comment
  +datetime created_at
}
class Product {
  +int id
  +string name
  +text description
  +decimal price
  +int brand_id
  +int category_id
  +string image_path
  +int low_stock_threshold
  +enum status
  +effectivePrice() float
  +totalStock() int
  +averageRating() float
  +reviewCount() int
  +galleryUrls() array
  +scopeLowStock()
}
class ProductSize {
  +int id
  +int product_id
  +int size_id
  +int stock_quantity
  +isLowStock() bool
  +scopeLowStock()
}
class ProductImage {
  +int id
  +int product_id
  +string image_path
  +int sort_order
  +imageUrl() string
}
class Promotion {
  +int id
  +string title
  +string description
  +int product_id
  +int discount_percent
  +string image_path
  +string hover_image_path
  +date ends_on
  +bool is_active
  +isLive() bool
  +discountedPrice() float
  +savings() float
  +scopeLive()
}
class GalleryImage {
  +int id
  +string image_path
  +string title
  +string subtitle
  +int product_id
  +int sort_order
  +bool is_active
  +scopeShown()
}
class Brand {
  +int id
  +string name
  +string logo_path
  +bool show_in_marquee
  +logoUrl() string
  +scopeInMarquee()
}
class Category {
  +int id
  +string name
}
class Size {
  +int id
  +string label
}
Admin "1" -- "0..*" Staff : manages
Customer "1" *-- "0..*" SavedCard : saves
Customer "1" *-- "0..*" Cart : adds to
Customer "1" -- "0..*" Order : places
Customer "1" -- "0..*" Review : writes
Staff "0..1" -- "0..*" Order : processes
Staff "0..1" -- "0..*" Payment : confirms
Order "1" *-- "1..*" OrderItem : contains
Order "1" *-- "0..1" Payment : paid by
Order "1" -- "0..*" Review : verifies
PaymentMethod "1" -- "0..*" Payment : used for
Brand "1" -- "0..*" Product : makes
Category "1" -- "0..*" Product : groups
Product "1" *-- "0..*" ProductSize : available in
Size "1" -- "0..*" ProductSize : used by
Product "1" *-- "0..*" ProductImage : photos
Product "1" -- "0..*" Promotion : discounts
Product "0..1" -- "0..*" GalleryImage : featured in
Product "1" -- "0..*" Review : rates
ProductSize "1" -- "0..*" Cart : holds
ProductSize "1" -- "0..*" OrderItem : orders
Promotion "0..1" -- "0..*" OrderItem : applied
```
