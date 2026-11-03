# SlayNa Sneaker Store — Class Diagram

UML class diagram of the Eloquent models in `app/Models`. Attributes come from
`database/migrations`; methods are the public model methods (relationship
accessors are shown as associations). A filled diamond (`*--`) marks a
cascade-delete composition.

GitHub and VS Code (with a Mermaid extension) render this diagram directly.

```mermaid
classDiagram
direction TB
class User {
  +int id
  +string name
  +string email
  -string password
  +string phone
  +string address
  +enum role
  +enum status
  +isCustomer() bool
  +isStaff() bool
  +isAdmin() bool
}
class Staff {
  +int id
  +int user_id
  +int managed_by
  +string position
  +date hired_on
  +processedOrders()
  +confirmedPayments()
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
class CartItem {
  +int id
  +int customer_id
  +int variant_id
  +int quantity
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
  +int variant_id
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
  +enum payment_method
  +string proof_image
  +string transaction_ref
  +enum payment_status
  +int confirmed_by
  +datetime confirmed_at
  +methodLabel() string
}
class Review {
  +int id
  +int product_id
  +int customer_id
  +int order_id
  +int rating
  +text comment
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
class ProductVariant {
  +int id
  +int product_id
  +int size_id
  +int stock_quantity
  +isLowStock() bool
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
User "1" -- "0..1" Staff : staffProfile
User "1" -- "0..*" Staff : manages
User "1" *-- "0..*" SavedCard : owns
User "1" *-- "0..*" CartItem : cart
User "1" -- "0..*" Order : places
User "0..1" -- "0..*" Order : processes
User "0..1" -- "0..*" Payment : confirms
User "1" -- "0..*" Review : writes
Order "1" *-- "1..*" OrderItem : items
Order "1" *-- "0..1" Payment : payment
Order "1" -- "0..*" Review : verifies
Brand "1" -- "0..*" Product
Category "1" -- "0..*" Product
Product "1" *-- "1..*" ProductVariant : variants
Size "1" -- "0..*" ProductVariant
Product "1" *-- "0..*" ProductImage : images
Product "1" -- "0..*" Promotion : promotions
Product "0..1" -- "0..*" GalleryImage
Product "1" -- "0..*" Review : reviews
ProductVariant "1" -- "0..*" CartItem
ProductVariant "1" -- "0..*" OrderItem
Promotion "0..1" -- "0..*" OrderItem : applied
```
